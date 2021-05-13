<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
	if(!empty( COM_BOOKINGFORCONNECTOR_CRAWLER )){
		$listCrawler = json_decode(COM_BOOKINGFORCONNECTOR_CRAWLER , true);
		foreach( $listCrawler as $key=>$crawler){
		if (preg_match('/'.$crawler['pattern'].'/', $_SERVER['HTTP_USER_AGENT'])) exit;
		}
		
	}
if (COM_BOOKINGFORCONNECTOR_USE_OPENSTREETMAP) {
	wp_enqueue_script('bfileaflet');
	wp_enqueue_script('bfileafletcontrolcustom');
}

$currencyclass = bfi_get_currentCurrency();
$page = bfi_get_current_page() ;
$start = ($page - 1) * COM_BOOKINGFORCONNECTOR_ITEMPERPAGE;

$listName = "";
$listNameAnalytics = 0;

	bfi_setSessionFromSubmittedDataEvent();
	$searchmodel = new BookingForConnectorModelSearchEvent;
	$pars = BFCHelper::getSearchEventParamsSession();
	$filterinsession = null;
	$items =  array();
	$total = 0;
	$currSorting = "";
	if (isset($pars['checkin']) && isset($pars['checkout'])){
		$now = new DateTime('UTC');
		$now->setTime(0,0,0);
		$checkin = isset($pars['checkin']) ? $pars['checkin'] : new DateTime('UTC');
		$checkout = isset($pars['checkout']) ? $pars['checkout'] : new DateTime('UTC');
		if ( $checkin == $checkout || $checkin->diff($checkout)->format("%a") <0 || $checkin < $now ){
			$nodata = true;
		}else{
			if (empty($GLOBALS['bfEventSearched'])) {
				$filterinsession = BFCHelper::getFilterSearchEventParamsSession();
				$items = $searchmodel->getItems(false, false, $start,COM_BOOKINGFORCONNECTOR_ITEMPERPAGE);
				$items = is_array($items) ? $items : array();
				$total=$searchmodel->getTotal();
				$currSorting=$searchmodel->getOrdering() . "|" . $searchmodel->getDirection();
				$GLOBALS['bfEventSearched'] = 1;
			}else{
				$items = $GLOBALS['bfEventSearchedItems'];
				$total = $GLOBALS['bfEventSearchedItemsTotal'];
				$currSorting = $GLOBALS['bfEventSearchedItemsCurrSorting'];
			}
		}

	}
//if(!isset($_GET['task'])) {
if(!isset($_POST['format'])) {
	get_header( 'searchevents' ); ?>
					<div class="bfi-row bfi-rowcontainer">
						<div class="bfi-col-md-3 bfi-col-sm-3 bfi-col-xs-12  bfi-sidebar">
							<?php
							if (!COM_BOOKINGFORCONNECTOR_ISMOBILE) {							
								bfi_get_template("widgets/booking-searchevents.php");	
								bfi_get_template("widgets/smallmap.php");	
							}
							bfi_get_template("widgets/search-filter-events.php");	
							if (!COM_BOOKINGFORCONNECTOR_ISMOBILE) {							
							?>
							<div class="bfilastmerchants bfi-hide">
								<h3><?php _e('Recently seen', 'bfi') ?></h3>
							</div>
							<?php } ?>
						</div>
						<div class="bfi-col-md-9 bfi-col-xs-12 bfi-col-sm-9  bfi-body">
		<?php
			/**
			 * bookingfor_before_main_content hook.
			 *
			 * @hooked bookingfor_output_content_wrapper - 10 (outputs opening divs for the content)
			 * @hooked bookingfor_breadcrumb - 20
			 */
			do_action( 'bookingfor_before_main_content' );
		?>
		<?php if ( apply_filters( 'bookingfor_show_page_title', true ) ) { ?>
		<?php } ?>
	<?php
}


$pages = ceil($total / COM_BOOKINGFORCONNECTOR_ITEMPERPAGE);



//********** CRITEO E ANALYTICS **********//
//********** END CRITEO E ANALYTICS **********//
				bfi_get_template("search/event.php",array("total"=>$total,"items"=>$items,"pages"=>$pages,"page"=>$page,"listNameAnalytics"=>$listNameAnalytics,"currSorting"=>$currSorting));	

if(!isset($_POST['format'])) {

?>	
	<?php
		/**
		 * bookingfor_after_main_content hook.
		 *
		 * @hooked bookingfor_output_content_wrapper_end - 10 (outputs closing divs for the content)
		 */
		do_action( 'bookingfor_after_main_content' );
	?>
	
	<?php
		/**
		 * bookingfor_sidebar hook.
		 *
		 * @hooked bookingfor_get_sidebar - 10
		 */
//		do_action( 'bookingfor_sidebar' );
	?>


						</div>
					<?php 
						if (COM_BOOKINGFORCONNECTOR_ISMOBILE) {
					?>
						<div class="bfi-col-md-3 bfi-col-sm-3 bfi-col-xs-12  bfi-sidebar">
							<?php 
								bfi_get_template("widgets/booking-searchevents.php");	
							?>
						</div>
					<?php 
						}
					?>						
					</div>
<?php get_footer( 'searchevents' ); ?>
<?php
}
?>	
