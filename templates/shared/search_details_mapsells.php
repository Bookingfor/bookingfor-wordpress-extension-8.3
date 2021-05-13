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

$base_url = get_site_url();
$language = $GLOBALS['bfi_lang'];
if (COM_BOOKINGFORCONNECTOR_USE_OPENSTREETMAP) {
	wp_enqueue_script('bfileaflet');
	wp_enqueue_script('bfileafletcontrolcustom');
}

$isportal = COM_BOOKINGFORCONNECTOR_ISPORTAL;
$usessl = COM_BOOKINGFORCONNECTOR_USESSL;
$formRoute= "";
$formMethod = "POST";
$btnSearchclass=""; 
$currentCartsItems = BFCHelper::getSession('totalItems', 0, 'bfi-cart');
$AvailabilityTimePeriod = array();
$minuteStart = 0;
$minuteEnd = 24*60;
$timeLength = 0;

$fromSearch =  BFCHelper::getVar('fromsearch','0');
$makesearch =  BFCHelper::getVar('refreshcalc','0');
$newsearch = BFCHelper::getVar('newsearch', 0);

$listNameAnalytics =  BFCHelper::getVar('lna','0');
if(empty( $listNameAnalytics )){
	$listNameAnalytics = 0;
}

$currLlistNameAnalytics = BFCHelper::$listNameAnalytics[$listNameAnalytics];
$ProductAvailabilityType = 1;
$ProductGroupId = $merchant->DefaultProductGroupId;
$ProductGroupName = $merchant->Name;
$MapImageUrl = BFCHelper::getImageUrlResized('mapsell', $merchant->MapImageUrl); 
if(!empty($resourcegroupId)){
	$ProductGroupId = $resourcegroupId;
	$ProductGroupName = $resource->Name;
	$MapImageUrl = BFCHelper::getImageUrlResized('mapsell', $resource->MapImageUrl); 
}

$cartdetails_page = get_post( bfi_get_page_id( 'cartdetails' ) );
$url_cart_page = get_permalink( $cartdetails_page->ID );
$formOrderRouteBook = $url_cart_page;

$merchantdetails_page = get_post( bfi_get_page_id( 'merchantdetails' ) );
$url_merchant_page = get_permalink( $merchantdetails_page->ID );
$routeMerchant = $url_merchant_page . $merchant->MerchantId.'-'.BFI()->seoUrl($merchant->Name);
$routeInfoRequest = $routeMerchant . '/contact';

$accommodationdetails_page = get_post( bfi_get_page_id( 'accommodationdetails' ) );
$url_resource_page = get_permalink( $accommodationdetails_page->ID );
$uri = $url_resource_page;

$resourcegroupdetails_page = get_post( bfi_get_page_id( 'resourcegroupdetails' ) );
$url_resourcegroup_page = get_permalink( $resourcegroupdetails_page->ID );

$selBookingType=0;

$checkinId = uniqid('checkincalculator');
$checkoutId = uniqid('checkoutcalculator');

$checkoutspan = '+0 day';

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

$duration = $checkout->diff($checkin)->format('%a');

$paxages = BFCHelper::getStayParam('paxages');
$refreshSearch = (isset($refreshSearch)) ? $refreshSearch : BFCHelper::getVar('refreshsearch','');

//if(!empty(BFCHelper::getVar('refreshcalc',''))){
//	BFCHelper::setSearchParamsSession($pars);
//}
//if ($checkin == $checkout){
//    $checkout->modify($checkoutspan); 
//}
if ($checkout < $checkin){
	$checkout = clone $checkin;
    $checkout->modify($checkoutspan); 
}
$dateStringCheckin =  $checkin->format('d/m/Y');
$dateStringCheckout =  $checkout->format('d/m/Y');

if(!empty($resourceId)){
	$resourceName = BFCHelper::getLanguage($resource->Name, $language, null, array('ln2br'=>'ln2br', 'striptags'=>'striptags')); 
	$ProductAvailabilityType = $resource->AvailabilityType;
	$currUriresource  = $uri.$resource->ResourceId.'-'.BFI()->seoUrl($resourceName);
	if(!empty($resourcegroupId)){
		$currUriresource  = $uri.$resourcegroupId.'-'.BFI()->seoUrl($resourceName);
	}
	$formRoute = $currUriresource .'/?task=getGroupResources';
}else{
	$formRoute = $routeMerchant .'/?task=getGroupResources';
}

