<?php
/**
 * Plugin Name:   TW Events PRO Widget
 * Description:   Adds an widget that displays the events from Events PRO Calendar.
 * Version:       1.0
 * Author:        Trayche Roshkoski
 * Author URI:    http://trajcheroshkoski.com/
 */

class TWD_Events_Pro_Widget extends WP_Widget {


  // Set up the widget name and description.
  public function __construct() {
    $widget_options = array( 'classname' => 'twd_events_pro_widget', 'description' => 'Upcoming events from Tribe PRO calendar' );
    parent::__construct( 'twd_events_pro_widget', 'TWD Events PRO Widget', $widget_options );
  }


  // Create the widget output.
  public function widget( $args, $instance ) {
    $title = apply_filters( 'widget_title', $instance[ 'title' ] );
    $numEvents = ( ! empty( $instance['eventsDisplayed'] ) ) ? $instance['eventsDisplayed']  : 5 ; ?>

    <?php if ($instance[ 'zurb' ] == 'on') : ?>
      <?php
      echo $before_widget . '<div class="twd_events_widget">';
      echo '<h4>' .apply_filters( 'widget_title',  $instance['title'], $instance, $this->id_base ). '</h4>';
      echo '<div id="twd_events_widget_foundation">';

      $queryEvents = tribe_get_events(array(
        'posts_per_page'  =>	50,
        'start_date'      =>	date('Y-m-d') . ' 00:00'
      ));

      if ($queryEvents) {

        $eventsDisplayed = 0;
        foreach ( $queryEvents as $post ) :
          //get meta data for post
          setup_postdata($post);
          $startDate = strtotime($post->EventStartDate);
          $endDate = strtotime($post->EventEndDate);

          if ($eventsDisplayed++ < $numEvents) :
          ?>

          <div class="event_item">
            <p class="dateevent"><?php echo date( 'l, F d', $startDate ) ?></p>
            <?php if( $post->post_excerpt !=''){ ?>
              <span class="eventname" style="cursor:pointer" data-tooltip aria-haspopup="true" class="has-tip" title="<?php echo $post->post_excerpt ?>">
            <?php }else{ ?>
              <span class="eventname" style="cursor:default">
            <?php } ?>
              <?php echo $post->post_title ?>
            </span>
            <p class="timevent">
              <?php if (tribe_event_is_all_day()) : ?>
                <?php _e('All day event', 'tw') ?>
              <?php else : ?>
                <?php
                  $start_d = tribe_get_start_time($post->ID);
                  $end_d = tribe_get_end_time($post->ID);
                ?>
                <?php if($start_d){ ?>
                  <?php echo $start_d ?>
                  <?php if($end_d){ ?>
                    <?php echo ' - ' . $end_d ?>
                    &middot;
                    <?php echo human_time_diff($startDate, $endDate) ?>
                  <?php } ?>
                <?php } ?>
              <?php endif ?>
            </p>
          </div>

          <?php
          endif;
        endforeach;

        wp_reset_postdata();

      } else {
        ?>
        <p class="text-center error-no-entries"><?php _e( 'There aren\'t any Upcomming Events added yet' ); ?></p>
        <?php
      }

      echo '</div>';
      echo '</div>'.$after_widget;
      ?>
    <?php else : ?>
        <?php
        echo $before_widget . '<div class="twd_events_widget">';
        echo '<h4>' .apply_filters( 'widget_title',  $instance['title'], $instance, $this->id_base ). '</h4>';
        echo '<ul id="twd_events_widget">';

        $queryEvents = tribe_get_events(array(
          'posts_per_page'  =>	50,
          'start_date'      =>	date('Y-m-d') . ' 00:00'
        ));

        if ($queryEvents) {

          $eventsDisplayed = 0;
          foreach ( $queryEvents as $post ) :
            //get meta data for post
            setup_postdata($post);
            $startDate = strtotime($post->EventStartDate);
            $endDate = strtotime($post->EventEndDate);

            if ($eventsDisplayed++ < $numEvents) :
            ?>

            <li class="clearfix">
              <div class="twd-events-date"><span class="twd-events-day"><?php echo date( 'd', $startDate ) ?></span> <span class="twd-events-month"><?php echo date( 'M', $startDate ) ?></span></div>
              <div class="twd-events-content" data-toggle="tooltip" title="<?php echo $post->post_excerpt ?>"><?php echo $post->post_title ?></div>
            </li>

            <?php
            endif;
          endforeach;

          wp_reset_postdata();

        } else {
          ?>
          <li><p class="text-center error-no-entries"><?php _e( 'There aren\'t any Events added yet' ); ?></p></li>
          <?php
        }

        echo '</ul>';
        echo '</div>'.$after_widget;
        ?>
    <?php endif; ?>
 <?php }


  // Create the admin area widget settings form.
  public function form( $instance ) { ?>
    <p>
        <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title', 'tw' ); ?></label>
        <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" placeholder="Optional widget title">
    </p>
    <p>
        <label for="<?php echo esc_attr( $this->get_field_id( 'eventsDisplayed' ) ); ?>"><?php esc_attr_e( 'Events Displayed', 'tw' ); ?></label>
        <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'eventsDisplayed' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'eventsDisplayed' ) ); ?>" type="number" value="<?php echo esc_attr( $instance['eventsDisplayed'] ); ?>" placeholder="Number of events">
    </p>
    <p>
        <input type="checkbox" class="checkbox" <?php checked( $instance['zurb'], 'on' ); ?> id="<?php echo $this->get_field_id('zurb'); ?>" name="<?php echo $this->get_field_name( 'zurb' ); ?>">
        <label for="<?php echo $this->get_field_id('zurb'); ?>">Use Foundation?</label>
    </p>
    <?php
  }


  // Apply settings to the widget instance.
  public function update( $new_instance, $old_instance ) {
    $instance = $old_instance;
    $instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
    $instance[ 'eventsDisplayed' ] = strip_tags( $new_instance[ 'eventsDisplayed' ] );
    $instance['zurb'] = ( ! empty( $new_instance['zurb'] ) ) ? strip_tags( $new_instance['zurb'] ) : '';
    return $instance;
  }

}

// Register the widget.
function register_twd_events_pro_widget() {
  register_widget( 'TWD_Events_Pro_Widget' );
}
add_action( 'widgets_init', 'register_twd_events_pro_widget' );

?>
