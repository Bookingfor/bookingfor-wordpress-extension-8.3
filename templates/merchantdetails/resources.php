<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
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
$showdata = COM_BOOKINGFORCONNECTOR_SHOWDATA;
$posx = COM_BOOKINGFORCONNECTOR_GOOGLE_POSX;
$posy = COM_BOOKINGFORCONNECTOR_GOOGLE_POSY;
$startzoom = COM_BOOKINGFORCONNECTOR_GOOGLE_STARTZOOM;
$googlemapsapykey = COM_BOOKINGFORCONNECTOR_GOOGLE_GOOGLEMAPSKEY;
$fromsearchparam = "/?lna=".$listNameAnalytics;

$merchantdetails_page = get_post( bfi_get_page_id( 'merchantdetails' ) );
$url_merchant_page = get_permalink( $merchantdetails_page->ID );
$routeMerchant = $url_merchant_page . $merchant->MerchantId.'-'.BFI()->seoUrl($merchant->Name);

$accommodationdetails_page = get_post( bfi_get_page_id( 'accommodationdetails' ) );
$url_resource_page = get_permalink( $accommodationdetails_page->ID );
$uri = $url_resource_page;

//$page = isset($_GET['paged']) ? $_GET['paged'] : 1;
$page = bfi_get_current_page() ;

$pages = 0;
if($total>0){
	$pages = ceil($total / COM_BOOKINGFORCONNECTOR_ITEMPERPAGE);
}


$listsId = array();

	$merchantNameTrack =  BFCHelper::string_sanitize($merchantName);
	$merchantCategoryNameTrack =  BFCHelper::string_sanitize($merchant->MainCategoryName);

	$payload["@type"] = "Organization";
	$payload["@context"] = "http://schema.org";
	$payload["name"] = $merchantName;
	$payload["description"] = BFCHelper::getLanguage($merchant->Description, $language, null, array( 'striptags'=>'striptags', 'bbcode'=>'bbcode','ln2br'=>'ln2br'));
	$payload["url"] = ($isportal)? $routeMerchant.$fromsearchparam: $base_url; 
	if (!empty($merchant->LogoUrl)){
		$payload["logo"] = "https:".BFCHelper::getImageUrlResized('merchant',$merchant->LogoUrl, 'logobig');
	}
/*--------------- FINE IMPOSTAZIONI SEO----------------------*/

?>
<script type="application/ld+json">
<?php echo json_encode($payload); ?>
</script>
<div class="bfi-content">
	<div class="bfi-row">
		<div class="bfi-col-xs-9 ">
			<div class="bfi-title-name bfi-hideonextra"><h1><?php echo  $merchant->Name?></h1>
				<span class="bfi-item-rating">
<?php 
				$ratingModel = array(
					"ratingModel"=>$merchant
				);
				bfi_get_template("shared/stars_rating.php",$ratingModel);
?>
				</span>
			</div>
			<div class="bfi-search-title">
				<?php echo sprintf(__('%s available accommodations', 'bfi'), $total) ?>
			</div>
		</div>	
	<?php if(!empty(COM_BOOKINGFORCONNECTOR_GOOGLE_GOOGLEMAPSKEY)){ ?>
		<!-- <div class="bfi-col-xs-3 ">
			<div class="bfi-search-view-maps ">
			<span><?php _e('Map view', 'bfi') ?></span>
			</div>	
		</div>	 -->
	<?php } ?>
	</div>	
