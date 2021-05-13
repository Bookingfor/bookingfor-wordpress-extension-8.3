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
//reset filter
BFCHelper::setFirstFilterDetailsParamsSession(null,"mrc".$merchant->MerchantId);

$merchantdetails_page = get_post( bfi_get_page_id( 'merchantdetails' ) );
$url_merchant_page = get_permalink( $merchantdetails_page->ID );
$routeMerchant = $url_merchant_page . $merchant->MerchantId.'-'.BFI()->seoUrl($merchant->Name);

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


//$merchantRules ='';
//if(!empty($merchant->Rules)){
//	$merchantRules = BFCHelper::getLanguage($merchant->Rules, $language, null, array('ln2br'=>'ln2br', 'striptags'=>'striptags', 'bbcode'=>'bbcode'));
//}
$resourceLat = null;
$resourceLon = null;

if (!empty($merchant->XGooglePos) && !empty($merchant->YGooglePos)) {
	$resourceLat = $merchant->XGooglePos;
	$resourceLon = $merchant->YGooglePos;
}
if(!empty($merchant->XPos)){
	$resourceLat = $merchant->XPos;
}
if(!empty($merchant->YPos)){
	$resourceLon = $merchant->YPos;
}
$showMap = (($resourceLat != null) && ($resourceLon !=null) ); 
if ($showmap && COM_BOOKINGFORCONNECTOR_USE_OPENSTREETMAP) {
	wp_enqueue_script('bfileaflet');
	wp_enqueue_script('bfileafletcontrolcustom');
}

$modelmerchant =  new BookingForConnectorModelMerchantDetails;

$fromSearch =  BFCHelper::getVar('fromsearch','0');

$routeSearch = $routeMerchant;
if(!empty($fromSearch)){
	$routeSearch .= "/?task=getMerchantResources&fromsearch=1";
}else{
	$routeSearch .= "/?task=getMerchantResources";
}

$reviewavg = isset($merchant->Avg) ? $merchant->Avg->Average : 0;
$reviewcount = isset($merchant->Avg) ? $merchant->Avg->Count : 0;
$resourceDescription = BFCHelper::getLanguage($merchant->Description, $language, null, array( 'striptags'=>'striptags', 'bbcode'=>'bbcode','ln2br'=>'ln2br')) ;
$merchantName = BFCHelper::getLanguage($merchant->Name, $language, null, array('ln2br'=>'ln2br', 'striptags'=>'striptags')); 

$tripadvisorId = 0;
if (!empty($merchant->tripAdvisorId)) {
	$tripadvisorId = $merchant->tripAdvisorId;
}

