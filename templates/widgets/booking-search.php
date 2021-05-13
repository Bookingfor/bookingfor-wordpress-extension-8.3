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
$currModID = uniqid('bfisearch');

// get searchresult page...
$searchOnSell_page = get_post( bfi_get_page_id( 'searchonsell' ) );
$url_page_RealEstate = get_permalink( $searchOnSell_page->ID );

$searchAvailability_page = get_post( bfi_get_page_id( 'searchavailability' ) );
$resultpageidDefault = $searchAvailability_page->ID;
$resultpageid = bfi_get_translated_page_id(( ! empty( $instance['resultpageid'] ) ) ? esc_attr($instance['resultpageid']) : $resultpageidDefault);

$url_page_Resources = get_permalink( $resultpageid );

if(BFI()->isSearchPage()){
	bfi_setSessionFromSubmittedData('search.params');
}
if(BFI()->isSearchOnSellPage()){
    $searchmodel = new BookingForConnectorModelSearchOnSell;
	$searchmodel->setItemPerPage(COM_BOOKINGFORCONNECTOR_ITEMPERPAGE);
	$searchmodel->populateState();
}

$parsRealEstate = BFCHelper::getSearchOnSellParamsSession();
$parsResource = BFCHelper::getSearchParamsSession('search.params');

$searchtypetab = -1;

$contractTypeId = 0;
$searchType = "0";
$searchTypeonsell = "0";

$categoryIdRealEstate = 0;
$categoryIdResource = 0;
$merchantCategoryIdRealEstate = 0;
$merchantCategoryIdResource = 0;

$zoneId = 0;
$cityId = 0;
$pricemax = '';
$pricemin = '';
$areamin = '';
$areamax = '';
$points = '';
$pointsonsell = '';
$roomsmin = '';
$roomsmax = '';
$bathsmin = '';
$bathsmax = '';
$services = '';
$isnewbuilding='';
$zoneIdsSplitted = array();
$bedroomsmin = '';
$bedroomsmax = '';
$checkoutspan = '+1 day';
//$checkoutspan = '+0 day';
$checkin = new DateTime('UTC');
$checkout = new DateTime('UTC');
$paxes = 2;
$paxages = array();
$masterTypeId = '';
$checkinId = uniqid('checkin');
$checkoutId = uniqid('checkout');
$durationId = uniqid('duration');
$duration = 1;
$bookableonly = 0;

$stateIds = "";
$regionIds = "";
$cityIds = "";
$zoneIds = '';
$getBaseFiltersFor = "";
$merchantIds = "";
$groupresourceIds = "";
$searchterm = '';
$searchTermValue = '';
$merchantTagIds = '';
$productTagIds = '';
$merchantCategoryId = '';
$minRooms = 1;
$maxRooms = 10;

$tablistSelected = ( ! empty( $instance['tablistSelected'] ) ) ? $instance['tablistSelected'] : array();

$tablistResources = array_intersect($tablistSelected,array(0,1,2,4));
$tablistRealEstate = array_intersect($tablistSelected, array(3));
$tablistEvents = array_intersect($tablistSelected, array(5));
$tablistMapSells = array_intersect($tablistSelected, array(6));


if (!empty($tablistResources) && !empty($instance['limitRooms'])) {
	$minRooms = ( !empty($tablistResources) && ! empty( $instance['minRooms'] ) ) ? ($instance['minRooms']) : 1;
	$maxRooms = ( !empty($tablistResources) && ! empty( $instance['maxRooms'] ) ) ? ($instance['maxRooms']) : 10;
}
$minrooms = $minRooms;
$maxrooms = $maxRooms;
$minqt = 1;
$currFilterOrder = "";
$currFilterOrderDirection = "";

if (!empty($parsResource)){
		
	$currFilterOrder = !empty($parsResource['filter_order']) ? $parsResource['filter_order'] : "";
	$currFilterOrderDirection = !empty($parsResource['filter_order_Dir']) ? $parsResource['filter_order_Dir'] : "";

	$checkin = !empty($parsResource['checkin']) ? $parsResource['checkin'] : new DateTime('UTC');
	$checkout = !empty($parsResource['checkout']) ? $parsResource['checkout'] : new DateTime('UTC');
	
//	$searchtypetab = isset($parsResource['searchtypetab']) ? $parsResource['searchtypetab'] : -1;
//	$availabilitytype = isset($parsResource['availabilitytype']) ? $parsResource['availabilitytype'] : 1;
	$searchtypetab = BFCHelper::getVar('searchtypetab',(isset($parsResource['searchtypetab']) ? $parsResource['searchtypetab'] : -1));
	$searchType = isset($parsResource['searchType']) ? $parsResource['searchType'] : 0;
	$points = isset($parsResource['points']) ? $parsResource['points']: null;

	$zoneId = !empty($parsResource['zoneId']) ? $parsResource['zoneId'] :0;
	$minqt = !empty($parsResource['minqt']) ? $parsResource['minqt'] : 1;
	$maxqt = !empty($parsResource['maxqt']) ? $parsResource['maxqt'] : 10;
	$minrooms = !empty($parsResource['minrooms']) ? $parsResource['minrooms'] : 1;
	$maxrooms = !empty($parsResource['maxrooms']) ? $parsResource['maxrooms'] : 10;
	$paxes = !empty($parsResource['paxes']) ? $parsResource['paxes'] : 2;
	$paxages = !empty($parsResource['paxages'])? $parsResource['paxages'] :  array('18','18');
	$merchantCategoryIdResource = !empty($parsResource['merchantCategoryId'])? $parsResource['merchantCategoryId']: 0;
	$masterTypeId = !empty($parsResource['masterTypeId'])? $parsResource['masterTypeId']: 0;
	$merchantIds = isset($parsResource['merchantIds']) ? $parsResource['merchantIds']: "";
	$groupresourceIds = isset($parsResource['groupresourceIds']) ? $parsResource['groupresourceIds']: "";
	$searchterm = !empty($parsResource['searchterm']) ? $parsResource['searchterm'] :'';
	$searchTermValue = !empty($parsResource['searchTermValue']) ? $parsResource['searchTermValue'] :'';
	$stateIds = isset($parsResource['stateIds']) ? $parsResource['stateIds']: "";
	$regionIds = isset($parsResource['regionIds']) ? $parsResource['regionIds']: "";
	$cityIds = isset($parsResource['cityIds']) ? $parsResource['cityIds']: "";
	$zoneIds = isset($parsResource['zoneIds']) ? $parsResource['zoneIds']: "";
	$getBaseFiltersFor = isset($parsResource['getBaseFiltersFor']) ? $parsResource['getBaseFiltersFor']: "";

	$merchantTagIds = isset($parsResource['merchantTagIds']) ? $parsResource['merchantTagIds']: "";
	$productTagIds = isset($parsResource['productTagIds']) ? $parsResource['productTagIds']: "";
	$merchantCategoryId = isset($parsResource['merchantCategoryId']) ? $parsResource['merchantCategoryId']: "";

	if (empty($parsResource['checkout'])){
		$checkout->modify($checkoutspan);
	}
}

if (!empty($parsRealEstate)){
	$contractTypeId = isset($parsRealEstate['contractTypeId']) ? $parsRealEstate['contractTypeId'] : 0;
	$categoryIdRealEstate = isset($parsRealEstate['unitCategoryId']) ? $parsRealEstate['unitCategoryId']: 0;

	$zoneId = isset($parsRealEstate['zoneId']) ? $parsRealEstate['zoneId'] :0;

	if(!empty($parsRealEstate['cityId'])){
		$cityId = $parsRealEstate['cityId'] ?: 0;
	}
	$searchTypeonsell = isset($parsRealEstate['searchType']) ? $parsRealEstate['searchType'] : 0;
//	$searchtypetab = isset($parsRealEstate['searchtypetab']) ? $parsRealEstate['searchtypetab'] : -1;
	$searchtypetab = BFCHelper::getVar('searchtypetab',(isset($parsRealEstate['searchtypetab']) ? $parsRealEstate['searchtypetab'] : -1));

	if(!empty($parsRealEstate['zoneIds'])){
		$zoneIds = $parsRealEstate['zoneIds'];
		$zoneIdsSplitted = explode(",",$zoneIds);
	}
	$pricemax = isset($parsRealEstate['pricemax']) ? $parsRealEstate['pricemax']: null;
	$pricemin = isset($parsRealEstate['pricemin']) ? $parsRealEstate['pricemin']: null;
	$areamin = isset($parsRealEstate['areamin']) ? $parsRealEstate['areamin']: null;
	$areamax = isset($parsRealEstate['areamax']) ? $parsRealEstate['areamax']: null;
	$roomsmin = isset($parsRealEstate['roomsmin']) ? $parsRealEstate['roomsmin']: null;
	$roomsmax = isset($parsRealEstate['roomsmax']) ? $parsRealEstate['roomsmax']: null;
	$bathsmin = isset($parsRealEstate['bathsmin']) ? $parsRealEstate['bathsmin']: null;
	$bathsmax = isset($parsRealEstate['bathsmax']) ? $parsRealEstate['bathsmax']: null;
	$pointsonsell = isset($parsRealEstate['points']) ? $parsRealEstate['points']: null;
	$services = isset($parsRealEstate['services']) ? $parsRealEstate['services']: null;
	if (isset($parsRealEstate['isnewbuilding']) && !empty($parsRealEstate['isnewbuilding']) && $parsRealEstate['isnewbuilding'] =="1") {
		$isnewbuilding = ' checked="checked"';
	}
	$bedroomsmin = isset($parsRealEstate['bedroomsmin']) ? $parsRealEstate['bedroomsmin']: null;
	$bedroomsmax = isset($parsRealEstate['bedroomsmax']) ? $parsRealEstate['bedroomsmax']: null;
}

//$tablistSelected = ( ! empty( $instance['tablistSelected'] ) ) ? $instance['tablistSelected'] : array();
//
//$tablistResources = array_intersect($tablistSelected,array(0,1,2));
//$tablistRealEstate = array_intersect($tablistSelected, array(3));

//if (($key = array_search(5, $tablistSelected)) !== false) {
//    unset($tablistSelected[$key]);
//}
if(!in_array($searchtypetab,$tablistSelected)){
	$searchtypetab = -1;
}

$tabiconbooking = ( ! empty( $instance['tabiconbooking'] ) ) ? ($instance['tabiconbooking']) : 'fa fa-suitcase';
$tabiconservices = ( ! empty( $instance['tabiconservices'] ) ) ? ($instance['tabiconservices']) : 'fa fa-calendar';
$tabiconactivities = ( ! empty( $instance['tabiconactivities'] ) ) ? ($instance['tabiconactivities']) : 'fa fa-calendar';
$tabiconothers = ( ! empty( $instance['tabiconothers'] ) ) ? ($instance['tabiconothers']) : 'fa fa-calendar';

$tabiconrealestate = ( ! empty( $instance['tabiconrealestate'] ) ) ? ($instance['tabiconrealestate']) : 'fa fa-home';

$tabiconevents = ( ! empty( $instance['tabiconevents'] ) ) ? ($instance['tabiconevents']) : 'fa fa-home';
$tabiconmapsell = ( ! empty( $instance['tabiconmapsell'] ) ) ? ($instance['tabiconmapsell']) : 'fa fa-home';

