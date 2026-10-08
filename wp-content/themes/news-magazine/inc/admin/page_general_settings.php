<?php

class WDWT_general_settings_page_class{
	public $options;

	

	function __construct(){

		
		$this->options = array(
			
			'custom_css_enable' => array(
				'name' => 'custom_css_enable',
				'title' => __( 'Custom CSS', "news-magazine" ),
				'type' => 'checkbox_open',
				'show' => array("custom_css_text"),
				'hide' => array(),
				'description' => __( 'Custom CSS will change the visual style of the website. The CSS code in this box is being applied to any page or post.', "news-magazine" ),
				'section' => 'general_main',
				'tab' => 'general',
				'default' => true,
				'customizer' => array()				
				),
			
			'custom_css_text' => array(
				'name' => 'custom_css_text',
				'title' => '',
				'type' => 'textarea',
				"sanitize_type" => "css",
				'valid_options' => '',
				'description' => __( 'Custom CSS code.', "news-magazine" ),
				'section' => 'general_main',
				'tab' => 'general',
				'default' => '',
				'customizer' => array()	
				),	
			'latest_posts_enable' => array(
				'name' => 'latest_posts_enable',
				'title' => __( 'Latest posts', "news-magazine" ),
				'type' => 'checkbox_open',
				'show' => array("latest_posts_text", "latest_posts_count", "latest_post_categories"),
				'hide' => array(),
				'description' => __( 'Latest posts.', "news-magazine" ),
				'section' => 'general_main',
				'tab' => 'general',
				'default' => true,
				'customizer' => array()				
				),
			'latest_posts_text' => array(
				'name' => 'latest_posts_text',
				'title' => '',
				'type' => 'text',
				"sanitize_type" => "sanitize_text_field",
				'valid_options' => '',
				'description' => __( 'Latest posts text.', "news-magazine" ),
				'section' => 'general_main',
				'tab' => 'general',
				'default' => 'Latest:',
				'customizer' => array()	
				),
			'latest_posts_count' => array(
				'name' => 'latest_posts_count',
				'title' => '',
				'type' => 'text',
				"sanitize_type" => "sanitize_text_field",
				'inpus_size' => 2,
				'valid_options' => '',
				'description' => __( 'Latest posts count.', "news-magazine" ),
				'section' => 'general_main',
				'tab' => 'general',
				'default' => get_option('posts_per_page'),
				'customizer' => array()	
				),
			"latest_post_categories" => array(
				"name" => "latest_post_categories",
				"title" => "",
				'type' => 'select',
				'multiple' => "true",
				"valid_options" => $this->get_all_categories(),
				"description" => __("Show posts only from these categories.", "news-magazine"),
				'section' => 'general_main',
				'tab' => 'general',
				'default' => array(''),
				'customizer' => array()
			),
			/*--- LOGO ---*/	
			'logo_type' => array(
				"name" => "logo_type", 
				"title" => __("Logo type", "news-magazine"),
				'type' => 'radio_open', 
				"description" => "", 
				'valid_options' => array(
					'none' => __("None", "news-magazine"),
					'image' => __("Image", "news-magazine"),
					'text' =>__("Text", "news-magazine"),
				),
				'show' => array('image'=>'logo_img'),
				'hide' => array(),
				'section' => 'general_main', 
				'tab' => 'general', 
				'default' => 'image',
				'customizer' => array()  
			),
			'logo_img' => array(
				'name' => 'logo_img', 
				'title' => __( 'Logo', "news-magazine" ),
				"sanitize_type" => "esc_url_raw",
				'type' => 'upload_single', 
				'description' => __( 'Upload custom logo image.', "news-magazine" ),
				'section' => 'general_main',  
				'tab' => 'general', 
				'default' => WDWT_IMG.'logo1.png',
				'customizer' => array()           
			),
			
			'show_desc' => array(
				'name' => 'show_desc',
				'title' =>  __( 'Show description', "news-magazine" ),
				'type' => 'checkbox',
				'description' => __( 'Check the box to show site description.', "news-magazine" ),
				'section' => 'general_main',
				'tab' => 'general',
				'default' => false,
				'customizer' => array()	
				),
			
			'blog_style' => array(
				'name' => 'blog_style',
				'title' =>  __( 'Blog Style post format', "news-magazine" ),
				'type' => 'checkbox',
				'description' => __( 'Check the box to have short previews for the homepage/index posts.', "news-magazine" ),
				'section' => 'general_main',
				'tab' => 'general',
				'default' => true,
				'customizer' => array()	
				),
			'grab_image' => array(
				'name' => 'grab_image',
				'title' =>  __( 'Grab the first post image', "news-magazine" ),
				'type' => 'checkbox',
				'description' => __( 'Enable this option if you want to use the images that are already in your post to create a thumbnail without using custom fields. In this case thumbnail images will be generated automatically using the first image of the post. Note that the image needs to be hosted on your own server.', "news-magazine" ),
				'section' => 'general_main',
				'tab' => 'general',
				'default' => true,
				'customizer' => array()	
				),
			
			'date_enable' => array(
				"name" => "date_enable",
				"title" => __("Display post meta information", "news-magazine" ),
				'type' => 'checkbox',
				"description" => __("Choose whether to display the post meta information such as date, author and etc.", "news-magazine" ),
				'section' => 'general_main',
				'tab' => 'general',
				'default' => true,
				'customizer' => array()
			),
			
			'footer_text_enable' => array(
				"name" => "footer_text_enable",
				"title" => __("Information in the Footer", "news-magazine" ),
				'type' => 'checkbox_open',
				'show' => array("footer_text"),
				'hide' => array(),
				"description" => __("Check the box to display custom HTML for the footer.", "news-magazine" ),
				'section' => 'general_main',
				'tab' => 'general',
				'default' => true,
				'customizer' => array()
			),

			'footer_text' => array(
				"name" => "footer_text",
				"title" => __("Information in the Footer", "news-magazine" ),
				'type' => 'textarea',
				"sanitize_type" => "sanitize_footer_html_field",
				"description" => __("Here you can provide the HTML code to be inserted in the footer of your web site.", "news-magazine" ),
				'section' => 'general_main',
				'tab' => 'general',
				'default' => '<span id="copyright">WordPress Themes by <a href="'.WDWT_HOMEPAGE.'"  target="_blank" title="Web-Dorado">Web-Dorado</a></span>',
				'customizer' => array()
			),	
			
			
				
			'show_facebook_icon' => array(
				"name" => "show_facebook_icon",
				"title" => __("Show Facebook Icon", "news-magazine" ),
				'type' => 'checkbox_open',
				'show' => array("facebook_url"),
				'hide' => array(),
				"description" => __("Check the box to display Facebook icon.", "news-magazine" ),
				'section' => 'general_links',
				'tab' => 'general',
				'default' => true,
				'customizer' => array()
			),
			
			'facebook_url' => array(
				"name" => "facebook_url",
				"title" => "",
				'type' => 'text',
				"sanitize_type" => "esc_url_raw",
				"description" => __("Enter your Facebook Profile URL below.", "news-magazine" ),
				'section' => 'general_links',
				'tab' => 'general',
				'default' => '#',
				'customizer' => array()
			),	
			
			'show_twitter_icon' => array(
				"name" => "show_twitter_icon",
				"title" => __("Show Twitter Icon", "news-magazine" ),
				'type' => 'checkbox_open',
				'show' => array("twitter_url"),
				'hide' => array(),
				"description" => __("Check the box to display Twitter icon.", "news-magazine" ),
				'section' => 'general_links',
				'tab' => 'general',
				'default' => true,
				'customizer' => array()				
			),
			
			'twitter_url' => array(
			  "name" => "twitter_url",
				"title" => "",
				'type' => 'text',
				"sanitize_type" => "esc_url_raw",
				"description" => __("Enter your Twitter Profile URL below.", "news-magazine" ),
				'section' => 'general_links',
				'tab' => 'general',
				'default' => '#',
				'customizer' => array()
			),
			
			
			'show_google_icon' => array(
				"name" => "show_google_icon",
				"title" => __("Show Google+ Icon", "news-magazine" ),
				'type' => 'checkbox_open',
				'show' => array("google_url"),
				'hide' => array(),
				"description" => __("Check the box to display Google + icon.", "news-magazine" ),
				'section' => 'general_links',
				'tab' => 'general',
				'default' => true,
				'customizer' => array()
			),
			'google_url' => array(
				"name" => "google_url",
				"title" => "",
				'type' => 'text',
				"sanitize_type" => "esc_url_raw",
				"description" => __("Enter your Google+ Profile URL below.", "news-magazine" ),
				'section' => 'general_links',
				'tab' => 'general',
				'default' => '#',
				'customizer' => array()
			),
			
			'show_rss_icon' => array(
			    "name" => "show_rss_icon",
				"title" => __("Show RSS Icon", "news-magazine" ),
				'type' => 'checkbox_open',
				'show' => array("rss_url"),
				'hide' => array(),
				"description" => __("Check the box to display RSS feed icon.", "news-magazine" ),
				'section' => 'general_links',
				'tab' => 'general',
				'default' => true,
				'customizer' => array()
			),
			
			'rss_url' => array(
				"name" => "rss_url",
				"title" => "",
				'type' => 'text',
				"sanitize_type" => "esc_url_raw",
				"description" => __("Enter your RSS URL below.", "news-magazine" ),
				'section' => 'general_links',
				'tab' => 'general',
				'default' => '#',
				'customizer' => array()
			),
			
			'show_instagram_icon' => array(
				"name" => "show_instagram_icon",
				"title" => __("Show instagram Icon", "news-magazine" ),
				'type' => 'checkbox_open',
				'show' => array("instagram_url"),
				'hide' => array(),
				"description" => __("Check the box to display Instagram icon.", "news-magazine" ),
				'section' => 'general_links',
				'tab' => 'general',
				'default' => true,
				'customizer' => array()
			),

			'instagram_url' => array(
				"name" => "instagram_url",
				"title" => "",
				'type' => 'text',
				"sanitize_type" => "esc_url_raw",
				"description" => __("Enter your Instagram Profile URL below.", "news-magazine" ),
				'section' => 'general_links',
				'tab' => 'general',
				'default' => '#',
				'customizer' => array()
			),
			
			'show_linkedin_icon' => array(
				"name" => "show_linkedin_icon",
				"title" => __("Show linkedin Icon", "news-magazine" ),
				'type' => 'checkbox_open',
				'show' => array("linkedin_url"),
				'hide' => array(),
				"description" => __("Check the box to display LinkedIn icon.", "news-magazine" ),
				'section' => 'general_links',
				'tab' => 'general',
				'default' => true,
				'customizer' => array()
			),

			'linkedin_url' => array(
				"name" => "linkedin_url",
				"title" => "",
				'type' => 'text',
				"sanitize_type" => "esc_url_raw",
				"description" => __("Enter your LinkedIn Profile URL below.", "news-magazine" ),
				'section' => 'general_links',
				'tab' => 'general',
				'default' => '#',
				'customizer' => array()
			),
			
			'show_pinterest_icon' => array(
				"name" => "show_pinterest_icon",
				"title" => __("Show pinterest Icon", "news-magazine" ),
				'type' => 'checkbox_open',
				'show' => array("pinterest_url"),
				'hide' => array(),
				"description" => __("Check the box to display Pinterest icon.", "news-magazine" ),
				'section' => 'general_links',
				'tab' => 'general',
				'default' => true,
				'customizer' => array()
			),

			'pinterest_url' => array(
				"name" => "pinterest_url",
				"title" => "",
				'type' => 'text',
				"sanitize_type" => "esc_url_raw",
				"description" => __("Enter your Pinterest Profile URL below.", "news-magazine" ),
				'section' => 'general_links',
				'tab' => 'general',
				'default' => '#',
				'customizer' => array()
			),

			'show_youtube_icon' => array(
				"name" => "show_youtube_icon",
				"title" => __("Show youtube Icon", "news-magazine" ),
				'type' => 'checkbox_open',
				'show' => array("youtube_url"),
				'hide' => array(),
				"description" => __("Check the box to display Youtube icon.", "news-magazine" ),
				'section' => 'general_links',
				'tab' => 'general',
				'default' => true,
				'customizer' => array()
			),

			'youtube_url' => array(
				"name" => "youtube_url",
				"title" => "",
				'type' => 'text',
				"sanitize_type" => "esc_url_raw",
				"description" => __("Enter your Youtube profile URL below.", "news-magazine" ),
				'section' => 'general_links',
				'tab' => 'general',
				'default' => '#',
				'customizer' => array()
			)
		);

	if ( ! function_exists( 'has_site_icon' ) || ! has_site_icon() ) {
		
		$this->options['favicon_enable'] = array(
				'name' => 'favicon_enable',
				'title' => __( 'Show Favicon', "news-magazine" ),
				'type' => 'checkbox_open',
				'show' => array("favicon_img"),
				'hide' => array(),
				'description' => __( 'Check the box to display the favicon.', "news-magazine" ),
				'section' => 'general_main',
				'tab' => 'general',
				'default' => false,
				'customizer' => array()	
				);

		$this->options['favicon_img'] = array(
				'name' => 'favicon_img',
				'title' => '',
				'type' => 'upload_single',
				"sanitize_type" => "esc_url_raw",
				'valid_options' => '',
				'description' => __( 'Click on the Upload Image button to upload the favicon image.', "news-magazine" ),
				'section' => 'general_main',
				'tab' => 'general',
				'default' => WDWT_IMG. 'favico.ico',
				'customizer' => array()	
				);
		}
		
		
	}
	
		private function get_all_categories($custom = false){
		$args= array(
				'hide_empty' => 0,
				'orderby' => 'name',
				'order' => 'ASC',
			);
		
		$categories_array_custom=array();
		$categories_array = get_categories( $args );
    if($custom===true){
			$categories_array_custom["random"] = __("Random Posts", "news-magazine");
			$categories_array_custom["popular"] = __("Popular Posts", "news-magazine");
			$categories_array_custom["recent"] = __("Recent Posts", "news-magazine");
		}
		foreach($categories_array as $category){
		  $categories_array_custom[$category->term_id] = $category->name;
		}
		return $categories_array_custom;
	}
		

}
 