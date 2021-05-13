<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if (!COM_BOOKINGFORCONNECTOR_DISALBLEINFOFORM) {
	$base_url = get_site_url();
	$formRoute = $base_url."/bfi-api/v1/task/?task=".$task ."&simple=1" ;
	$language = $GLOBALS['bfi_lang'];
	if(defined('ICL_LANGUAGE_CODE') &&  class_exists('SitePress')){
		$language = ICL_LANGUAGE_CODE;
		global $sitepress;
		if($sitepress->get_current_language() != $sitepress->get_default_language()){
			$base_url .= "/".ICL_LANGUAGE_CODE;
		}
	}
	$sitename = get_bloginfo();
	$formlabel = COM_BOOKINGFORCONNECTOR_FORM_KEY;
	$minCapacityPaxes = 0;
	$maxCapacityPaxes = 12;
	
	$idrecaptcha = uniqid("bfirecaptcha");

	$routePrivacy = str_replace("{language}", substr($language,0,2), COM_BOOKINGFORCONNECTOR_PRIVACYURL);
	$routeTermsofuse = str_replace("{language}", substr($language,0,2), COM_BOOKINGFORCONNECTOR_TERMSOFUSEURL);
	$routeNewsletter = str_replace("{language}", substr($language,0,2), COM_BOOKINGFORCONNECTOR_NEWSLETTERURL);
	$routeMarketing = str_replace("{language}", substr($language,0,2), COM_BOOKINGFORCONNECTOR_MARKETINGURL);
	$routeDataprofiling = str_replace("{language}", substr($language,0,2), COM_BOOKINGFORCONNECTOR_DATAPROFILINGURL);

	$infoSendBtn = sprintf(__('Choosing <b>Send</b> means that you agree to <a href="%3$s" target="_blank">Terms of use</a> of %1$s.' ,'bfi'),$sitename,$routePrivacy,$routeTermsofuse);

	 $portalinfo =  BFCHelper::getSubscriptionInfos();
	?>
	<div class="bfi-contactsfaq-title" >
		Domande e risposte sulla struttura
	</div>
	<div class="bfi-contactsfaq-heading">
		Cerchi più informazioni? Invia una domanda alla struttura.
	</div>
	<div class="bfi-contactsfaq-openpopup">
		Fai una domanda
	</div>

	<div class="bfi-contacts bfi-contactsfaq" style="display:none;">
		<div class="bfi-form-contacts" >
			<form method="post" class="form-validate bfi-form-infocontacts bfi-form-infocontactsfaq " action="<?php echo $formRoute; ?>" novalidate="novalidate" data-blockdays="" data-blockmonths="">
				<div class="bfi-form-field">
					<?php if(isset($resource)) {?>		
						<div class="">
							<?php echo $resource->Name; ?>
						</div><!--/span-->
					<?php } ?>	
					<?php if(isset($resource)) {?>		
							<input type="hidden" id="resourceId" name="form[resourceId]" value="<?php echo $resource->ResourceId;?>" > 
					<?php } ?>
					<label>Scrivi qui la tua domanda:</label>
					<textarea name="form[note]" style="height:100px;width: 100%;"  placeholder="<?php _e('Special Requests', 'bfi'); ?>"  data-rule-nourl="true"  data-msg-nourl="<?php _e('No URLs allowed!', 'bfi') ?>"></textarea>    
					<label>Indirizzo e-mail</label>
					<input placeholder="<?php _e('Email', 'bfi'); ?> *" type="email" value="" size="50" name="form[Email]" id="Email" required="" title="<?php _e('Mandatory', 'bfi'); ?>" aria-required="true">
					<div><?php echo $infoSendBtn ?></div>
					<div>
						<button type="submit" class="bfi-btn"><?php _e('Send your request', 'bfi') ?></button>
					</div>

					<?php bfi_display_captcha($idrecaptcha);  ?>
					<input type="hidden" id="actionform" name="actionform" value="<?php echo $formlabel ?>" />
					<input type="hidden" name="form[merchantId]" value="<?php echo $merchant->MerchantId;?>" > 
					<input type="hidden" id="orderType" name="form[orderType]" value="<?php echo $orderType ?>" />
					<input type="hidden" id="cultureCode" name="form[cultureCode]" value="<?php echo $language;?>" />
					<span style="display:none;"><input type="text" id="Fax" name="form[Fax]" value="" /></span>
					<input type="hidden" id="label" name="form[label]" value="" />
					<input type="hidden" id="redirect" name="form[Redirect]" value="<?php echo $routeThanks;?>" />
					<input type="hidden" id="redirecterror" name="form[Redirecterror]" value="<?php echo $routeThanksKo;?>" />    
				</div>
			</form>
		</div>
	</div>
<?php 
} // end if 
?>
<script type="text/javascript">
<!--
	var dialogFormfaq;
var bfi_wuiP_width= 560;
jQuery(document).on('click tap', ".bfi-contactsfaq-openpopup", function (e) {
		if(jQuery(window).width()<bfi_wuiP_width){
			bfi_wuiP_width = jQuery(window).width();
		}
		dialogFormfaq = jQuery( ".bfi-contactsfaq" ).dialog({
				closeText: "",
				title:"<?php _e('Ask a question', 'bfi'); ?>",
				autoOpen: false,
				width:bfi_wuiP_width,
				modal: true,
				dialogClass: 'bfi-dialog',
				clickOutside: true,

		});
		dialogFormfaq.dialog( "open" );
});

//-->
</script>