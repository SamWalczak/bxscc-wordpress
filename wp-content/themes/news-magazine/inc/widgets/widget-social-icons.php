<?php 
class news_magazine_social extends WP_Widget
{
    function __construct(){
		$widget_ops = array('description' => __('Social icons', "news-magazine"));
		$control_ops = array('width' => '', 'height' => '');
		parent::__construct(false,$name=__('Social Icons', "news-magazine"), $widget_ops, $control_ops);
	}

  /* Displays the Widget in the front-end */
    function widget($args, $instance){
		extract($args);
		$title = empty( $instance['title'] ) ? '' : esc_html($instance['title']);
		$show_facebook_icon = empty( $instance['show_facebook_icon'] ) ? '' : $instance['show_facebook_icon'];
		$show_twitter_icon = empty( $instance['show_twitter_icon'] ) ? '' : $instance['show_twitter_icon'];
		$facebook_url = empty( $instance['facebook_url'] ) ? '' : $instance['facebook_url'];
		$twitter_url = empty( $instance['twitter_url'] ) ? '' : $instance['twitter_url'];
		$show_rss_icon = empty( $instance['show_rss_icon'] ) ? '' : $instance['show_rss_icon'];
		$show_google_icon = empty( $instance['show_google_icon'] ) ? '' : $instance['show_google_icon'];
		$rss_url = empty( $instance['rss_url'] ) ? '' : $instance['rss_url'];
		$google_url = empty( $instance['google_url'] ) ? '' : $instance['google_url'];
		$show_inst_icon = empty( $instance['show_inst_icon'] ) ? '' : $instance['show_inst_icon'];
		$show_youtube_icon = empty( $instance['show_youtube_icon'] ) ? '' : $instance['show_youtube_icon'];
		$inst_url = empty( $instance['inst_url'] ) ? '' : $instance['inst_url'];
		$youtube_url = empty( $instance['youtube_url'] ) ? '' : $instance['youtube_url'];

		$instances = array(
		    'Facebook' => array( $show_facebook_icon, $facebook_url, '<i class="fa fa-facebook"></i>', 'facebook' ),
			'Twitter'  => array( $show_twitter_icon, $twitter_url, '<i class="fa fa-twitter"></i>', 'twitter' ),
			'Rss'	   => array( $show_rss_icon, $rss_url, '<i class="fa fa-rss"></i>', 'rss' ),
			'Google+'  => array( $show_google_icon, $google_url, '<i class="fa fa-google-plus"></i>', 'gplus' ),
			'Instagram'=> array( $show_inst_icon, $inst_url, '<i class="fa fa-instagram"></i>', 'instagram' ),
			'Youtube'  => array( $show_youtube_icon, $youtube_url, '<i class="fa fa-youtube"></i>','youtube' )
		);
		
		
		echo $before_widget;

		if ( $title )
			echo $before_title . $title . $after_title; ?>
	
		     <div class="social-widg-cont">
			 
				<?php foreach ($instances as $key => $value){ ?>
				
	              <a  class="<?php echo $value[3]; ?>" <?php if( $value[0]=='' || $value[1] == "" ){ echo "style=\"display:none;\""; } ?> href="<?php if( trim($value[1]) ) { echo esc_url($value[1]);} else { echo "javascript:;";}?>"  target="_blank" title="<?php echo $key; ?>">
					  <?php echo $value[2]; ?>
				  </a>
				 
				 <?php } ?>
				 
			  </div>
								   		
	<?php echo $after_widget;
				
		}

