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
		$model = new BookingForConnectorModelEvent;
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
		$resourceDescriptionSeo = BFCHelper::getLanguage($resource->ShortDescription, $language, null, array( 'nobr'=>'nobr', 'bbcode'=>'bbcode', 'striptags'=>'striptags')) ;
		if(empty( $resourceDescriptionSeo )){
			$resourceDescriptionSeo = BFCHelper::getLanguage($resource->Description, $language, null, array( 'nobr'=>'nobr', 'bbcode'=>'bbcode', 'striptags'=>'striptags')) ;
		}
		if (!empty($resourceDescriptionSeo) && strlen($resourceDescriptionSeo) > 170) {
			$resourceDescriptionSeo = substr($resourceDescriptionSeo,0,170);
		}

		$details_page = get_post( bfi_get_page_id( 'eventdetails' ) );
		$url_resource_page = get_permalink( $details_page->ID );
		$routeResource = $url_resource_page.$resource->EventId.'-'.BFI()->seoUrl($resourceName);
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

	$startDate = BFCHelper::parseJsonDate($resource->StartDate,'Y-m-d\TH:i:s');
	$endDate = BFCHelper::parseJsonDate($resource->EndDate,'Y-m-d\TH:i:s');
	$startDate  = new DateTime($startDate,new DateTimeZone('UTC'));
	$endDate  = new DateTime($endDate,new DateTimeZone('UTC'));
	$details_page = get_post( bfi_get_page_id( 'eventdetails' ) );
	$url_resource_page = get_permalink( $details_page->ID );
	$uri = $url_resource_page.$resource->EventId.'-'.BFI()->seoUrl($resourceName);
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

	$payloadlocation["@type"] = "Place";
	$payloadlocation["address"] = $payloadaddress;
	$payloadlocation["name"] = $comune . " - " . $provincia . " - " . $stato;
	
	// SEO
	$payloadresource["@context"] = "http://schema.org";
	$payloadresource["@type"] = "Event";
	$payloadresource["location"] = $payloadlocation;
	$payloadresource["name"] = $resourceName;
	$payloadresource["description"] = $resourceDescriptionSeo;
	$payloadresource["startDate"] = $startDate->format("Y-m-d");
	$payloadresource["endDate"] = $endDate->format("Y-m-d");
	$payloadresource["url"] = $resourceRoute; 
	if (!empty($resource->ImageUrl)){
		$payloadresource["image"] = "https:".BFCHelper::getImageUrlResized('events',$resource->ImageUrl, 'logobig');
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
					/* microformat */
					add_action( 'wpseo_head', function() use ($payloadresource) { bfi_add_json_ld( $payloadresource ); } , 30);
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
						add_filter( 'wpseo_opengraph_image', function() use ($resource) {return	"https:".BFCHelper::getImageUrlResized('events',$resource->DefaultImg, 'big');} );
						add_action( 'wp_head', function() use ($resource) {
							$image['secure_url'] = "https:".BFCHelper::getImageUrlResized('events',$resource->DefaultImg, 'big');
							$image['mime-type'] = 'image/jpeg';
							$image['width'] = 820;
							$image['height'] = 460;
							return bfi_add_opengraph_image_size($image);
						}, 1);

					}

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
				add_action( 'wp_head', function() use ($resource) {return bfi_add_opengraph_image("https:".BFCHelper::getImageUrlResized('events',$resource->DefaultImg, 'big')); }, 10, 1);
			}
		}
	/*--------------- END IMPOSTAZIONI SEO----------------------*/
			$paramRef = array(
				"resource"=>$resource,
				"resource_id"=>$resource_id,
				);

	get_header( 'eventdetails' );
	do_action( 'bookingfor_before_main_content' );

