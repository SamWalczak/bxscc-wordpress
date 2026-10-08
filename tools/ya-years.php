<?php
/*
 * Youth Ambassadors Program page: the bottom section becomes the same as "Archives by Year" on News and Events:
 *   <div class="bx-section">
 *   <h1 style="text-align: center;"><strong>Profiles of Past Programs</strong></h1>
 *   [bx_ya_years]
 *   </div>
 * [bx_ya_years] (plugin 1.66 or later) lists every program year by decade. Replaced: the "Profiles of Past Programs"
 * heading, the year links under it (the hand-written links, or the [bx_ya_years] put there by the first version of
 * this script), and the "- - -" separator lines directly above the heading and directly below the links.
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
if (!$ver || version_compare($ver, '1.66', '<')) { WP_CLI::error("The plugin here is version '$ver'; install the new version first, or the page would show [bx_ya_years] as text."); }

$page = get_page_by_path('youth-ambassadors-program');
if (!$page) { WP_CLI::error('No page at /youth-ambassadors-program/.'); }
$c = $page->post_content;
$section = "<div class=\"bx-section\">\n<h1 style=\"text-align: center;\"><strong>Profiles of Past Programs</strong></h1>\n[bx_ya_years]\n</div>\n";
if (strpos($c, rtrim($section)) !== false) { WP_CLI::success('Nothing to do: the page already has the new section.'); return; }

$sep = '(?:-|&#8211;|–)\s*(?:-|&#8211;|–)\s*(?:-|&#8211;|–)';      // "- - -", as typed or as WordPress shows it
$link = '<p[^>]*>\s*<a\s[^>]*href="[^"]*/\d{4}-bxscc-youth-ambassadors-program/?"[^>]*>\s*(?:<strong>)?\s*\d{4}\s*(?:</strong>)?\s*</a>\s*</p>\s*';
$re = '~(?:<h3[^>]*>\s*' . $sep . '\s*</h3>\s*)?'                                  // separator above (optional)
    . '<h3[^>]*>\s*<strong>\s*Profiles of Past Programs\s*</strong>\s*</h3>\s*'    // the heading
    . '((?:' . $link . ')+|\[bx_ya_years\]\s*)'                                    // hand-written links, or the shortcode
    . '(?:<p[^>]*>\s*' . $sep . '\s*</p>\s*)?~iu';                                 // separator below (optional)
if (preg_match_all($re, $c, $all, PREG_SET_ORDER) !== 1) { WP_CLI::error('Could not find exactly one "Profiles of Past Programs" heading followed by year links; the page has changed, so nothing was done.'); }
$m = $all[0];

// the years the shortcode will list: published pages with that address
$pages = array();
foreach (get_posts(array('post_type' => 'page', 'post_status' => 'publish', 'numberposts' => -1)) as $p) {
    if (preg_match('/^(\d{4})-bxscc-youth-ambassadors-program$/', $p->post_name, $pm)) { $pages[] = (int) $pm[1]; }
}
sort($pages);
WP_CLI::log('Years the list will show:  ' . implode(', ', $pages));
if (strpos($m[1], '[bx_ya_years]') === false) {
    preg_match_all('#/(\d{4})-bxscc-youth-ambassadors-program/?"#', $m[1], $ym);
    $listed = array_map('intval', $ym[1]);
    WP_CLI::log('Years linked by hand now:  ' . implode(', ', $listed));
    sort($listed);
    if ($listed !== $pages) { WP_CLI::error('Those differ, so the new list would add or drop a year; nothing was done.'); }
}

$old = $m[0];
$atEnd = substr($c, -strlen($old)) === $old;   // the section ends the page: no newline after it
$updated = str_replace($old, $atEnd ? rtrim($section) : $section, $c);
WP_CLI::log("\n--- now ---\n" . trim($old) . "\n\n--- after ---\n" . trim($section) . "\n");
if ($mode !== 'run') { WP_CLI::success('Dry run: nothing changed. Run again with "run" to make the change.'); return; }

$r = wp_update_post(wp_slash(array('ID' => $page->ID, 'post_content' => $updated)), true);
if (is_wp_error($r)) { WP_CLI::error($r->get_error_message()); }
clean_post_cache($page->ID);
$saved = get_post($page->ID)->post_content;
if ($saved !== $updated) { WP_CLI::error('Saved, but the stored content differs from what was written; check the page and its revisions.'); }
WP_CLI::success("Page {$page->ID} updated. The previous version is in its revisions.");
