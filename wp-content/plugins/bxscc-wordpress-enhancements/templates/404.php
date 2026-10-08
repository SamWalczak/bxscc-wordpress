<?php
/* The not-found page (block 19 of the main file), for the News Magazine theme. Same layout as the theme's page.php,
   so it lines up with the sidebar like every other page. An "Error Code 404" heading styled like the event section headings
   ("Upcoming Events"), the navy line those sections have under their heading (gold in dark mode), and one centered
   line: a playful 抱歉！ ("sorry!") and the English note.
   No search form. */
if (!defined('ABSPATH')) { exit; }
global $wdwt_front;
get_header();
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
      <h1 class="page-header"><span>Error Code 404</span></h1>
      <div class="entry">
<h1 style="text-align: center;"><strong>Error Code 404</strong></h1>
<div class="bx-404">
<p><span lang="zh-Hans">抱歉！</span>That page was not found. Please navigate using the menu above to find out more about the BXSCC.</p>
</div>
      </div>
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
