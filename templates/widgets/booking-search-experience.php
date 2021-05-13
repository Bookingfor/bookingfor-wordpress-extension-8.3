<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

	if (COM_BOOKINGFORCONNECTOR_ISBOT) {
		return '';
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
	$currModID = uniqid('bfisearchexperience');
	// get searchresult page...
	$searchAvailability_page = get_post( bfi_get_page_id( 'searchavailability' ) );
	$resultpageidDefault = $searchAvailability_page->ID;
	$resultpageid = bfi_get_translated_page_id(( ! empty( $instance['resultpageid'] ) ) ? esc_attr($instance['resultpageid']) : $resultpageidDefault);
	$url_page_Resources = get_permalink( $resultpageid );


	$customclass="";
	if (!empty($instance['classes'])) {
		$customclass=$instance['classes'];
	}
	if (!empty($instance['g5_classes'])) {
		$customclass=$instance['g5_classes'];
	}

	$sessionkeysearch = 'search.params.experience';
$parsResource = BFCHelper::getSearchParamsSession($sessionkeysearch );

if(BFI()->isSearchPage()){
	bfi_setSessionFromSubmittedData($sessionkeysearch );
}
$parsResource = BFCHelper::getSearchParamsSession($sessionkeysearch );

$stateIds = "";
$regionIds = "";
$cityIds = "";
$zoneIds = '';
$categoryIds = '';
$tagids = '';
$merchantIds = "";
$searchterm = '';
$searchTermValue = '';
$zoneIdsSplitted = array();
$merchantTagIds = '';
$groupresourceIds = "";
$productTagIds = '';
$merchantCategoryId = '';

$widgettoshow =  ( ! empty( $instance['widgettoshow'] ) ) ? $instance['widgettoshow'] : '';


$defaultdurationSelected =  ( ! empty( $instance['defaultduration'] ) ) ? $instance['defaultduration'] : 0;
//$checkoutspan = '+1 day';
$checkoutspan = '+'.$defaultdurationSelected.' day';
$checkin = new DateTime('UTC');
$checkout = new DateTime('UTC');
$paxes = 0;
$paxages = array();

$checkinId = uniqid('checkin');
$checkoutId = uniqid('checkout');
$categoryIdResource = 0;
$merchantCategoryIdResource = 0;
$masterTypeId = 0;
$bookableonly = 0;
//$showdirection =0;
$showdirection = ( ! empty( $instance['showdirection'] ) ) ? esc_attr($instance['showdirection']) : '0';
$showDateRange = (! empty( $instance['showDateRange'] ) ) ? esc_attr($instance['showDateRange']) : '1';
$showDateTimeRange = (! empty( $instance['showDateTimeRange'] ) ) ? esc_attr($instance['showDateTimeRange']) : '0';
$startDateTimeRange = ( ! empty( $instance['startDateTimeRange'] ) ) ? ($instance['startDateTimeRange']) : '00:00';
$endDateTimeRange = ( ! empty( $instance['endDateTimeRange'] ) ) ? ($instance['endDateTimeRange']) : '24:00';
$fixedontop= ( ! empty( $instance['fixedontop'] ) ) ? esc_attr($instance['fixedontop']) : '0';
$fixedontopcorrection= ( ! empty( $instance['fixedontopcorrection'] ) ) ? esc_attr($instance['fixedontopcorrection']) : '0';
$showSearchText = (! empty( $instance['showSearchText'] ) ) ? esc_attr($instance['showSearchText']) : '0';
$searchTextFields = '6,11,13,14,15,17,18';
$groupBySelected = ( ! empty( $instance['groupby'] ) ) ? $instance['groupby'] : [0];
$showLocation = (  ! empty( $instance['showLocation'] ) ) ? esc_attr($instance['showLocation']) : '0';
$showAccomodations = (  ! empty( $instance['showAccomodations'] ) ) ? esc_attr($instance['showAccomodations']) : '0';
$availabilitytype = isset($instance['availabilitytypes']) ? $instance['availabilitytypes'] : array(1,3);

if(!empty($instance['searchTextFields']) && count($instance['searchTextFields'])>0){
	$searchTextFields = implode(',', $instance['searchTextFields']) ;
}
$showCheckin = true;
$dateselected = 0;
//sospeso
$dateselected = 1;
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

if (!empty($parsResource)){
	//per ricerca senza date
	$checkin = !empty($parsResource['checkin']) ? $parsResource['checkin'] : new DateTime('UTC');
//	$checkin = !empty($parsResource['checkin']) ? $parsResource['checkin'] : null;
	if (!empty($checkin)) {
		$checkout = !empty($parsResource['checkout']) ? $parsResource['checkout'] : new DateTime('UTC');
		if (empty($parsResource['checkout'])){
			$checkout->modify($checkoutspan);
		}
	}else{
		$showCheckin = false;
		$checkin = new DateTime('UTC');
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
//	$paxes = !empty($parsResource['paxes']) ? $parsResource['paxes'] : 2;
//	$paxages = !empty($parsResource['paxages'])? $parsResource['paxages'] :  array('18','18');
	$paxes = !empty($parsResource['paxes']) ? $parsResource['paxes'] : 0;
	$paxages = !empty($parsResource['paxages'])? $parsResource['paxages'] :  array();
	$merchantCategoryIdResource = !empty($parsResource['merchantCategoryId'])? $parsResource['merchantCategoryId']: 0;
	$masterTypeId = !empty($parsResource['masterTypeId'])? $parsResource['masterTypeId']: 0;
	$dateselected = !empty($parsResource['dateselected'])? $parsResource['dateselected']: 0;

	$groupresourceIds = isset($parsResource['groupresourceIds']) ? $parsResource['groupresourceIds']: "";
	$merchantTagIds = !empty($parsResource['merchantTagIds']) ? $parsResource['merchantTagIds']: "";
	$productTagIds = !empty($parsResource['productTagIds']) ? $parsResource['productTagIds']: "";
	$merchantCategoryId = !empty($parsResource['merchantCategoryId']) ? $parsResource['merchantCategoryId']: "";
}


$merchantCategoriesSelected = ( ! empty( $instance['merchantcategories'] ) ) ? $instance['merchantcategories'] : array();
$unitCategoriesSelected = ( ! empty( $instance['unitcategories'] ) ) ? $instance['unitcategories'] : array();
//$masterTypeId = implode(',', $unitCategoriesSelected);

$merchantCategoriesResource = array();
$unitCategoriesResource = array();

$listmerchantCategoriesResource = "";
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
			
			$listunitCategoriesResource = '<option value="' . implode(',',$unitCategoriesSelected) .'">'.($showdirection?__('Type', 'bfi'):__('All', 'bfi')).'</option>';
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

//if ($checkin == $checkout){
//	$checkout->modify($checkoutspan);
//}

$showPerson = (  ! empty( $instance['showPerson'] ) ) ? esc_attr($instance['showPerson']) : '0';
$showAdult = (  ! empty( $instance['showAdult'] ) ) ? esc_attr($instance['showAdult']) : '0';
$showChildren = (  ! empty( $instance['showChildren'] ) ) ? esc_attr($instance['showChildren']) : '0';
$showSenior = (  ! empty( $instance['showSenior'] ) ) ? esc_attr($instance['showSenior']) : '0';
$showOnline = (  ! empty( $instance['showOnline'] ) ) ? esc_attr($instance['showOnline']) : '0';
$nad = 0;
$nch = 0;
$nse = 0;
$countPaxes = 0;
$maxchildrenAge = (int)BFCHelper::$defaultAdultsAge-1;

$nchs = array(null,null,null,null,null,null);
$fistSelect = false;
if (empty($paxages)){
	$fistSelect = true;
//	$nad = 2;
//	$paxages = array(BFCHelper::$defaultAdultsAge, BFCHelper::$defaultAdultsAge);
	$nad = 0;
	$paxages = array();

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
//	  echo (!empty($before_title) ?$before_title:"") . $title . (!empty($after_title) ?$after_title:"");
//}

$fixedonbottom= ( ! empty( $instance['fixedonbottom'] ) ) ? ($instance['fixedonbottom']) : '0';
if (!empty(COM_BOOKINGFORCONNECTOR_ISMOBILE )) {
//	$fixedonbottom = 1;    
}
$resultinsamepg = ( ! empty( $instance['resultinsamepg'] ) ) ? esc_attr($instance['resultinsamepg']) : '0';
$currId =  ( ! empty( $instance['currid'] ) ) ? $instance['currid'] : uniqid('currid');
if ($currId == 'REPLACE_TO_ID') { // fix for elementor
    $currId =   uniqid('currid');
}
$currModID = 'experience'. $currId;
$totalfields=0;

?>
	<div class="bfi-mod-bookingforsearch-experience <?php echo ( ! empty( $fixedonbottom ) ) ? 'bfiAffixBottom' : '' ?> <?php echo $customclass ?>" id="bfisearchexperience<?php echo $currModID ?>" >
			<form action="<?php echo $url_page_Resources ?>" method="get"" method="get" id="searchformexperience<?php echo $currModID ?>" class="bfi-form-experience bfi-form-default bfi-dateform-container bfi-form-<?php echo $showdirection?"horizontal":"vertical"; ?> " 
				data-blockdays="<?php echo $blockdays;?>"
				data-blockmonths="<?php echo $blockmonths;?>"
				data-currmodid="<?php echo $currModID;?>"
				data-showdaterange="<?php echo $showDateRange;?>"
				data-showdirection="<?php echo $showdirection;?>"
				data-fixedontop="<?php echo $fixedontop;?>"
				data-showsearchtext="<?php echo $showSearchText;?>"
				data-defaultduration="<?php echo $defaultdurationSelected;?>"
			>
				<div class="bfi-row">
				<?php if($showSearchText) { 
					$totalfields +=2;
				?>
					<div class="bfi_destination bfi-col-sm-2">
						<label><?php _e('Search text', 'bfi') ?></label>
						<input type="text" id="searchtext<?php echo $currModID ?>" name="searchterm" value="<?php echo $searchterm ?>" class="bfi-inputtext bfi-autocomplete" placeholder="<?php _e('Search experiences and areas...', 'bfi') ?>" data-scope="<?php echo $searchTextFields ?>" inputmode="search" data-itemtypeid="<?php echo bfi_ItemType::Experience ?>" />
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
					<div class="bfi_merchantcategoriesresource bfi_destination bfi-col-sm-2">
						<label><?php _e('Tipology', 'bfi') ?></label>
						<select id="merchantCategoryId<?php echo $currModID ?>" name="merchantCategoryId" class="hideRent">
							<?php echo $listmerchantCategoriesResource; ?>
						</select>
					</div>
				<?php } //$showAccomodations ?>
				<?php if(!empty($listunitCategoriesResource) && $showAccomodations){  
					$totalfields +=2;
				?>
					<div class="bfi_unitcategoriesresource bfi_destination bfi-col-sm-2">
						<label><?php _e('Type', 'bfi') ?></label>
						<select id="masterTypeId<?php echo $currModID ?>" name="masterTypeId" class="">
							<?php echo $listunitCategoriesResource; ?>
						</select>
					</div>
				<?php } //$showAccomodations ?>
				<?php
					$totalfields +=2;
				?>
				<div class="bfi-showdaterangenew bfi-col-sm-2">
					<label><?php _e('Date', 'bfi') ?></label>
					<div class="t-datepicker" data-checkin="<?php echo $checkin->format('Y-m-d'); ?>" data-checkout="<?php echo $checkout->format('Y-m-d'); ?>">
						<div class="t-check-in"></div>
						<div class="t-check-out"></div>
					</div>
				</div>
				<div class="bfi-showdaterange bfi-col-sm-2 bfidaterangepicker bfidaterangepicker-container" data-checkin="<?php echo $checkin->format('d/m/Y H:i') ?>" data-checkout="<?php echo $checkout->format('d/m/Y H:i') ?>">
					<label><?php _e('Date', 'bfi') ?></label>
					<div class="bfi-showdaterangecontainer">	
						<i class="fa fa-calendar"></i>&nbsp;
						<span class=" bfidaterangepicker-checkin">
							<span><!-- <?php echo date_i18n('D',$checkin->getTimestamp()) ?>  --><?php echo $checkin->format("d") ?> <?php echo date_i18n('M',$checkin->getTimestamp()) . (empty($showDateTimeRange)? '' :' '. $checkin->format(', H:i')) ?></span>
						</span>
						<span class="bfidaterangepicker-checkout">
							-
							<span><!-- <?php echo date_i18n('D',$checkout->getTimestamp()) ?>  --><?php echo $checkout->format("d") ?> <?php echo date_i18n('M',$checkout->getTimestamp()) .  (empty($showDateTimeRange)? '' : ' '. $checkout->format(', H:i')) ?></span>
						</span>
						<span class="bfi-hide"><span class="bfi-datepicker-clear">X</span></span>
					</div>
				</div>
<?php 
if ($showPerson) {
	$totalfields +=2;

?>
					<div class="bfi_destination bfi-col-sm-2">
						<label><?php _e('Persons', 'bfi'); ?></label>
						<select id="bfi-adult<?php echo $currModID ?>" name="adultssel" onchange="bfi_quoteChanged('<?php echo $currModID ?>')" class="" style="display:inline-block !important;">
							<option value="2" <?php echo ($nad == 0)?"selected":""; ?>><?php _e('Persons', 'bfi') ?></option>';
							<?php
							foreach (range(1, 25) as $number) {
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
					<label><?php _e('Persons', 'bfi'); ?></label>
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
					<div class="bfi-searchbutton-wrapper bfi-col-sm-<?php echo $showdirection? $widthbtn:"2";?>" id="divBtnResource<?php echo $currModID ?>">
						<a class="bfi-btnsendform bfi-btn " href="javascript: void(0);"><i class="fa fa-search" aria-hidden="true"></i> <?php _e('Search', 'bfi') ?></a>
					</div>
				</div>
				<div class="bfi-clearfix"></div>
				<div class="bfi-powered"><a href="https://www.bookingfor.com" target="_blank">Powered by Bookingfor</a></div>
				<input type="hidden" value="1" name="newsearch" />
				<input type="hidden" value="<?php echo uniqid('', true)?>" name="searchid" />
				<input type="hidden" value="<?php echo $searchTermValue ?>" name="searchTermValue" />
				<input type="hidden" value="" name="searchtermoverride" />
				<input type="hidden" name="filter_order" value="" />
				<input type="hidden" name="filter_order_Dir" value="" />
				<input type="hidden" name="groupresulttype"value="<?php echo implode(',', $groupBySelected) ?>" />
				<input type="hidden" name="availabilitytype" value="<?php echo implode(',', $availabilitytype)  ?>" />
				<input type="hidden" name="itemtypes" value="<?php echo bfi_ItemType::Experience ?>" />
				<input type="hidden" name="getallresults" value="0" />
				<input type="hidden" name="checkFullPeriod" value="0" />
				<input type="hidden" name="resview" value="experience" />
				<input type="hidden" name="resultinsamepg" value="<?php echo $resultinsamepg ?>" />
				<input type="hidden" name="persons" value="<?php echo ($showAdult) ? $nad + $nse + $nch :0 ;  ?>" id="searchformpersons<?php echo $currModID ?>">
				<input type="hidden" name="adults" value="<?php echo ($showAdult) ? $nad :0?>" id="searchformpersonsadult<?php echo $currModID ?>">
				<input type="hidden" name="seniores" value="<?php echo $nse?>" id="searchformpersonssenior<?php echo $currModID ?>">
				<input type="hidden" name="children" value="<?php echo $nch?>" id="searchformpersonschild<?php echo $currModID ?>">
				<input type="hidden" name="childages1" value="<?php echo $nchs[0]?>" id="searchformpersonschild1<?php echo $currModID ?>">
				<input type="hidden" name="childages2" value="<?php echo $nchs[1]?>" id="searchformpersonschild2<?php echo $currModID ?>">
				<input type="hidden" name="childages3" value="<?php echo $nchs[2]?>" id="searchformpersonschild3<?php echo $currModID ?>">
				<input type="hidden" name="childages4" value="<?php echo $nchs[3]?>" id="searchformpersonschild4<?php echo $currModID ?>">
				<input type="hidden" name="childages5" value="<?php echo $nchs[4]?>" id="searchformpersonschild5<?php echo $currModID ?>">
			<?php if($showSearchText) { ?>
				<input type="hidden" value="<?php echo $stateIds ?>" name="stateIds" />
				<input type="hidden" value="<?php echo $regionIds ?>" name="regionIds" />
				<input type="hidden" value="<?php echo $cityIds ?>" name="cityIds" />
				<input type="hidden" value="<?php echo $merchantIds ?>" name="merchantIds" />
				<input type="hidden" value="<?php echo $groupresourceIds ?>" name="groupresourceIds" />
				<input type="hidden" value="<?php echo $merchantTagIds ?>" name="merchantTagIds" />
				<input type="hidden" value="<?php echo $productTagIds ?>" name="productTagIds" />
				<input type="hidden" value="<?php echo $zoneIds ?>" name="zoneIds" />
			<?php } ?>
			<?php if(!$showAccomodations) { ?>
				<input type="hidden" name="merchantCategoryId" value="<?php echo implode(',',$merchantCategoriesSelected) ?>" />
				<input type="hidden" name="masterTypeId" value="<?php echo implode(',',$unitCategoriesSelected) ?>" />
			<?php } ?>
			<input type="hidden" name="checkin" value="<?php echo $checkin->format('d/m/Y'); ?>" />
			<input type="hidden" name="checkout" value="<?php echo $checkout->format('d/m/Y'); ?>" />
			<input name="checkAvailability" type="hidden" value="1" />
			<input name="checkStays" type="hidden" value="1" />
			<input type="hidden" name="dateselected" value="<?php echo $dateselected ?>" />
			<input type="hidden" value="<?php echo $widgettoshow ?>" name="widgettoshow" />

			</form>
			<div class="bficontainerviewform">
				<a class="bfiviewform" rel="bfi-form-experience"><i class="fa fa-search"></i> <?php _e('Search', 'bfi') ?> </a>
			</div>
</div>
<?php 
if (!empty($after_widget)) {
	echo $after_widget;
    }
?>