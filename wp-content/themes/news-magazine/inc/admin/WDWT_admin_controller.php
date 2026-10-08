<?php
global $wdwt_options;


/// include Layout page class
require_once('page_layout.php');
/// include General Settings page class
require_once('page_general_settings.php');
/// include Home page class
require_once('page_homepage.php');
/// include Typography page class
require_once('page_typography.php');
/// include Slider page class
require_once('page_slider.php');
/// include lightbox page class
/*temp hidden*/
//require_once( 'page_lightbox.php' );

///include licensing page
require_once('licensing.php');


/// include Theme support page class
require_once('WDWT_support_information.php');


$wdwt_layout_page = new WDWT_layout_page_class();
//$wdwt_lightbox_page = new WDWT_lightbox_page_class();
$wdwt_general_settings_page = new WDWT_general_settings_page_class();
$wdwt_homepage_page = new WDWT_homepage_page_class();
$wdwt_typography_page = new WDWT_typography_page_class();
$wdwt_slider_page = new WDWT_slider_page_class();
$wdwt_themes_support_page = new WDWT_themes_support_class();

$wdwt_licensing_page = new WDWT_licensing_page_class();


add_filter('option_' . WDWT_OPT, 'wdwt_options_mix_defaults');
/// ajax for install sample data
add_filter('wdwt_new_options_new_users', 'wdwt_new_options_new_users');

add_action('after_setup_theme', 'wdwt_options_init', 10, 2);


function wdwt_options_init()
{
  global $wdwt_options;


  $option_defaults = wdwt_get_option_defaults();

  $new_version = $option_defaults['theme_version'];
  $options = get_option(WDWT_OPT, array());

  if (isset($options['theme_version'])) {
    $old_version = $options['theme_version'];
  } else {
    $old_version = '0.0.0';
  }
  if ($new_version !== $old_version) {
    require_once('updater.php');
    $theme_update = new news_magazine_updater($new_version, $old_version);
    $options = $theme_update->get_old_params();  /* old params in new format */
  }
  /*overwrite defaults with new options*/
  $wdwt_options = apply_filters('wdwt_options_init', $options);

}

function wdwt_options_mix_defaults($options)
{
  $option_defaults = wdwt_get_option_defaults();
  /*theme version is saved separately*/
  /*for the updater*/
  if (isset($option_defaults['theme_version'])) {
    unset($option_defaults['theme_version']);
  }
  $options = wp_parse_args($options, $option_defaults);
  return $options;
}


function wdwt_get_options()
{

  global $wdwt_options;
  wdwt_options_init();/*refrest options*/

  return apply_filters('wdwt_get_options', $wdwt_options);
}


function wdwt_get_option_defaults()
{
  $option_parameters = wdwt_get_option_parameters();
  $option_defaults = array();

  $option_defaults['theme_version'] = WDWT_VERSION;

  foreach ($option_parameters as $option_parameter) {
    $name = (isset($option_parameter['name']) && $option_parameter['name'] != '') ? $option_parameter['name'] : false;
    if ($name && isset($option_parameter['default']))
      $option_defaults[$name] = $option_parameter['default'];
  }
  return apply_filters('WDWT_get_option_defaults', $option_defaults);
}

/**
 * new options to be enabled by default for new users only
 */

function wdwt_new_options_new_users($options)
{
  $options['shortcodes_deprecated'] = true;

  return $options;
}


function wdwt_get_option_parameters()
{
  global $wdwt_layout_page,
         $wdwt_general_settings_page,
         $wdwt_homepage_page,
         $wdwt_typography_page,
  //$wdwt_lightbox_page,
         $wdwt_slider_page;

  /*
  $WDWT_slider_page,*/

  global $wdwt_licensing_page;


  $options = array();

  foreach ($wdwt_layout_page->options as $kay => $x)
    $options[$kay] = $x;
  foreach ($wdwt_general_settings_page->options as $kay => $x)
    $options[$kay] = $x;
  /*
  foreach($wdwt_lightbox_page->options as $kay =>  $x) 
    $options[$kay] = $x;
  */
  foreach ($wdwt_homepage_page->options as $kay => $x)
    $options[$kay] = $x;

  foreach ($wdwt_typography_page->options as $kay => $x)
    $options[$kay] = $x;

  foreach ($wdwt_slider_page->options as $kay => $x)
    $options[$kay] = $x;

  return apply_filters('wdwt_get_option_parameters', $options);
}


