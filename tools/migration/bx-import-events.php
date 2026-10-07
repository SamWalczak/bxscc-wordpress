<?php
/*
 * One-time import of the existing News and Media entries into the BXSCC Events table.
 * Needs bxscc-wordpress-enhancements 1.45 or later (the events code is its events folder). Upload bx-events.json and this file to your home folder, then from public_html:
 *   wp eval-file ~/bx-import-events.php dry --skip-themes --skip-plugins    (lists what it would add; changes nothing)
 *   wp eval-file ~/bx-import-events.php run --skip-themes --skip-plugins    (adds them)
 * Safe to run twice: an event with the same title and first day is skipped.
 */
if (!defined('ABSPATH')) { exit; }
require_once WP_PLUGIN_DIR . '/bxscc-wordpress-enhancements/events/events.php';   // loaded by hand because of --skip-plugins
bx_ev_install();                                                // creates the table if no page has loaded since the upload

$mode = isset($args[0]) ? $args[0] : 'dry';
$file = dirname(ABSPATH) . '/bx-events.json';
if (!file_exists($file)) { WP_CLI::error("Missing $file"); }
$data = json_decode(file_get_contents($file), true);
if (!is_array($data) || !isset($data['events'])) { WP_CLI::error('Could not read the JSON file.'); }

global $wpdb;
$t = bx_ev_table();
$ids = array(); $made = 0; $skipped = 0;
foreach ($data['events'] as $e) {
    list($parts, $lo) = bx_ev_parts_bounds($e['dates']);
    $have = $wpdb->get_var($wpdb->prepare("SELECT id FROM $t WHERE title = %s AND start_date = %s", $e['title'], $lo));
    if ($have) { $ids[$e['key']] = (int) $have; $skipped++; continue; }
    if ($mode !== 'run') {
        WP_CLI::log(sprintf('%-40s  %s', bx_ev_when($parts), html_entity_decode(wp_strip_all_tags($e['title']))));
        $made++;
        continue;
    }
    $ids[$e['key']] = bx_ev_save($e);
    $made++;
}

/* Sections, with hand-picked lists translated from import keys to the new event ids */
if ($mode === 'run' && !empty($data['sections'])) {
    foreach ($data['sections'] as $name => $s) {
        $s['ids'] = array_values(array_filter(array_map(function ($k) use ($ids) { return isset($ids[$k]) ? $ids[$k] : 0; }, isset($s['keys']) ? $s['keys'] : array())));
        unset($s['keys']);
        bx_ev_section_save($name, $s);
    }
    WP_CLI::log(count($data['sections']) . ' sections saved.');
}
WP_CLI::success(($mode === 'run' ? "Added $made" : "Would add $made") . " events; $skipped already there.");