  /*Saves the settings. */
    function update($new_instance, $old_instance){
		
		$instance = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['show_facebook_icon'] = wp_filter_post_kses( addslashes($new_instance['show_facebook_icon']));
		$instance['show_twitter_icon'] = wp_filter_post_kses( addslashes($new_instance['show_twitter_icon']));
		$instance['facebook_url'] = wp_filter_post_kses( addslashes($new_instance['facebook_url']));
		$instance['twitter_url'] = wp_filter_post_kses( addslashes($new_instance['twitter_url']));
        $instance['show_rss_icon'] = wp_filter_post_kses( addslashes($new_instance['show_rss_icon']));
		$instance['show_google_icon'] = wp_filter_post_kses( addslashes($new_instance['show_google_icon']));
		$instance['rss_url'] = wp_filter_post_kses( addslashes($new_instance['rss_url']));
		$instance['google_url'] = wp_filter_post_kses( addslashes($new_instance['google_url']));
		$instance['show_youtube_icon'] = wp_filter_post_kses( addslashes($new_instance['show_youtube_icon']));
		$instance['youtube_url'] = wp_filter_post_kses( addslashes($new_instance['youtube_url']));
		$instance['show_inst_icon'] = wp_filter_post_kses( addslashes($new_instance['show_inst_icon']));
		$instance['inst_url'] = wp_filter_post_kses( addslashes($new_instance['inst_url']));
		
		return $instance;
		
		}

