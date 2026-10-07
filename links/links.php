<?php
/* Dead links (block 20). Every link from this site to another site is checked once a week. A link that fails three
   weekly checks in a row counts as dead, and visitors get the Internet Archive's copy of that page instead: the
   copy closest after the date the link was put on the site (the event's date, or the post's), from before it
   failed. Nothing in the database is edited: links are swapped while each page is built (block 3d), so a site that
   comes back gets its own link back after one good check, and "Keep original link" on the report undoes a swap.

   Where links are found: published pages and posts, the events table (most of the site's links are there, which is
   why no post-content link plugin covers this site), the sidebar's text and HTML widgets, and menus. Links to this
   site and to the Internet Archive are skipped.

   What counts as a failed check: no answer (address not found, refused, timed out, broken HTTPS certificate),
   404, 410, any other 4xx except the ones below, any 5xx, or a page that now sends visitors to its site's home page
   (the usual sign of a deleted article), or to a domain-parking service. 401, 403, 429, 451 and 999 mean the site
   refuses automated checks: those links are listed as "could not check" and never swapped, as are links to private
   network addresses, which are not requested at all.

   Report and settings: Tools > Link check. Command line: wp bx-links run | wp bx-links list [--status=dead]. */

if (!defined('ABSPATH')) { exit; }

