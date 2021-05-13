<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$isbot = false;
BFI()->define( "DONOTCACHEPAGE", true ); // Do not cache this page
	if(!empty( COM_BOOKINGFORCONNECTOR_CRAWLER )){
		$listCrawler = json_decode(COM_BOOKINGFORCONNECTOR_CRAWLER , true);
		foreach( $listCrawler as $key=>$crawler){
		if (preg_match('/'.$crawler['pattern'].'/', $_SERVER['HTTP_USER_AGENT'])) $isbot = true;
		}
		
	}
if (!$isbot) {
$showform = 1;
$base_url = get_site_url();

if (!empty($hideform) || !empty(BFCHelper::getVar('hideform','0'))) {
    $showform = 0;
}
    
$resetCart = 0;

$ProductAvailabilityType = 1;
$checkInDates = '';
$hideRateplanOver = 5;


$isportal = COM_BOOKINGFORCONNECTOR_ISPORTAL;
$usessl = COM_BOOKINGFORCONNECTOR_USESSL;

$fromSearch =  BFCHelper::getVar('fromsearch','0');
$makesearch =  BFCHelper::getVar('refreshcalc','0');
$timeSlotIdSelected =  BFCHelper::getVar('timeSlotId','0');

$itemTypeIds =  BFCHelper::getVar('itemTypeIds',$merchant->TypeId);
if(!empty($resourceId)){
	$itemTypeIds = $resource->ItemTypeId;
}

$listNameAnalytics =  BFCHelper::getVar('lna','0');
if(empty( $listNameAnalytics )){
	$listNameAnalytics = 0;
}

$currLlistNameAnalytics = BFCHelper::$listNameAnalytics[$listNameAnalytics];

$base_url = get_site_url();
$language = $GLOBALS['bfi_lang'];
$languageForm ='';
if(defined('ICL_LANGUAGE_CODE') &&  class_exists('SitePress')){
		global $sitepress;
		if($sitepress->get_current_language() != $sitepress->get_default_language()){
			$languageForm = "/" .ICL_LANGUAGE_CODE;
		}
}
$cartdetails_page = get_post( bfi_get_page_id( 'cartdetails' ) );
$url_cart_page = get_permalink( $cartdetails_page->ID );

$merchantdetails_page = get_post( bfi_get_page_id( 'merchantdetails' ) );
$url_merchant_page = get_permalink( $merchantdetails_page->ID );
$routeMerchant = $url_merchant_page . $merchant->MerchantId.'-'.BFI()->seoUrl($merchant->Name);
$routeInfoRequest = $routeMerchant . '/contact';

$accommodationdetails_page = get_post( bfi_get_page_id( 'accommodationdetails' ) );
if(!empty($resourcegroupId)){
	$accommodationdetails_page = get_post( bfi_get_page_id( 'resourcegroupdetails' ) );
}
$url_resource_page = get_permalink( $accommodationdetails_page->ID );

$experiencedetails_page = get_post( bfi_get_page_id( 'experiencedetails' ) );	
$url_resource_page_experience = get_permalink( $experiencedetails_page->ID );

$resourceName = "";

$uri = $url_resource_page;
$currUriresource  = $uri;
$formRoute= "";
$formMethod = "POST";

$roomtype_text = array(
        "0" => __('Bed room', 'bfi'),
        "1" => __('Living room', 'bfi'),
					);

$bedtype_text = array(
						'1' => __('single bed', 'bfi'),   
						'2' => __('double bed', 'bfi'),
						'3' => __('large double bed', 'bfi'),
						'4' => __('extra-large double bed', 'bfi'),
						'5' => __('bunk bed', 'bfi'),  
						'6' => __('sofa bed', 'bfi'),
						'7' => __('futon', 'bfi')                               
					);

$bedtypes_text = array(
						'1' => __('single beds', 'bfi'),   
						'2' => __('double beds', 'bfi'),
						'3' => __('large double beds', 'bfi'),
						'4' => __('extra-large double beds', 'bfi'),
						'5' => __('bunk beds', 'bfi'),  
						'6' => __('sofa beds', 'bfi'),
						'7' => __('futons', 'bfi')                               
					);

$mrcAcceptanceCheckInHours=0;
$mrcAcceptanceCheckInMins=0;
$mrcAcceptanceCheckInSecs=1;
$mrcAcceptanceCheckOutHours=0;
$mrcAcceptanceCheckOutMins=0;
$mrcAcceptanceCheckOutSecs=1;
if(!empty($merchant->AcceptanceCheckIn) && !empty($merchant->AcceptanceCheckOut) && $merchant->AcceptanceCheckIn != "-" && $merchant->AcceptanceCheckOut != "-"){
	$tmpAcceptanceCheckIn=$merchant->AcceptanceCheckIn;
	$tmpAcceptanceCheckOut=$merchant->AcceptanceCheckOut;
	$tmpAcceptanceCheckIns = explode('-', $tmpAcceptanceCheckIn);
	$tmpAcceptanceCheckOuts = explode('-', $tmpAcceptanceCheckOut);
	
	$correctAcceptanceCheckIns = $tmpAcceptanceCheckIns[0];
	if(empty( $correctAcceptanceCheckIns )){
		$correctAcceptanceCheckIns = $tmpAcceptanceCheckIns[1];
	}
	if(empty( $correctAcceptanceCheckIns )){
		$correctAcceptanceCheckIns = "0:0";
	}
	$correctAcceptanceCheckOuts = $tmpAcceptanceCheckOuts[1];
	if(empty( $correctAcceptanceCheckOuts )){
		$correctAcceptanceCheckOuts = $tmpAcceptanceCheckOuts[0];
	}
	if(empty( $correctAcceptanceCheckOuts )){
		$correctAcceptanceCheckOuts = "0:0";
	}
	if (strpos($correctAcceptanceCheckIns, ":") === false) {
		$correctAcceptanceCheckIns .= ":0";
	}
	if (strpos($correctAcceptanceCheckOuts, ":") === false) {
		$correctAcceptanceCheckOuts .= ":0";
	}

	list($mrcAcceptanceCheckInHours,$mrcAcceptanceCheckInMins,$mrcAcceptanceCheckInSecs) = explode(':',$correctAcceptanceCheckIns.":1");
	list($mrcAcceptanceCheckOutHours,$mrcAcceptanceCheckOutMins,$mrcAcceptanceCheckOutSecs) = explode(':',$correctAcceptanceCheckOuts.":1");
}

$startDate = DateTime::createFromFormat('d/m/Y',BFCHelper::getStartDateByMerchantId($merchant->MerchantId),new DateTimeZone('UTC'));
$endDate = DateTime::createFromFormat('d/m/Y',BFCHelper::getEndDateByMerchantId($merchant->MerchantId),new DateTimeZone('UTC'));
$startDate->setTime(0,0,0);
$endDate->setTime(0,0,0);
$aCheckInDates = array();
$resourcetype = $itemTypeIds;

if(!empty($resourceId)){
	$resourceName = BFCHelper::getLanguage($resource->Name, $language, null, array('ln2br'=>'ln2br', 'striptags'=>'striptags')); 
	$ProductAvailabilityType = $resource->AvailabilityType;
//	$resourcetype = $resource->ItemTypeId;
//	$checkInDates = BFCHelper::getCheckInDates($resource->ResourceId,$startDate);
//		
//	if(!empty( $checkInDates )){
//		$aCheckInDates = explode(',',$checkInDates);
//		$startDate=DateTime::createFromFormat('Ymd',$aCheckInDates[0],new DateTimeZone('UTC'));
//		$startDate->setTime(0,0,0);
//	}	
	switch ($resource->ItemTypeId ) {
		case bfi_ItemType::Experience :
			$currUriresource  = $url_resource_page_experience.$resource->ResourceId.'-'.BFI()->seoUrl($resourceName);
		break;
		default:      
			$currUriresource  = $uri.$resource->ResourceId.'-'.BFI()->seoUrl($resourceName);
		break;
	} // end switch


	if(!empty($resourcegroupId)){
		$currUriresource  = $uri.$resourcegroupId.'-'.BFI()->seoUrl($resourceName);
	}
	$formRoute = $currUriresource .'/?task=getMerchantResources';
}else{
	// se 
	if (!empty($merchant->EnableTimeCheckOnly) || $itemTypeIds == bfi_ItemType::Rental) {
	    $ProductAvailabilityType = 2;
	}
	$formRoute = $routeMerchant .'/?task=getMerchantResources';
}


//if($usessl){
//	$formRouteBook = str_replace( 'http:', 'https:', $formRouteBook );
//}

//$formOrderRouteBook = $formRouteBook ;

if($usessl){
	$url_cart_page = str_replace( 'http:', 'https:', $url_cart_page );
}

// controllo se il merchant utilizza ile fasce di età altrimenti non le visualizzo nel form di ricerca


$hasPaxes = $merchant->HasPaxes;

$formOrderRouteBook = $url_cart_page;

$pars = BFCHelper::getSearchParamsSession();
if(!is_array($pars)){
	$pars = array();
}

$pars['extras'] = '';
/*--------------------------------------------*/

$eecstays = array();

//$productCategory = BFCHelper::GetProductCategoryForSearch($language,1,$merchant->MerchantId); 

$checkoutspan = '+1 day';
if ($ProductAvailabilityType== 0 || $ProductAvailabilityType== 2)
{
	$checkoutspan = '+0 day';
}



if ($ProductAvailabilityType== 3)
{
	$checkoutspan = '+7 day';
}


$checkin = BFCHelper::getStayParam('checkin', new DateTime('UTC'));
$checkout = BFCHelper::getStayParam('checkout', new DateTime('UTC'));

if(!empty($_REQUEST['checkin']) && !empty($_REQUEST['checkout'])) {
	
	$checkin = DateTime::createFromFormat('YmdHis', $_REQUEST['checkin'], new DateTimeZone('UTC'));
	if(empty($checkin)) $checkin = DateTime::createFromFormat('d/m/YH:i:s', $_REQUEST['checkin'] . (isset($_REQUEST['checkintime']) ? $_REQUEST['checkintime'] : "00:00") . ':00', new DateTimeZone('UTC'));
	$checkout = DateTime::createFromFormat('YmdHis', $_REQUEST['checkout'], new DateTimeZone('UTC'));
	if(empty($checkout)) $checkout = DateTime::createFromFormat('d/m/YH:i:s', $_REQUEST['checkout'] . (isset($_REQUEST['checkouttime']) ? $_REQUEST['checkouttime'] : "00:00") . ':00', new DateTimeZone('UTC'));
	
	$checkoutspan = '+' . $checkout->diff($checkin)->format('%a') . ' day';
}

if ($ProductAvailabilityType != 2) {
	$checkout->setTime(0,0,0);
	$checkin->setTime(0,0,0);
}

$strCheckinTime = $checkin->format('H:i');
$strCheckoutTime = $checkout->format('H:i');


$paxages = BFCHelper::getStayParam('paxages');
/*

*/

	$nad = BFCHelper::$defaultAdultsQt;
	if(!empty($resourceId)){
		if(isset($resource->MaxCapacityPaxes) && $resource->MaxCapacityPaxes > 0 ) { 
			$nad = $resource->MinCapacityPaxes==0?($resource->MaxCapacityPaxes<$nad?$resource->MaxCapacityPaxe:$nad) :$resource->MinCapacityPaxes;
		 } 
	}

	$adults = isset($_REQUEST['adults']) ? $_REQUEST['adults'] : $nad;
	$children = isset($_REQUEST['children']) ? $_REQUEST['children'] : 0;
	$seniores = isset($_REQUEST['seniores']) ? $_REQUEST['seniores'] : 0;
	$useSeniores= isset($_REQUEST['seniores']);
	
	if (($adults == null || $adults == '') && ($children == null || $children == '') && (isset($pars['paxages']) && $pars['paxages'] != null && $pars['paxages'] != '')) {
		return array_slice($pars['paxages'],0);
	}
	$paxages = array();
	$totalPerson = $adults+$seniores+$children;

	if(!empty($resourceId) && empty($fromSearch) && empty($makesearch)){
		if(isset($resource->MaxCapacityPaxes) && $resource->MaxCapacityPaxes > 0 && ( $totalPerson > $resource->MaxCapacityPaxes || $totalPerson < $resource->MinCapacityPaxes )) { 
			$adults = $resource->MinCapacityPaxes==0?($resource->MaxCapacityPaxes<$adults?$resource->MaxCapacityPaxe:$adults) :$resource->MinCapacityPaxes;
		 } 
	}

	for ($i = 0; $i < $adults; $i++) {
		$paxages[] = BFCHelper::$defaultAdultsAge;
	}
	for ($i = 0; $i < $seniores; $i++) {
		$paxages[] = BFCHelper::$defaultSenioresAge;
	}
	if ($children > 0) {
		for ($i = 0;$i < $children; $i++) {
			$age =$_REQUEST['childages'.($i+1)];
			if ($age < BFCHelper::$defaultAdultsAge) {
				$paxages[] = $age;
			}
		}
	}

$paxes = count($paxages);
$currentState ='';

$ratePlanId = '';
$pricetype = '';
$selectablePrices ='';
$packages ='';
$nrooms = 1;
$newsearch = BFCHelper::getVar('newsearch', 0);

$currPackage = BFCHelper::getVar('bfipck');

if (!empty($pars)){

//	$checkin = !empty($pars['checkin']) ? $pars['checkin'] : new DateTime('UTC');
	$checkout = !empty($pars['checkout']) ? $pars['checkout'] : new DateTime('UTC');

//	if (!empty($pars['paxes'])) {
//		$paxes = $pars['paxes'];
//	}
	if (!empty($pars['paxages']) && empty($currPackage) && !empty($fromSearch) ) {
		$paxages = $pars['paxages'];
	}
	if (empty($pars['checkout'])){
		$checkout->modify($checkoutspan); 
	}

	$currentState = isset($pars['state'])?$pars['state']:'';
	$pricetype = isset($params['pricetype']) ? $params['pricetype'] : BFCHelper::getVar('pricetype','');
	$ratePlanId = isset($params['rateplanId']) ? $params['rateplanId'] : $pricetype;
	$selectablePrices = isset($params['extras']) ? $params['extras'] : '';
	$packages = isset($params['packages']) ? $params['packages'] : '';
}


if(empty( $resourcegroupId )){
	$resourcegroupId = BFCHelper::getVar('resourcegroupId',0);
}

$variationPlanId = (isset($currvariationPlanId)) ? $currvariationPlanId : BFCHelper::getVar('variationPlanId','');
$refreshSearch = (isset($refreshSearch)) ? $refreshSearch : BFCHelper::getVar('refreshsearch','');

$variationPlanMinDuration = 0;
$variationPlanMaxDuration = 0;
$endDate2 = clone $endDate;

if(!empty($variationPlanId)){
	$offer = BFCHelper::getMerchantOfferFromService($variationPlanId, $language);
	if(isset($offer) && $offer->HasValidSearch) {
		$variationPlanMinDuration = $offer->MinDuration;
		$variationPlanMaxDuration = $offer->MaxDuration;
		$checkoutspan = '+'.$offer->MinDuration.' day';
		$dateparsed = BFCHelper::parseJsonDate($offer->FirstAvailableDate, 'Y-m-d');
		$dateparsedend = BFCHelper::parseJsonDate($offer->LastAvailableDate, 'Y-m-d');
		
		$startDate = DateTime::createFromFormat('Y-m-d',$dateparsed,new DateTimeZone('UTC'));
		$endDate = DateTime::createFromFormat('Y-m-d',$dateparsedend,new DateTimeZone('UTC'));
		$endDate2 = clone $endDate;
//		$endDate2->modify('+'.$offer->MaxDuration.' day'); 
		
		if(empty(BFCHelper::getVar('refreshcalc',''))){
			$checkin = DateTime::createFromFormat('Y-m-d',$dateparsed,new DateTimeZone('UTC'));
			$checkout = clone $checkin;
			$checkout->modify($checkoutspan); 
		}
	}
}

$startDate2 = clone $startDate;
$startDate2->modify($checkoutspan);

if (($checkin < $startDate) || (!empty( $aCheckInDates ) && !in_array($checkin->format('Ymd'),$aCheckInDates)) ){
	$checkin = clone $startDate;
	$checkout = clone $checkin;
    $checkout->modify($checkoutspan); 
}
if(!empty(BFCHelper::getVar('refreshcalc',''))){
	BFCHelper::setSearchParamsSession($pars);
}
//if ($checkin == $checkout){
//    $checkout->modify($checkoutspan); 
//}
if ($checkout < $checkin){
	$checkout = clone $checkin;
    $checkout->modify($checkoutspan); 
}



$nad = 0;
$nch = 0;
$nse = 0;
$countPaxes = 0;
$maxchildrenAge = (int)BFCHelper::$defaultAdultsAge-1;

$nchs = array(null,null,null,null,null,null);

if (empty($paxages)){

	$nad = 2;
//	$paxages = array(BFCHelper::$defaultAdultsAge, BFCHelper::$defaultAdultsAge);
	if(!empty($resourceId)){
		if(isset($resource->MaxCapacityPaxes) && $resource->MaxCapacityPaxes > 0 && ( $totalPerson > $resource->MaxCapacityPaxes || $totalPerson < $resource->MinCapacityPaxes )) { 
			$nad = $resource->MinCapacityPaxes==0?($resource->MaxCapacityPaxes<$nad?$resource->MaxCapacityPaxes:$nad) :$resource->MinCapacityPaxes;
		 } 
	}
	$paxages  = array();
	for ($totAd=0;$totAd<$nad ; $totAd++) {
		array_push($paxages,BFCHelper::$defaultAdultsAge);
	}

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

$duration = 0;
//
// mintime
// maxtime
//
$AvailabilityTimePeriod = array();
$minuteStart = 0;
$minuteEnd = 24*60;
$timeLength = 0;
/*
if (!empty($resourceId) && $ProductAvailabilityType == 2)
{
	$currAvailCalHour = json_decode(BFCHelper::GetCheckInDatesPerTimes($resourceId, $checkin, null));
	if (count($currAvailCalHour)>0)
	{
		$timeLength = $currAvailCalHour[0]->TimeLength;
		foreach($currAvailCalHour as $singleHour) {
			$AvailabilityTimePeriod[$singleHour->StartDate] = json_decode($singleHour->TimeRangesString);
		}
	}
	if (empty($fromSearch) && empty($makesearch)) {
		$checkout = clone $checkin;
		$checkout = $checkout->modify($checkoutspan); 	    
	}
} else if ($ProductAvailabilityType== 3)
{
	$checkout = clone $checkin;
	$duration = 7;
	$checkout = $checkout->modify($checkoutspan); 
} else {
	$duration = $checkin->diff($checkout)->format('%a');
}
*/
//if (!empty($resourceId) && $ProductAvailabilityType == 2)
//{
//	$currAvailCalHour = BFCHelper::GetCheckInDatesPerTimes($resourceId, $checkin, null);
//	if (!empty($currAvailCalHour) && is_array($currAvailCalHour) && count($currAvailCalHour)>0)
//	{
//		$timeLength = $currAvailCalHour[0]->TimeLength;
//		foreach($currAvailCalHour as $singleHour) {
//			$AvailabilityTimePeriod[$singleHour->StartDate] = json_decode($singleHour->TimeRangesString);
//		}
//	}
//
//}

if ($ProductAvailabilityType == 3)
{
	$checkout = clone $checkin;
	$checkout = $checkout->modify($checkoutspan); 
}
$duration = $checkout->diff($checkin)->format('%a');

//echo "<pre>AvailabilityTimePeriod:<br />";
//echo print_r($AvailabilityTimePeriod);
//echo "</pre>";

//if ($ProductAvailabilityType== 0)
//{
//	$duration +=1; 
//}

$dateStringCheckin =  $checkin->format('d/m/Y');
$dateStringCheckout =  $checkout->format('d/m/Y');


$dateStayCheckin = new DateTime('UTC');
$dateStayCheckout = new DateTime('UTC');


$totalPerson = $nad + $nch + $nse;

//Se non utilizza le fasce di età allora resetto tutto e li considero solo come adulti
if (!$hasPaxes) {
    $nad = $totalPerson;
	$nch = 0;
	$nse = 0;
	$paxages  = array();
	for ($totAd=0;$totAd<$nad ; $totAd++) {
		array_push($paxages,BFCHelper::$defaultAdultsAge);
	}
}

$checkinId = uniqid('checkincalculator');
$checkoutId = uniqid('checkoutcalculator');

$allStaysToView = array();


$alternativeDateToSearch = clone $startDate;
if ($checkin > $alternativeDateToSearch)
{
	$alternativeDateToSearch = clone $checkin;
}


$defaultRatePlan = null;


$allRatePlans = array();
$allmerchants = BFCHelper::getVar('allmerchants',0);

if(!empty($fromSearch) && !empty($makesearch)){
	$currIdmerchant = $merchant->MerchantId;
	if ($allmerchants=='1') {
	    $currIdmerchant = 0;
	}
	$checkFullPeriod = isset($_REQUEST['checkFullPeriod']) ? $_REQUEST['checkFullPeriod'] : 0;
	$resultsItems = BFCHelper::GetRelatedResourceStays($currIdmerchant, $resourceId, $resourceId, $checkin,$duration,$paxages, $variationPlanId,$language, $resourcegroupId, $checkout, $checkFullPeriod,$itemTypeIds);
	if (!empty($resultsItems )) {
		$tmpAllRatePlans = json_decode($resultsItems->ItemsString);
		if (is_array($tmpAllRatePlans)) {
			$allRatePlans = array_filter($tmpAllRatePlans, function($item){ return !empty($item->RatePlan); });
			$allFilters = json_decode($resultsItems->FiltersString);
			if($newsearch == "1"){
				BFCHelper::setFirstFilterDetailsParamsSession($allFilters,"mrc".$merchant->MerchantId);
			}
			BFCHelper::setEnabledFilterDetailsParamsSession($allFilters,"mrc".$merchant->MerchantId);
		}
	}
}

if($newsearch == "1"){
	BFCHelper::setFilterDetailsParamsSession(null, "mrc".$merchant->MerchantId);
}else{
	$filtersselected = BFCHelper::getVar('filters', null);
	if ($filtersselected == null) { //provo a recuperarli dalla sessione...
		$filtersselected = BFCHelper::getFilterDetailsParamsSession("mrc".$merchant->MerchantId);
	}
	BFCHelper::setFilterDetailsParamsSession($filtersselected, "mrc".$merchant->MerchantId);
}

if(!empty($resourceId) && is_array($allRatePlans) && count($allRatePlans)>0){
	$defaultRatePlans =  array_values(array_filter($allRatePlans, function($p) use ($resourceId) {return $p->ResourceId == $resourceId ;})); // c#: allRatePlans.Where(p => p.ResourceId == resId);
	//usort($defaultRatePlans, "BFCHelper::bfi_sortResourcesRatePlans");
	
	//$allRatePlans = array_slice($allRatePlans, 0, 5);
	$allRatePlans = array_merge($allRatePlans, $defaultRatePlans);
	$allRatePlans = array_unique($allRatePlans, SORT_REGULAR);
	if(is_array($defaultRatePlans)){
		$defaultRatePlan =  reset($defaultRatePlans);
	}
}


$calPrices = null;

$stayAvailability = 0;

$selPriceType = 0;
$selBookingType=0;

$tmpSearchModel = new stdClass;
$tmpSearchModel->FromDate = $checkin;
$tmpSearchModel->ToDate = $checkout;

if(is_array($allRatePlans) && count($allRatePlans)>0){
	foreach($allRatePlans as $p) {
		if (!empty($p->BookingType)) {
			$selBookingType = $p->BookingType;
			break;
		}
	}
}
if(!empty($defaultRatePlan) && !empty($defaultRatePlan->RatePlanId) ){
	$selPriceType = $defaultRatePlan->RatePlanId;
}

$resourceImageUrl = BFI()->plugin_url() . "/assets/images/defaults/default-s6.jpeg";
if(!empty($resource->ImageUrl)){
	$resourceImageUrl = BFCHelper::getImageUrlResized('resources', $resource->ImageUrl,'small');	
}

$showChildrenagesmsg = isset($_REQUEST['showmsgchildage']) ? $_REQUEST['showmsgchildage'] : 0;

//$btnSearchclass=" bfi-not-active"; 
//if(empty($fromSearch)){
//	$btnSearchclass=""; 
//}

$btnSearchclass=""; 

$listDayTS = array();
$currentCartsItems = BFCHelper::getSession('totalItems', 0, 'bfi-cart');

$merchantCategoryId = $merchant->MerchantTypeId;
$masterTypeId = '';
$merchantIds = $merchant->MerchantId;
$stateIds = '';
$regionIds = '';
$cityIds = '';

if(!empty($resourceId)){
	$masterTypeId = $resource->CategoryId;
}

?>

<div id="bfi-datepicker-availability"></div>


<div id="calculator" class="ajaxReload">

<script type="text/javascript">
	var daysToEnable = {};
	var checkOutDaysToEnable = {};
    var unitId = '<?php echo $resourceId ?>';
    var bfi_MaxQtSelectable = <?php echo COM_BOOKINGFORCONNECTOR_MAXQTSELECTABLE ?>;
	var localeSetting = "<?php echo substr($language,0,2); ?>";
	
</script>
<!-- form fields -->
<h4 class="bfi-titleform" style="display: <?php echo (empty($showform)) ?"none":""; ?>"><?php _e('Availability', 'bfi') ?>
	<div class="bfi-pull-right"><a href="<?php echo $url_cart_page ?>" class="bfi-shopping-cart"><i class="fa fa-shopping-cart "></i> <span class="bfibadge" style="<?php echo (COM_BOOKINGFORCONNECTOR_SHOWBADGE) ?"":"display:none"; ?>"><?php echo ($currentCartsItems>0) ?$currentCartsItems:"";?></span><?php _e('Cart', 'bfi') ?></a></div>
	<div class="bfi-hide bfimodalcart">
		<div class="bfi-title"><?php _e('Cart', 'bfi') ?></div>
		<div class="bfi-body"><!-- <?php _e('Add to cart', 'bfi') ?> --></div>
		<div class="bfi-footer">
			<span class="bfi-btn bfi-alternative" onclick="jQuery('.bfi-shopping-cart').webuiPopover('destroy');"><?php _e('Continue shopping', 'bfi') ?></span>
			<span onclick="javascript:window.location.assign('<?php echo $url_cart_page ?>')" class="bfi-btn"><?php _e('Checkout', 'bfi') ?></span>
		</div>
	</div><!-- /.modal -->
<div class="bfi-clearfix"></div>
</h4>
<?php 
if(!empty($variationPlanId) ){
//	$uri  = 'index.php?option=com_bookingforconnector&view=search';
//	$db->setQuery('SELECT id FROM #__menu WHERE link LIKE '. $db->Quote( $uri ) .' AND (language='. $db->Quote($language) .' OR language='.$db->Quote('*').') AND published = 1 LIMIT 1' );
//	$itemId = ($db->getErrorNum())? 0 : intval($db->loadResult());
//	if ($itemId<>0){
//		$uri = 'index.php?Itemid='.$itemId ;
//	}
//	$formRoute = JRoute::_($uri);
	$searchAvailability_page = get_post( bfi_get_page_id( 'searchavailability' ) );
	$formRoute = get_permalink( $searchAvailability_page->ID );
	$formMethod = "GET";
}

$alternativeformRoute = "";
if(!empty($refreshSearch)){
	$searchAvailability_page = get_post( bfi_get_page_id( 'searchavailability' ) );
	$alternativeformRoute = get_permalink( $searchAvailability_page->ID );
}

$currCheckIn = $checkin; 
$currCheckOut = $checkout;

?>
<div class="bfi-summary-search bfi-hidden-xs bfi-hidden-sm" id="bfi-summary-search" style="display: <?php echo (empty($showform)) ?"none":""; ?>">
	<div class="bfi-row ">
		<div class="bfi-col-md-3">
			<div class="fieldLabel">
				<?php 
					switch ($resourcetype) {
						case 3:
							 echo _e('Pick-up','bfi');
							break;
						default:      
							 echo _e('Check-in','bfi');	
					}
				?>
			</div>
			<a href="javascript:bfishowsearch()" class="">
				<strong><?php echo date_i18n('D',$currCheckIn->getTimestamp()) ?> <?php echo $currCheckIn->format("d") ?> <?php echo date_i18n('M',$currCheckIn->getTimestamp()).' '.$currCheckIn->format("Y") ?></strong>
				<?php if($ProductAvailabilityType == 2 && strpos($strCheckinTime,":")!== false ){  ?>
					(<?php echo $strCheckinTime ?>)
				<?php } //$showDateTimeRange ?>
			</a>
		</div>
		<div class="bfi-col-md-3 <?php echo ($ProductAvailabilityType == 3) ? "bfi-hide" : "" ?>">
			<div class="fieldLabel">
				<?php 
					switch ($resourcetype) {
						case 3:
							 echo _e('Drop-off','bfi');
							break;
						default:      
							 echo _e('Check-out ','bfi');
					}
				?>
			</div>
			<a href="javascript:bfishowsearch()">
				<strong><?php echo date_i18n('D',$currCheckOut->getTimestamp()) ?> <?php echo $currCheckOut->format("d") ?> <?php echo date_i18n('M',$currCheckOut->getTimestamp()).' '.$currCheckOut->format("Y") ?></strong>
				<?php if($ProductAvailabilityType == 2 && strpos($strCheckoutTime,":")!== false){  ?>
					(<?php echo $strCheckoutTime ?>)
				<?php } //$showDateTimeRange ?>
			</a>
		</div>
		<div class="bfi-col-md-4">
		<span class="bfi-childmessage" style="clear:both;" id="bfi_lblchildrenagescalculator">&nbsp;</span>
			<div class="fieldLabel"><?php 
					switch ($resourcetype) {
						case 3:
							 echo _e('Persons','bfi');
							break;
						default:      
							 echo _e('Guest','bfi');
					}

			?></div>
			<a href="javascript:bfishowsearch()"><strong>
				<span id="bfi-room-info-calculator" class="bfi-comma bfi-hide"><span><?php echo $nrooms ?></span> <?php _e('Resource', 'bfi'); ?></span>
<?php if($hasPaxes) { ?>
				<span id="bfi-adult-info-calculator" class="bfi-comma"><span><?php echo $nad ?></span> <?php _e('Adults', 'bfi'); ?></span>
				<?php if($useSeniores && $nse>0) { ?>
					<span id="bfi-senior-info-calculator" class="bfi-comma"><span><?php echo $nse ?></span> <?php _e('Seniores', 'bfi'); ?></span>
				<?php } ?>
				<?php if($nch>0) { ?>
					<span id="bfi-child-info-calculator" class="bfi-comma"><span><?php echo $nch ?></span> <?php _e('Children', 'bfi'); ?> (<?php echo implode(',', array_slice($nchs,0,$nch)) ?>)</span>
				<?php } ?>
<?php } else { ?>
				<span id="bfi-adult-info-calculator" class="bfi-comma"><span><?php echo $nad ?></span> <?php _e('Persons', 'bfi'); ?></span>
<?php } ?>
			</strong></a>
		</div>
		<div class="bfi-col-md-2 bfi-text-right">
			<a href="javascript:bfishowsearch()" class=" bfi-btn bfi-alternative <?php echo $btnSearchclass ?>" ><?php _e('Change search', 'bfi'); ?> </a>
		</div>
	</div>
</div>

<form id="bfi-calculatorForm" action="<?php echo $formRoute?>" method="<?php echo $formMethod?>" class="bfi_resource-calculatorForm bfi_resource-calculatorTable bfi-dateform-container " style="display:none"
<?php if($ProductAvailabilityType == 2 && strpos($strCheckinTime,":")!== false){  ?>
	data-checkinTime="<?php echo $strCheckinTime ?>"
<?php } ?>
<?php if($ProductAvailabilityType == 2 && strpos($strCheckoutTime,":")!== false){  ?>
	data-checkoutTime="<?php echo $strCheckoutTime ?>"
<?php } ?>
	data-productavailabilitytype="<?php echo $ProductAvailabilityType ?>"
>
<?php if(!empty($resourceId)) { ?>
<!-- prevent open calendar before load dates -->
<span style="display:none;"><input autofocus type="text"/></span>
<?php } ?>

	<div class="bfi-row bfi_resource-calculatorForm-mandatory ">
			<div class="bfi-row">
				<div class="bfi-col-md-8">
					<div class="bfi-row ">
						<div class="bfi-col-md-6 bfi-col-xs-6 bfi-checkin-field-container" id="calcheckin">
							<label><?php 
								switch ($resourcetype) {
									case 3:
										 echo _e('Pick-up','bfi');
										break;
									default:      
										 echo _e('Check-in','bfi');	
								}
							?></label>
							<div class="bfi-datepicker">
								<input name="checkin" type="text" value="<?php echo $checkin->format('d/m/Y'); ?>" id="<?php echo $checkinId; ?>" class="bfi-checkin-field" readonly="readonly" />
							</div>
							<?php if($ProductAvailabilityType == 2){  ?>
								<div class="bfi-datetimepicker">
									<select id="checkintimedetailsselect" name="checkintime">
										<?php if(strpos($strCheckinTime,":")!== false){  ?>
											<option value="<?php echo $strCheckinTime ?>"><?php echo $strCheckinTime ?></option>
										<?php } ?>
									</select>
								</div>
							<?php } //$showDateTimeRange ?>
						</div>
						<div class="bfi-col-md-6 bfi-col-xs-6 <?php echo ($ProductAvailabilityType == 3 )? "bfi-hide " : " "  ?> bfi-checkout-field-container" id="calcheckout">
							<label><?php 
								switch ($resourcetype) {
									case 3:
										 echo _e('Drop-off','bfi');
										break;
									default:      
										 echo _e('Check-out','bfi');	
								}
							?></label>
							<div class="bfi-datepicker">
								<input type="text" name="checkout" value="<?php echo $checkout->format('d/m/Y'); ?>" id="<?php echo $checkoutId; ?>" class="bfi-checkout-field" readonly="readonly"/>
							</div>
							<?php if($ProductAvailabilityType == 2){  ?>
								<div class="bfi-datetimepicker">
									<select id="checkouttimedetailsselect" name="checkouttime">
										<?php if(strpos($strCheckoutTime,":")!== false){  ?>
											<option value="<?php echo $strCheckoutTime ?>"><?php echo $strCheckoutTime ?></option>
										<?php } ?>
									</select>
								</div>
							<?php } //$showDateTimeRange ?>
							<div class="<?php echo ($ProductAvailabilityType == 3 || $ProductAvailabilityType == 2 || empty($resourceId))? "bfi-hide " : " "  ?>">
								&nbsp;(<span class="calendarnight" id="durationdays"><?php echo $duration ?></span> <span class="calendarnightlabel"><?php echo $ProductAvailabilityType == 1 ? __('Nights' , 'bfi' ) : __('Days' , 'bfi' )  ?></span>)
							</div>
						</div>
					</div>
				</div>
				<div class="bfi-col-md-4 bfi-hidden-xs bfi-hidden-sm">
					<a  href="javascript:calculateQuote(<?php echo !empty($refreshSearch)?"'refreshsearch'":""; ?>)" id="calculateButton" class="calculateButton-mandatory calculateButton3 bfi-btn bfi-alternative <?php echo $btnSearchclass ?>" ><?php echo _e('Check availability','bfi') ?> </a>
				</div>
			</div>
				<div class="bfi-row">
					<div class="bfi-col-md-2 bfi-col-xs-4 bfi_resource-calculatorForm-adult">
						<label><?php echo __($hasPaxes?'Adults':'Persons','bfi') ?>:</label><br />
						<select id="adultscalculator" name="adultssel" onchange="quoteCalculatorChanged();" class="">
							<?php
							foreach (range(1, 10) as $number) {
								?> <option value="<?php echo $number ?>" <?php echo ($nad == $number)?"selected":""; ?>><?php echo $number ?></option><?php
							}
							?>
						</select>
					</div>
<?php if($hasPaxes) { ?>									
					<?php 
					if($useSeniores){
					?>
					<div class="bfi-col-md-2 bfi-col-xs-4 bfi_resource-calculatorForm-senior" >
						<label><?php echo __('Seniors','bfi') ?>:</label><br />
						<select id="seniorescalculator" name="senioressel" onchange="quoteCalculatorChanged();" class="">
							<?php
							foreach (range(0, 10) as $number) {
								?> <option value="<?php echo $number ?>" <?php echo ($nse == $number)?"selected":""; ?>><?php echo $number ?></option><?php
							}
							?>
						</select>
					</div>
					<?php 
					}
					?>
					<div class="bfi-col-md-2 bfi-col-xs-4 bfi_resource-calculatorForm-children">
						<label><?php echo __('Children','bfi') ?>:</label><br />
						<select id="childrencalculator" name="childrensel" onchange="quoteCalculatorChanged();" class="">
							<?php
							foreach (range(0, 4) as $number) {
								?> <option value="<?php echo $number ?>" <?php echo ($nch == $number)?"selected":""; ?>><?php echo $number ?></option><?php
							}
							?>
						</select>
					</div>
<?php } ?>
				</div>
				<div class="bfi_resource-calculatorForm-childrenages" style="display:none;">
					<span class="fieldLabel" style="display:inline"><?php  echo __('Ages of children','bfi')  ?>:</span>
					<span class="fieldLabel" style="display:inline" id="bfi_lblchildrenagesatcalculator"><?php echo _e('on', 'bfi') . " " .$checkout->format("d"). " " . date_i18n('M',$checkout->getTimestamp()) . " " . $checkout->format("Y") ?></span><br />
					<select id="childages1" name="childages1sel" onchange="quoteCalculatorChanged();" class="bfi-inputmini" style="display: none;">
						<option value="<?php echo COM_BOOKINGFORCONNECTOR_CHILDRENSAGE ?>" ></option>
						<?php
						foreach (range(0, $maxchildrenAge) as $number) {
							?> <option value="<?php echo $number ?>" <?php echo ($nchs[0] !== null && $nchs[0] == $number)?"selected":""; ?>><?php echo $number ?></option><?php
						}
						?>
					</select>
					<select id="childages2" name="childages2sel" onchange="quoteCalculatorChanged();" class="bfi-inputmini" style="display: none;">
						<option value="<?php echo COM_BOOKINGFORCONNECTOR_CHILDRENSAGE ?>" ></option>
						<?php
						foreach (range(0, $maxchildrenAge) as $number) {
							?> <option value="<?php echo $number ?>" <?php echo ($nchs[1] !== null && $nchs[1] == $number)?"selected":""; ?>><?php echo $number ?></option><?php
						}
						?>
					</select>
					<select id="childages3" name="childages3sel" onchange="quoteCalculatorChanged();" class="bfi-inputmini" style="display: none;">
						<option value="<?php echo COM_BOOKINGFORCONNECTOR_CHILDRENSAGE ?>" ></option>
						<?php
						foreach (range(0, $maxchildrenAge) as $number) {
							?> <option value="<?php echo $number ?>" <?php echo ($nchs[2] !== null && $nchs[2] == $number)?"selected":""; ?>><?php echo $number ?></option><?php
						}
						?>
					</select>
					<select id="childages4" name="childages4sel" onchange="quoteCalculatorChanged();" class="bfi-inputmini" style="display: none;">
						<option value="<?php echo COM_BOOKINGFORCONNECTOR_CHILDRENSAGE ?>" ></option>
						<?php
						foreach (range(0, $maxchildrenAge) as $number) {
							?> <option value="<?php echo $number ?>" <?php echo ($nchs[3] !== null && $nchs[3] == $number)?"selected":""; ?>><?php echo $number ?></option><?php
						}
						?>
					</select>
					<select id="childages5" name="childages5sel" onchange="quoteCalculatorChanged();" class="bfi-inputmini" style="display: none;">
						<option value="<?php echo COM_BOOKINGFORCONNECTOR_CHILDRENSAGE ?>" ></option>
						<?php
						foreach (range(0, $maxchildrenAge) as $number) {
							?> <option value="<?php echo $number ?>" <?php echo ($nchs[4] !== null && $nchs[4] == $number)?"selected":""; ?>><?php echo $number ?></option><?php
						}
						?>
					</select>
				</div> 
				<div class=" bfi-hidden-md bfi-hidden-lg bfi-margin-top10 bfi-margin-bottom10">
					<a href="javascript:calculateQuote(<?php echo !empty($refreshSearch)?"'refreshsearch'":""; ?>)" class="calculateButton-mandatory calculateButton3 bfi-btn bfi-alternative <?php echo $btnSearchclass ?>" ><?php echo _e('Check availability','bfi') ?> </a>
				</div>

	</div>	<!-- END bfi_resource-calculatorForm-mandatory -->
	<input name="onlystay" type="hidden" value="1" />
	<input name="newsearch" type="hidden" value="1" />
	<input name="groupresulttype" type="hidden" value="0" />
	<input name="calculate" type="hidden" value="true" />
	<input name="resourceId" type="hidden" value="<?php echo $resourceId?>" />
	
	<input type="hidden" name="persons" value="<?php echo $nad + $nse + $nch?>" id="searchformpersons-calculator" />
	<input type="hidden" name="adults" value="<?php echo $nad?>" id="searchformpersonsadult-calculator">
	<input type="hidden" name="seniores" value="<?php echo $nse?>" id="searchformpersonssenior-calculator">
	<input type="hidden" name="children" value="<?php echo $nch?>" id="searchformpersonschild-calculator">
	<input type="hidden" name="childages1" value="<?php echo $nchs[0]?>" id="searchformpersonschild1-calculator">
	<input type="hidden" name="childages2" value="<?php echo $nchs[1]?>" id="searchformpersonschild2-calculator">
	<input type="hidden" name="childages3" value="<?php echo $nchs[2]?>" id="searchformpersonschild3-calculator">
	<input type="hidden" name="childages4" value="<?php echo $nchs[3]?>" id="searchformpersonschild4-calculator">
	<input type="hidden" name="childages5" value="<?php echo $nchs[4]?>" id="searchformpersonschild5-calculator">

	<input name="pricetype" type="hidden" value="<?php echo $selPriceType ?>" />
	<input name="bookingType" type="hidden" value="<?php echo $selBookingType ?>" />
	<input name="variationPlanId" type="hidden" value="<?php echo $variationPlanId ?>" />
	<input name="refreshsearch" type="hidden" value="<?php echo $refreshSearch ?>" />
	<input name="merchantId" type="hidden" value="<?php echo $merchant->MerchantId ?>" />
	<input name="resourcegroupId" type="hidden" value="<?php echo $resourcegroupId ?>" />
	<input name="state" type="hidden" value="<?php echo $currentState ?>" />
	<input name="extras[]" type="hidden" value="<?php echo $selectablePrices ?>" />
	<input name="refreshcalc" type="hidden" value="1" />
	<input name="fromsearch" type="hidden" value="1" />
	<input name="itemTypeIds" type="hidden" value="<?php echo $itemTypeIds ?>" />
	<input name="lna" type="hidden" value="<?php echo $listNameAnalytics ?>" />
	<input name="checkFullPeriod" type="hidden" value="<?php echo $ProductAvailabilityType==2?1:0; ?>" />
	<input type="hidden" name="filter_order" value="" />
	<input type="hidden" name="filter_order_Dir" value="" />
	
	<input type="hidden" name="timeSlotId" value="<?php echo $timeSlotIdSelected ?>" />
	<input type="hidden" name="hideform" value="<?php echo (!empty($hideform) ? $hideform : "0") ?>" />

	<input type="hidden" value="0" name="showmsgchildage" id="showmsgchildagecalculator"/>
	<div class="bfi-hide" id="bfi_childrenagesmsgcalculator">
		<div style="line-height:0; height:0;"></div>
		<div class="bfi-pull-right" style="cursor:pointer;color:red">&nbsp;<i class="fa fa-times-circle" aria-hidden="true" onclick="jQuery('#bfi_lblchildrenagescalculator').webuiPopover('destroy');"></i></div>
		<?php echo sprintf(__('We preset your children\'s ages to %s years old - but if you enter their actual ages, you might be able to find a better price.', 'bfi'),COM_BOOKINGFORCONNECTOR_CHILDRENSAGE) ?>
	</div>
</form>	
<!-- form fields end-->

<!-- RESULT -->	
<?php 

$showResult= false; //" bfi-hide";
if(!empty($fromSearch)){
	$showResult= true;// "";
} 
	
$resCount = 0;
$totalResCount = count((array)$allRatePlans);	


$loadScriptTimeSlot = false;
$loadScriptTimePeriod = false;

$allResourceId = array();
$allServiceIds = array();

if(is_array($allRatePlans) && $totalResCount>0){
	$allResourceId = array_unique(array_map(function ($i) { return $i->ResourceId; }, $allRatePlans));
}

if(!empty($allResourceId)){
	$keyfirst = array_search($resourceId, $allResourceId);
	$tempfirst = array($keyfirst => $allResourceId[$keyfirst]);
	unset($allResourceId[$keyfirst]);
	$allResourceId = $tempfirst + $allResourceId;
}

$track = array($merchantCategoryId,$masterTypeId,$checkin->format('d/m/Y'),$checkout->format('d/m/Y'),$nad,$nse,$nch,implode(',',$nchs),$ProductAvailabilityType,$totalResCount,$merchantIds,$stateIds,$regionIds,$cityIds);
$trackstr = implode('|',$track);
if(strlen($trackstr) > 500){
	$trackstr = substr($trackstr, 0, 500);
}

?>

<div class="bfi-clearfix"></div>



<div class="bfi-hide">
	<div class="bfi-period-change bfi-dateform-container" id="bfimodalperiod" data-productavailabilitytype="0,1">
		<!-- prevent open calendar before load dates -->
		<span style="display:none;"><input autofocus type="text" /></span>
		<div class="bfi-simplerow bfi-checkin-field-container">
			<?php echo _e('Check-in','bfi') ?>
			<div class="bfi-datepicker check-availibility-date bfi-text-center">
				<input id="bfimodalperiodcheckin" type="text" name="checkin" value="<?php echo $checkin->format('d/m/Y'); ?>" class="ChkAvailibilityPeriod bfi-checkin-field" readonly="readonly" />
				</div>	
				</div>	
				<div class="bfi-simplerow bfi-checkout-field-container">
			<?php echo _e('Check-out','bfi') ?>
			<div class="bfi-datepicker check-availibility-date bfi-text-center">
				<input id="bfimodalperiodcheckout" type="text" name="checkout" value="<?php echo $checkout->format('d/m/Y'); ?>" class="ChkAvailibilityPeriod bfi-checkout-field" readonly="readonly" />
			</div>
		</div>
		<div class="bfi-simplerow bfi-text-center">
			<a id="bfi-period-select" class="bfi-btn" onclick="bfi_selectperiod(jQuery(this))" data-rateplanid="0" data-resid="0"><?php _e('Select', 'bfi') ?></a>
		</div>
	</div>
</div><!-- /.modal -->
<div class="bfi-hide">
		<div class="bfi-timeperiod-change bfi-dateform-container" id="bfimodaltimeperiod" data-productavailabilitytype="2">
				<!-- prevent open calendar before load dates -->
				<span style="display:none;"><input autofocus type="text" /></span>
				<div class="bfi-simplerow bfi-checkin-field-container">
					<?php echo _e('Check-in','bfi') ?>
					<div class="bfi-datepicker check-availibility-date bfi-text-center">
						<input id="bfimodaltimeperiodcheckin" type="text" name="checkin" value="<?php echo $checkin->format('d/m/Y'); ?>" class="ChkAvailibilityFromDateTimePeriod bfi-checkin-field" readonly="readonly" />
					</div>
				</div>	
				<div class="bfi-simplerow">
					<select class="bfi_input_select selectpickerTimePeriodStart" id="selectpickerTimePeriodStart" data-rateplanid="0"></select>
				</div>	
				<div class="bfi-simplerow bfi-checkout-field-container" style="margin-top:10px;">
					<?php echo _e('Check-out','bfi') ?>
					<div class="bfi-datepicker check-availibility-date-end bfi-text-center">
						<input id="bfimodaltimeperiodcheckout" type="text" name="checkout" value="<?php echo $checkout->format('d/m/Y'); ?>" class="ChkAvailibilityFromDateTimePeriod bfi-checkout-field" readonly="readonly" />
					</div>
				</div>	
				<div class="bfi-simplerow">
					<select class="bfi_input_select selectpickerTimePeriodEnd" id="selectpickerTimePeriodEnd" data-rateplanid="0"></select>
				</div>	
				<div class="bfi-simplerow bfi-text-center">
					<a id="bfi-timeperiod-select" class="bfi-btn" onclick="bfi_selecttimeperiod(this)" data-rateplanid="0" data-resid="0"><?php _e('Select', 'bfi') ?></a>
				</div>	
		</div>
</div><!-- /.modal -->

<div class="bfi-hide">
		<div class="bfi-timeslot-change" id="bfimodaltimeslot" data-productavailabilitytype="3">
				<!-- prevent open calendar before load dates -->
				<span style="display:none;"><input autofocus type="text" /></span>
				<div class="bfi-simplerow bfi-checkin-field-container">
					<div class="bfi-datepicker check-availibility-date bfi-text-center">
						<input id="bfimodaltimeslotcheckin" type="text" name="checkin" value="<?php echo $checkin->format('d/m/Y'); ?>" class="ChkAvailibilityFromDateTimeSlot bfi-checkin-field" readonly="readonly" />
					</div>
				</div>	
				<div class="bfi-simplerow ">
					<select class="bfi_input_select selectpickerTimeSlotRange" id="selectpickerTimeSlotRange" data-rateplanid="0"></select>
				</div>	
				<div class="bfi-simplerow bfi-text-center">
					<a id="bfi-timeslot-select" class="bfi-btn" onclick="bfi_selecttimeslot(this)" data-rateplanid="0" data-resid="0" data-sourceid="0"><?php _e('Select', 'bfi') ?></a>
				</div>	
		</div>
</div><!-- /.modal -->
<?php 
if(!empty($fromSearch) && empty($allResourceId) &&  !empty($makesearch)){
?>
					<div class="bfi-errorbooking" id="bfi-errorbooking">
						<strong><?php _e('No results available for the submitted data', 'bfi') ?></strong>
						<!-- No disponibile -->
<?php
	if(!empty($showform) && !empty(COM_BOOKINGFORCONNECTOR_ENALBLEOTHERMERCHANTSRESULT) && empty($allmerchants) && $newsearch==1) { 
?>
						<a href="javascript:calculateQuote('all')" id="calculateButton" class="calculateButton-mandatory calculateButton3 bfi-btn bfi-alternative <?php echo $btnSearchclass ?>" ><?php _e('Check availability at other merchants', 'bfi') ?> </a>
						<script type="text/javascript">
						<!--
						jQuery(document).ready(function () {
									setTimeout(function() {calculateQuote('all')}, 1);
						});
						//-->
						</script>
					</div>
<?php }else{ 
?>
					</div>
<?php 
			if (!empty($showform) && $newsearch==1) {
			?>
			<div class="bfi-altbookingdate bfi-check-more" data-type="merchant" data-id="<?php echo $merchant->MerchantId ?>" >
				<?php _e('Limited availability, but may sell out:', 'bfi') ?>
				<div class="bfi-check-more-slider">
				</div>
			</div>
			<script type="text/javascript">
			<!--
			jQuery(document).ready(function () {
                setTimeout(function () {
                    bookingfor.bfiCheckOtherAvailability(<?php echo $duration ?>,'<?php echo $checkin->format('Ymd'); ?>',<?php echo $paxes ?>,'<?php echo implode('|',$paxages) ?>','<?php echo $resourcegroupId ?>','<?php echo $ProductAvailabilityType ?>','0',true, currCheckin, currCheckout);
                }, 1);
			});
			//-->
			</script>
<?php 
			}
	}
?>
<?php 
	$showResult= false; //" bfi-hide";
}

if(!empty($fromSearch) && !empty($makesearch)){
?>
<script type="text/javascript">
<!--
	if (typeof(ga) !== 'undefined') {
		ga('send', 'event', 'Bookingfor - Search', 'Search', '<?php echo $trackstr ?>');
	}
	
//-->
</script>
	
<?php } ?>
<?php if($allmerchants=='1' &&  $showResult  ) { ?>
						<!-- No disponibile -->
					<div class="bfi-errorbooking">
						<strong><?php _e('No results available for the submitted data', 'bfi') ?></strong>
					</div>
					<div class="bfi-altbookingdate bfi-check-more" data-type="merchant" data-id="<?php echo $merchant->MerchantId ?>" >
						<?php _e('Limited availability, but may sell out:', 'bfi') ?>
						<div class="bfi-check-more-slider">
						</div>
					</div>
					<script type="text/javascript">
					<!--
					jQuery(document).ready(function () {
                        setTimeout(function () {
                            bookingfor.bfiCheckOtherAvailability(<?php echo $duration ?>,'<?php echo $checkin->format('Ymd'); ?>',<?php echo $paxes ?>,'<?php echo implode('|',$paxages) ?>','<?php echo $resourcegroupId ?>','<?php echo $ProductAvailabilityType ?>','0',true, currCheckin, currCheckout);
                        }, 1);
					});
					//-->
					</script>
					<div class="bfiotherfacilities">
						<?php _e('We found availability at other merchants', 'bfi') ?>
					</div>
<?php } ?>
	<a href="javascript:bfishowsearch()" class="bfi-btn bfi-alternative2 bfi-hidden-md bfi-hidden-lg" style="float:left;">
		<strong><?php echo $currCheckIn->format("d") ?> <?php echo date_i18n('M',$currCheckIn->getTimestamp())?>
		<?php if($ProductAvailabilityType == 2 && strpos($strCheckinTime,":")!== false ){  ?>(<?php echo $strCheckinTime ?>)<?php } //$showDateTimeRange ?>
		<?php if($ProductAvailabilityType !== 3) { ?> - <?php echo $currCheckOut->format("d") ?> <?php echo date_i18n('M',$currCheckOut->getTimestamp())?>
		<?php if($ProductAvailabilityType == 2 && strpos($strCheckoutTime,":")!== false ){  ?>(<?php echo $strCheckoutTime ?>)<?php } //$showDateTimeRange ?>
		<?php } ?></strong>
	</a>
	<a href="javascript:bfishowsearch()" class="bfi-btn bfi-alternative2 bfi-hidden-md bfi-hidden-lg" style="float:left;"><strong>
				<span id="bfi-room-info-calculator" class="bfi-comma bfi-hide"><span><?php echo $nrooms ?></span> <?php _e('Resource', 'bfi'); ?></span>
				<span id="bfi-adult-info-calculator" class="bfi-comma"><span><?php echo $nad ?></span> <?php _e('Adults', 'bfi'); ?></span>
				<?php if($nse>0) { ?>
					<span id="bfi-senior-info-calculator" class="bfi-comma"><span><?php echo $nse ?></span> <?php _e('Seniores', 'bfi'); ?></span>
				<?php } ?>
				<?php if($nch>0) { ?>
					<span id="bfi-child-info-calculator" class="bfi-comma"><span><?php echo $nch ?></span> <?php _e('Children', 'bfi'); ?> (<?php echo implode(',', array_slice($nchs,0,$nch)) ?>)</span>
				<?php } ?>
	</strong></a>



<?php

if (!empty(COM_BOOKINGFORCONNECTOR_ENALBLERESOURCEFILTER) ) {
	$filters = BFCHelper::getVar('filters');
	if ($showResult || !empty( $filters )) {
		bfi_get_template('shared/filter_details.php',array("merchant"=>$merchant,"cachekey"=>"mrc".$merchant->MerchantId)); 
	}   
}
?>

<div class="bfi-result-list <?php echo $showResult?"":" bfi-hide" ?> bfi-table-responsive">

<script type="text/javascript">
<!--
    var pricesExtraIncluded=[];
//-->
</script>
		<table class="bfi-table bfi-table-bordered bfi-table-resources bfi-table-resources-sticked bfi-table-resourcessearchdetails bfi-table-resources-step1" style="margin-top: 20px;">
			<thead>
				<tr>
					<th><div><?php _e('Information', 'bfi') ?></div></th>
					<th><div>&nbsp;</div></th>
					<th><div><?php _e('Price', 'bfi') ?></div></th>
					<th><div><?php _e('Options', 'bfi') ?></div></th>
					<th><div><?php _e('Qt.', 'bfi') ?></div></th>
					<th><div></div></th>
				</tr>
			</thead>

			
			<tbody>
			<tr>
				<td colspan="5" style="padding:0;border:none;"></td>
				<td rowspan="400" class="bfi-book-now-td">
							<div class="bfi-book-now">
								<div class="bfi-book-now-content">
									<div class="bfi-resource-totalselected"><span></span> <?php _e('selected items', 'bfi') ?></div>
									<div class="bfi-discounted-price bfi-discounted-price-total bfi_<?php echo $currencyclass ?>" style="display:none;"></div>
									<div class="bfi-price bfi-price-total bfi_<?php echo $currencyclass ?>" ></div>
									<div id="btnBookNow" class="bfi-btn bfi-btn-book-now" data-formroute="" onclick="bfi_ChangeVariation(this);" style="display:none;">
										<?php _e('Book Now', 'bfi') ?>
									</div>
									<div class="bfi-btn bfi-alternative bfi-request-now" onclick="bfi_ChangeVariation(this);" style="display:none;">
										<?php _e('Request Now', 'bfi') ?>
									</div>
								</div>
								<div class="bfi-btn bfi-alternative bfi-request-now-first" onclick="bfi_ChangeVariation(this);" style="display:;">
									<?php _e('Request Now', 'bfi') ?>
								</div>
							</div>
				</td>
			</tr>

			<?php  if(!empty($resourceId) && !in_array($resourceId,$allResourceId)) {
				switch ($resource->ItemTypeId ) {
					case bfi_ItemType::Experience :
						$currUriresource  = $url_resource_page_experience.$resource->ResourceId.'-'.BFI()->seoUrl($resourceName) . "?fromsearch=1&lna=".$listNameAnalytics;
					break;
					default:      
						$currUriresource  = $uri.$resource->ResourceId.'-'.BFI()->seoUrl($resourceName) . "?fromsearch=1&lna=".$listNameAnalytics;
					break;
				} // end switch

//				$currUriresource = $uri.$resourceId. '-' . BFCHelper::getSlug($resource->Name) . "?fromsearch=1&lna=".$listNameAnalytics;
				$resourceNameTrack =  BFCHelper::string_sanitize($resource->Name);
				$merchantNameTrack =  BFCHelper::string_sanitize($merchant->Name);
				$merchantCategoryNameTrack =  BFCHelper::string_sanitize($merchant->MainCategoryName);

			?>
			<tr>
				<td class="bfi-firstcol bfi-firstcolum-selected">
					<a class="bfi-resname eectrack" onclick="bfiGoToTop()" href="<?php echo $currUriresource ?>" data-type="Resource" data-id="<?php echo $resource->ResourceId?>" data-index="0" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php echo $resource->Name; ?></a>
<div class="bfi-clearfix"></div>
<?php 
			if(false && !empty($resource->ImageUrl)){
				$resourceImageUrl = BFCHelper::getImageUrlResized('resources',$resource->ImageUrl, 'small');
?>
<a class="bfi-link-searchdetails" onclick="bfiGoToTop()" href="<?php echo $currUriresource ?>"><img src="<?php echo $resourceImageUrl; ?>" class="bfi-img-searchdetails" /></a>
<div class="bfi-clearfix"></div>
<?php 
			}
					$listServices = array();
					if(!empty($resource->ResServiceIdList)){
						$listServices = explode(",", $resource->ResServiceIdList);
						$allServiceIds = array_merge($allServiceIds, $listServices);
						?>
						<div class="bfisimpleservices" rel="<?php echo $res->ResServiceIdList ?>"></div>
						
						<?php
					}
					if(!empty($resource->TagsIdList)){
						?>
						<div class="bfiresourcegroups" rel="<?php echo $resource->TagsIdList?>"></div>
						<?php
					}					

$currVat = isset($resource->VATValue)?$resource->VATValue:"";					
$currTouristTaxValue = isset($resource->TouristTaxValue)?$resource->TouristTaxValue:0;				
?>
<?php if(!empty($currVat)) { ?>
	<div class="bfi-incuded"><strong><?php _e('Included', 'bfi') ?></strong> : <?php echo $currVat?> <?php _e('VAT', 'bfi') ?> </div>
<?php } ?>
<?php if(!empty($currTouristTaxValue)) { ?>
	<div class="bfi-notincuded"><strong><?php _e('Not included', 'bfi') ?></strong> : <span class="bfi_<?php echo $currencyclass ?>" ><?php echo BFCHelper::priceFormat($currTouristTaxValue) ?></span> <?php _e('City tax per person per night.', 'bfi') ?> </div>
<?php } ?>

				</td>
				<td>
				<?php if (isset($resource->MaxCapacityPaxes) && $resource->MaxCapacityPaxes>0){?>
					<div class="bfi-icon-paxes">
						<i class="fa fa-user"></i> 
						<?php if ($resource->MaxCapacityPaxes==2){?>
						<i class="fa fa-user"></i> 
						<?php }?>
						<?php if ($resource->MaxCapacityPaxes>2){?>
							<?php echo ($resource->MinCapacityPaxes != $resource->MaxCapacityPaxes)? $resource->MinCapacityPaxes . "-" : "" ?><?php echo  $resource->MaxCapacityPaxes?>
						<?php }?>
					</div>
					<?php } ?>
				</td>
				<td colspan="3" style="vertical-align:middle;text-align:center;">
					<div class="bfi-errorbooking" id="bfi-errorbooking">
						<strong><?php _e('No results available for the submitted data', 'bfi') ?></strong>
						<!-- No disponibile -->
						<?php if(isset($resource->MaxCapacityPaxes) && $resource->MaxCapacityPaxes > 0 && ( $totalPerson > $resource->MaxCapacityPaxes || $totalPerson < $resource->MinCapacityPaxes )) { ?><!-- Errore persone-->
							<br /><?php echo sprintf(__('Persons min:%1$d max:%2$d', 'bfi'), $resource->MinCapacityPaxes, $resource->MaxCapacityPaxes) ?>
						<?php } ?>
					</div>
<?php if(!empty($fromSearch) &&  !empty($makesearch) && $showResult && $newsearch==1) { ?>
					<div class="bfi-altbookingdate bfi-check-more" data-type="resource" data-id="<?php echo $resource->ResourceId ?>" >
						<?php _e('Limited availability, but may sell out:', 'bfi') ?>
						<div class="bfi-check-more-slider">
						</div>
					</div>
					<script type="text/javascript">
					<!--
					jQuery(document).ready(function () {
                        setTimeout(function () {
                            bookingfor.bfiCheckOtherAvailability(<?php echo $duration ?>,'<?php echo $checkin->format('Ymd'); ?>',<?php echo $paxes ?>,'<?php echo implode('|',$paxages) ?>','<?php echo $resourcegroupId ?>','<?php echo $ProductAvailabilityType ?>','0',true, currCheckin, currCheckout);
                        }, 1);
					});
					//-->
					</script>
<?php } ?>
				</td>
			</tr>
		<?php } ?>
<?php

$allSelectablePrices = array();
$allTimeSlotResourceId = array();
$allTimePeriodResourceId = array();
$reskey = -1;
$resRef = -1;
$upselltitleviewed = array();
$normalResourcesCount = count(array_unique(array_map(function ($i) { return $i->ResourceId; }, array_filter($allRatePlans, function($rp){ return !$rp->UpSellResult && !$rp->CrossSellResult; }))));
$otherResourcesTitleViewed = false;

if (empty($showform) && !empty($resourceId) && in_array($resourceId,$allResourceId) ) {
    $allResourceId = array($resourceId);

}
foreach($allResourceId as $resId) {

	$reskey += 1;
	$currKey = $reskey;
	if(!empty($resourceId) && !in_array($resourceId,$allResourceId)) {
		$currKey += 1;
	}
	$resRateplansUnsorted =  array_values ( array_filter($allRatePlans, function($p) use ($resId) {return $p->ResourceId == $resId ;}) ); // c#: allRatePlans.Where(p => p.ResourceId == resId);

	$sort = array();
	foreach($resRateplansUnsorted as $k=>$v) {
	   $sort['TotalComputedPaxes'][$k] = $v->RatePlan->SuggestedStay->TotalComputedPaxes;
	   $adultPaxesParameters = 0;
	   foreach (explode("|", $v->RatePlan->SuggestedStay->ComputedPaxes) as $computedPaxesParameters) {
			$computedPaxesParameters =  explode(":", $computedPaxesParameters."::::");			
			if ($computedPaxesParameters[3] == "0") {
				$adultPaxesParameters += $computedPaxesParameters[1];
			}	   
	   }
	   $sort['ComputedPaxesParameters'][$k] = $adultPaxesParameters ;
	}	
	rsort($sort['TotalComputedPaxes'],SORT_NUMERIC );
	rsort($sort['ComputedPaxesParameters'],SORT_NUMERIC );
	$newOrderedresRateplansTotalComputedPaxes = array();
	$newOrderedresRateplans = array();

	foreach (array_unique($sort['TotalComputedPaxes']) as $currTotalComputedPaxes) {
		$newOrderedresRateplansTotalComputedPaxes =  array_filter($resRateplansUnsorted, function($p) use ($currTotalComputedPaxes) {return $p->RatePlan->SuggestedStay->TotalComputedPaxes == $currTotalComputedPaxes ;}) ; 
		$newOrderedresRateplansQuantity = array();
			foreach (array_unique($sort['ComputedPaxesParameters']) as $currQuantity) {
				$newOrderedresRateplansQuantity =  array_filter($newOrderedresRateplansTotalComputedPaxes, function($p) use ($currQuantity) {
					   $adultPaxesParameters = 0;
					   foreach (explode("|", $p->RatePlan->SuggestedStay->ComputedPaxes) as $computedPaxesParameters) {
							$computedPaxesParameters =  explode(":", $computedPaxesParameters."::::");
							if ($computedPaxesParameters[3] == "0") {
								$adultPaxesParameters += $computedPaxesParameters[1];
							}	   
					   }
					return $adultPaxesParameters == $currQuantity ;
					
					}) ; 
				$newOrderedresRateplans = array_merge($newOrderedresRateplans, $newOrderedresRateplansQuantity);
			}
	}

	$resRateplans = $newOrderedresRateplans ;
	//usort($resRateplans, "BFCHelper::bfi_sortResourcesRatePlans");
	
	$res = array_values($resRateplans)[0];
	
	if($res->UpSellResult && !isset($upselltitleviewed[$res->UpSellReferrerItemId])) {
		$upselltitleviewed[$res->UpSellReferrerItemId] = "1";
		?>
		<tr><td colspan="5">Possono interessarti anche le seguenti proposte di "<?php echo $res->UpSellReferrerItemName; ?>":</td></tr>
		<?php
	}
	$referenceResourceId = !$res->UpSellResult && !$res->CrossSellResult ? $resId : ($res->UpSellResult ? $res->UpSellReferrerItemId : $res->CrossSellReferrerItemId);
	//if (count($allResourceId) > 1 && (($referenceResourceId == $resourceId && $resCount > 1) || ($referenceResourceId == $resourceId && $resCount == 0))){ 
	if (!$otherResourcesTitleViewed && !empty($resourceId) && $referenceResourceId != $resourceId && ($normalResourcesCount-1)>0) {
		$otherResourcesTitleViewed = true;
		?>
			<tr><td colspan="5" class="bfi-otherresults-box"><div class="bfi-otherresults"><?php echo sprintf(__('Other %1$d choise', 'bfi'), ($normalResourcesCount)) ?></div> <?php _e('Find other great offers!', 'bfi') ?></td></tr>
		<?php 
	}

	$IsBookable = 0;
	
	$isResourceBlock = $res->ResourceId == $resourceId;

	$IsBookable = $res->IsBookable;
	$showQuote = false;
	
	$tmpCheckIn= DateTime::createFromFormat('Y-m-d\TH:i:s',$res->RatePlan->CheckIn,new DateTimeZone('UTC'));
	$tmpCheckOut= DateTime::createFromFormat('Y-m-d\TH:i:s',$res->RatePlan->CheckOut,new DateTimeZone('UTC'));
	if (($res->Price > 0 && $res->Availability > 0) && ($res->MaxPaxes == 0 || ($totalPerson <= $res->MaxPaxes && $totalPerson >= $res->MinPaxes)) &&
		(($res->AvailabilityType == 3 || $res->AvailabilityType == 2) && $tmpCheckIn == $dateStringCheckin)
		|| 
		(($res->AvailabilityType == 0 || $res->AvailabilityType == 1) && $tmpCheckIn == $dateStringCheckin && $tmpCheckOut == $dateStringCheckout))		{
			$showQuote = true;
		}
//	if (($res->Price > 0 && $res->Availability > 0) && ($res->MaxPaxes == 0 || ($totalPerson <= $res->MaxPaxes && $totalPerson >= $res->MinPaxes)) &&
//		(($res->AvailabilityType == 3 || $res->AvailabilityType == 2) && BFCHelper::parseJsonDate($res->RatePlan->CheckIn) == $dateStringCheckin)
//		|| 
//		(($res->AvailabilityType == 0 || $res->AvailabilityType == 1) && BFCHelper::parseJsonDate($res->RatePlan->CheckIn) == $dateStringCheckin && BFCHelper::parseJsonDate($res->RatePlan->CheckOut) == $dateStringCheckout))		{
//			$showQuote = true;
//		}
	
//	$resourceImageUrl = BFI()->plugin_url() . "/assets/images/defaults/default-s6.jpeg";
//	if(!empty($res->ImageUrl)){
//		$resourceImageUrl = BFCHelper::getImageUrlResized('resources', $res->ImageUrl,'small');	
//	}
	switch ($res->ItemTypeId ) {
		case bfi_ItemType::Experience :
			$currUriresource  = $url_resource_page_experience.$res->ResourceId.'-'.BFI()->seoUrl($res->ResName) . "?fromsearch=1&lna=".$listNameAnalytics;
		break;
		default:      
			$currUriresource  = $uri.$res->ResourceId.'-'.BFI()->seoUrl($res->ResName) . "?fromsearch=1&lna=".$listNameAnalytics;
		break;
	} // end switch
//	$currUriresource = $uri.$res->ResourceId . '-' . BFCHelper::getSlug($res->ResName) . "?fromsearch=1&lna=".$listNameAnalytics;

	$formRouteSingle = $currUriresource;

	$resourceNameTrack =  BFCHelper::string_sanitize($res->ResName);
	$merchantNameTrack =  BFCHelper::string_sanitize($merchant->Name);
	$merchantCategoryNameTrack =  BFCHelper::string_sanitize($merchant->MainCategoryName);

	$eecstay = new stdClass;
	$eecstay->id = "" . $res->ResourceId . " - Resource";
	$eecstay->name = "" . $resourceNameTrack;
	$eecstay->category = $merchantCategoryNameTrack;
	$eecstay->brand = $merchantCategoryNameTrack;
	$currCheckIn = DateTime::createFromFormat('Y-m-d\TH:i:s',$res->RatePlan->CheckIn,new DateTimeZone('UTC'));
	$currCheckOut = DateTime::createFromFormat('Y-m-d\TH:i:s',$res->RatePlan->CheckOut,new DateTimeZone('UTC'));
//	$eecstay->variant = $showQuote ? strtoupper($selRateName) : "NS";
	$eecstay->position = $reskey;
	if($isResourceBlock) {
		$eecmainstay = $eecstay;
	} else {
		$eecstays[] = $eecstay;
	}

		
//	$formRouteBook = JRoute::_('index.php?option=com_bookingforconnector&view=resource&layout=form&resourceId=' . $res->ResourceId . ':' . BFCHelper::getSlug($res->Name));
//	if($usessl){
//		$formRouteBook = JRoute::_('index.php?option=com_bookingforconnector&view=resource&layout=form&resourceId=' . $res->ResourceId . ':' . BFCHelper::getSlug($res->Name),true,1);
//	}
	
	$btnText = __('Request info', 'bfi');
	$btnClass = "bfi-alternative";
	if ($IsBookable){
		$btnText = __('Book Now', 'bfi');
		$btnClass = "";
	}
	$formRouteBook = "";
	$nRowSpan = 1+count($resRateplans);
	if (count($resRateplans)>$hideRateplanOver) {
	    $nRowSpan = 2+ $hideRateplanOver;
	}
?>
			<tr >
				<td rowspan="<?php echo $nRowSpan ?>" class="bfi-firstcol <?php echo ($resId == $resourceId)? '  bfi-firstcolum-selected' :  '' ; ?>" <?php echo !$res->UpSellResult && !$res->CrossSellResult ? 'style="border-top: 3px solid rgb(224, 224, 224);"' : ''; ?>  id="bfitdresdesc<?php echo $resId?>">
					<?php if($res->MerchantId != $merchant->MerchantId) { 
//						$currUriMerchant = $uriMerchant. '&merchantId=' . $res->MerchantId . ':' . BFCHelper::getSlug($res->MrcName) . "&fromsearch=1&lna=".$listNameAnalytics . "&checkin=" . $currCheckIn->format("YmdHis") . "&checkout=" . $currCheckOut->format("YmdHis");
//						$currRouteMerchant = JRoute::_($currUriMerchant);
						$currRouteMerchant = $url_merchant_page . $res->MerchantId .'-'.BFI()->seoUrl($res->MrcName)."&fromsearch=1&lna=".$listNameAnalytics . "&checkin=" . $currCheckIn->format("YmdHis") . "&checkout=" . $currCheckOut->format("YmdHis");;

					?>
						<a href="<?php echo $currRouteMerchant ?>" id="nameAnchor<?php echo $res->MerchantId?>" <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> class="eectrack" data-type="Merchant" data-id="<?php echo $merchant->MerchantId?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $merchantNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php echo  $res->MrcName ?></a> <br />
					<?php } ?>
					<a  class="bfi-resname eectrack" href="<?php echo $formRouteSingle ?>" <?php echo ($resId == $resourceId)? 'onclick="bfiGoToTop()"' : COM_BOOKINGFORCONNECTOR_TARGETURL; ?> data-type="Resource" data-id="<?php echo $res->ResourceId?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php echo $res->ResName; ?></a>

<div class="bfi-clearfix"></div>
<?php 
			if($resId != $resourceId && !empty($res->ImageUrl)){
				$resourceImageUrl = BFCHelper::getImageUrlResized('resources',$res->ImageUrl, 'small');
?>
<a  class="bfi-link-searchdetails" href="<?php echo $formRouteSingle ?>" <?php echo ($resId == $resourceId)? 'onclick="bfiGoToTop()"' : COM_BOOKINGFORCONNECTOR_TARGETURL; ?> ><img src="<?php echo $resourceImageUrl; ?>" class="bfi-img-searchdetails" /></a>
<div class="bfi-clearfix"></div>
								<?php
			}
/*-----------scelta date e ore--------------------*/	
									$currAvail = max(array_map(function($rp){ return $rp->Availability; }, $resRateplans));
									if (($res->AvailabilityType == 0 || $res->AvailabilityType == 1) && $currAvail < 2)
									{
										?>
									  <span class="bfi-availability-low"><?php echo sprintf(__('Only %d available' , 'bfi'),$currAvail) ?></span>
									<?php 
									}

									if ($res->AvailabilityType == 2)
									{
										
//										$currCheckIn = BFCHelper::parseJsonDateTime($res->RatePlan->CheckIn,'d/m/Y - H:i');
//										$currCheckOut =BFCHelper::parseJsonDateTime($res->RatePlan->CheckOut,'d/m/Y - H:i');

										$currDiff = $currCheckOut->diff($currCheckIn);

										//$loadScriptTimePeriod = true;
										

										//$timeDurationview = $currDiff->h + round(($currDiff->i/60), 2);
										$timeDuration = abs((new DateTime('UTC'))->setTimeStamp(0)->add($currDiff)->getTimeStamp() / 60); 

										
										$strDuration = "";
										if ($currDiff->d >= 1) {
//											$strDuration .= sprintf(__(' %d day/s' ,'bfi'), $currDiff->d);
											$strDuration .= sprintf(_n(' %d day', ' %d days', $currDiff->d, 'bfi'), $currDiff->d);
										}
										if ($currDiff->h > 0 || round(($currDiff->i / 60), 2) > 0) {
											$strDuration .= (!empty($strDuration) ? ", " : "") . ($currDiff->h + round(($currDiff->i / 60), 2)) . " " . __('hours', 'bfi');
										}

										//array_push($allTimePeriodResourceId, $res->ResourceId);
									?>
										<div class="bfi-timeperiod bfi-cursor" id="bfi-timeperiod-<?php echo $res->ResourceId ?>" 
											data-resid="<?php echo $res->ResourceId ?>" 
											data-checkin="<?php echo $currCheckIn->format('Ymd') ?>" 
											data-checkout="<?php echo $currCheckOut->format('Ymd') ?>" 
											data-checkintime="<?php echo $currCheckIn->format('YmdHis') ?>"
											data-timestart="<?php echo $currCheckIn->format('H:i') ?>"
											data-timeend="<?php echo $currCheckOut->format('H:i') ?>"
											data-timelength="<?php echo $res->TimeLength ?>"
											data-duration="<?php echo $timeDuration ?>"
										>
											<div class="bfi-row ">
												<div class="bfi-col-md-3 bfi-title"><?php 
														switch ($res->ItemTypeId) {
															case 3:
																 echo _e('Pick-up','bfi');
																break;
															default:      
																 echo _e('Check-in','bfi');	
														}
													?>
												</div>	
												<div class="bfi-col-md-9 bfi-time bfi-text-right"><span class="bfi-time-checkin"><?php echo date_i18n('D',$currCheckIn->getTimestamp()) ?> <?php echo $currCheckIn->format("d") ?> <?php echo date_i18n('M',$currCheckIn->getTimestamp()).' '.$currCheckIn->format("Y") ?></span> - <span class="bfi-time-checkin-hours"><?php echo $currCheckIn->format('H:i') ?></span>
												</div>	
											</div>	
											<div class="bfi-row ">
												<div class="bfi-col-md-3 bfi-title"><?php 
														switch ($res->ItemTypeId) {
															case 3:
																 echo _e('Drop-off','bfi');
																break;
															default:      
																 echo _e('Check-out','bfi');	
														}
													?>
												</div>	
												<div class="bfi-col-md-9 bfi-time bfi-text-right"><span class="bfi-time-checkout"><?php echo date_i18n('D',$currCheckOut->getTimestamp()) ?> <?php echo $currCheckOut->format("d") ?> <?php echo date_i18n('M',$currCheckOut->getTimestamp()).' '.$currCheckOut->format("Y") ?></span> - <span class="bfi-time-checkout-hours"><?php echo $currCheckOut->format('H:i') ?></span>
												</div>	
											</div>	
											<div class="bfi-row">
												<div class="bfi-col-md-3 "><?php _e('Total', 'bfi') ?>:
												</div>	
												<div class="bfi-col-md-9 bfi-text-right">
													<span class="bfi-total-duration" data-dayname='<?php echo _n(' %d day', ' %d days', $currDiff->d, 'bfi') ?>' data-hourname='<?php _e('hours', 'bfi') ?>'><?php echo $strDuration ?></span> 
												</div>	
											</div>	
										</div>
								<?php
									}
/*-------------------------------*/	
									if ($res->AvailabilityType == 3)
									{
										//$loadScriptTimeSlot = true;
										//$currDatesTimeSlot = array();
										
										if(!array_key_exists($resId, $allTimeSlotResourceId)){
											array_push($allTimeSlotResourceId, $res->ResourceId);
										}
										
										if(!array_key_exists($resId, $listDayTS)){
											$currDatesTimeSlot =  json_decode(BFCHelper::GetCheckInDatesTimeSlot($resId,$alternativeDateToSearch));
											$listDayTS[$resId] = $currDatesTimeSlot;
										}else{
											$currDatesTimeSlot =  $listDayTS[$resId];
										}
//										//riordinamento per data e minuti
										array_multisort(array_column($currDatesTimeSlot, 'StartDate'), SORT_ASC, array_column($currDatesTimeSlot, 'TimeSlotStart'), SORT_ASC, $currDatesTimeSlot);

										$currCheckIn = DateTime::createFromFormat('Ymd', $currDatesTimeSlot[0]->StartDate,new DateTimeZone('UTC'));
										$currCheckOut = clone $currCheckIn;
										$currCheckIn->setTime(0,0,0);
										$currCheckOut->setTime(0,0,0);
										$currCheckIn->add(new DateInterval('PT' . $currDatesTimeSlot[0]->TimeSlotStart . 'M'));
										$currCheckOut->add(new DateInterval('PT' . $currDatesTimeSlot[0]->TimeSlotEnd . 'M'));

										$currDiff = $currCheckOut->diff($currCheckIn);

										// overrides Availability by CheckInDatesTimeSlot
										$res->Availability = $currDatesTimeSlot[0]->Availability ;
										$strDuration = "";
										if ($currDiff->d >= 1) {
//											$strDuration .= sprintf(__(' %d day/s' ,'bfi'), $currDiff->d);
											$strDuration .= sprintf(_n(' %d day', ' %d days', $currDiff->d, 'bfi'), $currDiff->d);
										}
										if ($currDiff->h > 0 || round(($currDiff->i / 60), 2) > 0) {
											$strDuration .= (!empty($strDuration) ? ", " : "") . ($currDiff->h + round(($currDiff->i / 60), 2)) . " " . __('hours', 'bfi');
										}
									?>
										<div class="bfi-timeslot bfi-cursor" id="bfi-timeslot-<?php echo $res->ResourceId ?>" 
											data-resid="<?php echo $res->ResourceId ?>" 
											data-checkin="<?php echo $currCheckIn->format('Ymd') ?>" 
											data-checkout="<?php echo $currCheckOut->format('Ymd') ?>" 
											data-checkin-ext="<?php echo $currCheckIn->format('d/m/Y') ?>"
											data-timeslotid="<?php echo $currDatesTimeSlot[0]->ProductId ?>" 
											data-timeslotstart="<?php echo $currDatesTimeSlot[0]->TimeSlotStart ?>" 
											data-timeslotend="<?php echo $currDatesTimeSlot[0]->TimeSlotEnd ?>"
										>
											<div class="bfi-row ">
												<div class="bfi-col-md-3 bfi-title"><?php _e('Check-in', 'bfi') ?>
												</div>	
												<div class="bfi-col-md-9 bfi-time bfi-text-right"><span class="bfi-time-checkin"><?php echo date_i18n('D',$currCheckIn->getTimestamp()) ?> <?php echo $currCheckIn->format("d") ?> <?php echo date_i18n('M',$currCheckIn->getTimestamp()).' '.$currCheckIn->format("Y") ?></span> - <span class="bfi-time-checkin-hours"><?php echo $currCheckIn->format('H:i') ?></span>
												</div>	
											</div>	
											<div class="bfi-row ">
												<div class="bfi-col-md-3 bfi-title"><?php _e('Check-out', 'bfi') ?>
												</div>	
												<div class="bfi-col-md-9 bfi-time bfi-text-right"><span class="bfi-time-checkout"><?php echo date_i18n('D',$currCheckOut->getTimestamp()) ?> <?php echo $currCheckOut->format("d") ?> <?php echo date_i18n('M',$currCheckOut->getTimestamp()).' '.$currCheckOut->format("Y") ?></span> - <span class="bfi-time-checkout-hours"><?php echo $currCheckOut->format('H:i') ?></span>
												</div>	
											</div>	
											<div class="bfi-row">
												<div class="bfi-col-md-3 "><?php _e('Total', 'bfi') ?>:
												</div>	
												<div class="bfi-col-md-9 bfi-text-right"><span class="bfi-total-duration" data-dayname='<?php echo _n(' %d day', ' %d days', $currDiff->d, 'bfi') ?>' data-hourname='<?php _e('hours', 'bfi') ?>'><?php echo $strDuration ?></span>
												</div>	
											</div>	
										</div>
							<?php 
								if (!empty($currDatesTimeSlot) && count($currDatesTimeSlot)>0) {
																		
									$timeSlotList = array_values(array_filter($currDatesTimeSlot, function($itm) use ($currCheckIn) {
										return $itm->StartDate == $currCheckIn->format("Ymd");
									}));									
									if (!empty($timeSlotList) && count($timeSlotList)>0) {
										
										?>
											<div class="bfi-slot-list-title"><?php _e('Time Table', 'bfi')  ?>: <span><?php echo date_i18n('D',$currCheckIn->getTimestamp()) ?> <?php echo $currCheckIn->format("d") ?> <?php echo date_i18n('M',$currCheckIn->getTimestamp()).' '.$currCheckIn->format("Y") ?></span> </div>
											<ul class="bfi-slot-list" id="bfislotlist<?php echo $res->ResourceId ?>">
			
										<?php 			
										foreach ($timeSlotList as $singleSlot ) {
																						
											$currTime = clone $currCheckIn;
											$currTime->setTime(0,0,0);
											$currTime->modify('+' . $singleSlot->TimeSlotStart . ' minutes'); 

											?>
															<li><a href="javascript:void(0);" onclick="updateTotalTimeSlotList(this)" 
																data-resid="<?php echo $res->ResourceId ?>" 
																data-ProductAvailabilityPerSlotId="<?php echo $singleSlot->ProductAvailabilityPerSlotId ?>"  
																data-ProductId="<?php echo $singleSlot->ProductId ?>"  
																data-TimeSlotReferrerId="<?php echo $singleSlot->TimeSlotReferrerId ?>"  
																data-Availability="<?php echo $singleSlot->Availability ?>"
																data-checkin="<?php echo $currCheckIn->format('Y-m-d') ?>" 
																data-StartDate="<?php echo $singleSlot->StartDate ?>"  
																data-TimeSlotStart="<?php echo $singleSlot->TimeSlotStart ?>"  
																data-TimeSlotEnd="<?php echo $singleSlot->TimeSlotEnd ?>"  
															>
															<?php echo $currTime->format("H:i") ?>
															<?php if(!empty($singleSlot->PercentVariation)) { ?>
																<span class="bfi-slot-discount"><?php echo $singleSlot->PercentVariation ?>%</span>
															<?php } ?>
															</a></li>
											<?php 
															
										} // end foreach
										?>
											</ul>
									<?php 
									}	// end if timeSlotList
								} // end if resource->AvailableTimes		
							?>

							<!-- 
							<div class="bfi-slot-list-title"><?php _e('Time Table', 'bfi') ?> </div>
							<ul class="bfi-slot-list">
								<li><a href="&timeSlotId=1226"
											data-resid="<?php echo $res->ResourceId ?>" 
											data-sourceid="<?php echo $res->ResourceId ?>"
											data-checkin="<?php echo $currCheckIn->format('Ymd') ?>" 
											data-checkout="<?php echo $currCheckOut->format('Ymd') ?>" 
											data-checkintime="<?php echo $currCheckIn->format('YmdHis') ?>"
											data-timestart="<?php echo $currCheckIn->format('H:i') ?>"
											data-timeend="<?php echo $currCheckOut->format('H:i') ?>"
											data-timelength="<?php echo $res->TimeLength ?>"
								>08.00<span class="bfi-slot-discount">15%</span></a></li>
								<li><a href="&timeSlotId=1225" onclick="event.stopPropagation();" >10:30</a></li>
							</ul>
							-->

								<?php
									}								
/*-----------bed list--------------------*/
if(isset($res->BedsConfiguration) && !empty($res->BedsConfiguration)) { 
	$currBedsConfiguration = json_decode($res->BedsConfiguration);
	if (!isset($currBedsConfiguration->HasConfiguration) || (!empty($currBedsConfiguration->HasConfiguration) && strtolower($currBedsConfiguration->HasConfiguration) !='false')) {
	$showMainConfig = !empty($currBedsConfiguration->Main) && count($currBedsConfiguration->Main)>0 && is_array($currBedsConfiguration->Main );
	$showAlternateConfig = !empty($currBedsConfiguration->Alternate) && count($currBedsConfiguration->Alternate)>0 && is_array($currBedsConfiguration->Alternate );
	$selectBedConfiguration = $showMainConfig && $showAlternateConfig;

?>
<div class="bfi-bedsconfiguration <?php echo (($selectBedConfiguration) ?"bfi-bedsconfiguration-selectable":"") ?>">
<?php 

	/*-- main configuration --*/
	if ($showMainConfig) {
		if ($selectBedConfiguration) {
		    ?>
		    <span class="bfi-bedroom"> <?php _e('Choose your bed (if available)', 'bfi') ?></span>
			<div class="bfi-bedroom-selector">
				<div class="bfi-bedroom-select">
					<input type="radio" value="1" name="bfibedroom<?php echo $res->ResourceId ?>" rel="<?php echo $res->ResourceId ?>" data-config='<?php echo json_encode($currBedsConfiguration->Main  ) ?>' class="bfi-bedrooms-option" checked="checked">
				</div>
		    <?php 		    
		}
		?>
		<ul class="bfi-bedroomslist">
		<?php
		foreach($currBedsConfiguration->Main as $bedrooms) {
			 $roomType = isset($bedrooms->roomtype) ? $bedrooms->roomtype : 0;

			?>
			<li><span class="bfi-bedroom <?php echo (count($currBedsConfiguration->Main)>1) ?"":"bfi-hide";?>"> 
			<?php echo $roomtype_text[$roomType] ?> <?php echo  $bedrooms->index ?>: </span>
				<?php
				$currBeds = $bedrooms->beds;
				BFCHelper::osort($currBeds, 'type');
				foreach($currBeds as $beds) {?>
					<span class="bfi-comma"><?php echo $beds->quantity ?> <?php echo ($beds->quantity>1?$bedtypes_text[$beds->type]:$bedtype_text[$beds->type])  ?> <i class="bfi-bedtypes bfi-bedtypes<?php echo $beds->type ?>"></i></span>
				<?php }
			?>
			</li>
			<?php 
		}
		?>
		</ul>
		<?php 
		if ($selectBedConfiguration) { ?>
			</div>
		<?php 		    
		}
	}
	/*-- Alternate configuration --*/
	if ($showAlternateConfig) {
		if ($selectBedConfiguration) {
		    ?>
			<div class="bfi-bedroom-selector">
				<div class="bfi-bedroom-select">
					<input type="radio" value="2" name="bfibedroom<?php echo $res->ResourceId ?>" rel="<?php echo $res->ResourceId ?>" data-config='<?php echo json_encode($currBedsConfiguration->Alternate ) ?>' class="bfi-bedrooms-option">
				</div>
		    <?php 		    
		}
		?>
		<ul class="bfi-bedroomslist">
		<?php 
		foreach($currBedsConfiguration->Alternate  as $bedrooms) {
			?>
			<li><span class="bfi-bedroom <?php echo (count($currBedsConfiguration->Main)>1) ?"":"bfi-hide";?>"><?php _e('Room', 'bfi') ?> <?php echo  $bedrooms->index ?>: </span>
				<?php
				$currBeds = $bedrooms->beds;
				BFCHelper::osort($currBeds, 'type');
				foreach($currBeds as $beds) {?>
					 <span class="bfi-comma"><?php echo $beds->quantity ?> <?php echo ($beds->quantity>1?$bedtypes_text[$beds->type]:$bedtype_text[$beds->type])  ?> <i class="bfi-bedtypes bfi-bedtypes<?php echo $beds->type ?>"></i></span>
				<?php }
			?>
			</li>
			<?php 
		}
		?>
		</ul>
		<?php 
		if ($selectBedConfiguration) { ?>
			</div>
		<?php 		    
		}
	}
?>
</div>
<?php 
	}
}

/*-------------------------------*/									
					$listServices = array();
					if(!empty($res->ResServiceIdList)){
						$listServices = explode(",", $res->ResServiceIdList);
						$allServiceIds = array_merge($allServiceIds, $listServices);
						?>
						<div class="bfisimpleservices" rel="<?php echo $res->ResServiceIdList ?>"></div>
						<?php
					}
					if(!empty($res->TagsIdList)){
						?>
						<div class="bfiresourcegroups" rel="<?php echo $res->TagsIdList?>"></div>
						<?php
					}					

$currVat = $res->VATValue;				
$currTouristTaxValue = isset($res->TouristTaxValue)?$res->TouristTaxValue:0;				
?>
<br />
<?php if(!empty($currVat)) { ?>
	<div class="bfi-incuded"><strong><?php _e('Included', 'bfi') ?></strong> : <?php echo $currVat?> <?php _e('VAT', 'bfi') ?> </div>
<?php } ?>
<?php if(!empty($currTouristTaxValue)) { ?>
	<div class="bfi-notincuded"><strong><?php _e('Not included', 'bfi') ?></strong> : <span class="bfi_<?php echo $currencyclass ?>" ><?php echo BFCHelper::priceFormat($currTouristTaxValue) ?></span> <?php _e('City tax per person per night.', 'bfi') ?> </div>
<?php } ?>



				</td>
				<td colspan="4" style="padding:0;border:none;"></td>
			</tr>
<?php

//Calcolo
	$currRow = -1;
	foreach($resRateplans as $rpKey => $currRateplan) {
		$resRef += 1;
		$currRow += 1;
		
		$currSelectablePrices = json_decode($currRateplan->RatePlan->CalculablePricesString);
		$currSelectablePricesExtra = array_filter($currSelectablePrices, function($currSelectablePrice) {
			return $currSelectablePrice->Tag == "extrarequested";
		});
		$currSelectablePricesExtraIds= array_filter(array_map(function ($currSelectablePrice) { 
				if($currSelectablePrice->Tag == "extrarequested"){
					return $currSelectablePrice->PriceId; 
				}
			}, $currSelectablePricesExtra));
		
		$currCalculatedPrices = json_decode($currRateplan->RatePlan->CalculatedPricesString);
		$currCalculatedPricesExtra = array_filter($currCalculatedPrices, function($currCalculatedPrice) use ($currSelectablePricesExtraIds) {
			if(!in_array( $currCalculatedPrice->RelatedProductId,$currSelectablePricesExtraIds) && $currCalculatedPrice->Tag == "extrarequested"){
				return true;
			}
		});
			
		if(count($currSelectablePrices)>0){
			$formRouteBook = "showSelectablePrices"; 
		}
		$availability = array();
		$startAvailability = 0;
		$selectedtAvailability = 0;
		for ($i = $startAvailability; $i <= min($currRateplan->Availability, $currRateplan->MaxQt, COM_BOOKINGFORCONNECTOR_MAXQTSELECTABLE); $i++)
		{
			array_push($availability, $i);
		}

		$IsBookable = $currRateplan->IsBookable;
			$btnText = __('Request','bfi');
			$btnClass = "bfi-alternative";
			if ($IsBookable){
				$btnText = __('Book Now','bfi');
				$btnClass = "";
			}


		$SimpleDiscountIds = "";

		if(!empty($currRateplan->RatePlan->AllVariationsString)){
			$allVar = json_decode($currRateplan->RatePlan->AllVariationsString);
			$SimpleDiscountIds = implode(',',array_unique(array_map(function ($i) { return $i->VariationPlanId; }, $allVar)));
		}
//		$currCheckIn = BFCHelper::parseJsonDateTime($currRateplan->RatePlan->CheckIn,'d/m/Y\TH:i:s');
//		$currCheckOut =BFCHelper::parseJsonDateTime($currRateplan->RatePlan->CheckOut,'d/m/Y\TH:i:s');
		$currCheckIn = DateTime::createFromFormat('Y-m-d\TH:i:s',$currRateplan->RatePlan->CheckIn,new DateTimeZone('UTC'));
		$currCheckOut = DateTime::createFromFormat('Y-m-d\TH:i:s',$currRateplan->RatePlan->CheckOut,new DateTimeZone('UTC'));


if($currRateplan->AvailabilityType==0 || $currRateplan->AvailabilityType==1){
//	$currCheckIn = BFCHelper::parseJsonDateTime($currRateplan->RatePlan->CheckIn,'d/m/Y\TH:i:s');
//	$currCheckOut =BFCHelper::parseJsonDateTime($currRateplan->RatePlan->CheckOut,'d/m/Y\TH:i:s');
	$currCheckIn = DateTime::createFromFormat('Y-m-d\TH:i:s',$res->RatePlan->CheckIn,new DateTimeZone('UTC'));
	$currCheckOut = DateTime::createFromFormat('Y-m-d\TH:i:s',$res->RatePlan->CheckOut,new DateTimeZone('UTC'));
	$currCheckIn->setTime($mrcAcceptanceCheckInHours,$mrcAcceptanceCheckInMins,$mrcAcceptanceCheckInSecs);
	$currCheckOut->setTime($mrcAcceptanceCheckOutHours,$mrcAcceptanceCheckOutMins,$mrcAcceptanceCheckOutSecs);

}

	$policy = $currRateplan->RatePlan->Policy;
	$policyId= 0;
	$policyHelp = "";
	$policyHtml = "";
	if(!empty( $policy )){
		$currValue = $policy->CancellationBaseValue;
		$policyId= $policy->PolicyId;
		switch (true) {
			case strstr($policy->CancellationBaseValue ,'%'):
				$currValue = $policy->CancellationBaseValue;
				break;
			case strstr($policy->CancellationBaseValue ,'d'):
				$currValue = sprintf(__(' %d day/s' ,'bfi'),rtrim($policy->CancellationBaseValue,"d"));
				break;
			case strstr($policy->CancellationBaseValue ,'n'):
				$currValue = sprintf(__(' %d night/s' ,'bfi'),rtrim($policy->CancellationBaseValue,"n"));
				break;
			default:
				$currValue = '<span class="bfi_' . $currencyclass .'">'. BFCHelper::priceFormat($policy->CancellationBaseValue) .'</span>' ;
		}
		$currValuebefore = $policy->CancellationValue;
		switch (true) {
			case strstr($policy->CancellationValue, '%'):
				$currValuebefore = $policy->CancellationValue;
				break;
			case strstr($policy->CancellationValue, 'd'):
				$currValuebefore = sprintf(__(' %d day/s' ,'bfi'), rtrim($policy->CancellationValue, "d"));
				break;
			case strstr($policy->CancellationValue, 'n'):
				$currValuebefore = sprintf(__(' %d night/s' ,'bfi'), rtrim($policy->CancellationValue, "n"));
				break;
			default:
				$currValuebefore = '<span class="bfi_' . $currencyclass . '">' . BFCHelper::priceFormat($policy->CancellationValue) . '</span>' ;
		}
		if ($policy->CanBeCanceled) {
			$currTimeBefore = "";
			$currDateBefore = "";
			if (!empty($policy->CanBeCanceledCurrentTime )){
				if (!empty($policy->CancellationTime)) {
					//$currDatePolicyparsed = BFCHelper::parseJsonDate($res->RatePlan->CheckIn, 'Y-m-d\TH:i:s');
					$currDatePolicy = DateTime::createFromFormat('Y-m-d\TH:i:s', $res->RatePlan->CheckIn, new DateTimeZone('UTC'));
					switch (true) {
						case strstr($policy->CancellationTime, 'd'):
							$currTimeBefore = sprintf(__(' %d day/s', 'bfi'), rtrim($policy->CancellationTime, "d"));	
							$currDatePolicy->modify('-' . rtrim($policy->CancellationTime, "d") . ' days'); 
							break;
						case strstr($policy->CancellationTime, 'h'):
							$currTimeBefore = sprintf(__(' %d hour/s', 'bfi'), rtrim($policy->CancellationTime, "h"));
							$currDatePolicy->modify('-' . rtrim($policy->CancellationTime, "h") . ' hours'); 
							break;
						case strstr($policy->CancellationTime, 'w'):
							$currTimeBefore = sprintf(__(' %d week/s', 'bfi'),rtrim($policy->CancellationTime, "w"));
							$currDatePolicy->modify('-' . rtrim($policy->CancellationTime, "w") . ' weeks'); 
							break;
						case strstr($policy->CancellationTime, 'm'):
							$currTimeBefore = sprintf(__(' %d month/s', 'bfi'),rtrim($policy->CancellationTime, "m"));
							$currDatePolicy->modify('-' . rtrim($policy->CancellationTime, "m") . ' months'); 
							break;
						case strstr($policy->CancellationTime ,'n'):
							$currTimeBefore = sprintf(__(' %d night/s', 'bfi'),rtrim($policy->CancellationTime,"n"));
							$currDatePolicy->modify('-' . rtrim($policy->CancellationTime, "n") . ' nights'); 
							break;
					}
				}

				if ($policy->CancellationValue=="0" || $policy->CancellationValue=="0%") {
					$policyHtml = '<div class="bfi-policy-green">'. __('Cancellation FREE', 'bfi');
					if(!empty( $policy->CancellationTime )){
						$policyHtml .= '<br />'.__('until', 'bfi') . ' '.$currDatePolicy->format("d").' '.date_i18n('M',$currDatePolicy->getTimestamp()).' '.$currDatePolicy->format("Y");
						$policyHelp = sprintf(__('You may cancel free of charge until %1$s before arrival. You will be charged %2$s if you cancel in the %1$s before arrival.', 'bfi'),$currTimeBefore,$currValue);
					}
					$policyHtml .= '</div>';
				} else {
					if ($policy->CancellationBaseValue == "0%" || $policy->CancellationBaseValue == "0") {
						$policyHtml = '<div class="bfi-policy-green">' . __('Cancellation FREE', 'bfi') . '</div>';
						$policyHelp = __('You may cancel free of charge until arrival.', 'bfi');
					} else {
						$policyHtml = '<div class="bfi-policy-blue">' . __('Special conditions', 'bfi') . '</div>';
						$policyHelp = sprintf(__('You may cancel with a charge of %3$s  until %1$s before arrival. You will be charged %2$s if you cancel in the %1$s before arrival.', 'bfi'),$currTimeBefore,$currValue,$currValuebefore);
					}
				}
			} else {
				if ($policy->CancellationBaseValue=="0%" || $policy->CancellationBaseValue=="0") {
					$policyHtml = '<div class="bfi-policy-green">' . __('Cancellation FREE', 'bfi') . '</div>';
					$policyHelp = __('You may cancel free of charge until arrival.', 'bfi');
				} else {
					$policyHtml = '<div class="bfi-policy-blue">' . __('Special conditions', 'bfi') . '</div>';
					$policyHelp = sprintf(__('You will be charged %1$s if you cancel before arrival.', 'bfi'),$currValue);
				}
			}
			if ($policy->NoShowCancellationValue == "100%") {
				$policyHelp .= "&nbsp;" . __('In case of no-show, you will be charged all.', 'bfi');
			}
		} else { 
			// no refundable
			$policyHtml = '<div class="bfi-policy-none">' . __('Non refundable', 'bfi') . '</div>';
			$policyHelp = sprintf(__('You will be charged all if you cancel before arrival.', 'bfi'));
		
		}
	}

	$allMeals = array();
	$cssclassMeals = "bfi-meals-base";
	$mealsHelp = "";
	if ($currRateplan->ItemTypeId == 1) {
		$currRateplan->RatePlan->IncludedMeals = -1;
	}
	if ($currRateplan->RatePlan->IncludedMeals > -1) {
		$mealsHelp = __("There is no meal option with this room.", 'bfi');
		if ($currRateplan->RatePlan->IncludedMeals & bfi_Meal::Breakfast) {
			$allMeals[] = __("Breakfast", 'bfi');
		}
		if ($currRateplan->RatePlan->IncludedMeals & bfi_Meal::Lunch) {
			$allMeals[] = __("Lunch", 'bfi');
		}
		if ($currRateplan->RatePlan->IncludedMeals & bfi_Meal::Dinner) {
			$allMeals[] = __("Dinner", 'bfi');
		}
		if ($currRateplan->RatePlan->IncludedMeals & bfi_Meal::AllInclusive) {
			$allMeals[] = __("All Inclusive", 'bfi');
		}
		if (in_array(__("Breakfast", 'bfi'), $allMeals)) {
			$cssclassMeals = "bfi-meals-bb";
		}
		if (in_array(__("Lunch", 'bfi'), $allMeals) || in_array(__("Dinner", 'bfi'), $allMeals) || in_array(__("All Inclusive", 'bfi'), $allMeals)) {
			$cssclassMeals = "bfi-meals-fb";
		}
		if (count($allMeals) > 0) {
			$mealsHelp = implode(", ", $allMeals). " " . __('included', 'bfi');
		}
		if (count($allMeals) == 2) {
			$mealsHelp = implode(" & ", $allMeals). " " . __('included', 'bfi');
		}
	}

	$currMerchantBookingTypes = array();
	$prepayment = "";
	$prepaymentHelp = "";

//	if (!empty($currRateplan->RatePlan->MerchantBookingTypesString)) {
	if (!empty($currRateplan->RatePlan->MerchantBookingTypes)) {
		$currMerchantBookingTypes = json_decode($currRateplan->RatePlan->MerchantBookingTypesString);
		$currMerchantBookingTypes = $currRateplan->RatePlan->MerchantBookingTypes;
		$currBookingTypeId = $currRateplan->RatePlan->MerchantBookingTypeId;
		$currMerchantBookingType = array_filter($currMerchantBookingTypes, function($bt) use($currBookingTypeId) { return $bt->BookingTypeId == $currBookingTypeId; });
		$currMerchantBookingType = array_values($currMerchantBookingType);
		if (count($currMerchantBookingType) > 0){
			if ($currMerchantBookingType[0]->PayOnArrival) {
				$prepayment = __("Pay at the property – NO PREPAYMENT NEEDED", 'bfi');
				$prepaymentHelp = __("No prepayment is needed.", 'bfi');
			}
			if ($currMerchantBookingType[0]->AcquireCreditCardData && !empty($currMerchantBookingType[0]->DepositRelativeValue)) {
				$prepayment = "";
				if ($currMerchantBookingType[0]->DepositRelativeValue=="100%") {
					$prepaymentHelp = __('You will be charged a prepayment of the total price at any time.', 'bfi');
				} else if(strpos($currMerchantBookingType[0]->DepositRelativeValue, '%') !== false) {
					$prepaymentHelp = sprintf(__('You will be charged a prepayment of %1$s of the total price at any time.', 'bfi'), $currMerchantBookingType[0]->DepositRelativeValue);
				} else {
					$prepaymentHelp = sprintf(__('You will be charged a prepayment of %1$s at any time.', 'bfi'), $currMerchantBookingType[0]->DepositRelativeValue);
				}
			}
		}
	}

									$dateDuration = "";
									$dateDurationPrice = "";
									if ($res->AvailabilityType == 0 )
									{
										
										$currCheckInFull = clone $currCheckIn;
										$currCheckOutFull =clone $currCheckOut;
										$currCheckInFull->setTime(0,0,1);
										$currCheckOutFull->setTime(0,0,1);
										$currDiff = $currCheckOutFull->diff($currCheckInFull);
										
										$strDuration = "";
										if ($currDiff->d >= 1) {
											$strDuration .= sprintf(__(' %d day/s' ,'bfi'), $currDiff->d);
										}
										if ($currDiff->h > 0 || $currDiff->i % 60 > 0) {
											$strDuration .= (!empty($strDuration) ? ", " : "") . ($currDiff->h . ":" . $currDiff->i) . " " . __('hours', 'bfi');
										}
										$dateDuration = $strDuration . " " . __('days', 'bfi'). ", " . date_i18n('D',$currCheckIn->getTimestamp()) . " " . $currCheckIn->format("d") . " " . date_i18n('M',$currCheckIn->getTimestamp()).' '.$currCheckIn->format("Y");
										$dateDuration .= " - " .  date_i18n('D',$currCheckOut->getTimestamp()) . " " . $currCheckOut->format("d") . " " . date_i18n('M',$currCheckOut->getTimestamp()).' '.$currCheckOut->format("Y");
										$dateDurationPrice = $strDuration . " " . __('days', 'bfi') ;
									}

									if ($res->AvailabilityType == 1 )
									{
										$currCheckInFull = clone $currCheckIn;
										$currCheckOutFull =clone $currCheckOut;
										$currCheckInFull->setTime(0,0,1);
										$currCheckOutFull->setTime(0,0,1);

										$currDiff = $currCheckOutFull->diff($currCheckInFull);

										$dateDuration = $currDiff->d . " " . __('nights', 'bfi') .", " . date_i18n('D',$currCheckIn->getTimestamp()) . " " . $currCheckIn->format("d") . " " . date_i18n('M',$currCheckIn->getTimestamp()).' '.$currCheckIn->format("Y");
										$dateDuration .= " - " .  date_i18n('D',$currCheckOut->getTimestamp()) . " " . $currCheckOut->format("d") . " " . date_i18n('M',$currCheckOut->getTimestamp()).' '.$currCheckOut->format("Y");
										$dateDurationPrice = $currDiff->d  . " " . __('nights', 'bfi') ;
									}
									if ($res->AvailabilityType == 2)
									{
										
										$currDiff = $currCheckOut->diff($currCheckIn);
										$timeDuration = $currDiff->h + round(($currDiff->i/60), 2);
										
										$strDuration = "";
										if ($currDiff->d >0) {
											$strDuration .= sprintf(__(' %d day/s' ,'bfi'), $currDiff->d);
										}
										if ($currDiff->h > 0 || round(($currDiff->i / 60), 2) > 0) {
											$strDuration .= (!empty($strDuration) ? ", " : "") . ($currDiff->h + round(($currDiff->i / 60), 2)) . " " . __('hours', 'bfi');
										}

										$dateDuration = $strDuration .", " . date_i18n('D',$currCheckIn->getTimestamp()) . " " . $currCheckIn->format("d") . " " . date_i18n('M',$currCheckIn->getTimestamp()).' '.$currCheckIn->format("Y");
										$dateDuration .= " - " .  date_i18n('D',$currCheckOut->getTimestamp()) . " " . $currCheckOut->format("d") . " " . date_i18n('M',$currCheckOut->getTimestamp()).' '.$currCheckOut->format("Y");
										$dateDurationPrice = $strDuration;
									}
									if ($res->AvailabilityType == 3)
									{
										$currDiff = $currCheckOut->diff($currCheckIn);
										
										$dateDuration = $currDiff->format('%h') . " " . __('hours', 'bfi') .", " . date_i18n('D',$currCheckIn->getTimestamp()) . " " . $currCheckIn->format("d") . " " . date_i18n('M',$currCheckIn->getTimestamp()).' '.$currCheckIn->format("Y");
										$dateDuration .= " - " .  date_i18n('D',$currCheckOut->getTimestamp()) . " " . $currCheckOut->format("d") . " " . date_i18n('M',$currCheckOut->getTimestamp()).' '.$currCheckOut->format("Y");
										$dateDurationPrice = $currDiff->format('%h') . " " . __('hours', 'bfi') ;
									}
if ($currRow == $hideRateplanOver) {
?>
			<tr>
				<td colspan="4" class="bfishowoverrpl" data-resid="<?php echo $currRateplan->ResourceId ?>"> <?php _e('View other proposals', 'bfi') ?>
				</td>
			</tr>
    
<?php } ?>
			<tr id="data-id-<?php echo $currRateplan->ResourceId ?>-<?php echo $currRateplan->RatePlan->RatePlanId ?>-<?php echo $resRef ?>" class="<?php echo $IsBookable?"bfi-bookable":"bfi-canberequested"; ?>" style="display:<?php echo (($currRow >= $hideRateplanOver)?'none':'') ?>">
				<td style="<?php echo (COM_BOOKINGFORCONNECTOR_ISMOBILE) ?" border-bottom:1px solid #e0e0e0; ":"";?>"><!-- Min/Max -->
					<?php if(COM_BOOKINGFORCONNECTOR_ISMOBILE) { ?>
						 <div class="bfi-row ">
							<div class="bfi-col-xs-6">
					<?php } ?>
				<?php if ($currRateplan->MaxPaxes>0 || true){?>
					<?php 
					if(!empty( $currRateplan->RatePlan->SuggestedStay ) && !empty( $currRateplan->RatePlan->SuggestedStay->ComputedPaxes )){
						$computedPaxes = explode("|", $currRateplan->RatePlan->SuggestedStay->ComputedPaxes);
						$nadult =0;
						$nsenior =0;
						$nchild =0;
						
						foreach($computedPaxes as $computedPax) {
							$currComputedPax =  explode(":", $computedPax."::::");
							
							if ($currComputedPax[3] == "0") {
								$nadult += $currComputedPax[1];
							}
							if ($currComputedPax[3] == "1") {
								$nsenior += $currComputedPax[1];
							}
							if ($currComputedPax[3] == "2") {
								$nchild += $currComputedPax[1];
							}
						}
						$totPerson = $nadult+ $nsenior +$nchild;
						if(COM_BOOKINGFORCONNECTOR_ISMOBILE) {
							 echo __('Price for', 'bfi') . ': ';
						}
							if ($countPaxes == 1 && $totPerson==1 && $currRateplan->MaxPaxes>1) {
									?>
									<div class="bfi-icon-paxes">
										<i class="fa fa-user"></i></b>
										<?php for ($i=1; $i < ($currRateplan->MaxPaxes>4?4:$currRateplan->MaxPaxes) ;  $i++) {?>
											<i class="fa fa-user-o" style="font-size: 11px;"></i></b>
										<?php } ?>
									</div>
								<?php
							} else {
								if ($nadult>0) {
									?>
									<div class="bfi-icon-paxes">
										<i class="fa fa-user"></i> x <b><?php echo $nadult ?></b>
									<?php 
										if (($nsenior+$nchild)>0) {
											?>
											+ <br />
												<span class="bfi-redux"><i class="fa fa-user"></i></span> x <b><?php echo ($nsenior+$nchild) ?></b>
											<?php 
											
										}
									?>
									
									</div>
									<div class="webui-popover-content">
									   <div class="bfi-options-popover">
									   
									   <?php echo __('Price for', 'bfi') . $totPerson . __('person', 'bfi')?>
										</div>
									</div>
									
									<?php 
									
								}							    
							}
						



					}else{
					?>
						<?php if ($currRateplan->MaxPaxes>0){?>
						<div class="bfi-icon-paxes">
							<i class="fa fa-user"></i> 
							<?php if ($currRateplan->MaxPaxes==2 && $currRateplan->MinPaxes==2){?>
							<i class="fa fa-user"></i> 
							<?php }?>
							<?php if ($currRateplan->MaxPaxes>2){?>
								<?php echo ($currRateplan->MinPaxes != $currRateplan->MaxPaxes)? $currRateplan->MinPaxes . "-" : "" ?><?php echo  $currRateplan->MaxPaxes ?>
							<?php }?>
						</div>
						<?php }?>
					<?php } ?>
				<?php } ?>
					<?php if(COM_BOOKINGFORCONNECTOR_ISMOBILE) { ?>
							</div>
							<div class="bfi-col-xs-6 bfi-text-right">
								<span align="center">
									<div class="bfi-percent-discount" style="<?php echo ($currRateplan->PercentVariation < 0 ? "display:inline-block;float:none;margin-right: 0;padding: 2px 5px;font-size: 1em;" : "display:none"); ?>" rel="<?php echo $SimpleDiscountIds ?>" rel1="<?php echo $currRateplan->ResourceId ?>">
										<span class="bfi-percent"><?php echo $currRateplan->PercentVariation ?></span>% <i class="fa fa-question-circle bfi-cursor-helper" aria-hidden="true"></i>
									</div>
								</span>
							</div>
						</div>
					<?php }  ?>
				</td>

				<td style="<?php echo (COM_BOOKINGFORCONNECTOR_ISMOBILE) ?"":"text-align:center;"; ?>"><!-- price -->
					<?php if( $currRateplan->Price> 0) {?><!-- disponibile -->
					<?php if(!COM_BOOKINGFORCONNECTOR_ISMOBILE) { ?>
							 <div align="center">
								<div class="bfi-percent-discount" style="<?php echo ($currRateplan->PercentVariation < 0 ? " display:block" : "display:none"); ?>" rel="<?php echo $SimpleDiscountIds ?>" rel1="<?php echo $currRateplan->ResourceId ?>">
									<span class="bfi-percent"><?php echo $currRateplan->PercentVariation ?></span>% <i class="fa fa-question-circle bfi-cursor-helper" aria-hidden="true"></i>
								</div>
							</div>
							<div data-value="<?php echo $currRateplan->TotalPrice ?>" class="bfi-discounted-price bfi_<?php echo $currencyclass ?>" style="display:<?php echo ($currRateplan->Price < $currRateplan->TotalPrice)?"":"none"; ?>;"><?php echo BFCHelper::priceFormat($currRateplan->TotalPrice) ?></div>
							<div data-value="<?php echo $currRateplan->Price ?>" class="bfi-price  <?php echo ($currRateplan->Price < $currRateplan->TotalPrice ? "bfi-red" : "") ?> bfi_<?php echo $currencyclass ?>"><?php echo BFCHelper::priceFormat($currRateplan->Price) ?></div>
					<?php } else { ?>
						 <div class="bfi-row ">
							<div class="bfi-col-xs-9">
								<div><?php echo __('Price for', 'bfi') . " " . $dateDurationPrice; ?></div>
								<div data-value="<?php echo $currRateplan->TotalPrice ?>" class="bfi-discounted-price bfi_<?php echo $currencyclass ?>" style="<?php echo ($currRateplan->Price < $currRateplan->TotalPrice)?"display:inline-block;position:unset":"display:none"; ?>;"><?php echo BFCHelper::priceFormat($currRateplan->TotalPrice) ?></div>
								<div data-value="<?php echo $currRateplan->Price ?>" style="font-size: 2em;display:inline-block;position:unset" class="bfi-price  <?php echo ($currRateplan->Price < $currRateplan->TotalPrice ? "bfi-red" : "") ?> bfi_<?php echo $currencyclass ?>"><?php echo BFCHelper::priceFormat($currRateplan->Price) ?>
								</div>
									<div class="bfi-options-help bfimobile" style="top: 18px;display: inline-block;right: unset;padding-bottom: 5px;padding-left: 5px;">
										<i class="fa fa-question-circle bfi-cursor-helper" aria-hidden="true" data-placement="auto"></i>
										<div class="webui-popover-content">
										   <div class="bfi-options-popover">
										   <?php if(!empty($mealsHelp)) { ?>
											   <p><b><?php _e('Meals', 'bfi') ?>:</b> <?php echo $mealsHelp; ?></p>
										   <?php } ?>
										   <p><b><?php _e('Cancellation', 'bfi') ?>:</b> <?php echo $policyHelp; ?></p>
										   <?php if(!empty($prepaymentHelp)) { ?>
											   <p><b><?php _e('Prepayment', 'bfi') ?>:</b> <?php echo $prepaymentHelp; ?></p>
										   <?php } ?>
										   </div>
										</div>
									</div>
								<?php echo $policyHtml ?>
								<?php if(!empty($prepayment)) { ?>
									<div class="bfi-prepayment"><?php echo $prepayment ?></div>
								<?php } ?>
								<div class="bfi-meals <?php echo $cssclassMeals?>"><?php echo $mealsHelp ?></div>
								<?php if(!empty( $policy->OtherInfo)){ echo $policy->OtherInfo; }?>


							</div>
							<div class="bfi-col-xs-3">
								<div class="bfi-btn btn-number <?php echo $btnClass ?> bfi-btn-mobile bfi-btn-mobile-plus" data-type="plus" >
									<?php echo $btnText ?>
								</div>
								<div class="btn-number bfi-btn-mobile bfi-btn-mobile-minus" data-type="remove"  style="<?php echo (COM_BOOKINGFORCONNECTOR_ISMOBILE) ?"display:none;":""; ?>">
									<i class="fa fa-times-circle" aria-hidden="true"></i> <?php _e('Remove', 'bfi') ?>
								</div>
							</div>
						</div>
					<?php } ?>
					
					<?php }else{?>
						<strong><?php _e('No results available for the submitted data', 'bfi') ?></strong>
					<?php }?>
				</td>
				<?php if(!COM_BOOKINGFORCONNECTOR_ISMOBILE) { ?>
				<td><!-- options -->
					<div style="position:relative;">
							<?php echo $policyHtml ?>
							<?php if(!empty($prepayment)) { ?>
								<div class="bfi-prepayment"><?php echo $prepayment ?></div>
							<?php } ?>
							<div class="bfi-meals <?php echo $cssclassMeals?>"><?php echo $mealsHelp ?></div>
							<div class="bfi-options-help">
								<i class="fa fa-question-circle bfi-cursor-helper" aria-hidden="true" data-placement="right-bottom"></i>
								<div class="webui-popover-content">
								   <div class="bfi-options-popover">
								   <?php if(!empty($mealsHelp)) { ?>
									   <p><b><?php _e('Meals', 'bfi') ?>:</b> <?php echo $mealsHelp; ?></p>
								   <?php } ?>
								   <p><b><?php _e('Cancellation', 'bfi') ?>:</b> <?php echo $policyHelp; ?></p>
								   <?php if(!empty($prepaymentHelp)) { ?>
									   <p><b><?php _e('Prepayment', 'bfi') ?>:</b> <?php echo $prepaymentHelp; ?></p>
								   <?php } ?>
								   </div>
								</div>
							</div>
							<?php if(!$IsBookable) { ?>
								<div class="bfi-bookingenquiry"><?php _e('Non-binding booking request', 'bfi') ?></div>
							<?php }else{ ?>
								<div class="bfi-bookingreservationy"><?php _e('Instant Book', 'bfi') ?></div>
							<?php } ?>
						<?php if(!empty( $policy->OtherInfo)){ echo $policy->OtherInfo; }?>
					</div>
				</td>
						<?php } ?>
				<td class="bfiselectrooms" style="<?php echo (COM_BOOKINGFORCONNECTOR_ISMOBILE) ?"display:none !important;":""; ?>"><!-- select rooms -->									
<?php
				$currratePlanName =  BFCHelper::string_sanitize($currRateplan->RatePlan->Name);
				$currRealAvailProductId = $currRateplan->ResourceId;
				if (!empty($currRateplan->RealAvailProductId)) {
					$currRealAvailProductId = $currRateplan->RealAvailProductId;
				}
				$hidePeopleAge = 0;
				if (!empty($currRateplan->HidePeopleAge)) {
					$hidePeopleAge = 1;
				}
?>
                                        <div class="bfi-input-group bfi-mobile-input-group ">
                                            <span class="bfi-input-group-btn">

                                                <button type="button" class="btn btn-default bfi-btn btn-number bfi-hidden-md bfi-hidden-lg" data-type="minus" data-field="ddlrooms-@currRateplan.ResourceId-@currRateplan.RatePlan.RatePlanId-@resRef">
                                                    <i class="fa fa-minus" aria-hidden="true"></i>
                                                </button>
                                            </span>
					<select class="ddlrooms ddlrooms-<?php echo $currRateplan->ResourceId ?> ddlroomsrealav-<?php echo $currRealAvailProductId ?> ddlrooms-indipendent" 
					id="ddlrooms-<?php echo $currRateplan->ResourceId ?>-<?php echo $currRateplan->RatePlan->RatePlanId ?>-<?php echo $resRef ?>" 
					<?php echo (COM_BOOKINGFORCONNECTOR_ISMOBILE) ?"disabled":""; ?>
					onclick="bookingfor.checkMaxSelect(this);" 
					onchange="bookingfor.checkBookable(this);bfi_UpdateQuote(this);" 
					data-referenceid="<?php echo $currRateplan->RatePlan->RatePlanId ?>-<?php echo $resRef ?>" 
					data-realavailproductid="<?php echo $currRealAvailProductId?>" 
					data-resid="<?php echo $currRateplan->ResourceId ?>" 
					data-mrcid="<?php echo $currRateplan->MerchantId ?>" 
					data-name="<?php echo $resourceNameTrack ?>"
					data-lna="<?php echo $currLlistNameAnalytics ?>"
					data-brand="<?php echo $merchantNameTrack ?>"
					data-category="<?php echo $merchantCategoryNameTrack ?>"
					data-sourceid="<?php echo $currRateplan->ResourceId ?>"
					data-ratePlanId="<?php echo $currRateplan->RatePlan->RatePlanId ?>"
					data-ratePlanTypeId="<?php echo $currRateplan->RatePlan->RatePlanTypeId ?>"
					data-ratePlanName="<?php echo $currratePlanName ?>"
					data-policyId="<?php echo $policyId ?>"
					data-policy='<?php echo json_encode($policy) ?>'
					data-price="<?php echo BFCHelper::priceFormat($currRateplan->Price,2,".","") ?>" 
					data-totalprice="<?php echo BFCHelper::priceFormat($currRateplan->TotalPrice,2,".","") ?>" 
					data-baseprice="<?php echo $currRateplan->Price ?>" 
					data-basetotalprice="<?php echo $currRateplan->TotalPrice ?>"
					data-allvariations='<?php echo  str_replace("&", "e",  str_replace("'", "", $currRateplan->RatePlan->AllVariationsString)) ?>'
					data-percentvariation="<?php echo $currRateplan->RatePlan->PercentVariation ?>"
					data-availability="<?php echo $currRateplan->Availability ?>" 
					data-availabilitytype="<?php echo $currRateplan->AvailabilityType ?>"
					data-isbookable="<?php echo $IsBookable?"1":"0"; ?>" 
					data-checkin="<?php echo $currCheckIn->format('d/m/Y') ?>" 
					data-checkout="<?php echo $currCheckOut->format('d/m/Y') ?>"
					data-checkin-ext="<?php echo $currCheckIn->format('d/m/Y\TH:i:s') ?>" 
					data-checkout-ext="<?php echo $currCheckOut->format('d/m/Y\TH:i:s') ?>"
					data-includedmeals="<?php echo $currRateplan->RatePlan->IncludedMeals ?>" 
					data-touristtaxvalue="<?php echo $currRateplan->TouristTaxValue ?>" 
					data-vatvalue="<?php echo $currRateplan->VATValue ?>" 
					data-minpaxes="<?php echo $currRateplan->MinPaxes ?>" 
					data-maxpaxes="<?php echo $currRateplan->MaxPaxes ?>" 
					data-resetCart="<?php echo $resetCart ?>" 
					data-hidePeopleAge="<?php echo $hidePeopleAge ?>" 
					data-paxes="<?php echo (!empty( $currRateplan->RatePlan->SuggestedStay ) && !empty( $currRateplan->RatePlan->SuggestedStay->Paxes ))?$currRateplan->RatePlan->SuggestedStay->Paxes:":::::::" ?>" 
					data-computedpaxes="<?php echo (!empty( $currRateplan->RatePlan->SuggestedStay ) && !empty( $currRateplan->RatePlan->SuggestedStay->ComputedPaxes ))?$currRateplan->RatePlan->SuggestedStay->ComputedPaxes:":::::::" ?>" 
					data-bedconfig=""
					data-bedconfigindex=""
					data-timelength="<?php echo $currRateplan->TimeLength ?>"
					>
					<?php 
						foreach ($availability as $number) {
							?> <option value="<?php echo $number ?>" <?php echo ($selectedtAvailability== $number)?"selected":""; //selected( $selectedtAvailability, $number ); ?>><?php echo $number ?></option><?php
						}
					?>
					</select>
                                            <span class="bfi-input-group-btn">
                                                <button type="button" class="btn btn-default bfi-btn btn-number bfi-hidden-md bfi-hidden-lg" data-type="plus" data-field="ddlrooms-@currRateplan.ResourceId-@currRateplan.RatePlan.RatePlanId-@resRef">
                                                    <i class="fa fa-plus" aria-hidden="true"></i>
                                                </button>
                                            </span>
                                        </div>

<div class="bfi-mobile-view">
		<div class="bfi-mobile-view-totalselected"><span></span> <?php _e('selected items', 'bfi') ?>:
			<span class="bfi-mobile-view-totalpricediscounted bfi_<?php echo $currencyclass ?>"></span>
			<span class="bfi-mobile-view-totalprice bfi_<?php echo $currencyclass ?>" style="display:none;"></span>
		</div>
		<?php if(!empty($currVat)) { ?>
			<div class="bfi-incuded"><?php _e('Included', 'bfi') ?>: <?php echo $currVat?> <?php _e('VAT', 'bfi') ?> </div>
		<?php } ?>
		<?php if(!empty($currTouristTaxValue)) { ?>
			<div class="bfi-notincuded"><?php _e('Not included', 'bfi') ?>: <span class="bfi_<?php echo $currencyclass ?>" ><?php echo BFCHelper::priceFormat($currTouristTaxValue) ?></span> <?php _e('City tax per person per night.', 'bfi') ?> </div>
		<?php } ?>
		<div class="bfi-mobile-view-duration">(<?php echo $dateDuration ?>)</div>

		<div class="bfi-btn bfi-alternative bfi-mobile-step-next" data-sentocart="1">
			<?php _e('next step', 'bfi') ?> >
		</div>
</div>
<script type="text/javascript">
<!--
					pricesExtraIncluded["<?php echo $currRateplan->RatePlan->RatePlanId ?>-<?php echo $resRef ?>"] =<?php echo json_encode((object)$currCalculatedPricesExtra) ?> ;	
//-->
</script>
				</td>
			</tr>

<?php 
}
		$resCount++;
 } 
 ?>
			</tbody>
		</table>
		<!-- end bfi-table-resources -->

<!-- Service -->
<script>
    var servicesAvailability=[];
</script>



<?php if(count($allResourceId)>0){ ?>
    <div class="bfi-table-resources-step2 bfi-table-responsive" style="display:none;">

	<br /><?php  bfi_get_template("menu_small_booking.php");  ?>

<table class="bfi-table bfi-table-bordered bfi-table-resources bfi-table-selectableprice bfi-table-selectableprice-container bfi-table-resources-sticked" style="margin-top: 20px;">
			<thead>
				<tr>
					<th><?php _e('Do you want add more?', 'bfi') ?></th>
					<th><div><?php _e('Confirm your reservation', 'bfi') ?></div></th>
				</tr>
			</thead>
		<tr>
			<td class="bfi-nopad">
					

<?php 
	$countPrices = 0;
	$resRef =-1;
	foreach($allResourceId as $currResourceId) {
		$resRateplans =  array_filter($allRatePlans, function($p) use ($currResourceId) {return $p->ResourceId == $currResourceId ;}); // c#: allRatePlans.Where(p => p.ResourceId == resId);
		
		//usort($resRateplans, "BFCHelper::bfi_sortResourcesRatePlans");
		$res = array_values($resRateplans)[0];

	foreach($resRateplans as $currRateplan) {
		$resRef += 1;
		$selectablePrices = json_decode($currRateplan->RatePlan->CalculablePricesString);
		if (count($selectablePrices) == 0)
		{
			continue; //don't display table skip to next
		}
	
		$SimpleDiscountIds = "";

		if(!empty($currRateplan->RatePlan->AllVariationsString)){
			$allVar = json_decode($currRateplan->RatePlan->AllVariationsString);
			$SimpleDiscountIds = implode(',',array_unique(array_map(function ($i) { return $i->VariationPlanId; }, $allVar)));
		}
		$currCheckIn = DateTime::createFromFormat('Y-m-d\TH:i:s',$currRateplan->RatePlan->CheckIn,new DateTimeZone('UTC'));
		$currCheckOut = DateTime::createFromFormat('Y-m-d\TH:i:s',$currRateplan->RatePlan->CheckOut,new DateTimeZone('UTC'));

//		$currUriresource = $uri.$currRateplan->ResourceId . '-' . BFCHelper::getSlug($currRateplan->ResName) . "?fromsearch=1&lna=".$listNameAnalytics . "&checkin=" . $currCheckIn->format("YmdHis") . "&checkout=" . $currCheckOut->format("YmdHis");
		switch ($currRateplan->ItemTypeId ) {
			case bfi_ItemType::Experience :
				$currUriresource  = $url_resource_page_experience.$currRateplan->ResourceId.'-'.BFI()->seoUrl($currRateplan->ResName) . "?fromsearch=1&lna=".$listNameAnalytics . "&checkin=" . $currCheckIn->format("YmdHis") . "&checkout=" . $currCheckOut->format("YmdHis");
			break;
			default:      
				$currUriresource  = $uri.$currRateplan->ResourceId.'-'.BFI()->seoUrl($currRateplan->ResName) . "?fromsearch=1&lna=".$listNameAnalytics . "&checkin=" . $currCheckIn->format("YmdHis") . "&checkout=" . $currCheckOut->format("YmdHis");
			break;
		} // end switch

		$merchantNameTrack =  BFCHelper::string_sanitize($merchant->Name);
		$resourceNameTrack =  BFCHelper::string_sanitize($currRateplan->ResName);
				$merchantCategoryNameTrack =  BFCHelper::string_sanitize($merchant->MainCategoryName);
?>
					

		<div class="services-room-1-<?php echo $currRateplan->ResourceId ?>-<?php echo $currRateplan->RatePlan->RatePlanId ?>-<?php echo $resRef ?> bfi-table-responsive" style="display:none;">
		<div class="bfi-resname-extra"><a  class="bfi-resname eectrack" href="<?php echo $currUriresource ?>" <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> data-type="Resource" data-id="<?php echo $currRateplan->ResourceId?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php echo $currRateplan->ResName; ?></a></div>
		<div class="bfi-clearfix"></div>
		<?php  if(!empty($currRateplan->ImageUrl)){
			$resourceImageUrl = BFCHelper::getImageUrlResized('resources',$currRateplan->ImageUrl, 'medium');
		?>
		<a  class="bfi-link-searchdetails" href="<?php echo $currUriresource ?>" <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?>><img src="<?php echo $resourceImageUrl; ?>" class="bfi-img-searchdetails" /></a>
		<div class="bfi-clearfix"></div>
		<?php } ?>
		<!-- bfi-table-selectableprice -->
		<div class="bfi-mobile-view bfi-mobile-view-title" >
			<?php _e('Extra services', 'bfi'); ?>
		</div>
		<table class="bfi-table bfi-table-bordered bfi-table-resources bfi-table-selectableprice" style="margin-top: 20px;">
			<thead>
				<tr>
					<th><?php _e('Information', 'bfi') ?></th>
					<th><div><!-- <?php _e('For', 'bfi') ?> --></div></th>
					<th ><div><?php _e('Price', 'bfi') ?></div></th>
					<th><div><?php _e('Options', 'bfi') ?></div></th>
					<th><div><?php _e('Qt.', 'bfi') ?></div></th>
				</tr>
			</thead>
			<tbody>
<?php 
		foreach($selectablePrices as $selPrice) {

?>
				<tr class="data-sel-id-<?php echo $res->ResourceId ?>">
					<td >
					<div class="bfi-service-title"><?php echo $selPrice->Name; ?></div>
					<?php
/*-----------scelta date e ore--------------------*/	
									if (($selPrice->AvailabilityType == 0 || $selPrice->AvailabilityType == 1)) {
										
//										$currCheckIn = DateTime::createFromFormat('Y-m-d\TH:i:s',$selPrice->CheckIn,new DateTimeZone('UTC'));
// la data corretta è AvailabilityDate
										$currCheckIn = DateTime::createFromFormat('Y-m-d\TH:i:s',$selPrice->AvailabilityDate,new DateTimeZone('UTC'));
										$currCheckOut = DateTime::createFromFormat('Y-m-d\TH:i:s',$selPrice->CheckOut,new DateTimeZone('UTC'));
										$currDiff = $currCheckOut->diff($currCheckIn);

										$strDuration = "";
										if ($selPrice->AvailabilityType == 0) {
											$strDuration .= sprintf(__(' %d day/s' ,'bfi'), $currDiff->d + 1);
										} else {
											$strDuration .= sprintf(__(' %d night/s' ,'bfi'), $currDiff->d);
										}
										?>
										<div class="bfi-period <?php echo $selPrice->FullPeriodPrice ? "bfi-period-disabled" : "bfi-cursor"; ?> " id="bfi-period-<?php echo $selPrice->RelatedProductId ?>"
											style = "<?php echo $selPrice->FullPeriodPrice ? "display:none" : ""; ?>"
											data-resid="<?php echo $selPrice->RelatedProductId; ?>"
											data-checkin="<?php echo $currCheckIn->format('Ymd') ?>"
											data-checkout="<?php echo $currCheckOut->format('Ymd') ?>" 
											data-availabilitytype="<?php echo $selPrice->AvailabilityType; ?>"
											>
											<div class="bfi-row ">
												<div class="bfi-col-md-3 bfi-col-xs-3 bfi-title"><?php _e('Check-in', 'bfi') ?>
												</div>	
												<div class="bfi-col-md-9 bfi-col-xs-9 bfi-time bfi-text-right"><span class="bfi-period-checkin"><?php echo date_i18n('D',$currCheckIn->getTimestamp()) ?> <?php echo $currCheckIn->format("d") ?> <?php echo date_i18n('M',$currCheckIn->getTimestamp()).' '.$currCheckIn->format("Y") ?></span><?php if ($selPrice->AvailabilityType>1){?> - <span class="bfi-time-checkin-hours"><?php echo $currCheckIn->format('H:i') ?></span><?php } ?>
												</div>	
											</div>	
											<div class="bfi-row ">
												<div class="bfi-col-md-3 bfi-col-xs-3 bfi-title"><?php _e('Check-out', 'bfi') ?>
												</div>	
												<div class="bfi-col-md-9 bfi-col-xs-9 bfi-time bfi-text-right"><span class="bfi-period-checkout"><?php echo date_i18n('D',$currCheckOut->getTimestamp()) ?> <?php echo $currCheckOut->format("d") ?> <?php echo date_i18n('M',$currCheckOut->getTimestamp()).' '.$currCheckOut->format("Y") ?></span><?php if ($selPrice->AvailabilityType>1){?>  - <span class="bfi-time-checkout-hours"><?php echo $currCheckOut->format('H:i') ?></span><?php } ?>
												</div>	
											</div>	
											<div class="bfi-row">
												<div class="bfi-col-md-3 bfi-col-xs-3 "><?php _e('Total', 'bfi') ?>:
												</div>
												<div class="bfi-col-md-9 bfi-col-xs-9 bfi-text-right">
													<span class="bfi-total-duration" data-dayname='<?php echo $selPrice->AvailabilityType == 0 ? __(' %d day/s' ,'bfi') : __(' %d night/s' ,'bfi'); ?>' data-availabilitytype="<?php echo $selPrice->AvailabilityType; ?>"><?php echo $strDuration ?></span>
												</div>
											</div>
										</div>
										<?php
									}
									if ($selPrice->AvailabilityType == 2)
									{
										$currCheckIn = DateTime::createFromFormat('Y-m-d\TH:i:s',$selPrice->CheckIn,new DateTimeZone('UTC'));
										$currCheckOut = DateTime::createFromFormat('Y-m-d\TH:i:s',$selPrice->CheckOut,new DateTimeZone('UTC'));
										$currDiff = $currCheckOut->diff($currCheckIn);

   
										$loadScriptTimePeriod = true;
										
										$strDuration = "";
										if ($currDiff->d >= 1) {
											$strDuration .= sprintf(__(' %d day/s' ,'bfi'), $currDiff->d);
										}
										if ($currDiff->h > 0 || round(($currDiff->i / 60), 2) > 0) {
											$strDuration .= (!empty($strDuration) ? ", " : "") . ($currDiff->h + round(($currDiff->i / 60), 2)) . " " . __('hours', 'bfi');
										}
										
										
										//$timeDurationview = $currDiff->h + round(($currDiff->i/60), 2);
										$timeDuration = abs((new DateTime('UTC'))->setTimeStamp(0)->add($currDiff)->getTimeStamp() / 60); 										

										array_push($allTimePeriodResourceId, $selPrice->RelatedProductId );
//										$currCheckInString = date_i18n('D',$currCheckIn->getTimestamp()) ." " . $currCheckIn->format("d") ." " . date_i18n('M',$currCheckIn->getTimestamp()).' '.$currCheckIn->format("Y");
//										$currCheckOutString = date_i18n('D',$currCheckOut->getTimestamp()) ." " . $currCheckOut->format("d") ." " . date_i18n('M',$currCheckOut->getTimestamp()).' '.$currCheckOut->format("Y");
//										$currCheckInHour = $currCheckIn->format('H:i');
//										$currCheckOutHour = $currCheckOut->format('H:i');
//										$currDiffString = $currDiff->format('%h') ;

//$currCheckInString = __('Select a period', 'bfi');
//$currCheckOutString = "";
//$currCheckInHour = "";
//$currCheckOutHour = "";
//$currDiffString = "-";
//
									?>
										<div class="bfi-timeperiod <?php echo $selPrice->FullPeriodPrice ? "bfi-period-disabled" : "bfi-cursor"; ?>" id="bfi-timeperiod-<?php echo $selPrice->RelatedProductId ?>" 
											data-resid="<?php echo $selPrice->RelatedProductId ?>" 
											data-checkin="<?php echo $currCheckIn->format('Ymd') ?>"
											data-checkout="<?php echo $currCheckOut->format('Ymd') ?>" 
											data-checkintime="<?php echo $currCheckIn->format('YmdHis') ?>"
											data-timestart="<?php echo $currCheckIn->format('H:i') ?>"
											data-timeend="<?php echo $currCheckOut->format('H:i') ?>"
											data-timelength="<?php echo $selPrice->TimeLength ?>"
											data-duration="<?php echo $timeDuration ?>"
											>
											<div class="bfi-row ">
												<div class="bfi-col-md-3 bfi-col-xs-3 bfi-title"><?php _e('Check-in', 'bfi') ?>
												</div>	
												<div class="bfi-col-md-9 bfi-col-xs-9 bfi-time bfi-text-right"><span class="bfi-time-checkin"><?php echo date_i18n('D',$currCheckIn->getTimestamp()) ?> <?php echo $currCheckIn->format("d") ?> <?php echo date_i18n('M',$currCheckIn->getTimestamp()).' '.$currCheckIn->format("Y") ?></span> - <span class="bfi-time-checkin-hours"><?php echo $currCheckIn->format('H:i') ?></span>
												</div>	
											</div>	
											<div class="bfi-row ">
												<div class="bfi-col-md-3 bfi-col-xs-3 bfi-title"><?php _e('Check-out', 'bfi') ?>
												</div>	
												<div class="bfi-col-md-9 bfi-col-xs-9 bfi-time bfi-text-right"><span class="bfi-time-checkout"><?php echo date_i18n('D',$currCheckOut->getTimestamp()) ?> <?php echo $currCheckOut->format("d") ?> <?php echo date_i18n('M',$currCheckOut->getTimestamp()).' '.$currCheckOut->format("Y") ?></span> - <span class="bfi-time-checkout-hours"><?php echo $currCheckOut->format('H:i') ?></span>
												</div>	
											</div>	
											<div class="bfi-row">
												<div class="bfi-col-md-3 bfi-col-xs-3 "><?php _e('Total', 'bfi') ?>:
												</div>	
												<div class="bfi-col-md-9 bfi-col-xs-9 bfi-text-right">
													<span class="bfi-total-duration" data-dayname='<?php _e(' %d day/s' ,'bfi') ?>' data-hourname='<?php _e('hours', 'bfi') ?>'><?php echo $strDuration ?></span> 
												</div>	
											</div>	
										</div>
								<?php
									}
/*-------------------------------*/	
									if ($selPrice->AvailabilityType == 3)
									{
										$loadScriptTimeSlot = true;
										$currDatesTimeSlot = array();
										
										if(!array_key_exists($selPrice->RelatedProductId , $allTimeSlotResourceId)){
											array_push($allTimeSlotResourceId, $selPrice->RelatedProductId );
										}
										
										if(!array_key_exists($selPrice->RelatedProductId , $listDayTS)){
											$currDatesTimeSlot =  json_decode(BFCHelper::GetCheckInDatesTimeSlot($selPrice->RelatedProductId ,$alternativeDateToSearch));
											$listDayTS[$selPrice->RelatedProductId ] = $currDatesTimeSlot;
										}else{
											$currDatesTimeSlot =  $listDayTS[$selPrice->RelatedProductId ];
										}

										

//
//										array_push($allTimeSlotResourceId, $selPrice->RelatedProductId );
//										$currDatesTimeSlot =  json_decode(BFCHelper::GetCheckInDatesTimeSlot($selPrice->RelatedProductId ,$alternativeDateToSearch));

										$listDayTS[$selPrice->RelatedProductId] = $currDatesTimeSlot;

										$currCheckIn = DateTime::createFromFormat('Ymd', $currDatesTimeSlot[0]->StartDate,new DateTimeZone('UTC'));
										$currCheckOut = clone $currCheckIn;
										$currCheckIn->setTime(0,0,0);
										$currCheckOut->setTime(0,0,0);
										$currCheckIn->add(new DateInterval('PT' . $currDatesTimeSlot[0]->TimeSlotStart . 'M'));
										$currCheckOut->add(new DateInterval('PT' . $currDatesTimeSlot[0]->TimeSlotEnd . 'M'));

										$currDiff = $currCheckOut->diff($currCheckIn);

										// overrides Availability by CheckInDatesTimeSlot
										$res->Availability = $currDatesTimeSlot[0]->Availability ;

									?>
										<div class="bfi-timeslot bfi-cursor" data-sourceid="services-room-1-<?php echo $currRateplan->ResourceId ?>-<?php echo $currRateplan->RatePlan->RatePlanId ?>-<?php echo $resRef ?>" data-resid="<?php echo $selPrice->RelatedProductId?>" data-checkin="<?php echo $currCheckIn->format('Ymd') ?>"
										data-timeslotid="<?php echo $currDatesTimeSlot[0]->ProductId ?>" data-timeslotstart="<?php echo $currDatesTimeSlot[0]->TimeSlotStart ?>" data-timeslotend="<?php echo $currDatesTimeSlot[0]->TimeSlotEnd ?>"
										>
											<div class="bfi-row ">
												<div class="bfi-col-md-3 bfi-col-xs-3 bfi-title"><?php _e('Check-in', 'bfi') ?>
												</div>	
												<div class="bfi-col-md-9 bfi-col-xs-9 bfi-time bfi-text-right"><span class="bfi-time-checkin"><?php echo date_i18n('D',$currCheckIn->getTimestamp()) ?> <?php echo $currCheckIn->format("d") ?> <?php echo date_i18n('M',$currCheckIn->getTimestamp()).' '.$currCheckIn->format("Y") ?></span> - <span class="bfi-time-checkin-hours"><?php echo $currCheckIn->format('H:i') ?></span>
												</div>	
											</div>	
											<div class="bfi-row ">
												<div class="bfi-col-md-3 bfi-col-xs-3 bfi-title"><?php _e('Check-out', 'bfi') ?>
												</div>	
												<div class="bfi-col-md-9 bfi-col-xs-9 bfi-time bfi-text-right"><span class="bfi-time-checkout"><?php echo date_i18n('D',$currCheckOut->getTimestamp()) ?> <?php echo $currCheckOut->format("d") ?> <?php echo date_i18n('M',$currCheckOut->getTimestamp()).' '.$currCheckOut->format("Y") ?></span> - <span class="bfi-time-checkout-hours"><?php echo $currCheckOut->format('H:i') ?></span>
												</div>	
											</div>	
											<div class="bfi-row">
												<div class="bfi-col-md-3 bfi-col-xs-3 "><?php _e('Total', 'bfi') ?>:
												</div>	
												<div class="bfi-col-md-9 bfi-col-xs-9 bfi-text-right"><span class="bfi-total-duration"><?php echo ($currDiff->h + round(($currDiff->i / 60), 2)) ?></span> <?php _e('hours', 'bfi') ?>
												</div>	
											</div>	
										</div>
								<?php
									}								

/*-------------------------------*/									
							?>

					</td>
					<td>
					<?php if(!COM_BOOKINGFORCONNECTOR_ISMOBILE && (isset($selPrice->CalculationType) && !empty($selPrice->CalculationType)) && ($nad>0)) { ?>
						<!-- Min/Max -->
						<?php if (isset($selPrice->CalculationType) && !empty($selPrice->CalculationType)){?>
							<?php 
								if ($nad>0) {
									?>
									<div class="bfi-icon-paxes">
										<i class="fa fa-user"></i> x <b><?php echo $nad ?></b>
									<?php 
										if (($nse+$nch)>0) {
											?>
											+ <br />
												<span class="bfi-redux"><i class="fa fa-user"></i></span> x <b><?php echo ($nse+$nch) ?></b>
											<?php 
											
										}
									?>
									</div>
									<?php 
								}
							?>
						<?php } ?>
					<?php } ?>
					</td>
				<td style="<?php echo (COM_BOOKINGFORCONNECTOR_ISMOBILE) ?"":"text-align:center;"; ?>"><!-- price -->
						<?php
						$percentVariation = $selPrice->TotalAmount > 0 ? (int)((($selPrice->TotalDiscounted - $selPrice->TotalAmount) * 100) / $selPrice->TotalAmount) : 0;
						?>
					<?php if(COM_BOOKINGFORCONNECTOR_ISMOBILE) { ?>
						 <div class="bfi-row ">
							<div class="bfi-col-xs-9">
					<?php } ?>

						<div class="bfi-totalextrasselect" style="<?php echo ($selPrice->TotalDiscounted==0) ? "display:none;" : ""; ?>">
							<div align="center">
								<div class="bfi-percent-discount" style="<?php echo ($percentVariation < 0 ? " display:block" : "display:none"); ?>" rel="<?php echo $SimpleDiscountIds ?>" rel1="<?php echo $res->ResourceId ?>">
									<span class="bfi-percent"><?php echo $percentVariation ?></span>% <i class="fa fa-question-circle bfi-cursor-helper" aria-hidden="true"></i>
								</div>
							</div>
							<div data-value="<?php echo $selPrice->TotalAmount ?>" class="bfi-discounted-price bfi_<?php echo $currencyclass ?>" style="display:<?php echo ($selPrice->TotalDiscounted < $selPrice->TotalAmount)?"":"none"; ?>;"><?php echo BFCHelper::priceFormat($selPrice->TotalAmount) ?></div>
							<div data-value="<?php echo $selPrice->TotalDiscounted?>" class="bfi-price  <?php echo ($selPrice->TotalAmount < $selPrice->TotalDiscounted ? "bfi-red" : "") ?> bfi_<?php echo $currencyclass ?>"><?php echo BFCHelper::priceFormat($selPrice->TotalDiscounted) ?></div>
						</div>
					<?php if(COM_BOOKINGFORCONNECTOR_ISMOBILE) { ?>
							</div>
							<div class="bfi-col-xs-3">
								<div class="bfi-btn btn-number <?php echo $btnClass ?> bfi-btn-mobile bfi-btn-mobile-plus" data-type="plus" >
									<?php _e('Add', 'bfi') ?>
								</div>
								<div class="btn-number bfi-btn-mobile bfi-btn-mobile-minus" data-type="remove"  style="<?php echo (COM_BOOKINGFORCONNECTOR_ISMOBILE) ?"display:none;":""; ?>">
									<i class="fa fa-times-circle" aria-hidden="true"></i> <?php _e('Remove', 'bfi') ?>
								</div>
							</div>
					<?php } ?>

					</td>
					<?php if(!COM_BOOKINGFORCONNECTOR_ISMOBILE) { ?>
						<td><!-- options -->

						</td>
					<?php } ?>
					<td class="bfiselectrooms" style="<?php echo (COM_BOOKINGFORCONNECTOR_ISMOBILE) ?"display:none !important;":""; ?>">
<?php 
			$availability = array();
			$startAvailability = 0;
			$clickFunction = "bfi_quoteCalculatorServiceChanged(this)";
			$startAvailability = $selPrice->MinQt != null ? (int)$selPrice->MinQt : 0;
			$endAvailability = $selPrice->MaxQt != null ? min((int)$selPrice->MaxQt, COM_BOOKINGFORCONNECTOR_MAXQTSELECTABLE)  : COM_BOOKINGFORCONNECTOR_MAXQTSELECTABLE;
			for ($i = $startAvailability; $i <= $endAvailability; $i++)
			{
				array_push($availability, $i);
			}

				$extraNameTrack =  BFCHelper::string_sanitize($selPrice->Name);
				$currratePlanName =  BFCHelper::string_sanitize($currRateplan->RatePlan->Name);
?>

						<script>
							servicesAvailability[<?php echo $selPrice->PriceId ?>] =<?php echo (!empty($selPrice->Availability)? min($selPrice->Availability, COM_BOOKINGFORCONNECTOR_MAXQTSELECTABLE) : 0) ?> ;
						</script>
                                        <div class="bfi-input-group bfi-mobile-input-group ">
                                            <span class="bfi-input-group-btn">

                                                <button type="button" class="btn btn-default bfi-btn btn-number bfi-hidden-md bfi-hidden-lg" data-type="minus" data-field="ddlrooms-@currRateplan.ResourceId-@currRateplan.RatePlan.RatePlanId-@resRef">
                                                    <i class="fa fa-minus" aria-hidden="true"></i>
                                                </button>
                                            </span>
						<select class="ddlrooms ddlrooms-<?php echo $selPrice->RelatedProductId?> ddlroomsrealav-<?php echo $selPrice->RelatedProductId ?> ddlextras inputmini" 
							onchange="<?php echo $clickFunction ?>" 
							data-referenceid="<?php echo $currRateplan->RatePlan->RatePlanId ?>-<?php echo $resRef ?>" 
							data-maxvalue="<?php echo $selPrice->MaxQt ?>" 
							data-minvalue="<?php echo $selPrice->MinQt ?>" 
							data-priceid="<?php echo $selPrice->PriceId ?>"
							data-name="<?php echo $extraNameTrack ?>"
							data-lna="<?php echo $currLlistNameAnalytics ?>"
							data-brand="<?php echo $merchantNameTrack ?>"
							data-category="<?php echo $merchantCategoryNameTrack ?>"
							data-resourcename="<?php echo $resourceNameTrack ?>"
							data-resid="<?php echo $selPrice->RelatedProductId ?>"
							data-mrcid="<?php echo $currRateplan->MerchantId ?>" 
							data-realavailproductid="<?php echo $selPrice->RelatedProductId ?>" 
							data-sourceid="<?php echo $selPrice->RelatedProductId ?>"
							data-rateplanid="<?php echo $currRateplan->RatePlan->RatePlanId ?>" 
							data-rateplanname="<?php echo $currratePlanName?>" 
							data-availabilityType="<?php echo $selPrice->AvailabilityType ?>" 
							data-availabilityTypeRes="<?php echo $res->AvailabilityType ?>" 
							data-bindingproductid="<?php echo $res->ResourceId ?>"
							data-baseprice="<?php echo $selPrice->TotalDiscounted ?>" 
							data-basetotalprice="<?php echo $selPrice->TotalAmount ?>"
							data-price="<?php echo BFCHelper::priceFormat($selPrice->TotalDiscounted ,2,".","") ?>" 
							data-totalprice="<?php echo BFCHelper::priceFormat($selPrice->TotalAmount ,2,".","") ?>" 
							data-paxes="<?php echo (!empty( $currRateplan->RatePlan->SuggestedStay ) && !empty( $currRateplan->RatePlan->SuggestedStay->Paxes ))?$currRateplan->RatePlan->SuggestedStay->Paxes:":::::::" ?>" 
							data-computedpaxes="<?php echo (!empty( $currRateplan->RatePlan->SuggestedStay ) && !empty( $currRateplan->RatePlan->SuggestedStay->ComputedPaxes ))?$currRateplan->RatePlan->SuggestedStay->ComputedPaxes:":::::::" ?>" 
							>
							<?php 
								foreach ($availability as $number) {
									?> <option value="<?php echo $number ?>" <?php echo ($selPrice->CalculatedQt == $number)?"selected":""; //selected( $selectedtAvailability, $number ); ?>><?php echo $number ?></option><?php
								}
							?>
						</select>
                                            <span class="bfi-input-group-btn">
                                                <button type="button" class="btn btn-default bfi-btn btn-number bfi-hidden-md bfi-hidden-lg" data-type="plus" data-field="ddlrooms-@currRateplan.ResourceId-@currRateplan.RatePlan.RatePlanId-@resRef">
                                                    <i class="fa fa-plus" aria-hidden="true"></i>
                                                </button>
                                            </span>
                                        </div>					
					
					</td>
				</tr>
<?php 
$countPrices+=1;
		}//end foreach selPrices
?>

			</tbody>
		</table>
		<!-- end bfi-table-selectableprice -->
<div class="bfi-mobile-view">
		<div class="bfi-btn bfi-alternative bfi-mobile-step2-next" data-sentocart="1">
			<?php _e('next step', 'bfi') ?> >
		</div>
</div>
		</div>
<?php 
	}//end foreach bfi-table-resources-step2 resRateplans

	}//end foreach bfi-table-resources-step2 allResourceId
?>
			</td>
			<td class="bfi-book-now-td">
				<div class="totalextrasstay bfi-book-now" >
					<div class="bfi-resource-total"><span></span> <?php _e('selected items', 'bfi') ?></div>
					<div class="bfi-extras-total"><span></span> <?php _e('selected services', 'bfi') ?></div> 
					<div class="bfi-discounted-price bfi-discounted-price-total bfi_<?php echo $currencyclass ?>" style="display:none;"></div>
					<div class="bfi-price bfi-price-total bfi_<?php echo $currencyclass ?>" ></div>
					<div class="bfi-btn bfi-btn-book-now" onclick="bookingfor.BookNow(this);">
						<?php _e('Book Now', 'bfi') ?>
					</div>
					<div class="bfi-btn bfi-alternative bfi-request-now" onclick="bookingfor.BookNow(this);">
						<?php _e('Request Now', 'bfi') ?>
					</div>
				</div>
			</td>
		</tr>
</table>
    </div>
<?php 
 } //end if(!empty div-selectableprice
?>
</div>
<form action="<?php echo esc_url($formOrderRouteBook) ?>" class="frm-order" method="post"></form>
	
<script type="text/javascript">
var localeSetting = "<?php echo substr($language,0,2); ?>";
var productAvailabilityType = <?php echo $ProductAvailabilityType?>;
var allStays = <?php echo json_encode($allRatePlans) ?>; 
var availabilityValues = {};
var availabilityTimePeriodCheckOut = {};
var currCheckin, currCheckout,
	currCheckinTime,currCheckoutTime,
	currResourceid=<?php echo !empty($resourceId)? $resourceId : 0; ?>;
	
	
var calculator_checkin = null;
var calculator_checkout = null;
var bficalculatordpmode='';


jQuery(function($) {
	currCheckin = jQuery('#<?php echo $checkinId; ?>');
	currCheckout = jQuery('#<?php echo $checkoutId; ?>');
	currCheckinTime = jQuery('#checkintimedetailsselect');
	currCheckoutTime = jQuery('#checkouttimedetailsselect');
});

	function bfiOpen<?php echo $checkoutId; ?>() {
		setTimeout(function() {
			jQuery("#<?php echo $checkoutId; ?>").datepicker("show");
		}, 1);
		
	}
	jQuery(function($) {
		
		bfi_UpdateQuote(jQuery(".bfi-table-resourcessearchdetails"));
		var helpPlacement = 'left-bottom';
		if (parseInt("<?php echo COM_BOOKINGFORCONNECTOR_MONTHINCALENDAR;?>") >1)
		{
			helpPlacement = 'right-bottom';
		}
		jQuery('.bfi-options-help i').webuiPopover({trigger:'hover',placement:helpPlacement,style:'bfi-webuipopover'});

		jQuery('.bfi-bedroom-select input[type=radio]').change(function() {
			var currBedsSel = jQuery(this);
			jQuery(this).closest(".bfi-result-list").find(".ddlrooms-" + currBedsSel.attr('rel') + " ").each(function (index, ddlroom) {
				 jQuery(this).attr('data-bedconfig',currBedsSel.attr('data-config'));
				 jQuery(this).attr('data-bedconfigindex',currBedsSel.val());
			});
		});
	});
	
	var bfi_inizialized = false;
	function bfi_inizializeDialog() {
		if (!bfi_inizialized)
		{
			calculator_checkin = function() { 
			
				currCheckin.datepicker({
					numberOfMonths: bfi_variables.bfi_numberOfMonths,
					defaultDate: "+0d",
					dateFormat: "dd/mm/yy", 
					minDate: 0,
					//minDate: '<?php echo $startDate->format('d/m/Y') ?>', 
					//maxDate: '<?php echo $endDate->format('d/m/Y') ?>', 
					onSelect: function(date) { 
						jQuery('input[name=timeSlotId]').val('');						
						jQuery(".ui-datepicker a").removeAttr("href"); 
						bfi_checkDateBooking(date); 
						currCheckout.datepicker('option', 'minDate', date);
						if( productAvailabilityType==3){
							calculateQuote();
						}else{
							if(productAvailabilityType ==2){
								bfi_timeStepCheckin(currCheckout,"#checkintimedetailsselect",daysToEnableTimePeriod[currResourceid + ""],<?php echo $timeLength ?>);
							}
							dateCalculatorChanged(true,bfiOpen<?php echo $checkoutId; ?>);
						var currTmpForm = jQuery(this).closest("form");
						bfi_printChangedDate(currTmpForm);

						}
					}, 
					beforeShowDay: function (date) {
						var currTmpForm = jQuery(this).closest("form");
						return bfi_closedBooking(date, 0, daysToEnable[currResourceid + ""],currTmpForm,currResourceid); 
					}, 
					beforeShow: function(dateText, inst) {
						var currTmpForm = jQuery(this).closest("form");
						bfidpmode='checkin';
						bficalculatordpmode='checkin';
						jQuery('#ui-datepicker-div').addClass('notranslate');  
						jQuery(inst.dpDiv).addClass('bfi-calendar bfi-datepicker_notop');
						jQuery(this).attr("readonly", true); 
						setTimeout(function() { bfi_updateTitle(currTmpForm,"bfi-checkin","bfi-checkout","Checkin")	}, 1);
					}, 
					onChangeMonthYear: function(dateText, inst) { 
						var currTmpForm = jQuery(this).closest("form");
						setTimeout(function() { bfi_updateTitle(currTmpForm,"bfi-checkin","bfi-checkout","Checkin")	}, 1);
					}
				});
			};
			
			calculator_checkout = function() { 
				currCheckout.datepicker({
					numberOfMonths: bfi_variables.bfi_numberOfMonths,
					defaultDate: "+0d",
					dateFormat: "dd/mm/yy", 
					minDate: 0,
					//minDate: '<?php echo $startDate2->format('d/m/Y') ?>', 
					//maxDate: '<?php echo $endDate2->format('d/m/Y') ?>', 
					onClose: function(dateText, inst) { 
						jQuery(this).attr("disabled", false); 
						bfi_printChangedDate(jQuery(this).closest("form")); 
					}, 
					onSelect: function(date) {  
						jQuery(".ui-datepicker a").removeAttr("href"); 
						if(productAvailabilityType ==2){
							bfi_timeStepCheckin('#<?php echo $checkoutId; ?>',"#checkouttimedetailsselect",(availabilityTimePeriodCheckOut[currResourceid + ""]||[]),<?php echo $timeLength ?>, null,"1");
						}
						bfi_printChangedDate(jQuery(this).closest("form")); 

					}, 
					beforeShowDay: function (date) {
						var currTmpForm = jQuery(this).closest("form");
						return bfi_closedBooking(date, 0, checkOutDaysToEnable[currResourceid + ""],currTmpForm,currResourceid); 
					}, 
					beforeShow: function(dateText, inst) {
						var currTmpForm = jQuery(this).closest("form");
						bficalculatordpmode='checkout';
						jQuery('#ui-datepicker-div').addClass('notranslate');  
						jQuery(inst.dpDiv).addClass('bfi-calendar bfi-datepicker_notop');
						jQuery(this).attr("readonly", true); 
						setTimeout(function() {	bfi_updateTitle(currTmpForm,"bfi-checkout","bfi-checkin","Checkout"); }, 1);
					}, 
					onChangeMonthYear: function(dateText, inst) {
						var currTmpForm = jQuery(this).closest("form");
						setTimeout(function() {	bfi_updateTitle(currTmpForm,"bfi-checkout","bfi-checkin","Checkout"); }, 1);
					}
				});
			};

			calculator_checkin();
			calculator_checkout();
			if(productAvailabilityType ==2){
				var fcheckinTime;
				if (jQuery("#bfi-calculatorForm").attr('data-checkinTime') ) {
					fcheckinTime = jQuery("#bfi-calculatorForm").attr('data-checkinTime') ;
				}
				if (jQuery("#bfi-calculatorForm").attr('data-checkoutTime') ) {
					fcheckoutTime = jQuery("#bfi-calculatorForm").attr('data-checkoutTime') ;
				}
				jQuery(document).on('change', "#checkintimedetailsselect" ,  function(e) {
					dateCalculatorChanged(true,bfiOpen<?php echo $checkoutId; ?>);
				});
			}
			

			//fix Google Translator and datepicker
			jQuery('.ui-datepicker').addClass('notranslate');

			jQuery(".bfi_resource-calculatorForm-childrenages").hide();
			jQuery(".bfi_resource-calculatorForm-childrenages select").hide();
			checkChildren(<?php echo $nch ?>,<?php echo $showChildrenagesmsg ?>);
			jQuery(".bfi_resource-calculatorForm-children select#childrencalculator").change(function() {
				checkChildren(jQuery(this).val(),0);
			});

			/** hightligth selected --***/
			currCheckout.datepicker('widget').on('mouseover', 'tr td', function () {
				if(bficalculatordpmode!='checkout' || !jQuery("#<?php echo $checkinId; ?>").datepicker( "getDate" )){
					return
				}//this is hard code for start date
				var calendarId = jQuery(this).closest('.ui-datepicker').attr('id')
				// clear up highlight-day class
				jQuery('#' + calendarId + ' .ui-datepicker-calendar tr td.date-selected').removeClass('date-selected');
				jQuery('#' + calendarId + ' .ui-datepicker-calendar tr td.date-end-selected').removeClass('date-end-selected');
				jQuery('#' + calendarId + ' .ui-datepicker-calendar tr td.highlight-day').each(function(index, item){
					jQuery(item).removeClass('highlight-day');
				})

				// loop& add highligh-day class until reach $(this)
				var tds = jQuery('#' + calendarId + ' .ui-datepicker-calendar tr td')
				for(var index = 0; index < tds.size(); ++index) {
					var item = tds[index]
					jQuery(item).addClass('highlight-day')

					if(jQuery(item)[0].outerHTML === jQuery(this)[0].outerHTML) {
						break
					}
				}
			});

					bfi_checkDateBooking(currCheckin.datepicker({ dateFormat: "dd/mm/yy" }).val()); 
					<?php if(!empty($resourceId)) { ?>
					//						// -----------------------------------   Calendario 3 mesi disponibilità risorsa
//						bfi_datepicker_availability = function() { 
//							jQuery("#bfi-datepicker-availability").datepicker({
//								numberOfMonths: 3,
//								beforeShowDay: function (date) {
//									return availabilityBooking(date, 0, daysToEnable[unitId + ""]); 
//								},
//								beforeShow: function(dateText, inst) {
//									jQuery('#ui-datepicker-div').addClass('notranslate');  
//									jQuery(inst.dpDiv).addClass('bfi-calendar');
//									jQuery(this).attr("readonly", true); 
//								}
//							})
//						};
//						getCheckinAjaxDate(bfi_datepicker_availability);
						getCheckinAjaxDate(function() {
							jQuery('#calcheckin').unblock();
							bfi_getAjaxDateCheckout(currResourceid, currCheckin, currCheckout, productAvailabilityType,currCheckinTime);
							if(productAvailabilityType ==2){
								bfi_timeStepCheckin(currCheckout,"#checkintimedetailsselect",daysToEnableTimePeriod[currResourceid + ""],<?php echo $timeLength ?>);
							}

						});	

					<?php }else{ ?>

					var currForm = jQuery("#bfi-calculatorForm");
						var fcheckinTime;
						var fcheckoutTime;
						if (jQuery("#bfi-calculatorForm").attr('data-checkinTime') ) {
							fcheckinTime = jQuery("#bfi-calculatorForm").attr('data-checkinTime') ;
						}
						if (jQuery("#bfi-calculatorForm").attr('data-checkoutTime') ) {
							fcheckoutTime = jQuery("#bfi-calculatorForm").attr('data-checkoutTime') ;
						}
						bfi_timeStepCheckin(currCheckin, "#checkintimedetailsselect", [] , <?php echo $timeLength ?>, fcheckinTime);
						bfi_timeStepCheckin(currCheckout, "#checkouttimedetailsselect", [], <?php echo $timeLength ?>, fcheckoutTime, true);

						if (typeof bfi_printChangedDate !== "undefined" ){bfi_printChangedDate(currForm) ;}
					<?php } ?>
			
			
			bfi_inizialized = true;
		}

	}
	
	function checkChildren(nch,showMsg) {
		jQuery(".bfi_resource-calculatorForm-childrenages").hide();
		jQuery(".bfi_resource-calculatorForm-childrenages select").hide();
		if (nch > 0) {
			jQuery(".bfi_resource-calculatorForm-childrenages select").each(function(i) {
				if (i < nch) {
					var id=jQuery(this).attr('id');
					jQuery(this).css('display', 'inline-block');
				}
			});
			jQuery(".bfi_resource-calculatorForm-childrenages").show();
			if(showMsg==1) { 
				setTimeout(showpopovercalculator(), 1);
			}
		}
	}
	
function getDisplayDate(date) {
	return date == null ? "" : bookingfor.pad(date.getDate(),2) + '/' + bookingfor.pad((date.getMonth() + 1),2) + '/' + date.getFullYear();
}

function bfi_enableSpecificDates(date, offset, enableDays) {
	var month = date.getMonth() + 1;
	var day = date.getDate();
	var year = date.getFullYear();
	var copyarray = jQuery.extend(true, [], enableDays);
	for (var i = 0; i < offset; i++)
		copyarray.pop();
	var datereformat = year + '' + bookingfor.pad(month,2) + '' + bookingfor.pad(day,2);
	if (jQuery.inArray(Number(datereformat), copyarray) != -1) {
		return [true, 'greenDay'];
	}
	return [false, 'redDay'];
}

function bfi_onEnsureCheckOutDaysToEnableSuccess(currcheckOutDaysToEnable, currProdId, currCheckout, currCheckoutContainer, currForm) {
	if (!currcheckOutDaysToEnable || currcheckOutDaysToEnable.length == 0) {
		jQuery(currCheckoutContainer).unblock();
		return;
	}
	if (currcheckOutDaysToEnable[0] == 0)
	{
		jQuery(currCheckoutContainer).unblock();
		return;
	}
    var strDate = '' + currcheckOutDaysToEnable[0];
	var date = new Date(strDate.substr(0,4),strDate.substr(4,2)-1,strDate.substr(6,2));

	var datetocheck = currCheckout.datepicker("getDate");
	if (!bfi_enableSpecificDates(datetocheck, 0, currcheckOutDaysToEnable)[0]) {
		currCheckout.val(getDisplayDate(date));
	}

    if (typeof currForm !== "undefined" && typeof bfi_printChangedDate !== "undefined") {
        bfi_printChangedDate(currForm);
    }

	jQuery(currCheckoutContainer).unblock();

}


function bfi_checkDateBooking(selectedDate) {
	instance = currCheckin.data("datepicker");
	date = jQuery.datepicker.parseDate(
			instance.settings.dateFormat ||
			jQuery.datepicker._defaults.dateFormat,
			selectedDate, instance.settings);

	var offsetDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());

<?php if(!empty($variationPlanMinDuration)) { ?>
	offsetDate.setDate(offsetDate.getDate() + <?php echo $variationPlanMinDuration ?>);
<?php } ?>

	switch (productAvailabilityType) {
		case 0:
			if (currCheckout.datepicker("getDate") < date) {
				currCheckout.datepicker("setDate", Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
			}
			break;
		case 1:
			break;
		case2:
		<?php if(!empty($resourceId)) { ?>
			offsetDate.setDate(offsetDate.getDate() + 1);
		<?php } ?>
			currCheckout.datepicker("option", "minDate", offsetDate);
			if (currCheckout.datepicker("getDate") <= date) {
				currCheckout.datepicker("setDate", Date.UTC(offsetDate.getFullYear(), offsetDate.getMonth(), offsetDate.getDate()));
			}
			break;
		case 3:
			offsetDate.setDate(offsetDate.getDate() + 1);
			currCheckout.datepicker("option", "minDate", offsetDate);
			currCheckout.datepicker("option", "maxDate", offsetDate);
			currCheckout.datepicker("setDate", Date.UTC(offsetDate.getFullYear(), offsetDate.getMonth(), offsetDate.getDate()));
			break;
	}

<?php if(!empty($variationPlanMaxDuration)) { ?>
	var offsetEndDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
	offsetEndDate.setDate(offsetEndDate.getDate() + <?php echo $variationPlanMaxDuration //night ?>);
	currCheckout.datepicker("option", "maxDate", offsetEndDate);
<?php } ?>

}
var dialogForm;
var bfi_wuiP_width= 800;
function bfishowsearch() {
	if(jQuery(window).width()<bfi_wuiP_width){
		bfi_wuiP_width = jQuery(window).width();
	}
	if (!!jQuery.uniform){
		jQuery.uniform.restore(jQuery("#bfi-calculatorForm select"));
	}
	bfi_inizializeDialog();
	dialogForm = jQuery( "#bfi-calculatorForm" ).dialog({
			closeText: "",
			title:"<?php _e('Change your details', 'bfi'); ?>",
			autoOpen: false,
			width:bfi_wuiP_width,
			modal: true,
			dialogClass: 'bfi-dialog',
			clickOutside: true,

	});
	dialogForm.dialog( "open" );
}


	function availabilityBooking(date, offset, enableDays) {
		var dayEnabled = false;
		var month = date.getMonth() + 1;
		var day = date.getDate();
		var year = date.getFullYear();
		var copyarray = jQuery.extend(true, [], enableDays);
		for (var i = 0; i < offset; i++)
			copyarray.pop();
		var datereformat = year + '' + bookingfor.pad(month,2) + '' + bookingfor.pad(day,2);
		if (jQuery.inArray(Number(datereformat), copyarray) != -1) {
			dayEnabled = true;
		}

		var holydayTitle = "";
		var holydayCss = "";
		
		var currDay =  ("0" + date.getDate()).slice(-2) + "" + ("0" + (date.getMonth()+1)).slice(-2);
		var currIdxHoliday = jQuery.inArray(currDay, bookingfor.holydays);
		if (currIdxHoliday != -1) {
			holydayTitle = bookingfor.holydaysTitle[currIdxHoliday];
			holydayCss = "bfi-date-holidays ";
		}
		currDay =  ("0" + date.getDate()).slice(-2) + "" + ("0" + (date.getMonth()+1)).slice(-2) + date.getFullYear();
		currIdxHoliday = jQuery.inArray(currDay, bookingfor.holydays);
		if (currIdxHoliday != -1) {
			holydayTitle = bookingfor.holydaysTitle[currIdxHoliday];
			holydayCss = "bfi-date-holidays ";
		}
		if (dayEnabled)
		{
			holydayCss += " bfi-date-green ";
		}

		arr = [false, holydayCss, holydayTitle];  
	  return arr;
	}
	


function quoteCalculatorChanged(callback) {
	jQuery('#bfi_lblchildrenagescalculator').webuiPopover('destroy');
	jQuery('#resourceQuote').hide();
	jQuery('#resourceSummary').hide();
	jQuery('.bfiotherfacilities').hide();
	jQuery('input[name="refreshcalc"]').val("1");
	if (countMinAdults()>0)
	{
		jQuery('.bfi-result-list').hide();
	}else{
	}
}
function dateCalculatorChanged(getDate,callback) {
<?php if(!empty($resourceId)) { ?>
	if (getDate)
	{
		getCheckOutAjaxDate(function () {
			bfi_timeStepCheckin(currCheckout, "#checkouttimedetailsselect", availabilityTimePeriodCheckOut[currResourceid + ""],<?php echo $timeLength ?>, jQuery("#bfi-calculatorForm").attr('data-checkoutTime'), true);
		});
	}
<?php }else{ ?>
	if (callback) {
		callback();
	}
<?php }?>
	jQuery('#resourceQuote').hide();
	jQuery('#resourceSummary').hide();
	jQuery('input[name="refreshcalc"]').val("1");
	if (countMinAdults()>0)
	{
		jQuery('.bfi-result-list').hide();
	}else{
	}
}

function countMinAdults(){
	var minAdults = 0;
	var numAdults = new Number(jQuery('#adultscalculator').val() || 0);
	var numSeniores = new Number(jQuery('#seniorescalculator').val() || 0);
	var numChildren = new Number(jQuery("#childrencalculator").val() || 0);
	
	jQuery('#searchformpersonsadult-calculator').val(numAdults);
	jQuery('#searchformpersonssenior-calculator').val(numSeniores);
	jQuery('#searchformpersonschild-calculator').val(numChildren);
	
	jQuery(".bfi_resource-calculatorForm-childrenages select").each(function(i) {
		jQuery('#searchformpersonschild'+(i+1)+'-calculator').val(jQuery(this).val());
	});

	jQuery('#searchformpersons-calculator').val(numAdults + numChildren + numSeniores);
	
	
	minAdults = numAdults + numSeniores;
	return minAdults;
}


function calculateQuote(whatsearch) {
	
	if (xhralternativeMrc && xhralternativeMrc.readyState != 4) {
		xhralternativeMrc.abort();
	}
	if (xhralternativeRsc && xhralternativeRsc.readyState != 4) {
		xhralternativeRsc.abort();
	}
	jQuery('#bfi_lblchildrenagescalculator').webuiPopover('destroy');
	jQuery('#showmsgchildagecalculator').val(0);
	var numChildren = new Number(jQuery(".bfi_resource-calculatorForm-children select#childrencalculator").val());
	checkChildren(numChildren,0);
	jQuery(".bfi_resource-calculatorForm-childrenages select:visible option:selected").each(function(i) {
		if(jQuery(this).text()==""){
			jQuery('#showmsgchildagecalculator').val(1);
			return;
		}
	});

	jQuery('input[name="state"]','#bfi-calculatorForm').val('');
	jQuery('input[name="extras[]"]','#bfi-calculatorForm').val('');
	jQuery('.bfi-percent-discount').webuiPopover('destroy');
	if (typeof dialogForm !=='undefined' && dialogForm.hasClass("ui-dialog-content"))
	{
		dialogForm.dialog( "close" ).dialog('destroy');
	}

<?php 
if(!empty($variationPlanId)){
?>
	var currDateFormat = "dd/mm/yy";
	currCheckin.datepicker( "option", "dateFormat",currDateFormat );
	currCheckout.datepicker( "option", "dateFormat", currDateFormat );
	jQuery('#bfi-calculatorForm').submit();
<?php 
}else{
?>
	if (whatsearch !== undefined &&  whatsearch == 'refreshsearch')
	{
		var currDateFormat = "dd/mm/yy";
		currCheckin.datepicker( "option", "dateFormat",currDateFormat );
		currCheckout.datepicker( "option", "dateFormat", currDateFormat );
		jQuery('#calculator').block({
				message: '<i class="fa fa-spinner fa-spin fa-3x fa-fw margin-bottom"></i><span class="sr-only">Loading...</span>',
				css: {border: 'none'},
				overlayCSS: {backgroundColor: '#ffffff', opacity: 0.7}  
			});
			var curMerchantID = jQuery("input[name=merchantId]").val();
			jQuery("input[name=merchantId]").val("");
			jQuery("input[name=groupresulttype]").val("2");
			jQuery("input[name=filter_order]").val("merchantid:" + curMerchantID);
        jQuery('#bfi-calculatorForm').attr('method', "get").attr('action', "<?php echo $alternativeformRoute ?>").submit();
	}else{
		jQuery('#bfi-calculatorForm').ajaxSubmit(bfiGetAjaxOptions(whatsearch));
	}

<?php 
}
?>

}

function showpopovercalculator() {
		jQuery('#bfi_lblchildrenagescalculator').webuiPopover({
			content : jQuery("#bfi_childrenagesmsgcalculator").html(),
			container: document.body,
			cache: false,
			placement:"top-right",
			maxWidth: "300px",
			type:'html',
			style:'bfi-webuipopover'
		});
		jQuery('#bfi_lblchildrenagescalculator').webuiPopover("show");
}
jQuery(window).resize(function(){
	jQuery('#bfi_lblchildrenagescalculator').webuiPopover('destroy');
});

function getCheckinAjaxDate(callback) {
	// prepare Options Object 
	jQuery('#calcheckin').block({message: ''});
    var task = "listDateCheckin";
    if (productAvailabilityType == 2) {
        task = "listCheckInDateHours";
    }
	var options = { 
		url: bookingfor.getActionUrl(null, null, task, 'resourceId=' + currResourceid + '&simple=1'),
	    dataType: 'json',
		cache: false,
		success: function(data) { 
            if (productAvailabilityType == 2) {
                daysToEnable[currResourceid + ""] = [];
                daysToEnableTimePeriod[currResourceid + ""] = {};
                jQuery.each(data, function (i, dt) {
                    daysToEnable[currResourceid + ""].push(dt.StartDate);
                    daysToEnableTimePeriod[currResourceid + ""][dt.StartDate + ""] = JSON.parse(dt.TimeRangesString);
                });
            } else {
                daysToEnable[currResourceid + ""] = data;
            }
			if (callback) {
				callback();
			}
	    }, 
		error: function (xhr, ajaxOptions, thrownError) {
			jQuery('#calcheckin').unblock();
	    } 
	}; 
	jQuery.ajax(options);
}

function getCheckOutAjaxDate(callback) {
	var datereformat = jQuery.datepicker.formatDate("yymmdd", jQuery('#<?php echo $checkinId ?>').datepicker("getDate"));
    jQuery('#calcheckout').block({ message: '' });
    var task = "GetCheckOutDatesDetailed";
    if (productAvailabilityType == 2) {
        task = "GetCheckOutDatesPerTimes";
        datereformat += jQuery('#checkintimedetailsselect').val().replace(":", "") + "00";
    }
    var options = {
        url: bookingfor.getActionUrl(null, null, task, 'resourceId=' + currResourceid + '&checkin=' + datereformat + '&simple=1'),
        dataType: 'json',
		cache: false,
        success: function (data) {
            checkOutDaysToEnable[currResourceid + ""] = [];
            availabilityValues[currResourceid + ""] = {};
            switch (productAvailabilityType) {
                case 0:
                case 1:
                    jQuery.each(data, function (j, av) {
                        checkOutDaysToEnable[currResourceid + ""].push(parseInt(av.PeriodDate));
                        availabilityValues[currResourceid + ""][av.PeriodDate] = av.Availability;
                    });
                    break;
                case 2:
                    availabilityTimePeriodCheckOut[currResourceid + ""] = {};
                    jQuery.each(data, function (i, dt) {
                        checkOutDaysToEnable[currResourceid + ""].push(dt.StartDate);
                        availabilityTimePeriodCheckOut[currResourceid + ""][dt.StartDate + ""] = JSON.parse(dt.TimeRangesString);
                        jQuery.each(JSON.parse(dt.AvailabilitiesString), function (j, av) {
                            availabilityValues[currResourceid + ""][av.Time] = av.Availability;
                        });
                    });
                    break;
                case 3:
                    checkOutDaysToEnable[currResourceid + ""] = data;
                    break;
            }

            if (callback) {
                callback();
            }
			bfi_onEnsureCheckOutDaysToEnableSuccess(checkOutDaysToEnable[currResourceid + ""], currResourceid, currCheckout, jQuery("#calcheckout"),jQuery("#bfi-calculatorForm"));
	    },
		error: function (xhr, ajaxOptions, thrownError) {
			checkoutContainer.unblock();
		}
		
	}; 
	jQuery.ajax(options);
}


function bfiGetAjaxOptions(whatsearch) {
	var formUrl = '<?php echo $formRoute?>';
	var msgwait = '<i class="fa fa-spinner fa-spin fa-3x fa-fw margin-bottom"></i><span class="sr-only">Loading...</span>'
	formUrl += ((formUrl.indexOf('?') > -1)? "&" :"?") + 'format=calc&tmpl=component'
	if (whatsearch !== undefined &&  whatsearch == 'all')
	{
		formUrl += "&allmerchants=1";
		msgwait +="<br />Stiamo cercando presso altre strutture";
	}
	var currDateFormat = "dd/mm/yy";
	currCheckin.datepicker( "option", "dateFormat",currDateFormat );
	currCheckout.datepicker( "option", "dateFormat", currDateFormat );

	var options = { 
	    target:     '#calculator',
		type: 'POST',
	    replaceTarget: true, 
	    url:        formUrl, 
	    beforeSend: function() {
	    	jQuery('#calculator').block({
					message: '<i class="fa fa-spinner fa-spin fa-3x fa-fw margin-bottom"></i><span class="sr-only">Loading...</span>',
					message: msgwait,
					css: {border: 'none'},
					overlayCSS: {backgroundColor: '#ffffff', opacity: 0.7}  
				});
		},
	    success: function() { 
			jQuery('#calculator').unblock();
				var timeSlotId = jQuery('input[name=timeSlotId]').val();
				if (timeSlotId!='' && timeSlotId!='0')
				{
					var currTSSelected  = jQuery(".bfi-slot-list a[data-productid="+timeSlotId+"]");
					if(currTSSelected.length>0){
						updateTotalTimeSlotList(currTSSelected);
					}
				}
	    } 
	}; 
	return options;
}

    var totalOrderPriceLoaded = false;
    var totalOrderPrice = 0;
    var totalOrderPriceWhitOutDiscount = 0;

<?php if(COM_BOOKINGFORCONNECTOR_GAENABLED == 1 && !empty(COM_BOOKINGFORCONNECTOR_GAACCOUNT) && COM_BOOKINGFORCONNECTOR_EECENABLED == 1 ){ ?>
jQuery(function($) {
	<?php if(isset($eecmainstay)){ ?>
	if (typeof callAnalyticsEEc !== "undefined") {
		callAnalyticsEEc("addProduct", [<?php echo json_encode($eecmainstay); ?>], "item");
	}
	<?php } ?>
	<?php if(count($eecstays) > 0 && $currentState != 'optionalPackages'){ ?>
//	callAnalyticsEEc("addImpression", <?php echo json_encode($eecstays); ?>, "list", "Suggested Products");
	<?php } ?>
});
<?php } ?>
<?php if(isset($criteoConfig) && !empty($criteoConfig) && $criteoConfig->enabled): ?>
window.criteo_q = window.criteo_q || []; 
window.criteo_q.push( 
	{ event: "setAccount", account: <?php echo $criteoConfig->campaignid ?>}, 
	{ event: "setSiteType", type: "d" }, 
	{ event: "viewSearch", checkin_date: "<?php echo $checkin->format('d/m/Y') ?>", checkout_date: "<?php echo $checkout->format('d/m/Y') ?>"},
	{ event: "setEmail", email: "" }, 
	{ event: "viewItem", item: "<?php// echo $criteoConfig->merchants[0] ?>" }
);
<?php endif; ?>

var strAlternativeDateToSearch = "<?php echo $alternativeDateToSearch->format('d/m/Y') ?>";
var strEndDate = "<?php echo $checkout->format('d/m/Y') ?>";
var dateToUpdate = <?php echo $checkin->format('Ymd') ?>;
jQuery(document).ready(function() {
	if(jQuery(".ui-dialog").length){
		jQuery(".ui-dialog").remove();
	}
});
var txtSelectADay = "<?php _e('Please, select a day', 'bfi') ?>";
var daysToEnableTimeSlot = {};
var currTimeSlotDisp = {};

var bfi_currTRselected = null;
var daysToEnableTimePeriod = {};
var checkOutDaysToEnableTimePeriod = {};


jQuery(document).ready(function() {
	jQuery("#bfi-calculatorForm .checking-container .ui-datepicker-trigger").click(function() {
        jQuery(".ui-datepicker-calendar td").click(function() {
            if (jQuery(this).hasClass('ui-state-disabled') == false) {
                if(jQuery("#bfi-calculatorForm .lastdatecheckout button.ui-datepicker-trigger").is(":visible")){
					jQuery("#bfi-calculatorForm .lastdate button.ui-datepicker-trigger").trigger("click");
				}
                jQuery("#bfi-calculatorForm .ui-datepicker-trigger").each(function() {
                    jQuery(this).addClass("activeclass");
                });
                jQuery("#bfi-calculatorForm .checking-container .ui-datepicker-trigger").removeClass("activeclass");
                jQuery(".ui-icon-circle-triangle-w").addClass("fa fa-angle-left").removeClass("ui-icon ui-icon-circle-triangle-w").html("");
                jQuery(".ui-icon-circle-triangle-e").addClass("fa fa-angle-right").removeClass("ui-icon ui-icon-circle-triangle-e").html("");
//                jQuery("#ui-datepicker-div").css("top", jQuery(this).position().top + 35 + "px");
            }
        });
    });
    jQuery("#bfi-calculatorForm .ui-datepicker-trigger").click(function() {
//        jQuery("#ui-datepicker-div").css("top", jQuery(this).position().top + 35 + "px");
        jQuery("#bfi-calculatorForm .ui-datepicker-trigger").each(function() {
            jQuery(this).removeClass("activeclass");
        });
        jQuery(this).addClass("activeclass");
        jQuery(".ui-icon-circle-triangle-w").addClass("fa fa-angle-left").removeClass("ui-icon ui-icon-circle-triangle-w").html("");
        jQuery(".ui-icon-circle-triangle-e").addClass("fa fa-angle-right").removeClass("ui-icon ui-icon-circle-triangle-e").html("");

    });
    jQuery("#ui-datepicker-div").click(function() {
        jQuery(".ui-icon-circle-triangle-w").addClass("fa fa-angle-left").removeClass("ui-icon ui-icon-circle-triangle-w").html("");
        jQuery(".ui-icon-circle-triangle-e").addClass("fa fa-angle-right").removeClass("ui-icon ui-icon-circle-triangle-e").html("");
    });
    jQuery("#bfi-calculatorForm").hover(function(){
        jQuery(".ui-datepicker-trigger").click(function() {
//            jQuery("#ui-datepicker-div").css("top", jQuery(this).position().top + 35 + "px");
            jQuery(".ui-datepicker-trigger").each(function() {
                jQuery(this).removeClass("activeclass");
            });
            jQuery(this).addClass("activeclass");
            jQuery(".ui-icon-circle-triangle-w").addClass("fa fa-angle-left").removeClass("ui-icon ui-icon-circle-triangle-w").html("");
            jQuery(".ui-icon-circle-triangle-e").addClass("fa fa-angle-right").removeClass("ui-icon ui-icon-circle-triangle-e").html("");

        });
    });


	initDatepickerPeriod();
	jQuery("#bfi-period-select").attr("data-resid",0);
	jQuery("#bfimodalperiod").dialog({
				title: "<?php _e('Change your details', 'bfi') ?>",
				autoOpen: false,
				modal: true,
				width: 'auto',
				maxWidth: "300px",
		dialogClass: 'bfi-dialog bfi-dialog-period',
				close: function() {
				}
			});
			bfi_currTRselected = null;
    jQuery(".bfi-result-list").on("click", ".bfi-period:not(.bfi-period-disabled)", function (e) {
        var currResId = jQuery("#bfi-period-select").attr("data-resid");
        var elm = jQuery(this).hasClass("bfi-period") ? jQuery(this) : jQuery(this).closest(".bfi-period");
        var newResId = jQuery(elm).attr("data-resid");
        if (bfi_currTRselected != jQuery(this).closest("tr")) {
            bfi_currTRselected = jQuery(this).closest("tr");
            jQuery("#bfi-period-select").attr("data-resid", newResId);
            jQuery("#bfi-period-select").attr("data-checkin", jQuery(elm).attr("data-checkin"));
            jQuery("#bfi-period-select").attr("data-checkout", jQuery(elm).attr("data-checkout"));
            jQuery("#bfimodalperiodcheckin").attr("data-resid", newResId);
            jQuery("#bfimodalperiodcheckout").attr("data-resid", newResId);
					
					function loadPicker() {
                jQuery("#bfimodalperiodcheckin").datepicker("setDate", jQuery.datepicker.parseDate("yymmdd", jQuery(elm).attr("data-checkin")));
                jQuery("#bfimodalperiodcheckin").datepicker("option", "minDate", jQuery.datepicker.parseDate("yymmdd","<?php echo $checkin->format('yMd') ?>"));
                jQuery("#bfimodalperiodcheckin").datepicker("option", "maxDate", jQuery.datepicker.parseDate("yymmdd","<?php echo $checkout->format('yMd') ?>"));
                dateTimeChanged(elm, function () {
                    jQuery("#bfimodalperiodcheckout").datepicker("setDate", jQuery.datepicker.parseDate("yymmdd", jQuery(elm).attr("data-checkout")));
                    jQuery("#bfimodalperiodcheckout").datepicker("option", "minDate", jQuery.datepicker.parseDate("yymmdd","<?php echo $checkin->format('yMd') ?>"));
                    jQuery("#bfimodalperiodcheckout").datepicker("option", "maxDate", jQuery.datepicker.parseDate("yymmdd","<?php echo $checkout->format('yMd') ?>"));
                });
            }

            if (!daysToEnable[newResId]) {
						jQuery.ajax({ 
					url: bookingfor.getActionUrl(null, null, "lisDates", 'resourceId=' + newResId),
                    dataType: 'json',
                    success: function (data) {
                        daysToEnable[newResId + ""] = data;
                        loadPicker();
                    }
                });
            } else {
                loadPicker();
            }
        }
        jQuery("#bfimodalperiod").dialog("open");
        jQuery("#bfimodalperiodcheckin").datepicker("hide");

    });

			if (typeof daysToEnableTimePeriod !== 'undefined' && typeof initDatepickerTimePeriod !== 'undefined' && jQuery.isFunction(initDatepickerTimePeriod)) {
				initDatepickerTimePeriod();
			}
			jQuery("#bfi-timeperiod-select").attr("data-resid",0);
			jQuery("#bfimodaltimeperiod").dialog({
				closeText: "",
				title: "<?php _e('Change your details', 'bfi') ?>",
				autoOpen: false,
				modal: true,
				width: 'auto',
				maxWidth: "300px",
				dialogClass: 'bfi-dialog bfi-dialog-timeperiod',
				close: function() {
				}
			});
			bfi_currTRselected = null;
			jQuery(".bfi-result-list").on("click",".bfi-timeperiod:not(.bfi-timeperiod-disabled)", function (e) {
				var currResId = jQuery("#bfi-timeperiod-select").attr("data-resid");
				var elm = jQuery(this).hasClass("bfi-timeperiod") ? jQuery(this) : jQuery(this).closest(".bfi-timeperiod");
				var newResId = jQuery(elm).attr("data-resid");
				jQuery("#bfimodaltimeperiod").attr("data-timestart", jQuery(this).attr("data-timestart"));
				jQuery("#bfimodaltimeperiod").attr("data-timeend", jQuery(this).attr("data-timeend"));
				
				if (bfi_currTRselected != jQuery(this).closest("tr")){
					bfi_currTRselected = jQuery(this).closest("tr");
					jQuery("#bfi-timeperiod-select").attr("data-resid", newResId);
					jQuery("#bfi-timeperiod-select").attr("data-timelength", jQuery(elm).attr("data-timelength"));
					jQuery("#bfi-timeperiod-select").attr("data-checkin", jQuery(elm).attr("data-checkin"));
					jQuery("#selectpickerTimePeriodStart").attr("data-resid", newResId);
					jQuery("#bfimodaltimeperiodcheckin").attr("data-resid", newResId);
					jQuery('#bfimodaltimeperiod').block({message: ''});

					function loadPicker() {
						jQuery("#bfimodaltimeperiodcheckin").datepicker("setDate", jQuery.datepicker.parseDate( "yymmdd", jQuery(elm).attr("data-checkin")) );
						jQuery("#bfimodaltimeperiodcheckout").datepicker("setDate", jQuery.datepicker.parseDate( "yymmdd", jQuery(elm).attr("data-checkout")) );
						updateTimePeriodRange(jQuery.datepicker.parseDate( "yymmdd", jQuery(elm).attr("data-checkin")), newResId, jQuery("#bfimodaltimeperiod"));
					}
					
					if (!daysToEnableTimePeriod[jQuery(this).attr("data-resid")]) {
						jQuery.ajax({ 
							url: bookingfor.getActionUrl(null, null, "listCheckInDateHours", 'resourceId=' + newResId),
							dataType: 'json',
							cache: false,
							success: function(data) { 
								daysToEnableTimePeriod[newResId + ""] = {};
								daysToEnable[newResId + ""] = [];
								jQuery.each(data, function(i, dt){
									daysToEnable[newResId + ""].push(parseInt(dt.StartDate));
									daysToEnableTimePeriod[newResId + ""][dt.StartDate] = JSON.parse(dt.TimeRangesString);
								});
								jQuery('#bfimodaltimeperiod').unblock();
								loadPicker();
							} 
						});
					} else {
						loadPicker();
					}
				}

				jQuery("#bfimodaltimeperiod").dialog("open");
				jQuery("#bfimodaltimeperiodcheckin").datepicker("hide");

			 });

			initDatepickerTimeSlot();
			jQuery("#bfi-timeslot-select").attr("data-resid",0);
			jQuery("#bfi-timeslot-select").attr("data-sourceid",0);
			dialogTimeslot = jQuery("#bfimodaltimeslot").dialog({
				closeText: "",
				title: "<?php _e('Change your details', 'bfi') ?>",
				autoOpen: false,
				modal: true,
				width: 'auto',
				maxWidth: "300px",
				dialogClass: 'bfi-dialog bfi-dialog-timeslot',
				close: function() {
				}
			});
			bfi_currTRselected = null;
			jQuery(".bfi-result-list").on("click", ".bfi-timeslot", function (e) {

				var currSourceId = jQuery("#bfi-timeslot-select").attr("data-sourceid");
				var newSourceId = jQuery(this).attr("data-sourceid");
				var elm = jQuery(this).hasClass("bfi-timeslot") ? jQuery(this) : jQuery(this).closest(".bfi-timeslot");
				var currResId = jQuery("#bfi-timeslot-select").attr("data-resid");
				var newResId = jQuery(elm).attr("data-resid");
				var newDate = jQuery(elm).attr("data-checkin");
				
				if (bfi_currTRselected != jQuery(this).closest("tr")){
					bfi_currTRselected = jQuery(this).closest("tr");
					jQuery("#bfi-timeslot-select").attr("data-sourceid", newSourceId);
					jQuery("#bfi-timeslot-select").attr("data-resid", newResId);
					jQuery("#selectpickerTimeSlotRange").attr("data-resid", newResId);
					jQuery("#selectpickerTimeSlotRange").attr("data-sourceid", newSourceId);
					jQuery("#bfi-timeslot-select").attr("data-checkin", newDate);
					jQuery("#bfimodaltimeslotcheckin").attr("data-resid", newResId);
					jQuery("#bfimodaltimeslotcheckin").datepicker("setDate", jQuery.datepicker.parseDate( "yymmdd", newDate) );
					jQuery('#bfimodaltimeslot').block({message: ''});

					function loadPicker() {
						jQuery("#bfimodaltimeslotcheckin").datepicker("setDate", jQuery.datepicker.parseDate("yymmdd", newDate));
						dateTimeSlotChanged(jQuery("#bfimodaltimeslotcheckin"));
					}
					 var datereformat = jQuery.datepicker.formatDate("yymmdd", jQuery("#bfimodaltimeslotcheckin").datepicker("getDate"));
					if (!daysToEnable[newResId + ""]) {
						jQuery.ajax({
							url: bookingfor.getActionUrl(null, null, "GetCheckInDatesTimeSlot", 'resourceId=' + newResId + '&checkin=' + datereformat),
							dataType: 'json',
							success: function (data) {
								daysToEnable[newResId + ""] = [];
								daysToEnableTimeSlot[newResId + ""] = data;
								jQuery.each(data, function (i, dt) {
									daysToEnable[newResId + ""].push(dt.StartDate);
								});
								loadPicker();
							}
						});
					} else {
						loadPicker();
					}
				}
				jQuery("#bfimodaltimeslot").dialog( "open" );

			 });
	getAjaxInformationsSrv();
//	getAjaxInformationsResGrp();

	bookingfor.bfiGetAllTags(bfiUpdateInfoResGrp);
		});
    </script>




<script type="text/javascript">
<!--
var bfi_currMerchantId = <?php echo $merchant->MerchantId ?>;
var bfi_currAdultsAge = <?php echo COM_BOOKINGFORCONNECTOR_ADULTSAGE ?>;
var bfi_currSenioresAge = <?php echo COM_BOOKINGFORCONNECTOR_SENIORESAGE ?>;

var bfisrv = [];
var imgPathMG = "<?php echo BFCHelper::getImageUrlResized('tag','[img]', 'merchant_merchantgroup') ?>";
var imgPathMGError = "<?php echo BFCHelper::getImageUrl('tag','[img]', 'merchant_merchantgroup') ?>";
var listServiceIds = "<?php echo implode(",", $allServiceIds) ?>";
var bfisrvloaded=false;
var resGrp = [];
var loadedResGrp=false;

function bfiUpdateInfoResGrp(){
	jQuery(".bfiresourcegroups").each(function(){
		var currList = jQuery(this).attr("rel");
		if (currList!= null && currList!= '')
		{
			var srvlist = currList.split(',');
			var srvArr = [];
			jQuery.each(srvlist, function(key, srvid) {
				if(typeof bookingfor.tagNameLoaded[srvid] !== 'undefined' ){
					srvArr.push(bookingfor.tagNameLoaded[srvid]);
				}
			});
			jQuery(this).html(srvArr.join(" "));
		}

	});
}


function getAjaxInformationsSrv(){
	if (!bfisrvloaded)
	{
		bfisrvloaded=true;
		if(listServiceIds!=""){
			jQuery.get(bookingfor.getActionUrl(null, null, "GetServicesByIds", "ids=" + listServiceIds + "&language=<?php echo $language ?>"), function(data) {
				if(data!=null){
					jQuery.each(data, function(key, val) {
						bfisrv[val.ServiceId] = val.Name ;
					});	
					bfiUpdateInfo();
				}
			},'json');
		}
	}
}

function bfiUpdateInfo(){
	jQuery(".bfisimpleservices").each(function(){
		var currList = jQuery(this).attr("rel");
		if (currList!= null && currList!= '')
		{
			var srvlist = currList.split(',');
			var srvArr = [];
			jQuery.each(srvlist, function(key, srvid) {
				if(typeof bfisrv[srvid] !== 'undefined' ){
					srvArr.push(bfisrv[srvid]);
				}
			});
			jQuery(this).html(srvArr.join(", "));
		}
	});
	bookingfor.shortenText(jQuery(".bfisimpleservices"),150);
}

var xhralternativeMrc;
var xhralternativeRsc;

jQuery(document).ready(function () {
<?php if(!empty($fromSearch) && empty($makesearch)){ ?>
	calculateQuote();
<?php } ?>
});
jQuery(".bfishowoverrpl").click(function() {
			jQuery(this).toggleClass("bfishowoverrpl-active");
			var currResid = jQuery(this).attr('data-resid');
			var currentTrs = jQuery(this).closest("table").find("[id^=data-id-"+currResid+"-]");
			var currTotRtpls = currentTrs.length;
			var currTotVisibleRtpls = currentTrs.filter(":visible").size();
			var currentResTr = jQuery("#bfitdresdesc"+currResid);
			var maxVisible = Number(currentResTr.attr("data-maxvis")||5);
			if (currTotVisibleRtpls>maxVisible)
			{
				currentTrs.slice(5).hide();
				currentTrs.slice(5).filter("[IsSelected='true']").show();
				var currNowVisibleRtpls = currentTrs.filter(":visible").size();
				currentResTr.attr("data-maxvis",currNowVisibleRtpls)
//				currentTrs.slice(5).fadeOut();
				currentResTr.attr('rowspan', currNowVisibleRtpls +2 );
			}else{
				currentResTr.attr('rowspan', currTotRtpls +2 );
//				currentTrs.show();
				currentTrs.fadeIn();
			}
});

//-->
</script>
</div>
<?php 
} // if isbot
?>