?>
					<div class="bfi-row bfi-rowcontainer bfi-eventdetails-page ">
					<?php 
						if (!COM_BOOKINGFORCONNECTOR_ISMOBILE) {
					?>
						<div class="bfi-col-md-3 bfi-col-sm-3 bfi-col-xs-12  bfi-sidebar">
							<?php 
							bfi_get_template("widgets/booking-searchevents.php");	
							bfi_get_template("widgets/smallmap.php");	
							bfi_get_template("widgets/proximitypoi.php",$paramRef );	
							?>
							<div class="bfilastmerchants bfi-hide">
								<h3><?php _e('Recently seen', 'bfi') ?></h3>
							</div>
						</div>
					<?php 
						}
					?>						
						<div class="bfi-col-md-9 bfi-col-xs-12 bfi-col-sm-9  bfi-body">
<?php
//	$layout = get_query_var( 'bfi_layout', '' );
//	$model->setResourceId($resource_id);
	
	$listName = "";
	$sendAnalytics = true;


	switch ( $layout) {
//		case _x('mapspopup', 'Page slug', 'bfi' ):
//			bfi_get_template("onselldetails/mapspopup.php",$paramRef);	
////			include(BFI()->plugin_path().'/templates/onselldetails/mapspopup.php'); // merchant template
//			die();
//		break;	
		default:
			$listName = "Events Page";
			$paramRef = array(
				"resource"=>$resource,
				"listName"=>$listName,
				"resource_id"=>$resource_id,
				);
//			include(BFI()->plugin_path().'/templates/onselldetails/resourcedetails.php'); // merchant template
			bfi_get_template("eventdetails/resourcedetails.php",$paramRef);	
	}

		add_action('bfi_head', 'bfi_google_analytics_EEc', 10, 1);
		do_action('bfi_head', "");
//		if($sendAnalytics && COM_BOOKINGFORCONNECTOR_GAENABLED == 1 && !empty(COM_BOOKINGFORCONNECTOR_GAACCOUNT) && COM_BOOKINGFORCONNECTOR_EECENABLED == 1) {
//			$obj = new stdClass;
//			$obj->id = "" . $resource->EventId . " - Events";
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
						</div>
					<?php 
						if (COM_BOOKINGFORCONNECTOR_ISMOBILE) {
					?>
						<div class="bfi-col-md-3 bfi-col-sm-3 bfi-col-xs-12  bfi-sidebar">
							<?php 
							bfi_get_template("widgets/booking-searchevents.php");	
							bfi_get_template("widgets/smallmap.php");	
							bfi_get_template("widgets/proximitypoi.php",$paramRef );	
							?>
							<br />
						</div>
					<?php 
						}
					?>						
					</div>
<?php get_footer( 'onselldetails' ); ?>

