<?php
/*
 * Which http:// links on the site have a working https:// version (October 2026). Read-only: nothing on the site
 * changes.
 * Finds every outside link that starts with http:// in published pages and posts, events, sidebar widgets and menus
 * (the same places the weekly link check reads), then asks each one from the server, both as written and with https://,
 * one request a second per site, with the link checker's protections (no private addresses, 12 s timeout).
 * Sorts them into:
 *   1. Switch to https: the https:// address answers (sites that send http:// visitors to https:// themselves included).
 *   2. Keep http: only the http:// address answers.
 *   3. Check in your browser: the server got no usable answer for either (no reply, an error, or a site that refuses
 *      automated requests). Both addresses are listed to click.
 * From public_html (needs BXSCC WordPress Enhancements 1.60 or later active, for its link functions):
 *   wp eval-file ~/https-check.php --skip-themes --skip-plugins=wordfence,updraftplus,shortcodes-ultimate
 * Prints the three lists and writes the same as a page with clickable links to ~/https-check.html (open it from
 * cPanel File Manager: select it, then View, or Download and open it in a browser). Takes a few minutes.
 */
if (!defined('ABSPATH')) { exit; }
if (!function_exists('bx_lk_collect') || !function_exists('bx_lk_check') || !function_exists('bx_lk_vet')) {
    WP_CLI::error('The BXSCC plugin is not loaded or is older than 1.60. Run this without skipping that plugin.');
}

$links = array();
foreach (bx_lk_collect() as $u => $f) {
    if (stripos($u, 'http://') === 0) { $links[$u] = $f['places']; }
}
ksort($links);
WP_CLI::log(count($links) . ' outside links start with http://. Checking each twice, one request a second per site...');

/* where an http:// address sends visitors (one step, no following), or '' */
function bx_hc_redirect($url) {
    $v = bx_lk_vet($url);
    if ($v[0] !== 'ok') { return ''; }
    $r = wp_remote_head($url, array('timeout' => 12, 'redirection' => 0, 'user-agent' => BX_LK_UA));
    if (is_wp_error($r)) { return ''; }
    $code = (int) wp_remote_retrieve_response_code($r);
    $loc = (string) wp_remote_retrieve_header($r, 'location');
    return ($code >= 300 && $code < 400 && $loc !== '') ? WP_Http::make_absolute_url($loc, $url) : '';
}
function bx_hc_place($place) {
    list($kind, $id) = explode(':', $place, 2);
    if ($kind === 'p') { return array(get_the_title((int) $id), get_permalink((int) $id)); }
    if ($kind === 'e') {
        global $wpdb;
        $t = function_exists('bx_ev_table') ? $wpdb->get_var($wpdb->prepare('SELECT title FROM ' . bx_ev_table() . ' WHERE id = %d', (int) $id)) : '';
        return array('Event: ' . wp_strip_all_tags((string) $t), admin_url('admin.php?page=bx-event-edit&id=' . (int) $id));
    }
    if ($kind === 'w') { return array('Sidebar widget ' . $id, admin_url('widgets.php')); }
    return array('Menu item ' . $id, admin_url('nav-menus.php'));
}

$last = array();   // host => time of the last request
$pace = function ($url) use (&$last) {
    $h = strtolower((string) wp_parse_url($url, PHP_URL_HOST));
    if (isset($last[$h])) { $wait = 1.0 - (microtime(true) - $last[$h]); if ($wait > 0) { usleep((int) ($wait * 1e6)); } }
    $last[$h] = microtime(true);
};
$groups = array('switch' => array(), 'keep' => array(), 'check' => array());
$i = 0;
foreach ($links as $http => $places) {
    $i++;
    $https = 'https://' . substr($http, 7);
    $pace($http); $redir = bx_hc_redirect($http);
    $pace($http); $a = bx_lk_check($http);
    $pace($https); $b = bx_lk_check($https);
    $to_https = $redir !== '' && stripos($redir, 'https://') === 0;
    if ($b[0] === 'ok') { $g = 'switch'; }
    elseif ($a[0] === 'ok') { $g = 'keep'; }
    else { $g = 'check'; }
    $note = function ($r) { return $r[0] . ($r[1] ? ' ' . $r[1] : '') . ($r[2] !== '' ? ' (' . $r[2] . ')' : ''); };
    $groups[$g][] = array('http' => $http, 'https' => $https, 'a' => $note($a), 'b' => $note($b), 'redir' => $to_https ? $redir : '', 'places' => $places);
    WP_CLI::log(sprintf('%3d/%d %-6s %s', $i, count($links), $g, $http));
}

$titles = array('switch' => '1. Switch to https (the https:// address works)', 'keep' => '2. Keep http (https:// does not answer, http:// does)',
                'check' => '3. Check in your browser (the server got no usable answer for either)');
WP_CLI::log('');
foreach ($groups as $g => $rows) {
    WP_CLI::log($titles[$g] . ': ' . count($rows));
    foreach ($rows as $r) {
        WP_CLI::log('   ' . ($g === 'keep' ? $r['http'] : $r['https']) . ($g === 'check' ? '    and    ' . $r['http'] : ''));
    }
    WP_CLI::log('');
}

$e = 'esc_html';
$out = '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>https check, bxscc.org</title>'
     . '<style>body{font:15px/1.5 system-ui,sans-serif;margin:24px;max-width:1100px}table{border-collapse:collapse;width:100%;margin:8px 0 28px}'
     . 'td,th{border:1px solid #ccc;padding:6px 8px;text-align:left;vertical-align:top;font-size:14px}th{background:#f2f2f2}a{word-break:break-all}small{color:#666}</style>'
     . '<h1>http:// links on bxscc.org</h1><p>Checked ' . $e(wp_date('F j, Y, g:i a')) . ' from the server. ' . count($links) . ' links.</p>';
foreach ($groups as $g => $rows) {
    $out .= '<h2>' . $e($titles[$g]) . ': ' . count($rows) . '</h2>';
    if (!$rows) { continue; }
    $out .= '<table><tr><th>Link as written</th><th>https:// version</th><th>Server\'s answer (http / https)</th><th>Used on</th></tr>';
    foreach ($rows as $r) {
        $used = array();
        foreach ($r['places'] as $p) { list($t, $u) = bx_hc_place($p); $used[] = '<a href="' . esc_url($u) . '">' . $e($t) . '</a>'; }
        $out .= '<tr><td><a href="' . esc_url($r['http']) . '" target="_blank" rel="noopener noreferrer">' . $e($r['http']) . '</a></td>'
              . '<td><a href="' . esc_url($r['https']) . '" target="_blank" rel="noopener noreferrer">' . $e($r['https']) . '</a></td>'
              . '<td>' . $e($r['a']) . '<br>' . $e($r['b']) . ($r['redir'] ? '<br><small>http:// sends visitors to ' . $e($r['redir']) . '</small>' : '') . '</td>'
              . '<td>' . implode('<br>', $used) . '</td></tr>';
    }
    $out .= '</table>';
}
$file = rtrim((string) getenv('HOME'), '/') . '/https-check.html';
file_put_contents($file, $out);
@chmod($file, 0600);
WP_CLI::success(sprintf('%d to switch, %d to keep, %d to check by hand. Page with clickable links: %s', count($groups['switch']), count($groups['keep']), count($groups['check']), $file));
