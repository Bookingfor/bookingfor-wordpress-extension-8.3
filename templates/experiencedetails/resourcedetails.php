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
$merchant = $resource->Merchant;
//reset filter
BFCHelper::setFirstFilterDetailsParamsSession(null,"mrc".$merchant->MerchantId);
$merchantdetails_page = get_post( bfi_get_page_id( 'merchantdetails' ) );
$url_merchant_page = get_permalink( $merchantdetails_page->ID );
$routeMerchant = $url_merchant_page . $merchant->MerchantId.'-'.BFI()->seoUrl($merchant->Name);

$layout = get_query_var( 'bfi_layout', '' );
$currentView = 'resource';
$orderType = "c";
$task = "sendInforequest";
//$routeThanks = $routeMerchant .'/'._x('thanks', 'Page slug', 'bfi' );
//$routeThanksKo = $routeMerchant .'/'._x('errors', 'Page slug', 'bfi' );
$routeThanks = $routeMerchant .'/thanks';
$routeThanksKo = $routeMerchant .'/errors';

$experiencedetails_page = get_post( bfi_get_page_id( 'experiencedetails' ) );
$url_resource_page = get_permalink( $experiencedetails_page->ID );
$resourceName = BFCHelper::getLanguage($resource->Name, $GLOBALS['bfi_lang'], null, array('ln2br'=>'ln2br', 'striptags'=>'striptags')); 
$merchantName = BFCHelper::getLanguage($merchant->Name, $GLOBALS['bfi_lang'], null, array('ln2br'=>'ln2br', 'striptags'=>'striptags')); 
$resourceDescription = BFCHelper::getLanguage($resource->Description, $GLOBALS['bfi_lang'], null, array('striptags'=>'striptags', 'bbcode'=>'bbcode','ln2br'=>'ln2br'));
$resourceTermOfUse = BFCHelper::getLanguage($resource->TermOfUse, $GLOBALS['bfi_lang'], null, array('striptags'=>'striptags', 'bbcode'=>'bbcode','ln2br'=>'ln2br'));

$resourceDescriptionSeo = BFCHelper::getLanguage($resource->Description, $GLOBALS['bfi_lang'], null, array('ln2br'=>'ln2br', 'bbcode'=>'bbcode', 'striptags'=>'striptags'));
$uri = $url_resource_page.$resource->ResourceId.'-'.BFI()->seoUrl($resourceName);

$isportal = COM_BOOKINGFORCONNECTOR_ISPORTAL;
$showdata = COM_BOOKINGFORCONNECTOR_SHOWDATA;

