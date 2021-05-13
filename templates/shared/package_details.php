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
$resetCart = 0;
$ProductAvailabilityType = 1;
$checkInDates = '';
$hideRateplanOver = 5;

//stile letti
wp_enqueue_style('bfiicomoon');

$isportal = COM_BOOKINGFORCONNECTOR_ISPORTAL;
$usessl = COM_BOOKINGFORCONNECTOR_USESSL;

$fromSearch =  BFCHelper::getVar('fromsearch','0');
$makesearch =  BFCHelper::getVar('refreshcalc','0');

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
$defaultAdultsAge = BFCHelper::$defaultAdultsAge;
$defaultSenioresAge = BFCHelper::$defaultSenioresAge;
//$useSeniores= isset($_REQUEST['seniores']);
$useSeniores = 0;

// controllo se sono passate fasce di età nel merchant, allora sovrascrivo i dati
if (!empty($merchant->PaxRangesString)) {
	$paxRanges = json_decode($merchant->PaxRangesString);	

	$fullPaxs =  array_values(array_filter($paxRanges, function($ages) {
			return $ages->FullPax;
		}));
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


$cartdetails_page = get_post( bfi_get_page_id( 'cartdetails' ) );
$url_cart_page = get_permalink( $cartdetails_page->ID );

$merchantdetails_page = get_post( bfi_get_page_id( 'merchantdetails' ) );
$url_merchant_page = get_permalink( $merchantdetails_page->ID );
$routeMerchant = $url_merchant_page . $merchant->MerchantId.'-'.BFI()->seoUrl($merchant->Name);
$routeInfoRequest = $routeMerchant . '/contact';

$accommodationdetails_page = get_post( bfi_get_page_id( 'accommodationdetails' ) );
$url_resource_page = get_permalink( $accommodationdetails_page->ID );

$resourceName = "";

$uri = $url_resource_page;
$currUriresource  = $uri;
$searchAvailability_page = get_post( bfi_get_page_id( 'searchavailability' ) );
$formRoute = get_permalink( $searchAvailability_page->ID );
$formMethod = "GET";
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

if($usessl){
	$url_cart_page = str_replace( 'http:', 'https:', $url_cart_page );
}

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

$checkout = new DateTime('UTC');

$paxes = 2;
$paxages = array();

$selectablePrices ='';
$minqt = 1;
$maxqt = 10;
$minrooms = 1;
$maxrooms = 10;

if (!empty($pars)){
//	$checkin = !empty($pars['checkin']) ? $pars['checkin'] : new DateTime('UTC');
	$checkout = !empty($pars['checkout']) ? $pars['checkout'] : new DateTime('UTC');

	if (!empty($pars['paxes'])) {
		$paxes = $pars['paxes'];
	}
	if (!empty($pars['paxages'])) {
		$paxages = $pars['paxages'];
	}
	if (empty($pars['checkout'])){
		$checkout->modify($checkoutspan); 
	}
	$minqt = !empty($pars['minqt']) ? $pars['minqt'] : 1;
	$maxqt = !empty($pars['maxqt']) ? $pars['maxqt'] : 10;
	$minrooms = !empty($pars['minrooms']) ? $pars['minrooms'] : 1;
	$maxrooms = !empty($pars['maxrooms']) ? $pars['maxrooms'] : 10;

}

$availabilityTypes = BFCHelper::getVar('availabilityTypes','0');
$itemTypeIds = BFCHelper::getVar('itemTypeIds',1);

$pckpaxages = BFCHelper::getStayParam('pckpaxages');

//echo "<pre>pckpaxages: ";
//echo $pckpaxages;
//echo "</pre>";

$checkout->setTime(0,0,0);
$checkin->setTime(0,0,0);


$nad = 0;
$nch = 0;
$nse = 0;
$countPaxes = 0;
$maxchildrenAge = (int)$defaultAdultsAge-1;

$AvailabilityTimePeriod = array();
$minuteStart = 0;
$minuteEnd = 24*60;
$timeLength = 0;

$dateStringCheckin =  $checkin->format('d/m/Y');
$dateStringCheckout =  $checkout->format('d/m/Y');

$totalPerson = $nad + $nch + $nse;

$allStaysToView = array();


$alternativeDateToSearch = clone $startDate;
if ($checkin > $alternativeDateToSearch)
{
	$alternativeDateToSearch = clone $checkin;
}

$allRatePlans = $currPackage->Results;
$firstPackage = $allRatePlans;

$tmpSearchModel = new stdClass;
$tmpSearchModel->FromDate = $checkin;
$tmpSearchModel->ToDate = $checkout;
$groupprice = 0;
$grouptotalprice = 0;
$groupAvailabilityType = 0;

foreach($allRatePlans as $p) {
	$groupprice += $p->Price;
	$grouptotalprice += $p->TotalPrice;
	$checkin = BFCHelper::parseStringDateTime($p->RatePlan->CheckIn);
	$checkout = BFCHelper::parseStringDateTime($p->RatePlan->CheckOut);
}

$currDiff = $checkout->diff($checkin);
$duration = $currDiff->d;

$IsBookable = in_array(true, array_map(function ($t) { return $t->IsBookable; }, $allRatePlans));
$currIsBookable = $IsBookable;

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

$merchantCategoryId = $merchant->MerchantTypeId;
$masterTypeId = '';
$merchantIds = $merchant->MerchantId;
$stateIds = '';
$regionIds = '';
$cityIds = '';
$newsearch = 0;

?>
<div >

<script type="text/javascript">
    var bfi_MaxQtSelectable = <?php echo COM_BOOKINGFORCONNECTOR_MAXQTSELECTABLE ?>;
    var pricesExtraIncluded=[];	
	var servicesAvailability=[];
</script>
<br />
<!-- RESULT -->	
<?php 

$resCount = 0;
$totalResCount = count((array)$allRatePlans);	

$loadScriptTimeSlot = false;
$loadScriptTimePeriod = false;

$allResourceId = array();
$allServiceIds = array();
$allSelectablePrices = array();
$allTimeSlotResourceId = array();
$allTimePeriodResourceId = array();

if(is_array($allRatePlans) && $totalResCount>0){
	$allResourceId = array_unique(array_map(function ($i) { return $i->ResourceId; }, $allRatePlans));
}

$minrooms = $totalResCount;
$paxesRes = array();
foreach($allRatePlans as $currKey=>$currRateplan) {
					$nadult =0;
					$nsenior =0;
					$nchild =0;
					$nchs = array();
					if(!empty( $currRateplan->RatePlan->SuggestedStay ) && !empty( $currRateplan->RatePlan->SuggestedStay->Paxes )){
						
//						echo "<pre>";
//						echo $currRateplan->RatePlan->SuggestedStay->Paxes;
//						echo "</pre>";
						
						$computedPaxes = explode("|", $currRateplan->RatePlan->SuggestedStay->Paxes);
						foreach($computedPaxes as $computedPax) {
							$currComputedPax =  explode(":", $computedPax."::::");
							
							if (($currComputedPax[2] >= (int)$defaultAdultsAge) && ($currComputedPax[2] < (int)$defaultSenioresAge )) {
								$nadult += $currComputedPax[1];
								$nad += $currComputedPax[1];
							}
							if ($currComputedPax[2] >= (int)$defaultSenioresAge ) {
								if ($useSeniores) {
									$nsenior += $currComputedPax[1];
									$nse += $currComputedPax[1];
								}else{
									$nadult += $currComputedPax[1];
									$nad += $currComputedPax[1];
								}
							}
							if ($currComputedPax[2] < (int)$defaultAdultsAge ) {
								$nchild += $currComputedPax[1];
								$nch += $currComputedPax[1];
								array_push($nchs,$currComputedPax[2]);
							}
						}

					}
					$paxesRes[$currKey] = array(
						'nadult' =>$nadult,
						'nsenior' =>$nsenior,
						'nchild' =>$nchild,
						'childages' =>$nchs,
					);
					
}

$track = array($merchantCategoryId,$masterTypeId,$checkin->format('d/m/Y'),$checkout->format('d/m/Y'),$nad,$nse,$nch,implode(',',$nchs),$ProductAvailabilityType,$totalResCount,$merchantIds,$stateIds,$regionIds,$cityIds);
$trackstr = implode('|',$track);
if(strlen($trackstr) > 500){
	$trackstr = substr($trackstr, 0, 500);
}
$currModID = uniqid('bfipackage');
?>


<div class="bfi-clearfix"></div>

<div class="bfi-group-recommendation bfi-table-responsive">
	<div class="bfi-group-recommendation-title bf-title-book bfi-border ">
		<?php _e('Recommended for', 'bfi') ?> <?php echo ($nad + $nse) ?> <?php _e('Adults', 'bfi') ?> 
		<?php if($nch>0) { ?>
			e <?php echo $nch ?> <?php _e('Children', 'bfi') ?>
		<?php } ?>
		<a href="javascript:bfishowsearchPck()" class="bfi-btn bfi-alternative bfi-request-now"><?php _e('Change the search details', 'bfi') ?></a>
	</div>
<form id="bfi-package" action="<?php echo $formRoute?>" method="<?php echo $formMethod?>" class="bfi_resource-calculatorForm bfi_resource-calculatorTable " style="display:none">
	<input name="onlystay" type="hidden" value="1" />
	<input name="newsearch" type="hidden" value="1" />
	<input name="calculate" type="hidden" value="true" />
	<input type="hidden" name="persons" value="<?php echo $nad + $nse + $nch?>" />
	
	<div class="bfi-row"><!-- showresource -->
		<div class="bfi-col-md-2 "><?php _e('Resource', 'bfi') ?></div>
		<div class="bfi-col-md-3 bfi-col-xs-5 ">
			<select id="bfi-minrooms<?php echo $currModID ?>" name="minrooms" class="" onchange="bfiQuoteQtPckChanged();">
				<?php
				foreach (range(1, 10) as $number) {
					?> <option value="<?php echo $number ?>" <?php echo ($minrooms == $number)?"selected":""; ?>><?php echo $number ?></option><?php
				}
				?>
			</select>
		</div>
	</div>
<?php 
$checkinId = uniqid('checkincalculator');
$checkoutId = uniqid('checkoutcalculator');
?>
		
		<div class="bfi-row ">
			<div class="bfi-col-md-6 bfi-col-xs-6 bfi-checkin-field-container" id="calcheckin">      
				<label><?php echo _e('Check-in','bfi') ?></label>
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
				<label><?php echo _e('Check-out ','bfi') ?></label>
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
<?php 
foreach(range(1, 10) as $number) {
					$nadult = 1;
					$nsenior = 0;
					$nchild = 0;
					$nchs = array();
					if(!empty( $paxesRes[$number-1])){
								$nadult = $paxesRes[$number-1]['nadult'];
								$nsenior = $paxesRes[$number-1]['nsenior'];
								$nchild = $paxesRes[$number-1]['nchild'];
								$nchs = $paxesRes[$number-1]['childages'];
					}
					array_push($nchs,null,null,null,null,null,null);


?>
<div class="bfi_paxes_package bfi-row" id="bfi_paxes_package_<?php echo $number ?>">
	<div class="bfi-col-md-2 "><?php _e('Resource', 'bfi'); ?> <?php echo $number ?></div>
	<div class="bfi-col-md-10 ">
				<div class="bfi-row">
					<div class="bfi-col-md-3 bfi-col-xs-4 bfi_resource-package-adult">
						<label><?php _e('Adults', 'bfi'); ?> (<?php echo $defaultAdultsAge ?>+):</label>
						<select name="adultssel-room<?php echo $number; ?>" onchange="bfiQuotePaxesPckChanged(this);" class="">
							<?php
							foreach (range(0, 10) as $optionqt) {
								?> <option value="<?php echo $optionqt ?>" <?php echo ($nadult == $optionqt)?"selected":""; ?>><?php echo $optionqt ?></option><?php
							}
							?>
						</select>
					</div>
					<?php 
					if($useSeniores){
					?>
					<div class="bfi-col-md-3 bfi-col-xs-4 bfi_resource-package-senior" >
						<label><?php echo sprintf(__('Over %s', 'bfi'), $defaultSenioresAge);?>:</label>
						<select name="senioressel-room<?php echo $number; ?>" onchange="bfiQuotePaxesPckChanged(this);" class="">
							<?php
							foreach (range(0, 10) as $optionqt) {
								?> <option value="<?php echo $optionqt ?>" <?php echo ($nsenior == $optionqt)?"selected":""; ?>><?php echo $optionqt ?></option><?php
							}
							?>
						</select>
					</div>
					<?php 
					}
					?>
					<div class="bfi-col-md-3 bfi-col-xs-4 bfi_resource-package-children">
						<label><?php _e('Children', 'bfi'); ?> (0-<?php echo $maxchildrenAge ?>):</label>
						<select name="childrensel-room<?php echo $number; ?>" onchange="bfiQuotePaxesPckChanged(this);" class="">
							<?php
							foreach (range(0, 4) as $optionqt) {
								?> <option value="<?php echo $optionqt ?>" <?php echo ($nchild == $optionqt)?"selected":""; ?>><?php echo $optionqt ?></option><?php
							}
							?>
						</select>
					</div>
				</div>
				<div class="bfi_resource-package-childrenages" style="display:none;">
					<span class="fieldLabel" style="display:inline"><?php _e('Age of children', 'bfi'); ?>:</span>
					<span class="fieldLabel" style="display:inline"><?php echo _e('on', 'bfi') . " " .$checkout->format("d"). " " . $checkout->format("M") . " " . $checkout->format("Y") ?></span><br />
					<select name="childages1sel-room<?php echo $number; ?>" class="bfi-inputmini" style="display: none;">
						<option value="<?php echo COM_BOOKINGFORCONNECTOR_CHILDRENSAGE ?>" ></option>
						<?php
						foreach (range(0, $maxchildrenAge) as $optionqt) {
							?> <option value="<?php echo $optionqt ?>" <?php echo ($nchs[0] !== null && $nchs[0] == $optionqt)?"selected":""; ?>><?php echo $optionqt ?></option><?php
						}
						?>
					</select>
					<select name="childages2sel-room<?php echo $number; ?>" class="bfi-inputmini" style="display: none;">
						<option value="<?php echo COM_BOOKINGFORCONNECTOR_CHILDRENSAGE ?>" ></option>
						<?php
						foreach (range(0, $maxchildrenAge) as $optionqt) {
							?> <option value="<?php echo $optionqt ?>" <?php echo ($nchs[1] !== null && $nchs[1] == $optionqt)?"selected":""; ?>><?php echo $optionqt ?></option><?php
						}
						?>
					</select>
					<select name="childages3sel-room<?php echo $number; ?>" class="bfi-inputmini" style="display: none;">
						<option value="<?php echo COM_BOOKINGFORCONNECTOR_CHILDRENSAGE ?>" ></option>
						<?php
						foreach (range(0, $maxchildrenAge) as $optionqt) {
							?> <option value="<?php echo $optionqt ?>" <?php echo ($nchs[2] !== null && $nchs[2] == $optionqt)?"selected":""; ?>><?php echo $optionqt ?></option><?php
						}
						?>
					</select>
					<select name="childages4sel-room<?php echo $number; ?>" class="bfi-inputmini" style="display: none;">
						<option value="<?php echo COM_BOOKINGFORCONNECTOR_CHILDRENSAGE ?>" ></option>
						<?php
						foreach (range(0, $maxchildrenAge) as $optionqt) {
							?> <option value="<?php echo $optionqt ?>" <?php echo ($nchs[3] !== null && $nchs[3] == $optionqt)?"selected":""; ?>><?php echo $optionqt ?></option><?php
						}
						?>
					</select>
					<select name="childages5sel-room<?php echo $number; ?>" class="bfi-inputmini" style="display: none;">
						<option value="<?php echo COM_BOOKINGFORCONNECTOR_CHILDRENSAGE ?>" ></option>
						<?php
						foreach (range(0, $maxchildrenAge) as $optionqt) {
							?> <option value="<?php echo $optionqt ?>" <?php echo ($nchs[4] !== null && $nchs[4] == $optionqt)?"selected":""; ?>><?php echo $optionqt ?></option><?php
						}
						?>
					</select>
				</div> 
		</div>
</div> 
<?php 
}
?>

	<input type="hidden" name="paxes" value="" id="paxes<?php echo $currModID ?>">
	<input type="hidden" name="paxages" value="" id="paxages<?php echo $currModID ?>" >

	<input name="variationPlanId" type="hidden" value="" />
	<input name="adultssel" type="hidden" value="<?php echo $nad; ?>" />
	<input name="senioressel" type="hidden" value="<?php echo $nse; ?>" />
	<input name="childrensel" type="hidden" value="<?php echo $nch; ?>" />
	<input name="maxrooms" type="hidden" value="<?php echo $maxrooms; ?>" />
	<input name="minqt" type="hidden" value="<?php echo $minqt; ?>" />
	<input name="maxqt" type="hidden" value="<?php echo $maxqt; ?>" />
	<input name="filter_order_Dir" type="hidden" value="" />
	<input name="resourcegroupId" type="hidden" value="" />
	<input name="state" type="hidden" value="" />
	<input name="extras[]" type="hidden" value="<?php echo $selectablePrices ?>" />
	<input name="refreshcalc" type="hidden" value="1" />
	<input name="fromsearch" type="hidden" value="1" />
	<input name="lna" type="hidden" value="<?php echo $listNameAnalytics ?>" />
	<input name="groupresulttype" type="hidden" value="<?php echo $_REQUEST["groupresulttype"]; ?>" />
	<?php if($_REQUEST["resulttype"] == "0") { ?>
	<input name="filter_order" type="hidden" value="resourceid:<?php echo $_REQUEST["resultid"]; ?>" />
	<?php } else if ($_REQUEST["resulttype"] == "1") { ?>
	<input name="filter_order" type="hidden" value="merchantid:<?php echo $_REQUEST["resultid"]; ?>" />
	<?php } else { ?>
	<input name="filter_order" type="hidden" value="condominumid:<?php echo $_REQUEST["resultid"]; ?>" />
	<?php } ?>
	<input name="availabilitytype" type="hidden" value="<?php echo $ProductAvailabilityType?>" />
	<input type="hidden" value="0" name="showmsgchildage" />

	<div class="bfi-pull-right">
		<a href="javascript:bfiSearchFromPck()" class="calculateButton-mandatory calculateButton3 bfi-btn bfi-alternative <?php echo $btnSearchclass ?>" ><?php _e('Check availability', 'bfi') ?> </a>
	</div>
	<div class="bfi-clearfix"></div>

</form>	


		<table class="bfi-table bfi-border bfi-table-groupedresources bfi-table-resources-step1">			
			<tbody>
<?php if(!COM_BOOKINGFORCONNECTOR_ISMOBILE) { ?>

			<tr>
				<td colspan="2" style="padding:0;border:none;"></td>
				<td rowspan="400" class="bfi-groupedresources-prices">
							<div class="bfi-book-now-btn">
							<?php echo sprintf(__('Group price for %s nights:', 'bfi') ,$duration) ?>
							<br />
							<br />
								<div class="bfi-discounted-price bfi-discounted-price-total bfi_<?php echo $currencyclass ?>" style="display:<?php echo ($groupprice < $grouptotalprice)?"":"none"; ?>;"><?php echo BFCHelper::priceFormat($grouptotalprice) ?></div>
								<div class="bfi-price-total <?php echo ($groupprice < $grouptotalprice? "bfi-red" : "") ?> bfi_<?php echo $currencyclass ?>"><?php echo BFCHelper::priceFormat($groupprice) ?></div>
<?php if($IsBookable) { ?>
								<div id="btnBookNow" class="bfi-btn bfi-btn-book-now" onclick="bookingfor.bfiCheckgroup(this);">
									<?php _e('Book Now', 'bfi') ?>
								</div>
<?php }else{ ?>
								<div class="bfi-btn bfi-alternative bfi-request-now" onclick="bookingfor.bfiCheckgroup(this);">
									<?php _e('Request info', 'bfi') ?>
								</div>
<?php } ?>


							</div>
				</td>
			</tr>
<?php } ?>
<?php

$reskey = -1;
$resRef = -1;

foreach($allResourceId as $resId) {

	$reskey += 1;
	$currKey = $reskey;
	if(!empty($resourceId) && !in_array($resourceId,$allResourceId)) {
		$currKey += 1;
	}
	$resRateplans =  array_filter($allRatePlans, function($p) use ($resId) {return $p->ResourceId == $resId ;}); // c#: allRatePlans.Where(p => p.ResourceId == resId);
	
//	usort($resRateplans, "BFCHelper::bfi_sortResourcesRatePlans");
	$res = array_values($resRateplans)[0];

	$IsBookable = 0;
	
	$isResourceBlock = $res->ResourceId == $resourceId;

	$IsBookable = $res->IsBookable;
//	$showQuote = false;
		
//			echo "<pre>";
//			echo print_r($res);
//			echo "</pre>";
				
//	if (($res->Price > 0 && $res->Availability > 0) && ($res->MaxPaxes == 0 || ($totalPerson <= $res->MaxPaxes && $totalPerson >= $res->MinPaxes)) &&
//		(($res->AvailabilityType == 3 || $res->AvailabilityType == 2) && BFCHelper::parseStringDateTime($res->RatePlan->CheckIn) == $dateStringCheckin)
//		|| 
//		(($res->AvailabilityType == 0 || $res->AvailabilityType == 1) && BFCHelper::parseStringDateTime($res->RatePlan->CheckIn) == $dateStringCheckin && BFCHelper::parseStringDateTime($res->RatePlan->CheckOut) == $dateStringCheckout))		{
//			$showQuote = true;
//		}
	
//	$resourceImageUrl = Juri::root() . "components/com_bookingforconnector/assets/images/defaults/default-s6.jpeg";
//	if(!empty($res->ImageUrl)){
//		$resourceImageUrl = BFCHelper::getImageUrlResized('resources', $res->ImageUrl,'small');	
//	}
	$currUriresource = $uri.$res->ResourceId . '-' . BFCHelper::getSlug($res->Name) . "?fromsearch=1&lna=".$listNameAnalytics;
//	$currUriresourceJM = $uri.'&resourceId=' . $res->ResourceId  . ':' . BFCHelper::getSlug($res->Name);
//	if ($itemId<>0){
//		$currUriresourceJM.='&Itemid='.$itemId;
//	}
//	$currUriresourceJM .= "&fromsearch=1&lna=".$listNameAnalytics;
//	$currUriresource = JRoute::_($currUriresourceJM);

	$formRouteSingle = $currUriresource;
	
	$resourceNameTrack =  BFCHelper::string_sanitize($res->Name);
	$merchantNameTrack =  BFCHelper::string_sanitize($merchant->Name);
	$merchantCategoryNameTrack =  BFCHelper::string_sanitize($merchant->MainCategoryName);


	$eecstay = new stdClass;
	$eecstay->id = "" . $res->ResourceId . " - Resource";
	$eecstay->name = "" . $resourceNameTrack;
	$eecstay->category = $merchantCategoryNameTrack;
	$eecstay->brand = $merchantCategoryNameTrack;
//	$eecstay->variant = $showQuote ? strtoupper($selRateName) : "NS";
	$eecstay->position = $reskey;
	if($isResourceBlock) {
		$eecmainstay = $eecstay;
	} else {
		$eecstays[] = $eecstay;
	}
	$btnText = __('Request info', 'bfi');
	if ($IsBookable){
		$btnText = __('Book Now', 'bfi');
	}
	$showServiceGrouped = "false";
	foreach($resRateplans as $rpKey => $currRateplan) {
	$resRef += 1;
		
//		$currSelectablePrices = json_decode($currRateplan->RatePlan->CalculablePricesString);
		$currSelectablePrices = $currRateplan->RatePlan->CalculablePrices;
		$currSelectablePricesExtra = array_filter($currSelectablePrices, function($currSelectablePrice) {
			return $currSelectablePrice->Tag == "extrarequested";
		});
		$currSelectablePricesExtraIds= array_filter(array_map(function ($currSelectablePrice) { 
				if($currSelectablePrice->Tag == "extrarequested"){
					return $currSelectablePrice->PriceId; 
				}
			}, $currSelectablePricesExtra));
		
//		$currCalculatedPrices = json_decode($currRateplan->RatePlan->CalculatedPricesString);
		$currCalculatedPrices = $currRateplan->RatePlan->CalculatedPrices;
		$currCalculatedPricesExtra = array_filter($currCalculatedPrices, function($currCalculatedPrice) use ($currSelectablePricesExtraIds) {
			if(!in_array( $currCalculatedPrice->RelatedProductId,$currSelectablePricesExtraIds) && $currCalculatedPrice->Tag == "extrarequested"){
				return true;
			}
		});
			
		if(count($currSelectablePrices)>0){
			$showServiceGrouped = "true";
		}
		$availability = array();
		$startAvailability = 0;
		$selectedtAvailability = 1;
//		for ($i = $startAvailability; $i <= min($res->Availability, COM_BOOKINGFORCONNECTOR_MAXQTSELECTABLE); $i++)
//		{
//			array_push($availability, $i);
//		}
			array_push($availability, 1);

		$IsBookable = $currRateplan->IsBookable;

		$SimpleDiscountIds = "";

		if(!empty($currRateplan->RatePlan->AllVariationsString)){
//			$allVar = json_decode($currRateplan->RatePlan->AllVariationsString);
			$allVar = $currRateplan->RatePlan->AllVariations;
			$SimpleDiscountIds = implode(',',array_unique(array_map(function ($i) { return $i->VariationPlanId; }, $allVar)));
		}
		$currCheckIn = BFCHelper::parseStringDateTime($currRateplan->RatePlan->CheckIn);
		$currCheckOut =BFCHelper::parseStringDateTime($currRateplan->RatePlan->CheckOut);

if($currRateplan->AvailabilityType==0 || $currRateplan->AvailabilityType==1){
	$currCheckIn = BFCHelper::parseStringDateTime($currRateplan->RatePlan->CheckIn);
	$currCheckOut =BFCHelper::parseStringDateTime($currRateplan->RatePlan->CheckOut);
	$currCheckIn->setTime($mrcAcceptanceCheckInHours,$mrcAcceptanceCheckInMins,$mrcAcceptanceCheckInSecs);
	$currCheckOut->setTime($mrcAcceptanceCheckOutHours,$mrcAcceptanceCheckOutMins,$mrcAcceptanceCheckOutSecs);

}

?>
			<tr id="data-id-<?php echo $currRateplan->ResourceId ?>-<?php echo $currRateplan->RatePlan->RatePlanId ?>-<?php echo $resRef ?>" class="<?php echo $IsBookable?"bfi-bookable":"bfi-canberequested"; ?>">
				<td>
					<a  class="bfi-resname eectrack" href="<?php echo $formRouteSingle ?>" <?php echo ($resId == $resourceId)? 'onclick="bfiGoToTop()"' :  COM_BOOKINGFORCONNECTOR_TARGETURL ; ?> data-type="Resource" data-id="<?php echo $res->ResourceId?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php echo $res->Name; ?></a>

<?php if(COM_BOOKINGFORCONNECTOR_ISMOBILE) { ?>
				<div style="text-align:right;"><!-- price -->
					<?php if( $currRateplan->Price> 0) {?><!-- disponibile -->
					 <div align="center">
						<div class="bfi-percent-discount" style="<?php echo ($currRateplan->PercentVariation < 0 ? " display:block" : "display:none"); ?>" rel="<?php echo $SimpleDiscountIds ?>" rel1="<?php echo $currRateplan->ResourceId ?>">
							<span class="bfi-percent"><?php echo $currRateplan->PercentVariation ?></span>% <i class="fa fa-question-circle bfi-cursor-helper" aria-hidden="true"></i>
						</div>
					</div>
					<div data-value="<?php echo $currRateplan->TotalPrice ?>" class="bfi-discounted-price bfi_<?php echo $currencyclass ?>" style="display:<?php echo ($currRateplan->Price < $currRateplan->TotalPrice)?"":"none"; ?>;"><?php echo BFCHelper::priceFormat($currRateplan->TotalPrice) ?></div>
					<div data-value="<?php echo $currRateplan->Price ?>" class="bfi-price  <?php echo ($currRateplan->Price < $currRateplan->TotalPrice ? "bfi-red" : "") ?> bfi_<?php echo $currencyclass ?>"><?php echo BFCHelper::priceFormat($currRateplan->Price) ?></div>
					
					<?php }else{?>
						<strong><?php _e('No results available for the submitted data', 'bfi') ?></strong>
					<?php }?>
				</div>
<?php } ?>
<div class="bfi-clearfix"></div>
<?php 
/*-----------scelta date e ore--------------------*/	


									if ($res->AvailabilityType == 2)
									{
										
//										$currCheckIn = BFCHelper::parseJsonDateTime($res->RatePlan->CheckIn,'d/m/Y - H:i');
//										$currCheckOut =BFCHelper::parseJsonDateTime($res->RatePlan->CheckOut,'d/m/Y - H:i');
										$currCheckIn = DateTime::createFromFormat('Y-m-d\TH:i:s',$res->RatePlan->CheckIn,new DateTimeZone('UTC'));
										$currCheckOut = DateTime::createFromFormat('Y-m-d\TH:i:s',$res->RatePlan->CheckOut,new DateTimeZone('UTC'));

										$currDiff = $currCheckOut->diff($currCheckIn);

										$loadScriptTimePeriod = true;

										$timeDurationview = $currDiff->h + round(($currDiff->i/60), 2);
										$timeDuration = abs((new DateTime('UTC'))->setTimeStamp(0)->add($currDiff)->getTimeStamp() / 60); 										

										array_push($allTimePeriodResourceId, $res->ResourceId);
									?>
										<div class="bfi-timeperiod bfi-cursor" id="bfi-timeperiod-<?php echo $res->ResourceId ?>" 
											data-resid="<?php echo $res->ResourceId ?>" 
											data-checkin="<?php echo $currCheckIn->format('Ymd') ?>" 
											data-checkintime="<?php echo $currCheckIn->format('YmdHis') ?>"
											data-timeminstart="<?php echo $currCheckIn->format('His') ?>"
											data-timeminend="<?php echo $currCheckOut->format('His') ?>"
											data-duration="<?php echo $timeDuration ?>"
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
												<div class="bfi-col-md-9 bfi-text-right"><span class="bfi-total-duration"><?php echo $timeDurationview  ?></span> <?php _e('hours', 'bfi') ?>
												</div>	
											</div>	
										</div>
								<?php
									}
/*-------------------------------*/	
									if ($res->AvailabilityType == 3)
									{
										$loadScriptTimeSlot = true;
										$currDatesTimeSlot = array();
										
										if(!array_key_exists($resId, $allTimeSlotResourceId)){
											array_push($allTimeSlotResourceId, $res->ResourceId);
										}
										
										if(!array_key_exists($resId, $listDayTS)){
											$currDatesTimeSlot =  json_decode(BFCHelper::GetCheckInDatesTimeSlot($resId,$alternativeDateToSearch));
											$listDayTS[$resId] = $currDatesTimeSlot;
										}else{
											$currDatesTimeSlot =  $listDayTS[$resId];
										}

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
										<div class="bfi-timeslot bfi-cursor" id="bfi-timeslot-<?php echo $res->ResourceId ?>" data-resid="<?php echo $res->ResourceId ?>" data-checkin="<?php echo $currCheckIn->format('Ymd') ?>" data-checkin-ext="<?php echo $currCheckIn->format('d/m/Y') ?>"
										data-timeslotid="<?php echo $currDatesTimeSlot[0]->ProductId ?>" data-timeslotstart="<?php echo $currDatesTimeSlot[0]->TimeSlotStart ?>" data-timeslotend="<?php echo $currDatesTimeSlot[0]->TimeSlotEnd ?>"
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
												<div class="bfi-col-md-9 bfi-text-right"><span class="bfi-total-duration"><?php echo $currDiff->format('%h') ?></span> <?php _e('hours', 'bfi') ?>
												</div>	
											</div>	
										</div>
								<?php
									}								
/*-------------------------------*/									
					$resAlltag = array();
					$resAlltagClose = true;
					if (!empty($res->TagsIdList)) {
						$resAlltag = explode(",", $res->TagsIdList);
					}
					$resArea = (isset($res->Area) && $res->Area>0) ?$res->Area:0;

					if (!empty($res->ResServiceIdList) && $resArea>0 ) {
						$bfiresourcetagshighlighted = explode(",", $res->ResServiceIdList);
						if (count($bfiresourcetagshighlighted) >4) {
							$resAlltag = array_diff($resAlltag ,$bfiresourcetagshighlighted);
							?>
							<div class="bfiresourcetagshighlighted" rel="<?php echo $res->ResServiceIdList ?>" data-area="<?php echo $resArea ?>"></div>
							<?php
						    $resArea = 0; // resetto l'area così non compare successivamente
							if ($currKey==0 && count($resAlltag) > 0) {
								?>
								<hr class="bfiresourcetagshighlighted-divider">
								<?php 
								
							}
						}
					}
					if ($currKey==0 && count($resAlltag) > 0) {
						$resAlltagClose = false;
					}
					if (count($resAlltag)>0 || $resArea>0 ) {
						?>
						<div class="bfiresourcetags" rel="<?php echo implode(',',$resAlltag) ?>" data-area="<?php echo $resArea ; ?>" style="<?php echo ($resAlltagClose) ?"display:none": "" ?>"></div>
						<?php 
						if($currKey>0){
							?>
							<span class="bfishowresourcetags bfishowresourcetags-active"><span class="bfishowresourcetagsopen">Altro</span><span class="bfishowresourcetagsclose">Nascondi</span></span>
						<?php 
						}
					}					

$currVat = $res->VATValue;				
$currTouristTaxValue = isset($res->TouristTaxValue)?$res->TouristTaxValue:0;				
?>
<br />
<?php if(!empty($currVat)) { 
	if ($currVat =="-1") {
		?>
		<div class="bfi-exempt"><?php _e('VAT', 'bfi') ?>: <?php _e('Exempt', 'bfi') ?> </div>
		<?php 
	}else {
		?>
		<div class="bfi-incuded"><strong><?php _e('Included', 'bfi') ?></strong>: <?php echo $currVat?> <?php _e('VAT', 'bfi') ?> </div>
		<?php 
	}
} 
?>
<?php if(!empty($currTouristTaxValue)) { 
	if ($currVat =="-1") {
		?>
		<div class="bfi-exempt"><?php _e('City tax', 'bfi') ?>: <?php _e('Exempt', 'bfi') ?></div>
		<?php 
	}else {
		?>
		<div class="bfi-notincuded"><strong><?php _e('Not included', 'bfi') ?></strong> : <span class="bfi_<?php echo $currencyclass ?>" ><?php echo BFCHelper::priceFormat($currTouristTaxValue) ?></span> <?php _e('City tax per person per night.', 'bfi') ?> </div>
		<?php 
	}
} 
?>
				<!-- Min/Max -->
				<?php if ($currRateplan->MaxPaxes>0){?>
					<?php _e('Fits', 'bfi') ?>: 
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
						if ($nadult>0) {
							?>
							<div class="bfi-icon-paxes">
								<i class="fa fa-user"></i> x <b><?php echo $nadult ?></b>
							<?php 
								if (($nsenior+$nchild)>0) {
									?>
									+  <span class="bfi-redux"><i class="fa fa-user"></i></span> x <b><?php echo ($nsenior+$nchild) ?></b>
									<?php 
									
								}
							?>
							
							</div>
							<div class="webui-popover-content">
							   <div class="bfi-options-popover">
									<?php echo sprintf(__('Max %s person', 'bfi') ,$totPerson) ?>
								</div>
							</div>
							
							<?php 
							
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
						<?php } ?>
					<?php } ?>
				<?php } ?>
				<!-- options -->
					<br />
					<div style="position:relative;display:  inline-block;padding-right: 10px;">
					<?php 
$policy = $currRateplan->RatePlan->Policy;
$policyId= 0;
$policyHelp = "";
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
			$currValue = sprintf(__(' %d day/s' ,'bfi'),rtrim($policy->CancellationBaseValue,"n"));
			break;
		default:
			$currValue = '<span class="bfi_' . $currencyclass .'">'. BFCHelper::priceFormat($policy->CancellationBaseValue) .'</span>' ;
	}
	$currValuebefore = $policy->CancellationValue;
	switch (true) {
		case strstr($policy->CancellationValue ,'%'):
			$currValuebefore = $policy->CancellationValue;
			break;
		case strstr($policy->CancellationValue ,'d'):
			$currValuebefore = sprintf(__(' %d day/s' ,'bfi'),rtrim($policy->CancellationValue,"d"));
			break;
		case strstr($policy->CancellationValue ,'n'):
			$currValuebefore = sprintf(__(' %d day/s' ,'bfi'),rtrim($policy->CancellationValue,"n"));
			break;
		default:
			$currValuebefore = '<span class="bfi_' . $currencyclass .'">'. BFCHelper::priceFormat($policy->CancellationValue) .'</span>' ;
	}
	if($policy->CanBeCanceled){
		$currTimeBefore = "";
		$currDateBefore = "";
		if(!empty( $policy->CanBeCanceledCurrentTime )){
				if(!empty( $policy->CancellationTime )){
//					$currDatePolicyparsed = BFCHelper::parseJsonDate($res->RatePlan->CheckIn, 'Y-m-d');
//					$currDatePolicy = DateTime::createFromFormat('Y-m-d',$currDatePolicyparsed,new DateTimeZone('UTC'));
					$currDatePolicy =BFCHelper::parseStringDateTime($res->RatePlan->CheckIn);
					switch (true) {
						case strstr($policy->CancellationTime ,'d'):
							$currTimeBefore = sprintf(__(' %d day/s' ,'bfi'),rtrim($policy->CancellationTime,"d"));	
							$currDatePolicy->modify('-'. rtrim($policy->CancellationTime,"d") .' days'); 
							break;
						case strstr($policy->CancellationTime ,'h'):
							$currTimeBefore = sprintf(__(' %d hour/s' ,'bfi'),rtrim($policy->CancellationTime,"h"));
							$currDatePolicy->modify('-'. rtrim($policy->CancellationTime,"h") .' hours'); 
							break;
						case strstr($policy->CancellationTime ,'w'):
							$currTimeBefore = sprintf(__(' %d week/s' ,'bfi'),rtrim($policy->CancellationTime,"w"));
							$currDatePolicy->modify('-'. rtrim($policy->CancellationTime,"w") .' weeks'); 
							break;
						case strstr($policy->CancellationTime ,'m'):
							$currTimeBefore = sprintf(__(' %d month/s' ,'bfi'),rtrim($policy->CancellationTime,"m"));
							$currDatePolicy->modify('-'. rtrim($policy->CancellationTime,"m") .' months'); 
							break;
					}
				}

				if($policy->CancellationValue=="0" || $policy->CancellationValue=="0%"){
					?>
					<div class="bfi-policy-green"><?php _e('Cancellation FREE', 'bfi') ?>
					<?php 
					if(!empty( $policy->CancellationTime )){
						echo '<br />'.__('until', 'bfi') ;
						echo ' '.$currDatePolicy->format("d").' '.date_i18n('M',$currDatePolicy->getTimestamp()).' '.$currDatePolicy->format("Y");
						$policyHelp = sprintf(__('You may cancel free of charge until %1$s before arrival. You will be charged %2$s if you cancel in the %1$s before arrival.', 'bfi'),$currTimeBefore,$currValue);
					}
					?>
					</div>
					<?php 

					
				}else{
				if($policy->CancellationBaseValue=="0%" || $policy->CancellationBaseValue=="0"){
					?>
					<div class="bfi-policy-green"><?php _e('Cancellation FREE', 'bfi') ?></div>
					<?php 
					$policyHelp = __('You may cancel free of charge until arrival.', 'bfi');
				}else{
					?>
					<div class="bfi-policy-blue"><?php _e('Special conditions', 'bfi') ?></div>
					<?php 
					$policyHelp = sprintf(__('You may cancel with a charge of %3$s  until %1$s before arrival. You will be charged %2$s if you cancel in the %1$s before arrival.', 'bfi'),$currTimeBefore,$currValue,$currValuebefore);
				}
				}

			
		}else{
				if($policy->CancellationBaseValue=="0%" || $policy->CancellationBaseValue=="0"){
					?>
					<div class="bfi-policy-green"><?php _e('Cancellation FREE', 'bfi') ?></div>
					<?php 
					$policyHelp = __('You may cancel free of charge until arrival.', 'bfi');
				}else{
					?>
					<div class="bfi-policy-blue"><?php _e('Special conditions', 'bfi') ?></div>
					<?php 
					$policyHelp = sprintf(__('You will be charged %1$s if you cancel before arrival.', 'bfi'),$currValue);
				}
		}
				
	}else{ 
		// no refundable
		?>
			<div class="bfi-policy-none"><?php _e('Non refundable', 'bfi') ?></div>
		<?php 
		$policyHelp = sprintf(__('You will be charged all if you cancel before arrival.', 'bfi'));
	
	}
}
$currMerchantBookingTypes = array();
$prepayment = "";
$prepaymentHelp = "";

