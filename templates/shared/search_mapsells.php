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
wp_enqueue_script('jquerytemplate');
$base_url = get_site_url();
$language = $GLOBALS['bfi_lang'];

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
$dateStringCheckin =  $checkin->format('d/m/Y');
$dateStringCheckout =  $checkout->format('d/m/Y');
?>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.4.0/css/ol.css" type="text/css">
    <script src="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.4.0/build/ol.js"></script>
    <script src="https://unpkg.com/ol-layerswitcher@3.5.0"></script>
	<style>
	#bfFusionForm{
		display: none !important;
	}
	</style>
	<link rel="stylesheet" href="https://unpkg.com/ol-layerswitcher@3.5.0/src/ol-layerswitcher.css" />
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
					<a  onclick="bfi_getSeats(this)" id="calculateButton" class="calculateButton3 bfi-btn bfi-alternative <?php echo $btnSearchclass ?>" ><?php echo _e('Check availability','bfi') ?> </a>
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
</form>	
	<div id="bfimaptab">
		<div id="merchant_mapsells" style="width:100%;height: <?php echo (COM_BOOKINGFORCONNECTOR_ISMOBILE) ?"400px":"550px"; ?> "  data-lat="<?php echo $resourceLat?>"  data-lon="<?php echo $resourceLon?>" ></div>
		<div id="bfi-ol-popup" class="ol-popup">
		  <a href="#" id="bfi-ol-popup-closer" class="ol-popup-closer"></a>
		  <div id="bfi-ol-popup-content"></div>
		</div>
		<a name="bfiancorcart"></a>
		<div id="bfi-tmp-cart-container" class="bfi_tmp_cart bfi-result-list bfi-table-responsive " style="display:none;">
		<table class="bfi-table bfi-table-bordered bfi-table-resource-seats bfi-table-resources bfi-table-selectableprice bfi-table-selectableprice-container bfi-table-resources-sticked" style="margin-top: 20px;">
		<tr>
			<td class="bfi-nopad">
				<div id="bookingsListContainerdiv" class="">
					<table id="bookingsList" class="table table-condensed ">
						<thead>
							<tr>
								<th><?php _e('Information', 'bfi') ?></th>
								<th><div><!-- Per --></div></th>
								<th><div><?php _e('Price', 'bfi') ?></div></th>
								<th><div><?php _e('Extra', 'bfi') ?></div></th>
								<th><div></div></th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>
			</td>
			<td class="bfi-book-now-td">
				<div class="bfi-book-now" >
					<div class="bfi-resource-total"><span></span> <?php _e('selected items', 'bfi') ?></div>
					<div class="bfi-extras-total"><span></span> <?php _e('selected services', 'bfi') ?></div> 
					<div class="bfi-discounted-price bfi-discounted-price-total bfi_<?php echo $currencyclass ?>" style="display:none;"></div>
					<div class="bfi-price bfi-price-total bfi_<?php echo $currencyclass ?>" ></div>
					<div class="bfi-btn bfi-btn-book-now" onclick="bookingfor.BookNow(this);bfi_removeBookings();" style="display:none">
						<?php _e('Book Now', 'bfi') ?>
					</div>
					<div class="bfi-btn bfi-alternative bfi-request-now" onclick="bookingfor.BookNow(this);bfi_removeBookings();" style="display:none">
						<?php _e('Request Now', 'bfi') ?>
					</div>
				</div>
			</td>
		</tr>
</table>
		</div>
	</div>
<script id="bfi-cartTmpl" type="text/x-jquery-tmpl">
	<div class="bookingHeader" >
		<span class="text"><span class="itemcount">${count} <?php _e('resources', 'bfi') ?></span>
	</div>
	{{if count}}
	<div class="bookingsListContainerdiv">
	</div>
	{{/if}}
</script>
<script id="cartFooterTmpl" type="text/x-jquery-tmpl">
	{{if count}}
	<div class="bookingsListContainerdiv" style="display:none;"  class="bfi-table bfi-table-condensed ">
	<table style="width:100%;" cellspacing="3" cellpadding="2">
	<tr style="border-top:1px dotted #b0afac;border-bottom:1px dotted #b0afac;font-size:13px;">
		<td valign="middle" style="text-align:left;padding:7px;">diritti:</td>
		<td style="text-align:right;padding:5px;">&euro;${currencyFormat(totalFeeAmount)}</td>
	</tr>
	</table>
	</div>
	{{/if}}
	<div class="bookingFooter" style="display:none;">
		<div class="row">
			{{if count}}
			<div class="col-md-6">
			<span class="text"><?php _e('Total', 'bfi') ?>:
			<br /><span class="totalammount">&euro;${currencyFormat(totalAmount + totalFeeAmount)}</span></span>
			</div>
			<div class="col-md-6 text-right"><br />
			<span class="submitCheckout btn btn-warning" id="submitCheckout"> <?php _e('Send', 'bfi') ?> </span>
			</div>
			{{/if}}
		</div>
	</div>
