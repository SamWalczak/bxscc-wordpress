<?php 
get_header();
global $wdwt_front;

$news_magazine_meta_date = get_post_meta($post->ID,WDWT_META,TRUE);
?>
</header>
<div class="container">
   <?php if ( is_active_sidebar( 'sidebar-1' ) ) { ?>
        <aside id="sidebar1" >
            <div class="sidebar-container">     
        <?php  dynamic_sidebar( 'sidebar-1' );  ?>
        <div class="clear"></div>
            </div>
        </aside>
  <?php }  ?> 
    <div id="content">
        <?php  if(have_posts()) : while(have_posts()) : the_post(); ?>
        <div class="single-post">
         <h1 class="page-header"><span><?php the_title(); ?></span></h1>
         <div class="entry"><?php

          $show_featured_image = $wdwt_front->get_param('show_featured_image', $news_magazine_meta_date, false);
          if($show_featured_image){
            if ( has_post_thumbnail()) { ?>
            <div class="post-thumbnail-div">
              <div class="img_container fixed size250x180">
                <?php echo news_magazine_frontend_functions::fixed_thumbnail(250,180,false); ?>
            </div>          
            </div>
            <?php
            }
          }

         the_content(); ?></div>
        </div>
      <?php endwhile; ?>
       <div class="navigation">
        <?php posts_nav_link(); ?>
       </div>
        <?php endif; ?>
    <div class="clear"></div>
    <?php $wdwt_front->bottom_advertisment();
    if(comments_open()){  ?>
        <div class="comments-template">
          <?php echo comments_template(); ?>
        </div>
    
    <?php } ?>  
    </div>
  <?php if ( is_active_sidebar( 'sidebar-2' ) ) { ?>
    <aside id="sidebar2">
      <div class="sidebar-container">
        <?php  dynamic_sidebar( 'sidebar-2' );  ?>
        <div class="clear"></div>
      </div>
    </aside>
  <?php } ?>
  <div class="clear"></div>
</div>
<?php get_footer(); ?>