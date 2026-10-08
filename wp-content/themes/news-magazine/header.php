<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <?php
  global $wdwt_front; ?>
  <meta charset="<?php bloginfo('charset'); ?>"/>
  <meta name="viewport" content="width=device-width"/>
  <meta name="viewport" content="initial-scale=1.0"/>
  <meta name="HandheldFriendly" content="true"/>
  <link rel="profile" href="http://gmpg.org/xfn/11"/>
  <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>"/>
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php
$logo_img = $wdwt_front->get_param('logo_img');
$show_desc = $wdwt_front->get_param('show_desc');
$date_enable = $wdwt_front->get_param('date_enable');
$wdwt_front->integration_body(); // body integretion
$header_image = get_header_image();

$show_slider_wd = $wdwt_front->get_param('show_slider_wd', array(), false);
$slider_wd_id = $wdwt_front->get_param('slider_wd_id');
if (is_array($slider_wd_id)) {
  $slider_wd_id = $slider_wd_id[0];
}

$show_slider = $wdwt_front->get_param('show_slider');
if (is_array($show_slider)) {
  $show_slider = $show_slider[0];
}

?>
<header>
  <?php if (!empty($header_image)) { ?>
    <div class="container">
      <a class="custom-header-a" href="<?php echo esc_url(home_url('/')); ?>">
        <img src="<?php echo header_image(); ?>" class="custom-header">
      </a>
    </div>
  <?php } ?>
  <div id="header">
    <?php
    //if($wdwt_front->header_social_buttons()!= NULL || $wdwt_front->header_latest_posts()!=NULL){ ?>
    <div id="header_part">
      <div class="container">
        <?php if ($date_enable) echo '<p style="float:left;margin: 7px 21px 7px 0;">' . date_i18n(get_option('date_format')) . '</p>'; ?>
        <?php $wdwt_front->header_latest_posts();
        $wdwt_front->header_social_buttons(); ?>
      </div>
    </div>
    <?php //} ?>
    <div id="header-container">
      <div class="container">
        <div id="header-middle">
          <?php $wdwt_front->logo(); ?>
          <div id="adv">
            <?php $wdwt_front->head_advertisment();
            if ($show_desc) { ?>
              <p id="site_desc"><?php echo get_bloginfo('description', 'display'); ?></p>
            <?php } ?>
          </div>
          <div class="clear"></div>
        </div>
      </div>
    </div>
  </div>
  <div class="phone-menu-block">
    <nav id="top-nav">
      <div class="container">
        <?php

        $news_magazine_show_home = true;
        if (has_nav_menu('primary-menu')) {
          $news_magazine_show_home = false;
        }
        $wdwt_menu = wp_nav_menu(array(
          'show_home' => $news_magazine_show_home,
          'theme_location' => 'primary-menu',
          'container' => false,
          'container_class' => '',
          'container_id' => '',
          'menu_class' => 'top-nav-list',
          'menu_id' => '',
          'echo' => false,
          'fallback_cb' => 'wp_page_menu',
          'before' => '',
          'after' => '',
          'link_before' => '',
          'link_after' => '',
          'items_wrap' => '<ul id="top-nav-list" class=" %2$s">%3$s</ul>',
          'depth' => 0,
          'walker' => ''
        ));
        echo $wdwt_menu;
        ?>
      </div>
    </nav>
  </div>
  <div id="menu-button-block">
    <div class="active_menu_responsive">
			
				<span style="display:inline-block; float:left; padding:0 10px;">
					<span id='trigram-for-heaven'></span>
				</span>
      <span style="position:relative; padding-right:7px;"><?php echo __('Menu', "news-magazine"); ?> </span>

    </div>
  </div>
  <?php
  if (is_plugin_active('slider-wd/slider-wd.php') && $show_slider_wd && function_exists("wd_slider")) {
    if ($show_slider != "Hide Slider" &&
      ((is_front_page() && $show_slider == "Only on Homepage") ||
        $show_slider == "On all the pages and posts")
    ) {
      ?>
      <div class="container">
        <?php wd_slider($slider_wd_id); ?>
      </div>
      <?php
    }
  } else {
    $wdwt_front->slideshow();
  }
  ?>
