<?php
/* The not-found page (block 19 of the main file), for the News Magazine theme. Same layout as the theme's page.php,
   so the box's top line meets the sidebar's as on every other page. No search form: a short explanation and the
   site's sections, read from the primary menu, so the list follows any change to the menu. */
if (!defined('ABSPATH')) { exit; }
global $wdwt_front;
get_header();

$bx_sections = array();
$bx_locations = get_nav_menu_locations();
$bx_items = !empty($bx_locations['primary-menu']) ? wp_get_nav_menu_items($bx_locations['primary-menu']) : false;
if (is_array($bx_items)) {
    $bx_home = untrailingslashit(home_url('/'));
    foreach ($bx_items as $bx_item) {   // top-level items, in menu order, each with its sub-pages
        if ((int) $bx_item->menu_item_parent === 0 && untrailingslashit($bx_item->url) !== $bx_home) { $bx_sections[$bx_item->ID] = array('item' => $bx_item, 'sub' => array()); }
    }
    foreach ($bx_items as $bx_item) {
        if (isset($bx_sections[(int) $bx_item->menu_item_parent])) { $bx_sections[(int) $bx_item->menu_item_parent]['sub'][] = $bx_item; }
    }
}
?>
</header>
<div class="container">
  <?php if (is_active_sidebar('sidebar-1')) { ?>
    <aside id="sidebar1">
      <div class="sidebar-container">
        <?php dynamic_sidebar('sidebar-1'); ?>
        <div class="clear"></div>
      </div>
    </aside>
  <?php } ?>
  <div id="content">
    <div class="single-post">
      <h1 class="page-header"><span>Page not found</span></h1>
      <div class="entry"><table class="bx-404">
<tbody>
<tr>
<td colspan="2"><h3 style="text-align: center;"><strong>Page not found</strong></h3></td>
</tr>
<tr>
<td colspan="2"><p>There is no page at this address. It may have moved when the site was reorganized, or the address may have a typo. Start from the <a href="<?php echo esc_url(home_url('/')); ?>">home page</a><?php echo $bx_sections ? ' or one of these sections:' : '.'; ?></p>
<?php if ($bx_sections) { ?>
<ul class="bx-404-map">
<?php foreach ($bx_sections as $bx_s) {
    echo '<li><a href="' . esc_url($bx_s['item']->url) . '">' . esc_html($bx_s['item']->title) . '</a>';
    if ($bx_s['sub']) {
        echo '<span class="bx-404-sub">';
        foreach ($bx_s['sub'] as $bx_i => $bx_sub) {
            if ($bx_i) { echo ' <span aria-hidden="true">&middot;</span> '; }
            echo '<a href="' . esc_url($bx_sub->url) . '">' . esc_html($bx_sub->title) . '</a>';
        }
        echo '</span>';
    }
    echo "</li>\n";
} ?>
</ul>
<?php } ?></td>
</tr>
</tbody>
</table></div>
    </div>
    <div class="clear"></div>
    <?php if (is_object($wdwt_front) && method_exists($wdwt_front, 'bottom_advertisment')) { $wdwt_front->bottom_advertisment(); } ?>
  </div>
  <?php if (is_active_sidebar('sidebar-2')) { ?>
    <aside id="sidebar2">
      <div class="sidebar-container">
        <?php dynamic_sidebar('sidebar-2'); ?>
        <div class="clear"></div>
      </div>
    </aside>
  <?php } ?>
  <div class="clear"></div>
</div>
<?php get_footer();
