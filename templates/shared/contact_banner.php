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
		// banner contattaci
		if ($showcontactbanner && (!empty(COM_BOOKINGFORCONNECTOR_SHOWCONTACTBANNERFORM) || !empty(COM_BOOKINGFORCONNECTOR_CONTACTBANNERPHONE) || !empty(COM_BOOKINGFORCONNECTOR_CONTACTBANNERPAGE) ) ) {
			$showcontactbanner  = false; // lo faccio vedere solo una volta
			?>
			<div class="bfi-contactbanner bfi-col-sm-6 bfi-item bfi-list-group-item " style="width:100%" >
				<div class="bfi-contactbanner-title"><?php _e("Haven't found what you were looking for yet?", 'bfi') ?></div>
				<div class="bfi-contactbanner-container">
				<?php 
				
				if (!empty(COM_BOOKINGFORCONNECTOR_SHOWCONTACTBANNERFORM)) {
					$url_genericrequest = BFCHelper::getPageUrl('genericrequest');
					?>
					<a class="" href="<?php echo $url_genericrequest ?>" ><i class="far fa-envelope"></i><?php if(!COM_BOOKINGFORCONNECTOR_ISMOBILE) { ?><br /><?php _e('Contact us', 'bfi') ?><?php } ?></a>
					
					<?php 
				}
				if (!empty(COM_BOOKINGFORCONNECTOR_CONTACTBANNERPHONE)) {
					?>
					 <a href="tel:<?php echo COM_BOOKINGFORCONNECTOR_CONTACTBANNERPHONE ?>"><i class="far fa-phone"></i><?php if(!COM_BOOKINGFORCONNECTOR_ISMOBILE) { ?><br /><?php _e('Call us', 'bfi') ?><?php } ?></a>
					<?php 
				}
				if (!empty(COM_BOOKINGFORCONNECTOR_CONTACTBANNERPHONEWHATSAPP)) {
					?>
					 <a href="https://wa.me/<?php echo COM_BOOKINGFORCONNECTOR_CONTACTBANNERPHONEWHATSAPP ?>"><i class="bfi-whatsapp-icon"></i><?php if(!COM_BOOKINGFORCONNECTOR_ISMOBILE) { ?><br /><?php _e('Call us', 'bfi') ?><?php } ?></a>
					<?php 
				}
				if (!empty(COM_BOOKINGFORCONNECTOR_CONTACTBANNERPAGE)) {
					$url_contactbannerpage = BFCHelper::getPageUrlbyIdtranslated(COM_BOOKINGFORCONNECTOR_CONTACTBANNERPAGE);
					?>
					<a href="<?php echo $url_contactbannerpage ?>" ><i class="far fa-comments"></i><?php if(!COM_BOOKINGFORCONNECTOR_ISMOBILE) { ?><br /><?php _e('Talk to us', 'bfi') ?><?php } ?></a> 
					<?php 
				}
				?>
				</div>
			</div>
		<?php 
		}
		?>
