<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$totalResult = count($merchants);
$language = $GLOBALS['bfi_lang'];
$languageForm ='';
$base_url = get_site_url();
if(defined('ICL_LANGUAGE_CODE') &&  class_exists('SitePress')){
		global $sitepress;
		if($sitepress->get_current_language() != $sitepress->get_default_language()){
			$base_url = "/" .ICL_LANGUAGE_CODE;
		}
}
$isportal = COM_BOOKINGFORCONNECTOR_ISPORTAL;
$showdata = COM_BOOKINGFORCONNECTOR_SHOWDATA;
$posx = COM_BOOKINGFORCONNECTOR_GOOGLE_POSX;
$posy = COM_BOOKINGFORCONNECTOR_GOOGLE_POSY;
$startzoom = COM_BOOKINGFORCONNECTOR_GOOGLE_STARTZOOM;
$googlemapsapykey = COM_BOOKINGFORCONNECTOR_GOOGLE_GOOGLEMAPSKEY;

$merchantImageUrl = BFI()->plugin_url() . "/assets/images/defaults/default-s6.jpeg";

$merchantdetails_page = get_post( bfi_get_page_id( 'merchantdetails' ) );
$url_merchant_page = get_permalink( $merchantdetails_page->ID );

$startswith = !empty($currParam['startswith'])?$currParam['startswith']:'';

$currURL = esc_url( get_permalink() ); 
$formAction = $currURL;
//$page = isset($_GET['paged']) ? $_GET['paged'] : 1;
//$page = (get_query_var('page')) ? get_query_var('page') : 1;
$page = bfi_get_current_page() ;

$pages = 0;
if($total>0){
	$pages = ceil($total / COM_BOOKINGFORCONNECTOR_ITEMPERPAGE);

}
$currSorting= $filter_order . "|" . $filter_order_Dir;
$listNameAnalytics =4;
$listName = BFCHelper::$listNameAnalytics[$listNameAnalytics];// "Resources Search List";
$fromsearchparam = "/?lna=".$listNameAnalytics;
	$rating_text = array('merchants_reviews_text_value_0' => __('Very poor', 'bfi'),
						'merchants_reviews_text_value_1' => __('Poor', 'bfi'),   
						'merchants_reviews_text_value_2' => __('Disappointing', 'bfi'),
						'merchants_reviews_text_value_3' => __('Fair', 'bfi'),
						'merchants_reviews_text_value_4' => __('Okay', 'bfi'),
						'merchants_reviews_text_value_5' => __('Pleasant', 'bfi'),  
						'merchants_reviews_text_value_6' => __('Good', 'bfi'),
						'merchants_reviews_text_value_7' => __('Very good', 'bfi'),  
						'merchants_reviews_text_value_8' => __('Fabulous', 'bfi'), 
						'merchants_reviews_text_value_9' => __('Exceptional', 'bfi'),  
						'merchants_reviews_text_value_10' => __('Exceptional', 'bfi'),                                 
					);

