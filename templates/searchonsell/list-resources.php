<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$totalResult = count($results);
$language = $GLOBALS['bfi_lang'];
$languageForm ='';
if(defined('ICL_LANGUAGE_CODE') &&  class_exists('SitePress')){
		global $sitepress;
		if($sitepress->get_current_language() != $sitepress->get_default_language()){
			$languageForm = "/" .ICL_LANGUAGE_CODE;
		}
}
$listsId = array();
$listResourceMaps = array();

$base_url = get_site_url();

$isportal = COM_BOOKINGFORCONNECTOR_ISPORTAL;
$showdata = COM_BOOKINGFORCONNECTOR_SHOWDATA;

$resourceImageUrl = BFI()->plugin_url() . "/assets/images/defaults/default-s6.jpeg";

//$searchOnSell_page = get_post( bfi_get_page_id( 'searchonsell' ) );
//$formAction = get_permalink( $searchOnSell_page->ID );
//
//
//$merchantdetails_page = get_post( bfi_get_page_id( 'merchantdetails' ) );
//$url_merchant_page = get_permalink( $merchantdetails_page->ID );
//
//$onselldetails_page = get_post( bfi_get_page_id( 'onselldetails' ) );
//$url_resource_page = get_permalink( $onselldetails_page->ID );

$formAction = BFCHelper::getPageUrl('searchonsell');
$url_merchant_page = BFCHelper::getPageUrl('merchantdetails');
$url_resource_page = BFCHelper::getPageUrl('onselldetails');

$uri = $url_resource_page;
$currFilterOrder = "";
$currFilterOrderDirection = "";
if (!empty($currSorting) &&strpos($currSorting, '|') !== false) {
	$acurrSorting = explode('|',$currSorting);
	$currFilterOrder = $acurrSorting[0];
	$currFilterOrderDirection = $acurrSorting[1];
}else{
	$currSorting="";
}
$fromsearchparam = "?fromsearch=1&lna=".$listNameAnalytics;
$showmap = !empty(COM_BOOKINGFORCONNECTOR_ENABLEGOOGLEMAPSAPI);
if ($showmap && COM_BOOKINGFORCONNECTOR_USE_OPENSTREETMAP) {
	wp_enqueue_script('bfileaflet');
	wp_enqueue_script('bfileafletcontrolcustom');
}

?>
<div class="bfi-content">
<div class="bfi-row">
	<div class="bfi-col-xs-9 ">
		<div class="bfi-search-title">
			<?php echo sprintf( __('Found %s results', 'bfi'),$totalResult ) ?>
		</div>
	</div>	
<?php if($showmap && !COM_BOOKINGFORCONNECTOR_ISMOBILE){ ?>
	<div class="bfi-col-xs-3 ">
		<div class="bfi-search-view-maps ">
		<span><?php _e('Map view', 'bfi') ?></span>
		</div>	
	</div>	
<?php } ?>
</div>	

<div class="bfi-search-menu">
	<form action="<?php echo $formAction; ?>" method="post" name="bookingforsearchForm" id="bookingforsearchFilterForm">
			<input type="hidden" class="filterOrder" name="filter_order" value="<?php echo $currFilterOrder ?>" />
			<input type="hidden" class="filterOrderDirection" name="filter_order_Dir" value="<?php echo $currFilterOrderDirection ?>" />
			<input type="hidden" name="searchid" value="<?php //echo   $searchid ?>" />
			<input type="hidden" name="limitstart" value="0" />
	</form>
	<div class="bfi-results-sort">
		<span class="bfi-sort-item-order"><?php echo _e('Order by' , 'bfi')?>:</span>
		<span class="bfi-sort-item <?php echo $currSorting=="price|asc" ? "bfi-sort-item-active": "" ; ?>" rel="price|asc" ><?php echo _e('Lowest price first' , 'bfi'); ?></span>
		<span class="bfi-sort-item <?php echo $currSorting=="created|desc" ? "bfi-sort-item-active": "" ; ?>" rel="created|desc" ><?php echo _e('Latest ads' , 'bfi'); ?></span>
	</div>
	<div class="bfi-view-changer">
		<div class="bfi-view-changer-selected"><?php echo _e('List' , 'bfi') ?></div>
		<div class="bfi-view-changer-content">
			<div id="bfi-list-view"><?php echo _e('List' , 'bfi') ?></div>
			<div id="bfi-grid-view" class="bfi-view-changer-grid"><?php echo _e('Grid' , 'bfi') ?></div>
		</div>
	</div>
</div>