$showdirection = ( ! empty( $instance['showdirection'] ) ) ? esc_attr($instance['showdirection']) : '0';
$fixedontop= ( ! empty( $instance['fixedontop'] ) ) ? esc_attr($instance['fixedontop']) : '0';
$fixedontopcorrection= ( ! empty( $instance['fixedontopcorrection'] ) ) ? esc_attr($instance['fixedontopcorrection']) : '0';

if($fixedontop){
// Add styles
//$style = '.bfi-affix-top'.$currModID.'.bfiAffixTop {'
//        . 'top: '.$fixedontopcorrection.'px !important;'
//        . '}' 
//        . '.bfi-calendar-affixtop'.$currModID.'{'
//        . 'top:'.($fixedontopcorrection + 110).'px !important;'
//        . '}';
//$document->addStyleDeclaration($style);
}
$resultinsamepg = ( ! empty( $instance['resultinsamepg'] ) ) ? esc_attr($instance['resultinsamepg']) : '0';

$currId =  ( ! empty( $instance['currid'] ) ) ? $instance['currid'] : uniqid('currid');
if ($currId == 'REPLACE_TO_ID') { // fix for elementor
    $currId =   uniqid('currid');
}
$currModID = $currId;

$style = '.bfi-calendar-affixtop'.$currModID.'{'
		. 'top:'.($fixedontopcorrection + 110).'px !important; position: fixed !important;'
		. '}';

if(!empty($fixedontop)){
	// Add styles
	$style .= '.bfi-affix-top'.$currModID.'.bfiAffixTop {'
			. 'top: '.$fixedontopcorrection.'px !important;'
			. '}';
}
	echo "<style>";
	echo $style;
	echo "</style>";

$fixedonbottom= ( ! empty( $instance['fixedonbottom'] ) ) ? ($instance['fixedonbottom']) : '0';

$showLocation = ( !empty($tablistResources) && ! empty( $instance['showLocation'] ) ) ? esc_attr($instance['showLocation']) : '0';
$showMapIcon = ( !empty($tablistResources) && ! empty( $instance['showMapIcon'] )  && !empty(COM_BOOKINGFORCONNECTOR_ENABLEGOOGLEMAPSAPI) ) ? esc_attr($instance['showMapIcon']) : '0';
$showSearchText = ( !empty($tablistResources) && ! empty( $instance['showSearchText'] ) ) ? esc_attr($instance['showSearchText']) : '0';
$searchTextFields = '6,11,13,14,15,17,18';
if(!empty($instance['searchTextFields']) && count($instance['searchTextFields'])>0){
	$searchTextFields = implode(',', $instance['searchTextFields']) ;
}
$searchTextFieldsMapsell = '6,11,13,14,15,17,18';
if(!empty($instance['searchTextFieldsMapsell']) && count($instance['searchTextFieldsMapsell'])>0){
	$searchTextFieldsMapsell = implode(',', $instance['searchTextFieldsMapsell']) ;
}
$searchTextFieldsEvent = '6,11,13,14,15,17,18';
if(!empty($instance['searchTextFieldsEvent']) && count($instance['searchTextFieldsEvent'])>0){
	$searchTextFieldsEvent = implode(',', $instance['searchTextFieldsEvent']) ;
}


$showAccomodations = ( !empty($tablistResources) && ! empty( $instance['showAccomodations'] ) ) ? esc_attr($instance['showAccomodations']) : '0';
$showDateRange = ( !empty($tablistResources) && ! empty( $instance['showDateRange'] ) ) ? esc_attr($instance['showDateRange']) : '0';
$showDateTimeRange = ( !empty($tablistResources) && ! empty( $instance['showDateTimeRange'] ) ) ? esc_attr($instance['showDateTimeRange']) : '0';
$showDateOneDays = ( !empty($tablistResources) && ! empty( $instance['showDateOneDays'] ) ) ? esc_attr($instance['showDateOneDays']) : '0';
$showDateOneDaysMapSell = ( !empty($tablistMapSells) && ! empty( $instance['showDateOneDaysMapSell'] ) ) ? esc_attr($instance['showDateOneDaysMapSell']) : '0';

$startDateTimeRange = ( ! empty( $instance['startDateTimeRange'] ) ) ? ($instance['startDateTimeRange']) : '00:00';
$endDateTimeRange = ( ! empty( $instance['endDateTimeRange'] ) ) ? ($instance['endDateTimeRange']) : '24:00';

$startDate =  new DateTime('UTC');

if($showDateTimeRange){ 
	if (strpos($startDateTimeRange,":")!== false) {
		$checkinTime = explode(':',$startDateTimeRange.":0");
		$startDate->setTime((int)$checkinTime[0], (int)$checkinTime[1]); 
		$checkin->setTime((int)$checkinTime[0], (int)$checkinTime[1]); 
	}
	if (strpos($endDateTimeRange,":")!== false) {
		$checkoutTime = explode(':',$endDateTimeRange.":0");
		$checkout->setTime((int)$checkoutTime[0], (int)$checkoutTime[1]); 
	}
}else{
	$startDate->setTime(0,0,0);
	$checkin->setTime(0,0,0);
	$checkout->setTime(0,0,0);

}

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

$duration = $checkin->diff($checkout);

$onlystay = ( !empty($tablistResources) && ! empty( $instance['onlystay'] ) ) ? ($instance['onlystay']) : '0';

if($showSearchText) {
	$showLocation = '0';
	$showAccomodations = '0';
	if (empty($showSearchText)) {
	    $masterTypeId = '';
	}
}

$showResource = ( !empty($tablistResources) && ! empty( $instance['showResource'] ) ) ? ($instance['showResource']) : '0';
$minResource = 1;
$maxResource = 10;
if (!empty($tablistResources) && !empty($instance['limitResource'])) {
	$minResource = ( !empty($tablistResources) && ! empty( $instance['minResource'] ) ) ? ($instance['minResource']) : 1;
	$maxResource = ( !empty($tablistResources) && ! empty( $instance['maxResource'] ) ) ? ($instance['maxResource']) : 10;
}

$showRooms = ( !empty($tablistResources) && ! empty( $instance['showRooms'] ) ) ? ($instance['showRooms']) : '0';

/*
    $minqt = $minResource;
if ($maxqt<$minResource || $maxqt>$maxResource ) {
    $maxqt = $minResource;
}
*/
$showPerson = ( !empty($tablistResources) && ! empty( $instance['showPerson'] ) ) ? esc_attr($instance['showPerson']) : '0';
$showAdult = ( !empty($tablistResources) && ! empty( $instance['showAdult'] ) ) ? esc_attr($instance['showAdult']) : '0';
$showChildren = ( !empty($tablistResources) && ! empty( $instance['showChildren'] ) ) ? esc_attr($instance['showChildren']) : '0';
$showSenior = ( !empty($tablistResources) && ! empty( $instance['showSenior'] ) ) ? esc_attr($instance['showSenior']) : '0';
$showServices = ( !empty($tablistResources) && ! empty( $instance['showServices'] ) ) ? esc_attr($instance['showServices']) : '0';
$showOnlineBooking = ( !empty($tablistResources) && ! empty( $instance['showOnlineBooking'] ) ) ? esc_attr($instance['showOnlineBooking']) : '0';

$showSearchTextOnSell = ( !empty($tablistRealEstate) && ! empty( $instance['showSearchTextOnSell'] ) ) ? esc_attr($instance['showSearchTextOnSell']) : '0';
$showMapIconOnSell = ( !empty($tablistRealEstate) && ! empty( $instance['showMapIconOnSell'] ) ) ? esc_attr($instance['showMapIconOnSell']) : '0';
$showAccomodationsOnSell = ( !empty($tablistRealEstate) && ! empty( $instance['showAccomodationsOnSell'] ) ) ? esc_attr($instance['showAccomodationsOnSell']) : '0';
$showMaxPrice = ( !empty($tablistRealEstate) && ! empty( $instance['showMaxPrice'] ) ) ? esc_attr($instance['showMaxPrice']) : '0';
$showMinFloor = ( !empty($tablistRealEstate) && ! empty( $instance['showMinFloor'] ) ) ? esc_attr($instance['showMinFloor']) : '0';
$showContract = ( !empty($tablistRealEstate) && ! empty( $instance['showContract'] ) ) ? esc_attr($instance['showContract']) : '0';
$showBedRooms = ( !empty($tablistRealEstate) && ! empty( $instance['showBedRooms'] ) ) ? esc_attr($instance['showBedRooms']) : '0';
//$showRooms = ( !empty($tablistRealEstate) && ! empty( $instance['showRooms'] ) ) ? esc_attr($instance['showRooms']) : '0';
$showBaths = ( !empty($tablistRealEstate) && ! empty( $instance['showBaths'] ) ) ? esc_attr($instance['showBaths']) : '0';
$showOnlyNew = ( !empty($tablistRealEstate) && ! empty( $instance['showOnlyNew'] ) ) ? esc_attr($instance['showOnlyNew']) : '0';
$showServicesList = ( !empty($tablistRealEstate) && ! empty( $instance['showServicesList'] ) ) ? esc_attr($instance['showServicesList']) : '0';

$merchantCategoriesSelectedBooking = ( ! empty( $instance['merchantcategoriesbooking'] ) ) ? $instance['merchantcategoriesbooking'] : array();
$merchantCategoriesSelectedServices = ( ! empty( $instance['merchantcategoriesservices'] ) ) ? $instance['merchantcategoriesservices'] : array();
$merchantCategoriesSelectedActivities = ( ! empty( $instance['merchantcategoriesactivities'] ) ) ? $instance['merchantcategoriesactivities'] : array();
$merchantCategoriesSelectedOthers = ( ! empty( $instance['merchantcategoriesothers'] ) ) ? $instance['merchantcategoriesothers'] : array();
$merchantCategoriesSelectedRealEstate = ( ! empty( $instance['merchantcategoriesrealestate'] ) ) ? $instance['merchantcategoriesrealestate'] : array();

$unitCategoriesSelectedBooking = ( ! empty( $instance['unitcategoriesbooking'] ) ) ? $instance['unitcategoriesbooking'] : array();
$unitCategoriesSelectedServices = ( ! empty( $instance['unitcategoriesservices'] ) ) ? $instance['unitcategoriesservices'] : array();
$unitCategoriesSelectedActivities = ( ! empty( $instance['unitcategoriesactivities'] ) ) ? $instance['unitcategoriesactivities'] : array();
$unitCategoriesSelectedOthers = ( ! empty( $instance['unitcategoriesothers'] ) ) ? $instance['unitcategoriesothers'] : array();
$unitCategoriesSelectedRealEstate = ( ! empty( $instance['unitcategoriesrealestate'] ) ) ? $instance['unitcategoriesrealestate'] : array();

$tabnamebooking = ( ! empty( $instance['tabnamebooking'] ) ) ? esc_attr($instance['tabnamebooking']) : 'Booking';
$tabnameservices = ( ! empty( $instance['tabnameservices'] ) ) ? esc_attr($instance['tabnameservices']) : 'Services';
$tabnameactivities = ( ! empty( $instance['tabnameactivities'] ) ) ? esc_attr($instance['tabnameactivities']) : 'Activities';
$tabnameothers = ( ! empty( $instance['tabnameothers'] ) ) ? esc_attr($instance['tabnameothers']) : 'Others';

$tabnameevents = ( ! empty( $instance['tabnameevents'] ) ) ? esc_attr($instance['tabnameevents']) : 'Events';
$tabnamemapsell = ( ! empty( $instance['tabnamemapsell'] ) ) ? esc_attr($instance['tabnamemapsell']) : 'Search Maps';