</script>
<script id="bookingTitleTmpl" type="text/x-jquery-tmpl">
	<tr class="bookingTitle${$item.mode} bfi-table-responsive" id="data-id-${seat.Rateplan.ProductId}-${seat.Rateplan.RatePlan.RatePlanId}" >
		<td valign="top" style="vertical-align:top;">
			<strong>${seat.Sector}</strong><br/>
			<div class="bfi-resname "><nowrap>${seat.Name}, <?php _e('seat', 'bfi') ?> ${position.Name}</nowrap></div>
			<div>
				{{if seat.Rateplan.AvailabilityType==0 }}	
					<?php _e('from', 'bfi') ?> ${jQuery.datepicker.formatDate("D, dd M yy", dateFrom)} {{if (dateTo>dateFrom)}} <?php _e('to', 'bfi') ?> ${jQuery.datepicker.formatDate("D, dd M yy", dateTo)} {{/if}}  ( ${Math.round((dateTo-dateFrom)/(1000*60*60*24))+1} <?php _e('days', 'bfi') ?>)
				{{/if}}
				{{if seat.Rateplan.AvailabilityType==1 }}	
					<?php _e('from', 'bfi') ?> ${jQuery.datepicker.formatDate("D, dd M yy", dateFrom)} <?php _e('to', 'bfi') ?> ${jQuery.datepicker.formatDate("D, dd M yy", dateTo)} ( ${Math.round((dateTo-dateFrom)/(1000*60*60*24))} <?php _e('nights', 'bfi') ?>)
				{{/if}}
				{{if seat.Rateplan.AvailabilityType==2 }}	
					<?php _e('from', 'bfi') ?> ${jQuery.datepicker.formatDate("D, dd M yy", dateFrom)} <?php _e('to', 'bfi') ?> ${jQuery.datepicker.formatDate("D, dd M yy", dateTo)}  ( ${Math.round((dateTo-dateFrom)/(1000*60*60*24))} <?php _e('days', 'bfi') ?>)
				{{/if}}
		</div>
	{{if seat.DistanceFromOtherPositions>0}}