<div class="bfi-clearfix"></div>
<div id="bfi-list" class="bfi-row bfi-list">
	<?php foreach ($results as $currKey => $result){?>
		<?php 
		$resourceImageUrl = BFI()->plugin_url() . "/assets/images/defaults/default-s6.jpeg";

		$resource = $result;
		$resourceName = BFCHelper::getLanguage($result->Name, $language, null, array('ln2br'=>'ln2br', 'striptags'=>'striptags')); 
		$merchantName = $resource->MerchantName;

		if (!empty($result->OnSellUnitId)){
			$resource->ResourceId = $result->OnSellUnitId;
		}
		$resource->Price = $result->MinPrice;	

		$typeName =  BFCHelper::getLanguage($resource->CategoryName, $language);
		$contractType = ($resource->ContractType) ? __('To rent', 'bfi')  : __('On sale', 'bfi');
		$location = $resource->LocationName;
		$resourceName = BFCHelper::getLanguage($resource->Name, $language, null, array('ln2br'=>'ln2br', 'striptags'=>'striptags')); 

		$addressData = "";
		$resourceLat ="";
		$resourceLon = "";
		if(!empty($resource->XPos)){
			$resourceLat = $resource->XPos;
		}
		if(!empty($resource->YPos)){
			$resourceLon = $resource->YPos;
		}
		$isMapVisible = $resource->IsMapVisible;
		$isMapMarkerVisible = $resource->IsMapMarkerVisible;
		$showResourceMap = (($resourceLat != null) && ($resourceLon !=null) && $isMapVisible && $isMapMarkerVisible);
		$val= new StdClass;
		if ($showResourceMap) {
			$val->Id = $resource->ResourceId;
			$val->Lat = $resource->XPos;
			$val->Long = $resource->YPos;
	//		$listResourceMaps[] = $val;
		}
		
		$currUriresource = $uri.$resource->ResourceId.'-'.BFI()->seoUrl($resourceName);
		
		$resourceRoute = $route = $currUriresource.$fromsearchparam;		
		
		$routeMerchant = "";
		if($isportal){
			$routeMerchant = $url_merchant_page . $result->MerchantId .'-' .BFI()->seoUrl($result->MerchantName).$fromsearchparam;
		}
		

		if(!empty($result->ImageUrl)){
			$resourceImageUrl = BFCHelper::getImageUrlResized('onsellunits',$result->ImageUrl, 'medium');
		}
		$resource->RatingsContext = 0;	//set 0 so not show 
		
		$rating = 0;	//set 0 so not show 
		$ratingMrc= 0;	//set 0 so not show 
//		$rating = $resource->Rating;
//		if ($rating>9 )
//		{
//			$rating = $rating/10;
//		}
//		$ratingMrc = $resource->MrcRating;
//		if ($ratingMrc>9 )
//		{
//			$ratingMrc = $ratingMrc/10;
//		}
		$resourceDataTypeTrack = "Onsell";
		$resourceDataIdTrack = $resource->ResourceId ;
		$resourceNameTrack =  BFCHelper::string_sanitize($resourceName);
		$merchantNameTrack =  BFCHelper::string_sanitize($merchantName);
		$merchantCategoryNameTrack =  BFCHelper::string_sanitize($resource->MerchantCategoryName);
	?>
	<div class="bfi-col-sm-6 bfi-item">
		<div class="bfi-row bfi-sameheight" >
			<div class="bfi-col-sm-3 bfi-img-container">
				<a href="<?php echo $resourceRoute ?>" onclick="event.stopPropagation();"  style='background: url("<?php echo $resourceImageUrl; ?>") center 25% / cover;' <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> class="eectrack" data-type="Sales Resource" data-id="<?php echo $resource->ResourceId?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><img src="<?php echo $resourceImageUrl; ?>" class="bfi-img-responsive" /></a> 
			</div>
			<div class="bfi-col-sm-9 bfi-details-container">
				<!-- merchant details -->
				<div class="bfi-row" >
					<div class="bfi-col-sm-12">
						<div class="bfi-item-title">
							<a href="<?php echo $resourceRoute ?>" onclick="event.stopPropagation();"  id="nameAnchor<?php echo $resource->ResourceId?>" <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> class="eectrack" data-type="Sales Resource" data-id="<?php echo $resource->ResourceId?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php echo  $resourceName?></a> 
							<span class="bfi-item-rating">
								<?php for($i = 0; $i < $rating; $i++) { ?>
									<i class="fa fa-star"></i>
								<?php } ?>	             
							</span>
							<?php if($isportal) { ?>
								- <a href="<?php echo $routeMerchant?>" onclick="event.stopPropagation();"  class="bfi-subitem-title eectrack" <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> data-type="Merchant" data-id="<?php echo $resource->MerchantId?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $merchantNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php echo $merchantName; ?></a>
								<span class="bfi-item-rating">
									<?php for($i = 0; $i < $ratingMrc; $i++) { ?>
										<i class="fa fa-star"></i>
									<?php } ?>	             
								</span>
							<?php } ?>
							
						</div>
						<div class="bfi-item-address">
							<?php if ($showResourceMap){?>
								<a href="javascript:void(0);" onclick="event.stopPropagation();bookingfor.bfiShowMarker(<?php echo $resource->ResourceId?>)"><span id="address<?php echo $resource->ResourceId?>"></span></a>
								<?php if(isset($resource->CenterDistance)) { ?>
									<span class="bfi-centerdistance" data-toggle="tooltip" title="<?php _e('This is the straight-line distance on the map. Actual travel distance may vary.', 'bfi') ?>">(<i class="fa fa-road" aria-hidden="true"></i> <?php echo BFCHelper::formatDistanceUnits($resource->CenterDistance)?> <?php _e('from centre', 'bfi') ?>)</span>
								<?php } ?>

								<div class="bfi-hide" id="markerInfo<?php echo $resource->ResourceId?>">
									<div class="bfi-map-info-container">
										<div class="bfi-map-info-container-img">
											<a href="<?php echo $itemRoute ?>" onclick="event.stopPropagation();" style='height:100%;background: url("<?php echo $resourceImageUrl; ?>") center 25%;background-size: cover;display: block;' <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> class="eectrack" data-type="<?php echo $resourceDataTypeTrack ?>" data-id="<?php echo $resourceDataIdTrack?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>">&nbsp;</a> 
										</div>
										<div class="bfi-map-info-container-content" >
												<div class="bfi-item-title">
													<a href="<?php echo $itemRoute ?>" onclick="event.stopPropagation();" <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> class="eectrack" data-type="<?php echo $resourceDataTypeTrack ?>" data-id="<?php echo $resourceDataIdTrack?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php echo  $resourceName ?></a> 
												</div>
												<span id="mapaddress<?php echo $resource->ItemId?>"></span>
												<div class="bfi-text-right"><a onclick="event.stopPropagation();" href="<?php echo $itemRoute ?>" <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> class="bfi-btn eectrack" data-type="<?php echo $resourceDataTypeTrack ?>" data-id="<?php echo $resourceDataIdTrack?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php _e('View', 'bfi') ?></a> </div>
										</div>
									</div>
								</div>

							<?php } ?>
						</div>

						<div class="bfi-mrcgroup" id="bfitags<?php echo $resource->ResourceId; ?>"></div>
						<span class="bfi-label-alternative2 bfi-hide" id="showcaseresource<?php echo $resource->ResourceId?>">
							<?php _e('Vetrina', 'bfi') ?> 
							<i class="fa fa-angle-double-up"></i>
						</span>
						<span class="bfi-label-alternative bfi-hide" id="topresource<?php echo $resource->ResourceId?>">
							<?php _e('Top', 'bfi') ?>
							<i class="fa fa-angle-up"></i>
						</span>
						<span class="bfi-label bfi-hide" id="newbuildingresource<?php echo $resource->ResourceId?>">
							<?php _e('New!', 'bfi') ?>
							<i class="fa fa-home"></i>
						</span>

					</div>
				</div>
				<div class="bfi-clearfix bfi-hr-separ"></div>
				<!-- resource details -->
				<div class="bfi-row" >
					<div class="bfi-col-sm-5">
						<?php if (isset($resource->Rooms) && $resource->Rooms>0):?>
						<div class="bfi-icon-rooms">
							<?php echo $resource->Rooms ?> <?php _e('Rooms', 'bfi') ?>
						</div>
						<?php endif; ?>
						<?php if (isset($resource->Rooms) && $resource->Rooms>0 && isset($resource->Area) && $resource->Area>0 ){?>
						- 
						<?php } ?>
						
						<?php if (isset($resource->Area) && $resource->Area>0):?>
						<div class="bfi-icon-area  ">
							<?php echo  $resource->Area ?> <?php _e('m&sup2;', 'bfi') ?>
						</div>
						<?php endif; ?>
					</div>
					<div class="bfi-col-sm-4 bfi-pad0-10 bfi-text-right">
						<?php if ($resource->Price != null && $resource->Price > 0 && isset($resource->IsReservedPrice) && $resource->IsReservedPrice!=1 ) :?>
							<span class="bfi-price bfi-price-total bfi_<?php echo $currencyclass ?>"> <?php echo BFCHelper::priceFormat($resource->Price,0, ',', '.')?></span>
						<?php else: ?>
							<?php _e('Contact Agent', 'bfi') ?>
						<?php endif; ?>
					
					</div>
					<div class="bfi-col-sm-3 bfi-text-right">
							<a href="<?php echo $resourceRoute ?>" onclick="event.stopPropagation();"  class="bfi-btn eectrack" <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> data-type="Sales Resource" data-id="<?php echo $resource->ResourceId?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php echo _e('Details' , 'bfi')?></a>
					</div>
				</div>
				<div class="bfi-clearfix"></div>
				<!-- end resource details -->
		</div>
				<div  class="bfi-ribbonnew bfi-hide" id="ribbonnew<?php echo $resource->ResourceId?>"><?php _e('New ad', 'bfi') ?></div>
	</div>
	</div>
		<?php 
			if ($showResourceMap) {
				$listResourceMaps[] = $val;
			}

		$listsId[]= $result->ResourceId;
		?>
	<?php } ?>
