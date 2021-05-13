<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$isportal = COM_BOOKINGFORCONNECTOR_ISPORTAL;
$showdata = COM_BOOKINGFORCONNECTOR_SHOWDATA;
$showcontactbanner = COM_BOOKINGFORCONNECTOR_SHOWCONTACTBANNER;
$eecpromos = array();

$showmap = !empty(COM_BOOKINGFORCONNECTOR_ENABLEGOOGLEMAPSAPI);
if ($showmap && COM_BOOKINGFORCONNECTOR_USE_OPENSTREETMAP) {
	wp_enqueue_script('bfileaflet');
	wp_enqueue_script('bfileafletcontrolcustom');
}

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
$currParam = BFCHelper::getSearchParamsSession();
if(isset($currParam) && isset($currParam['onlystay'])){
		$onlystay =  ($currParam['onlystay'] === 'false' || $currParam['onlystay'] === 0)? false: true;
}
$itemtypes = '0';
$availabilitytype = 1;
	$listResourceMaps = array();

$checkin = BFCHelper::getStayParam('checkin', new DateTime('UTC'));
$checkout = BFCHelper::getStayParam('checkout', new DateTime('UTC'));
$checkinstr = $checkin->format("d") . " " . date_i18n('F',$checkin->getTimestamp()) . ' ' . $checkin->format("Y") ;
$checkoutstr = $checkout->format("d") . " " . date_i18n('F',$checkout->getTimestamp()) . ' ' . $checkout->format("Y") ;
$totalResult = $totalAvailable;
$totPerson = (isset($currParam)  && isset($currParam['paxes']))? $currParam['paxes']:0 ;
$groupResultType = (isset($currParam)  && isset($currParam['groupresulttype']))? $currParam['groupresulttype']:0 ;
$counter = 0;
$availabilitytype = isset($currParam['availabilitytype']) ? $currParam['availabilitytype'] : $availabilitytype ;
$itemtypes = !empty($currParam['itemtypes']) ? $currParam['itemtypes'] : $itemtypes ;

$pckpaxages = BFCHelper::getStayParam('pckpaxages');
$minqt = isset($currParam['minqt']) ? $currParam['minqt'] : 1;
$maxqt = isset($currParam['maxqt']) ? $currParam['maxqt'] : 1;
$nad = 0;
$nch = 0;
$nse = 0;

$paxages = (isset($currParam) && isset($currParam['paxages']))? $currParam['paxages']:array() ;
if (empty($paxages)){
	$nad = 2;
	$nch = 0;
	$nse = 0;
	$totPerson = 2;
	$paxages = array(BFCHelper::$defaultAdultsAge, BFCHelper::$defaultAdultsAge);
}
$newpaxages = array();
foreach ($paxages as $age) {
	if ($age >= BFCHelper::$defaultSenioresAge) {
		array_push($newpaxages, $age.":".bfiAgeType::$Seniors);
		$nse += 1;
	} else if ($age >= BFCHelper::$defaultAdultsAge) {
		array_push($newpaxages, $age.":".bfiAgeType::$Adult);
		$nad += 1;
	} else {
		array_push($newpaxages, $age.":".bfiAgeType::$Reduced);
		$nch += 1;
	}
}


if (empty($pckpaxages))	$pckpaxages = implode('|',$newpaxages);


$listsId = array();
$base_url = get_site_url();


$resourceImageUrl = BFI()->plugin_url() . "/assets/images/defaults/default-s6.jpeg";

$searchAvailability_page = get_post( bfi_get_page_id( 'searchavailability' ) );
$formAction = (isset($_SERVER['HTTPS']) ? "https" : "http") . ':' ."//" .$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
//$formAction = get_permalink( $searchAvailability_page->ID );

if(!empty($page)){
	$formAction = str_replace('/page/'.$page."/","/",$formAction);
}

////$merchantdetails_page = get_post( bfi_get_page_id( 'merchantdetails' ) );
//$url_merchant_page = get_permalink( $merchantdetails_page->ID );
$url_merchant_page = BFCHelper::getPageUrl('merchantdetails');

