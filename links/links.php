<?php
/* Dead links (block 20). Every link from this site to another site is checked once a week. A link that fails three
   weekly checks in a row counts as dead. Then, depending on the setting on Tools > Link check:
   - "Warn me" (the default): an email lists the newly dead links, and the report offers, for each one, the Internet
     Archive's copy (one click), a new address typed in (replaced in every page, post, event, widget and menu that
     has the old one), or keeping the original link. Nothing changes for visitors until someone chooses.
   - "Automatically": visitors get the Internet Archive's copy straight away; the report can undo any swap.
   The archived copy is the one closest after the date the link was put on the site (the event's date, or the post's),
   from before it failed. Using it edits nothing in the database: the link is swapped while each page is built (block
   3d), so a site that comes back gets its own link back after one good check. A new address, by contrast, is written
   into the content itself (posts keep a revision of the old text).

   The Internet Archive can only offer copies it has. So, after the checks, every working link the Archive has never
   captured is sent to its Save Page Now service, once a month at most per link, so that a copy exists if the page
   later disappears. Optional archive.org keys (BX_IA_ACCESS_KEY and BX_IA_SECRET_KEY in wp-config.php, from
   https://archive.org/account/s3.php) raise the Archive's limits; without them the requests go in anonymously.

   Where links are found: published pages and posts, the events table (most of the site's links are there, which is
   why no post-content link plugin covers this site), the text and HTML widgets placed in a sidebar, and menus.
   Links to this site and to the Internet Archive are skipped.

   What counts as a failed check: no answer (address not found, refused, timed out, broken HTTPS certificate),
   404, 410, any other 4xx except the ones below, any 5xx, or a page that now sends visitors to its site's home page
   (the usual sign of a deleted article), or to a domain-parking service. 401, 403, 429, 451 and 999 mean the site
   refuses automated checks: those links are listed as "could not check" and never called dead, as are links to
   private network addresses, which are not requested at all.

   Command line: wp bx-links run | wp bx-links list [--status=dead] | wp bx-links save <url> */

if (!defined('ABSPATH')) { exit; }

