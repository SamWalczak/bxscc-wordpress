<?php
/* include  fornt end framework class */
require_once('WDWT_front_functions.php');

class news_magazine_frontend_functions extends WDWT_frontend_functions{




  public static function top_posts($paged=1){

    global $wdwt_front,$post;

    $show_top_posts = $wdwt_front->get_param('hide_top_posts'); /*sic!*/
    if (!$show_top_posts) {
      return 0;
    }
    $top_post_categories = implode(',',$wdwt_front->get_param('top_post_categories', array(), array('')));
    $top_post_cat_name = $wdwt_front->get_param('top_post_cat_name');
    $top_post_count = $wdwt_front->get_param('top_post_count', array(), 2);
    $top_post_desc_length = $wdwt_front->get_param('top_post_desc_length', array(), 200);
    $hide_dates = $wdwt_front->get_param('hide_dates');

    $order = $wdwt_front->get_param('top_post_order', array(), array('desc'));
    $order = $order[0];
    $orderby = $wdwt_front->get_param('top_post_orderby', array(), array('date'));
    $orderby = $orderby[0];

    $grab_image = $wdwt_front->get_param('grab_image');
    $args = array(
      "posts_per_page"      => $top_post_count,
      "ignore_sticky_posts" => 1,
      "cat"                 => $top_post_categories,
      "paged"               => $paged,
      "orderby"             => $orderby,
      "order"               => $order,
    );

    ?>
    <div id="top-posts">
      <?php
      $wp_query = new WP_Query($args);
      $curent_query_posts=$wp_query->get_posts();
      if(!isset($curent_query_posts[0]))
        $curent_query_posts[0]='';
      $expert_News_post_date=get_the_time( 'Y.m.d, l',$curent_query_posts[0]);
      unset($curent_query_posts);
      ?>
      <div>
        <h2><?php echo esc_html($top_post_cat_name); ?></h2>
        <div id="top-posts-scroll">
          <div class="top-posts-wrapper">
            <div class="top-posts-block">
              <ul id="top-posts-list">

                <?php if($wp_query->have_posts()) {
                  while ($wp_query->have_posts()) {

                    $wp_query->the_post();
                    ?>
                    <li>
                      <?php
                      $thumb_div_class = "";
                      if (!(has_post_thumbnail() || (news_magazine_frontend_functions::post_image_url() && $grab_image)))
                        $thumb_div_class = "no-image";
                      if ($hide_dates)
                        $thumb_div_class .= " no-date";

                      $tumb_id=get_post_thumbnail_id( $post->ID );
                      $thumb_url=wp_get_attachment_image_src($tumb_id,'full');

                      $has_thumb = true;
                      if( $thumb_url ){
                        $thumb_url = $thumb_url[0];
                      }
                      else{
                        $thumb_url = news_magazine_frontend_functions::catch_that_image();
                        if(isset($thumb_url['image_catched']) && $thumb_url['image_catched'] ){
                          $has_thumb = true;
                        }
                        else{
                          $has_thumb = false;
                        }
                        $thumb_url = $thumb_url['src'];
                      }

                      ?>
                      <div class="image-block_wrap post-thumbnail <?php echo $thumb_div_class; ?>">
                        <div class="image-block" style='background: url("<?php echo $thumb_url; ?>") 50% 50% / cover no-repeat;'>
                          <?php if(!$hide_dates){ ?>
                            <div class="date">
                              <div class="month_year">
                                <span id="day"><?php echo get_the_time( 'd' ); ?></span>
                                <span id="month"><?php echo get_the_time( 'M' );?></span>
                                <span id="year"><?php echo get_the_time( 'Y' )?></span>
                              </div>
                            </div>
                          <?php } ?>
                        </div>

                      </div>

                      <div class="text">
                        <h4>
                          <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h4>
                        <p><?php news_magazine_frontend_functions::the_excerpt_max_charlength($top_post_desc_length); ?></p>
                      </div>

                    </li>
                  <?php } } ?>
              </ul>
              <div class="section_pagination">
                <?php if($paged >1){ ?>
                  <span class="top_posts_pagination prev_section" id="content_posts_left" onclick="wdwt_front_ajax_pagination(<?php echo $paged-1; ?>, 'top_posts_section', '#top-posts');" ><i class="fa fa-chevron-left"></i>&nbsp;&nbsp;<b><?php _e('Previous', "news-magazine"); ?></b></span>
                  <?php
                }
                if($paged < $wp_query->max_num_pages){ ?>
                  <span class="top_posts_pagination next_section" id="top_posts_right" onclick="wdwt_front_ajax_pagination(<?php echo $paged+1; ?>, 'top_posts_section', '#top-posts');"><b><?php _e('Next', "news-magazine"); ?></b>&nbsp;&nbsp;<i class="fa fa-chevron-right"></i></span>
                <?php } ?>
                <div class="clear"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php
  }


