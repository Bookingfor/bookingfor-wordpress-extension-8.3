<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if(BFI()->isMerchantPage() || BFI()->isResourcePage() ){

$language = $GLOBALS['bfi_lang'];
$languageForm ='';
$base_url = get_site_url();
if(defined('ICL_LANGUAGE_CODE') &&  class_exists('SitePress')){
		global $sitepress;
		if($sitepress->get_current_language() != $sitepress->get_default_language()){
			$base_url = "/" .ICL_LANGUAGE_CODE;
		}
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
					
$ratings = array();
$reviewavg = 0;
$reviewcount = 0;
if(BFI()->isMerchantPage()){
	$merchant_id = get_query_var( 'merchant_id', 0 );
	$model = new BookingForConnectorModelMerchantDetails;
	$merchant = $model->getItem($merchant_id);	 
	$ratings = BFCHelper::getMerchantRatings(0,5,$merchant->MerchantId,$language,1);
	$reviewavg = isset($merchant->Avg) ? $merchant->Avg->Average : 0;
	$reviewcount = isset($merchant->Avg) ? $merchant->Avg->Count : 0;
}
if(BFI()->isResourcePage()){
	$resource_id = get_query_var( 'resource_id', 0 );
	$model = new BookingForConnectorModelResource;
	$resource = $model->getItem($resource_id);
	$merchant_id = $resource->MerchantId;
	$model = new BookingForConnectorModelMerchantDetails;
	$merchant = $model->getItem($merchant_id);
	
	if (($merchant->RatingsContext == 0 || $merchant->RatingsContext == 1) && !empty($merchant->Avg)) {
		$ratings = BFCHelper::getMerchantRatings(0,5,$merchant->MerchantId,$language,1);
		$reviewavg = $merchant->Avg->Average;
		$reviewcount = $merchant->Avg->Count;
	} else if(($merchant->RatingsContext == 2 || $merchant->RatingsContext == 3) && !empty($resource->Avg)) {
		$reviewavg = $resource->Avg->Average;
		$reviewcount = $resource->Avg->Count;
		$ratings = BFCHelper::getResourceRating(0,5,$merchant->MerchantId,$resource->ResourceId,$language,1);
	}
}
$tripadvisorId = 0;
if (!empty($merchant->tripAdvisorId)) {
	$tripadvisorId = $merchant->tripAdvisorId;
}
//$tripadvisorId = 1184758;
$merchantdetails_page = get_post( bfi_get_page_id( 'merchantdetails' ) );
$url_merchant_page = get_permalink( $merchantdetails_page->ID );

$routeMerchant = $url_merchant_page . $merchant->MerchantId .'-'.BFI()->seoUrl($merchant->Name);

?>
	<?php if($reviewcount>0 || !empty($tripadvisorId)){ ?>
		<div class="bfi-widget-reviews-avg bfi-cursor bfi-avg bfi-panel-toggle">
		<?php if($reviewcount>0){ 
			$totalreviewavg = BFCHelper::convertTotal($reviewavg);
		?>
			<div class="bfi-widget-reviews-avg-value"><?php echo number_format($reviewavg, 1); ?></div>
			<div class="bfi-widget-reviews-avg-container">
				<span class="bfi-avg-value"><?php echo $rating_text['merchants_reviews_text_value_'.$totalreviewavg]; ?></span>
				<span class="bfi-avg-count"><?php echo $reviewcount; ?> <?php _e('Reviews', 'bfi') ?></span>
			</div>
		<?php } elseif(!empty($tripadvisorId)){ 
			echo BFCHelper::bfi_getWidget_tripadvisor($tripadvisorId,1);
		} ?>
		</div>
	<?php } ?>
		<?php if ($ratings != null){ ?>
		<div class="bfi-widget-reviews-list">
			<?php foreach($ratings as $rating){ ?>
			<?php 
			$location = "";
//			if ( $rating->City != ""){
//				$location .= $rating->City . ", ";
//			}
//			$location .= $rating->Nation;
			$t = BFCHelper::convertTotal($rating->Total);
			?>
			<div class="bfi-row bfi-widget-reviews">
				<?php if($rating->NotesData !="") {?>
					<div class="bfi-widget-reviews-descr">
						<span class="expander">&laquo;<?php echo  stripslashes($rating->NotesData); ?>&raquo;</span>
					</div>
				<?php }?>
				<div class="bfi-row bfi-widget-reviews-user">
					<div class="bfi-col-md-2 "><div class="bfi-widget-reviews-user-initial"><?php echo  substr($rating->Name . "  ", 0, 1); ?></div></div>
					<div class="bfi-col-md-10 "><strong><?php echo  $rating->Name; ?></strong><br />
					<span class="flag-icon flag-icon-<?php echo strtolower($rating->Nation) ?>"></span> <?php echo BFCHelper::bfi_get_country_name_by_code($rating->Nation); ?>
					</div>
				</div>
			</div>
			<?php }?>
			<?php 
			if($reviewcount >COM_BOOKINGFORCONNECTOR_ITEMPERPAGE){
			?>
			<div ><a href="<?php echo $routeMerchant; ?>/<?php echo _x('reviews', 'Page slug', 'bfi' ) ?>" class="bfi-btn bfi-alternative bfi-pull-right"><?php _e('all review', 'bfi') ?></a></div>
			<div class="bfi-clearfix"></div>
			<?php 
				}
			?>
			
		</div>	
		<?php }else{
			if(!empty($tripadvisorId)){ 
				echo BFCHelper::bfi_getWidget_tripadvisor($tripadvisorId,2);
			} 		
		}?>
<?php 
} // enfif controllo coerenza pagina
?>