  /*Creates the form for the widget in the back-end. */
    function form($instance){
				//Defaults
		$instance = extract(wp_parse_args( (array) $instance, array( 'title'=>'', 'show_facebook_icon'=>'', 'show_twitter_icon'=>'', 'facebook_url'=>'', 'twitter_url' =>'','show_rss_icon'=>'', 'show_google_icon'=>'', 'rss_url'=>'', 'google_url' =>'','show_youtube_icon'=>'', 'youtube_url'=>'','show_inst_icon'=>'', 'inst_url'=>'') ));
 				
		?>
		
		<script type="text/javascript">
			function open_or_hide_param(cur_element){
				if(cur_element.checked){
					jQuery(cur_element).parent().parent().find('.open_hide').show();
				}
				else
				{
					jQuery(cur_element).parent().parent().find('.open_hide').hide();
				}				
			}
			jQuery(document).ready(function() {
					jQuery('.with_input').each(function(){open_or_hide_param(this)})
			});
		</script>
			
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e("Title", "news-magazine") ?>:</label><input class="widefat" id="<?php echo  $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
				
			<div class="optiontitle">
				<p class="block margin">				
						<input type="checkbox" onclick="open_or_hide_param(this)" class="checkbox with_input" name="<?php echo  $this->get_field_name('show_facebook_icon'); ?>" id="<?php echo  $this->get_field_id('show_facebook_icon'); ?>" <?php checked($show_facebook_icon, "on"); ?> />								
						<label for="<?php echo $this->get_field_id('show_facebook_icon'); ?>"><?php _e("Show Facebook Icon", "news-magazine"); ?></label>
				</p> 
				<p class="block open_hide">
						<?php _e("Enter your Facebook Profile URL below.", "news-magazine"); ?>
						<input name="<?php echo  $this->get_field_name('facebook_url'); ?>" id="<?php echo  $this->get_field_id('facebook_url'); ?>" type="text" value="<?php echo stripslashes(esc_attr($facebook_url)); ?>">
				</p>
			</div>
			<div class="optiontitle">
				<p class="block margin">				
						<input type="checkbox" onclick="open_or_hide_param(this)" class="checkbox with_input" name="<?php echo  $this->get_field_name('show_twitter_icon'); ?>" id="<?php echo  $this->get_field_id('show_twitter_icon'); ?>" <?php checked($show_twitter_icon, "on"); ?> />								
						<label for="<?php echo $this->get_field_id('show_twitter_icon'); ?>"><?php _e("Show Twitter Icon", "news-magazine"); ?></label>
				</p> 
				<p class="block open_hide">
						<?php _e("Enter your Twitter Profile URL below.", "news-magazine"); ?>
						<input name="<?php echo  $this->get_field_name('twitter_url'); ?>" id="<?php echo  $this->get_field_id('twitter_url'); ?>" type="text" value="<?php echo stripslashes(esc_attr($twitter_url)); ?>">
				</p>
			</div>
			<div class="optiontitle">
				<p class="block margin">				
						<input type="checkbox" onclick="open_or_hide_param(this)" class="checkbox with_input" name="<?php echo  $this->get_field_name('show_rss_icon'); ?>" id="<?php echo  $this->get_field_id('show_rss_icon'); ?>" <?php checked($show_rss_icon, "on"); ?> />								
						<label for="<?php echo $this->get_field_id('show_rss_icon'); ?>"><?php _e("Show RSS Icon", "news-magazine"); ?></label>
				</p> 
				<p class="block open_hide">
						<?php _e("Enter your RSS URL below.", "news-magazine"); ?>
						<input name="<?php echo  $this->get_field_name('rss_url'); ?>" id="<?php echo  $this->get_field_id('rss_url'); ?>" type="text" value="<?php echo stripslashes(esc_attr($rss_url)); ?>">
				</p>
			</div>
			<div class="optiontitle">
				<p class="block margin">				
						<input type="checkbox" onclick="open_or_hide_param(this)" class="checkbox with_input" name="<?php echo  $this->get_field_name('show_google_icon'); ?>" id="<?php echo  $this->get_field_id('show_google_icon'); ?>" <?php checked($show_google_icon, "on"); ?> />								
						<label for="<?php echo $this->get_field_id('show_google_icon'); ?>"><?php _e("Show Google+ Icon" , "news-magazine"); ?></label>
				</p> 
				<p class="block open_hide">
						<?php _e("Enter your Google+ Profile URL below.", "news-magazine"); ?>
						<input name="<?php echo  $this->get_field_name('google_url'); ?>" id="<?php echo  $this->get_field_id('google_url'); ?>" type="text" value="<?php echo stripslashes(esc_attr($google_url)); ?>">
				</p>
			</div>
			<div class="optiontitle">
				<p class="block margin">				
						<input type="checkbox" onclick="open_or_hide_param(this)" class="checkbox with_input" name="<?php echo  $this->get_field_name('show_youtube_icon'); ?>" id="<?php echo  $this->get_field_id('show_youtube_icon'); ?>" <?php checked($show_youtube_icon, "on"); ?> />								
						<label for="<?php echo $this->get_field_id('show_youtube_icon'); ?>"><?php _e("Show Youtube Icon", "news-magazine"); ?></label>
				</p> 
				<p class="block open_hide">
						<?php _e("Enter your Youtube Profile URL below.", "news-magazine"); ?>
						<input name="<?php echo  $this->get_field_name('youtube_url'); ?>" id="<?php echo  $this->get_field_id('youtube_url'); ?>" type="text" value="<?php echo stripslashes(esc_attr($youtube_url)); ?>">
				</p>
			</div>
			<div class="optiontitle">
				<p class="block margin">				
						<input type="checkbox" onclick="open_or_hide_param(this)" class="checkbox with_input" name="<?php echo  $this->get_field_name('show_inst_icon'); ?>" id="<?php echo  $this->get_field_id('show_inst_icon'); ?>" <?php checked($show_inst_icon, "on"); ?> />								
						<label for="<?php echo $this->get_field_id('show_inst_icon'); ?>"><?php _e("Show Instagram Icon", "news-magazine"); ?></label>
				</p> 
				<p class="block open_hide">
						<?php _e("Enter your Instagram Profile URL below." , "news-magazine"); ?>
						<input name="<?php echo  $this->get_field_name('inst_url'); ?>" id="<?php echo  $this->get_field_id('inst_url'); ?>" type="text" value="<?php echo stripslashes(esc_attr($inst_url)); ?>">
				</p>
			</div>
		
<?php	
      
}

}// end news_magazine_social class
add_action('widgets_init', 'news_magazine_widget_social_icons');
function news_magazine_widget_social_icons(){
  return register_widget("news_magazine_social");
}
?>