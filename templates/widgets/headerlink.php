<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if (COM_BOOKINGFORCONNECTOR_ISBOT) {
    return '';
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


$uniqueDevice = BFCHelper::GetUniqueDeviceCookie();
if (empty($uniqueDevice)) {
	$uniqueDevice = BFCHelper::SetUniqueDeviceCookie();
}

$currUser = BFCHelper::getSession('bfiUser',null, 'bfi-User');
if ($currUser == null && !empty($_REQUEST["usertoken"])) {

	$tmpUserId = BFCHelper::decrypt($_REQUEST["usertoken"], false, COM_BOOKINGFORCONNECTOR_SUBSCRIPTION_KEY, false);
	$userParams = explode("|", $tmpUserId);
	//echo print_r($tmpUserId);
	if (count($userParams) == 3) {
		$response = BFCHelper::getLoginTwoFactor($userParams[0], '', '', BFCHelper::GetUniqueDeviceCookie(), $userParams[2], $userParams[1]);
		if ($response == "-1" && $userParams[2] != $uniqueDevice) {
			BFCHelper::SetUniqueDeviceCookie($userParams[2]);
		}
	}
} else if ($currUser != null) {
	$currUserId = BFCHelper::bfi_get_userId();
	if (!BFCHelper::checkDeviceToken($currUserId, BFCHelper::GetUniqueDeviceCookie(), $currUser->UserToken)) {
		//echo "notvalid";
		BFCHelper::setSession('bfiUser', null, 'bfi-User');
		BFCHelper::UpdateCartExternalUser($currUserId);
	}
}


	$showcurrency= ( ! empty( $instance['showcurrency'] ) ) ? esc_attr($instance['showcurrency']) : '0';
	$showcart= ( ! empty( $instance['showcart'] ) ) ? esc_attr($instance['showcart']) : '0';
	$showlogin= ( ! empty( $instance['showlogin'] ) ) ? esc_attr($instance['showlogin']) : '0';
	$showfavorites= ( ! empty( $instance['showfavorites'] ) ) ? esc_attr($instance['showfavorites']) : '0';
//	$tmpUserId = rawurlencode(BFCHelper::encrypt(BFCHelper::bfi_get_userId(). "|". COM_BOOKINGFORCONNECTOR_FORM_KEY,COM_BOOKINGFORCONNECTOR_SUBSCRIPTION_KEY));

	$tmpUserId = BFCHelper::encrypt(BFCHelper::bfi_get_userId() . "|" . COM_BOOKINGFORCONNECTOR_FORM_KEY . "|" . BFCHelper::GetUniqueDeviceCookie(),COM_BOOKINGFORCONNECTOR_SUBSCRIPTION_KEY, false);
	$accountTravelplannerUrl =  str_replace("{language}", substr($language,0,2), COM_BOOKINGFORCONNECTOR_ACCOUNTTRAVELPLANNER);
	$accountTravelplannerUrl .= "?usertoken=".$tmpUserId."&cv=".COM_BOOKINGFORCONNECTOR_CRYPTOVERSION;
	$accountTravelplannerLoggedUrl =  str_replace("{language}", substr($language,0,2), COM_BOOKINGFORCONNECTOR_ACCOUNTTRAVELPLANNERLOGGED);

	$currUser = BFCHelper::getSession('bfiUser',null, 'bfi-User');
	$userLogged = false;
	if ($currUser!=null && !empty($currUser->Email)) {
		$userLogged = true;
	}

$customclass="";
if (!empty($instance['classes'])) {
	$customclass=$instance['classes'];
}
if (!empty($instance['g5_classes'])) {
	$customclass=$instance['g5_classes'];
}
?>
<div class="bfiwidgetcontainer <?php echo $customclass ?>">

<?php if(!empty($showcurrency)) { ?>
	<div class="bficurrencycontainer">

		<?php bfi_get_template("widgets/currency-switcher.php"); ?>

	</div>
<?php } ?>
<?php if(!empty($showlogin)) { ?>
	<div class="bfilogincontainer">

		<?php bfi_get_template("widgets/login.php", array('moduleclass_sfx'=>'','showpopup'=>1)); ?>
	</div>

<?php } ?>
<?php if(!empty($showfavorites)) { ?>
	<div class="bfitravelplannercontainer">
	
	<?php if($userLogged) { ?>
	
		<form method="post" action="<?php echo $accountTravelplannerLoggedUrl ?>" target="_blank" >
	
			<input type="hidden" name="UserId" value="<?php echo $currUser->Email ?>"/>
	
			<input type="hidden" name="deviceCode" value="<?php echo $currUser->DeviceToken ?>"/>
	
			<input type="hidden" name="deviceToken" value="<?php echo  $currUser->UserToken ?>"/>
	
			<input type="submit" style="display:none;"/>
	
			<span onclick="jQuery(this).closest('form').submit()" class="bfi-travelplanner" ><i class="fa fa-heart "></i> <span class="bfi-travelplanner-label"><?php _e('Travel planner', 'bfi') ?></span></span>
	
		</form>
	
	<?php } else { ?>
	
		<a href="<?php echo $accountTravelplannerUrl ?>" target="_blank" class="bfi-travelplanner"><i class="fa fa-heart "></i> <span class="bfi-travelplanner-label"><?php _e('Travel planner', 'bfi') ?></span></a>
		<?php } ?>
	</div>
<?php } ?>
<?php if(!empty($showcart)) { ?>
	<div class="bficartcontainer">

		<?php bfi_get_template("widgets/cart.php"); ?>

	</div>
<?php } ?>
</div>