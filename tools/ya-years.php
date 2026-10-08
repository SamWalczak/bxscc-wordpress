<?php
/*
 * Youth Ambassadors Program page: replaces the hand-written year links under "Profiles of Past Programs" with
 * [bx_ya_years] (plugin 1.66 or later), which lists every program year by decade, like Archives by Year on News and
 * Events, and gives the heading id="past-programs" (the "All Program Years" link on each year's page lands there).
 * From public_html:
 *   wp eval-file ~/ya-years.php dry --skip-themes --skip-plugins   (shows what would change)
 *   wp eval-file ~/ya-years.php run --skip-themes --skip-plugins   (changes it)
 * WordPress keeps the previous version as a revision (page editor > Revisions).
 */
if (!defined('ABSPATH')) { exit; }
$mode = isset($args[0]) ? $args[0] : 'dry';
kses_remove_filters();   // save the content exactly as written (no user is logged in under WP-CLI)

$plugin = WP_PLUGIN_DIR . '/bxscc-wordpress-enhancements/bxscc-wordpress-enhancements.php';
$ver = is_file($plugin) ? get_file_data($plugin, array('v' => 'Version'))['v'] : '';
if (!$ver || version_compare($ver, '1.66', '<')) { WP_CLI::error("The plugin here is version '$ver'; install 1.66 first, or the page would show [bx_ya_years] as text."); }

$page = get_page_by_path('youth-ambassadors-program');
if (!$page) { WP_CLI::error('No page at /youth-ambassadors-program/.'); }
$c = $page->post_content;
if (strpos($c, '[bx_ya_years]') !== false) { WP_CLI::success('Nothing to do: the page already has [bx_ya_years].'); return; }

// the heading, then one paragraph per year, each holding one link to a YYYY-bxscc-youth-ambassadors-program page
$re = '#(<h3)((?:(?!\bid=)[^>])*)(>\s*<strong>\s*Profiles of Past Programs\s*</strong>\s*</h3>)\s*'
    . '((?:<p[^>]*>\s*<a\s[^>]*href="[^"]*/\d{4}-bxscc-youth-ambassadors-program/?"[^>]*>\s*(?:<strong>)?\s*\d{4}\s*(?:</strong>)?\s*</a>\s*</p>\s*)+)#i';
if (preg_match_all($re, $c, $all, PREG_SET_ORDER) !== 1) { WP_CLI::error('Could not find exactly one "Profiles of Past Programs" heading followed by year links; the page has changed, so nothing was done.'); }
$m = $all[0];
preg_match_all('#/(\d{4})-bxscc-youth-ambassadors-program/?"#', $m[4], $ym);
$listed = array_map('intval', $ym[1]);

// the years the shortcode will list: published pages with that address
$pages = array();
foreach (get_posts(array('post_type' => 'page', 'post_status' => 'publish', 'numberposts' => -1)) as $p) {
    if (preg_match('/^(\d{4})-bxscc-youth-ambassadors-program$/', $p->post_name, $pm)) { $pages[] = (int) $pm[1]; }
}
sort($pages); $sorted = $listed; sort($sorted);
WP_CLI::log('Years linked by hand now:       ' . implode(', ', $listed));
WP_CLI::log('Years the list will show:       ' . implode(', ', $pages));
if ($sorted !== $pages) { WP_CLI::error('Those differ, so the new list would add or drop a year; nothing was done.'); }

$old = $m[0];
$new = $m[1] . ' id="past-programs"' . $m[2] . $m[3] . "\n[bx_ya_years]\n";
$updated = str_replace($old, $new, $c);
WP_CLI::log("\n--- now ---\n" . trim($old) . "\n\n--- after ---\n" . trim($new) . "\n");
if ($mode !== 'run') { WP_CLI::success('Dry run: nothing changed. Run again with "run" to make the change.'); return; }

$r = wp_update_post(wp_slash(array('ID' => $page->ID, 'post_content' => $updated)), true);
if (is_wp_error($r)) { WP_CLI::error($r->get_error_message()); }
clean_post_cache($page->ID);
$saved = get_post($page->ID)->post_content;
if ($saved !== $updated) { WP_CLI::error('Saved, but the stored content differs from what was written; check the page and its revisions.'); }
WP_CLI::success("Page {$page->ID} updated. The previous version is in its revisions.");