$posx = COM_BOOKINGFORCONNECTOR_GOOGLE_POSX;
$posy = COM_BOOKINGFORCONNECTOR_GOOGLE_POSY;
$startzoom = COM_BOOKINGFORCONNECTOR_GOOGLE_STARTZOOM;
$googlemapsapykey = COM_BOOKINGFORCONNECTOR_GOOGLE_GOOGLEMAPSKEY;

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
$experienceLevel_text = array('0' => __('Very easy', 'bfi'),
						'1' => __('Easy', 'bfi'),   
						'2' => __('Moderate', 'bfi'),
						'3' => __('Stimulating', 'bfi'),
						'4' => __('Demanding', 'bfi'),
						'5' => __('Extreme', 'bfi'),  
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
if(empty($resourceLat) && !empty($merchant->XGooglePos)){
	$resourceLat = $merchant->XGooglePos;
}
if(empty($resourceLon) && !empty($merchant->YGooglePos)){
	$resourceLon = $merchant->YGooglePos;
}

$showMap = (($resourceLat != null) && ($resourceLon !=null) );
if ($showmap && COM_BOOKINGFORCONNECTOR_USE_OPENSTREETMAP) {
	wp_enqueue_script('bfileaflet');
	wp_enqueue_script('bfileafletcontrolcustom');
}

$htmlmarkerpoint = "&markers=color:blue%7C" . $resourceLat . "," . $resourceLon;
	if (COM_BOOKINGFORCONNECTOR_USE_OPENSTREETMAP) {
		wp_enqueue_script('bfileaflet');
		wp_enqueue_script('bfileafletcontrolcustom');
	}

$services = [];
if (!empty($resource->ServiceIdList)){
	$listServices = explode(",", $resource->ServiceIdList);
	$services = BFCHelper::GetServicesByIds($resource->ServiceIdList,$language);
	$services = array_filter($services, function($p) use ($listServices) {return in_array($p->ServiceId,$listServices);});
}


$resourceRoute = $uri;
$routeRating = $uri.'/'._x('review', 'Page slug', 'bfi' );

$ProductAvailabilityType = $resource->AvailabilityType;
$resourcetype = $resource->ItemTypeId;

$fromSearch =  BFCHelper::getVar('fromsearch','0');

$reviewavg = 0;
$reviewcount = 0;
$showReview = false;

if ($merchant->RatingsContext != NULL && $merchant->RatingsContext > 0) {
	$showReview = true;
	if ($merchant->RatingsContext ==1 && !empty($merchant->Avg)) {
		$reviewavg =  isset($merchant->Avg) ? $merchant->Avg->Average : 0;
		$reviewcount =  isset($merchant->Avg) ? $merchant->Avg->Count : 0;
	}
	if ($merchant->RatingsContext ==2 || $merchant->RatingsContext ==3 ) {
		$summaryRatings = $resource->Avg;
		//$summaryRatings = BFCHelper::getResourceRatingAverage($merchant->MerchantId,$resource->ResourceId);
		if(!empty($summaryRatings)){
			$reviewavg = $summaryRatings->Average;
			$reviewcount = $summaryRatings->Count;
		}
	}
}

$tagsHighlight = array();
if(!empty( $resource->Tags)){
	foreach ( $resource->Tags as $gr ) {
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
$tagsIncluded = array();
$tagsNotIncluded = array();
if(!empty( $resource->Tags)){
	foreach ( $resource->Tags as $gr ) {
		$subGroupRefIds = array_filter(array_map(function ($i) { return $i->SubGroupRefId; }, $gr->Tags));
		$gr->subGroupRefIds = $subGroupRefIds;
		if (!empty($gr->subGroupRefIds )) {
			$currTag = $gr->Tags;
			foreach($currTag as $item) {
				if ($item->TagValue=="0") {
					$tagsIncluded[] = $item;
				}
				if ($item->TagValue=="-1") {
					$tagsNotIncluded[] = $item;
				}
			}
		}
	}
}


$payloadresource["@type"] = "Product";
$payloadresource["@context"] = "http://schema.org";
$payloadresource["name"] = $resourceName;
$payloadresource["description"] = $resourceDescriptionSeo;
$payloadresource["url"] = $resourceRoute; 
if (!empty($resource->ImageUrl)){
	$payloadresource["image"] = "https:".BFCHelper::getImageUrlResized('resources',$resource->ImageUrl, 'logomedium');
}
$imgPopup = COM_BOOKINGFORCONNECTOR_DEFAULTIMAGE;
if (!empty($resource->ImageUrl)){
	$imgPopup =  BFCHelper::getImageUrlResized('resources',$resource->ImageUrl, 'logomedium');
}

?>
<script type="application/ld+json">// <![CDATA[
<?php echo json_encode($payloadresource); ?>
// ]]></script>
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
<script type="application/ld+json">// <![CDATA[
<?php echo json_encode($payload); ?>
// ]]></script>

<div class="bfi-content bfi-content-rescat bfi-content-rescat<?php echo $resource->CategoryId ?> <?php echo ($resourcetype == bfi_ItemType::Rental) ?"bfi-res-tabs":"";?> bfi-details-experience ">	
<!-- Navigation -->
<?php 
	switch ($resourcetype) {
		case bfi_ItemType::Rental:
			break;
		default:      
				?>
	<div class="bfi-menu-top-container">
		<ul class="bfi-menu-top">
			<?php if(!$resource->IsCatalog){ ?><li><a rel="#divcalculator"><?php echo _e('Info & prices','bfi'); ?></a></li><?php } ?>
			<?php if (!empty( $resource->Tags)){?><li><a rel=".bfi-facilities"><?php echo  _e('Facilities', 'bfi') ?></a></li><?php } ?>
			<li><a rel=".bfi-merchant-inshort"><?php echo  _e('House rules', 'bfi') ?></a></li>
			<?php if (false && $showReview){?><li class="bfi-hidden-xs bfi-hidden-sm"><a rel=".bfi-ratingslist"><?php echo  _e('Reviews' , 'bfi') ?></a></li><?php } ?>
		</ul>
	</div>	
	<?php 
	}
	?>
	
	<div class="bfi-row bfi-margin-bottom10 bfi-row-background">
	<?php 
$nColTitle = 12;

			switch ($resourcetype) {
			case bfi_ItemType::Rental :
				if(!empty($resource->ImageUrl)){
					$nColTitle -=4;
					$resourceImageUrl = BFCHelper::getImageUrlResized('resources',$resource->ImageUrl, 'medium');
					?><div class="bfi-col-md-4">
					<img src="<?php echo $resourceImageUrl ?>" style="padding:10px;"/>
					</div>
					<?php 
				}
				break;
			default:      
				break;
			} // end switch
			if($reviewcount>0){
				$nColTitle -=2;
			} 
	?>
		<div class="bfi-col-md-<?php echo $nColTitle ?>">
	<?php 

		if(empty($reviewcount)){
		?><div class="bfi-pull-right">
			<div class="bfi-pull-right bfi-btn-book-top bfi-hide">
				<a rel="#divcalculator" class="bfi-btn bfi-alternative bfi-btn-calc"><?php echo _e('Book now','bfi'); ?></a>
			</div> 
			<div class="bfi-pull-right">
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
			<div class="bfi-title-name bfi-hideonextra"><?php echo  $resourceName?> <?php if($resourcetype == bfi_ItemType::Rental) { ?><span class="bfi-rental-caption"><?php _e('or similar', 'bfi') ?></span><?php } ?>
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
<?php 
if ($resourcetype == bfi_ItemType::Rental) {
    
// icon tag Highlight
	if(!empty( $tagsHighlight) ){  //Rental
		?>
		<div class="bfirestags bfi-hideonextra">
		<?php 
		foreach($tagsHighlight as $tagHighlight) {
			if (!empty($tagHighlight->IconSrc)) {
				$currType = explode(';',$tagHighlight->IconType);
				switch ($currType[0]) {
					case "fontawesome5":
						?>
							<i class='<?php echo $tagHighlight->IconSrc ?>' data-toggle="tooltip" title="<?php echo $tagHighlight->Name ?>"></i> 
						<?php
						break;
					case "fontawesome4":
					default:
						?>
							<i class='fa <?php echo $tagHighlight->IconSrc ?>' data-toggle="tooltip" title="<?php echo $tagHighlight->Name ?>"></i> 
						<?php
				}
			} else {
				echo $tagHighlight->Name . ' ';
			} //end if	
		}	//end foreach				
		?> 
		</div>	
		<?php 
	}					

	if( ($merchant->AcceptanceCheckIn != "-" && $merchant->AcceptanceCheckOut != "-")  ){
			?>
			<div ><b><?php _e('Pick Up & Drop Off', 'bfi') ?></b>: <?php echo $merchant->AcceptanceCheckIn  ?> | <?php echo $merchant->AcceptanceCheckOut  ?> 				
			</div>	
		<?php 
	}


}
?>
			

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
		<?php } ?>
	</div>	
	<div class="bfi-clearfix"></div> 
<!-- Navigation -->
<?php 
	switch ($resourcetype) {
		case bfi_ItemType::Experience:
			break;
		default:      
				?>	<ul class="bfi-menu-top bfi-hideonextra">
		<!-- <li ><a rel=".bfi-resourcecontainer-gallery"><?php echo  _e('Media' , 'bfi') ?></a></li> -->
		<?php if (!empty($resourceDescription)){?><li class="bfi-hidden-xs bfi-hidden-sm"><a rel=".bfi-description-data"><?php echo  _e('Description', 'bfi') ?></a></li><?php } ?>
		<?php if (false && $isportal){ ?><li class="bfi-hidden-xs bfi-hidden-sm"><a rel=".bfi-merchant-simple"><?php echo  _e('Host' , 'bfi') ?></a></li>
		<?php if ($showReview){?><li class="bfi-hidden-xs bfi-hidden-sm"><a rel=".bfi-ratingslist"><?php echo  _e('Reviews' , 'bfi') ?></a></li><?php } ?><?php } ?>
		<?php if ($showMap) {?><li><a class="bfiopenpopupmap"><?php echo _e('Map' , 'bfi') ?></a></li><?php } ?>
		<?php if(!$resource->IsCatalog){ ?><li class="bfi-book"><a rel="#divcalculator"><?php echo _e('Book now','bfi'); ?></a></li><?php } ?>
		<?php if(!COM_BOOKINGFORCONNECTOR_DISALBLEINFOFORM){ ?><li class="bfi-request"><a rel=".bfi-form-contacts" data-toggle="tab"><?php echo  _e('Request' , 'bfi') ?></a></li><?php } ?>
	</ul>
	<?php 
	}
	?>
	
</div>
				<?php 

					switch ($resourcetype) {
						case bfi_ItemType::Rental:
							break;
						case bfi_ItemType::Experience:
							?>
						<div class="bfi-row">								
							<div class="bfi-col-md-9 bfi-col-sm-12 bfi-col-xs-12 ">
								<div class="bfi-resourcecontainer-gallery bfi-resourcecontainer-gallery-experience bfi-hideonextra">
									<?php  
											$bfiSourceData = 'resources';
											$bfiImageData = null;
											$bfiVideoData = null;
											if(!empty($resource->ImageData)) {
												$bfiImageData = $resource->ImageData;
											}
											if(!empty($resource->VideoData)) {
												$bfiVideoData = $resource->VideoData;
											}
											bfi_get_template("shared/gallery_type2.php",array("merchant"=>$merchant,"bfiSourceData"=>$bfiSourceData,"bfiImageData"=>$bfiImageData,"bfiVideoData"=>$bfiVideoData));	
									?>
								</div>
							</div>
							<div class="bfi-col-md-3 bfi-col-sm-12 bfi-col-xs-12 bficontainerexperience">
									<?php 
									$resourceId = $resource->ResourceId;
									$resourcegroupId = 0;
									if (isset($resource->ParentProductId) && !empty($resource->ParentProductId)) {
										$resourcegroupId = $resource->ParentProductId;
									}
									$refreshSearch = 0;
									bfi_get_template("shared/search_details_experience.php",array("resource"=>$resource,"merchant"=>$merchant,"resourceId"=>$resourceId,"resourcegroupId"=>$resourcegroupId,"currencyclass"=>$currencyclass,"refreshSearch"=>$refreshSearch));		
									 ?>
							</div>
						</div>

							<?php 
							break;
						default:   
							?>
								<div class="bfi-resourcecontainer-gallery bfi-hideonextra">
									<?php  
											$bfiSourceData = 'resources';
											$bfiImageData = null;
											$bfiVideoData = null;
											if(!empty($resource->ImageData)) {
												$bfiImageData = $resource->ImageData;
											}
											if(!empty($resource->VideoData)) {
												$bfiVideoData = $resource->VideoData;
											}
											bfi_get_template("shared/gallery_type2.php",array("merchant"=>$merchant,"bfiSourceData"=>$bfiSourceData,"bfiImageData"=>$bfiImageData,"bfiVideoData"=>$bfiVideoData));	
									?>
								</div>
							<?php 
					}
				?>
<?php 
// icon tag Highlight
	if($resourcetype != bfi_ItemType::Rental && ( 
		(isset($resource->Area) && $resource->Area>0)  
		|| (isset($resource->ExperienceTimeLength) && $resource->ExperienceTimeLength>0) 
		|| (isset($resource->ExperienceMinAge) && $resource->ExperienceMinAge>0) 
		|| (isset($resource->ExperienceLevel) && $resource->ExperienceLevel>0) 
		) ){
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
		if(isset($resource->ExperienceTimeLength) && $resource->ExperienceTimeLength>0  ){ 
//		$experienceTime = $resource->ExperienceTimeLength/(60*24);
//		$experienceTimeLabel =  __('Days', 'bfi');
//		if ($experienceTime<1) {
//		    $experienceTime = $resource->ExperienceTimeLength/60;
//			$experienceTimeLabel =  __('Hours', 'bfi') . " " . __('(Approx.)', 'bfi');
//		}
//		    $experienceTime = round($experienceTime, 0, PHP_ROUND_HALF_UP);
//		
//		
//		echo "<pre>";
//		echo BFCHelper::GetDurationString(BFCHelper::GetDurationStringFromSpan($resource->ExperienceTimeLength),', ',true);
//		echo "</pre>";		
		?>
				<div class="bfi-group-tags-icons-item">
					<div class="bfi-group-tags-icons-item-value">
						<svg class="mr-2 fill-secondary" width="24" height="24" aria-labelledby="title" role="img" viewBox="0 0 24 24"><title>icon</title><path d="M12,22A10,10,0,1,1,22,12,10.01177,10.01177,0,0,1,12,22ZM12,3a9,9,0,1,0,9,9A9.00984,9.00984,0,0,0,12,3Z"></path><path d="M15,15.5a.49842.49842,0,0,1-.35352-.14648l-3-3A.49965.49965,0,0,1,11.5,12V6a.5.5,0,0,1,1,0v5.793l2.85352,2.85351A.5.5,0,0,1,15,15.5Z"></path></svg>
					</div>
					<div class="bfi-group-tags-icons-item-title">
						<?php _e('Duration', 'bfi') ?>
					</div>
					<div class="bfi-group-tags-icons-item-label">
						<?php echo BFCHelper::GetDurationString(BFCHelper::GetDurationStringFromSpan($resource->ExperienceTimeLength),', ',true); ?>
					</div>
				</div>
		<?php 
		}
		if(isset($resource->MinCapacityPaxes) && $resource->MinCapacityPaxes>0  ){ 
		?>
				<div class="bfi-group-tags-icons-item">
					<div class="bfi-group-tags-icons-item-value">
						<i class="fas fa-users"></i>
					</div>
					<div class="bfi-group-tags-icons-item-title">
						<?php _e('Participants', 'bfi') ?>
					</div>
					<div class="bfi-group-tags-icons-item-label">
						<?php _e('from', 'bfi') ?> <?php echo $resource->MinCapacityPaxes?> <?php if(isset($resource->MaxCapacityPaxes) && $resource->MaxCapacityPaxes>0  ){ ?>
						 <?php _e('to', 'bfi') ?> <?php echo $resource->MaxCapacityPaxes?>
						<?php }?>
					</div>
				</div>
		<?php 
		}
		if(isset($resource->ExperienceMinAge) && $resource->ExperienceMinAge>0  ){ 
		?>
				<div class="bfi-group-tags-icons-item">
					<div class="bfi-group-tags-icons-item-value">
						<i class='fa fa-user'></i> 
					</div>
					<div class="bfi-group-tags-icons-item-title">
						<?php _e('Minimum age', 'bfi') ?>
					</div>
					<div class="bfi-group-tags-icons-item-label">
						<?php echo $resource->ExperienceMinAge?>
						<?php _e('Years', 'bfi') ?>
					</div>
				</div>
		<?php 
		}
		if(isset($resource->ExperienceLevel) && $resource->ExperienceLevel>-1  ){ 
		?>
				<div class="bfi-group-tags-icons-item">
					<div class="bfi-group-tags-icons-item-value">
						<meter class="bfimeter" max="5" min="0" value="<?php echo $resource->ExperienceLevel ?>" high="4" low="1" optimum="2"></meter>
					</div>
					<div class="bfi-group-tags-icons-item-title">
						<?php _e('Difficulty level', 'bfi') ?>
					</div>
					<div class="bfi-group-tags-icons-item-label">
						<?php echo $experienceLevel_text[$resource->ExperienceLevel] ?>
					</div>
				</div>
		<?php 
		}
		
		if(!empty( $resource->HasLiveGuide)) {
		?>
				<div class="bfi-group-tags-icons-item">
					<div class="bfi-group-tags-icons-item-value">
						<i class="fas fa-language"></i>
					</div>
					<div class="bfi-group-tags-icons-item-title">
						<?php _e('Languages', 'bfi') ?>
					</div>
					<div class="bfi-group-tags-icons-item-label">
						<?php 
							$currLanguages = array();
							foreach(explode(",",$resource->LiveGuideLanguages) as $currLang) {
								$currLanguages[] = locale_get_display_language($currLang,$GLOBALS['bfi_lang']);
							}
							if (Count($currLanguages)>1) {
								echo $currLanguages[0];// . ', ' $currLanguages[2];
								?>
							    <span class="bfi-otherlang"><?php echo sprintf( __(' and %s more ', 'bfi'),(Count($currLanguages)-1))?> </span>
									<div class="webui-popover-content">
									<div class="bfi-titlepopover"><?php _e('Offered in', 'bfi') ?>:</div>
									<?php echo implode(", ",$currLanguages) ?>
									</div>
							    <?php 
							    
							}else{
								echo implode(", ",$currLanguages);
							}
							
						?>
					</div>
				</div>
		<?php 
		}			
		?> 
			</div>	
		<?php 
	}					

?>
<!--// availability-->
	<?php if(!$resource->IsCatalog){ ?>
		<div id="bfiavailabilitytab">
			<!-- calc -->
			<a name="calc"></a>
			<div id="divcalculator">
				<?php 
				$resourceId = $resource->ResourceId;
				$resourcegroupId = 0;
				if (isset($resource->ParentProductId) && !empty($resource->ParentProductId)) {
					$resourcegroupId = $resource->ParentProductId;
				}
				$refreshSearch = 0;
				bfi_get_template("shared/search_details.php",array("resource"=>$resource,"merchant"=>$merchant,"resourceId"=>$resourceId,"resourcegroupId"=>$resourcegroupId,"currencyclass"=>$currencyclass,"refreshSearch"=>$refreshSearch, "hideform"=>1));		
				 ?>
			</div>
		</div>
	<?php } ?>

<div class="bfi-content bfi-content-rescat bfi-content-rescat<?php echo $resource->CategoryId ?> bfi-details-experience " >	
	<div class="bfiexperience">

<!--// description-->
<?php 

if(!empty($resourceTermOfUse) && !empty($resourceDescription)){
	switch ($resourcetype) {
		case bfi_ItemType::Rental:
			?><ul class="bfi-tabs-resource bfi-hideonextra">
				<li class="bfi-hidden-xs bfi-hidden-sm"><a href="#bfidescriptiontab"><?php echo  _e('Description', 'bfi') ?></a></li>
				<li class="bfi-hidden-xs bfi-hidden-sm"><a href="#bfitermofusetab"><?php echo _e('Important information', 'bfi') ?></a></li>
			</ul>
			<?php 
			break;
		default:      
		}
	}
	?>
	<?php if (!empty($resourceDescription)){?>
		<div id="bfidescriptiontab" class="bfi-hideonextra">
			<div class="bfi-row bfi-row-background">
				<div class="bfi-col-md-12">
					<div class="bfi-description-data bfi-shortenicon">
						<h4 class="bfi-title-content" ><?php echo  _e('Description', 'bfi') ?></h4>
						<?php echo $resourceDescription ?>
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
	<?php if (!empty($resourceTermOfUse)){?>
		<div id="bfitermofusetab" class="bfi-hideonextra">
			<div class="bfi-description-data">
				<?php echo $resourceTermOfUse ?><br /><br />
			</div>
		</div>
	<?php } ?>
	<?php 
	} 
	?>
	<?php 
	if(!empty( $tagsIncluded) ||  !empty( $resource->Inclusion)  ){
	?>
	<div class="bfitagincluded bficontentinfo" >
		<h2 class="bfititletoggle"><?php _e('What is Included', 'bfi') ?></h2>
		<div class="bficontenttoggle">
			<?php if(!empty( $resource->Inclusion)) { ?>
				<div class="bfiexperienceincluded">
				<?php echo BFCHelper::getLanguage($resource->Inclusion, $GLOBALS['bfi_lang'], null, array('striptags'=>'striptags', 'bbcode'=>'bbcode')); ?>
				 
				</div>
			<?php } ?>
			<?php 
			foreach($tagsIncluded as $tg) {
				echo '<div class="bfiexperienceincludedtag"> ' . $tg->Name .'</div>';
			}			
			?>
			<?php if(!empty( $resource->HasLiveGuide)) {
				?>
				<div class="bfiexperienceincludedtag"><?php echo __('Live Guide ', 'bfi')?>: 
				<?php 
				$currLanguages = array();
				foreach(explode(",",$resource->LiveGuideLanguages) as $currLang) {
					$currLanguages[] = locale_get_display_language($currLang,$GLOBALS['bfi_lang']);
				}			
				echo implode(", ",$currLanguages). '</div>';
			} ?>
			<?php if(!empty( $resource->HasAudioGuide)) { 
				?>
				<div class="bfiexperienceincludedtag"><?php echo __('Audio Guide ', 'bfi')?>: 
				<?php 
				$currLanguages = array();
				foreach(explode(",",$resource->AudioGuideLanguages) as $currLang) {
					$currLanguages[] = locale_get_display_language($currLang,$GLOBALS['bfi_lang']);
				}			
				echo implode(", ",$currLanguages). '</div>';
			} ?>

			<?php if(!empty( $resource->HasPaperGuide)) { 
				?>
				<div class="bfiexperienceincludedtag"><?php echo __('Paper Guide ', 'bfi')?>: 
				<?php 
				$currLanguages = array();
				foreach(explode(",",$resource->PaperGuideLanguages) as $currLang) {
					$currLanguages[] = locale_get_display_language($currLang,$GLOBALS['bfi_lang']);
				}			
				echo implode(", ",$currLanguages). '</div>';
			} ?>
		</div>
	</div>
	<?php }	?>				
	<?php 
	if(!empty( $tagsNotIncluded) || !empty( $resource->Exclusion) ){
	?>
	<div class="bfitagnotincluded bficontentinfo" >
		<h2 class="bfititletoggle"><?php _e('What is not Included', 'bfi') ?></h2>
		<div class="bficontenttoggle">

			<?php if(!empty( $resource->Exclusion)) { ?>
				<div class="bfiexperienceexclusion">
				<?php echo BFCHelper::getLanguage($resource->Exclusion, $GLOBALS['bfi_lang'], null, array('striptags'=>'striptags', 'bbcode'=>'bbcode')); ?>
				</div>
			<?php } ?>
			
			<?php 
			foreach($tagsNotIncluded as $tg) {
				echo '<div><i class="fa fa-times"></i> ' . $tg->Name .'</div>';
			}			
			?> 
		</div>
	</div>
	<?php }	?>				
	<div class="bfidepartureandreturn bficontentinfo" >
		<h2 class="bfititletoggle"><?php _e('Departure & Return', 'bfi') ?></h2>
		<div class="bficontenttoggle">		
		<?php if(($resource->MeetingType==0 || $resource->MeetingType ==2) && !empty($resource->MeetingPointsString) ) { 
		?>
			<div class="bfisubtitle"><?php _e('Departure Point', 'bfi') ?></div>
		<?php
			$meetingPoints = json_decode($resource->MeetingPointsString);
			foreach ($meetingPoints as $meetingPoint) {
					$showResourceMap = false && (!empty($meetingPoint->Address->XPos) && !empty($meetingPoint->Address->YPos)) && !empty(COM_BOOKINGFORCONNECTOR_ENABLEGOOGLEMAPSAPI);
					$indirizzo = isset($meetingPoint->Address->Address)?$meetingPoint->Address->Address:"";
					$cap = isset($meetingPoint->Address->ZipCode)?$meetingPoint->Address->ZipCode:""; 
					$comune = isset($meetingPoint->Address->CityName)?$meetingPoint->Address->CityName:"";
					$stato = isset($meetingPoint->Address->StateName)?$meetingPoint->Address->StateName:"";
			    ?>
			    	<div class="bfilocationaddress"><i class="fa fa-map-marker fa-1"></i> <?php if (($showResourceMap)) {?><a class="bfi-map-link bfiopenpopupmap" ><?php } ?><span class="street-address"><?php echo $indirizzo ?></span>, <span class="postal-code "><?php echo  $cap ?></span> <span class="locality"><?php echo $comune ?></span>, <span class="region"><?php echo  $stato ?></span></div>
		    <?php 
			}
		 } ?>
		<?php if($resource->MeetingType!=1 && (isset($resource->MeetingWindowType) && $resource->MeetingWindowType == 0) && (!empty( $resource->MeetingWindowMinutesBefore) ||  ($resource->AvailabilityType == 0 || $resource->AvailabilityType ==  1)  )) { ?>
			<div class="bfisubtitle"><?php _e('Meeting Time', 'bfi') ?></div>
			<?php 
						
			if ($resource->AvailabilityType == 0 || $resource->AvailabilityType ==  1) {
				$meetingWindowStart = new DateTime('UTC');
				$meetingWindowStart->setTime(0,0,0);
				$meetingWindowStart->add(new DateInterval('PT' . $resource->MeetingWindowStart . 'M'));
				$meetingWindowEnd = new DateTime('UTC');
				$meetingWindowEnd->setTime(0,0,0);
				$meetingWindowEnd->add(new DateInterval('PT' . $resource->MeetingWindowEnd . 'M'));
				echo $meetingWindowStart->format('H:i');
				if ($meetingWindowEnd>$meetingWindowStart) {
					echo " - " .$meetingWindowEnd->format('H:i');
				}
			}else {
				if (!empty( $resource->MeetingWindowMinutesBefore)) {
						echo sprintf( __('show up %s minutes before the start time ', 'bfi'), $resource->MeetingWindowMinutesBefore);
//					if (!empty( $resource->MeetingWindowMinutesLength)) {
//						echo sprintf( " ". __('and no later than %s minutes ', 'bfi'), $resource->MeetingWindowMinutesLength);
//					}
				}
			}

			?> 
		<?php } ?>
		<?php if(($resource->MeetingType==1 || $resource->MeetingType ==2) && !empty($resource->PickUpPointsString) ) { 
			 ?>
			<div class="bfisubtitle"><?php _e('Pick-up Point', 'bfi') ?></div>
			<?php 
			$pickUpPoints = json_decode($resource->PickUpPointsString);
			foreach ($pickUpPoints as $pickUpPoint) {
					$showResourceMap = false && (!empty($pickUpPoint->Address->XPos) && !empty($pickUpPoint->Address->YPos)) && !empty(COM_BOOKINGFORCONNECTOR_ENABLEGOOGLEMAPSAPI);
					$indirizzo = isset($pickUpPoint->Address->Address)?$pickUpPoint->Address->Address:"";
					$cap = isset($pickUpPoint->Address->ZipCode)?$pickUpPoint->Address->ZipCode:""; 
					$comune = isset($pickUpPoint->Address->CityName)?$pickUpPoint->Address->CityName:"";
					$stato = isset($pickUpPoint->Address->StateName)?$pickUpPoint->Address->StateName:"";
			    ?>
			    	<div class="bfilocationaddress"><i class="fa fa-map-marker fa-1"></i> <?php if (($showResourceMap)) {?><a class="bfi-map-link bfiopenpopupmap" ><?php } ?><span class="street-address"><?php echo $indirizzo ?></span>, <span class="postal-code "><?php echo  $cap ?></span> <span class="locality"><?php echo $comune ?></span>, <span class="region"><?php echo  $stato ?></span>
					<?php if($resource->PickUpWindowType == 1) { 
						if ($resource->AvailabilityType == 0 || $resource->AvailabilityType ==  1) {
							$pickUpWindowStart = new DateTime('UTC');
							$pickUpWindowStart->setTime(0,0,0);
							$pickUpWindowStart->add(new DateInterval('PT' . $pickUpPoint->PickUpWindowStart . 'M'));
							$pickUpWindowEnd = new DateTime('UTC');
							$pickUpWindowEnd->setTime(0,0,0);
							$pickUpWindowEnd->add(new DateInterval('PT' . $pickUpPoint->PickUpWindowEnd . 'M'));
							echo $pickUpWindowStart->format('H:i');
							if ($pickUpWindowEnd>$pickUpWindowStart) {
								echo " - " .$pickUpWindowEnd->format('H:i');
							}
						}else {
							if (!empty( $pickUpPoint->MeetingWindowMinutesBefore)) {
									echo sprintf( __('show up %s minutes before the start time ', 'bfi'), $pickUpPoint->MeetingWindowMinutesBefore);
//								if (!empty( $pickUpPoint->MeetingWindowMinutesLength)) {
//									echo sprintf(  " ".__('and no later than %s minutes ', 'bfi'), $pickUpPoint->MeetingWindowMinutesLength);
//								}
							}
						}

					 } ?>
					</div>
			    <?php 
			}
		 } ?>
		<?php if(($resource->MeetingType==1 || $resource->MeetingType ==2) && $resource->PickUpWindowType == 0 && (($resource->AvailabilityType == 0 || $resource->AvailabilityType ==  1) || !empty( $resource->PickUpWindowMinutesBefore)) ) { ?>
			<div class="bfisubtitle"><?php _e('Pick-up Time', 'bfi') ?></div>
			<?php 			
			if ($resource->AvailabilityType == 0 || $resource->AvailabilityType ==  1) {
				$pickUpWindowStart = new DateTime('UTC');
				$pickUpWindowStart->setTime(0,0,0);
				$pickUpWindowStart->add(new DateInterval('PT' . $resource->PickUpWindowStart . 'M'));
				$pickUpWindowEnd = new DateTime('UTC');
				$pickUpWindowEnd->setTime(0,0,0);
				$pickUpWindowEnd->add(new DateInterval('PT' . $resource->PickUpWindowEnd . 'M'));
				echo $pickUpWindowStart->format('H:i');
				if ($pickUpWindowEnd>$pickUpWindowStart) {
					echo " - " .$pickUpWindowEnd->format('H:i');
				}
			}else {
				if (!empty( $resource->PickUpWindowMinutesBefore)) {
						echo sprintf( __('show up %s minutes before the start time ', 'bfi'), $resource->PickUpWindowMinutesBefore);
//					if (!empty( $resource->PickUpWindowMinutesLength)) {
//						echo sprintf( " ". __('and no later than %s minutes ', 'bfi'), $resource->PickUpWindowMinutesLength);
//					}
				}
			}
			?> 
		<?php } ?>
		<div class="bfisubtitle"><?php _e('Return Details', 'bfi') ?></div>
		<?php 
		switch ($resource->DropOffType) {
		case 1:
			echo _e('Returns to original departure pickup', 'bfi');
			break;
		case 2:
			$dropOffPoints = json_decode($resource->DropOffPointsString);
			foreach ($dropOffPoints as $dropOffPoint) {
					$showResourceMap = false && (!empty($dropOffPoint->Address->XPos) && !empty($dropOffPoint->Address->YPos)) && !empty(COM_BOOKINGFORCONNECTOR_ENABLEGOOGLEMAPSAPI);
					$indirizzo = isset($dropOffPoint->Address->Address)?$dropOffPoint->Address->Address:"";
					$cap = isset($dropOffPoint->Address->ZipCode)?$dropOffPoint->Address->ZipCode:""; 
					$comune = isset($dropOffPoint->Address->CityName)?$dropOffPoints->Address->CityName:"";
					$stato = isset($dropOffPoint->Address->StateName)?$dropOffPoint->Address->StateName:"";
			    ?>
			    	<div class="bfilocationaddress"><i class="fa fa-map-marker fa-1"></i> <?php if (($showResourceMap)) {?><a class="bfi-map-link bfiopenpopupmap" ><?php } ?><span class="street-address"><?php echo $indirizzo ?></span>, <span class="postal-code "><?php echo  $cap ?></span> <span class="locality"><?php echo $comune ?></span>, <span class="region"><?php echo  $stato ?></span></div>
		    <?php 
			}
			break;
		default: 
			if ($resource->MeetingType==1 ) {
				echo _e('Returns to original pick-up point', 'bfi');
			    
			}else{
				echo _e('Returns to original departure point', 'bfi');
			}
		}
		?>
		</div>
	</div>
	<?php 
	if(!empty( $resource->RoutePlacesString) && $resource->RoutePlacesString!="[]" ){
	?>
	<div class="bfiitinerary bficontentinfo" >
		<h2 class="bfititletoggle"><?php _e('What To Expect', 'bfi') ?></h2>
		<div class="bficontenttoggle">
			<div class="bfisubtitle"><?php _e('Itinerary', 'bfi') ?></div>
		<?php 
			$routePlaces = json_decode($resource->RoutePlacesString);
			$itineraryDays = array_unique(array_map(function ($routePlace) { return $routePlace->AdditionaInfos->Day; }, $routePlaces));
			foreach ($itineraryDays as $itineraryDay) {
				$itineraryDayPlaces = array_values(array_filter($routePlaces, function($routePlace) use($itineraryDay) {
					if ($routePlace->AdditionaInfos->Day == $itineraryDay)
						return true;
					return false;
				}));
				$itineraryCities = array_filter(array_map(function ($routePlace) { return $routePlace->Address->CityName; }, $itineraryDayPlaces));
				?>
				<div class="bfiitinerarytitle" style="display:<?php echo (count($itineraryDays )>1) ?"":"none"; ?>">
					<div class="bfiitineraryday"><?php _e('Day', 'bfi') ?> <?php echo $itineraryDay ?></div>
					<div class="bficities"><?php echo implode("-",$itineraryCities ) ?></div>
					<div class="bficitiescount"><?php echo count($itineraryDayPlaces ) ?> <?php echo count($itineraryDayPlaces ) >1 ? __('Stops', 'bfi'): __('Stop', 'bfi') ?></div>
				</div>
				<div class="bficontentcitiesitineraries" style="display:<?php echo (count($itineraryDays )>1) ?"none":""; ?>" >
				<?php 

				foreach ($itineraryDayPlaces as $routePlace) {
						$showResourceMap = false && (!empty($routePlace->Address->XPos) && !empty($routePlace->Address->YPos)) && !empty(COM_BOOKINGFORCONNECTOR_ENABLEGOOGLEMAPSAPI);
						$routePlaceDescription = BFCHelper::getLanguage($routePlace->Description, $language, null, array( 'striptags'=>'striptags', 'bbcode'=>'bbcode','ln2br'=>'ln2br')) ;
					?>
				<div class="bficontentitineraries">
						<i class="fa fa-map-marker fa-1"></i> <b><?php echo $routePlace->Name ?></b>
						<div class="bfilocationaddress"><?php if (($showResourceMap)) {?><a class="bfi-map-link bfiopenpopupmap" ><?php } ?><?php echo BFCHelper::buildAddress($routePlace->Address); ?></div>
						<div class="bficontentitinerariesdescription"><?php echo $routePlaceDescription?></div>
				</div>
				<?php }	?>
				</div>
			<?php 
			}


		?>
		</div>
	</div>
<?php } ?>
<?php 
if(!empty($resource->WhatToKnow)) {
?>
	<div class="bfiadditionalinfo bficontentinfo" >
		<h2 class="bfititletoggle"><?php _e('Additional Info', 'bfi') ?></h2>
		<div class="bficontenttoggle">	
			<?php if(!empty($resource->WhatToKnow)) { ?>
				<div><?php echo BFCHelper::getLanguage($resource->WhatToKnow, $GLOBALS['bfi_lang'], null, array('striptags'=>'striptags', 'bbcode'=>'bbcode')); ?></div>
			<?php } ?>
		</div>
	</div>
<?php } ?>
<?php 
if(!empty($resource->WhatToBring)) {
?>
	<div class="bfiadditionalinfo bficontentinfo" >
		<h2 class="bfititletoggle"><?php _e('What to bring', 'bfi') ?></h2>
		<div class="bficontenttoggle">	
			<?php if(!empty($resource->WhatToBring)) { ?>
				<div><?php echo BFCHelper::getLanguage($resource->WhatToBring, $GLOBALS['bfi_lang'], null, array('striptags'=>'striptags', 'bbcode'=>'bbcode')); ?></div>
			<?php } ?>
		</div>
	</div>
<?php } ?>
<?php 
	if(!empty( $resource->PoliciesString) && $resource->PoliciesString!="[]" ){
?>
	<div class="bficancellationpolicy bficontentinfo" >
		<h2 class="bfititletoggle"><?php _e('Cancellation Policy', 'bfi') ?></h2>
		<div class="bficontenttoggle">
		<?php 
			$policies = json_decode($resource->PoliciesString);
			foreach ($policies as $policy) {
				echo '<div class="bfi-policiesdescription">' . BFCHelper::getLanguage($policy->Description , $GLOBALS['bfi_lang'], null, array('striptags'=>'striptags','ln2br'=>'ln2br')) .'</div>';
				
			}
?>
		</div>
	</div>
<?php } ?>

<?php 
if(isset($resource->faq) && !empty($resource->faq)) { //sospeso
?>

	<div class="bfifaq bficontentinfo" >
		<h2 class="bfititletoggle"><?php _e('Frequently Asked Questions', 'bfi') ?></h2>
		<div class="bficontenttoggle">
		</div>
	</div>
<?php } ?>


</div>




	<!--// RatingsContext-->
	<?php if ($showReview){?>
        <div id="bfiratingslisttab">
			<div class="bfi-slide-panel">
				<span class="bfi-panel-close bfi-panel-toggle"></span>
					<h2><?php echo  _e('Reviews' , 'bfi') ?></h2>
					<div class="bfi-ratingslist bfi-hideonextra">
					<?php
						$summaryRatings = 0;
						$ratings = null;
						if ($merchant->RatingsContext ==1) {
							$summaryRatings = $merchant->Avg;
							$ratings = BFCHelper::getMerchantRatings(0,5,$merchant->MerchantId,$language,1);

						} else {
							$summaryRatings = $resource->Avg;
							//$summaryRatings = BFCHelper::getResourceRatingAverage($merchant->MerchantId,$resource->ResourceId);
							$ratings = BFCHelper::getResourceRating(0,5,$merchant->MerchantId,$resource->ResourceId,$language,1);
							//$ratings = BFCHelper::getResourceRating(0,5,$resource->ResourceId);
						}
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
		<div id="markerInfo<?php echo $resource->ResourceId ?>" style="display:none">
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

<?php bfi_get_template('shared/merchant_small_details.php',array("merchant"=>$merchant,"routeMerchant"=>$routeMerchant,"resource"=>$resource));  ?>
										
<!-- bficarouselcrosssellresources -->
<div class="bficarouselcrosssellcontainer" style="display:none;">
	<div class="bfi-border bfi-cart-title2"><?php _e('Customers who bought this product also viewed', 'bfi') ?></div>
	<div class="bficarouselcrosssellresources" 
		data-ids="<?php echo $resource->ResourceId  ?>" 
		data-maxitems="10" 
		data-cols="4"
		data-descmaxchars="150" 
		data-theme="0" 
		data-details="<?php _e('Details', 'bfi') ?>"
		></div>
	<!-- end bficarouselcrosssellresources -->
</div>	

<!-- form request -->
    <?php 
				$paramRef = array(
					"merchant"=>$merchant,
					"layout"=>$layout,
					"currentView"=>$currentView,
					"resource"=>$resource,
					"task"=>$task,
					"orderType"=>$orderType,
					"routeThanks"=>$routeThanks,
					"routeThanksKo"=>$routeThanksKo,
					);
				bfi_get_template("/shared/infocontact.php",$paramRef);	
	?>
<script type="text/javascript">
<!--
jQuery(function($) {
	bookingfor.carouselCrossSellResources();
		if (typeof bfiTooltip  !== "function") {
			jQuery.widget.bridge('bfiTooltip', jQuery.ui.tooltip );
		}
	jQuery('[data-toggle="tooltip"]').bfiTooltip({
		position : { my: 'center bottom', at: 'center top-10' },
		tooltipClass: 'bfi-tooltip bfi-tooltip-top '
	}); 
	jQuery('.bfi-title-name span').click(function() {
		jQuery('html, body').animate({ scrollTop: jQuery(".bfi-merchant-simple").offset().top }, 2000);
	});
	<?php if(isset($_REQUEST["fromsearch"])){ ?>	
		jQuery('html, body').animate({scrollTop: jQuery("#bfiavailabilitytab").offset().top}, 1000);
	<?php }  ?>	
		jQuery('.bfi-otherlang').webuiPopover({
				container: document.body,
				closeable: true,
				placement: 'auto-bottom',
				dismissible: true,
				trigger: 'click',
				type: 'html',			
				width: 250,
				style: 'bfi-webuipopover'
			});

});
function bfiGoToTop() {
	this.event.preventDefault();
	jQuery('html, body').animate({ scrollTop: jQuery(".bfi-title-name ").offset().top }, 2000);
};

<?php if (($showMap)) {
	$val= new StdClass;
	$val->Id = $resource->ResourceId ;
	$val->Lat = $resourceLat;
	$val->Long = $resourceLon;
	$val->MarkerType = 0;  //tipo marker 2= eventi con date
	$listResourceMaps[] = $val;
?>
	var listResourceMaps = <?php echo json_encode($listResourceMaps) ?>;

<?php  } ?>

//-->
</script>
