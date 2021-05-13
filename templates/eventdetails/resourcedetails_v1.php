<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$base_url = get_site_url();
$currencyclass = bfi_get_currentCurrency();

$language = $GLOBALS['bfi_lang'];
$languageForm ='';
if(defined('ICL_LANGUAGE_CODE') &&  class_exists('SitePress')){
		global $sitepress;
		if($sitepress->get_current_language() != $sitepress->get_default_language()){
			$languageForm = "/" .ICL_LANGUAGE_CODE;
		}
}

$details_page = get_post( bfi_get_page_id( 'eventdetails' ) );
$url_resource_page = get_permalink( $details_page->ID );
$resourceName = BFCHelper::getLanguage($resource->Name, $GLOBALS['bfi_lang'], null, array('ln2br'=>'ln2br', 'striptags'=>'striptags')); 
$resourceDescription = BFCHelper::getLanguage($resource->Description, $GLOBALS['bfi_lang'], null, array('striptags'=>'striptags', 'bbcode'=>'bbcode','ln2br'=>'ln2br'));
$resourceDescriptionSeo = BFCHelper::getLanguage($resource->Description, $GLOBALS['bfi_lang'], null, array('ln2br'=>'ln2br', 'bbcode'=>'bbcode', 'striptags'=>'striptags'));
$uri = $url_resource_page.$resource->EventId.'-'.BFI()->seoUrl($resourceName);
$merchantdetails_page = get_post( bfi_get_page_id( 'merchantdetails' ) );
$url_merchant_page = get_permalink( $merchantdetails_page->ID );

$isportal = COM_BOOKINGFORCONNECTOR_ISPORTAL;
$showdata = COM_BOOKINGFORCONNECTOR_SHOWDATA;

$posx = COM_BOOKINGFORCONNECTOR_GOOGLE_POSX;
$posy = COM_BOOKINGFORCONNECTOR_GOOGLE_POSY;
$startzoom = COM_BOOKINGFORCONNECTOR_GOOGLE_STARTZOOM;
$googlemapsapykey = COM_BOOKINGFORCONNECTOR_GOOGLE_GOOGLEMAPSKEY;

$resourceLat = "";
$resourceLon = "";
if(!empty($resource->Address->XPos)){
	$resourceLat = $resource->Address->XPos;
}
if(!empty($resource->Address->YPos)){
	$resourceLon = $resource->Address->YPos;
}

$showResourceMap = (($resourceLat != null) && ($resourceLon !=null));

$indirizzo = "";
$cap = "";
$comune = "";
$provincia = "";

if(!empty( $resource->Address )){
	$indirizzo = $resource->Address->Address;
	$cap = $resource->Address->ZipCode;
	$comune = $resource->Address->CityName;
	$provincia = $resource->Address->RegionName;
	$stato = !empty($resource->Address->StateName)?$resource->Address->StateName:"";
}

$resourceRoute = $uri;

$resource->Dates = json_decode($resource->DatesString);
//ordinamento per data
usort($resource->Dates, function($a, $b) {
	$currStartDate = BFCHelper::parseStringDateTime($a->StartDate);
	$currEndDate = BFCHelper::parseStringDateTime($b->EndDate);
	return $currStartDate->format('U') - $currEndDate->format('U');
});
$dateCount = Count($resource->Dates);

$lstTags= array();
$resourceAttachments= array();
if(isset($resource->TagsString) && !empty($resource->TagsString)){
	$lstTags = json_decode($resource->TagsString);
}
if(isset($resource->AttachmentsString) && !empty($resource->AttachmentsString)){
	$resourceAttachments = json_decode($resource->AttachmentsString);
	//ordinamento per ordine
	usort($resourceAttachments, function($a, $b) {
		return $a->Order - $b->Order;
	});
}

$searchAvailability_page = get_post( bfi_get_page_id( 'searchavailability' ) );
$formRoute = get_permalink( $searchAvailability_page->ID );

$startDate = BFCHelper::parseJsonDate($resource->StartDate,'Y-m-d\TH:i:s');
$endDate = BFCHelper::parseJsonDate($resource->EndDate,'Y-m-d\TH:i:s');
$startDate  = new DateTime($startDate,new DateTimeZone('UTC'));
$endDate  = new DateTime($endDate,new DateTimeZone('UTC'));