</div>
</div>
<script type="text/javascript">
<!--
var listToCheck = "<?php echo implode(",", $listsId) ?>";
var strAddressSimple = " ";
var strAddress = "[indirizzo] - [cap] - [comune] ([provincia])";
var listResourceMaps = <?php echo json_encode($listResourceMaps) ?>;

var onsellunitDaysToBeNew = '<?php echo BFCHelper::$onsellunitDaysToBeNew ?>';
var nowDate =  new Date();
var newFromDate =  new Date();
newFromDate.setDate(newFromDate.getDate() - onsellunitDaysToBeNew); 
var listAnonymous = ",<?php echo COM_BOOKINGFORCONNECTOR_ANONYMOUS_TYPE ?>,";

var loaded=false;
function getAjaxInformations(){
	if (!loaded)
	{
		loaded=true;
		//var query = "resourcesId=" + listToCheck + "&language=<?php echo $language ?>&task=GetResourcesOnSellByIds";

		jQuery.get(bookingfor.getActionUrl(null, null, "GetResourcesOnSellByIds", "resourcesId=" + listToCheck + "&language=<?php echo $language ?>"), function(data) {
				jQuery.each(data || [], function(key, val) {

				$html = '';

				var $indirizzo = "";
				var $cap = "";
				var $comune = "";
				var $provincia = "";
				
				if (val.IsAddressVisible)
				{
					$indirizzo = val.Address;
				}	
				$cap = val.ZipCode;
				$comune = val.CityName;
				$provincia = val.RegionName;

				addressData = strAddress.replace("[indirizzo]",$indirizzo);
				addressData = addressData.replace("[cap]",$cap);
				addressData = addressData.replace("[comune]",$comune);
				addressData = addressData.replace("[provincia]",$provincia);
				jQuery("#address"+val.ResourceId).html(addressData);
					
				if(val.AddedOn!= null){
					var parsedDate = new Date(parseInt(val.AddedOn.substr(6)));
					var jsDate = new Date(parsedDate); //Date object				
					var isNew = jsDate > newFromDate;
					if (isNew)
						{
							jQuery("#ribbonnew"+val.ResourceId).removeClass("bfi-hide");
						}
				}

				/* highlite seller*/
				if(val.IsHighlight){
							jQuery("#container"+val.ResourceId).addClass("com_bookingforconnector_highlight");
						}

				/*Top seller*/
				if (val.IsForeground)
					{
						jQuery("#topresource"+val.ResourceId).removeClass("bfi-hide");
//						jQuery("#borderimg"+val.ResourceId).addClass("bfi-hide");
					}

				/*Showcase seller*/
				if (val.IsShowcase)
					{
						jQuery("#topresource"+val.ResourceId).addClass("bfi-hide");
						jQuery("#showcaseresource"+val.ResourceId).removeClass("bfi-hide");
						jQuery("#lensimg"+val.ResourceId).removeClass("bfi-hide");
//						jQuery("#borderimg"+val.ResourceId).addClass("bfi-hide");
					}
				
				/*Top seller*/
				if(val.IsNewBuilding){
					jQuery("#newbuildingresource"+val.ResourceId).removeClass("bfi-hide");
				}


					jQuery(".container"+val.ResourceId).click(function(e) {
						var $target = jQuery(e.target);
						if ( $target.is("div")|| $target.is("p")) {
							document.location = jQuery( ".nameAnchor"+val.ResourceId ).attr("href");
						}
					});
			});	
		},'json');
	}
}



<?php if(count($results)>0){?>
	
jQuery(document).ready(function() {
	getAjaxInformations();
	jQuery('.bfi-sort-item').click(function() {
		var rel = jQuery(this).attr('rel');
		var vals = rel.split("|"); 
		jQuery('#bookingforsearchFilterForm .filterOrder').val(vals[0]);
		jQuery('#bookingforsearchFilterForm .filterOrderDirection').val(vals[1]);
		jQuery('#bookingforsearchFilterForm').submit();
	})
});
<?php } ?>


//-->
</script>