//$accommodationdetails_page = get_post( bfi_get_page_id( 'accommodationdetails' ) );
//$url_resource_page = get_permalink( $accommodationdetails_page->ID );
$url_resource_page = BFCHelper::getPageUrl('accommodationdetails');
$uri = $url_resource_page;

//$resourcegroupdetails_page = get_post( bfi_get_page_id( 'resourcegroupdetails' ) );
//$url_resourcegroup_page = get_permalink( $resourcegroupdetails_page->ID );
$url_resourcegroup_page = BFCHelper::getPageUrl('resourcegroupdetails');


$currFilterOrder = "";
$currFilterOrderDirection = "";
if (!empty($currSorting) &&strpos($currSorting, '|') !== false) {
	$acurrSorting = explode('|',$currSorting);
	$currFilterOrder = $acurrSorting[0];
	$currFilterOrderDirection = $acurrSorting[1];
}
$resources = $items;

$allTagIds = array();
$urlparts = parse_url($formAction);
$query = array();
if (isset($urlparts['query']) && !empty($urlparts['query'])) {
	parse_str($urlparts['query'], $query);    
}

//if (empty($currFilterOrder)) {
//	shuffle($resources);
//}
$eventId = BFCHelper::getVar('eventid','');
$eventName = "";
if(!empty($eventId)){
	$event = BFCHelper::getEventById($eventId, $language);
	$eventName = BFCHelper::getLanguage($event->Name, $language, null, array('ln2br'=>'ln2br', 'striptags'=>'striptags')); 
}
?>
<div class="bfi-content">
	<div class="bfi-row">
		<div class="bfi-col-xs-<?php echo (COM_BOOKINGFORCONNECTOR_ISMOBILE) ?"12 bfi-search-title-mobile":"9"; ?> ">
			<?php if($showSearchTitle){ ?>
			<div class="bfi-search-title">
				<?php if(!empty($eventName)) { ?>
				<div class="bfi-title-searched"><?php echo $eventName ?></div>
				<?php } ?>
				
				<?php if ($totalAvailable != $total) {
//					echo " ". __('on', 'bfi') . " " . $total ." ";
					echo sprintf( __('Found %s results on %s', 'bfi'),$totalResult, $total );
				}else{
					echo sprintf( __('Found %s results', 'bfi'),$totalResult );
				} ?>
			</div>
			<div class="bfi-search-title-sub">
				<?php echo sprintf( __('From %s to %s', 'bfi'),$checkinstr,$checkoutstr ) ?>
			</div>
			<?php } ?>
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
		<form action="<?php echo $urlparts['path']; ?>" method="get" name="bookingforsearchForm" id="bookingforsearchFilterForm">
		
		<?php 
		foreach($query as $key => $value) {
			if (!in_array($key, array("newsearch", "limitstart", "filter_order", "filter_order_Dir"))) { 
				if ($key=="extras" || $key=="filters") {
				    continue;
				}
			?>
			<input type="hidden" value="<?php echo $value; ?>" name="<?php echo $key; ?>" />
		<?php }
		}
		?>
		
				<input type="hidden" class="filterOrder" name="filter_order" value="<?php echo $currFilterOrder ?>" />
				<input type="hidden" class="filterOrderDirection" name="filter_order_Dir" value="<?php echo $currFilterOrderDirection ?>" />
				<input type="hidden" name="limitstart" value="0" />
				<input type="hidden" name="newsearch" value="0" />
		</form>
		<div class="bfi-results-sort">
			<span class="bfi-sort-item-order"><?php echo _e('Order by' , 'bfi')?>:</span>
			<span class="bfi-sort-item <?php echo $currSorting=="price|asc" ? "bfi-sort-item-active": "" ; ?>" rel="price|asc" ><?php echo _e('Lowest price first' , 'bfi'); ?></span>
			<span class="bfi-sort-item <?php echo $currSorting=="rating|desc" ? "bfi-sort-item-active": "" ; ?>" rel="rating|desc" ><?php echo _e('Review score' , 'bfi'); ?></span>
			<span class="bfi-sort-item <?php echo $currSorting=="offer|asc" ? "bfi-sort-item-active": "" ; ?>" rel="offer|asc" ><?php echo _e('Best offers' , 'bfi'); ?></span> 
			<?php if($currParam != null && !empty($currParam['points'])) { 
				if (!empty($currParam['searchTermValue']) && strpos($currParam['searchTermValue'], "cityIds|") === 0) { ?>
				<span class="bfi-sort-item <?php echo $currSorting=="distance|asc" ? "bfi-sort-item-active": "" ; ?>" rel="distance|asc" ><?php echo _e('Distance from center' , 'bfi'); ?></span> 
				<?php
				} else if (!empty($currParam['searchTermValue']) && strpos($currParam['searchTermValue'], "poiIds|") === 0) { ?>
				<span class="bfi-sort-item <?php echo $currSorting=="distance|asc" ? "bfi-sort-item-active": "" ; ?>" rel="distance|asc" ><?php echo _e('Distance from point of interest' , 'bfi'); ?></span> 
				<?php 
				}
			}			
			?>
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