// SEO
$payloadresource["@type"] = "Event";
$payloadresource["@context"] = "http://schema.org";
$payloadresource["name"] = $resourceName;
$payloadresource["description"] = $resourceDescriptionSeo;
$payloadresource["url"] = $resourceRoute; 
if (!empty($resource->ImageUrl)){
	$payloadresource["image"] = "https:".BFCHelper::getImageUrlResized('events',$resource->ImageUrl, 'logobig');
}
?>
<script type="application/ld+json">// <![CDATA[
<?php echo json_encode($payloadresource); ?>
// ]]></script>

<div class="bfi-content">
    <div class="bfi-row">
        <div class="bfi-col-md-10">
            <div class="bfi-title-name ">
				<?php echo  $resourceName?>
<?php 
				$favoriteModel = array(
					"ItemId"=>$resource->EventId,
					"ItemName"=>BFCHelper::string_sanitize($resourceName),
					"ItemType"=>2,
					"StartDate"=>$startDate->format("YmdHis") ,
					"EndDate"=>$endDate->format("YmdHis"),
					"ItemURL"=>BFCHelper::bfi_get_curr_url(),
					"WrapToContainer"=>1,
					);
				bfi_get_template("shared/favorite_icon.php",$favoriteModel);	
?>
			</div>
            <div class="bfi-address ">
                <i class="fa fa-map-marker fa-1"></i>
				<?php if (($showResourceMap)) {?><a class="bfi-map-link" rel="#resource_map"><?php } ?>
					<?php if(isset($resource->Address) && !empty( $resource->Address) && !empty($resource->Address->Name)) { ?><span class="street-address"><?php echo $resource->Address->Name ?></span>,&nbsp;<?php } ?>
                        <span class="street-address"><?php echo $indirizzo ?></span>,&nbsp;
                        <span class="postal-code "><?php echo  $cap ?></span>&nbsp;
                        <span class="locality"><?php echo $comune ?></span>,&nbsp;
                        <span class="region"><?php echo  $stato ?></span>
  				<?php if (($showResourceMap)) {?></a><?php } ?>
				<?php if(isset($resource->Address) && isset($resource->Address->CenterDistance)) { ?>
                    <span class="bfi-centerdistance" data-toggle="tooltip" title="<?php _e('This is the straight-line distance on the map. Actual travel distance may vary.', 'bfi') ?>">(<i class="fa fa-road" aria-hidden="true"></i> <?php echo BFCHelper::formatDistanceUnits($resource->Address->CenterDistance)?> <?php _e('from centre', 'bfi') ?>)</span>
				<?php } ?>
            </div>
        </div>
        <div class="bfi-col-md-2 bfi-text-right">
        </div>
    </div>
</div>

	<div class="bfi-resourcecontainer-gallery">
	<?php  
			$bfiSourceData = 'events';
			$bfiImageData = null;
			$bfiVideoData = null;
			if(!empty($resource->ImageData)) {
				$bfiImageData = $resource->ImageData;
			}
			if(!empty($resource->VideoData)) {
				$bfiVideoData = $resource->VideoData;
			}
			bfi_get_template("shared/gallery.php",array("merchant"=>null,"bfiSourceData"=>$bfiSourceData,"bfiImageData"=>$bfiImageData,"bfiVideoData"=>$bfiVideoData));	
	?>
	</div>

<div class="bfi-content">
    <div class="bfi-row bfi-border bfi-table-container">
        <div class="bfi-col-md-3">
            <div class="bfi-shortcal">
                <i class="fa fa-calendar" aria-hidden="true"></i> <i class="fa fa-clock-o" aria-hidden="true"></i>
                <div class="bfi-shortcal-date">
					<?php echo date_i18n('l',$startDate->getTimestamp()) . " " . $startDate->format("d") . " " . date_i18n('M',$startDate->getTimestamp()) . " " . $startDate->format("Y") ?>
                    <?php 
					if($startDate != $endDate && $dateCount == 1) { 
						echo date_i18n('l',$endDate->getTimestamp()) . " " . $endDate->format("d") . " " . date_i18n('M',$endDate->getTimestamp()) . " " . $endDate->format("Y") ;
                    } 
					if($startDate != $endDate && $dateCount > 1) { 
						echo " + " . ($dateCount-1) . " " . __('Dates', 'bfi');
                    } 
					?>
                </div>
                <div class="bfi-shortcal-hours">
                    <?php _e('from hours', 'bfi') ?> <?php echo $startDate->format("H:i") ?>
                    <?php 
					if($startDate != $endDate) {
					?>
                        <span><?php _e('to hours', 'bfi') ?> <?php echo $endDate->format("H:i") ?></span>
				<?php
					} 
				?>

                </div>
