<?php
/**
 * BookingFor Widget Functions
 *
 * Widget related functions and widget registration.
 *
 * @author		BookingFor
 * @category	Core
 * @package		BookingFor/Functions
 * @version     2.0.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Include widget classes.
include_once( 'widgets/class-bfi-widget-search-filters.php' );
include_once( 'widgets/class-bfi-widget-search-events.php' );
include_once( 'widgets/class-bfi-widget-merchant-vcard.php' );
include_once( 'widgets/class-bfi-widget-merchant.php' );
include_once( 'widgets/class-bfi-widget-carouselevents.php' );
include_once( 'widgets/class-bfi-widget-carouselpoi.php' );
include_once( 'widgets/class-bfi-widget-carouselresources.php' );
include_once( 'widgets/class-bfi-widget-booking-search.php' );
include_once( 'widgets/class-bfi-widget-booking-currency-switcher.php' );
include_once( 'widgets/class-bfi-widget-booking-cart.php' );
include_once( 'widgets/class-bfi-widget-booking-login.php' );
include_once( 'widgets/class-bfi-widget-booking-headerlink.php' );
include_once( 'widgets/class-bfi-widget-booking-reviews.php' );
include_once( 'widgets/class-bfi-widget-smallmap.php' );
include_once( 'widgets/class-bfi-widget-search-resources.php' );
include_once( 'widgets/class-bfi-widget-search-mapsells.php' );
include_once( 'widgets/class-bfi-widget-search-rental.php' );
include_once( 'widgets/class-bfi-widget-search-slot.php' );
include_once( 'widgets/class-bfi-widget-search-experience.php' );

/**
 * Register Widgets.
 *
 * @since 2.3.0
 */
function bfi_register_widgets() {
	register_widget( 'BFI_Widget_Booking_search' );
	register_widget( 'BFI_Widget_Search_Events' );
//	register_widget( 'BFI_Widget_Search_Filters' );
//	register_widget( 'BFI_Widget_Merchant_Vcard' );
	register_widget( 'BFI_Widget_Merchants' );
	register_widget( 'BFI_Widget_CarouselEvents' );
	register_widget( 'BFI_Widget_CarouselPoi' );
	register_widget( 'BFI_Widget_CarouselResources' );
	register_widget( 'BFI_Widget_Currency_Switcher' );
	register_widget( 'BFI_Widget_Cart' );
	register_widget( 'BFI_Widget_Login' );
	register_widget( 'BFI_Widget_Headerlink' );
//	register_widget( 'BFI_Widget_Reviews' );
//	register_widget( 'BFI_Widget_SmallMap' );
	register_widget( 'BFI_Widget_Search_MapSells' );
	register_widget( 'BFI_Widget_Booking_Search_Rental' );
	register_widget( 'BFI_Widget_Booking_Search_Slot' );
	register_widget( 'BFI_Widget_Booking_Search_Experience' );
	
	register_widget( 'BFI_Widget_Booking_Search_Resources' );

$bfiSidebars = array ( 
	'bfisidebar' => array(
						'id' =>	'bfisidebar',
						'name' =>	__( 'bfisidebar', 'bfi' ),
						'widgets' => array
								(
									'bookingfor_booking_search_resources-3'
								)
						),
	'bfisidebarrental' => array(
						'id' =>	'bfisidebarrental',
						'name' =>	__( 'bfisidebar Rental', 'bfi' ),
						'widgets' => array
								(
									'bookingfor_booking_search_rental-2'
								)
						),
	'bfisidebarmapsells' => array(
						'id' =>	'bfisidebarmapsells',
						'name' =>	__( 'bfisidebar MapSell', 'bfi' ),
						'widgets' => array
								(
									'bookingfor_search_mapsells-2'
								)
						),
	'bfisidebarslots' => array(
						'id' =>	'bfisidebarslots',
						'name' =>	__( 'bfisidebar Time Slots', 'bfi' ),
						'widgets' => array
								(
									'bookingfor_booking_search_slot-2'
								)
						),
	'bfisidebarexperience' => array(
						'id' =>	'bfisidebarexperience',
						'name' =>	__( 'bfisidebarExperience', 'bfi' ),
						'widgets' => array
								(
									'bookingfor_booking_search_bfisidebarexperience-2'
								)
						),
	'bookingforsearch' => array(
						'id' =>	'bookingforsearch',
						'name' =>	__( 'bfisidebar', 'bfi' ),
						'widgets' => array
								(
									'bookingfor_booking_search_resources-2'
								)
						),
	'bfisidebarhidden' => array(
						'id' =>	'bfisidebarhidden',
						'name' =>	__( 'bfisidebar Hidden', 'bfi' ),
						'widgets' => array
								(
									'bookingfor_booking_search-3',
									'bookingfor_booking_search_slot-3',
									'bookingfor_booking_search_rental-3',
									'bookingfor_booking_search-4',
									'bookingfor_search_mapsells-3',
								)
						),
	);
	foreach ( $bfiSidebars as $sidebarId => $sidebar )
		{
			register_sidebar(
				array (
					'id'            => $sidebarId,
					'name'          => $sidebar['name'],
					'description'   => __( 'Add widgets here to appear in Bookingfor pages. (<b><u>NOT delete or rename</u></b>)', 'bfi' ),
					'before_widget' => '<section id="%1$s" class="widget %2$s">',
					'after_widget'  => '</section>',
					'before_title'  => '<h2 class="widget-title">',
					'after_title'   => '</h2>',
				)
			);
		}

	$active_widgets = get_option( 'sidebars_widgets' );

//echo "<pre>";
//echo print_r($active_widgets);
//echo "</pre>";
//
	// devo controllare se esistono i widget con le opzioni e poi li posso associare alle sidebar
// i singoli wigdet sono contenuti nelle classi base  es: bookingfor_booking_search_slot-3  è in  get_option( 'bookingfor_booking_search_slot' ) che è un array di dati...
// da creare un file di configurazione ad ok per ongi widget



//	// ciclo per le sidebar per vedere se sono vuote
//	foreach ( $bfiSidebars as $sidebarId => $sidebar )
//		{
//			// We don't want to undo user changes, so we look for changes first.
//			if ( ! empty ( $active_widgets[ $sidebarId ] )) {
//				$active_widgets[ $sidebarId ] = $sidebar['widgets'];
//			}
//		}
//
//	// Now save the $active_widgets array.
//    update_option( 'sidebars_widgets', $active_widgets );
	
}
add_action( 'widgets_init', 'bfi_register_widgets' );


