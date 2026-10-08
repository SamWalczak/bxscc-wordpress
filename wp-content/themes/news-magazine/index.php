<?php 
get_header();
global $wdwt_front;
$date_enable = $wdwt_front->get_param('date_enable');
$blog_style = $wdwt_front->get_param('blog_style');
$grab_image = $wdwt_front->get_param('grab_image');
 ?>
</header>
<div class="container">
		<?php if ( is_active_sidebar( 'sidebar-1' ) ) { ?>
			<aside id="sidebar1">
				<div class="sidebar-container">
					<?php dynamic_sidebar( 'sidebar-1' );	?>
					<div class="clear"></div>
				</div>
			</aside>
		<?php } ?>
			<div id="content"><?php

			 

   if(have_posts()) { 
      while (have_posts()) {
        the_post();

        ?>
    <div class="blog-post home-post">        
      <a class="title_href" href="<?php echo get_permalink() ?>">
         <h3><?php the_title(); ?></h3>
      </a><?php  if($date_enable){ ?>
         <div class="home-post-date">
          <?php echo news_magazine_frontend_functions::posted_on();?>
         </div>
        <?php } 
         ?>
         <div class="img_container unfixed clear"> 
         <?php
         if(has_post_thumbnail() || (news_magazine_frontend_functions::post_image_url() && $blog_style && $grab_image)){
            echo news_magazine_frontend_functions::auto_thumbnail($grab_image);
          }
         ?>
         </div>
         <?php
        if($blog_style) 
        {
           the_excerpt();
        }
        else 
        {
           the_content(__('More', "news-magazine"));
        }  
         ?><div class="clear"></div>  
      
    </div>
    <?php 
    }
	    if( $wp_query->max_num_pages > 2 ){ ?>
    <div class="blog-page-navigation">
          <div class="alignleft"><?php previous_posts_link( '<i class="fa fa-chevron-left"></i> Previous Entries' ); ?></div>
          <div class="alignright"><?php next_posts_link( 'Next Entries <i class="fa fa-chevron-right"></i>', '' ); ?></div>
          <div class="clear"></div>
        </div>
    <?php } 
    
    } ?>
    <div class="clear"></div><?php
     $wdwt_front->bottom_advertisment();
    wp_reset_query(); 
    ?>			
			</div>
			<?php if ( is_active_sidebar( 'sidebar-2' ) ) { ?>
			<aside id="sidebar2">
				<div class="sidebar-container">
				  <?php  dynamic_sidebar( 'sidebar-2' ); 	?>
				  <div class="clear"></div>
				</div>
			</aside>
		<?php } ?>
		</div>
<?php get_footer(); ?>