<!-- LISTA DATE -->
				<?php 
					if($dateCount > 1 ) {
						$currIndex = 0;
				?>
                    <div class="bfi-showotherdates">
                        <a class="bfi-btn bfi-alternative2" href="javascript:bfishowotherdates()"><i class="fa fa-list-ul" aria-hidden="true"></i> <?php _e('Show dates and places', 'bfi') ?></a>
                        <div id="bfi-otherdatesevents" style="display:none;">
						<?php 
							$listEvents = array();
							foreach($resource->Dates as $item) {
								if(!array_key_exists($item->EventId, $listEvents)){
									$listEvents[$item->EventId] = array();
								}
								$listEvents[$item->EventId][] = $item;

							}

//						echo "<pre>";
//						echo print_r($listEvents);
//						echo "</pre>";
							foreach($listEvents as $item) {
									$itemRoute = $url_resource_page.$item[0]->EventId.'-'.BFI()->seoUrl($item[0]->EventName);
								?>
								<a href="<?php echo $itemRoute ?>" class="bfi-event-title"><?php echo $item[0]->EventName ?></a>
								
								<?php 
									foreach ($item as $itm ) { //elenco singole date
										$currIndex += 1;
										$currStartDate = BFCHelper::parseStringDateTime($itm->StartDate);
										$currEndDate = BFCHelper::parseStringDateTime($itm->EndDate);

									?>
										<div class="bfi-row bfi-list-date-event">
											<div class="bfi-col-md-2 bfi-text-center">
												<div class="bfi-date-event">
													<?php echo date_i18n('D',$currStartDate->getTimestamp()) ?> <div class="bfi-date-event-day"><?php echo $currStartDate->format("d") ?></div><?php echo date_i18n('M',$currStartDate->getTimestamp()) ?>
<?php 
				$favoriteModel = array(
					"ItemId"=>(!empty($itm->EventDateId)) ?$itm->EventDateId:$itm->EventId,
					"ItemName"=>BFCHelper::string_sanitize($itm->EventName),
					"ItemType"=>3,
					"StartDate"=>$currStartDate->format("YmdHis") ,
					"EndDate"=>$currEndDate->format("YmdHis"),
					"ItemURL"=>BFCHelper::bfi_get_curr_url(),
					"WrapToContainer"=>1,
					);
				bfi_get_template("shared/favorite_icon.php",$favoriteModel);	
?>
												</div>
											</div>
											<div class="bfi-col-md-8">
												<div class="bfi-item-date-event">
													<i class="fa fa-calendar" aria-hidden="true"></i> <?php _e('from', 'bfi') ?> <?php echo date_i18n('D',$currStartDate->getTimestamp()) ?> <?php echo $currStartDate->format("d") ?> <?php echo date_i18n('M',$currStartDate->getTimestamp()) ?> <?php _e('from hours', 'bfi') ?> <?php echo $currStartDate->format("H:i") ?>
													<?php if($currStartDate != $currEndDate) { ?>
														<?php _e('to', 'bfi') ?> <?php echo date_i18n('D',$currEndDate->getTimestamp()) ?> <?php echo $currEndDate->format("d") ?> <?php echo date_i18n('M',$currEndDate->getTimestamp()) ?> <?php _e('to hours', 'bfi') ?> <?php echo $currEndDate->format("H:i") ?>
													<?php } ?>
												   <div class="bfi-address" style="cursor:pointer;" onclick="createSingleEventMap('bfi-map-single-event-<?php echo $currIndex ?>', <?php echo $itm->XPos ?>, <?php echo $itm->YPos ?>, <?php echo $startzoom ?>,'<?php echo date_i18n('D',$currStartDate->getTimestamp()) ?>','<?php echo $currStartDate->format("d") ?>','<?php echo date_i18n('M',$currStartDate->getTimestamp()) ?>')"><i class="fa fa-map-marker fa-1"></i> <?php echo $itm->Address ?>, <?php echo $itm->ZipCode ?> <?php echo $itm->CityName ?> <?php echo $itm->StateName ?></div>
													<div id="bfi-map-single-event-<?php echo $currIndex ?>" class="bfi-map-single-event"></div>
												</div>
											 </div>
											<div class="bfi-col-md-2 bfi-text-center">
											<?php if(!empty($resource->IsEventGroup)) { ?>
														 <a href="<?php echo $itemRoute ?>" class="bfi-btn bfi-alternative"><?php _e('Details', 'bfi') ?></a>
											<?php } ?>
											</div>
										</div>
									<?php 
									    
									}

							}
												
						?>
                        </div>
                    </div>
				<?php
					} 
				?>
				<!-- FINE LISTA DATE -->
				

            </div>
        </div>
        <div class="bfi-col-md-6">