?>
<?php if (count($merchants)>0) { ?>
<div class="bfi-content">
<?php if(!empty(COM_BOOKINGFORCONNECTOR_GOOGLE_GOOGLEMAPSKEY)){ ?>
	<div class="bfi-text-right ">
		<div class="bfi-search-view-maps ">
		<span><?php _e('Map view', 'bfi') ?></span>
		</div>	
	</div>
<?php } ?>

<div class="bfi-search-menu">
	<form action="<?php echo $formAction; ?>" method="post" name="bookingforsearchForm" id="bookingforsearchFilterForm">
			<input type="hidden" class="filterOrder" name="filter_order" value="<?php echo $filter_order ?>" />
			<input type="hidden" class="filterOrderDirection" name="filter_order_Dir" value="<?php echo $filter_order_Dir ?>" />
			<input type="hidden" name="searchid" value="<?php //echo   $searchid ?>" />
			<input type="hidden" name="startswith" id="startswith" value="<?php echo $startswith ?>" />
			<input type="hidden" name="limitstart" value="0" />
	</form>
	<div class="bfi-results-sort">
		<span class="bfi-sort-item-order"><?php echo _e('Order by' , 'bfi')?>:</span>
		<span class="bfi-sort-item <?php echo $currSorting=="Name|asc" ? "bfi-sort-item-active": "" ; ?>" rel="Name|asc" ><?php _e('A-Z', 'bfi') ?></span>
		<span class="bfi-sort-item <?php echo $currSorting=="Name|desc" ? "bfi-sort-item-active": "" ; ?>" rel="Name|desc" ><?php _e('Z-A', 'bfi') ?></span>
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
<?php 
	$listResourceIds = array();
	$listResourceMaps = array();
?>  
	<?php foreach ($merchants as $currKey => $merchant): ?>
		<?php 

			$routeMerchant = $url_merchant_page . $merchant->MerchantId.'-'.BFI()->seoUrl($merchant->Name);
			$routeRating = $routeMerchant .'/'._x('reviews', 'Page slug', 'bfi' );
			$routeInfoRequest = $routeMerchant .'/'._x('contactspopup', 'Page slug', 'bfi' );
			
			$counter = 0;
			$merchantLat = $merchant->XPos;
			$merchantLon = $merchant->YPos;
			$showMerchantMap = (($merchantLat != null) && ($merchantLon !=null));
			$merchantLogo = BFI()->plugin_url() . "/assets/images/defaults/default-s1.jpeg";
			$merchantImageUrl = BFI()->plugin_url() . "/assets/images/defaults/default-s6.jpeg";
			
			if(!empty($merchant->LogoUrl)){
				$merchantLogo = BFCHelper::getImageUrlResized('merchant',$merchant->LogoUrl, 'logomedium');
				$merchantLogoError = BFCHelper::getImageUrl('merchant',$merchant->LogoUrl, 'logomedium');
			}
			if(!empty($merchant->DefaultImg)){
				$merchantImageUrl = BFCHelper::getImageUrlResized('merchant',$merchant->DefaultImg, 'medium');
			}
			
//			$merchantSiteUrl = '';
//			if ($merchant->SiteUrl != '') {
//				$merchantSiteUrl =$merchant->SiteUrl;
//				if (strpos('http://', $merchantSiteUrl) == false) {
//					$merchantSiteUrl = 'http://' . $merchantSiteUrl;
//				}
//				$merchantSiteUrlstripped = str_replace('http://', "", $merchantSiteUrl);
//				if (strpos($merchantSiteUrlstripped,'?') !== false) {
//					$tmpurl = explode("?",$merchantSiteUrlstripped);
//					$merchantSiteUrlstripped = $tmpurl[0];
//				}
//			}
			$merchantName = BFCHelper::getLanguage($merchant->Name, $language, null, array('ln2br'=>'ln2br', 'striptags'=>'striptags')); 
			$merchantDescription = BFCHelper::getLanguage($merchant->Description, $language, null, array('striptags'=>'striptags', 'bbcode'=>'bbcode','ln2br'=>'ln2br')); 
			$routeMerchant .= $fromsearchparam;
			$merchantNameTrack =  BFCHelper::string_sanitize($merchantName);
			$merchantCategoryNameTrack = ""; // BFCHelper::string_sanitize($merchant->MainCategoryName);

			if(isset($merchant->XPos) && !empty($merchant->XPos)  ){
				$val= new StdClass;
				$val->Id = $merchant->MerchantId ;
				$val->Lat = $merchant->XPos;
				$val->Long = $merchant->YPos;
				$listResourceMaps[] = $val;
			}

			$favoriteModel = array(
				"ItemId"=>$merchant->MerchantId,
				"ItemName"=>BFCHelper::string_sanitize($merchant->Name),
				"ItemType"=>0,
				"ItemURL"=>$routeMerchant,
				"WrapToContainer"=>1,
				);

		?>


	<div class="bfi-col-sm-6 bfi-item">
		<div class="bfi-row bfi-sameheight" >
			<div class="bfi-col-sm-3 bfi-img-container">
				<a href="<?php echo $routeMerchant ?>" style='background: url("<?php echo $merchantImageUrl; ?>") center 25% / cover;' <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> class="eectrack" data-type="Merchant" data-id="<?php echo $merchant->MerchantId?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $merchantNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>">
				<?php bfi_get_template("shared/favorite_icon.php",$favoriteModel); ?>
				<img src="<?php echo $merchantImageUrl; ?>" class="bfi-img-responsive" />
				</a> 
			</div>
			<div class="bfi-col-sm-9 bfi-details-container">
				<!-- merchant details -->
				<div class="bfi-row" >
					<div class="bfi-col-sm-10">
						<div class="bfi-item-title">
							<a href="<?php echo $routeMerchant ?>" onclick="event.stopPropagation();"  id="nameAnchor<?php echo $merchant->MerchantId?>" <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> class="eectrack" data-type="Merchant" data-id="<?php echo $merchant->MerchantId?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $merchantNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php echo  $merchantName ?></a> 
							<span class="bfi-item-rating">
							<?php 
							$ratingModel = array(
								"ratingModel"=>$merchant
							);
							bfi_get_template("shared/stars_rating.php",$ratingModel);
							?>
							</span>
						</div>
						<div class="bfi-item-address">
							<?php if ($showMerchantMap){?>
                            <a href="javascript:void(0);" onclick="event.stopPropagation();bookingfor.bfiShowMarker(<?php echo $merchant->MerchantId?>)">
                                <?php }?>
                                <span id="address<?php echo $merchant->MerchantId?>"></span><?php if ($showMerchantMap){?>
                            </a>
							<div class="bfi-hide" id="markerInfo<?php echo $merchant->MerchantId?>">
                                <a href="<?php echo $routeMerchant ?>" onclick="event.stopPropagation();"  style='background: url("<?php echo $merchantImageUrl; ?>") center 25%;background-size: cover;' <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> class="eectrack" data-type="Merchant" data-id="<?php echo $merchant->MerchantId?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $merchantNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>">
                                    <img src="<?php echo $merchantImageUrl; ?>" class="bfi-img-responsive" />
                                </a> 
									<div style="padding:5px;">
									    <div class="bfi-item-title">
										    <a href="<?php echo $routeMerchant ?>" onclick="event.stopPropagation();"  <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> class="eectrack" data-type="Merchant" data-id="<?php echo $merchant->MerchantId?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $merchantNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php echo  $merchantName ?></a> 
										    <span class="bfi-item-rating">
											<?php 
											$ratingModel = array(
												"ratingModel"=>$merchant
											);
											bfi_get_template("shared/stars_rating.php",$ratingModel);
											?>
										    </span>
									    </div>
									    <span id="mapaddress<?php echo $merchant->MerchantId?>"></span>
                                    </div>

								<?php if(isset($resource->CenterDistance)) { ?>
									<span class="bfi-centerdistance" id="addressdist<?php echo $merchant->MerchantId?>" style="display:none;" data-toggle="tooltip" title="<?php _e('This is the straight-line distance on the map. Actual travel distance may vary.', 'bfi') ?>">(<i class="fa fa-road" aria-hidden="true"></i> <?php echo BFCHelper::formatDistanceUnits($merchant->CenterDistance)?> <?php _e('from centre', 'bfi') ?>)</span>
								<?php } ?>
							</div>
							<?php } ?>
						</div>
						<div class="bfi-mrcgroup" id="bfitags<?php echo $merchant->MerchantId; ?>"></div>
						<div class="bfi-description bfi-shortentextlong" id="bfidescription<?php echo $merchant->MerchantId; ?>"><?php echo $merchantDescription ?></div>
					</div>
					<div class="bfi-col-sm-2 bfi-text-right">
						<?php if ($isportal && ($merchant->RatingsContext ==1 || $merchant->RatingsContext ==3)){?>
								<div class="bfi-avg">
								<?php if ($merchant->MrcAVGCount>0){
									$totalInt = BFCHelper::convertTotal(number_format((float)$merchant->MrcAVG, 1, '.', ''));

									?>
			<div class="bfi-widget-reviews-avg-container">
									<a class="bfi-avg-value eectrack" onclick="event.stopPropagation();"  href="<?php echo $routeRating ?>" <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> data-type="Merchant" data-id="<?php echo $merchant->MerchantId?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $merchantNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php echo $rating_text['merchants_reviews_text_value_'.$totalInt] ?></a>
									<a class="bfi-avg-count eectrack" onclick="event.stopPropagation();"  href="<?php echo $routeRating ?>" <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> data-type="Merchant" data-id="<?php echo $merchant->MerchantId?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $merchantNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php echo sprintf(__('%s reviews', 'bfi') ,$merchant->MrcAVGCount) ?></a>
			</div>
			<div class="bfi-widget-reviews-avg-value"><?php echo  number_format((float)$merchant->MrcAVG, 1, '.', '') ?></div>
								<?php } ?>
								</div>
						<?php } ?>
					</div>
				</div>
				<div class="bfi-clearfix bfi-hr-separ"></div>
				<!-- end merchant details -->
					<div class=" bfi-text-right">
							<a href="<?php echo $routeMerchant ?>" onclick="event.stopPropagation();"  class="bfi-btn eectrack" <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> data-type="Merchant" data-id="<?php echo $merchant->MerchantId?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $merchantNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php echo _e('Details' , 'bfi')?></a>
					</div>
				<div class="bfi-clearfix"></div>
			</div>
		</div>
	</div>
		<?php $listsId[]= $merchant->MerchantId; ?>
	<?php endforeach; ?>
</div>
</div>
<?php
  
  $url = $currURL ;
//$fragment = "&filter_order=" . $filter_order. "&filter_order_Dir=" . $filter_order_Dir;
	if( get_option('permalink_structure') ) {
		$format = 'page/%#%/';
	} else {
		$format = '?paged=%#%';
	}
  $pagination_args = array(
    'base'            => $url. '%_%',
    'format'          => $format, //'?page=%#%',
    'total'           => $pages,
    'current'         => $page,
    'show_all'        => false,
    'end_size'        => 5,
    'mid_size'        => 2,
    'prev_next'       => true,
    'prev_text'       => __('&laquo;'),
    'next_text'       => __('&raquo;'),
    'type'            => 'plain',
    'add_args'        => false,
    'add_fragment'    => '' //$fragment
  );

  $paginate_links = paginate_links($pagination_args);
    if ($paginate_links) {
      echo "<nav class='bfi-pagination'>";
//      echo "<span class='page-numbers page-num'>Page " . $page . " of " . $numpages . "</span> ";
      echo "<span class='page-numbers page-num'>".__('Page', 'bfi')." </span> ";
      print $paginate_links;
      echo "</nav>";
    }
 ?>
<script type="text/javascript">
<!--

jQuery(document).ready(function() {
	jQuery('.bfi-sort-item').click(function() {
	  var rel = jQuery(this).attr('rel');
	  var vals = rel.split("|"); 
	  jQuery('#bookingforsearchFilterForm .filterOrder').val(vals[0]);
	  jQuery('#bookingforsearchFilterForm .filterOrderDirection').val(vals[1]);
	  jQuery('#bookingforsearchFilterForm').submit();
	});
});

var listToCheck = "<?php echo implode(",", $listsId) ?>";
var strAddress = "[indirizzo] - [cap] - [comune] ([provincia])";
var imgPathMG = "<?php echo BFCHelper::getImageUrlResized('tag','[img]', 'merchant_merchantgroup') ?>";
var imgPathMGError = "<?php echo BFCHelper::getImageUrl('tag','[img]', 'merchant_merchantgroup') ?>";

var mg = [];

var loaded=false;

function getAjaxInformations(){
	if (!loaded)
	{
		loaded=true;
		var queryMG = "task=getMerchantGroups";
		jQuery.get(bookingfor.getActionUrl(null, null, "getMerchantGroups", ""), function(data) {
				if(data!=null){
					jQuery.each(JSON.parse(data) || [], function(key, val) {
						if (val.ImageUrl!= null && val.ImageUrl!= '') {
							var $imageurl = imgPathMG.replace("[img]", val.ImageUrl );		
							var $imageurlError = imgPathMGError.replace("[img]", val.ImageUrl );		
							/*--------getName----*/
							var $name = val.Name;
							/*--------getName----*/
							mg[val.TagId] = '<img src="' + $imageurl + '" onerror="this.onerror=null;this.src=\'' + $imageurlError + '\';" alt="' + $name + '" data-toggle="tooltip" title="' + $name + '" />';
						} else {
							if (val.IconSrc != null && val.IconSrc != '') {
								if (val.IconType != null && val.IconType != '')
								{
									var fontIcons = val.IconType .split(";");
									if (fontIcons[0] == 'fontawesome5')
									{
										mg[val.TagId] = '<i class="' + val.IconSrc + '" data-toggle="tooltip" title="' + val.Name + '"> </i> ';
									}
									if (fontIcons[0] == 'fontawesome4')
									{
										mg[val.TagId] = '<i class="fa ' + val.IconSrc + '" data-toggle="tooltip" title="' + val.Name + '"> </i> ';
									}

								}else{
									mg[val.TagId] = '<i class="fa ' + val.IconSrc + '" data-toggle="tooltip" title="' + val.Name + '"> </i> ';
								}
							}
						}
					});	
				}
				getlist();
		},'json');
	}
}


function getlist(){
	//var query = "merchantsId=" + listToCheck + "&language=<?php echo $language ?>";
	if(listToCheck!='')
	
	jQuery.get(bookingfor.getActionUrl(null, null, "GetMerchantsByIds", "merchantsId=" + listToCheck + "&language=<?php echo $language ?>"), function(data) {
			var eecitems = [];

			if(typeof callfilterloading === 'function'){
				callfilterloading();
				callfilterloading = null;
			}
			jQuery.each(data || [], function(key, val) {
				$html = '';

				jQuery(".eectrack[data-id=" + val.MerchantId + "]").attr("data-category", val.MainCategoryName);
				eecitems.push({
					id: "" + val.MerchantId + " - Merchant",
					name: val.Name,
					category: val.MainCategoryName,
					brand: val.Name,
					position: key
				});

				if (val.AddressData != '') {
					var merchAddress = "";
					var $indirizzo = "";
					var $cap = "";
					var $comune = "";
					var $provincia = "";
					
					$indirizzo = val.AddressData.Address;
					$cap = val.AddressData.ZipCode;
					$comune = val.AddressData.CityName;
					$provincia = val.AddressData.RegionName;

					merchAddress = strAddress.replace("[indirizzo]",$indirizzo);
					merchAddress = merchAddress.replace("[cap]",$cap);
					merchAddress = merchAddress.replace("[comune]",$comune);
					merchAddress = merchAddress.replace("[provincia]",$provincia);
					jQuery("#address"+val.MerchantId).append(merchAddress);
					jQuery("#mapaddress"+val.MerchantId).append(merchAddress);
					jQuery("#addressdist"+val.MerchantId).show();
					
				}
				if (val.TagsIdList!= null && val.TagsIdList != '')
				{
					var mglist = val.TagsIdList.split(',');
					$htmlmg = '';
					jQuery.each(mglist, function(key, mgid) {
						if(typeof mg[mgid] !== 'undefined' ){
							$htmlmg += mg[mgid];
						}
					});
					jQuery("#bfitags"+val.MerchantId).html($htmlmg);
				}

				if (val.Description!= null && val.Description != ''){
					$html += bookingfor.nl2br(jQuery("<p>" + bookingfor.stripbbcode(val.Description) + "</p>").text());
					jQuery("#bfidescription"+val.MerchantId).data('jquery.shorten', false);
					jQuery("#bfidescription"+val.MerchantId).html($html);
					bookingfor.shortenText(jQuery("#bfidescription"+val.MerchantId),250);
				}

				jQuery("#container"+val.MerchantId).click(function(e) {
					var $target = jQuery(e.target);
					if ( $target.is("div")|| $target.is("p")) {
						document.location = jQuery( "#nameAnchor"+val.MerchantId ).attr("href");
					}
				});

		});	
		if (typeof bfiTooltip  !== "function") {
			jQuery.widget.bridge('bfiTooltip', jQuery.ui.tooltip );
		}
		jQuery('[data-toggle="tooltip"]').bfiTooltip({
			position : { my: 'center bottom', at: 'center top-10' },
			tooltipClass: 'bfi-tooltip bfi-tooltip-top '
		}); 
		<?php if(COM_BOOKINGFORCONNECTOR_GAENABLED == 1 && !empty(COM_BOOKINGFORCONNECTOR_GAACCOUNT) && COM_BOOKINGFORCONNECTOR_EECENABLED == 1){ ?>
			if (typeof callAnalyticsEEc !== "undefined") {	
				callAnalyticsEEc("addImpression", eecitems, "list");
			}
		<?php } ?>
	},'json');
}


var listResourceMaps = <?php echo json_encode($listResourceMaps) ?>;

		var mapSearch;
		var myLatlngsearch;
		var oms;
		var markersLoading = false;
		var infowindow = null;
		var markersLoaded = false;
		var bfiCurrMarkerId = 0;
		var bfiLastZIndexMarker = 1000;
		var Leaflet;
    if (bfi_variables.bfiMapsFree)
		{
			Leaflet = L.noConflict();
		}


jQuery(document).ready(function() {
	getAjaxInformations();
	if(jQuery( "#bfi-maps-popup").length == 0) {
		jQuery("body").append("<div id='bfi-maps-popup'></div>");
	}
	jQuery('.bfi-maps-static,.bfi-search-view-maps').click(function() {
		jQuery( "#bfi-maps-popup" ).dialog({
			open: function( event, ui ) {
                bookingfor.bfiOpenGoogleMapSearch();
			},
			height: 500,
			width: 800,
            dialogClass: 'bfi-dialog bfi-dialog-map',
            resize: function (event, ui) {
                if (bfi_variables.bfiMapsFree) {
                    mapSearch.invalidateSize();
                }
            }

		});
	});

});

//-->
</script>
<?php } ?>