<div id="bfi-list" class="bfi-row bfi-list <?php echo (COM_BOOKINGFORCONNECTOR_SHOWEVENTBANNER) ?'bfilistwithbanner':'' ?>" 
	data-banner="<?php echo (COM_BOOKINGFORCONNECTOR_SHOWEVENTBANNER) ?COM_BOOKINGFORCONNECTOR_SHOWEVENTBANNEREVERY:'0' ?>" data-banner-repeated="<?php echo (COM_BOOKINGFORCONNECTOR_SHOWEVENTBANNERREPEATED) ?'1':'0' ?>"
	data-checkin="<?php echo $checkin->format('YmdHis'); ?>"
	data-checkout="<?php echo $checkout->format('YmdHis'); ?>"
	data-totalitems="<?php echo count($resources); ?>"
	>
<?php 
foreach ($resources as $currKey => $resource){

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
			$itemRoute = $url_resource_page.$resource->ItemId.'-'.BFI()->seoUrl($resourceName).$fromsearchparam;
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
			$itemRoute = $url_merchant_page . $resource->MerchantId .'-'.BFI()->seoUrl($resourceName).$fromsearchparam;
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
			$itemRoute = $url_resourcegroup_page.$resource->ItemId.'-'.BFI()->seoUrl($resourceName).$fromsearchparam;
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

	$btnText = __('Request','bfi');
	$btnClass = "bfi-alternative";
	if ($IsBookable){
		$btnText = __('Book Now','bfi');
		$btnClass = "";
	}



	$resourceNameTrack =  BFCHelper::string_sanitize($resourceName);
	$merchantNameTrack =  BFCHelper::string_sanitize($merchantName);
	$merchantCategoryNameTrack =  BFCHelper::string_sanitize($resource->DefaultLangCategoryName);

	$totalInt = 0;

	if( Count($resource->Results)<=0) { 
		bfi_get_template("shared/contact_banner.php",array("showcontactbanner"=>$showcontactbanner));
		$showcontactbanner = false; // lo faccio vedere solo una volta

	}
	?>
	<div class="bfi-col-sm-6 bfi-item bfi-list-group-item" data-href="<?php echo $itemRoute ?>" 
	style="<?php echo ($resource->Available) ?"":"opacity:0.7"; ?>">
		<div class="bfi-row bfi-sameheight" >
			<div class="bfi-col-sm-3 bfi-img-container">
				<a href="<?php echo $itemRoute ?>" style='background: url("<?php echo $imageUrl; ?>") center 25%;background-size: cover;' <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?>   class="eectrack" data-type="<?php echo $resourceDataTypeTrack ?>" data-id="<?php echo $resourceDataIdTrack?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>">
				<?php bfi_get_template("shared/favorite_icon.php",$favoriteModel); ?>
				<img src="<?php echo $imageUrl; ?>" class="bfi-img-responsive" />
				</a> 
			</div>
			<div class="bfi-col-sm-9 bfi-details-container">
				<!-- merchant details -->
				<div class="bfi-row" >
					<div class="bfi-col-sm-9">
						<div class="bfi-item-title">
							<a href="<?php echo $itemRoute ?>" onclick="event.stopPropagation();" id="nameAnchor<?php echo $resource->ItemId?>" <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> class="eectrack" data-type="<?php echo $resourceDataTypeTrack ?>" data-id="<?php echo $resourceDataIdTrack?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php echo  $resourceName ?></a> 
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
								- <a href="<?php echo $routeMerchant?>" onclick="event.stopPropagation();" class="bfi-subitem-title eectrack" <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> data-type="Merchant" data-id="<?php echo $resource->MerchantId?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $merchantNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php echo $merchantName; ?></a><?php } ?>
							
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
										<a href="<?php echo $itemRoute ?>" onclick="event.stopPropagation();" style='height:100%;background: url("<?php echo $imageUrl; ?>") center 25%;background-size: cover;display: block;' <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> class="eectrack" data-type="<?php echo $resourceDataTypeTrack ?>" data-id="<?php echo $resourceDataIdTrack?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>">&nbsp;</a> 
									</div>
									<div class="bfi-map-info-container-content" >
											<div class="bfi-item-title">
												<a href="<?php echo $itemRoute ?>" onclick="event.stopPropagation();" <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> class="eectrack" data-type="<?php echo $resourceDataTypeTrack ?>" data-id="<?php echo $resourceDataIdTrack?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php echo  $resourceName ?></a> 
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
											<div class="bfi-text-right"><a onclick="event.stopPropagation();" href="<?php echo $itemRoute ?>" <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> class="bfi-btn eectrack" data-type="<?php echo $resourceDataTypeTrack ?>" data-id="<?php echo $resourceDataIdTrack?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php _e('View', 'bfi') ?></a> </div>
									</div>
								</div>
							</div>

							<?php } else { ?>
                                <span class="bfi-comma" id="address<?php echo $resource->ItemId?>"></span>
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
									<a class="bfi-avg-value eectrack" onclick="event.stopPropagation();" href="<?php echo $itemRoute ?>" <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> data-type="<?php echo $resourceDataTypeTrack ?>" data-id="<?php echo $resource->MerchantId?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $merchantNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php echo $rating_text['merchants_reviews_text_value_'.$totalInt] ?></a>
									<a class="bfi-avg-count eectrack" onclick="event.stopPropagation();" href="<?php echo $itemRoute ?>" <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> data-type="<?php echo $resourceDataTypeTrack ?>" data-id="<?php echo $resourceDataIdTrack?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php echo sprintf(__('%s reviews', 'bfi') ,$resource->AVGCount) ?></a>
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
	$currViewed = rand(0, 5);
	if ($currViewed >1) {
	?>
		<div class="bfi_wiewedby bfi-hide">
			<?php echo sprintf(__('%s people are watching right now', 'bfi'),$currViewed) ?>
		</div>
	<?php 
		}
	$currViewed = rand(0, 5);
	if(!COM_BOOKINGFORCONNECTOR_ISMOBILE && $currViewed >1){
		?>
		<div class="bfi-recommendation-title">
			<?php _e('Recommended for', 'bfi') ?> 
			<?php
			if ($nse > 0 && $nch > 0) {?>
				<?php echo $nad ?> <?php echo strtolower(($nad > 1)? __('Adults', 'bfi') : __('Adult', 'bfi')); ?>,
				<?php echo $nse ?> <?php echo __('Seniores', 'bfi'); ?>
				<?php _e('and', 'bfi') ?> <?php echo $nch ?> <?php echo strtolower(($nch > 1)? __('Children', 'bfi'): _e('Child', 'bfi')); ?>
				<?php } else { ?>
				<?php echo $nad ?> <?php echo strtolower(($nad>1)? __('Adults', 'bfi') : __('Adult', 'bfi')); ?>
				<?php if($nse > 0){?> <?php _e('and', 'bfi') ?> <?php echo $nse ?> <?php echo __('Seniores', 'bfi'); ?><?php }?>
				<?php if($nch > 0){?> <?php _e('and', 'bfi') ?> <?php echo $nch ?> <?php echo strtolower(($nch > 1)? __('Children', 'bfi'): _e('Child', 'bfi')); ?><?php }?>
			<?php 
			}
			?>:
		</div>
	<?php 
		
	}
	

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


	foreach ($listResources as $resourceIndex=>$singleResource) // foreach $listMerchantsCart
	{
		$currResource = $singleResource[0];
		$currName = BFCHelper::getLanguage($currResource->Name, $language, null, array('ln2br'=>'ln2br', 'striptags'=>'striptags')); 
		$searchparamsuffix = "";
// TODO
//		if (isset($currCheckIn) && isset($currCheckOut) && !empty($currCheckIn) && !empty($currCheckOut)) {

		if ($currResource->Availability > 0 && $currResource->TotalPrice > 0) {
			if (!empty($currResource->AvailabilityDate)) {
				$currCheckIn = DateTime::createFromFormat('Y-m-d\TH:i:s',$currResource->AvailabilityDate,new DateTimeZone('UTC'));
				$currCheckOut = DateTime::createFromFormat('Y-m-d\TH:i:s',$currResource->CheckOutDate,new DateTimeZone('UTC'));
				$searchparamsuffix = "&checkin=" . $currCheckIn->format("YmdHis") . "&checkout=" . $currCheckOut->format("YmdHis");
			}
		}
		$resourceRoute = $url_resource_page . $currResource->ResourceId . '-' . BFI()->seoUrl($resourceName) . $fromsearchparam . $searchparamsuffix;
		$currresourceNameTrack =  BFCHelper::string_sanitize($currName);

		if ($totalResources > 1) {
			$resourceRoute = $itemRoute.$searchparamsuffix ;
		}

		// per tutti i link si rmanda sempre alla stessa pagina
		$resourceRoute = $itemRoute.$searchparamsuffix ;

?>
				<div>
						<div class="bfi-result-singleitem bfi-row">
							<div class="bfi-col-sm-8">
							<?php 
								if ($currResource->MaxPaxes>0) {?>
								<div class="bfi-icon-paxes">
									<i class="fa fa-user"></i> 
									<?php if ($currResource->MaxPaxes==2){?>
									<i class="fa fa-user"></i> 
									<?php }?>
									<?php if ($currResource->MaxPaxes>2){?>
										<?php echo ($currResource->MinPaxes != $currResource->MaxPaxes)? $currResource->MinPaxes . "-" : "" ?><?php echo  $currResource->MaxPaxes ?>
									<?php }?>
								</div>
							<?php
								}
							?>
							<a href="<?php echo $resourceRoute?>" onclick="event.stopPropagation();" class="bfi-subitem-title eectrack" <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> data-type="Resource" data-id="<?php echo $currResource->ResourceId?>" data-index="<?php echo $counter?>" data-itemname="<?php echo $currresourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><b><?php echo (count($resource->Results) > 1 ? count($singleResource) . " x " : "") .$currName; ?></b></a>
							<div class="bfi-result-singleitem-details">
								<?php 
								
								$mainDetails = array();
								if (!empty($currResource->BedRooms) && $currResource->BedRooms > 0) {
									array_push($mainDetails, $currResource->BedRooms . " " .__('BedRooms' , 'bfi'));
								}
								if (!empty($currResource->LivingRooms) && $currResource->LivingRooms > 0) {
									array_push($mainDetails, $currResource->LivingRooms . " " .__('Living room' , 'bfi'));
								}
								if (!empty($currResource->Baths) && $currResource->Baths > 0) {
									array_push($mainDetails, $currResource->Baths . " " .__('Baths' , 'bfi'));
								}
								if (!empty($currResource->Area) && $currResource->Area > 0) {
									array_push($mainDetails, $currResource->Area . " " . __('m&sup2;' , 'bfi'));
								}
								if (isset($currResource->MainBedsConfiguration) && !empty($currResource->MainBedsConfiguration)) {
									$listBeds = array();
									foreach ($currResource->MainBedsConfiguration as $bedrooms) {
										$currBeds = $bedrooms->Beds;
										foreach ($currBeds as $beds) {
											if (isset($listBeds[$beds->Type])) {
												$listBeds[$beds->Type]->Quantity += $beds->Quantity;
											} else {
												$listBeds[$beds->Type] = $beds;
											}
										}
									}
									
									foreach ($listBeds as $beds) {
										array_push($mainDetails, ($beds->Quantity . " " . ($beds->Quantity > 1 ? $bedtypes_text[$beds->Type] : $bedtype_text[$beds->Type])));
									}
									
								}
								
								foreach ($mainDetails as $det) {?>
									<span class="bfi-comma"><?php echo $det ?></span>
								<?php
								} ?>
								
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

							<?php } ?>
							<?php
								$mealsHelp = "";
									// Pasti
									if($currResource->IncludedMeals >-1){
										switch ($currResource->IncludedMeals) {
											case bfi_Meal::Breakfast:
													$mealsHelp = __("Breakfast included", 'bfi');
												break;
											case bfi_Meal::BreakfastLunch:
											case bfi_Meal::BreakfastDinner:
											case bfi_Meal::LunchDinner :
													$mealsHelp = __("Half board", 'bfi');
												break;
											case bfi_Meal::BreakfastLunchDinner:
													$mealsHelp = __("Full board", 'bfi');
												break;
											case bfi_Meal::AllInclusive:
											case bfi_Meal::BreakfastLunchDinnerAllInclusive:
													$mealsHelp = __("All Inclusive", 'bfi');
												break;
												
										}
									}
									$allMeals = array();
									$cssclassMeals = "bfi-meals-base";
									if ($currResource->IncludedMeals > -1) {
									//	$mealsHelp = __("There is no meal option with this room.", 'bfi');
										if ($currResource->IncludedMeals & bfi_Meal::Breakfast) {
											$allMeals[] = __("Breakfast", 'bfi');
										}
										if ($currResource->IncludedMeals & bfi_Meal::Lunch) {
											$allMeals[] = __("Lunch", 'bfi');
										}
										if ($currResource->IncludedMeals & bfi_Meal::Dinner) {
											$allMeals[] = __("Dinner", 'bfi');
										}
										if ($currResource->IncludedMeals & bfi_Meal::AllInclusive) {
											$allMeals[] = __("All Inclusive", 'bfi');
										}
										if (in_array(__("Breakfast", 'bfi'), $allMeals)) {
											$cssclassMeals = "bfi-meals-bb";
										}
										if (in_array(__("Lunch", 'bfi'), $allMeals) || in_array(__("Dinner", 'bfi'), $allMeals) || in_array(__("All Inclusive", 'bfi'), $allMeals)) {
											$cssclassMeals = "bfi-meals-fb";
										}
									//	if (count($allMeals) > 0) {
									//		$mealsHelp = implode(", ", $allMeals). " " . __('included', 'bfi');
									//	}
									//	if (count($allMeals) == 2) {
									//		$mealsHelp = implode(" & ", $allMeals). " " . __('included', 'bfi');
									//	}
									}
									// fine Pasti
									?>
									<div class="bfi-meals <?php echo $cssclassMeals?>"><?php echo $mealsHelp ?></div>
									<?php if (!$currResource->IsCatalog && $onlystay ){ 
										$currBookingTypeId = $currResource->RatePlan->MerchantBookingTypeId;
										if (isset($currResource->RatePlan->MerchantBookingTypes) && is_array($currResource->RatePlan->MerchantBookingTypes)) {
											$currMerchantBookingType = array_filter($currResource->RatePlan->MerchantBookingTypes, function($bt) use($currBookingTypeId) { return $bt->BookingTypeId == $currBookingTypeId; });
											if (count($currMerchantBookingType) > 0 && $currMerchantBookingType[0]->PayOnArrival && $currMerchantBookingType[0]->AcquireCreditCardData && !empty($currMerchantBookingType[0]->DepositRelativeValue)) { ?>
												<span class="bfi-noprepayment"><?php _e('Pay at the property – NO PREPAYMENT NEEDED', 'bfi') ?></span>
											<?php 
											}										    
										}

								} ?>
							</div>
							<div class="bfi-col-sm-4 bfi-text-right bfi-containerPrice">
							<?php 
							if ($resource->Price>0 && $resourceIndex ==0 ) {
								$val->Price = $resource->Price;
								$val->TotalPrice = $resource->TotalPrice;
								$val->Currencyclass = $currencyclass;

								if (isset($currResource->RatePlan) && count($currResource->RatePlan->AllVariations)>0) {
									foreach ($currResource->RatePlan->AllVariations as $promotion ) {
										$eecpromo = new stdClass;
										$eecpromo->id = $promotion->VariationPlanId;
										$eecpromo->name = $promotion->Name;
										if ($promotion->TotalAmount<0 && !array_key_exists($eecpromo->id, $eecpromos)) {
											$eecpromos[$eecpromo->id] = $eecpromo;
										}					
									}

								}

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


								if (empty($resource->FullPeriodPrice) || !$resource->FullPeriodPrice) {
									echo __("From", 'bfi');
								}
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
							?>
								</span>
								<span class="bfi-comma">
								<?php if ($nse > 0 && $nch > 0) {?>
									<?php echo $nad ?> <?php echo strtolower(($nad > 1)? __('Adults', 'bfi') : __('Adult', 'bfi')); ?>,
									<?php echo $nse ?> <?php echo __('Seniores', 'bfi'); ?>
									<?php _e('and', 'bfi') ?> <?php echo $nch ?> <?php echo strtolower(($nch > 1)? __('Children', 'bfi'): _e('Child', 'bfi')); ?>
									<?php } else { ?>
									<?php echo $nad ?> <?php echo strtolower(($nad>1)? __('Adults', 'bfi') : __('Adult', 'bfi')); ?>
									<?php if($nse > 0){?> <?php _e('and', 'bfi') ?> <?php echo $nse ?> <?php echo __('Seniores', 'bfi'); ?><?php }?>
									<?php if($nch > 0){?> <?php _e('and', 'bfi') ?> <?php echo $nch ?> <?php echo strtolower(($nch > 1)? __('Children', 'bfi'): _e('Child', 'bfi')); ?><?php }?>
								<?php 
								}?>
								</span>

								</div><?php
								}
								?>
								<?php if ($resource->Price < $resource->TotalPrice){ ?>
								<span class="bfi-discounted-price bfi-discounted-price-total bfi_<?php echo $currencyclass ?> bfi-cursor" rel="<?php echo $SimpleDiscountIds ?>"
								data-allvariations='<?php echo json_encode($currResource->RatePlan->AllVariations) ?>'
								><?php echo BFCHelper::priceFormat($resource->TotalPrice,2, ',', '.')  ?><span class="bfi-no-line-through">&nbsp;<i class="fa fa-question-circle" aria-hidden="true"></i></span></span>
								<?php } ?>
								<span class="bfi-price bfi-price-total bfi_<?php echo $currencyclass ?> <?php echo ($resource->Price < $resource->TotalPrice)?"bfi-red":"" ?>"  ><?php echo BFCHelper::priceFormat($resource->Price,2, ',', '.') ?></span>
 							<?php } ?>
							</div>
						</div>
				</div>
				<div class="bfi-clearfix"></div>
