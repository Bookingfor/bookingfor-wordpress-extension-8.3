<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$base_url = get_site_url();

$language = $GLOBALS['bfi_lang'];
$languageForm ='';
if(defined('ICL_LANGUAGE_CODE') &&  class_exists('SitePress')){
		global $sitepress;
		if($sitepress->get_current_language() != $sitepress->get_default_language()){
			$languageForm = "/" .ICL_LANGUAGE_CODE;
		}
}
$isportal = COM_BOOKINGFORCONNECTOR_ISPORTAL;
$currModID = uniqid('bfisearch');

$searchAvailability_page = get_post( bfi_get_page_id( 'searchevents' ) );
$url_page_Resources = get_permalink( $searchAvailability_page->ID );
if(BFI()->isSearchEventsPage()){
	bfi_setSessionFromSubmittedDataEvent();
}
$parsResource = BFCHelper::getSearchEventParamsSession();


$stateIds = "";
$regionIds = "";
$cityIds = "";
$zoneIds = '';
$eventId = 0;
$categoryIds = '';
$tagids = '';
$eventId = 0;
$pointOfInterestId = 0;
$merchantIds = "";
$searchterm = '';
$searchTermValue = '';

$checkoutspan = '+1 day';
$checkin = new DateTime('UTC');
$checkout = new DateTime('UTC');

$checkinId = uniqid('checkin');
$checkoutId = uniqid('checkout');

$bookableonly = 0;
$showdirection =0;
//$showdirection = ( ! empty( $instance['showdirection'] ) ) ? esc_attr($instance['showdirection']) : '0';

if (!empty($parsResource)){
		
	$checkin = !empty($parsResource['checkin']) ? $parsResource['checkin'] : new DateTime('UTC');
	$checkout = !empty($parsResource['checkout']) ? $parsResource['checkout'] : new DateTime('UTC');
	if (empty($parsResource['checkout'])){
		$checkout->modify($checkoutspan);
	}
	$stateIds = isset($parsResource['stateIds']) ? $parsResource['stateIds']: "";
	$regionIds = isset($parsResource['regionIds']) ? $parsResource['regionIds']: "";
	$cityIds = isset($parsResource['cityIds']) ? $parsResource['cityIds']: "";
	$zoneIds = isset($parsResource['zoneIds']) ? $parsResource['zoneIds']: "";
	$eventId = isset($parsResource['eventId']) ? $parsResource['eventId']: 0;
	$categoryIds = isset($parsResource['categoryIds']) ? $parsResource['categoryIds']: "";
	$tagids = isset($parsResource['tagids']) ? $parsResource['tagids']: "";
	$pointOfInterestId = isset($parsResource['pointOfInterestId']) ? $parsResource['pointOfInterestId']: 0;
	$merchantIds = isset($parsResource['merchantIds']) ? $parsResource['merchantIds']: "";
	$searchterm = !empty($parsResource['searchterm']) ? $parsResource['searchterm'] :'';
	$searchTermValue = !empty($parsResource['searchTermValue']) ? $parsResource['searchTermValue'] :'';
}


$currModID = uniqid('currid');
$startDate =  new DateTime('UTC');

$startDate->setTime(0,0,0);
$checkin->setTime(0,0,0);
$checkout->setTime(0,0,0);

if ($checkin < $startDate){
	$checkin = $startDate;
	$checkout = clone $checkin;
    $checkout->modify($checkoutspan); 
}
if ($checkin > $checkout){
	$checkout = clone $checkin;
	$checkout->modify($checkoutspan);
}

if ($checkin == $checkout){
	$checkout->modify($checkoutspan);
}

////only for Joomla
//$checkin = new JDate($checkin->format('Y-m-d')); 
//$checkout = new JDate($checkout->format('Y-m-d')); 

$blockmonths = '14';
$blockdays = '7';
?>
<?php 
if (!empty($before_widget)) {
	echo $before_widget;
}
//// Check if title is set
//if (!empty($title)) {
//	  echo $before_title . $title . $after_title;
//}

