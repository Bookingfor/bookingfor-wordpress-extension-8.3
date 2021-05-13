<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$isportal = COM_BOOKINGFORCONNECTOR_ISPORTAL;
$showdata = COM_BOOKINGFORCONNECTOR_SHOWDATA;
$showmap = !empty(COM_BOOKINGFORCONNECTOR_ENABLEGOOGLEMAPSAPI);
if ($showmap && COM_BOOKINGFORCONNECTOR_USE_OPENSTREETMAP) {
	wp_enqueue_script('bfileaflet');
	wp_enqueue_script('bfileafletcontrolcustom');
}

$fromsearchparam = "/?fromsearch=1&lna=".$listNameAnalytics;

$language = $GLOBALS['bfi_lang'];
$languageForm ='';
if(defined('ICL_LANGUAGE_CODE') &&  class_exists('SitePress')){
		global $sitepress;
		if($sitepress->get_current_language() != $sitepress->get_default_language()){
			$languageForm = "/" .ICL_LANGUAGE_CODE;
		}
}
$showSearchTitle = true;

$onlystay =  true;
$currParam = BFCHelper::getSearchEventParamsSession();

$checkin = BFCHelper::getStayParam('checkin', new DateTime('UTC'));
$checkout = BFCHelper::getStayParam('checkout', new DateTime('UTC'));
if (!empty($currParam)){
		$checkin = isset($currParam['checkin']) ? $currParam['checkin'] : $checkin ;
		$checkout = isset($currParam['checkout']) ? $currParam['checkout'] : $checkout ;
}    

$checkinstr = $checkin->format("d") . " " . date_i18n('F',$checkin->getTimestamp()) . ' ' . $checkin->format("Y") ;
$checkoutstr = $checkout->format("d") . " " . date_i18n('F',$checkout->getTimestamp()) . ' ' . $checkout->format("Y") ;
$totalResult = $total;
$counter = 0;

$listsId = array();
$allTagIds = array();
$listResourceMaps = array();
$base_url = get_site_url();

$resourceImageUrl = BFI()->plugin_url() . "/assets/images/defaults/default-s6.jpeg";

$searchAvailability_page = get_post( bfi_get_page_id( 'searchevents' ) );
$formAction = get_permalink( $searchAvailability_page->ID );

if(!empty($page)){
	$formAction = str_replace('/page/'.$page."/","/",$formAction);
}

$accommodationdetails_page = get_post( bfi_get_page_id( 'eventdetails' ) );
$url_resource_page = get_permalink( $accommodationdetails_page->ID );
$uri = $url_resource_page;

$currFilterOrder = "";
$currFilterOrderDirection = "";
if (!empty($currSorting) &&strpos($currSorting, '|') !== false) {
	$acurrSorting = explode('|',$currSorting);
	$currFilterOrder = $acurrSorting[0];
	$currFilterOrderDirection = $acurrSorting[1];
}
$resources = $items;

//if (empty($currFilterOrder)) {
//	shuffle($resources);
//}

