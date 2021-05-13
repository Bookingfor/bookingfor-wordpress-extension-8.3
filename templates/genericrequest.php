<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $post;

$language = $GLOBALS['bfi_lang'];

get_header( 'genericrequest' );
do_action( 'bookingfor_before_main_content' );

$url_cart_page = BFCHelper::getPageUrl('genericrequest');
$layout = get_query_var( 'bfi_layout', '' );

switch ( $layout) {
	case _x('thanks', 'Page slug', 'bfi' ):
	case 'thanks':
		bfi_get_template("thanks.php"); 

	break;
	case _x('errors', 'Page slug', 'bfi' ):
	case 'errors':
		bfi_get_template("errors.php"); 
		$sendAnalytics = false;
	break;
}
if(empty($layout)){
		$currentView = '';
		$orderType = "e";
		$task = "sendContact";
//		$routeThanks = $url_cart_page .'/'._x('thanks', 'Page slug', 'bfi' );
//		$routeThanksKo = $url_cart_page .'/'._x('errors', 'Page slug', 'bfi' );
		$routeThanks = $url_cart_page .'/thanks';
		$routeThanksKo = $url_cart_page .'/errors';

		$paramRef = array(
			"merchant"=>null,
			"layout"=>$layout,
			"currentView"=>$currentView,
			"resource"=>null,
			"task"=>$task,
			"orderType"=>$orderType,
			"routeThanks"=>$routeThanks,
			"routeThanksKo"=>$routeThanksKo,
			);
		bfi_get_template("/shared/infocontact.php",$paramRef);	
}

do_action( 'bookingfor_after_main_content' );
get_footer( 'genericrequest' ); 
?>