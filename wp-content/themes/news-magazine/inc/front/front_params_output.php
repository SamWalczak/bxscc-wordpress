<?php

/* include  fornt end framework class */
require_once('WDWT_front_params_output.php');

class news_magazine_front extends WDWT_front
{


  /**
   * print Layout styles
   *
   */

  public function layout()
  {
    global $post;
    if (is_singular() && isset($post)) {
      /*get all the meta of the current theme for the post*/
      $meta = get_post_meta($post->ID, WDWT_META, true);
    } else {
      $meta = array();
    }
    $default_layout = $this->get_param('default_layout', $meta);
    $main_column = esc_html($this->get_param('main_column', $meta));
    $pwa_width = esc_html($this->get_param('pwa_width', $meta));
    $full_width = trim(esc_html($this->get_param('full_width', $meta)));
    $content_area_percent = esc_html($this->get_param('content_area_percent', $meta));
    $content_area_percent = (intval($content_area_percent) < 100 && intval($content_area_percent) >= 75) ? intval($content_area_percent) : 75;
    if ($full_width) {
      $them_content_are_width = '99';
      ?>
      <script>var full_width_magazine = 1</script><?php echo "\r\n";
    } else {
      $them_content_are_width = $content_area_percent;
      ?>
      <script> var full_width_magazine = 0</script><?php echo "\r\n";
    }

    switch ($default_layout) :
      case 1:
        ?>
        <style type="text/css">
          #sidebar1,
          #sidebar2 {
            display: none;
          }

