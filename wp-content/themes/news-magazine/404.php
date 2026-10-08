<?php
get_header(); 
global $wdwt_front;
?>
</header>
<div class="container"><?php
        if ( is_active_sidebar( 'sidebar-1' ) ) { ?>
			<aside id="sidebar1" >
				<div class="sidebar-container">			
				<?php  dynamic_sidebar( 'sidebar-1' ); 	?>	
				<div class="clear"></div>
				</div>
			</aside>
		<?php }  ?>
		<div id="content" class="blog">
		    <h1 class="page-header"><span><?php _e('Not Found', "news-magazine"); ?></span></h1>
			<p class="content-404"><?php _e('You are trying to reach a page that does not exist here. Either it has been moved or you typed a wrong address. Try searching:', "news-magazine"); ?></p>
		    <?php get_search_form(); ?>
			<?php $wdwt_front->bottom_advertisment(); ?>
		</div>
    <?php if ( is_active_sidebar( 'sidebar-2' ) ) { ?>
		<aside id="sidebar2"> 
			<div class="sidebar-container">
			   <?php dynamic_sidebar( 'sidebar-2' ); ?>
			   <div class="clear"></div>
			</div>
		</aside>
    <?php }	?>
</div>
<?php get_footer(); ?>