$tagsHighlight = array();
if(!empty( $merchant->Tags)){
	foreach ( $merchant->Tags as $gr ) {
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
$imgPopup = COM_BOOKINGFORCONNECTOR_DEFAULTIMAGE;
if (!empty($merchant->LogoUrl)){
	$imgPopup =  BFCHelper::getImageUrlResized('merchant',$merchant->LogoUrl, 'logobig');
}

/*---------------IMPOSTAZIONI SEO----------------------*/

	$payload["@type"] = "Organization";
	$payload["@context"] = "http://schema.org";
	$payload["name"] = $merchantName;
	$payload["description"] = BFCHelper::getLanguage($merchant->Description, $language, null, array( 'striptags'=>'striptags', 'bbcode'=>'bbcode','ln2br'=>'ln2br'));
	$payload["url"] = ($isportal)? $routeMerchant: $base_url; 
	if (!empty($merchant->LogoUrl)){
		$payload["logo"] = "https:".BFCHelper::getImageUrlResized('merchant',$merchant->LogoUrl, 'logobig');
	}
	if (!empty($merchant->Avg)){
		$aggregateRating["@type"] = "AggregateRating";
		$aggregateRating["ratingValue"] = number_format($merchant->Avg->Average, 1) ."";
		$aggregateRating["reviewCount"] = $merchant->Avg->Count."";
		$aggregateRating["bestRating"] = "10";
		$aggregateRating["worstRating"] = "1";
		$payload["aggregateRating"] = $aggregateRating;
	}
/*--------------- FINE IMPOSTAZIONI SEO----------------------*/
	$services = [];
	$listServices = array();
	if (!empty($merchant->ServiceIdList)){
		$listServices = explode(",", $merchant->ServiceIdList);
		$services = BFCHelper::GetServicesByIds($merchant->ServiceIdList,$language);
		$services = array_filter($services, function($p) use ($listServices) {return in_array($p->ServiceId,$listServices);});
	}

$favoriteModel = array(
	"ItemId"=>$merchant->MerchantId,
	"ItemName"=>BFCHelper::string_sanitize($merchant->Name),
	"ItemType"=>0,
	"ItemURL"=>BFCHelper::bfi_get_curr_url(),
	"WrapToContainer"=>1,
	);

?>
<script type="application/ld+json">
<?php echo json_encode($payload); ?>
</script>
<div class="bfi-content bfi-content-mrc bfi-content-mrc<?php echo $merchant->MerchantId?> bfi-hideonextra">
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
	<?php if($reviewcount>0 || !empty($tripadvisorId)){ ?>
	<div class="bfi-row bfi-margin-bottom10 bfi-row-background">
		<div class="bfi-col-md-8">
	<?php }else{ ?>
	<div class="bfi-row bfi-margin-bottom10 bfi-row-background">
		<div class="bfi-col-md-12">
	<?php } ?>
<?php 
if(empty($reviewcount) && empty($tripadvisorId)){
		?><div class="bfi-pull-right">
			<div class="bfi-pull-right bfi-btn-book-top">
				<a rel="#divcalculator" class="bfi-btn bfi-alternative bfi-btn-calc"><?php echo _e('Book now','bfi'); ?></a>
			</div> 
			<div class="bfi-pull-right">
				<?php bfi_get_template("shared/favorite_icon.php",$favoriteModel);?>
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
		<div class="bfi-title-name bfi-hideonextra"><h1><span class="bfi-title-name-badge"><?php echo  $merchant->MainCategoryName?></span> <?php echo  $merchant->Name?></h1>
			<span class="bfi-item-rating">
<?php 
				$ratingModel = array(
					"ratingModel"=>$merchant
				);
				bfi_get_template("shared/stars_rating.php",$ratingModel);
?>
			</span>
		</div>
		<div class="bfi-address bfi-hideonextra">
			<?php if (($showMap)) {?><a class="bfi-map-link bfiopenpopupmap" rel="#bfimaptab"><?php } ?><i class="fa fa-map-marker fa-1"></i><?php if (($showMap)) {?></a><?php } ?> <span class="street-address"><?php echo $indirizzo ?></span>, <span class="postal-code "><?php echo  $cap ?></span> <span class="locality"><?php echo $comune ?></span>, <span class="region"><?php echo  $stato ?></span>
			<?php if(isset($merchant->CenterDistance)) { ?>
				<span class="bfi-centerdistance" data-toggle="tooltip" title="<?php _e('This is the straight-line distance on the map. Actual travel distance may vary.', 'bfi') ?>">(<i class="fa fa-road" aria-hidden="true"></i> <?php echo BFCHelper::formatDistanceUnits($merchant->CenterDistance)?> <?php _e('from centre', 'bfi') ?>)</span>
			<?php } ?> <?php if (($showMap)) {?>- <a class="bfi-map-link bfiopenpopupmap" rel="#bfimaptab"><?php } ?><?php _e('show map', 'bfi') ?><?php if (($showMap)) {?></a><?php } ?>
			
		</div>
	<?php if($reviewcount>0 || !empty($tripadvisorId)){ ?>
		</div>	
		<div class="bfi-col-md-4">
			<div class="bfi-pull-right bfi-btn-book-top">
				<a rel="#divcalculator" class="bfi-btn bfi-alternative bfi-btn-calc"><?php echo _e('Book now','bfi'); ?></a>
			</div>	
			<div class="bfi-cursor bfi-avg bfi-pull-right" id="bfi-avgreview" style="display:inline-flex;margin-left:10px;">
			<?php if($reviewcount>0){ 
				$totalreviewavg = BFCHelper::convertTotal($reviewavg);
			?>
			<div class="bfi-widget-reviews-avg-container">
				<a class="bfi-avg-value bfi-panel-toggle"><?php echo $rating_text['merchants_reviews_text_value_'.$totalreviewavg]; ?></a>
				<a class="bfi-avg-count bfi-panel-toggle"><?php echo $reviewcount; ?> <?php _e('Reviews', 'bfi') ?></a>
			</div>
			<div class="bfi-widget-reviews-avg-value" style="margin-left: 5px;"><?php echo number_format($reviewavg, 1); ?></div>
			<?php } elseif(!empty($tripadvisorId)){ 
				echo BFCHelper::bfi_getWidget_tripadvisor($tripadvisorId,1);
			} ?>
			</div>
			<div class="bfi-pull-right">
				<?php bfi_get_template("shared/favorite_icon.php",$favoriteModel);?>
			</div> 
			<div class="bfi-addtoany bfi-pull-right">
				<!-- AddToAny BEGIN -->
				<a class="a2a_dd" href="https://www.addtoany.com/share "title="<?php _e('Share', 'bfi') ?>" ><i class="far fa-share-alt"></i></a>
				<script async src="https://static.addtoany.com/menu/page.js"></script>
				<!-- AddToAny END -->
			</div>
		</div>	
	</div>	
	<?php }else{ ?>
		</div>	
	</div>	
	<?php } ?>

</div>
	
	<div class="bfi-resourcecontainer-gallery">
	<?php  
			$bfiSourceData = 'merchant';
			$bfiImageData = null;
			$bfiVideoData = null;
			if(!empty($merchant->ImageData)) {
				$bfiImageData = $merchant->ImageData;
			}
			if(!empty($merchant->VideoData)) {
				$bfiVideoData = $merchant->VideoData;
			}
			bfi_get_template("shared/gallery_type2.php",array("merchant"=>$merchant,"bfiSourceData"=>$bfiSourceData,"bfiImageData"=>$bfiImageData,"bfiVideoData"=>$bfiVideoData));	
	?>
	</div>
<?php 
if (false) {
// icon tag Highlight
	if(!empty( $tagsHighlight)){
		?>
		<div class="bfi-group-tags-icons">
		<?php 
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


<div class="bfi-content bfi-content-mrc bfi-content-mrc<?php echo $merchant->MerchantId?>">
	<div class="">
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
						$tagsList = implode(' ',array_filter(array_map(function ($i) { 
											$strHtml = '<span class="bfi-tag-highlight">';
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
				</div>	
			</div>
		</div>
	<?php } ?>
	
<!--// availability-->	
	<?php if ($merchant->HasResources){?>
		<div id="bfiavailabilitytab">
<?php 
//se c'è una vendita per mappa
if($merchant->HasSelectionMap) { 

?>
	<?php if ($showMap){?>
			<!-- calc -->
			<a name="calc"></a>
			<div id="divcalculator">
				<?php 
				$resourceId = 0;
				$resourcegroupId = 0;
				$refreshSearch = 0;
				if (!empty($fromSearch)) {
					$refreshSearch = 1;				    
				}
				bfi_get_template("shared/search_mapsells.php",array("mapConfiguration"=>$merchant->MapConfiguration,"resourceLat"=>$resourceLat,"resourceLon"=>$resourceLon,"merchant"=>$merchant,"resourceId"=>$resourceId,"resourcegroupId"=>$resourcegroupId,"currencyclass"=>$currencyclass,"refreshSearch"=>$refreshSearch));	
				?>
			</div>
	<?php } ?>	

<?php
// prenotazione classica	
} else{ 
	

// se ricerca per spiaggia allora ricerco solo su gruppi di risorse
	switch ($merchant->TypeId) {
		case 5: // spiagge
			?>	
			<!-- calc -->
			<a name="calc"></a>
			<div id="divcalculator">
				<?php 
				$resourceId = 0;
				$resourcegroupId = 0;
				$refreshSearch = 0;
				if (!empty($fromSearch)) {
					$refreshSearch = 1;				    
				}
				bfi_get_template("shared/search_details_mapsells.php",array("mapConfiguration"=>$merchant->MapConfiguration,"resourceLat"=>$resourceLat,"resourceLon"=>$resourceLon,"merchant"=>$merchant,"resourceId"=>$resourceId,"resourcegroupId"=>$resourcegroupId,"currencyclass"=>$currencyclass,"refreshSearch"=>$refreshSearch));	
				?>
			</div>
			<?php 

			break;
		default:      


?>
		<!-- pacchetto -->
			<?php
			$currPackage = BFCHelper::getVar('bfipck');
			if(!empty($currPackage)){
				$currPackage= json_decode(gzinflate(hex2bin($currPackage)));
				$resourceId = 0;
				$resourcegroupId = 0;
				bfi_get_template('shared/package_details.php',array("currPackage"=>$currPackage,"merchant"=>$merchant,"resourceId"=>$resourceId,"resourcegroupId"=>$resourcegroupId,"currencyclass"=>$currencyclass));	
			}else{
			?>
			<!-- calc -->
			<a name="calc"></a>
			<div id="divcalculator">
				<?php 
				$resourceId = 0;
				$resourcegroupId = 0;
				$refreshSearch = 0;
				if (!empty($fromSearch)) {
					$refreshSearch = 1;				    
				}
				bfi_get_template("shared/search_details.php",array("merchant"=>$merchant,"resourceId"=>$resourceId,"resourcegroupId"=>$resourcegroupId,"currencyclass"=>$currencyclass,"refreshSearch"=>$refreshSearch));	
				?>
			</div>
			<?php 
			}
			?><?php 
	} // end switch ricerca
}
?>



			
		</div>
	<?php } ?>
	<?php if ($showMap){?>
		<div id="markerInfo<?php echo $merchant->MerchantId ?>" style="display:none">
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
					"ratingModel"=>$merchant
				);
				bfi_get_template("shared/stars_rating.php",$ratingModel);
?>
								</span>
								<?php echo  $merchant->Name?>
							</div>
							<div class=" bfi-street-address-block">
									<span class="bfi-street-address"><?php echo $indirizzo ?></span>, <span class="postal-code "><?php echo $cap ?></span> <span class="locality"><?php echo $comune ?></span> <span class="state">, <?php echo $stato ?></span><br />
							</div>
						</div>
					</div>			
			</div>
		</div>
	<?php } ?>
<!--// RatingsContext-->
<?php if ($merchant->RatingsContext ==1 || $merchant->RatingsContext ==3){?>
<!-- Slide in panel -->
        <div id="bfiratingslisttab">
			<div class="bfi-slide-panel">
				<span class="bfi-panel-close bfi-panel-toggle"></span>
						<div class="bfi-ratingslist">
						<?php
							$summaryRatings = $merchant->Avg;
							$ratings = BFCHelper::getMerchantRatings(0,5,$merchant->MerchantId,$language,1);
						?>
						<?php  bfi_get_template('merchantdetails/merchant-ratings.php',array("merchant"=>$merchant,"summaryRatings"=>$summaryRatings,"ratings"=>$ratings,"routeMerchant"=>$routeMerchant,"merchantName"=>$merchantName));  ?>
						<?php if(!empty($tripadvisorId)){ 
							echo BFCHelper::bfi_getWidget_tripadvisor($tripadvisorId,2);
						} ?>
						</div>
			</div>
		</div>

<?php } ?>
</div>
	<div class="bfi-clearfix"></div>
	<?php bfi_get_template('shared/tagslist.php',array("tags"=>$merchant->Tags));  ?>
					<?php if(isset($merchant->AttachmentsString) && !empty($merchant->AttachmentsString)){
						$resourceAttachments = json_decode($merchant->AttachmentsString);
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
	<?php bfi_get_template('shared/merchant_small_details.php',array("merchant"=>$merchant,"routeMerchant"=>$routeMerchant));  ?>
<!-- form request -->
    <?php 
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
		id:'<?php echo $merchant->MerchantId ?>',
		name:'<?php echo  BFCHelper::escapeJavaScriptText($payload["name"]) ?>',
		img:'<?php echo (!empty($payload["logo"])) ?$payload["logo"]:""; ?>',
		url: '<?php echo $payload["url"] ?>'
	}
	bookingfor.bfiAddMerchant(newMerchant);
});
<?php if (($showMap)) {
	$val= new StdClass;
	$val->Id = $merchant->MerchantId ;
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