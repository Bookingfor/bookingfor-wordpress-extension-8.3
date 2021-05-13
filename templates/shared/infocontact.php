<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if (!COM_BOOKINGFORCONNECTOR_DISALBLEINFOFORM) {
    

if(COM_BOOKINGFORCONNECTOR_MONTHINCALENDAR==1){
?>
<style type="text/css">
.ui-datepicker-trigger.activeclass:after {
  top: 35px !important;
}
</style>
<?php
}
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
//	$privacy = BFCHelper::GetPrivacy($language);
//	$additionalPurpose = BFCHelper::GetAdditionalPurpose($language);
	$formlabel = COM_BOOKINGFORCONNECTOR_FORM_KEY;
	$minCapacityPaxes = 0;
	$maxCapacityPaxes = 12;
	
	$idrecaptcha = uniqid("bfirecaptcha");

	$routePrivacy = str_replace("{language}", substr($language,0,2), COM_BOOKINGFORCONNECTOR_PRIVACYURL);
	$routeTermsofuse = str_replace("{language}", substr($language,0,2), COM_BOOKINGFORCONNECTOR_TERMSOFUSEURL);
	$routeNewsletter = str_replace("{language}", substr($language,0,2), COM_BOOKINGFORCONNECTOR_NEWSLETTERURL);
	$routeMarketing = str_replace("{language}", substr($language,0,2), COM_BOOKINGFORCONNECTOR_MARKETINGURL);
	$routeDataprofiling = str_replace("{language}", substr($language,0,2), COM_BOOKINGFORCONNECTOR_DATAPROFILINGURL);

//	$infoSendBtn = sprintf(__('Choosing <b>Send</b> means that you agree to <a href="%3$s" target="_blank">Terms of use</a> of %1$s and <a href="%2$s" target="_blank">privacy and cookies statement.</a>.' ,'bfi'),$sitename,$routePrivacy,$routeTermsofuse);
	$infoSendBtn = sprintf(__('Choosing <b>Send</b> means that you agree to <a href="%3$s" target="_blank">Terms of use</a> of %1$s.' ,'bfi'),$sitename,$routePrivacy,$routeTermsofuse);
	$checkoutspan = '+1 day';
	$checkin = new DateTime('UTC');
	$checkout = new DateTime('UTC');
	$checkout->modify($checkoutspan); 
	$paxes = 2;
	$pars = BFCHelper::getSearchParamsSession();

	 $portalinfo =  BFCHelper::getSubscriptionInfos();
	 
//	 echo "<pre>portalinfo: ";
//	 echo print_r($portalinfo);
//	 echo "</pre>";
	 
	if (!empty($pars)){

		$checkin = !empty($pars['checkin']) ? $pars['checkin'] : new DateTime('UTC');
		$checkout = !empty($pars['checkout']) ? $pars['checkout'] : new DateTime('UTC');

		if (!empty($pars['paxes'])) {
			$paxes = $pars['paxes'];
		}
		if (!empty($pars['merchantCategoryId'])) {
			$merchantCategoryId = $pars['merchantCategoryId'];
		}
		if (!empty($pars['paxages'])) {
			$paxages = $pars['paxages'];
		}
		if (empty($pars['checkout'])){
			$checkout->modify($checkoutspan); 
		}
	}
	$checkinId = uniqid('checkin');
	$checkoutId = uniqid('checkout');

	?>
	<div class="bfi-row bfi-row-background">
		<div class="bfi-col-md-12">

	<div class="bfi-contacts-title"> <?php _e('Got a question?', 'bfi') ?></div>
	<?php if(isset($merchant)) { ?>
		<div class="bfi-contacts-subtitle"><?php _e('Send your own question to the property. The property usually replies within a few days', 'bfi') ?></div>
	<?php }else{ ?>
		<div class="bfi-contacts-subtitle"><?php _e('Send us your request, you will be contacted as soon as possible', 'bfi') ?></div>
	<?php } ?>
	
	<div class="bfi-contacts bfi-hideonextra">
	<div align="center" class="bfi-form-contacts" >
		<form method="post" class="form-validate bfi-form-infocontacts bfi-dateform-container " action="<?php echo $formRoute; ?>" novalidate="novalidate" data-blockdays="-1" data-blockmonths="-1">
			<div class="bfi-form-field">
				<div class="bfi-msgrequired"><?php _e('Fields marked with * (asterisk) are required', 'bfi') ?></div>
				<h4 class="bfi-titleform"><?php _e('Enter your details', 'bfi'); ?></h4>
<?php if(isset($resource)) {?>		
				<input type="hidden" id="resourceId" name="form[resourceId]" value="<?php echo $resource->ResourceId;?>" > 
<?php 
	$minCapacityPaxes = $resource->MinCapacityPaxes;
	$maxCapacityPaxes = $resource->MaxCapacityPaxes;
	if(empty($maxCapacityPaxes)) {
		$maxCapacityPaxes = 10;
	}
}
?>
			<div class="bfi-row ">
				<div class="bfi-col-md-4">
					<div class="bfi_form_txt">
						<input placeholder="<?php _e('Name', 'bfi'); ?> *" type="text" value="" size="50" name="form[Name]" id="Name" required="" title="<?php _e('Mandatory', 'bfi'); ?>" aria-required="true">
					</div>
				</div>
				<div class="bfi-col-md-4">
					<div class="bfi_form_txt">
						<input placeholder="<?php _e('Surname', 'bfi'); ?> *" type="text" value="" size="50" name="form[Surname]" id="Surname" required="" title="<?php _e('Mandatory', 'bfi'); ?>" aria-required="true">
					</div>				
				</div>
				<div class="bfi-col-md-4">
					<div class="bfi_form_txt">
						<input placeholder="<?php _e('Email', 'bfi'); ?> *" type="email" value="" size="50" name="form[Email]" id="Email" required="" title="<?php _e('Mandatory', 'bfi'); ?>" aria-required="true">
					</div>				
				</div>
			</div>
			<div class="bfi-row ">
				<div class="bfi-col-md-4">
					<div class="bfi_form_txt">
						<input placeholder="<?php _e('Phone', 'bfi'); ?>*" type="text" value="" size="20" name="form[Phone]" id="Phone" required="" title="<?php _e('Mandatory', 'bfi'); ?>" aria-required="true">
					</div>				
				</div>
				<div class="bfi-col-md-4">
					<div class="bfi_form_txt">
						<input placeholder="<?php _e('Address', 'bfi'); ?>" type="text" value="" size="50" name="form[Address]" id="Address"  title="<?php _e('Mandatory', 'bfi'); ?>" >
					</div>
				</div>
				<div class="bfi-col-md-4">
					<div class="bfi_form_txt">
						<input placeholder="<?php _e('Cap', 'bfi'); ?>" type="text" value="" size="20" name="form[Cap]" id="Cap"  title="<?php _e('Mandatory', 'bfi'); ?>" >
					</div>
				</div>
			</div>
			<div class="bfi-row ">
				<div class="bfi-col-md-4">
					<div class="bfi_form_txt">
						<input placeholder="<?php _e('City', 'bfi'); ?>" type="text" value="" size="50" name="form[City]" id="City"  title="<?php _e('Mandatory', 'bfi'); ?>" >
					</div>			
				</div>
				<div class="bfi-col-md-4">
					<div class="bfi_form_txt">
						<input placeholder="<?php _e('Province', 'bfi'); ?>" type="text" value="" size="20" name="form[Provincia]" id="Provincia" title="<?php _e('Mandatory', 'bfi'); ?>" >
					</div>
				</div>
				<div class="bfi-col-md-4">
					<div class="bfi_form_txt">
							<select id="formNation" name="form[Nation]" class="bfi_input_select width90percent">
								<?php 
								bfi_get_template("/shared/option_nations.php");	
								?>
							</select>
						</div>
				</div>
			</div>

		<?php if ( !isset($merchant) || (isset($merchant) && $merchant->HasResources && $layout !== 'onsellunits' && $layout !== 'onsellunit' && $currentView !== 'onsellunit')){?>
				<div class="bfi-row">   
					<div class="bfi-col-md-4 bfi-inline-field-right">
						<div class="bfi-inline-field"><label><?php _e('Check-in', 'bfi'); ?> </label></div>
						<div class="bfi_form_txt">
							<input type="text" name="checkin" value="<?php echo $checkin->format('d/m/Y') ?>" class="bfidate bfistart bfi-checkin-field" />	
						</div>
					</div>
					<div class="bfi-col-md-4 bfi-inline-field-left">
						<div class="bfi-inline-field"><label><?php _e('Check-out', 'bfi'); ?></label></div>
						<div class="bfi_form_txt">
							<input type="text" name="checkout" value="<?php echo $checkout->format('d/m/Y') ?>" class="bfidate bfiend bfi-checkout-field" />	
						</div>
					</div>
					<div class="bfi-col-md-4">
						<div class="bfi-inline-field-pers"><label><?php _e('Persons', 'bfi') ?> </label></div>
						<div class="bfi_form_txt">
							<select name="form[Totpersons]" class="bfi_input_select">
							<?php
							foreach (range($minCapacityPaxes, $maxCapacityPaxes) as $number) {
								?> <option value="<?php echo $number ?>" <?php selected( 2, $number ); ?>><?php echo $number ?></option><?php
							}
							?>
								</select>
						</div>
					</div>

				</div>
                
                
		<?php } ?>	
		<div class="bfi-row">
            <div class="bfi-col-md-12" style="padding:0;">
              <textarea name="form[note]" style="height:100px;width: 100%;"  placeholder="<?php _e('Special Requests', 'bfi'); ?>"  data-rule-nourl="true"  data-msg-nourl="<?php _e('No URLs allowed!', 'bfi') ?>"></textarea>    
            </div>
        </div>
		<?php bfi_display_captcha($idrecaptcha);  ?>

				<input type="hidden" id="actionform" name="actionform" value="<?php echo $formlabel ?>" />
				<input type="hidden" name="form[merchantId]" value="<?php echo (isset($merchant)) ? $merchant->MerchantId:""; ?>" > 
				<input type="hidden" id="orderType" name="form[orderType]" value="<?php echo $orderType ?>" />
				<input type="hidden" id="cultureCode" name="form[cultureCode]" value="<?php echo $language;?>" />
				<span style="display:none;"><input type="text" id="Fax" name="form[Fax]" value="" /></span>
				<input type="hidden" id="VatCode" name="form[VatCode]" value="" />
				<input type="hidden" id="label" name="form[label]" value="" />
				<input type="hidden" id="redirect" name="form[Redirect]" value="<?php echo $routeThanks;?>" />
				<input type="hidden" id="redirecterror" name="form[Redirecterror]" value="<?php echo $routeThanksKo;?>" />
				<input type="hidden" name="form[otherDetails]" value="<?php echo !empty($otherDetails) ?str_replace(array("'", "\"", "&quot;"), "", htmlspecialchars($otherDetails ) ):"";?>" />
		</div>

<?php if(isset($popupview)){  ?>
			<div class="bfi-row bfi-footer-book" >
				<div class="bfi-col-md-10">
					<?php echo $infoSendBtn ?>
				</div>
				<div class="bfi-col-md-2 bfi-footer-send"><button type="submit" class="bfi-btn"><?php _e('Send', 'bfi') ?></button></div>
			</div>
<?php }else{  ?>
		<div class="bfi-row bfi-margin-top10">
				<div class="bfi-col-md-12 bfi-checkbox-wrapper">
					<input name="form[optinprivacy]" id="optinprivacy" type="checkbox" required="" title="<?php _e('Mandatory', 'bfi'); ?>" aria-required="true" />
					<label for="optinprivacy"><?php echo sprintf(__('I consent to the management of the request', 'bfi'),$sitename) ?> - <a href="<?php echo $routePrivacy ?>" target="_blank"><?php _e('More information', 'bfi') ?></a></label>
				</div>
            <?php if(!empty($portalinfo) && BFCHelper::GetSettingValue($portalinfo->Settings,'system.gdpr.newsletter.enable')) { ?>            
				<div class="bfi-col-md-12 bfi-checkbox-wrapper">
					<input name="form[optinemail]" id="optinemail" type="checkbox">
					<label for="optinemail"><?php echo sprintf(__('I give my consent to send the newsletter', 'bfi'),$sitename) ?> - <a href="<?php echo $routeNewsletter ?>" target="_blank"><?php _e('More information', 'bfi') ?></a></label>
				</div>
            <?php } ?>
            <?php if(!empty($portalinfo) && BFCHelper::GetSettingValue($portalinfo->Settings,'system.gdpr.marketing.enable')) { ?>            
				<div class="bfi-col-md-12 bfi-checkbox-wrapper">
					<input name="form[optinmarketing]" id="optinmarketing" type="checkbox"/>
					<label for="optinmarketing"><?php echo sprintf(__('I give my consent for marketing purposes', 'bfi'),$sitename) ?> - <a href="<?php echo $routeMarketing ?>" target="_blank"><?php _e('More information', 'bfi') ?></a></label>
				</div>
            <?php } ?>
            <?php if(!empty($portalinfo) && BFCHelper::GetSettingValue($portalinfo->Settings,'system.gdpr.dataprofiling.enable')) { ?>            
				<div class="bfi-col-md-12 bfi-checkbox-wrapper">
					<input name="form[optinprofiling]" id="optinprofiling" type="checkbox"/>
					<label for="optinprofiling"><?php echo sprintf(__('I give my consent for profiling purposes', 'bfi'),$sitename) ?> - <a href="<?php echo $routeDataprofiling ?>" target="_blank"><?php _e('More information', 'bfi') ?></a></label>
				</div>
            <?php } ?>
            <div class="bfi-col-md-6">
				<div class="bfi-footer-book" >
					<?php echo $infoSendBtn ?>
				</div>
			</div>
            <div class="bfi-col-md-6 bfi-footer-btn bfi-pull-right">
				<button type="submit" class="bfi-btn bfi-alternative" style="width: 100%;" ><?php _e('Send', 'bfi') ?></button>
			</div>
		</div>
<?php } ?>
</form>
		</div>
	</div>
</div>
<?php 
} // end if 
?>