?>
<div class="bfi-content">
	<?php if(empty($hidetop)) { ?>
	<div class="bfi-row">
		<div class="bfi-col-xs-9 ">
			<?php if($showSearchTitle){ ?>
			<div class="bfi-search-title">
				<?php echo sprintf( __('Found %s results', 'bfi'),$totalResult ) ?>
			</div>
			<div class="bfi-search-title-sub">
				<?php echo sprintf( __('From %s to %s', 'bfi'),$checkinstr,$checkoutstr ) ?>
			</div>
			<?php } ?>
		</div>	
	<?php if($showmap){ ?>
		<div class="bfi-col-xs-3 ">
			<div class="bfi-search-view-maps ">
			<span><?php _e('Map view', 'bfi') ?></span>
			</div>	
		</div>	
	<?php } ?>
	</div>	
	<?php } ?>
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
<?php 
foreach ($resources as $currKey => $res){

	$resourceName = BFCHelper::getLanguage($res->Name, $language, null, array('ln2br'=>'ln2br', 'bbcode'=>'bbcode', 'striptags'=>'striptags')); 
	$resourceDescription = BFCHelper::getLanguage($res->Description, $language, null, array('ln2br'=>'ln2br', 'bbcode'=>'bbcode', 'striptags'=>'striptags')); 


	$resourceDataTypeTrack = "Event";

	$showResourceMap = (!empty($res->Address->XPos) && !empty($res->Address->YPos)) && !empty(COM_BOOKINGFORCONNECTOR_ENABLEGOOGLEMAPSAPI);
	$startDate = BFCHelper::parseStringDateTime($res->StartDate);
	$endDate = BFCHelper::parseStringDateTime($res->EndDate);
	$val= new StdClass;
	if ($showResourceMap) {
		$val->Id = $res->EventId;
		$val->Lat = $res->Address->XPos;
		$val->Long = $res->Address->YPos;
		$val->infoHtml = "<div class='bfi-map-label bfi-googlemap blulighttheme bfi-map-events'><div class='bfi-map-label-content bfi-map-event'>" . date_i18n('D',$startDate->getTimestamp()) . "<div class='bfi-map-event-day'>" . $startDate->format("d") ."</div>". date_i18n('M',$startDate->getTimestamp()) ."</div><div class='bfi-map-label-arrow'></div></div>";
	}
	$itemRoute = "";
	$imageUrl = COM_BOOKINGFORCONNECTOR_DEFAULTIMAGE;

	$itemRoute = $url_resource_page.$res->EventId.'-'.BFI()->seoUrl($resourceName).$fromsearchparam;

	if(!empty($res->DefaultImg)){
		$imageUrl = BFCHelper::getImageUrlResized('events',$res->DefaultImg, 'medium');
	}

	$resourceRoute = $itemRoute;
	
	$btnText = __('Details','bfi');
	$btnClass = "bfi-alternative";

	$resourceNameTrack =  BFCHelper::string_sanitize($resourceName);
	$merchantCategoryNameTrack =  BFCHelper::string_sanitize($resourceName);
	$merchantNameTrack =  BFCHelper::string_sanitize($resourceName);

	$totalInt = 0;

//	$res->Dates = json_decode($res->DatesString);
//	//ordinamento per data
//	usort($res->Dates, function($a, $b) {
//		$currStartDate = BFCHelper::parseStringDateTime($a->StartDate);
//		$currEndDate = BFCHelper::parseStringDateTime($b->EndDate);
//		return $currStartDate->format('U') - $currEndDate->format('U');
//	});
//	$dateCount = Count($res->Dates);
	$dateCount = $res->DatesCount;
	$indirizzo = "";
	$cap = "";
	$comune = "";
	$provincia = "";

	if(!empty( $res->Address )){
		$indirizzo = $res->Address->Address;
		$cap = $res->Address->ZipCode;
		$comune = $res->Address->CityName;
		$provincia = $res->Address->RegionName;
		$stato = !empty($res->Address->StateName)?$res->Address->StateName:"";
	}
	
	?>
	<div class="bfi-col-sm-6 bfi-item">
		<div class="bfi-row bfi-sameheight" >
			<div class="bfi-col-sm-3 bfi-img-container">
				<a href="<?php echo $itemRoute ?>" style='background: url("<?php echo $imageUrl; ?>") center 25%;background-size: cover;' <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> class="eectrack" data-type="<?php echo $resourceDataTypeTrack ?>" data-id="<?php echo $res->EventId?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>">
<?php 
				$favoriteModel = array(
					"ItemId"=>$res->EventId,
					"ItemName"=>BFCHelper::string_sanitize($resourceName),
					"ItemType"=>2,
					"StartDate"=>$startDate->format("YmdHis") ,
					"EndDate"=>$endDate->format("YmdHis"),
					"ItemURL"=>$itemRoute,
					"WrapToContainer"=>1,
					);
				bfi_get_template("shared/favorite_icon.php",$favoriteModel);	
?>
				<img src="<?php echo $imageUrl; ?>" class="bfi-img-responsive" />
				</a> 
				<div class="bfi-item-date">
					<i class="fa fa-calendar" aria-hidden="true"></i> 
					<?php echo date_i18n('D',$startDate->getTimestamp()) . " " . $startDate->format("d") . " " . date_i18n('M',$startDate->getTimestamp()) . " " . $startDate->format("Y") ?>
                    <?php 
					if($startDate != $endDate && $dateCount > 1) { 
						echo " + " . ($dateCount-1) . " " . __('Dates', 'bfi');
                    } 
					?>
				</div>
			</div>
			<div class="bfi-col-sm-9 bfi-details-container">
				<!-- merchant details -->
				<div class="bfi-row" >
					<div class="bfi-col-sm-12">
						<div class="bfi-item-title">
							<a href="<?php echo $itemRoute ?>" onclick="event.stopPropagation();"  id="nameAnchor<?php echo $res->EventId?>" <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> class="eectrack" data-type="<?php echo $resourceDataTypeTrack ?>" data-id="<?php echo $res->EventId?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php echo  $resourceName ?></a> 
						</div>
						<div class="bfi-item-address">
							<?php if ($showResourceMap){?>
							<i class="fa fa-map-marker fa-1"></i> <a href="javascript:void(0);" onclick="event.stopPropagation();bookingfor.bfiShowMarker(<?php echo $res->EventId?>)"><span id="address<?php echo $res->EventId?>"></span>
								<?php if(isset($resource->Address) && !empty( $resource->Address) && !empty($resource->Address->Name)) { ?><span class="street-address"><?php echo $resource->Address->Name ?></span>,&nbsp;<?php } ?>
								<span class="street-address"><?php echo $indirizzo ?></span>,&nbsp;
								<span class="postal-code "><?php echo  $cap ?></span>&nbsp;
								<span class="locality"><?php echo $comune ?></span>,&nbsp;
								<span class="region"><?php echo  $stato ?></span>
							</a>
							<div class="bfi-hide" id="markerInfo<?php echo $res->EventId?>">
									<a href="<?php echo $itemRoute ?>" onclick="event.stopPropagation();"  style='background: url("<?php echo $imageUrl; ?>") center 25%;background-size: cover;' <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> class="eectrack" data-type="<?php echo $resourceDataTypeTrack ?>" data-id="<?php echo $res->EventId?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><img src="<?php echo $imageUrl; ?>" class="bfi-img-responsive" /></a> 
									<div style="padding:5px;">
										<div class="bfi-item-title">
											<a href="<?php echo $itemRoute ?>" onclick="event.stopPropagation();"  <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> class="eectrack" data-type="<?php echo $resourceDataTypeTrack ?>" data-id="<?php echo $res->EventId?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php echo  $resourceName ?></a> 
										</div>
										<span id="mapaddress<?php echo $res->EventId?>"></span>
										<div class="bfi-text-right"><a href="<?php echo $itemRoute ?>" <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> onclick="event.stopPropagation();"  class="bfi-btn eectrack" data-type="<?php echo $resourceDataTypeTrack ?>" data-id="<?php echo $res->EventId?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php _e('View', 'bfi') ?></a> </div>
									</div>
							</div>
							<?php if(isset($res->CenterDistance)) { ?>
								<span class="bfi-centerdistance" id="addressdist<?php echo $res->EventId?>" data-toggle="tooltip" title="<?php _e('This is the straight-line distance on the map. Actual travel distance may vary.', 'bfi') ?>">(<i class="fa fa-road" aria-hidden="true"></i> <?php echo BFCHelper::formatDistanceUnits($res->CenterDistance)?> <?php _e('from centre', 'bfi') ?>)</span>
							<?php } ?>

							<?php } ?>
						</div>
						<div class="bfi-clearfix bfi-hr-separ"></div>
                        <div class="bfi-item-descr bfi-description bfi-shortentextlong" id="descrInfo<?php echo $res->EventId?>" >
							<?php echo $resourceDescription?>  
						</div>

                    </div>
				</div>
                <div class="bfi-clearfix bfi-hr-separ"></div>
					<a href="<?php echo $itemRoute ?>" onclick="event.stopPropagation();"  class="bfi-btn eectrack <?php echo $btnClass ?> bfi-pull-right" <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> data-type="<?php echo $resourceDataTypeTrack ?>" data-id="<?php echo $res->EventId?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php echo $btnText ?></a>
					<?php 
						if (!empty($res->TagsIdList)) {
						   $allTagIds = array_unique(array_merge($allTagIds,explode (",",$res->TagsIdList))); 
						}
					?>
					<div class="bfirestags" id="bfitags<?php echo $res->EventId?>" rel="<?php echo $res->TagsIdList ?>"></div>

                <!-- end merchant details -->
                <div class="bfi-clearfix"></div>
			</div>
		</div>
	</div>
<?php 
	if ($showResourceMap) {
		$listResourceMaps[] = $val;
	}


	$listsId[]= $res->EventId;
	$counter++;
}
?>

