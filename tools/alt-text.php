<?php
/*
 * Image descriptions (alt text) from the October 2026 site review, as approved on October 11, 2026. Needs BXSCC
 * WordPress Enhancements 1.80 for the banner (1.80 prints each slide's Alt Text; earlier versions print none).
 *   Banner: the Alternative Text of the nine slides (carousel1-cropped to carousel9-cropped) in the Media Library.
 *   Photos in pages: the empty (or missing) alt of nine photos on the 2018, 2019, 2024 and 2025 Youth Ambassadors
 *     pages and the Youth Ambassadors Program page (13 places), and the same text as each photo's Alternative Text in the Media
 *     Library, so it comes along when the photo is inserted again.
 *   Donate button (sidebar widget and the Youth Ambassadors Program page): "Donate" instead of PayPal's slogan.
 *   Sister Cities International badge (sidebar Image widget): "Sister Cities International".
 * The logo's description comes from the plugin (1.80), not from here.
 * Everything is checked first; if anything is not as expected (someone edited it), nothing at all is changed, except
 * that Media Library Alternative Text someone has already written is left alone and listed. WordPress keeps each
 * page's previous version as a revision. A second run says there is nothing to do.
 * From public_html:
 *   wp eval-file ~/alt-text.php dry --skip-themes --skip-plugins   (lists every change)
 *   wp eval-file ~/alt-text.php run --skip-themes --skip-plugins   (makes them)
 * Empty the page cache after (the sidebar is on every page).
 */
if (!defined('ABSPATH')) { exit; }
$mode = isset($args[0]) ? $args[0] : 'dry';
kses_remove_filters();   // save the content exactly as written (no user is logged in under WP-CLI)

// banner slides: attachment id => array(name, description)
$slides = array(
    1914 => array('carousel1-cropped', 'A speaker at a podium and a panel of five on stage at the 7th China-U.S. Sister Cities Conference in Hangzhou, under screens reading "How Partnerships and Innovation Have Shaped the Future of Baltimore\'s Inner Harbor"'),
    1915 => array('carousel2-cropped', 'Xiamen Vice Mayor Zhuang Rongliang and Baltimore Deputy Mayor JD Merrill in conversation in a wood-paneled room, April 2025'),
    1916 => array('carousel3-cropped', 'The Xiamen Vice Mayoral delegation and their hosts listening to a guide beside exhibit cases at the Enoch Pratt Free Library, Baltimore, April 2025'),
    1917 => array('carousel4-cropped', 'Guests around a long table at Baltimore City Hall watching the 2025 CHINA Town Hall webcast on a wall screen'),
    1918 => array('carousel5-cropped', 'Baltimore and Xiamen delegates on stage in front of a banner reading "In Celebration of the 40th Anniversary of Xiamen-Baltimore Sister City Relations," Xiamen, July 2025'),
    1919 => array('carousel6-cropped', 'Baltimore Symphony Orchestra members and uniformed Xiamen students with their instruments, giving a thumbs-up in front of a large carved pipe organ on Gulangyu, October 2025'),
    1920 => array('carousel7-cropped', 'Baltimore visitors and their Xiamen hosts behind a sign reading "Welcome to Xiamen Library," July 2025'),
    1921 => array('carousel8-cropped', 'Three 2025 Youth Ambassadors and a host digging with shovels to plant a tree beside a marker at the China-U.S. Youth Friendship Orchard, Xiamen'),
    1922 => array('carousel9-cropped', 'The 2025 Youth Ambassadors with other U.S. students holding a "U.S. Youth Ambassadors in Xiamen 2025" banner in a hall lined with curved bookshelves'),
);
// photos in pages: file under wp-content/uploads => array(attachment id, description, array(page id => times it appears))
$photos = array(
    '2018/09/Trip-1.png' => array(746, 'Five people in white T-shirts and lanyards waving small American flags on a red carpet, in a room with red lanterns, a tiled eave and Chinese ink paintings', array(745 => 1)),
    '2018/09/image1-1-e1536206487504.jpeg' => array(767, 'Aaron G., 2018 Youth Ambassador', array(745 => 1)),
    '2018/09/Trip-3.png' => array(751, 'Alexander G., 2018 Youth Ambassador', array(745 => 1)),
    '2018/11/Trip-4.png' => array(808, 'Kolby L., 2018 Youth Ambassador', array(745 => 1)),
    '2018/11/Trip-5.png' => array(809, 'Mateen K., 2018 Youth Ambassador', array(745 => 1)),
    '2018/09/Trip-6.png' => array(748, 'The 2018 Youth Ambassadors', array(745 => 1, 917 => 1)),
    '2024/03/2019-6-27-Image_20190630203727-compressed.jpg' => array(1551, '2019 Youth Ambassadors in Xiamen, June 2019', array(828 => 1, 917 => 1)),
    '2019/07/Screen-Shot-2019-07-21-at-11.00.07-PM.png' => array(940, 'Nine young people smiling for a photo in a blue room, seven in matching white school polo shirts with red collars, two of them making peace signs', array(828 => 1)),
    '2024/08/2024-7-25-Xiamen-FAO-DSC_2192-compressed-for-web.jpg' => array(1657, '2024 Youth Ambassadors at the Xiamen Foreign Affairs Office, July 25, 2024', array(917 => 1, 1781 => 1, 1851 => 1)),
);
$page_names = array(745 => '2018-bxscc-youth-ambassadors-program', 828 => '2019-bxscc-youth-ambassadors-program', 917 => 'youth-ambassadors-program',
                    1781 => '2024-bxscc-youth-ambassadors-program', 1851 => '2025-bxscc-youth-ambassadors-program');