  public static  function categories_vertical_tabs(){
    global $wdwt_front;

    $show_categories_vertical_tabs = $wdwt_front->get_param('hide_categories_vertical_tabs'); /*sic!*/
    if (!$show_categories_vertical_tabs) {
      return;
    }

    $categories_vertical_tabs_categories = implode(',',$wdwt_front->get_param('categories_vertical_tabs_categories', array(), array('')));
    $categories_vertical_tabs_name = $wdwt_front->get_param('categories_vertical_tabs_name');
    $categories_vertical_tabs_count = $wdwt_front->get_param('categories_vertical_tabs_count');

    $grab_image = $wdwt_front->get_param('grab_image');
    $blog_style = $wdwt_front->get_param('blog_style');
    $hide_dates = $wdwt_front->get_param('hide_dates');
    $order = $wdwt_front->get_param('categories_vertical_tabs_order', array(), array('desc'));
    $order = $order[0];
    $orderby = $wdwt_front->get_param('categories_vertical_tabs_orderby', array(), array('date'));
    $orderby = $orderby[0];

    $args = array(
      "posts_per_page"      => $categories_vertical_tabs_count,
      "ignore_sticky_posts" => 1,
      "cat"                 => $categories_vertical_tabs_categories,
      "orderby"             => $orderby,
      "order"               => $order,
    );
    $wp_query = new WP_Query($args);

    $style_hide_date = (($hide_dates) ? "style='height:35px;'" : "" );
    ?>
    <div id="wd-categories-vertical-tabs">
      <h2 style="border-bottom: 3px solid #f1f1f1 !important;"><?php echo $categories_vertical_tabs_name; ?></h2>
      <div class="arrows-block">
        <div class="arrow-up"><a href="#up"></a></div>
        <div class="tabs-block" >
          <div class="tabs-scroll-block" style="top: 0px;">
            <ul class="tabs" data-visible="<?php echo $categories_vertical_tabs_count; ?>" data-count="<?php echo $categories_vertical_tabs_count; ?>">
              <?php if(have_posts()) {
                $i=1;
                while ($wp_query->have_posts()) {
                  $wp_query->the_post();
                  $vertical_id = $i++;
                  ?>
                  <li <?php if($vertical_id==1) echo 'class="active"'; ?> >
                    <a href="#<?php echo $vertical_id; ?>" <?php echo $style_hide_date; ?>>
                      <p style="font-size: 18px;"><?php the_title(); ?></p>
                      <?php if(!$hide_dates){ ?>
                        <span class="date"><?php echo get_the_time( 'M d, Y' )?> </span>
                      <?php } ?>
                    </a>
                  </li>
                <?php } } ?>
            </ul>
          </div>
        </div>
        <div class="arrow-down"><a href="#down"></a></div>
      </div>
      <div class="content-block">
        <ul class="content">
          <?php if(have_posts()) {
            $ii=1;
            while ($wp_query->have_posts()) {
              $wp_query->the_post();
              $vertical_tabs_id = $ii++; ?>
              <li <?php if($vertical_tabs_id==1) echo 'class="active"'; ?> id="categories-vertical-tabs-content-<?php echo $vertical_tabs_id; ?>">
                <div class="image_for_vertcat">
                  <?php  if($grab_image)
                  {
                    echo news_magazine_frontend_functions::display_thumbnail(500,500);
                  }
                  else
                  {
                    echo news_magazine_frontend_functions::thumbnail(500,500);
                  } ?>
                </div>
                <div class="text">
                  <p> <?php
                    news_magazine_frontend_functions::the_excerpt_max_charlength(1100);
                    ?></p>
                </div>
                <div class="tabs-more">
                  <a class="tab-more" href="<?php echo get_permalink() ?>"><?php _e("More Information", "news-magazine"); ?></a>
                </div>
              </li>
            <?php } } ?>
        </ul>
      </div>
      <?php wp_reset_postdata(); ?>
      <div class="clear"></div>
      <?php $wdwt_front->bottom_advertisment(); ?>
    </div>
    <?php
  }


