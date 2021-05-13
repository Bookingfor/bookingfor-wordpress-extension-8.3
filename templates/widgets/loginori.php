<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$language = $GLOBALS['bfi_lang'];
$languageForm ='';
$base_url = get_site_url();
if(defined('ICL_LANGUAGE_CODE') &&  class_exists('SitePress')){
		global $sitepress;
		if($sitepress->get_current_language() != $sitepress->get_default_language()){
			$base_url = "/" .ICL_LANGUAGE_CODE;
		}
}

$currUser = BFCHelper::getSession('bfiUser',null, 'bfi-User');
$accountLoginUrl =  str_replace("{language}", substr($language,0,2), COM_BOOKINGFORCONNECTOR_ACCOUNTLOGIN);
$accountRegistrationUrl =  str_replace("{language}", substr($language,0,2), COM_BOOKINGFORCONNECTOR_ACCOUNTREGISTRATION);
$accountForgotPasswordUrl =  str_replace("{language}", substr($language,0,2), COM_BOOKINGFORCONNECTOR_ACCOUNTFORGOTPASSWORD);

$formRouteLogin = $base_url . "/bfi-api/v1/task/?task=bfilogin"; 

$currModID = uniqid('bfilogin');
if(empty( $showpopup )){
	$showpopup= ( ! empty( $instance['showpopup'] ) ) ? esc_attr($instance['showpopup']) : '0';	
}