</div>
</div>

<script type="text/javascript">
<!--

var listToCheck = "<?php echo implode(",", $listsId) ?>";
var listResourceMaps = <?php echo json_encode($listResourceMaps) ?>;
var strAddress = "[indirizzo] - [cap] - [comune] ([provincia])";
var imgPathMG = "<?php echo BFCHelper::getImageUrlResized('tag','[img]', 'tag24') ?>";
var imgPathMGError = "<?php echo BFCHelper::getImageUrl('tag','[img]', 'tag24') ?>";

var mg = [];

var loaded=false;

function getAjaxInformations(){
	if (!loaded)
	{
		loaded=true;
		var queryMG  = "GetTagsByIds&ids=<?php echo implode(',', $allTagIds) ?>&language=" + bfi_variables.bfi_cultureCode + "&viewContextType=8";
		jQuery.get(bookingfor.getActionUrl(null, null, queryMG), function(data) {
				if(data!=null){
					jQuery.each(data || [], function(key, val) {
                        if (val.ImageUrl != null && val.ImageUrl != '') {
	                        var $imageurl = imgPathMG.replace("[img]", val.ImageUrl );
	                        var $imageurlError = imgPathMGError.replace("[img]", val.ImageUrl );
	                        mg[val.TagId] = '<img src="' + $imageurl + '" onerror="this.onerror=null;this.src=\'' + $imageurlError + '\';" alt="' + val.Name + '" data-toggle="tooltip" title="' + val.Name + '" />';
                        } else if (val.IconSrc != null && val.IconSrc != '') {
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
                        } else {
                            mg[val.TagId] = val.Name;
                        }
					});	
				}
				bfiUpdateInfoResGrp();
		},'json');
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
				if(typeof mg[srvid] !== 'undefined' ){
					srvArr.push(mg[srvid]);
				}
			});
			jQuery(this).html(srvArr.join(", "));
		}

	});
}

jQuery(document).ready(function() {
	getAjaxInformations();
    jQuery('.bfi-shortentextlong').each(function () {
        bookingfor.shortenText(jQuery(this), 250);
    });
});


//-->
</script>

