<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

//print_r($offers);

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

$merchantdetails_page = get_post( bfi_get_page_id( 'merchantdetails' ) );
$url_merchant_page = get_permalink( $merchantdetails_page->ID );

//$page = isset($_GET['paged']) ? $_GET['paged'] : 1;
$page = bfi_get_current_page() ;


?>
<div class="bfi-content">

<div class="bfi-search-menu hidden">
	<div class="bfi-view-changer">
		<div class="bfi-view-changer-selected"><?php echo _e('List' , 'bfi') ?></div>
		<div class="bfi-view-changer-content">
			<div id="bfi-list-view"><?php echo _e('List' , 'bfi') ?></div>
			<div id="bfi-grid-view" class="bfi-view-changer-grid"><?php echo _e('Grid' , 'bfi') ?></div>
		</div>
	</div>
</div>
<div class="bfi-clearfix"></div>
	<?php if ($offers != null){ ?>
		<div id="bfi-list" class="bfi-row bfi-list">
			<?php foreach($offers as $currKey=>$resource){ ?>
			<?php
			
			$url_merchant_page = get_permalink( $merchantdetails_page->ID );
			$routeMerchant = $url_merchant_page . $resource->MerchantId.'-'.BFI()->seoUrl($resource->MrcName);
			
			
		$resourceImageUrl = BFI()->plugin_url() . "/assets/images/defaults/default-s6.jpeg";
		$resourceName = BFCHelper::getLanguage($resource->Name, $language, null, array('ln2br'=>'ln2br', 'striptags'=>'striptags')); 
		$resourceDescription = BFCHelper::getLanguage($resource->Description, $language, null, array('ln2br'=>'ln2br', 'bbcode'=>'bbcode', 'striptags'=>'striptags')); 
//		$currUriresource = $uri.$resource->VariationPlanId.'-'.BFI()->seoUrl($resourceName);
		
		$resourceRoute = $routeMerchant.'/'._x('offer', 'Page slug', 'bfi' ).'/'. $resource->VariationPlanId . '-' . BFCHelper::getSlug($resourceName);
		if(!empty($resource->DefaultImg)){
			$resourceImageUrl = BFCHelper::getImageUrlResized('variationplans',$resource->DefaultImg, 'medium');
		}
		$resourceNameTrack =  BFCHelper::string_sanitize($resourceName);
		$merchantNameTrack =  BFCHelper::string_sanitize($resource->MrcName);
		$merchantCategoryNameTrack = "";

			?>
				<div class="bfi-col-sm-6 bfi-item">
					<div class="bfi-row bfi-sameheight" >
						<div class="bfi-col-sm-3 bfi-img-container">
							<a href="<?php echo $resourceRoute ?>" style='background: url("<?php echo $resourceImageUrl; ?>") center 25% / cover;' <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> class="eectrack" data-type="Offer" data-id="<?php echo $resource->VariationPlanId?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>" data-list="<?php echo $analyticsListName; ?>"><img src="<?php echo $resourceImageUrl; ?>" class="bfi-img-responsive" /></a> 
						</div>
						<div class="bfi-col-sm-9 bfi-details-container">
							<!-- merchant details -->
							<div class="bfi-row" >
								<div class="bfi-col-sm-10">
									<div class="bfi-item-title">
                                        <h3><?php echo $resource->MrcName?></h3>
										<a href="<?php echo $resourceRoute ?>" id="nameAnchor<?php echo $resource->VariationPlanId?>" <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> class="eectrack" data-type="Offer" data-id="<?php echo $resource->VariationPlanId?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>" data-list="<?php echo $analyticsListName; ?>"><?php echo  $resource->Name ?></a> 
									</div>
									<div class="bfi-description bfi-shortentextlong"><?php echo $resourceDescription ?></div>
								</div>
							</div>
							<div class="bfi-clearfix bfi-hr-separ"></div>
							<!-- end merchant details -->
							<!-- resource details -->
							<div class="bfi-row" >
								<div class="bfi-col-sm-8">
								
								</div>
								<div class="bfi-col-sm-4 bfi-text-right">
										<a href="<?php echo $resourceRoute ?>" class="bfi-btn eectrack" <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?> data-type="Offer" data-id="<?php echo $resource->VariationPlanId?>" data-index="<?php echo $currKey?>" data-itemname="<?php echo $resourceNameTrack; ?>" data-category="<?php echo $merchantCategoryNameTrack; ?>" data-brand="<?php echo $merchantNameTrack; ?>" data-list="<?php echo $analyticsListName; ?>"><?php echo _e('Details' , 'bfi')?></a>
								</div>
							</div>
							<!-- end resource details -->
							<div class="bfi-clearfix"></div>
							<!-- end price details -->
						</div>
					</div>
				</div>
			<?php } ?>
		</div>
	<?php }else{?>
	<div class="bfi-noresults">
		<?php _e('No Results Found', 'bfi'); ?>
	</div>
	<?php } ?>	
