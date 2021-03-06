<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
 ?>
<?php
	$resource_id = get_query_var( 'resource_id', 0 );
	$language = $GLOBALS['bfi_lang'];
	$layout = get_query_var( 'bfi_layout', '' );
	$sitename = sanitize_text_field( get_bloginfo( 'name' ) );
	$model = new BookingForConnectorModelExperience;
	$model->setResourceId($resource_id);
	$model->setItemPerPage(COM_BOOKINGFORCONNECTOR_ITEMPERPAGE);
	$resource = $model->getItem($resource_id);	 
	if(empty($resource)){
	  global $wp_query;
	  $wp_query->set_404();
	  status_header( 404 );
	  get_template_part( 404 );
	  exit();		
	}

	$currencyclass = bfi_get_currentCurrency();
	$merchant = $resource->Merchant;
	$merchants = array();
	$merchants[] = $resource->MerchantId;

	$resourceName = BFCHelper::getLanguage($resource->Name, $language, null, array('nobr'=>'nobr', 'striptags'=>'striptags')); 
	$merchantName = BFCHelper::getLanguage($merchant->Name, $language, null, array('nobr'=>'nobr', 'striptags'=>'striptags')); 
	$resourceDescription = BFCHelper::getLanguage($resource->Description, $language, null, array('ln2br'=>'ln2br', 'bbcode'=>'bbcode', 'striptags'=>'striptags'));

	$indirizzo = isset($resource->Address)?$resource->Address:"";
	$cap = isset($resource->ZipCode)?$resource->ZipCode:""; 
	$comune = isset($resource->CityName)?$resource->CityName:"";
	$stato = isset($resource->StateName)?$resource->StateName:"";

	$experiencedetails_page = get_post( bfi_get_page_id( 'experiencedetails' ) );	
	$url_resource_page = get_permalink( $experiencedetails_page->ID );
	$routeResource = $url_resource_page.$resource->ResourceId.'-'.BFI()->seoUrl($resourceName);
	$canonicalUrl = $routeResource;

/*---------------IMPOSTAZIONI SEO----------------------*/
	$merchantDescriptionSeo = BFCHelper::getLanguage($merchant->Description, $language, null, array( 'nobr'=>'nobr', 'bbcode'=>'bbcode', 'striptags'=>'striptags')) ;
	$resourceDescriptionSeo = BFCHelper::getLanguage($resource->Description, $language, null, array( 'nobr'=>'nobr', 'bbcode'=>'bbcode', 'striptags'=>'striptags')) ;
	if (!empty($merchantDescriptionSeo) && strlen($merchantDescriptionSeo) > 170) {
	    $merchantDescriptionSeo = substr($merchantDescriptionSeo,0,170);
	}
	if (!empty($resourceDescriptionSeo) && strlen($resourceDescriptionSeo) > 170) {
	    $resourceDescriptionSeo = substr($resourceDescriptionSeo,0,170);
	}

	$titleHead = "$merchantName: $resourceName ($comune, $stato) - $merchant->MainCategoryName - $sitename";
	$keywordsHead = "$merchantName, $resourceName, $comune, $stato, $merchant->MainCategoryName";

//	$this->document->setTitle($titleHead);
//	$this->document->setDescription($resourceDescriptionSeo);
//	$this->document->setMetadata('keywords', $keywordsHead);
//	$this->document->setMetadata('robots', "index,follow");
//	$this->document->setMetadata('og:title', $titleHead);
//	$this->document->setMetadata('og:description', $resourceDescriptionSeo);
//	$this->document->setMetadata('og:url', $resourceRouteSeo);