<?php if ($total > 0){ ?>

<div class="bfi-search-menu">
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
	<?php foreach ($resources as $currKey=>$resource){?>
	<?php 
		$resourceImageUrl = BFI()->plugin_url() . "/assets/images/defaults/default-s6.jpeg";

		$resourceName = BFCHelper::getLanguage($resource->Name, $language, null, array('ln2br'=>'ln2br', 'striptags'=>'striptags')); 
		$resourceDescription = BFCHelper::getLanguage($resource->Description, $language, null, array( 'ln2br'=>'ln2br', 'bbcode'=>'bbcode', 'striptags'=>'striptags')); 

		$resourceLat = $resource->XPos;
		$resourceLon = $resource->YPos;
		
		$resource->MinPaxes = $resource->MinCapacityPaxes;
		$resource->MaxPaxes= $resource->MaxCapacityPaxes;
		
		$showResourceMap = (($resourceLat != null) && ($resourceLon !=null));
		
		$currUriresource = $uri.$resource->ResourceId.'-'.BFI()->seoUrl($resourceName);
		$resourceRoute = $route = $currUriresource.$fromsearchparam;
		$routeRating = $routeRatingform = $currUriresource.'/'._x('rating', 'Page slug', 'bfi' );
//		$routeMerchant = "";
//		if($isportal){
//			$routeMerchant = $url_merchant_page . $resource->MerchantId .'-'.BFI()->seoUrl($resource->MerchantName)."?fromsearch=1";
//		}
		$hasSuperior = !empty($resource->RatingSubValue);
		$rating = (int)$resource->Rating;
		if ($rating>9 )
		{
			$rating = $rating/10;
			$hasSuperior = ($resource->Rating%10)>0;
		} 
		$hasSuperiorMrc = !empty($resource->MrcRatingSubValue);
		$ratingMrc = (int)$resource->MrcRating ;
		if ($ratingMrc>9 )
		{
			$ratingMrc = $ratingMrc/10;
			$hasSuperiorMrc = ($resource->MrcRating %10)>0;
		} 
		if(!empty($resource->ImageUrl)){
			$resourceImageUrl = BFCHelper::getImageUrlResized('resources',$resource->ImageUrl, 'medium');
		}
		$resourceNameTrack =  BFCHelper::string_sanitize($resourceName);
	?>
	<div class="bfi-col-sm-6 bfi-item">
		<div class="bfi-row bfi-sameheight" >
			<div class="bfi-col-sm-3 bfi-img-container">
				<a href="<?php echo $resourceRoute ?>" onclick="event.stopPropagation();"  style='background: url("<?php echo $resourceImageUrl; ?>") center 25% / cover;' <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> class="eectrack" data-type="Resource" data-id="<?php echo $resource->ResourceId?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>" data-list="<?php echo $analyticsListName; ?>"><img src="<?php echo $resourceImageUrl; ?>" class="bfi-img-responsive" /></a> 
			</div>
			<div class="bfi-col-sm-9 bfi-details-container">
				<!-- merchant details -->
				<div class="bfi-row" >
					<div class="bfi-col-sm-12">
						<div class="bfi-item-title">
							<a href="<?php echo $resourceRoute ?>" onclick="event.stopPropagation();"  id="nameAnchor<?php echo $resource->ResourceId?>"  <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> class="eectrack" data-type="Resource" data-id="<?php echo $resource->ResourceId?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>" data-list="<?php echo $analyticsListName; ?>"><?php echo  $resource->Name ?></a> 
							<span class="bfi-item-rating">
<?php 
				$ratingModel = array(
					"ratingModel"=>$resource
				);
				bfi_get_template("shared/stars_rating.php",$ratingModel);
?>
							</span>
							<?php if($isportal){ ?>
							- <a href="<?php echo $routeMerchant.$fromsearchparam?>" class="bfi-subitem-title eectrack" <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> data-type="Merchant" data-id="<?php echo $resource->MerchantId?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $merchantNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>" data-list="<?php echo $analyticsListName; ?>"><?php echo $merchantName; ?></a>
							<?php } ?>
							<span class="bfi-item-rating">
							</span>
						</div>
						<div class="bfi-item-address">
							<?php if ($showResourceMap){?>
							<a href="javascript:void(0);" onclick="event.stopPropagation();showMarker(<?php echo $resource->ResourceId?>)"><span id="address<?php echo $resource->ResourceId?>"></span></a>
							<?php if(isset($resource->CenterDistance)) { ?>
								<span class="bfi-centerdistance" data-toggle="tooltip" title="<?php _e('This is the straight-line distance on the map. Actual travel distance may vary.', 'bfi') ?>">(<i class="fa fa-road" aria-hidden="true"></i> <?php echo BFCHelper::formatDistanceUnits($resource->CenterDistance)?> <?php _e('from centre', 'bfi') ?>)</span>
							<?php } ?>
							<?php } ?>
						</div>
						<div class="bfi-mrcgroup" id="bfitags<?php echo $resource->ResourceId; ?>"></div>
						<div class="bfi-description bfi-shortentext"><?php echo $resourceDescription ?></div>
					</div>
				</div>
				<div class="bfi-clearfix bfi-hr-separ"></div>
				<!-- end merchant details -->
				<!-- resource details -->
				<div class="bfi-row" >
					<div class="bfi-col-sm-8">
						<?php if ($resource->MaxPaxes>0){?>
							<div class="bfi-icon-paxes">
								<i class="fa fa-user"></i> 
								<?php if ($resource->MaxPaxes==2){?>
								<i class="fa fa-user"></i> 
								<?php }?>
								<?php if ($resource->MaxPaxes>2){?>
									<?php echo ($resource->MinPaxes != $resource->MaxPaxes)? $resource->MinPaxes . "-" : "" ?><?php echo  $resource->MaxPaxes ?>
								<?php }?>
							</div>
						<?php } ?>
					
					</div>
					<div class="bfi-col-sm-4 bfi-text-right">
						<a href="<?php echo $resourceRoute ?>" onclick="event.stopPropagation();"  class="bfi-btn eectrack" <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> data-type="Resource" data-id="<?php echo $resource->ResourceId?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>" data-list="<?php echo $analyticsListName; ?>"><?php echo _e('Details' , 'bfi')?></a>
					</div>
				</div>
				<!-- end resource details -->

				<div class="bfi-clearfix"></div>
				<!-- end price details -->
			</div>
		</div>
	</div>
	<?php 
	$listsId[]= $resource->ResourceId;
	?>
<?php } ?>
</div>
<?php if ($pages > 1 ) { ?>
	<div class="pagination">
<?php   

if( get_option('permalink_structure') ) {
	$format = 'page/%#%/';
} else {
	$format = '?paged=%#%';
}

  
//$paginationDetails = array();
//$paginationDetails['start'] = $page * COM_BOOKINGFORCONNECTOR_ITEMPERPAGE;

  $pagination_args = array(
    'base'            => $routeMerchant .'/' . _x( 'resources', 'Page slug', 'bfi' ) . '/%_%',
    'format'          => $format,
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
    'add_fragment'    => ''
  );

  $paginate_links = paginate_links($pagination_args);
    if ($paginate_links) {
      echo "<nav class='bfi-pagination'>";
//      echo "<span class='page-numbers page-num'>Page " . $page . " of " . $numpages . "</span> ";
      echo "<span class='page-numbers page-num'>".__('Page', 'bfi')." </span> ";
      print $paginate_links;
      echo "</nav>";
    }	 ?>
	</div> <!-- endpagination -->
<?php } ?>

<script type="text/javascript">
<!--
var listToCheck = "<?php echo implode(",", $listsId) ?>";

var imgPathMG = "<?php echo BFCHelper::getImageUrlResized('tag','[img]', 'merchant_merchantgroup') ?>";
var imgPathMGError = "<?php echo BFCHelper::getImageUrl('tag','[img]', 'merchant_merchantgroup') ?>";
var strAddress = "[indirizzo] - [cap] - [comune] ([provincia])";

var mg = [];

var loaded=false;

function getlist(){
	var query = "resourcesId=" + listToCheck + "&language=<?php echo $language ?>";
	if(listToCheck!='')
	

	jQuery.get(bookingfor.getActionUrl(null, null, "GetResourcesByIds", "resourcesId=" + listToCheck + "&language=<?php echo $language ?>"), function(data) {

				if(typeof callfilterloading === 'function'){
					callfilterloading();
					callfilterloading = null;
				}
			jQuery.each(data || [], function(key, val) {
				$html = '';
	
				var $indirizzo = "";
				var $cap = "";
				var $comune = "";
				var $provincia = "";
				
				$indirizzo = val.Resource.AddressData;
				$cap = val.Resource.ZipCode;
				$comune = val.Resource.CityName;
				$provincia = val.Resource.RegionName;

				addressData = strAddress.replace("[indirizzo]",$indirizzo);
				addressData = addressData.replace("[cap]",$cap);
				addressData = addressData.replace("[comune]",$comune);
				addressData = addressData.replace("[provincia]",$provincia);
				jQuery("#address"+val.Resource.ResourceId).html(addressData);

				if (val.Resource.TagsIdList!= null && val.Resource.TagsIdList != '')
				{
					var mglist = val.Resource.TagsIdList.split(',');
					$htmlmg = '';
					jQuery.each(mglist, function(key, mgid) {
						if(typeof bookingfor.tagLoaded[mgid] !== 'undefined' ){
							$htmlmg += bookingfor.tagLoaded[mgid];
						}
					});
					jQuery("#bfitags"+val.Resource.ResourceId).html($htmlmg);
				}			

				jQuery(".container"+val.Resource.ResourceId).click(function(e) {
					var $target = jQuery(e.target);
					if ( $target.is("div")|| $target.is("p")) {
						document.location = jQuery( ".nameAnchor"+val.Resource.ResourceId ).attr("href");
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
		},'json');
}

	
jQuery(document).ready(function() {
//	getAjaxInformations();
	bookingfor.bfiGetAllTags(getlist);

	jQuery('.bfi-maps-static,.bfi-search-view-maps').click(function() {
		jQuery( "#bfi-maps-popup" ).dialog({
			open: function( event, ui ) {
				openGoogleMapSearch();
			},
			height: 500,
			width: 800,
			dialogClass: 'bfi-dialog bfi-dialog-map'
		});
	});
});


//-->
</script>

<?php } else { ?>
<div class="bfi-noresults">
	<?php _e('No results available', 'bfi') ?>
</div>
<?php } ?>
	<div class="bfi-clearfix"></div>