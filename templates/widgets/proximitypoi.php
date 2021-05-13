<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$moduleclass_sfx = ''; // classe modulo...
if (empty($resource->Poi )) {
    return;
}
if (!empty( $before_widget) ){
	echo $before_widget;
}
// Check if title is set
if (!empty( $title) ) {
  echo $before_title . $title . $after_title;
}
$currdetails_page = get_post( bfi_get_page_id( 'pointsofinterestdetails' ) );
$urlpage = get_permalink( $currdetails_page->ID );

$merchantdetails_page = get_post( bfi_get_page_id( 'merchantdetails' ) );
$url_merchant_page = get_permalink( $merchantdetails_page->ID );

$accommodationdetails_page = get_post( bfi_get_page_id( 'accommodationdetails' ) );
$url_accommodationdetails_page = get_permalink( $accommodationdetails_page->ID );

$eventdetails_page = get_post( bfi_get_page_id( 'eventdetails' ) );
$url_eventdetails_page = get_permalink( $eventdetails_page->ID );


?>

<div id="bfiproximitycontainer" class="bfimapcontainer <?php echo $moduleclass_sfx ?>" >
	<div class="bfiproximitycontainer-title"><?php _e('Nearby points of interest', 'bfi') ?>:</div>
<?php 
foreach ($resource->Poi as $item ) {
	$route= $urlpage . $item->PointOfInterestId.'-'.BFI()->seoUrl($item->Name);
	if (isset($item->PointOfInterestType) && !empty($item->ReferrerObjectId) && !empty($item->ReferrerObjectName) ) {
		switch ( $item->PointOfInterestType ) {
			case 0 : //Merchant
				$route = $url_merchant_page . $item->ReferrerObjectId  .'-'.BFI()->seoUrl($item->ReferrerObjectName );
				break;
			case 1 : //Product 
				$route = $url_accommodationdetails_page.$item->ReferrerObjectId .'-'.BFI()->seoUrl($item->ReferrerObjectName );
				break;
			case 2 : //Event  
				$route = $url_eventdetails_page.$item->ReferrerObjectId .'-'.BFI()->seoUrl($item->ReferrerObjectName );
				break;
			case 3 : //EventDate   
				$route = $url_eventdetails_page.$item->ReferrerObjectId .'-'.BFI()->seoUrl($item->ReferrerObjectName );
				break;
			case 4 : //GenericPOI    
				break;
		}
	}
?>
	<div class="bfiproximitycontainer-label"><a href="<?php echo $route ?>"><i class="fa fa-map-marker fa-1"></i> <?php echo $item->Name ?> - <?php echo BFCHelper::formatDistanceUnits($item->DistanceFromPoint )?></a></div>
<?php 
}

?>

</div>
<?php 
if (!empty($after_widget)) {
	echo $after_widget;
    }
?><div class="bfi-clearfix"></div>