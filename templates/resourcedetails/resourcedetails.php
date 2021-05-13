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

$accommodationdetails_page = get_post( bfi_get_page_id( 'accommodationdetails' ) );
$url_resource_page = get_permalink( $accommodationdetails_page->ID );
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
//$tagsIncluded = array();
//$tagsNotIncluded = array();
//if(!empty( $resource->Tags)){
//	foreach ( $resource->Tags as $gr ) {
//		$subGroupRefIds = array_filter(array_map(function ($i) { return $i->SubGroupRefId; }, $gr->Tags));
//		$gr->subGroupRefIds = $subGroupRefIds;
//		if (!empty($gr->subGroupRefIds )) {
//			$currTag = $gr->Tags;
//			foreach($currTag as $item) {
//				if ($item->TagValue=="0") {
//					$tagsIncluded[] = $item;
//				}
//				if ($item->TagValue=="-1") {
//					$tagsNotIncluded[] = $item;
//				}
//			}
//		}
//	}
//}



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

<div class="bfi-content bfi-content-rescat bfi-content-rescat<?php echo $resource->CategoryId ?> <?php echo ($resourcetype == bfi_ItemType::Rental) ?"bfi-res-tabs":"";?> bfi-details-resource ">	
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
			<?php if ($showReview){?><li class="bfi-hidden-xs bfi-hidden-sm"><a rel=".bfi-ratingslist"><?php echo  _e('Reviews' , 'bfi') ?></a></li><?php } ?>
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
			<div class="bfi-pull-right bfi-btn-book-top">
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
			<div class="bfi-title-name bfi-hideonextra"><h1><span class="bfi-title-name-badge"><?php echo  $resource->CategoryName?></span> <?php echo  $resourceName?> </h1><?php if($resourcetype == bfi_ItemType::Rental) { ?><span class="bfi-rental-caption"><?php _e('or similar', 'bfi') ?></span><?php } ?>
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
			<div class="bfi-clearfix"></div>
			<div class="bfi-pull-right">
				<a rel="#divcalculator" class="bfi-btn bfi-alternative bfi-btn-calc"><?php echo _e('Book now','bfi'); ?></a>
			</div>	
		</div>	
		<?php } ?>
	</div>	
	</div>	
	<div class="bfi-clearfix"></div> 

				<?php 
					switch ($resourcetype) {
						case bfi_ItemType::Rental:
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
if (false) {
	// icon tag Highlight
		if($resourcetype != bfi_ItemType::Rental && !empty( $tagsHighlight) || (isset($resource->Area) && $resource->Area>0  ) ){
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
    
}

?>

<div class="bfi-content bfi-content-rescat bfi-content-rescat<?php echo $resource->CategoryId ?> bfi-details-resource " >	
<?php 
$fromSearch =  BFCHelper::getVar('fromsearch','0');
?>
	<div id="navbookingfordetails" class="">

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
						<?php echo $resourceDescription ?><br /><br />
					</div>
					<?php
// for evidence
					if(!empty( $tagsHighlight)){
						$tagsList = implode(', ',array_filter(array_map(function ($i) { 
											$strHtml = '<span>';
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

//							return '<i class="fa fa-check"></i>&nbsp;' . $i->Name; 
							}, $tagsHighlight)));
						?>
							<div class="bfi-group-tags bfi-tags-highlight-container">
							<h4 class="bfi-title-content" ><?php echo  _e('Most popular facilities for longer stays', 'bfi') ?></h4>
								<div class="bfi-tags-highlight"><?php echo $tagsList ?></div>
							</div>	
						<?php 
					}					
					?>
					<?php 
					if(isset($resource->TouristRentalCode) && !empty($resource->TouristRentalCode)) {
					?>
							<div class="bfi-touristrentalcode-container">
								<h3 class="bfi-description-sub-header" style="display:inline-block"><?php _e('IDENTIFICATION CODE - TOURIST LEASE', 'bfi') ?>:</h3>
								<div class="bfi-touristrentalcode" style="display:inline-block">&nbsp;<?php echo $resource->TouristRentalCode ?></div>
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
</div>
<!--// availability-->
	<?php if(!$resource->IsCatalog){ ?>
		<div id="bfiavailabilitytab">
			<!-- calc -->
			<a name="calc"></a>
			<div id="divcalculator">
				<?php 
				$resourceId = $resource->ResourceId;
				$resourcegroupId = 0;

				if (isset($resource->ParentProductId) && !empty($resource->ParentProductId ) ) {
					$resourcegroupId = $resource->ParentProductId;
				}
				$refreshSearch = 0;
				bfi_get_template("shared/search_details.php",array("resource"=>$resource,"merchant"=>$merchant,"resourceId"=>$resourceId,"resourcegroupId"=>$resourcegroupId,"currencyclass"=>$currencyclass,"refreshSearch"=>$refreshSearch));		
 ?>
					

			</div>
		</div>
	<?php } ?>
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
	<?php  bfi_get_template('shared/merchant_small_details.php',array("merchant"=>$merchant,"routeMerchant"=>$routeMerchant,"resource"=>$resource));  ?>
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
