<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$merchant = $resource->Merchant;
$base_url = get_site_url();
$language = $GLOBALS['bfi_lang'];
$languageForm ='';
if(defined('ICL_LANGUAGE_CODE') &&  class_exists('SitePress')){
		global $sitepress;
		if($sitepress->get_current_language() != $sitepress->get_default_language()){
			$languageForm = "/" .ICL_LANGUAGE_CODE;
		}
}
$merchantdetails_page = get_post( bfi_get_page_id( 'merchantdetails' ) );
$url_merchant_page = get_permalink( $merchantdetails_page->ID );
$routeMerchant = $url_merchant_page . $merchant->MerchantId.'-'.BFI()->seoUrl($merchant->Name);

$currencyclass = bfi_get_currentCurrency();

$accommodationdetails_page = get_post( bfi_get_page_id( 'resourcegroupdetails' ) );
$url_resource_page = get_permalink( $accommodationdetails_page->ID );
$resourceName = BFCHelper::getLanguage($resource->Name, $GLOBALS['bfi_lang'], null, array('ln2br'=>'ln2br', 'striptags'=>'striptags')); 
$merchantName = BFCHelper::getLanguage($merchant->Name, $GLOBALS['bfi_lang'], null, array('ln2br'=>'ln2br', 'striptags'=>'striptags')); 
$resourceDescription = BFCHelper::getLanguage($resource->Description, $GLOBALS['bfi_lang'], null, array('striptags'=>'striptags', 'bbcode'=>'bbcode','ln2br'=>'ln2br'));
$resourceDescriptionSeo = BFCHelper::getLanguage($resource->Description, $GLOBALS['bfi_lang'], null, array('ln2br'=>'ln2br', 'bbcode'=>'bbcode', 'striptags'=>'striptags'));
$uri = $url_resource_page.$resource->CondominiumId.'-'.BFI()->seoUrl($resourceName);

$isportal = COM_BOOKINGFORCONNECTOR_ISPORTAL;
$showdata = COM_BOOKINGFORCONNECTOR_SHOWDATA;

$posx = COM_BOOKINGFORCONNECTOR_GOOGLE_POSX;
$posy = COM_BOOKINGFORCONNECTOR_GOOGLE_POSY;
$startzoom = COM_BOOKINGFORCONNECTOR_GOOGLE_STARTZOOM;
$googlemapsapykey = COM_BOOKINGFORCONNECTOR_GOOGLE_GOOGLEMAPSKEY;


$layout = get_query_var( 'bfi_layout', '' );
$currentView = '';
$orderType = "a";
$task = "sendContact";
//$routeThanks = $routeMerchant .'/'._x('thanks', 'Page slug', 'bfi' );
//$routeThanksKo = $routeMerchant .'/'._x('errors', 'Page slug', 'bfi' );
$routeThanks = $routeMerchant .'/thanks';
$routeThanksKo = $routeMerchant .'/errors';


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


$resourceLat = null;
$resourceLon = null;

if (!empty($resource->XGooglePos) && !empty($resource->YGooglePos)) {
	$resourceLat = $resource->XGooglePos;
	$resourceLon = $resource->YGooglePos;
}
if(!empty($resource->XPos)){
	$resourceLat = $resource->XPos;
}
if(!empty($resource->YPos)){
	$resourceLon = $resource->YPos;
}
if(empty($resourceLat) && !empty($merchant->XPos)){
	$resourceLat = $merchant->XPos;
}
if(empty($resourceLon) && !empty($merchant->YPos)){
	$resourceLon = $merchant->YPos;
}

$showMap = (($resourceLat != null) && ($resourceLon !=null) );
if ($showMap && COM_BOOKINGFORCONNECTOR_USE_OPENSTREETMAP) {
	wp_enqueue_script('bfileaflet');
	wp_enqueue_script('bfileafletcontrolcustom');
}
$htmlmarkerpoint = "&markers=color:blue%7C" . $resourceLat . "," . $resourceLon;