function wdwt_get_tabs()
{
  $tabs = array();


  $tabs['layout_editor'] = array(
    'name' => 'layout_editor',
    'title' => __('Layout Editor', "news-magazine"),
    'sections' => array(
      'layout_editor' => array(
        'name' => 'layout_editor',
        'title' => __('Layout Editor', "news-magazine"),
        'description' => ''
      )
    ),
    'description' => wdwt_section_descr('layout_editor')
  );

  $tabs['general'] = array(
    'name' => 'general',
    'title' => __('General', "news-magazine"),
    'sections' => array(
      'general_main' => array(
        'name' => 'general_main',
        'title' => __('General - Main', "news-magazine"),
        'description' => ''
      ),
      'general_links' => array(
        'name' => 'general_links',
        'title' => __('General - Links', "news-magazine"),
        'description' => ''
      ),

    ),
    'description' => wdwt_section_descr('general')
  );

  $tabs['homepage'] = array(
    'name' => 'homepage',
    'title' => __('Homepage', "news-magazine"),
    'sections' => array(

      'top_posts' => array(
        'name' => 'top_posts',
        'title' => __('Top Posts', "news-magazine"),
        'description' => ''
      ),
      'content_posts' => array(
        'name' => 'content_posts',
        'title' => __('Content Posts', "news-magazine"),
        'description' => ''
      ),
      'vertical_tabs' => array(
        'name' => 'vertical_tabs',
        'title' => __('Vertical Tabs', "news-magazine"),
        'description' => ''
      ),
      'hide_all_dates' => array(
        'name' => 'hide_all_dates',
        'title' => __('Home Page Dates', "news-magazine"),
        'description' => ''
      ),
    ),
    'description' => wdwt_section_descr('homepage'),
  );


  $tabs['typography'] = array(
    'name' => 'typography',
    'title' => __('Typography', "news-magazine"),
    'description' => wdwt_section_descr('typography'),
    'sections' => array(
      'text_headers' => array(
        'name' => 'text_headers',
        'title' => __('Typography - Headings', "news-magazine"),
        'description' => ''
      ),
      'primary_font' => array(
        'name' => 'primary_font',
        'title' => __('Typography - Primary Font', "news-magazine"),
        'description' => ''
      ),
      'inputs_textareas' => array(
        'name' => 'inputs_textareas',
        'title' => __('Typography - Inputs and Text Areas', "news-magazine"),
        'description' => ''
      )
    ),

  );


  $tabs['slider'] = array(
    'name' => 'slider',
    'title' => __('Slider', "news-magazine"),
    'description' => wdwt_section_descr('slider'),
    'sections' => array(
      'slider_main' => array(
        'name' => 'slider_main',
        'title' => __('Slider - General', "news-magazine"),
        'description' => ''
      ),
      'slider_imgs' => array(
        'name' => 'slider_imgs',
        'title' => __('Slider - Images', "news-magazine"),
        'description' => ''
      ),
    ),
  );

  /*
  $tabs['lightbox'] = array(
    'name' => 'lightbox',
    'title' => __( 'Lightbox', "news-magazine" ),
    'description' => wdwt_section_descr('lightbox'),
    'sections' => array(
      'lightbox' => array(
        'name' => 'lightbox',
        'title' => __( 'Lightbox', "news-magazine" ),
        'description' => ''
      ),
    ),
  );
  */

  /* NO if WDWT_IS_PRO*/
  $tabs['color_control'] = array(
    'name' => 'color_control',
    'title' => __('Color Control', "news-magazine"),
    'sections' => array(
      'color_control' => array(
        'name' => 'color_control',
        'title' => __('Color Control', "news-magazine"),
        'description' => ''
      )
    ),
    'description' => wdwt_section_descr('color_control')
  );


  $tabs['licensing'] =
    array(
      'name' => 'licensing',
      'title' => __('Upgrade to Pro', "news-magazine"),
      'sections' => array(
        'licensing' => array(
          'name' => 'licensing',
          'title' => __('Upgrade to Pro', "news-magazine"),
          'description' => ''
        )
      ),
      'description' => ''
    );


  $tabs['themes_support'] = array(
    'name' => 'themes_support',
    'title' => __('Theme support', "news-magazine"),
    'sections' => array(
      'themes_support' => array(
        'name' => 'themes_support',
        'title' => '',
        'description' => ''
      )
    ),
    'description' => ''
  );

  return apply_filters('wdwt_get_tabs', $tabs);
}