$paypal_old = 'alt="PayPal - The safer, easier way to pay online!"';
$paypal_new = 'alt="Donate"';
$badge_alt = 'Sister Cities International';

$log = array(); $warn = array(); $done = 0;

// 1. Media Library Alternative Text (slides and the eight photos)
$meta = array();
foreach ($slides as $id => $s) { $meta[$id] = $s; }
foreach ($photos as $file => $p) { $meta[$p[0]] = array(basename($file), $p[1]); }
foreach ($meta as $id => $m) {
    $a = get_post($id);
    if (!$a || $a->post_type !== 'attachment') { WP_CLI::error("Media Library item $id ({$m[0]}) is missing. Nothing was changed."); }
    $file = (string) get_post_meta($id, '_wp_attached_file', true);
    $stem = preg_replace('/-e\d{10,}$/', '', pathinfo($m[0], PATHINFO_FILENAME));   // an image edited in WordPress gets -e<time>
    if (strpos(basename($file), $stem) !== 0) {
        WP_CLI::error("Media Library item $id is \"$file\", not {$m[0]}. Nothing was changed.");
    }
    $cur = trim((string) get_post_meta($id, '_wp_attachment_image_alt', true));
    if ($cur === $m[1]) { $done++; continue; }
    if ($cur !== '') { $warn[] = "Media Library item $id ({$m[0]}) already has Alternative Text \"$cur\"; left alone."; unset($meta[$id]); continue; }
    $log[] = sprintf('Media Library %-45s Alternative Text', $m[0]);
}

// 2. photos in pages
$pages = array();
foreach ($photos as $file => $p) {
    foreach ($p[2] as $pid => $times) {
        if (!isset($pages[$pid])) {
            $post = get_post($pid);
            if (!$post || $post->post_name !== $page_names[$pid] || $post->post_status !== 'publish') {
                WP_CLI::error("Page $pid is not the published \"{$page_names[$pid]}\" this script expects. Nothing was changed.");
            }
            $pages[$pid] = array('old' => $post->post_content, 'new' => $post->post_content, 'what' => array());
        }
        $re = '#<img\b[^>]*\ssrc="[^"]*/wp-content/uploads/' . preg_quote($file, '#') . '"[^>]*>#';
        preg_match_all($re, $pages[$pid]['new'], $mm);
        $tags = $mm[0];
        $empty = array_filter($tags, function ($t) { return strpos($t, ' alt=""') !== false || !preg_match('#\salt="#', $t); });   // empty, or no alt at all
        $named = array_filter($tags, function ($t) use ($p) { return strpos($t, ' alt="' . esc_attr($p[1]) . '"') !== false; });
        if (count($named) === $times && !$empty) { $done++; continue; }
        if (count($tags) !== $times || count($empty) !== $times) {
            WP_CLI::error("{$page_names[$pid]} (page $pid): " . basename($file) . " appears " . count($tags) . " times, " . count($empty) . " with an empty description (expected $times and $times). Nothing was changed.");
        }
        $pages[$pid]['new'] = preg_replace_callback($re, function ($t) use ($p) {
            $alt = ' alt="' . esc_attr($p[1]) . '"';
            return strpos($t[0], ' alt=""') !== false ? str_replace(' alt=""', $alt, $t[0]) : preg_replace('#^<img\b#', '<img' . $alt, $t[0], 1);
        }, $pages[$pid]['new']);
        $pages[$pid]['what'][] = basename($file);
    }
}