<!-- MAPPA -->
<?php if (($showResourceMap)) {?>
                <div id="bfimaptab">
                    <div class="bfi-content-map">
                        <div id="resource_map" style="width:100%;height:200px"></div>
						<?php 
							if (COM_BOOKINGFORCONNECTOR_USE_OPENSTREETMAP) {
						?>
                            <script type="text/javascript">
var mapUnit;
var myLatLng;
var Leaflet;
Leaflet = L.noConflict();
jQuery(document).ready(function(){

    myLatLng = new Leaflet.LatLng(<?php echo $resourceLat ?>, <?php echo $resourceLon ?>);
	mapUnit = Leaflet.map('resource_map').setView(myLatLng, <?php echo $startzoom ?>);
	var OpenStreetMap_Mapnik = Leaflet.tileLayer(bfi_variables.bfi_freemaptileurl, {
		maxZoom: 19,
		attribution: bfi_variables.bfi_freemaptileattribution
	});
	OpenStreetMap_Mapnik.addTo(mapUnit);

	//var marker = Leaflet.marker(myLatLng).addTo(mapUnit);

	var instanceclass = "blulighttheme";
	var bfiIcon,loc;
	bfiIcon = Leaflet.divIcon({
			iconSize:null,
			html: '<div class="bfi-map-label ' + instanceclass + '"><div class="bfi-map-label-content bfi-map-event"><?php echo date_i18n('l',$startDate->getTimestamp()) ?> <div class="bfi-map-event-day"><?php echo $startDate->format("d") ?></div><?php echo date_i18n('M',$startDate->getTimestamp()) ?></div><div class="bfi-map-label-arrow"></div></div>'
		});
	mapUnit.addLayer(new L.Marker(myLatLng,{icon: bfiIcon}));
	bookingfor.bfiLoadPois(mapUnit);
});

function createSingleEventMap(refId, posx, posy, startzoom, currNameDay, currDay, currMonth) {
	jQuery(".bfi-map-single-event").not(jQuery("#" + refId)).hide();
	jQuery("#" + refId).toggle(200, function () {
		var currLatLng = new Leaflet.LatLng(posx, posy);
		if (!this._leaflet_id) {
			var currMap = Leaflet.map(refId).setView(currLatLng, startzoom);
			var OpenStreetMap_Mapnik = Leaflet.tileLayer(bfi_variables.bfi_freemaptileurl, {
				maxZoom: 19,
				attribution: bfi_variables.bfi_freemaptileattribution
			});
			OpenStreetMap_Mapnik.addTo(currMap);
			var instanceclass = "blulighttheme";
			var bfiIcon = Leaflet.divIcon({
					iconSize:null,
					html: '<div class="bfi-map-label ' + instanceclass + '"><div class="bfi-map-label-content bfi-map-event">'+currNameDay+' <div class="bfi-map-event-day">'+currDay+'</div>'+currMonth+' </div><div class="bfi-map-label-arrow"></div></div>'
				});
				currMap.addLayer(new L.Marker(currLatLng,{icon: bfiIcon}));

		}
	});
}
                            </script>

						<?php 
							}else{
						?>
                            <script type="text/javascript">

		<!--
			var mapUnit;
			var myLatLng,loc;

			// make map
			function handleApiReady() {
				jQuery.getScript("<?php echo BFI()->plugin_url() ?>/assets/js/markerwithlabel.js", function(data, textStatus, jqxhr) {

				myLatLng = new google.maps.LatLng(<?php echo $resourceLat ?>, <?php echo $resourceLon ?>);
				var myOptions = {
					zoom: <?php echo $startzoom ?>,
					center: myLatLng,
					mapTypeId: google.maps.MapTypeId.ROADMAP

				}
				mapUnit = new google.maps.Map(document.getElementById("resource_map"), myOptions);
				var marker = new google.maps.Marker({
					position: myLatLng,
					map: mapUnit,
					title: '<?php echo BFCHelper::string_sanitize($resource->Name) ?>'
				});

				var instanceclass = " bfi-map-label bfi-googlemap blulighttheme bfi-map-events";
				marker = new MarkerWithLabel({
					position: myLatLng,
					draggable: false,
					raiseOnDrag: false,
					map: mapUnit,
					labelContent: '<div class="bfi-map-label-content bfi-map-event"><?php echo date_i18n('l',$startDate->getTimestamp()) ?> <div class="bfi-map-event-day"><?php echo $startDate->format("d") ?></div><?php echo date_i18n('M',$startDate->getTimestamp()) ?></div><div class="bfi-map-label-arrow"></div>',
					labelAnchor: new google.maps.Point(22, 22),
					labelClass: instanceclass, // the CSS class for the label
					icon: {
					url: 'https://maps.gstatic.com/mapfiles/api-3/images/spotlight-poi2.png',
					scaledSize : new google.maps.Size(1, 1)
					},
				});
				redrawmap(mapUnit)
			});

        }

        function openGoogleMapResource() {
            if (typeof google !== 'object' || typeof google.maps !== 'object'){
                var script = document.createElement("script");
                script.type = "text/javascript";
				script.src = "https://maps.google.com/maps/api/js?key=<?php echo $googlemapsapykey ?>&libraries=drawing,places&callback=handleApiReady";
                document.body.appendChild(script);
            }else{
                if (typeof mapUnit !== 'object'){
                    handleApiReady();
                }
            }
        }
        function redrawmap(currMap) {
            if (typeof google !== "undefined")
            {
                if (typeof google === 'object' || typeof google.maps === 'object'){
                    google.maps.event.trigger(currMap, 'resize');
                    mapUnit.setCenter(myLatLng);
                }
            }
				}

		function hasMap( id ) {
			return !! document.getElementById(id).firstChild;
		}


        jQuery(window).resize(function() {
            redrawmap(mapUnit)
        });

        jQuery(document).ready(function(){
            openGoogleMapResource();
        });

	//-->
function createSingleEventMap(refId, posx, posy, startzoom,currNameDay,currDay,currMonth){
	jQuery(".bfi-map-single-event").not(jQuery("#" + refId)).hide();
	jQuery("#" + refId).toggle(200, function () {
		var currLatLng = new google.maps.LatLng(posx, posy);
		var myOptions = {
			zoom: startzoom,
			center: currLatLng,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		}
		if (!hasMap(refId)) {
			var currMap = new google.maps.Map(document.getElementById(refId), myOptions);
			var marker = new google.maps.Marker({
				position: currLatLng,
				map: currMap,
				title: '<?php echo BFCHelper::string_sanitize($resource->Name) ?>'
			});
			var instanceclass = " bfi-map-label bfi-googlemap blulighttheme bfi-map-events";
			marker = new MarkerWithLabel({
				position: currLatLng,
				draggable: false,
				raiseOnDrag: false,
				map: currMap,
				labelContent: '<div class="bfi-map-label-content bfi-map-event">'+currNameDay+' <div class="bfi-map-event-day">'+currDay+'</div>'+currMonth+'</div><div class="bfi-map-label-arrow"></div>',
				labelAnchor: new google.maps.Point(22, 22),
				labelClass: instanceclass, // the CSS class for the label
				icon: {
				url: 'https://maps.gstatic.com/mapfiles/api-3/images/spotlight-poi2.png',
				scaledSize : new google.maps.Size(1, 1)
				},
			});
			redrawmap(currMap);
		}
	});
}
                            </script>
						
						<?php
							} 
						?>
 
					</div>
                </div>


<?php } ?>


