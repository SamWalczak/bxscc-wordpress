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
   later disappears. Save Page Now takes these requests only with an archive.org account's keys (from
   https://archive.org/account/s3.php), entered on Tools > Link check or set in wp-config.php (see bx_lk_ia_keys);
   without keys, pages are not sent and everything else works.

   Where links are found: published pages and posts, the events table (most of the site's links are there, which is
   why no post-content link plugin covers this site), the text and HTML widgets placed in a sidebar, and menus.
   Links to this site and to the Internet Archive are skipped.

   What counts as a failed check: no answer (address not found, refused, timed out, broken HTTPS certificate),
   404, 410, any other 4xx except the ones below, any 5xx, or a page that now sends visitors to its site's home page
   (the usual sign of a deleted article), or to a domain-parking service. 401, 403, 429, 451 and 999 mean the site
   refuses automated checks: those links are listed as "could not check" and never called dead, as are links to
   private network addresses (or that redirect to one), which are not requested (see bx_lk_vet).

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

/* ---------- never ask the server's own network ----------
   A link, or any address a link redirects to, that points into a private, loopback, link-local, carrier or otherwise
   reserved network is not requested: the link is listed as "could not check". Every request is vetted just before it
   is sent (the first one and each redirect), against every IPv4 and IPv6 address its host name has, and the connection
   then goes to exactly the addresses that were vetted (cURL's own lookup is skipped), so a name that answers
   differently a moment later changes nothing. A host written as a number in an unusual form (0x7f000001, 2130706433,
   0177.0.0.1) is refused outright: no real site is addressed that way. Only the weekly check pays for this: one more
   DNS lookup (IPv6) per request. */
function bx_lk_public_ip($ip) {
    if (strpos($ip, ':') !== false) {
        $b = @inet_pton($ip);
        if ($b === false || strlen($b) !== 16) { return false; }
        // IPv6 forms that carry an IPv4 address: mapped ::ffff:a.b.c.d, compatible ::a.b.c.d, NAT64 64:ff9b::/96, 6to4 2002::/16
        if (substr($b, 0, 12) === str_repeat("\0", 10) . "\xff\xff" || substr($b, 0, 12) === str_repeat("\0", 12) || substr($b, 0, 12) === "\x00\x64\xff\x9b" . str_repeat("\0", 8)) {
            return bx_lk_public_ip(inet_ntop(substr($b, 12)));
        }
        if (substr($b, 0, 2) === "\x20\x02") { return bx_lk_public_ip(inet_ntop(substr($b, 2, 4))); }
        if (substr($b, 0, 8) === str_repeat("\0", 8) && substr($b, 8, 4) === "\xff\xff\0\0") { return bx_lk_public_ip(inet_ntop(substr($b, 12))); }   // IPv4-translated ::ffff:0:a.b.c.d
        if (substr($b, 0, 6) === "\x00\x64\xff\x9b\x00\x01") { return false; }   // local-use NAT64 64:ff9b:1::/48 (the IPv4 address is not at a fixed place)
        $b0 = ord($b[0]); $b1 = ord($b[1]);
        if (($b0 & 0xfe) === 0xfc || ($b0 === 0xfe && ($b1 & 0x80) === 0x80) || $b0 === 0xff) { return false; }   // unique local, link-local and old site-local (fe80::/10, fec0::/10), multicast
        return (bool) filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) { return false; }
    $n = ip2long($ip);
    // ranges PHP's filter lets through: carrier NAT, IETF protocol assignments, benchmarking, multicast
    foreach (array(array('100.64.0.0', 10), array('192.0.0.0', 24), array('198.18.0.0', 15), array('224.0.0.0', 4)) as $r) {
        $mask = (-1 << (32 - $r[1])) & 0xffffffff;
        if (($n & $mask) === (ip2long($r[0]) & $mask)) { return false; }
    }
    return true;
}
/* array('ok', CURLOPT_RESOLVE entries, or none for a literal address) | array('blocked') | array('unresolved', host) */
function bx_lk_vet($url) {
    $p = wp_parse_url($url);
    $given = (string) ($p['host'] ?? '');
    $host = strtolower(rtrim($given, '.'));
    if ($host === '') { return array('blocked'); }
    $port = isset($p['port']) ? (int) $p['port'] : (strtolower((string) ($p['scheme'] ?? '')) === 'https' ? 443 : 80);
    if ($host[0] === '[') { return bx_lk_public_ip(trim($host, '[]')) ? array('ok', array()) : array('blocked'); }
    if (preg_match('/^(0x[0-9a-f]*|\d+)(\.(0x[0-9a-f]*|\d+)){0,3}$/i', $host)) {   // a number: only the plain dotted form is accepted
        $plain = preg_match('/^(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}$/', $host);
        return $plain && bx_lk_public_ip($host) ? array('ok', array()) : array('blocked');
    }
    $name = $host;
    if (preg_match('/[^\x21-\x7e]/', $name) && function_exists('idn_to_ascii')) { $name = (string) idn_to_ascii($name, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46) ?: $name; }
    $v4 = @gethostbynamel($name);
    $v4 = is_array($v4) ? $v4 : array();
    $v6 = array();
    if (function_exists('dns_get_record')) { foreach ((array) @dns_get_record($name, DNS_AAAA) as $rec) { if (!empty($rec['ipv6'])) { $v6[] = $rec['ipv6']; } } }
    if (!$v4 && !$v6) { return array('unresolved', $host); }
    foreach (array_merge($v4, $v6) as $ip) { if (!bx_lk_public_ip($ip)) { return array('blocked'); } }
    $to = implode(',', $v4 ? $v4 : array_map(function ($a) { return '[' . $a . ']'; }, $v6));
    // cURL looks the name up as the address spells it ("example.org." with its final dot is another entry to cURL),
    // so the vetted addresses are given for every spelling
    $entries = array();
    foreach (array_unique(array($given, $host, $host . '.', $name, $name . '.')) as $h) { if ($h !== '' && $h !== '.') { $entries[] = $h . ':' . $port . ':' . $to; } }
    return array('ok', $entries);
}

