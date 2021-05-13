<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $post;

$orderid = get_query_var( 'orderid', BFCHelper::getVar('payedOrderId',0) );

$actionmode = BFCHelper::getVar('actionmode',"");
$model = new BookingForConnectorModelPayment;
$model->populateState();

//$item = $model->getItem($orderid);
$lastPayment = $model->GetLastOrderPayment($orderid);

$language = $GLOBALS['bfi_lang'];

get_header( 'payment' );
do_action( 'bookingfor_before_main_content' );

//$cartdetails_page = get_post( bfi_get_page_id( 'cartdetails' ) );
//$url_cart_page = get_permalink( $cartdetails_page->ID );
$url_cart_page = BFCHelper::GetPageUrl('cartdetails');

//$redirect = $url_cart_page . _x('thanks', 'Page slug', 'bfi' ) . "/".$orderid . '?orderid='.$orderid;
//$redirecterror = $url_cart_page . _x('errors', 'Page slug', 'bfi' ) . "/".$orderid. '?orderid='.$orderid;
//$redirect = $url_cart_page . _x('thanks', 'Page slug', 'bfi' ). "?orderid=".$orderid;
//$redirecterror = $url_cart_page . _x('errors', 'Page slug', 'bfi' ). "?orderid=".$orderid;
$redirect = $url_cart_page . 'thanks' . "?orderid=".$orderid;
$redirecterror = $url_cart_page . 'errors' . "?orderid=".$orderid;

$errorPayment = false;
$invalidate=0;
$errorCode ="0";

if (empty($lastPayment) || $lastPayment->PaymentType!=3 || ($lastPayment->Status!=1 && $lastPayment->Status!=3 && $lastPayment->Status!=7 && $lastPayment->Status!=0 && $lastPayment->Status!=4 && $lastPayment->Status!=5 && $lastPayment->Status!=22 )) {
    $errorPayment= true;
	$errorCode ="1";

}
if(!empty($lastPayment->Status) && ($lastPayment->Status==1 ||$lastPayment->Status==3 || $lastPayment->Status==7 )){
	$invalidate=1;
}
if (!empty($lastPayment->Status) && $lastPayment->Status==5 ) {
    $errorPayment= true;
	$errorCode ="2";
}

if ($errorPayment) {
		$redirecterror .= '?errorCode='.$errorCode;
		header( 'Location: ' . $redirecterror  );
		exit();
}

$paymentUrl =  str_replace("{language}", substr($language,0,2), COM_BOOKINGFORCONNECTOR_PAYMENTURL).$orderid."/".$lastPayment->OrderPaymentId;
$typeMode="hidden";
		
?>
<h1 class="page-title"><?php _e('Payment', 'bfi') ?></h1>
<?php _e('If you are not redirected to the payment page within a few seconds, click the following button', 'bfi') ?>:<br />
<form action="<?php echo $paymentUrl?>" method="post" id="bfi_paymentform">
	<input id="urlok" name="urlok" type="<?php echo $typeMode ?>" title="urlok" value="<?php echo $redirect?>" />
	<input id="urlko" name="urlko" type="<?php echo $typeMode ?>" title="urlko"  value="<?php echo $redirecterror ?>" />
	<input id="invalidate" name="invalidate" type="<?php echo $typeMode ?>" title="urlok" value="<?php echo $invalidate?>" />
	<input type="submit" value="<?php _e('Send', 'bfi') ?>">
</form>
<script type="text/javascript">
<!--
		jQuery(function($) {
			jQuery("#bfi_paymentform").submit();
		});
//-->
</script>
<?php
do_action( 'bookingfor_after_main_content' );
get_footer( 'payment' ); 
?>