// 3. donate button: the sidebar Custom HTML widget and the program page
$widgets = get_option('widget_custom_html');
$wkey = null;
foreach ((array) $widgets as $k => $w) {
    if (is_array($w) && isset($w['content']) && strpos($w['content'], 'paypal.com') !== false) {
        if ($wkey !== null) { WP_CLI::error('More than one Custom HTML widget has a PayPal button. Nothing was changed.'); }
        $wkey = $k;
    }
}
$widget_new = null;
if ($wkey === null) { WP_CLI::error('No Custom HTML widget has the PayPal button. Nothing was changed.'); }
$c = $widgets[$wkey]['content'];
if (substr_count($c, $paypal_new) === 1 && substr_count($c, $paypal_old) === 0) { $done++; }
elseif (substr_count($c, $paypal_old) === 1) { $widget_new = str_replace($paypal_old, $paypal_new, $c); $log[] = "Sidebar widget custom_html-$wkey          donate button"; }
else { WP_CLI::error("The sidebar widget custom_html-$wkey has PayPal's description " . substr_count($c, $paypal_old) . ' times (expected once). Nothing was changed.'); }
if (!isset($pages[917])) {
    $post = get_post(917);
    $pages[917] = array('old' => $post->post_content, 'new' => $post->post_content, 'what' => array());
}
$n = substr_count($pages[917]['new'], $paypal_old);
if ($n === 1) { $pages[917]['new'] = str_replace($paypal_old, $paypal_new, $pages[917]['new']); $pages[917]['what'][] = 'donate button'; }
elseif ($n === 0 && substr_count($pages[917]['new'], $paypal_new) === 1) { $done++; }
else { WP_CLI::error("youth-ambassadors-program (page 917) has PayPal's description $n times (expected once). Nothing was changed."); }

// 4. Sister Cities International badge: the sidebar Image widget showing Media Library item 182
$images = get_option('widget_media_image');
$bkey = null;
foreach ((array) $images as $k => $w) {
    if (is_array($w) && isset($w['attachment_id']) && (int) $w['attachment_id'] === 182) { $bkey = $k; }
}
if ($bkey === null) { WP_CLI::error('No Image widget shows the Sister Cities International badge (item 182). Nothing was changed.'); }
$badge_change = false;
if (trim((string) $images[$bkey]['alt']) === $badge_alt) { $done++; }
elseif (trim((string) $images[$bkey]['alt']) === '') { $badge_change = true; $log[] = "Sidebar widget media_image-$bkey           Sister Cities International badge"; }
else { $warn[] = "The badge widget already has the description \"{$images[$bkey]['alt']}\"; left alone."; }

foreach ($pages as $pid => $pg) {
    if ($pg['new'] !== $pg['old']) { $log[] = sprintf('%-45s %s', $page_names[$pid], implode(', ', $pg['what'])); }
}
foreach ($log as $l) { WP_CLI::log($l); }
foreach ($warn as $w) { WP_CLI::warning($w); }
if (!$log) { WP_CLI::success("Nothing to do: all descriptions are already in place ($done)."); return; }
if ($mode !== 'run') { WP_CLI::success('Dry run: ' . count($log) . ' changes would be made. Run again with "run".'); return; }

foreach ($meta as $id => $m) {
    if (trim((string) get_post_meta($id, '_wp_attachment_image_alt', true)) === '') {
        update_post_meta($id, '_wp_attachment_image_alt', $m[1]);
        if (get_post_meta($id, '_wp_attachment_image_alt', true) !== $m[1]) { WP_CLI::error("Media Library item $id: the saved text differs."); }
    }
}
foreach ($pages as $pid => $pg) {
    if ($pg['new'] === $pg['old']) { continue; }
    $r = wp_update_post(wp_slash(array('ID' => $pid, 'post_content' => $pg['new'])), true);
    if (is_wp_error($r)) { WP_CLI::error("Page $pid: " . $r->get_error_message()); }
    clean_post_cache($pid);
    if (get_post($pid)->post_content !== $pg['new']) { WP_CLI::error("Page $pid: the saved content differs from what was written. Check its revisions."); }
}
if ($widget_new !== null) {
    $widgets[$wkey]['content'] = $widget_new;
    update_option('widget_custom_html', $widgets);
}
if ($badge_change) {
    $images[$bkey]['alt'] = $badge_alt;
    update_option('widget_media_image', $images);
}
WP_CLI::success('Saved ' . count($log) . ' changes. Empty the page cache next (every page: the sidebar changed).');
