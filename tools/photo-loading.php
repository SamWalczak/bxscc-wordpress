<?php
/*
 * Four photos load in the wrong order (October 2026 audit, measured on a slow phone connection):
 * - History and Mission Overview: the main photo at the top was marked loading="lazy" by hand, so the browser starts
 *   it only after laying out the page. Removing that mark: the photo shows about 0.3 to 0.6 seconds sooner on phones.
 * - Youth Ambassadors Program and the 2019 program page: WordPress loads the first three content photos straight
 *   away and counts the sidebar's PayPal pixel among them, so the 2019 group photo far down the page downloads
 *   together with the main photo. Marking it (and on the program page the Trip-6 graphic, which WordPress already
 *   loads lazily today) loading="lazy": the main photo shows about 1.2 seconds sooner on phones.
 * Nothing else changes; the photos look and sit exactly as before.
 * From public_html:
 *   wp eval-file ~/photo-loading.php dry --skip-themes --skip-plugins   (shows what would change)
 *   wp eval-file ~/photo-loading.php run --skip-themes --skip-plugins   (changes it)
 * WordPress keeps the previous version of each page as a revision.
 */
if (!defined('ABSPATH')) { exit; }
$mode = isset($args[0]) ? $args[0] : 'dry';
kses_remove_filters();   // save the content exactly as written (no user is logged in under WP-CLI)

$jobs = array(   // page slug => array(file name => 'lazy' (add the mark) | 'eager' (remove it))
    'history' => array('1985-1st-balt-to-xiamen-delegation.jpg' => 'eager'),
    'mission-overview' => array('kaliope-parthemos-768x493.jpg' => 'eager'),
    'youth-ambassadors-program' => array('2019-6-27-Image_20190630203727-compressed.jpg' => 'lazy', 'Trip-6.png' => 'lazy'),
    '2019-bxscc-youth-ambassadors-program' => array('2019-6-27-Image_20190630203727-compressed.jpg' => 'lazy'),
);
$changed = 0;
foreach ($jobs as $slug => $files) {
    $pages = get_posts(array('post_type' => 'page', 'name' => $slug, 'post_status' => 'publish', 'numberposts' => 2));
    if (count($pages) !== 1) { WP_CLI::warning("$slug: found " . count($pages) . ' published pages with this address; skipped.'); continue; }
    $p = $pages[0];
    $c = $p->post_content;
    $new = $c;
    foreach ($files as $file => $want) {
        if (!preg_match_all('#<img\s[^>]*/' . preg_quote($file, '#') . '"[^>]*>#i', $new, $m) || count($m[0]) !== 1) {
            WP_CLI::warning("$slug: expected one photo $file, found " . count($m[0] ?? array()) . '; skipped.');
            continue;
        }
        $tag = $m[0][0];
        $has = (bool) preg_match('#\sloading="lazy"#i', $tag);
        if ($want === 'eager' && $has) { $to = preg_replace('#\sloading="lazy"#i', '', $tag, 1); }
        elseif ($want === 'lazy' && !$has) { $to = preg_replace('#^<img\s#i', '<img loading="lazy" ', $tag, 1); }
        else { WP_CLI::log("$slug: $file already as wanted."); continue; }
        WP_CLI::log("$slug: $file\n  now:   $tag\n  after: $to");
        $new = str_replace($tag, $to, $new);
    }
    if ($new === $c) { continue; }
    $changed++;
    if ($mode !== 'run') { continue; }
    $r = wp_update_post(wp_slash(array('ID' => $p->ID, 'post_content' => $new)), true);
    if (is_wp_error($r)) { WP_CLI::error("$slug: " . $r->get_error_message()); }
    clean_post_cache($p->ID);
    if (get_post($p->ID)->post_content !== $new) { WP_CLI::error("$slug: saved, but the stored content differs from what was written; check its revisions."); }
    WP_CLI::log("$slug: saved (page {$p->ID}).");
}
if ($mode !== 'run') { WP_CLI::success("Dry run: $changed pages would change; nothing was saved. Run again with \"run\"."); return; }
WP_CLI::success("$changed pages saved. Empty the page cache next.");