define('BX_LK_OPT', 'bx_links');            // every outside link and its state (not loaded on pages)
define('BX_LK_MAP', 'bx_links_map');        // dead link => archived copy (small; read while building each page)
define('BX_LK_QUEUE', 'bx_links_queue');    // links still to check in the current run
define('BX_LK_FAILS', 3);                   // failed weekly checks in a row before a link counts as dead
define('BX_LK_CDX', 'https://web.archive.org/cdx/search/cdx');
define('BX_LK_UA', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0 Safari/537.36 (+https://bxscc.org/)');

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
    $shown = array();   // widgets placed in a sidebar (not the inactive ones)
    foreach ((array) get_option('sidebars_widgets') as $area => $ids) {
        if ($area !== 'wp_inactive_widgets' && is_array($ids)) { $shown = array_merge($shown, $ids); }
    }
    foreach (array('text' => 'text', 'custom_html' => 'content') as $base => $field) {
        foreach ((array) get_option('widget_' . $base) as $n => $w) {
            if (is_array($w) && !empty($w[$field]) && in_array($base . '-' . $n, $shown, true)) { $add($w[$field], 'w:' . $base . '-' . $n, ''); }
        }
    }
    foreach ($wpdb->get_results("SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_menu_item_url' AND meta_value LIKE 'http%'") as $mi) {
        $add('<a href="' . esc_attr($mi->meta_value) . '">', 'm:' . $mi->post_id, '');
    }
    foreach ($found as $u => $f) { $found[$u]['places'] = array_values(array_unique($f['places'])); }
    return $found;
}

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

/* the Internet Archive's copy: the first good (HTTP 200) capture from the year the link went on the site, else the
   last good one; never one from after the link started failing. '' = no copy; null = the Archive did not answer. */
function bx_lk_archive($url, $date, $failed_since) {
    $base = array('url' => $url, 'output' => 'json', 'fl' => 'timestamp,original', 'filter' => 'statuscode:200', 'to' => gmdate('YmdHis', (int) $failed_since));
    $tries = array();
    if (preg_match('/^(\d{4})/', (string) $date, $y)) { $tries[] = $base + array('from' => $y[1], 'limit' => '1'); }
    $tries[] = $base + array('limit' => '-1');
    foreach ($tries as $q) {
        $r = wp_remote_get(add_query_arg(array_map('rawurlencode', $q), apply_filters('bx_links_cdx', BX_LK_CDX)), array('timeout' => 20, 'user-agent' => BX_LK_UA));
        if (is_wp_error($r) || (int) wp_remote_retrieve_response_code($r) !== 200) { return null; }
        $body = trim(wp_remote_retrieve_body($r));
        $rows = $body === '' ? array() : json_decode($body, true);   // no captures: [] (or nothing at all)
        if (is_array($rows) && isset($rows[1][0], $rows[1][1]) && preg_match('/^\d{14}$/', $rows[1][0])) {
            return 'https://web.archive.org/web/' . $rows[1][0] . '/' . $rows[1][1];
        }
        if (!is_array($rows)) { return null; }
    }
    return '';
}

/* ---------- the weekly run, in batches small enough for one web request each ---------- */

function bx_lk_state() { $s = get_option(BX_LK_OPT); return is_array($s) ? $s : array(); }

/* start a run: refresh the list of links from the site, queue those not checked in the last six days */
function bx_lk_start() {
    $found = bx_lk_collect();
    $state = bx_lk_state();
    $now = time();
    foreach ($found as $u => $f) {
        if (!isset($state[$u])) {
            $state[$u] = array('status' => 'new', 'fails' => 0, 'code' => 0, 'note' => '', 'checked' => 0, 'since' => 0,
                               'archive' => null, 'keep' => false, 'seen' => $now);
        }
        $state[$u]['places'] = $f['places'];
        $state[$u]['date'] = $f['date'];   // '' for a sidebar or menu link: no date to start the archive search from
    }
    foreach (array_keys($state) as $u) { if (!isset($found[$u])) { unset($state[$u]); } }   // no longer on the site
    $gap = (int) apply_filters('bx_links_gap', 6 * DAY_IN_SECONDS);
    $queue = array();
    foreach ($state as $u => $s) { if ($s['checked'] <= $now - $gap) { $queue[] = $u; } }
    update_option(BX_LK_OPT, $state, false);
    update_option(BX_LK_QUEUE, $queue, false);
    update_option('bx_links_run', array('started' => $now, 'finished' => 0, 'queued' => count($queue)), false);
    $lookups = count(array_filter($state, function ($s) { return $s['status'] === 'dead' && $s['archive'] === null; }));
    return count($queue) + $lookups;   // work for this run: checks, and archive lookups still owed
}

/* check queued links until the time budget is used; returns how many are left */
function bx_lk_work($budget) {
    $stop = microtime(true) + $budget;
    $state = bx_lk_state();
    $queue = get_option(BX_LK_QUEUE);
    $queue = is_array($queue) ? $queue : array();
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
            $s['status'] = 'ok'; $s['fails'] = 0; $s['since'] = 0; $s['archive'] = null;   // if it dies again, look again
        } elseif ($result === 'blocked') {
            $s['status'] = 'blocked';   // neither a failure nor a success: the count of failures stays as it was
        } else {
            $s['fails']++;
            if ($s['fails'] === 1) { $s['since'] = $s['checked']; }
            $s['status'] = $s['fails'] >= BX_LK_FAILS ? 'dead' : 'failing';
        }
        $state[$u] = $s;
    }
    // dead links without an archived copy yet (newly dead, or the Archive did not answer on an earlier run): one
    // lookup each per run, at most three per batch, as each can take a while
    $run = get_option('bx_links_run');
    $started = is_array($run) ? (int) $run['started'] : 0;
    $due = function ($s) use ($started) { return $s['status'] === 'dead' && $s['archive'] === null && (int) ($s['looked'] ?? 0) < $started; };
    $lookups = 0;
    foreach ($state as $u => $s) {
        if ($lookups >= 3 || (microtime(true) >= $stop && ($queue || $lookups))) { break; }   // always at least one once the checks are done
        if ($due($s)) {
            $lookups++;
            $state[$u]['looked'] = time();
            $state[$u]['archive'] = bx_lk_archive($u, $s['date'], $s['since']);
            usleep(1500000);   // the Archive asks for no more than about one lookup a second
        }
    }
    $pending = count(array_filter($state, $due));
    update_option(BX_LK_OPT, $state, false);
    update_option(BX_LK_QUEUE, $queue, false);
    if (!$queue && !$pending) { bx_lk_finish($state); }
    return count($queue) + $pending;
}

