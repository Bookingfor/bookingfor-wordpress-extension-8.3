<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if (COM_BOOKINGFORCONNECTOR_ISBOT) {  // se bot blocco i favoriti
    return;
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
$guidpopup = uniqid('', true);
?>
<div class="bfi-icon-favorite-container <?php echo (!empty( $WrapToContainer )) ?" bfi-pull-right":""; ?>">
    <span class="bfi-icon-favorite bfi-iconcontainer"
          data-itemid="<?php echo $ItemId ?>" 
		  data-itemname="<?php echo $ItemName ?>" 
		  data-itemurl="<?php echo $ItemURL ?>"
          data-groupid="" 
		  data-itemtype="<?php echo $ItemType ?>"
          data-startdate="<?php echo !empty($StartDate)?$StartDate:""; ?>"
          data-enddate="<?php echo !empty($EndDate)?$EndDate:""; ?>"
          data-toggle="tooltip"
		  data-fromtravelplanner="0"
          data-hasfromtime="<?php echo !empty($HasStartTime)?"1" : "0"; ?>"
          data-hastotime="<?php echo !empty($HasEndTime)?"1" : "0"; ?>"
          data-favoriteid="0"
          data-operation-type=""
		  title="<?php _e('Add to favorites', 'bfi') ?>">
        <i class="fa fa-heart"></i><i class="fa fa-heart-o"></i>
    </span>
	<div class="bfi-favoritegroups-container">
	</div>
</div>