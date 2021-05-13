<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$language = $GLOBALS['bfi_lang'];
$languageForm ='';
if(defined('ICL_LANGUAGE_CODE') &&  class_exists('SitePress')){
		global $sitepress;
		if($sitepress->get_current_language() != $sitepress->get_default_language()){
			$languageForm = "/" .ICL_LANGUAGE_CODE;
		}
}
$base_url = get_site_url();

$showmap = true;
if($total<1){
	$showmap = false;
}
if ($showmap && COM_BOOKINGFORCONNECTOR_USE_OPENSTREETMAP) {
	wp_enqueue_script('bfileaflet');
	wp_enqueue_script('bfileafletcontrolcustom');
}

$fromsearchparam = "/?lna=".$listNameAnalytics;

?>
<div id="bfi-merchantlist">
		<?php if ($total > 0){ 
			bfi_get_template("searchonsell/list-resources.php",array("results"=>$results,"total"=>$total,"items"=>$items,"pages"=>$pages,"page"=>$page,"currencyclass"=>$currencyclass,"listNameAnalytics"=>$listNameAnalytics));	
			 }else{ ?>
			<div>
			<?php _e('No result available', 'bfi') ?>
			</div>
		<?php } ?>

		<div class="bfi-clearfix"></div>		
</div>