<?php
  
  }
  else {
    $task = BFCHelper::getVar('task','');
	$model = new BookingForConnectorModelEvent;
	$resource = $model->getItem($resource_id);	 
    $layout = BFCHelper::getVar('layout','0');
	
	$startDate = BFCHelper::parseJsonDate($resource->StartDate,'Y-m-d\TH:i:s');
	$endDate = BFCHelper::parseJsonDate($resource->EndDate,'Y-m-d\TH:i:s');
	$startDate  = new DateTime($startDate,new DateTimeZone('UTC'));
	$endDate  = new DateTime($endDate,new DateTimeZone('UTC'));
	$details_page = get_post( bfi_get_page_id( 'eventdetails' ) );
	$url_resource_page = get_permalink( $details_page->ID );
	$uri = $url_resource_page.$resource->EventId.'-'.BFI()->seoUrl($resourceName);
	$resourceRoute = $uri;
	$resourceLat = "";
	$resourceLon = "";
	if(!empty($resource->Address->XPos)){
		$resourceLat = $resource->Address->XPos;
	}
	if(!empty($resource->Address->YPos)){
		$resourceLon = $resource->Address->YPos;
	}
	$searchContest = $layout;

//	$model = new BookingForConnectorModelMerchantDetails;
//	$merchant = $model->getItem($merchant_id);	 
//
	if($task == 'getMerchantResources') {
				$listName = "";
				$sendAnalytics = true;
	wp_enqueue_script('bf_appTimePeriod', BFI()->plugin_url() . '/assets/js/bf_appTimePeriod.js',array(),BFI_VERSION);
	wp_enqueue_script('bf_appTimeSlot', BFI()->plugin_url() . '/assets/js/bf_appTimeSlot.js',array(),BFI_VERSION);

						$listName = "Events Page";
						$paramRef = array(
							"resource"=>$resource,
							"listName"=>$listName,
							"resource_id"=>$resource_id,
							);
		$searchContest = "0";

		$currSearchParam = $resource->AccommodationSearch;
			switch ( $layout) {
				case '1':
					$currSearchParam = $resource->ServiceSearch;
					$searchContest = "1";
				break;
				default:
			}
//				switch ( $layout) {
//					default:
		if(!empty( $currSearchParam)){

			if ($currSearchParam->SearchType==4) { //solo se è una ricerca allora mostro i dati per la ricerca
			    
				$listName = "Events Page";
				$paramRef = array(
					"resource"=>$resource,
					"listName"=>$listName,
					"resource_id"=>$resource_id,
					);
				$currFrom = BFCHelper::parseJsonDate($currSearchParam->StartDate,'d/m/Y');
				$currTo = BFCHelper::parseJsonDate($currSearchParam->EndDate,'d/m/Y');	
				$currFrom = BFCHelper::parseStringDateTime($currFrom,'d/m/Y');
				$currTo = BFCHelper::parseStringDateTime($currTo,'d/m/Y');

				$now = new DateTime('UTC');
				$now->setTime(0,0,0);
				if ($currFrom < $now) {
				   $currFrom = $now;
				}
				$checkin =$currFrom;
				$checkout =$currTo;
				if(!$currSearchParam->FixedDates && !empty( $_REQUEST['checkin'] ) && !empty( $_REQUEST['checkout'] )){
//					$currFrom  = DateTime::createFromFormat('d/m/YH:i:s', $_REQUEST['checkin'], new DateTime($currFrom,new DateTimeZone('UTC')));
//					$currTo  = DateTime::createFromFormat('d/m/YH:i:s', $_REQUEST['checkout'], new DateTime($currTo,new DateTimeZone('UTC')));
					$currFrom = DateTime::createFromFormat('YmdHis', $_REQUEST['checkin'], new DateTimeZone('UTC'));
					if(empty($currFrom)) $currFrom = DateTime::createFromFormat('d/m/YH:i:s', $_REQUEST['checkin'] . (isset($_REQUEST['checkintime']) ? $_REQUEST['checkintime'] : "00:00") . ':00', new DateTimeZone('UTC'));
					$currTo = DateTime::createFromFormat('YmdHis', $_REQUEST['checkout'], new DateTimeZone('UTC'));
					if(empty($currTo)) $currTo = DateTime::createFromFormat('d/m/YH:i:s', $_REQUEST['checkout'] . (isset($_REQUEST['checkouttime']) ? $_REQUEST['checkouttime'] : "00:00") . ':00', new DateTimeZone('UTC'));

					
			$checkin = BFCHelper::parseStringDateTime($currFrom,'d/m/Y');
			$checkout = BFCHelper::parseStringDateTime($currTo,'d/m/Y');
				}


				if ($currSearchParam->IsLimitedStay ) {
					$tmpDuration = $checkout->diff($checkin)->format('%a');
					$minstay = !empty($currSearchParam->MinStay) ?$currSearchParam->MinStay:0;
					$maxstay = !empty($currSearchParam->MaxStay) ?$currSearchParam->MaxStay:30;
					if ($tmpDuration<$minstay || $tmpDuration>$maxstay) {
				$checkout = clone $checkin;
						$checkout = $checkout->modify('+' . $currSearchParam->MinStay. ' day'); 
					}
			}

	$checkin->setTime(0,0,0);
	$checkout->setTime(0,0,0);
				$currParamInSession = BFCHelper::getSearchParamsSession();
				$paxages = array(BFCHelper::$defaultAdultsAge, BFCHelper::$defaultAdultsAge);
				if (isset($currSearchParam->MinPaxes)) {
					$paxages = array();
					for ($i = 0;$i<$currSearchParam->MinPaxes ;$i++ ) {
						array_push($paxages, BFCHelper::$defaultAdultsAge);
					}		
				}
				$showPerson = !$currSearchParam->FixedPaxes;
	
	$currParam = array(
		'searchid' => uniqid('', true),
		'searchtypetab' =>  '0',
		'newsearch' => 1,
		'getallresults' => 1,
		'checkin' => $checkin,
		'checkout' => $checkout,
		'duration' => $checkout->diff($checkin)->format('%a'),
					'checkFullPeriod' => $currSearchParam->CheckFullPeriod,
		'searchterm' => '',
		'searchTermValue' => '',
		'stateIds' => '',
		'regionIds' =>  '',
		'cityIds' =>  '',
		'zoneIds' =>  '',
		'merchantIds' =>  '',
		'groupresourceIds' =>  '',
					'merchantTagIds' => $currSearchParam->MrcTags,
					'productTagIds' => $currSearchParam->ResTags ,
					'groupTagsIds' => $currSearchParam->GrpTags,
					'masterTypeId' => $currSearchParam->ProductCategories,
		'merchantResults' => '',
		'resourcegroupsResults' => '',
					'merchantCategoryId' => $currSearchParam->MerchantCategories,
		'merchantId' => 0,
		'zoneId' =>  0,
					'availabilitytype' => $currSearchParam->AvailabilityTypes ,
					'itemtypes' => $currSearchParam->ItemTypes,
					'groupresulttype' => $currSearchParam->GroupResultType,
		'locationzone' => 0,
		'cultureCode' => $cultureCode,
		'minqt' =>  1,
		'maxqt' =>  10,
					'paxages' => $paxages ,
					'paxes' => $currSearchParam->MinPaxes,
					'calculateperson' => ($showPerson ?1:0),
		'tags' => '',
		'resourceName' => 0,
		'refid' =>0,
		'pricerange' => '',
					'onlystay' => $currSearchParam->CheckAvailability,
		'resourceId' => '',
		'extras' =>'',
		'packages' =>  '',
		'pricetype' => '',
		'filters' => '',
		'rateplanId' =>  '',
		'variationPlanId' =>  '',
		'gotCalculator' => '',
		'totalDiscounted' =>  '',
		'suggestedstay' =>'',
		'variationPlanIds' => '',
		'points' =>  '',
		'filter_order' => '',
		'filter_order_Dir' => '',
		'getBaseFiltersFor' => '',
		'groupResultType' => 0,
	);
	BFCHelper::setSearchParamsSessionforEvent($currParam);
		if(isset($_REQUEST['newsearch'])){
			bfi_setSessionFromSubmittedDataforEvent();
		}

				bfi_get_template("shared/results_details.php",array("resource"=>$resource,"eventId"=>$eventid,"discountcodes"=>($currSearchParam->DiscountCode ?? ""),"currencyclass"=>$currencyclass,"currSearchParam"=>$currSearchParam,"searchContest"=>$searchContest));	
			}
		}
		die();
	}   
	//------------------------------
	if(empty($task)){
			switch ( $layout) {
				case _x('mapspopup', 'Page slug', 'bfi' ):
					$paramRef = array(
						"resource"=>$resource
						);
					bfi_get_template("eventdetails/mapspopup.php",$paramRef);	
//					include(BFI()->plugin_path().'/templates/onselldetails/mapspopup.php'); // merchant template
					die();
				break;
			}

	}

}
?>
