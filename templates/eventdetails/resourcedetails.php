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
	$indirizzo = !empty($resource->Address->Address)?$resource->Address->Address:"";
	$cap = !empty($resource->Address->ZipCode)?$resource->Address->ZipCode:"";
	$comune = !empty($resource->Address->CityName)?$resource->Address->CityName:"";
	$provincia = !empty($resource->Address->RegionName)?$resource->Address->RegionName:"";
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
/*
if(isset($resource->TagsString) && !empty($resource->TagsString)){
	$lstTags = json_decode($resource->TagsString);
	$resource->tags = $lstTags;
}
*/
$tagsHighlight = array();
if(!empty( $resource->Tags)){
	foreach ( $resource->Tags as $gr ) {
		$gr->Tags = json_decode($gr->TagsString);
		$subGroupRefIds = array_filter(array_map(function ($i) { return $i->SubGroupRefId; }, $gr->Tags));
		$gr->subGroupRefIds = $subGroupRefIds;
		if (!empty($gr->subGroupRefIds )) {
			$currTag = $gr->Tags;
			usort($currTag, function($a,$b) {
				return BFCHelper::orderBy($a, $b, 'SubGroupOrder', 'asc');
			});

			$subgrs = array();
			$subgrsHighlight = array();

			foreach($currTag as $item) {
				if (!empty($item->SubGroupHighlight)) {
					if(!array_key_exists($item->SubGroupRefId, $subgrsHighlight)){
						$subgrsHighlight[$item->SubGroupRefId] = array();
					}
					$subgrsHighlight[$item->SubGroupRefId][] = $item;
					$tagsHighlight[] = $item;
				}else{
					if(!array_key_exists($item->SubGroupRefId, $subgrs)){
						$subgrs[$item->SubGroupRefId] = array();
					}
					$subgrs[$item->SubGroupRefId][] = $item;
				}
			}
			$gr->subgrs = $subgrs;
			$gr->subgrsHighlight = $subgrsHighlight;
		}
	}
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

$startDateSimple = $startDate->format("Y-m-d");
$endDateSimple = $endDate->format("Y-m-d");


$imgPopup = COM_BOOKINGFORCONNECTOR_DEFAULTIMAGE;
if (!empty($resource->DefaultImg)){
	$imgPopup =  BFCHelper::getImageUrlResized('events',$resource->DefaultImg, 'logomedium');
}
?>

<div class="bfi-content bfi-content-event bfi-content-event<?php echo $resource->EventId ?>">
    <div class="bfi-row">
        <div class="bfi-col-md-10">
            <div class="bfi-title-name ">
				<?php echo  $resourceName?>
			</div>
            <div class="bfi-address ">
                <i class="fa fa-map-marker fa-1"></i>
				<?php if (($showResourceMap)) {?><a class="bfi-map-link bfiopenpopupmap" ><?php } ?>
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
            <div class="bfi-shortcal-inline"><i class="fa fa-calendar" aria-hidden="true"></i> 
					<?php echo date_i18n('l',$startDate->getTimestamp()) . " " . $startDate->format("d") . " " . date_i18n('M',$startDate->getTimestamp()) . " " . $startDate->format("Y") ?>
                    <?php 
					if($startDateSimple != $endDateSimple && $dateCount == 1) { 
						echo date_i18n('l',$endDate->getTimestamp()) . " " . $endDate->format("d") . " " . date_i18n('M',$endDate->getTimestamp()) . " " . $endDate->format("Y") ;
                    } 
					if($startDateSimple != $endDateSimple && $dateCount > 1) { 
						echo " + " . ($dateCount-1) . " " . __('Dates', 'bfi');
                    } 
					?>
                    <?php _e('from hours', 'bfi') ?> <?php echo $startDate->format("H:i") ?>
                    <?php 
					if($startDate != $endDate) {
					?>
                        <span><?php _e('to hours', 'bfi') ?> <?php echo $endDate->format("H:i") ?></span>
				<?php
					} 
				?>
			</div>
        </div>
        <div class="bfi-col-md-2 bfi-text-right">
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
			<div class="bfi-addtoany bfi-pull-right">

							<!-- AddToAny BEGIN -->
							<a class="a2a_dd" href="https://www.addtoany.com/share "title="<?php _e('Share', 'bfi') ?>" ><i class="far fa-share-alt"></i></a>
							<script async src="https://static.addtoany.com/menu/page.js"></script>
							<!-- AddToAny END -->
			</div>	
		</div>
    </div>
	<ul class="bfi-menu-top bfi-hideonextra">
		<li class="bfi-hidden-xs bfi-hidden-sm"><a rel=".bfi-description-data"><?php echo  _e('Description', 'bfi') ?></a></li>
		<?php if (($showResourceMap)) {?><li><a rel="#bfimaptab" class="bfiopenpopupmap"><?php echo _e('Map' , 'bfi') ?></a></li><?php } ?>
		<?php if($dateCount > 1){ ?><li class="bfi-request"><a  href="javascript:void(0);" onclick="bookingfor.bfishowotherdates('bfi-otherdatesevents','<?php _e('All dates and places', 'bfi') ?>')"><?php _e('Show dates and places', 'bfi') ?></a></li><?php } ?>
<!-- BOTTONI -->

<?php
	
$currSearchParam = $resource->AccommodationSearch;
	
if(!empty($currSearchParam) && $currSearchParam->SearchType == 4 ) {
	
$currFrom = BFCHelper::parseJsonDate($currSearchParam->StartDate,'d/m/Y');
$currTo = BFCHelper::parseJsonDate($currSearchParam->EndDate,'d/m/Y');
if(!empty( $_REQUEST['checkin'] ) && !empty( $_REQUEST['checkout'] )){
	$currFrom  = DateTime::createFromFormat('YmdHis', $_REQUEST['checkin'], new DateTime($startDate,new DateTimeZone('UTC')));
	$currTo  = DateTime::createFromFormat('YmdHis', $_REQUEST['checkout'], new DateTime($endDate,new DateTimeZone('UTC')));
	
}
?>
<li class="bfi-book">
			<form id="searchformeventsdetails" action="<?php echo $formRoute?>" method="get" <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?>>
				<input type="hidden" name="stateIds" value="" />
				<input type="hidden" name="regionIds" value="" />
				<input type="hidden" name="cityIds" value="" />
				<input type="hidden" name="merchantIds" value="" />
				<input type="hidden" name="groupresourceIds" value="" />
				<input type="hidden" name="zoneIds" value="" />
				<input type="hidden" name="merchantTagIds" value="<?php echo $currSearchParam->MrcTags ?>" />
				<input type="hidden" name="productTagIds" value="<?php echo $currSearchParam->ResTags ?>" />
				<input type="hidden" name="groupTagsIds" value="<?php echo $currSearchParam->GrpTags ?>" />
				<input type="hidden" name="masterTypeId" value="<?php echo $currSearchParam->ProductCategories ?>" />
				<input type="hidden" name="merchantCategoryId" value="<?php echo $currSearchParam->MerchantCategories ?>" />
				<input type="hidden" name="onlystay" value="<?php echo $currSearchParam->CheckAvailability ?>" />
				<input type="hidden" name="checkin" value="<?php echo $currFrom ?>" />
				<input type="hidden" name="checkout" value="<?php echo $currTo ?>" />
				<input type="hidden" name="minqt" value="1" />
				<input type="hidden" name="maxqt" value="10" />
				<input type="hidden" name="persons" value="<?php echo $currSearchParam->MinPaxes ?>" />
				<input type="hidden" name="adults" value="<?php echo $currSearchParam->MinPaxes ?>" />
				<input type="hidden" name="adultssel" value="<?php echo $currSearchParam->MinPaxes ?>" />
				<input type="hidden" name="childrensel" value="0" />
				<input type="hidden" name="childages1sel" value="12" />
				<input type="hidden" name="childages2sel" value="12" />
				<input type="hidden" name="childages3sel" value="12" />
				<input type="hidden" name="childages4sel" value="12" />
				<input type="hidden" name="childages5sel" value="12" />
				<input type="hidden" name="showmsgchildage" value="0" />
				<input type="hidden" name="seniores" value="0" />
				<input type="hidden" name="children" value="0" />
				<input type="hidden" name="childages1" value="" />
				<input type="hidden" name="childages2" value="" />
				<input type="hidden" name="childages3" value="" />
				<input type="hidden" name="childages4" value="" />
				<input type="hidden" name="childages5" value="" />
				<input type="hidden" name="layout" value="" />
				<input type="hidden" name="points" value="" />
				<input type="hidden" name="newsearch" value="1" />
				<input type="hidden" name="limitstart" value="0" />
				<input type="hidden" name="filter_order" value="" />
				<input type="hidden" name="filter_order_Dir" value="" />
				<input type="hidden" name="searchtypetab" value="0" />
				<input type="hidden" name="searchId" value="<?php echo uniqid('', true)?>" />
				<input type="hidden" name="availabilitytype" value="<?php echo $currSearchParam->AvailabilityTypes ?>" />
				<input type="hidden" name="itemtypes" value="<?php echo $currSearchParam->ItemTypes ?>" />
				<input type="hidden" name="groupresulttype" value="<?php echo $currSearchParam->GroupResultType ?>" />
				<input type="hidden" name="GetBestGroupResult" value="1" />
				<input type="hidden" name="getallresults" value="0" />
				<input type="hidden" name="discountcodes" value="<?php echo ($currSearchParam->DiscountCode ?? "") ?>" />
				<input type="hidden" name="checkFullPeriod" value="<?php echo $currSearchParam->CheckFullPeriod ?>" />
				<input type="hidden" name="eventid" value="<?php echo $resource->EventId ?>" />

				<a href="javascript:void();" onclick="document.getElementById('searchformeventsdetails').submit();"> <?php _e('Book Resource', 'bfi') ?></a>
			</form>
</li>
<?php } ?>
<?php 
if(!empty( $resource->ServiceSearch )){
	switch ($resource->ServiceSearch->SearchType) {
                case 1: // link esterno
				?><li class="bfi-request">
                    <a href="<?php echo $resource->ServiceSearch->ExternalLink ?>" target="_blank"><?php _e('Tickets', 'bfi') ?></a>
					</li>
				<?php 
                    break;
                case 2: //risorsa
					if(!empty( $resource->ServiceSearch->ResourceId )){
						$res = BFCHelper::GetResourcesById( $resource->ServiceSearch->ResourceId );
						$accommodationdetails_page = get_post( bfi_get_page_id( 'accommodationdetails' ) );
						$url_accommodationdetails_page = get_permalink( $accommodationdetails_page->ID );
						$resourceRouteaccommodationdetails = $url_accommodationdetails_page . $res->ResourceId .'-'.BFI()->seoUrl($res->Name);
?><li class="bfi-book">
						<a href="<?php echo $resourceRouteaccommodationdetails ?>"><?php _e('Buy tickets', 'bfi') ?></a>
</li><?php 

					}
					
                    break;
                case 3: //gruppo di risorse
					if(!empty( $resource->ServiceSearch->ResourceGroupId )){
						$res = BFCHelper::getResourcegroupFromServicebyId( $resource->ServiceSearch->ResourceGroupId );
						if (!empty($res )) {
						    
						$accommodationdetails_page = get_post( bfi_get_page_id( 'resourcegroupdetails' ) );
						$url_accommodationdetails_page = get_permalink( $accommodationdetails_page->ID );
						$resourceRouteaccommodationdetails = $url_accommodationdetails_page . $res->CondominiumId .'-'.BFI()->seoUrl($res->Name);
?><li class="bfi-book">
						<a href="<?php echo $resourceRouteaccommodationdetails ?>"><?php _e('Buy tickets', 'bfi') ?></a>
</li><?php 
						}
					}
					break;
				case 4:

$currSearchParam = $resource->ServiceSearch;
		
if(!empty($currSearchParam) && $currSearchParam->SearchType == 4 ) {
	
$currFrom = BFCHelper::parseJsonDate($currSearchParam->StartDate,'d/m/Y');
$currTo = BFCHelper::parseJsonDate($currSearchParam->EndDate,'d/m/Y');
if(!empty( $_REQUEST['checkin'] ) && !empty( $_REQUEST['checkout'] )){
	$currFrom  = DateTime::createFromFormat('YmdHis', $_REQUEST['checkin'], new DateTime($startDate,new DateTimeZone('UTC')));
	$currTo  = DateTime::createFromFormat('YmdHis', $_REQUEST['checkout'], new DateTime($endDate,new DateTimeZone('UTC')));
	
}
?>
<li class="bfi-book">
			<form id="searchServiceformeventsdetails" action="<?php echo $formRoute?>" method="get" <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?>>
				<input type="hidden" name="stateIds" value="" />
				<input type="hidden" name="regionIds" value="" />
				<input type="hidden" name="cityIds" value="" />
				<input type="hidden" name="merchantIds" value="" />
				<input type="hidden" name="groupresourceIds" value="" />
				<input type="hidden" name="zoneIds" value="" />
				<input type="hidden" name="merchantTagIds" value="<?php echo $currSearchParam->MrcTags ?>" />
				<input type="hidden" name="productTagIds" value="<?php echo $currSearchParam->ResTags ?>" />
				<input type="hidden" name="groupTagsIds" value="<?php echo $currSearchParam->GrpTags ?>" />
				<input type="hidden" name="masterTypeId" value="<?php echo $currSearchParam->ProductCategories ?>" />
				<input type="hidden" name="merchantCategoryId" value="<?php echo $currSearchParam->MerchantCategories ?>" />
				<input type="hidden" name="onlystay" value="<?php echo $currSearchParam->CheckAvailability ?>" />
				<input type="hidden" name="checkin" value="<?php echo $currFrom ?>" />
				<input type="hidden" name="checkout" value="<?php echo $currTo ?>" />
				<input type="hidden" name="minqt" value="1" />
				<input type="hidden" name="maxqt" value="10" />
				<input type="hidden" name="persons" value="<?php echo $currSearchParam->MinPaxes ?>" />
				<input type="hidden" name="adults" value="<?php echo $currSearchParam->MinPaxes ?>" />
				<input type="hidden" name="adultssel" value="<?php echo $currSearchParam->MinPaxes ?>" />
				<input type="hidden" name="childrensel" value="0" />
				<input type="hidden" name="childages1sel" value="12" />
				<input type="hidden" name="childages2sel" value="12" />
				<input type="hidden" name="childages3sel" value="12" />
				<input type="hidden" name="childages4sel" value="12" />
				<input type="hidden" name="childages5sel" value="12" />
				<input type="hidden" name="showmsgchildage" value="0" />
				<input type="hidden" name="seniores" value="0" />
				<input type="hidden" name="children" value="0" />
				<input type="hidden" name="childages1" value="" />
				<input type="hidden" name="childages2" value="" />
				<input type="hidden" name="childages3" value="" />
				<input type="hidden" name="childages4" value="" />
				<input type="hidden" name="childages5" value="" />
				<input type="hidden" name="layout" value="" />
				<input type="hidden" name="points" value="" />
				<input type="hidden" name="newsearch" value="1" />
				<input type="hidden" name="limitstart" value="0" />
				<input type="hidden" name="filter_order" value="" />
				<input type="hidden" name="filter_order_Dir" value="" />
				<input type="hidden" name="searchtypetab" value="0" />
				<input type="hidden" name="searchId" value="<?php echo uniqid('', true)?>" />
				<input type="hidden" name="availabilitytype" value="<?php echo $currSearchParam->AvailabilityTypes ?>" />
				<input type="hidden" name="itemtypes" value="<?php echo $currSearchParam->ItemTypes ?>" />
				<input type="hidden" name="groupresulttype" value="<?php echo $currSearchParam->GroupResultType ?>" />
				<input type="hidden" name="GetBestGroupResult" value="1" />
				<input type="hidden" name="getallresults" value="0" />
				<input type="hidden" name="discountcodes" value="<?php echo ($currSearchParam->DiscountCode ?? "") ?>" />
				<input type="hidden" name="checkFullPeriod" value="<?php echo $currSearchParam->CheckFullPeriod ?>" />
				<input type="hidden" name="eventid" value="<?php echo $resource->EventId ?>" />

				<a href="javascript:void();" onclick="document.getElementById('searchServiceformeventsdetails').submit();"> <?php _e('Buy tickets', 'bfi') ?></a>
			</form>
</li>
<?php }
				    
				    break;
                default:
                    break;
	        
	}

}
?>
<!-- FINE BOTTONI -->
	
	</ul>

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
			bfi_get_template("shared/gallery_type2.php",array("merchant"=>null,"bfiSourceData"=>$bfiSourceData,"bfiImageData"=>$bfiImageData,"bfiVideoData"=>$bfiVideoData));	
	?>
	</div>

<div class="bfi-content bfi-content-event bfi-content-event<?php echo $resource->EventId ?>">
	<?php if (!empty($resourceDescription)){?>
    <div id="bfidescriptiontab">
		<div class="bfi-description-data bfi-shortenicon">
			<h4 class="bfi-title-content" ><?php echo  _e('Description', 'bfi') ?></h4>
			<?php echo $resourceDescription ?>		
		</div>
		<br />
    </div>
	<?php } ?>

<?php 
// modulo risultati ricerca
// ------- sospeso momentaneamente ----------

if (false) {
    
			$checkin = BFCHelper::parseStringDateTime($currFrom,'d/m/Y');
			$checkout = BFCHelper::parseStringDateTime($currTo,'d/m/Y');
			$checkin->setTime(0,0,0);
			$checkout->setTime(0,0,0);

			$currParamInSession = BFCHelper::getSearchParamsSession();
			$currParam = array(
				'searchid' => uniqid('', true),
				'searchtypetab' =>  '0',
				'newsearch' => 1,
				'getallresults' => 1,
				'checkin' => $checkin,
				'checkout' => $checkout,
				'duration' => $checkout->diff($checkin)->format('%a'),
				'checkFullPeriod' => $currSearchParam->CheckFullPeriod,
				'searchterm' => '',
				'searchTermValue' => '',
				'stateIds' => '',
				'regionIds' =>  '',
				'cityIds' =>  '',
				'zoneIds' =>  '',
				'merchantIds' =>  '',
				'groupresourceIds' =>  '',
				'merchantTagIds' => $currSearchParam->MrcTags,
				'productTagIds' => $currSearchParam->ResTags ,
				'groupTagsIds' => $currSearchParam->GrpTags,
				'paxages' => array(BFCHelper::$defaultAdultsAge, BFCHelper::$defaultAdultsAge),
				'masterTypeId' => $currSearchParam->ProductCategories,
				'merchantResults' => '',
				'resourcegroupsResults' => '',
				'merchantCategoryId' => $currSearchParam->MerchantCategories,
				'merchantId' => 0,
				'zoneId' =>  0,
				'availabilitytype' => $currSearchParam->AvailabilityTypes ,
				'itemtypes' => $currSearchParam->ItemTypes,
				'groupresulttype' => $currSearchParam->GroupResultType,
				'locationzone' => 0,
				'cultureCode' => $cultureCode,
				'minqt' =>  1,
				'maxqt' =>  10,
				'paxes' => $currSearchParam->MinPaxes,
				'tags' => '',
				'resourceName' => 0,
				'refid' =>0,
				'pricerange' => '',
				'onlystay' => $currSearchParam->CheckAvailability,
				'resourceId' => '',
				'extras' =>'',
				'packages' =>  '',
				'pricetype' => '',
				'filters' => '',
				'rateplanId' =>  '',
				'variationPlanId' =>  '',
				'gotCalculator' => '',
				'totalDiscounted' =>  '',
				'suggestedstay' =>'',
				'variationPlanIds' => '',
				'points' =>  '',
				'filter_order' => '',
				'filter_order_Dir' => '',
				'getBaseFiltersFor' => '',
				'groupResultType' => 0,
			);
			BFCHelper::setSearchParamsSession($currParam);			
			bfi_get_template("shared/results_details.php",array("resource"=>$resource,"eventId"=>$eventid,"discountcodes"=>($currSearchParam->DiscountCode ?? ""),"currencyclass"=>$currencyclass,"currSearchParam"=>$currSearchParam));	
}
?>

<?php
$showMerchantForm = false;
$merchant = null;
$routeMerchant ="";
if(!empty($resource->Organizer) && $resource->Organizer->InformationType != -1) { 
	if(!empty($resource->Organizer->MerchantId)) {
		$merchant = BFCHelper::getMerchantFromServicebyId($resource->Organizer->MerchantId);
		$routeMerchant = $url_merchant_page . $merchant->MerchantId.'-'.BFI()->seoUrl($merchant->Name);
		bfi_get_template("shared/merchant_small_details.php",array("merchant"=>$merchant, "routeMerchant"=>$routeMerchant));	
		$showMerchantForm = true;

	} else {
	$merchant = new stdClass;
	$merchant->id=0;
	$merchant->LogoUrl = $resource->Organizer->LogoUrl;
	$merchant->Name = $resource->Organizer->Name;
	$merchant->Rating = $resource->Organizer->Rating;
	$merchant->RatingSubValue = $resource->Organizer->RatingSubValue;
	$merchant->Address = $resource->Organizer->Address;
	$merchant->ZipCode = $resource->Organizer->ZipCode;
	$merchant->CityName = $resource->Organizer->CityName;
	$merchant->StateName = $resource->Organizer->StateName;
	$merchant->SiteUrl = $resource->Organizer->SiteUrl;
	$merchant->Phone = $resource->Organizer->Phone;
	$merchant->HasResources = false;
	$merchant->HasOnSellUnits = false;
	$merchant->RatingsContext =0;
	$routeMerchant = $merchant->SiteUrl;
	$merchant->AcceptanceCheckIn = "-";
	$merchant->AcceptanceCheckOut = "-";
	$merchant->OtherDetails = "";
	bfi_get_template("shared/merchant_small_details.php",array("merchant"=>$merchant, "routeMerchant"=>$routeMerchant));	
?>
<?php 
	} 
} 
?>

<?php 
// for all tags
					if(!empty( $resource->Tags)){
						foreach ( $resource->Tags as $gr ) {
							?>
							<div class="bfi-group-tags bfi-group-tags-<?php echo $gr->TagGroupId?>">
							<h3 class="bfi-description-sub-header"><?php echo $gr->Name ?>:</h3>
							<?php
							switch ($gr->LayoutType ) {
								case 0:
									$tagsList = implode(' ',array_filter(array_map(function ($i) { 
											$strHtml = '<span style="color:' . $i->SubGroupColor. '">';
												if (!empty($i->ImageUrl)) {
													$strHtml .= "<img src='" . BFCHelper::getImageUrlResized('tag',$i->ImageUrl, 'tag24') . "' alt='" . $i->Name . "' title='" . $i->Name . "' />";
													
												}else {
												   if (!empty($i->IconSrc)) {
															$currType = explode(';',$i->IconType);
															switch ($currType[0]) {
																case "fontawesome5":
																	$strHtml .= "<i class='" . $i->IconSrc . "'></i> ";
																	break;
																case "fontawesome4":
																default:
																	$strHtml .= "<i class='fa " . $i->IconSrc . "'></i> ";
															}
												   }else{
													   $strHtml .= "";
												   }
												
												}
											$strHtml .= $i->Name . '</span>';
											return $strHtml;
										}, $gr->Tags)));
//									$tagsList = implode(' ',array_filter(array_map(function ($i) { return '<span style="color:' . $i->SubGroupColor. '">' . (!empty($i->ImageUrl) ? "<img src='" . BFCHelper::getImageUrlResized('tag',$i->ImageUrl, 'tag24') . "' alt='" . $i->Name . "' title='" . $i->Name . "' />" : (!empty($i->IconSrc) ? "<i class='fa " . $i->IconSrc . "'></i> ":""))  . $i->Name . '</span>'; }, $gr->Tags)));
									?>
									<div class="bfi-facility-list"><?php echo $tagsList ?></div>		
									<?php 
									break;
								case 1:
									if (!empty($gr->SubGroupHighlight )) {
										foreach($gr->SubGroupHighlight  as $subgr) {
												$tagsList = implode(', ',array_filter(array_map(function ($i) { return '<span><i class="fa fa-check"></i>&nbsp;' . $i->Name . '</span>'; }, $subgr)));
												?>
												<div class="bfi-tags-highlight"><?php echo $tagsList ?></div>		
												<?php 
										}
									}	
									if (!empty($gr->subgrs )) {
										?>
										<div class="bfi-list-tags-container">
										<?php 
										
									}
									foreach($gr->subgrs  as $subgr) {
										?>
										<div class="bfi-list-tags">
											<h5>
												<?php echo $subgr[0]->SubGroupName ?>
												 <?php if (!empty($subgr[0]->SubGroupInEvidence))
												{
													echo '<span class="bfi-tag-inevidence">' . __('in evidence', 'bfi') . '</span>';
												}?>
											</h5>
											<?php
												foreach ($subgr as $tg ) {
							?>
													<div class="bfi-tags" >
														<?php 
														switch ($tg->TagValueType ) {
															case 1:
																switch ($tg->TagValue ) {
																	case '-1':
																		echo '<i class="fa fa-check"></i> '. $tg->Name . ' <span class="bfi-tag-excluding">' . __('excluding', 'bfi') . '</span>';
																	    break;
																	case '0':
																		echo '<i class="fa fa-check"></i> '. $tg->Name . ' <span class="bfi-tag-included">' . __('included', 'bfi') . '</span>';
																	    break;
																	case '1':
																		echo '<span class="bfi-tag-free">' .__('Free!', 'bfi') . '</span> ' . $tg->Name ;
																	    break;
																	case '2':
																		echo '<i class="fa fa-check"></i> '. $tg->Name . ' <span class="bfi-tag-additional">' . __('additional charge', 'bfi') . '</span>';
																	    break;
																	default:
																		echo '<i class="fa fa-check"></i> ' . $tg->Name ;
																	    break;
																}
//																if ($tg->TagValue == "1") {
//																	echo '<span class="bfi-tag-free">' .__('Free!', 'bfi') . '</span> ' . $tg->Name ;
//																}else if ($tg->TagValue == "2") {
//																	echo '<i class="fa fa-check"></i> '. $tg->Name . ' <span class="bfi-tag-additional">' . __('additional charge', 'bfi') . '</span>';
//																}
																break;
															default:
																echo '<i class="fa fa-check"></i> ' . $tg->Name ;
																
														}
														?>
													</div>					    
													<?php 
												}
										?>
										</div>					    
										<?php 
									}
									if (!empty($gr->subgrs )) {
										?>
										</div>
										<?php 
									}
								break;
							}
							?>
							</div>					    
							<?php 
						}
					}
?>
<?php if (!empty($resourceAttachments) && count($resourceAttachments)>0) {
	?>
	<div  class="bfi-attachmentfiles">
		<div class="bfi-download-title"><?php _e('Download', 'bfi') ?></div>
	<?php 
	foreach ($resourceAttachments as $keyAttachment=> $resourceAttachment) {
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
<?php
}
?>										
<?php 
if($showMerchantForm){
	?>
	<div class="bfi-clearfix bfi-hr-separ"></div>
	<!-- form request -->
		<?php 
		$layout = get_query_var( 'bfi_layout', '' );
		$currentView = '';
		$orderType = "a";
		$task = "sendContact";
//		$routeThanks = $routeMerchant .'/'._x('thanks', 'Page slug', 'bfi' );
//		$routeThanksKo = $routeMerchant .'/'._x('errors', 'Page slug', 'bfi' );
		$routeThanks = $routeMerchant .'/thanks';
		$routeThanksKo = $routeMerchant .'/errors';

		$paramRef = array(
			"merchant"=>$merchant,
			"layout"=>$layout,
			"currentView"=>$currentView,
			"resource"=>null,
			"task"=>$task,
			"orderType"=>$orderType,
			"routeThanks"=>$routeThanks,
			"routeThanksKo"=>$routeThanksKo,
			);
		bfi_get_template("/shared/infocontact.php",$paramRef);	
}

?>
</div>


<!-- LISTA DATE -->
<?php 
if($dateCount > 1 ) {
	$currIndex = 0;
?>
	<div id="bfi-otherdatesevents" style="display:none;">
	<?php 
		$listEvents = array();
		foreach($resource->Dates as $item) {
			if(!array_key_exists($item->EventId, $listEvents)){
				$listEvents[$item->EventId] = array();
			}
			$listEvents[$item->EventId][] = $item;

		}
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
						   <div class="bfi-address" style="cursor:pointer;" onclick="bookingfor.createSingleEventMap('bfi-map-single-event-<?php echo $currIndex ?>', <?php echo $itm->XPos ?>, <?php echo $itm->YPos ?>, <?php echo $startzoom ?>,'<?php echo date_i18n('D',$currStartDate->getTimestamp()) ?>','<?php echo $currStartDate->format("d") ?>','<?php echo date_i18n('M',$currStartDate->getTimestamp()) ?>')"><i class="fa fa-map-marker fa-1"></i> <?php echo $itm->Address ?>, <?php echo $itm->ZipCode ?> <?php echo $itm->CityName ?> <?php echo $itm->StateName ?></div>
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
<?php
	} 
?>
	<?php if ($showResourceMap){?>
		<div id="markerInfo<?php echo $resource->EventId ?>" style="display:none">
					<div class="bfi-merchant-simple-title"><?php _e('In short', 'bfi') ?>:</div>
					<div class="bfi-row ">
						<div class="bfi-col-md-5 bfi-vcard-logo-box">
							<div class="bfi-vcard-logo"><img src="<?php echo $imgPopup?>" /></div>	
						</div>
						<div class="bfi-col-md-7 bfi-pad0-10">
							<div class="bfi-vcard-name">
								<?php echo  $resourceName?>
							</div>
							<div class=" bfi-street-address-block">
									<span class="bfi-street-address"><?php echo $indirizzo ?></span>, <span class="postal-code "><?php echo $cap ?></span> <span class="locality"><?php echo $comune ?></span> <span class="state">, <?php echo $stato ?></span><br />
							</div>
						</div>
					</div>			
			</div>
		</div>
	<?php } ?>

<!-- FINE LISTA DATE -->
<?php if (($showResourceMap)) {
	$val= new StdClass;
	$val->Id = $resource->EventId ;
	$val->Lat = $resourceLat;
	$val->Long = $resourceLon;
	$val->MarkerType = 2;  //tipo marker 2= eventi con date
	$val->NameDay = date_i18n('l',$startDate->getTimestamp());
	$val->Day =  $startDate->format("d");
	$val->Month = date_i18n('M',$startDate->getTimestamp());
	$listResourceMaps[] = $val;
?>
<script type="text/javascript">
<!--
	var listResourceMaps = <?php echo json_encode($listResourceMaps) ?>;	
//-->
</script>
<?php  } ?>
