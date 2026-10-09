<?php
/*
 * The PayPal donate button, in the sidebar of every page and on the Youth Ambassadors Program page (October 2026
 * audit): its picture is served from this site instead of baltimoresistercities.org, and PayPal's invisible 1-pixel
 * tracking image is replaced by a blank 1-pixel image written into the page itself (so nothing moves, not even by half a
 * pixel). Visitors see and use the same button; their browsers stop contacting those two other sites on every page
 * view, and the button no longer depends on another organization's website.
 * First, download the button's picture into the home folder (from the server, which can reach that site):
 *   curl -s -A "Mozilla/5.0" -o ~/donate-2.png https://baltimoresistercities.org/wp-content/uploads/2018/02/donate-2.png
 * Then, from public_html:
 *   wp eval-file ~/donate-button.php dry --skip-themes --skip-plugins=wordfence,updraftplus,shortcodes-ultimate
 *   wp eval-file ~/donate-button.php run --skip-themes --skip-plugins=wordfence,updraftplus,shortcodes-ultimate
 * "run" adds the picture to the Media Library (title "PayPal donate button", attached to the program page, with a
 * description saying where it is used), then changes the sidebar's Custom HTML
 * widget and the program page (WordPress keeps the page's previous version as a revision). Empty the page cache after.
 * Safe to run again: it reuses the picture it added and reports "Nothing to do" once both places are changed.
 */
if (!defined('ABSPATH')) { exit; }
$mode = isset($args[0]) ? $args[0] : 'dry';
kses_remove_filters();   // save the content exactly as written (no user is logged in under WP-CLI)
$remote = 'https://baltimoresistercities.org/wp-content/uploads/2018/02/donate-2.png';
$pixel = 'src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif"';
$blank = 'src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"';   // a transparent 1-pixel GIF
$home = trailingslashit(dirname(untrailingslashit(ABSPATH)));

// where the button is: the sidebar widget and the program page, each with the picture once and the pixel once
$widgets = get_option('widget_custom_html');
$wkey = null;
foreach ((array) $widgets as $k => $w) {
    if (is_array($w) && isset($w['content']) && strpos($w['content'], $remote) !== false) {
        if ($wkey !== null) { WP_CLI::error('The button picture is in more than one Custom HTML widget; nothing was done.'); }
        $wkey = $k;
    }
}
$page = get_page_by_path('youth-ambassadors-program');
$places = array();
if ($wkey !== null) { $places['the sidebar widget (custom_html-' . $wkey . ')'] = $widgets[$wkey]['content']; }
if ($page && strpos($page->post_content, $remote) !== false) { $places['the Youth Ambassadors Program page'] = $page->post_content; }
if (!$places) { WP_CLI::success('Nothing to do: the button picture is no longer loaded from baltimoresistercities.org.'); return; }
foreach ($places as $where => $c) {
    $n = substr_count($c, $remote); $p = substr_count($c, $pixel);
    if ($n !== 1 || $p !== 1) { WP_CLI::error("$where has the picture $n times and the pixel $p times (expected once each); nothing was done."); }
    WP_CLI::log("Found the button in $where.");
}

// the picture: the one added on an earlier run, or ~/donate-2.png
$found = get_posts(array('post_type' => 'attachment', 'post_status' => 'any', 'meta_key' => '_bx_donate_button', 'posts_per_page' => 1, 'fields' => 'ids'));
$att = $found ? (int) $found[0] : 0;
if (!$att) {
    $file = $home . 'donate-2.png';
    $size = is_file($file) ? @getimagesize($file) : false;
    if (!$size || $size['mime'] !== 'image/png') { WP_CLI::error("$file is missing or not a PNG picture. Download it first (see the top of this script)."); }
    WP_CLI::log(sprintf('Picture to add: %s, %d x %d pixels, %d bytes, md5 %s.', $file, $size[0], $size[1], filesize($file), md5_file($file)));
}
if ($mode !== 'run') {
    WP_CLI::log('Would ' . ($att ? 'reuse the picture already added (#' . $att . ')' : 'add the picture to the Media Library') . ', point the button at it, and blank the pixel in each place above.');
    WP_CLI::success('Dry run: nothing changed. Run again with "run" to make the change.');
    return;
}
if (!$att) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $tmp = wp_tempnam('donate-2.png');
    if (!$tmp || !copy($file, $tmp)) { WP_CLI::error('Could not copy the picture to a temporary file.'); }
    // attached to the program page (so it is not listed under Media > Unattached), with a note for whoever tidies the Media Library
    $att = media_handle_sideload(array('name' => 'donate-2.png', 'tmp_name' => $tmp), $page ? $page->ID : 0, 'PayPal donate button',
        array('post_content' => 'The PayPal donate button in the sidebar of every page and on the Youth Ambassadors Program page. Deleting it breaks the button.'));
    if (is_wp_error($att)) { @unlink($tmp); WP_CLI::error('Could not add the picture: ' . $att->get_error_message()); }
    update_post_meta($att, '_bx_donate_button', 1);
    if (md5_file(get_attached_file($att)) !== md5_file($file)) { WP_CLI::error('The stored picture differs from ~/donate-2.png; check Media Library #' . $att . '. Nothing else was changed.'); }
    WP_CLI::log('Added the picture to the Media Library (#' . $att . ').');
}
$local = wp_get_attachment_url($att);
if (!$local) { WP_CLI::error('The picture added (#' . $att . ') has no address; nothing else was changed.'); }
$fix = function ($c) use ($remote, $local, $pixel, $blank) { return str_replace(array($remote, $pixel), array($local, $blank), $c); };

if ($wkey !== null) {
    $widgets[$wkey]['content'] = $fix($widgets[$wkey]['content']);
    update_option('widget_custom_html', $widgets);
    WP_CLI::log('Sidebar widget changed.');
}
if ($page && strpos($page->post_content, $remote) !== false) {
    $new = $fix($page->post_content);
    $r = wp_update_post(wp_slash(array('ID' => $page->ID, 'post_content' => $new)), true);
    if (is_wp_error($r)) { WP_CLI::error('The program page was not saved: ' . $r->get_error_message()); }
    clean_post_cache($page->ID);
    if (get_post($page->ID)->post_content !== $new) { WP_CLI::error('The program page was saved, but its stored content differs from what was written; check its revisions.'); }
    WP_CLI::log('Program page changed (page ' . $page->ID . ').');
}
WP_CLI::success("Done: the button's picture now comes from $local. Empty the page cache next (the sidebar is on every page).");
