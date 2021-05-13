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

$merchantdetails_page = get_post( bfi_get_page_id( 'merchantdetails' ) );
$url_merchant_page = get_permalink( $merchantdetails_page->ID );
$routeMerchant = $url_merchant_page . $merchant->MerchantId.'-'.BFI()->seoUrl($merchant->Name);

$fromSearch =  BFCHelper::getVar('fromsearch','0');

if(!empty($fromSearch)){
	$routeMerchant .= "/?fromsearch=1";
}
?>

<div class="mapdetails">
<h2 class="bfi-title-name"><a class="com_bookingforconnector_merchantdetails-nameAnchor" href="<?php echo $routeMerchant ?>"><?php echo  $merchant->Name?></a>
	<span class="bfi-item-rating">
<?php 
				$ratingModel = array(
					"ratingModel"=>$merchant
				);
				bfi_get_template("shared/stars_rating.php",$ratingModel);
?>
  </span>
</h2>
</div>
