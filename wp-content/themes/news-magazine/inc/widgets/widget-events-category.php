<?php

class news_magazine_events_categ extends WP_Widget
{
  function __construct()
  {
    $widget_ops = array('description' => __('Displays Events Categories Posts', "news-magazine"));
    $control_ops = array('width' => '', 'height' => '');
    parent::__construct(false, $name = __('Events Categories Posts', "news-magazine"), $widget_ops, $control_ops);
  }

  /* Displays the Widget in the front-end */
  function widget($args, $instance)
  {
    extract($args);
    $title = esc_html($instance['title']);
    $categ_id = empty($instance['categ_id']) ? '' : $instance['categ_id'];
    $post_count = empty($instance['post_count']) ? '' : $instance['post_count'];

    echo $before_widget;

    if ($title)
      echo $before_title . $title . $after_title; ?>

    <style>
      .widget-title {
        margin-bottom: 0;
      }

      .widget_news_magazine_events_categ div:last-child div {
        border-bottom: 0;
      }
    </style>
    <?php

    $wp_query = null;
    $wp_query = new WP_Query();
    if (!isset($post_count))
      $post_count = 0;
    $wp_query->query('posts_per_page=' . $post_count . '&cat=' . $categ_id);

    while ($wp_query->have_posts()) : $wp_query->the_post();

      ?>

      <div class="events">
        <div class="events-widg">
          <a class="title_href" href="<?php echo get_permalink() ?>">
							<span class="date">
								<?php
                $day = 'd';
                $month = 'M';
                ?>
                <h3><span class="events-day"><?php the_time($day); ?></span></h3>
                <span class="events-month"><?php the_time($month); ?></span>
							</span>
          </a>
          <p>
            <?php echo news_magazine_frontend_functions::the_excerpt_max_charlength(100); ?>
          </p>
        </div>

      </div>

    <?php endwhile;
    wp_reset_postdata();

    echo $after_widget;

  }

  /*Saves the settings. */
  function update($new_instance, $old_instance)
  {

    $instance = $old_instance;
    $instance['title'] = sanitize_text_field($new_instance['title']);
    $instance['categ_id'] = wp_filter_post_kses(addslashes($new_instance['categ_id']));
    $instance['post_count'] = wp_filter_post_kses(addslashes($new_instance['post_count']));

    return $instance;

  }

  /*Creates the form for the widget in the back-end. */
  function form($instance)
  {
    //Defaults
    $instance = wp_parse_args((array)$instance, array('title' => 'Events Categories Posts', 'categ_id' => '0', 'post_count' => '3'));

    $title = esc_attr($instance['title']);
    $categ_id = esc_attr($instance['categ_id']);
    $post_count = esc_attr($instance['post_count']) ?>

    <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __('Title', "news-magazine"); ?>:</label><input
        class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
        name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>"/></p>


    <p><label for="<?php echo $this->get_field_id('categ_id'); ?>"><?php echo __('Select Category', "news-magazine"); ?>
        :</label>
      <select name="<?php echo $this->get_field_name('categ_id'); ?>" id="<?php echo $this->get_field_id('categ_id') ?>"
              style="font-size:12px" class="inputbox">
        <option value="0"><?php echo __('Select Category', "news-magazine"); ?></option>
        <?php $categories = get_categories();
        $category_count = count($categories);
        for ($i = 0; $i < $category_count; $i++) {
          if (isset($categories[$i])) {
            ?>
            <option
              value="<?php echo $categories[$i]->term_id ?>" <?php selected($instance['categ_id'], $categories[$i]->term_id); ?>><?php echo $categories[$i]->name ?></option>
            <?php
          }
        }
        ?>
      </select></p>
    <p><label for="<?php echo $this->get_field_id('post_count'); ?>"><?php echo __('Number of Posts', "news-magazine"); ?>
        :</label><input id="<?php echo $this->get_field_id('post_count'); ?>"
                        name="<?php echo $this->get_field_name('post_count'); ?>" type="text"
                        value="<?php echo $post_count; ?>" size="6"/></p>
    <?php
  }

}// end news_magazine_events_categ class
add_action('widgets_init', 'news_magazine_widget_events_category');
function news_magazine_widget_events_category(){
  return register_widget("news_magazine_events_categ");
}
?>