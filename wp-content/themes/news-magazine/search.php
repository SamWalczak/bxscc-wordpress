<?php 

get_header();
global $wdwt_front;
$blog_style = $wdwt_front->get_param('blog_style');
?>
</header>
<div class="container">
	<?php if ( is_active_sidebar( 'sidebar-1' ) ) { ?>
		<aside id="sidebar1">
			<div class="sidebar-container">
				<?php  dynamic_sidebar( 'sidebar-1' ); 	?>	
				<div class="clear"></div>
			</div>
		</aside>
	<?php }  ?>
    <div id="content" class="blog search-page">
        <div class="single-post">
            <h1 class="page-header">
                <span><?php echo __('Search', "news-magazine"); ?></span>
            </h1>
        </div>
		<?php get_search_form(); ?>
        
        <?php  if( have_posts() ) {  while( have_posts()){  the_post(); ?>
                 <div class="search-result">
                    <h3>
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h3>
                    <div class="entry">
                        <?php
                        if($blog_style){
                          ?>
                          <p><?php news_magazine_frontend_functions::the_excerpt_max_charlength(250); ?></p>
                          <?php
                        }
                        else{
                          the_content();  
                        }
                        
                        ?>
                    </div>
                </div>
				<?php } ?>
				<div class="page-navigation">
					<?php posts_nav_link(); ?>
				</div>
        <?php }else {?>
				<div class="search-no-result">
			   <?php echo __("Nothing was found. Please try another keyword.", "news-magazine");  ?>
				</div>
		<?php }
		$wdwt_front->bottom_advertisment();  ?>
	</div>
	<?php if ( is_active_sidebar( 'sidebar-2' ) ) { ?>
		<aside id="sidebar2">
			<div class="sidebar-container">
			  <?php  dynamic_sidebar( 'sidebar-2' ); 	?>
			  <div class="clear"></div>
			</div>
		</aside>
	<?php } ?>
	<div class="clear"></div>		
</div>
<?php get_footer(); ?>