/* one check: 'ok', 'fail' or 'blocked', with the status code (0: no answer) and a note */
function bx_lk_check($url) {
    $pin = array();
    $vet = function ($u) use (&$pin) {   // each request, the first and every redirect, just before it is sent
        $v = bx_lk_vet((string) $u);
        if ($v[0] === 'blocked') { throw new \WpOrg\Requests\Exception('bx-private', 'bx-private'); }
        if ($v[0] === 'unresolved') { throw new \WpOrg\Requests\Exception('cURL error 6: Could not resolve host: ' . $v[1], 'curlerror'); }
        $pin = $v[1];
    };
    $connect = function ($handle) use (&$pin) { if ($pin) { curl_setopt($handle, CURLOPT_RESOLVE, $pin); } };
    add_action('requests-requests.before_request', $vet, 10, 1);
    add_action('http_api_curl', $connect, 10, 1);
    try {
        $r = wp_remote_get($url, array(
            'timeout' => 12, 'redirection' => 5, 'user-agent' => BX_LK_UA, 'limit_response_size' => 65536,
            'headers' => array('Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', 'Accept-Language' => 'en-US,en;q=0.9'),
        ));
    } finally {
        remove_action('requests-requests.before_request', $vet, 10);
        remove_action('http_api_curl', $connect, 10);
        // a refusal leaves the hook WordPress was running on its list of current hooks (the exception skipped the
        // tidy-up), so it is taken off here
        global $wp_current_filter;
        while ($wp_current_filter && end($wp_current_filter) === 'requests-requests.before_request') { array_pop($wp_current_filter); }
    }
    if (is_wp_error($r) && $r->get_error_message() === 'bx-private') { return array('blocked', 0, 'not checked: a private network address'); }
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

/* ---------- archive.org keys (optional; Save Page Now accepts this site's requests only with them) ----------
   Two places, the first one found is used:
   - wp-config.php: define('BX_IA_ACCESS_KEY', '...'); define('BX_IA_SECRET_KEY', '...');
   - Tools > Link check, for administrators, so that someone without a terminal can set them. Stored in the option
     bx_links_ia_keys, encrypted (libsodium secretbox) with a key derived from this site's secret keys in wp-config.php
     (wp_salt), so a copy of the database alone, such as a backup, does not reveal them. If those secret keys are ever
     changed, the stored keys can no longer be read, and the screen asks for them again. The secret key is never
     shown on the screen after it is saved. */
define('BX_LK_KEYS', 'bx_links_ia_keys');
function bx_lk_box_key() {
    return sodium_crypto_generichash('bx-links-ia-keys|' . wp_salt('secure_auth'), '', SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
}
function bx_lk_seal(#[\SensitiveParameter] $plain) {
    $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
    return base64_encode($nonce . sodium_crypto_secretbox($plain, $nonce, bx_lk_box_key()));
}
function bx_lk_unseal($sealed) {
    $raw = base64_decode((string) $sealed, true);
    $n = SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;
    if ($raw === false || strlen($raw) <= $n) { return null; }
    try { $plain = sodium_crypto_secretbox_open(substr($raw, $n), substr($raw, 0, $n), bx_lk_box_key()); } catch (\Throwable $e) { return null; }
    return $plain === false ? null : $plain;
}
/* the keys in use: array('access', 'secret', 'from' => 'config' | 'dashboard'), or null for none */
function bx_lk_ia_keys() {
    if (defined('BX_IA_ACCESS_KEY') && defined('BX_IA_SECRET_KEY') && BX_IA_ACCESS_KEY && BX_IA_SECRET_KEY) {
        return array('access' => (string) BX_IA_ACCESS_KEY, 'secret' => (string) BX_IA_SECRET_KEY, 'from' => 'config');
    }
    $o = get_option(BX_LK_KEYS);
    if (!is_array($o) || empty($o['sealed'])) { return null; }
    $k = json_decode((string) bx_lk_unseal($o['sealed']), true);
    return is_array($k) && !empty($k['a']) && !empty($k['s']) ? array('access' => (string) $k['a'], 'secret' => (string) $k['s'], 'from' => 'dashboard') : null;
}
function bx_lk_spn_headers(#[\SensitiveParameter] $keys = null) {
    $h = array('Accept' => 'application/json');
    $k = $keys ? $keys : bx_lk_ia_keys();
    if ($k) { $h['Authorization'] = 'LOW ' . $k['access'] . ':' . $k['secret']; }
    return $h;
}
/* ask Save Page Now for a capture: array(result, detail) with result
   'queued' (detail: job id), 'refused' (the Archive will not save this page; detail: why),
   'busy' (too many requests now; try later), 'keys' (the Archive wants archive.org keys), 'error' (no usable answer).
   $keys: keys to try instead of the saved ones (the key form tests new keys before saving them).
   Requests that carry the keys never follow a redirect, so the keys cannot be sent on to another address. */
function bx_lk_spn($url, #[\SensitiveParameter] $keys = null) {
    $r = wp_remote_post(apply_filters('bx_links_spn', BX_LK_SPN), array(
        'timeout' => 30, 'redirection' => 0, 'user-agent' => BX_LK_UA, 'headers' => bx_lk_spn_headers($keys),
        'body' => array('url' => $url, 'skip_first_archive' => '1', 'if_not_archived_within' => '30d'),
    ));
    if (is_wp_error($r)) { return array('error', $r->get_error_message()); }
    $code = (int) wp_remote_retrieve_response_code($r);
    $j = json_decode(wp_remote_retrieve_body($r), true);
    if ($code === 429) { return array('busy', 'too many requests'); }
    if ($code >= 300 && $code < 400) { return array('error', 'HTTP ' . $code . ', a redirect (not followed)'); }
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
    $r = wp_remote_get(trailingslashit(apply_filters('bx_links_spn', BX_LK_SPN)) . 'status/' . rawurlencode($job), array('timeout' => 20, 'redirection' => 0, 'user-agent' => BX_LK_UA, 'headers' => bx_lk_spn_headers()));
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
                $run['spn_stop'] = $res === 'keys' ? (bx_lk_ia_keys() ? 'the Internet Archive did not accept this site\'s archive.org keys' : 'the Internet Archive asked for archive.org keys') : ($res === 'busy' ? 'the Internet Archive was busy: ' . $detail : 'no usable answer from the Internet Archive: ' . $detail);
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
        static $titles = null;   // every event's title in one query, for the whole report
        if ($titles === null) { global $wpdb; $titles = function_exists('bx_ev_table') ? $wpdb->get_results('SELECT id, title FROM ' . bx_ev_table(), OBJECT_K) : array(); }
        $t = isset($titles[(int) $id]) ? $titles[(int) $id]->title : '';
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

/* one text field from a form or the address; '' for anything else (an array sent in its place, say) */
function bx_lk_in($from, $key) {
    return isset($from[$key]) && is_string($from[$key]) ? wp_unslash($from[$key]) : '';
}
function bx_lk_back($args) {
    wp_safe_redirect(add_query_arg($args, admin_url('tools.php?page=bx-links')));
    exit;
}
add_action('admin_post_bx_links_choice', function () {
    if (!current_user_can('manage_options')) { wp_die('Not allowed.'); }
    check_admin_referer('bx_links_choice');
    $u = bx_lk_in($_POST, 'url');
    $c = bx_lk_in($_POST, 'choice');
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
    $old = bx_lk_in($_POST, 'url');
    $new = trim(bx_lk_in($_POST, 'new'));
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
    $mode = bx_lk_in($_POST, 'mode') === 'auto' ? 'auto' : 'ask';
    $email = sanitize_email(bx_lk_in($_POST, 'email'));
    update_option('bx_links_settings', array('mode' => $mode, 'email' => is_email($email) ? $email : ''), false);
    bx_lk_apply(bx_lk_state());
    bx_lk_back(array('done' => 'settings'));
});
/* archive.org keys from the form: tested with the Archive (the home page is sent to Save Page Now) before they are
   saved; keys the Archive refuses are not saved. A run that stopped saving pages for want of keys carries on. */
add_action('admin_post_bx_links_keys', function () {
    if (!current_user_can('manage_options')) { wp_die('Not allowed.'); }
    check_admin_referer('bx_links_keys');
    if (!empty($_POST['remove'])) {
        delete_option(BX_LK_KEYS);
        bx_lk_back(array('done' => 'keys_removed'));
    }
    $a = preg_replace('/\s+/', '', bx_lk_in($_POST, 'access'));
    $s = preg_replace('/\s+/', '', bx_lk_in($_POST, 'secret'));
    if (!preg_match('/^[A-Za-z0-9]{8,64}$/', $a) || !preg_match('/^[A-Za-z0-9]{8,64}$/', $s) || $a === $s) { bx_lk_back(array('done' => 'keys_format')); }
    list($res, $detail) = bx_lk_spn(home_url('/'), array('access' => $a, 'secret' => $s));
    if ($res === 'keys') { bx_lk_back(array('done' => 'keys_rejected')); }
    $ok = in_array($res, array('queued', 'refused'), true);   // refused: the Archive read the keys and declined the page itself
    update_option(BX_LK_KEYS, array(
        'sealed' => bx_lk_seal(wp_json_encode(array('a' => $a, 's' => $s))),
        'end' => substr($a, -4), 'by' => wp_get_current_user()->user_login, 'at' => time(),
        'test' => $ok ? 'accepted' : 'untested', 'detail' => $ok ? '' : substr((string) $detail, 0, 200),
    ), false);
    $run = get_option('bx_links_run');
    if (is_array($run) && !empty($run['spn_stop']) && strpos($run['spn_stop'], 'keys') !== false) {
        $run['spn_stop'] = '';
        update_option('bx_links_run', $run, false);
        wp_schedule_single_event(time(), 'bx_links_batch');
    }
    bx_lk_back(array('done' => $ok ? 'keys_saved' : 'keys_untested'));
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
    $ids = array();   // the pages and posts the report names, loaded in one query
    foreach (array('dead', 'failing', 'blocked') as $k) { foreach ($by[$k] as $s) { foreach ((array) $s['places'] as $pl) { if (strpos($pl, 'p:') === 0) { $ids[] = (int) substr($pl, 2); } } } }
    if ($ids) { _prime_post_caches(array_unique($ids), false, false); }
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
        'keys_saved' => 'Keys saved. The Internet Archive accepted them (as the test, it was asked to save the home page). Pages waiting to be saved are sent in the background now.',
        'keys_untested' => 'Keys saved, but the Internet Archive could not be reached to test them just now. They are tried on the next run; this page shows the result.',
        'keys_rejected' => 'Not saved: the Internet Archive did not accept these keys. Copy both again from https://archive.org/account/s3.php (the access key and the secret key are two different codes).',
        'keys_format' => 'Not saved: each key is one run of letters and digits, and the two keys are different. Check that both boxes are filled and nothing else was copied with them.',
        'keys_removed' => 'Keys removed. Pages are no longer sent to the Internet Archive to be saved; everything else works as before.',
    );
    $errors = array('badnew', 'keys_rejected', 'keys_format');
    $done = bx_lk_in($_GET, 'done');
    $keys = bx_lk_ia_keys();
    $kopt = get_option(BX_LK_KEYS);
    $spn_mode = $keys ? 'with archive.org keys' : 'without archive.org keys, which the Internet Archive requires (see "Internet Archive keys" above)';
    $live = array_filter($state, function ($s) { return in_array($s['status'], array('ok', 'blocked'), true); });
    $never = array_filter($live, function ($s) { return $s['cap'] === ''; });
    $asked = array_filter($never, function ($s) { return $s['spn'] !== ''; });
    $refused = array_filter($never, function ($s) { return $s['spn'] === '' && $s['spn_note'] !== ''; });
    $unknown = array_filter($live, function ($s) { return $s['cap'] === null; });
    ?>
    <div class="wrap">
      <h1>Link check</h1>
      <?php if (isset($notes[$done])) { echo '<div class="notice ' . (in_array($done, $errors, true) ? 'notice-error' : 'notice-success') . '"><p>' . esc_html($notes[$done]) . '</p></div>'; } ?>
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

      <div style="background: #fff; border: 1px solid #c3c4c7; padding: 4px 16px 12px; max-width: 760px; margin-top: 16px;">
        <h2 style="margin-bottom: 6px;">Internet Archive keys (optional)</h2>
        <p>When a working link has never been saved by the Internet Archive, this site asks the Archive to save it, so that a copy exists if the page disappears later. The Archive takes these requests only with the keys of an archive.org account. Without keys, everything else still works: dead links are found, you are warned, and the Archive's existing copies are offered.</p>
        <?php
        if ($keys && $keys['from'] === 'config') {
            echo '<p><strong>Keys are set in wp-config.php</strong> (access key ending in ' . esc_html(substr($keys['access'], -4)) . '). They are used instead of this form. To manage the keys here, someone with access to the server removes the two BX_IA_ lines from wp-config.php.</p>';
        } else {
            if ($keys) {
                echo '<p><strong>Keys saved</strong> ' . esc_html($when($kopt['at'] ?? 0)) . ' by ' . esc_html($kopt['by'] ?? '?') . ' (access key ending in ' . esc_html(substr($keys['access'], -4)) . '). '
                   . (($kopt['test'] ?? '') === 'accepted' ? 'The Internet Archive accepted them when they were saved.' : 'They could not be tested when they were saved' . (!empty($kopt['detail']) ? ' (' . esc_html($kopt['detail']) . ')' : '') . '.') . '</p>';
            } elseif (is_array($kopt) && !empty($kopt['sealed'])) {
                echo '<p><strong>Keys were saved ' . esc_html($when($kopt['at'] ?? 0)) . ' but can no longer be read</strong>, because this site\'s own secret keys in wp-config.php have changed since. Enter them again below.</p>';
            } else {
                echo '<p><strong>No keys saved.</strong></p>';
            }
            ?>
            <ol style="margin-left: 1.6em;">
              <li>Sign in at <a href="https://archive.org/account/login" target="_blank" rel="noopener">archive.org</a> with an account the committee keeps, for example one under the committee's shared email, so that whoever looks after the site later can sign in to it too (a new account is free: <a href="https://archive.org/account/signup" target="_blank" rel="noopener">sign up</a>).</li>
              <li>Open <a href="https://archive.org/account/s3.php" target="_blank" rel="noopener">archive.org/account/s3.php</a>. It shows two codes: an access key and a secret key.</li>
              <li>Copy each one into its box below and click <strong><?php echo $keys ? 'Replace keys' : 'Save keys'; ?></strong>. The site tries them with the Archive straight away and tells you whether they work.</li>
            </ol>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" autocomplete="off">
              <input type="hidden" name="action" value="bx_links_keys"><?php wp_nonce_field('bx_links_keys'); ?>
              <label style="display: block; margin: 6px 0;">Access key: <input type="text" name="access" class="regular-text" autocomplete="off" spellcheck="false" required></label>
              <label style="display: block; margin: 6px 0;">Secret key: <input type="password" name="secret" class="regular-text" autocomplete="new-password" spellcheck="false" required></label>
              <?php submit_button($keys ? 'Replace keys' : 'Save keys', 'primary', 'submit', false); ?>
            </form>
            <?php if (is_array($kopt) && !empty($kopt['sealed'])) { ?>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top: 8px;" onsubmit="return confirm('Remove the archive.org keys? Pages will no longer be sent to the Internet Archive to be saved.');">
              <input type="hidden" name="action" value="bx_links_keys"><input type="hidden" name="remove" value="1"><?php wp_nonce_field('bx_links_keys'); ?>
              <?php submit_button('Remove keys', 'small', 'submit', false); ?>
            </form>
            <?php } ?>
            <p class="description">The keys are stored encrypted, and the secret key is never shown again after it is saved. Only administrators can see this form.</p>
        <?php } ?>
      </div>

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
