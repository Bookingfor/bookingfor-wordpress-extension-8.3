<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BookingFor Reviews Widget.
 *
 * @author   BookingFor
 * @category Widgets
 * @package  BookingFor/Widgets
 * @version     2.0.5
 * @extends  WP_Widget
 */
if ( ! class_exists( 'BFI_Widget_Reviews' ) ) :
class BFI_Widget_Reviews extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->widget_cssclass    = 'bfi-widget_reviews';
		$this->widget_description = __("Display the user's Review in the sidebar.", 'bfi' );
		$this->widget_id          = 'bookingfor_widget_reviews';
		$this->widget_name        = __( 'BookingFor Reviews', 'bfi' );
		$this->settings           = array(
			'title'  => array(
				'type'  => 'text',
				'std'   => '',
				'label' => __( 'Title', 'bfi' )
			)
		);
		$widget_ops = array(
			'classname'   => $this->widget_cssclass,
			'description' => $this->widget_description
		);

		parent::__construct( $this->widget_id, $this->widget_name, $widget_ops );

//		parent::__construct();
	}

		// widget form creation
		function form($instance) {

		}
		// update widget
		function update($new_instance, $old_instance) {
			$instance = $old_instance;
			return $instance;
		}
	/**
	 * Output widget.
	 *
	 * @see WP_Widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		extract( $args );
		// these are the widget options
        $title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : "";
		$title = apply_filters('widget_title', $title );
		$args["title"] =  $title;
		$args["instance"] =  $instance;
		bfi_get_template("widgets/reviews.php",$args);	
	}
}
endif;