define('BX_LK_OPT', 'bx_links');            // every outside link and its state (not loaded on pages)
define('BX_LK_MAP', 'bx_links_map');        // dead link => archived copy (small; read while building each page)
define('BX_LK_QUEUE', 'bx_links_queue');    // links still to check in the current run
define('BX_LK_FAILS', 3);                   // failed weekly checks in a row before a link counts as dead
define('BX_LK_CDX', 'https://web.archive.org/cdx/search/cdx');
define('BX_LK_SPN', 'https://web.archive.org/save');
define('BX_LK_UA', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0 Safari/537.36 (+https://bxscc.org/)');

/* ---------- links and settings ---------- */

/* the same link written two ways (HTML entities, spaces around it, a #section) is one link */
function bx_lk_norm($url) {
    $u = trim(html_entity_decode((string) $url, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    $hash = strpos($u, '#');
    return $hash === false ? $u : substr($u, 0, $hash);
}
function bx_lk_skip($url) {
    $host = strtolower((string) wp_parse_url($url, PHP_URL_HOST));
    if ($host === '' || !preg_match('#^https?://#i', $url)) { return true; }
    $own = strtolower((string) wp_parse_url(home_url(), PHP_URL_HOST));
    $strip = function ($h) { return preg_replace('/^www\./', '', $h); };
    if ($strip($host) === $strip($own)) { return true; }
    return (bool) preg_match('/(^|\.)(archive\.org|archive\.today|archive\.ph|archive\.is)$/', $host);
}
function bx_lk_hrefs($html) {
    if (!preg_match_all('#<a\s[^>]*?\bhref\s*=\s*(["\'])(.*?)\1#is', (string) $html, $m)) { return array(); }
    $out = array();
    foreach ($m[2] as $h) { $u = bx_lk_norm($h); if (!bx_lk_skip($u)) { $out[] = $u; } }
    return array_unique($out);
}

function bx_lk_settings() {
    $s = get_option('bx_links_settings');
    $s = is_array($s) ? $s : array();
    $mode = isset($s['mode']) && $s['mode'] === 'auto' ? 'auto' : 'ask';
    $email = isset($s['email']) && is_email($s['email']) ? $s['email'] : (string) get_option('admin_email');
    return array('mode' => $mode, 'email' => $email);
}

function bx_lk_blank() {
    return array('status' => 'new', 'fails' => 0, 'code' => 0, 'note' => '', 'checked' => 0, 'since' => 0, 'archive' => null,
                 'looked' => 0, 'choice' => '', 'warned' => 0, 'seen' => time(), 'places' => array(), 'date' => '',
                 'cap' => null, 'cap_at' => 0, 'spn' => '', 'spn_at' => 0, 'spn_note' => '', 'polled' => 0);
}
function bx_lk_state() {
    $s = get_option(BX_LK_OPT);
    if (!is_array($s)) { return array(); }
    foreach ($s as $u => $r) {
        if (array_key_exists('keep', $r)) { $r['choice'] = !empty($r['keep']) ? 'keep' : ''; unset($r['keep']); }   // 1.60 records
        $s[$u] = $r + bx_lk_blank();
    }
    return $s;
}
/* a batch saves the state it loaded at its start; anything the report changed meanwhile wins: the choice made for a
   link, and links replaced by a new address (gone from the state) or added in their place */
function bx_lk_save_merged($state) {
    $fresh = bx_lk_state();
    foreach ($state as $u => $s) {
        if (!isset($fresh[$u])) { unset($state[$u]); continue; }
        $state[$u]['choice'] = $fresh[$u]['choice'];
    }
    foreach ($fresh as $u => $s) { if (!isset($state[$u])) { $state[$u] = $s; } }
    update_option(BX_LK_OPT, $state, false);
    return $state;
}

/* every outside link on the site: url => array('places' => ['p:12', 'e:40', 'w:text-3', 'm:88'], 'date' => 'Y-m-d') */
function bx_lk_collect() {
    global $wpdb;
    $found = array();
    $add = function ($html, $place, $date) use (&$found) {
        foreach (bx_lk_hrefs($html) as $u) {
            if (!isset($found[$u])) { $found[$u] = array('places' => array(), 'date' => ''); }
            $found[$u]['places'][] = $place;
            if ($date && ($found[$u]['date'] === '' || $date < $found[$u]['date'])) { $found[$u]['date'] = $date; }
        }
    };
    foreach ($wpdb->get_results("SELECT ID, post_date, post_content FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type IN ('post', 'page')") as $p) {
        $add($p->post_content, 'p:' . $p->ID, substr($p->post_date, 0, 10));
    }
    if (function_exists('bx_ev_table')) {
        foreach ($wpdb->get_results('SELECT id, title, time, location, body, start_date FROM ' . bx_ev_table()) as $e) {
            $add($e->title . ' ' . $e->time . ' ' . $e->location . ' ' . $e->body, 'e:' . $e->id, $e->start_date);
        }
    }
    foreach (bx_lk_widgets() as $id => $w) { $add($w['html'], 'w:' . $id, ''); }
    foreach ($wpdb->get_results("SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_menu_item_url' AND meta_value LIKE 'http%'") as $mi) {
        $add('<a href="' . esc_attr($mi->meta_value) . '">', 'm:' . $mi->post_id, '');
    }
    foreach ($found as $u => $f) { $found[$u]['places'] = array_values(array_unique($f['places'])); }
    return $found;
}
/* text and HTML widgets placed in a sidebar (not the inactive ones): 'text-3' => array('base', 'n', 'field', 'html') */
function bx_lk_widgets() {
    $shown = array();
    foreach ((array) get_option('sidebars_widgets') as $area => $ids) {
        if ($area !== 'wp_inactive_widgets' && is_array($ids)) { $shown = array_merge($shown, $ids); }
    }
    $out = array();
    foreach (array('text' => 'text', 'custom_html' => 'content') as $base => $field) {
        foreach ((array) get_option('widget_' . $base) as $n => $w) {
            if (is_array($w) && !empty($w[$field]) && in_array($base . '-' . $n, $shown, true)) {
                $out[$base . '-' . $n] = array('base' => $base, 'n' => $n, 'field' => $field, 'html' => $w[$field]);
            }
        }
    }
    return $out;
}

/* ---------- talking to the outside ---------- */

/* one check: 'ok', 'fail' or 'blocked', with the status code (0: no answer) and a note */
function bx_lk_check($url) {
    // never ask the server's own network: a link to a private or reserved address is not checked
    $host = (string) wp_parse_url($url, PHP_URL_HOST);
    $ip = filter_var(trim($host, '[]'), FILTER_VALIDATE_IP) ? trim($host, '[]') : gethostbyname($host);
    if (filter_var($ip, FILTER_VALIDATE_IP) && !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        return array('blocked', 0, 'not checked: a private network address');
    }
    $r = wp_remote_get($url, array(
        'timeout' => 12, 'redirection' => 5, 'user-agent' => BX_LK_UA, 'limit_response_size' => 65536,
        'headers' => array('Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', 'Accept-Language' => 'en-US,en;q=0.9'),
    ));
    if (is_wp_error($r)) { return array('fail', 0, $r->get_error_message()); }
    $code = (int) wp_remote_retrieve_response_code($r);
    if (in_array($code, array(401, 403, 429, 451, 999), true)) { return array('blocked', $code, 'the site refuses automated checks'); }
    if ($code >= 400 || $code < 200) { return array('fail', $code, ''); }
    if ($code >= 300) { return array('fail', $code, 'redirects without saying where'); }
    // where the redirects ended
    $final = $url;
    if (isset($r['http_response']) && is_object($r['http_response']) && method_exists($r['http_response'], 'get_response_object')) {
        $obj = $r['http_response']->get_response_object();
        if (is_object($obj) && !empty($obj->url)) { $final = $obj->url; }
    }
    $path = function ($u) { return trim((string) wp_parse_url($u, PHP_URL_PATH), '/'); };
    if ($final !== $url && $path($url) !== '' && $path($final) === '' && (string) wp_parse_url($final, PHP_URL_QUERY) === '') {
        return array('fail', $code, 'now sends visitors to the home page of ' . wp_parse_url($final, PHP_URL_HOST));
    }
    $parking = '/(^|\.)(sedoparking\.com|sedo\.com|parkingcrew\.net|bodis\.com|hugedomains\.com|dan\.com|afternic\.com|above\.com|domainmarket\.com|undeveloped\.com)$/i';
    if (preg_match($parking, (string) wp_parse_url($final, PHP_URL_HOST))) { return array('fail', $code, 'the domain is parked or for sale'); }
    return array('ok', $code, '');
}

/* rows from the Wayback Machine's index of captures (good captures only); null = the Archive did not answer */
function bx_lk_cdx($q) {
    $q = array('output' => 'json', 'fl' => 'timestamp,original', 'filter' => 'statuscode:200') + $q;
    $r = wp_remote_get(add_query_arg(array_map('rawurlencode', $q), apply_filters('bx_links_cdx', BX_LK_CDX)), array('timeout' => 20, 'user-agent' => BX_LK_UA));
    if (is_wp_error($r) || (int) wp_remote_retrieve_response_code($r) !== 200) { return null; }
    $body = trim(wp_remote_retrieve_body($r));
    $rows = $body === '' ? array() : json_decode($body, true);   // no captures: [], the header row alone, or nothing at all
    if (!is_array($rows)) { return null; }
    return array_values(array_filter(array_slice($rows, 1), function ($row) { return isset($row[0], $row[1]) && preg_match('/^\d{14}$/', $row[0]); }));
}

/* the copy for a dead link: the first good capture from the year the link went on the site, else the last good one;
   never one from after the link started failing. '' = no copy; null = the Archive did not answer. */
function bx_lk_archive($url, $date, $failed_since) {
    $base = array('url' => $url, 'to' => gmdate('YmdHis', (int) $failed_since));
    $tries = array();
    if (preg_match('/^(\d{4})/', (string) $date, $y)) { $tries[] = $base + array('from' => $y[1], 'limit' => '1'); }
    $tries[] = $base + array('limit' => '-1');
    foreach ($tries as $q) {
        $rows = bx_lk_cdx($q);
        if ($rows === null) { return null; }
        if ($rows) { return 'https://web.archive.org/web/' . $rows[0][0] . '/' . $rows[0][1]; }
    }
    return '';
}

/* does the Archive have any good capture of this page? timestamp, '' (none) or null (no answer) */
function bx_lk_captured($url) {
    $rows = bx_lk_cdx(array('url' => $url, 'limit' => '-1'));
    return $rows === null ? null : ($rows ? $rows[0][0] : '');
}

function bx_lk_spn_headers() {
    $h = array('Accept' => 'application/json');
    if (defined('BX_IA_ACCESS_KEY') && defined('BX_IA_SECRET_KEY') && BX_IA_ACCESS_KEY && BX_IA_SECRET_KEY) {
        $h['Authorization'] = 'LOW ' . BX_IA_ACCESS_KEY . ':' . BX_IA_SECRET_KEY;
    }
    return $h;
}
/* ask Save Page Now for a capture: array(result, detail) with result
   'queued' (detail: job id), 'refused' (the Archive will not save this page; detail: why),
   'busy' (too many requests now; try later), 'keys' (the Archive wants archive.org keys), 'error' (no usable answer) */
function bx_lk_spn($url) {
    $r = wp_remote_post(apply_filters('bx_links_spn', BX_LK_SPN), array(
        'timeout' => 30, 'user-agent' => BX_LK_UA, 'headers' => bx_lk_spn_headers(),
        'body' => array('url' => $url, 'skip_first_archive' => '1', 'if_not_archived_within' => '30d'),
    ));
    if (is_wp_error($r)) { return array('error', $r->get_error_message()); }
    $code = (int) wp_remote_retrieve_response_code($r);
    $j = json_decode(wp_remote_retrieve_body($r), true);
    if ($code === 429) { return array('busy', 'too many requests'); }
    if (in_array($code, array(401, 403), true) || (!is_array($j) && $code >= 200 && $code < 500)) { return array('keys', 'HTTP ' . $code); }
    if (!is_array($j)) { return array('error', 'HTTP ' . $code); }
    if (!empty($j['job_id'])) { return array('queued', (string) $j['job_id']); }
    $ext = isset($j['status_ext']) ? (string) $j['status_ext'] : '';
    $msg = trim($ext . ' ' . (isset($j['message']) ? (string) $j['message'] : ''));
    if (in_array($ext, array('error:too-many-requests', 'error:user-session-limit', 'error:too-many-daily-captures'), true)) { return array('busy', $msg); }
    if (in_array($ext, array('error:unauthorized', 'error:no-access'), true)) { return array('keys', $msg); }
    return array('refused', $msg !== '' ? $msg : 'HTTP ' . $code);
}
/* how a Save Page Now job ended: array('success', timestamp) | array('pending', '') | array('error', why) | null */
function bx_lk_spn_status($job) {
    $r = wp_remote_get(trailingslashit(apply_filters('bx_links_spn', BX_LK_SPN)) . 'status/' . rawurlencode($job), array('timeout' => 20, 'user-agent' => BX_LK_UA, 'headers' => bx_lk_spn_headers()));
    if (is_wp_error($r) || (int) wp_remote_retrieve_response_code($r) !== 200) { return null; }
    $j = json_decode(wp_remote_retrieve_body($r), true);
    if (!is_array($j) || empty($j['status'])) { return null; }
    if ($j['status'] === 'success') { return array('success', isset($j['timestamp']) ? (string) $j['timestamp'] : ''); }
    if ($j['status'] === 'pending') { return array('pending', ''); }
    return array('error', trim((isset($j['status_ext']) ? $j['status_ext'] . ' ' : '') . (isset($j['message']) ? (string) $j['message'] : '')));
}

/* ---------- the weekly run, in batches small enough for one web request each ---------- */

/* start a run: refresh the list of links from the site, queue those not checked in the last six days */
function bx_lk_start() {
    $found = bx_lk_collect();
    $state = bx_lk_state();
    $now = time();
    foreach ($found as $u => $f) {
        if (!isset($state[$u])) { $state[$u] = bx_lk_blank(); }
        $state[$u]['places'] = $f['places'];
        $state[$u]['date'] = $f['date'];   // '' for a sidebar or menu link: no date to start the archive search from
    }
    foreach (array_keys($state) as $u) { if (!isset($found[$u])) { unset($state[$u]); } }   // no longer on the site
    $gap = (int) apply_filters('bx_links_gap', 6 * DAY_IN_SECONDS);
    $queue = array();
    foreach ($state as $u => $s) { if ($s['checked'] <= $now - $gap) { $queue[] = $u; } }
    update_option(BX_LK_OPT, $state, false);
    update_option(BX_LK_QUEUE, $queue, false);
    update_option('bx_links_run', array('started' => $now, 'finished' => 0, 'queued' => count($queue), 'spn_stop' => ''), false);
    return count($queue) + count(bx_lk_owed($state, $now, ''));
}

/* the Archive work a run still owes, after the checks: url => 'copy' (find the copy for a dead link), 'poll' (how did a
   capture request end), 'look' (has the Archive ever captured this working link), 'save' (ask it to). Each at most once
   per run; requests to save stop for the run when the Archive says it is busy or wants keys. */
function bx_lk_owed($state, $started, $spn_stop) {
    $owed = array('copy' => array(), 'poll' => array(), 'look' => array(), 'save' => array());   // in this order: dead links first
    $month = 30 * DAY_IN_SECONDS;
    foreach ($state as $u => $s) {
        $live = in_array($s['status'], array('ok', 'blocked'), true);
        if ($s['status'] === 'dead' && $s['archive'] === null && $s['looked'] < $started) { $owed['copy'][$u] = 'copy'; }
        elseif ($live && $s['cap'] === '' && $s['spn'] !== '' && $s['spn_at'] < $started && $s['polled'] < $started) { $owed['poll'][$u] = 'poll'; }   // asked on an earlier run
        elseif ($live && $s['cap'] === null && $s['cap_at'] < $started) { $owed['look'][$u] = 'look'; }
        elseif ($live && $s['cap'] === '' && $s['spn'] === '' && $s['spn_at'] < time() - $month && $spn_stop === '') { $owed['save'][$u] = 'save'; }
    }
    return $owed['copy'] + $owed['poll'] + $owed['look'] + $owed['save'];
}

/* check queued links until the time budget is used, then do the Archive work; returns how much is left */
function bx_lk_work($budget) {
    $stop = microtime(true) + $budget;
    $state = bx_lk_state();
    $queue = get_option(BX_LK_QUEUE);
    $queue = is_array($queue) ? $queue : array();
    $run = get_option('bx_links_run');
    $run = is_array($run) ? $run + array('started' => 0, 'spn_stop' => '') : array('started' => 0, 'spn_stop' => '');
    $hosts = array();
    while ($queue && microtime(true) < $stop) {
        $u = array_shift($queue);
        if (!isset($state[$u])) { continue; }
        $h = wp_parse_url($u, PHP_URL_HOST);
        if (isset($hosts[$h]) && microtime(true) - $hosts[$h] < 1) { usleep(1000000); }   // one request a second per site
        list($result, $code, $note) = bx_lk_check($u);
        $hosts[$h] = microtime(true);
        $s = $state[$u];
        $s['checked'] = time(); $s['code'] = $code; $s['note'] = $note;
        if ($result === 'ok') {
            if ($s['status'] === 'dead' && $s['choice'] === 'archive') { $s['choice'] = ''; }   // back: its own link again
            $s['status'] = 'ok'; $s['fails'] = 0; $s['since'] = 0; $s['archive'] = null;    // if it dies again, look again
        } elseif ($result === 'blocked') {
            $s['status'] = 'blocked';   // neither a failure nor a success: the count of failures stays as it was
        } else {
            $s['fails']++;
            if ($s['fails'] === 1) { $s['since'] = $s['checked']; }
            $s['status'] = $s['fails'] >= BX_LK_FAILS ? 'dead' : 'failing';
        }
        $state[$u] = $s;
    }
    // the Archive's turn, once the checks are done (always at least one step per batch, so a run always ends)
    $steps = 0;
    while (!$queue && ($owed = bx_lk_owed($state, (int) $run['started'], (string) $run['spn_stop']))) {
        if ($steps && microtime(true) >= $stop) { break; }
        $u = key($owed); $job = current($owed); $s = $state[$u];
        if ($job === 'copy') {
            $s['looked'] = time();
            $s['archive'] = bx_lk_archive($u, $s['date'], $s['since']);
        } elseif ($job === 'poll') {
            $s['polled'] = time();
            $st = bx_lk_spn_status($s['spn']);
            if ($st && $st[0] === 'success') { $s['cap'] = $st[1] !== '' ? $st[1] : gmdate('YmdHis'); $s['spn'] = ''; $s['spn_note'] = ''; }
            elseif ($st && $st[0] === 'error') { $s['spn'] = ''; $s['spn_note'] = $st[1]; }   // asked again in a month
        } elseif ($job === 'look') {
            $s['cap_at'] = time();
            $c = bx_lk_captured($u);
            if ($c !== null) { $s['cap'] = $c; }
        } else {
            $s['spn_at'] = time();
            list($res, $detail) = bx_lk_spn($u);
            if ($res === 'queued') { $s['spn'] = $detail; $s['spn_note'] = ''; }
            elseif ($res === 'refused') { $s['spn_note'] = $detail; }
            else {
                $s['spn_at'] = 0;   // not this link's fault: it stays first in line for the next run
                $run['spn_stop'] = $res === 'keys' ? 'the Internet Archive asked for archive.org keys' : ($res === 'busy' ? 'the Internet Archive was busy: ' . $detail : 'no usable answer from the Internet Archive: ' . $detail);
                update_option('bx_links_run', $run, false);
            }
        }
        $state[$u] = $s;
        $steps++;
        usleep((int) (($job === 'save' ? 5000000 : 1500000) * (float) apply_filters('bx_links_pace', 1)));   // about one request a second to the Archive; five seconds between capture requests
    }
    $state = bx_lk_save_merged($state);
    update_option(BX_LK_QUEUE, $queue, false);
    $left = count($queue) + count(bx_lk_owed($state, (int) $run['started'], (string) $run['spn_stop']));
    if (!$left) { bx_lk_finish($state); }
    return $left;
}

/* the swap list for pages, and the count shown on the menu; if the swap list changed, empty the page cache */
function bx_lk_apply($state) {
    $mode = bx_lk_settings()['mode'];
    $map = array(); $pending = 0;
    foreach ($state as $u => $s) {
        if ($s['status'] !== 'dead') { continue; }
        $has = is_string($s['archive']) && $s['archive'] !== '';
        if ($has && ($s['choice'] === 'archive' || ($mode === 'auto' && $s['choice'] !== 'keep'))) { $map[$u] = $s['archive']; }
        if ($mode === 'ask' && $s['choice'] === '') { $pending++; }
    }
    ksort($map);
    $old = get_option(BX_LK_MAP, array());
    if ($map !== (is_array($old) ? $old : array())) {
        update_option(BX_LK_MAP, $map, true);
        if (function_exists('bx_purge_all_pages_soon')) { bx_purge_all_pages_soon(); }
    }
    update_option('bx_links_pending', $pending, true);
}

/* end of a run: apply, warn by email about links that died since the last warning, record the time */
function bx_lk_finish($state) {
    bx_lk_apply($state);
    $set = bx_lk_settings();
    if ($set['mode'] === 'ask') {
        $new = array();
        foreach ($state as $u => $s) { if ($s['status'] === 'dead' && $s['choice'] === '' && $s['warned'] < $s['since']) { $new[$u] = $s; } }
        if ($new && bx_lk_email($set['email'], $new)) {
            $state = bx_lk_state();
            foreach (array_keys($new) as $u) { if (isset($state[$u])) { $state[$u]['warned'] = time(); } }
            update_option(BX_LK_OPT, $state, false);
        }
    }
    $run = get_option('bx_links_run');
    $run = is_array($run) ? $run : array();
    $run['finished'] = time();
    update_option('bx_links_run', $run, false);
}

function bx_lk_where_text($place) {
    list($kind, $id) = array_pad(explode(':', $place, 2), 2, '');
    if ($kind === 'p') { return 'Page: ' . html_entity_decode(get_the_title((int) $id), ENT_QUOTES, 'UTF-8'); }
    if ($kind === 'e' && function_exists('bx_ev_table')) {
        global $wpdb;
        return 'Event: ' . html_entity_decode(wp_strip_all_tags((string) $wpdb->get_var($wpdb->prepare('SELECT title FROM ' . bx_ev_table() . ' WHERE id = %d', (int) $id))), ENT_QUOTES, 'UTF-8');
    }
    return $kind === 'w' ? 'Sidebar widget' : ($kind === 'm' ? 'Menu' : $place);
}
function bx_lk_email($to, $links) {
    $report = admin_url('tools.php?page=bx-links');
    $n = count($links);
    $lines = array(($n === 1 ? 'This link' : 'These ' . $n . ' links') . ' on ' . wp_parse_url(home_url(), PHP_URL_HOST) . ' failed ' . BX_LK_FAILS . ' weekly checks in a row:', '');
    $i = 0;
    foreach ($links as $u => $s) {
        $i++;
        $lines[] = $i . '. ' . $u;
        $lines[] = '   On: ' . implode('; ', array_map('bx_lk_where_text', (array) $s['places']));
        $lines[] = '   Last result: ' . ($s['code'] ? $s['code'] : 'no answer') . ($s['note'] !== '' ? ', ' . $s['note'] : '');
        $lines[] = '   ' . (is_string($s['archive']) && $s['archive'] !== '' ? 'Internet Archive copy: ' . $s['archive'] : 'The Internet Archive has no copy of this page.');
        $lines[] = '   Choose: ' . $report . '#' . bx_lk_anchor($u);
        $lines[] = '';
    }
    $lines[] = 'For each one, the report offers: use the Internet Archive\'s copy (visitors get the copy; pages and events stay as they are), enter a new address (it replaces the old one everywhere it appears), or keep the original link.';
    $lines[] = 'Until you choose, visitors keep getting the original link.';
    $lines[] = '';
    $lines[] = 'Report and settings: ' . $report;
    $subject = '[' . wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES) . '] ' . ($n === 1 ? '1 dead link needs' : $n . ' dead links need') . ' a decision';
    return wp_mail($to, $subject, implode("\n", $lines));
}
function bx_lk_anchor($u) { return 'bx-link-' . substr(md5($u), 0, 10); }

/* WP-Cron: start once a week; each batch is one background request of at most about 20 seconds */
add_action('init', function () {
    if (!wp_next_scheduled('bx_links_weekly')) { wp_schedule_event(time() + HOUR_IN_SECONDS, 'weekly', 'bx_links_weekly'); }
});
add_action('bx_links_weekly', function () {
    if (bx_lk_start()) { wp_schedule_single_event(time(), 'bx_links_batch'); }
});
add_action('bx_links_batch', function () {
    if (get_transient('bx_links_lock')) { return; }
    set_transient('bx_links_lock', 1, 5 * MINUTE_IN_SECONDS);
    if (function_exists('set_time_limit')) { @set_time_limit(120); }
    $left = bx_lk_work((float) apply_filters('bx_links_budget', 20));
    delete_transient('bx_links_lock');
    if ($left) { wp_schedule_single_event(time() + 30, 'bx_links_batch'); }
});

/* the swap itself, called by block 3d on every page it builds */
function bx_lk_rewrite($html) {
    $map = get_option(BX_LK_MAP);
    if (!is_array($map) || !$map) { return $html; }
    $out = preg_replace_callback('#(<a\s[^>]*?\bhref\s*=\s*)(["\'])(.*?)\2#is', function ($m) use ($map) {
        $u = bx_lk_norm($m[3]);
        if (!isset($map[$u])) { return $m[0]; }
        $hash = strpos(html_entity_decode($m[3]), '#');
        $frag = $hash === false ? '' : substr(html_entity_decode($m[3]), $hash);
        return $m[1] . $m[2] . esc_url($map[$u] . $frag) . $m[2];
    }, $html);
    return is_string($out) ? $out : $html;
}

/* ---------- a new address, written into the content ---------- */

/* in one piece of HTML: every link to $old points to $new (keeping any #section), and a link whose words are its own
   old address shows the new one; returns array(html, how many links changed) */
function bx_lk_fix_html($html, $old, $new) {
    $n = 0;
    $out = preg_replace_callback('#(<a\s[^>]*?\bhref\s*=\s*)(["\'])(.*?)\2([^>]*>)(.*?)(</a>)#is', function ($m) use ($old, $new, &$n) {
        if (bx_lk_norm($m[3]) !== $old) { return $m[0]; }
        $n++;
        $dec = html_entity_decode($m[3], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $hash = strpos($dec, '#');
        $frag = $hash === false ? '' : substr($dec, $hash);
        $words = bx_lk_norm(wp_strip_all_tags($m[5])) === $old ? esc_html($new) : $m[5];
        return $m[1] . $m[2] . esc_url($new . $frag) . $m[2] . $m[4] . $words . $m[6];
    }, (string) $html);
    return is_string($out) ? array($out, $n) : array($html, 0);
}
/* replace $old with $new in every page, post, event, widget and menu item that has it; returns how many places */
function bx_lk_replace_everywhere($old, $new) {
    global $wpdb;
    $places = 0;
    kses_remove_filters();   // the text is the site's own, changed only in its links; restored below
    foreach ($wpdb->get_results("SELECT ID, post_content FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type IN ('post', 'page')") as $p) {
        list($html, $n) = bx_lk_fix_html($p->post_content, $old, $new);
        if ($n && !is_wp_error(wp_update_post(wp_slash(array('ID' => (int) $p->ID, 'post_content' => $html)), true))) { $places++; }
    }
    kses_init();
    if (function_exists('bx_ev_table')) {
        foreach ($wpdb->get_results('SELECT id, title, time, location, body FROM ' . bx_ev_table()) as $e) {
            $row = array(); $n = 0;
            foreach (array('title', 'time', 'location', 'body') as $col) {
                list($html, $k) = bx_lk_fix_html($e->$col, $old, $new);
                if ($k) { $row[$col] = $html; $n += $k; }
            }
            if ($n) { $row['updated'] = current_time('mysql'); $wpdb->update(bx_ev_table(), $row, array('id' => (int) $e->id)); $places++; }
        }
    }
    foreach (bx_lk_widgets() as $id => $w) {
        list($html, $n) = bx_lk_fix_html($w['html'], $old, $new);
        if ($n) {
            $all = get_option('widget_' . $w['base']);
            $all[$w['n']][$w['field']] = $html;
            update_option('widget_' . $w['base'], $all);
            $places++;
        }
    }
    foreach ($wpdb->get_results("SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_menu_item_url' AND meta_value LIKE 'http%'") as $mi) {
        if (bx_lk_norm($mi->meta_value) === $old) { update_post_meta((int) $mi->post_id, '_menu_item_url', esc_url_raw($new)); $places++; }
    }
    if (function_exists('bx_purge_all_pages_soon')) { bx_purge_all_pages_soon(); }
    return $places;
}

/* ---------- Tools > Link check ---------- */

function bx_lk_where($place) {
    list($kind, $id) = array_pad(explode(':', $place, 2), 2, '');
    if ($kind === 'p') {
        $t = get_the_title((int) $id);
        return '<a href="' . esc_url((string) get_edit_post_link((int) $id)) . '">' . esc_html($t !== '' ? $t : '#' . $id) . '</a>';
    }
    if ($kind === 'e') {
        global $wpdb;
        $t = function_exists('bx_ev_table') ? $wpdb->get_var($wpdb->prepare('SELECT title FROM ' . bx_ev_table() . ' WHERE id = %d', (int) $id)) : '';
        return 'Event: <a href="' . esc_url(admin_url('admin.php?page=bx-event-edit&id=' . (int) $id)) . '">' . esc_html(wp_strip_all_tags((string) $t)) . '</a>';
    }
    if ($kind === 'w') { return '<a href="' . esc_url(admin_url('widgets.php')) . '">Sidebar widget</a>'; }
    if ($kind === 'm') { return '<a href="' . esc_url(admin_url('nav-menus.php')) . '">Menu</a>'; }
    return esc_html($place);
}

add_action('admin_menu', function () {
    $n = (int) get_option('bx_links_pending', 0);
    $bubble = $n ? ' <span class="awaiting-mod count-' . $n . '"><span class="pending-count">' . $n . '</span></span>' : '';
    add_management_page('Link check', 'Link check' . $bubble, 'manage_options', 'bx-links', 'bx_lk_screen');
});

function bx_lk_back($args) {
    wp_safe_redirect(add_query_arg($args, admin_url('tools.php?page=bx-links')));
    exit;
}
add_action('admin_post_bx_links_choice', function () {
    if (!current_user_can('manage_options')) { wp_die('Not allowed.'); }
    check_admin_referer('bx_links_choice');
    $u = isset($_POST['url']) ? (string) wp_unslash($_POST['url']) : '';
    $c = isset($_POST['choice']) ? (string) wp_unslash($_POST['choice']) : '';
    $state = bx_lk_state();
    if (isset($state[$u]) && in_array($c, array('', 'archive', 'keep'), true)) {
        $state[$u]['choice'] = $c;
        update_option(BX_LK_OPT, $state, false);
        bx_lk_apply($state);
    }
    bx_lk_back(array('done' => 'choice'));
});
add_action('admin_post_bx_links_replace', function () {
    if (!current_user_can('manage_options')) { wp_die('Not allowed.'); }
    check_admin_referer('bx_links_replace');
    $old = isset($_POST['url']) ? (string) wp_unslash($_POST['url']) : '';
    $new = isset($_POST['new']) ? trim((string) wp_unslash($_POST['new'])) : '';
    $new = esc_url_raw($new, array('http', 'https'));
    $state = bx_lk_state();
    if (!isset($state[$old]) || $new === '' || !preg_match('#^https?://[^/\s]+#i', $new) || bx_lk_norm($new) === $old) { bx_lk_back(array('done' => 'badnew')); }
    $places = bx_lk_replace_everywhere($old, $new);
    $state = bx_lk_state();
    $key = bx_lk_norm($new);
    if (!isset($state[$key]) && !bx_lk_skip($key)) { $state[$key] = array('places' => $state[$old]['places'], 'date' => $state[$old]['date']) + bx_lk_blank(); }
    unset($state[$old]);
    update_option(BX_LK_OPT, $state, false);
    bx_lk_apply($state);
    $log = get_option('bx_links_log');
    $log = is_array($log) ? $log : array();
    array_unshift($log, array('t' => time(), 'old' => $old, 'new' => $new, 'places' => $places, 'user' => wp_get_current_user()->user_login));
    update_option('bx_links_log', array_slice($log, 0, 50), false);
    bx_lk_back(array('done' => 'replaced', 'n' => $places));
});
add_action('admin_post_bx_links_settings', function () {
    if (!current_user_can('manage_options')) { wp_die('Not allowed.'); }
    check_admin_referer('bx_links_settings');
    $mode = isset($_POST['mode']) && $_POST['mode'] === 'auto' ? 'auto' : 'ask';
    $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    update_option('bx_links_settings', array('mode' => $mode, 'email' => is_email($email) ? $email : ''), false);
    bx_lk_apply(bx_lk_state());
    bx_lk_back(array('done' => 'settings'));
});
add_action('admin_post_bx_links_run', function () {
    if (!current_user_can('manage_options')) { wp_die('Not allowed.'); }
    check_admin_referer('bx_links_run');
    $n = bx_lk_start();
    if ($n) { wp_schedule_single_event(time(), 'bx_links_batch'); }
    $q = get_option(BX_LK_QUEUE);
    bx_lk_back(array('done' => 'run', 'n' => is_array($q) ? count($q) : 0));
});

function bx_lk_screen() {
    if (!current_user_can('manage_options')) { return; }
    $state = bx_lk_state();
    $set = bx_lk_settings();
    $run = get_option('bx_links_run');
    $queue = get_option(BX_LK_QUEUE);
    $by = array('dead' => array(), 'failing' => array(), 'blocked' => array(), 'ok' => array(), 'new' => array());
    foreach ($state as $u => $s) { $by[isset($by[$s['status']]) ? $s['status'] : 'new'][$u] = $s; }
    $when = function ($t) { return $t ? wp_date('M j, Y g:i a', (int) $t) : 'never'; };
    $result = function ($s) {
        $r = $s['code'] ? (string) $s['code'] : 'no answer';
        return esc_html($s['note'] !== '' ? $r . ': ' . $s['note'] : $r);
    };
    $places = function ($s) { return implode('<br>', array_map('bx_lk_where', (array) $s['places'])); };
    $link = function ($u) { return '<a href="' . esc_url($u) . '" target="_blank" rel="noopener" style="word-break: break-all;">' . esc_html($u) . '</a>'; };
    $post = function ($action, $fields, $label, $class = 'small') {
        $h = '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="display: inline-block; margin: 4px 6px 0 0;"><input type="hidden" name="action" value="' . esc_attr($action) . '">';
        foreach ($fields as $k => $v) { $h .= '<input type="hidden" name="' . esc_attr($k) . '" value="' . esc_attr($v) . '">'; }
        return $h . wp_nonce_field($action, '_wpnonce', true, false) . get_submit_button($label, $class, 'submit', false) . '</form>';
    };
    $notes = array(
        'run' => 'Started: ' . (int) ($_GET['n'] ?? 0) . ' links queued (links checked in the last six days wait for next week). The check runs in the background; reload this page in a few minutes.',
        'choice' => 'Saved. Pages are being rebuilt with the change.',
        'settings' => 'Settings saved.',
        'replaced' => 'Replaced in ' . (int) ($_GET['n'] ?? 0) . ((int) ($_GET['n'] ?? 0) === 1 ? ' place' : ' places') . ' (pages, posts, events, widgets, menus). Pages are being rebuilt; the new address is checked on the next run.',
        'badnew' => 'Not replaced: enter a full address starting with http:// or https://, different from the old one.',
    );
    $done = isset($_GET['done']) ? (string) $_GET['done'] : '';
    $spn_mode = isset(bx_lk_spn_headers()['Authorization']) ? 'with this site\'s archive.org keys' : 'anonymously (no archive.org keys in wp-config.php, so the Archive\'s lower limits apply)';
    $live = array_filter($state, function ($s) { return in_array($s['status'], array('ok', 'blocked'), true); });
    $never = array_filter($live, function ($s) { return $s['cap'] === ''; });
    $asked = array_filter($never, function ($s) { return $s['spn'] !== ''; });
    $refused = array_filter($never, function ($s) { return $s['spn'] === '' && $s['spn_note'] !== ''; });
    $unknown = array_filter($live, function ($s) { return $s['cap'] === null; });
    ?>
    <div class="wrap">
      <h1>Link check</h1>
      <?php if (isset($notes[$done])) { echo '<div class="notice ' . ($done === 'badnew' ? 'notice-error' : 'notice-success') . '"><p>' . esc_html($notes[$done]) . '</p></div>'; } ?>
      <p>Every link from this site to another site is checked once a week. A link that fails <?php echo (int) BX_LK_FAILS; ?> weekly checks in a row counts as dead.
      The Internet Archive's copy, when used, is swapped in as each page is built (pages and events are not edited, and a site that comes back gets its own link back after one good check); a new address is written into the pages and events themselves.</p>

      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="background: #fff; border: 1px solid #c3c4c7; padding: 4px 16px 12px; max-width: 760px;">
        <input type="hidden" name="action" value="bx_links_settings"><?php wp_nonce_field('bx_links_settings'); ?>
        <h2 style="margin-bottom: 6px;">When a link is dead</h2>
        <label style="display: block; margin: 6px 0;"><input type="radio" name="mode" value="ask" <?php checked($set['mode'], 'ask'); ?>> Email a warning and wait for a choice: the Internet Archive's copy, a new address, or the original link (default)</label>
        <label style="display: block; margin: 6px 0;"><input type="radio" name="mode" value="auto" <?php checked($set['mode'], 'auto'); ?>> Use the Internet Archive's copy automatically, without asking (the report can still undo it, or replace the address)</label>
        <label style="display: block; margin: 10px 0 4px;">Send warnings to: <input type="email" name="email" value="<?php echo esc_attr($set['email']); ?>" class="regular-text"></label>
        <?php submit_button('Save settings', 'primary', 'submit', false); ?>
      </form>

      <p style="margin-top: 16px;">
        <?php echo count($state); ?> outside links.
        Last run: started <?php echo esc_html($when(is_array($run) ? ($run['started'] ?? 0) : 0)); ?>,
        <?php echo is_array($run) && !empty($run['finished']) && $run['finished'] >= $run['started'] ? 'finished ' . esc_html($when($run['finished'])) : 'still running (' . (is_array($queue) ? count($queue) : 0) . ' links to go, then the Internet Archive lookups)'; ?>.
        Next run: <?php echo esc_html($when(wp_next_scheduled('bx_links_weekly'))); ?>.
      </p>
      <p>
        Saving to the Internet Archive (so that a copy exists if a page disappears later): <?php echo count($never); ?> working links the Archive has never captured;
        <?php echo count($asked); ?> sent to its Save Page Now service and waiting; <?php echo count($refused); ?> it would not save; <?php echo count($unknown); ?> not looked up yet.
        Requests go in <?php echo esc_html($spn_mode); ?>.
        <?php if (is_array($run) && !empty($run['spn_stop'])) { echo 'Stopped for this run: ' . esc_html($run['spn_stop']) . '.'; } ?>
      </p>
      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="bx_links_run"><?php wp_nonce_field('bx_links_run'); ?>
        <?php submit_button('Run the check now', 'secondary', 'submit', false); ?>
      </form>

      <h2>Dead (<?php echo count($by['dead']); ?>)</h2>
      <table class="widefat striped">
        <thead><tr><th style="width: 24%;">Link</th><th style="width: 18%;">Where</th><th>Failing since</th><th>Last result</th><th style="width: 36%;">Visitors get, and what to do</th></tr></thead>
        <tbody>
        <?php if (!$by['dead']) { echo '<tr><td colspan="5">None.</td></tr>'; } ?>
        <?php foreach ($by['dead'] as $u => $s) {
            $has = is_string($s['archive']) && $s['archive'] !== '';
            $swapped = $has && ($s['choice'] === 'archive' || ($set['mode'] === 'auto' && $s['choice'] !== 'keep'));
            if ($swapped) { $gets = '<strong>The Internet Archive\'s copy</strong>: ' . $link($s['archive']); }
            elseif ($s['choice'] === 'keep') { $gets = '<strong>The original link</strong> (kept by choice).'; }
            elseif ($set['mode'] === 'ask') { $gets = '<strong>The original link, until you choose.</strong>'; }
            else { $gets = '<strong>The original link</strong>.'; }
            if (!$has) { $gets .= '<br>' . ($s['archive'] === '' ? 'The Internet Archive has no copy of this page.' : 'The Internet Archive has not answered yet; it is asked again on the next run.'); }
            elseif (!$swapped) { $gets .= '<br>Archived copy: ' . $link($s['archive']); }
            $acts = '';
            if ($has && !$swapped) { $acts .= $post('bx_links_choice', array('url' => $u, 'choice' => 'archive'), 'Use the archived copy', 'small primary'); }
            if ($swapped) { $acts .= $post('bx_links_choice', array('url' => $u, 'choice' => 'keep'), 'Keep the original link'); }
            if (!$swapped && $s['choice'] !== 'keep') { $acts .= $post('bx_links_choice', array('url' => $u, 'choice' => 'keep'), $set['mode'] === 'ask' ? 'Keep the original link (no more warnings)' : 'Keep the original link'); }
            if ($s['choice'] === 'keep') { $acts .= $post('bx_links_choice', array('url' => $u, 'choice' => ''), 'Undo'); }
            $acts .= '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="margin-top: 8px;"><input type="hidden" name="action" value="bx_links_replace"><input type="hidden" name="url" value="' . esc_attr($u) . '">'
                   . wp_nonce_field('bx_links_replace', '_wpnonce', true, false)
                   . '<input type="url" name="new" placeholder="New address: https://..." style="width: 70%;" required> '
                   . get_submit_button('Replace everywhere', 'small', 'submit', false, array('onclick' => "return confirm('Replace this link in every page, post, event, widget and menu that has it?');")) . '</form>';
            echo '<tr id="' . esc_attr(bx_lk_anchor($u)) . '"><td>' . $link($u) . '</td><td>' . $places($s) . '</td><td>' . esc_html($when($s['since'])) . '</td><td>' . $result($s) . '</td><td>' . $gets . '<div>' . $acts . '</div></td></tr>';
        } ?>
        </tbody>
      </table>

      <h2>Failing, not dead yet (<?php echo count($by['failing']); ?>)</h2>
      <p>Failed the last weekly check, but not <?php echo (int) BX_LK_FAILS; ?> in a row yet. Nothing happens until then.</p>
      <table class="widefat striped">
        <thead><tr><th style="width: 30%;">Link</th><th>Where</th><th>Failed checks in a row</th><th>Last result</th></tr></thead>
        <tbody>
        <?php if (!$by['failing']) { echo '<tr><td colspan="4">None.</td></tr>'; } ?>
        <?php foreach ($by['failing'] as $u => $s) { echo '<tr><td>' . $link($u) . '</td><td>' . $places($s) . '</td><td>' . (int) $s['fails'] . '</td><td>' . $result($s) . '</td></tr>'; } ?>
        </tbody>
      </table>

      <h2>Could not check (<?php echo count($by['blocked']); ?>)</h2>
      <p>These sites refuse automated checks, so whether the page still exists is unknown. They are never called dead; open them to see.</p>
      <table class="widefat striped">
        <thead><tr><th style="width: 30%;">Link</th><th>Where</th><th>Last result</th></tr></thead>
        <tbody>
        <?php if (!$by['blocked']) { echo '<tr><td colspan="3">None.</td></tr>'; } ?>
        <?php foreach ($by['blocked'] as $u => $s) { echo '<tr><td>' . $link($u) . '</td><td>' . $places($s) . '</td><td>' . $result($s) . '</td></tr>'; } ?>
        </tbody>
      </table>

      <?php if ($refused) { ?>
      <h2>The Internet Archive would not save (<?php echo count($refused); ?>)</h2>
      <p>Usually because the site asks not to be archived. Asked again after a month.</p>
      <table class="widefat striped">
        <thead><tr><th style="width: 40%;">Link</th><th>Asked</th><th>The Archive's answer</th></tr></thead>
        <tbody>
        <?php foreach ($refused as $u => $s) { echo '<tr><td>' . $link($u) . '</td><td>' . esc_html($when($s['spn_at'])) . '</td><td>' . esc_html($s['spn_note']) . '</td></tr>'; } ?>
        </tbody>
      </table>
      <?php } ?>

      <?php $log = get_option('bx_links_log'); if (is_array($log) && $log) { ?>
      <h2>Replaced with a new address</h2>
      <table class="widefat striped">
        <thead><tr><th>When</th><th style="width: 32%;">Old</th><th style="width: 32%;">New</th><th>Places</th><th>By</th></tr></thead>
        <tbody>
        <?php foreach ($log as $l) { echo '<tr><td>' . esc_html($when($l['t'])) . '</td><td style="word-break: break-all;">' . esc_html($l['old']) . '</td><td>' . $link($l['new']) . '</td><td>' . (int) $l['places'] . '</td><td>' . esc_html($l['user']) . '</td></tr>'; } ?>
        </tbody>
      </table>
      <?php } ?>

      <p style="margin-top: 20px;">Working: <?php echo count($by['ok']); ?>. Not checked yet: <?php echo count($by['new']); ?>.</p>
    </div>
    <?php
}

/* ---------- command line ---------- */
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('bx-links', new class {
        /**
         * Run the link check now, in this window, start to finish (links checked in the last six days are skipped).
         */
        public function run($args, $assoc) {
            bx_lk_start();
            $q = get_option(BX_LK_QUEUE);
            $total = is_array($q) ? count($q) : 0;
            WP_CLI::log($total . ' links to check, then the Internet Archive lookups.');
            while (($left = bx_lk_work(30)) > 0) {
                // each working link adds an Archive lookup, so the two counts are shown apart
                $q = get_option(BX_LK_QUEUE);
                $q = is_array($q) ? count($q) : 0;
                WP_CLI::log(sprintf('%d of %d links checked; %d Internet Archive steps to go.', $total - $q, $total, $left - $q));
            }
            $state = bx_lk_state();
            $count = array();
            foreach ($state as $s) { $count[$s['status']] = ($count[$s['status']] ?? 0) + 1; }
            ksort($count);
            foreach ($count as $k => $v) { WP_CLI::log(sprintf('%-8s %d', $k, $v)); }
            $never = count(array_filter($state, function ($s) { return in_array($s['status'], array('ok', 'blocked'), true) && $s['cap'] === ''; }));
            $run = get_option('bx_links_run');
            WP_CLI::log('Working links the Internet Archive has never captured: ' . $never . (is_array($run) && !empty($run['spn_stop']) ? ' (saving stopped: ' . $run['spn_stop'] . ')' : ''));
            $map = get_option(BX_LK_MAP);
            WP_CLI::success((is_array($map) ? count($map) : 0) . ' dead links swapped for archived copies; ' . (int) get_option('bx_links_pending', 0) . ' waiting for a decision.');
        }
        /**
         * List links with their state.
         *
         * [--status=<status>]
         * : Only this state: ok, failing, dead, blocked, new.
         */
        public function list($args, $assoc) {
            $rows = array();
            foreach (bx_lk_state() as $u => $s) {
                if (!empty($assoc['status']) && $s['status'] !== $assoc['status']) { continue; }
                $rows[] = array('status' => $s['status'], 'fails' => $s['fails'], 'code' => $s['code'], 'url' => $u,
                                'archive' => $s['archive'] === null ? '' : ($s['archive'] === '' ? '(none)' : $s['archive']), 'choice' => $s['choice'],
                                'captured' => $s['cap'] === null ? '?' : ($s['cap'] === '' ? 'never' : $s['cap']), 'note' => $s['note']);
            }
            WP_CLI\Utils\format_items('table', $rows, array('status', 'fails', 'code', 'url', 'archive', 'choice', 'captured', 'note'));
        }
        /**
         * Ask the Internet Archive's Save Page Now to capture one page, and show its answer.
         *
         * <url>
         * : The page.
         */
        public function save($args, $assoc) {
            list($res, $detail) = bx_lk_spn($args[0]);
            WP_CLI::log($res . ': ' . $detail . (isset(bx_lk_spn_headers()['Authorization']) ? ' (with archive.org keys)' : ' (anonymous)'));
        }
    });
}