<div><i class="fas fa-arrows-alt-h"></i> Distanza ombrelloni ${seat.DistanceFromOtherPositions} m</div>
	{{/if}}
	{{if (position && position.Tags)}}
			{{each(i, tagid) position.Tags}}
				{{if (typeof bookingfor.tagLoaded[tagid] !== 'undefined') }}
					<span class="bfi-seat-tag-name">{{html bookingfor.tagLoaded[tagid]}} ${bookingfor.tagNameLoaded[tagid]} </span>
				{{/if}}
			{{/each}}
	{{/if}}
			<!-- RatePlan:<br />
			Name: ${seat.Rateplan.Name}<br />
			resourceItemId: ${seat.resourceItemId}<br />
			RatePlanId: ${seat.Rateplan.RatePlan.RatePlanId}<br />
			ResourceId: ${seat.Rateplan.ProductId}<br />
			MerchantId: ${seat.Rateplan.MerchantId}<br />
			RatePlanTypeId: ${seat.Rateplan.RatePlan.RatePlanTypeId}<br />
			Policy: ${seat.Rateplan.RatePlan.Policy}<br />
			PolicyId: ${seat.Rateplan.RatePlan.Policy.PolicyId}<br /> -->
			<form method="post" action="" class="bfi-groupdform">
				<input type="hidden" name="checkin" value="${bookingfor.convertDateToIta(dateFrom)}" />
				<input type="hidden" name="checkout" value="${bookingfor.convertDateToIta(dateTo)}" />
				<input name="checkAvailability" type="hidden" value="1" />
				<input name="checkStays" type="hidden" value="1" />
				<input type="hidden" name="adults" value="2" />
				<input type="hidden" name="children" value="0" />
				<input type="hidden" name="seniores" value="0" />
				<input type="hidden" name="childages1" value="0" />
				<input type="hidden" name="childages2" value="0" />
				<input type="hidden" name="childages3" value="0" />
				<input type="hidden" name="childages4" value="0" />
				<input type="hidden" name="childages5" value="0" />
				<input type="hidden" name="resourceItemId" value="${seat.resourceItemId}" />
				<input type="hidden" name="duration" value="${seat.duration}" />
				<input type="hidden" name="bookingType" value="${seat.Rateplan.RatePlan.MerchantBookingTypeId}" />
				<input type="hidden" name="ResourceGroupItemId" value="${seat.resourceItemId}" />
				<input type="hidden" name="ResourceGroupItemName" value="${position.Name}" />
				<input type="hidden" name="ResourceGroupItemXPos" value="${position.XPos}" />
				<input type="hidden" name="ResourceGroupItemYPos" value="${position.YPos}" />
				<input type="hidden" name="ResourceGroupItemRowName" value="${position.RowName}" />
				<input type="hidden" name="ResourceGroupItemColumnName" value="${position.ColumnName}" />
				<input type="hidden" name="ResourceGroupSectorId" value="${seat.SectorId}" />
				<input type="hidden" name="ResourceGroupItemTagsIdList" value="${seat.TagsIdList}" />
				<input type="hidden" name="ResourceGroupSectorName" value="${seat.Sector}" />
				<input type="hidden" name="ResourceGroupId" value="<?php echo $ProductGroupId ?>" />
				<input type="hidden" name="ResourceGroupName" value="<?php echo $ProductGroupName ?>" />
			</form>
			<div style="display:none;">			
			<select class="ddlrooms ddlrooms-${seat.Rateplan.ResourceId} ddlroomsrealav-${seat.Rateplan.ProductId} ddlrooms-indipendent" 
						id="ddlrooms-${seat.Rateplan.ResourceId}-${seat.Rateplan.RatePlan.RatePlanId}-${seat.Rateplan.ResourceId}" 
						<?php echo (COM_BOOKINGFORCONNECTOR_ISMOBILE) ?"disabled":""; ?>
						data-referenceid="${seat.Rateplan.RatePlan.RatePlanId}-${seat.resourceItemId}" 
						data-realavailproductid="${seat.Rateplan.ResourceId}" 
						data-resid="${seat.Rateplan.ProductId}" 
						data-mrcid="${seat.Rateplan.MerchantId}" 
						data-name="${seat.Rateplan.Name}"
						data-lna=""
						data-brand=""
						data-category=""
						data-sourceid="${seat.Rateplan.ResourceId}"
						data-ratePlanId="${seat.Rateplan.RatePlan.RatePlanId}"
						data-ratePlanTypeId="${seat.Rateplan.RatePlan.RatePlanTypeId}"
						data-ratePlanName="${seat.Rateplan.RatePlan.Name}"
						data-policyId="${seat.Rateplan.RatePlan.Policy.PolicyId}"
						data-policy='${JSON.stringify(seat.Rateplan.RatePlan.Policy)}'
						data-price="${bookingfor.priceFormat(seat.Rateplan.Price, 2, '.', '')}" 
						data-totalprice="${bookingfor.priceFormat(seat.Rateplan.TotalPrice, 2, '.', '')}" 
						data-baseprice="${seat.Rateplan.Price}" 
						data-basetotalprice="${seat.Rateplan.TotalPrice}"
						data-allvariations='${seat.Rateplan.RatePlan.AllVariationsString}'
						data-percentvariation="${seat.Rateplan.RatePlan.PercentVariation}"
						data-availability="${seat.Rateplan.RatePlan.Availability}" 
						data-availabilitytype="${seat.Rateplan.AvailabilityType}"
						data-isbookable="1" 
						data-checkin="${bookingfor.convertDateToIta(dateFrom)}" 
						data-checkout="${bookingfor.convertDateToIta(dateTo)}"
						data-checkin-ext="${bookingfor.convertDateToIta(dateFrom)}T00:00:00" 
						data-checkout-ext="${bookingfor.convertDateToIta(dateTo)}T00:00:00"
						data-includedmeals="${seat.Rateplan.RatePlan.IncludedMeals}" 
						data-touristtaxvalue="${seat.Rateplan.TouristTaxValue}" 
						data-vatvalue="${seat.Rateplan.VATValue}" 
						data-minpaxes="${seat.Rateplan.MinPaxes}" 
						data-maxpaxes="${seat.Rateplan.MaxPaxes}" 
						data-resetCart="0" 
						data-hidePeopleAge="{{if seat.Rateplan.HidePeopleAge}}1{{/if}}" 
						data-paxes="${seat.Rateplan.RatePlan.SuggestedStay.Paxes}" 
						data-computedpaxes="${seat.Rateplan.RatePlan.SuggestedStay.ComputedPaxes}" 
						data-bedconfig=""
						data-bedconfigindex=""
						data-timelength="${seat.Rateplan.TimeLength}"
						><option value=1 selected>1</option></select>
			</div>
		</td>
		<td valign="top" style="vertical-align:top;">
		</td>
		<td valign="top" style="vertical-align:top;">
			<span class="cartprice"> 
			<nowrap><span class="bfi-price bfi_${currentCurrency}"> ${bookingfor.priceFormat(amount, 2, ',', '.')}</span></nowrap></span>
		</td>
		<td valign="top" style="vertical-align:top;padding:0!important">{{if seat.Rateplan.RatePlan.CalculablePrices.length>0 }}
		<table class="bfi-table bfi-table-extra-select" style="width:100%;border:0">
			{{each seat.Rateplan.RatePlan.CalculablePrices}}
			<tr class="data-sel-id-${seat.Rateplan.ProductId} services-room-1-${seat.Rateplan.ProductId}-${seat.Rateplan.RatePlan.RatePlanId}-${seat.resourceItemId}">
				<td style="display:block"><div class="bfi-service-title">${Name}</div>
					<div class="bfi-period {{if FullPeriodPrice}}bfi-period-disabled"{{else}}bfi-cursor{{/if}} " id="bfi-period-${RelatedProductId}"
					data-resid="${RelatedProductId}"
					data-checkin="${bookingfor.convertDateToInt(dateFrom)}"
					data-checkout="${bookingfor.convertDateToInt(dateTo)}" 
					data-availabilitytype="${AvailabilityType}"
					></div>
				</td>
				<td style="display:block">
					<div class="bfi-totalextrasselect" style="{{if TotalAmount==0}}display:none;{{/if}}">
						<div align="center">
							${( $data.percentVariation = TotalAmount> 0 ? parseInt((((TotalDiscounted - TotalAmount) * 100) / TotalAmount),10) : 0),''}
							<div class="bfi-percent-discount" style="{{if percentVariation <= 0}}display:none;{{/if}}" rel="$SimpleDiscountIds" rel1="${RelatedProductId}">
								<span class="bfi-percent">${percentVariation}</span>% <i class="fa fa-question-circle bfi-cursor-helper" aria-hidden="true"></i>
							</div>
						</div>
						<div data-value="${TotalAmount}" class="bfi-discounted-price bfi_<?php echo $currencyclass ?>" style="display:{{if TotalDiscounted<=TotalAmount}}none{{/if}};">${bookingfor.priceFormat(TotalAmount, 2, ',', '.')}</div>
						<div data-value="${TotalDiscounted}" class="bfi-price {{if TotalAmount<TotalDiscounted}}bfi-red{{/if}} bfi_<?php echo $currencyclass ?>">${bookingfor.priceFormat(TotalDiscounted, 2, ',', '.')}</div>
					</div>
				</td>
				<td style="display:block">
					<div class="bfi-input-group bfi-mobile-input-group ">
						<span class="bfi-input-group-btn">
							<button type="button" class="btn bfi-btn btn-number" data-type="minus" data-field="ddlrooms-@currRateplan.ResourceId-@currRateplan.RatePlan.RatePlanId-@resRef">
								<i class="fa fa-minus" aria-hidden="true"></i>
							</button>
						</span>

					<select name="ddlextras" class="ddlrooms ddlrooms-${RelatedProductId} ddlroomsrealav-${RelatedProductId} ddlextras inputmini" style="height:33px" onchange="bfi_quoteCalculatorServiceChanged(this)" 
					data-name = "${Name}"
					data-resid = "${RelatedProductId}"
					data-priceid = "${PriceId}"
					data-bindingproductid = "${seat.Rateplan.ProductId}"
						data-availabilityTypeRes = "${AvailabilityType}"
						data-rateplanid = "${seat.Rateplan.RatePlan.RatePlanId}"
						data-availabilityType = "${AvailabilityType}"
						data-baseprice="${TotalDiscounted}" 
						data-basetotalprice="${TotalAmount}"
						data-price="${bookingfor.priceFormat(TotalDiscounted, 2, ',', '.')}" 
						data-totalprice="${bookingfor.priceFormat(TotalAmount, 2, ',', '.')}" 
						data-paxes="${seat.Rateplan.RatePlan.SuggestedStay.Paxes}" 
						data-computedpaxes="${seat.Rateplan.RatePlan.SuggestedStay.ComputedPaxes}" 
						data-ratePlanName="${seat.Rateplan.RatePlan.Name}"

					>
					{{each(i) Selectable}}
						<option value="${i}">${i}</option>
					{{/each}}</select>
						<span class="bfi-input-group-btn">
							<button type="button" class="btn bfi-btn btn-number" data-type="plus" data-field="ddlrooms-@currRateplan.ResourceId-@currRateplan.RatePlan.RatePlanId-@resRef">
								<i class="fa fa-plus" aria-hidden="true"></i>
							</button>
						</span>
					</div>

				</td>
			</tr>
	{{/each}}
	</table>			
{{/if}}
<!-- <textarea >${JSON.stringify(seat.Rateplan)}</textarea>	
 -->	</td>
		<td valign="top" style="vertical-align:top;">
			<i class="fas fa-times bfi-remove-seat" id="bfitoremove${Id}"></i>
		</td>
	</tr>
