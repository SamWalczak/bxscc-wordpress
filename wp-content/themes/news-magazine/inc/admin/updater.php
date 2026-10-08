<?php
require_once('WDWT_updater.php');


class news_magazine_updater extends WDWT_updater
{

  /*first version with settings API*/
  protected $version_set_api = '1.0.50';

  protected $old_meta_name = 'web_dorado_meta_date';

  protected $theme_mods_name = 'theme_mods_news-magazine';

  /**
   * rules for converting old param to new
   *
   * keep order from old to new
   *
   *
   * start from $version_set_api
   * @param types : get_param_with_old_name, get_old_colors, checkbox_to_select, option_change, widget name change, slider
   */
  protected $rules = array(
    '1.0.50' => array(
      array('old' => "news_magazine_hide_top_posts", 'new' => 'hide_top_posts', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazine_top_post_count", 'new' => 'top_post_count', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazine_top_post_categories", 'new' => 'top_post_categories', 'type' => 'get_old_posts_cats'),
      array('old' => "news_magazine_hide_content_post", 'new' => 'hide_content_posts', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazine_content_post_cat_name", 'new' => 'content_post_cat_name', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazine_content_post_categories", 'new' => 'content_post_categories', 'type' => 'get_old_posts_cats'),

      array('old' => "news_magazine_hide_cat_tab_post", 'new' => 'hide_categories_vertical_tabs', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazine_tab_post_visible_count", 'new' => 'categories_vertical_tabs_count', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazine_tab_post_categories", 'new' => 'categories_vertical_tabs_categories', 'type' => 'get_old_posts_cats'),
      array('old' => "news_magazine_hide_category_tabs_posts", 'new' => 'hide_category_tabs_posts', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazinegs_default_layout", 'new' => 'default_layout', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazinegs_full_width", 'new' => 'full_width', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazinegs_content_area", 'new' => 'content_area', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazinegs_main_column", 'new' => 'main_column', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazinegs_pwa_width", 'new' => 'pwa_width', 'type' => 'get_param_with_old_name'),
      array('old' => "seo_seo_home_title", 'new' => 'seo_home_title', 'type' => 'get_param_with_old_name'),
      array('old' => "seo_seo_home_description", 'new' => 'seo_home_description', 'type' => 'get_param_with_old_name'),
      array('old' => "seo_seo_home_keywords", 'new' => 'seo_home_keywords', 'type' => 'get_param_with_old_name'),
      array('old' => "seo_seo_home_titletext", 'new' => 'seo_home_titletext', 'type' => 'get_param_with_old_name'),
      array('old' => "seo_seo_home_descriptiontext", 'new' => 'seo_home_descriptiontext', 'type' => 'get_param_with_old_name'),
      array('old' => "seo_seo_home_keywordstext", 'new' => 'seo_home_keywordstext', 'type' => 'get_param_with_old_name'),
      array('old' => "seo_seo_home_type", 'new' => 'seo_home_type', 'type' => 'select_to_select_array'),
      array('old' => "seo_seo_home_separate", 'new' => 'seo_home_separate', 'type' => 'get_param_with_old_name'),
      array('old' => "seo_seo_single_title", 'new' => 'seo_single_title', 'type' => 'get_param_with_old_name'),
      array('old' => "seo_seo_single_description", 'new' => 'seo_single_description', 'type' => 'get_param_with_old_name'),
      array('old' => "seo_seo_single_keywords", 'new' => 'seo_single_keywords', 'type' => 'get_param_with_old_name'),
      array('old' => "seo_seo_single_type", 'new' => 'seo_single_type', 'type' => 'select_to_select_array'),
      array('old' => "seo_seo_single_separate", 'new' => 'seo_single_separate', 'type' => 'get_param_with_old_name'),
      array('old' => "seo_seo_index_description", 'new' => 'seo_index_description', 'type' => 'get_param_with_old_name'),
      array('old' => "seo_seo_index_type", 'new' => 'seo_index_type', 'type' => 'select_to_select_array'),
      array('old' => "seo_seo_index_separate", 'new' => 'seo_index_separate', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazine_logo_img", 'new' => 'logo_img', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazine_custom_css_enable", 'new' => 'custom_css_enable', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazine_custom_css", 'new' => 'custom_css_text', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazine_favicon_img", 'new' => 'favicon_img', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazine_blog_style", 'new' => 'blog_style', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazine_grab_image", 'new' => 'grab_image', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazine_header_part", 'new' => 'header_part', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazine_latest_posts_enable", 'new' => 'latest_posts_enable', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazine_latest_posts_text", 'new' => 'latest_posts_text', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazine_latest_posts_count", 'new' => 'latest_posts_count', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazine_show_twitter_icon", 'new' => 'show_twitter_icon', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazine_show_rss_icon", 'new' => 'show_rss_icon', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazine_show_facebook_icon", 'new' => 'show_facebook_icon', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazine_twitter_url", 'new' => 'twitter_url', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazine_rss_url", 'new' => 'rss_url', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazine_facebook_url", 'new' => 'facebook_url', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazine_favicon_enable", 'new' => 'favicon_enable', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazine_date_enable", 'new' => 'date_enable', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazine_footer_text_enable", 'new' => 'footer_text_enable', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazine_footer_text", 'new' => 'footer_text', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazine_show_google_icon", 'new' => 'show_google_icon', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazine_google_url", 'new' => 'google_url', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazineint_integrate_header_enable", 'new' => 'integrate_header_enable', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazineint_integrate_body_enable", 'new' => 'integrate_body_enable', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazineint_integrate_singletop_enable", 'new' => 'integrate_singletop_enable', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazineint_integrate_singlebottom_enable", 'new' => 'integrate_singlebottom_enable', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazineint_integration_head", 'new' => 'integration_head', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazineint_integration_body", 'new' => 'integration_body', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazineint_integration_single_top", 'new' => 'integration_single_top', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazineint_integration_single_bottom", 'new' => 'integration_single_bottom', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazineint_integration_head_adsense_hide", 'new' => 'int_integration_head_adsense_type', 'type' => 'checkbox_add_to_radio', 'args' => array('value' => 'adsens', 'option_type' => 'mods')),
      array('old' => "news_magazineint_integration_head_adsense_type", 'new' => 'integration_head_adsense_type', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazineint_integration_head_adsense", 'new' => 'integration_head_adsense', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazineint_integration_bottom_adsense_type", 'new' => 'integration_bottom_adsense_type', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazineint_integration_head_advertisment_picture", 'new' => 'integration_head_advertisment_picture', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazineint_integration_head_advertisment_url", 'new' => 'integration_head_advertisment_url', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazineint_integration_head_advertisment_title", 'new' => 'integration_head_advertisment_title', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazineint_integration_head_advertisment_alt", 'new' => 'integration_head_advertisment_alt', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazineint_integration_bottom_adsense_hide", 'new' => 'int_integration_bottom_adsense_type', 'type' => 'checkbox_add_to_radio', 'args' => array('value' => 'adsens', 'option_type' => 'mods')),//rename in rules 1.1.0
      array('old' => "news_magazineint_integration_bottom_adsense", 'new' => 'integration_bottom_adsense', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazineint_integration_bottom_advertisment_picture", 'new' => 'integration_bottom_advertisment_picture', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazineint_integration_bottom_advertisment_url", 'new' => 'integration_bottom_advertisment_url', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazineint_integration_bottom_advertisment_title", 'new' => 'integration_bottom_advertisment_title', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazineint_integration_bottom_advertisment_alt", 'new' => 'integration_bottom_advertisment_alt', 'type' => 'get_param_with_old_name'),
      array('old' => "ct_slider_height", 'new' => 'image_height', 'type' => 'get_param_with_old_name'),
      array('old' => "ct_anim_speed", 'new' => 'animation_speed', 'type' => 'get_param_with_old_name'),
      array('old' => "ct_pause_time", 'new' => 'slideshow_interval', 'type' => 'get_param_with_old_name'),
      array('old' => "ct_pause_on_hover", 'new' => 'stop_on_hover', 'type' => 'get_param_with_old_name'),
      array('old' => "web_busines_image_link", 'new' => 'slider_head', 'type' => 'get_param_with_old_name'),
      array('old' => "web_busines_image_href", 'new' => 'slider_head_href', 'type' => 'get_param_with_old_name'),
      array('old' => "web_busines_image_title", 'new' => 'slider_head_title', 'type' => 'get_param_with_old_name'),
      array('old' => "web_busines_image_textarea", 'new' => 'slider_head_desc', 'type' => 'get_param_with_old_name'),
      array('old' => "ct_effect", 'new' => 'effect', 'type' => 'select_to_select_array'),
      array('old' => "ct_slider_title_position", 'new' => 'title_position', 'type' => 'select_to_select_array'),
      array('old' => "ct_slider_description_position", 'new' => 'description_position', 'type' => 'select_to_select_array'),
      array('old' => "news_magazine_hide_slider", 'new' => 'show_slider', 'type' => 'get_param_with_old_name'),
      array('old' => "news_magazinety_type_headers_font", 'new' => 'text_headers_font', 'type' => 'select_to_select_array'),
      array('old' => "news_magazinety_type_headers_kern", 'new' => 'text_headers_kern', 'type' => 'select_to_select_array'),
      array('old' => "news_magazinety_type_headers_transform", 'new' => 'text_headers_transform', 'type' => 'select_to_select_array'),
      array('old' => "news_magazinety_type_headers_variant", 'new' => 'text_headers_variant', 'type' => 'select_to_select_array'),
      array('old' => "news_magazinety_type_headers_weight", 'new' => 'text_headers_weight', 'type' => 'select_to_select_array'),
      array('old' => "news_magazinety_type_headers_style", 'new' => 'text_headers_style', 'type' => 'select_to_select_array'),
      array('old' => "news_magazinety_type_primary_font", 'new' => 'text_primary_font', 'type' => 'select_to_select_array'),
      array('old' => "news_magazinety_type_primary_kern", 'new' => 'text_primary_kern', 'type' => 'select_to_select_array'),
      array('old' => "news_magazinety_type_primary_transform", 'new' => 'text_primary_transform', 'type' => 'select_to_select_array'),
      array('old' => "news_magazinety_type_primary_variant", 'new' => 'text_primary_variant', 'type' => 'select_to_select_array'),
      array('old' => "news_magazinety_type_primary_weight", 'new' => 'text_primary_weight', 'type' => 'select_to_select_array'),
      array('old' => "news_magazinety_type_primary_style", 'new' => 'text_primary_style', 'type' => 'select_to_select_array'),
      array('old' => "news_magazinety_type_secondary_font", 'new' => 'text_secondary_font', 'type' => 'select_to_select_array'),
      array('old' => "news_magazinety_type_secondary_kern", 'new' => 'text_secondary_kern', 'type' => 'select_to_select_array'),
      array('old' => "news_magazinety_type_secondary_transform", 'new' => 'text_secondary_transform', 'type' => 'select_to_select_array'),
      array('old' => "news_magazinety_type_secondary_variant", 'new' => 'text_secondary_variant', 'type' => 'select_to_select_array'),
      array('old' => "news_magazinety_type_secondary_weight", 'new' => 'text_secondary_weight', 'type' => 'select_to_select_array'),
      array('old' => "news_magazinety_type_secondary_style", 'new' => 'text_secondary_style', 'type' => 'select_to_select_array'),
      array('old' => "news_magazinety_type_inputs_font", 'new' => 'text_inputs_font', 'type' => 'select_to_select_array'),
      array('old' => "news_magazinety_type_inputs_kern", 'new' => 'text_inputs_kern', 'type' => 'select_to_select_array'),
      array('old' => "news_magazinety_type_inputs_transform", 'new' => 'text_inputs_transform', 'type' => 'select_to_select_array'),
      array('old' => "news_magazinety_type_inputs_variant", 'new' => 'text_inputs_variant', 'type' => 'select_to_select_array'),
      array('old' => "news_magazinety_type_inputs_weight", 'new' => 'text_inputs_weight', 'type' => 'select_to_select_array'),
      array('old' => "news_magazinety_type_inputs_style", 'new' => 'text_inputs_style', 'type' => 'select_to_select_array'),
      array('old' => "news_magazinecc_menu_elem_back_color", 'new' => 'menu_elem_back_color', 'type' => 'get_old_colors', 'args' => array('default' => "#FFFFFF")),
      array('old' => "news_magazinecc_slider_back_color", 'new' => 'slider_back_color', 'type' => 'get_old_colors', 'args' => array('default' => "#F9F9F9")),
      array('old' => "news_magazinecc_slider_text_color", 'new' => 'slider_text_color', 'type' => 'get_old_colors', 'args' => array('default' => "#ffffff")),
      array('old' => "news_magazinecc_sideb_background_color", 'new' => 'sideb_background_color', 'type' => 'get_old_colors', 'args' => array('default' => "#ffffff")),
      array('old' => "news_magazinecc_footer_back_color", 'new' => 'footer_back_color', 'type' => 'get_old_colors', 'args' => array('default' => "#efefef")),
      array('old' => "news_magazinecc_footer_sidebar_back_color", 'new' => 'footer_sideb_background_color', 'type' => 'get_old_colors', 'args' => array('default' => "#F9F9F9")),
      array('old' => "news_magazinecc_borders_color", 'new' => 'primary_text_headers_color', 'type' => 'get_old_colors', 'args' => array('default' => "#025a7e")),
      array('old' => "news_magazinecc_block_text_color", 'new' => 'block_text_color', 'type' => 'get_old_colors', 'args' => array('default' => "#FFFFFF")),
      array('old' => "news_magazinecc_top_posts_color", 'new' => 'top_posts_color', 'type' => 'get_old_colors', 'args' => array('default' => "#FFFFFF")),
      array('old' => "news_magazinecc_text_headers_color", 'new' => 'text_headers_color', 'type' => 'get_old_colors', 'args' => array('default' => "#0480b4")),
      array('old' => "news_magazinecc_primary_text_color", 'new' => 'primary_text_color', 'type' => 'get_old_colors', 'args' => array('default' => "#232323")),
      array('old' => "news_magazinecc_footer_text_color", 'new' => 'footer_text_color', 'type' => 'get_old_colors', 'args' => array('default' => "#565656")),
      array('old' => "news_magazinecc_primary_links_color", 'new' => 'primary_links_color', 'type' => 'get_old_colors', 'args' => array('default' => "#074a7a")),
      array('old' => "news_magazinecc_primary_links_hover_color", 'new' => 'primary_links_hover_color', 'type' => 'get_old_colors', 'args' => array('default' => "#025a7e")),
      array('old' => "news_magazinecc_menu_links_color", 'new' => 'menu_links_color', 'type' => 'get_old_colors', 'args' => array('default' => "#232323")),
      array('old' => "news_magazinecc_menu_links_hover_color", 'new' => 'menu_links_hover_color', 'type' => 'get_old_colors', 'args' => array('default' => "#ffffff")),
      array('old' => "news_magazinecc_menu_color", 'new' => 'menu_color', 'type' => 'get_old_colors', 'args' => array('default' => "#000000")),
      array('old' => "news_magazinecc_selected_menu_color", 'new' => 'selected_menu_color', 'type' => 'get_old_colors', 'args' => array('default' => "#FFFFFF")),
      array('old' => "news_magazinecc_logo_text_color", 'new' => 'logo_text_color', 'type' => 'get_old_colors', 'args' => array('default' => "#8F8F8F")),
      array('old' => "news_magazinecc_meta_info_color", 'new' => 'meta_info_color', 'type' => 'get_old_colors', 'args' => array('default' => "#484848")),
      array('old' => "date_bg_color", 'new' => 'date_bg_color', 'type' => 'get_old_colors', 'args' => array('default' => "#0480B4", 'new_param' => true)),
      array('old' => "lightbox_bg_color", 'new' => 'lightbox_bg_color', 'type' => 'get_old_colors', 'args' => array('default' => "#000000", 'new_param' => true)),
      array('old' => "lightbox_ctrl_cont_bg_color", 'new' => 'lightbox_ctrl_cont_bg_color', 'type' => 'get_old_colors', 'args' => array('default' => "#000000", 'new_param' => true)),
      array('old' => "lightbox_title_color", 'new' => 'lightbox_title_color', 'type' => 'get_old_colors', 'args' => array('default' => "#FFFFFF", 'new_param' => true)),
      array('old' => "lightbox_ctrl_btn_color", 'new' => 'lightbox_ctrl_btn_color', 'type' => 'get_old_colors', 'args' => array('default' => "#FFFFFF", 'new_param' => true)),
      array('old' => "lightbox_close_rl_btn_hover_color", 'new' => 'lightbox_close_rl_btn_hover_color', 'type' => 'get_old_colors', 'args' => array('default' => "#7DC112", 'new_param' => true)),
    ),


  );
  /**
   *  meta content should not be changed
   * only name
   *
   */
  protected $rules_meta = array(
    '1.0.50' => array(
      array('old' => "layout", 'new' => 'default_layout'),
      array('old' => "content_width", 'new' => 'content_area'),
      array('old' => "main_col_width", 'new' => 'main_column'),
      array('old' => "pr_widget_area_width", 'new' => 'pwa_width'),
      array('old' => "fullwidthpage", 'new' => 'full_width'),
      array('old' => "blogstyle", 'new' => 'blog_style'),
      array('old' => "address", 'new' => 'addrval'),
      array('old' => "categories", 'new' => 'categories', 'type' => 'get_old_posts_cats_meta'),
      array('old' => "category_tabs_mst_pop", 'new' => 'category_tabs_mst_pop', 'type' => 'get_old_posts_cats_meta'),
      array('old' => "_single_post_soe_title", 'new' => 'seo_single_title', 'external' => true),
      array('old' => "_single_post_soe_description", 'new' => 'seo_single_description', 'external' => true),
      array('old' => "_single_post_soe_keywords", 'new' => 'seo_single_keywords', 'external' => true),
    ),
  );


  /**
   *  widget content should not be changed
   * only name
   *
   */
  protected $rules_widget = array(
    '1.0.50' => array(
      array('old' => "web_buis_adsens", 'new' => 'news_magazine_adsens'),
      array('old' => "web_buis_adv", 'new' => 'news_magazine_adv'),
      array('old' => "news_magazine_categ", 'new' => 'news_magazine_categ'),
      array('old' => "news_magazine_events_categ", 'new' => 'news_magazine_events_categ'),
      array('old' => "news_magazine_social", 'new' => 'news_magazine_social'),
    ),
  );

  /**
   * get colors created with theme mods
   * $args=array('default'=>'','title'=>'')
   */

  protected function get_old_colors($val, $param_name, $args = array())
  {
    $this->options['color_scheme']['active'] = 0;
    $this->options['color_scheme']['themes'] = array(
      array("name" => "theme_1", "title" => "Blue",),
      array("name" => "theme_2", "title" => "Red",),
      array("name" => "theme_3", "title" => "Gray",),
      array("name" => "theme_4", "title" => "Green",),
      array("name" => "theme_5", "title" => "Yellow",),
    );
    $this->options['color_scheme']['colors'][0][$param_name] = array(
      'value' => $val,
      'default' => $args['default']
    );

    $this->options['color_scheme']['colors'][0]['selected_menu_item_color'] = array('value' => "#8F8F8F", 'default' => "#8F8F8F");
    $this->options['color_scheme']['colors'][0]['home_top_posts_color'] = array('value' => "#FFFFFF", 'default' => "#FFFFFF");
    $this->options['color_scheme']['colors'][0]['cat_tab_backgr_color'] = array('value' => "#0e78a6", 'default' => "#0e78a6");
    $this->options['color_scheme']['colors'][0]['lightbox_bg_color'] = array('value' => "#ffffff", 'default' => "#ffffff");
    $this->options['color_scheme']['colors'][0]['lightbox_ctrl_cont_bg_color'] = array('value' => "#000000", 'default' => "#000000");
    $this->options['color_scheme']['colors'][0]['lightbox_title_color'] = array('value' => "#000000", 'default' => "#000000");
    $this->options['color_scheme']['colors'][0]['lightbox_ctrl_btn_color'] = array('value' => "#025a7e", 'default' => "#025a7e");
    $this->options['color_scheme']['colors'][0]['lightbox_close_rl_btn_hover_color'] = array('value' => "#cccccc", 'default' => "#cccccc");

    $this->options['color_scheme']['colors'][1] = array(
      "menu_elem_back_color" => array('value' => "#FFFFFF", 'default' => "#FFFFFF"),
      "slider_back_color" => array('value' => "#F9F9F9", 'default' => "#F9F9F9"),
      "slider_text_color" => array('value' => "#FFFFFF", 'default' => "#FFFFFF"),
      "sideb_background_color" => array('value' => "#FFFFFF", 'default' => "#FFFFFF"),
      "footer_back_color" => array('value' => "#efefef", 'default' => "#efefef"),
      "footer_sideb_background_color" => array('value' => "#F9F9F9", 'default' => "#F9F9F9"),
      "primary_text_headers_color" => array('value' => "#025a7e", 'default' => "#025a7e"),
      "block_text_color" => array('value' => "#FFFFFF", 'default' => "#FFFFFF"),
      "top_posts_color" => array('value' => "#FFFFFF", 'default' => "#FFFFFF"),
      "text_headers_color" => array('value' => "#025a7e", 'default' => "#025a7e"),
      "primary_text_color" => array('value' => "#232323", 'default' => "#232323"),
      "footer_text_color" => array('value' => "#565656", 'default' => "#565656"),
      "primary_links_color" => array('value' => "#991226", 'default' => "#991226"),
      "primary_links_hover_color" => array('value' => "#025a7e", 'default' => "#025a7e"),
      "menu_links_color" => array('value' => "#232323", 'default' => "#232323"),
      "menu_links_hover_color" => array('value' => "#FFFFFF", 'default' => "#FFFFFF"),
      "menu_color" => array('value' => "#000000", 'default' => "#000000"),
      "selected_menu_color" => array('value' => "#FFFFFF", 'default' => "#FFFFFF"),
      "logo_text_color" => array('value' => "#8F8F8F", 'default' => "#8F8F8F"),
      "meta_info_color" => array('value' => "#484848", 'default' => "#484848"),
      "date_bg_color" => array('value' => "#0480B4", 'default' => "#0480B4"),
      "selected_menu_item_color" => array('value' => "#8F8F8F", 'default' => "#8F8F8F"),
      "home_top_posts_color" => array('value' => "#FFFFFF", 'default' => "#FFFFFF"),
      "cat_tab_backgr_color" => array('value' => "#0e78a6", 'default' => "#0e78a6"),
      "lightbox_bg_color" => array('value' => "#ffffff", 'default' => "#ffffff"),
      "lightbox_ctrl_cont_bg_color" => array('value' => "#000000", 'default' => "#000000"),
      "lightbox_title_color" => array('value' => "#000000", 'default' => "#000000"),
      "lightbox_ctrl_btn_color" => array('value' => "#ffffff", 'default' => "#ffffff"),
      "lightbox_close_rl_btn_hover_color" => array('value' => "#cccccc", 'default' => "#cccccc"),

    );
    $this->options['color_scheme']['colors'][2] = array(
      "menu_elem_back_color" => array('value' => "#FFFFFF", 'default' => "#FFFFFF"),
      "slider_back_color" => array('value' => "#FFFFFF", 'default' => "#FFFFFF"),
      "slider_text_color" => array('value' => "#FFFFFF", 'default' => "#FFFFFF"),
      "sideb_background_color" => array('value' => "#FFFFFF", 'default' => "#FFFFFF"),
      "footer_back_color" => array('value' => "#efefef", 'default' => "#efefef"),
      "footer_sideb_background_color" => array('value' => "#F9F9F9", 'default' => "#F9F9F9"),
      "primary_text_headers_color" => array('value' => "#828282", 'default' => "#828282"),
      "block_text_color" => array('value' => "#FFFFFF", 'default' => "#FFFFFF"),
      "top_posts_color" => array('value' => "#FFFFFF", 'default' => "#FFFFFF"),
      "text_headers_color" => array('value' => "#000000", 'default' => "#000000"),
      "primary_text_color" => array('value' => "#838383", 'default' => "#838383"),
      "footer_text_color" => array('value' => "#565656", 'default' => "#565656"),
      "primary_links_color" => array('value' => "#565656", 'default' => "#565656"),
      "primary_links_hover_color" => array('value' => "#828282", 'default' => "#828282"),
      "menu_links_color" => array('value' => "#838383", 'default' => "#838383"),
      "menu_links_hover_color" => array('value' => "#FFFFFF", 'default' => "#FFFFFF"),
      "menu_color" => array('value' => "#000000", 'default' => "#000000"),
      "selected_menu_color" => array('value' => "#FFFFFF", 'default' => "#FFFFFF"),
      "logo_text_color" => array('value' => "#828282", 'default' => "#828282"),
      "meta_info_color" => array('value' => "#484848", 'default' => "#484848"),
      "date_bg_color" => array('value' => "#0480B4", 'default' => "#0480B4"),
      "selected_menu_item_color" => array('value' => "#8F8F8F", 'default' => "#8F8F8F"),
      "home_top_posts_color" => array('value' => "#FFFFFF", 'default' => "#FFFFFF"),
      "cat_tab_backgr_color" => array('value' => "#0e78a6", 'default' => "#0e78a6"),
      "lightbox_bg_color" => array('value' => "#ffffff", 'default' => "#ffffff"),
      "lightbox_ctrl_cont_bg_color" => array('value' => "#000000", 'default' => "#000000"),
      "lightbox_title_color" => array('value' => "#000000", 'default' => "#000000"),
      "lightbox_ctrl_btn_color" => array('value' => "#828282", 'default' => "#828282"),
      "lightbox_close_rl_btn_hover_color" => array('value' => "#cccccc", 'default' => "#cccccc"),
    );
    $this->options['color_scheme']['colors'][3] = array(
      "menu_elem_back_color" => array('value' => "#FFFFFF", 'default' => "#FFFFFF"),
      "slider_back_color" => array('value' => "#FFFFFF", 'default' => "#FFFFFF"),
      "slider_text_color" => array('value' => "#FFFFFF", 'default' => "#FFFFFF"),
      "sideb_background_color" => array('value' => "#FFFFFF", 'default' => "#FFFFFF"),
      "footer_back_color" => array('value' => "#efefef", 'default' => "#efefef"),
      "footer_sideb_background_color" => array('value' => "#F9F9F9", 'default' => "#F9F9F9"),
      "primary_text_headers_color" => array('value' => "#005816", 'default' => "#005816"),
      "block_text_color" => array('value' => "#FFFFFF", 'default' => "#FFFFFF"),
      "top_posts_color" => array('value' => "#FFFFFF", 'default' => "#FFFFFF"),
      "text_headers_color" => array('value' => "#000000", 'default' => "#000000"),
      "primary_text_color" => array('value' => "#838383", 'default' => "#838383"),
      "footer_text_color" => array('value' => "#565656", 'default' => "#565656"),
      "primary_links_color" => array('value' => "#2d6f61", 'default' => "#2d6f61"),
      "primary_links_hover_color" => array('value' => "#005816", 'default' => "#005816"),
      "menu_links_color" => array('value' => "#838383", 'default' => "#838383"),
      "menu_links_hover_color" => array('value' => "#FFFFFF", 'default' => "#FFFFFF"),
      "menu_color" => array('value' => "#000000", 'default' => "#000000"),
      "selected_menu_color" => array('value' => "#FFFFFF", 'default' => "#FFFFFF"),
      "logo_text_color" => array('value' => "#005816", 'default' => "#005816"),
      "meta_info_color" => array('value' => "#484848", 'default' => "#484848"),
      "date_bg_color" => array('value' => "#0480B4", 'default' => "#0480B4"),
      "selected_menu_item_color" => array('value' => "#8F8F8F", 'default' => "#8F8F8F"),
      "home_top_posts_color" => array('value' => "#FFFFFF", 'default' => "#FFFFFF"),
      "cat_tab_backgr_color" => array('value' => "#0e78a6", 'default' => "#0e78a6"),
      "lightbox_bg_color" => array('value' => "#ffffff", 'default' => "#ffffff"),
      "lightbox_ctrl_cont_bg_color" => array('value' => "#000000", 'default' => "#000000"),
      "lightbox_title_color" => array('value' => "#000000", 'default' => "#000000"),
      "lightbox_ctrl_btn_color" => array('value' => "#005816", 'default' => "#005816"),
      "lightbox_close_rl_btn_hover_color" => array('value' => "#cccccc", 'default' => "#cccccc"),
    );
    $this->options['color_scheme']['colors'][4] = array(
      "menu_elem_back_color" => array('value' => "#FFFFFF", 'default' => "#FFFFFF"),
      "slider_back_color" => array('value' => "#FFFFFF", 'default' => "#FFFFFF"),
      "slider_text_color" => array('value' => "#FFFFFF", 'default' => "#FFFFFF"),
      "sideb_background_color" => array('value' => "#FFFFFF", 'default' => "#FFFFFF"),
      "footer_back_color" => array('value' => "#efefef", 'default' => "#efefef"),
      "footer_sideb_background_color" => array('value' => "#F9F9F9", 'default' => "#F9F9F9"),
      "primary_text_headers_color" => array('value' => "#daaa10", 'default' => "#daaa10"),
      "block_text_color" => array('value' => "#FFFFFF", 'default' => "#FFFFFF"),
      "top_posts_color" => array('value' => "#FFFFFF", 'default' => "#FFFFFF"),
      "text_headers_color" => array('value' => "#000000", 'default' => "#000000"),
      "primary_text_color" => array('value' => "#838383", 'default' => "#838383"),
      "footer_text_color" => array('value' => "#565656", 'default' => "#565656"),
      "primary_links_color" => array('value' => "#0b5489", 'default' => "#0b5489"),
      "primary_links_hover_color" => array('value' => "#daaa10", 'default' => "#daaa10"),
      "menu_links_color" => array('value' => "#838383", 'default' => "#838383"),
      "menu_links_hover_color" => array('value' => "#FFFFFF", 'default' => "#FFFFFF"),
      "menu_color" => array('value' => "#000000", 'default' => "#000000"),
      "selected_menu_color" => array('value' => "#FFFFFF", 'default' => "#FFFFFF"),
      "logo_text_color" => array('value' => "#daaa10", 'default' => "#daaa10"),
      "meta_info_color" => array('value' => "#484848", 'default' => "#484848"),
      "date_bg_color" => array('value' => "#0480B4", 'default' => "#0480B4"),
      "selected_menu_item_color" => array('value' => "#8F8F8F", 'default' => "#8F8F8F"),
      "home_top_posts_color" => array('value' => "#FFFFFF", 'default' => "#FFFFFF"),
      "cat_tab_backgr_color" => array('value' => "#0e78a6", 'default' => "#0e78a6"),
      "lightbox_bg_color" => array('value' => "#8F8F8F", 'default' => "#8F8F8F"),
      "lightbox_ctrl_cont_bg_color" => array('value' => "#000000", 'default' => "#000000"),
      "lightbox_title_color" => array('value' => "#000000", 'default' => "#000000"),
      "lightbox_ctrl_btn_color" => array('value' => "#daaa10", 'default' => "#daaa10"),
      "lightbox_close_rl_btn_hover_color" => array('value' => "#cccccc", 'default' => "#cccccc"),
    );


    $this->options['colors_active']['select_theme'] = 'color_scheme';
    $this->options['colors_active']['active'] = '0';
    $this->options['colors_active']['colors'][$param_name] = array(
      'value' => $val,
      'default' => $args['default'],
    );

  }

}