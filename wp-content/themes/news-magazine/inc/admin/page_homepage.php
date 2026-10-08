<?php

class WDWT_homepage_page_class
{

  public $options;

  function __construct()
  {

    include_once(ABSPATH . 'wp-admin/includes/plugin.php');

    $this->options = array(


      /*top posts*/

      "hide_top_posts" => array(
        "name" => "hide_top_posts",
        "title" => "",
        'type' => 'checkbox_open',
        'show' => array("top_post_cat_name", "top_post_categories", "top_post_count", "top_post_order", "top_post_orderby"),
        'hide' => array(),
        "description" => __("Display top posts on the homepage.", "news-magazine"),
        'section' => 'top_posts',
        'tab' => 'homepage',
        'default' => true,
        'customizer' => array()
      ),

      "top_post_cat_name" => array(
        "name" => "top_post_cat_name",
        "title" => "",
        'type' => 'text',
        "sanitize_type" => "sanitize_text_field",
        "description" => __("Title of the top posts section", "news-magazine"),
        'section' => 'top_posts',
        'tab' => 'homepage',
        'default' => '',
        'customizer' => array()
      ),
      "top_post_count" => array(
        "name" => "top_post_count",
        "title" => "",
        'type' => 'text',
        "sanitize_type" => "sanitize_text_field",
        "description" => __("Number of top posts", "news-magazine"),
        'section' => 'top_posts',
        'tab' => 'homepage',
        'default' => '2',
        'customizer' => array()
      ),

      "top_post_desc_length" => array(
        "name" => "top_post_desc_length",
        "title" => "",
        'type' => 'number',
        "sanitize_type" => "sanitize_text_field",
        "description" => __("Excerpt length of top posts. Minimum is 100.", "news-magazine"),
        'min' => '100',
        'max' => '500',
        'section' => 'top_posts',
        'tab' => 'homepage',
        'default' => '200',
        'customizer' => array()
      ),

      "top_post_order" => array(
        "name" => "top_post_order",
        "title" => "",
        'type' => 'select',
        "sanitize_type" => "sanitize_text_field",
        "valid_options" => array('asc' => __("Ascending", "news-magazine"), 'desc' => __("Descending", "news-magazine")),
        "description" => __("Order of posts", "news-magazine"),
        'section' => 'top_posts',
        'tab' => 'homepage',
        'default' => array('desc'),
        'customizer' => array()
      ),
      "top_post_orderby" => array(
        "name" => "top_post_orderby",
        "title" => "",
        'type' => 'select',
        "sanitize_type" => "sanitize_text_field",
        "valid_options" => array(
          'date' => __("Date", "news-magazine"),
          'title' => __("Title", "news-magazine"),
          'name' => __("Slug", "news-magazine")
        ),
        "description" => __("Order by", "news-magazine"),
        'section' => 'top_posts',
        'tab' => 'homepage',
        'default' => array('date'),
        'customizer' => array()
      ),

      "top_post_categories" => array(
        "name" => "top_post_categories",
        "title" => "",
        'type' => 'select',
        'multiple' => "true",
        "valid_options" => $this->get_categories(),
        "description" => __("Show posts only from these categories.", "news-magazine"),
        'section' => 'top_posts',
        'tab' => 'homepage',
        'default' => array(''),
        'customizer' => array()
      ),

      /*content posts*/

      "hide_content_posts" => array(
        "name" => "hide_content_posts",
        "title" => "",
        'type' => 'checkbox_open',
        'show' => array("content_post_cat_name", "content_post_categories", "content_post_order", "content_post_orderby"),
        'hide' => array(),
        "description" => __("Display content posts on the homepage. Set number of posts in reading settings.", "news-magazine"),
        'section' => 'content_posts',
        'tab' => 'homepage',
        'default' => true,
        'customizer' => array()
      ),

      "content_post_cat_name" => array(
        "name" => "content_post_cat_name",
        "title" => "",
        'type' => 'text',
        "sanitize_type" => "sanitize_text_field",
        "description" => __("Title of the content posts section", "news-magazine"),
        'section' => 'content_posts',
        'tab' => 'homepage',
        'default' => '',
        'customizer' => array()
      ),
      "content_post_order" => array(
        "name" => "content_post_order",
        "title" => "",
        'type' => 'select',
        "sanitize_type" => "sanitize_text_field",
        "valid_options" => array('asc' => __("Ascending", "news-magazine"), 'desc' => __("Descending", "news-magazine")),
        "description" => __("Order of posts", "news-magazine"),
        'section' => 'content_posts',
        'tab' => 'homepage',
        'default' => array('desc'),
        'customizer' => array()
      ),
      "content_post_orderby" => array(
        "name" => "content_post_orderby",
        "title" => "",
        'type' => 'select',
        "sanitize_type" => "sanitize_text_field",
        "valid_options" => array(
          'date' => __("Date", "news-magazine"),
          'title' => __("Title", "news-magazine"),
          'name' => __("Slug", "news-magazine")
        ),
        "description" => __("Order by", "news-magazine"),
        'section' => 'content_posts',
        'tab' => 'homepage',
        'default' => array('date'),
        'customizer' => array()
      ),

      "content_post_categories" => array(
        "name" => "content_post_categories",
        "title" => "",
        'type' => 'select',
        'multiple' => "true",
        "valid_options" => $this->get_categories(),
        "description" => __("Show posts only from these categories.", "news-magazine"),
        'section' => 'content_posts',
        'tab' => 'homepage',
        'default' => array(''),
        'customizer' => array()
      ),

      /*vertical tabs*/

      "hide_categories_vertical_tabs" => array(
        "name" => "hide_categories_vertical_tabs",
        "title" => "",
        'type' => 'checkbox_open',
        'show' => array("categories_vertical_tabs_name", "categories_vertical_tabs_count", "categories_vertical_tabs_order", "categories_vertical_tabs_orderby", "categories_vertical_tabs_categories"),
        'hide' => array(),
        "description" => __("Display vertical tabs on the homepage.", "news-magazine"),
        'section' => 'vertical_tabs',
        'tab' => 'homepage',
        'default' => true,
        'customizer' => array()
      ),

      "categories_vertical_tabs_name" => array(
        "name" => "categories_vertical_tabs_name",
        "title" => "",
        'type' => 'text',
        "sanitize_type" => "sanitize_text_field",
        "description" => __("Title of the vertical tabs section", "news-magazine"),
        'section' => 'vertical_tabs',
        'tab' => 'homepage',
        'default' => '',
        'customizer' => array()
      ),

      "categories_vertical_tabs_count" => array(
        "name" => "categories_vertical_tabs_count",
        "title" => "",
        'type' => 'text',
        'input_size' => 2,
        "sanitize_type" => "sanitize_text_field",
        "description" => __("Number of vertical tabs posts", "news-magazine"),
        'section' => 'vertical_tabs',
        'tab' => 'homepage',
        'default' => '5',
        'customizer' => array()
      ),
      "categories_vertical_tabs_order" => array(
        "name" => "categories_vertical_tabs_order",
        "title" => "",
        'type' => 'select',
        "sanitize_type" => "sanitize_text_field",
        "valid_options" => array('asc' => __("Ascending", "news-magazine"), 'desc' => __("Descending", "news-magazine")),
        "description" => __("Order of posts", "news-magazine"),
        'section' => 'vertical_tabs',
        'tab' => 'homepage',
        'default' => array('desc'),
        'customizer' => array()
      ),
      "categories_vertical_tabs_orderby" => array(
        "name" => "categories_vertical_tabs_orderby",
        "title" => "",
        'type' => 'select',
        "sanitize_type" => "sanitize_text_field",
        "valid_options" => array(
          'date' => __("Date", "news-magazine"),
          'title' => __("Title", "news-magazine"),
          'name' => __("Slug", "news-magazine")
        ),
        "description" => __("Order by", "news-magazine"),
        'section' => 'vertical_tabs',
        'tab' => 'homepage',
        'default' => array('date'),
        'customizer' => array()
      ),

      "categories_vertical_tabs_categories" => array(
        "name" => "categories_vertical_tabs_categories",
        "title" => "",
        'type' => 'select',
        'multiple' => "true",
        "valid_options" => $this->get_categories(),
        "description" => __("Show posts only from these categories", "news-magazine"),
        'section' => 'vertical_tabs',
        'tab' => 'homepage',
        'default' => array(''),
        'customizer' => array()
      ),

      "hide_dates" => array(
        "name" => "hide_dates",
        "title" => "",
        'type' => 'checkbox',
        "description" => __('Hide dates on homepage posts', "news-magazine"),
        'section' => 'hide_all_dates',
        'tab' => 'homepage',
        'default' => false,
        'customizer' => array()
      ),
    );
  }