if(!empty( $currRateplan->RatePlan->MerchantBookingTypesString )){
	$currMerchantBookingTypes = json_decode($currRateplan->RatePlan->MerchantBookingTypesString);
	$currBookingTypeId = $currRateplan->RatePlan->MerchantBookingTypeId;
	$currMerchantBookingType = array_filter($currMerchantBookingTypes, function($bt) use($currBookingTypeId) {return $bt->BookingTypeId == $currBookingTypeId;});
	$currMerchantBookingType = array_values($currMerchantBookingType);
	if(count($currMerchantBookingType)>0){
		if($currMerchantBookingType[0]->PayOnArrival){
			$prepayment = __("Pay at the property – NO PREPAYMENT NEEDED", 'bfi');
			$prepaymentHelp = __("No prepayment is needed.", 'bfi');
		}
		if($currMerchantBookingType[0]->AcquireCreditCardData){
			$prepayment = "";
			if($currMerchantBookingType[0]->DepositRelativeValue=="100%"){
				$prepaymentHelp = __('You will be charged a prepayment of the total price at any time.', 'bfi');
			}else if(strpos($currMerchantBookingType[0]->DepositRelativeValue, '%') !== false  ) {
				$prepaymentHelp = sprintf(__('You will be charged a prepayment of %1$s of the total price at any time.', 'bfi'),$currMerchantBookingType[0]->DepositRelativeValue);
			}else{
				$prepaymentHelp = sprintf(__('You will be charged a prepayment of %1$s at any time.', 'bfi'),$currMerchantBookingType[0]->DepositRelativeValue);
			}
		}
	}
}