$indirizzo = isset($resource->Address)?$resource->Address:"";
$cap = isset($resource->ZipCode)?$resource->ZipCode:""; 
$comune = isset($resource->CityName)?$resource->CityName:"";
$stato = isset($resource->StateName)?$resource->StateName:"";

$merchantRules = "";
if(isset($merchant->Rules)){
	$merchantRules = BFCHelper::getLanguage($merchant->Rules, $language, null, array('ln2br'=>'ln2br', 'striptags'=>'striptags'));
}
$resourceRules = "";
if(isset($resource->Rules)){
	$resourceRules = BFCHelper::getLanguage($resource->Rules, $language, null, array('ln2br'=>'ln2br', 'striptags'=>'striptags'));
}

$services = [];
if (!empty($resource->ServiceIdList)){
	$services=BFCHelper::GetServicesByIds($resource->ServiceIdList, $language);
}

$resourceRoute = $uri;
$routeRating = $uri.'/'._x('review', 'Page slug', 'bfi' );
$routeInfoRequest = $uri.'/'._x('inforequestpopup', 'Page slug', 'bfi' );
$routeRapidView = $uri.'/'._x('rapidview', 'Page slug', 'bfi' );

$searchedRequest =  array(
	'pricetype' => BFCHelper::getStayParam('pricetype'),
	'rateplanId' => BFCHelper::getStayParam('rateplanId'),
	'variationPlanId' => BFCHelper::getStayParam('variationPlanId'),
	'state' => BFCHelper::getStayParam('state'),
	'gotCalculator' => isset($_REQUEST['calculate'])?$_REQUEST['calculate']:''
);

$ProductAvailabilityType = "0";

$fromSearch =  BFCHelper::getVar('fromsearch','0');

$routeSearch = $uri;
if(!empty($fromSearch)){
	$routeSearch .= "/?task=getMerchantResources&fromsearch=1";
}else{
	$routeSearch .= "/?task=getMerchantResources";
}

$reviewavg = 0;
$reviewcount = 0;
$showReview = false;
$resource->IsCatalog = false;
$resource->MaxCapacityPaxes = 0;
$resource->MinCapacityPaxes = 0;
//$resource->TagsIdList = "";


