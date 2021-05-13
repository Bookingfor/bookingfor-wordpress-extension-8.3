<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$isbot = false;
//define( "DONOTCACHEPAGE", true ); // Do not cache this page
	if(!empty( COM_BOOKINGFORCONNECTOR_CRAWLER )){
		$listCrawler = json_decode(COM_BOOKINGFORCONNECTOR_CRAWLER , true);
		foreach( $listCrawler as $key=>$crawler){
		if (preg_match('/'.$crawler['pattern'].'/', $_SERVER['HTTP_USER_AGENT'])) $isbot = true;
		}
		
	}
if (!$isbot) {
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
$currModID = uniqid('bfisearchmapsells');
// get searchresult page...
$searchAvailability_page = get_post( bfi_get_page_id( 'searchavailability' ) );
$resultpageidDefault = $searchAvailability_page->ID;
$resultpageid = bfi_get_translated_page_id(( ! empty( $instance['resultpageid'] ) ) ? esc_attr($instance['resultpageid']) : $resultpageidDefault);

$url_page_Resources = get_permalink( $resultpageid );

$widgettoshow =  ( ! empty( $instance['widgettoshow'] ) ) ? $instance['widgettoshow'] : '';


}

if(BFI()->isSearchPage()){
	bfi_setSessionFromSubmittedData('search.params.mapsells');
}
$parsResource = BFCHelper::getSearchParamsSession('search.params.mapsells');

$stateIds = "";
$regionIds = "";
$cityIds = "";
$zoneIds = '';
$categoryIds = '';
$tagids = '';
$merchantIds = "";
$searchterm = '';
$searchTermValue = '';

$checkoutspan = '+1 day';
$checkin = new DateTime('UTC');
$checkout = new DateTime('UTC');

$checkinId = uniqid('checkin');
$checkoutId = uniqid('checkout');

$bookableonly = 0;
//$showdirection =0;
$showdirection = ( ! empty( $instance['showdirection'] ) ) ? esc_attr($instance['showdirection']) : '0';
$showDateRange = (! empty( $instance['showDateRange'] ) ) ? esc_attr($instance['showDateRange']) : '1';
$showDateTimeRange = (! empty( $instance['showDateTimeRange'] ) ) ? esc_attr($instance['showDateTimeRange']) : '0';
$showDateOneDays = (! empty( $instance['showDateOneDays'] ) ) ? esc_attr($instance['showDateOneDays']) : '1';
$startDateTimeRange = ( ! empty( $instance['startDateTimeRange'] ) ) ? ($instance['startDateTimeRange']) : '00:00';
$endDateTimeRange = ( ! empty( $instance['endDateTimeRange'] ) ) ? ($instance['endDateTimeRange']) : '24:00';
$fixedontop= ( ! empty( $instance['fixedontop'] ) ) ? esc_attr($instance['fixedontop']) : '0';
$fixedontopcorrection= ( ! empty( $instance['fixedontopcorrection'] ) ) ? esc_attr($instance['fixedontopcorrection']) : '0';
$showSearchText = (! empty( $instance['showSearchText'] ) ) ? esc_attr($instance['showSearchText']) : '1';
$searchTextFields = '6,11,13,14,15,17,18';
if(!empty($instance['searchTextFields']) && count($instance['searchTextFields'])>0){
	$searchTextFields = implode(',', $instance['searchTextFields']) ;
}

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
	$categoryIds = isset($parsResource['categoryIds']) ? $parsResource['categoryIds']: "";
	$tagids = isset($parsResource['tagids']) ? $parsResource['tagids']: "";
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
// Check if title is set
//if (!empty($title)) {
//	  echo $before_title . $title . $after_title;
//}

$fixedonbottom= ( ! empty( $instance['fixedonbottom'] ) ) ? ($instance['fixedonbottom']) : '0';
if (!empty(COM_BOOKINGFORCONNECTOR_ISMOBILE )) {
	$fixedonbottom = 1;    
}
$resultinsamepg = ( ! empty( $instance['resultinsamepg'] ) ) ? esc_attr($instance['resultinsamepg']) : '0';
$currId =  ( ! empty( $instance['currid'] ) ) ? $instance['currid'] : uniqid('currid');
if ($currId == 'REPLACE_TO_ID') { // fix for elementor
    $currId =   uniqid('currid');
}
$currModID = 'mapsells'. $currId;


?>
	<div class="bfi-mod-bookingforsearchmapsells <?php echo ( ! empty( $fixedonbottom ) ) ? 'bfiAffixBottom' : '' ?>" id="bfisearchmapsells<?php echo $currModID ?>" >
			<form action="<?php echo $url_page_Resources ?>" method="get"" method="get" id="searchformmapsells<?php echo $currModID ?>" class="bfi-form-mapsells bfi-form-default bfi-dateform-container bfi-form-<?php echo $showdirection?"horizontal":"vertical"; ?> " 
				data-blockdays="<?php echo $blockdays;?>"
				data-blockmonths="<?php echo $blockmonths;?>"
				data-currmodid="<?php echo $currModID;?>"
				data-showdaterange="<?php echo $showDateRange;?>"
				data-showdatetimerange="<?php echo $showDateTimeRange;?>"
				data-showdirection="<?php echo $showdirection;?>"
				data-fixedontop="<?php echo $fixedontop;?>"
				data-showsearchtext="<?php echo $showSearchText;?>"
				data-startdatetimerange="<?php echo $startDateTimeRange;?>"
				data-enddatetimerange="<?php echo $endDateTimeRange;?>"
			>