$allMeals = array();
$cssclassMeals = "bfi-meals-base";
$mealsHelp = "";
if($currRateplan->ItemTypeId==1){
	$currRateplan->RatePlan->IncludedMeals = -1;
}
if($currRateplan->RatePlan->IncludedMeals >-1){
	$mealsHelp = __("There is no meal option with this room.", 'bfi');
	if ($currRateplan->RatePlan->IncludedMeals & bfi_Meal::Breakfast){
		$allMeals[]= __("Breakfast", 'bfi');
	}
	if ($currRateplan->RatePlan->IncludedMeals & bfi_Meal::Lunch){
		$allMeals[]= __("Lunch", 'bfi');
	}
	if ($currRateplan->RatePlan->IncludedMeals & bfi_Meal::Dinner){
		$allMeals[]= __("Dinner", 'bfi');
	}
	if ($currRateplan->RatePlan->IncludedMeals & bfi_Meal::AllInclusive){
		$allMeals[]= __("All Inclusive", 'bfi');
	}
	if(in_array(__("Breakfast", 'bfi'), $allMeals)){
		$cssclassMeals = "bfi-meals-bb";
	}
	if(in_array(__("Lunch", 'bfi'), $allMeals) || in_array(__("Dinner", 'bfi'), $allMeals) || in_array(__("All Inclusive", 'bfi'), $allMeals)  ){
		$cssclassMeals = "bfi-meals-fb";
	}
	if(count($allMeals)>0){
		$mealsHelp = implode(", ",$allMeals). " " . __('included', 'bfi');
	}
	if(count($allMeals)==2){
		$mealsHelp = implode(" & ",$allMeals). " " . __('included', 'bfi');
	}
}
?>
<?php if(!empty($prepayment)) { ?>
						<div class="bfi-prepayment"><?php echo $prepayment ?></div>
<?php } ?>

						<div class="bfi-meals <?php echo $cssclassMeals?>"><?php echo $mealsHelp ?></div>
						<div class="bfi-options-help">
							<i class="fa fa-question-circle bfi-cursor-helper" aria-hidden="true"></i>
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
<?php 
					$currAvail = max(array_map(function($rp){ return $rp->Availability; }, $resRateplans));
					if (($res->AvailabilityType == 0 || $res->AvailabilityType == 1) && $currAvail < 2) {
					?>
					  <br /><br /><span class="bfi-availability-low"><?php echo sprintf(__('Only %d available', 'bfi') ,$res->Availability) ?></span>
<?php
					}
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
					<input type="hidden" class="ddlrooms ddlrooms-<?php echo $currRateplan->ResourceId ?> noddlroomsrealav-<?php echo $currRealAvailProductId ?> ddlrooms-indipendent" 
					id="ddlrooms-<?php echo $currRateplan->ResourceId ?>-<?php echo $currRateplan->RatePlan->RatePlanId ?>-<?php echo $resRef ?>" 
					data-referencid="<?php echo $currRateplan->RatePlan->RatePlanId ?>-<?php echo $resRef ?>" 
					data-realavailproductid="<?php echo $currRealAvailProductId?>" 
					data-resid="<?php echo $currRateplan->ResourceId ?>" 
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
					value = "<?php echo $selectedtAvailability ?>"
					data-bedconfig=""
					data-bedconfigindex=""
					/>