          #blog {
            display: block;
            float: left;
          }

          .container, #blog {
            width: <?php echo $them_content_are_width; ?>%;
          }
        </style>
        <?php
        break;
      case 2:
        ?>
        <style type="text/css">
          #sidebar2 {
            display: none;
          }

          #sidebar1 {
            display: block;
            float: right;
          }

          .container {
            width: <?php echo $them_content_are_width; ?>%;
          }

          .blog, #content {
            width: <?php echo $main_column ; ?>%;
            display: block;
            float: left;
          }

          #sidebar1 {
            width: <?php echo (100 - $main_column); ?>%;
          }
        </style>
        <?php
        break;
      case 3:
        ?>
        <style type="text/css">
          #sidebar2 {
            display: none;
          }

          #sidebar1 {
            display: block;
            float: left;
          }

          .container {
            width: <?php echo $them_content_are_width; ?>%;
          }

          .blog, #content {
            width: <?php echo $main_column ; ?>%;
            display: block;
            float: left;
          }

          #sidebar1 {
            width: <?php echo (100 -  $main_column); ?>%;
          }
        </style>
        <?php
        break;
      case 4:
        ?>
        <style type="text/css">
          #sidebar2 {
            display: block;
            float: right;
          }

          #sidebar1 {
            display: block;
            float: right;
          }

          .container {
            width: <?php echo $them_content_are_width; ?>%;
          }

          .blog, #content {
            width: <?php echo $main_column ; ?>%;
            display: block;
            float: left;
          }

          #sidebar1 {
            width: <?php if(isset($pwa_width)) echo $pwa_width ; ?>%;
          }

          #sidebar2 {
            width: <?php echo (100 -  $pwa_width - $main_column); ?>%;
          }
        </style>
        <?php
        break;
      case 5:
        ?>
        <style type="text/css">
          #sidebar2 {
            display: block;
            float: left;
          }

          #sidebar1 {
            display: block;
            float: left;
          }

          .container {
            width: <?php echo $them_content_are_width; ?>%;
          }

          .blog, #content {
            width: <?php echo $main_column ; ?>%;
            display: block;
            float: right;
          }

          #sidebar1 {
            width: <?php echo $pwa_width ; ?>%;
          }

          #sidebar2 {
            width: <?php echo (100 - $pwa_width - $main_column); ?>%;
          }
        </style>
        <?php
        break;
      case 6:
        ?>
        <style type="text/css">
          #sidebar2 {
            display: block;
            float: right;
          }

          #sidebar1 {
            display: block;
            float: left;
          }

          .container {
            width: <?php echo $them_content_are_width; ?>%;
          }

          .blog, #content {
            width: <?php echo $main_column; ?>%;
            display: block;
            float: left;
          }

          #sidebar1 {
            width: <?php echo $pwa_width; ?>%;
          }

          #sidebar2 {
            width: <?php echo (100 - $pwa_width - $main_column); ?>%;
          }
        </style>
        <?php
        break;
    endswitch;
  }


  /**
   *    FRONT END COLOR CONTROL
   */

  public function color_control()
  {

    $background_color = get_theme_mod('background_color', 'FFFFFF');

    $color_scheme = esc_html($this->get_param('[colors_active][active]'));
    $menu_elem_back_color = esc_html($this->get_param('[colors_active][colors][menu_elem_back_color][value]', array(), "#FFFFFF"));
    $slider_back_color = esc_html($this->get_param('[colors_active][colors][slider_back_color][value]', array(), "#F9F9F9"));
    $slider_text_color = esc_html($this->get_param('[colors_active][colors][slider_text_color][value]', array(), "#FFFFFF"));
    $sideb_background_color = esc_html($this->get_param('[colors_active][colors][sideb_background_color][value]', array(), "#FFFFFF"));
    $footer_sideb_background_color = esc_html($this->get_param('[colors_active][colors][footer_sideb_background_color][value]', array(), "#F9F9F9"));
    $footer_back_color = esc_html($this->get_param('[colors_active][colors][footer_back_color][value]', array(), "#efefef"));
    $home_top_posts_color = esc_html($this->get_param('[colors_active][colors][home_top_posts_color][value]', array(), "#FFFFFF"));
    $cat_tab_backgr_color = esc_html($this->get_param('[colors_active][colors][cat_tab_backgr_color][value]', array(), "#0E78A6"));
    $top_posts_color = esc_html($this->get_param('[colors_active][colors][top_posts_color][value]', array(), "#FFFFFF"));
    $primary_text_headers_color = esc_html($this->get_param('[colors_active][colors][primary_text_headers_color][value]', array(), "#025a7e"));
    $block_text_color = esc_html($this->get_param('[colors_active][colors][block_text_color][value]', array(), "#ffffff"));
    $text_headers_color = esc_html($this->get_param('[colors_active][colors][text_headers_color][value]', array(), "#025a7e"));
    $primary_text_color = esc_html($this->get_param('[colors_active][colors][primary_text_color][value]', array(), "#232323"));
    $footer_text_color = esc_html($this->get_param('[colors_active][colors][footer_text_color][value]', array(), "#565656"));
    $primary_links_color = esc_html($this->get_param('[colors_active][colors][primary_links_color][value]', array(), "#074a7a"));
    $primary_links_hover_color = esc_html($this->get_param('[colors_active][colors][primary_links_hover_color][value]', array(), "#025a7e"));
    $menu_links_color = esc_html($this->get_param('[colors_active][colors][menu_links_color][value]', array(), "#232323"));
    $menu_links_hover_color = esc_html($this->get_param('[colors_active][colors][menu_links_hover_color][value]', array(), "#FFFFFF"));
    $menu_color = esc_html($this->get_param('[colors_active][colors][menu_color][value]', array(), "#000000"));
    $selected_menu_color = esc_html($this->get_param('[colors_active][colors][selected_menu_color][value]', array(), "#FFFFFF"));
    $selected_menu_item_color = esc_html($this->get_param('[colors_active][colors][selected_menu_item_color][value]', array(), "#0977A4"));
    $logo_text_color = esc_html($this->get_param('[colors_active][colors][logo_text_color][value]', array(), "#8F8F8F"));
    $meta_info_color = esc_html($this->get_param('[colors_active][colors][meta_info_color][value]', array(), "#484848"));
    $date_bg_color = esc_html($this->get_param('[colors_active][colors][date_bg_color][value]', array(), "#0480B4"));
    $lightbox_bg_color = esc_html($this->get_param('[colors_active][colors][lightbox_bg_color][value]', array(), "#ffffff"));
    $lightbox_ctrl_cont_bg_color = esc_html($this->get_param('[colors_active][colors][lightbox_ctrl_cont_bg_color][value]', array(), "#000000"));
    $lightbox_title_color = esc_html($this->get_param('[colors_active][colors][lightbox_title_color][value]', array(), "#000000"));
    $lightbox_ctrl_btn_color = esc_html($this->get_param('[colors_active][colors][lightbox_ctrl_btn_color][value]', array(), "#ffffff"));
    $lightbox_close_rl_btn_hover_color = esc_html($this->get_param('[colors_active][colors][lightbox_close_rl_btn_hover_color][value]', array(), "#cccccc"));
    ?>
    <style type="text/css">
      h1, h2, h3, h4, h5, h6, h1 > a, h2 > a, h3 > a, h4 > a, h5 > a, h6 > a, h1 > a:link, h2 > a:link, h3 > a:link, h4 > a:link, h5 > a:link, h6 > a:link, h1 > a:hover, h2 > a:hover, h3 > a:hover, h4 > a:hover, h6 > a:hover, h1 > a:visited, h2 > a:visited, h3 > a:visited, h4 > a:visited, h5 > a:visited, h6 > a:visited {
        color: <?php echo $text_headers_color; ?>;
      }

      .page-header span:not(.vcard) {
        background-color: <?php echo "#".get_background_color(); ?>;
      }

      .widget-area > h3, .widget-area > h2 {
        color: <?php echo $text_headers_color; ?>;
      }

      h2 > a, h2 > a:link, h2 > a:hover, h2 > a:visited, #sidebar1 .widget-container h3, .most_categories h3 > a {
        color: <?php echo $primary_text_headers_color; ?>;
      }

      .page-header {
        border-bottom: 2px solid <?php echo $text_headers_color; ?>;
      }

      .most-categories-header {
        border-bottom: 2px solid <?php echo $primary_text_headers_color; ?>;
      }

      .most-categories-header a {
        color: <?php echo $primary_text_headers_color; ?>;
        background: # <?php echo $background_color; ?> !important;
      }

      #logo h1 {
        color: <?php echo $logo_text_color; ?>
      }

      #slideshow {
        background-color: <?php echo $slider_back_color; ?>;
      }

      .bwg_slideshow_description_text, .bwg_slideshow_description_text *, .bwg_slideshow_title_text * {
        color: <?php echo $slider_text_color; ?>;
      }

      a:link.site-title-a, a:hover.site-title-a, a:visited.site-title-a, a.site-title-a {
        color: <?php echo $logo_text_color;?>;
      }

      .read_more, #commentform #submit, .reply {
        color: <?php echo $menu_links_color;?>;
        background-color: <?php echo $menu_elem_back_color; ?>;
      }

      /*
      .reply a{
         color:
      <?php echo $menu_links_color;?>
      !important;
      }
      */
      .read_more:hover, #commentform #submit:hover, .reply:hover, .reply a:hover {
        color: <?php echo $menu_links_color; ?> !important;
        background-color: <?php echo $menu_elem_back_color; ?>;
      }

      .more-link {
        color: <?php echo $menu_links_color;?> !important;
      }

      #footer-bottom {
        background-color: <?php echo $footer_back_color; ?>;
      }

      #footer {
        background-color: <?php echo $footer_sideb_background_color; ?>;
      }

      .container.device {
        background-color: <?php echo $footer_back_color; ?>;
      }

      body {
        color: <?php echo $primary_text_color; ?>;
      }

      #wrapper {
        color: <?php echo $primary_text_color; ?>;
      }

      .container.device, #footer-bottom {
        color: <?php echo $footer_text_color; ?>;
      }

      a:link, a:visited,
      .content-inner-block ul li h3 > a {
        text-decoration: none;
        color: <?php echo $primary_links_color; ?>;
      }

      .top-nav-list .current-menu-item {
        color: <?php echo $menu_links_hover_color; ?> !important;
        background-color: <?php echo  $selected_menu_color; ?>;
      }

      .get_title {
        background-color: <?php echo $menu_color; ?>;
        background-image: url(<?php get_template_directory_uri(); ?>/images/Shado.png);
        background-position: bottom;
        background-repeat: no-repeat;
      }

      .sub-menu li.current-menu-item,
      .sub-menu li.current_page_item,
      .haschild li.current-menu-item,
      .haschild li.current_page_item {
        background-color: <?php echo $menu_color; ?> !important;
      }

      .top-nav-list li.current-menu-item, .top-nav-list li.current_page_item {
        color: <?php echo $primary_links_hover_color ?>;
        background-color: <?php echo $selected_menu_color; ?> !important;
      }

      .top-nav-list li.current-menu-item > a, .top-nav-list li.current_page_item > a, #top-nav-list li.current-menu-item > a, #top-nav-list li.current_page_item > a {
        color: <?php echo $selected_menu_item_color; ?> !important;
      }

      .top-nav-list li:hover {
        background-color: <?php echo $menu_color; ?> !important;
      }

      a.read_more:visited, a.read_more:link, .read_more, .more-link, #searchsubmit, a .page-links-number, .post-password-form input[type="submit"], .more-link {
        background-color: <?php echo $primary_text_headers_color; ?>;
        color: <?php echo $block_text_color; ?>;
      }

      ::selection {
        background-color: <?php echo $primary_text_headers_color; ?>;
        color: <?php echo $block_text_color; ?>;
      }

      #wd-categories-vertical-tabs ul.tabs li:hover h3, #wd-categories-vertical-tabs ul.tabs li:hover span {
        color: <?php echo $block_text_color; ?>;
      }

      #wd-categories-vertical-tabs ul.tabs li.active a h3, #wd-categories-vertical-tabs ul.tabs li.active a span {
        color: <?php echo $block_text_color; ?>;
      }

      #top-posts-list li, #latest-news, .post-date + img {
        border-top: 2px solid <?php echo $primary_text_headers_color; ?>;
      }

      .sidebar-container .widget-area ul li:before, .news_categories ul li:before {
        border-left: solid <?php echo $primary_text_headers_color; ?>;
        border-width: 6px;
      }

      .arrow-left {
        border: 2px solid <?php echo $primary_text_headers_color; ?>;
      }

      .arrow-right {
        border-left: 5px solid <?php echo $primary_text_headers_color; ?>;
      }

      #menu-button-block {
        border-left: 3px solid <?php echo $menu_links_hover_color; ?>;
      }

      #search-input::-webkit-input-placeholder, #search-input, .widget-area > h3, .widget-area > h2, .sep, .sitemap h3, .comment-author .fn, .tab-more, #latest-news + .page-navigation {
        color: <?php echo $primary_text_headers_color; ?> !important;
      }

      .widget-area > h3, .widget-area > h2,
      .content-inner-block h2 {
        border-bottom: 3px solid #E8E8E8;
        color: <?php echo $primary_text_headers_color; ?> !important;
      }

      #top-nav {
        background: <?php echo $menu_elem_back_color; ?>;
      }

      #commentform #submit, .reply, #reply-title small {
        background-color: <?php echo $primary_text_headers_color; ?>;
        color: <?php echo $block_text_color;?>;
      }

      .reply a, #reply-title small a {
        color: <?php echo $block_text_color;?>;
      }

      #top-nav-list > li ul, .top-nav-list > ul > li ul {
        background: <?php echo $menu_elem_back_color; ?>;
      }

      .page-header a, .page-header span {
        background: # <?php echo $background_color; ?> !important;
      }

      .sidebar-container, #latest-news > h2 {
        background-color: <?php echo $sideb_background_color; ?>;
      }

      .commentlist li {
        background-color: <?php echo $sideb_background_color; ?>;
      }

      .children .comment {
        background-color: # <?php echo $this->ligther($sideb_background_color,37); ?>;
      }

      #respond {
        background-color: # <?php echo $this->ligther($sideb_background_color,37); ?>;
      }

      .entry-meta *, .entry-meta-cat * {
        color: <?php echo $meta_info_color; ?> !important;
      }

      #top-posts {
        background-color: <?php echo $top_posts_color; ?>;
      }

      #top-nav-list > li > a, .top-nav-list > ul > li > a {
        color: <?php echo $menu_links_color ?>;
      }

      #menu-button-block {
        color: <?php echo $menu_links_color ?>;
      }

      #top-nav-list > li ul > li > a, .top-nav-list > li ul > li > a {
        color: <?php echo $menu_links_color ?> !important;
        border-top: 1px solid <?php echo '#'.$this->ligther($menu_color,20); ?> !important;
      }

      #menu-button-block a, #menu-button-block a {
        color: <?php echo $menu_links_color ?> !important;
      }

      #top-nav-list > li:hover > a, #top-nav-list > li ul > li > a:hover, .top-nav-list li:hover a, .top-nav-list li ul li a:hover {
        color: <?php echo $menu_links_hover_color; ?> !important;
      }

      #wd-categories-vertical-tabs ul.tabs li a:focus, #wd-categories-vertical-tabs ul.tabs li a:active {
        color: <?php echo $block_text_color; ?> !important;
      }

      #wd-categories-vertical-tabs ul.tabs li.active {

      color: {
        tabsActiveColor
      }

      ;
      background:

      <?php echo $primary_text_headers_color; ?>
      ;
      }

      #wd-categories-vertical-tabs .tabs-block {
        border-right: 4px solid <?php echo $primary_text_headers_color; ?>;
      }

      #wd-categories-vertical-tabs {
        background-color: <?php echo $home_top_posts_color; ?>;
      }

      #wd-categories-vertical-tabs ul.tabs li.active,
      #wd-categories-vertical-tabs ul.tabs li:hover {
        background-color: <?php echo $cat_tab_backgr_color; ?>;
      }

      .tabs-block {
        border-right: 5px solid <?php echo $cat_tab_backgr_color; ?>;
        border-left: 1px solid #ddd;
      }

      #wd-categories-vertical-tabs ul.tabs li.active a *,
      #wd-categories-vertical-tabs ul.tabs li:hover a * {
        color: <?php echo $menu_links_hover_color; ?>;
      }

      #header_part #latest_posts b {
        color: <?php echo $primary_text_headers_color; ?> !important;
      }

      #header-block {
        background-color: <?php echo $menu_color; ?>;
      }

      .top-nav-list .current-menu-item {
        color: <?php echo $menu_links_hover_color; ?> !important;
        background-color: <?php echo  $selected_menu_color; ?>;
      }

      a:hover {
        color: <?php echo $primary_links_hover_color; ?>;
      }

      #menu-button-block {
        background-color: <?php echo $menu_elem_back_color; ?>;
      }

      .blog.page-news .news-post {
        border-bottom: 1px solid <?php echo $menu_elem_back_color; ?>;
      }

      .most_categories ul li:before {
        border-left: solid <?php echo $menu_elem_back_color; ?>;
      }

      #top-posts-list .date,
      .date.lat_news,
      .blog-post .date,
      .most_pop_date.date {
        background-color: <?php echo $primary_text_headers_color; ?>;
        color: <?php echo $block_text_color; ?>;
      }

      #year,
      .date.lat_news, .blog-post .date,
      .date.most_pop_date {
        background: <?php echo $date_bg_color; ?>;
      }

      @media only screen and (max-width: 767px) {
        #header .phone-menu-block {
          background-color: <?php echo $menu_color; ?>;
        }

        .top-nav-list li ul li:hover > a, .top-nav-list li ul li > a:hover, .top-nav-list li ul li > a:focus, .top-nav-list li ul li > a:active, .top-nav-list li.current-menu-item > a:hover, #top-nav-list > li ul li.current-menu-item, #top-nav-list > li ul li.current_page_item {
          color: <?php echo $primary_links_hover_color ?>;
          background-color: <?php echo $selected_menu_color; ?>;
        }

        .phone-menu-block:before {
          border-left: solid <?php echo $menu_links_hover_color; ?>;
          border-width: 9px;
        }

        #top-nav > div > ul li ul {
          background: <?php echo $menu_color; ?> !important;
        }

        #top-nav > div > ul, #top-nav > div ul {
          background: <?php echo $menu_elem_back_color; ?> !important;
        }

        #top-nav {
          background: none !important;
        }

        #top-nav > li > a, #top-nav > li > a:link, #top-nav > li > a:visited {
          color: <?php echo $menu_links_color; ?>;
          background: <?php echo $menu_elem_back_color; ?>;
        }

        .top-nav-list > li:hover > a, .top-nav-list > li > a:hover, .top-nav-list > li > a:focus, .top-nav-list > li > a:active {
          color: <?php echo $menu_links_hover_color; ?> !important;
          background-color: <?php echo $menu_color; ?> !important;
        }

        .top-nav-list li ul li > a, .top-nav-list li ul li > a:link, .top-nav-list li ul li > a:visited {
          color: <?php echo $menu_links_color ?> !important;
        }

        .top-nav-list li ul li:hover > a, .top-nav-list li ul li > a:hover, .top-nav-list li ul li > a:focus, .top-nav-list li ul li > a:active {
          color: <?php echo $menu_links_hover_color; ?> !important;
          background-color: <?php echo $menu_color; ?> !important;
        }

        .top-nav-list li.has-sub > a, .top-nav-list li.has-sub > a:link, .top-nav-list li.has-sub > a:visited {
          background: <?php echo $menu_elem_back_color; ?> url(<?php echo get_template_directory_uri(); ?>/images/arrow.menu.png) right top no-repeat !important;
        }

        .top-nav-list li.has-sub:hover > a, .top-nav-list li.has-sub > a:hover, .top-nav-list li.has-sub > a:focus, .top-nav-list li.has-sub > a:active {
          background: <?php echo $menu_elem_back_color; ?> url(<?php echo get_template_directory_uri(); ?>/images/arrow.menu.png) right top no-repeat !important;
        }

        .top-nav-list li ul li.has-sub > a, .top-nav-list li ul li.has-sub > a:link, .top-nav-list li ul li.has-sub > a:visited {
          background: <?php echo $menu_elem_back_color; ?> url(<?php echo get_template_directory_uri(); ?>/images/arrow.menu.png) right -18px no-repeat !important;
        }

        .top-nav-list li ul li.has-sub:hover > a, .top-nav-list li ul li.has-sub > a:hover, .top-nav-list li ul li.has-sub > a:focus, .top-nav-list li ul li.has-sub > a:active {
          background: <?php echo '#'.$this->ligther($menu_elem_back_color,15); ?> url(<?php echo get_template_directory_uri(); ?>/images/arrow.menu.png) right -18px no-repeat !important;
        }

        #top-nav-list > li ul li, .top-nav-list > ul > li ul li {
          background-color: <?php echo '#'.$this->ligther($selected_menu_color,10); ?>;
        }

        .top-nav-list > li.current-menu-ancestor > a:hover, .top-nav-list > li.current-menu-item > a:focus, .top-nav-list > li.current-menu-item > a:active {
          color: <?php echo $menu_links_color ?> !important;
          background-color: <?php echo $menu_elem_back_color; ?> !important;
        }

        .top-nav-list li.current-menu-item > a, .top-nav-list li.current-menu-item > a:visited {
          color: <?php echo $primary_links_hover_color ?> !important;
          background-color: <?php echo $selected_menu_color; ?> !important;
        }

        #top-nav-list > li ul > li.current-menu-item > a, .top-nav-list > li ul > li.current-menu-item > a {
          color: <?php echo $primary_links_hover_color ?> !important;
          background: none !important;
        }

        #top-nav-list li, #top-nav-list > li ul li, .top-nav-list > li ul li, .top-nav-list li {
          border-bottom: 1px solid <?php echo $menu_color; ?> !important;
        }

        .top-nav-list li.current-menu-parent > a, .top-nav-list li.current-menu-parent > a:link, .top-nav-list li.current-menu-parent > a:visited, .top-nav-list li.current-menu-parent > a:hover, .top-nav-list li.current-menu-parent > a:focus, .top-nav-list li.current-menu-parent > a:active, .top-nav-list li.has-sub.current-menu-item > a, .top-nav-list li.has-sub.current-menu-item > a:link, .top-nav-list li.has-sub.current-menu-item > a:visited, .top-nav-list li.has-sub.current-menu-ancestor > a:hover, .top-nav-list li.has-sub.current-menu-item > a:focus, .top-nav-list li.has-sub.current-menu-item > a:active,
        .top-nav-list li.current-menu-ancestor > a, .top-nav-list li.current-menu-ancestor > a:link, .top-nav-list li.current-menu-ancestor > a:visited, .top-nav-list li.current-menu-ancestor > a:hover, .top-nav-list li.current-menu-ancestor > a:focus, .top-nav-list li.current-menu-ancestor > a:active {
          color: <?php echo $menu_links_color ?> !important;
          background: url(<?php echo get_template_directory_uri(); ?>/images/arrow_menu_left.png) 97% center no-repeat !important;
        }

        .top-nav-list li ul li.current-menu-item > a:visited, .top-nav-list li ul li.current-menu-ancestor > a:hover, .top-nav-list li ul li.current-menu-item > a:focus, .top-nav-list li ul li.current-menu-item > a:active {
          color: <?php echo $menu_links_color ?> !important;
          background-color: <?php echo '#'.$this->ligther($menu_elem_back_color,15); ?> !important;
        }

        .top-nav-list li ul li.current-menu-parent > a, .top-nav-list li ul li.current-menu-parent > a:link, .top-nav-list li ul li.current-menu-parent > a:visited, .top-nav-list li ul li.current-menu-parent > a:hover, .top-nav-list li ul li.current-menu-parent > a:focus, .top-nav-list li ul li.current-menu-parent > a:active, .top-nav-list li ul li.has-sub.current-menu-item > a, .top-nav-list li ul li.has-sub.current-menu-item > a:link, .top-nav-list li ul li.has-sub.current-menu-item > a:visited,
        .top-nav-list li ul li.has-sub.current-menu-ancestor > a:hover, .top-nav-list li ul li.has-sub.current-menu-item > a:focus, .top-nav-list li ul li.has-sub.current-menu-item > a:active,
        .top-nav-list li ul li.current-menu-ancestor > a, .top-nav-list li ul li.current-menu-ancestor > a:link, .top-nav-list li ul li.current-menu-ancestor > a:visited, .top-nav-list li ul li.current-menu-ancestor > a:hover, .top-nav-list li ul li.current-menu-ancestor > a:focus, .top-nav-list li ul li.current-menu-ancestor > a:active {
          color: <?php echo $menu_links_color ?> !important;
          background: <?php echo '#'.$this->ligther($menu_elem_back_color,15); ?> url(<?php echo get_template_directory_uri(); ?>/images/arrow.menu.png) right -158px no-repeat !important;
        }

        #wd-categories-vertical-tabs .content-block {
          border-top: 1px solid <?php echo $primary_text_headers_color; ?>;
        }

        #wd-categories-vertical-tabs .tabs-block {
          background-color: <?php echo $primary_text_headers_color; ?>;
        }

        #wd-categories-vertical-tabs .arrows-block .arrow-up a,
        #wd-categories-vertical-tabs .arrows-block .arrow-down a {
          background-color: <?php echo $cat_tab_backgr_color; ?>;
        }

      }
    </style>
    <?php
  }


  public function slideshow()
  {
    $show_slider = $this->get_param('show_slider');
    $imgs_url = $this->get_param('slider_head');
    $image_textarea = $this->get_param('slider_head_desc');
    $logo_text = $this->get_param('logo_text');
    $image_title = $this->get_param('slider_head_title');
    $image_height = $this->get_param('image_height');
    $title_position = $this->get_param('title_position');
    $description_position = $this->get_param('description_position');
    $imgs_href = $this->get_param('slider_head_href');

    $imgs_url = explode('||wd||', $imgs_url);
    $imgs_href = explode('||wd||', $imgs_href);
    $image_title = explode('||wd||', $image_title);
    $image_textarea = explode('||wd||', $image_textarea);
    $imgs_number = count($imgs_url);
    $imgs_number = (array) $imgs_number;
    /*clear from spaces etc */

    foreach ($imgs_url as $i => $url) {
      $imgs_url[$i] = trim($url);
    }
    for ($i = 0; $i < count($imgs_number); $i++) {
      $imgs_href[$i] = isset($imgs_href[$i]) ? trim($imgs_href[$i]) : '';
      $image_title[$i] = isset($image_title[$i]) ? trim($image_title[$i]) : '';
      $image_textarea[$i] = isset($image_textarea[$i]) ? trim($image_textarea[$i]) : '';
    }

    if (is_array($show_slider)) $slider_show = $show_slider[0];
    else $slider_show = $show_slider;

    if (($slider_show != "Hide Slider" && ((is_front_page() && $slider_show == "Only on Homepage") || $slider_show == "On all the pages and posts")) && count($imgs_url) && is_array($imgs_url)) { ?>
      <script>
        var data = [];
        var event_stack = [];


        <?php

        if ($imgs_url && is_array($imgs_url))
          $link_array = $imgs_url;
        else
          $link_array = array();

        for ($i = 0; $i < count($link_array); $i++) {
          echo 'data["' . $i . '"]=[];';
        }

        for ($i = 0; $i < count($link_array); $i++) {
          echo 'data["' . $i . '"]["id"]="' . $i . '";';
          echo 'data["' . $i . '"]["image_url"]="' . $link_array[$i] . '";';
        }

        if ($image_textarea && is_array($image_textarea))
          $textarea_array = $image_textarea;
        else
          $textarea_array = array();

        for ($i = 0; $i < count($textarea_array); $i++) {
          echo 'data["' . $i . '"]["description"]="' . str_replace(array("\n", "\r"), '', addslashes($textarea_array[$i])) . '";';
        }

        if ($image_title && is_array($image_title))
          $title_array = $image_title;
        else
          $title_array = array();

        for ($i = 0; $i < count($title_array); $i++) {
          echo 'data["' . $i . '"]["alt"]="' . str_replace(array("\n", "\r"), '', $title_array[$i]) . '";';

        } ?>
      </script>

      <?php
      $slideshow_title_position = explode('-', trim($title_position[0]));
      $slideshow_description_position = explode('-', trim($description_position[0]));
      ?>
      <style>
        .bwg_slideshow_image_wrap {
          height: <?php echo esc_html( $image_height ); ?>px;
          width: 100% !important;
        }

        .bwg_slideshow_title_span {
          text-align: <?php echo esc_html( $slideshow_title_position[0] ); ?>;
          vertical-align: <?php echo esc_html( $slideshow_title_position[1] ); ?>;
        }

        .bwg_slideshow_description_span {
          text-align: <?php echo esc_html( $slideshow_description_position[0] ); ?>;
          vertical-align: <?php echo esc_html( $slideshow_description_position[1] ); ?>;
        }
      </style>

      <!--SLIDESHOW START-->
      <div id="slideshow">
        <div class="container">
          <div class="slider_contener_for_exklusive">
            <div class="bwg_slideshow_image_wrap" id="bwg_slideshow_image_wrap_id">
              <?php
              $current_image_id = 0;
              $current_pos = 0;
              $current_key = 0; ?>
              <!--################# DOTS ################# -->

              <a id="spider_slideshow_left"
                 onclick="wdwt_bwg_change_image(parseInt(jQuery('#bwg_current_image_key').val()), (parseInt(jQuery('#bwg_current_image_key').val()) - iterator()) >= 0 ? (parseInt(jQuery('#bwg_current_image_key').val()) - iterator()) % data.length : data.length - 1, data); return false;"><span
                  id="spider_slideshow_left-ico"><span><i class="bwg_slideshow_prev_btn fa"></i></span></span></a>
              <a id="spider_slideshow_right"
                 onclick="wdwt_bwg_change_image(parseInt(jQuery('#bwg_current_image_key').val()), (parseInt(jQuery('#bwg_current_image_key').val()) + iterator()) % data.length, data); return false;"><span
                  id="spider_slideshow_right-ico"><span><i class="bwg_slideshow_next_btn fa "></i></span></span></a>
              <!--################################## -->

              <!--################ IMAGES ################## -->
              <div id="bwg_slideshow_image_container" width="100%" class="bwg_slideshow_image_container">
                <div class="bwg_slide_container" width="100%">
                  <div class="bwg_slide_bg">
                    <div class="bwg_slider">
                      <?php
                      if ($imgs_href && is_array($imgs_href))
                        $href_array = $imgs_href;
                      else
                        $href_array = array();

                      if ($imgs_url && is_array($imgs_url))
                        $image_rows = $imgs_url;
                      else
                        $image_rows = array();
                      $i = 0;
                      foreach ($image_rows as $key => $image_row) {
                        if ($i == $current_image_id) {
                          $current_key = $key;
                          ?>
                          <span class="bwg_slideshow_image_span" id="image_id_<?php echo $i; ?>">
                <span class="bwg_slideshow_image_span1">
                  <span class="bwg_slideshow_image_span2">
            <a href="<?php echo esc_url($href_array[$i]); ?>">
              <img id="bwg_slideshow_image" class="bwg_slideshow_image" src="<?php echo esc_attr($image_row); ?>"
                   image_id="<?php echo $i; ?>"/>
            </a>
                  </span>
                </span>
              </span>
                          <input type="hidden" id="bwg_current_image_key" value="<?php echo $key; ?>"/>
                          <?php
                        } else {
                          ?>
                          <span class="bwg_slideshow_image_second_span" id="image_id_<?php echo $i; ?>">
                <span class="bwg_slideshow_image_span1">
                  <span class="bwg_slideshow_image_span2">
          <?php if (isset($image_row)) { ?>
            <a href="<?php echo esc_url($href_array[$i]); ?>"><img id="bwg_slideshow_image_second"
                                                                   class="bwg_slideshow_image"
                                                                   src="<?php echo esc_attr($image_row); ?>"/></a>
          <?php } ?>
                  </span>
                </span>
              </span>
                          <?php
                        }
                        $i++;
                      }
                      ?>
                    </div>
                  </div>
                </div>
              </div>

              <!--################ TITLE ################## -->
              <div class="bwg_slideshow_image_container" style="position: absolute;">
                <div class="bwg_slideshow_title_container">
                  <div style="display:table; margin:0 auto;">
            <span class="bwg_slideshow_title_span">
        <div class="bwg_slideshow_title_text <?php echo empty($title_array[0]) ? 'none' : '' ?>">
          <?php echo str_replace(array("\n", "\r"), '', $title_array[0]); ?>
         </div>
            </span>
                  </div>
                </div>
              </div>
              <!--################ DESCRIPTION ################## -->
              <div class="bwg_slideshow_image_container" style="position: absolute;">
                <div class="bwg_slideshow_title_container">
                  <div style="display:table; margin:0 auto;">
            <span class="bwg_slideshow_description_span">
              <div class="bwg_slideshow_description_text <?php echo empty($textarea_array[0]) ? 'none' : '' ?>">
                <?php echo str_replace(array("\n", "\r"), '', $textarea_array[0]); ?>        
        </div>
            </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!--SLIDESHOW END-->

      <?php
      $this->slider_script();
    }

  }

  public function header_social_buttons()
  {
    global $wdwt_front;
    $show_facebook_icon = $this->get_param('show_facebook_icon');
    $facebook_url = $this->get_param('facebook_url');
    $show_twitter_icon = $this->get_param('show_twitter_icon');
    $twitter_url = $this->get_param('twitter_url');
    $show_google_icon = $this->get_param('show_google_icon');
    $google_url = $this->get_param('google_url');
    $show_rss_icon = $this->get_param('show_rss_icon');
    $rss_url = $this->get_param('rss_url');

    $show_instagram_icon = $this->get_param('show_instagram_icon');
    $instagram_url = $this->get_param('instagram_url');
    $show_linkedin_icon = $this->get_param('show_linkedin_icon');
    $linkedin_url = $this->get_param('linkedin_url');
    $show_pinterest_icon = $this->get_param('show_pinterest_icon');
    $pinterest_url = $this->get_param('pinterest_url');
    $show_youtube_icon = $this->get_param('show_youtube_icon');
    $youtube_url = $this->get_param('youtube_url');
    ?>
    <ul id="social">
      <li class="facebook" <?php if ($show_facebook_icon != 'on' || $facebook_url == "") {
        echo "style=\"display:none;\"";
      } ?>>
        <a href="<?php if (trim($facebook_url)) {
          echo esc_url($facebook_url);
        } else {
          echo "javascript:;";
        } ?>" target="_blank" title="Facebook"><i class="fa fa-facebook"></i></a>
      </li>
      <li class="twitter" <?php if ($show_twitter_icon != 'on' || $twitter_url == "") {
        echo "style=\"display:none;\"";
      } ?>>
        <a href="<?php if (trim($twitter_url)) {
          echo esc_url($twitter_url);
        } else {
          echo "javascript:;";
        } ?>" target="_blank" title="Twitter"><i class="fa fa-twitter"></i></a>
      </li>
      <li class="gplus" <?php if ($show_google_icon != 'on' || $google_url == "") {
        echo "style=\"display:none;\"";
      } ?>>
        <a href="<?php if (trim($google_url)) {
          echo esc_url($google_url);
        } else {
          echo "javascript:;";
        } ?>" target="_blank" title="Google +"><i class="fa fa-google-plus"></i></a>
      </li>
      <li class="rss" <?php if ($show_rss_icon != 'on' || $rss_url == "") {
        echo "style=\"display:none;\"";
      } ?>>
        <a href="<?php if (trim($rss_url)) {
          echo esc_url($rss_url);
        } else {
          echo "javascript:;";
        } ?>" target="_blank" title="Rss"><i class="fa fa-rss"></i></a>
      </li>
      <li class="instagram" <?php if ($show_instagram_icon != 'on' || $instagram_url == "") {
        echo "style=\"display:none;\"";
      } ?>>
        <a href="<?php if (trim($instagram_url)) {
          echo esc_url($instagram_url);
        } else {
          echo "javascript:;";
        } ?>" target="_blank" title="Instagram"><i class="fa fa-instagram"></i></a>
      </li>
      <li class="linkedin" <?php if ($show_linkedin_icon != 'on' || $linkedin_url == "") {
        echo "style=\"display:none;\"";
      } ?>>
        <a href="<?php if (trim($linkedin_url)) {
          echo esc_url($linkedin_url);
        } else {
          echo "javascript:;";
        } ?>" target="_blank" title="LinkEdin"><i class="fa fa-linkedin"></i></a>
      </li>
      <li class="pinterest" <?php if ($show_pinterest_icon != 'on' || $pinterest_url == "") {
        echo "style=\"display:none;\"";
      } ?>>
        <a href="<?php if (trim($pinterest_url)) {
          echo esc_url($pinterest_url);
        } else {
          echo "javascript:;";
        } ?>" target="_blank" title="Pinterest"><i class="fa fa-pinterest"></i></a>
      </li>
      <li class="youtube" <?php if ($show_youtube_icon != 'on' || $youtube_url == "") {
        echo "style=\"display:none;\"";
      } ?>>
        <a href="<?php if (trim($youtube_url)) {
          echo esc_url($youtube_url);
        } else {
          echo "javascript:;";
        } ?>" target="_blank" title="YouTube"><i class="fa fa-youtube"></i></a>
      </li>
    </ul>


  <?php }

  public function header_latest_posts()
  {
    global $wdwt_front;
    $latest_posts_enable = $this->get_param('latest_posts_enable');
    $latest_posts_text = $this->get_param('latest_posts_text');
    $latest_posts_count = $this->get_param('latest_posts_count');
    $top_post_categories = implode(',', $this->get_param('latest_post_categories', array(), array('')));

//var_dump($latest_post_categories);
    if ($latest_posts_enable && $latest_posts_count > 0) {
      $recent_posts = wp_get_recent_posts(array('category' => $top_post_categories, 'numberposts' => $latest_posts_count, 'post_status' => 'publish')); /* BXSCC 2026-10: moved inside the if, so the query runs only when the ticker is on */
      ?>
      <div id="latest_posts">
        <b><?php echo $latest_posts_text; ?></b>
        <ul>
          <?php
          foreach ($recent_posts as $recent) {
            echo '<li><a href="' . get_permalink($recent["ID"]) . '">' . $recent["post_title"] . '</a> </li> ';
          }
          ?>
        </ul>
      </div>
      <script>
        var wdwt_active_post = 1;
        jQuery("#header_part #latest_posts ul li").eq(0).addClass("active_post");
        if (jQuery("#header_part #latest_posts ul li").length > wdwt_active_post)
          setInterval(function ()
          {
            jQuery("#header_part #latest_posts ul li").removeClass("active_post");
            jQuery("#header_part #latest_posts ul li").eq(wdwt_active_post).addClass("active_post");
            var wdwt_margin_latestpost = wdwt_active_post * 20;
            jQuery("#header_part #latest_posts ul li").eq(0).animate({marginTop: -wdwt_margin_latestpost + "px"}, 500);
            if (wdwt_active_post === jQuery("#header_part #latest_posts ul li").length - 1) {
              wdwt_active_post = 0;
            } else {
              wdwt_active_post++;
            }
          }, 8000);
      </script>
    <?php }
  }

  /*------------Display logo image or text -------------------*/
  public function logo()
  {
    global $wdwt_front;
    $logo_type = $this->get_param('logo_type');
    $logo_img = esc_url(trim($this->get_param('logo_img')));

    $color_scheme = $this->get_param('[colors_active][active]', $meta_array = array(), $default = 0);
    if ($color_scheme == 1 && $logo_img == WDWT_IMG . 'logo1.png') {
      $logo_img = WDWT_IMG . 'logo2.png';
    }
    if ($color_scheme == 2 && $logo_img == WDWT_IMG . 'logo1.png') {
      $logo_img = WDWT_IMG . 'logo3.png';
    }
    if ($color_scheme == 3 && $logo_img == WDWT_IMG . 'logo1.png') {
      $logo_img = WDWT_IMG . 'logo4.png';
    }
    if ($color_scheme == 4 && $logo_img == WDWT_IMG . 'logo1.png') {
      $logo_img = WDWT_IMG . 'logo5.png';
    }
    $logo_text = get_bloginfo('name', 'display');
    ?>

    <?php
    if ($logo_type == 'image'): ?>
      <a id="logo" href="<?php echo esc_url(home_url('/')); ?>" title="<?php echo esc_attr($logo_text); ?>">
        <img id="site-title" src="<?php echo $logo_img; ?>" alt="logo">
      </a>
    <?php elseif ($logo_type == 'text'): ?>
      <a id="logo" href="<?php echo esc_url(home_url('/')); ?>" title="<?php echo esc_attr($logo_text); ?>">
        <h1><?php echo esc_html($logo_text); ?></h1>
      </a>
      <?php
    endif; ?>

    <?php
  }

  public function slider_script()
  {

    $animation_speed = $this->get_param('animation_speed');
    $effect = $this->get_param('effect');
    if (empty($effect[0])) {
      $effect = array('fade');
    }

    $image_height = $this->get_param('image_height');
    $stop_on_hover = $this->get_param('stop_on_hover');
    $slideshow_interval = $this->get_param('slideshow_interval');
    $imgs_url = $this->get_param('slider_head');
    $imgs_url = explode('||wd||', $imgs_url);
    if ($stop_on_hover === true)
      $stop_on_hover = 1;
    else
      $stop_on_hover = 0;

    ?>

    <script>

      var wdwts_trans_in_progress = false;
      var wdwts_transition_duration =  <?php echo $animation_speed; ?>;
      var wdwts_playInterval;
      var kkk = 1;
      /* Stop autoplay.*/
      window.clearInterval(wdwts_playInterval);
      /* Set watermark container size.*/
      var wdwts_current_key = '';


      function bwg_testBrowser_cssTransitions()
      {
        return wdwts_testDom('Transition');
      }
      function bwg_testBrowser_cssTransforms3d()
      {
        return wdwts_testDom('Perspective');
      }
      function wdwts_testDom(prop)
      {
        /* Browser vendor CSS prefixes.*/
        var browserVendors = ['', '-webkit-', '-moz-', '-ms-', '-o-', '-khtml-'];
        /* Browser vendor DOM prefixes.*/
        var domPrefixes = ['', 'Webkit', 'Moz', 'ms', 'O', 'Khtml'];
        var i = domPrefixes.length;
        while (i--) {
          if (typeof document.body.style[domPrefixes[i] + prop] !== 'undefined') {
            return true;
          }
        }
        return false;
      }
      function bwg_cube(tz, ntx, nty, nrx, nry, wrx, wry, current_image_class, next_image_class, direction)
      {

        /* If browser does not support 3d transforms/CSS transitions.*/
        if (!bwg_testBrowser_cssTransitions()) {
          return bwg_fallback(current_image_class, next_image_class, direction);
        }
        if (!bwg_testBrowser_cssTransforms3d()) {
          return bwg_fallback3d(current_image_class, next_image_class, direction);
        }
        wdwts_trans_in_progress = true;
        /* Set active thumbnail.*/
        jQuery(".bwg_slide_bg").css('perspective', 1000);
        jQuery(current_image_class).css({
          transform: 'translateZ(' + tz + 'px)',
          backfaceVisibility: 'hidden'
        });
        jQuery(next_image_class).css({
          opacity: 1,
          filter: 'Alpha(opacity=100)',
          backfaceVisibility: 'hidden',
          transform: 'translateY(' + nty + 'px) translateX(' + ntx + 'px) rotateY(' + nry + 'deg) rotateX(' + nrx + 'deg)'
        });
        jQuery(".bwg_slider").css({
          transform: 'translateZ(-' + tz + 'px)',
          transformStyle: 'preserve-3d'
        });
        /* Execution steps.*/
        setTimeout(function ()
        {
          jQuery(".bwg_slider").css({
            transition: 'all ' + wdwts_transition_duration + 'ms ease-in-out',
            transform: 'translateZ(-' + tz + 'px) rotateX(' + wrx + 'deg) rotateY(' + wry + 'deg)'
          });
        }, 20);
        /* After transition.*/
        jQuery(".bwg_slider").one('webkitTransitionEnd transitionend otransitionend oTransitionEnd mstransitionend', jQuery.proxy(bwg_after_trans));
        function bwg_after_trans()
        {
          jQuery(current_image_class).removeAttr('style');
          jQuery(next_image_class).removeAttr('style');
          jQuery(".bwg_slider").removeAttr('style');
          jQuery(".bwg_slide_bg").css('perspective', 'none');
          jQuery(current_image_class).css({'opacity': 0, filter: 'Alpha(opacity=0)', 'z-index': 1});
          jQuery(next_image_class).css({'opacity': 1, filter: 'Alpha(opacity=100)', 'z-index': 2});
          wdwts_trans_in_progress = false;
          if (typeof event_stack !== 'undefined' && event_stack.length > 0) {
            key = event_stack[0].split("-");
            event_stack.shift();
              wdwt_bwg_change_image(key[0], key[1], data, true);
          }
        }
      }
      function bwg_cubeH(current_image_class, next_image_class, direction)
      {
        /* Set to half of image width.*/
        var dimension = jQuery(current_image_class).width() / 2;
        if (direction == 'right') {
          bwg_cube(dimension, dimension, 0, 0, 90, 0, -90, current_image_class, next_image_class, direction);
        }
        else
          if (direction == 'left') {
            bwg_cube(dimension, -dimension, 0, 0, -90, 0, 90, current_image_class, next_image_class, direction);
          }
      }
      function bwg_cubeV(current_image_class, next_image_class, direction)
      {

        /* Set to half of image height.*/
        var dimension = jQuery(current_image_class).height() / 2;
        /* If next slide.*/
        if (direction == 'right') {
          bwg_cube(dimension, 0, -dimension, 90, 0, -90, 0, current_image_class, next_image_class, direction);
        }
        else
          if (direction == 'left') {
            bwg_cube(dimension, 0, dimension, -90, 0, 90, 0, current_image_class, next_image_class, direction);
          }
      }
      /* For browsers that does not support transitions.*/
      function bwg_fallback(current_image_class, next_image_class, direction)
      {
        bwg_fade(current_image_class, next_image_class, direction);
      }
      /* For browsers that support transitions, but not 3d transforms (only used if primary transition makes use of 3d-transforms).*/
      function bwg_fallback3d(current_image_class, next_image_class, direction)
      {
        bwg_sliceV(current_image_class, next_image_class, direction);
      }
      function bwg_none(current_image_class, next_image_class, direction)
      {
        jQuery(current_image_class).css({'opacity': 0, 'z-index': 1});
        jQuery(next_image_class).css({'opacity': 1, 'z-index': 2});
        /* Set active thumbnail.*/
      }
      function bwg_fade(current_image_class, next_image_class, direction)
      {
        /* Set active thumbnail.*/
        if (bwg_testBrowser_cssTransitions()) {
          jQuery(next_image_class).css('transition', 'opacity ' + wdwts_transition_duration + 'ms linear');
          jQuery(current_image_class).css({'opacity': 0, 'z-index': 1});
          jQuery(next_image_class).css({'opacity': 1, 'z-index': 2});
        }
        else {
          jQuery(current_image_class).animate({'opacity': 0, 'z-index': 1}, wdwts_transition_duration);
          jQuery(next_image_class).animate({
            'opacity': 1,
            'z-index': 2
          }, {
            duration: wdwts_transition_duration,
            complete: function ()
            {
            }
          });
          /* For IE.*/
          jQuery(current_image_class).fadeTo(wdwts_transition_duration, 0);
          jQuery(next_image_class).fadeTo(wdwts_transition_duration, 1);
        }
      }
      function bwg_grid(cols, rows, ro, tx, ty, sc, op, current_image_class, next_image_class, direction)
      {
        /* If browser does not support CSS transitions.*/
        if (!bwg_testBrowser_cssTransitions()) {
          return bwg_fallback(current_image_class, next_image_class, direction);
        }
        wdwts_trans_in_progress = true;
        /* Set active thumbnail.*/
        /* The time (in ms) added to/subtracted from the delay total for each new gridlet.*/
        var count = (wdwts_transition_duration) / (cols + rows);
        /* Gridlet creator (divisions of the image grid, positioned with background-images to replicate the look of an entire slide image when assembled)*/
        function bwg_gridlet(width, height, top, img_top, left, img_left, src, imgWidth, imgHeight, c, r)
        {
          var delay = (c + r) * count;
          /* Return a gridlet elem with styles for specific transition.*/
          return jQuery('<div class="bwg_gridlet" />').css({
            width: width,
            height: height,
            top: top,
            left: left,
            backgroundImage: 'url("' + src + '")',
            backgroundColor: jQuery(".bwg_slideshow_image_wrap").css("background-color"),
            /*backgroundColor: rgba(0, 0, 0, 0),*/
            backgroundRepeat: 'no-repeat',
            backgroundPosition: img_left + 'px ' + img_top + 'px',
            backgroundSize: imgWidth + 'px ' + imgHeight + 'px',
            transition: 'all ' + wdwts_transition_duration + 'ms ease-in-out ' + delay + 'ms',
            transform: 'none'
          });
        }

        /* Get the current slide's image.*/
        var cur_img = jQuery(current_image_class).find('img');
        /* Create a grid to hold the gridlets.*/
        var grid = jQuery('<div />').addClass('bwg_grid');
        /* Prepend the grid to the next slide (i.e. so it's above the slide image).*/
        jQuery(current_image_class).prepend(grid);
        /* vars to calculate positioning/size of gridlets*/
        var cont = jQuery(".bwg_slide_bg");
        var imgWidth = cur_img.width();
        var imgHeight = cur_img.height();
        var contWidth = cont.width(),
          contHeight = cont.height(),
          imgSrc = cur_img.attr('src'), /*.replace('/thumb', ''),*/
          colWidth = Math.floor(contWidth / cols),
          rowHeight = Math.floor(contHeight / rows),
          colRemainder = contWidth - (cols * colWidth),
          colAdd = Math.ceil(colRemainder / cols),
          rowRemainder = contHeight - (rows * rowHeight),
          rowAdd = Math.ceil(rowRemainder / rows),
          leftDist = 0,
          img_leftDist = (jQuery(".bwg_slide_bg").width() - cur_img.width()) / 2;
        /* tx/ty args can be passed as 'auto'/'min-auto' (meaning use slide width/height or negative slide width/height).*/
        tx = tx === 'auto' ? contWidth : tx;
        tx = tx === 'min-auto' ? -contWidth : tx;
        ty = ty === 'auto' ? contHeight : ty;
        ty = ty === 'min-auto' ? -contHeight : ty;
        /* Loop through cols*/
        for (var i = 0; i < cols; i++) {
          var topDist = 0,
            img_topDst = (jQuery(".bwg_slide_bg").height() - cur_img.height()) / 2,
            newColWidth = colWidth;
          /* If imgWidth (px) does not divide cleanly into the specified number of cols, adjust individual col widths to create correct total.*/
          if (colRemainder > 0) {
            var add = colRemainder >= colAdd ? colAdd : colRemainder;
            newColWidth += add;
            colRemainder -= add;
          }
          /* Nested loop to create row gridlets for each col.*/
          for (var j = 0; j < rows; j++) {
            var newRowHeight = rowHeight,
              newRowRemainder = rowRemainder;
            /* If contHeight (px) does not divide cleanly into the specified number of rows, adjust individual row heights to create correct total.*/
            if (newRowRemainder > 0) {
              add = newRowRemainder >= rowAdd ? rowAdd : rowRemainder;
              newRowHeight += add;
              newRowRemainder -= add;
            }
            /* Create & append gridlet to grid.*/
            grid.append(bwg_gridlet(newColWidth, newRowHeight, topDist, img_topDst, leftDist, img_leftDist, imgSrc, imgWidth, imgHeight, i, j));
            topDist += newRowHeight;
            img_topDst -= newRowHeight;
          }
          img_leftDist -= newColWidth;
          leftDist += newColWidth;
        }
        /* Set event listener on last gridlet to finish transitioning.*/
        var last_gridlet = grid.children().last();
        /* Show grid & hide the image it replaces.*/
        grid.show();
        cur_img.css('opacity', 0);
        /* Add identifying classes to corner gridlets (useful if applying border radius).*/
        grid.children().first().addClass('rs-top-left');
        grid.children().last().addClass('rs-bottom-right');
        grid.children().eq(rows - 1).addClass('rs-bottom-left');
        grid.children().eq(-rows).addClass('rs-top-right');
        /* Execution steps.*/
        setTimeout(function ()
        {
          grid.children().css({
            opacity: op,
            transform: 'rotate(' + ro + 'deg) translateX(' + tx + 'px) translateY(' + ty + 'px) scale(' + sc + ')'
          });
        }, 100);
        jQuery(next_image_class).css('opacity', 1);
        /* After transition.*/
        jQuery(last_gridlet).one('webkitTransitionEnd transitionend otransitionend oTransitionEnd mstransitionend', jQuery.proxy(bwg_after_trans));


        function bwg_after_trans()
        {
          jQuery(current_image_class).css({'opacity': 0, 'z-index': 1});
          jQuery(next_image_class).css({'opacity': 1, 'z-index': 2});
          cur_img.css('opacity', 1);
          grid.remove();
          wdwts_trans_in_progress = false;
          if (typeof event_stack !== 'undefined' && event_stack.length > 0) {
            key = event_stack[0].split("-");
            event_stack.shift();
              wdwt_bwg_change_image(key[0], key[1], data, true);
          }
        }
      }
      function bwg_sliceH(current_image_class, next_image_class, direction)
      {
        if (direction == 'right') {
          var translateX = 'min-auto';
        }
        else
          if (direction == 'left') {
            var translateX = 'auto';
          }
        bwg_grid(1, 8, 0, translateX, 0, 1, 0, current_image_class, next_image_class, direction);
      }
      function bwg_sliceV(current_image_class, next_image_class, direction)
      {
        if (direction == 'right') {
          var translateY = 'min-auto';
        }
        else
          if (direction == 'left') {
            var translateY = 'auto';
          }
        bwg_grid(10, 1, 0, 0, translateY, 1, 0, current_image_class, next_image_class, direction);
      }
      function bwg_slideV(current_image_class, next_image_class, direction)
      {
        if (direction == 'right') {
          var translateY = 'auto';
        }
        else
          if (direction == 'left') {
            var translateY = 'min-auto';
          }
        bwg_grid(1, 1, 0, 0, translateY, 1, 1, current_image_class, next_image_class, direction);
      }
      function bwg_slideH(current_image_class, next_image_class, direction)
      {
        if (direction == 'right') {
          var translateX = 'min-auto';
        }
        else
          if (direction == 'left') {
            var translateX = 'auto';
          }
        bwg_grid(1, 1, 0, translateX, 0, 1, 1, current_image_class, next_image_class, direction);
      }
      function bwg_scaleOut(current_image_class, next_image_class, direction)
      {
        bwg_grid(1, 1, 0, 0, 0, 1.5, 0, current_image_class, next_image_class, direction);
      }
      function bwg_scaleIn(current_image_class, next_image_class, direction)
      {
        bwg_grid(1, 1, 0, 0, 0, 0.5, 0, current_image_class, next_image_class, direction);
      }
      function bwg_blockScale(current_image_class, next_image_class, direction)
      {
        bwg_grid(8, 6, 0, 0, 0, .6, 0, current_image_class, next_image_class, direction);
      }
      function bwg_kaleidoscope(current_image_class, next_image_class, direction)
      {
        bwg_grid(10, 8, 0, 0, 0, 1, 0, current_image_class, next_image_class, direction);
      }
      function bwg_fan(current_image_class, next_image_class, direction)
      {
        if (direction == 'right') {
          var rotate = 45;
          var translateX = 100;
        }
        else
          if (direction == 'left') {
            var rotate = -45;
            var translateX = -100;
          }
        bwg_grid(1, 10, rotate, translateX, 0, 1, 0, current_image_class, next_image_class, direction);
      }
      function bwg_blindV(current_image_class, next_image_class, direction)
      {
        bwg_grid(1, 8, 0, 0, 0, .7, 0, current_image_class, next_image_class);
      }
      function bwg_blindH(current_image_class, next_image_class, direction)
      {
        bwg_grid(10, 1, 0, 0, 0, .7, 0, current_image_class, next_image_class);
      }
      function bwg_random(current_image_class, next_image_class, direction)
      {
        var anims = ['sliceH', 'sliceV', 'slideH', 'slideV', 'scaleOut', 'scaleIn', 'blockScale', 'kaleidoscope', 'fan', 'blindH', 'blindV'];
        /* Pick a random transition from the anims array.*/
        this["bwg_" + anims[Math.floor(Math.random() * anims.length)] + ""](current_image_class, next_image_class, direction);
      }
      function iterator()
      {
        var iterator = 1;
        if (0) {
          iterator = Math.floor((data.length - 1) * Math.random() + 1);
        }
        return iterator;
      }
      function wdwt_bwg_change_image(current_key, key, data, from_effect)
      {
        if (data[key]) {
          if (jQuery('.bwg_ctrl_btn').hasClass('fa-pause')) {
            play();
          }
          if (!from_effect) {
            jQuery("#bwg_current_image_key").val(key);
          }


          if (wdwts_trans_in_progress) {
            event_stack.push(current_key + '-' + key);
            return;
          }
          var direction = 'right';
          if (wdwts_current_key > key) {
            var direction = 'left';
          }
          else
            if (wdwts_current_key == key) {
              return;
            }
          /* Set active thumbnail position.*/
          wdwts_current_key = key;
          /* Change image id, title, description.*/

          // Change image id, key, title, description.
          jQuery("#bwg_current_image_key").val(key);
          jQuery("#bwg_slideshow_image").attr('image_id', data[key]["id"]);


          jQuery(".bwg_slideshow_title_text").html(data[key]["alt"]);
          jQuery(".bwg_slideshow_description_text").html(data[key]["description"]);

          jQuery("#bwg_slideshow_image").attr('image_id', data[key]["id"]);
          //jQuery(".bwg_slideshow_title_text").html(jQuery('<div />').html(data[key]["alt"]).text());
          //jQuery(".bwg_slideshow_description_text").html(jQuery('<div />').html(data[key]["description"]));
          var current_image_class = "#image_id_" + data[current_key]["id"];
          var next_image_class = "#image_id_" + data[key]["id"];
          bwg_<?php echo $effect[0]; ?>(current_image_class, next_image_class, direction);
        }
        jQuery('.bwg_slideshow_title_text').removeClass('none');
        if (jQuery('.bwg_slideshow_title_text').html() == "") {
          jQuery('.bwg_slideshow_title_text').addClass('none');
        }

        jQuery('.bwg_slideshow_description_text').removeClass('none');
        if (jQuery('.bwg_slideshow_description_text').html() == "") {
          jQuery('.bwg_slideshow_description_text').addClass('none');
        }
      }
      function wdwt_slider_resize()
      {

        //standart chap vor@ voroshvac chi bnav template um

        var firstsize = 1024;
        var bodyWidth = jQuery(window).width();
        var parentWidth = jQuery(".bwg_slideshow_image_wrap").parent().width();
        //tryuk vor hankarc responsive.js @  ushana body i chap@ verci vochte verevi div i 
        if (parentWidth > bodyWidth) {
          parentWidth = bodyWidth;
        }
        var kaificent =<?php echo $image_height; ?>;
        var str = ( kaificent / firstsize  );

        jQuery(".bwg_slideshow_image_wrap").css({height: ((parentWidth) * str)});
        jQuery("#slideshow").css({height: ((parentWidth) * str)});

        jQuery(".bwg_slideshow_image_wrap > div").css({height: ((parentWidth) * str)});
        jQuery(".bwg_slideshow_title_container > div").css({height: ((parentWidth) * str)});
        //jQuery(".bwg_slideshow_image").css({height: ((parentWidth) * str)});


        jQuery(".bwg_slideshow_image_container").css({width: (parentWidth)});
        jQuery(".bwg_slideshow_image_container, img.bwg_slideshow_image").css({height: ((parentWidth) * str)});
        jQuery(".bwg_slideshow_image").css({
          maxWidth: parentWidth
        });

      }
      jQuery(window).resize(function ()
      {
        wdwt_slider_resize();
      });
      jQuery(window).load(function ()
      {

        if (typeof jQuery().swiperight !== 'undefined' && jQuery.isFunction(jQuery().swiperight)) {
          jQuery('#bwg_container1').swiperight(function ()
          {
              wdwt_bwg_change_image(parseInt(jQuery('#bwg_current_image_key').val()), (parseInt(jQuery('#bwg_current_image_key').val()) - iterator()) >= 0 ? (parseInt(jQuery('#bwg_current_image_key').val()) - iterator()) % data.length : data.length - 1, data);
            return false;
          });
        }
        if (typeof jQuery().swipeleft !== 'undefined' && jQuery.isFunction(jQuery().swipeleft)) {
          jQuery('#bwg_container1').swipeleft(function ()
          {
              wdwt_bwg_change_image(parseInt(jQuery('#bwg_current_image_key').val()), (parseInt(jQuery('#bwg_current_image_key').val()) + iterator()) % data.length, data);
            return false;
          });
        }

        var isMobile = (/android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i.test(navigator.userAgent.toLowerCase()));
        var bwg_click = isMobile ? 'touchend' : 'click';
        wdwt_slider_resize();
        jQuery("#bwg_container1").css({visibility: 'visible'});

        /* Set image container height.*/

        var mousewheelevt = (/Firefox/i.test(navigator.userAgent)) ? "DOMMouseScroll" : "mousewheel";
        /* FF doesn't recognize mousewheel as of FF3.x */

        /* Play/pause.*/
        if (1) {
          play();
        }
      });
      function play()
      {
        window.clearInterval(wdwts_playInterval);
        /* Play.*/
        wdwts_playInterval = setInterval(function ()
        {
          var iterator = 1;
          if (0) {
            iterator = Math.floor((data.length - 1) * Math.random() + 1);
          }
            wdwt_bwg_change_image(parseInt(jQuery('#bwg_current_image_key').val()), (parseInt(jQuery('#bwg_current_image_key').val()) + 1) % data.length, data)
        }, ''+<?php echo $slideshow_interval; ?>+
        ''
      )
        ;
      }
      jQuery(window).focus(function ()
      {
        /* event_stack = [];*/
        if (!jQuery(".bwg_ctrl_btn").hasClass("fa-play")) {
          play();
        }
        var iiii = 0;
        jQuery(".bwg_slider").children("span").each(function ()
        {
          if (jQuery(this).css('opacity') == 1) {
            jQuery("#bwg_current_image_key").val(iiii);
          }
        });
      });
      jQuery(window).blur(function ()
      {
        event_stack = [];
        window.clearInterval(wdwts_playInterval);
      });
      var pausehover =<?php echo $stop_on_hover; ?>;
      if (pausehover == 1) {
        jQuery(document).ready(function ()
        {
          jQuery("#bwg_slideshow_image_container, .bwg_slideshow_image_container").hover(function ()
            {
              event_stack = [];
              clearInterval(wdwts_playInterval);
            },
            function ()
            {
              play();
            });
        });
      }


    </script>
    <?php
  }

} /*end of class*/

?>