?>
<?php 
if (!$showpopup) {
	echo $before_widget;
	// Check if title is set
	if (!empty( $title) ) {
	  echo $before_title . $title . $after_title;
	}

    
}
?>
<div class="bfi-login-container bfi-mod-bookingforlogin<?php echo ($showpopup) ?"-popup":"";?>" id="bfi-mod-bookingforlogin<?php echo ($showpopup) ?"-popup":"";?><?php echo $currModID ?>">
<?php if($currUser==null) { ?>
	<div class="bfi-mod-bookingforlogin-title"><?php _e('Sign in', 'bfi'); ?></div>
	<div class="bfi-mod-bookingforlogin-content" id="bfi-mod-bookingforlogin-content<?php echo $currModID ?>">

		<form action="<?php echo $formRouteLogin ?>" id="bfi-login-form<?php echo $currModID ?>" class="bfi-login-form bfi-form bfi-form-vertical bfi-row">
			<div class="bfi-container">
				<div id="bfi-login-msg<?php echo $currModID ?>" class="bfi-login-msg">
					<span id="bfi-text-login-msg<?php echo $currModID ?>" class="bfi-text-login-msg"></span>
				</div>
		<!-- pchLogin -->
				<div id="pchLogin<?php echo $currModID ?>" class="bfi-pchlogin">
					<div class="bfi-form-txt">
						<label for="bfiloginEmail<?php echo $currModID ?>" ><?php _e('Email', 'bfi'); ?></label>
						<input id="bfiloginEmail<?php echo $currModID ?>" value="" name="email" type="email"  class="bfi-inputtext bfi-loginemail" placeholder='<?php _e('Email', 'bfi') ?>'
						autocomplete="email" onfocus="this.removeAttribute('readonly');" readonly 
						data-rule-required="true" data-rule-email="true" data-msg-required="<?php _e('This field is required.', 'bfi') ?>" data-msg-email="<?php _e('Please enter a valid email address', 'bfi') ?>" aria-required="true"
						/>
					</div>
					<div class="bfi-form-txt">
						<label for="bfiloginPassword<?php echo $currModID ?>"><?php _e('Password', 'bfi'); ?></label>
						<input id="bfiloginPassword<?php echo $currModID ?>" name="password" type="password" class="bfi-inputtext" placeholder='<?php _e('Password', 'bfi') ?>' 
						data-rule-required="true" data-msg-required="<?php _e('This field is required.', 'bfi') ?>" aria-required="true"
						/>
					</div>
					<div class="checkbox" style="display: none">
						<label>
							<input type="checkbox"> Remember me
						</label>
					</div>
				</div>
		<!-- pchTwoFactorAuthentication -->
				<div id="pchTwoFactorAuthentication<?php echo $currModID ?>" class="bfi-hide bfi-pchtwofactorauthentication">
					<div id="pchTwoFactorAuthenticationError<?php echo $currModID ?>" class="bfi-pchtwofactorauthenticationerror">
						<?php _e('An email was sent to {0}. Please check your email box and type the authentication code you have received.', 'bfi') ?>
					</div>
					<div class="bfi-form-txt">
						<label for="twoFactorAuthCode<?php echo $currModID ?>"><?php _e('Two-factor secure authentication', 'bfi') ?></label>
						<input id="twoFactorAuthCode<?php echo $currModID ?>" class="bfi-twofactorauthcode" name="twoFactorAuthCode" type="text" placeholder="<?php _e('Two-factor secure authentication', 'bfi') ?>" data-rule-required="true" data-msg-required="<?php _e('This field is required.', 'bfi') ?>" aria-required="true" autocomplete="off" onfocus="this.removeAttribute('readonly');" readonly = "true" />
					</div>
				</div>
		<!-- bfibtnSendLogin -->
				<div class="bfi-form-sep">
					<a href="javascript: void(0);" class="bfi-btn bfi-alternative bfi-btn-warning bfi-btn-lg bfi-btn-block bfi-btnsendlogin bfi-btnsendform" id="bfibtnSendLogin<?php echo $currModID ?>"><?php _e('Login', 'bfi') ?></a>
					<a href="javascript: void(0);" class="bfi-btn bfi-alternative bfi-btn-warning bfi-btn-lg bfi-btn-block bfi-btnsendconfirm bfi-btnsendform" style="display:none" id="bfibtnSendConfirm<?php echo $currModID ?>"><?php _e('Confirm', 'bfi') ?></a>
				</div>
				<div class="bfi-form-sep">
					<a href="javascript: bfi_lostpass(this);" id="bfibtnforgotpassword<?php echo $currModID ?>" target="" class="bfi-login-link bfi-btnforgotpassword"><?php _e('Lost password', 'bfi') ?></a>
					<a href="<?php echo $accountRegistrationUrl ?>" target="_blank" class="bfi-login-link"><?php _e('Register', 'bfi') ?></a>
				</div>
			</div>
			<input type="submit" style="display:none"/>
		</form>
		<form id="bfi-lostpass-form<?php echo $currModID ?>" action="<?php echo $accountForgotPasswordUrl ?>" class="bfi-lostpass-form bfi-form bfi-form-vertical bfi-row bfi-hide">
				<div id="pchLostpass<?php echo $currModID ?>" class="bfi-container">
					<div>
						<?php _e('Forgot Password', 'bfi') ?>
					</div>
					<div>&nbsp;</div>
					 <div>              
						<?php _e('If you are having difficulty logging in, you can reset your password by typing the Email Address that you used while registration. You will receive an email with a new password.', 'bfi') ?><br />&nbsp;
					</div>
					<div class="bfi-form-txt">
						<input id="bfiLostEmail<?php echo $currModID ?>" name="email" type="email"  class="bfi-inputtext" placeholder='<?php _e('Email', 'bfi') ?>' 
						data-rule-required="true" data-rule-email="true" data-msg-required="<?php _e('This field is required.', 'bfi') ?>" data-msg-email="<?php _e('Please enter a valid email address', 'bfi') ?>" aria-required="true"
					</div>
				</div>
				<div class="bfi-clearfix"></div>
				<div class="bfi-form-sep">
					<a href="javascript: void(0);" class="bfi-btn bfi-alternative bfi-btnsendform" id="bfibtnSendLostpass<?php echo $currModID ?>"><?php _e('Send', 'bfi') ?></a>
				</div>
				<div class="bfi-form-sep">
					<a href="javascript: bfi_lostpassback(this);" class="bfi-login-link"><?php _e('Back', 'bfi') ?></a>
				</div>
			</div>
			<input type="submit" style="display:none"/>
		</form>
	</div>

<?php }else{ ?>
<div class="bfi-form bfi-form-vertical bfi-row">
	<div class="bfi-mod-bookingforlogin-title"><?php _e('Your account', 'bfi') ?></div>
	<div class="bfi-mod-bookingforlogin-content" id="bfi-mod-bookingforlogin-content<?php echo $currModID ?>">
		<div class="bfi-welcomeuser"><?php _e('Welcome', 'bfi') ?> <?php echo $currUser->Name ?> <?php echo $currUser->Surname  ?></div>
		<div><a href="<?php echo $accountLoginUrl ?>" class="bfi-login-link" target="_blank"><?php _e('Your private area', 'bfi') ?></a></div>
		<div><a href="javascript: void(0);" class="bfi-login-link bfi-btnlogout" id="bfibtnLogout<?php echo $currModID ?>"><?php _e('Logout', 'bfi') ?></a></div>
	</div>
</div>
<?php } ?>
<!-- Other button -->
<?php
if (!$showpopup) {
	echo $after_widget; 
}
?>
</div>	<!-- module -->