  public static  function content_posts($paged=null) {

    global $wp_query,$wdwt_front,$post;

    if(!isset($paged)){
      global $paged;
      $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
    }
    if($paged === 0){
      $paged = 1;
    }


    $show_content_posts = $wdwt_front->get_param('hide_content_posts');  /*sic!*/
    if(!$show_content_posts){
      return;
    }


    $content_post_categories = $wdwt_front->get_param('content_post_categories', array(), array(''));
    $content_post_categories = implode(',',$content_post_categories);
    $content_post_cat_name = $wdwt_front->get_param('content_post_cat_name');
    $hide_dates = $wdwt_front->get_param('hide_dates');

    $order = $wdwt_front->get_param('content_post_order', array(), array('desc'));
    $order = $order[0];
    $orderby = $wdwt_front->get_param('content_post_orderby', array(), array('date'));
    $orderby = $orderby[0];

    $n_of_home_post=get_option( 'posts_per_page', 6);
    if($n_of_home_post==0){
      $n_of_home_post = 1;
    }

    $args = array(
      'posts_per_page'      => $n_of_home_post,
      'ignore_sticky_posts' => 1,
      'cat'                 => $content_post_categories,
      'paged'               => $paged,
      'order'               => $order,
      'orderby'             => $orderby,
    );

    $grab_image = $wdwt_front->get_param('grab_image');

    ?>

    <div id="blog" class="content-inner-block">
      <?php

      $wp_query = new WP_Query($args);
      ?>
      <div class="blog-post">
        <h2><?php echo esc_html($content_post_cat_name); ?></h2>
        <div id="blog_post">
          <ul id="list">
            <?php
            if(have_posts()) {
              while ($wp_query->have_posts()) {
                $wp_query->the_post();

                $thumb_div_class = "";
                if (!(has_post_thumbnail() || (news_magazine_frontend_functions::post_image_url()  && $grab_image)))
                  $thumb_div_class = "no-image";
                if ($hide_dates)
                  $thumb_div_class .= " no-date";

                ?>
                <li>
                  <div class="latest_posts <?php echo ($thumb_div_class); ?>">
                    <?php
                    if(has_post_thumbnail() || (news_magazine_frontend_functions::post_image_url() && $grab_image)) {
                      echo news_magazine_frontend_functions::fixed_thumbnail(145,100, $grab_image);
                    }   ?>
                    <?php if(!$hide_dates){ ?>
                      <span class="date lat_news"><?php echo get_the_time( 'd M' )?></span>
                    <?php } ?>
                  </div>
                  <div class="cont_posts_content">
                    <h3><a href="<?php echo get_permalink() ?>"><?php the_title(); ?></a></h3>
                    <p> <?php
                      news_magazine_frontend_functions::the_excerpt_max_charlength(100);
                      ?></p>
                  </div>
                </li>
              <?php }
            }?>
          </ul>
          <div class="section_pagination">
            <?php
            if($paged >1){ ?>
              <span class="content_posts_pagination prev_section" id="content_posts_left" onclick="wdwt_front_ajax_pagination(<?php echo $paged-1; ?>, 'content_posts_section', '#content_posts_section');" ><i class="fa fa-chevron-left"></i>&nbsp;&nbsp;<b>Previous</b></span>
              <?php
            }
            if($paged < $wp_query->max_num_pages){ ?>
              <span class="content_posts_pagination next_section" id="content_posts_right" onclick="wdwt_front_ajax_pagination(<?php echo $paged+1; ?>, 'content_posts_section', '#content_posts_section');"><b>Next</b>&nbsp;&nbsp;<i class="fa fa-chevron-right"></i></span>
            <?php } ?>
            <div class="clear"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="clear"></div>


    <?php
    wp_reset_query();
  }


  public static function homepage() {
    global $wp_query,$paged,$wdwt_front;


    $date_enable = $wdwt_front->get_param('date_enable');
    $blog_style = $wdwt_front->get_param('blog_style');
    $grab_image = $wdwt_front->get_param('grab_image');


    if(have_posts()) {
      while (have_posts()) {
        the_post();

        ?>
        <div class="blog-post home-post">
          <a class="title_href" href="<?php echo get_permalink() ?>">
            <h2><?php the_title(); ?></h2>
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
      <?php } ?>

    <?php } ?>
    <div class="clear"></div><?php
    $wdwt_front->bottom_advertisment();
    wp_reset_query();

  }



}



