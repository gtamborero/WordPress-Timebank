<?php

// SIDEBAR CREATION
class TimeBankWidget extends WP_Widget
{
  function __construct() {
    $widget_ops = array('classname' => 'RandomPostWidget', 'description' => 'Timebank user access / options view' );
    parent::__construct( 'RandomPostWidget', 'TimeBank -> Options', $widget_ops );
  }

  function form($instance){
    $instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
    $title = $instance['title'];
?>
  <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
<?php
  }

  function update($new_instance, $old_instance){
    $instance = $old_instance;
    $instance['title'] = $new_instance['title'];
    return $instance;
  }

  function widget($args, $instance){
    extract($args, EXTR_SKIP);

    echo $before_widget;
    $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);

    if (!empty($title))
      echo $before_title . $title . $after_title;;

    // WIDGET PRINT
    include_once( TB_PLUGIN_DIR . '../user/sidebar.php');

    echo $after_widget;
  }
}
add_action( 'widgets_init', function() {
  return register_widget("TimeBankWidget");
  }
);
// SIDEBAR CREATION END
