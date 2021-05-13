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

$details_page = get_post( bfi_get_page_id( 'pointsofinterestdetails' ) );
$url_resource_page = get_permalink( $details_page->ID );
$resourceName = BFCHelper::getLanguage($resource->Name, $GLOBALS['bfi_lang'], null, array('ln2br'=>'ln2br', 'striptags'=>'striptags')); 
$resourceDescription = BFCHelper::getLanguage($resource->Description, $GLOBALS['bfi_lang'], null, array('striptags'=>'striptags', 'bbcode'=>'bbcode','ln2br'=>'ln2br'));
$resourceDescriptionSeo = BFCHelper::getLanguage($resource->Description, $GLOBALS['bfi_lang'], null, array('ln2br'=>'ln2br', 'bbcode'=>'bbcode', 'striptags'=>'striptags'));
$uri = $url_resource_page.$resource->PointOfInterestId.'-'.BFI()->seoUrl($resourceName);
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

$showMap = (($resourceLat != null) && ($resourceLon !=null));

$indirizzo = "";
$cap = "";
$comune = "";
$provincia = "";

if(!empty( $resource->Address )){
	$indirizzo = $resource->Address->Address;
	$cap = $resource->Address->ZipCode;
	$comune = $resource->Address->CityName;
	$provincia = $resource->Address->RegionName;
	$stato = !empty($resource->Address->StateName)?$resource->Address->StateName:"";
}

$resourceRoute = $uri;

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


if(isset($resource->AttachmentsString) && !empty($resource->AttachmentsString)){
	$resourceAttachments = json_decode($resource->AttachmentsString);
	//ordinamento per ordine
	usort($resourceAttachments, function($a, $b) {
		return $a->Order - $b->Order;
	});
}

$imgPopup = COM_BOOKINGFORCONNECTOR_DEFAULTIMAGE;
if (!empty($resource->DefaultImg)){
	$imgPopup =  BFCHelper::getImageUrlResized('poi',$resource->DefaultImg, 'small');
}

?>

<div class="bfi-content bfi-content-poi bfi-content-poi<?php echo $resource->PointOfInterestId ?>">
    <div class="bfi-row">
        <div class="bfi-col-md-10">
            <div class="bfi-title-name ">
				<?php echo  $resourceName?>
			</div>
			<div class="bfi-address bfi-hideonextra">
				<?php if (($showMap)) {?><a class="bfi-map-link bfiopenpopupmap" rel="#bfimaptab"><?php } ?><i class="fa fa-map-marker fa-1"></i><?php if (($showMap)) {?></a><?php } ?> <span class="street-address"><?php echo $indirizzo ?></span>, <span class="postal-code "><?php echo  $cap ?></span> <span class="locality"><?php echo $comune ?></span>, <span class="region"><?php echo  $stato ?></span>
				<?php if(isset($resource->Address->CenterDistance)) { ?>
					<span class="bfi-centerdistance" data-toggle="tooltip" title="<?php _e('This is the straight-line distance on the map. Actual travel distance may vary.', 'bfi') ?>">(<i class="fa fa-road" aria-hidden="true"></i> <?php echo BFCHelper::formatDistanceUnits($resource->Address->CenterDistance)?> <?php _e('from centre', 'bfi') ?>)</span>
				<?php } ?> <?php if (($showMap)) {?>- <a class="bfi-map-link bfiopenpopupmap" rel="#bfimaptab"><?php } ?><?php _e('show map', 'bfi') ?><?php if (($showMap)) {?></a><?php } ?>
				
			</div>
        </div>
        <div class="bfi-col-md-2 bfi-text-right">
<?php 
				$favoriteModel = array(
					"ItemId"=>$resource->PointOfInterestId,
					"ItemName"=>BFCHelper::string_sanitize($resourceName),
					"ItemType"=>4,
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
</div>

	<div class="bfi-resourcecontainer-gallery">
	<?php  
			$bfiSourceData = 'poi';
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

<div class="bfi-content bfi-content-poi bfi-content-poi<?php echo $resource->PointOfInterestId ?>">
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

	<div class="bfi-clearfix"></div>
<!-- event list -->
		<?php 
		if(isset($resource->NearbyEventsString) && !empty($resource->NearbyEventsString)){
		    $results = json_decode($resource->NearbyEventsString);
			$page = 1 ;
			$pages = 0 ;
			$items = $results;
			$total = count($items);
			$currSorting = "distance|asc";
			$listName = "";
			$listNameAnalytics = 0;
			if ($total>6) {
			    $items = array_slice($items, 0, 6);
				$total = count($items);
			}
			?>
			<div class="bfi-titlenextevents"><?php _e('Next events near ', 'bfi') ?></div>
			<?php 
			
			bfi_get_template("search/event_results.php",array( "hidetop"=>1,"results"=>$results,"total"=>$total,"items"=>$items,"pages"=>$pages,"page"=>$page,"listNameAnalytics"=>$listNameAnalytics,"currSorting"=>$currSorting));	
		}
		?>

</div>
	<?php if ($showMap){?>
		<div id="markerInfo<?php echo $resource->PointOfInterestId ?>" style="display:none">
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
<?php if (($showMap)) {
	$val= new StdClass;
	$val->Id = $resource->PointOfInterestId ;
	$val->Lat = $resourceLat;
	$val->Long = $resourceLon;
	$val->Name = $resourceName;
	$val->MarkerType = 3;  //tipo marker 2= eventi con date
	$listResourceMaps[] = $val;
?>
<script type="text/javascript">
<!--
	var listResourceMaps = <?php echo json_encode($listResourceMaps) ?>;
//-->
</script>
<?php  } ?>
