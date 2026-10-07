<?php
/*
 * Undo for bx-apply-pages.php: puts back the home page, News and Events, and every yearly post exactly as they
 * were in ~/bx-events-dump.json (the copy made before the switch). Events stay in their table; nothing is deleted.
 *   wp eval-file ~/bx-restore-pages.php dry --skip-themes --skip-plugins
 *   wp eval-file ~/bx-restore-pages.php run --skip-themes --skip-plugins
 */
if (!defined('ABSPATH')) { exit; }
$mode = isset($args[0]) ? $args[0] : 'dry';
$file = dirname(ABSPATH) . '/bx-events-dump.json';
if (!file_exists($file)) { WP_CLI::error("Missing $file"); }
$rows = json_decode(file_get_contents($file), true);
if (!is_array($rows)) { WP_CLI::error('Could not read the dump.'); }
kses_remove_filters();
$n = 0;
foreach ($rows as $r) {
    $p = get_post((int) $r['ID']);
    if (!$p || $p->post_name !== $r['post_name']) { WP_CLI::warning("Skipped {$r['post_name']} (ID {$r['ID']} not found or renamed)"); continue; }
    if ($p->post_content === $r['post_content']) { continue; }
    if ($mode !== 'run') { WP_CLI::log(sprintf('%5d  %-28s  would be restored', $p->ID, $p->post_name)); $n++; continue; }
    $res = wp_update_post(wp_slash(array('ID' => $p->ID, 'post_content' => $r['post_content'])), true);
    if (is_wp_error($res)) { WP_CLI::warning($p->post_name . ': ' . $res->get_error_message()); } else { $n++; }
}
WP_CLI::success(($mode === 'run' ? 'Restored ' : 'Would restore ') . $n . ' pages and posts.');
