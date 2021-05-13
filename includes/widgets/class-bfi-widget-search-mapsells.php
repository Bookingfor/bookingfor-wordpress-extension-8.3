<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Product Search Widget.
 *
 * @author   BookingFor
 * @category Widgets
 * @package  BookingFor/Widgets
 * @version     8.1.0
 * @extends  WP_Widget
 */
if ( ! class_exists( 'BFI_Widget_Search_MapSells' ) ) {
	class BFI_Widget_Search_MapSells extends WP_Widget {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->widget_cssclass    = 'bfi-widget_search_mapsells';
			$this->widget_description = __( 'A box for search on Maps.', 'bfi' );
			$this->widget_id          = 'bookingfor_search_mapsells';
			$this->widget_name        = __( 'BookingFor Search MapSells', 'bfi' );
			$this->widget_sidebar    = 'bfisidebar MapSell';
			$this->settings           = array(
				'title'  => array(
					'type'  => 'text',
					'std'   => '',
					'label' => __( 'Title', 'bfi' )
				)
			);
			$widget_ops = array(
				'classname'   => $this->widget_cssclass,
				'description' => $this->widget_description,
				'sidebar' => $this->widget_sidebar,
			);

			parent::__construct( $this->widget_id, $this->widget_name, $widget_ops );

	//		parent::__construct();
		}
			// widget form creation
			function form($instance) {
				$resultpageidDefault = bfi_get_page_id( 'searchavailability',1 );
				$resultinsamepg = ( ! empty( $instance['resultinsamepg'] ) ) ? esc_attr($instance['resultinsamepg']) : '1';
				$resultpageid = ( ! empty( $instance['resultpageid'] ) ) ? esc_attr($instance['resultpageid']) : $resultpageidDefault;
				$showdirection = ( ! empty( $instance['showdirection'] ) ) ? esc_attr($instance['showdirection']) : '1';
		
				$widgettoshowSelected = ( ! empty( $instance['widgettoshow'] ) ) ? esc_attr($instance['widgettoshow']) : '';
				// aggiunta id del widget nel titolo
				if ($this->number=="__i__"){
				}  else {
					$instance[ 'title' ] = $this->number ;
				}

			?>
				<p class="">
					<label class="checkbox"><input type="checkbox" name="<?php echo $this->get_field_name('showdirection'); ?>" value="1" <?php  echo ($showdirection=='1') ? 'checked="checked"' : ''; ?> /><?php _e('Displays horizontally', 'bfi'); ?></label>
				</p>
			<?php 

				if ($this->number=="__i__"){
				//echo "<p><strong>Widget ID is</strong>: Please save the widget</p>"   ;
				}  else {
				?>
					ID: <b><?php echo $this->widget_id ?>-<?php echo $this->number ?></b>
					<p class="">
						<label class="checkbox"><input type="checkbox" name="<?php echo $this->get_field_name('resultinsamepg'); ?>" value="1" <?php  echo ($resultinsamepg=='1') ? 'checked="checked"' : ''; ?> /><?php _e('Pagina dei risultati: ', 'bfi'); ?></label>
						
						<label for="<?php echo $this->get_field_id('resultpageid'); ?>"><?php _e('Select', 'bfi'); ?>
							<?php 
								  $argsPage = array(
									  'depth' => 0,
									  'child_of' => 0, 
									  'echo' => 1, 
									  'exclude' => '', 
									  'exclude_tree' => '',
									  'hierarchical' => 1, 
									  'class' => 'widefat select2full',
									  'name' => $this->get_field_name('resultpageid'),
									  'id' => $this->get_field_id('resultpageid'),
									  'post_type' => 'page',
									  'selected' => bfi_get_translated_page_id($resultpageid),  //$resultpageid,
									  'post_status' => 'publish',
									  'sort_column' => 'post_title',
									  'sort_order' => 'ASC'
								  );
				if ( ! function_exists( 'my_list_pages_result_default' ) ) {
					function my_list_pages_result_default( $title, $page ) {
						$searchAvailability_pageID = bfi_get_page_id( 'searchavailability',1 );
								
						if ($page->ID == $searchAvailability_pageID) {
							$title = $title . ' (default) ';
						}
						return $title;
					}
				}
				add_filter( 'list_pages', 'my_list_pages_result_default', 10, 2 );
								wp_dropdown_pages($argsPage);
				remove_filter( 'list_pages', 'my_list_pages_result_default', 10 );
				//				echo '</select>';
								?>
						</label>
	<?php 

		 global $wp_registered_widgets;
		 $sidebarWidget = array();
		 foreach ($wp_registered_widgets as $key=>$value) {
			 if (strrpos($key,$this->widget_id) >-1 ) {
				 array_push($sidebarWidget,$key);
			 }
		 }
	?>
			<label for="<?php echo $this->get_field_id('widgettoshow'); ?>"><?php _e('Visualizza il seguente widget nella sidebar ' . $this->widget_sidebar, 'bfi'); ?>
				<?php 
					printf(
						'<select name="%s" id="%s" class="widefat">',
						$this->get_field_name('widgettoshow'),
						$this->get_field_id('widgettoshow')
					);
						printf(
							'<option value="" style="margin-bottom:3px;">%s</option>',
							"tutti",
							( '' == $widgettoshowSelected) ? 'selected="selected"' : '',
							''
						);
					foreach ($sidebarWidget as $value) {
						printf(
							'<option value="%s" %s style="margin-bottom:3px;">%s</option>',
							$value,
							( $value == $widgettoshowSelected) ? 'selected="selected"' : '',
							$value
						);
					}
					echo '</select>';
					?>
			</label>

					<br />
					Shortcode for page:<br />
				[bookingfor_dowidget id=<?php echo $this->widget_id ?>-<?php echo $this->number ?>]
				<?php if($resultinsamepg==1) { ?>[bookingfor_search_result]
				<?php } ?>


				<p>

				<?php 
					}
			}
			// update widget
			function update($new_instance, $old_instance) {
				$instance = $old_instance;
				// Fields
				$instance['showdirection'] =! empty( $new_instance[ 'showdirection' ] ) ? 1 : 0;
				$instance['resultinsamepg'] =! empty( $new_instance[ 'resultinsamepg' ] ) ? 1 : 0;
				$instance['resultpageid'] = ! empty( $new_instance[ 'resultpageid' ] ) ?  bfi_get_default_page_id(esc_sql( $new_instance['resultpageid']) ) : "";

				$instance['widgettoshow'] = ! empty( $new_instance[ 'widgettoshow' ] ) ?  esc_sql( $new_instance['widgettoshow']) : "";
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
	//		$this->widget_start( $args, $instance );
			wp_enqueue_script('jquery-ui-core');
			extract( $args );
			// these are the widget options
			
			$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : "";
			$title = apply_filters('widget_title', $title );
			$args["title"] =  $title;
			$args["instance"] =  $instance;
			//chech widget to show
				$widgettoshowFromsearch = ( ! empty( $_REQUEST['widgettoshow'] ) ) ? ($_REQUEST['widgettoshow']) : ''; 
				if (empty($widgettoshowFromsearch) || $widgettoshowFromsearch == $args["widget_id"]) {
					bfi_get_template("widgets/booking-searchmapsells.php",$args);	
				}
	//		$this->widget_end( $args );
		}
	}
}