</script>
<script id="bookingEditTmpl" type="text/x-jquery-tmpl">
	{{tmpl($data, {mode: "Edit"}) "#bookingTitleTmpl"}}
</script>
<script type="text/javascript">
<!--
var bfi_currMerchantId = <?php echo $merchant->MerchantId ?>;
var configurationObj = JSON.parse('<?php echo $mapConfiguration ?>');
var pricesExtraIncluded=[];

var bookingTmplItems = {}, selectedBooking, cartTmplItem, cartTmplFooter, summaryTmplItem;
var bfi_seatCart = { bookings: {}, dateFrom: "", dateTo: "", count: 0, sortBy: 0, totalFeeAmount: 0, totalAmount: 0, totalDiscounted: 0};
	bfi_seatCart.bookingsArray = function () {
		var arr = [];
		for (prop in bfi_seatCart.bookings) {
			arr.push(bfi_seatCart.bookings[prop]);
		}
		return arr;
	}
jQuery(document).ready(function () {
//	(function ($) {
//		$.extend(jQuery.tmpl.tag, {
//			"for": {
//				_default: {$2: "var i=1;i<=1;i++"},
//				open: 'for ($2){',
//				close: '};'
//			}
//		});
//	})(jQuery);
	jQuery(".bfi-changedays").change(function() {
	var currForm = jQuery(this).closest("form");
	var currCheckin = jQuery(currForm).find("input[name='checkin']").first();
	var currCheckout = jQuery(currForm).find("input[name='checkout']").first();

		if (this.value == '1') {
//			var newDate = bookingfor.dateAdd(currCheckin.datepicker("getDate"), "day", 1);
//			currCheckout.datepicker().datepicker('setDate', newDate);
			currCheckout.datepicker().datepicker('setDate', currCheckin.datepicker("getDate"));
			jQuery(currForm).find(".bfi-checkout-field-container").hide();
		}
		else if (this.value == '2') {
			jQuery(currForm).find(".bfi-checkout-field-container").show();
		}
	});
	if (configurationObj.Type == 0) {
		bookingfor.loadMapSells("merchant_mapsells",configurationObj,'<?php echo $MapImageUrl?>',"bfi-ol-popup");
	} else {
				var tmpImg = new Image();
				tmpImg.src='<?php echo $MapImageUrl?>'; //or  document.images[i].src;
				jQuery(tmpImg).one('load',function(){
					bookingfor.loadMapSells("merchant_mapsells",configurationObj,'<?php echo $MapImageUrl?>',"bfi-ol-popup",tmpImg);
				});
	} 
	
	jQuery("#bfi-cartTmpl").tmpl(bfi_seatCart).prependTo("#bfi-tmp-cart-container", bfi_seatCart);
	jQuery("#cartFooterTmpl").tmpl(bfi_seatCart).appendTo("#bfi-tmp-cart-container", bfi_seatCart);
	cartTmplItem = jQuery(".bookingHeader ").tmplItem();
	cartTmplFooter = jQuery(".bookingFooter").tmplItem();

	jQuery(document).on('click tap', '.bfi_select_place', function (e) {
		bookingfor.addTotempCart(this);
	});
	jQuery(document).on('click tap', '.bfi_remove_select_place', function (e) {
		var tmpForm = jQuery(this).closest("form").first();
		var tmpSeatSelected = tmpForm.find("input[name='bfi_seatSelected']:checked").first();
		var tmpSelected = tmpSeatSelected.attr("dataseatid");
		bookingfor.popupCloser.click();
		jQuery("#bfitoremove" + tmpSelected).click();
//		removeBooking();
	});
	
	bookingfor.addTotempCart = function (obj) {
		var tmpForm = jQuery(obj).closest("form").first();
		var tmpSeatSelected = tmpForm.find("input[name='bfi_seatSelected']:checked").first();
		var tmpSelected = tmpSeatSelected.attr("dataseatid");
		var tmpSelectedName = tmpSeatSelected.attr("dataName");
		var tmpSelectedPrice = parseFloat(tmpSeatSelected.attr("dataPrice"));
		var booking = bfi_seatCart.bookings[tmpSelected];
		var currSeat = bookingfor.seatVector.getSource().getFeatureById(tmpSelected);
		bookingfor.popupCloser.click();
		var currentPos = jQuery.grep(configurationObj.ResourceSetPositions, function (pos) {
			return pos.Id == tmpSelected;
		})[0];
		currSeat.Name=  tmpSelectedName;
		bookingfor.seatVector.getSource().removeFeature(currSeat);
		if ( booking ) {
//			currSeat.Selected= false;
//			currSeat.setStyle([bookingfor.shadowStyle,bookingfor.seatStyleOpen]);
//			delete bfi_seatCart.bookings[tmpSelected];
//			bfi_seatCart.count--;
		} else {
			bfi_seatCart.count++;
			bfi_totalRooms = bfi_seatCart.count;
			cartTmplItem.update();
			currSeat.Selected= true;
			currSeat.PriceSelected = tmpSeatSelected.val();
			jQuery.each(currSeat.PriceResult, function (i, price) {
				 if (currSeat.PriceSelected==(price.ProductId + '_' + currentPos.Id))
				 {
					currSeat.Rateplan= price;
				 }
			 });

			//booking = currSeat;
			var duration = (currSeat.to-currSeat.from ) / 86400000;
			if (currSeat.Rateplan.AvailabilityType == 0)
			{
				duration +=1;
			}
			currSeat.duration = duration;
			booking = { Id:tmpSelected , seat: currSeat, position: currentPos, dateFrom: currSeat.from , dateTo: currSeat.to, quantity: 1,currentCurrency:bfi_variables.currentCurrency , feeAmount: parseFloat(tmpSelectedPrice), amount: parseFloat(tmpSelectedPrice) };
			bfi_seatCart.dateFrom = jQuery.datepicker.formatDate('yymmdd', currSeat.from);
			bfi_seatCart.dateTo = jQuery.datepicker.formatDate('yymmdd', currSeat.to);
			bfi_seatCart.totalAmount += tmpSelectedPrice;
			bfi_seatCart.totalDiscounted += tmpSelectedPrice;
//			cart.totalFeeAmount += booking.feeAmount;
//			cartTmplFooter.update();
			// change icon with selected
//			bfi_seatCart.bookings[tmpSelected] = booking;
//			bfi_seatCart.bookings[tmpSelected] = currSeat;
			cartTmplItem.update();
//			currSeat.setStyle([bookingfor.shadowStyle,bookingfor.seatStyleSelected]);
			currSeat.setStyle(function(feature,resolution) {
				bookingfor.TextStyle.setText(bookingfor.getFeatureText(feature,resolution));
				bookingfor.seatStyleSelected.setText(bookingfor.TextStyle);
				bookingfor.seatStyleSelected.getImage().setRadius(bookingfor.getFeatureRadius(feature,resolution));
				var styles = [bookingfor.shadowStyle,bookingfor.seatStyles[currentPos.IconType],bookingfor.seatStyleSelected]
				return styles;
			});

			// aggiornamento laterale
			bfi_totalQuote = bfi_seatCart.totalAmount;
			bfi_totalQuoteDiscount = bfi_seatCart.totalDiscounted;
			jQuery(".bfi-price-total").html(bookingfor.number_format(bfi_seatCart.totalAmount, 2, ',', '.'));
			jQuery(".bfi-resource-total span").html(bfi_seatCart.count);
			bfi_updateQuoteService();
		}
			bookingfor.seatVector.getSource().addFeature(currSeat);
//			jQuery(".bfi_tmp_cart").append( '<p>'+tmpSelectedName+ ' (' + currentPos.Name+ ') <span class="bfi_' + bfi_variables.currentCurrency + '">'+bookingfor.priceFormat(tmpSelectedPrice, 2, ',', '.')+'</span> <a href="#" id="bfi-remove" class="">x</a></p>' );
//		alert(tmpSelected);
		selectBooking( booking );
		if (bfi_seatCart.count>0)
		{
			jQuery("#bfi-tmp-cart-container").show();
			jQuery("#bfi-tmp-cart-container").find(".bfi-book-now").show();
			jQuery([document.documentElement, document.body]).animate({
				scrollTop: (jQuery(".bookingHeader").first().offset().top - 140)
			}, 1000);
		}else{
			jQuery("#bfi-tmp-cart-container").hide();
			jQuery("#bfi-tmp-cart-container").find(".bfi-book-now").hide();
		}
			bfi_UpdateQuote(jQuery(".bfi-table-resource-seats").last());
		bfi_updateQuoteService();
	};

	function selectBooking( booking ) {
		if ( selectedBooking ) {
			if ( selectedBooking === booking ) {
				updateBooking( bookingTmplItems[selectedBooking.Id]);
				return;
			}
			// Collapse previously selected booking, and switch to non-edit view
			var oldSelected = selectedBooking;
//			jQuery( "div", bookingTmplItems[oldSelected.Id].nodes ).animate( { height: 0 }, 500, function() {
				switchView( oldSelected );
//			});
		}
		selectedBooking = booking;
		if ( !booking ) {
			return;
		}
		if (bfi_seatCart.bookings[booking.Id]) {
			switchView( booking, true );
		} else {
			bfi_seatCart.bookings[booking.Id] = booking;

			var bookingNode = jQuery( "#bookingEditTmpl" )

				// Render the booking for the chosen movie using the bookingEditTemplate
				.tmpl( booking, { animate: true } )

				// Append the rendered booking to the bookings list
				.appendTo( "#bookingsList" )
				//.prependTo( "#bookingFooter" )
				// Get the 2nd <tr> of the appended booking
				.last()[0];

			////////////////////////////////////////////////////////////////////////////////////////////////////////////summaryTmplItem.update();
			// Get the template item for the 2nd <tr>, which is the template item for the "bookingEditTmpl" template
			var newItem = jQuery.tmplItem( bookingNode );
			bookingTmplItems[booking.Id] = newItem;

			// Attach handlers etc. on the rendered template.
			bookingEditRendered( newItem );
		}
	}

	function bookingEditRendered( item ) {
		var data = item.data, nodes = item.nodes;

		jQuery( nodes[0] ).click( function() {
			selectBooking();
		});

		jQuery( ".bfi-remove-seat", nodes ).click( removeBooking );
//		if ( item.animate ) {
//			jQuery( "div", nodes ).css( "height", 0 ).animate( { height: 116 }, 500 );
//		}
	}

	function bookingRendered( item ) {
		jQuery( item.nodes ).click( function() {
			selectBooking( item.data );
		});
		jQuery( ".bfi-remove-seat", item.nodes ).click( removeBooking );
	}

	function switchView( booking, edit ) {
		if ( !booking ) {
			return;
		}
		var item = bookingTmplItems[booking.Id],
			tmpl = jQuery( edit ? "#bookingEditTmpl" : "#bookingTitleTmpl" ).template();
		if ( item.tmpl !== tmpl) {
			item.tmpl = tmpl;
			item.update();
			(edit ? bookingEditRendered : bookingRendered)( item );
		}
	}

	function updateBooking( item ) {
		item.animate = false;
		item.update();
		(item.data === selectedBooking ? bookingEditRendered : bookingRendered)( item );
		item.animate = true;
	}

	function removeBooking() {
		var booking = jQuery.tmplItem(this).data;
		if ( booking === selectedBooking ) {
			selectedBooking = null;
		}
		bfi_seatCart.totalAmount -= booking.amount;
		bfi_seatCart.totalDiscounted -= booking.amount;
//		bfi_seatCart.totalFeeAmount -= booking.feeAmount;
		//bfi_seatCart.bookings.splice(booking.Id, 1);
		delete bfi_seatCart.bookings[booking.Id];
		bfi_seatCart.count--;
		cartTmplItem.update();
		cartTmplFooter.update();
		jQuery( bookingTmplItems[booking.Id].nodes ).remove();
		///////////////////////////////////////////////////////////////////summaryTmplItem.update();

		// change icon with selected
		////////////////////////////////////////booking.umbrella.marker.setIcon(iUmbrella);
		var currSeat = bookingfor.seatVector.getSource().getFeatureById(booking.Id);
		bookingfor.seatVector.getSource().removeFeature(currSeat);
		currSeat.Selected= false;
		currSeat.setStyle([bookingfor.shadowStyle,bookingfor.seatStyles[booking.position.IconType],bookingfor.seatStyleOpen]);
		bookingfor.seatVector.getSource().addFeature(currSeat);

		delete bookingTmplItems[booking.Id];

		// aggiornamento laterale
		bfi_totalQuote = bfi_seatCart.totalAmount;
		bfi_totalQuoteDiscount = bfi_seatCart.totalDiscounted;
		jQuery(".bfi-price-total").html(bookingfor.number_format(bfi_seatCart.totalAmount, 2, ',', '.'));
		
		if (bfi_seatCart.count>0)
		{
			jQuery("#bfi-tmp-cart-container").show();
		}else{
			jQuery("#bfi-tmp-cart-container").hide();
		}
		bfi_UpdateQuote(jQuery(".bfi-table-resource-seats").last());
		bfi_updateQuoteService();
		
		return false;
	}
	var loadResultMaps = setInterval(function() {
	   if (bookingfor.loadedMapSells ) {
		 jQuery(".calculateButton3").first().click();
		  clearInterval(loadResultMaps);
	   }
	}, 100); // check every 100ms
});
//	jQuery(".calculateButton3").first().click();
	function bfi_removeBookings() {
		for ( var item in bookingTmplItems ) {
			jQuery(bookingTmplItems[item].nodes).remove();

			// change icon with selected
			/////////////////////////////bookingTmplItems[item].data.umbrella.marker.setIcon(iUmbrella);

			delete bookingTmplItems[item];
		}
		bookingTmplItems = {};
		bfi_seatCart.count = 0;
		bfi_seatCart.totalAmount = 0;
		bfi_seatCart.TotalDiscounted = 0;
		bfi_seatCart.totalFeeAmount = 0;
		bfi_seatCart.bookings = {};
		selectedBooking = null;
		cartTmplItem.update();
		cartTmplFooter.update();
		// aggiornamento laterale
		bfi_totalQuote = bfi_seatCart.totalAmount;
		bfi_totalQuoteDiscount = bfi_seatCart.totalDiscounted;
		jQuery(".bfi-price-total").html(bookingfor.number_format(bfi_seatCart.totalAmount, 2, ',', '.'));
		
		if (bfi_seatCart.count>0)
		{
			jQuery("#bfi-tmp-cart-container").show();
		}else{
			jQuery("#bfi-tmp-cart-container").hide();
		}
		bfi_UpdateQuote(jQuery(".bfi-table-resource-seats").last());
		bfi_updateQuoteService();
		
		return false;
	}	
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
		var currquery = "productGroupId=" + <?php echo $ProductGroupId ?> + "&checkIn=" + jQuery.datepicker.formatDate('yymmdd', currCheckin.datepicker("getDate")) + "000000&checkOut=" + jQuery.datepicker.formatDate('yymmdd', currCheckout.datepicker("getDate")) + "000000&language=" + bfi_variables.bfi_cultureCode + "&task=GetMapAvailabilitiesByProductGroupId";
		if (bookingfor.seatVector)
		{
			bookingfor.currentMapSells.removeLayer(bookingfor.seatVector);
		}
		bookingfor.waitSimpleWhiteBlock(jQuery("#" + bookingfor.currentMapSells.getTarget()));

		jQuery.post(bfi_variables.bfi_urlCheck, currquery, function (data) {
			if (data != null) {

			// posti spiaggia
			var seats = [];
            if (data.MapPositions && data.MapPositions.length > 0) {
				var currPriceResults=[];
				if (data.PriceResults && data.PriceResults.length > 0 )
				{
					currPriceResults = data.PriceResults;
				}
                jQuery.each(currPriceResults, function (i, currPrice) {
					pricesExtraIncluded[currPrice.RatePlan.RatePlanId+"-"+currPrice.ProductId] =  '' ;
				});
				// recupero tag
				if(data.Tags && data.Tags.length !=null){
					jQuery.each(data.Tags || [], function(key, val) {
						if (typeof bookingfor.tagLoaded[val.TagId] == 'undefined') {
							if (val.ImageUrl!= null && val.ImageUrl!= '') {
								var $imageurl = imgPathMG.replace("[img]", val.ImageUrl );		
								var $imageurlError = imgPathMGError.replace("[img]", val.ImageUrl );		
								/*--------getName----*/
								var $name = val.Name;
								/*--------getName----*/
								bookingfor.tagLoaded[val.TagId] = '<img src="' + $imageurl + '" onerror="this.onerror=null;this.src=\'' + $imageurlError + '\';" alt="' + $name + '" data-toggle="tooltip" title="' + $name + '" />';
							} else  if (val.IconSrc != null && val.IconSrc != '') {
								if (val.IconType != null && val.IconType != '')
								{
									var fontIcons = val.IconType .split(";");
									if (fontIcons[0] == 'fontawesome5')
									{
										bookingfor.tagLoaded[val.TagId] = '<i class="' + val.IconSrc + '" data-toggle="tooltip" title="' + val.Name + '"> </i> ';
									}
									if (fontIcons[0] == 'fontawesome4')
									{
										bookingfor.tagLoaded[val.TagId] = '<i class="fa ' + val.IconSrc + '" data-toggle="tooltip" title="' + val.Name + '"> </i> ';
									}

								}else{
									bookingfor.tagLoaded[val.TagId] = '<i class="fa ' + val.IconSrc + '" data-toggle="tooltip" title="' + val.Name + '"> </i> ';
								}
							} else {
								bookingfor.tagLoaded[val.TagId] = val.Name;
							}
							bookingfor.tagNameLoaded[val.TagId] = val.Name; //solo il nome!
						}
					});	
				}
                jQuery.each(data.MapPositions, function (i, pnt) {
                    var marker = new ol.Feature({
                        geometry: new ol.geom.Point(configurationObj.Type == 0 ? ol.proj.fromLonLat([pnt.YPos, pnt.XPos]) : [pnt.XPos, pnt.YPos]),
                    });

					var currentPos = jQuery.grep(configurationObj.ResourceSetPositions, function (pos) {
						return pos.Id == pnt.RefId;
					})[0];
					if (currentPos)
					{
//					currentPos.IconType = Math.floor(Math.random() * 9);	// tipo di ombrellone
						currentPos.IconType = 0;
						if (pnt.IconType !== 'undefined')
						{
					currentPos.IconType = pnt.IconType ;
						}


                    var currentSector = jQuery.grep(configurationObj.Sectors, function (currSector) {
						return currSector.Id = pnt.SectorId;
					})[0];
                    marker.setId(pnt.RefId);
                    marker.Sector = currentSector?currentSector.Name:'';
                    marker.SectorId = currentSector?currentSector.Id:'';
					marker.Available = pnt.Available;
					marker.Selected= false;
					marker.from= currCheckin.datepicker("getDate");
					marker.to= currCheckout.datepicker("getDate");
					marker.labelText= currentPos.Name;
					marker.DistanceFromOtherPositions= parseFloat(currentPos.DistanceFromOtherPositions||0);

					marker.resourceItemId = pnt.ProductId; // id del posto
					//tag
					marker.Tags=[];
					if (pnt.TagsIdList != null && pnt.TagsIdList != '') {
						marker.TagsIdList = pnt.TagsIdList 
						var tagIdlist = pnt.TagsIdList.split(',');
						jQuery.each(tagIdlist, function (key, tagId) {
							if (typeof bookingfor.tagLoaded[tagId] !== 'undefined') {
								marker.Tags.push(bookingfor.tagLoaded[tagId]);
							}
						});
					}


					if (pnt.Available)
					{
//                        var currentPriceResult = jQuery.grep(currPriceResults, function (currPrice) {
//                            return !jQuery.inArray( currPrice.UniqueId,pnt.PriceResults);
//                        });
                        var currentPriceResult = jQuery.map(currPriceResults, function(currPrice){
						  return jQuery.inArray(currPrice.UniqueId, pnt.PriceResults) < 0 ? null : currPrice;
						});
						if (currentPriceResult && currentPriceResult.length > 0 )
						{
//							console.log(pnt.PriceResults);
//							console.log(currentPriceResult);
//							console.log('-------------------------');
							marker.PriceResult = currentPriceResult;
							marker.PriceSelected = currentPriceResult[0].ProductId + '_' + pnt.RefId;
						}
						marker.setStyle(function(feature,resolution) {
							bookingfor.TextStyle.setText(bookingfor.getFeatureText(feature,resolution));
							bookingfor.seatStyleOpen.setText(bookingfor.TextStyle);
							bookingfor.seatStyleOpen.getImage().setRadius(bookingfor.getFeatureRadius(feature,resolution));
							if (bookingfor.seatStyles[currentPos.IconType].getImage())
							{
								bookingfor.seatStyles[currentPos.IconType].getImage().setScale(bookingfor.getFeatureZoom(feature,resolution));
							}
							var styles = [bookingfor.shadowStyle,bookingfor.seatStyles[currentPos.IconType],bookingfor.seatStyleOpen]
							return styles;
						});
//						marker.setStyle([bookingfor.shadowStyle,bookingfor.seatStyleOpen,styleLabel]);

					}else{
						marker.setStyle(function(feature,resolution) {
							bookingfor.TextStyle.setText(bookingfor.getFeatureText(feature,resolution));
							bookingfor.seatStyle.setText(bookingfor.TextStyle);
							bookingfor.seatStyle.getImage().setRadius(bookingfor.getFeatureRadius(feature,resolution));
							var styles = [bookingfor.shadowStyle,bookingfor.seatStyles[currentPos.IconType],bookingfor.seatStyle];
							return styles;
						});
//						marker.setStyle([bookingfor.shadowStyle,bookingfor.seatStyle]);
					}
                    seats.push(marker);
					}

                });
            }
            var seatSource = new ol.source.Vector({
                zIndex: 2,
                features: seats
            });
            bookingfor.seatVector = new ol.layer.Vector({
                source: seatSource,
            });
			bookingfor.currentMapSells.addLayer(bookingfor.seatVector);
//			var layerExtent = bookingfor.seatVector.getSource().getExtent();
//			if (layerExtent) {
//				bookingfor.currentMapSells.getView().fit(layerExtent);
//			}
			bookingfor.currentMapSells.getView().setZoom(configurationObj.DefaultZoom);
			}

			jQuery("#" + bookingfor.currentMapSells.getTarget()).unblock();

		}, 'json');

	}
	function bfigotocarttemp(){
		if (bfi_seatCart.count>0)
		{
			jQuery([document.documentElement, document.body]).animate({
				scrollTop: (jQuery(".bookingHeader").first().offset().top - 140)
			}, 1000);
		}
	}

//-->
</script>
<!-- form fields end-->

<?php 

} // if isbot
?>