$tabintrobooking = ( ! empty( $instance['tabintrobooking'] ) ) ? esc_attr($instance['tabintrobooking']) : '';
$tabintroservices = ( ! empty( $instance['tabintroservices'] ) ) ? esc_attr($instance['tabintroservices']) : '';
$tabintroactivities = ( ! empty( $instance['tabintroactivities'] ) ) ? esc_attr($instance['tabintroactivities']) : '';
$tabintroothers = ( ! empty( $instance['tabintroothers'] ) ) ? esc_attr($instance['tabintroothers']) : '';

$instanceContext = ( ! empty( $instance['currcontext'] ) ) ? $instance['currcontext'] : uniqid('currcontext'); ;
// translation
// WPML >= 3.2
if ( defined( 'ICL_SITEPRESS_VERSION' ) && version_compare( ICL_SITEPRESS_VERSION, '3.2', '>=' ) ) {
	$tabnamebooking = apply_filters( 'wpml_translate_single_string', $instance['tabnamebooking'], $instanceContext, 'Search 1' );
	$tabnameservices = apply_filters( 'wpml_translate_single_string',  $instance['tabnameservices'], $instanceContext, 'Search 2' );
	$tabnameactivities = apply_filters( 'wpml_translate_single_string', $instance['tabnameactivities'], $instanceContext, 'Search 3' );
	$tabnameothers = apply_filters( 'wpml_translate_single_string', $instance['tabnameothers'], $instanceContext, 'Search 4' );

	$tabnameevents = apply_filters( 'wpml_translate_single_string', $instance['tabnameevents'], $instanceContext, 'Search 5' );
	$tabnamemapsell = apply_filters( 'wpml_translate_single_string', $instance['tabnamemapsell'], $instanceContext, 'Search 6' );

	$tabintrobooking = apply_filters( 'wpml_translate_single_string', $instance['tabintrobooking'], $instanceContext, 'Search 1' );
	$tabintroservices = apply_filters( 'wpml_translate_single_string',  $instance['tabintroservices'], $instanceContext, 'Search 2' );
	$tabintroactivities = apply_filters( 'wpml_translate_single_string', $instance['tabintroactivities'], $instanceContext, 'Search 3' );
	$tabintroothers = apply_filters( 'wpml_translate_single_string', $instance['tabintroothers'], $instanceContext, 'Search 4' );

// WPML and Polylang compatibility
} elseif ( function_exists( 'icl_t' ) ) {
	$tabnamebooking = icl_t( $instanceContext, 'Search 1', $tabnamebooking );
	$tabnameservices = icl_t( $instanceContext, 'Search 2', $tabnameservices );
	$tabnameactivities = icl_t( $instanceContext, 'Search 3', $tabnameactivities );
	$tabnameothers = icl_t( $instanceContext, 'Search 4', $tabnameothers );

	$tabnameevents = icl_t( $instanceContext, 'Search 5', $tabnameevents );
	$tabnamemapsell = icl_t( $instanceContext, 'Search 6', $tabnamemapsell );

	$tabintrobooking = icl_t( $instanceContext, 'Search 1', $tabintrobooking );
	$tabintroservices = icl_t( $instanceContext, 'Search 2', $tabintroservices );
	$tabintroactivities = icl_t( $instanceContext, 'Search 3', $tabintroactivities );
	$tabintroothers = icl_t( $instanceContext, 'Search 4', $tabintroothers );
}else{
	$tabnamebooking = __( $tabnamebooking, 'bfi');
	$tabnameservices = __( $tabnameservices, 'bfi');
	$tabnameactivities = __( $tabnameactivities, 'bfi');
	$tabnameothers = __( $tabnameothers, 'bfi');

	$tabintrobooking = __( $tabintrobooking, 'bfi');
	$tabintroservices = __( $tabintroservices, 'bfi');
	$tabintroactivities = __( $tabintroactivities, 'bfi');
	$tabintroothers = __( $tabintroothers, 'bfi');

}

$tabnamebooking = ( ! empty( $tabnamebooking ) ) ? $tabnamebooking : __('Booking', 'bfi');
$tabnameservices = ( ! empty( $tabnameservices ) ) ? $tabnameservices : __('Services', 'bfi');
$tabnameactivities = ( ! empty( $tabnameactivities ) ) ? $tabnameactivities : __('Activities', 'bfi');
$tabnameothers = ( ! empty( $tabnameothers ) ) ? $tabnameothers : __('Others', 'bfi');

$tabiconbooking = ( ! empty( $instance['tabiconbooking'] ) ) ? esc_attr($instance['tabiconbooking']) : 'fa fa-suitcase';
$tabiconservices = ( ! empty( $instance['tabiconservices'] ) ) ? esc_attr($instance['tabiconservices']) : 'fa fa-calendar';
$tabiconactivities = ( ! empty( $instance['tabiconactivities'] ) ) ? esc_attr($instance['tabiconactivities']) : 'fa fa-calendar';
$tabiconothers = ( ! empty( $instance['tabiconothers'] ) ) ? esc_attr($instance['tabiconothers']) : 'fa fa-calendar';

$tabiconevents = ( ! empty( $instance['tabiconevents'] ) ) ? esc_attr($instance['tabiconevents']) : 'fa fa-calendar';
$tabiconmapsell = ( ! empty( $instance['tabiconmapsell'] ) ) ? esc_attr($instance['tabiconmapsell']) : 'fa fa-calendar';

$merchantCategoriesResource = array();
$merchantCategoriesRealEstate = array();
$unitCategoriesResource = array();
$unitCategoriesRealEstate = array();

$listmerchantCategoriesResource = "";
$listmerchantCategoriesRealEstate = "";

$availabilityTypeList = array();
$availabilityTypeList['1'] = __('Nights', 'bfi');
$availabilityTypeList['0'] = __('Days', 'bfi');

$availabilityTypesSelectedBooking = ( ! empty( $instance['availabilitytypesbooking'] ) ) ? $instance['availabilitytypesbooking'] : array();
$availabilityTypesSelectedServices = ( ! empty( $instance['availabilitytypesservices'] ) ) ? $instance['availabilitytypesservices'] : array();
$availabilityTypesSelectedActivities = ( ! empty( $instance['availabilitytypesactivities'] ) ) ? $instance['availabilitytypesactivities'] : array();
$availabilityTypesSelectedOthers = ( ! empty( $instance['availabilitytypesothers'] ) ) ? $instance['availabilitytypesothers'] : array();

$itemTypesSelectedBooking = ( ! empty( $instance['itemtypesbooking'] ) ) ? $instance['itemtypesbooking'] : array();
$itemTypesSelectedServices = ( ! empty( $instance['itemtypesservices'] ) ) ? $instance['itemtypesservices'] : array();
$itemTypesSelectedActivities = ( ! empty( $instance['itemtypesactivities'] ) ) ? $instance['itemtypesactivities'] : array();
$itemTypesSelectedOthers = ( ! empty( $instance['itemtypesothers'] ) ) ? $instance['itemtypesothers'] : array();

$groupBySelectedBooking = ( ! empty( $instance['groupbybooking'] ) ) ? $instance['groupbybooking'] : [0];
$groupBySelectedServices = ( ! empty( $instance['groupbyservices'] ) ) ? $instance['groupbyservices'] : [0];
$groupBySelectedActivities = ( ! empty( $instance['groupbyactivities'] ) ) ? $instance['groupbyactivities'] : [0];
$groupBySelectedOthers = ( ! empty( $instance['groupbyothers'] ) ) ? $instance['groupbyothers'] : [0];

$resultViewSelectedBooking = ( ! empty( $instance['resultviewsbooking'] ) ) ? $instance['resultviewsbooking'] :  array('resource');
$resultViewSelectedServices = ( ! empty( $instance['resultviewsservices'] ) ) ? $instance['resultviewsservices'] :  array('resource');
$resultViewSelectedActivities = ( ! empty( $instance['resultviewsactivities'] ) ) ? $instance['resultviewsactivities'] :  array('resource');
$resultViewSelectedOthers = ( ! empty( $instance['resultviewsothers'] ) ) ? $instance['resultviewsothers'] :  array('resource');

$tmpMerchantCategoryIdResource = (strpos($merchantCategoryIdResource, ',') !== FALSE )?"0":$merchantCategoryIdResource;
$tmpmasterTypeId = (strpos($masterTypeId, ',') !== FALSE )?"0":$masterTypeId;

if($showAccomodations || $showAccomodationsOnSell){
	if(!empty($merchantCategoriesSelectedBooking) || !empty($merchantCategoriesSelectedServices) || !empty($merchantCategoriesSelectedActivities) || !empty($merchantCategoriesSelectedOthers) || !empty($merchantCategoriesSelectedRealEstate) ){
//		$allMerchantCategories = BFCHelper::getMerchantCategories();
		$allMerchantCategories = BFCHelper::getMerchantCategories($language);

		if(!empty($merchantCategoriesSelectedBooking) || !empty($merchantCategoriesSelectedServices) || !empty($merchantCategoriesSelectedActivities) || !empty($merchantCategoriesSelectedOthers) ){
			$listmerchantCategoriesResource = '<option value="0">'.($showdirection?__('Tipology', 'bfi'):__('All', 'bfi')).'</option>';
		}
		if(!empty($merchantCategoriesSelectedRealEstate) ){
			$listmerchantCategoriesRealEstate = '<option value="0">'.__('All', 'bfi').'</option>';
		}
		if (!empty($allMerchantCategories))
		{
			foreach($allMerchantCategories as $merchantCategory)
			{
				if(in_array($merchantCategory->MerchantCategoryId,$merchantCategoriesSelectedBooking) || in_array($merchantCategory->MerchantCategoryId,$merchantCategoriesSelectedServices) || in_array($merchantCategory->MerchantCategoryId,$merchantCategoriesSelectedActivities) || in_array($merchantCategory->MerchantCategoryId,$merchantCategoriesSelectedOthers)){
					$merchantCategoriesResource[$merchantCategory->MerchantCategoryId] = $merchantCategory->Name;
					$listmerchantCategoriesResource .= '<option value="'.$merchantCategory->MerchantCategoryId.'" ' . ($merchantCategory->MerchantCategoryId== $tmpMerchantCategoryIdResource? 'selected':'' ).'>'.$merchantCategory->Name.'</option>';
				}
				if(in_array($merchantCategory->MerchantCategoryId,$merchantCategoriesSelectedRealEstate)){
					$merchantCategoriesRealEstate[$merchantCategory->MerchantCategoryId] = $merchantCategory->Name;
					$listmerchantCategoriesRealEstate .= '<option value="'.$merchantCategory->MerchantCategoryId.'" ' . ($merchantCategory->MerchantCategoryId== $merchantCategoryIdRealEstate? 'selected':'' ).'>'.$merchantCategory->Name.'</option>';
				}
			}
		}

	}

	$listunitCategoriesResource = "";
	if(!empty($unitCategoriesSelectedBooking) || !empty($unitCategoriesSelectedServices) || !empty($unitCategoriesSelectedActivities) || !empty($unitCategoriesSelectedOthers)) {
		$allUnitCategories =  BFCHelper::GetProductCategoryForSearch($language,1);
		if (!empty($allUnitCategories))
		{
			if ((strpos($masterTypeId, ',') !== FALSE )) {
				$tmpAllUnitCategoriesIds = array_unique(array_map(function ($i) { return $i->ProductCategoryId; }, $allUnitCategories));
				$masterTypeId  = implode(',',array_intersect($tmpAllUnitCategoriesIds , explode(',',$masterTypeId)));
			}else{


				if(!array_key_exists(intval($masterTypeId),$allUnitCategories)){
					$masterTypeId  = 0;
				}
		}

			$listunitCategoriesResource = '<option value="0">'.($showdirection?__('Type', 'bfi'):__('All', 'bfi')).'</option>';
			foreach($allUnitCategories as $unitCategory)
			{
				if(in_array($unitCategory->ProductCategoryId,$unitCategoriesSelectedBooking) || in_array($unitCategory->ProductCategoryId,$unitCategoriesSelectedServices) || in_array($unitCategory->ProductCategoryId,$unitCategoriesSelectedActivities) || in_array($unitCategory->ProductCategoryId,$unitCategoriesSelectedOthers)){
					$unitCategoriesResource[$unitCategory->ProductCategoryId] = $unitCategory->Name;
					$listunitCategoriesResource .= '<option value="'.$unitCategory->ProductCategoryId.'" ' . ($unitCategory->ProductCategoryId == $tmpmasterTypeId? 'selected':'' ).'>'.$unitCategory->Name.'</option>';
				}
			}
		}else{
		$masterTypeId  = "0";
		}
		}else{
		$masterTypeId  = "0";
	}


	$listunitCategoriesRealEstate = "";
	if(!empty($unitCategoriesSelectedRealEstate) ) {
		$allUnitCategoriesRealEstate =  BFCHelper::GetProductCategoryForSearch($language,2);
		if (!empty($allUnitCategoriesRealEstate))
		{
			$listunitCategoriesRealEstate = '<option value="0">'.($showdirection?__('Type', 'bfi'):__('All', 'bfi')).'</option>';
			foreach($allUnitCategoriesRealEstate as $unitCategory)
			{
				if(in_array($unitCategory->ProductCategoryId,$unitCategoriesSelectedRealEstate)){
					$unitCategoriesResource[$unitCategory->ProductCategoryId] = $unitCategory->Name;
					$listunitCategoriesRealEstate .= '<option value="'.$unitCategory->ProductCategoryId.'" ' . ($unitCategory->ProductCategoryId == $categoryIdRealEstate? 'selected':'' ).'>'.$unitCategory->Name.'</option>';
				}
			}
		}
	}
}