<!-- FINE MAPPA -->
		</div>
        <div class="bfi-col-md-3 bfi-btn-event">
<!-- BOTTONI -->
<?php if (false) { ?>

            <button class="bfi-btn bfi-alternative5 bfi-addtravelplanner bfi-sendto-favorite bfi-icon-favorite" 
			  data-itemid="<?php echo $resource->EventId ?>" 
			  data-itemname="<?php echo BFCHelper::string_sanitize($resourceName)?>" 
			  data-itemurl="<?php echo BFCHelper::bfi_get_curr_url()?>"
			  data-groupid="" 
			  data-itemtype="2"
			  data-startdate="<?php echo !empty($startDate)?$startDate->format("YmdHis"):""; ?>"
			  data-enddate="<?php echo !empty($endDate)?$endDate->format("YmdHis"):""; ?>"
			><i class="fa fa-heart-o"></i>
        <i class="fa fa-heart"></i> <?php _e('Travel planner', 'bfi') ?></button>
<?php } ?>

<?php if(!empty($resource->AccommodationSearch) && $resource->AccommodationSearch->SearchType == 4 ) {
	
$currFrom = BFCHelper::parseJsonDate($resource->AccommodationSearch->StartDate,'Y-m-d\TH:i:s');
$currTo = BFCHelper::parseJsonDate($resource->AccommodationSearch->EndDate,'Y-m-d\TH:i:s');
if(!empty( $_REQUEST['FromDate'] ) && !empty( $_REQUEST['ToDate'] )){
	$currFrom  = DateTime::createFromFormat('YmdHis', $_REQUEST['FromDate'], new DateTime($startDate,new DateTimeZone('UTC')));
	$currTo  = DateTime::createFromFormat('YmdHis', $_REQUEST['ToDate'], new DateTime($endDate,new DateTimeZone('UTC')));
	
}
	
//                    $currFrom = DateTime::createFromFormat('YmdHis', $_REQUEST['FromDate'], ((!empty($resource->AccommodationSearch->StartDate))?$resource->AccommodationSearch->StartDate: new DateTime('UTC')))->format("YmdHis");
//                    $currFrom = DateTime::createFromFormat('YmdHis', $_REQUEST['ToDate'], ((!empty($resource->AccommodationSearch->EndDate))?$resource->AccommodationSearch->EndDate: new DateTime('UTC')))->format("YmdHis");
?>
            <form id="searchformeventsdetails" action="<?php echo $formRoute?>" method="get" target="_blank">
				<input type="hidden" name="stateIds" value="" />
				<input type="hidden" name="regionIds" value="" />
				<input type="hidden" name="cityIds" value="" />
				<input type="hidden" name="merchantIds" value="" />
				<input type="hidden" name="merchantTagIds" value="<?php echo $resource->AccommodationSearch->MrcTags ?>" />
				<input type="hidden" name="productTagIds" value="<?php echo $resource->AccommodationSearch->ResTags ?>" />
				<input type="hidden" name="MerchantCategoryIds" value="<?php echo $resource->AccommodationSearch->MerchantCategories ?>" />
				<input type="hidden" name="MasterTypologyIds" value="<?php echo $resource->AccommodationSearch->ProductCategories ?>" />
				<input type="hidden" name="CheckAvailability" value="<?php echo $resource->AccommodationSearch->CheckAvailability ?>" />
				<input type="hidden" name="FromDate" value="<?php echo $currFrom ?>" />
				<input type="hidden" name="ToDate" value="<?php echo $currTo ?>" />
				<input type="hidden" name="minqt" value="1" />
				<input type="hidden" name="maxqt" value="1" />
				<input type="hidden" name="AdultCount" value="<?php echo $resource->AccommodationSearch->MinPaxes ?>" />
				<input type="hidden" name="SeniorCount" value="0" />
				<input type="hidden" name="ChildrenCount" value="0" />
				<input type="hidden" name="BookableOnly" value="0" />
				<input type="hidden" name="layout" value="" />
				<input type="hidden" name="SearchType" value="<?php echo $resource->AccommodationSearch->SearchType ?>" />
				<input type="hidden" name="SearchTypeTab" value="0" />
				<input type="hidden" name="searchId" value="<?php echo uniqid('', true)?>" />
				<input type="hidden" name="maxqt" value="" />
				<input type="hidden" name="AvailabilityTypes" value="<?php echo $resource->AccommodationSearch->AvailabilityTypes ?>" />
				<input type="hidden" name="ItemTypes" value="<?php echo $resource->AccommodationSearch->ItemTypes ?>" />
				<input type="hidden" name="GroupResultType" value="<?php echo $resource->AccommodationSearch->ItemTypes ?>" />
				<input type="hidden" name="GetBestGroupResult" value="1" />
				<input type="hidden" name="getAllResults" value="0" />
				<input type="hidden" name="discountcodes" value="<?php echo ($resource->AccommodationSearch->DiscountCode ?? "") ?>" />
				<input type="hidden" name="CheckFullPeriod" value="<?php echo $resource->AccommodationSearch->CheckFullPeriod ?>" />
				<button type="submit" class="bfi-btn bfi-bookresource">
					<svg xmlns="http://www.w3.org/2000/svg"
						 xmlns:xlink="http://www.w3.org/1999/xlink"
						 width="16px" height="10px">
						<image x="0px" y="0px" width="16px" height="10px" xlink:href="data:img/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAKCAQAAAAXtxYXAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAAAmJLR0QA/4ePzL8AAAAHdElNRQfjCA0KJRhoFp33AAAAzklEQVQY013OMUuCYRiF4Vv6UIiQcAj8BCFxcg6XBptqitpUaK9+haj/IZobcnB3z7G2ljQoadMGsRoiUe6GVzC61nM4z4OIeGVDxLbXIl56GxLEbfHIunjg1IkV8dgTN9wJhYIPxuJAffFTfTIl3rsfCpg3dqjuWXWh6qNZ43Di3J4lT9WyTddqZux7gbsemvFGffavrkkr5iJGjIAuRXK0SJLgizM+6DDnDiKCAe9s8koamPHNmLdVImLKsf/Nw5NhYUmfIlPWtpjxA/AL8tK1kUvZ4uIAAAAASUVORK5CYII=" />
					</svg>
					<?php _e('Book Resource', 'bfi') ?>
				</button>
			</form>
<?php } ?>
<?php 
if(!empty( $resource->ServiceSearch )){
	switch ($resource->ServiceSearch->SearchType) {
                case 1: // link esterno
				?>
                    <a class="bfi-btn bfi-alternative bfi-buyresource" href="<?php echo $resource->ServiceSearch->ExternalLink ?>" target="_blank"><i class="fa fa-ticket" aria-hidden="true"></i> <?php _e('Buy resource', 'bfi') ?></a>
				<?php 
				
                    break;
                case 2: //risorsa
					if(!empty( $resource->ServiceSearch->ResourceId )){
						$res = BFCHelper::GetResourcesById( $resource->ServiceSearch->ResourceId );
						$accommodationdetails_page = get_post( bfi_get_page_id( 'accommodationdetails' ) );
						$url_resource_page = get_permalink( $accommodationdetails_page->ID );
						$resourceRoute = $url_resource_page . $res->ResourceId .'-'.BFI()->seoUrl($res->Name);
?>
						<a class="bfi-btn bfi-alternative bfi-buyresource" href="<?php echo $resourceRoute ?>"><i class="fa fa-ticket" aria-hidden="true"></i> <?php _e('Buy resource', 'bfi') ?></a>
<?php 

					}
					
                    break;
                case 3: //gruppo di risorse
					if(!empty( $resource->ServiceSearch->ResourceGroupId )){
						$res = BFCHelper::getResourcegroupFromServicebyId( $resource->ServiceSearch->ResourceId );
						$accommodationdetails_page = get_post( bfi_get_page_id( 'resourcegroupdetails' ) );
						$url_resource_page = get_permalink( $accommodationdetails_page->ID );
						$resourceRoute = $url_resource_page . $res->ResourceId .'-'.BFI()->seoUrl($res->Name);
?>
						<a class="bfi-btn bfi-alternative bfi-buyresource" href="<?php echo $resourceRoute ?>"><i class="fa fa-ticket" aria-hidden="true"></i> <?php _e('Buy resource', 'bfi') ?></a>
<?php 
					}
                default:
                    break;
	        
	}

}
?>
			<!-- AddToAny BEGIN -->
			<a class="bfi-btn bfi-alternative2 a2a_dd"  href="http://www.addtoany.com/share_save" ><i class="far fa-share-alt"></i> <?php _e('Share', 'bfi') ?></a>
			<script async src="https://static.addtoany.com/menu/page.js"></script>
			<!-- AddToAny END -->


