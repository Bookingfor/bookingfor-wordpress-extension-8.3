<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$posx = COM_BOOKINGFORCONNECTOR_GOOGLE_POSX;
$posy = COM_BOOKINGFORCONNECTOR_GOOGLE_POSY;
$startzoom = COM_BOOKINGFORCONNECTOR_GOOGLE_STARTZOOM;
$googlemapsapykey = COM_BOOKINGFORCONNECTOR_GOOGLE_GOOGLEMAPSKEY;

$resourceLat = null;
$resourceLon = null;
$showMarker = true;
$datamarkettype = 0;
$datanameday = "";
$dataday = "";
$datamonth = "";

if(BFI()->isMerchantPage()){
	$merchant_id = get_query_var( 'merchant_id', 0 );
	$model = new BookingForConnectorModelMerchantDetails;
	$merchant = $model->getItem($merchant_id);
	if (!empty($merchant->XGooglePos) && !empty($merchant->YGooglePos)) {
		$resourceLat = $merchant->XGooglePos;
		$resourceLon = $merchant->YGooglePos;
	}
}
if(BFI()->isResourcePage()){
	$resource_id = get_query_var( 'resource_id', 0 );
	$model = new BookingForConnectorModelResource;
	$resource = $model->getItem($resource_id);
    if(!empty($resource->XPos)){
		$resourceLat = $resource->XPos;
	}
	if(!empty($resource->YPos)){
		$resourceLon = $resource->YPos;
	}

    if(!empty($resource->Address->XPos)){
		$resourceLat = $resource->Address->XPos;
	}
	if(!empty($resource->Address->YPos)){
		$resourceLon = $resource->Address->YPos;
	}
}

if(BFI()->isResourcegroupPage()){
	$resource_id = get_query_var( 'resource_id', 0 );
	$model = new BookingForConnectorModelResourcegroup;
	$resource = $model->getItem($resource_id);
    if(!empty($resource->XPos)){
		$resourceLat = $resource->XPos;
	}
	if(!empty($resource->YPos)){
		$resourceLon = $resource->YPos;
	}

    if(!empty($resource->Address->XPos)){
		$resourceLat = $resource->Address->XPos;
	}
	if(!empty($resource->Address->YPos)){
		$resourceLon = $resource->Address->YPos;
	}
}

if(BFI()->isResourceOnSellPage()){
	$resource_id = get_query_var( 'resource_id', 0 );
	$model = new BookingForConnectorModelOnSellUnit;
	$resource = $model->getItem($resource_id);
    if(!empty($resource->XPos)){
		$resourceLat = $resource->XPos;
	}
	if(!empty($resource->YPos)){
		$resourceLon = $resource->YPos;
	}

    if(!empty($resource->Address->XPos)){
		$resourceLat = $resource->Address->XPos;
	}
	if(!empty($resource->Address->YPos)){
		$resourceLon = $resource->Address->YPos;
	}
}

if(BFI()->isEventPage()){
	$resource_id = get_query_var( 'resource_id', 0 );
	$model = new BookingForConnectorModelEvent;
	$resource = $model->getItem($resource_id);
	if(!empty($resource->Address->XPos)){
		$resourceLat = $resource->Address->XPos;
	}
	if(!empty($resource->Address->YPos)){
		$resourceLon = $resource->Address->YPos;
	}
	$datamarkettype = 2;
	$startDate = BFCHelper::parseJsonDate($resource->StartDate,'Y-m-d\TH:i:s');
	$endDate = BFCHelper::parseJsonDate($resource->EndDate,'Y-m-d\TH:i:s');
	$startDate  = new DateTime($startDate,new DateTimeZone('UTC'));
	$endDate  = new DateTime($endDate,new DateTimeZone('UTC'));

	$datanameday = date_i18n('l',$startDate->getTimestamp());
	$dataday =  $startDate->format("d");
	$datamonth = date_i18n('M',$startDate->getTimestamp());

}
if(BFI()->isPoiPage()){
	$resource_id = get_query_var( 'resource_id', 0 );
	$model = new BookingForConnectorModelPointsofinterest;
	$resource = $model->getItem($resource_id);
	if(!empty($resource->Address->XPos)){
		$resourceLat = $resource->Address->XPos;
	}
	if(!empty($resource->Address->YPos)){
		$resourceLon = $resource->Address->YPos;
	}
}
if(BFI()->isSearchPage() || BFI()->isSearchOnSellPage() || (isset($_GET) && !empty($_GET["resultinsamepg"])) ){
	$resourceLat = COM_BOOKINGFORCONNECTOR_GOOGLE_POSX;
	$resourceLon = COM_BOOKINGFORCONNECTOR_GOOGLE_POSY;
	$showMarker = false;
}
if(BFI()->isSearchEventsPage()){
	$resourceLat = COM_BOOKINGFORCONNECTOR_GOOGLE_POSX;
	$resourceLon = COM_BOOKINGFORCONNECTOR_GOOGLE_POSY;
	$showMarker = false;
}

if ( is_single() && get_post_type() == 'merchantlist' ) {
	$resourceLat = COM_BOOKINGFORCONNECTOR_GOOGLE_POSX;
	$resourceLon = COM_BOOKINGFORCONNECTOR_GOOGLE_POSY;
	$showMarker = false;
}
//if ( is_single() && get_post_type() == 'eventlist' ) {
//	$resourceLat = COM_BOOKINGFORCONNECTOR_GOOGLE_POSX;
//	$resourceLon = COM_BOOKINGFORCONNECTOR_GOOGLE_POSY;
//	$showMarker = false;
//}
if (!empty($setLat) && !empty($setLon)) {
 	$resourceLat = $setLat;
	$resourceLon = $setLon;
	$showMarker = false;
   
}
$moduleclass_sfx = ''; // classe modulo...

$showMap = (($resourceLat != null) && ($resourceLon !=null) );
if (!$showMap) {
    return;
}
if (COM_BOOKINGFORCONNECTOR_USE_OPENSTREETMAP) {
	wp_enqueue_script('bfileaflet');
	wp_enqueue_script('bfileafletcontrolcustom');
}

if (!empty( $before_widget) ){
	echo $before_widget;
}
// Check if title is set
if (!empty( $title) ) {
  echo $before_title . $title . $after_title;
}
?>

<div id="bfimapcontainer" class="bfimapcontainer <?php echo $moduleclass_sfx ?>" style="width:100%;height:300px"  
	data-lat="<?php echo $resourceLat?>"  data-lon="<?php echo $resourceLon?>" 
	data-poi="false"
	data-autocomplete="false"
	<?php if($showMarker) { ?>
		data-markettype="<?php echo $datamarkettype?>" data-markerlon="<?php echo $resourceLon?>" data-markerlat="<?php echo $resourceLat?>"
		data-nameday="<?php echo $datanameday ?>" data-day="<?php echo $dataday ?>" data-month="<?php echo $datamonth ?>" 
	<?php } ?>
	>
	<div class="bfishowpopupmap"
	data-lat="<?php echo $resourceLat?>"  data-lon="<?php echo $resourceLon?>" 
	data-poi="false"
	data-autocomplete="true"
	data-markettype="<?php echo $datamarkettype?>" data-markerlon="<?php echo $resourceLon?>" data-markerlat="<?php echo $resourceLat?>"
	><?php _e('Map view', 'bfi') ?></div>
</div>
<script type="text/javascript">
<!--
	jQuery(document).ready(function(){
		bookingfor.loadSingleMap("bfimapcontainer");
	});
//-->
</script>
<?php 
if (!empty($after_widget)) {
	echo $after_widget;
    }
?><div class="bfi-clearfix"></div>