<script type="text/javascript">
<!--
					pricesExtraIncluded["<?php echo $currRateplan->RatePlan->RatePlanId ?>-<?php echo $resRef ?>"] =<?php echo json_encode((object)$currCalculatedPricesExtra) ?> ;	
//-->
</script>
				
				</td>
<?php if(!COM_BOOKINGFORCONNECTOR_ISMOBILE) { ?>

				<td style="text-align:right;"><!-- price -->
					<?php if( $currRateplan->Price> 0) {?><!-- disponibile -->
					 <div align="center">
						<div class="bfi-percent-discount" style="<?php echo ($currRateplan->PercentVariation < 0 ? " display:block" : "display:none"); ?>" rel="<?php echo $SimpleDiscountIds ?>" rel1="<?php echo $currRateplan->ResourceId ?>">
							<span class="bfi-percent"><?php echo $currRateplan->PercentVariation ?></span>% <i class="fa fa-question-circle bfi-cursor-helper" aria-hidden="true"></i>
						</div>
					</div>
					<div data-value="<?php echo $currRateplan->TotalPrice ?>" class="bfi-discounted-price bfi_<?php echo $currencyclass ?>" style="display:<?php echo ($currRateplan->Price < $currRateplan->TotalPrice)?"":"none"; ?>;"><?php echo BFCHelper::priceFormat($currRateplan->TotalPrice) ?></div>
					<div data-value="<?php echo $currRateplan->Price ?>" class="bfi-price  <?php echo ($currRateplan->Price < $currRateplan->TotalPrice ? "bfi-red" : "") ?> bfi_<?php echo $currencyclass ?>"><?php echo BFCHelper::priceFormat($currRateplan->Price) ?></div>
					
					<?php }else{?>
						<strong><?php _e('No results available for the submitted data', 'bfi') ?></strong>
					<?php }?>
				</td>
<?php } ?>
			</tr>

<?php 
}

$resCount++;
} 
 ?>
<?php if(COM_BOOKINGFORCONNECTOR_ISMOBILE) { ?>
			<tr>
				<td class="bfi-groupedresources-prices">
							<div class="bfi-book-now-btn" style="text-align:left">
							<?php echo sprintf(__('Group price for %s nights:', 'bfi') ,$duration) ?>
								<div class="bfi-discounted-price bfi-discounted-price-total bfi_<?php echo $currencyclass ?>" style="display:<?php echo ($groupprice < $grouptotalprice)?"":"none"; ?>;"><?php echo BFCHelper::priceFormat($grouptotalprice) ?></div>
								<div class="bfi-price-total <?php echo ($groupprice < $grouptotalprice? "bfi-red" : "") ?> bfi_<?php echo $currencyclass ?>"><?php echo BFCHelper::priceFormat($groupprice) ?></div>
<?php if($IsBookable) { ?>
								<div id="btnBookNow" class="bfi-btn bfi-btn-book-now" onclick="bookingfor.bfiCheckgroup(this);">
									<?php _e('Book Now', 'bfi') ?>
								</div>
<?php }else{ ?>
								<div class="bfi-btn bfi-alternative bfi-request-now" onclick="bookingfor.bfiCheckgroup(this);">
									<?php _e('Request info', 'bfi') ?>
								</div>
<?php } ?>


							</div>
				</td>
			</tr>
<?php } ?>
			</tbody>
		</table>
		<!-- end bfi-table-resources -->
</div>

<!-- new list bfi-table-resources -->
<?php 

if(count($allResourceId)>0){

	$currIdmerchant = $merchant->MerchantId;
	$variationPlanId = null;
	$tmpallRatePlans = BFCHelper::getSearchPackages($currIdmerchant, $resourceId, $resourceId, $checkin,$duration,$pckpaxages, $variationPlanId,$language, $resourcegroupId,$availabilityTypes,$itemTypeIds,$minqt,$maxqt);
	$totalResCount = count((array)$tmpallRatePlans);	
	if(is_array($tmpallRatePlans) && $totalResCount>0){
		$tmpallResults = array();
		foreach($tmpallRatePlans as $tmpRatePlans) {
//			$tmpItemId = $tmpRatePlans->ItemId;
			foreach($tmpRatePlans->Results as $tmpResults) {
//				$tmpResults->ResourceId = $tmpItemId; // override temporaneo <------------------
				array_push($tmpallResults, $tmpResults);
			}
		}


//echo "<pre>";
//echo print_r($tmpallRatePlans);
//echo "</pre>";
		$tmpallResourceId = array_unique(array_map(function ($i) { return $i->ResourceId; }, $tmpallResults));

//echo "<pre>";
//echo print_r($tmpallResourceId);
//echo "</pre>";
$allRatePlans = $tmpallResults;
$allResourceId = $tmpallResourceId;
//		$allResourceId = $tmpallResourceId;
//		$allRatePlans = $tmpallResults;
	}


}

//raggruppo per id risorsa

?>
<div class="bfi-result-list bfi-group-recommendation bfi-table-responsive" data-grptotalpaxes="<?php echo $nad + $nse + $nch?>">
		<table class="bfi-table bfi-table-bordered bfi-table-resources bfi-table-resources-sticked bfi-table-resourcessearchdetails bfi-table-resources-step1" style="margin-top: 20px;">
			<thead>
				<tr>
					<th><?php _e('Information', 'bfi') ?></th>
					<th><div><!-- <?php _e('For', 'bfi') ?> --></div></th>
					<th ><div><?php _e('Price', 'bfi') ?></div></th>
					<th><div><?php _e('Options', 'bfi') ?></div></th>
					<th><div><?php _e('Qt.', 'bfi') ?></div></th>
					<th><div><?php _e('Confirm your reservation', 'bfi') ?></div></th>
				</tr>
			</thead>

			
			<tbody>
			<tr>
				<td colspan="5" style="padding:0;border:none;"></td>
				<td rowspan="400" class="bfi-book-now-td">
							<div class="bfi-book-now">
								<div class="bfi-tooltip-allok-container"><div class="bfi-tooltip-allok">Qui c'è spazio per tutto il tuo gruppo.</div></div>
								<div class="bfi-resource-total"><span></span> <?php _e('selected items', 'bfi') ?></div>
								<div class="bfi-discounted-price bfi-discounted-price-total bfi_<?php echo $currencyclass ?>" style="display:none;"></div>
								<div class="bfi-price bfi-price-total bfi_<?php echo $currencyclass ?>" ></div>
								<div id="btnBookNow" class="bfi-btn bfi-btn-book-now" data-formroute="" onclick="bfi_ChangeVariation(this);" style="display:none;">
									<?php _e('Book Now', 'bfi') ?>
								</div>
								<div class="bfi-btn bfi-alternative bfi-request-now" onclick="bfi_ChangeVariation(this);" style="display:none;">
									<?php _e('Request Now', 'bfi') ?>
								</div>

							</div>
				</td>
			</tr>
<?php

$allSelectablePrices = array();
$allTimeSlotResourceId = array();
$allTimePeriodResourceId = array();
$reskey = -1;
$resRef = -1;