<!-- FINE BOTTONI -->
		</div>
    </div>


	<?php if (!empty($resourceDescription)){?>
    <div id="bfidescriptiontab">
            <div class="bfi-description-data">
				<?php echo $resourceDescription ?>		
            </div>
            <br />
    </div>
	<?php } ?>

    <div class="bfi-row bfi-padding10 bfi-chess">
<?php if(!empty($resource->Organizer) && $resource->Organizer->InformationType != -1) { ?>
            <div class="bfi-col-md-6">
                <div class="bfi-org-title"><?php _e('Organizer', 'bfi') ?></div>
				<?php if(!empty($resource->Organizer->MerchantId)) { 
					$merchant = $resource->Organizer;  
					$routeMerchant = $url_merchant_page . $merchant->MerchantId.'-'.BFI()->seoUrl($merchant->Name);
                    $mrcindirizzo = $merchant->Address;
                    $mrccap = $merchant->ZipCode;
                    $mrccomune = $merchant->CityName;
                    $mrcstate = $merchant->StateName;
					$merchantLogo = BFI()->plugin_url() . "/assets/images/defaults/default-s3.jpeg";
					if (!empty($merchant->LogoUrl)){
						$merchantLogo = BFCHelper::getImageUrlResized('merchant',$merchant->LogoUrl, 'logobig');
					}
					$hasSuperior = !empty($merchant->RatingSubValue);
					$rating = $merchant->Rating;
					if ($rating > 9)
					{
						$hasSuperior = ($merchant->Rating%10)>0;
						$rating = (int)($rating / 10);
					} 
				
				?>
                    <div class="bfi-row ">
                        <div class="bfi-col-md-5 bfi-vcard-logo-box">
                            <div class="bfi-vcard-logo">
                                <a href="<?php echo $routeMerchant ?>">
                                    <img src="<?php echo $merchantLogo ?>">
                                </a>
                            </div>
                        </div>
                        <div class="bfi-col-md-7 bfi-pad0-10">
                            <div class="bfi-vcard-name bfi-org-name">
								<span class="bfi-item-rating">
									<?php for($i = 0; $i < $rating ; $i++) { ?>
									  <i class="fa fa-star"></i>
									<?php } ?>
									<?php if ($hasSuperior) { ?>
										&nbsp;S
									<?php } ?>
								</span>

                                <a href="<?php echo $routeMerchant ?>" class="bfi-org-name-link"><?php echo $merchant->Name ?> </a>
                            </div>
                            <div class="bfi-org-address">
                                <span class="street-address"><?php echo $mrcindirizzo ?></span>, <span class="postal-code "><?php echo $mrccap ?></span> <span class="locality"><?php echo $mrccomune ?></span><span class="state"> (<?php echo $mrcstate ?>)</span><br>
                            </div>
                        </div>
                    </div>
                <?php 
                } else {
                ?>
                
                    <div class="bfi-org-name">
                        <?php echo $resource->Organizer->Name ?>
                    </div>
                    <div class="bfi-org-address">
                        <div class="bfi-org-address">
                            <span class="street-address"><?php echo $resource->Organizer->Address ?></span>, <span class="postal-code "><?php echo $resource->Organizer->ZipCode ?></span> <span class="locality"><?php echo $resource->Organizer->CityName ?></span><span class="state"> (<?php echo $resource->Organizer->StateName ?>)</span><br>
                        </div>
                    </div>

				<?php } ?>
            </div>
<?php } ?>
<?php 
	if(!empty($lstTags)) { 
		foreach ($lstTags as $gr ) {
?>
                <div class="bfi-col-md-6">
                    <div class="bfi-org-title"><?php echo $gr->Name ?></div>
                    <div class="bfi-facility-list">
						<?php echo implode(' ',array_unique(array_map(function ($i) { return "<span class='bfi-tag-name'>".$i->Name."</span>" ; }, $gr->Tags))); ?>
                    </div>
                </div>

<?php 
		}
	} 
