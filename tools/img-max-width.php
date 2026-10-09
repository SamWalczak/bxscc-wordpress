<?php
/*
 * Photos with a fixed pixel cap fit narrow phones (October 2026 cross-browser test).
 * Some content photos carry style="max-width: 320px; height: auto;" (the Partners logo; 240px and 260px on the 2013
 * trade mission pages, CHINA Town Hall and History). An inline max-width replaces the theme's "never wider than the
 * column" rule, so on a screen narrower than the cap plus the page margins the photo sticks out and the page scrolls
 * sideways: the Partners page at 350px and narrower (small phones, iPhones set to Display Zoom), the 2013 pages at
 * 290px and narrower. Each such cap becomes max-width: min(<cap>, 100%): the same size everywhere it fits, never
 * wider than the column. Photos whose style already sets a width are left alone.
 * From public_html:
 *   wp eval-file ~/img-max-width.php dry --skip-themes --skip-plugins   (lists every change)
 *   wp eval-file ~/img-max-width.php run --skip-themes --skip-plugins   (makes them)
 * WordPress keeps the previous version of each page as a revision. A second run says there is nothing to do.
 */
if (!defined('ABSPATH')) { exit; }
$mode = isset($args[0]) ? $args[0] : 'dry';
kses_remove_filters();   // save the content exactly as written (no user is logged in under WP-CLI)

function bx_imw_fix($html, &$n) {
    return preg_replace_callback('#<img\s[^>]*>#i', function ($m) use (&$n) {
        $tag = $m[0];
        if (!preg_match('#\sstyle="([^"]*)"#i', $tag, $s)) { return $tag; }
        $style = $s[1];
        if (preg_match('#(^|;)\s*width\s*:#i', $style)) { return $tag; }                 // a width already keeps it in the column
        if (!preg_match('#max-width\s*:\s*(\d+(?:\.\d+)?px)\s*(;|$)#i', $style)) { return $tag; }   // no plain pixel cap (or already min())
        $new = preg_replace('#max-width\s*:\s*(\d+(?:\.\d+)?px)#i', 'max-width: min($1, 100%)', $style, 1);
        $n++;
        return str_replace('style="' . $style . '"', 'style="' . $new . '"', $tag);
    }, $html);
}

$posts = get_posts(array('post_type' => array('page', 'post'), 'post_status' => array('publish', 'private'), 'numberposts' => -1));
$total = 0; $pages = 0;
foreach ($posts as $p) {
    $n = 0;
    $new = bx_imw_fix($p->post_content, $n);
    if (!$n) { continue; }
    $pages++; $total += $n;
    WP_CLI::log(sprintf('%s (page %d): %d photo%s', get_permalink($p), $p->ID, $n, $n === 1 ? '' : 's'));
    if ($mode !== 'run') { continue; }
    $r = wp_update_post(wp_slash(array('ID' => $p->ID, 'post_content' => $new)), true);
    if (is_wp_error($r)) { WP_CLI::error("Page {$p->ID}: " . $r->get_error_message()); }
    clean_post_cache($p->ID);
    $check = 0; bx_imw_fix(get_post($p->ID)->post_content, $check);
    if ($check) { WP_CLI::error("Page {$p->ID}: the saved page still has $check capped photo(s)."); }
}
if (!$total) { WP_CLI::success('Nothing to do: no photo has a plain pixel max-width.'); return; }
if ($mode !== 'run') { WP_CLI::success("Dry run: $total photos on $pages pages would change. Run again with \"run\"."); return; }
WP_CLI::success("Saved $pages pages ($total photos). Empty the page cache next.");