  private function get_posts()
  {
    $args = array(
      'posts_per_page' => 3000,
      'orderby' => 'post_date',
      'order' => 'DESC',
      'post_type' => 'post',
      'post_status' => 'publish',
    );

    $posts_array_custom = array();
    $posts_array = get_posts($args);

    foreach ($posts_array as $post) {
      $key = $post->ID;
      $posts_array_custom[$key] = $post->post_title;
    }
    return $posts_array_custom;
  }

  private function get_categories($custom = false)
  {
    $args = array(
      'hide_empty' => 0,
      'orderby' => 'name',
      'order' => 'ASC',
    );

    $categories_array_custom = array();
    $categories_array = get_categories($args);
    if ($custom === true) {
      $categories_array_custom["random"] = __("Random Posts", "news-magazine");
      $categories_array_custom["popular"] = __("Popular Posts", "news-magazine");
      $categories_array_custom["recent"] = __("Recent Posts", "news-magazine");
    }
    foreach ($categories_array as $category) {
      $categories_array_custom[$category->term_id] = $category->name;
    }

    return $categories_array_custom;
  }

  private function get_categories_ids()
  {
    $args = array(
      'hide_empty' => 0,
      'orderby' => 'name',
      'order' => 'ASC',
    );

    $categories_array_custom = array();
    $categories_array = get_categories($args);
    foreach ($categories_array as $category) {
      array_push($categories_array_custom, $category->term_id);
    }
    return $categories_array_custom;
  }


}
 