<?php if($showdirection && $showDateOneDays) { ?>
				<div class="bfi-row">
						<div class="bfi-showdaterangedays bfi-col-sm-12 bfi-text-left">
						<?php 
							$tmpcheckin = clone $checkin;
							$tmpcheckin->modify($checkoutspan);
							$oneDays= (($tmpcheckin->format('d/m/Y')) ==$checkout->format('d/m/Y'));
						?>
							<input type="radio" class="bfi-changedays-widget" name="bfi-select-days" <?php echo ($oneDays) ?"checked":""; ?> value="1"/><label><?php echo _e('One day','bfi') ?> </label>
							<input type="radio" class="bfi-changedays-widget" name="bfi-select-days" <?php echo ($oneDays) ?"":"checked"; ?> value="2"/><label><?php echo _e('More days','bfi') ?> </label>
						</div>
				</div>
<?php } ?>

				<div class="bfi-row">
					<div class="bfi_destination bfi-col-sm-2">
						<!-- <label><?php _e('Search text', 'bfi') ?></label> -->
						<input type="text" id="searchtextonsell<?php echo $currModID ?>" name="searchterm" value="<?php echo $searchterm ?>" class="bfi-inputtext bfi-autocomplete" placeholder="<?php _e('Search text', 'bfi') ?>" data-scope="<?php echo $searchTextFields ?>" inputmode="search" />
					</div>
					<?php if(!$showdirection && $showDateOneDays) { ?>
						<div class="bfi-showdaterangedays bfi-col-sm-2">
						<?php 
							$tmpcheckin = clone $checkin;
							$tmpcheckin->modify($checkoutspan);
							$oneDays= (($tmpcheckin->format('d/m/Y')) ==$checkout->format('d/m/Y'));
						?>
							<input type="radio" class="bfi-changedays-widget" name="bfi-select-days" <?php echo ($oneDays) ?"checked":""; ?> value="1"/><label><?php echo _e('One day','bfi') ?> </label>
							<input type="radio" class="bfi-changedays-widget" name="bfi-select-days" <?php echo ($oneDays) ?"":"checked"; ?> value="2"/><label><?php echo _e('More days','bfi') ?> </label>
						</div>
					<?php } ?>
					
					<div class="bfi-showdaterange bfi-col-sm-2">
						<label><?php _e('From' , 'bfi' ); ?></label>
						<div class="bfi-datepicker">
							<i class="fa fa-calendar"></i> <input name="checkin" type="text" value="<?php echo $checkin->format('d/m/Y'); ?>" class="bfidate bfistart bfi-checkin-field"/>
						</div>
					</div>
					<div class="bfi-showdaterange bfi-checkout-field-container bfi-col-sm-2" id="divcheckoutsearch<?php echo $currModID ?>"style="display:<?php echo ($oneDays) ?"none":""; ?>">
						<label><?php _e('To' , 'bfi' ); ?></label>
						<div class="bfi-datepicker">
							<i class="fa fa-calendar"></i> <input type="text" name="checkout" value="<?php echo $checkout->format('d/m/Y'); ?>" class="bfidate bfiend bfi-checkout-field"/>
						</div>
					</div>
					<div class="bfi-searchbutton-wrapper bfi-col-sm-2" id="divBtnResource<?php echo $currModID ?>">
						<a   class="bfi-btnsendform bfi-btn " href="javascript: void(0);"><i class="fa fa-search" aria-hidden="true"></i> <?php _e('Search', 'bfi') ?></a>
					</div>
				</div>
				<div class="bfi-powered"><a href="https://www.bookingfor.com" target="_blank">Powered by Bookingfor</a></div>
				<input type="hidden" value="1" name="newsearch" />
				<input type="hidden" value="<?php echo uniqid('', true)?>" name="searchid" />
				<input type="hidden" value="<?php echo uniqid('', true)?>" name="mapsells" />
				<input type="hidden" value="<?php echo $searchTermValue ?>" name="searchTermValue" />
				<input type="hidden" name="filter_order" value="" />
				<input type="hidden" name="filter_order_Dir" value="" />
				<input type="hidden" name="groupresulttype" value="2" />
				<input type="hidden" name="availabilitytype" value="0,2,3" />
				<input type="hidden" name="itemtypes" value="<?php echo bfi_ItemType::Beach ?>" />
				<input type="hidden" name="getallresults" value="0" />
				<input type="hidden" name="checkFullPeriod" value="0" />
				<input type="hidden" name="resview" value="mapsells" />
				<input type="hidden" name="resultinsamepg" value="<?php echo $resultinsamepg ?>" />
				<input type="hidden" name="dateselected" value="1" />
				<input type="hidden" value="<?php echo $widgettoshow ?>" name="widgettoshow" />
			</form>
			<div class="bficontainerviewform">
				<a class="bfiviewform" rel="bfi-form-mapsells"><i class="fa fa-search"></i> <?php _e('Search', 'bfi') ?> </a>
			</div>
</div>
<?php 
if (!empty($after_widget)) {
	echo $after_widget;
    }
?>