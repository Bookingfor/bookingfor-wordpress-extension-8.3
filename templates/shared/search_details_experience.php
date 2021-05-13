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

$persontype_text = array(
						'1' => __('Seniores', 'bfi'),   
						'2' => __('Adults', 'bfi'),
						'3' => __('Youth', 'bfi'),
						'4' => __('Children', 'bfi'),
						'5' => __('Infant', 'bfi'),  
					);
	
$resetCart = 0;
$showSenior = 0; // blocco i seniores
$showChildren = 1; // blocco i seniores
$ProductAvailabilityType = 1;
$checkInDates = '';
$hideRateplanOver = 5;

$defaultAdultsAge = BFCHelper::$defaultAdultsAge;
$defaultSenioresAge = BFCHelper::$defaultSenioresAge;
//$useSeniores= isset($_REQUEST['seniores']);
$useSeniores = 0;
$paxRanges =[];
$fullPaxs = null;

if(!empty($resourceId)){
		// controllo se sono passate fasce di età nel merchant, allora sovrascrivo i dati
		if (!empty($resource->PaxRangesString)) {
			$paxRanges = json_decode($resource->PaxRangesString);	
			$fullPaxs =  array_values(array_filter($paxRanges, function($ages) {
					return $ages->FullPax;
				}));
				// riordino le età e prima gli adulti
				usort($paxRanges, function($a, $b)
				{
					return $b->MaxAge>$a->MaxAge;
				});
				usort($paxRanges, function($a, $b)
				{
					return $b->FullPax>$a->FullPax;
				});


			if (!empty($fullPaxs) && count($fullPaxs)>0) {
				$defaultAdultsAge = ($defaultAdultsAge<$fullPaxs[0]->MinAge)?$fullPaxs[0]->MinAge:$defaultAdultsAge;
				$defaultOversAge = $fullPaxs[0]->MaxAge;
				$overPaxs =  array_values(array_filter($paxRanges, function($ages) use ($defaultOversAge) {
				return !$ages->FullPax && $ages->MinAge>=$defaultOversAge;
					}));

				if (!empty($overPaxs) && count($overPaxs)>0) {
					$useSeniores = 1;
					$defaultSenioresAge = $fullPaxs[0]->MaxAge;
				}
			}

		}
}
		// aggiungo la proprietà value con valore 0 di default
		if (count($paxRanges)>0) {	
				foreach ($paxRanges as $keypr => $paxRange) {										
					if ($paxRange->MinAge<= $key && $paxRange->MaxAge > $key) {
					    $paxRanges[$keypr]->value = 0;						
					}
				}
		}
$isportal = COM_BOOKINGFORCONNECTOR_ISPORTAL;
$usessl = COM_BOOKINGFORCONNECTOR_USESSL;
$currModID = uniqid('bfisearchexperiences');

$fromSearch = BFCHelper::getVar('fromsearch','0');
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
//if(!empty($resourcegroupId)){
//	$accommodationdetails_page = get_post( bfi_get_page_id( 'resourcegroupdetails' ) );
//}
$url_resource_page = get_permalink( $accommodationdetails_page->ID );

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
	$currUriresource  = $uri.$resource->ResourceId.'-'.BFI()->seoUrl($resourceName);
//	if(!empty($resourcegroupId)){
//		$currUriresource  = $uri.$resourcegroupId.'-'.BFI()->seoUrl($resourceName);
//	}
	$formRoute = $currUriresource .'/?task=getMerchantResources';
}else{
	// se 
	if (!empty($merchant->EnableTimeCheckOnly) || $itemTypeIds == bfi_ItemType::Rental) {
	    $ProductAvailabilityType = 2;
	}
	$formRoute = $routeMerchant .'/?task=getMerchantResources';
}

if($usessl){
	$url_cart_page = str_replace( 'http:', 'https:', $url_cart_page );
}

// controllo se il merchant utilizza ile fasce di età altrimenti non le visualizzo nel form di ricerca


$hasPaxes = $merchant->HasPaxes;

$formOrderRouteBook = $url_cart_page;