/* end of a run: rebuild the swap list; if it changed, empty the page cache so pages are built with it */
function bx_lk_finish($state) {
    $map = array();
    foreach ($state as $u => $s) {
        if ($s['status'] === 'dead' && empty($s['keep']) && is_string($s['archive']) && $s['archive'] !== '') { $map[$u] = $s['archive']; }
    }
    ksort($map);
    $old = get_option(BX_LK_MAP, array());
    if ($map !== (is_array($old) ? $old : array())) {
        update_option(BX_LK_MAP, $map, true);
        if (function_exists('bx_purge_all_pages_soon')) { bx_purge_all_pages_soon(); }
    }
    $run = get_option('bx_links_run');
    $run = is_array($run) ? $run : array();
    $run['finished'] = time();
    update_option('bx_links_run', $run, false);
}

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
    add_management_page('Link check', 'Link check', 'manage_options', 'bx-links', 'bx_lk_screen');
});

add_action('admin_post_bx_links_keep', function () {
    if (!current_user_can('manage_options')) { wp_die('Not allowed.'); }
    check_admin_referer('bx_links_keep');
    $u = isset($_POST['url']) ? (string) wp_unslash($_POST['url']) : '';
    $state = bx_lk_state();
    if (isset($state[$u])) {
        $state[$u]['keep'] = !empty($_POST['keep']);
        update_option(BX_LK_OPT, $state, false);
        bx_lk_finish($state);
    }
    wp_safe_redirect(admin_url('tools.php?page=bx-links&done=keep'));
    exit;
});
add_action('admin_post_bx_links_run', function () {
    if (!current_user_can('manage_options')) { wp_die('Not allowed.'); }
    check_admin_referer('bx_links_run');
    $n = bx_lk_start();
    if ($n) { wp_schedule_single_event(time(), 'bx_links_batch'); }
    wp_safe_redirect(admin_url('tools.php?page=bx-links&done=run&n=' . $n));
    exit;
});