$fixedonbottom= ( ! empty( $instance['fixedonbottom'] ) ) ? ($instance['fixedonbottom']) : '0';
if (!empty(COM_BOOKINGFORCONNECTOR_ISMOBILE )) {
	$fixedonbottom = 1;    
}
?>
	<div class="bfi-mod-bookingforsearchevent <?php echo ( ! empty( $fixedonbottom ) ) ? 'bfiAffixBottom' : '' ?>" id="bfisearchevent<?php echo $currModID ?>" >
			<form action="<?php echo $url_page_Resources; ?>" method="get" id="searchformevent<?php echo $currModID ?>" class="bfi-form-event bfi-form-default bfi-dateform-container bfi-form-<?php echo $showdirection?"horizontal":"vertical"; ?> " 
			data-blockdays="<?php echo $blockdays;?>"
			data-blockmonths="<?php echo $blockmonths;?>"
			data-currmodid="<?php echo $currModID;?>"
			>
			<div class="bfi-row">
                <h4><?php _e('Search events', 'bfi') ?></h4>
				<div class="bfi-showdaterange bfi-col-sm-2">
					<select onchange="bfisetrange(this)">
						<option value=""><?php _e('Select a period', 'bfi') ?></option>
						<option value="today"><?php _e('Today', 'bfi') ?></option>
						<option value="thisweek"><?php _e('This Week', 'bfi') ?></option>
						<option value="thismonth"><?php _e('This Month', 'bfi') ?></option>
						<option value="nextweek"><?php _e('Next Week', 'bfi') ?></option>
						<option value="nextmonth"><?php _e('Next Month', 'bfi') ?></option>
					</select>				
				</div>
				<div class="bfi-showdaterange bfi-col-sm-2">
					<label><?php _e('From' , 'bfi' ); ?></label>
					<div class="bfi-datepicker">
						<input name="checkin" type="text" value="<?php echo $checkin->format('d/m/Y'); ?>" class="bfidate bfistart bfi-checkin-field"/>
					</div>
				</div>
				<div class="bfi-showdaterange bfi-col-sm-2" id="divcheckoutsearch<?php echo $currModID ?>">
					<label><?php _e('To' , 'bfi' ); ?></label>
					<div class="bfi-datepicker">
						<input type="text" name="checkout" value="<?php echo $checkout->format('d/m/Y'); ?>" class="bfidate bfiend bfi-checkout-field"/>
					</div>
				</div>
				<div class="bfi_destination bfi-col-sm-2">
					<label><?php _e('Search text', 'bfi') ?></label>
					<input type="text" id="searchtextonsell<?php echo $currModID ?>" name="searchterm" value="<?php echo $searchterm ?>" class="bfi-inputtext bfi-autocomplete" placeholder="<?php _e('Search text', 'bfi') ?>" data-scope="0,1,2,3,4,5,6,7,8,12,16" />
				</div>
				<div class="bfi-searchbutton-wrapper bfi-col-sm-2" id="divBtnResource<?php echo $currModID ?>">
					<a   class="bfi-btnsendform bfi-btn " href="javascript: void(0);"><i class="fa fa-search" aria-hidden="true"></i> <?php _e('Search', 'bfi') ?></a>
				</div>
			</div>
				<div class="bfi-clearfix"></div>
				<div class="bfi-powered"><a href="https://www.bookingfor.com" target="_blank">Powered by Bookingfor</a></div>
				<input type="hidden" value="1" name="newsearch" />
				<input type="hidden" value="<?php echo uniqid('', true)?>" name="searchid" />
				<input type="hidden" value="<?php echo uniqid('', true)?>" name="events" />

				<input type="hidden" value="<?php echo $stateIds ?>" name="stateIds" />
				<input type="hidden" value="<?php echo $regionIds ?>" name="regionIds" />
				<input type="hidden" value="<?php echo $cityIds ?>" name="cityIds" />
				<input type="hidden" value="<?php echo $merchantIds ?>" name="merchantIds" />
				<input type="hidden" value="<?php echo $zoneIds ?>" name="zoneIds" />
				<input type="hidden" value="<?php echo $eventId ?>" name="eventId" />
				<input type="hidden" value="<?php echo $categoryIds ?>" name="categoryIds" />
				<input type="hidden" value="<?php echo $tagids ?>" name="tagids" />
				<input type="hidden" value="<?php echo $pointOfInterestId ?>" name="pointOfInterestId" />
				<input type="hidden" value="<?php echo $searchTermValue ?>" name="searchTermValue" />
				<input type="hidden" name="filter_order" value="" />
				<input type="hidden" name="filter_order_Dir" value="" />

			</form>
			<div class="bficontainerviewform">
				<a class="bfiviewform" rel="bfi-form-event"><i class="fa fa-search"></i> <?php _e('Search events', 'bfi') ?> </a>
			</div>
</div>
<?php 
if (!empty($after_widget)) {
	echo $after_widget;
    }
?>