<?php 
	}
?>
				<div class="bfi-row">
					<div class="bfi-col-sm-8">
						<?php 
//						$totalotherresources = $resource->TotalAvailableResources - count(array_values(array_unique(array_map(function($elem) { return $elem->ResourceId; }, $resource->Results))));
						$totalotherresources = $resource->TotalAvailableResources - 1;
						if ($resource->Available && $totalotherresources > 0) {
							?>
							<div class="bfi-othersolutions">
								<a href="<?php echo $itemRoute.$searchparamsuffix ?>" onclick="event.stopPropagation();" <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> class="eectrack" data-type="<?php echo $resourceDataTypeTrack ?>" data-id="<?php echo $resourceDataIdTrack?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php echo sprintf(__('View other %d solutions', 'bfi'), $totalotherresources) ?></a>
							</div>	
							<?php
						}
						$currViewed = rand(0, 5);
						if(!COM_BOOKINGFORCONNECTOR_ISMOBILE && $currViewed == 0){
						?>
							<div class="bfi_hurryup">
								<span class="bfi_hurryup_title"><?php _e('Hurry up', 'bfi') ?>!</span><br />
								<span><?php _e('This resource is in great demand', 'bfi') ?></span>
							</div>
						<?php } ?>
						<?php 
						$currViewed = rand(0, 10);
						if ($currViewed == 0 && false) {
						?>
							<div class="bfi_lastbooking">
								<?php echo  sprintf(__('%s request in the last 24 hours', 'bfi'),$currViewed) ?>
							</div>
						<?php } ?>
						<?php 
						//$totalInt = rand(5, 10);
						if ($totalInt >9) {
						?>
							<div class="bfi_ratingbest">
								<i class="fa fa-smile-o" aria-hidden="true"></i> <?php _e('This structure has met or exceeded the expectations of more than 90% of guests', 'bfi') ?>
							</div>
						<?php } ?>
						<?php 
							if (isset($nch) && $nch>0) {
						?>
							<div class="bfi_forfamily">
								<i class="fa fa-child" aria-hidden="true"></i> <?php _e('This structure is suitable for children', 'bfi') ?>
							</div>
						<?php } ?>
					</div>
					<div class="bfi-col-sm-4 bfi-text-right">
						<?php 
						if ($totalResources>1) {
	//					    echo "Multi risorsa -> form<br />";

						?>
						<form method="post" action="<?php echo $itemRoute.$searchparamsuffix ?>" <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> >
							<input type="hidden" name="minqt" value="<?php echo $minqt ?>" />
							<input type="hidden" name="maxqt" value="<?php echo $maxqt ?>" />
							<input type="hidden" name="paxages" value="<?php echo $pckpaxages ?>" />
							<input type="hidden" name="itemTypeIds" value="<?php echo $itemtypes ?>" />
							<input type="hidden" name="availabilityTypes" value="<?php echo $availabilitytype ?>" />
							<input type="hidden" name="resulttype" value="<?php echo $resource->ItemType ?>" />
							<input type="hidden" name="resultid" value="<?php echo ($resource->ItemType == 1 ? $resource->MerchantId : $resource->ItemId); ?>" />
							<input type="hidden" name="groupresulttype" value="<?php echo $groupResultType ?>" />
							<input type="hidden" name="bfipck" value="<?php echo bin2hex(gzdeflate(json_encode($resource,JSON_UNESCAPED_UNICODE),1)) ?>" />
							<button href="<?php echo $itemRoute.$searchparamsuffix ?>"  onclick="event.stopPropagation();" type="submit" class="bfi-btn bfi-btn eectrack <?php echo $btnClass ?>" <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> data-type="<?php echo $resourceDataTypeTrack ?>" data-id="<?php echo $resourceDataIdTrack?>" data-index="<?php echo $counter?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php echo $btnText ?></button>
						</form>
						<?php 
						} else {
						?>
							<a href="<?php echo $itemRoute.$searchparamsuffix ?>" onclick="event.stopPropagation();" class="bfi-btn eectrack <?php echo $btnClass ?>" <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> data-type="<?php echo $resourceDataTypeTrack ?>" data-id="<?php echo $resourceDataIdTrack?>" data-index="<?php echo $counter?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>"><?php echo $btnText ?></a>
						<?php 
						}
						?>
					</div>
				</div>
				<div class="bfi-clearfix"></div>
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
								<?php _e("We're sorry! we do not have any availability for this resource.", 'bfi') ?>
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
}
?>

</div>
</div>

<script type="text/javascript">
<!--
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
	<?php if(COM_BOOKINGFORCONNECTOR_GAENABLED == 1 && !empty(COM_BOOKINGFORCONNECTOR_GAACCOUNT) && COM_BOOKINGFORCONNECTOR_EECENABLED == 1  &&count($eecpromos) > 0){ 
	$eecpromos = array_values($eecpromos);
	?>
	if (typeof callAnalyticsEEc !== "undefined") {
		callAnalyticsEEc("addPromo", <?php echo json_encode($eecpromos); ?>, "item");
	}
	<?php } ?>

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

