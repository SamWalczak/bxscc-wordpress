<?php
/*
 * Switches the home page, News and Events, and every yearly post to the event lists.
 * Run AFTER bx-import-events.php. From public_html:
 *   wp eval-file ~/bx-apply-pages.php dry --skip-themes --skip-plugins   (shows what would change)
 *   wp eval-file ~/bx-apply-pages.php run --skip-themes --skip-plugins   (changes it)
 * WordPress keeps the previous version of each page as a revision, and ~/bx-events-dump.json holds them all.
 */
if (!defined('ABSPATH')) { exit; }
$mode = isset($args[0]) ? $args[0] : 'dry';
kses_remove_filters();

$home = <<<'HTML'
<!-- wp:html -->
[bx_section name="upcoming"]
[bx_section name="recent"]
[bx_section name="highlights"]
<!-- /wp:html -->
HTML;

$news = <<<'HTML'
<!-- wp:html -->
<h3 style="text-align: center;"><strong>Regularly Scheduled Monthly Meeting</strong></h3>
<p>The Baltimore-Xiamen Sister City Committee usually meets at 6pm on the third Thursday of every month. Monthly meetings are held virtually, with selected special programs and events conducted in-person, virtually, or in a hybrid format.</p>
<p>Individuals who might like to join these meetings as guests should contact the <a href="mailto:secretary-xiamen@baltimoresistercities.org">Committee Secretary</a> in advance to receive call-in information.</p>
<div style="height: 24px;"></div>
[bx_section name="upcoming"]
[bx_section name="recent"]
[bx_section name="highlights"]
[bx_section name="previous"]
[bx_section name="prepandemic"]
<div class="bx-section">
<h1 style="text-align: center;"><strong>Archives by Year</strong></h1>
[bx_years]
</div>
<!-- /wp:html -->
HTML;

$targets = array();
$front = (int) get_option('page_on_front');
if ($front) { $targets[$front] = $home; }
$ne = get_page_by_path('news-and-events');
if ($ne) { $targets[$ne->ID] = $news; }
foreach (get_posts(array('post_type' => 'post', 'post_status' => array('publish', 'private'), 'numberposts' => -1)) as $p) {
    if (preg_match('/^news-and-media-(\d{4})$/', $p->post_name, $m)) {
        $targets[$p->ID] = '<!-- wp:shortcode -->[bx_year year="' . $m[1] . '"]<!-- /wp:shortcode -->';
    }
}
foreach ($targets as $id => $content) {
    $p = get_post($id);
    if ($mode !== 'run') { WP_CLI::log(sprintf('%5d  %-28s  %6d chars -> %d chars', $id, $p->post_name, strlen($p->post_content), strlen($content))); continue; }
    $r = wp_update_post(wp_slash(array('ID' => $id, 'post_content' => $content)), true);
    if (is_wp_error($r)) { WP_CLI::warning("$id: " . $r->get_error_message()); }
}
WP_CLI::success(($mode === 'run' ? 'Updated ' : 'Would update ') . count($targets) . ' pages and posts.');