?>
<?php if(!empty($resourceAttachments)){
	?>
		<div class="bfi-col-md-6 bfi-attachmentfiles">
			<div class="bfi-download-title"><?php _e('Download', 'bfi') ?></div>
		<?php 
		foreach ($resourceAttachments as $resourceAttachment) {
			$resourceAttachmentName = $resourceAttachment->Name;
			$resourceAttachmentExtension= "";
			
			$path_parts = pathinfo($resourceAttachmentName);
			if(!empty( $path_parts['extension'])){
				$resourceAttachmentExtension = $path_parts['extension'];
				$resourceAttachmentName =  str_replace(".".$resourceAttachmentExtension, "", $resourceAttachmentName);
			}
			?>
			<span class="bfi-download-name"><a href="<?php echo $resourceAttachment->LinkValue ?>" target="_blank"><?php echo $resourceAttachmentName ?></a></span>
			<?php 
		}
?>
	</div>
<?php } ?>


	</div>
	<div class="bfi-clearfix"></div>

</div>


                <script>
						var bfi_wuiP_width = 800;
						var bfi_wuiP_height = 600;
						var dialogEvents;

					    function bfishowotherdates() {
						    if(jQuery(window).width()<bfi_wuiP_width){
							    bfi_wuiP_width = jQuery(window).width()*0.8;
						    }
						    if(jQuery(window).height()<bfi_wuiP_height){
							    bfi_wuiP_height = jQuery(window).height()*0.8;
						    }
						    if(jQuery(window).width()<465){
							    bfi_wuiP_width = jQuery(window).width();
						    }
						    if (!!jQuery.uniform){
							    jQuery.uniform.restore(jQuery("#bfi-calculatorForm select"));
						    }
						    dialogEvents = jQuery( "#bfi-otherdatesevents" ).dialog({
							    title:'<i class="fa fa-list-ul" aria-hidden="true"></i> <?php _e('All dates and places', 'bfi') ?>',
							    autoOpen: false,
							    width: bfi_wuiP_width,
							     maxHeight: bfi_wuiP_height,
							    modal: true,
							    dialogClass: 'bfi-dialog bfi-dialog-event',
							    clickOutside: true,
							    resizable: false,
						    });
						    dialogEvents.dialog("open");
					    }
                </script>
