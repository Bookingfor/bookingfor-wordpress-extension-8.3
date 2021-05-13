<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
 ?>
<?php
$merchant_id = get_query_var( 'merchant_id', 0 );
$parent_id = get_query_var( 'parent_id', 0 );
$language = $GLOBALS['bfi_lang'];
$currencyclass = bfi_get_currentCurrency();
?>
<?php
	$sitename = sanitize_text_field( get_bloginfo( 'name' ) );
	$layout = get_query_var( 'bfi_layout', '' );
	$isportal = COM_BOOKINGFORCONNECTOR_ISPORTAL;

	$model = new BookingForConnectorModelMerchantDetails;
	$merchant = $model->getItem($merchant_id);	 
if (empty($merchant)) {
	  global $wp_query;
	  $wp_query->set_404();
	  status_header( 404 );
	  get_template_part( 404 );
	  exit();		
}
	$indirizzo = isset($merchant->AddressData->Address)?$merchant->AddressData->Address:"";
	$cap = isset($merchant->AddressData->ZipCode)?$merchant->AddressData->ZipCode:""; 
	$comune = isset($merchant->AddressData->CityName)?$merchant->AddressData->CityName:"";
	$stato = isset($merchant->AddressData->StateName)?$merchant->AddressData->StateName:"";
	$merchantName = BFCHelper::getLanguage($merchant->Name, $language, null, array('ln2br'=>'ln2br', 'striptags'=>'striptags')); 

	$merchantdetails_page = get_post( bfi_get_page_id( 'merchantdetails' ) );
	$url_merchant_page = get_permalink( $merchantdetails_page->ID );
	$routeMerchant = $url_merchant_page . $merchant->MerchantId.'-'.BFI()->seoUrl($merchant->Name);
	$canonicalUrl = $routeMerchant;

	if(!isset($_GET['task']) &&  ($layout !=_x('contactspopup', 'Page slug', 'bfi' ) &&  ($layout != _x('mapspopup', 'Page slug', 'bfi' )) &&  ($layout != _x('thankspopup', 'Page slug', 'bfi' )) &&  ($layout != _x('errorspopup', 'Page slug', 'bfi' ))  )) {

	$model->setMerchantId($merchant_id);
	if (!empty($parent_id)) {
		$model->setParentId($parent_id);
	}
	$model->setItemPerPage(COM_BOOKINGFORCONNECTOR_ITEMPERPAGE);

	$listName = "";
	$checkAnalytics = false;
	$itemType = 0;
	$totalItems = array();
	$type = "";
	$sendAnalytics = true;
	$layoutcriteo = "";
	$listNameAnalytics =  BFCHelper::getVar('lna','0');

	$total = 0;
	$resources = null;
	$offers = null;
	$offer = null;
	$ratings = null;
	$summaryRatings = null;
	$paramRefevents = array(
						"total"=>0
						);
	$summaryRatings = $merchant->Avg;

/*---------------IMPOSTAZIONI SEO----------------------*/
	$merchantDescriptionSeo = BFCHelper::getLanguage($merchant->Description, $language, null, array( 'nobr'=>'nobr', 'bbcode'=>'bbcode', 'striptags'=>'striptags')) ;
	if (!empty($merchantDescriptionSeo) && strlen($merchantDescriptionSeo) > 170) {
	    $merchantDescriptionSeo = substr($merchantDescriptionSeo,0,170);
	}

	$titleHead = "$merchantName ($comune, $stato) - $merchant->MainCategoryName - $sitename";
	$keywordsHead = "$merchantName, $comune, $stato, $merchant->MainCategoryName";
	$merchantNameTrack =  BFCHelper::string_sanitize($merchantName);
	$merchantCategoryNameTrack =  BFCHelper::string_sanitize($merchant->MainCategoryName);
		
	switch ( $layout) {
		case _x( 'resources', 'Page slug', 'bfi' ):
			$titleHead = "$merchantName ($comune, $stato) - " . _x( 'resources', 'Page slug', 'bfi' ) . " - $sitename";
			$keywordsHead = "$merchantName, $comune, $stato, $merchant->MainCategoryName, " . _x( 'resources', 'Page slug', 'bfi' ) ;
			$canonicalUrl = $routeMerchant .'/'._x( 'resources', 'Page slug', 'bfi' );

			$resources = $model->getItems('',0, $merchant_id, $parent_id);
			$total = $model->getTotal();
			$listNameAnalytics =  5;
			$type = "Resource";
			$itemType = 1;
			if ($resources  != null){
				foreach ($resources as $key => $value) {
					$obj = new stdClass;
					$obj->Id = $value->ResourceId;
					$obj->Name = $value->Name;
					$totalItems[] = $obj;
				}
			}
		break;
		case _x( 'events', 'Page slug', 'bfi' ):
			$titleHead = "$merchantName ($comune, $stato) - " . _x( 'events', 'Page slug', 'bfi' ) . " - $sitename";
			$keywordsHead = "$merchantName, $comune, $stato, $merchant->MainCategoryName, " . _x( 'events', 'Page slug', 'bfi' ) ;
			$canonicalUrl = $routeMerchant .'/'._x('events', 'Page slug', 'bfi' );
				$page = bfi_get_current_page() ;
				$start = ($page - 1) * COM_BOOKINGFORCONNECTOR_ITEMPERPAGE;
				$onlylist =  !empty($atts['onlylist']) ? $atts['onlylist'] : '0';

				bfi_setSessionFromSubmittedDataEvent();
				$searchmodel = new BookingForConnectorModelSearchEvent();
				$searchmodel->setItemPerPage(COM_BOOKINGFORCONNECTOR_ITEMPERPAGE);
				$currParam = BFCHelper::getSearchEventParamsSession();
				$currParam['merchantId'] = $merchant_id;
				$newsearch = isset($_REQUEST['newsearch']) ? $_REQUEST['newsearch'] : '1';
				$currParam['newsearch'] = $newsearch;

				$searchmodel->setParam($currParam);
				BFCHelper::setSearchEventParamsSession($currParam);

				$listNameAnalytics = 11;
				$items = $searchmodel->getItems(false, false, $start,COM_BOOKINGFORCONNECTOR_ITEMPERPAGE);
				$items = is_array($items) ? $items : array();
				$total=$searchmodel->getTotal();

				$filter_order = $searchmodel->getOrdering();
				$filter_order_Dir = $searchmodel->getDirection();
				$currSorting = $filter_order ."|".$filter_order_Dir ;
				$pages = ceil($total / COM_BOOKINGFORCONNECTOR_ITEMPERPAGE);

				$GLOBALS['bfEventSearchedItems'] = $items;
				$GLOBALS['bfEventSearchedItemsTotal'] = $total;
				$GLOBALS['bfEventSearchedItemsCurrSorting'] = $currSorting;
				$GLOBALS['bfEventSearched'] = 1;

				if ($total>0) {
					$paramRefevents = array(
						"total"=>$total,
						"items"=>$items,
						"page"=>$page,
						"pages"=>$pages,
						"currParam"=>$currParam,
						"filter_order"=>$filter_order,
						"filter_order_Dir"=>$filter_order_Dir,
						"currSorting"=>$currSorting,
						"listNameAnalytics"=>$listNameAnalytics,
						"showfilter"=>0
						);
				}
	

		break;
		case _x('offers', 'Page slug', 'bfi' ):
			$titleHead = "$merchantName ($comune, $stato) - " . _x('offers', 'Page slug', 'bfi' ) . " - $sitename";
			$keywordsHead = "$merchantName, $comune, $stato, $merchant->MainCategoryName, " . _x('offers', 'Page slug', 'bfi' ) ;
			$canonicalUrl = $routeMerchant .'/'._x('offers', 'Page slug', 'bfi' );
			$offers = $model->getItems('offers',0, $merchant_id);
			$total = $model->getTotal('offers');
			$listNameAnalytics =  6;
			$type = "Offer";
			$itemType = 1;
			if ($offers  != null){
				foreach ($offers as $key => $value) {
					$obj = new stdClass;
					$obj->Id = $value->VariationPlanId;
					$obj->Name = $value->Name;
					$totalItems[] = $obj;
				}
			}

		break;
		case _x( 'onsellunits', 'Page slug', 'bfi' ):
			$titleHead = "$merchantName ($comune, $stato) - " . _x( 'onsellunits', 'Page slug', 'bfi' ) . " - $sitename";
			$keywordsHead = "$merchantName, $comune, $stato, $merchant->MainCategoryName, " . _x( 'onsellunits', 'Page slug', 'bfi' ) ;
			$canonicalUrl = $routeMerchant .'/'._x( 'onsellunits', 'Page slug', 'bfi' );
			$resources = $model->getItems('onsellunits',0, $merchant_id);
			$total = $model->getTotal("onsellunits");
			$listNameAnalytics =  7;
			$type = "Sales Resource";
			$itemType = 1;
			if ($resources  != null){
				foreach ($resources as $key => $value) {
					$obj = new stdClass;
					$obj->Id = $value->ResourceId;
					$obj->Name = $value->Name;
					$totalItems[] = $obj;
				}
			}

		break;
		case _x('offer', 'Page slug', 'bfi' ):
			$offerId = get_query_var( 'bfi_id', 0 );
			if(!empty($offerId)){
				$offer = $model->getMerchantOfferFromService($offerId);
				$merchantDescriptionSeo = BFCHelper::getLanguage($offer->Description, $language, null, array( 'nobr'=>'nobr', 'bbcode'=>'bbcode', 'striptags'=>'striptags')) ;
				if (!empty($merchantDescriptionSeo) && strlen($merchantDescriptionSeo) > 170) {
					$merchantDescriptionSeo = substr($merchantDescriptionSeo,0,170);
				}
				$titleHead = "$merchantName: $offer->Name ($comune, $stato) - $merchant->MainCategoryName - " . _x('offer', 'Page slug', 'bfi' ) . " - $sitename";
				$keywordsHead = "$offer->Name, $merchantName, $comune, $stato, $merchant->MainCategoryName, " . _x('offer', 'Page slug', 'bfi' ) ;
				$canonicalUrl =  $routeMerchant.'/'._x('offer', 'Page slug', 'bfi' ).'/'. $offerId. '-' . BFCHelper::getSlug($offer->Name );
				$type = "Offer";
				$itemType = 0;
				$obj = new stdClass;
				$obj->Id = $offer->VariationPlanId;
				$obj->Name = $offer->Name;
				$totalItems[] = $obj;
			}
		break;
		case _x('thanks', 'Page slug', 'bfi' ):
		case 'thanks':
			$layoutcriteo = "thanks";
			$itemType = 2;
		break;
		case _x('errors', 'Page slug', 'bfi' ):
		case 'errors':
			$sendAnalytics = false;
		break;
		case _x('reviews', 'Page slug', 'bfi' ):
			$titleHead = "$merchantName ($comune, $stato) - " . _x('reviews', 'Page slug', 'bfi' ) . " - $sitename";
			$keywordsHead = "$merchantName, $comune, $stato, $merchant->MainCategoryName, " . _x('reviews', 'Page slug', 'bfi' ) ;
			$canonicalUrl = $routeMerchant .'/'._x('reviews', 'Page slug', 'bfi' );
			if(isset($_POST) && !empty($_POST)) {
				$_SESSION['ratings']['filters']['typologyid'] = $_POST['filters']['typologyid'];
			}
			$ratings = $model->getItems('ratings',0, $merchant_id, null);
			$total = $model->getTotal('ratings');
			$summaryRatings = $merchant->Avg;
			//$summaryRatings = $model->getMerchantRatingAverageFromService($merchant_id);
			$sendAnalytics = false;
		break;
		case _x('review', 'Page slug', 'bfi' ):
			$sendAnalytics = false;
		break;
		case _x('redirect', 'Page slug', 'bfi' ):
			$sendAnalytics = false;
		break;		
		default:
			$type = "Merchant";
			$itemType = 0;
			$obj = new stdClass;
			$obj->Id = $merchant->MerchantId;
			$obj->Name = $merchant->Name;
			$totalItems[] = $obj;
			$layoutcriteo = "default";

	}
	
	$listName = BFCHelper::$listNameAnalytics[$listNameAnalytics];
	$analyticsListName = $listName;

	if ( defined('WPSEO_VERSION') ) {
				add_filter( 'wpseo_title', function() use ($titleHead) {return	$titleHead;} , 10, 1 );
				add_filter( 'wpseo_metakey', function() use ($keywordsHead) {return $keywordsHead; } , 10, 1  );
				add_filter( 'wpseo_metadesc', function() use ($merchantDescriptionSeo) {return $merchantDescriptionSeo; } , 10, 1 );
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
				add_filter( 'wpseo_opengraph_desc', function() use ($merchantDescriptionSeo) {return	$merchantDescriptionSeo;});
				if (!empty($merchant->LogoUrl)){
					add_action( 'wpseo_add_opengraph_images', 'add_images' );
					function add_images( $object ) {
					  $object->add_image( COM_BOOKINGFORCONNECTOR_DEFAULTIMAGE );
					}
					add_filter( 'wpseo_opengraph_image', function() use ($merchant) {return	"https:".BFCHelper::getImageUrlResized('merchant',$merchant->LogoUrl, 'logobig');} );
				}
	}else{
		add_filter( 'wp_title', function() use ($titleHead) {return	$titleHead;} , 10, 1 );
		add_action( 'wp_head', function() use ($keywordsHead) {return bfi_add_meta_keywords($keywordsHead); }, 10, 1);
		add_action( 'wp_head', function() use ($merchantDescriptionSeo) {return bfi_add_meta_description($merchantDescriptionSeo); } , 10, 1 );
		add_action( 'wp_head', function() use ($canonicalUrl) {return bfi_add_canonicalurl($canonicalUrl); }, 10, 1);
		add_action( 'wp_head', 'bfi_add_meta_robots', 10, 1);
		// OpenGraph for Social
		add_action( 'wp_head', function() use ($titleHead) {return bfi_add_opengraph_title($titleHead); }, 10, 1);
		add_action( 'wp_head', function() use ($merchantDescriptionSeo) {return bfi_add_opengraph_desc($merchantDescriptionSeo); } , 10, 1 );
		add_action( 'wp_head', function() use ($canonicalUrl) {return bfi_add_opengraph_url($canonicalUrl); }, 10, 1);
		if (!empty($merchant->LogoUrl)){
			add_action( 'wp_head', function() use ($merchant) {return bfi_add_opengraph_image("https:".BFCHelper::getImageUrlResized('merchant',$merchant->LogoUrl, 'logobig')); }, 10, 1);
		}
	}



	get_header( 'merchantdetails' );
	do_action( 'bookingfor_before_main_content' );

?>
					<div class="bfi-row bfi-rowcontainer bfi-merchantdetails-page">
					<?php 
						if (!COM_BOOKINGFORCONNECTOR_ISMOBILE) {
					?>
						<div class="bfi-col-md-3 bfi-col-sm-3 bfi-col-xs-12  bfi-sidebar">

							<?php 
							dynamic_sidebar('bfisidebar'); 
							bfi_get_template("widgets/smallmap.php");	
							bfi_get_template("widgets/proximitypoi.php",array("resource"=>$merchant));	
							bfi_get_template("widgets/reviews.php");	
							
							$currentView = '';
							$orderType = "a";
							$task = "sendContact";
//							$routeThanks = $routeMerchant .'/'._x('thanks', 'Page slug', 'bfi' );
//							$routeThanksKo = $routeMerchant .'/'._x('errors', 'Page slug', 'bfi' );
							$routeThanks = $routeMerchant .'/thanks';
							$routeThanksKo = $routeMerchant .'/errors';
							$paramRefInfo = array(
								"merchant"=>$merchant,
								"currentView"=>$currentView,
								"resource"=>null,
								"task"=>$task,
								"orderType"=>$orderType,
								"routeThanks"=>$routeThanks,
								"routeThanksKo"=>$routeThanksKo,
								);
							//bfi_get_template("/shared/infocontactfaq.php",$paramRefInfo);	
	
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
	$paramRef = array(
		"merchant"=>$merchant,
		"merchant_id"=>$merchant_id,
		"parent_id"=>$parent_id,
		"indirizzo"=>$indirizzo,
		"cap"=>$cap,
		"currencyclass"=>$currencyclass,
		"comune"=>$comune,
		"stato"=>$stato,
		"merchantName"=>$merchantName,
		"listNameAnalytics"=>$listNameAnalytics,
		"analyticsListName"=>$analyticsListName,
		"total"=>$total,
		"resources"=>$resources,
		"offers"=>$offers,
		"offer"=>$offer,
		"ratings"=>$ratings,
		"summaryRatings"=>$summaryRatings
		);
	switch ( $layout) {
		case _x( 'resources', 'Page slug', 'bfi' ):
			bfi_get_template("merchantdetails/resources.php",$paramRef);	
		?>
		<div class="bfi-clearfix"></div>
		<?php  bfi_get_template('shared/merchant_small_details.php',array("merchant"=>$merchant,"routeMerchant"=>$routeMerchant));  ?>
		</div>
		<?php 
//		include(BFI()->plugin_path().'/templates/merchantdetails/resources.php'); // merchant template
		break;
		case _x( 'events', 'Page slug', 'bfi' ):
?>
						<div class="bfi-title-name bfi-hideonextra"><h1><?php echo  $merchant->Name?></h1>
				<span class="bfi-item-rating">
<?php 
				$ratingModel = array(
					"ratingModel"=>$merchant
				);
				bfi_get_template("shared/stars_rating.php",$ratingModel);
?>
				</span>
			</div>
			
			<?php 

		bfi_get_template("search/event.php",$paramRefevents);
		?>
		<div class="bfi-clearfix"></div>
		<?php  bfi_get_template('shared/merchant_small_details.php',array("merchant"=>$merchant,"routeMerchant"=>$routeMerchant));  ?>
		</div>
		<?php 

		break;
		case _x('offers', 'Page slug', 'bfi' ):
			bfi_get_template("merchantdetails/offers.php",$paramRef);	
		?>
		<div class="bfi-clearfix"></div>
		<?php  bfi_get_template('shared/merchant_small_details.php',array("merchant"=>$merchant,"routeMerchant"=>$routeMerchant));  ?>
		</div>
		<?php 
//			include(BFI()->plugin_path().'/templates/merchantdetails/offers.php'); // merchant template
		break;
		case _x( 'onsellunits', 'Page slug', 'bfi' ):
			bfi_get_template("merchantdetails/onsellunits.php",$paramRef);	
//			include(BFI()->plugin_path().'/templates/merchantdetails/onsellunits.php'); // merchant template
		break;
		case _x('offer', 'Page slug', 'bfi' ):
			if(!empty($offer)){
				bfi_get_template("merchantdetails/offer-details.php",$paramRef);	
//				include(BFI()->plugin_path().'/templates/merchantdetails/offer-details.php'); // merchant template
			}
		break;
		case _x('thanks', 'Page slug', 'bfi' ):
		case 'thanks':
			bfi_get_template("merchantdetails/thanks.php",$paramRef);	
//			include(BFI()->plugin_path().'/templates/merchantdetails/thanks.php'); // merchant template
		break;
		case _x('errors', 'Page slug', 'bfi' ):
		case 'errors':
			bfi_get_template("merchantdetails/errors.php",$paramRef);	
//			include(BFI()->plugin_path().'/templates/merchantdetails/errors.php'); // merchant template
		break;
		case _x('reviews', 'Page slug', 'bfi' ):
			bfi_get_template("merchantdetails/reviews.php",$paramRef);	
//			include(BFI()->plugin_path().'/templates/merchantdetails/reviews.php'); // merchant template
		break;
		case _x('review', 'Page slug', 'bfi' ):
			bfi_get_template("merchantdetails/review.php",$paramRef);	
//			include(BFI()->plugin_path().'/templates/merchantdetails/review.php'); // merchant template
		break;
		case _x('redirect', 'Page slug', 'bfi' ):
			bfi_get_template("merchantdetails/redirect.php",$paramRef);	
//			include(BFI()->plugin_path().'/templates/merchantdetails/redirect.php'); // merchant template
		break;		
		default:
			
			bfi_get_template("merchantdetails/merchantdetails.php",$paramRef);	
//			include(BFI()->plugin_path().'/templates/merchantdetails/merchantdetails.php'); // merchant template
	}

		if(COM_BOOKINGFORCONNECTOR_CRITEOENABLED && ($layoutcriteo == "thanks" || $layoutcriteo == "default")) {
			$merchants = array();
			$merchants[] = $merchant->MerchantId;
			if($layoutcriteo == "thanks") {								
				$orderid = isset($_REQUEST['orderid'])?$_REQUEST['orderid']:0;
				$criteoConfig = BFCHelper::getCriteoConfiguration(4, $merchants, $orderid);	
			} else if ($layout == "default") {
				$criteoConfig = BFCHelper::getCriteoConfiguration(2, $merchants);
			}
			if(isset($criteoConfig) && isset($criteoConfig->enabled) && $criteoConfig->enabled && count($criteoConfig->merchants) > 0) {
//				$document->addScript('//static.criteo.net/js/ld/ld.js');
				echo '<script type="text/javascript" src="//static.criteo.net/js/ld/ld.js" async="true"></script>';
				if($layoutcriteo == "thanks") {
//					$document->addScriptDeclaration('window.criteo_q = window.criteo_q || []; 
//					window.criteo_q.push( 
//						{ event: "setAccount", account: ' . $criteoConfig->campaignid . '}, 
//						{ event: "setSiteType", type: "d" }, 
//						{ event: "setEmail", email: "" }, 
//						{ event: "trackTransaction", id: "' . $criteoConfig->transactionid . '",  item: [' . json_encode($criteoConfig->orderdetails) . '] }
//					);');
					echo '<script type="text/javascript"><!--
					window.criteo_q = window.criteo_q || []; 
					var deviceTypeCriteo = /iPad/.test(navigator.userAgent) ? "t" : /Mobile|iP(hone|od)|Android|BlackBerry|IEMobile|Silk/.test(navigator.userAgent) ? "m" : "d";
					window.criteo_q.push( 
						{ event: "setAccount", account: ' . $criteoConfig->campaignid . '}, 
						{ event: "setSiteType", type: deviceTypeCriteo }, 
						{ event: "setEmail", email: "" }, 
						{ event: "trackTransaction", id: "' . $criteoConfig->transactionid . '",  item: [' . json_encode($criteoConfig->orderdetails) . '] }
					);
					//--></script>';
				} else if ($layoutcriteo == "default") {
//					$document->addScriptDeclaration('window.criteo_q = window.criteo_q || []; 
//					window.criteo_q.push( 
//						{ event: "setAccount", account: ' . $criteoConfig->campaignid . '}, 
//						{ event: "setSiteType", type: "d" }, 
//						{ event: "setEmail", email: "" }, 
//						{ event: "viewItem", item: "' . $criteoConfig->merchants[0] . '" }
//					);');
					echo '<script type="text/javascript"><!--
					window.criteo_q = window.criteo_q || []; 
					var deviceTypeCriteo = /iPad/.test(navigator.userAgent) ? "t" : /Mobile|iP(hone|od)|Android|BlackBerry|IEMobile|Silk/.test(navigator.userAgent) ? "m" : "d";
					window.criteo_q.push( 
						{ event: "setAccount", account: ' . $criteoConfig->campaignid . '}, 
						{ event: "setSiteType", type: deviceTypeCriteo }, 
						{ event: "setEmail", email: "" }, 
						{ event: "viewItem", item: "' . $criteoConfig->merchants[0] . '" }
					);
					//--></script>';

				}
			}
		}

		add_action('bfi_head', 'bfi_google_analytics_EEc', 10, 1);
		do_action('bfi_head', $listName);
				
		if($total >0 && $sendAnalytics && COM_BOOKINGFORCONNECTOR_GAENABLED == 1 && !empty(COM_BOOKINGFORCONNECTOR_GAACCOUNT) && COM_BOOKINGFORCONNECTOR_EECENABLED == 1) {
			$item = $merchant;
				switch($itemType) {
					case 0:
						$value = $totalItems[0];
						$obj = new stdClass;
						$obj->id = "" . $value->Id . " - " . $type;
						$obj->name = $value->Name;
						$obj->category = $item->MainCategoryName;
						$obj->brand = $item->Name;
						$obj->variant = 'NS';
						
						echo '<script type="text/javascript"><!--
						';
						echo ('callAnalyticsEEc("addProduct", [' . json_encode($obj) . '], "item");');
						echo "//--></script>";
						
						break;
					case 1:
						$allobjects = array();
						foreach ($totalItems as $key => $value) {
							$obj = new stdClass;
							$obj->id = "" . $value->Id . " - " . $type;
							$obj->name = $value->Name;
							$obj->category = $item->MainCategoryName;
							$obj->brand = $item->Name;
							$obj->position = $key;
							$allobjects[] = $obj;
						}
						echo '<script type="text/javascript"><!--
						';
						echo ('callAnalyticsEEc("addImpression", ' . json_encode($allobjects) . ', "list");');
						echo "//--></script>";
						break;
					case 2:
						$orderid = 	isset($_REQUEST['orderid'])?$_REQUEST['orderid']:0;;
						$act = 	BFCHelper::getVar('act');
						if(!empty($orderid) && $act!="Contact" ){
							$order = BFCHelper::getSingleOrderFromService($orderid);
							$purchaseObject = new stdClass;
							$purchaseObject->id = "" . $order->OrderId;
							$purchaseObject->affiliation = "" . $order->Label;
							$purchaseObject->revenue = $order->TotalAmount;
							$purchaseObject->tax = 0.00;
							
							$allobjects = array();
							$allservices = array();
							$svcTotal = 0;
							
							if(!empty($order->NotesData) && !empty(bfi_simpledom_load_string($order->NotesData)->xpath("//price"))) {
								$allservices = array_values(array_filter(bfi_simpledom_load_string($order->NotesData)->xpath("//price"), function($prc) {
									return (string)$prc->tag == "extrarequested";
								}));
								
								
								if(!empty($allservices )){
									foreach($allservices as $svc) {
										$svcObj = new stdClass;
										$svcObj->id = "" . (int)$svc->priceId . " - Service";
										$svcObj->name = (string)$svc->name;
										$svcObj->category = "Services";
										$svcObj->brand = $item->Name;
										$svcObj->variant = (string)BFCHelper::getItem($order->NotesData, 'nome', 'unita');
										$svcObj->price = round((float)$svc->discountedamount / (int)$svc->quantity, 2);
										$svcObj->quantity = (int)$svc->quantity;
										$allobjects[] = $svcObj;
										$svcTotal += (float)$svc->discountedamount;
									}
								}
							
								$mainObj = new stdClass;
								$mainObj->id = "" . $order->RequestedItemId . " - Resource";
								$mainObj->name = (string)BFCHelper::getItem($order->NotesData, 'nome', 'unita');
								$mainObj->variant = (string)BFCHelper::getItem($order->NotesData, 'refid', 'rateplan');
								$mainObj->category = $item->MainCategoryName;
								$mainObj->brand = $item->Name;
								$mainObj->price = $order->TotalAmount - $svcTotal;
								$mainObj->quantity = 1;
								
								$allobjects[] = $mainObj;
								
								echo '<script type="text/javascript"><!--
								';
								echo ('callAnalyticsEEc("addProduct", ' . json_encode($allobjects) . ', "checkout", "", {"step": 3,});
									   callAnalyticsEEc("addProduct", ' . json_encode($allobjects) . ', "purchase", "", ' . json_encode($purchaseObject) . ');');
								echo "//--></script>";
			
							}
						}

						break;
				}
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
						</div>
					<?php 
						if (COM_BOOKINGFORCONNECTOR_ISMOBILE) {
					?>
						<div class="bfi-col-md-3 bfi-col-sm-3 bfi-col-xs-12  bfi-sidebar">
							<?php 
							bfi_get_template("widgets/smallmap.php");	
							bfi_get_template("widgets/proximitypoi.php",array("resource"=>$merchant));	

							bfi_get_template("widgets/reviews.php");	
							?>
							<div class="bfilastmerchants bfi-hide">
								<h3><?php _e('Recently seen', 'bfi') ?></h3>
							</div>
						</div>
					<?php 
						}
					?>						
					</div>
	<?php
		/**
		 * bookingfor_after_main_content hook.
		 *
		 * @hooked bookingfor_output_content_wrapper_end - 10 (outputs closing divs for the content)
		 */
		do_action( 'bookingfor_after_main_content' );
	?>	
<?php get_footer( 'merchantdetails' ); ?>
<?php
  
  }
  else {
    $task = BFCHelper::getVar('task','');
	
//	$model = new BookingForConnectorModelMerchantDetails;
//	$merchant = $model->getItem($merchant_id);	 
	$resourceId = 0;
	$resourcegroupId = 0;

	if($task == 'getMerchantResources') { // ricerca classica
		if(!empty(BFCHelper::getVar('refreshcalc',''))){
			bfi_setSessionFromSubmittedData();
		}
		$output = '';
		$merchants = array();
		$merchants[] = $merchant->MerchantId;
		$criteoConfig = null;
		if(COM_BOOKINGFORCONNECTOR_CRITEOENABLED ){
			$criteoConfig = BFCHelper::getCriteoConfiguration(2, $merchants);
		}
//		include(BFI()->plugin_path().'/templates/merchantdetails/search.php'); //merchant temp 
		
		bfi_get_template("shared/search_details.php",array("merchant"=>$merchant,"resourceId"=>$resourceId,"resourcegroupId"=>$resourcegroupId,"currencyclass"=>$currencyclass));	
//		include(BFI()->plugin_path().'/templates/search_details.php'); //merchant temp 
		die($output);
	} 
	if($task == 'getGroupResources') { // ricerca per gruppo di risorse spiaggia
		if(!empty(BFCHelper::getVar('refreshcalc',''))){
			bfi_setSessionFromSubmittedData('search.params.mapsells');
		}
        $currParam = BFCHelper::getSearchParamsSession('search.params.mapsells');
		$currParam['paxes'] = 0;
		BFCHelper::setSearchParamsSession($currParam,'search.params.mapsells');
		$output = '';
		$merchants = array();
		$merchants[] = $merchant->MerchantId;
		$criteoConfig = null;
		if(COM_BOOKINGFORCONNECTOR_CRITEOENABLED ){
			$criteoConfig = BFCHelper::getCriteoConfiguration(2, $merchants);
		}
//		include(BFI()->plugin_path().'/templates/merchantdetails/search.php'); //merchant temp 
		
		bfi_get_template("shared/search_details_mapsells.php",array("merchant"=>$merchant,"resourceId"=>$resourceId,"resourcegroupId"=>$resourcegroupId,"currencyclass"=>$currencyclass));	
//		include(BFI()->plugin_path().'/templates/search_details.php'); //merchant temp 
		die($output);
	} 
	//------------------------------
	if(empty($task)){
			switch ( $layout) {
				case _x('contactspopup', 'Page slug', 'bfi' ):
					
					$orderType = "a";
					$task = "sendContact";
					$popupview = true;
					$layout ='';
					$currentView='merchant';
//					$merchant = $model->getItem($merchant_id);	
					$merchantdetails_page = get_post( bfi_get_page_id( 'merchantdetails' ) );
					$url_merchant_page = get_permalink( $merchantdetails_page->ID );
					$routeMerchant = $url_merchant_page . $merchant->MerchantId.'-'.BFI()->seoUrl($merchant->Name);
					$uriMerchant = $routeMerchant;

					$resource_id = get_query_var( 'bfi_id', 0 );
					$resourceType = get_query_var( 'bfi_name', 0 );
					if(!empty($resource_id) && $resourceType == _x( 'accommodation-details', 'Page slug', 'bfi' )){
						$model = new BookingForConnectorModelResource;
						$resource = $model->getItem($resource_id);
						$currentView = 'resource';
						$orderType = "c";
						$task = "sendInforequest";
					}
					if(!empty($resource_id) && $resourceType == _x( 'properties-for-sale', 'Page slug', 'bfi' )){
						$model = new BookingForConnectorModelOnSellUnit;
						$resource = $model->getItem($resource_id);
						$currentView = 'onsellunit';
						$orderType = "b";
						$task = "sendOnSellrequest";
					}
					if(!empty($resource_id) && $resourceType == _x( 'resourcegroupdetails', 'Page slug', 'bfi' )){
						$model = new BookingForConnectorModelResourcegroup;
						$resource = $model->getResourcegroupFromService($resource_id);
						$currentView = 'resource';
						$orderType = "c";
						$task = "sendInforequest";
					}


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
					"currencyclass"=>$currencyclass,
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

//					include(BFI()->plugin_path().'/templates/merchant-sidebar-contact.php'); // merchant template
				die($output);
				break;
				case _x('mapspopup', 'Page slug', 'bfi' ):
//					$merchant = $model->getItem($merchant_id);	
					$paramRef = array(
						"merchant"=>$merchant
						);
					bfi_get_template("merchantdetails/mapspopup.php",$paramRef);	
//					include(BFI()->plugin_path().'/templates/merchantdetails/mapspopup.php'); // merchant template
					die();
				break;
				case _x('thankspopup', 'Page slug', 'bfi' ):
				$output = '';
					$paramRef = array(
						"merchant"=>$merchant
						);
					bfi_get_template("merchantdetails/thanks.php",$paramRef);	
//					include(BFI()->plugin_path().'/templates/merchantdetails/thanks.php'); // merchant template
				die($output);
				case _x('errorspopup', 'Page slug', 'bfi' ):
				$output = '';
					$paramRef = array(
						"merchant"=>$merchant
						);
					bfi_get_template("merchantdetails/errors.php",$paramRef);	
//					include(BFI()->plugin_path().'/templates/merchantdetails/thanks.php'); // merchant template
				die($output);
				break;

			}

	}

}
?>
