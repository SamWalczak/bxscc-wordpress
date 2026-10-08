<?php

class news_magazine_meta_layout_model extends WDWT_meta_model{
  
  public $params;
  
  function __construct(){
    
    $this->params = array( 

      'default_layout' => array(
        "name" => "default_layout", 
        "title" =>  __( "Choose Default Layout", "news-magazine" ),
        'type' => 'layout_open', 
        "description" => __( "Select the default layout for pages and posts on the website.", "news-magazine" ),
        'valid_options' => array( 
          array('index' => '1', 'title'=> __( 'No Sidebar', "news-magazine" ), 'description'=>''),
          array('index' => '2', 'title'=> __( 'Right Sidebar', "news-magazine" ), 'description'=>''),
          array('index' => '3', 'title'=> __( 'Left Sidebar', "news-magazine" ), 'description'=>''),
          array('index' => '4', 'title'=> __( 'Two Right Sidebars', "news-magazine" ), 'description'=>''),
          array('index' => '5', 'title'=> __( 'Two Left Sidebars', "news-magazine" ), 'description'=>''),
          array('index' => '6', 'title'=> __( 'One Right One Left Sidebars', "news-magazine" ), 'description'=>''),

        ),
        'show' => array(
                      '2'=>array('main_column'),
                      '3'=>array('main_column'),
                      '4'=>array('main_column','pwa_width'),
                      '5'=>array('main_column','pwa_width'),
                      '6'=>array('main_column','pwa_width'),
                      ),
        'hide' => array(),
        'img_src' => 'sprite-layouts.png',
        'img_height' => 289,
        'img_width' => 50,
        'default' => '0',
      ),
	  'full_width' => array(
		'name' => 'full_width',
		'title' => __( 'Full Width', "news-magazine" ),
		'type' => 'checkbox',
		'description' => __( 'You can choose full width for pages and posts on the website.', "news-magazine" ),
		'section' => 'layout_editor',
		'tab' => 'layout_editor',
		'default' => false
		),	  
	
		'content_area_percent' => array(
    'name' => 'content_area_percent',
    'title' => __( 'Content Area Width', "news-magazine" ),
    'type' => 'number',
    'valid_options' => '',
    "sanitize_type" => "sanitize_text_field",
    'description' => __( 'Enter a number between 75 and 99', "news-magazine" ),
    'step' => '1',
    'min' => '75',
    'max' => '99',
    'default' => '75',
    "unit_symbol" => "%"
    ),
      'main_column' => array( 
        "name" => "main_column", 
        "title" =>  __( "Main Column Width", "news-magazine" ),
        'type' => 'text', 
		"sanitize_type" => "sanitize_text_field",
        "description" =>  __( "Specify the width of the Main Column", "news-magazine" ),
        'unit_symbol' => '%',
        'input_size' => '2',
        'default' => '67'    
      ),
      'pwa_width' => array( 
        "name" => "pwa_width", 
        "title" =>  __( "Primary Widget Area width", "news-magazine" ),
        'type' => 'text', 
		"sanitize_type" => "sanitize_text_field",
        "description" =>  __( "Specify the width of the Primary Widget Area", "news-magazine" ),
        'unit_symbol' => '%',
        'input_size' => '2',
        'default' => '16'    
      ),
      'show_featured_image' => array(
        'name' => 'show_featured_image', 
        'title' =>  __( 'Show Featured Image', "news-magazine" ),
        'type' => 'checkbox', 
        'description' => __( 'Show Featured Image in single page/post view', "news-magazine" ),
        'default' => $this->post_type_check() == 'page' ? false : true ,
      ),
    );

  }
 
}