// recupero risultati
	$sessionkeysearch = 'search.params.mapsells';
	bfi_setSessionFromSubmittedData($sessionkeysearch);
	$searchmodel = new BookingForConnectorModelSearch;
			
	$pars = BFCHelper::getSearchParamsSession($sessionkeysearch);
	$pars['paxes'] = 0;  // resetto le persone
	BFCHelper::setSearchParamsSession($pars,$sessionkeysearch);

	$onlystay =  true;
	$currParam = $pars;
	if(isset($currParam) && isset($currParam['onlystay'])){
			$onlystay =  ($currParam['onlystay'] === 'false' || $currParam['onlystay'] === 0)? false: true;
	}

	$filterinsession = null;

	$listsId = array();
	$listResourceMaps = array();
	$allTagIds = array();
	$items =  array();
	$total = 0;
	$currSorting = "";
	$totalAvailable = 0;
	$paxages = array();
	$nrooms = 1;
	$searchterm = '';
	$availabilitytype = '0,2,3';
	$itemtypes = '5';
	// valori fissi pe evitare paginazione
	$pages =1;
	$page =1;
	$totPerson = 0;
	$pckpaxages = BFCHelper::getStayParam('pckpaxages');
	$minqt = isset($currParam['minqt']) ? $currParam['minqt'] : 1;
	$maxqt = isset($currParam['maxqt']) ? $currParam['maxqt'] : 1;
	$availabilitytype = isset($currParam['availabilitytype']) ? $currParam['availabilitytype'] : $availabilitytype ;
	$itemtypes = !empty($currParam['itemtypes']) ? $currParam['itemtypes'] : $itemtypes ;

	$groupResultType = 2; // raggruppamento per gruppi di risorse
	$listNameAnalytics = 2;
	$fromsearchparam = "/?fromsearch=1&lna=".$listNameAnalytics;
	$counter = 0;

	if(!empty($fromSearch) && !empty($makesearch)){

				$filterinsession = BFCHelper::getFilterSearchParamsSession($sessionkeysearch);
				$items = $searchmodel->getItems(false, false, 0,30, $sessionkeysearch,true);
				
				$items = is_array($items) ? $items : array();
						
				$total=$searchmodel->getTotal($sessionkeysearch);
				$totalAvailable=$searchmodel->getTotalAvailable($sessionkeysearch);
				$currSorting=$searchmodel->getOrdering() . "|" . $searchmodel->getDirection();

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
	var servicesAvailability =[];
</script>
<!-- form fields -->
<h4 class="bfi-titleform"><!-- <?php _e('Availability', 'bfi') ?> -->
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
$currCheckIn = $checkin; 
$currCheckOut = $checkout;
$blockmonths = '14';
$blockdays = '7';
$currModID = uniqid('bfisearch');

?>
<form action="<?php echo esc_url($formOrderRouteBook) ?>" class="frm-order" method="post"></form>
<form id="bfi-calculatorForm" action="<?php echo $formRoute?>" method="<?php echo $formMethod?>" class="bfi-calculatorForm bfi-form-mapsells bfi_resource-calculatorForm bfi_resource-calculatorTable bfi-dateform-container "
<?php if($ProductAvailabilityType == 2 && strpos($strCheckinTime,":")!== false){  ?>
	data-checkinTime="<?php echo $strCheckinTime ?>"
<?php } ?>
<?php if($ProductAvailabilityType == 2 && strpos($strCheckoutTime,":")!== false){  ?>
	data-checkoutTime="<?php echo $strCheckoutTime ?>"
<?php } ?>
	data-productavailabilitytype="<?php echo $ProductAvailabilityType ?>"
			data-blockdays="<?php echo $blockdays;?>"
			data-blockmonths="<?php echo $blockmonths;?>"
			data-currmodid="<?php echo $currModID;?>"
>
<?php if(!empty($resourceId)) { ?>
<!-- prevent open calendar before load dates -->
<span style="display:none;"><input autofocus type="text"/></span>
<?php } ?>
<?php 
$oneDays= (($checkin->format('d/m/Y')) ==$checkout->format('d/m/Y'));
?>

	<div class="bfi-row bfi_resource-calculatorForm-mandatory ">
				<?php _e('Select a period', 'bfi') ?>: <input type="radio" class="bfi-changedays" name="bfi-select-days" <?php echo ($oneDays) ?"checked":""; ?> value="1"/><?php echo _e('One day','bfi') ?> 
				<input type="radio" class="bfi-changedays" name="bfi-select-days" <?php echo ($oneDays) ?"":"checked"; ?> value="2"/><?php echo _e('More day','bfi') ?> 
			<div class="bfi-row">
				<div class="bfi-col-md-8">
					<div class="bfi-row ">
						<div class="bfi-col-md-6 bfi-col-xs-6 bfi-checkin-field-container" id="calcheckin">      

							<label><?php echo _e('from','bfi') ?></label>
							<div class="bfi-datepicker bfi-datepicker-icon">
								<i class="far fa-calendar-alt"></i><input name="checkin" type="text" value="<?php echo $checkin->format('d/m/Y'); ?>" id="<?php echo $checkinId; ?>" class="bfidate bfistart bfi-checkin-field" readonly="readonly" />
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
						<div class="bfi-col-md-6 bfi-col-xs-6 <?php echo ($ProductAvailabilityType == 3 )? "bfi-hide " : " "  ?> bfi-checkout-field-container" id="calcheckout" style="display:<?php echo ($oneDays) ?"none":""; ?>">
							<label><?php echo _e('to','bfi') ?></label>
							<div class="bfi-datepicker bfi-datepicker-icon">
								<i class="far fa-calendar-alt"></i><input type="text" name="checkout" value="<?php echo $checkout->format('d/m/Y'); ?>" id="<?php echo $checkoutId; ?>" class="bfidate bfiend bfi-checkout-field" readonly="readonly"/>
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
					<a  onclick="bfi_getSeats(this)" id="calculateButton" class="calculateButton3 bfi-btn bfi-alternative <?php echo $btnSearchclass ?>" style="margin-top: 20px;" ><?php echo _e('Check availability','bfi') ?> </a>
				</div>
			</div>
				<div class=" bfi-hidden-md bfi-hidden-lg bfi-margin-top10 bfi-margin-bottom10">
					<a onclick="bfi_getSeats(this)" class="calculateButton3 bfi-btn bfi-alternative <?php echo $btnSearchclass ?>" ><?php echo _e('Check availability','bfi') ?> </a>
				</div>

	</div>	<!-- END bfi_resource-calculatorForm-mandatory -->

				<input name="onlystay" type="hidden" value="1" />
				<input name="newsearch" type="hidden" value="1" />
				<input name="calculate" type="hidden" value="true" />
				<input name="refreshsearch" type="hidden" value="<?php echo $refreshSearch ?>" />
				<input name="refreshcalc" type="hidden" value="1" />
				<input name="fromsearch" type="hidden" value="1" />
				<input name="lna" type="hidden" value="<?php echo $listNameAnalytics ?>" />
				<input type="hidden" name="filter_order" value="" />
				<input type="hidden" name="filter_order_Dir" value="" />
				<input type="hidden" name="groupresulttype" value="<?php echo $groupResultType ?>" />
				<input type="hidden" name="availabilitytype" value="0,2,3" />
				<input type="hidden" name="itemtypes" value="<?php echo bfi_ItemType::Beach ?>" />
				<input type="hidden" name="getallresults" value="1" />
				<input type="hidden" name="resview" value="mapsells" />
				<input type="hidden" name="merchantId" value="<?php echo $merchant->MerchantId ?>" />

</form>	
	
	
	
<!-- risultati  -->	
<br /><br />
	<div id="bfimaptab">
<?php 
	if ($total > 0) {
	    
		foreach ($items as $currKey => $resource){

			$resourceName = BFCHelper::getLanguage($resource->Name, $language, null, array('ln2br'=>'ln2br', 'bbcode'=>'bbcode', 'striptags'=>'striptags')); 
			$merchantName = BFCHelper::getLanguage($resource->MerchantName, $language, null, array('ln2br'=>'ln2br', 'bbcode'=>'bbcode', 'striptags'=>'striptags')); 
			$resourceDataTypeTrack = "Resource";
			$resourceDataIdTrack = "";

			$showResourceMap = (!empty($resource->Lat) && !empty($resource->Lng)) && !empty(COM_BOOKINGFORCONNECTOR_ENABLEGOOGLEMAPSAPI);
			$val= new StdClass;
			if ($showResourceMap) {
				$val->Id = $resource->ItemId;
				$val->Lat = $resource->Lat;
				$val->Long = $resource->Lng;
		//		$listResourceMaps[] = $val;
			}
			$itemRoute = "";
			$routeMerchant = "";
			$imageUrl = COM_BOOKINGFORCONNECTOR_DEFAULTIMAGE;
			$currTypeAltDates = "resource";
			$tripadvisorId = null;
			$favItemType = 0;
			
			switch ($groupResultType) {
				case 0: //resources			
					if(!empty($resource->ImageUrl)){
						$imageUrl = BFCHelper::getImageUrlResized('resources',$resource->ImageUrl, 'medium');
					}
					break;
				case 1: //merchants
					if(!empty($resource->ImageUrl)){
						$imageUrl = BFCHelper::getImageUrlResized('merchant',$resource->ImageUrl, 'medium');
					}
					break;
				case 2: //grouped resources
					if(!empty($resource->ImageUrl)){
						$imageUrl = BFCHelper::getImageUrlResized('resourcegroup',$resource->ImageUrl, 'medium');
					}			
					break;
			}
			
			$startDate = new DateTimeZone('UTC');
			$endDate = new DateTimeZone('UTC');
			$setFavDate = 0;
			if($resource->Available && $resource->TotalPrice>0 && Count($resource->Results)>0) { //ok disp 
					foreach ($resource->Results as $keyRes=>$currResource) {
							$currCheckIn = DateTime::createFromFormat('Y-m-d\TH:i:s',$currResource->AvailabilityDate,new DateTimeZone('UTC'));
							$currCheckOut = DateTime::createFromFormat('Y-m-d\TH:i:s',$currResource->CheckOutDate,new DateTimeZone('UTC'));
							if ($currCheckIn >$startDate) {
								$startDate = $currCheckIn;
								$endDate = $currCheckOut;
								$setFavDate = 1;
							}
					}
			}
			
			$resource->checkmoreId = $resource->ItemId;
			switch ($resource->ItemType) {
				case 0: //resources			
					$itemRoute = $url_resource_page.$resource->ItemId.'-'.BFI()->seoUrl($resourceName).$fromsearchparam."&checkin=".$checkin->format('YmdHis')."&checkout=".$checkout->format('YmdHis');
					$routeMerchant = $url_merchant_page . $resource->MerchantId .'-'.BFI()->seoUrl($resource->MerchantName).$fromsearchparam;
					$currTypeAltDates = "resource";
					$favItemType = 1;
					$resourceDataIdTrack = $resource->ItemId;
					$resource->checkmoreId = $resource->ItemId;
					$favoriteModel = array(
						"ItemId"=>$resource->ItemId,
						"ItemName"=>BFCHelper::string_sanitize($resourceName),
						"ItemType"=>1,
						"ItemURL"=>$itemRoute,
						"StartDate"=> ($setFavDate==1) ?$startDate->format("YmdHis"):"",
						"EndDate"=> ($setFavDate==1) ?$endDate->format("YmdHis"):"",
						"WrapToContainer"=>1,
					);
					
					break;
				case 1: //merchants
					$itemRoute = $url_merchant_page . $resource->MerchantId .'-'.BFI()->seoUrl($resourceName).$fromsearchparam."&checkin=".$checkin->format('YmdHis')."&checkout=".$checkout->format('YmdHis');;
					$resource->checkmoreId = $resource->MerchantId;
					$resourceDataTypeTrack = "Merchant";
					$routeMerchant = $itemRoute;
					$currTypeAltDates = "merchant";
					$resourceDataIdTrack = $resource->MerchantId;
					$tripadvisorId = !empty($resource->tripAdvisorId)?$resource->tripAdvisorId:"";
					$favoriteModel = array(
						"ItemId"=>$resource->MerchantId,
						"ItemName"=>BFCHelper::string_sanitize($resourceName),
						"ItemType"=>0,
						"ItemURL"=>$itemRoute,
						"StartDate"=> ($setFavDate==1) ?$startDate->format("YmdHis"):"",
						"EndDate"=> ($setFavDate==1) ?$endDate->format("YmdHis"):"",
						"WrapToContainer"=>1,
					);
					break;
				case 2: //grouped resources
					$itemRoute = $url_resourcegroup_page.$resource->ItemId.'-'.BFI()->seoUrl($resourceName).$fromsearchparam."&checkin=".$checkin->format('YmdHis')."&checkout=".$checkout->format('YmdHis');;
					$resource->checkmoreId = $resource->ItemId;
					$routeMerchant = $url_merchant_page . $resource->MerchantId .'-'.BFI()->seoUrl($resource->MerchantName).$fromsearchparam;
					$resourceDataTypeTrack =  "Resource Group";
					$currTypeAltDates = "resourcegroup";
					$resourceDataIdTrack = $resource->ItemId;
					$favoriteModel = array(
						"ItemId"=>$resource->ItemId,
						"ItemName"=>BFCHelper::string_sanitize($resourceName),
						"ItemType"=>6,
						"ItemURL"=>$itemRoute,
						"StartDate"=> ($setFavDate==1) ?$startDate->format("YmdHis"):"",
						"EndDate"=> ($setFavDate==1) ?$endDate->format("YmdHis"):"",
						"WrapToContainer"=>1,
					);
					
					break;
			}
			
			// per tutti i link si rmanda sempre alla stessa pagina
			$routeMerchant = $itemRoute;

			$IsBookable = in_array(true, array_map(function ($t) { return $t->IsBookable; }, $resource->Results));
			$SimpleDiscountIds = implode(',',array_unique(array_map(function ($i) { 
					if (!empty($i->DiscountIds)) {
						return  implode(',',json_decode($i->DiscountIds));
					}		
				}, $resource->Results)));
			$SimpleDiscountNames = implode(' + ',array_unique(array_map(function ($i) { 
					if (!empty($i->DiscountNames)) {
						return  implode(' + ',json_decode($i->DiscountNames));
					}		
				}, $resource->Results)));

			$btnText = __('Choose place','bfi');
			$btnClass = "bfi-alternative";
			if ($IsBookable){
				$btnText = __('Book Now','bfi');
				$btnClass = "";
			}



			$resourceNameTrack =  BFCHelper::string_sanitize($resourceName);
			$merchantNameTrack =  BFCHelper::string_sanitize($merchantName);
			$merchantCategoryNameTrack =  BFCHelper::string_sanitize($resource->DefaultLangCategoryName);

			$totalInt = 0;


			?>
			<div class="bfi-col-sm-6 bfi-item bfi-list-group-item" data-href="<?php echo $itemRoute ?>">
				<div class="bfi-row bfi-sameheight" >
					<div class="bfi-col-sm-3 bfi-img-container">
						<a href="<?php echo $itemRoute ?>" style='background: url("<?php echo $imageUrl; ?>") center 25%;background-size: cover;'    class="eectrack" data-type="<?php echo $resourceDataTypeTrack ?>" data-id="<?php echo $resourceDataIdTrack?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>">
						<?php bfi_get_template("shared/favorite_icon.php",$favoriteModel); ?>
						<img src="<?php echo $imageUrl; ?>" class="bfi-img-responsive" />
						</a> 
					</div>
					<div class="bfi-col-sm-9 bfi-details-container">
						<!-- merchant details -->
						<div class="bfi-row" >
							<div class="bfi-col-sm-9">
								<div class="bfi-item-title">
									<a href="<?php echo $itemRoute ?>" onclick="event.stopPropagation();" id="nameAnchor<?php echo $resource->ItemId?>"  class="eectrack" data-type="<?php echo $resourceDataTypeTrack ?>" data-id="<?php echo $resourceDataIdTrack?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php echo  $resourceName ?></a> 
									<span class="bfi-item-rating">
		<?php 
						$ratingModel = array(
							"ratingModel"=>$resource
						);
						bfi_get_template("shared/stars_rating.php",$ratingModel);
		?>
									</span>
									<?php if((isset($resource->IsRecommendedResult) && $resource->IsRecommendedResult )) { ?><i class="fa fa-tags" aria-hidden="true" data-toggle="tooltip" title="<?php _e('Certainly it is our Preferred Merchant! They provide a great value and an excellent service.', 'bfi') ?>"></i>	<?php } ?>
									<?php if($isportal && $resource->ItemType != 1 && !empty($resource->MerchantId)) { ?>
										- <a href="<?php echo $routeMerchant?>" onclick="event.stopPropagation();" class="bfi-subitem-title eectrack"  data-type="Merchant" data-id="<?php echo $resource->MerchantId?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $merchantNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php echo $merchantName; ?></a>
									<?php } ?>
									
								</div>
								<div class="bfi-item-address">
									<?php if ($showResourceMap){?>
									<a href="javascript:void(0);" onclick="event.stopPropagation();bookingfor.bfiShowMarker(<?php echo $resource->ItemId?>)">
										<span class="bfi-comma" id="address<?php echo $resource->ItemId?>"></span>
										<?php 
										if ($currParam != null && !empty($currParam['points']) && $resource->DistanceFromPoint > 0) { 
											if (!empty($currParam['searchTermValue']) && strpos($currParam['searchTermValue'], "cityIds|") === 0) { ?>
											<span class="bfi-comma" id="distance<?php echo $resource->ItemId?>"><?php echo BFCHelper::formatDistanceUnits($resource->DistanceFromPoint )?> <?php echo _e('from center' , 'bfi'); ?></span>	
											<?php
											} else if (!empty($currParam['searchTermValue']) && strpos($currParam['searchTermValue'], "poiIds|") === 0) { ?>
											<span class="bfi-comma" id="distance<?php echo $resource->ItemId?>"><?php echo BFCHelper::formatDistanceUnits($resource->DistanceFromPoint )?> <?php echo _e('from point of interest' , 'bfi'); ?></span>	
											<?php 
											}
										}
										?>
										
										
										<?php if($resource->DistanceFromPoint > 0) { ?>
										<?php } ?>
										<span class="bfishowmap bfi-comma"><?php _e('Map view', 'bfi') ?></span> 
									</a>
									<?php if(isset($resource->CenterDistance)) { ?>
										<span class="bfi-item-address-dot-separator"></span><span class="bfi-centerdistance" id="addressdist<?php echo $resource->ItemId?>" style="display:none;" data-toggle="tooltip" title="<?php _e('This is the straight-line distance on the map. Actual travel distance may vary.', 'bfi') ?>"> <i class="fa fa-road" aria-hidden="true"></i> <?php echo BFCHelper::formatDistanceUnits($resource->CenterDistance)?> <?php _e('from centre', 'bfi') ?></span>
									<?php } ?>

									<div class="bfi-hide" id="markerInfo<?php echo $resource->ItemId?>">
										<div class="bfi-map-info-container">
											<div class="bfi-map-info-container-img">
												<a href="<?php echo $itemRoute ?>" onclick="event.stopPropagation();" style='height:100%;background: url("<?php echo $imageUrl; ?>") center 25%;background-size: cover;display: block;'  class="eectrack" data-type="<?php echo $resourceDataTypeTrack ?>" data-id="<?php echo $resourceDataIdTrack?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>">&nbsp;</a> 
											</div>
											<div class="bfi-map-info-container-content" >
													<div class="bfi-item-title">
														<a href="<?php echo $itemRoute ?>" onclick="event.stopPropagation();"  class="eectrack" data-type="<?php echo $resourceDataTypeTrack ?>" data-id="<?php echo $resourceDataIdTrack?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php echo  $resourceName ?></a> 
														<span class="bfi-item-rating">
		<?php 
						$ratingModel = array(
							"ratingModel"=>$resource
						);
						bfi_get_template("shared/stars_rating.php",$ratingModel);
		?>
														</span>
													</div>
													<span id="mapaddress<?php echo $resource->ItemId?>"></span>
													<div class="bfi-text-right"><a onclick="event.stopPropagation();" href="<?php echo $itemRoute ?>"  class="bfi-btn eectrack" data-type="<?php echo $resourceDataTypeTrack ?>" data-id="<?php echo $resourceDataIdTrack?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php _e('View', 'bfi') ?></a> </div>
											</div>
										</div>
									</div>

									<?php } ?>
								</div>
								<?php
								if (isset($resource->TagsIdList) && !empty($resource->TagsIdList)) {
									 $allTagIds = array_merge($allTagIds, explode(",", $resource->TagsIdList));
								}
								?>
								<div class="bfirestags" id="bfitags<?php echo $resource->ItemId; ?>" rel="<?php echo $resource->TagsIdList?>"></div>
							</div>
							<div class="bfi-col-sm-3 bfi-text-right">
								<?php if ($isportal && ($resource->RatingsContext ==1 || $resource->RatingsContext ==2 || $resource->RatingsContext ==3)){?>
										<div class="bfi-avg">
										<?php if ($resource->AVGCount>0){
											$totalInt = BFCHelper::convertTotal(number_format((float)$resource->AVG, 1, '.', ''));
											?>
					<div class="bfi-widget-reviews-avg-container">
											<a class="bfi-avg-value eectrack" onclick="event.stopPropagation();" href="<?php echo $itemRoute ?>"  data-type="<?php echo $resourceDataTypeTrack ?>" data-id="<?php echo $resource->MerchantId?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $merchantNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php echo $rating_text['merchants_reviews_text_value_'.$totalInt] ?></a>
											<a class="bfi-avg-count eectrack" onclick="event.stopPropagation();" href="<?php echo $itemRoute ?>"  data-type="<?php echo $resourceDataTypeTrack ?>" data-id="<?php echo $resourceDataIdTrack?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php echo sprintf(__('%s reviews', 'bfi') ,$resource->AVGCount) ?></a>
					</div>
					<div class="bfi-widget-reviews-avg-value"><?php echo  number_format((float)$resource->AVG, 1, '.', '') ?></div>
										<?php } 
										elseif(!empty($tripadvisorId)){ 
											echo BFCHelper::bfi_getWidget_tripadvisor($tripadvisorId,1);
										} ?>

										</div>
								<?php } ?>
							</div>
						</div>
						<div class="bfi-clearfix bfi-hr-separ"></div>
						<!-- end merchant details -->
		<?php if( Count($resource->Results)>0) { //ok disp ?>
		<?php 
				$listResources = array();
				$totalResources = 0;
				foreach ($resource->Results as $keyRes=>$singleResource) {
					$id = $singleResource->ResourceId;

					if (isset($listResources[$id])) {
						$listResources[$id][] = $singleResource;
					} else {
						$listResources[$id] = array($singleResource);
					}
					$totalResources++;
				}

				array_multisort(array_map('count', $listResources), SORT_DESC, $listResources);


			foreach ($listResources as $resourceId=>$singleResource) // foreach $listMerchantsCart
			{
				$currResource = $singleResource[0];
				$currName = BFCHelper::getLanguage($currResource->Name, $language, null, array('ln2br'=>'ln2br', 'striptags'=>'striptags')); 
				$searchparamsuffix = "";
				if ($currResource->Availability > 0 && $currResource->TotalPrice > 0) {
					$currCheckIn = DateTime::createFromFormat('Y-m-d\TH:i:s',$currResource->AvailabilityDate,new DateTimeZone('UTC'));
					$currCheckOut = DateTime::createFromFormat('Y-m-d\TH:i:s',$currResource->CheckOutDate,new DateTimeZone('UTC'));
					$searchparamsuffix = "&checkin=" . $currCheckIn->format("YmdHis") . "&checkout=" . $currCheckOut->format("YmdHis");
				}
				$resourceRoute = $url_resource_page . $currResource->ResourceId . '-' . BFI()->seoUrl($resourceName) . $fromsearchparam . $searchparamsuffix;
				$currresourceNameTrack =  BFCHelper::string_sanitize($currName);

				if ($totalResources > 1) {
					$resourceRoute = $itemRoute ;
				}

				// per tutti i link si rmanda sempre alla stessa pagina
				$resourceRoute = $itemRoute ;

		?>
						<div>
								<div class="bfi-result-singleitem bfi-row">
									<div class="bfi-col-sm-8">
									<a href="<?php echo $resourceRoute?>" onclick="event.stopPropagation();" class="bfi-subitem-title eectrack"  data-type="Resource" data-id="<?php echo $currResource->ResourceId?>" data-index="<?php echo $counter?>" data-itemname="<?php echo $currresourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><b><?php echo $currName; ?></b> <span class="bfi-rental-caption"><?php _e('or similar', 'bfi') ?></span></a> 
									<div class="bfi-result-singleitem-details">								
									</div>
									<?php if (!$currResource->IsCatalog && $onlystay ){ ?>

										<div class="bfi-availability">
										<?php 
										if ($currResource->Availability > 0 && $currResource->Availability < 2) { ?>
											<span class="bfi-availability-low"><?php echo sprintf(__('Only %d available' , 'bfi'),$currResource->Availability) ?></span>
										<?php
										}
		$policy = isset($currResource->RatePlan) && isset($currResource->RatePlan->Policy) ? $currResource->RatePlan->Policy : null;
										if (!empty($policy) && $policy->CanBeCanceledCurrentTime && ($policy->CancellationValue == "" || $policy->CancellationValue == "0" || $policy->CancellationValue == "0%")) { ?>
											<span class="bfi-freecancellation"><?php _e('FREE Cancellation', 'bfi') ?></span>
										<?php 
										}

										?>

										</div>

									<?php 
										
									} 
									?>
									</div>
									<div class="bfi-col-sm-4 bfi-text-right">
										
										<?php 
											
										if (!$currResource->IsCatalog && $onlystay ){ 
		if (!empty($currResource->AvailabilityDate)) {
		?><div class="bfi_totalfor">
		<span class="bfi-comma">
		<?php 

										$currCheckIn = DateTime::createFromFormat('Y-m-d\TH:i:s',$currResource->AvailabilityDate,new DateTimeZone('UTC'));
										$currCheckOut = DateTime::createFromFormat('Y-m-d\TH:i:s',$currResource->CheckOutDate,new DateTimeZone('UTC'));
										$currDiff = $currCheckOut->diff($currCheckIn);
										$days = $currDiff->d;
										$hours = $currDiff->h;
										$minutes = $currDiff->i;


//										if (empty($resource->FullPeriodPrice) || !$resource->FullPeriodPrice) {
//											//echo __("From", 'bfi');
//										}else{
//										}
											echo __("Price for", 'bfi'). ' ';
											//echo '<span class="bfi-comma">';
											//echo __('Total for', 'bfi');
											switch ($currResource->AvailabilityType) {
												case 0:
													//echo sprintf(__(' %d day/s' ,'bfi'),$currResource->Days);
													echo sprintf(_n(' %d day', ' %d days', $currResource->Days, 'bfi'), $currResource->Days);
													break;
												case 1:
													echo sprintf(__(' %d night/s' ,'bfi'),$currResource->Days);
													break;
												case 2:
													if($days >0){
														//echo sprintf(__(' %d day/s' ,'bfi'),$days);
														echo sprintf(_n(' %d day', ' %d days', $days, 'bfi'), $days);
													}
													if($hours >0){
														echo sprintf(__(' %d hour/s' ,'bfi'),$hours);
													}
													if($minutes >0){
														echo sprintf(__(' %d minute/s' ,'bfi'),$minutes);
													}
													break;
												case 3:
			//										echo __('Total for', 'bfi');
			//										if($hours >0){
			//											echo sprintf(__('%d hour/s' ,'bfi'),$hours);
			//										}
			//										if($minutes >0){
			//											echo sprintf(__('%d minute/s' ,'bfi'),$minutes);
			//										}
													break;
											}
										//echo '</span>';
										if (empty($resource->FullPeriodPrice) || !$resource->FullPeriodPrice) {
											echo ' ' . __("from", 'bfi');
										}else{
											//echo __("Price for", 'bfi');
										}
									?>
										</span>

		</div><?php
		}
											if ($currResource->RatePlan->TotalDiscounted< $currResource->RatePlan->TotalAmount){ ?>
											<span class="bfi-discounted-price bfi-discounted-price-total bfi_<?php echo $currencyclass ?> bfi-cursor" rel="<?php echo $SimpleDiscountIds ?>"><?php echo BFCHelper::priceFormat($currResource->RatePlan->TotalAmount,2, ',', '.')  ?><span class="bfi-no-line-through">&nbsp;<i class="fa fa-question-circle" aria-hidden="true"></i></span></span>
											<?php } ?>
											<span class="bfi-price bfi-price-total bfi_<?php echo $currencyclass ?> <?php echo ($currResource->RatePlan->TotalDiscounted< $currResource->RatePlan->TotalAmount)?"bfi-red":"" ?>"  ><?php echo BFCHelper::priceFormat($currResource->RatePlan->TotalDiscounted,2, ',', '.') ?></span>

											<?php 

											$currBookingTypeId = $currResource->RatePlan->MerchantBookingTypeId;
											$currMerchantBookingType = array_filter($currResource->RatePlan->MerchantBookingTypes, function($bt) use($currBookingTypeId) { return $bt->BookingTypeId == $currBookingTypeId; });
											if (count($currMerchantBookingType) > 0 && $currMerchantBookingType[0]->PayOnArrival && $currMerchantBookingType[0]->AcquireCreditCardData && !empty($currMerchantBookingType[0]->DepositRelativeValue)) { ?>
												<span class="bfi-noprepayment"><?php _e('Pay at the property – NO PREPAYMENT NEEDED', 'bfi') ?></span>
											<?php 
											}

											
										} ?>
									</div>
								</div>
						</div>
						<div class="bfi-clearfix"></div>
		<?php 
			}
		?>
						<div class="bfi-row">
							<div class="bfi-col-sm-9 ">
		<?php 
		if ($totalInt >9) {
		?>
								<div class="bfi_ratingbest">
									<i class="fa fa-smile-o" aria-hidden="true"></i> <?php _e('This structure has met or exceeded the expectations of more than 90% of guests', 'bfi') ?>
								</div>
		<?php } ?>

							</div>
							<div class="bfi-col-sm-3 bfi-text-right ">
									<?php 
									if ($resource->Price>0 && $totalResources == 1) {
		$val->Price = $resource->Price;
		$val->TotalPrice = $resource->TotalPrice;
		$val->Currencyclass = $currencyclass;

		if (!empty($currResource->AvailabilityDate)) {
		?><div class="bfi_totalfor">
		<span class="bfi-comma">
		<?php 

										$currCheckIn = DateTime::createFromFormat('Y-m-d\TH:i:s',$currResource->AvailabilityDate,new DateTimeZone('UTC'));
										$currCheckOut = DateTime::createFromFormat('Y-m-d\TH:i:s',$currResource->CheckOutDate,new DateTimeZone('UTC'));
										$currDiff = $currCheckOut->diff($currCheckIn);
										$days = $currDiff->d;
										$hours = $currDiff->h;
										$minutes = $currDiff->i;


//										if (empty($resource->FullPeriodPrice) || !$resource->FullPeriodPrice) {
//											//echo __("From", 'bfi');
//										}else{
//										}
											echo __("Price for", 'bfi');
											//echo '<span class="bfi-comma">';
											//echo __('Total for', 'bfi');
											switch ($currResource->AvailabilityType) {
												case 0:
													echo sprintf(__(' %d day/s' ,'bfi'),$currResource->Days);
													break;
												case 1:
													echo sprintf(__(' %d night/s' ,'bfi'),$currResource->Days);
													break;
												case 2:
													if($days >0){
														echo sprintf(__(' %d day/s' ,'bfi'),$days);
													}
													if($hours >0){
														echo sprintf(__(' %d hour/s' ,'bfi'),$hours);
													}
													if($minutes >0){
														echo sprintf(__(' %d minute/s' ,'bfi'),$minutes);
													}
													break;
												case 3:
			//										echo __('Total for', 'bfi');
			//										if($hours >0){
			//											echo sprintf(__('%d hour/s' ,'bfi'),$hours);
			//										}
			//										if($minutes >0){
			//											echo sprintf(__('%d minute/s' ,'bfi'),$minutes);
			//										}
													break;
											}
										//echo '</span>';
										if (empty($resource->FullPeriodPrice) || !$resource->FullPeriodPrice) {
											echo ' ' . __("From", 'bfi');
										}else{
											//echo __("Price for", 'bfi');
										}
									?>
										</span>

		</div><?php
		}
									?>
									<?php if ($resource->Price < $resource->TotalPrice){ ?>
									<span class="bfi-discounted-price bfi-discounted-price-total bfi_<?php echo $currencyclass ?> bfi-cursor" rel="<?php echo $SimpleDiscountIds ?>"><?php echo BFCHelper::priceFormat($resource->TotalPrice,2, ',', '.')  ?><span class="bfi-no-line-through">&nbsp;<i class="fa fa-question-circle" aria-hidden="true"></i></span></span>
									<?php } ?>
									<span class="bfi-price bfi-price-total bfi_<?php echo $currencyclass ?> <?php echo ($resource->Price < $resource->TotalPrice)?"bfi-red":"" ?>"  ><?php echo BFCHelper::priceFormat($resource->Price,2, ',', '.') ?></span>
									<?php } ?>
							</div>
						</div>
						<div class="bfi-clearfix"></div>
						<div class="bfi-row">
							<div class="bfi-col-sm-9">

							</div>
							<div class="bfi-col-sm-3 bfi-text-right">
							<?php 
							if ($totalResources>1) {
		//					    echo "Multi risorsa -> form<br />";

							?>
							<form method="post" action="<?php echo $itemRoute ?>"  >
								<input type="hidden" name="minqt" value="<?php echo $minqt ?>" />
								<input type="hidden" name="maxqt" value="<?php echo $maxqt ?>" />
								<input type="hidden" name="paxages" value="<?php echo $pckpaxages ?>" />
								<input type="hidden" name="itemTypeIds" value="<?php echo bfi_ItemType::Beach  ?>" />
								<input type="hidden" name="availabilityTypes" value="<?php echo $availabilitytype ?>" />
								<input type="hidden" name="resulttype" value="<?php echo $resource->ItemType ?>" />
								<input type="hidden" name="resultid" value="<?php echo ($resource->ItemType == 1 ? $resource->MerchantId : $resource->ItemId); ?>" />
								<input type="hidden" name="groupresulttype" value="<?php echo $groupResultType ?>" />
								<input type="hidden" name="bfipck" value="<?php echo bin2hex(gzdeflate(json_encode($resource,JSON_UNESCAPED_UNICODE),1)) ?>" />
								<button href="<?php echo $itemRoute ?>"  onclick="event.stopPropagation();" type="submit" class="bfi-btn bfi-btn eectrack <?php echo $btnClass ?>"  data-type="<?php echo $resourceDataTypeTrack ?>" data-id="<?php echo $resourceDataIdTrack?>" data-index="<?php echo $counter?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php echo $btnText ?></button>
							</form>
		<?php 
		} else {
		?>
								<a href="<?php echo $itemRoute ?>" onclick="event.stopPropagation();" class="bfi-btn eectrack <?php echo $btnClass ?>"  data-type="<?php echo $resourceDataTypeTrack ?>" data-id="<?php echo $resourceDataIdTrack?>" data-index="<?php echo $counter?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php echo $btnText ?></a>
		<?php 
							}
		?>

							</div>
						</div>
		<?php 
		   
		} else {  //ko disp:alternative
		?>
		<!-- merchant No resource  -->
						<?php
		//				$currStart = BFCHelper::getVar('limitstart','-1');
		//				if ($currKey==0 && $currStart =='0' ) {
						if((isset($resource->IsRecommendedResult) && $resource->IsRecommendedResult )) {				
		//				if(true) {				
						?>
							<div class="bfi-noavailability">
								<div class="bfi-alert bfi-alert-danger">
									<b><?php echo sprintf( __('Unfortunately we have no availability at this merchant for your dates: %s - %s', 'bfi') ,$checkinstr,$checkoutstr ) ?></b>
								</div>
							</div>
							<div class="bfi-check-more" data-type="<?php echo $currTypeAltDates ?>" data-id="<?php echo $resource->checkmoreId?>" >
								<?php _e('Limited availability, but may sell out:', 'bfi') ?>
								<div class="bfi-check-more-slider">
								</div>
							</div>
						<?php } else { ?>
							<div class="bfi-noavailability">
								<div class="bfi-alert bfi-alert-danger">
									<b><?php if(rand(0, 1)==0 && false) { // bloccato momentaneamente?>
										<?php _e('For a short time you missed it. Our last resource sold out a few days ago', 'bfi') ?>
									<?php }else{ ?>
										<?php _e("We're sorry! we do not have any availability.", 'bfi') ?>
									<?php } ?>
									</b>
								</div>
							</div>
						<?php } ?>
						<div class="bfi-clearfix"></div>
		<?php } ?>
					</div>
					<div class="bfi-discount-box" style="display:<?php echo ($resource->PercentVariation < 0)?"block":"none"; ?>;">
					<?php if(!empty($SimpleDiscountNames)) { ?>
						<span class="bfi-discount-names"><?php echo $SimpleDiscountNames ?></span>
					<?php } else { ?>
						<?php echo sprintf(__('Offer %d%%' , 'bfi'), number_format($resource->PercentVariation, 1)); ?>
					<?php } ?>
					</div>
				</div>
			</div>
		<?php 
			if ($showResourceMap) {
				$listResourceMaps[] = $val;
			}

			$listsId[]= $resource->ItemId;
			$counter++;
} // end foreach
	} // end if total >0

?>


	</div>
<!-- fine risultati -->
<script type="text/javascript">
<!--
	function bfi_getSeats(currObj){
		var currForm = jQuery(currObj).closest(".bfi-form-mapsells").first();
		var currCheckin = jQuery(currForm).find("input[name='checkin']").first();
		var currCheckout = jQuery(currForm).find("input[name='checkout']").first();
		var currselectdays = jQuery(currForm).find("input[name='bfi-select-days']:checked").first();
		if (currselectdays.val() == '1') {
			currCheckout.datepicker().datepicker('setDate', currCheckin.datepicker("getDate"));
		}
		var currDateFormat = "dd/mm/yy";
		currCheckin.datepicker( "option", "dateFormat",currDateFormat );
		currCheckout.datepicker( "option", "dateFormat", currDateFormat );
		var currquery = "checkIn=" + jQuery.datepicker.formatDate('yymmdd', currCheckin.datepicker("getDate")) + "000000&checkOut=" + jQuery.datepicker.formatDate('yymmdd', currCheckout.datepicker("getDate")) + "000000&language=" + bfi_variables.bfi_cultureCode + "&task=getGroupResources";
		
		var formUrl = '<?php echo $formRoute?>';
		var msgwait = '<i class="fa fa-spinner fa-spin fa-3x fa-fw margin-bottom"></i><span class="sr-only">Loading...</span>'
		formUrl += ((formUrl.indexOf('?') > -1)? "&" :"?") + 'format=calc&tmpl=component'

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
			} 
		}; 
		jQuery('#bfi-calculatorForm').ajaxSubmit(options);


	}

