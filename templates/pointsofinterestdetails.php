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
	if(!isset($_GET['task']) && ($layout !=_x('inforequestpopup', 'Page slug', 'bfi' )) && ($layout !=_x('mapspopup', 'Page slug', 'bfi' ))  ) {

		$model = new BookingForConnectorModelPointsofinterest;
		$resource = $model->getItem($resource_id);	 
		if (empty($resource)) {
			  global $wp_query;
			  $wp_query->set_404();
			  status_header( 404 );
			  get_template_part( 404 );
			  exit();		
		}
	
	/*---------------IMPOSTAZIONI SEO----------------------*/
		$resourceName = BFCHelper::getLanguage($resource->Name, $language, null, array('nobr'=>'nobr', 'striptags'=>'striptags')); 
		$resourceDescriptionSeo = BFCHelper::getLanguage($resource->Description, $language, null, array( 'nobr'=>'nobr', 'bbcode'=>'bbcode', 'striptags'=>'striptags')) ;
		if (!empty($resourceDescriptionSeo) && strlen($resourceDescriptionSeo) > 170) {
			$resourceDescriptionSeo = substr($resourceDescriptionSeo,0,170);
		}
		$details_page = get_post( bfi_get_page_id( 'pointsofinterestdetails' ) );
		$url_resource_page = get_permalink( $details_page->ID );
		$routeResource = $url_resource_page.$resource->PointOfInterestId.'-'.BFI()->seoUrl($resourceName);
		$canonicalUrl = $routeResource;


		$indirizzo = "";
		$cap = "";
		$comune = "";
		$provincia = "";

		if(!empty( $resource->Address )){
			$indirizzo = $resource->Address->Address;
			$cap = $resource->Address->ZipCode;
			$comune = $resource->Address->CityName;
			$provincia = $resource->Address->RegionName;
			$stato = !empty($resource->Address->StateName)?$resource->Address->StateName:"";
		}
		$titleHead = "$resourceName ($comune, $stato) - $resource->CategoryNames - $sitename";
		$keywordsHead = "$resourceName, $comune, $stato, $resource->CategoryNames";

		$details_page = get_post( bfi_get_page_id( 'pointsofinterestdetails' ) );
		$url_resource_page = get_permalink( $details_page->ID );
		$uri = $url_resource_page.$resource->PointOfInterestId.'-'.BFI()->seoUrl($resourceName);
		$resourceRoute = $uri;
		$resourceLat = "";
		$resourceLon = "";
		if(!empty($resource->Address->XPos)){
			$resourceLat = $resource->Address->XPos;
		}
		if(!empty($resource->Address->YPos)){
			$resourceLon = $resource->Address->YPos;
		}

	/* microformat */

	$payloadaddress["@type"] = "PostalAddress";
	$payloadaddress["streetAddress"] = $indirizzo;
	$payloadaddress["addressLocality"] = $comune;
	$payloadaddress["postalCode"] = $cap;
	$payloadaddress["addressRegion"] = $provincia;
	$payloadaddress["addressCountry"] =  BFCHelper::bfi_get_country_code_by_name($stato);

	// SEO
	$payloadresource["@context"] = "http://schema.org";
	$payloadresource["@type"] = "TouristAttraction";
	$payloadresource["location"] = $payloadaddress;
	$payloadresource["name"] = $resourceName;
	$payloadresource["description"] = $resourceDescriptionSeo;
	$payloadresource["url"] = $resourceRoute; 
	if (!empty($resource->ImageUrl)){
		$payloadresource["image"] = "https:".BFCHelper::getImageUrlResized('poi',$resource->ImageUrl, 'logobig');
	}
	if (!empty($resourceLat) && !empty($resourceLon)) {
		$payloadgeo["@type"] = "GeoCoordinates";
		$payloadgeo["latitude"] = $resourceLat;
		$payloadgeo["longitude"] = $resourceLon;
		$payloadresource["geo"] = $payloadgeo; 
	}

	/* end microformat */

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
										
					if (!empty($resource->DefaultImg)){
						add_action( 'wpseo_add_opengraph_images', 'add_images' );
						function add_images( $object ) {
						  $object->add_image( COM_BOOKINGFORCONNECTOR_DEFAULTIMAGE );
						}
						add_filter( 'wpseo_opengraph_image', function() use ($resource) {return	"https:".BFCHelper::getImageUrlResized('poi',$resource->DefaultImg, 'medium');} );
					}
					/* microformat */
					add_action( 'wpseo_head', function() use ($payloadresource) { bfi_add_json_ld( $payloadresource ); } , 30);
		}else{
			add_filter( 'wp_title', function() use ($titleHead) {return	$titleHead;} , 10, 1 );
			add_action( 'wp_head', function() use ($keywordsHead) {return bfi_add_meta_keywords($keywordsHead); }, 10, 1);
			add_action( 'wp_head', function() use ($resourceDescriptionSeo) {return bfi_add_meta_description($resourceDescriptionSeo); } , 10, 1 );
			add_action( 'wp_head', 'bfi_add_meta_robots', 10, 1);
			add_action( 'wp_head', function() use ($canonicalUrl) {return bfi_add_canonicalurl($canonicalUrl); }, 10, 1);
					/* microformat */
			add_action( 'wp_head', function() use ($payloadresource) { bfi_add_json_ld( $payloadresource ); } , 10, 1 );
			// OpenGraph for Social
			add_action( 'wp_head', function() use ($titleHead) {return bfi_add_opengraph_title($titleHead); }, 10, 1);
			add_action( 'wp_head', function() use ($resourceDescriptionSeo) {return bfi_add_opengraph_desc($resourceDescriptionSeo); } , 10, 1 );
			add_action( 'wp_head', function() use ($canonicalUrl) {return bfi_add_opengraph_url($canonicalUrl); }, 10, 1);
			if (!empty($resource->DefaultImg)){
				add_action( 'wp_head', function() use ($resource) {return bfi_add_opengraph_image("https:".BFCHelper::getImageUrlResized('poi',$resource->DefaultImg, 'medium')); }, 10, 1);
			}
		}
	/*--------------- END IMPOSTAZIONI SEO----------------------*/
			$paramRef = array(
				"resource"=>$resource,
				"resource_id"=>$resource_id,
				);

	get_header( 'pointsofinterestdetails' );
	do_action( 'bookingfor_before_main_content' );
