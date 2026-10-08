<?php 
/*The template for displaying Archive pages*/

get_header(); 
global $wdwt_front;
$grab_image = $wdwt_front->get_param('grab_image');
$blog_style = $wdwt_front->get_param('blog_style');
$date_enable = $wdwt_front->get_param('date_enable');
?>
</header>
<div class="container"><?php 
	/* SIDBAR1 */	
	if ( is_active_sidebar( 'sidebar-1' ) ) { ?>
    <aside id="sidebar1">
        <div class="sidebar-container">
            <?php  dynamic_sidebar( 'sidebar-1' ); 	?>	
			<div class="clear"></div>
        </div>
    </aside>
    <?php }?>

	<div id="content" class="blog archive-page">

	<?php if (have_posts()) : ?>
	<?php $post = $posts[0]; ?>
		
		<?php  if (is_category()) { ?>
		<h1 class="styledHeading page-header"><span><?php _e('Archive For The ', "news-magazine"); ?>&ldquo;<?php single_cat_title(); ?>&rdquo; <?php _e('Category', "news-magazine"); ?></span></h1>
	 	<?php  } elseif( is_tag() ) { ?>
		<h1 class="styledHeading page-header"><span><?php _e('Posts Tagged ', "news-magazine"); ?>&ldquo;<?php single_tag_title(); ?>&rdquo;</span></h1>
		<?php  } elseif (is_day()) { ?>
		<h1 class="styledHeading page-header"><span><?php _e('Archive For ', "news-magazine"); ?><?php the_time(get_option( 'date_format' )); ?></span></h1>
		<?php  } elseif (is_month()) { ?>
		<h1 class="styledHeading page-header"><span><?php _e('Archive For ', "news-magazine"); ?><?php the_time(get_option( 'date_format' )); ?></span></h1>
		<?php  } elseif (is_year()) { ?>
		<h1 class="styledHeading page-header"><span><?php _e('Archive For ', "news-magazine"); ?><?php the_time(get_option( 'date_format' )); ?></span></h1>
		<?php  } elseif (is_author()) { ?>
		<h1 class="styledHeading page-header"><span><?php _e('Author Archive', "news-magazine"); ?></span></h1>
		<?php  } elseif (isset($_GET['paged']) && !empty($_GET['paged'])) { ?>
		<h1 class="styledHeading page-header"><span><?php _e('Blog Archives', "news-magazine"); ?></span></h1>
	 	<?php } ?>
			
		<?php while (have_posts()) : the_post(); ?>
			
		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<div class="post">
				<h3 class="archive-header">
					<a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
				</h3>
				<?php if($date_enable){ ?>
					<p class="meta-date"><?php _e('By ', "news-magazine"); ?><?php the_author_posts_link(); ?> | <?php news_magazine_frontend_functions::posted_on(); ?></p>
				<?php } ?>		
			</div>
			<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute( ); ?>" rel="bookmark">
			<?php
					if(has_post_thumbnail() || (news_magazine_frontend_functions::post_image_url() && $blog_style && $grab_image)){
					?>
						<div class="img_container fixed size180x150">
							<?php echo news_magazine_frontend_functions::fixed_thumbnail(180,150, $grab_image); ?>
						</div>
					<?php
					} ?>
			</a>
			<?php  if($blog_style){the_excerpt();}else{the_content();}  ?>
			<p><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute( ); ?>" rel="bookmark"><?php _e('Read more', "news-magazine"); ?> &raquo;</a></p>
		</div>
        <?php if($date_enable) news_magazine_frontend_functions::entry_meta(); ?>
		<?php endwhile; ?>
		<div class="page-navigation">
		     <?php posts_nav_link(); ?>
	    </div>
	<?php else : ?>

		<h3 class="archive-header"><?php _e('Not Found', "news-magazine"); ?></h3>
		<p><?php _e('There are not posts belonging to this category or tag. Try searching below:', "news-magazine"); ?></p>
		<div id="search-block-category"><?php get_search_form(); ?></div>
	
	<?php endif; ?>
	
	<?php $wdwt_front->bottom_advertisment();  
	if(comments_open()){  ?>
		<div class="comments-template">
			<?php echo comments_template();	?>
		</div>
	<?php } ?>
	</div>
	 <?php
	if ( is_active_sidebar( 'sidebar-2' ) ) { ?>
		<aside id="sidebar2">
			<div class="sidebar-container">
			  <?php  dynamic_sidebar( 'sidebar-2' ); 	?>
			  <div class="clear"></div>
			</div>
		</aside>
    <?php } ?>
</div>
	
<?php get_footer(); ?>
