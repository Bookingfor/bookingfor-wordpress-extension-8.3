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
if ( ! class_exists( 'BFI_Widget_Headerlink' ) ) {
	class BFI_Widget_Headerlink extends WP_Widget {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->widget_cssclass    = 'bfi-widget_headerlink';
			$this->widget_description = __("Display the link for  login, cart and change currency.", 'bfi' );
			$this->widget_id          = 'bookingfor_widget_headerlink';
			$this->widget_name        = __( 'BookingFor Header Link', 'bfi' );
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
			bfi_get_template("widgets/headerlink.php",$args);	
	//		include(BFI()->plugin_path() .'/templates/widgets/cart.php');
	//		$this->widget_end( $args );
		}

		// widget form creation
		function form($instance) {
			$showcurrency = ( ! empty( $instance['showcurrency'] ) ) ? esc_attr($instance['showcurrency']) : '0';
			$showcart = ( ! empty( $instance['showcart'] ) ) ? esc_attr($instance['showcart']) : '0';
			$showlogin = ( ! empty( $instance['showlogin'] ) ) ? esc_attr($instance['showlogin']) : '0';
			$showfavorites= ( ! empty( $instance['showfavorites'] ) ) ? esc_attr($instance['showfavorites']) : '0';	

		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'wp_widget_plugin'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo ($instance)?esc_attr($instance['title']):''; ?>" />
		</p>
			<p class="bookingoptions">
				<label class="checkbox"><input type="checkbox" name="<?php echo $this->get_field_name('showcurrency'); ?>" value="1" <?php  echo ($showcurrency=='1') ? 'checked="checked"' : ''; ?> /><?php _e('Show currency selector', 'bfi'); ?></label>
			</p>
			<p class="bookingoptions">
				<label class="checkbox"><input type="checkbox" name="<?php echo $this->get_field_name('showcart'); ?>" value="1" <?php  echo ($showcart=='1') ? 'checked="checked"' : ''; ?> /><?php _e('Show link to cart', 'bfi'); ?></label>
			</p>
			<p class="bookingoptions">
				<label class="checkbox"><input type="checkbox" name="<?php echo $this->get_field_name('showlogin'); ?>" value="1" <?php  echo ($showlogin=='1') ? 'checked="checked"' : ''; ?> /><?php _e('Show login as popup', 'bfi'); ?></label>
			</p>
			<p class="bookingoptions">
				<label class="checkbox"><input type="checkbox" name="<?php echo $this->get_field_name('showfavorites'); ?>" value="1" <?php  echo ($showfavorites=='1') ? 'checked="checked"' : ''; ?> /><?php _e('Show link to Travel planner', 'bfi'); ?></label>
			</p>
		
		<?php 
				
		}
		// update widget
		function update($new_instance, $old_instance) {

			  $instance = $old_instance;
			  // Fields
			  $instance['title'] = strip_tags($new_instance['title']);
			  $instance['showcurrency'] =! empty( $new_instance[ 'showcurrency' ] ) ? 1 : 0;
			  $instance['showcart'] =! empty( $new_instance[ 'showcart' ] ) ? 1 : 0;
			  $instance['showlogin'] =! empty( $new_instance[ 'showlogin' ] ) ? 1 : 0;
			  $instance['showfavorites'] =! empty( $new_instance[ 'showfavorites' ] ) ? 1 : 0;
			 return $instance;
		}

	}
}