$blockmonths = '14';
$blockdays = '7';

if(!empty($instance['blockmonths']) && count($instance['blockmonths'])>0){
	$blockmonths = implode(',', $instance['blockmonths']) ;
}

if(!empty($instance['blockdays']) && count($instance['blockdays'])>0){
	$blockdays = implode(',', $instance['blockdays']) ;
}



if (!empty($services) ) {
	$filtersServices = explode(",", $services);
}

if (isset($filters)) {
	if (!empty($filters['services'])) {
		$filtersServices = explode(",", $filters['services']);
	}

}

$zonesString="";

if($showLocation){
	$locationZones = BFCHelper::getGeographicZones();
	if(!empty($zoneIds)){
		$zoneIdsSplitted = explode(",",$zoneIds);
	}
	
	if(!empty($locationZones)){
		$zonesString = '<option value="0" selected>'.($showdirection?__('Destination', 'bfi'):__('All', 'bfi')).'</option>';
		foreach ($locationZones as $lz) {
			if(empty($zoneIds) && $zoneIds != 0){
				$zoneIdsSplitted[] = $lz->LocationZoneID;
			}
			if(in_array($lz->LocationZoneID , $zoneIdsSplitted)){
				$zonesString = $zonesString . '<option value="'.$lz->LocationZoneID.'" selected>'.$lz->Name.'</option>';
			}else{
				$zonesString = $zonesString . '<option value="'.$lz->LocationZoneID.'">'.$lz->Name.'</option>';
			}

		}
	}

} //if($showLocation)
		
$listcontractType = '<option value="0" selected>'.__('On sale', 'bfi').'</option>';
$listcontractType .= '<option value="1">'.__('To rent', 'bfi').'</option>';

if($contractTypeId ==1 ){
	$listcontractType = '<option value="0">'.__('On sale', 'bfi').'</option>';
	$listcontractType .= '<option value="1" selected>'.__('To rent', 'bfi').'</option>';
}


$baths = array(
	'|' =>  $showdirection? __('Bathrooms', 'bfi').":" .__('Any', 'bfi'):__('Any', 'bfi') ,
	'1|1' =>  __('1') ,
	'2|2' =>  __('2') ,
	'3|3' =>  __('3') ,
	'3|' =>  __('>3') 
);


//$show_direction = $params->get('show_direction');
//$show_title = $params->get('show_title');




$nad = 0;
$nch = 0;
$nse = 0;
$countPaxes = 0;
$maxchildrenAge = (int)BFCHelper::$defaultAdultsAge-1;

$nchs = array(null,null,null,null,null,null);
$fistSelect = false;
if (empty($paxages)){
	$fistSelect = true;
	$nad = 2;
	$paxages = array(BFCHelper::$defaultAdultsAge, BFCHelper::$defaultAdultsAge);

}else{
	if(is_array($paxages)){
		$countPaxes = array_count_values($paxages);
		$nchs = array_values(array_filter($paxages, function($age) {
			if ($age < (int)BFCHelper::$defaultAdultsAge)
				return true;
			return false;
		}));
	}
}
array_push($nchs, null,null,null,null,null,null);

if($countPaxes>0){
	foreach ($countPaxes as $key => $count) {
		if ($key >= BFCHelper::$defaultAdultsAge) {
			if ($key >= BFCHelper::$defaultSenioresAge) {
				$nse += $count;
			} else {
				$nad += $count;
			}
		} else {
			$nch += $count;
		}
	}
}
if (($showMapIcon || $showMapIconOnSell) && COM_BOOKINGFORCONNECTOR_ENABLEGOOGLEMAPSAPI){ 
	if (COM_BOOKINGFORCONNECTOR_USE_OPENSTREETMAP) {
		wp_enqueue_script('bfileaflet');
		wp_enqueue_script('bfileafletcontrolcustom');
	}
	wp_enqueue_script('bfisearchonmap');
}

$showChildrenagesmsg = isset($_REQUEST['showmsgchildage']) ? $_REQUEST['showmsgchildage'] : 0;
$tabActive = "";
$totalTabs = count($tablistSelected);
if(empty( $totalTabs )){
	$totalTabs=1;
}
$widthTabs = 100/$totalTabs;
if ((empty(COM_BOOKINGFORCONNECTOR_ISMOBILE ) &&!$showdirection )|| BFI()->isSearchPage()) {
	$fixedonbottom = 0;    
}
if (!empty( $before_widget) ){
	echo $before_widget;
}
// Check if title is set
//if (!empty( $title) ) {
//  echo $before_title . $title . $after_title;
////  echo  $title ;
//}



?>
<div class="bfi-searchwidget bfi-affix-top<?php echo $currModID ?> <?php echo ( ! empty( $fixedonbottom ) ) ? 'bfiAffixBottom' : '' ?>" data-affixbottom="<?php echo ( ! empty( $fixedonbottom ) ) ? '1' : '0' ?>"><!-- per span8 e padding -->
<div class="bfi-mod-bookingforsearch" id="bfisearch<?php echo $currModID ?>" 
	data-currmodid="<?php echo $currModID;?>"
	data-showdaterange="<?php echo $showDateRange;?>"
	data-showdatetimerange="<?php echo $showDateTimeRange;?>"
	data-nch="<?php echo $nch;?>"
	data-showChildrenagesmsg="<?php echo $showChildrenagesmsg;?>"
	data-searchtypetab="<?php echo $searchtypetab;?>"
	data-fixedontop="<?php echo $fixedontop;?>"
	data-fixedontopcorrection="<?php echo $fixedontopcorrection;?>"

	>
    <ul class="bfi-tabs" id="navbookingforsearch<?php echo $currModID ?>" style="<?php echo ($totalTabs>1) ?"": "display:none" ?>">
		<?php if(in_array(0, $tablistSelected)){ ?>
		<li class="" data-searchtypeid="0" style="width:<?php echo $widthTabs ?>%">
            <a href="#bfisearchtab<?php echo $currModID ?>" data-toggle="tab" aria-expanded="true" class="searchResources">
                <?php if(!empty($tabiconbooking) && $tabiconbooking!='none') { ?><i class="<?php echo $tabiconbooking ?>" aria-hidden="true"></i><?php } ?>
                <?php echo $tabnamebooking ?>
            </a>
        </li>
		<?php }  ?>
		<?php if(in_array(1, $tablistSelected)){ ?>
        <li class="" data-searchtypeid="1" style="width:<?php echo $widthTabs ?>%">
            <a href="#bfisearchtab<?php echo $currModID ?>" data-toggle="tab" aria-expanded="true" class="searchServices">
                <?php if(!empty($tabiconservices) && $tabiconservices!='none') { ?><i class="<?php echo $tabiconservices ?>" aria-hidden="true"></i><?php } ?>
                <?php echo $tabnameservices ?>
            </a>
        </li>
		<?php }  ?>
		<?php if(in_array(2, $tablistSelected)){ ?>
        <li class="" data-searchtypeid="2" style="width:<?php echo $widthTabs ?>%">
            <a href="#bfisearchtab<?php echo $currModID ?>" data-toggle="tab" aria-expanded="true" class="searchTimeSlots">
                <?php if(!empty($tabiconactivities) && $tabiconactivities!='none') { ?><i class="<?php echo $tabiconactivities ?>" aria-hidden="true"></i><?php } ?>
                <?php echo $tabnameactivities ?>
            </a>
        </li>
		<?php }  ?>
		<?php if(in_array(4, $tablistSelected)){ ?>
        <li class="" data-searchtypeid="4" style="width:<?php echo $widthTabs ?>%">
            <a href="#bfisearchtab<?php echo $currModID ?>" data-toggle="tab" aria-expanded="true" class="searchOthers">
                <?php if(!empty($tabiconothers) && $tabiconothers!='none') { ?><i class="<?php echo $tabiconothers ?>" aria-hidden="true"></i><?php } ?>
                <?php echo $tabnameothers ?>
            </a>
        </li>
		<?php }  ?>
		<?php if(in_array(5, $tablistSelected)){ //eventi ?>
        <li class="" data-searchtypeid="5" style="width:<?php echo $widthTabs ?>%">
            <a href="#bfisearchevents<?php echo $currModID ?>" data-toggle="tab" aria-expanded="false" class="searchEvents">
                <?php if(!empty($tabiconevents) && $tabiconevents!='none') { ?><i class="<?php echo $tabiconevents ?>" aria-hidden="true"></i><?php } ?>
                <?php echo $tabnameevents; ?>
            </a>
        </li>
		<?php }  ?>
		<?php if(in_array(6, $tablistSelected)){ //mapsell ?>
        <li class="" data-searchtypeid="6" style="width:<?php echo $widthTabs ?>%">
            <a href="#bfisearchmapsell<?php echo $currModID ?>" data-toggle="tab" aria-expanded="false" class="searchmapsell">
                <?php if(!empty($tabiconmapsell) && $tabiconmapsell!='none') { ?><i class="<?php echo $tabiconmapsell ?>" aria-hidden="true"></i><?php } ?>
                <?php echo $tabnamemapsell; ?>
            </a>
        </li>
		<?php }  ?>
		<?php if(in_array(3, $tablistSelected)){ ?>
        <li class="" data-searchtypeid="3" style="width:<?php echo $widthTabs ?>%">
            <a href="#bfisearchselling<?php echo $currModID ?>" data-toggle="tab" aria-expanded="false" class="searchSelling">
                <?php if(!empty($tabiconrealestate) && $tabiconrealestate!='none') { ?><i class="<?php echo $tabiconrealestate ?>" aria-hidden="true"></i><?php } ?>
                <?php _e('Real Estate', 'bfi') ?>
            </a>
        </li>
		<?php }  ?>
    </ul>
    <div class="bfi-tab-content tab-content initial">