$pars = BFCHelper::getSearchParamsSession('search.params.experience');
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
//	$useSeniores= isset($_REQUEST['seniores']);
	
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
		$paxages[] = $defaultAdultsAge;
	}
	for ($i = 0; $i < $seniores; $i++) {
		$paxages[] = $defaultSenioresAge;
	}
	if ($children > 0) {
		for ($i = 0;$i < $children; $i++) {
			$age =$_REQUEST['childages'.($i+1)];
			if ($age < $defaultAdultsAge) {
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
//	if (!empty($pars['paxages']) && empty($currPackage) && !empty($fromSearch) ) {
	if (!empty($pars['paxages']) && empty($currPackage)  ) {
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
	BFCHelper::setSearchParamsSession($pars,'search.params.experience');
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
$maxchildrenAge = (int)$defaultAdultsAge-1;

$nchs = array(null,null,null,null,null,null);

$maxPersonValue = 10;
$minPersonValue = 1;

if(!empty($resourceId)){
	if(isset($resource->MaxCapacityPaxes) && $resource->MaxCapacityPaxes > 0) { 
		$maxPersonValue = $resource->MaxCapacityPaxes;
	 } 
	if(isset($resource->MinCapacityPaxes) && $resource->MinCapacityPaxes > 0) { 
		$minPersonValue = $resource->MinCapacityPaxes;
	 } 
}

// non ho una lista ricercata di età
if (empty($paxages)){

	$nad = 2;
//	$paxages = array($defaultAdultsAge, $defaultAdultsAge);
	if(!empty($resourceId)){
		if(isset($resource->MaxCapacityPaxes) && $resource->MaxCapacityPaxes > 0 && ( $totalPerson > $resource->MaxCapacityPaxes || $totalPerson < $resource->MinCapacityPaxes )) { 
			$nad = $resource->MinCapacityPaxes==0?($resource->MaxCapacityPaxes<$nad?$resource->MaxCapacityPaxes:$nad) :$resource->MinCapacityPaxes;
		 } 
	}
	$paxages  = array();
	for ($totAd=0;$totAd<$nad ; $totAd++) {
		array_push($paxages,$defaultAdultsAge);
	}

}else{
	if(is_array($paxages)){
		$countPaxes = array_count_values($paxages);
		$nchs = array_values(array_filter($paxages, function($age) {
			if ($age < (int)$defaultAdultsAge)
				return true;
			return false;
		}));
	}
}


array_push($nchs, null,null,null,null,null,null);

	// controllo se ho un range di età 
if($countPaxes>0){
	foreach ($countPaxes as $key => $count) {
		if ($key >= $defaultAdultsAge) {
			if ($key >= $defaultSenioresAge) {
				$nse += $count;
			} else {
				$nad += $count;
			}
		} else {
			$nch += $count;
		}

		// se ho range di età ciclo per associare le giuste quantità
		if (count($paxRanges)>0) {	
				foreach ($paxRanges as $keypr => $paxRange) {										
					if ($paxRange->MinAge<= $key && $paxRange->MaxAge > $key) {
					    $paxRanges[$keypr]->value = $count;						
					}
				}
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
		array_push($paxages,$defaultAdultsAge);
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
<div class="bficontainerexperiencebox">
<script type="text/javascript">
	var daysToEnable = {};
	var checkOutDaysToEnable = {};
    var unitId = '<?php echo $resourceId ?>';
    var bfi_MaxQtSelectable = <?php echo COM_BOOKINGFORCONNECTOR_MAXQTSELECTABLE ?>;
</script>
<!-- form fields -->
<?php if(!empty($resource->MinPrice)) { ?>
	<div class="bfipricefrom <?php echo ($resource->MinPrice>0) ?"":"bfi-hide"; ?>"><?php _e('from', 'bfi') ?> <span class="text-nowrap bfi_<?php echo $currencyclass ?>"><?php echo BFCHelper::priceFormat($resource->MinPrice); ?></span></div>
	<div class="bfipricefrominfo"><?php _e('The price varies according to the dates and the number of people', 'bfi') ?></div>
<?php } ?>
<h4 class="bfi-hide ">

	<div class="bfi-hide bfi-pull-right"><a href="<?php echo $url_cart_page ?>" class="bfi-shopping-cart"><i class="fa fa-shopping-cart "></i> <span class="bfibadge" style="<?php echo (COM_BOOKINGFORCONNECTOR_SHOWBADGE) ?"":"display:none"; ?>"><?php echo ($currentCartsItems>0) ?$currentCartsItems:"";?></span><?php _e('Cart', 'bfi') ?></a></div>
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
<form id="bfi-calculatorFormexperience" action="<?php echo $formRoute?>" method="<?php echo $formMethod?>" class="bficalculatorformexperience bfi_resource-calculatorForm bfi_resource-calculatorTable bfi-dateform-container "
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
<!-- data -->
				<div class="bfi-showdaterange bfi-col-md-12 bfidaterangepicker bfidaterangepicker-container " data-checkin="<?php echo $checkin->format('d/m/Y H:i') ?>" data-checkout="<?php echo $checkout->format('d/m/Y H:i') ?>">
					<label><?php _e('Select dates', 'bfi') ?></label>
					<div class="bfi-showdaterangecontainer bfiboxcontainerfield">	
						<i class="fa fa-calendar"></i>&nbsp;
						<span class=" bfidaterangepicker-checkin">
							<span><?php echo date_i18n('l',$checkin->getTimestamp()) ?> <?php echo $checkin->format("d") ?> <?php echo date_i18n('F',$checkin->getTimestamp()) ?> <?php echo $checkin->format('Y') ?></span>
						</span>
						<span class="bfidaterangepicker-checkout  bfi-hide">
							-
							<span><?php echo date_i18n('l',$checkout->getTimestamp()) ?> <?php echo $checkout->format("d") ?> <?php echo date_i18n('F',$checkout->getTimestamp()) ?> <?php echo $checkout->format('Y') ?></span>
						</span>
					</div>
				</div>
<!-- persone -->
<?php if($hasPaxes ) { ?>
	<?php if($maxPersonValue==1) { ?>
				<div class="bfi-col-md-12 bfi-showperson-container ">
					<label class="bfi-title-showprerson"><?php _e('Participants', 'bfi'); ?></label>
					<div class="bfi-showperson-text bfi-container bfiboxcontainerfield" style="cursor: default;">
						<i class="fas fa-user-friends"></i>
						<span id="bfi-adult-info<?php echo $currModID ?>"><span><?php echo $nad ?></span> <?php _e('Person', 'bfi'); ?></span>
						<input id="bfi-adult<?php echo $currModID ?>" value="<?php echo $nad ?>" data-field type="number" pattern="[0-9]" min="<?php echo $minPersonValue ?>" max="<?php echo $maxPersonValue ?>" step="1" maxlength="2" autocomplete="off" name="adultssel" onchange="bfi_quoteChanged('<?php echo $currModID ?>')" class="bfi-input-number bfi-adult<?php echo $currModID ?>" style="display:none !important;">
					</div>
				</div>
	<?php } else { ?>
			<?php if(count($paxRanges)>0) { 
	
	?>
				<div class="bfi-col-md-12 bfi-showperson-container ">
					<label class="bfi-title-showprerson"><?php _e('Participants', 'bfi'); ?></label>
					<div class="bfi-showperson-text bfi-container bfiboxcontainerfield">
						<i class="fas fa-user-friends"></i>
<?php 
				foreach ($paxRanges as $key => $paxRange) {										
						$persontype="";
						switch (true) {
							case $paxRange->MinAge > $defaultAdultsAge : //Seniores
								$persontype = $persontype_text[1];
								break;
							case $paxRange->MaxAge <= $defaultAdultsAge && $paxRange->MinAge >=13 : //Youth
								$persontype = $persontype_text[3];
								break;
							case $paxRange->MaxAge <= $defaultAdultsAge && $paxRange->MinAge >=2  && $paxRange->MinAge <13 : //Youth
								$persontype = $persontype_text[4];
								break;
							case $paxRange->MaxAge <= $defaultAdultsAge && $paxRange->MinAge >=0 && $paxRange->MinAge <2  : //Youth
								$persontype = $persontype_text[5];
								break;
							default: //Adults
								$persontype = $persontype_text[2];
						}
						?>
						<span id="bfi-person-info-<?php echo $key ?>-<?php echo $currModID ?>" class="bfi-comma" style="<?php //echo $paxRange->value>0?"":"display:none;" ?>"><span><?php echo $paxRange->value>0?$paxRange->value:"0" ?></span> <?php _e($persontype, 'bfi'); ?> (<?php echo $paxRange->MinAge ?>-<?php echo $paxRange->MaxAge ?>)</span>
						
						<?php 
				}
?>

					</div>
				</div>
				<div class="bfi-showperson bfi-hide "  data-max="<?php echo $maxPersonValue ?>"  data-currmodid="<?php echo $currModID ?>" >
						<?php foreach ($paxRanges as $key => $paxRange) {	
						$persontype="";
						switch (true) {
							case $paxRange->MinAge > $defaultAdultsAge : //Seniores
								$persontype = $persontype_text[1];
								break;
							case $paxRange->MaxAge <= $defaultAdultsAge && $paxRange->MinAge >=13 : //Youth
								$persontype = $persontype_text[3];
								break;
							case $paxRange->MaxAge <= $defaultAdultsAge && $paxRange->MinAge >=2  && $paxRange->MinAge <13 : //Youth
								$persontype = $persontype_text[4];
								break;
							case $paxRange->MaxAge <= $defaultAdultsAge && $paxRange->MinAge >=0 && $paxRange->MinAge <2  : //Youth
								$persontype = $persontype_text[5];
								break;
							default: //Adults
								$persontype = $persontype_text[2];
						}
						?>
						
						<div class="bfi-showperson-selector-container ">
							<div class="bfi-showadult bfi-showperson-selector"><!-- Person -->
								<label><?php _e($persontype, 'bfi'); ?> (<?php echo $paxRange->MinAge ?>-<?php echo $paxRange->MaxAge?>)</label>								
								<div class="bfi-input-group bfi-mobile-input-group ">
									<span class="bfi-input-group-btn">
										<button type="button" class="btn btn-alternative bfi-btn btn-number-person " data-type="minus" data-field=".bfi-person-<?php echo $key ?>-<?php echo $currModID ?>">
											<i class="fa fa-minus" aria-hidden="true"></i>
										</button>
									</span>

									<input id="bfi-person-<?php echo $key ?>-<?php echo $currModID ?>" value="<?php echo $paxRange->value+0  ?>" data-field type="number" pattern="[0-9]" min="<?php echo $paxRange->FullPax?$minPersonValue:0; ?>" max="<?php echo $maxPersonValue ?>" step="1" maxlength="2" autocomplete="off" 
									name="personsel-<?php echo $currModID ?>" 
									onchange="bfi_countRangePersone(this, '<?php echo $currModID ?>')" 
									data-ref="bfi-person-info-<?php echo $key ?>-<?php echo $currModID ?>" 
									data-age="<?php echo $paxRange->FullPax?max($paxRange->MinAge, $defaultAdultsAge) :$paxRange->MinAge ?>"
									class="bfi-input-number bfi-paxages bfi-person-<?php echo $key ?>-<?php echo $currModID ?>" style="display:inline-block !important;">
									
									<span class="bfi-input-group-btn">
										<button type="button" class="btn btn-alternative bfi-btn btn-number-person " data-type="plus" data-field=".bfi-person-<?php echo $key ?>-<?php echo $currModID ?>">
											<i class="fa fa-plus" aria-hidden="true"></i>
										</button>
									</span>
								</div>
							</div>
						</div>
						<?php }	?>
				</div>

			<?php } else {?>

				<div class="bfi-col-md-12 bfi-showperson-container ">
					<label class="bfi-title-showprerson"><?php _e('Participants', 'bfi'); ?></label>
					<div class="bfi-showperson-text bfi-container bfiboxcontainerfield">
						<i class="fas fa-user-friends"></i>
						<span id="bfi-adult-info<?php echo $currModID ?>" class="bfi-comma"><span><?php echo $nad ?></span> <?php _e('Adults', 'bfi'); ?></span>
						<?php if($useSeniores){?><span id="bfi-senior-info<?php echo $currModID ?>" class="bfi-comma"><span><?php echo $nse ?></span> </span> <?php _e('Seniores', 'bfi'); ?></span><?php }?>
						<?php if($showChildren){?><span id="bfi-child-info<?php echo $currModID ?>" class="bfi-comma"><span><?php echo $nch ?></span> <?php _e('Children', 'bfi'); ?></span><?php }?>
					</div>
				</div>
				<div class="bfi-showperson bfi-hide "  data-max="<?php echo $maxPersonValue ?>"  data-currmodid="<?php echo $currModID ?>" >
						<div class="bfi-showperson-selector-container ">
							<div class="bfi-showadult bfi-showperson-selector"><!-- Adults -->
								<label><?php _e('Adults', 'bfi'); ?></label>								
								<div class="bfi-input-group bfi-mobile-input-group ">
									<span class="bfi-input-group-btn">
										<button type="button" class="btn btn-alternative bfi-btn btn-number-person " data-type="minus" data-field=".bfi-adult<?php echo $currModID ?>">
											<i class="fa fa-minus" aria-hidden="true"></i>
										</button>
									</span>

									<input id="bfi-adult<?php echo $currModID ?>" value="<?php echo $nad ?>" data-field type="number" pattern="[0-9]" min="<?php echo $minPersonValue ?>" max="<?php echo $maxPersonValue ?>" step="1" maxlength="2" autocomplete="off" name="adultssel" onchange="bfi_quoteChanged('<?php echo $currModID ?>')" class="bfi-input-number bfi-adult<?php echo $currModID ?>" style="display:inline-block !important;">
									
									<span class="bfi-input-group-btn">
										<button type="button" class="btn btn-alternative bfi-btn btn-number-person " data-type="plus" data-field=".bfi-adult<?php echo $currModID ?>">
											<i class="fa fa-plus" aria-hidden="true"></i>
										</button>
									</span>
								</div>
							</div>
						<?php if($useSeniores){ 
						?>
							<div class="bfi-showsenior  bfi-showperson-selector"><!-- Seniores -->
								<label><?php _e('Seniores', 'bfi'); ?></label>
								<div class="bfi-input-group bfi-mobile-input-group ">
									<span class="bfi-input-group-btn">
										<button type="button" class="btn btn-alternative bfi-btn btn-number-person " data-type="minus" data-field=".bfi-senior<?php echo $currModID ?>">
											<i class="fa fa-minus" aria-hidden="true"></i>
										</button>
									</span>

									<input id="bfi-senior<?php echo $currModID ?>" value="<?php echo $nse ?>" data-field type="number" pattern="[0-9]" min="0" max="<?php echo $maxPersonValue ?>" step="1" maxlength="2" autocomplete="off" name="senioressel" onchange="bfi_quoteChanged('<?php echo $currModID ?>')" class="bfi-input-number bfi-senior<?php echo $currModID ?>" style="display:inline-block !important;">
									
									<span class="bfi-input-group-btn">
										<button type="button" class="btn btn-alternative bfi-btn btn-number-person " data-type="plus" data-field=".bfi-senior<?php echo $currModID ?>">
											<i class="fa fa-plus" aria-hidden="true"></i>
										</button>
									</span>
								</div>
							</div>
						<?php }?>
						<?php if($showChildren){ 
						?>
							<div class="bfi-showchildren bfi-showperson-selector" id="mod_bookingforsearch-children<?php echo $currModID ?>" ><!-- n childrens -->
								<label><?php _e('Children', 'bfi'); ?></label>
								<div class="bfi-input-group bfi-mobile-input-group ">
									<span class="bfi-input-group-btn">
										<button type="button" class="btn btn-alternative bfi-btn btn-number-person " data-type="minus" data-field=".bfi-child<?php echo $currModID ?>">
											<i class="fa fa-minus" aria-hidden="true"></i>
										</button>
									</span>

									<input id="bfi-child<?php echo $currModID ?>" value="<?php echo $nch ?>" data-field type="number" pattern="[0-9]" min="0" max="4" step="1" maxlength="2" autocomplete="off" name="childrensel" onchange="bfi_quoteChanged('<?php echo $currModID ?>')" class="bfi-input-number bfi-child<?php echo $currModID ?>" style="display:inline-block !important;">
									
									<span class="bfi-input-group-btn">
										<button type="button" class="btn btn-alternative bfi-btn btn-number-person " data-type="plus" data-field=".bfi-child<?php echo $currModID ?>">
											<i class="fa fa-plus" aria-hidden="true"></i>
										</button>
									</span>
								</div>
							</div>
						<?php }?>
						</div>
						<?php if($showChildren){?>
						<div class="bfi-childrenages bfi_resource-experienceForm-childrenages" style="display:none;"  id="mod_bookingforsearch-childrenages<?php echo $currModID ?>">
								
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
			<?php } //end if paxrange ?>
	<?php } ?>
<?php 
}else{
?>
				<div class="bfi-showperson "  data-max="<?php echo $maxPersonValue ?>">
					<div class="bfi-showperson-selector-container  bfi-col-md-12 ">
							<div class="bfi-showadult bfi-showperson-selector"><!-- Adults -->
								<label><?php _e('Persons', 'bfi'); ?></label>								
								<div class="bfi-input-group bfi-mobile-input-group ">
									<span class="bfi-input-group-btn">
										<button type="button" class="btn btn-alternative bfi-btn btn-number-person " data-type="minus" data-field=".bfi-adult<?php echo $currModID ?>">
											<i class="fa fa-minus" aria-hidden="true"></i>
										</button>
									</span>

									<input id="bfi-adult<?php echo $currModID ?>" value="<?php echo $nad ?>" data-field type="number" pattern="[0-9]" min="<?php echo $minPersonValue ?>" max="<?php echo $maxPersonValue ?>" step="1" maxlength="2" autocomplete="off" name="adultssel" onchange="bfi_quoteChanged('<?php echo $currModID ?>')" class="bfi-input-number bfi-adult<?php echo $currModID ?>" style="display:inline-block !important;">
									
									<span class="bfi-input-group-btn">
										<button type="button" class="btn btn-alternative bfi-btn btn-number-person " data-type="plus" data-field=".bfi-adult<?php echo $currModID ?>">
											<i class="fa fa-plus" aria-hidden="true"></i>
										</button>
									</span>
								</div>
							</div>
					</div>
				</div>
<?php } ?>
				
			</div>

 
<!-- invio -->				
				<div class="  bfi-margin-top10 bfi-margin-bottom10">
					<a href="javascript:calculateQuoteExperience()" class="calculateButton-mandatory calculateButton3 bfi-btn bfi-alternative <?php echo $btnSearchclass ?>" ><?php echo _e('Check availability','bfi') ?> </a>
				</div>

	</div>	<!-- END bfi_resource-calculatorForm-mandatory -->
	<input name="onlystay" type="hidden" value="1" />
	<input name="newsearch" type="hidden" value="1" />
	<input name="groupresulttype" type="hidden" value="0" />
	<input name="calculate" type="hidden" value="true" />
	<input name="resourceId" type="hidden" value="<?php echo $resourceId?>" />
	
	<input type="hidden" name="adults" value="<?php echo $nad?>" id="searchformpersonsadult<?php echo $currModID ?>">
	<input type="hidden" name="seniores" value="<?php echo $nse?>" id="searchformpersonssenior<?php echo $currModID ?>">
	<input type="hidden" name="children" value="<?php echo $nch?>" id="searchformpersonschild<?php echo $currModID ?>">
	<input type="hidden" name="childages1" value="<?php echo $nchs[0]?>" id="searchformpersonschild1<?php echo $currModID ?>">
	<input type="hidden" name="childages2" value="<?php echo $nchs[1]?>" id="searchformpersonschild2<?php echo $currModID ?>">
	<input type="hidden" name="childages3" value="<?php echo $nchs[2]?>" id="searchformpersonschild3<?php echo $currModID ?>">
	<input type="hidden" name="childages4" value="<?php echo $nchs[3]?>" id="searchformpersonschild4<?php echo $currModID ?>">
	<input type="hidden" name="childages5" value="<?php echo $nchs[4]?>" id="searchformpersonschild5<?php echo $currModID ?>">
	
	<input type="hidden" name="paxages" value="<?php echo implode(",", $paxages)?>" id="searchformpersonspaxages<?php echo $currModID ?>">

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

	<input type="hidden" name="checkin" value="<?php echo $checkin->format('d/m/Y'); ?>" />
	<input type="hidden" name="checkout" value="<?php echo $checkout->format('d/m/Y'); ?>" />
	<input name="checkAvailability" type="hidden" value="1" />
	<input name="checkStays" type="hidden" value="1" />
	
	<input type="hidden" name="timeSlotId" value="<?php echo $timeSlotIdSelected ?>" />
	<input type="hidden" name="hideform" value="1" />

	<input type="hidden" value="0" name="showmsgchildage" id="showmsgchildagecalculator"/>
	<div class="bfi-hide" id="bfi_childrenagesmsgcalculator">
		<div style="line-height:0; height:0;"></div>
		<div class="bfi-pull-right" style="cursor:pointer;color:red">&nbsp;<i class="fa fa-times-circle" aria-hidden="true" onclick="jQuery('#bfi_lblchildrenagescalculator').webuiPopover('destroy');"></i></div>
		<?php echo sprintf(__('We preset your children\'s ages to %s years old - but if you enter their actual ages, you might be able to find a better price.', 'bfi'),COM_BOOKINGFORCONNECTOR_CHILDRENSAGE) ?>
	</div>
</form>	
<!-- form fields end-->
	
<script type="text/javascript">
var productAvailabilityType = <?php echo $ProductAvailabilityType?>;
var currCheckin, currCheckout,
	currResourceid=<?php echo !empty($resourceId)? $resourceId : 0; ?>;
var daysToEnable = {};
	
function bfiGetAjaxOptionsExperience(whatsearch) {
	var currForm = jQuery('#bfi-calculatorFormexperience');

	var currShowdatetimerange = false;
	var currCheckin = jQuery(currForm).find("input[name='checkin']").first();
	var currCheckout = jQuery(currForm).find("input[name='checkout']").first();
	var formUrl = '<?php echo $formRoute?>';
	var msgwait = '<i class="fa fa-spinner fa-spin fa-3x fa-fw margin-bottom"></i><span class="sr-only">Loading...</span>'
	formUrl += ((formUrl.indexOf('?') > -1)? "&" :"?") + 'format=calc&tmpl=component'

	var options = { 
	    target:     '#calculator',
		type: 'POST',
	    replaceTarget: true, 
	    url:        formUrl, 
	    beforeSend: function() {
			jQuery('.bfi-result-list').hide();
	    	jQuery(currForm).block({
					message: '<i class="fa fa-spinner fa-spin fa-3x fa-fw margin-bottom"></i><span class="sr-only">Loading...</span>',
					message: msgwait,
					css: {border: 'none'},
					overlayCSS: {backgroundColor: '#ffffff', opacity: 0.7}  
				});
		},
	    success: function() { 
			jQuery(currForm).unblock();
				var timeSlotId = jQuery('input[name=timeSlotId]').val();
				if (timeSlotId!='' && timeSlotId!='0')
				{
					var currTSSelected  = jQuery(".bfi-slot-list a[data-productid="+timeSlotId+"]");
					if(currTSSelected.length>0){
						updateTotalTimeSlotList(currTSSelected);
					}
				}

			getCheckinAjaxDateExperience(function() {
				var currForm = jQuery('#bfi-calculatorFormexperience');
				currForm.unblock();
				var currShowdatetimerange = false;
				var currCheckin = jQuery(currForm).find("input[name='checkin']").first();
				var currCheckout = jQuery(currForm).find("input[name='checkout']").first();
					// new calendar
					var currCalendarRange = jQuery(currForm).find(".bfidaterangepicker");
					currCalendarRange.daterangepicker({
						"singleDatePicker" : true,  // visualizzo solo 1 data
						"minDate": moment(),
						"maxDate": moment().add(1,'year'),
						"minYear": parseInt(moment().format('YYYY')),
						"maxYear": parseInt(moment().format('YYYY'),1),
						"showLabel": true,
						"autoApply": true,
						"timePicker": currShowdatetimerange,
						"timePicker24Hour": true,
						"timePickerIncrement": 15,
						"maxSpan": {
								"days": 20
							},
						"locale": {
							"format": (currShowdatetimerange==0?"DD/MM/YYYY":"DD/MM/YYYY HH:mm"),
							"formatdisplay": (currShowdatetimerange==0?"dddd, DD MMMM YYYY":"ddd, DD MMM HH:mm"),
							"separator": " - ",
							"applyLabel": bfi_variables.bfi_txtTitleBtnOk,
							"cancelLabel": "Cancel",
							"fromLabel": "From",
							"toLabel": "To",
							"customRangeLabel": "Custom",
							"weekLabel": "W",
							"daysOfWeek": bfi_variables.bfi_txtTitleDays,
							"monthNames": bfi_variables.bfi_txtTitleMonths,
							"firstDay": 1
						},
						isInvalidDate: function(date) {
							//compare to your list of dates, return true if date is in the list
							var currTmpForm = jQuery(this).closest("form");
							var okday = bfi_closedBookingExperience(date, 0, daysToEnable[currResourceid + ""],currTmpForm,currResourceid); 
							return !okday;
						},
						"startDate": jQuery(currForm).find("input[name='checkin']").first().val(),
						"endDate": jQuery(currForm).find("input[name='checkout']").first().val()
					}, function(start, end) {
							var currDateFormat = "DD dd MM yy";
							var windowsize = jQuery(window).width();
							if (windowsize > 769 && windowsize < 1300) {
								currDateFormat = " dd mm";
							}
							var currTmpForm = jQuery(this).closest("form");
							var currCheckin = jQuery(currForm).find("input[name='checkin']").first();
							var currCheckout = jQuery(currForm).find("input[name='checkout']").first();
							currCheckin.val(start.format('DD/MM/YYYY'));
	//						currCheckout.val(end.format('DD/MM/YYYY'));
							currCheckout.val(bookingfor.getDisplayDate(bookingfor.dateAdd(start.toDate(), "day", 1)));

							
							jQuery(this.element).find('.bfidaterangepicker-checkin span').html(jQuery.datepicker.formatDate(currDateFormat,start.toDate())  );
							jQuery(this.element).find('.bfidaterangepicker-checkout span').html(jQuery.datepicker.formatDate(currDateFormat,end.toDate()) );
						}
					);

					var currDateFormat = "DD dd MM yy";
					var windowsize = jQuery(window).width();
					if (windowsize > 769 && windowsize < 1300) {
						currDateFormat = " dd mm";
					}
					var startDate = currCalendarRange.data('daterangepicker').startDate;
					var endDate = currCalendarRange.data('daterangepicker').endDate;

			});	


		} 
	}; 
	return options;
}
function calculateQuoteExperience(whatsearch) {
		var currForm = jQuery('#bfi-calculatorFormexperience');
		currForm.ajaxSubmit(bfiGetAjaxOptionsExperience(whatsearch));
	}
function getCheckinAjaxDateExperience(callback) {
	// prepare Options Object 
	jQuery('#bfi-calculatorFormexperience').block({message: ''});
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
			jQuery('#bfi-calculatorFormexperience').unblock();
	    } 
	}; 
	jQuery.ajax(options);
}

jQuery(document).ready(function () {
			getCheckinAjaxDateExperience(function() {
				var currForm = jQuery('#bfi-calculatorFormexperience');
				currForm.unblock();
				var currShowdatetimerange = false;
				var currCheckin = jQuery(currForm).find("input[name='checkin']").first();
				var currCheckout = jQuery(currForm).find("input[name='checkout']").first();
					// new calendar
					var currCalendarRange = jQuery(currForm).find(".bfidaterangepicker");
					currCalendarRange.daterangepicker({
						"singleDatePicker" : true,  // visualizzo solo 1 data
						"minDate": moment(),
						"maxDate": moment().add(1,'year'),
						"minYear": parseInt(moment().format('YYYY')),
						"maxYear": parseInt(moment().format('YYYY'),1),
						"showLabel": true,
						"autoApply": true,
						"timePicker": currShowdatetimerange,
						"timePicker24Hour": true,
						"timePickerIncrement": 15,
						"maxSpan": {
								"days": 20
							},
						"locale": {
							"format": (currShowdatetimerange==0?"DD/MM/YYYY":"DD/MM/YYYY HH:mm"),
							"formatdisplay": (currShowdatetimerange==0?"dddd, DD MMMM YYYY":"ddd, DD MMM HH:mm"),
							"separator": " - ",
							"applyLabel": bfi_variables.bfi_txtTitleBtnOk,
							"cancelLabel": "Cancel",
							"fromLabel": "From",
							"toLabel": "To",
							"customRangeLabel": "Custom",
							"weekLabel": "W",
							"daysOfWeek": bfi_variables.bfi_txtTitleDays,
							"monthNames": bfi_variables.bfi_txtTitleMonths,
							"firstDay": 1
						},
						isInvalidDate: function(date) {
							//compare to your list of dates, return true if date is in the list
							var currTmpForm = jQuery(this).closest("form");
							var okday = bfi_closedBookingExperience(date, 0, daysToEnable[currResourceid + ""],currTmpForm,currResourceid); 
							return !okday;
						},
						"startDate": currCalendarRange.attr('data-checkin'),
						"endDate": currCalendarRange.attr('data-checkout')
					}, function(start, end) {
							var currDateFormat = "DD dd MM yy";
							var windowsize = jQuery(window).width();
							if (windowsize > 769 && windowsize < 1300) {
								currDateFormat = " dd mm";
							}
							var currTmpForm = jQuery(this).closest("form");
							var currCheckin = jQuery(currForm).find("input[name='checkin']").first();
							var currCheckout = jQuery(currForm).find("input[name='checkout']").first();
							currCheckin.val(start.format('DD/MM/YYYY'));
	//						currCheckout.val(end.format('DD/MM/YYYY'));
							currCheckout.val(bookingfor.getDisplayDate(bookingfor.dateAdd(start.toDate(), "day", 1)));

							
							jQuery(this.element).find('.bfidaterangepicker-checkin span').html(jQuery.datepicker.formatDate(currDateFormat,start.toDate())  );
							jQuery(this.element).find('.bfidaterangepicker-checkout span').html(jQuery.datepicker.formatDate(currDateFormat,end.toDate()) );
						}
					);

					var currDateFormat = "DD dd MM yy";
					var windowsize = jQuery(window).width();
					if (windowsize > 769 && windowsize < 1300) {
						currDateFormat = " dd mm";
					}
					var startDate = currCalendarRange.data('daterangepicker').startDate;
					var endDate = currCalendarRange.data('daterangepicker').endDate;

			});	
});

    </script>

</div>
<?php 
} // if isbot
?>