//	register_sidebar(
//		array(
//			'name'          => __( 'bfisidebar', 'bfi' ),
//			'id'            => 'bfisidebar',
//			'description'   => __( 'Add widgets here to appear in Bookingfor pages. (<b><u>NOT delete or rename</u></b>)', 'bfi' ),
//			'before_widget' => '<section id="%1$s" class="widget %2$s">',
//			'after_widget'  => '</section>',
//			'before_title'  => '<h2 class="widget-title">',
//			'after_title'   => '</h2>',
//		)
//	);
//	register_sidebar(
//		array(
//			'name'          => __( 'bfisidebar Rental', 'bfi' ),
//			'id'            => 'bfisidebarrental',
//			'description'   => __( 'Add widgets here to appear in Bookingfor pages. (<b><u>NOT delete or rename</u></b>)', 'bfi' ),
//			'before_widget' => '<section id="%1$s" class="widget %2$s">',
//			'after_widget'  => '</section>',
//			'before_title'  => '<h2 class="widget-title">',
//			'after_title'   => '</h2>',
//		)
//	);
//	register_sidebar(
//		array(
//			'name'          => __( 'bfisidebar MapSell', 'bfi' ),
//			'id'            => 'bfisidebarmapsells',
//			'description'   => __( 'Add widgets here to appear in Bookingfor pages. (<b><u>NOT delete or rename</u></b>)', 'bfi' ),
//			'before_widget' => '<section id="%1$s" class="widget %2$s">',
//			'after_widget'  => '</section>',
//			'before_title'  => '<h2 class="widget-title">',
//			'after_title'   => '</h2>',
//		)
//	);
//	register_sidebar(
//		array(
//			'name'          => __( 'bfisidebar Time Slots', 'bfi' ),
//			'id'            => 'bfisidebarslots',
//			'description'   => __( 'Add widgets here to appear in Bookingfor pages. (<b><u>NOT delete or rename</u></b>)', 'bfi' ),
//			'before_widget' => '<section id="%1$s" class="widget %2$s">',
//			'after_widget'  => '</section>',
//			'before_title'  => '<h2 class="widget-title">',
//			'after_title'   => '</h2>',
//		)
//	);