var listToCheck = "<?php echo implode(",", $listsId) ?>";
var listResourceMaps = <?php echo json_encode($listResourceMaps) ?>;
var imgPathMG = "<?php echo BFCHelper::getImageUrlResized('tag','[img]', 'tag24') ?>";
var imgPathMGError = "<?php echo BFCHelper::getImageUrl('tag','[img]', 'tag24') ?>";
var currGroupResultType = "<?php echo $groupResultType ?>";

var resGrp = [];
var loaded = false;
var loadedResGrp=false;
var tagids = "<?php echo implode(",", array_values(array_unique($allTagIds)))?>";

	 function getAjaxInformations() {
		 if (!loaded) {
             loaded = true;
             if (tagids!='')
             {
				 jQuery.getJSON(bookingfor.getActionUrl(null, null, "GetTagsByIds", "ids=" + tagids + "&viewContextType=8"), function (data) {
					 if (data != null) {
						 jQuery.each(data, function (key, val) {
							if (val.ImageUrl != null && val.ImageUrl != '') {
								var $imageurl = imgPathMG.replace("[img]", val.ImageUrl );
								var $imageurlError = imgPathMGError.replace("[img]", val.ImageUrl );
								resGrp[val.TagId] = '<img src="' + $imageurl + '" onerror="this.onerror=null;this.src=\'' + $imageurlError + '\';" alt="' + val.Name + '" data-toggle="tooltip" title="' + val.Name + '" />';
							} else if (val.IconSrc != null && val.IconSrc != '') {
								if (val.IconType != null && val.IconType != '')
								{
									var fontIcons = val.IconType .split(";");
									if (fontIcons[0] == 'fontawesome5')
									{
										resGrp[val.TagId] = '<i class="' + val.IconSrc + '" data-toggle="tooltip" title="' + val.Name + '"> </i> ';
									}
									if (fontIcons[0] == 'fontawesome4')
									{
										resGrp[val.TagId] = '<i class="fa ' + val.IconSrc + '" data-toggle="tooltip" title="' + val.Name + '"> </i> ';
									}

								}else{
									resGrp[val.TagId] = '<i class="fa ' + val.IconSrc + '" data-toggle="tooltip" title="' + val.Name + '"> </i> ';
								}
							} else {
								resGrp[val.TagId] = val.Name;
							}
						});
					 }
					 bfiUpdateInfoResGrp();
				 });
			 }
		 }
    }