//	$payload["@type"] = "Organization";
//	$payload["@context"] = "http://schema.org";
//	$payload["name"] = $merchantName;
//	$payload["description"] = $merchantDescriptionSeo;
//	$payload["url"] = $routeSeo; 
//	if (!empty($merchant->LogoUrl)){
//		$payload["logo"] = "https:".BFCHelper::getImageUrlResized('merchant',$merchant->LogoUrl, 'logobig');
//	}
//
//	$payloadresource["@type"] = "Product";
//	$payloadresource["@context"] = "http://schema.org";
//	$payloadresource["name"] = $resourceName;
//	$payloadresource["description"] = $resourceDescriptionSeo;
//	$payloadresource["url"] = $resourceRouteSeo; 
//	if (!empty($resource->ImageUrl)){
//		$payloadresource["image"] = "https:".BFCHelper::getImageUrlResized('resources',$resource->ImageUrl, 'logobig');
//	}
/*--------------- FINE IMPOSTAZIONI SEO----------------------*/
	
	if(!isset($_GET['task']) && ($layout !=_x('inforequestpopup', 'Page slug', 'bfi' )) && ($layout !=_x('mapspopup', 'Page slug', 'bfi' ))  ) {

	if ( defined('WPSEO_VERSION') ) {
				add_filter( 'wpseo_title', function() use ($titleHead) {return	$titleHead;} , 10, 1 );
				add_filter( 'wpseo_metakey', function() use ($keywordsHead) {return $keywordsHead; } , 10, 1  );
				add_filter( 'wpseo_metadesc', function() use ($resourceDescriptionSeo) {return $resourceDescriptionSeo; } , 10, 1 );
				add_filter( 'wpseo_robots', function() {return "index,follow"; } , 10, 1 );
				add_filter( 'wpseo_canonical', function() use ($canonicalUrl) {
					if(substr($canonicalUrl , -1)!= '/'){
						$canonicalUrl .= '/';
					}
					return $canonicalUrl; 
				} , 10, 1 );
				// OpenGraph for Social
				add_filter( 'wpseo_opengraph_url', function() use ($canonicalUrl) {
					if(substr($canonicalUrl , -1)!= '/'){
						$canonicalUrl .= '/';
					}
					return $canonicalUrl; 
				} , 10, 1 );		
				add_filter( 'wpseo_opengraph_title', function() use ($titleHead) {return	$titleHead;});
				add_filter( 'wpseo_opengraph_desc', function() use ($resourceDescriptionSeo) {return	$resourceDescriptionSeo;});
				if (!empty($resource->ImageUrl)){
					add_action( 'wpseo_add_opengraph_images', 'add_images' );
					function add_images( $object ) {
					  $object->add_image( COM_BOOKINGFORCONNECTOR_DEFAULTIMAGE );
					}
					add_filter( 'wpseo_opengraph_image', function() use ($resource) {return	"https:".BFCHelper::getImageUrlResized('resources',$resource->ImageUrl, 'logobig');});
				}
	}else{
		add_filter( 'wp_title', function() use ($titleHead) {return	$titleHead;} , 10, 1 );
		add_action( 'wp_head', function() use ($keywordsHead) {return bfi_add_meta_keywords($keywordsHead); }, 10, 1);
		add_action( 'wp_head', function() use ($resourceDescriptionSeo) {return bfi_add_meta_description($resourceDescriptionSeo); } , 10, 1 );
		add_action( 'wp_head', function() use ($canonicalUrl) {return bfi_add_canonicalurl($canonicalUrl); }, 10, 1);
		add_action( 'wp_head', 'bfi_add_meta_robots', 10, 1);
		// OpenGraph for Social
		add_action( 'wp_head', function() use ($titleHead) {return bfi_add_opengraph_title($titleHead); }, 10, 1);
		add_action( 'wp_head', function() use ($resourceDescriptionSeo) {return bfi_add_opengraph_desc($resourceDescriptionSeo); } , 10, 1 );
		add_action( 'wp_head', function() use ($canonicalUrl) {return bfi_add_opengraph_url($canonicalUrl); }, 10, 1);
		if (!empty($resource->ImageUrl)){
			add_action( 'wp_head', function() use ($resource) {return bfi_add_opengraph_image("https:".BFCHelper::getImageUrlResized('resources',$resource->ImageUrl, 'logobig')); }, 10, 1);
		}
	}


	get_header( 'experiencedetails' );
	do_action( 'bookingfor_before_main_content' );
?>
					<div class="bfi-row bfi-rowcontainer bfi-experiencedetails-page ">
					<?php 
						if (false && !COM_BOOKINGFORCONNECTOR_ISMOBILE) {
					?>
						<div class="bfi-col-md-3 bfi-col-sm-3 bfi-col-xs-12  bfi-sidebar">

							<?php 
							bfi_get_template("widgets/smallmap.php");	
							bfi_get_template("widgets/reviews.php");	
							?>
							<div class="bfilastmerchants bfi-hide">
								<h3><?php _e('Recently seen', 'bfi') ?></h3>
							</div>
						</div>
					<?php 
						}
					?>
						<div class="bfi-col-md-12 bfi-col-xs-12 bfi-col-sm-12  bfi-body">
<?php

		if(isset($_REQUEST['newsearch'])){
			bfi_setSessionFromSubmittedData();
		}
		if(isset($_REQUEST['state'])){
			$_SESSION['search.params']['state'] = $_REQUEST['state'];

		}
	?>
	
<?php
//	$layout = get_query_var( 'bfi_layout', '' );

	$sendAnalytics =false;
	$criteoConfig = null;

	$paramRef = array(
		"merchant"=>$merchant,
		"resource"=>$resource,
		"indirizzo"=>$indirizzo,
		"cap"=>$cap,
		"comune"=>$comune,
		"stato"=>$stato,
		"resource_id"=>$resource_id,
		"currencyclass"=>$currencyclass,
		);

	switch ( $layout) {
		case _x('review', 'Page slug', 'bfi' ):
			bfi_get_template("experiencedetails/review.php",$paramRef);	
		break;
		default:
			if(COM_BOOKINGFORCONNECTOR_CRITEOENABLED){
				$criteoConfig = BFCHelper::getCriteoConfiguration(2, $merchants);
				if(isset($criteoConfig) && isset($criteoConfig->enabled) && $criteoConfig->enabled && count($criteoConfig->merchants) > 0) {
					echo '<script type="text/javascript" src="//static.criteo.net/js/ld/ld.js" async="true"></script>';
					echo '<script type="text/javascript"><!--
					';
					echo ('
					window.criteo_q = window.criteo_q || [];					
					var deviceTypeCriteo = /iPad/.test(navigator.userAgent) ? "t" : /Mobile|iP(hone|od)|Android|BlackBerry|IEMobile|Silk/.test(navigator.userAgent) ? "m" : "d";
					window.criteo_q.push( 
						{ event: "setAccount", account: '. $criteoConfig->campaignid .'}, 
						{ event: "setSiteType", type: deviceTypeCriteo }, 
						{ event: "setEmail", email: "" }, 
						{ event: "viewItem", item: "'. $criteoConfig->merchants[0] .'" }
					);');
					echo "//--></script>";
				}
			}
						
			bfi_get_template("experiencedetails/resourcedetails.php",$paramRef);	
			$sendAnalytics = true; //$resource->IsCatalog;
	}

		add_action('bfi_head', 'bfi_google_analytics_EEc', 10, 1);
		do_action('bfi_head', "");
		if($sendAnalytics && COM_BOOKINGFORCONNECTOR_GAENABLED == 1 && !empty(COM_BOOKINGFORCONNECTOR_GAACCOUNT) && COM_BOOKINGFORCONNECTOR_EECENABLED == 1) {
				$obj = new stdClass;
				$obj->id = "" . $resource->ResourceId . " - Resource";
				$obj->name = $resource->Name;
				$obj->category = $resource->MerchantCategoryName;
				$obj->brand = $resource->MerchantName;
				$obj->variant = $resource->IsCatalog ? 'CATALOG': 'NS';
				echo '<script type="text/javascript"><!--
				';
				echo ('callAnalyticsEEc("addProduct", [' . json_encode($obj) . '], "item");');
				echo "//--></script>";
		}
	
	wp_enqueue_script('bf_appTimePeriod', BFI()->plugin_url() . '/assets/js/bf_appTimePeriod.js',array(),BFI_VERSION);
	wp_enqueue_script('bf_appTimeSlot', BFI()->plugin_url() . '/assets/js/bf_appTimeSlot.js',array(),BFI_VERSION);


?>
	<?php
		/**
		 * bookingfor_sidebar hook.
		 *
		 * @hooked bookingfor_get_sidebar - 10
		 */
//		do_action( 'bookingfor_sidebar' );
	?>
	<div class="bfi-hide"><?php bfi_get_template("widgets/smallmap.php");	?></div>
						</div>
					</div>
					</div>
	<?php
		/**
		 * bookingfor_after_main_content hook.
		 *
		 * @hooked bookingfor_output_content_wrapper_end - 10 (outputs closing divs for the content)
		 */
		do_action( 'bookingfor_after_main_content' );
	?>	
<?php get_footer( 'experiencedetails' ); ?>
<?php
  
  }
  else {
    $task = BFCHelper::getVar('task','');
	
//	$model = new BookingForConnectorModelMerchantDetails;
//	$merchant = $model->getItem($merchant_id);	 
//
	if($task == 'getMerchantResources') {
		if(!empty(BFCHelper::getVar('refreshcalc',''))){
			bfi_setSessionFromSubmittedData();
		}
		$criteoConfig = null;
		if(COM_BOOKINGFORCONNECTOR_CRITEOENABLED){
			$criteoConfig = BFCHelper::getCriteoConfiguration(2, $merchants);
		}
	
		
		$_SESSION['search.params']['resourceId'] = $resource_id;
		$output = '';
		$resourceId = $resource->ResourceId;
		$resourcegroupId = 0;

		bfi_get_template("shared/search_details.php",array("resource"=>$resource,"merchant"=>$merchant,"resourceId"=>$resourceId,"resourcegroupId"=>$resourcegroupId,"currencyclass"=>$currencyclass));	
//		include(BFI()->plugin_path().'/templates/search_details.php'); //merchant temp 
		die($output);
	}   
	//------------------------------
	if(empty($task)){
			switch ( $layout) {
				case _x('inforequestpopup', 'Page slug', 'bfi' ):
					
					$merchant_id = $resource->MerchantId;
					$currentView = 'resource';
					$orderType = "c";
					$task = "sendInforequest";
					$popupview = true;

					$merchantdetails_page = get_post( bfi_get_page_id( 'merchantdetails' ) );
					$url_merchant_page = get_permalink( $merchantdetails_page->ID );
					$routeMerchant = $url_merchant_page . $merchant->MerchantId.'-'.BFI()->seoUrl($merchant->Name);
					$uriMerchant = $routeMerchant;

					$routeThanks = $uriMerchant .'/'._x('thankspopup', 'Page slug', 'bfi' );
					$routeThanksKo = $uriMerchant .'/'._x('errorspopup', 'Page slug', 'bfi' );
					$checkoutspan = '+1 day';
					$checkin = new DateTime('UTC');
					$checkout = new DateTime('UTC');
					$paxes = 2;
					$pars = BFCHelper::getSearchParamsSession();
					if (!empty($pars)){

						$checkin = isset($pars['checkin']) ? $pars['checkin'] : new DateTime('UTC');
						$checkout = isset($pars['checkout']) ? $pars['checkout'] : new DateTime('UTC');

						if (!empty($pars['paxes'])) {
							$paxes = $pars['paxes'];
						}
						if (!empty($pars['merchantCategoryId'])) {
							$merchantCategoryId = $pars['merchantCategoryId'];
						}
						if (!empty($pars['paxages'])) {
							$paxages = $pars['paxages'];
						}
						if ($pars['checkout'] == null){
							$checkout->modify($checkoutspan); 
						}
					}
					$checkinId = uniqid('checkin');
					$checkoutId = uniqid('checkout');
					$output = '';
					$paramRef = array(
						"merchant"=>$merchant,
						"layout"=>$layout,
						"currentView"=>$currentView,
						"resource"=>$resource,
						"popupview"=>$popupview,
						"task"=>$task,
						"checkoutId"=>$checkoutId,
						"checkinId"=>$checkinId,
						"orderType"=>$orderType,
						"routeThanks"=>$routeThanks,
						"routeThanksKo"=>$routeThanksKo,
						"paxes"=>$paxes,
						"checkin"=>$checkin,
						"checkout"=>$checkout
						);

					bfi_get_template("merchant-sidebar-contact.php",$paramRef);	
					die($output);
					break;
				case _x('mapspopup', 'Page slug', 'bfi' ):
					$paramRef = array(
						"resource"=>$resource
						);
					bfi_get_template("experiencedetails/mapspopup.php",$paramRef);	
				die();
				break;
			}

	}

}
?>
