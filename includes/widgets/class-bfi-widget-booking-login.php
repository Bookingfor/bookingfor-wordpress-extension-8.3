<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BookingFor Cart Widget.
 *
 * @author   BookingFor
 * @category Widgets
 * @package  BookingFor/Widgets
 * @version     2.0.5
 * @extends  WP_Widget
 */
if ( ! class_exists( 'BFI_Widget_Login' ) ) {
	class BFI_Widget_Login extends WP_Widget {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->widget_cssclass    = 'bfi-widget_login';
			$this->widget_description = __("Display the Login form in the sidebar.", 'bfi' );
			$this->widget_id          = 'bookingfor_widget_login';
			$this->widget_name        = __( 'BookingFor Login', 'bfi' );
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
	//		$this->widget_start( $args, $instance );
			$args["title"] =  $title;
			$args["instance"] =  $instance;
			bfi_get_template("widgets/login.php",$args);	
	//		include(BFI()->plugin_path() .'/templates/widgets/cart.php');
	//		$this->widget_end( $args );
		}

		// widget form creation
		function form($instance) {
			$showpopup = ( ! empty( $instance['showpopup'] ) ) ? esc_attr($instance['showpopup']) : '0';
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'wp_widget_plugin'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo ($instance)?esc_attr($instance['title']):''; ?>" />
		</p>
			<p class="bookingoptions">
				<label class="checkbox"><input type="checkbox" name="<?php echo $this->get_field_name('showpopup'); ?>" value="1" <?php  echo ($showpopup=='1') ? 'checked="checked"' : ''; ?> /><?php _e('Show like a popup', 'bfi'); ?></label>
			</p>
		
		<?php 
				
		}
		// update widget
		function update($new_instance, $old_instance) {

			  $instance = $old_instance;
			  // Fields
			  $instance['title'] = strip_tags($new_instance['title']);
			  $instance['showpopup'] =! empty( $new_instance[ 'showpopup' ] ) ? 1 : 0;
			 return $instance;
		}

	}
}