?>
					<div class="bfi-row bfi-rowcontainer bfi-pointsofinterestdetails-page ">
						<div class="bfi-col-md-3 bfi-col-sm-3 bfi-col-xs-12  bfi-sidebar">
							<?php 
							bfi_get_template("widgets/smallmap.php");	
							bfi_get_template("widgets/proximitypoi.php",$paramRef );	
							?>
							<div class="bfilastmerchants bfi-hide">
								<h3><?php _e('Recently seen', 'bfi') ?></h3>
							</div>
						</div>
						<div class="bfi-col-md-9 bfi-col-xs-12 bfi-col-sm-9  bfi-body">
	
<?php
//	$layout = get_query_var( 'bfi_layout', '' );
//	$model->setResourceId($resource_id);
	
	$listName = "";
	$sendAnalytics = true;

	$paramRef = array(
		"resource"=>$resource,
		"resource_id"=>$resource_id,
		);

	switch ( $layout) {
//		case _x('mapspopup', 'Page slug', 'bfi' ):
//			bfi_get_template("pointsofinterestdetails/mapspopup.php",$paramRef);	
////			include(BFI()->plugin_path().'/templates/onselldetails/mapspopup.php'); // merchant template
//			die();
//		break;	
		default:
			$listName = "Pointsofinterests Page";
//			include(BFI()->plugin_path().'/templates/onselldetails/resourcedetails.php'); // merchant template
			$paramRef = array(
				"resource"=>$resource,
				"listName"=>$listName,
				"resource_id"=>$resource_id,
				);
			bfi_get_template("pointsofinterestdetails/resourcedetails.php",$paramRef);	
	}

		add_action('bfi_head', 'bfi_google_analytics_EEc', 10, 1);
		do_action('bfi_head', "");
//		if($sendAnalytics && COM_BOOKINGFORCONNECTOR_GAENABLED == 1 && !empty(COM_BOOKINGFORCONNECTOR_GAACCOUNT) && COM_BOOKINGFORCONNECTOR_EECENABLED == 1) {
//			$obj = new stdClass;
//			$obj->id = "" . $resource->PointOfInterestId . " - pointsofinterests";
//			$obj->name = $resource->Name;
////			$obj->category = $resource->MerchantCategoryName;
////			$obj->brand = $resource->MerchantName;
//			$obj->variant = 'NS';
////			$document->addScriptDeclaration('callAnalyticsEEc("addProduct", [' . json_encode($obj) . '], "item");');
//			echo '<script type="text/javascript"><!--
//			';
//				echo ('callAnalyticsEEc("addProduct", [' . json_encode($obj) . '], "item");');
//			echo "//--></script>";
//		}
//
?>
	<?php
		/**
		 * bookingfor_after_main_content hook.
		 *
		 * @hooked bookingfor_output_content_wrapper_end - 10 (outputs closing divs for the content)
		 */
		do_action( 'bookingfor_after_main_content' );
	?>	
	<?php
		/**
		 * bookingfor_sidebar hook.
		 *
		 * @hooked bookingfor_get_sidebar - 10
		 */
//		do_action( 'bookingfor_sidebar' );
	?>
<?php get_footer( 'onselldetails' ); ?>
						</div>
					</div>
<?php
  
  }
  else {
    $task = BFCHelper::getVar('task','');
	$model = new BookingForConnectorModelOnSellUnit;
	$model->setResourceId($resource_id);
//	$model->setItemPerPage(COM_BOOKINGFORCONNECTOR_ITEMPERPAGE);
	$resource = $model->getItem($resource_id);	 
	
//	$model = new BookingForConnectorModelMerchantDetails;
//	$merchant = $model->getItem($merchant_id);	 
//
//	if($task == 'getMerchantResources') {
//		if(!empty(BFCHelper::getVar('refreshcalc',''))){
//			bfi_setSessionFromSubmittedData();
//		}
//
//		$output = '';
//		bfi_get_template("onselldetails/search.php",array("total"=>$total,"items"=>$items,"pages"=>$pages,"page"=>$page,"currencyclass"=>$currencyclass,"listNameAnalytics"=>$listNameAnalytics));	
//		die($output);
//	}   
	//------------------------------
	if(empty($task)){
			switch ( $layout) {
				case _x('mapspopup', 'Page slug', 'bfi' ):
					$paramRef = array(
						"resource"=>$resource
						);
					bfi_get_template("pointsofinterestdetails/mapspopup.php",$paramRef);	
//					include(BFI()->plugin_path().'/templates/onselldetails/mapspopup.php'); // merchant template
					die();
				break;
			}

	}

}
?>