foreach($allResourceId as $resId) {

	$reskey += 1;
	$currKey = $reskey;
//	if(!empty($resourceId) && !in_array($resourceId,$allResourceId)) {
//		$currKey += 1;
//	}
	$currResRateplans =  array_filter($allRatePlans, function($p) use ($resId,$currIsBookable) {return ($p->ResourceId == $resId) && ($p->IsBookable == $currIsBookable) ;}); // c#: allRatePlans.Where(p => p.ResourceId == resId);
	if (!empty($currResRateplans)) {
    
	$firstSelection = [];
	foreach($firstPackage as $tmpResRateplans) {
		$alreadyTaken =  current(array_filter($currResRateplans, function($p) use ($tmpResRateplans) {
							if ($p->RatePlan->RatePlanId == $tmpResRateplans->RatePlan->RatePlanId && $p->RatePlan->SuggestedStay->Paxes == $tmpResRateplans->RatePlan->SuggestedStay->Paxes) {
								return $p ;
							}
						})); // c#: allRatePlans.Where(p => p.ResourceId == resId);
		if (!empty($alreadyTaken )) {
//			echo "<pre>alreadyTaken";
//			echo print_r($alreadyTaken);
//			echo "</pre>";
			
			$keyfirst = array_search($alreadyTaken, $currResRateplans);
			unset($currResRateplans[$keyfirst]);
			$firstSelection[$keyfirst] =  $alreadyTaken;
		}
	}


	// reupero i primi calcolati e limito a 6 risultati

//	shuffle($currResRateplans);	

//echo "<pre>";
//echo print_r($currResRateplans);
//echo "</pre>";

//	$resRateplans = array_slice($currResRateplans, 0, 6);

// Build temporary array for array_unique
	usort($currResRateplans, "BFCHelper::bfi_sortMultiRatePlans");

	$resRateplans = $currResRateplans;
	if (!empty($firstSelection )) {
//		echo "<pre>firstSelection";
//		echo print_r($firstSelection);
//		echo "</pre>";
		$resRateplans = array_merge($firstSelection, $resRateplans);
	}


//	usort($resRateplans, "BFCHelper::bfi_sortResourcesRatePlans");
	$res = array_values($resRateplans)[0];

	$IsBookable = 0;
	
	$isResourceBlock = $res->ResourceId == $resourceId;

	$IsBookable = $res->IsBookable;
	$currUriresource = $uri.$res->ResourceId . '-' . BFCHelper::getSlug($res->Name) . "?fromsearch=1&lna=".$listNameAnalytics;

	$formRouteSingle = $currUriresource;

	$resourceNameTrack =  BFCHelper::string_sanitize($res->Name);
	$merchantNameTrack =  BFCHelper::string_sanitize($merchant->Name);
	$merchantCategoryNameTrack =  BFCHelper::string_sanitize($merchant->MainCategoryName);

	$eecstay = new stdClass;
	$eecstay->id = "" . $res->ResourceId . " - Resource";
	$eecstay->name = "" . $resourceNameTrack;
	$eecstay->category = $merchantCategoryNameTrack;
	$eecstay->brand = $merchantCategoryNameTrack;
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
				<td rowspan="<?php echo $nRowSpan ?>" class="bfi-firstcol <?php echo ($resId == $resourceId)? '  bfi-firstcolum-selected' :  '' ; ?>" id="bfitdresdesc<?php echo $resId?>">
					<a  class="bfi-resname eectrack" href="<?php echo $formRouteSingle ?>" <?php echo ($resId == $resourceId)? 'onclick="bfiGoToTop()"' :  COM_BOOKINGFORCONNECTOR_TARGETURL ; ?> data-type="Resource" data-id="<?php echo $res->ResourceId?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php echo $res->Name; ?></a>

<div class="bfi-clearfix"></div>
<?php 
			if($resId != $resourceId && !empty($res->ImageUrl)){
				$resourceImageUrl = BFCHelper::getImageUrlResized('resources',$res->ImageUrl, 'small');
?>
<a  class="bfi-link-searchdetails" href="<?php echo $formRouteSingle ?>" <?php echo ($resId == $resourceId)? 'onclick="bfiGoToTop()"' :  COM_BOOKINGFORCONNECTOR_TARGETURL ; ?> ><img src="<?php echo $resourceImageUrl; ?>" class="bfi-img-searchdetails" /></a>
<div class="bfi-clearfix"></div>
<?php
			}
/*-----------scelta date e ore--------------------*/	
/*-----------scelta date e ore--------------------*/	

									if (($res->AvailabilityType == 0 || $res->AvailabilityType == 1) && $res->Availability < 2)
									{
										?>
									  <span class="bfi-availability-low"><?php echo sprintf(__('Only %d available' , 'bfi'),$res->Availability) ?></span>
									<?php 
									}

									if ($res->AvailabilityType == 2)
									{
										
//										$currCheckIn = BFCHelper::parseJsonDateTime($res->RatePlan->CheckIn,'d/m/Y - H:i');
//										$currCheckOut =BFCHelper::parseJsonDateTime($res->RatePlan->CheckOut,'d/m/Y - H:i');
										$currCheckIn = DateTime::createFromFormat('Y-m-d\TH:i:s',$res->RatePlan->CheckIn,new DateTimeZone('UTC'));
										$currCheckOut = DateTime::createFromFormat('Y-m-d\TH:i:s',$res->RatePlan->CheckOut,new DateTimeZone('UTC'));

										$currDiff = $currCheckOut->diff($currCheckIn);

										$loadScriptTimePeriod = true;

										$timeDurationview = $currDiff->h + round(($currDiff->i/60), 2);
										$timeDuration = abs((new DateTime('UTC'))->setTimeStamp(0)->add($currDiff)->getTimeStamp() / 60); 										

										array_push($allTimePeriodResourceId, $res->ResourceId);
									?>
										<div class="bfi-timeperiod bfi-cursor" id="bfi-timeperiod-<?php echo $res->ResourceId ?>" 
											data-resid="<?php echo $res->ResourceId ?>" 
											data-checkin="<?php echo $currCheckIn->format('Ymd') ?>" 
											data-checkintime="<?php echo $currCheckIn->format('YmdHis') ?>"
											data-timeminstart="<?php echo $currCheckIn->format('His') ?>"
											data-timeminend="<?php echo $currCheckOut->format('His') ?>"
											data-duration="<?php echo $timeDuration ?>"
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
												<div class="bfi-col-md-9 bfi-text-right"><span class="bfi-total-duration"><?php echo $timeDurationview  ?></span> <?php _e('hours', 'bfi') ?>
												</div>	
											</div>	
										</div>
								<?php
									}
/*-------------------------------*/	
									if ($res->AvailabilityType == 3)
									{
										$loadScriptTimeSlot = true;
										$currDatesTimeSlot = array();
										
										if(!array_key_exists($resId, $allTimeSlotResourceId)){
											array_push($allTimeSlotResourceId, $res->ResourceId);
										}
										
										if(!array_key_exists($resId, $listDayTS)){
											$currDatesTimeSlot =  json_decode(BFCHelper::GetCheckInDatesTimeSlot($resId,$alternativeDateToSearch));
											$listDayTS[$resId] = $currDatesTimeSlot;
										}else{
											$currDatesTimeSlot =  $listDayTS[$resId];
										}

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
										<div class="bfi-timeslot bfi-cursor" id="bfi-timeslot-<?php echo $res->ResourceId ?>" data-resid="<?php echo $res->ResourceId ?>" data-checkin="<?php echo $currCheckIn->format('Ymd') ?>" data-checkin-ext="<?php echo $currCheckIn->format('d/m/Y') ?>"
										data-timeslotid="<?php echo $currDatesTimeSlot[0]->ProductId ?>" data-timeslotstart="<?php echo $currDatesTimeSlot[0]->TimeSlotStart ?>" data-timeslotend="<?php echo $currDatesTimeSlot[0]->TimeSlotEnd ?>"
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
												<div class="bfi-col-md-9 bfi-text-right"><span class="bfi-total-duration"><?php echo $currDiff->format('%h') ?></span> <?php _e('hours', 'bfi') ?>
												</div>	
											</div>	
										</div>
								<?php
									}								
/*-----------bed list--------------------*/
if(isset($res->BedsConfiguration) && !empty($res->BedsConfiguration)) { 

	$currBedsConfiguration = json_decode($res->BedsConfiguration);
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
					<input type="radio" value="1" name="bfibedroom<?php echo $res->ResourceId ?>" rel="<?php echo $res->ResourceId ?>" data-config='<?php echo json_encode($currBedsConfiguration->Main  ) ?>' class="bfi-bedrooms-option">
				</div>
		    <?php 		    
		}
		?>
		<ul class="bfi-bedroomslist">
		<?php
		foreach($currBedsConfiguration->Main as $bedrooms) {
			?>
			<li><span class="bfi-bedroom <?php echo (count($currBedsConfiguration->Main)>1) ?"":"bfi-hide";?>"> <?php _e('Room', 'bfi') ?> <?php echo  $bedrooms->index ?>: </span>
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

/*-------------------------------*/									
					$resAlltag = array();
					$resAlltagClose = true;
					if (!empty($res->TagsIdList)) {
						$resAlltag = explode(",", $res->TagsIdList);
					}
					$resArea = (isset($res->Area) && $res->Area>0) ?$res->Area:0;

					if (!empty($res->ResServiceIdList) && $resArea>0 ) {
						$bfiresourcetagshighlighted = explode(",", $res->ResServiceIdList);
						if (count($bfiresourcetagshighlighted) >4) {
							$resAlltag = array_diff($resAlltag ,$bfiresourcetagshighlighted);
							?>
							<div class="bfiresourcetagshighlighted" rel="<?php echo $res->ResServiceIdList ?>" data-area="<?php echo $resArea ?>"></div>
							<?php
						    $resArea = 0; // resetto l'area così non compare successivamente
							if ($currKey==0 && count($resAlltag) > 0) {
								?>
								<hr class="bfiresourcetagshighlighted-divider">
								<?php 
								
							}
						}
					}
					if ($currKey==0 && count($resAlltag) > 0) {
						$resAlltagClose = false;
					}
					if (count($resAlltag)>0 || $resArea>0 ) {
						?>
						<div class="bfiresourcetags" rel="<?php echo implode(',',$resAlltag) ?>" data-area="<?php echo $resArea ; ?>" style="<?php echo ($resAlltagClose) ?"display:none": "" ?>"></div>
						<?php 
						if($currKey>0){
							?>
							<span class="bfishowresourcetags bfishowresourcetags-active"><span class="bfishowresourcetagsopen">Altro</span><span class="bfishowresourcetagsclose">Nascondi</span></span>
						<?php 
						}
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



	foreach($resRateplans as $rpKey => $currRateplan) {
		$resRef += 1;
	
// Sospeso:
//		$currSelectablePrices = json_decode($currRateplan->RatePlan->CalculablePricesString);
		$currSelectablePrices = $currRateplan->RatePlan->CalculablePrices;
		$currSelectablePricesExtra = array_filter($currSelectablePrices, function($currSelectablePrice) {
			return $currSelectablePrice->Tag == "extrarequested";
		});
		$currSelectablePricesExtraIds= array_filter(array_map(function ($currSelectablePrice) { 
				if($currSelectablePrice->Tag == "extrarequested"){
					return $currSelectablePrice->PriceId; 
				}
			}, $currSelectablePricesExtra));
		
// Sospeso:
//		$currCalculatedPrices = json_decode($currRateplan->RatePlan->CalculatedPricesString);
		$currCalculatedPrices = $currRateplan->RatePlan->CalculatedPrices;
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

		$SimpleDiscountIds = "";

		if(!empty($currRateplan->RatePlan->AllVariationsString)){
			$allVar = json_decode($currRateplan->RatePlan->AllVariationsString);
			$SimpleDiscountIds = implode(',',array_unique(array_map(function ($i) { return $i->VariationPlanId; }, $allVar)));
		}
// Sospeso:
//		$currCheckIn = BFCHelper::parseJsonDateTime($currRateplan->RatePlan->CheckIn,'d/m/Y\TH:i:s');
//		$currCheckOut =BFCHelper::parseJsonDateTime($currRateplan->RatePlan->CheckOut,'d/m/Y\TH:i:s');
		$currCheckIn = BFCHelper::parseStringDateTime($currRateplan->RatePlan->CheckIn);
		$currCheckOut =BFCHelper::parseStringDateTime($currRateplan->RatePlan->CheckOut);

if($currRateplan->AvailabilityType==0 || $currRateplan->AvailabilityType==1){
// Sospeso:
//	$currCheckIn = BFCHelper::parseJsonDateTime($currRateplan->RatePlan->CheckIn,'d/m/Y\TH:i:s');
//	$currCheckOut =BFCHelper::parseJsonDateTime($currRateplan->RatePlan->CheckOut,'d/m/Y\TH:i:s');
	$currCheckIn = BFCHelper::parseStringDateTime($currRateplan->RatePlan->CheckIn);
	$currCheckOut =BFCHelper::parseStringDateTime($currRateplan->RatePlan->CheckOut);
	$currCheckIn->setTime($mrcAcceptanceCheckInHours,$mrcAcceptanceCheckInMins,$mrcAcceptanceCheckInSecs);
	$currCheckOut->setTime($mrcAcceptanceCheckOutHours,$mrcAcceptanceCheckOutMins,$mrcAcceptanceCheckOutSecs);

}

if ($rpKey == $hideRateplanOver) {
?>
			<tr>
				<td colspan="4" class="bfishowoverrpl" data-resid="<?php echo $currRateplan->ResourceId ?>"> <?php _e('View other proposals', 'bfi') ?>
				</td>
			</tr>
    
<?php } ?>
			<tr id="data-id-<?php echo $currRateplan->ResourceId ?>-<?php echo $currRateplan->RatePlan->RatePlanId ?>-<?php echo $resRef ?>" class="<?php echo $IsBookable?"bfi-bookable":"bfi-canberequested"; ?>" style="display:<?php echo (($rpKey >= $hideRateplanOver)?'none':'') ?>">
				<td><!-- Min/Max -->
				<?php if ($currRateplan->MaxPaxes>0){?>
					<?php 
					if(!empty( $currRateplan->RatePlan->SuggestedStay ) && !empty( $currRateplan->RatePlan->SuggestedStay->Paxes )){

//echo "<pre style=''>";
//echo $currRateplan->RatePlan->SuggestedStay->TotalPaxes . " - " . $currRateplan->RatePlan->RatePlanId . " - " . $currRateplan->RatePlan->SuggestedStay->Paxes ." - ". $currRateplan->RatePlan->SuggestedStay->ComputedPaxes;
//echo "</pre>";

						
						$computedPaxes = explode("|", $currRateplan->RatePlan->SuggestedStay->Paxes);
						$nadult =0;
						$nsenior =0;
						$nchild =0;
						$currnchildAge = [];

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
								for ($a=0;$a< $currComputedPax[1] ;++$a ) {
									$currnchildAge[]=$currComputedPax[2];
								}
							}
						}
						array_push($currnchildAge, null,null,null,null,null,null);
						$currnchildAge = array_slice($currnchildAge,0,$nchild);
						$totPerson = $nadult+ $nsenior +$nchild;
						if ($nadult>0) {
							?>
							<div class="bfi-icon-paxes">
								<i class="fa fa-user"></i> x <b><?php echo $nadult ?></b>
							<?php 
								if (($nsenior+$nchild)>0) {
									?>
									+ <br />
										<span class="bfi-redux"><i class="fa fa-user"></i></span> x <b><?php echo ($nsenior+$nchild) ?> </b>
									<?php 
									
								}
							?>
							
							</div>
							<div class="webui-popover-content">
							   <div class="bfi-options-popover">
							   <?php echo sprintf(__('Price for %s person', 'bfi') ,$nadult) ?>	
								<?php if (($nchild)>0) { ?>
								, <?php echo $nchild ?> <?php _e('Children', 'bfi') ?> (<?php echo  implode(" ". __('Years', 'bfi') .', ',$currnchildAge) ?> <?php _e('Years', 'bfi') ?>
								<?php } ?>
								</div>
							</div>
							
							<?php 
							
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

				<td style="text-align:center;"><!-- price -->
					<?php if( $currRateplan->Price> 0) {?><!-- disponibile -->
					 <div align="center">
						<div class="bfi-percent-discount" style="<?php echo ($currRateplan->PercentVariation < 0 ? " display:block" : "display:none"); ?>" rel="<?php echo $SimpleDiscountIds ?>" rel1="<?php echo $currRateplan->ResourceId ?>">
							<span class="bfi-percent"><?php echo $currRateplan->PercentVariation ?></span>% <i class="fa fa-question-circle bfi-cursor-helper" aria-hidden="true"></i>
						</div>
					</div>
					<div data-value="<?php echo $currRateplan->TotalPrice ?>" class="bfi-discounted-price bfi_<?php echo $currencyclass ?>" style="display:<?php echo ($currRateplan->Price < $currRateplan->TotalPrice)?"":"none"; ?>;"><?php echo BFCHelper::priceFormat($currRateplan->TotalPrice) ?></div>
					<div data-value="<?php echo $currRateplan->Price ?>" class="bfi-price  <?php echo ($currRateplan->Price < $currRateplan->TotalPrice ? "bfi-red" : "") ?> bfi_<?php echo $currencyclass ?>"><?php echo BFCHelper::priceFormat($currRateplan->Price) ?></div>
					
					<?php }else{?>
						<strong><?php _e('No results available for the submitted data', 'bfi') ?></strong>
					<?php }?>
				</td>
				<td><!-- options -->
					<div style="position:relative;">
					<?php 
$policy = $currRateplan->RatePlan->Policy;
$policyId= 0;
$policyHelp = "";
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
			$currValue = sprintf(__(' %d day/s' ,'bfi'),rtrim($policy->CancellationBaseValue,"n"));
			break;
		default:
			$currValue = '<span class="bfi_' . $currencyclass .'">'. BFCHelper::priceFormat($policy->CancellationBaseValue) .'</span>' ;
	}
	$currValuebefore = $policy->CancellationValue;
	switch (true) {
		case strstr($policy->CancellationValue ,'%'):
			$currValuebefore = $policy->CancellationValue;
			break;
		case strstr($policy->CancellationValue ,'d'):
			$currValuebefore = sprintf(__(' %d day/s' ,'bfi'),rtrim($policy->CancellationValue,"d"));
			break;
		case strstr($policy->CancellationValue ,'n'):
			$currValuebefore = sprintf(__(' %d day/s' ,'bfi'),rtrim($policy->CancellationValue,"n"));
			break;
		default:
			$currValuebefore = '<span class="bfi_' . $currencyclass .'">'. BFCHelper::priceFormat($policy->CancellationValue) .'</span>' ;
	}
	if($policy->CanBeCanceled){
		$currTimeBefore = "";
		$currDateBefore = "";
		if(!empty( $policy->CanBeCanceledCurrentTime )){
				if(!empty( $policy->CancellationTime )){
//					$currDatePolicyparsed = BFCHelper::parseJsonDate($res->RatePlan->CheckIn, 'Y-m-d');
//					$currDatePolicy = DateTime::createFromFormat('Y-m-d',$currDatePolicyparsed,new DateTimeZone('UTC'));
					$currDatePolicy =BFCHelper::parseStringDateTime($res->RatePlan->CheckIn);
					switch (true) {
						case strstr($policy->CancellationTime ,'d'):
							$currTimeBefore = sprintf(__(' %d day/s' ,'bfi'),rtrim($policy->CancellationTime,"d"));	
							$currDatePolicy->modify('-'. rtrim($policy->CancellationTime,"d") .' days'); 
							break;
						case strstr($policy->CancellationTime ,'h'):
							$currTimeBefore = sprintf(__(' %d hour/s' ,'bfi'),rtrim($policy->CancellationTime,"h"));
							$currDatePolicy->modify('-'. rtrim($policy->CancellationTime,"h") .' hours'); 
							break;
						case strstr($policy->CancellationTime ,'w'):
							$currTimeBefore = sprintf(__(' %d week/s' ,'bfi'),rtrim($policy->CancellationTime,"w"));
							$currDatePolicy->modify('-'. rtrim($policy->CancellationTime,"w") .' weeks'); 
							break;
						case strstr($policy->CancellationTime ,'m'):
							$currTimeBefore = sprintf(__(' %d month/s' ,'bfi'),rtrim($policy->CancellationTime,"m"));
							$currDatePolicy->modify('-'. rtrim($policy->CancellationTime,"m") .' months'); 
							break;
					}
				}

				if($policy->CancellationValue=="0" || $policy->CancellationValue=="0%"){
					?>
					<div class="bfi-policy-green"><?php _e('Cancellation FREE', 'bfi') ?>
					<?php 
					if(!empty( $policy->CancellationTime )){
						echo '<br />'.__('until', 'bfi') ;
						echo ' '.$currDatePolicy->format("d").' '.date_i18n('M',$currDatePolicy->getTimestamp()).' '.$currDatePolicy->format("Y");
						$policyHelp = sprintf(__('You may cancel free of charge until %1$s before arrival. You will be charged %2$s if you cancel in the %1$s before arrival.', 'bfi'),$currTimeBefore,$currValue);
					}
					?>
					</div>
					<?php 

					
				}else{
				if($policy->CancellationBaseValue=="0%" || $policy->CancellationBaseValue=="0"){
					?>
					<div class="bfi-policy-green"><?php _e('Cancellation FREE', 'bfi') ?></div>
					<?php 
					$policyHelp = __('You may cancel free of charge until arrival.', 'bfi');
				}else{
					?>
					<div class="bfi-policy-blue"><?php _e('Special conditions', 'bfi') ?></div>
					<?php 
					$policyHelp = sprintf(__('You may cancel with a charge of %3$s  until %1$s before arrival. You will be charged %2$s if you cancel in the %1$s before arrival.', 'bfi'),$currTimeBefore,$currValue,$currValuebefore);
				}
				}

			
		}else{
				if($policy->CancellationBaseValue=="0%" || $policy->CancellationBaseValue=="0"){
					?>
					<div class="bfi-policy-green"><?php _e('Cancellation FREE', 'bfi') ?></div>
					<?php 
					$policyHelp = __('You may cancel free of charge until arrival.', 'bfi');
				}else{
					?>
					<div class="bfi-policy-blue"><?php _e('Special conditions', 'bfi') ?></div>
					<?php 
					$policyHelp = sprintf(__('You will be charged %1$s if you cancel before arrival.', 'bfi'),$currValue);
				}
		}
				
	}else{ 
		// no refundable
		?>
			<div class="bfi-policy-none"><?php _e('Non refundable', 'bfi') ?></div>
		<?php 
		$policyHelp = sprintf(__('You will be charged all if you cancel before arrival.', 'bfi'));
	
	}
}
$currMerchantBookingTypes = array();
$prepayment = "";
$prepaymentHelp = "";

if(!empty( $currRateplan->RatePlan->MerchantBookingTypesString )){
	$currMerchantBookingTypes = json_decode($currRateplan->RatePlan->MerchantBookingTypesString);
	$currBookingTypeId = $currRateplan->RatePlan->MerchantBookingTypeId;
	$currMerchantBookingType = array_filter($currMerchantBookingTypes, function($bt) use($currBookingTypeId) {return $bt->BookingTypeId == $currBookingTypeId;});
	$currMerchantBookingType = array_values($currMerchantBookingType);
	if(count($currMerchantBookingType)>0){
		if($currMerchantBookingType[0]->PayOnArrival){
			$prepayment = __("Pay at the property – NO PREPAYMENT NEEDED", 'bfi');
			$prepaymentHelp = __("No prepayment is needed.", 'bfi');
		}
		if($currMerchantBookingType[0]->AcquireCreditCardData){
			$prepayment = "";
			if($currMerchantBookingType[0]->DepositRelativeValue=="100%"){
				$prepaymentHelp = __('You will be charged a prepayment of the total price at any time.', 'bfi');
			}else if(strpos($currMerchantBookingType[0]->DepositRelativeValue, '%') !== false  ) {
				$prepaymentHelp = sprintf(__('You will be charged a prepayment of %1$s of the total price at any time.', 'bfi'),$currMerchantBookingType[0]->DepositRelativeValue);
			}else{
				$prepaymentHelp = sprintf(__('You will be charged a prepayment of %1$s at any time.', 'bfi'),$currMerchantBookingType[0]->DepositRelativeValue);
			}
		}
	}
}




$allMeals = array();
$cssclassMeals = "bfi-meals-base";
$mealsHelp = "";
if($currRateplan->ItemTypeId==1){
	$currRateplan->RatePlan->IncludedMeals = -1;
}
if($currRateplan->RatePlan->IncludedMeals >-1){
	$mealsHelp = __("There is no meal option with this room.", 'bfi');
	if ($currRateplan->RatePlan->IncludedMeals & bfi_Meal::Breakfast){
		$allMeals[]= __("Breakfast", 'bfi');
	}
	if ($currRateplan->RatePlan->IncludedMeals & bfi_Meal::Lunch){
		$allMeals[]= __("Lunch", 'bfi');
	}
	if ($currRateplan->RatePlan->IncludedMeals & bfi_Meal::Dinner){
		$allMeals[]= __("Dinner", 'bfi');
	}
	if ($currRateplan->RatePlan->IncludedMeals & bfi_Meal::AllInclusive){
		$allMeals[]= __("All Inclusive", 'bfi');
	}
	if(in_array(__("Breakfast", 'bfi'), $allMeals)){
		$cssclassMeals = "bfi-meals-bb";
	}
	if(in_array(__("Lunch", 'bfi'), $allMeals) || in_array(__("Dinner", 'bfi'), $allMeals) || in_array(__("All Inclusive", 'bfi'), $allMeals)  ){
		$cssclassMeals = "bfi-meals-fb";
	}
	if(count($allMeals)>0){
		$mealsHelp = implode(", ",$allMeals). " " . __('included', 'bfi');
	}
	if(count($allMeals)==2){
		$mealsHelp = implode(" & ",$allMeals). " " . __('included', 'bfi');
	}
}
?>
						
<?php if(!empty($prepayment)) { ?>
						<div class="bfi-prepayment"><?php echo $prepayment ?></div>
<?php } ?>

						<div class="bfi-meals <?php echo $cssclassMeals?>"><?php echo $mealsHelp ?></div>
						<div class="bfi-options-help">
							<i class="fa fa-question-circle bfi-cursor-helper" aria-hidden="true"></i>
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
				<td>
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
					<select class="ddlrooms ddlrooms-<?php echo $currRateplan->ResourceId ?> ddlroomsrealav-<?php echo $currRealAvailProductId ?> ddlrooms-indipendent" 
					id="ddlrooms-<?php echo $currRateplan->ResourceId ?>-<?php echo $currRateplan->RatePlan->RatePlanId ?>-<?php echo $resRef ?>" 
					onclick="bookingfor.checkMaxSelect(this);bookingfor.ddlroomsgroupclk(this);" 
					onchange="bookingfor.checkBookable(this);bfi_UpdateQuote(this);" 
					data-referencid="<?php echo $currRateplan->RatePlan->RatePlanId ?>-<?php echo $resRef ?>" 
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
					>
					<?php 
						foreach ($availability as $number) {
							?> <option value="<?php echo $number ?>" <?php echo ($selectedtAvailability== $number)?"selected":""; //selected( $selectedtAvailability, $number ); ?>><?php echo $number ?></option><?php
						}
					?>
					</select>
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
	} // if (!empty($currResRateplans)) {
 } 
 ?>
			</tbody>
		</table>


<!-- end list bfi-table-resources -->


<!-- Service -->
<script>
    var showServiceGrouped= <?php echo $showServiceGrouped ?>;
</script>
<?php if(count($allResourceId)>0){ ?>
    <div class="bfi-table-resources-step2 bfi-table-responsive" style="display:none;">

	<br /><?php  bfi_get_template("shared/menu_small_booking.php");  ?>
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

		
		
	$currResRateplans =  array_filter($allRatePlans, function($p) use ($currResourceId,$currIsBookable) {return ($p->ResourceId == $currResourceId) && ($p->IsBookable == $currIsBookable) ;}); // c#: allRatePlans.Where(p => p.ResourceId == resId);
	if (!empty($currResRateplans)) {
    
	$firstSelection = [];
	foreach($firstPackage as $tmpResRateplans) {
		$alreadyTaken =  current(array_filter($currResRateplans, function($p) use ($tmpResRateplans) {
							if ($p->RatePlan->RatePlanId == $tmpResRateplans->RatePlan->RatePlanId && $p->RatePlan->SuggestedStay->Paxes == $tmpResRateplans->RatePlan->SuggestedStay->Paxes) {
								return $p ;
							}
						})); // c#: allRatePlans.Where(p => p.ResourceId == resId);
		if (!empty($alreadyTaken )) {			
			$keyfirst = array_search($alreadyTaken, $currResRateplans);
			unset($currResRateplans[$keyfirst]);
			$firstSelection[$keyfirst] =  $alreadyTaken;
		}
	}

// Build temporary array for array_unique
	usort($currResRateplans, "BFCHelper::bfi_sortMultiRatePlans");

	$resRateplans = $currResRateplans;
	if (!empty($firstSelection )) {
//		echo "<pre>firstSelection";
//		echo print_r($firstSelection);
//		echo "</pre>";
		$resRateplans = array_merge($firstSelection, $resRateplans);
	}
		
		
		
//		$resRateplans =  array_filter($allRatePlans, function($p) use ($currResourceId) {return $p->ResourceId == $currResourceId ;}); // c#: allRatePlans.Where(p => p.ResourceId == resId);
//		
//		usort($resRateplans, "BFCHelper::bfi_sortResourcesRatePlans");
		$res = array_values($resRateplans)[0];

	foreach($resRateplans as $currRateplan) {
		$resRef += 1;
//		$selectablePrices = json_decode($currRateplan->RatePlan->CalculablePricesString);
		$selectablePrices = $currRateplan->RatePlan->CalculablePrices;
		if (count($selectablePrices) == 0)
		{
			continue; //don't display table skip to next
		}
		
		$SimpleDiscountIds = "";

		if(!empty($currRateplan->RatePlan->AllVariationsString)){
//			$allVar = json_decode($currRateplan->RatePlan->AllVariationsString);
			$allVar = $currRateplan->RatePlan->AllVariations;
			$SimpleDiscountIds = implode(',',array_unique(array_map(function ($i) { return $i->VariationPlanId; }, $allVar)));
		}

		$currUriresource = $uri.$res->ResourceId . '-' . BFCHelper::getSlug($res->Name) . "?fromsearch=1&lna=".$listNameAnalytics;
//		$currUriresource = $uri.'&resourceId='.$currRateplan->ResourceId . '-' . BFCHelper::getSlug($currRateplan->Name);
////				if ($itemId<>0){
////					$currUriresource.='&Itemid='.$itemId;
////				}
//				$currUriresource .= "&fromsearch=1&lna=".$listNameAnalytics;
//				$currUriresource = JRoute::_($currUriresource);

				$merchantNameTrack =  BFCHelper::string_sanitize($merchant->Name);
				$resourceNameTrack =  BFCHelper::string_sanitize($currRateplan->Name);
				$merchantCategoryNameTrack =  BFCHelper::string_sanitize($merchant->MainCategoryName);

				$currCheckIn = $checkin;
				$currCheckOut = $checkout;
				$currnadult =0;
				$currnsenior =0;
				$currnchild =0;
				$currnchildAge = [];
				if(!empty( $currRateplan->RatePlan->SuggestedStay )) {
					$currCheckIn = DateTime::createFromFormat('Y-m-d\TH:i:s',$currRateplan->RatePlan->SuggestedStay->CheckIn,new DateTimeZone('UTC'));
					$currCheckOut = DateTime::createFromFormat('Y-m-d\TH:i:s',$currRateplan->RatePlan->SuggestedStay->CheckOut,new DateTimeZone('UTC'));					

					if(!empty( $currRateplan->RatePlan->SuggestedStay->Paxes )){

						$currPaxes = explode("|", $currRateplan->RatePlan->SuggestedStay->Paxes);						
						foreach($currPaxes as $computedPax) {
							$currComputedPax =  explode(":", $computedPax."::::");
							
							if ($currComputedPax[3] == "0") {
								$currnadult += $currComputedPax[1];
							}
							if ($currComputedPax[3] == "1") {
								$currnsenior += $currComputedPax[1];
							}
							if ($currComputedPax[3] == "2") {
								$currnchild += $currComputedPax[1];
								for ($a=0;$a< $currComputedPax[1] ;++$a ) {
									$currnchildAge[]=$currComputedPax[2];
								}
							}
						}
					}
				}
								
				array_push($currnchildAge,0,0,0,0,0,0);
				$currtotPerson = $currnadult+ $currnsenior +$currnchild;
?>
					
		<div class="services-room-1-<?php echo $currRateplan->ResourceId ?>-<?php echo $currRateplan->RatePlan->RatePlanId ?>-<?php echo $resRef ?> bfi-table-responsive"  style="display:none;">
		<form method="post" action="" class="bfi-groupdform">
			<input type="hidden" name="checkin" value="<?php echo $currCheckIn->format('d/m/Y') ?>" />
			<input type="hidden" name="checkout" value="<?php echo $currCheckOut->format('d/m/Y') ?>" />
			<input name="checkAvailability" type="hidden" value="1" />
			<input name="checkStays" type="hidden" value="1" />
			<input type="hidden" name="adults" value="<?php echo $currnadult ?>" />
			<input type="hidden" name="children" value="<?php echo $currnchild ?>" />
			<input type="hidden" name="seniores" value="<?php echo $currnsenior ?>" />
			<input type="hidden" name="childages1" value="<?php echo $currnchildAge[0] ?>" />
			<input type="hidden" name="childages2" value="<?php echo $currnchildAge[1] ?>" />
			<input type="hidden" name="childages3" value="<?php echo $currnchildAge[2] ?>" />
			<input type="hidden" name="childages4" value="<?php echo $currnchildAge[3] ?>" />
			<input type="hidden" name="childages5" value="<?php echo $currnchildAge[4] ?>" />

		</form>
		<div class="bfi-resname-extra"><a  class="bfi-resname eectrack" href="<?php echo $currUriresource ?>" <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> data-type="Resource" data-id="<?php echo $currRateplan->ResourceId?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php echo $currRateplan->Name; ?></a>
				<?php if ($currRateplan->MaxPaxes>0){?>
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
						if ($nadult>0) {
							?>
							(<div class="bfi-icon-paxes">
								<i class="fa fa-user"></i> x <b><?php echo $nadult ?></b>
							<?php 
								if (($nsenior+$nchild)>0) {
									?>
									+ 
										<span class="bfi-redux"><i class="fa fa-user"></i></span> x <b><?php echo ($nsenior+$nchild) ?> </b>
									<?php 
									
								}
							?>
							
							</div>
							<div class="webui-popover-content">
							   <div class="bfi-options-popover">
							   <?php echo sprintf(__('Price for %s person', 'bfi') ,$totPerson) ?>
								</div>
							</div>
							)
							<?php 
							
						}


					}else{
					?>
						<?php if ($currRateplan->MaxPaxes>0){?>
						(<div class="bfi-icon-paxes">
							<i class="fa fa-user"></i> 
							<?php if ($currRateplan->MaxPaxes==2 && $currRateplan->MinPaxes==2){?>
							<i class="fa fa-user"></i> 
							<?php }?>
							<?php if ($currRateplan->MaxPaxes>2){?>
								<?php echo ($currRateplan->MinPaxes != $currRateplan->MaxPaxes)? $currRateplan->MinPaxes . "-" : "" ?><?php echo  $currRateplan->MaxPaxes ?>
							<?php }?>
						</div>(
						<?php }?>
					<?php } ?>
				<?php } ?>
		</div>
		<div class="bfi-clearfix"></div>
		<?php  if(false && !empty($currRateplan->ImageUrl)){
			$resourceImageUrl = BFCHelper::getImageUrlResized('resources',$currRateplan->ImageUrl, 'small');
		?>
		<a  class="bfi-link-searchdetails" href="<?php echo $currUriresource ?>" <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?>><img src="<?php echo $resourceImageUrl; ?>" class="bfi-img-searchdetails" /></a>
		<div class="bfi-clearfix"></div>
		<?php } ?>
		<!-- bfi-table-selectableprice -->
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
					<?php echo $selPrice->Name; ?>
					<br />
					<?php
/*-----------scelta date e ore--------------------*/	
									if ($selPrice->AvailabilityType == 2)
									{
										$currCheckIn = DateTime::createFromFormat('Y-m-d\TH:i:s',$selPrice->CheckIn,new DateTimeZone('UTC'));
										$currCheckOut = DateTime::createFromFormat('Y-m-d\TH:i:s',$selPrice->CheckOut,new DateTimeZone('UTC'));
										$currDiff = $currCheckOut->diff($currCheckIn);

   
										$loadScriptTimePeriod = true;
										
										$timeDurationview = $currDiff->h + round(($currDiff->i/60), 2);
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
										<div class="bfi-timeperiod bfi-cursor" id="bfi-timeperiod-<?php echo $selPrice->RelatedProductId ?>" 
											data-resid="<?php echo $selPrice->RelatedProductId ?>" 
											data-checkin="<?php echo $currCheckIn->format('Ymd') ?>"
											data-checkintime="<?php echo $currCheckIn->format('YmdHis') ?>"
											data-timeminstart="<?php echo $currCheckIn->format('His') ?>"
											data-timeminend="<?php echo $currCheckOut->format('His') ?>"
											data-duration="<?php echo $timeDuration ?>"
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
												<div class="bfi-col-md-9 bfi-text-right"><span class="bfi-total-duration"><?php echo $timeDurationview  ?></span> <?php _e('hours', 'bfi') ?>
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
												<div class="bfi-col-md-9 bfi-text-right"><span class="bfi-total-duration"><?php echo $currDiff->format('%h') ?></span> <?php _e('hours', 'bfi') ?>
												</div>	
											</div>	
										</div>
								<?php
									}								

/*-------------------------------*/									
							?>

					</td>
					<td>
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
					</td>
					<td style="text-align:center;"><!-- price -->
						<?php
						$percentVariation = $selPrice->TotalAmount > 0 ? (int)((($selPrice->TotalDiscounted - $selPrice->TotalAmount) * 100) / $selPrice->TotalAmount) : 0;
						?>
						<div class="bfi-totalextrasselect" style="<?php echo ($selPrice->TotalDiscounted==0) ? "display:none;" : ""; ?>">
							<div align="center">
								<div class="bfi-percent-discount" style="<?php echo ($percentVariation < 0 ? " display:block" : "display:none"); ?>" rel="<?php echo $SimpleDiscountIds ?>" rel1="<?php echo $res->ResourceId ?>">
									<span class="bfi-percent"><?php echo $percentVariation ?></span>% <i class="fa fa-question-circle bfi-cursor-helper" aria-hidden="true"></i>
								</div>
							</div>
							<div data-value="<?php echo $selPrice->TotalAmount ?>" class="bfi-discounted-price bfi_<?php echo $currencyclass ?>" style="display:<?php echo ($selPrice->TotalDiscounted < $selPrice->TotalAmount)?"":"none"; ?>;"><?php echo BFCHelper::priceFormat($selPrice->TotalAmount) ?></div>
							<div data-value="<?php echo $selPrice->TotalDiscounted?>" class="bfi-price  <?php echo ($currRateplan->Price < $selPrice->TotalDiscounted ? "bfi-red" : "") ?> bfi_<?php echo $currencyclass ?>"><?php echo BFCHelper::priceFormat($selPrice->TotalDiscounted) ?></div>
						</div>
					</td>
					<td><!-- options -->

					</td>
					<td>
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
						<select class="ddlrooms ddlrooms-<?php echo $selPrice->RelatedProductId?> ddlroomsrealav-<?php echo $selPrice->RelatedProductId ?> ddlextras inputmini" 
							onchange="<?php echo $clickFunction ?>" 
							data-referencid="<?php echo $currRateplan->RatePlan->RatePlanId ?>-<?php echo $resRef ?>" 
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
							>
							<?php 
								foreach ($availability as $number) {
									?> <option value="<?php echo $number ?>" <?php echo ($selPrice->CalculatedQt == $number)?"selected":""; //selected( $selectedtAvailability, $number ); ?>><?php echo $number ?></option><?php
								}
							?>
						</select>
					</td>
				</tr>
<?php 
$countPrices+=1;
		}//end foreach selPrices
?>

			</tbody>
		</table>
		<!-- end bfi-table-selectableprice -->
		</div>
<?php 
	}//end foreach bfi-table-resources-step2 resRateplans
	}//end foreach bfi-table-resources-step2 allResourceId

	}//end foreach bfi-table-resources-step2 allResourceId
?>
			</td>
			<td>
				<div class="totalextrasstay bfi-book-now" >
						<?php echo sprintf(__('Group price for %s nights:', 'bfi') ,$duration) ?>
							<br />
					<div class="bfi-resource-total"><span></span> <?php _e('selected items', 'bfi') ?></div>
					<div class="bfi-extras-total"><span></span> <?php _e('selected services', 'bfi') ?></div> 
					<div class="bfi-discounted-price bfi-discounted-price-total bfi_<?php echo $currencyclass ?>" style="display:<?php echo ($groupprice < $grouptotalprice)?"":"none"; ?>;"><?php echo BFCHelper::priceFormat($grouptotalprice) ?></div>
					<div class="bfi-price-total <?php echo ($groupprice < $grouptotalprice? "bfi-red" : "") ?> bfi_<?php echo $currencyclass ?>"><?php echo BFCHelper::priceFormat($groupprice) ?></div>
<?php if($IsBookable) { ?>
					<div class="bfi-btn bfi-btn-book-now" onclick="bookingfor.BookNow(this);">
						<?php _e('Book Now', 'bfi') ?>
					</div>
<?php }else{ ?>
					<div class="bfi-btn bfi-alternative bfi-request-now" onclick="bookingfor.BookNow(this);">
						<?php _e('Request Now', 'bfi') ?>
					</div>
<?php } ?>
				</div>
			</td>
		</tr>
</table>
    </div>
<?php 
 } //end if(!empty bfi-table-resources-step2

//
//$checkin = new JDate($checkin->format('Y-m-d\TH:i:s')); 
//$checkout = new JDate($checkout->format('Y-m-d\TH:i:s')); 
//
//$checkin = new JDate($checkin);
?>
</div>
<form action="<?php echo $formOrderRouteBook ?>" class="frm-order" method="post"></form>
	
<script type="text/javascript">
var productAvailabilityType = <?php echo $ProductAvailabilityType?>;
var allStays = <?php echo json_encode($allRatePlans) ?>; 

var dialogForm;
var bfi_wuiP_width= 800;

    var totalOrderPriceLoaded = false;
    var totalOrderPrice = 0;
    var totalOrderPriceWhitOutDiscount = 0;



//-->
</script>
<script type="text/javascript">
	

<?php if(COM_BOOKINGFORCONNECTOR_GAENABLED == 1 && !empty(COM_BOOKINGFORCONNECTOR_GAACCOUNT) && COM_BOOKINGFORCONNECTOR_EECENABLED == 1 ){ ?>
jQuery(function($) {
	<?php if(isset($eecmainstay)){ ?>
	callAnalyticsEEc("addProduct", [<?php echo json_encode($eecmainstay); ?>], "item");
	<?php } ?>
});
<?php } ?>
<?php if(isset($criteoConfig) && !empty($criteoConfig) && $criteoConfig->enabled){ ?>
window.criteo_q = window.criteo_q || []; 
window.criteo_q.push( 
	{ event: "setAccount", account: <?php echo $criteoConfig->campaignid ?>}, 
	{ event: "setSiteType", type: "d" }, 
	{ event: "viewSearch", checkin_date: "<?php echo $checkin->format('d/m/Y') ?>", checkout_date: "<?php echo $checkout->format('d/m/Y') ?>"},
	{ event: "setEmail", email: "" }, 
	{ event: "viewItem", item: "<?php// echo $criteoConfig->merchants[0] ?>" }
);
<?php } ?>

</script>
<!-- --------------------------------------------------------------------------------------------------------------------------------------------------------- -->
<?php if($loadScriptTimePeriod || $loadScriptTimeSlot) { ?>
    <script>
        //TimeSlot
        var strAlternativeDateToSearch = "<?php echo $alternativeDateToSearch->format('d/m/Y') ?>";
        var strEndDate = "<?php echo $checkout->format('d/m/Y') ?>";
        var dateToUpdate = <?php echo $checkin->format('Ymd') ?>;
		jQuery(document).ready(function() {
			if(jQuery(".ui-dialog").length){
				jQuery(".ui-dialog").remove();
			}
		});

    </script>
<?php } ?>

<?php if($loadScriptTimePeriod) { 
	$listDayTP = array();
	$allTimePeriodResourceId = array_unique($allTimePeriodResourceId);
	foreach ($allTimePeriodResourceId as $resId) { 
		$listDayTP[$resId] = json_decode(BFCHelper::GetCheckInDatesPerTimes($resId,$alternativeDateToSearch,$duration+2));
	}
	?>
    <script>
        var txtSelectADay = "<?php _e('Please, select a day', 'bfi') ?>";
        var daysToEnableTimePeriod = <?php echo json_encode($listDayTP) ?>; 
        var strbuttonTextTimePeriod = "<?php echo date_i18n('D',$checkin->getTimestamp()) ?> <?php echo $checkin->format("d") ?> <?php echo date_i18n('M',$checkin->getTimestamp()).' '.$checkin->format("Y") ?>";
        var urlGetCompleteRatePlansStay = bookingfor.getActionUrl(null, null, "getCompleteRateplansStay");
        var urlGetListCheckInDayPerTimes = bookingfor.getActionUrl(null, null, "getListCheckInDayPerTimes");

		var dialogTimeperiod;
		jQuery(document).ready(function() {
			if (typeof daysToEnableTimePeriod !== 'undefined' && typeof initDatepickerTimePeriod !== 'undefined' && jQuery.isFunction(initDatepickerTimePeriod)) {
				initDatepickerTimePeriod();
			}
			jQuery("#bfi-timeperiod-select").attr("data-resid",0);
//			jQuery(".bfi-timeperiod-change").dialog('destroy');
			dialogTimeperiod = jQuery("#bfimodaltimeperiod").dialog({
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
			jQuery(".bfi-result-list").on("click",".bfi-timeperiod", function (e) {
//			debugger;
				var currResId = jQuery("#bfi-timeperiod-select").attr("data-resid");
				var newResId = jQuery(this).attr("data-resid");
				var newDate = jQuery(this).attr("data-checkin");
//				if(currResId!=newResId &&  bfi_currTRselected != jQuery(this).closest("tr")){
				if (bfi_currTRselected != jQuery(this).closest("tr")){
					bfi_currTRselected = jQuery(this).closest("tr");
					jQuery("#bfi-timeperiod-select").attr("data-resid", newResId);
					jQuery("#selectpickerTimePeriodStart").attr("data-resid", newResId);
					jQuery("#bfi-timeperiod-select").attr("data-checkin", newDate);
					jQuery("#bfimodaltimeperiodcheckin").attr("data-resid", newResId);
					jQuery("#bfimodaltimeperiodcheckin").datepicker("setDate", jQuery.datepicker.parseDate( "yymmdd", newDate) );
					dateTimePeriodChanged(jQuery("#bfimodaltimeperiodcheckin"),jQuery("#bfimodaltimeperiodcheckin").datepicker("getDate"))
//					updateTimePeriodRange(newDate, newResId, jQuery("#bfimodaltimeperiod"));
//					jQuery('.ui-datepicker-current-day').click();
//					var currCheckinhours = jQuery(this).find(".bfi-time-checkin-hours").first();
//					var currCheckouthours = jQuery(this).find(".bfi-time-checkout-hours").first();
//					jQuery("#selectpickerTimePeriodStart").val(currCheckinhours.html());
//					jQuery("#selectpickerTimePeriodEnd").val(currCheckinhours.html());
				}

//				var currMess = jQuery("#bfi-timeperiod-change-"+jQuery(this).attr("data-id")).clone(true,true);
//				jQuery("#bfi-timeperiod-source-"+jQuery(this).attr("data-id")).empty();
//				jQuery("#bfi-timeperiod-change-"+jQuery(this).attr("data-id"));
//				jQuery.blockUI({ message: currMess }); 
//				jQuery("#bfi-timeperiod-change-"+jQuery(this).attr("data-id")).show();
				dialogTimeperiod.dialog( "open" );

			 });
//            jQuery(".ChkAvailibilityFromDateTimePeriod:not(.extraprice)").each(function(){
//                updateTimePeriodRange(<?php echo $checkin->format('Ymd') ?>, jQuery(this).attr("data-id"), jQuery(this));
//            });
        });
    </script>
<?php } ?>

<?php if($loadScriptTimeSlot) { 
	$allTimeSlotResourceId = array_unique($allTimeSlotResourceId);
//	foreach ($allTimeSlotResourceId as $resId) { 
//		$listDayTS[$resId] = json_decode(BFCHelper::GetCheckInDatesTimeSlot($resId,$alternativeDateToSearch));
//	}

	?>
    <script>
        //TimeSlot
        var strbuttonTextTimeSlot = "<?php echo date_i18n('D',$checkin->getTimestamp()) ?> <?php echo $checkin->format("d") ?> <?php echo date_i18n('M',$checkin->getTimestamp()).' '.$checkin->format("Y") ?>";
        var daysToEnableTimeSlot = <?php echo json_encode($listDayTS) ?>;
        var currTimeSlotDisp = {};
		var dialogTimeslot;
		jQuery(document).ready(function () {
			initDatepickerTimeSlot();
			jQuery("#bfi-timeslot-select").attr("data-resid",0);
			jQuery("#bfi-timeslot-select").attr("data-sourceid",0);
			dialogTimeslot = jQuery("#bfimodaltimeslot").dialog({
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
				
				var currResId = jQuery("#bfi-timeslot-select").attr("data-resid");
				var newResId = jQuery(this).attr("data-resid");
				var newDate = jQuery(this).attr("data-checkin");
				
//				if(currSourceId!=newSourceId ){
				if (bfi_currTRselected != jQuery(this).closest("tr")){
					bfi_currTRselected = jQuery(this).closest("tr");
					jQuery("#bfi-timeslot-select").attr("data-sourceid", newSourceId);
					jQuery("#bfi-timeslot-select").attr("data-resid", newResId);
					jQuery("#selectpickerTimeSlotRange").attr("data-resid", newResId);
					jQuery("#selectpickerTimeSlotRange").attr("data-sourceid", newSourceId);
					jQuery("#bfi-timeslot-select").attr("data-checkin", newDate);
					jQuery("#bfimodaltimeslotcheckin").attr("data-resid", newResId);
					jQuery("#bfimodaltimeslotcheckin").datepicker("setDate", jQuery.datepicker.parseDate( "yymmdd", newDate) );
					dateTimeSlotChanged(jQuery("#bfimodaltimeslotcheckin"));
				}
				dialogTimeslot.dialog( "open" );

			 });

		});
    </script>

<?php } ?>
<script type="text/javascript">
<!--
var bfi_currMerchantId = <?php echo $merchant->MerchantId ?>;
var bfi_currAdultsAge = <?php echo $defaultAdultsAge ?>;
var bfi_currSenioresAge = <?php echo $defaultSenioresAge ?>;

var bfisrv = [];
var imgPathMG = "<?php echo BFCHelper::getImageUrlResized('tag','[img]', 'merchant_merchantgroup') ?>";
var imgPathMGError = "<?php echo BFCHelper::getImageUrl('tag','[img]', 'merchant_merchantgroup') ?>";
var resGrp = [];
var loadedResGrp=false;

function bfiUpdateInfoResGrp(){
	jQuery(".bfiresourcetags, .bfiresourcetagshighlighted").each(function(){
		var currObj = jQuery(this);
		var currList = currObj.attr("rel");
		var currArea = Number(currObj.attr("data-area"));
		if (currList!= null && currList!= '' || currArea>0)
		{
			var srvArr = [];
			if (currArea>0)
			{
				srvArr.push('<i class="fa fa-exchange"></i> '+ currArea + ' m²');
			}
			if (currList!= null && currList!= '')
			{
				var srvlist = currList.split(',');
				jQuery.each(srvlist, function(key, srvid) {
					if(typeof bookingfor.tagFullLoaded[srvid] !== 'undefined' ){
						val = bookingfor.tagFullLoaded[srvid];
						if (currObj.hasClass("bfiresourcetags"))
						{
							srvArr.push('<i class="fa fa-check"></i> '+val.Name);
						}else {
							if (val.ImageUrl != null && val.ImageUrl != '') {
								var $imageurl = imgPathMG.replace("[img]", val.ImageUrl );
								var $imageurlError = imgPathMGError.replace("[img]", val.ImageUrl );
								srvArr.push('<img src="' + $imageurl + '" onerror="this.onerror=null;this.src=\'' + $imageurlError + '\';" alt="' + val.Name + '" data-toggle="tooltip" title="' + val.Name + '" /> ' + val.Name);
							} else if (val.IconSrc != null && val.IconSrc != '') {
								if (val.IconType != null && val.IconType != '')
								{
									var fontIcons = val.IconType .split(";");
									if (fontIcons[0] == 'fontawesome5')
									{
										srvArr.push('<i class="' + val.IconSrc + '" data-toggle="tooltip" title="' + val.Name + '"> </i> '+ val.Name);
									}
									if (fontIcons[0] == 'fontawesome4')
									{
										srvArr.push('<i class="fa ' + val.IconSrc + '" data-toggle="tooltip" title="' + val.Name + '"> </i> '+ val.Name);
									}

								}else{
									srvArr.push('<i class="fa ' + val.IconSrc + '" data-toggle="tooltip" title="' + val.Name + '"> </i> '+ val.Name);
								}
							} else {
								srvArr.push('<i class="fa fa-check"></i> '+val.Name);
							}
						}
					}
				});
				jQuery(this).html(srvArr.join(" "));
			}
		}
	});
}

function bfiSearchFromPck(){
	//submit
	currQt = jQuery("#bfi-minrooms<?php echo $currModID ?>").val();
	currPaxes = 0;
	currPaxages = [];
	var totalAdults = 0;
	var totalSeniors = 0;
	var totalChildren = 0;
	var totalChildrenAges = [];
	
	for (ipck = 1;ipck<=currQt ;ipck++ )
	{
		currPck = jQuery('#bfi_paxes_package_'+ipck);
		currPck.show();

		var currContainer = jQuery(currPck).closest(".bfi_paxes_package").first();	
		var numAdults = new Number( jQuery(currContainer).find(".bfi_resource-package-adult select").val() || 0);
		var numSeniores = new Number( jQuery(currContainer).find(".bfi_resource-package-senior select").val() || 0);
		var numChildren = new Number(jQuery(currContainer).find(".bfi_resource-package-children select").val() || 0);
		//currPaxes += numAdults + numSeniores + numChildren;
		
		totalAdults += numAdults;
		totalSeniors += numSeniores;
		totalChildren += numChildren;
		
		for (i=1;i<=numAdults; i++)
		{
//			paxages += "|<?php echo $defaultAdultsAge ?>:<?php echo bfiAgeType::$Adult ?>:" + (ipck-1);
			currPaxages.push("<?php echo $defaultAdultsAge ?>:<?php echo bfiAgeType::$Adult ?>:" + (ipck));
		}
		for (i=1;i<=numSeniores; i++)
		{
//			paxages += "|<?php echo $defaultSenioresAge ?>:<?php echo bfiAgeType::$Seniors ?>:" + (ipck-1);
			currPaxages.push("<?php echo $defaultSenioresAge ?>:<?php echo bfiAgeType::$Seniors ?>:" + (ipck));
		}

		jQuery(currContainer).find(".bfi_resource-package-childrenages select:visible").each(function(i) {
//			paxages +=  "|" + jQuery(this).val() + ":<?php echo bfiAgeType::$Reduced ?>:" + (ipck-1);
			currPaxages.push(jQuery(this).val() + ":<?php echo bfiAgeType::$Reduced ?>:" + (ipck));
			totalChildrenAges.push(jQuery(this).val());
		});
	}
	jQuery("#bfi-package input[name=paxes]").val(currPaxes);
	jQuery("#bfi-package input[name=paxages]").val(currPaxages.join("|"));
	jQuery("#bfi-package input[name=adultssel]").val(totalAdults);
	jQuery("#bfi-package input[name=senioressel]").val(totalSeniors);
	jQuery("#bfi-package input[name=childrensel]").val(totalChildren);
	
	totalChildrenAges.forEach(function (ag, i) {
		jQuery("#bfi-package").append("<input name='childagessel" + (i + 1) + "' type='hidden' value='" + ag + "' />");
		jQuery("#bfi-package").append("<input name='childages" + (i + 1) + "' type='hidden' value='" + ag + "' />");
	});
	
	var currDateFormat = "dd/mm/yy";
	currCheckin.datepicker( "option", "dateFormat",currDateFormat );
	currCheckout.datepicker( "option", "dateFormat", currDateFormat );
	jQuery('#bfi-package').submit();

}

function bfiQuoteQtPckChanged(){
	currQt = jQuery("#bfi-minrooms<?php echo $currModID ?>").val();
	jQuery('.bfi_paxes_package').hide();
	for (i = 1;i<=currQt ;i++ )
	{
		currPck = jQuery('#bfi_paxes_package_'+i);
		currPck.show();
		checkPackageChildren(currPck);

	}
}
function bfiQuotePaxesPckChanged(currObj){
	checkPackageChildren(currObj)
}

function checkPackageChildren(currObj) {
//		debugger;
	var currContainer = jQuery(currObj).closest(".bfi_paxes_package").first();	
	var nch = jQuery(currContainer).find(".bfi_resource-package-children select").val();	
	jQuery(currContainer).find(".bfi_resource-package-childrenages").hide();
	jQuery(currContainer).find(".bfi_resource-package-childrenages select").hide();
	if (nch > 0) {
		jQuery(currContainer).find(".bfi_resource-package-childrenages select").each(function(i) {
			if (i < nch) {
				var id=jQuery(this).attr('id');
				jQuery(this).css('display', 'inline-block');
			}
		});
		jQuery(currContainer).find(".bfi_resource-package-childrenages").show();
	}
	
}

var currCheckin, currCheckout,
	currCheckinTime,currCheckoutTime,
	currResourceid=<?php echo !empty($resourceId)? $resourceId : 0; ?>;
var daysToEnable = {};
var checkOutDaysToEnable = {};
var localeSetting = "<?php echo substr($language,0,2); ?>";

var calculator_checkin = null;
var calculator_checkout = null;
var bficalculatordpmode='';

var bfi_wuiP_width= 800;
function bfishowsearchPck() {
	if(jQuery(window).width()<bfi_wuiP_width){
		bfi_wuiP_width = jQuery(window).width();
	}
	if (!!jQuery.uniform){
		jQuery.uniform.restore(jQuery("#bfi-package select"));
	}
	bfi_inizializeDialog();
	currCheckin.datepicker("disable");
	currCheckout.datepicker("disable");
	dialogForm = jQuery( "#bfi-package" ).dialog({
			title:"<?php _e('Change your details', 'bfi') ?>",
			autoOpen: false,
			width:bfi_wuiP_width,
			modal: true,
			dialogClass: 'bfi-dialog bfi-dialog-calculate ',
			clickOutside: true,
			closeText: "",
			open: function(event, ui) {
				// prevent auto open datepicker
				currCheckin.datepicker("enable");
				currCheckout.datepicker("disable");
			}
	});
	dialogForm.dialog( "open" );

}

function bfiOpen<?php echo $checkoutId; ?>() {
	setTimeout(function() {
		jQuery("#<?php echo $checkoutId; ?>").datepicker("show");
	}, 1);		
}
function dateCalculatorChanged(getDate,callback) {
	if (callback) {
		callback();
	}
}
function bfi_checkDateBooking(selectedDate) {
	instance = currCheckin.data("datepicker");
	date = jQuery.datepicker.parseDate(
			instance.settings.dateFormat ||
			jQuery.datepicker._defaults.dateFormat,
			selectedDate, instance.settings);

	var offsetDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());

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
}

jQuery(document).ready(function () {
	currCheckin = jQuery('#<?php echo $checkinId; ?>');
	currCheckout = jQuery('#<?php echo $checkoutId; ?>');
	currCheckinTime = jQuery('#checkintimedetailsselect');
	currCheckoutTime = jQuery('#checkouttimedetailsselect');

	bfi_UpdateQuote(jQuery(".bfi-table-resourcessearchdetails"));
	bfiQuoteQtPckChanged()
//	getAjaxInformationsResGrp();
	bookingfor.bfiGetAllTags(bfiUpdateInfoResGrp);
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
					onSelect: function(date) {  
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
				if (jQuery("#bfi-package").attr('data-checkinTime') ) {
					fcheckinTime = jQuery("#bfi-package").attr('data-checkinTime') ;
				}
				if (jQuery("#bfi-package").attr('data-checkoutTime') ) {
					fcheckoutTime = jQuery("#bfi-package").attr('data-checkoutTime') ;
				}
				jQuery(document).on('change', "#checkintimedetailsselect" ,  function(e) {
					dateCalculatorChanged(true,bfiOpen<?php echo $checkoutId; ?>);
				});
			}
			

			//fix Google Translator and datepicker
			jQuery('.ui-datepicker').addClass('notranslate');
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
			var currForm = jQuery("#bfi-package");
			var fcheckinTime;
			var fcheckoutTime;
			if (jQuery("#bfi-package").attr('data-checkinTime') ) {
				fcheckinTime = jQuery("#bfi-package").attr('data-checkinTime') ;
			}
			if (jQuery("#bfi-package").attr('data-checkoutTime') ) {
				fcheckoutTime = jQuery("#bfi-package").attr('data-checkoutTime') ;
			}
			bfi_timeStepCheckin(currCheckin, "#checkintimedetailsselect", [] , <?php echo $timeLength ?>, fcheckinTime);
			bfi_timeStepCheckin(currCheckout, "#checkouttimedetailsselect", [], <?php echo $timeLength ?>, fcheckoutTime, true);

			if (typeof bfi_printChangedDate !== "undefined" ){bfi_printChangedDate(currForm) ;}
			
			
			bfi_inizialized = true;
		}

	}

jQuery(".bfishowresourcetags").click(function() {
			jQuery(this).toggleClass("bfishowresourcetags-active");
			jQuery(this).prev().toggle("slow");
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
