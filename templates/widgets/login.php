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
$currModID = uniqid('bfilogin');

if(empty( $showpopup )){
	$showpopup= ( ! empty( $instance['showpopup'] ) ) ? esc_attr($instance['showpopup']) : '0';
}

$formRouteLogin = "/?task=loadlogin&uniqidbfilogin=".$currModID ."&showpopup=".$showpopup ;

?>

<?php 
if (!$showpopup && !empty( $before_widget )) {
		echo $before_widget;
	// Check if title is set
	if (!empty($title)) {
	  echo $before_title . $title . $after_title;
	}

    
}
?>
<div class="bflogin<?php echo $currModID ?>"></div>
<script type="text/javascript">
<!--
jQuery(document).ready(function() {
	jQuery( ".bflogin<?php echo $currModID ?>" ).load( bfi_variables.bfi_urlCheck + "<?php echo $formRouteLogin ?>", function() {
		bfi_initializer();
	});

});
//-->
</script>
<?php
if (!$showpopup && !empty( $after_widget )) {
	echo $after_widget; 
}
?>
