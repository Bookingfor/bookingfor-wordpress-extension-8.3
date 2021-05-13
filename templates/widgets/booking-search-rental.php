<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if (COM_BOOKINGFORCONNECTOR_ISBOT) {
    return '';
}

$customclass="";
if (!empty($instance['classes'])) {
	$customclass=$instance['classes'];
}
if (!empty($instance['g5_classes'])) {
	$customclass=$instance['g5_classes'];
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
$currModID = uniqid('bfisearchrental');

// get searchresult page...
$searchAvailability_page = get_post( bfi_get_page_id( 'searchavailability' ) );
$resultpageidDefault = $searchAvailability_page->ID;
$resultpageid = bfi_get_translated_page_id(( ! empty( $instance['resultpageid'] ) ) ? esc_attr($instance['resultpageid']) : $resultpageidDefault);

$url_page_Resources = get_permalink( $resultpageid );

if(BFI()->isSearchPage()){
	bfi_setSessionFromSubmittedData('search.params.rental');
}
$parsResource = BFCHelper::getSearchParamsSession('search.params.rental');

$categoryIdResource = 0;
$merchantCategoryIdResource = 0;

$zoneId = 0;
$cityId = 0;
$pricemax = '';
$pricemin = '';
$areamin = '';
$areamax = '';
$points = '';
$pointsonsell = '';
$zoneIdsSplitted = array();
$checkoutspan = '+1 day';
//$checkoutspan = '+0 day';
$checkin = new DateTime('UTC');
$checkin->modify('+3 hour');
$checkin->setTime($checkin->format("H"), 0); 
$checkout = clone $checkin;
$checkout->modify($checkoutspan); 
$paxes = 1;
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
$dropoff = '';
$dropoffValue = '';

$merchantTagIds = '';
$productTagIds = '';
$merchantCategoryId = '';
$minqt = 1;
$currFilterOrder = "";
$currFilterOrderDirection = "";

$startDateTimeRange = ( ! empty( $instance['startDateTimeRange'] ) ) ? ($instance['startDateTimeRange']) : '00:00';
$endDateTimeRange = ( ! empty( $instance['endDateTimeRange'] ) ) ? ($instance['endDateTimeRange']) : '24:00';

$widgettoshow =  ( ! empty( $instance['widgettoshow'] ) ) ? $instance['widgettoshow'] : '';

$startDate =  new DateTime('UTC');
$startDate->modify('+3 hour');
$startDate->setTime($startDate->format("H"), 0); 
	if (strpos($startDateTimeRange,":")!== false) {
		$checkinTime = explode(':',$startDateTimeRange.":0");
		$startDate->setTime((int)$checkinTime[0], (int)$checkinTime[1]); 
		$checkin->setTime((int)$checkinTime[0], (int)$checkinTime[1]); 
		$checkout->setTime((int)$checkinTime[0], (int)$checkinTime[1]); 
	}
//	if (strpos($endDateTimeRange,":")!== false) {
//		$checkoutTime = explode(':',$endDateTimeRange.":0");
//		$checkout->setTime((int)$checkoutTime[0], (int)$checkoutTime[1]); 
//	}

if (!empty($parsResource)){
		
	$currFilterOrder = !empty($parsResource['filter_order']) ? $parsResource['filter_order'] : "";
	$currFilterOrderDirection = !empty($parsResource['filter_order_Dir']) ? $parsResource['filter_order_Dir'] : "";

	$checkin = !empty($parsResource['checkin']) ? $parsResource['checkin'] : new DateTime('UTC');
	$checkout = !empty($parsResource['checkout']) ? $parsResource['checkout'] : new DateTime('UTC');
	
	$searchType = isset($parsResource['searchType']) ? $parsResource['searchType'] : 0;
	$points = isset($parsResource['points']) ? $parsResource['points']: null;

	$zoneId = !empty($parsResource['zoneId']) ? $parsResource['zoneId'] :0;
	$minqt = !empty($parsResource['minqt']) ? $parsResource['minqt'] : 1;
	$maxqt = !empty($parsResource['maxqt']) ? $parsResource['maxqt'] : 10;
	$paxes = !empty($parsResource['paxes']) ? $parsResource['paxes'] : 1;
	$paxages = !empty($parsResource['paxages'])? $parsResource['paxages'] :  array('18','18');
	$merchantCategoryIdResource = !empty($parsResource['merchantCategoryId'])? $parsResource['merchantCategoryId']: 0;
	$masterTypeId = !empty($parsResource['masterTypeId'])? $parsResource['masterTypeId']: 0;
	$merchantIds = isset($parsResource['merchantIds']) ? $parsResource['merchantIds']: "";
	$groupresourceIds = isset($parsResource['groupresourceIds']) ? $parsResource['groupresourceIds']: "";
	$searchterm = !empty($parsResource['searchterm']) ? $parsResource['searchterm'] :'';
	$searchTermValue = !empty($parsResource['searchTermValue']) ? $parsResource['searchTermValue'] :'';
	$dropoff = !empty($parsResource['dropoff']) ? $parsResource['dropoff'] :!empty($searchterm) ? $searchterm :'';
	$dropoffValue = !empty($parsResource['dropoffValue']) ? $parsResource['dropoffValue'] :!empty($searchTermValue) ? $searchTermValue :'';
	
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



$showdirection = ( ! empty( $instance['showdirection'] ) ) ? esc_attr($instance['showdirection']) : '0';
if (isset($showdirectionoverride)) {
    $showdirection = $showdirectionoverride; 
}
$fixedontop= ( ! empty( $instance['fixedontop'] ) ) ? esc_attr($instance['fixedontop']) : '0';
$fixedontopcorrection= ( ! empty( $instance['fixedontopcorrection'] ) ) ? esc_attr($instance['fixedontopcorrection']) : '0';

$resultinsamepg = ( ! empty( $instance['resultinsamepg'] ) ) ? esc_attr($instance['resultinsamepg']) : '0';

$currId =  ( ! empty( $instance['currid'] ) ) ? $instance['currid'] : uniqid('currid');
if ($currId == 'REPLACE_TO_ID') { // fix for elementor
    $currId =   uniqid('currid');
}
$currModID = 'rental'. $currId;

$style = '.bfi-calendar-affixtop'.$currModID.'{'
		. 'top:'.($fixedontopcorrection + 110).'px !important; position: fixed !important;'
		. '}';

if(!empty($fixedontop)){
	// Add styles
	$style .= '.bfi-affix-top'.$currModID.'.bfiAffixTop .bfi-mod-bookingforsearch-rental{'
			. 'top: '.$fixedontopcorrection.'px !important;'
//			. '}'
//			. '.bfi-affix-top'.$currModID.'.bfiAffixTop .bfi-mod-bookingforsearch-resources {'
			. 'bottom: unset  !important;'
			. 'position: relative !important;'
			. '}';
}
	echo "<style>";
	echo $style;
	echo "</style>";

$fixedonbottom= ( ! empty( $instance['fixedonbottom'] ) ) ? ($instance['fixedonbottom']) : '0';

$showLocation = (  ! empty( $instance['showLocation'] ) ) ? esc_attr($instance['showLocation']) : '0';
$showSearchText = (  ! empty( $instance['showSearchText'] ) ) ? esc_attr($instance['showSearchText']) : '0';
$searchTextFields = '6,11,13,14,15,17,18';
if(!empty($instance['searchTextFields']) && count($instance['searchTextFields'])>0){
	$searchTextFields = implode(',', $instance['searchTextFields']) ;
}

$showAccomodations = (  ! empty( $instance['showAccomodations'] ) ) ? esc_attr($instance['showAccomodations']) : '0';

// check checkin > now
if ($checkin < new DateTime('UTC')) {
    $checkin = new DateTime('UTC');
	$checkin->modify('+3 hour');
	$checkin->setTime($checkin->format("H"), 0); 
	$checkout = clone $checkin;
    $checkout->modify($checkoutspan); 
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

if ($checkin >= $checkout){
	$checkout->modify($checkoutspan);
}


////only for Joomla
//$checkin = new JDate($checkin->format('Y-m-d')); 
//$checkout = new JDate($checkout->format('Y-m-d')); 

$duration = $checkin->diff($checkout);

$onlystay = (  ! empty( $instance['onlystay'] ) ) ? ($instance['onlystay']) : '0';

if($showSearchText) {
	$showLocation = '0';
	$showAccomodations = '0';
}

$showResource = ( ! empty( $instance['showResource'] ) ) ? ($instance['showResource']) : '0';
$minResource = 1;
$maxResource = 10;
if (!empty($instance['limitResource'])) {
	$minResource = (  ! empty( $instance['minResource'] ) ) ? ($instance['minResource']) : 1;
	$maxResource = (  ! empty( $instance['maxResource'] ) ) ? ($instance['maxResource']) : 10;
}

$showPerson = (  ! empty( $instance['showPerson'] ) ) ? esc_attr($instance['showPerson']) : '0';
$showAdult = (  ! empty( $instance['showAdult'] ) ) ? esc_attr($instance['showAdult']) : '0';
$showChildren = (  ! empty( $instance['showChildren'] ) ) ? esc_attr($instance['showChildren']) : '0';
$showSenior = (  ! empty( $instance['showSenior'] ) ) ? esc_attr($instance['showSenior']) : '0';
$showOnline = (  ! empty( $instance['showOnline'] ) ) ? esc_attr($instance['showOnline']) : '0';

$merchantCategoriesSelected = ( ! empty( $instance['merchantcategories'] ) ) ? $instance['merchantcategories'] : array();
$unitCategoriesSelected = ( ! empty( $instance['unitcategories'] ) ) ? $instance['unitcategories'] : array();
$masterTypeId = implode(',', $unitCategoriesSelected);

$instanceContext = ( ! empty( $instance['currcontext'] ) ) ? $instance['currcontext'] : uniqid('currcontext'); ;

$merchantCategoriesResource = array();
$unitCategoriesResource = array();

$listmerchantCategoriesResource = "";

$availabilityTypesSelected = ( ! empty( $instance['availabilitytypes'] ) ) ? $instance['availabilitytypes'] : array();
$itemTypesSelected = ( ! empty( $instance['itemtypes'] ) ) ? $instance['itemtypes'] : array();

$groupBySelected = ( ! empty( $instance['groupby'] ) ) ? $instance['groupby'] : [0];

$tmpMerchantCategoryIdResource = (strpos($merchantCategoryIdResource, ',') !== FALSE )?"0":$merchantCategoryIdResource;
$tmpmasterTypeId = (strpos($masterTypeId, ',') !== FALSE )?"0":$masterTypeId;
if($showAccomodations ){
	if(!empty($merchantCategoriesSelected)){
		$allMerchantCategories = BFCHelper::getMerchantCategories($language);
		$listmerchantCategoriesResource = '<option value="0">'.($showdirection?__('Tipology', 'bfi'):__('All', 'bfi')).'</option>';
		if (!empty($allMerchantCategories))
		{
			foreach($allMerchantCategories as $merchantCategory)
			{
				if(in_array($merchantCategory->MerchantCategoryId,$merchantCategoriesSelected) ){
					$merchantCategoriesResource[$merchantCategory->MerchantCategoryId] = $merchantCategory->Name;
					$listmerchantCategoriesResource .= '<option value="'.$merchantCategory->MerchantCategoryId.'" ' . ($merchantCategory->MerchantCategoryId== $tmpMerchantCategoryIdResource? 'selected':'' ).'>'.$merchantCategory->Name.'</option>';
				}
			}
		}

	}

	$listunitCategoriesResource = "";
	if(!empty($unitCategoriesSelected)) {
		$allUnitCategories =  BFCHelper::GetProductCategoryForSearch($language,1);
		if (!empty($allUnitCategories))
		{
			$listunitCategoriesResource = '<option value="0">'.($showdirection?__('Type', 'bfi'):__('All', 'bfi')).'</option>';
			foreach($allUnitCategories as $unitCategory)
			{
				if(in_array($unitCategory->ProductCategoryId,$unitCategoriesSelected)){
					$unitCategoriesResource[$unitCategory->ProductCategoryId] = $unitCategory->Name;
					$listunitCategoriesResource .= '<option value="'.$unitCategory->ProductCategoryId.'" ' . ($unitCategory->ProductCategoryId == $tmpmasterTypeId? 'selected':'' ).'>'.$unitCategory->Name.'</option>';
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

$showChildrenagesmsg = isset($_REQUEST['showmsgchildage']) ? $_REQUEST['showmsgchildage'] : 0;
if ((empty(COM_BOOKINGFORCONNECTOR_ISMOBILE ) &&!$showdirection )|| BFI()->isSearchPage()) {
	$fixedonbottom = 0;    
}
if (!empty( $before_widget) ){
	echo $before_widget;
}
// Check if title is set
if (!empty( $title) ) {
//  echo $before_title . $title . $after_title;
}

?>
<div class="bfi-searchwidget bfi-affix-top<?php echo $currModID ?> <?php echo ( ! empty( $fixedonbottom ) ) ? 'bfiAffixBottom' : '' ?>" data-affixbottom="<?php echo ( ! empty( $fixedonbottom ) ) ? '1' : '0' ?> <?php echo $customclass ?>"><!-- per span8 e padding -->
<div class="bfi-mod-bookingforsearch-rental <?php echo $showdirection?"bfi-bookingforsearch-rental-horizontal":""; ?>" id="bfisearch<?php echo $currModID ?>" 
	data-currmodid="<?php echo $currModID;?>"
	data-nch="<?php echo $nch;?>"
	data-showChildrenagesmsg="<?php echo $showChildrenagesmsg;?>"
	data-fixedontop="<?php echo $fixedontop;?>"
	data-fixedontopcorrection="<?php echo $fixedontopcorrection;?>"
	>
    <div >
<?php
$totalfields=0;
	$samelocation = ($searchterm == $dropoff) ;
?>
        <div id="bfisearchtab<?php echo $currModID ?>" style="display:<?php echo (COM_BOOKINGFORCONNECTOR_ISMOBILE) ?"none":""; ?>;">
		<form action="<?php echo $url_page_Resources ?>" method="get" id="searchform<?php echo $currModID ?>" class="bfi-form-default bfi-form-default-resources bfi-dateform-container bfi-form-<?php echo $showdirection?"horizontal":"vertical"; ?> " 
		data-blockdays="<?php echo $blockdays;?>"
		data-blockmonths="<?php echo $blockmonths;?>"
		data-currmodid="<?php echo $currModID;?>"
		data-showdirection="<?php echo $showdirection;?>"
		data-fixedontop="<?php echo $fixedontop;?>"
		data-showsearchtext="<?php echo $showSearchText;?>"
		data-startdatetimerange="<?php echo $startDateTimeRange;?>"
		data-enddatetimerange="<?php echo $endDateTimeRange;?>"
		data-defaultaction="<?php echo $url_page_Resources ?>"
		>
				<div class="bfi-row">
						<div class="bfi-show-returnsamelocation bfi-col-sm-12 bfi-text-left">
							<input type="radio" class="bfi-returnsamelocation-widget" name="bfi-returnsamelocation" <?php echo ($samelocation) ?"checked":""; ?> value="1"/><label><?php echo _e("Return to the same location",'bfi') ?> </label>
<?php if(!$showdirection) { ?><br>
<?php } ?>
							<input type="radio" class="bfi-returnsamelocation-widget" name="bfi-returnsamelocation" <?php echo ($samelocation) ?"":"checked"; ?> value="2"/><label><?php echo _e("Drop off in another location",'bfi') ?> </label>
						</div>
				</div>
			
			<div class="bfi-row">
				<?php if($showSearchText) { 
					$totalfields +=2;
				?>
					<div class="bfi_destination bfi-col-sm-2">
						<!-- <label><?php _e('Search text', 'bfi') ?></label> -->
						<input type="text" id="searchtext<?php echo $currModID ?>" name="searchterm" value="<?php echo $searchterm ?>" class="bfi-pickup bfi-inputtext bfi-autocomplete" placeholder="<?php _e('Pick-up location', 'bfi') ?>..." data-scope="<?php echo $searchTextFields ?>" inputmode="search" />
						<input type="text" id="searchtextend<?php echo $currModID ?>" name="dropoff" value="<?php echo $dropoff ?>" class="bfi-dropoff bfi-inputtext bfi-autocomplete" placeholder="<?php _e('Drop-off location', 'bfi') ?>..." data-scope="<?php echo $searchTextFields ?>" inputmode="search" style="display:<?php echo ($searchterm == $dropoff ) ?"none":"";
						 ?>"/>
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
				<?php
					$totalfields +=4;
				?>
				<div class="bfi-showdaterange bfi-col-sm-<?php echo $showdirection?"4":"2"; ?> bfidaterangepicker" data-checkin="<?php echo $checkin->format('d/m/Y H:i') ?>" data-checkout="<?php echo $checkout->format('d/m/Y H:i') ?>">
					<div class="bfi-row">
						<div class="bfidaterangepicker-container bfidaterangepicker-checkin bfi-col-sm-6">
							<i class="fa fa-calendar"></i>&nbsp;
							<span><?php echo date_i18n('D',$checkin->getTimestamp()) ?> <?php echo $checkin->format("d") ?> <?php echo date_i18n('M',$checkin->getTimestamp()).' '. $checkin->format(', H:i') ?></span>
						</div>
						<div class="bfidaterangepicker-container bfidaterangepicker-checkout bfi-col-sm-6">
							<i class="fa fa-calendar"></i>&nbsp;
							<span><?php echo date_i18n('D',$checkout->getTimestamp()) ?> <?php echo $checkout->format("d") ?> <?php echo date_i18n('M',$checkout->getTimestamp()).' '. $checkout->format(', H:i') ?></span>
						</div>
					</div>
				</div>
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
			<input type="hidden" name="persons" value="<?php echo $nad + $nse + $nch?>" id="searchformpersons<?php echo $currModID ?>">
			<input type="hidden" name="adults" value="<?php echo $nad?>" id="searchformpersonsadult<?php echo $currModID ?>">
			<input type="hidden" name="seniores" value="<?php echo $nse?>" id="searchformpersonssenior<?php echo $currModID ?>">
			<input type="hidden" name="children" value="<?php echo $nch?>" id="searchformpersonschild<?php echo $currModID ?>">
			<input type="hidden" name="childages1" value="<?php echo $nchs[0]?>" id="searchformpersonschild1<?php echo $currModID ?>">
			<input type="hidden" name="childages2" value="<?php echo $nchs[1]?>" id="searchformpersonschild2<?php echo $currModID ?>">
			<input type="hidden" name="childages3" value="<?php echo $nchs[2]?>" id="searchformpersonschild3<?php echo $currModID ?>">
			<input type="hidden" name="childages4" value="<?php echo $nchs[3]?>" id="searchformpersonschild4<?php echo $currModID ?>">
			<input type="hidden" name="childages5" value="<?php echo $nchs[4]?>" id="searchformpersonschild5<?php echo $currModID ?>">

			<input type="hidden" name="checkin" value="<?php echo $checkin->format('d/m/Y'); ?>" />
			<input type="hidden" name="checkout" value="<?php echo $checkout->format('d/m/Y'); ?>" />
			<input name="checkAvailability" type="hidden" value="1" />
			<input name="checkStays" type="hidden" value="1" />
			<input type="hidden" name="checkintime" value="<?php echo $checkin->format('H:i') ?>" />
			<input type="hidden" name="checkouttime" value="<?php echo $checkout->format('H:i'); ?>" />
			
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

			<input type="hidden" value="<?php echo $masterTypeId ?>" name="filters[productcategory]" />
			<input type="hidden" value="<?php echo $searchTermValue ?>" name="searchTermValue" />
			<input type="hidden" value="1" name="newsearch" />
			<input type="hidden" value="0" name="limitstart" />
			<input type="hidden" name="filter_order" value="<?php echo $currFilterOrder ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $currFilterOrderDirection ?>" />
			<input type="hidden" value="<?php echo $language ?>" name="cultureCode" />
			<input type="hidden" value="<?php echo $points ?>" name="points" id="points<?php echo $currModID ?>" />
			<input type="hidden" value="0" name="showmsgchildage" id="showmsgchildage<?php echo $currModID ?>"/>
			<div class="bfi-hide" id="bfi_childrenagesmsg<?php echo $currModID ?>">
				<div style="line-height:0; height:0;"></div>
				<div class="bfi-pull-right" style="cursor:pointer;color:red">&nbsp;<i class="fa fa-times-circle" aria-hidden="true" onclick="jQuery('#bfi_lblchildrenages<?php echo $currModID ?>').webuiPopover('destroy');"></i></div>
				<?php echo sprintf(__('We preset your children\'s ages to %s years old - but if you enter their actual ages, you might be able to find a better price.', 'bfi'),COM_BOOKINGFORCONNECTOR_CHILDRENSAGE) ?>
			</div>
			<input type="hidden" name="availabilitytype" value="<?php echo implode(',', $availabilityTypesSelected)  ?>" />
			<input type="hidden" name="itemtypes"  value="<?php echo implode(',', $itemTypesSelected)  ?>" />
			<input type="hidden" name="groupresulttype"value="<?php echo implode(',', $groupBySelected) ?>" />
			<input type="hidden" name="getallresults" class="getallresultshd" value="<?php echo $showOnline; ?>" id="hdgetallresults<?php echo $currModID; ?>" />
			<input type="hidden" name="cultureCode" value="<?php echo $language ?>" />
			<input type="hidden" name="checkFullPeriod" value="1" />
			<input type="hidden" name="resultinsamepg" value="<?php echo $resultinsamepg ?>" />
			<input type="hidden" name="resview" value="rental" />
			<input type="hidden" name="dropoffValue" value="<?php echo $dropoffValue ?>" />
			<input type="hidden" name="dateselected" value="1" />
			<input type="hidden" value="<?php echo $widgettoshow ?>" name="widgettoshow" />
			
		</form>
				   
        </div>
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