function bfiUpdateInfoResGrp(){
	jQuery(".bfirestags").each(function(){
		var currList = jQuery(this).attr("rel");
		if (currList!= null && currList!= '')
		{
			var srvlist = currList.split(',');
			var srvArr = [];
			jQuery.each(srvlist, function(key, srvid) {
				if(typeof resGrp[srvid] !== 'undefined' ){
					srvArr.push(resGrp[srvid]);
				}
			});
			jQuery(this).html(srvArr.join(" "));
		}

	});
}

jQuery(document).ready(function() {
	getAjaxInformations();
	/*----load sticky for other result...----*/
	jQuery('#bfi-list').on("cssClassChanged",function() {
		bfiCheckOtherAvailabilityResize();
	});
	
//	bfiCheckOtherAvailability()		
	if (currGroupResultType==0)
	{
		bookingfor.bfiGetAllTags(function () {
			bookingfor.GetResourcesByIds(listToCheck);
		});
	} else if (currGroupResultType==1)
	{
		bookingfor.bfiGetAllTags(function () {
			bookingfor.GetMerchantsByIds(listToCheck);
		});
	} else if (currGroupResultType==2)
	{
		bookingfor.bfiGetAllTags(function () {
			bookingfor.getResourcegroupByIds(listToCheck);
		});
	}
	jQuery('.bfi-sort-item').click(function() {
		var rel = jQuery(this).attr('rel');
		var vals = rel.split("|"); 
		jQuery('#bookingforsearchFilterForm .filterOrder').val(vals[0]);
		jQuery('#bookingforsearchFilterForm .filterOrderDirection').val(vals[1]);

		if(jQuery('#searchformfilter').length){
			jQuery('#searchformfilter').submit();
		}else{
			jQuery('#bookingforsearchFilterForm').submit();
		}
	});

});


	function bfiCheckOtherAvailabilityResize() {
		jQuery(".bfi-check-more").each(function(){
			var currSlider = jQuery(this).find(".bfi-check-more-slider").first();
			if(currSlider.hasClass("slick-slider")){
				var currSliderWidth = jQuery(this).width()-80;
//				console.log(jQuery(this).width());
//				console.log(currSliderWidth);
				jQuery(currSlider).width(currSliderWidth);
				var ncolslick = Math.round(currSliderWidth/120);
				jQuery(currSlider).slick('slickSetOption', 'slidesToShow', ncolslick, true);
				jQuery(currSlider).slick('slickSetOption', 'slidesToScroll', ncolslick, true);
			}
		});	
	}



//-->
</script>


<!-- form fields end-->
<?php 

} // if isbot
?>