<?php if(!empty($tablistResources)){ 
$totalfields=0;
?>
        <div id="bfisearchtab<?php echo $currModID ?>" class="tab-pane fade in" style="display:<?php echo (COM_BOOKINGFORCONNECTOR_ISMOBILE) ?"none":""; ?>;">
		<?php 
	
	if (!empty($tablistResources) && count($tablistResources)==1) {
//			if(in_array(0, $tablistSelected) && !empty( $tabintrobooking )){ 
//				echo '<div class="bfisearchtab-subtitle">' . $tabintrobooking . '</div>';
//			}
//			if(in_array(1, $tablistSelected) && !empty( $tabintroservices )){ 
//				echo '<div class="bfisearchtab-subtitle">' . $tabintroservices . '</div>';
//			}
//			if(in_array(2, $tablistSelected) && !empty( $tabintroactivities )){ 
//				echo '<div class="bfisearchtab-subtitle">' . $tabintroactivities . '</div>';
//			}
//			if(in_array(4, $tablistSelected) && !empty( $tabintroothers )){ 
//				echo '<div class="bfisearchtab-subtitle">' . $tabintroothers . '</div>';
//			}	    
	}
		?>
		<form action="<?php echo $resultinsamepg ==0? $url_page_Resources: ""; ?>" method="get" id="searchform<?php echo $currModID ?>" class="bfi-form-default bfi-form-default-resources bfi-dateform-container bfi-form-<?php echo $showdirection?"horizontal":"vertical"; ?> " 
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
		data-defaultaction="<?php echo $url_page_Resources ?>"
		>
			<div class="bfi-row">
				<?php if($showSearchText) { 
					$totalfields +=2;
				?>
					<div class="bfi_destination bfi-col-sm-2">
						<label><?php _e('Search text', 'bfi') ?></label>
						<input type="text" id="searchtext<?php echo $currModID ?>" name="searchterm" value="<?php echo $searchterm ?>" class="bfi-inputtext bfi-autocomplete" placeholder="<?php _e('Search for accommodation or area', 'bfi') ?>..."  data-scope="<?php echo $searchTextFields ?>" inputmode="search"/>
					</div>
				<?php }//$showSearchText ?>
				<?php if(!empty($zonesString) && $showLocation){  
					$totalfields +=2;
				?>
					<div class="bfi_destination bfi-col-sm-2">
						<label><?php _e('Destination', 'bfi') ?></label>
						<select name="zoneIds" class="" data-live-search="true" data-width="99%">
						<?php echo $zonesString; ?>
						</select>
					</div>
				<?php } //$showLocation ?>
				<?php if(!empty($listmerchantCategoriesResource) && $showAccomodations){  
					$totalfields +=2;
				?>
					<div class="bfi_merchantcategoriesresource bfi-col-sm-2">
						<label><?php _e('Tipology', 'bfi') ?></label>
						<select id="merchantCategoryId<?php echo $currModID ?>" name="merchantCategoryId" class="hideRent">
							<?php echo $listmerchantCategoriesResource; ?>
						</select>
					</div>
				<?php } //$showAccomodations ?>
				<?php if(!empty($listunitCategoriesResource) && $showAccomodations){  
					$totalfields +=2;
				?>
					<div class="bfi_unitcategoriesresource bfi-col-sm-2">
						<label><?php _e('Type', 'bfi') ?></label>
						<select id="masterTypeId<?php echo $currModID ?>" name="masterTypeId" class="">
							<?php echo $listunitCategoriesResource; ?>
						</select>
					</div>
				<?php } //$showAccomodations ?>
				<?php if($showMapIcon){  
					$totalfields +=2;
				?>
				<div class="bfi_listlocations bfi-col-sm-2">
					<input type="hidden" value="<?php echo $searchType ?>" name="searchType"  />
					<div class="bfi-btn bfi-mapsearchbtn <?php echo $searchType==1?"bfi-alternative":"bfi-alternative4"; ?>" onclick="javascript:bfiOpenGoogleMapDrawer('searchform<?php echo $currModID ?>','<?php echo $currModID ?>');">
						<i class="fa fa-map-marker fa-1"></i>
					</div>
				</div>
				<?php } //$showLocation ?>
				<?php if($showDateRange){  
					$totalfields +=4;
				?>
				<div class="bfi-showdaterangenew bfi-col-sm-2">
					<div class="t-datepicker" data-checkin="<?php echo $checkin->format('Y-m-d'); ?>" data-checkout="<?php echo $checkout->format('Y-m-d'); ?>">
						<div class="t-check-in"></div>
						<div class="t-check-out"></div>
					</div>
				</div>
				<?php 
				$oneDays = false;
				if(in_array(1, $tablistSelected) && in_array(1, $itemTypesSelectedServices) && $showDateOneDays  ){ 
					$tmpcheckin = clone $checkin;
					$tmpcheckin->modify($checkoutspan);
					$oneDays= (($tmpcheckin->format('d/m/Y')) ==$checkout->format('d/m/Y'));
					?>
					<div class="bfi-showdaterangedays bfi-col-sm-2">

					<input type="radio" class="bfi-changedays-widget" name="bfi-select-days" <?php echo ($oneDays) ?"checked":""; ?> value="1"/><label><?php echo _e('One day','bfi') ?> </label>
					<input type="radio" class="bfi-changedays-widget" name="bfi-select-days" <?php echo ($oneDays) ?"":"checked"; ?> value="2"/><label><?php echo _e('More days','bfi') ?> </label>
					</div>
				<?php 
					}
				?>
				<div class="bfi-showdaterange bfi-col-sm-2 ">
					<label><?php _e('Check-in' , 'bfi' ); ?></label>
					<div class="bfi-datepicker">
						<input name="checkin" type="text" value="<?php echo $checkin->format('d/m/Y'); ?>" id="<?php echo $checkinId; ?>" class="bfidate bfistart bfi-checkin-field"/>
					</div>
					<?php if($showDateTimeRange){  ?>
						<div class="bfi-datetimepicker">
							<input name="checkintime" type="text" value="<?php echo $checkin->format('H:i'); ?>"  class="bfitime bfistart" />
						</div>
					<?php } //$showDateTimeRange ?>
				</div>
				<div class="bfi-showdaterange bfi-col-sm-2 bfi-checkout-field-container" id="divcheckoutsearch<?php echo $currModID ?>" style="display:<?php echo ($oneDays) ?"none":""; ?>">
					<label><?php _e('Check-out' , 'bfi' ); ?></label>
					<div class="bfi-datepicker">
						<input type="text" name="checkout" value="<?php echo $checkout->format('d/m/Y'); ?>" id="<?php echo $checkoutId; ?>" class="bfidate bfiend bfi-checkout-field"/>
					</div>
					<?php if($showDateTimeRange){  ?>
						<div class="bfi-datetimepicker">
							<input name="checkouttime" type="text" value="<?php echo $checkout->format('H:i'); ?>" class="bfitime bfiend"  />
						</div>
					<?php } //$showDateTimeRange ?>
				</div>
				<?php } //$showDateRange ?>
<?php 
if ($showPerson) {
	$totalfields +=2;

?>
					<div class="bfi_destination bfi-col-sm-2">
						<label><?php _e('Guest', 'bfi'); ?></label>
						<select id="bfi-adult<?php echo $currModID ?>" name="adultssel" onchange="bfi_quoteChanged('<?php echo $currModID ?>')" class="" style="display:inline-block !important;">
							<option value="2" <?php echo ($nad == 0)?"selected":""; ?>><?php _e('Persons', 'bfi') ?></option>';
							<?php
							foreach (range(1, 10) as $number) {
								?> <option value="<?php echo $number ?>" <?php echo ($nad == $number)?"selected":""; ?>><?php echo $number . " " . __('Persons', 'bfi') ?></option><?php
							}
							?>
						</select>
					</div>
<?php 
}else{
?>
				<?php if($showAdult){ 
					$totalfields +=2;
				?>
				<div class="bfi-col-sm-2 bfi-showperson-container">
					<label><?php _e('Guest', 'bfi'); ?></label>
					<div class="bfi-showperson-text bfi-container " id="bfi-showperson-text<?php echo $currModID ?>">
						<?php if($showResource){?><span id="bfi-room-info<?php echo $currModID ?>" class="bfi-comma"><span><?php echo $minResource ?></span> <?php _e('Resource', 'bfi'); ?></span><?php }?>
						<?php if($showRooms){?><span id="bfi-room-info<?php echo $currModID ?>" class="bfi-comma"><span><?php echo $maxrooms ?></span> <?php _e('Room', 'bfi'); ?></span><?php }?>
						<span id="bfi-adult-info<?php echo $currModID ?>" class="bfi-comma"><span><?php echo $nad ?></span> <?php _e('Adults', 'bfi'); ?></span>
						<?php if($showSenior){?><span id="bfi-senior-info<?php echo $currModID ?>" class="bfi-comma"><span><?php echo $nse ?></span> </span> <?php _e('Seniores', 'bfi'); ?></span><?php }?>
						<?php if($showChildren){?><span id="bfi-child-info<?php echo $currModID ?>" class="bfi-comma"><span><?php echo $nch ?></span> <?php _e('Children', 'bfi'); ?></span><?php }?>
					</div>
				</div>
					<div class="bfi-showperson" id="bfishowperson<?php echo $currModID ?>">
						<?php if($showResource){
						?>
							<div class="bfi-showresource "><!-- showresource -->
								<label><?php _e('Resource', 'bfi'); ?></label>
								<select id="bfi-minqt<?php echo $currModID ?>" name="minqtselected" onchange="bfi_quoteChanged('<?php echo $currModID ?>')" class="" style="display:inline-block !important;">
									<?php
									foreach (range($minResource, $maxResource) as $number) {
										?> <option value="<?php echo $number ?>" <?php echo ($minqt == $number)?"selected":""; ?>><?php echo $number ?></option><?php
									}
									?>
								</select>
						</div>
						<?php }?>
						<?php if($showRooms){
						?>
							<div class="bfi-showrooms "><!-- showresource -->
								<label><?php _e('Resource', 'bfi'); ?></label>
								<select id="bfi-minrooms<?php echo $currModID ?>" name="minroomsselected" onchange="bfi_quoteChanged('<?php echo $currModID ?>')" class="" style="display:inline-block !important;">
									<?php
									foreach (range($minRooms, $maxRooms) as $number) {
										?> <option value="<?php echo $number ?>" <?php echo ($minrooms == $number)?"selected":""; ?>><?php echo $number ?></option><?php
									}
									?>
								</select>
						</div>
						<?php }?>
						<div class="bfi-row">
							<div class="bfi-showadult bfi-col-md-<?php echo ($showSenior)?"4":"6" ?> bfi-col-xs-<?php echo ($showSenior)?"4":"6" ?>"><!-- Adults -->
								<label><?php _e('Adults', 'bfi'); ?></label>
								<select id="bfi-adult<?php echo $currModID ?>" name="adultssel" onchange="bfi_quoteChanged('<?php echo $currModID ?>')" class="" style="display:inline-block !important;">
									<?php
									foreach (range(1, 10) as $number) {
										?> <option value="<?php echo $number ?>" <?php echo ($nad == $number)?"selected":""; ?>><?php echo $number ?></option><?php
									}
									?>
								</select>
							</div>
						<?php if($showSenior){ 
						?>
							<div class="bfi-showsenior bfi-col-md-4 bfi-col-xs-4"><!-- Seniores -->
								<label><?php _e('Seniores', 'bfi'); ?></label>
								<select id="bfi-senior<?php echo $currModID ?>" name="senioressel" onchange="bfi_quoteChanged('<?php echo $currModID ?>')" class="" style="display:inline-block !important;">
									<?php
									foreach (range(0, 10) as $number) {
										?> <option value="<?php echo $number ?>" <?php echo ($nse == $number)?"selected":""; ?>><?php echo $number ?></option><?php
									}
									?>
								</select>
							</div>
						<?php }?>
						<?php if($showChildren){ 
						?>
							<div class="bfi-showchildren bfi-col-md-<?php echo ($showSenior)?"4":"6" ?> bfi-col-xs-<?php echo ($showSenior)?"4":"6" ?>" id="mod_bookingforsearch-children<?php echo $currModID ?>" ><!-- n childrens -->
								<label><?php _e('Children', 'bfi'); ?></label>
								<select id="bfi-child<?php echo $currModID ?>" name="childrensel" onchange="bfi_quoteChanged('<?php echo $currModID ?>')" class="" style="display:inline-block !important;">
									<?php
									foreach (range(0, 4) as $number) {
										?> <option value="<?php echo $number ?>" <?php echo ($nch == $number)?"selected":""; ?>><?php echo $number ?></option><?php
									}
									?>
								</select>
							</div>
						<?php }?>
						</div>
						<?php if($showChildren){?>
						<div class="bfi-childrenages " style="display:none;"  id="mod_bookingforsearch-childrenages<?php echo $currModID ?>">
								
							<label ><?php _e('Age of children', 'bfi'); ?>
							<span class="bfi_lblchildrenagesat" id="bfi_lblchildrenagesat<?php echo $currModID ?>"><?php echo  _e('on', 'bfi') . " " .$checkout->format("d"). " " .date_i18n('F',$checkout->getTimestamp()). " " . $checkout->format("Y") ?></span></label><!-- Ages childrens -->		
									<select name="childages1sel" onchange="bfi_quoteChanged('<?php echo $currModID ?>')" class="bfi-inputmini" style="display: none;">
										<option value="<?php echo COM_BOOKINGFORCONNECTOR_CHILDRENSAGE ?>" ></option>
										<?php
										foreach (range(0, $maxchildrenAge) as $number) {
											?> <option value="<?php echo $number ?>" <?php echo ($nchs[0] != null && $nchs[0] == $number)?"selected":""; ?>><?php echo $number ?></option><?php
										}
										?>
									</select>
									<select  name="childages2sel" onchange="bfi_quoteChanged('<?php echo $currModID ?>')" class="bfi-inputmini" style="display: none;">
										<option value="<?php echo COM_BOOKINGFORCONNECTOR_CHILDRENSAGE ?>" ></option>
										<?php
										foreach (range(0, $maxchildrenAge) as $number) {
											?> <option value="<?php echo $number ?>" <?php echo ($nchs[1] != null && $nchs[1] == $number)?"selected":""; ?>><?php echo $number ?></option><?php
										}
										?>
									</select>
									<select  name="childages3sel" onchange="bfi_quoteChanged('<?php echo $currModID ?>')" class="bfi-inputmini" style="display: none;">
										<option value="<?php echo COM_BOOKINGFORCONNECTOR_CHILDRENSAGE ?>" ></option>
										<?php
										foreach (range(0, $maxchildrenAge) as $number) {
											?> <option value="<?php echo $number ?>" <?php echo ($nchs[2] != null && $nchs[2] == $number)?"selected":""; ?>><?php echo $number ?></option><?php
										}
										?>
									</select>
									<select name="childages4sel" onchange="bfi_quoteChanged('<?php echo $currModID ?>')" class="bfi-inputmini" style="display: none;">
										<option value="<?php echo COM_BOOKINGFORCONNECTOR_CHILDRENSAGE ?>" ></option>
										<?php
										foreach (range(0, $maxchildrenAge) as $number) {
											?> <option value="<?php echo $number ?>" <?php echo ($nchs[3] != null && $nchs[3] == $number)?"selected":""; ?>><?php echo $number ?></option><?php
										}
										?>
									</select>
									<select name="childages5sel" onchange="bfi_quoteChanged('<?php echo $currModID ?>')" class="bfi-inputmini" style="display: none;">
										<option value="<?php echo COM_BOOKINGFORCONNECTOR_CHILDRENSAGE ?>" ></option>
										<?php
										foreach (range(0, $maxchildrenAge) as $number) {
											?> <option value="<?php echo $number ?>" <?php echo ($nchs[4] != null && $nchs[4] == $number)?"selected":""; ?>><?php echo $number ?></option><?php
										}
										?>
									</select>
									</div>
									<span class="bfi-childmessage" id="bfi_lblchildrenages<?php echo $currModID ?>">&nbsp;</span>
							<?php }?>

						</div>
				<?php if(!$showdirection) { ?>
							<div class="bfi-clearfix"></div>
				<?php } ?>
				<?php } //$showAdult?>
<?php 
}
?>
				<?php									
					$widthbtn = (($totalfields-2) % 12);
					if (($totalfields  <12)) {
						$widthbtn = 2;						
					}else{
						if (($widthbtn <4)) {
							$widthbtn = 12;
						}
					}
				?>
				<div class="bfi-searchbutton-wrapper bfi-col-sm-<?php echo $showdirection? $widthbtn:"2"; $widthbtn ?>" id="divBtnResource<?php echo $currModID ?>">
					<a  id="BtnResource<?php echo $currModID ?>" class="bfi-btnsendform bfi-btn " href="javascript: void(0);"><i class="fa fa-search" aria-hidden="true"></i> <?php _e('Search', 'bfi') ?></a>
				</div>
			</div>
			<div class="bfi-clearfix"></div>
			<div class="bfi-powered"><a href="https://www.bookingfor.com" target="_blank">Powered by Bookingfor</a></div>
			<input type="hidden" value="<?php echo uniqid('', true)?>" name="searchid" />
			<input type="hidden" name="onlystay" value="<?php echo $onlystay ?>">
			<?php if(true || !$showResource) { ?>
			<input type="hidden" name="minqt" value="<?php echo $minResource?>" id="searchformminqt<?php echo $currModID ?>">
			<?php } ?>
			<input type="hidden" name="maxqt" value="<?php echo $maxResource?>" id="searchformmaxqt<?php echo $currModID ?>">
			<?php if(true || !$showRooms) { ?>
			<input type="hidden" name="minrooms" value="<?php echo $minrooms?>" id="searchformminrooms<?php echo $currModID ?>">
			<?php } ?>
			<input type="hidden" name="maxrooms" value="<?php echo $maxrooms?>" id="searchformmaxrooms<?php echo $currModID ?>">
			<input type="hidden" name="persons" value="<?php echo $nad + $nse + $nch?>" id="searchformpersons<?php echo $currModID ?>">
			<input type="hidden" name="adults" value="<?php echo $nad?>" id="searchformpersonsadult<?php echo $currModID ?>">
			<input type="hidden" name="seniores" value="<?php echo $nse?>" id="searchformpersonssenior<?php echo $currModID ?>">
			<input type="hidden" name="children" value="<?php echo $nch?>" id="searchformpersonschild<?php echo $currModID ?>">
			<input type="hidden" name="childages1" value="<?php echo $nchs[0]?>" id="searchformpersonschild1<?php echo $currModID ?>">
			<input type="hidden" name="childages2" value="<?php echo $nchs[1]?>" id="searchformpersonschild2<?php echo $currModID ?>">
			<input type="hidden" name="childages3" value="<?php echo $nchs[2]?>" id="searchformpersonschild3<?php echo $currModID ?>">
			<input type="hidden" name="childages4" value="<?php echo $nchs[3]?>" id="searchformpersonschild4<?php echo $currModID ?>">
			<input type="hidden" name="childages5" value="<?php echo $nchs[4]?>" id="searchformpersonschild5<?php echo $currModID ?>">
			
			<?php if($showSearchText) { ?>
				<input type="hidden" value="<?php echo $getBaseFiltersFor ?>" name="getBaseFiltersFor" />
				<input type="hidden" value="<?php echo $stateIds ?>" name="stateIds" />
				<input type="hidden" value="<?php echo $regionIds ?>" name="regionIds" />
				<input type="hidden" value="<?php echo $cityIds ?>" name="cityIds" />
				<input type="hidden" value="<?php echo $merchantIds ?>" name="merchantIds" />
				<input type="hidden" value="<?php echo $groupresourceIds ?>" name="groupresourceIds" />
				<input type="hidden" value="<?php echo $merchantTagIds ?>" name="merchantTagIds" />
				<input type="hidden" value="<?php echo $productTagIds ?>" name="productTagIds" />
				<input type="hidden" value="<?php echo $zoneIds ?>" name="zoneIds" />
				<input type="hidden" value="<?php echo $merchantCategoryId ?>" name="merchantCategoryId" />
				<input type="hidden" value="<?php echo $masterTypeId ?>" name="masterTypeId" />
			<?php } ?>
			<?php if(!$showAccomodations) { ?>
				<input type="hidden" value="<?php echo (empty($merchantCategoryId)? '':  $merchantCategoryId) ?>" name="merchantCategoryId"  class="bfi-merchantCategoryId"/>
				<input type="hidden" value="<?php echo (empty($masterTypeId)? '':  $masterTypeId) ?>" name="masterTypeId" class="bfi-masterTypeId" />
			<?php } ?>
			
			<!--
			<input type="hidden" value="<?php echo $merchantCategoryId ?>" name="filters[merchantCategoryId]" />
			-->
			<input type="hidden" value="<?php echo $masterTypeId ?>" name="filters[productcategory]" class="bfi-filtersproductcategory" />
			<input type="hidden" value="<?php echo $searchTermValue ?>" name="searchTermValue" />
			<input type="hidden" value="1" name="newsearch" />
			<input type="hidden" value="0" name="limitstart" />
			<input type="hidden" name="filter_order" value="<?php echo $currFilterOrder ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $currFilterOrderDirection ?>" />
			<input type="hidden" value="<?php echo $language ?>" name="cultureCode" />
			<input type="hidden" value="<?php echo $points ?>" name="points" id="points<?php echo $currModID ?>" />
			<input type="hidden" value="<?php echo $searchtypetab ?>" name="searchtypetab" id="searchtypetab<?php echo $currModID ?>" />
			<input type="hidden" value="0" name="showmsgchildage" id="showmsgchildage<?php echo $currModID ?>"/>
			<div class="bfi-hide" id="bfi_childrenagesmsg<?php echo $currModID ?>">
				<div style="line-height:0; height:0;"></div>
				<div class="bfi-pull-right" style="cursor:pointer;color:red">&nbsp;<i class="fa fa-times-circle" aria-hidden="true" onclick="jQuery('#bfi_lblchildrenages<?php echo $currModID ?>').webuiPopover('destroy');"></i></div>
				<?php echo sprintf(__('We preset your children\'s ages to %s years old - but if you enter their actual ages, you might be able to find a better price.', 'bfi'),COM_BOOKINGFORCONNECTOR_CHILDRENSAGE) ?>
			</div>
			<input type="hidden" name="availabilitytype" class="resbynighthd" value="1" />
			<input type="hidden" name="itemtypes" class="itemtypeshd" value="0" id="hdItemTypes<?php echo $checkoutId; ?>" />
			<input type="hidden" name="groupresulttype" class="groupresulttypehd" value="1" id="hdSearchGroupby<?php echo $checkoutId; ?>" />
			<input type="hidden" name="getallresults" class="getallresultshd" value="<?php echo $showOnlineBooking; ?>" id="hdgetallresults<?php echo $currModID; ?>" />
			<input type="hidden" name="cultureCode" value="<?php echo $language ?>" />
			<input type="hidden" name="checkFullPeriod" value="<?php echo $showDateTimeRange ?>" />
			<input type="hidden" name="resultinsamepg" value="<?php echo $resultinsamepg ?>" />
			<input type="hidden" name="dateselected" value="1" />
			<input type="hidden" name="resview" class="resviewhd" value="resource" />

		</form>
<div class="bfi-parameter" style="display:none">
	<span class="bfi-merchantcategoriesresource"><?php echo json_encode($merchantCategoriesResource) ?></span>
	<span class="bfi-merchantcategoriesselectedbooking"><?php echo implode(',', $merchantCategoriesSelectedBooking) ?></span>
	<span class="bfi-merchantcategoriesselectedservices"><?php echo implode(',', $merchantCategoriesSelectedServices) ?></span>
	<span class="bfi-merchantcategoriesselectedactivities"><?php echo implode(',', $merchantCategoriesSelectedActivities) ?></span>
	<span class="bfi-merchantCategoriesSelectedOthers"><?php echo implode(',', $merchantCategoriesSelectedOthers) ?></span>

	<span class="bfi-unitcategoriesresource"><?php echo json_encode($unitCategoriesResource) ?></span>
	<span class="bfi-unitcategoriesselectedbooking"><?php echo implode(',', $unitCategoriesSelectedBooking) ?></span>
	<span class="bfi-unitcategoriesselectedservices"><?php echo implode(',', $unitCategoriesSelectedServices) ?></span>
	<span class="bfi-unitcategoriesselectedactivities"><?php echo implode(',', $unitCategoriesSelectedActivities) ?></span>
	<span class="bfi-unitcategoriesselectedothers"><?php echo implode(',', $unitCategoriesSelectedOthers) ?></span>

	<span class="bfi-availabilitytypesselectedbooking"><?php echo implode(',', $availabilityTypesSelectedBooking) ?></span>
	<span class="bfi-availabilitytypesselectedservices"><?php echo implode(',', $availabilityTypesSelectedServices) ?></span>
	<span class="bfi-availabilitytypesselectedactivities"><?php echo implode(',', $availabilityTypesSelectedActivities) ?></span>
	<span class="bfi-availabilitytypesselectedothers"><?php echo implode(',', $availabilityTypesSelectedOthers) ?></span>

	<span class="bfi-itemtypesselectedbooking"><?php echo implode(',', $itemTypesSelectedBooking) ?></span>
	<span class="bfi-itemtypesselectedservices"><?php echo implode(',', $itemTypesSelectedServices) ?></span>
	<span class="bfi-itemtypesselectedactivities"><?php echo implode(',', $itemTypesSelectedActivities) ?></span>
	<span class="bfi-itemtypesselectedothers"><?php echo implode(',', $itemTypesSelectedOthers) ?></span>

	<span class="bfi-groupbyselectedbooking"><?php echo implode(',', $groupBySelectedBooking) ?></span>
	<span class="bfi-groupbyselectedservices"><?php echo implode(',', $groupBySelectedServices) ?></span>
	<span class="bfi-groupbyselectedactivities"><?php echo implode(',', $groupBySelectedActivities) ?></span>
	<span class="bfi-groupbyselectedothers"><?php echo implode(',', $groupBySelectedOthers) ?></span>

	<span class="bfi-resultviewsselectedbooking"><?php echo implode(',', $resultViewSelectedBooking) ?></span>
	<span class="bfi-resultviewsselectedservices"><?php echo implode(',', $resultViewSelectedServices) ?></span>
	<span class="bfi-resultviewsselectedactivities"><?php echo implode(',', $resultViewSelectedActivities) ?></span>
	<span class="bfi-resultviewsselectedothers"><?php echo implode(',', $resultViewSelectedOthers) ?></span>
</div>
				   
        </div>
<?php }  ?>
<?php if(!empty($tablistEvents)){ 
$totalfields=0;			
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
		<div id="bfisearchevents<?php echo $currModID ?>" class="tab-pane fade in">
		<?php 
			if(!empty( $tabnameevents )){ 
				echo '<div class="bfisearchtab-subtitle">' . $tabnameevents . '</div>';
			}
		?>
			<form action="<?php echo $url_page_Resources; ?>" method="get" id="searchformevent<?php echo $currModID ?>" class="bfi-form-event bfi-form-default bfi-dateform-container bfi-form-<?php echo $showdirection?"horizontal":"vertical"; ?> " 
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
			<div class="bfi-row">
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
					<input type="text" id="searchtextonsell<?php echo $currModID ?>" name="searchterm" value="<?php echo $searchterm ?>" class="bfi-inputtext bfi-autocomplete" placeholder="<?php _e('Search text', 'bfi') ?>" data-scope="<?php echo $searchTextFieldsEvent ?>" />
				</div>
				<div class="bfi-searchbutton-wrapper bfi-col-sm-2" id="divBtnResource<?php echo $currModID ?>">
					<a   class="bfi-btnsendform bfi-btn " href="javascript: void(0);"><i class="fa fa-search" aria-hidden="true"></i> <?php _e('Search', 'bfi') ?></a>
				</div>
			</div>
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
				<input type="hidden" value="" name="searchtermoverride" />
				<input type="hidden" name="filter_order" value="" />
				<input type="hidden" name="filter_order_Dir" value="" />

			</form>
			<div class="bficontainerviewform">
				<a class="bfiviewform" rel="bfi-form-event"><i class="fa fa-search"></i> <?php _e('Search events', 'bfi') ?> </a>
			</div>
        </div>

<?php }  ?>
<?php if(!empty($tablistMapSells)){ 
$totalfields=0;			
$searchAvailability_page = get_post( bfi_get_page_id( 'searchavailability' ) );
$url_page_Resources = get_permalink( $searchAvailability_page->ID );

?>
		<div id="bfisearchmapsell<?php echo $currModID ?>" class="tab-pane fade in">
		<?php 
			if(!empty( $tabnamemapsell )){ 
				echo '<div class="bfisearchtab-subtitle">' . $tabnamemapsell . '</div>';
			}
		?>
			<form action="<?php echo $url_page_Resources; ?>" method="get" id="searchformmapsells<?php echo $currModID ?>" class="bfi-form-mapsells bfi-form-default bfi-dateform-container bfi-form-<?php echo $showdirection?"horizontal":"vertical"; ?> " 
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
				<div class="bfi-row">
					<div class="bfi_destination bfi-col-sm-2">
						<label><?php _e('Search text', 'bfi') ?></label>
						<input type="text" id="searchtextonsell<?php echo $currModID ?>" name="searchterm" value="<?php echo $searchterm ?>" class="bfi-inputtext bfi-autocomplete" placeholder="<?php _e('Search text', 'bfi') ?>" data-scope="<?php echo $searchTextFieldsMapsell ?>" inputmode="search" />
					</div>
					<?php if($showDateOneDaysMapSell) { ?>
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
							<input name="checkin" type="text" value="<?php echo $checkin->format('d/m/Y'); ?>" class="bfidate bfistart bfi-checkin-field"/>
						</div>
					</div>
					<div class="bfi-showdaterange bfi-col-sm-2" id="divcheckoutsearch<?php echo $currModID ?>">
						<label><?php _e('To' , 'bfi' ); ?></label>
						<div class="bfi-datepicker">
							<input type="text" name="checkout" value="<?php echo $checkout->format('d/m/Y'); ?>" class="bfidate bfiend bfi-checkout-field"/>
						</div>
					</div>
					<div class="bfi-searchbutton-wrapper bfi-col-sm-2" id="divBtnResource<?php echo $currModID ?>">
						<a   class="bfi-btnsendform bfi-btn " href="javascript: void(0);"><i class="fa fa-search" aria-hidden="true"></i> <?php _e('Search', 'bfi') ?></a>
					</div>
				</div>
				<input type="hidden" value="1" name="newsearch" />
				<input type="hidden" value="<?php echo uniqid('', true)?>" name="searchid" />
				<input type="hidden" value="<?php echo uniqid('', true)?>" name="mapsells" />
				<input type="hidden" value="<?php echo $searchTermValue ?>" name="searchTermValue" />
				<input type="hidden" name="filter_order" value="" />
				<input type="hidden" name="filter_order_Dir" value="" />
				<input type="hidden" name="groupresulttype" value="2" />
				<input type="hidden" name="availabilitytype" value="0,2,3" />
				<input type="hidden" name="itemtypes" value="1" />
				<input type="hidden" name="getallresults" value="0" />
				<input type="hidden" name="checkFullPeriod" value="0" />
				<input type="hidden" name="resview" value="mapsells" />
				
			</form>
			<div class="bficontainerviewform">
				<a class="bfiviewform" rel="bfi-form-mapsells"><i class="fa fa-search"></i> <?php _e('Search', 'bfi') ?> </a>
			</div>
		</div>

<?php }  ?>
<?php if(!empty($tablistRealEstate)){ 
$totalfields=0;			
?>
		<div id="bfisearchselling<?php echo $currModID ?>" class="tab-pane fade in">
		<form action="<?php echo $url_page_RealEstate; ?>" method="get" id="searchformonsellunit<?php echo $currModID ?>" 
			class="bfi-form-onsell bfi-form-<?php echo $showdirection?"horizontal":"vertical"; ?> "
			data-showsearchtextonsell="<?php echo $showSearchTextOnSell;?>"	
		>			
			<div  id="searchBlock<?php echo $currModID ?>" class="bfi-row">
				<?php if($showContract){  
					$totalfields +=2;
				?>
				<div class="bfi_contracttypeid bfi-col-sm-2" >
					<label><?php _e('Contract', 'bfi') ?></label>
					<select name="contractTypeId" class="">
								<?php echo $listcontractType; ?>
					</select>
				</div><!--/span-->
				<?php } //$showContract ?>
				<?php if($showSearchTextOnSell) {  
					$totalfields +=2;
				?>
					<div class="bfi_destination bfi-col-sm-2">
						<label><?php _e('Search text', 'bfi') ?></label>
						<input type="text" id="searchtextonsell<?php echo $currModID ?>" name="searchterm" class="bfi-inputtext bfi-autocomplete" placeholder="<?php _e('Search text', 'bfi') ?>" />
					</div>
					<input type="hidden" value="" name="locationzone" />
					<input type="hidden" value="" name="searchTermValue" />
				<?php }//$showSearchText ?>				
				<?php if($showMapIconOnSell){  
					$totalfields +=1;
				?>
				<div class="bfi_listlocations bfi-col-sm-1">
					<input type="hidden" value="<?php echo $searchTypeonsell ?>" name="searchType" id="mapSearch<?php echo $currModID ?>" />
					<div class="bfi-btn bfi-mapsearchbtn <?php echo $searchTypeonsell==1?"bfi-alternative":"bfi-alternative4"; ?> " onclick="javascript:bfiOpenGoogleMapDrawer('searchformonsellunit<?php echo $currModID ?>','<?php echo $currModID ?>');">
						<i class="fa fa-map-marker fa-1"></i>
					</div>
				</div>
				<?php } //$showLocation ?>
				<?php if(!empty($listunitCategoriesRealEstate) && $showAccomodationsOnSell){  
					$totalfields +=2;
				?>
				<div class="bfi_unitCategoryId bfi-col-sm-2">
					<label><?php _e('Type', 'bfi') ?></label>
					<select name="unitCategoryId" class="">
						<?php echo $listunitCategoriesRealEstate; ?>
					</select>
				</div><!--/span-->
				<?php } //$listunitCategoriesRealEstate ?>
				<?php if($showMaxPrice){  
					$totalfields +=2;
				?>
				<div class="bfi-range-price bfi-col-sm-2" id="bfi-range-price<?php echo $currModID ?>">
					<label><?php _e('Price', 'bfi') ?></label>
					<div class="bfi-row">   
						<div class="bfi-col-md-6 bfi-col-sm-6">
							<input name="pricemin" type="text" placeholder="<?php echo __('from', 'bfi') ?>" value="<?php echo $pricemin;?>" class="bfi-inputtext" data-rule-digits="true" data-msg-digits="<?php _e('Please enter a integer number', 'bfi') ?>" rel="#bfi-range-pricemin<?php echo $currModID ?>"  > 
						</div><!--/span-->
						<div class="bfi-col-md-6 bfi-col-sm-6">
							<input name="pricemax" type="text" placeholder="<?php echo __('to', 'bfi') ?>" value="<?php echo $pricemax;?>"  class="bfi-inputtext" data-rule-digits="true" data-msg-digits="<?php _e('Please enter a integer number', 'bfi') ?>"" rel="#bfi-range-pricemax<?php echo $currModID ?>" > 
						</div><!--/span-->
					</div>
				</div><!--/span-->
				<?php } //$showMaxPrice ?>
				<?php if($showMinFloor){  
					$totalfields +=2;
				?>
				<div class="bfi_floor_area  bfi-col-sm-2">
					<label><?php _e('Floor area m&sup2;', 'bfi') ?></label>
					<div class="bfi-row">   
						<div class="bfi-col-md-6 bfi-col-sm-6">
							<input name="areamin" type="text" placeholder="<?php echo __('from', 'bfi') ?>" value="<?php echo $areamin;?>" class="bfi-inputtext" data-rule-digits="true" data-msg-digits="<?php _e('Please enter a integer number', 'bfi') ?>" > 
						</div><!--/span-->
						<div class="bfi-col-md-6 bfi-col-sm-6">
							<input name="areamax" type="text" placeholder="<?php echo __('to', 'bfi') ?>" value="<?php echo $areamax;?>" class="bfi-inputtext" data-rule-digits="true" data-msg-digits="<?php _e('Please enter a integer number', 'bfi') ?>" > 
						</div><!--/span-->
					</div>
				</div><!--/span-->
				<?php } //$showMinFloor ?>
				<?php if($showBedRooms){  
					$totalfields +=2;
				?>
				<div class="bfi_bedrooms  bfi-col-sm-2">
					<label><?php _e('Bedrooms', 'bfi') ?></label>
					<div class="bfi-row">   
						<div class="bfi-col-md-6 bfi-col-sm-6">
					<input name="bedroomsmin" type="text" placeholder="<?php echo __('from', 'bfi') ?>" value="<?php echo $bedroomsmin;?>" class="bfi-inputtext" data-rule-digits="true" data-msg-digits="<?php _e('Please enter a integer number', 'bfi') ?>" > 
						</div><!--/span-->
						<div class="bfi-col-md-6 bfi-col-sm-6">
					<input name="bedroomsmax" type="text" placeholder="<?php echo __('to', 'bfi') ?>" value="<?php echo $bedroomsmax;?>" class="bfi-inputtext" data-rule-digits="true" data-msg-digits="<?php _e('Please enter a integer number', 'bfi') ?>" > 
						</div><!--/span-->
					</div>
				</div><!--/span-->
				<?php } //$showBedRooms ?>
				<?php if($showRooms){  
					//$totalfields +=2;
				?>
				<div class="bfi_rooms  bfi-col-sm-2">
					<label><?php _e('Rooms', 'bfi') ?></label>
					<div class="bfi-row">   
						<div class="bfi-col-md-6 bfi-col-sm-6">
					<input name="roomsmin" type="text" placeholder="<?php echo __('from', 'bfi') ?>" value="<?php echo $roomsmin;?>" class="bfi-inputtext" data-rule-digits="true" data-msg-digits="<?php _e('Please enter a integer number', 'bfi') ?>" > 
						</div><!--/span-->
						<div class="bfi-col-md-6 bfi-col-sm-6">
					<input name="roomsmax" type="text" placeholder="<?php echo __('to', 'bfi') ?>" value="<?php echo $roomsmax;?>" class="bfi-inputtext" data-rule-digits="true" data-msg-digits="<?php _e('Please enter a integer number', 'bfi') ?>" > 
						</div><!--/span-->
					</div>
				</div><!--/span-->
				<?php } //$showRooms ?>
				<?php if($showBaths){  
					$totalfields +=2;
				?>
				<div class="bfi_bathrooms  bfi-col-sm-2">
					<label><?php _e('Bathrooms', 'bfi') ?></label>
					<select name="baths" onchange="bfi_changeBaths(this);" class="">
					<?php foreach ($baths as $key => $value){?>
						<option value="<?php echo $key ?>" <?php selected( $bathsmin ."|". $bathsmax, $key ); ?>><?php echo $value ?></option>
					<?php } ?>
					</select>
					<input name="bathsmin" type="hidden" placeholder="<?php _e('from', 'bfi') ?>" value="<?php echo $bathsmin;?>" class="bfi-inputtext" > 
					<input name="bathsmax" type="hidden" placeholder="<?php _e('to', 'bfi') ?>" value="<?php echo $bathsmax;?>" class="bfi-inputtext" > 
				</div><!--/span-->
				<?php } //$showBaths ?>
				<?php if (isset($listServices) && $showServicesList) { 
					$totalfields +=2;
				?>
				<?php  $countServ=0;?>
				<div class="bfi_listservices  bfi-col-sm-2">
					<div class="bfi-row">   
						<?php foreach ($listServices as $singleService){?>
							<div class="bfi-col-md-6">
							<?php $checked = '';
								if (isset($filtersServices) &&  is_array($filtersServices) && in_array($singleService->ServiceId,$filtersServices)){
									$checked = ' checked="checked"';
								}
							?>
								<label class="checkbox"><input type="checkbox" name="services"  class="checkboxservices" value="<?php echo ($singleService->ServiceId) ?>" <?php echo $checked ?> /><?php echo BFCHelper::getLanguage($singleService->Name, $language) ?></label>
							</div>
						<?php  $countServ++;
						if($countServ%2==0){
						?>
						</div>
						<div class="bfi-row">	
						<?php } ?>

						<?php } ?>
					</div>
				</div><!--/span-->
				<?php } ?>
				<?php if($showOnlyNew){  
					$totalfields +=2;
				?>
				<div class="bfi_isnewbuilding  bfi-col-sm-2">  
					<label class="checkbox"><input type="checkbox" name="isnewbuilding" value="1" <?php echo $isnewbuilding ?> /><?php _e('Only new building', 'bfi') ?></label>
				</div><!--/span-->
				<?php } ?>

				<?php
					$widthbtn = ($totalfields % 12);
					if (($widthbtn >6)) {
					    $widthbtn = 12;
					}
				?>
				<div class="bfi-searchbutton-wrapper bfi-col-sm-<?php echo $showdirection? $widthbtn:"2"; $widthbtn ?>" id="searchButtonArea<?php echo $currModID ?>">
					<div class="" id="divBtnRealEstate">
						<a  id="BtnRealEstate<?php echo $currModID ?>" class="bfi-btnsendform bfi-btn" href="javascript: void(0);"><i class="fa fa-search" aria-hidden="true"></i> <?php _e('Search', 'bfi') ?></a>
					</div>
				</div>
					<div class="bfi-clearfix"></div>
				<div class="bfi-powered"><a href="https://www.bookingfor.com" target="_blank">Powered by Bookingfor</a></div>

			</div><!--/span-->

			<input type="hidden" value="<?php echo uniqid('', true)?>" name="searchid" />
			<input type="hidden" value="3" name="searchtypetab" />
			<input type="hidden" value="1" name="newsearch" />
			<input type="hidden" value="0" name="limitstart" />
			<input type="hidden" name="filter_order" value="" />
			<input type="hidden" name="filter_order_Dir" value="" />
			<input type="hidden" value="<?php echo $language ?>" name="cultureCode" />
			<input type="hidden" value="<?php echo $pointsonsell ?>" name="points" id="pointsonsell<?php echo $currModID ?>" />
			<input type="hidden" value="<?php echo $services ?>" name="servicesonsell" id="servicesonsell<?php echo $currModID ?>" />
			<input type="hidden" name="availabilitytype" class="resbynighthd" value="1" />
			<input type="hidden" value="" name="stateIds" />
			<input type="hidden" value="" name="regionIds" />
			<input type="hidden" value="" name="cityIds" />
		</form>
		</div>  <!-- role="tabpanel" -->
<?php }  ?>
    </div>
</div>
<div class="bfi-clearfix"></div>

</div>

<?php
if (!empty($after_widget)) {
	echo $after_widget;
    }
?>
	<div id="bfi_MapDrawer<?php echo $currModID ?>" style="width:100%; height:400px; display:none;">
		<div style="width:100%; height:50px; position:relative;">
			<div class="bfi-row"> 
				<div class="bfi-col-md-6 bfi-col-sm-6">
					<div class="bfi-mapeditor"><?php _e('Draw area', 'bfi') ?>
					<a class="bfi-btn bfi-select-figure bfi-drawpoligon" onclick="javascript: drawPoligon()"><?php _e('Area', 'bfi') ?></a>
					<a class="bfi-btn bfi-select-figure bfi-drawcircle" onclick="javascript: drawCircle()"><?php _e('Circle', 'bfi') ?></a>
					</div>
				</div><!--/span-->
				<div class="bfi-col-md-6 bfi-col-sm-6 bfi-text-right">
					<input type="text" class="bfi-map-addresssearch" placeholder="<?php _e('Search', 'bfi') ?>" />
					<div class="bfi-btnCompleta" style="display:none;">
						<a class="bfi-btn bfi-btndelete" href="javascript: void(0);" ><?php _e('Reset', 'bfi') ?></a>
						<a class="bfi-btn bfi-btnconfirm" type="button" href="javascript: void(0);" ><?php _e('Confirm', 'bfi') ?></a>
						<span class="bfi-spanarea"></span>
					</div>
				
				</div><!--/span-->
			</div>

		</div>
		<div class="bfi-map-canvas" id="bfi-map-canvas<?php echo $currModID ?>" style="width:100%; height:350px;"></div>
		<div class="bfi-map-tooltip"><strong><?php _e('Click on map and choose your area.', 'bfi') ?></strong></div>
	</div>
	<?php 
} // if isbot
?>