function bx_lk_screen() {
    if (!current_user_can('manage_options')) { return; }
    $state = bx_lk_state();
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
    ?>
    <div class="wrap">
      <h1>Link check</h1>
      <?php if (isset($_GET['done']) && $_GET['done'] === 'run') { echo '<div class="notice notice-success"><p>Started: ' . (int) ($_GET['n'] ?? 0) . ' links queued (links checked in the last six days wait for next week). The check runs in the background; reload this page in a few minutes.</p></div>'; } ?>
      <?php if (isset($_GET['done']) && $_GET['done'] === 'keep') { echo '<div class="notice notice-success"><p>Saved. Pages are being rebuilt with the change.</p></div>'; } ?>
      <p>Every link from this site to another site is checked once a week. A link that fails <?php echo (int) BX_LK_FAILS; ?> weekly checks in a row counts as dead, and visitors get the Internet Archive's copy of the page instead. Pages and events are not edited: the link is swapped as each page is built, and a site that comes back gets its own link back after one good check.</p>
      <p>
        <?php echo count($state); ?> outside links.
        Last run: started <?php echo esc_html($when(is_array($run) ? ($run['started'] ?? 0) : 0)); ?>,
        <?php echo is_array($run) && !empty($run['finished']) && $run['finished'] >= $run['started'] ? 'finished ' . esc_html($when($run['finished'])) : 'still running (' . (is_array($queue) ? count($queue) : 0) . ' links to go)'; ?>.
        Next run: <?php echo esc_html($when(wp_next_scheduled('bx_links_weekly'))); ?>.
      </p>
      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="bx_links_run"><?php wp_nonce_field('bx_links_run'); ?>
        <?php submit_button('Run the check now', 'secondary', 'submit', false); ?>
      </form>

      <h2>Dead (<?php echo count($by['dead']); ?>)</h2>
      <table class="widefat striped">
        <thead><tr><th style="width: 30%;">Link</th><th>Where</th><th>Failing since</th><th>Last result</th><th style="width: 25%;">Visitors get</th></tr></thead>
        <tbody>
        <?php if (!$by['dead']) { echo '<tr><td colspan="5">None.</td></tr>'; } ?>
        <?php foreach ($by['dead'] as $u => $s) {
            $has = is_string($s['archive']) && $s['archive'] !== '';
            if (!empty($s['keep'])) { $gets = 'the original link (kept by hand)'; }
            elseif ($has) { $gets = 'the archived copy: ' . $link($s['archive']); }
            elseif ($s['archive'] === '') { $gets = 'the original link: the Internet Archive has no copy of this page'; }
            else { $gets = 'the original link, until the Internet Archive answers (asked again on the next run)'; }
            $btn = '';
            if ($has) {
                $btn = '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="margin-top: 6px;">'
                     . '<input type="hidden" name="action" value="bx_links_keep"><input type="hidden" name="url" value="' . esc_attr($u) . '">'
                     . (empty($s['keep']) ? '<input type="hidden" name="keep" value="1">' : '')
                     . wp_nonce_field('bx_links_keep', '_wpnonce', true, false)
                     . get_submit_button(empty($s['keep']) ? 'Keep original link' : 'Use the archived copy', 'small', 'submit', false) . '</form>';
            }
            echo '<tr><td>' . $link($u) . '</td><td>' . $places($s) . '</td><td>' . esc_html($when($s['since'])) . '</td><td>' . $result($s) . '</td><td>' . $gets . $btn . '</td></tr>';
        } ?>
        </tbody>
      </table>

      <h2>Failing, not dead yet (<?php echo count($by['failing']); ?>)</h2>
      <p>Failed the last weekly check, but not <?php echo (int) BX_LK_FAILS; ?> in a row yet. Nothing is swapped.</p>
      <table class="widefat striped">
        <thead><tr><th style="width: 30%;">Link</th><th>Where</th><th>Failed checks in a row</th><th>Last result</th></tr></thead>
        <tbody>
        <?php if (!$by['failing']) { echo '<tr><td colspan="4">None.</td></tr>'; } ?>
        <?php foreach ($by['failing'] as $u => $s) { echo '<tr><td>' . $link($u) . '</td><td>' . $places($s) . '</td><td>' . (int) $s['fails'] . '</td><td>' . $result($s) . '</td></tr>'; } ?>
        </tbody>
      </table>

      <h2>Could not check (<?php echo count($by['blocked']); ?>)</h2>
      <p>These sites refuse automated checks, so whether the page still exists is unknown. They are never swapped; open them to see.</p>
      <table class="widefat striped">
        <thead><tr><th style="width: 30%;">Link</th><th>Where</th><th>Last result</th></tr></thead>
        <tbody>
        <?php if (!$by['blocked']) { echo '<tr><td colspan="3">None.</td></tr>'; } ?>
        <?php foreach ($by['blocked'] as $u => $s) { echo '<tr><td>' . $link($u) . '</td><td>' . $places($s) . '</td><td>' . $result($s) . '</td></tr>'; } ?>
        </tbody>
      </table>

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
            WP_CLI::log((is_array($q) ? count($q) : 0) . ' links to check.');
            while (($left = bx_lk_work(30)) > 0) { WP_CLI::log($left . ' to go.'); }
            $state = bx_lk_state();
            $count = array();
            foreach ($state as $s) { $count[$s['status']] = ($count[$s['status']] ?? 0) + 1; }
            ksort($count);
            foreach ($count as $k => $v) { WP_CLI::log(sprintf('%-8s %d', $k, $v)); }
            $map = get_option(BX_LK_MAP);
            WP_CLI::success((is_array($map) ? count($map) : 0) . ' dead links are swapped for archived copies.');
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
                                'archive' => $s['archive'] === null ? '' : ($s['archive'] === '' ? '(none)' : $s['archive']), 'keep' => empty($s['keep']) ? '' : 'yes', 'note' => $s['note']);
            }
            WP_CLI\Utils\format_items('table', $rows, array('status', 'fails', 'code', 'url', 'archive', 'keep', 'note'));
        }
    });
}