$tagsHighlight = array();
if (!empty($resource->Tags)) {
	foreach ($resource->Tags as $gr) {
		//$gr->Tags = json_decode($gr->TagsString);
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
$imgPopup = COM_BOOKINGFORCONNECTOR_DEFAULTIMAGE;
if (!empty($resource->DefaultImg)){
	$imgPopup =  BFCHelper::getImageUrlResized('resourcegroup',$resource->DefaultImg, 'logomedium');
}

//if ($merchant->RatingsContext != NULL && $merchant->RatingsContext > 0) {
//	$showReview = true;
//	if ($merchant->RatingsContext ==1 && !empty($merchant->Avg)) {
//		$reviewavg =  isset($merchant->Avg) ? $merchant->Avg->Average : 0;
//		$reviewcount =  isset($merchant->Avg) ? $merchant->Avg->Count : 0;
//	}
//	if ($merchant->RatingsContext ==2 || $merchant->RatingsContext ==3 ) {
//		$summaryRatings = $model->getRatingAverageFromService($merchant->MerchantId,$resource->ResourceId);
//		if(!empty($summaryRatings)){
//			$reviewavg = $summaryRatings->Average;
//			$reviewcount = $summaryRatings->Count;
//		}
//	}
//}

$payloadresource["@type"] = "Product";
$payloadresource["@context"] = "http://schema.org";
$payloadresource["name"] = $resourceName;
$payloadresource["description"] = $resourceDescriptionSeo;
$payloadresource["url"] = $resourceRoute; 
if (!empty($resource->DefaultImg)){
	$payloadresource["image"] = "https:".BFCHelper::getImageUrlResized('resourcegroup',$resource->DefaultImg, 'logobig');
}
?>
<script type="application/ld+json">
<?php echo json_encode($payloadresource); ?>
</script>
<?php 
$payload["@type"] = "Organization";
$payload["@context"] = "http://schema.org";
$payload["name"] = $merchant->Name;
$payload["description"] = BFCHelper::getLanguage($merchant->Description, $language, null, array( 'striptags'=>'striptags', 'bbcode'=>'bbcode','ln2br'=>'ln2br'));
$payload["url"] = ($isportal)? $routeMerchant: $base_url; 
if (!empty($merchant->LogoUrl)){
	$payload["logo"] = "https:".BFCHelper::getImageUrlResized('merchant',$merchant->LogoUrl, 'logobig');
}
?>
<script type="application/ld+json">
<?php echo json_encode($payload); ?>
</script>


<div class="bfi-content bfi-content-rescat bfi-content-rescat<?php echo $resource->MainCategoryId ?> bfi-details-resourcegroup ">	
	<div class="bfi-menu-top-container">
		<ul class="bfi-menu-top">
			<?php if ($merchant->HasResources){?><li><a rel="#divcalculator" data-toggle="tab"><?php echo  _e('Info & prices' , 'bfi') ?></a></li><?php } ?>
			<?php if (!empty( $merchant->Tags)){?><li><a rel=".bfi-facilities"><?php echo  _e('Facilities', 'bfi') ?></a></li><?php } ?>
			<li><a rel=".bfi-merchant-inshort"><?php echo  _e('House rules', 'bfi') ?></a></li>
			<?php if ((false && $showMap)) {?><li><a rel="#bfimaptab" class="bfiopenpopupmap"><?php echo _e('Map' , 'bfi') ?></a></li><?php } ?>
			<?php if(false && !COM_BOOKINGFORCONNECTOR_DISALBLEINFOFORM){ ?><li class="bfi-request"><a rel=".bfi-form-contacts" data-toggle="tab"><?php echo  _e('Request' , 'bfi') ?></a></li><?php } ?>
			<?php if ($isportal && ($merchant->RatingsContext ==1 || $merchant->RatingsContext ==3)){?><li class="bfi-hidden-xs bfi-hidden-sm"><a class="bfi-panel-toggle"><?php echo  _e('Reviews' , 'bfi') ?> <?php echo ($reviewcount>0 ? " (".$reviewcount.")": ""); ?></a></li><?php } ?>
		</ul>
	</div>
	<div class="bfi-clearfix"></div>
	
	<?php if($reviewcount>0){ ?>
	<div class="bfi-row bfi-margin-bottom10">
		<div class="bfi-col-md-10">
	<?php }
if(empty($reviewcount)){
		?><div class="bfi-pull-right">
			<div class="bfi-pull-right bfi-btn-book-top">
				<a rel="#divcalculator" class="bfi-btn bfi-alternative bfi-btn-calc"><?php echo _e('Book now','bfi'); ?></a>
			</div> 
			<div class="bfi-pull-right">
				<?php 
					$favoriteModel = array(
						"ItemId"=>$resource->CondominiumId,
						"ItemName"=>BFCHelper::string_sanitize($resourceName),
						"ItemType"=>6,
						"ItemURL"=>BFCHelper::bfi_get_curr_url(),
						"WrapToContainer"=>1,
						);
					bfi_get_template("shared/favorite_icon.php",$favoriteModel);	
				?>
			</div> 
			<div class="bfi-addtoany bfi-pull-right">
				<!-- AddToAny BEGIN -->
				<a class="a2a_dd" href="https://www.addtoany.com/share "title="<?php _e('Share', 'bfi') ?>" ><i class="far fa-share-alt"></i></a>
				<script async src="https://static.addtoany.com/menu/page.js"></script>
				<!-- AddToAny END -->
			</div>	
		</div>	
<?php 
}
?>
			<div class="bfi-title-name bfi-hideonextra"><?php echo  $resourceName?>
						<span class="bfi-item-rating">
<?php 
				$ratingModel = array(
					"ratingModel"=>$resource
				);
				bfi_get_template("shared/stars_rating.php",$ratingModel);
?>
						</span>
						<?php if($isportal){ ?> - <span class="bfi-cursor"><?php echo  $merchantName?></span><?php } ?>
			</div>
			<div class="bfi-address bfi-hideonextra">
				<?php if (($showMap)) {?><a class="bfi-map-link bfiopenpopupmap" rel="#bfimaptab"><?php } ?><i class="fa fa-map-marker fa-1"></i><?php if (($showMap)) {?></a><?php } ?> <span class="street-address"><?php echo $indirizzo ?></span>, <span class="postal-code "><?php echo  $cap ?></span> <span class="locality"><?php echo $comune ?></span>, <span class="region"><?php echo  $stato ?></span>
				<?php if(isset($resource->CenterDistance)) { ?>
					<span class="bfi-centerdistance" data-toggle="tooltip" title="<?php _e('This is the straight-line distance on the map. Actual travel distance may vary.', 'bfi') ?>">(<i class="fa fa-road" aria-hidden="true"></i> <?php echo BFCHelper::formatDistanceUnits($resource->CenterDistance)?> <?php _e('from centre', 'bfi') ?>)</span>
				<?php } ?> <?php if (($showMap)) {?>- <a class="bfi-map-link bfiopenpopupmap" rel="#bfimaptab"><?php } ?><?php _e('show map', 'bfi') ?><?php if (($showMap)) {?></a><?php } ?>
			</div>
	<?php if($reviewcount>0){ 
		$totalreviewavg = BFCHelper::convertTotal($reviewavg);
		?>
		</div>	
		<div class="bfi-col-md-2">
			<div class="bfi-cursor bfi-avg bfi-pull-right" id="bfi-avgreview" style="display:inline-block;margin-left:10px;">
				<a href="#bfi-rating-container" class="bfi-avg-value"><?php echo $rating_text['merchants_reviews_text_value_'.$totalreviewavg]; ?> <?php echo number_format($reviewavg, 1); ?></a><br />
				<a href="#bfi-rating-container" class="bfi-avg-count"><?php echo $reviewcount; ?> <?php _e('Reviews', 'bfi') ?></a>
			</div>	
			<div class="bfi-addtoany bfi-pull-right">
				<!-- AddToAny BEGIN -->
				<a class="a2a_dd" href="https://www.addtoany.com/share "title="<?php _e('Share', 'bfi') ?>" ><i class="far fa-share-alt"></i></a>
				<script async src="https://static.addtoany.com/menu/page.js"></script>
				<!-- AddToAny END -->
			</div>	
<?php 
				$favoriteModel = array(
					"ItemId"=>$resource->ResourceId,
					"ItemName"=>BFCHelper::string_sanitize($resourceName),
					"ItemType"=>1,
					"ItemURL"=>BFCHelper::bfi_get_curr_url(),
					"WrapToContainer"=>1,
					);
				bfi_get_template("shared/favorite_icon.php",$favoriteModel);	
?>
		</div>	
	</div>	
	<?php } ?>
</div>

<div class="bfi-resourcecontainer-gallery bfi-hideonextra">
	<?php  
			$bfiSourceData = 'resourcegroup';
			$bfiImageData = null;
			$bfiVideoData = null;
			if(!empty($resource->ImagesData)) {
				$bfiImageData = $resource->ImagesData;
			}
			if(!empty($resource->VideoData)) {
				$bfiVideoData = $resource->VideoData;
			}
			bfi_get_template("shared/gallery_type2.php",array("merchant"=>$merchant,"bfiSourceData"=>$bfiSourceData,"bfiImageData"=>$bfiImageData,"bfiVideoData"=>$bfiVideoData));	
	?>
</div>
<?php 
// icon tag Highlight
	if(!empty( $tagsHighlight) || (isset($resource->Area) && $resource->Area>0  ) ){
		?>
		<div class="bfi-group-tags-icons bfi-hideonextra">
		<?php 
		if(isset($resource->Area) && $resource->Area>0  ){ 
		?>
				<div class="bfi-group-tags-icons-item">
					<div class="bfi-group-tags-icons-item-value">
						<?php echo $resource->Area ?> m&sup2;
					</div>
					<div class="bfi-group-tags-icons-item-label">
						<?php echo __('Floor area', 'bfi') ?>
					</div>
				</div>
		<?php 
		}
		foreach($tagsHighlight as $tagHighlight) {
			if (!empty($tagHighlight->IconSrc)) {
			    
		?>
				<div class="bfi-group-tags-icons-item">
					<div class="bfi-group-tags-icons-item-value">
							<?php 
							$currType = explode(';',$tagHighlight->IconType);
							switch ($currType[0]) {
								case "fontawesome5":
									?>
										<i class='<?php echo $tagHighlight->IconSrc ?>'></i> 
									<?php
									break;
								case "fontawesome4":
								default:
									?>
										<i class='fa <?php echo $tagHighlight->IconSrc ?>'></i> 
									<?php
							}
							?>
					</div>
					<div class="bfi-group-tags-icons-item-label">
						<?php echo $tagHighlight->Name ?>
					</div>
				</div>
		<?php 
			} //end if	
		}	//end foreach				
		?> 
			</div>	
		<?php 
	}					

?>


<div class="bfi-content bfi-content-rescat bfi-content-rescat<?php echo $resource->MainCategoryId ?> bfi-details-resourcegroup ">	
<?php 
$fromSearch =  BFCHelper::getVar('fromsearch','0');

$first_display_tab = 'bfidescriptiontab';
if(!empty($fromSearch)){
	$first_display_tab = 'bfiavailabilitytab';
}
?>
	<div id="navbookingfordetails" class="">
<!--// description-->

	<?php if (!empty($resourceDescription)){?>
		<div id="bfidescriptiontab" class="bfi-hideonextra">
			<div class="bfi-row bfi-row-background">
				<div class="bfi-col-md-12">
					<div class="bfi-description-data bfi-shortenicon">
						<h4 class="bfi-title-content" ><?php echo  _e('Description', 'bfi') ?></h4>
						<?php echo $resourceDescription ?><br /><br />
					</div>
					<?php
// for evidence
					if(!empty( $tagsHighlight)){
						$tagsList = implode(', ',array_filter(array_map(function ($i) { return '<i class="fa fa-check"></i>&nbsp;' . $i->Name; }, $tagsHighlight)));
						?>
							<div class="bfi-group-tags">
							<h3 class="bfi-description-sub-header"><?php echo __('in evidence', 'bfi') ?>:</h3>
								<div class="bfi-tags-highlight"><?php echo $tagsList ?></div>
							</div>	
						<?php 
					}					
					?>
				</div>	
			</div>	
		</div>
	<?php } ?>
	
	<?php if(!$resource->IsCatalog){ ?>
		<div id="bfiavailabilitytab">
<?php 
//se c'è una vendita per mappa
if($resource->HasSelectionMap) { 

?>
	<?php if ($showMap){?>
			<!-- calc -->
			<a name="calc"></a>
			<div id="divcalculator">
				<?php 
				$resourceId = 0;
				$resourcegroupId = $resource->CondominiumId;
				$refreshSearch = 0;
				if (!empty($fromSearch)) {
					$refreshSearch = 1;				    
				}
				bfi_get_template("shared/search_mapsells.php",array("resource"=>$resource, "mapConfiguration"=>$resource->MapConfiguration,"resourceLat"=>$resourceLat,"resourceLon"=>$resourceLon,"merchant"=>$merchant,"resourceId"=>$resourceId,"resourcegroupId"=>$resourcegroupId,"currencyclass"=>$currencyclass,"refreshSearch"=>$refreshSearch));	
				?>
			</div>
	<?php } ?>	

<?php
// prenotazione classica	
} else{ ?>
		<?php
		
			$currPackage = BFCHelper::getVar('bfipck');
			if(!empty($currPackage)){
				$currPackage= json_decode(gzinflate(hex2bin($currPackage)));
				$resourceId = 0;
				$resourcegroupId = 0;
				bfi_get_template('shared/package_details.php',array("currPackage"=>$currPackage,"merchant"=>$merchant,"resourceId"=>$resourceId,"resourcegroupId"=>$resource->CondominiumId,"currencyclass"=>$currencyclass));	
			}else{
			?>
			<!-- calc -->
			<a name="calc"></a>
			<div id="divcalculator">
				<?php 
				$refreshSearch = 0;
				$currencyclass = bfi_get_currentCurrency();
				bfi_get_template("shared/search_details.php",array("resource"=>$resource,"merchant"=>$merchant,"resourceId"=>0,"resourcegroupId"=>$resource->CondominiumId,"currencyclass"=>$currencyclass,"refreshSearch"=>$refreshSearch));	
				?>
			</div>
			<?php 
			} ?>
<?php } ?>	
			
			</div>
	<?php } ?>
	<!--// RatingsContext-->
	<?php if ($showReview) {?>
        <div id="bfiratingslisttab">
			<div class="bfi-slide-panel">
				<span class="bfi-panel-close bfi-panel-toggle"></span>
					<h2><?php echo  _e('Reviews' , 'bfi') ?></h2>
					<div class="bfi-ratingslist bfi-hideonextra">
					<?php
						$summaryRatings = 0;
						$ratings = null;
						if ($merchant->RatingsContext ==1){
							$summaryRatings = $merchant->Avg;
							$ratings = BFCHelper::getMerchantRatings(0,5,$merchant->MerchantId,$language,1);

						}
						/*else{
							//$summaryRatings = $resource->Avg;
							$summaryRatings = BFCHelper::getResourceRatingAverage($merchant->MerchantId,$resource->CondominiumId);
							$ratings = BFCHelper::getResourceRating(0,5,$resource->CondominiumId);
						}
						*/
						if ( false !== ( $temp_message = get_transient( 'temporary_message' ) )){
							echo $temp_message;
							delete_transient( 'temporary_message' );
						}
					?>
						<?php  bfi_get_template('resourcedetails/resource-ratings.php',array("merchant"=>$merchant,"summaryRatings"=>$summaryRatings,"ratings"=>$ratings,"routeMerchant"=>$routeMerchant,"routeRating"=>$routeRating,"rating_text"=>$rating_text ));  ?>
					</div>
			</div>
		</div>
	<?php } ?>

	<?php if ($showMap){?>
		<div id="markerInfo<?php echo $resource->CondominiumId ?>" style="display:none">
					<div class="bfi-merchant-simple-title"><?php _e('In short', 'bfi') ?>:</div>
					<div class="bfi-row ">
						<div class="bfi-col-md-5 bfi-vcard-logo-box">
							<div class="bfi-vcard-logo"><img src="<?php echo $imgPopup?>" /></div>	
						</div>
						<div class="bfi-col-md-7 bfi-pad0-10">
							<div class="bfi-vcard-name">
								<span class="bfi-item-rating">
<?php 
				$ratingModel = array(
					"ratingModel"=>$resource
				);
				bfi_get_template("shared/stars_rating.php",$ratingModel);
?>
								</span>
								<?php echo  $resource->Name?>
							</div>
							<div class=" bfi-street-address-block">
									<span class="bfi-street-address"><?php echo $indirizzo ?></span>, <span class="postal-code "><?php echo $cap ?></span> <span class="locality"><?php echo $comune ?></span> <span class="state">, <?php echo $stato ?></span><br />
							</div>
						</div>
					</div>			
			</div>
	<?php } ?>

			<div class=" bfi-feature-data bfi-hide">
				<strong><?php _e('In short', 'bfi') ?></strong>
				<div class="bfiresourcetags" id="bfitags" rel="<?php echo $resource->TagsIdList ?>"></div>
				<?php if(isset($resource->Area) && $resource->Area>0  ){ ?><?php _e('Floor area', 'bfi') ?>: <?php echo $resource->Area ?> m&sup2; <br /><?php } ?>
				<?php if ($resource->MaxCapacityPaxes>0){?>
					<br />
					<?php if ($resource->MinCapacityPaxes<$resource->MaxCapacityPaxes){?>
						<?php _e('Min paxes', 'bfi') ?>: <?php echo $resource->MinCapacityPaxes ?><br />
					<?php } ?>
					<?php _e('Max paxes', 'bfi') ?>: <?php echo $resource->MaxCapacityPaxes ?><br />
				<?php } ?>
				<?php if((isset($resource->EnergyClass) && $resource->EnergyClass>0 ) || (isset($resource->EpiValue) && $resource->EpiValue>0 ) ){ ?>
				<!-- Table Details --><br />	
				<table class="bfi-table bfi-table-striped bfi-resourcetablefeature">
					<tr>
						<?php if(isset($resource->EnergyClass) && $resource->EnergyClass>0){ ?>
						<td class="bfi-col-md-"><?php _e('Energy Class', 'bfi'); ?>:</td>
						<td class="bfi-col-md-3" <?php if(!isset($resource->EpiValue)) {echo "colspan=\"3\"";}?>>
							<div class="bfi-energyClass bfi-energyClass<?php echo $resource->EnergyClass?>">
							<?php 
								switch ($resource->EnergyClass) {
									case 0: echo __('not set', 'bfi'); break;
									case 1: echo __('nondescript', 'bfi'); break;
									case 2: echo __('free property', 'bfi'); break;
									case 3: echo __('Under evaluation', 'bfi'); break;
									case 100: echo __('A+', 'bfi'); break;
									case 101: echo __('A', 'bfi'); break;
									case 102: echo __('B', 'bfi'); break;
									case 103: echo __('C', 'bfi'); break;
									case 104: echo __('D', 'bfi'); break;
									case 105: echo __('E', 'bfi'); break;
									case 106: echo __('F', 'bfi'); break;
									case 107: echo __('G', 'bfi'); break;
									case 108: echo "A1"; break;
									case 109: echo "A2"; break;
									case 110: echo "A3"; break;
								}
							?>
							</div>
						</td>
						<?php } ?>
						<?php if(isset($resource->EpiValue) && $resource->EpiValue>0){ ?>
						<td class="bfi-col-md-"><?php _e('EPI Value', 'bfi'); ?>:</td>
						<td class="bfi-col-md-" <?php if(!isset($resource->EnergyClass)) {echo "colspan=\"3\"";}?>><?php echo $resource->EpiValue?> <?php echo $resource->EpiUnit?></td>
						<?php } ?>
					</tr>
				</table>
				<?php } ?>
				<?php if(isset($resource->AttachmentsString) && !empty($resource->AttachmentsString)){
					?>
					<div  class="bfi-attachmentfiles">
					<?php 
								
					$resourceAttachments = json_decode($resource->AttachmentsString);
					
					foreach ($resourceAttachments as $keyAttachment=> $resourceAttachment) {
						if ($keyAttachment>COM_BOOKINGFORCONNECTOR_MAXATTACHMENTFILES) {
							break;
						}
						$resourceAttachmentName = $resourceAttachment->Name;
						$resourceAttachmentExtension= "";
						
						$path_parts = pathinfo($resourceAttachmentName);
						if(!empty( $path_parts['extension'])){
							$resourceAttachmentExtension = $path_parts['extension'];
							$resourceAttachmentName =  str_replace(".".$resourceAttachmentExtension, "", $resourceAttachmentName);
						}
						$resourceAttachmentIcon = bfi_get_file_icon($resourceAttachmentExtension);
						?>
						<?php echo $resourceAttachmentIcon ?> <a href="<?php echo $resourceAttachment->LinkValue ?>" target="_blank"><?php echo $resourceAttachmentName ?></a><br />
						<?php 
					}
				?>
					</div>
				<?php } ?>
			</div>
	
	

</div>
	<div class="bfi-clearboth"></div>
	<?php bfi_get_template('shared/tagslist.php',array("tags"=>$resource->Tags));  ?>
	<?php if(isset($resource->AttachmentsString) && !empty($resource->AttachmentsString)){
		$resourceAttachments = json_decode($resource->AttachmentsString);
		if (!empty($resourceAttachments) && count($resourceAttachments)>0) {
		?>
		<div  class="bfi-attachmentfiles bfi-hideonextra">
			<div class="bfi-download-title"><?php _e('Download', 'bfi') ?></div>
		<?php 
		foreach ($resourceAttachments as $keyAttachment=> $resourceAttachment) {
	//							if ($keyAttachment>COM_BOOKINGFORCONNECTOR_MAXATTACHMENTFILES) {
	//								break;
	//							}
			$resourceAttachmentName = $resourceAttachment->Name;
			$resourceAttachmentExtension= "";
			
			$path_parts = pathinfo($resourceAttachmentName);
			if(!empty( $path_parts['extension'])){
				$resourceAttachmentExtension = $path_parts['extension'];
				$resourceAttachmentName =  str_replace(".".$resourceAttachmentExtension, "", $resourceAttachmentName);
			}
	//							$resourceAttachmentIcon = bfi_get_file_icon($resourceAttachmentExtension);
			?>
				<span class="bfi-download-name"><a href="<?php echo $resourceAttachment->LinkValue ?>" target="_blank"><?php echo $resourceAttachmentName ?></a></span>
			<?php 
		}
	?>
		</div>
	<?php
		}
	}
	?>										
	<?php  bfi_get_template('shared/merchant_small_details.php',array("merchant"=>$merchant,"routeMerchant"=>$routeMerchant));  ?>	
<!-- form request -->
    <?php 
				$paramRef = array(
					"merchant"=>$merchant,
					"layout"=>$layout,
					"currentView"=>$currentView,
					//"resource"=>$resource,
					"task"=>$task,
					"orderType"=>$orderType,
					"routeThanks"=>$routeThanks,
					"routeThanksKo"=>$routeThanksKo,
					"otherDetails"=>$resourceName,
					);
				bfi_get_template("/shared/infocontact.php",$paramRef);	
	?>
	
	
	
<script type="text/javascript">
<!--
jQuery(function($) {
	if (typeof bfiTooltip  !== "function") {
		jQuery.widget.bridge('bfiTooltip', jQuery.ui.tooltip );
	}
	jQuery('[data-toggle="tooltip"]').bfiTooltip({
		position : { my: 'center bottom', at: 'center top-10' },
		tooltipClass: 'bfi-tooltip bfi-tooltip-top '
	}); 
	<?php if(isset($_REQUEST["fromsearch"])){ ?>	
		jQuery('html, body').animate({scrollTop: jQuery("#bfiavailabilitytab").offset().top}, 1000);
	<?php }  ?>	
});

jQuery(document).ready(function() {
	var newMerchant = {
		id:'<?php echo $resource->CondominiumId ?>',
		name:'<?php echo  BFCHelper::escapeJavaScriptText($payload["name"]) ?>',
		img:'<?php echo (!empty($payload["logo"])) ?$payload["logo"]:""; ?>',
		url: '<?php echo $payload["url"] ?>'
	}
	bookingfor.bfiAddMerchant(newMerchant);
});
<?php if (($showMap)) {
	$val= new StdClass;
	$val->Id = $resource->CondominiumId;
	$val->Lat = $resourceLat;
	$val->Long = $resourceLon;
	$val->MarkerType = 0;  //tipo marker 2= eventi con date
	$val->CssClass = "bfi-gps-ring";
	$listResourceMaps[] = $val;
?>
	var listResourceMaps = <?php echo json_encode($listResourceMaps) ?>;

<?php  } ?>

//-->
</script>
