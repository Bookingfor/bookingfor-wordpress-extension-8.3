<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'bfi_Shortcodes' ) ) {
/**
 * bfi_Shortcodes class
 *
 * @class       bfi_Shortcodes
 * @version     2.0.5
 * @package     Bookingfor/Classes
 * @category    Class
 * @author      Bookingfor
 */
class bfi_Shortcodes {

	/**
	 * $shortcode_tag
	 * holds the name of the shortcode tag
	 * @var string
	 */
	public $shortcode_tag = 'bfi_panel';

	/**
	 * Init shortcodes.
	 */
	public static function init() {
		$shortcodes = array(
			'bookingfor_search'           => __CLASS__ . '::bfi_shortcode_search',
			'bookingfor_merchants'           => __CLASS__ . '::bfi_shortcode_merchants',
			'bookingfor_merchant'           => __CLASS__ . '::bfi_shortcode_merchant',
			'bookingfor_merchantscarousel'           => __CLASS__ . '::bfi_shortcode_merchantscarousel',
			'bookingfor_resourcescarousel'           => __CLASS__ . '::bfi_shortcode_resourcescarousel',
			'bookingfor_eventscarousel'           => __CLASS__ . '::bfi_shortcode_eventscarousel',
			'bookingfor_poicarousel'           => __CLASS__ . '::bfi_shortcode_poicarousel',
			'bookingfor_resources'           => __CLASS__ . '::bfi_shortcode_resources',
			'bookingfor_groupedresource'           => __CLASS__ . '::bfi_shortcode_groupedresource',
			'bookingfor_onsells'           => __CLASS__ . '::bfi_shortcode_onsells',
			'bookingfor_tag'           => __CLASS__ . '::bfi_shortcode_tag',
			'bookingfor_currencyswitcher'           => __CLASS__ . '::bfi_shortcode_currencyswitcher',
			'bookingfor_events'           => __CLASS__ . '::bfi_shortcode_events',
			'bookingfor_poi'           => __CLASS__ . '::bfi_shortcode_pointsofinterests',
			'bookingfor_event'           => __CLASS__ . '::bfi_shortcode_event',
			'bookingfor_dowidget'           => __CLASS__ . '::bfi_shortcode_dowidget',
			'bookingfor_search_result'           => __CLASS__ . '::bfi_shortcode_search_result',
			'bookingfor_search_result_rental'           => __CLASS__ . '::bfi_shortcode_search_result_rental',
             'bookingfor_offers'           => __CLASS__ . '::bfi_shortcode_offers',

//			'buildings'                    => __CLASS__ . '::buildings',
//			'real_estates'               => __CLASS__ . '::realestates',
//			'tag'            => __CLASS__ . '::tag',
		);
		foreach ( $shortcodes as $shortcode => $function ) {
			add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );
		}
	}

	/**
	 * __construct
	 * class constructor will set the needed filter and action hooks
	 *
	 * @param array $args
	 */
	function __construct($args = array()){
//		if ( is_admin() ){
//			add_action( 'admin_head', array( $this, 'admin_head') );
//			add_action( 'admin_enqueue_scripts', array($this , 'admin_enqueue_scripts' ) );
//		}

	}

	/**
	 * admin_head
	 * calls your functions into the correct filters
	 * @return void
	 */
	function admin_head() {
		// check user permissions
		if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) ) {
			return;
		}
	}


	/**
	 * admin_enqueue_scripts
	 * Used to enqueue custom styles
	 * @return void
	 */
	function admin_enqueue_scripts(){
	}


	/**
	 * Shortcode Wrapper.
	 *
	 * @param string[] $function
	 * @param array $atts (default: array())
	 * @return string
	 */
	public static function shortcode_wrapper(
		$function,
		$atts    = array(),
		$wrapper = array(
			'class'  => 'bookingfor',
			'before' => null,
			'after'  => null
		)
	) {
		ob_start();

		echo empty( $wrapper['before'] ) ? '<div class="' . esc_attr( $wrapper['class'] ) . '">' : $wrapper['before'];
		call_user_func( $function, $atts );
		echo empty( $wrapper['after'] ) ? '</div>' : $wrapper['after'];

		return ob_get_clean();
	}

	/**
	 * bfi_shortcode_search form shortcode.
	 *
	 * @param mixed $atts
	 * @return string
	 */
	public static function bfi_shortcode_search( $atts ) {
		if (COM_BOOKINGFORCONNECTOR_ISBOT) {
			return '';
		}
		ob_start();
		bfi_get_template("widgets/booking-search.php",array("instance" =>$atts));
		return ob_get_clean();
	}

	public static function bfi_shortcode_currencyswitcher( $atts ) {
		ob_start();
		bfi_get_template("widgets/currency-switcher.php",array("instance" =>$atts));
		return ob_get_clean();
	}

	public static function bfi_shortcode_resourcescarousel( $atts ) {
		$atts = shortcode_atts( array(
			'tags'  => '',
			'itemspage'    =>4,
			'maxitems' => 10,  // Slugs
			'descmaxchars' => 300,  // Slugs
		), $atts );


		if ( ! $atts['tags'] ) {
			return '';
		}

		$tags =[];
		if(!empty($atts['tags'])){
			$tags =explode(",",$atts['tags']);
		}

        $instance['tags'] = $tags;
        $instance['itemspage'] = $atts['itemspage'];
        $instance['maxitems'] = $atts['maxitems'];
        $instance['descmaxchars'] = $atts['descmaxchars'];

		ob_start();
		bfi_get_template("widgets/carouselresources.php",array("instance" =>$instance,"tags" =>$tags));
		return ob_get_clean();
	}

	public static function bookingfor_poicarousel( $atts ) {
		$atts = shortcode_atts( array(
			'tags'  => '',
			'merchantid'  => 0,
			'itemspage'    =>4,
			'maxitems' => 10,  // Slugs
			'descmaxchars' => 300,  // Slugs
		), $atts );


		if ( ! $atts['tags'] && empty($atts['merchantid'])  ) {
			return '';
		}

		$tags =[];
		if(!empty($atts['tags'])){
			$tags =explode(",",$atts['tags']);
		}

        $instance['tags'] = $tags;
        $instance['itemspage'] = $atts['itemspage'];
        $instance['maxitems'] = $atts['maxitems'];
        $instance['descmaxchars'] = $atts['descmaxchars'];

		if ( is_admin() ) {
			return '';
		}
		ob_start();
		bfi_get_template("widgets/carouselpoi.php",array("instance" =>$instance,"tags" =>$tags,"merchantid" =>$merchantid));
		return ob_get_clean();
	}

	public static function bfi_shortcode_eventscarousel( $atts ) {
		$atts = shortcode_atts( array(
			'tags'  => '',
			'merchantid'  => 0,
			'itemspage'    =>4,
			'maxitems' => 10,  // Slugs
			'descmaxchars' => 300,  // Slugs
		), $atts );


		if ( ! $atts['tags'] && empty($atts['merchantid'])  ) {
			return '';
		}

		$tags =[];
		if(!empty($atts['tags'])){
			$tags =explode(",",$atts['tags']);
		}

        $instance['tags'] = $tags;
        $instance['itemspage'] = $atts['itemspage'];
        $instance['maxitems'] = $atts['maxitems'];
        $instance['descmaxchars'] = $atts['descmaxchars'];

		if ( is_admin() ) {
			return '';
		}
		ob_start();
		bfi_get_template("widgets/carouselevents.php",array("instance" =>$instance,"tags" =>$tags,"merchantid" =>$merchantid));
		return ob_get_clean();
	}

	public static function bfi_shortcode_merchantscarousel( $atts ) {
		$atts = shortcode_atts( array(
			'tags'  => '',
			'itemspage'    =>4,
			'maxitems' => 10,  // Slugs
			'descmaxchars' => 300,  // Slugs
		), $atts );


		if ( ! $atts['tags'] ) {
			return '';
		}

		$tags =[];
		if(!empty($atts['tags'])){
			$tags =explode(",",$atts['tags']);
		}

		 $instance['tags'] = $tags;
		 $instance['itemspage'] = $atts['itemspage'];
		 $instance['maxitems'] = $atts['maxitems'];
		 $instance['descmaxchars'] = $atts['descmaxchars'];
		if ( is_admin() ) {
			return '';
		}

		ob_start();
		bfi_get_template("widgets/merchantscarousel.php",array("instance" =>$instance,"tags" =>$tags));
		return ob_get_clean();
	}


	/**
	 * Merchants page shortcode.
	 *
	 * @param mixed $atts
	 * @return string
	 */
	public static function bfi_shortcode_merchants( $atts ) {

		$atts = shortcode_atts( array(
			'per_page' => COM_BOOKINGFORCONNECTOR_ITEMPERPAGE,
			'orderby'  => 'title',
			'order'    => 'desc',
			'category' => '',  // Slugs
			'rating' => '',  // Slugs
			'cityids' => '',  // Slugs
			'zoneids' => '',  // Slugs
			'onlylist' => '0',  // Slugs
		), $atts );
		if ( is_admin() ) {
			return '';
		}
		if (COM_BOOKINGFORCONNECTOR_ISBOT) {
			$atts['onlylist'] = '1';
		}

        //if ( ! $atts['category'] ) {
        //    return '';
        //}
		$page = bfi_get_current_page();
		$start = ($page - 1) * COM_BOOKINGFORCONNECTOR_ITEMPERPAGE;
		$onlylist =  !empty($atts['onlylist']) ? $atts['onlylist'] : '0';

		$merchantCategories =[];
		if(!empty($atts['category'])){
			$merchantCategories =explode(",",$atts['category']);
		}
		$rating = !empty($atts['rating'])?$atts['rating']:'';
		$cityids = [];
		if(!empty($atts['cityids'])){
			$cityids =explode(",",$atts['cityids']);
		}
		$zoneIds = [];
		
		if(!empty($atts['zoneids'])){
			$zoneIds =explode(",",$atts['zoneids']);
		}

        $language = $GLOBALS['bfi_lang'];


		$fileNameCached = 'bfi_shortcode_merchants_' . $language . '_' . implode("_", array_values($atts)). '_' . $page. '_' . COM_BOOKINGFORCONNECTOR_ITEMPERPAGE ;
		
		if (COM_BOOKINGFORCONNECTOR_ISBOT) {
			$currContent = BFI_Cache::getCachedContent($fileNameCached);
			if (!empty($currContent)) {
			    return $currContent;
			}
		}

		$model = new BookingForConnectorModelMerchants;
		$model->populateState();
		$model->setItemPerPage(COM_BOOKINGFORCONNECTOR_ITEMPERPAGE);
//
		$filter_order = $model->getOrdering();
		$filter_order_Dir = $model->getDirection();

		$currParam = $model->getParam();
		$currParam['categoryId'] = $merchantCategories;
		$currParam['rating'] = $rating;
		$currParam['cityids'] = $cityids;
		$currParam['zoneIds'] = $zoneIds;
		$newsearch = (isset($_REQUEST['newsearch']) ? $_REQUEST['newsearch'] : ($page==1))? '1' : '0' ;
		$currParam['newsearch'] = $newsearch;
		$model->setParam($currParam);

		$total = $model->getTotal();
		$items = $model->getItems();

		$merchants = is_array($items) ? $items : array();
		ob_start();
		$listNameAnalytics =4;
		$listName = BFCHelper::$listNameAnalytics[$listNameAnalytics];// "Resources Search List";
		add_action('bfi_head', 'bfi_google_analytics_EEc', 10, 1);
		do_action('bfi_head', $listName);

		$paramRef = array(
			"merchants"=>$merchants,
			"total"=>$total,
			"items"=>$items,
			"currParam"=>$currParam,
			"filter_order"=>$filter_order,
			"filter_order_Dir"=>$filter_order_Dir,
			"showfilter"=>1

			);
        $currSorting = $filter_order ."|".$filter_order_Dir ;
        $GLOBALS['bfSearchedMerchantsItems'] = $items;
        $GLOBALS['bfSearchedMerchantsItemsTotal'] = $total;
        $GLOBALS['bfSearchedMerchantsItemsCurrSorting'] = $currSorting;
        $GLOBALS['bfSearchedMerchants'] = 1;

		if(empty( $onlylist )){
?>		
					<div class="bfi-row ">
						<div class="bfi-col-md-3 bfi-col-sm-3 bfi-col-xs-12  bfi-sidebar">
							<?php 
							$setLat = COM_BOOKINGFORCONNECTOR_GOOGLE_POSX;
							$setLon = COM_BOOKINGFORCONNECTOR_GOOGLE_POSY;
							bfi_get_template("widgets/smallmap.php",array("setLat"=>$setLat,"setLon"=>$setLon));	

							bfi_get_template("widgets/search-filter-merchants.php",$paramRef);	
							?>
						</div>
						<div class="bfi-col-md-9 bfi-col-xs-12 bfi-col-sm-9  bfi-body">
							<?php 
					
			} //if onlylist
		bfi_get_template("merchantslist/merchantslist.php",$paramRef);
			if(empty( $onlylist )){
							?>
						</div>
					</div>
					<?php 
			} //if onlylist
		$output =  ob_get_clean();
		if (COM_BOOKINGFORCONNECTOR_ISBOT) {
			BFI_Cache::setCachedContent($fileNameCached,$output);
		}
		return $output;
	}
	
	/**
	 * Merchant page shortcode.
	 *
	 * @param mixed $atts
	 * @return string
	 */
	public static function bfi_shortcode_merchant( $atts ) {
		$atts = shortcode_atts( array(
			'id' => 0,  
			'layout' => '',  
			'parent_id' => 0,  
		), $atts );

		$merchant_id = !empty($atts['id'])?$atts['id']:0;
		$parent_id = !empty($atts['parent_id'])?$atts['parent_id']:0;
		$layout = !empty($atts['layout'])?$atts['layout']:'';
		if ( is_admin() || empty($merchant_id) ) {
			return '';
		}
		$language = $GLOBALS['bfi_lang'];

		$fileNameCached = 'bfi_shortcode_merchant_' . $language . '_' . implode("_", array_values($atts)). '_' . $page. '_' . COM_BOOKINGFORCONNECTOR_ITEMPERPAGE ;
		
		if (COM_BOOKINGFORCONNECTOR_ISBOT) {
			$currContent = BFI_Cache::getCachedContent($fileNameCached);
			if (!empty($currContent)) {
			    return $currContent;
			}
		}


		$model = new BookingForConnectorModelMerchantDetails;
		$merchant = $model->getItem($merchant_id);	 
		if (empty($merchant)) {
			return '';
		}

		ob_start();
	$sitename = sanitize_text_field( get_bloginfo( 'name' ) );
	$isportal = COM_BOOKINGFORCONNECTOR_ISPORTAL;

	$indirizzo = isset($merchant->AddressData->Address)?$merchant->AddressData->Address:"";
	$cap = isset($merchant->AddressData->ZipCode)?$merchant->AddressData->ZipCode:""; 
	$comune = isset($merchant->AddressData->CityName)?$merchant->AddressData->CityName:"";
	$stato = isset($merchant->AddressData->StateName)?$merchant->AddressData->StateName:"";
	$merchantName = BFCHelper::getLanguage($merchant->Name, $language, null, array('ln2br'=>'ln2br', 'striptags'=>'striptags')); 

//	$merchantdetails_page = get_post( bfi_get_page_id( 'merchantdetails' ) );
//	$url_merchant_page = get_permalink( $merchantdetails_page->ID );
	$url_merchant_page = BFCHelper::GetPageUrl('merchantdetails');

	$routeMerchant = $url_merchant_page . $merchant->MerchantId.'-'.BFI()->seoUrl($merchant->Name);
	$canonicalUrl = $routeMerchant;

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
		case _x('contact', 'Page slug', 'bfi' ):
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
		case _x('contact', 'Page slug', 'bfi' ):


			$currentView = '';
			$orderType = "a";
			$task = "sendContact";
			$routeThanks = $routeMerchant .'/'._x('thanks', 'Page slug', 'bfi' );
			$routeThanksKo = $routeMerchant .'/'._x('errors', 'Page slug', 'bfi' );
			$paramRefInfo = array(
				"merchant"=>$merchant,
				"layout"=>$layout,
				"currentView"=>$currentView,
				"resource"=>null,
				"task"=>$task,
				"orderType"=>$orderType,
				"routeThanks"=>$routeThanks,
				"routeThanksKo"=>$routeThanksKo,
				);
			bfi_get_template("/shared/infocontact.php",$paramRefInfo);	
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
							bfi_get_template("widgets/smallmap.php");	
							bfi_get_template("widgets/proximitypoi.php",array("resource"=>$merchant));	

							bfi_get_template("widgets/reviews.php");	
							?>
							<div class="bfilastmerchants">
								<h3><?php _e('Recently seen', 'bfi') ?></h3>
							</div>
						</div>
					<?php 
						}
					?>						
					</div>
<?php

					
        //return ob_get_clean();
		$output =  ob_get_clean();
		if (COM_BOOKINGFORCONNECTOR_ISBOT) {
			BFI_Cache::setCachedContent($fileNameCached,$output);
		}
		return $output;
	}


	
	/**
	 * Offers page shortcode.
	 *
	 * @param mixed $atts
	 * @return string
	 */
	public static function bfi_shortcode_offers( $atts ) {

		$atts = shortcode_atts( array(
			'per_page' => COM_BOOKINGFORCONNECTOR_ITEMPERPAGE,
			/*'orderby'  => 'title',
			'order'    => 'desc',
			'category' => '',  // Slugs
			'rating' => '',  // Slugs
			'cityids' => '',  // Slugs
			'onlylist' => '0',  // Slugs
			'tag' => '',  // Slugs*/
		), $atts );
		
		$model = new BookingForConnectorModelOffers;
		$page = bfi_get_current_page();
		$listName = BFCHelper::$listNameAnalytics[6];
		
		$items = $model->getAllOffers(1/*(absint($page)-1)*$this->itemPerPage*/,50/*$this->itemPerPage*/,null);
		$paramRef = array(
			"offers"=>$items,
			"analyticsListName"=>$listName,
			/*"total"=>$total,
			"items"=>$items,
			"currParam"=>$currParam,
			"filter_order"=>$filter_order,
			"filter_order_Dir"=>$filter_order_Dir,
			"showfilter"=>1*/

			);

        /*//if ( ! $atts['category'] ) {
        //    return '';
        //}
		$page = bfi_get_current_page();
		$start = ($page - 1) * COM_BOOKINGFORCONNECTOR_ITEMPERPAGE;
		$onlylist =  !empty($atts['onlylist']) ? $atts['onlylist'] : '0';

		$merchantCategories =[];
		if(!empty($atts['category'])){
			$merchantCategories =explode(",",$atts['category']);
		}
		$rating = !empty($atts['rating'])?$atts['rating']:'';
		$cityids = [];
		if(!empty($atts['cityids'])){
			$cityids =explode(",",$atts['cityids']);
		}
		if ( is_admin() ) {
			return '';
		}

		$model = new BookingForConnectorModelOffers;
		//$model->populateState();
		$model->setItemPerPage(COM_BOOKINGFORCONNECTOR_ITEMPERPAGE);
//
		$filter_order = $model->getOrdering();
		$filter_order_Dir = $model->getDirection();

		$currParam = $model->getParam();
		$currParam['categoryId'] = $merchantCategories;
		$currParam['rating'] = $rating;
		$currParam['cityids'] = $cityids;
		$model->setParam($currParam);


		//$total = $model->getTotal();
		$items = $model->getItems();

		$merchants = is_array($items) ? $items : array();
		ob_start();
		$listNameAnalytics =4;
		$listName = BFCHelper::$listNameAnalytics[$listNameAnalytics];// "Resources Search List";
		add_action('bfi_head', 'bfi_google_analytics_EEc', 10, 1);
		do_action('bfi_head', $listName);

		$paramRef = array(
			"merchants"=>$merchants,
			"total"=>$total,
			"items"=>$items,
			"currParam"=>$currParam,
			"filter_order"=>$filter_order,
			"filter_order_Dir"=>$filter_order_Dir,
			"showfilter"=>1

			);
        $currSorting = $filter_order ."|".$filter_order_Dir ;
        $GLOBALS['bfSearchedMerchantsItems'] = $items;
        $GLOBALS['bfSearchedMerchantsItemsTotal'] = $total;
        $GLOBALS['bfSearchedMerchantsItemsCurrSorting'] = $currSorting;
        $GLOBALS['bfSearchedMerchants'] = 1;*/

		if(empty( $onlylist )){
?>		
					<div class="bfi-row  ">
						<div class="bfi-col-md-3 bfi-col-sm-3 bfi-col-xs-12  bfi-sidebar">
							<?php 
							dynamic_sidebar('bfisidebar');
							/*$setLat = COM_BOOKINGFORCONNECTOR_GOOGLE_POSX;
							$setLon = COM_BOOKINGFORCONNECTOR_GOOGLE_POSY;
							bfi_get_template("widgets/smallmap.php",array("setLat"=>$setLat,"setLon"=>$setLon));*/	

							//bfi_get_template("widgets/search-filter-merchants.php",$paramRef);	
							?>
						</div>
						<div class="bfi-col-md-9 bfi-col-xs-12 bfi-col-sm-9  bfi-body">
							<?php 
					
			} //if onlylist
		bfi_get_template("offerslist/offerslist.php",$paramRef);
			if(empty( $onlylist )){
							?>
						</div>
					</div>
					<?php 
			} //if onlylist
			//bfi_get_template("offerslist/offerslist.php",$paramRef);
		return ob_get_clean();
	}
	
	
	
	/**
	 * Resources page shortcode.
	 *
	 * @param mixed $atts
	 * @return string
	 */
	public static function bfi_shortcode_resources( $atts ) {

		$atts = shortcode_atts( array(
			'per_page' => COM_BOOKINGFORCONNECTOR_ITEMPERPAGE,
			'orderby'  => 'title',
			'order'    => 'desc',
			'categories' => '',  // Slugs
			'resourcegroupid' => 0,  // Slugs
		), $atts );

		if ( is_admin() ) {
			return '';
		}

		$page = bfi_get_current_page();
		$language = $GLOBALS['bfi_lang'];

		$fileNameCached = 'bfi_shortcode_resources' . '_' . $language . '_' . implode("_", array_values($atts)). '_' . $page. '_' . COM_BOOKINGFORCONNECTOR_ITEMPERPAGE ;
		
		if (COM_BOOKINGFORCONNECTOR_ISBOT) {
			$currContent = BFI_Cache::getCachedContent($fileNameCached);
			if (!empty($currContent)) {
			    return $currContent;
			}
		}

		$resourcesmodel = new BookingForConnectorModelResources();
		$resourcesmodel->populateState();
		$resourcesmodel->setItemPerPage(COM_BOOKINGFORCONNECTOR_ITEMPERPAGE);
//
		$filter_order = $resourcesmodel->getOrdering();
		$filter_order_Dir = $resourcesmodel->getDirection();

		$currParam = $resourcesmodel->getParam();
		$categories = !empty($atts['categories'])?$atts['categories']:'';
		$currParam['categories'] = $categories;
		$resourcegroupid = !empty($atts['resourcegroupid'])?$atts['resourcegroupid']:0;
		$currParam['parentProductId'] = $resourcegroupid;
		$resourcesmodel->setParam($currParam);

		$total = $resourcesmodel->getTotal();
		$items = $resourcesmodel->getItems();

		$merchants = is_array($items) ? $items : array();
		$resources = $resourcesmodel->getItems();
		$results = is_array($items) ? $items : array();

		ob_start();
		$paramRef = array(
			"merchants"=>$merchants,
			"resources"=>$resources,
			"results"=>$results,
			"total"=>$total,
			"items"=>$items,
			"currParam"=>$currParam,
			"filter_order"=>$filter_order,
			"filter_order_Dir"=>$filter_order_Dir,
			);
		bfi_get_template("resources.php",$paramRef);
		
		$output =  ob_get_clean();
		if (COM_BOOKINGFORCONNECTOR_ISBOT) {
			BFI_Cache::setCachedContent($fileNameCached,$output);
		}
		return $output;
	}

	/**
	 * GroupedResource page shortcode.
	 *
	 * @param mixed $atts
	 * @return string
	 */
	public static function bfi_shortcode_groupedresource( $atts ) {

		$atts = shortcode_atts( array(
			'id' => 0,  
			'layout' => '',  
		), $atts );

		$language = $GLOBALS['bfi_lang'];

		$fileNameCached = 'bfi_shortcode_groupedresource' . '_' . $language . '_' . implode("_", array_values($atts));
		
		if (COM_BOOKINGFORCONNECTOR_ISBOT) {
			$currContent = BFI_Cache::getCachedContent($fileNameCached);
			if (!empty($currContent)) {
			    return $currContent;
			}
		}
		wp_enqueue_script('jquerytemplate');

		$resource_id = !empty($atts['id'])?$atts['id']:0;
		$layout = !empty($atts['layout'])?$atts['layout']:'';
		if ( is_admin() || empty($resource_id) ) {
			return '';
		}
		$language = $GLOBALS['bfi_lang'];
		$model = new BookingForConnectorModelResourcegroup;
		$resource = $model->getResourcegroupFromService($resource_id,$language);	 
		if (empty($resource)) {
			return '';
		}
	$sitename = sanitize_text_field( get_bloginfo( 'name' ) );
	$merchant = $resource->Merchant;
	$currencyclass = bfi_get_currentCurrency();
	$merchants = array();
	$merchants[] = $resource->MerchantId;
	$resourceName = BFCHelper::getLanguage($resource->Name, $language, null, array('nobr'=>'nobr', 'striptags'=>'striptags')); 
	$merchantName = BFCHelper::getLanguage($merchant->Name, $language, null, array('nobr'=>'nobr', 'striptags'=>'striptags')); 
	$resourceDescription = BFCHelper::getLanguage($resource->Description, $language, null, array('ln2br'=>'ln2br', 'bbcode'=>'bbcode', 'striptags'=>'striptags'));
	
//	$accommodationdetails_page = get_post( bfi_get_page_id( 'resourcegroupdetails' ) );
//	$url_resource_page = get_permalink( $accommodationdetails_page->ID );
	$url_resource_page = BFCHelper::GetPageUrl('resourcegroupdetails');

	$routeResource = $url_resource_page.$resource->CondominiumId.'-'.BFI()->seoUrl($resourceName);
	$canonicalUrl = $routeResource;

	$indirizzo = isset($resource->Address)?$resource->Address:"";
	$cap = isset($resource->ZipCode)?$resource->ZipCode:""; 
	$comune = isset($resource->CityName)?$resource->CityName:"";
	$stato = isset($resource->StateName)?$resource->StateName:"";

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
	}else{
		add_filter( 'wp_title', function() use ($titleHead) {return	$titleHead;} , 10, 1 );
		add_action( 'wp_head', function() use ($keywordsHead) {return bfi_add_meta_keywords($keywordsHead); }, 10, 1);
		add_action( 'wp_head', function() use ($resourceDescriptionSeo) {return bfi_add_meta_description($resourceDescriptionSeo); } , 10, 1 );
		add_action( 'wp_head', function() use ($canonicalUrl) {return bfi_add_canonicalurl($canonicalUrl); }, 10, 1);
		add_action( 'wp_head', 'bfi_add_meta_robots', 10, 1);
	}
	$sessionkeysearch = 'search.params';

		if(isset($_REQUEST['newsearch'])){
			bfi_setSessionFromSubmittedData($sessionkeysearch);
		}
		if(isset($_REQUEST['state'])){
			$_SESSION['search.params']['state'] = $_REQUEST['state'];

		}
	$sendAnalytics =false;
	$criteoConfig = null;

		ob_start();

	switch ( $layout) {
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
						
			
			$paramRef = array(
				"merchant"=>$merchant,
				"resource"=>$resource,
				"resource_id"=>$resource_id,
				"layoutshortcode" =>$layout,
				);
$resource->IsCatalog = false;

if($resource->HasSelectionMap) { 
$resourceLat = null;
$resourceLon = null;

if (!empty($resource->XGooglePos) && !empty($resource->YGooglePos)) {
	$resourceLat = $resource->XGooglePos;
	$resourceLon = $resource->YGooglePos;
}
if(!empty($resource->XPos)){
	$resourceLat = $resource->XPos;
}
if(!empty($resource->YPos)){
	$resourceLon = $resource->YPos;
}
if(empty($resourceLat) && !empty($merchant->XPos)){
	$resourceLat = $merchant->XPos;
}
if(empty($resourceLon) && !empty($merchant->YPos)){
	$resourceLon = $merchant->YPos;
}

$showMap = (($resourceLat != null) && ($resourceLon !=null) );
if ($showmap && COM_BOOKINGFORCONNECTOR_USE_OPENSTREETMAP) {
	wp_enqueue_script('bfileaflet');
	wp_enqueue_script('bfileafletcontrolcustom');
}

?>
	<?php if ($showMap){?>
			<!-- calc -->
			<a name="calc"></a>
			<div id="divcalculator">
				<?php 
				$resourceId = 0;
				$resourcegroupId = $resource->CondominiumId;
				$refreshSearch = 0;
				if (!empty($fromSearch)) {
					$refreshSearch = 1;				    
				}
				bfi_get_template("shared/search_mapsells.php",array("resource"=>$resource, "mapConfiguration"=>$resource->MapConfiguration,"resourceLat"=>$resourceLat,"resourceLon"=>$resourceLon,"merchant"=>$merchant,"resourceId"=>$resourceId,"resourcegroupId"=>$resourcegroupId,"currencyclass"=>$currencyclass,"refreshSearch"=>$refreshSearch));	
				?>
			</div>
	<?php } ?>	

<?php
// prenotazione classica	
}
			$sendAnalytics = $resource->IsCatalog;
	}

		add_action('bfi_head', 'bfi_google_analytics_EEc', 10, 1);
		do_action('bfi_head', "Merchant Resources Search List");
		if($sendAnalytics && COM_BOOKINGFORCONNECTOR_GAENABLED == 1 && !empty(COM_BOOKINGFORCONNECTOR_GAACCOUNT) && COM_BOOKINGFORCONNECTOR_EECENABLED == 1) {
				$obj = new stdClass;
				$obj->id = "" . $resource->ResourceId . " - Resource";
				$obj->name = $resource->Name;
				$obj->category = $resource->MerchantCategoryName;
				$obj->brand = $resource->MerchantName;
				$obj->variant = 'CATALOG';
				echo '<script type="text/javascript"><!--
				';
				echo ('callAnalyticsEEc("addProduct", [' . json_encode($obj) . '], "item");');
				echo "//--></script>";
		}
	

	wp_enqueue_script('bf_appTimePeriod', BFI()->plugin_url() . '/assets/js/bf_appTimePeriod.js',array(),BFI_VERSION);
	wp_enqueue_script('bf_appTimeSlot', BFI()->plugin_url() . '/assets/js/bf_appTimeSlot.js',array(),BFI_VERSION);
  }


		$output =  ob_get_clean();
		if (COM_BOOKINGFORCONNECTOR_ISBOT) {
			BFI_Cache::setCachedContent($fileNameCached,$output);
		}
		return $output;
	}


	/**
	 * Event page shortcode.
	 *
	 * @param mixed $atts
	 * @return string
	 */
	public static function bfi_shortcode_event( $atts ) {

		$atts = shortcode_atts( array(
			'id' => 0,  
			'layout' => '',  
		), $atts );

		$resource_id = !empty($atts['id'])?$atts['id']:0;
		$layout = !empty($atts['layout'])?$atts['layout']:'';
		if ( is_admin() || empty($resource_id) ) {
			return '';
		}
		$language = $GLOBALS['bfi_lang'];
		
		$fileNameCached = 'bfi_shortcode_event' . '_' . $language . '_' . implode("_", array_values($atts)) ;
		
		if (COM_BOOKINGFORCONNECTOR_ISBOT) {
			$currContent = BFI_Cache::getCachedContent($fileNameCached);
			if (!empty($currContent)) {
			    return $currContent;
			}
		}


		$model = new BookingForConnectorModelEvent;
		$resource = $model->getItem($resource_id);	 
				
		if (empty($resource)) {
			return '';
		}
	$sitename = sanitize_text_field( get_bloginfo( 'name' ) );
	$currencyclass = bfi_get_currentCurrency();	
	/*---------------IMPOSTAZIONI SEO----------------------*/
		$resourceName = BFCHelper::getLanguage($resource->Name, $language, null, array('nobr'=>'nobr', 'striptags'=>'striptags')); 
		$resourceDescriptionSeo = BFCHelper::getLanguage($resource->Description, $language, null, array( 'nobr'=>'nobr', 'bbcode'=>'bbcode', 'striptags'=>'striptags')) ;
		if (!empty($resourceDescriptionSeo) && strlen($resourceDescriptionSeo) > 170) {
			$resourceDescriptionSeo = substr($resourceDescriptionSeo,0,170);
		}

//		$details_page = get_post( bfi_get_page_id( 'eventdetails' ) );
//		$url_resource_page = get_permalink( $details_page->ID );
		$url_resource_page = BFCHelper::GetPageUrl('eventdetails');

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
//	$details_page = get_post( bfi_get_page_id( 'eventdetails' ) );
//	$url_resource_page = get_permalink( $details_page->ID );
	$url_resource_page = BFCHelper::GetPageUrl('eventdetails');
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

//	/* microformat */
//
//	$payloadaddress["@type"] = "PostalAddress";
//	$payloadaddress["streetAddress"] = $indirizzo;
//	$payloadaddress["addressLocality"] = $comune;
//	$payloadaddress["postalCode"] = $cap;
//	$payloadaddress["addressRegion"] = $provincia;
//	$payloadaddress["addressCountry"] =  BFCHelper::bfi_get_country_code_by_name($stato);
//
//	$payloadlocation["@type"] = "Place";
//	$payloadlocation["address"] = $payloadaddress;
//	$payloadlocation["name"] = $comune . " - " . $provincia . " - " . $stato;
//	
//	// SEO
//	$payloadresource["@context"] = "http://schema.org";
//	$payloadresource["@type"] = "Event";
//	$payloadresource["location"] = $payloadlocation;
//	$payloadresource["name"] = $resourceName;
//	$payloadresource["description"] = $resourceDescriptionSeo;
//	$payloadresource["startDate"] = $startDate->format("Y-m-d");
//	$payloadresource["endDate"] = $endDate->format("Y-m-d");
//	$payloadresource["url"] = $resourceRoute; 
//	if (!empty($resource->ImageUrl)){
//		$payloadresource["image"] = "https:".BFCHelper::getImageUrlResized('events',$resource->ImageUrl, 'logobig');
//	}
//	if (!empty($resourceLat) && !empty($resourceLon)) {
//		$payloadgeo["@type"] = "GeoCoordinates";
//		$payloadgeo["latitude"] = $resourceLat;
//		$payloadgeo["longitude"] = $resourceLon;
//		$payloadresource["geo"] = $payloadgeo; 
//	}

	/* end microformat */


//		if ( defined('WPSEO_VERSION') ) {
//					add_filter( 'wpseo_title', function() use ($titleHead) {return	$titleHead;} , 10, 1 );
//					add_filter( 'wpseo_metakey', function() use ($keywordsHead) {return $keywordsHead; } , 10, 1  );
//					add_filter( 'wpseo_metadesc', function() use ($resourceDescriptionSeo) {return $resourceDescriptionSeo; } , 10, 1 );
//					add_filter( 'wpseo_robots', function() {return "index,follow"; } , 10, 1 );
//					add_filter( 'wpseo_canonical', function() use ($canonicalUrl) {
//						if(substr($canonicalUrl , -1)!= '/'){
//							$canonicalUrl .= '/';
//						}
//						return $canonicalUrl; 
//					} , 10, 1 );
//					/* microformat */
//					add_action( 'wpseo_head', function() use ($payloadresource) { bfi_add_json_ld( $payloadresource ); } , 30);
//		}else{
//			add_filter( 'wp_title', function() use ($titleHead) {return	$titleHead;} , 10, 1 );
//			add_action( 'wp_head', function() use ($keywordsHead) {return bfi_add_meta_keywords($keywordsHead); }, 10, 1);
//			add_action( 'wp_head', function() use ($resourceDescriptionSeo) {return bfi_add_meta_description($resourceDescriptionSeo); } , 10, 1 );
//			add_action( 'wp_head', 'bfi_add_meta_robots', 10, 1);
//			add_action( 'wp_head', function() use ($canonicalUrl) {return bfi_add_canonicalurl($canonicalUrl); }, 10, 1);
//					/* microformat */
//			add_action( 'wp_head', function() use ($payloadresource) { bfi_add_json_ld( $payloadresource ); } , 10, 1 );
//		}
	/*--------------- END IMPOSTAZIONI SEO----------------------*/
			$paramRef = array(
				"resource"=>$resource,
				"resource_id"=>$resource_id,
				);
				$listName = "";
				$sendAnalytics = true;

		ob_start();

	wp_enqueue_script('bf_appTimePeriod', BFI()->plugin_url() . '/assets/js/bf_appTimePeriod.js',array(),BFI_VERSION);
	wp_enqueue_script('bf_appTimeSlot', BFI()->plugin_url() . '/assets/js/bf_appTimeSlot.js',array(),BFI_VERSION);
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
				$currFrom  = DateTime::createFromFormat('YmdHis', $_REQUEST['checkin'], new DateTime($currFrom,new DateTimeZone('UTC')));
				$currTo  = DateTime::createFromFormat('YmdHis', $_REQUEST['checkout'], new DateTime($currTo,new DateTimeZone('UTC')));
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
	$currParamInSession = BFCHelper::getSearchEventParamsSession();
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
		
		$output =  ob_get_clean();
		if (COM_BOOKINGFORCONNECTOR_ISBOT) {
			BFI_Cache::setCachedContent($fileNameCached,$output);
		}
		return $output;

	}
	
	/**
	 * shortcode for rental result.
	 *
	 * @param mixed $atts
	 * @return string
	 */
	public static function bfi_shortcode_search_result_rental( $atts ) {
		if ( is_admin() ) {
			return '';
		}
		if (COM_BOOKINGFORCONNECTOR_ISBOT) {
			return '';
		}
		
	if(isset($_GET) && !empty($_GET["resultinsamepg"])) {
		ob_start();
		?><div class="bfi-resultview" ><?php 
		
		$currencyclass = bfi_get_currentCurrency();
		$page = bfi_get_current_page() ;
		$start = ($page - 1) * COM_BOOKINGFORCONNECTOR_ITEMPERPAGE;
		$listName = "";
		$listNameAnalytics = 0;
		$sessionkeysearch = 'search.params.rental';

		bfi_setSessionFromSubmittedData($sessionkeysearch);
		$searchmodel = new BookingForConnectorModelSearch;
				
		$pars = BFCHelper::getSearchParamsSession($sessionkeysearch);
		$filterinsession = null;

		$items =  array();
		$total = 0;
		$currSorting = "";
		$totalAvailable = 0;
		$paxages = array();
		$nrooms = 1;
		$searchterm = '';

		if (isset($pars['checkin']) && isset($pars['checkout'])){
			$now = new DateTime('UTC');
			$now->setTime(0,0,0);
			$checkin = isset($pars['checkin']) ? $pars['checkin'] : new DateTime('UTC');
			$checkout = isset($pars['checkout']) ? $pars['checkout'] : new DateTime('UTC');
			$paxages = !empty($pars['paxages'])? $pars['paxages'] :  array('18','18');
			$nrooms = !empty($pars['minrooms'])? $pars['minrooms'] :  1;
			$searchterm = !empty($pars['searchterm']) ? $pars['searchterm'] :'';

			$availabilitytype = isset($pars['availabilitytype']) ? $pars['availabilitytype'] : "1";
			
			$availabilitytype = explode(",",$availabilitytype);
			if (($checkin == $checkout && (!in_array("0",$availabilitytype) && !in_array("2",$availabilitytype)&& !in_array("3",$availabilitytype) ) ) || $checkin->diff($checkout)->format("%a") <0 || $checkin < $now ){
				$nodata = true;
			}else{
				if (empty($GLOBALS['bfSearched'])) {
					
					$filterinsession = BFCHelper::getFilterSearchParamsSession($sessionkeysearch);
					$items = $searchmodel->getItems(false, false, $start,COM_BOOKINGFORCONNECTOR_ITEMPERPAGE, $sessionkeysearch);
					
					$items = is_array($items) ? $items : array();
							
					$total=$searchmodel->getTotal($sessionkeysearch);
					$totalAvailable=$searchmodel->getTotalAvailable($sessionkeysearch);
					$currSorting=$searchmodel->getOrdering() . "|" . $searchmodel->getDirection();
					$GLOBALS['bfSearched'] = 1;
				}else{
					$items = $GLOBALS['bfSearchedItems'];
					$total = $GLOBALS['bfSearchedItemsTotal'];
					$totalAvailable = $GLOBALS['bfSearchedItemsTotalAvailable'];
					$currSorting = $GLOBALS['bfSearchedItemsCurrSorting'];
				}
			}

		}

		// calcolo persone
		$nad = 0;
		$nch = 0;
		$nse = 0;
		$countPaxes = 0;
		$maxchildrenAge = (int)BFCHelper::$defaultAdultsAge-1;

		$nchs = array(null,null,null,null,null,null);

		if (empty($paxages)){
			$nad = 2;
			$paxages = array(BFCHelper::$defaultAdultsAge, BFCHelper::$defaultAdultsAge);

		}else{
			if(is_array($paxages)){
				$countPaxes = array_count_values($paxages);
				$nchs = array_values(array_filter($paxages, function($age) {
					if ($age < (int)BFCHelper::$defaultAdultsAge)
						return true;
					return false;
				}));
			}
		}
		array_push($nchs, null,null,null,null,null,null);

		if($countPaxes>0){
			foreach ($countPaxes as $key => $count) {
				if ($key >= BFCHelper::$defaultAdultsAge) {
					if ($key >= BFCHelper::$defaultSenioresAge) {
						$nse += $count;
					} else {
						$nad += $count;
					}
				} else {
					$nch += $count;
				}
			}
		}
	?>
						<div class="bfi-row ">
							<div class="bfi-col-md-3 bfi-col-sm-3 bfi-col-xs-12  bfi-sidebar">

								<?php 
									if (COM_BOOKINGFORCONNECTOR_ISMOBILE) {
										$currParam = BFCHelper::getSearchParamsSession($sessionkeysearch);
								?>

									<div class="bfi-summary-search">
										<?php if(!empty($searchterm)) { ?>
										<div class="bfi-summary-search-searchterm">
											<i class="fa fa-search"></i> <?php echo $searchterm ?>
										</div>
										<?php } ?>
										
										<div class="bfi-summary-search-other">
											<i class="fa fa-calendar-alt"></i> <?php echo $checkin->format("d") . ' ' . date_i18n('M',$checkin->getTimestamp())?> 
											<?php 
											if ($availabilitytype != 3) {
												 echo  ' - ' . $checkout->format("d") . ' ' . date_i18n('M',$checkout->getTimestamp());
												 echo  ' <b>(' . $checkout->diff($checkin)->format('%d') . ' ' . __('nights', 'bfi') . ')</b> ';

											}
											?>
											<span class="bfi-summary-search-persons">
												<span id="bfi-room-info-calculator" class="bfi-comma bfi-hide"><i class="fa fa-caret-down"></i> <span><?php echo $nrooms ?></span> <?php _e('Resource', 'bfi'); ?></span>
												<i class="fa fa-user"></i> <span id="bfi-adult-info-calculator" class="bfi-comma"><span><?php echo $nad ?></span> <?php _e('Adults', 'bfi'); ?></span>
												<?php if($nse>0) { ?>
													<span id="bfi-senior-info-calculator" class="bfi-comma"><span><?php echo $nse ?></span> <?php _e('Seniores', 'bfi'); ?></span>
												<?php } ?>
												<?php if($nch>0) { ?>
													<span id="bfi-child-info-calculator" class="bfi-comma"><span><?php echo $nch ?></span> <?php _e('Children', 'bfi'); ?> (<?php echo implode(',', array_slice($nchs,0,$nch)) ?>)</span>
												<?php } ?>
											</span>
										</div>
									</div>
								<div class="bfisearchdialog">		
								<?php 
									dynamic_sidebar('bfisidebar'); 
								?>
								</div>
		<ul class="bfi-menu-search">
			<li><div class="bfiopenpopupsors"><i class="fas fa-exchange-alt fa-rotate-90"></i> <?php echo _e('Order by' , 'bfi') ?>
	<select class="bfi-orderby-content" name="currsorting" style="display: block; opacity: 0; position: absolute; left: 0; top: 0; height: 100%; padding: 0 10px; width: 100%; font-size: 16px">
		<option value="" rel="" style="display:none;"  ><?php echo _e('Lowest price first' , 'bfi'); ?></option>
		<option value="price|asc" rel="price|asc" <?php echo $currSorting=="price|asc" ? 'selected="selected"': '' ; ?>  ><?php echo _e('Lowest price first' , 'bfi'); ?></option>
		<option value="rating|asc" rel="rating|asc" <?php echo $currSorting=="rating|asc" ? 'selected="selected"': '' ; ?>  ><?php echo _e('Review score' , 'bfi'); ?></option>
		<option value="offer|asc" rel="offer|asc" <?php echo $currSorting=="offer|asc" ? 'selected="selected"': '' ; ?>  ><?php echo _e('Best offers' , 'bfi'); ?></option>
						<?php if($currParam != null && !empty($currParam['points'])) { 
							if (!empty($currParam['searchTermValue']) && strpos($currParam['searchTermValue'], "cityIds|") === 0) { ?>
							<option value="distance|asc" rel="distance|asc" <?php echo $currSorting=="distance|asc" ? 'selected="selected"': '' ; ?>  ><?php echo _e('Distance from center' , 'bfi'); ?></option>
							<?php
							} else if (!empty($currParam['searchTermValue']) && strpos($currParam['searchTermValue'], "poiIds|") === 0) { ?>
							<option value="distance|asc" rel="distance|asc" <?php echo $currSorting=="distance|asc" ? 'selected="selected"': '' ; ?>  ><?php echo _e('Distance from point of interest' , 'bfi'); ?></option>
							<?php 
							}
						}			
						?>
	</select>
				</div>
			</li>
			<li><a class="bfi-panel-toggle"><i class="fas fa-filter"></i> <?php echo _e('Filter' , 'bfi') ?></a></li>
			<li><a class="bfiopenpopupmap"><i class="fas fa-map-marker-alt"></i> <?php echo _e('Map' , 'bfi') ?></a></li>
		</ul>
		<span class="bficurrentfilter"></span>
	<script type="text/javascript">
	var dialogForm;
	var bfi_wuiP_width= 800;

	function bfishowsearch() {
		if(jQuery(window).width()<bfi_wuiP_width){
			bfi_wuiP_width = jQuery(window).width();
		}
		if (!!jQuery.uniform){
			jQuery.uniform.restore(jQuery("#bfi-calculatorForm select"));
		}
		dialogForm = jQuery( ".bfisearchdialog" ).dialog({
				closeText: "",
				title:"<?php _e('Change your details', 'bfi'); ?>",
				autoOpen: false,
				width:bfi_wuiP_width,
				position: ['center',0], 
				modal: true,
				dialogClass: 'bfi-dialog bfi-dialog-search',
	//			clickOutside: true,

		});
		dialogForm.dialog( "open" );
	}

	jQuery(document).ready(function() {
		jQuery(".bfi-summary-search").on('click tap', function (e) {
			if (typeof dialogForm !=='undefined' && dialogForm.hasClass("ui-dialog-content"))
			{
				dialogForm.dialog( "close" ).dialog('destroy');
			}

			bfishowsearch();
		});

		var currDetailsFiltered = [];
		var currFilterActive = jQuery(".bfi-orderby-content option:selected").first();
		if(currFilterActive.length && jQuery(".bfi-orderby-content").val() != "" ){
			currDetailsFiltered.push("<span>" + currFilterActive.html() + ' <i class="fa fa-times-circle bfi-removesort" aria-hidden="true" ></i></span>' );
		}

		jQuery('.bfi-option-title').each(function(){
			var currFilterActive = jQuery(this).parent("div").first().find(".bfi-filter-active");
			if(currFilterActive.length){
				var currfilter = [];
				currFilterActive.each(function(){
					var rel = jQuery(this).attr("rel");
					var rel1 = jQuery(this).attr("rel1");
					currfilter.push("<span>" +jQuery(this).find(".bfi-filter-label").first().html() + ' <i class="fa fa-times-circle bfi-removefilter" aria-hidden="true" rel="'+rel+'" rel1="'+rel1+'"></i></span>' );
				});
	//			currDetailsFiltered.push(jQuery(this).text() + ": " + currfilter.join(", "));
				currDetailsFiltered.push(currfilter.join(" "));
			}
		});
		if (currDetailsFiltered.length){
			jQuery('.bficurrentfilter').append( currDetailsFiltered.join(" "));
		}else{
			jQuery('.bficurrentfilter').hide();
		}
		jQuery('.bfi-orderby-content').change(function() {
			var rel = jQuery(this).val();
			var vals = rel.split("|"); 
			jQuery('#bookingforsearchFilterForm .filterOrder').val(vals[0]);
			jQuery('#bookingforsearchFilterForm .filterOrderDirection').val(vals[1]);

			if(jQuery('#searchformfilter').length){
				jQuery('#searchformfilter').submit();
			}else{
				jQuery('#bookingforsearchFilterForm').submit();
			}
		});
		jQuery('.bfi-removesort').click(function() {
			jQuery('#bookingforsearchFilterForm .filterOrder').val("");
			jQuery('#bookingforsearchFilterForm .filterOrderDirection').val("");

			if(jQuery('#searchformfilter').length){
				jQuery('#searchformfilter').submit();
			}else{
				jQuery('#bookingforsearchFilterForm').submit();
			}
		});

	});

	</script>
		<div class="bfifilterlisttab">
			<div class="bfi-slide-panel">
				<div class="bfi-slide-panel-title"><span class="bfi-slide-panel-title-span"><?php _e('Filters', 'bfi'); ?></span><span class="bfi-panel-close bfi-panel-toggle"></span></div>
				<?php bfi_get_template("widgets/search-filter.php"); ?>
				<div class="bfi-slide-panel-bottom"><span class="bfi-btn bfi-panel-toggle"><?php echo sprintf( __('Show %s results', 'bfi'),$totalAvailable ); ?></span></div>
			</div>
		</div>
										
									<?php 
																			
									}else{
											dynamic_sidebar('bfisidebarrental'); 
											bfi_get_template("widgets/search-filter-rental.php");	
									}
								?>
								<!-- <div class="bfilastmerchants">
									<h3><?php _e('Recently seen', 'bfi') ?></h3>
								</div> -->
							</div>
							<div class="bfi-col-md-9 bfi-col-xs-12 bfi-col-sm-9  bfi-body">
			<?php
				/**
				 * bookingfor_before_main_content hook.
				 *
				 * @hooked bookingfor_output_content_wrapper - 10 (outputs opening divs for the content)
				 * @hooked bookingfor_breadcrumb - 20
				 */
				do_action( 'bookingfor_before_main_content' );
			?>
			<?php if ( apply_filters( 'bookingfor_show_page_title', true ) ) { ?>
			<?php } ?>
		<?php

		$pages = ceil($total / COM_BOOKINGFORCONNECTOR_ITEMPERPAGE);

		$merchant_ids = '';


		$currParam = BFCHelper::getSearchParamsSession($sessionkeysearch);
		$merchantResults = $currParam['merchantResults'];
		$resourcegroupsResults = $currParam['resourcegroupsResults'];
		$totPerson = (isset($currParam)  && isset($currParam['paxes']))? $currParam['paxes']:0 ;
		/*-- criteo --*/
		$criteoConfig = null;
		if(COM_BOOKINGFORCONNECTOR_CRITEOENABLED){
				$merchantsCriteo = array();
				if(!empty($items)) {
					$merchantsCriteo = array_unique(array_map(function($a) { return $a->MerchantId; }, $items));
				}
				$criteoConfig = BFCHelper::getCriteoConfiguration(1, $merchantsCriteo);
				if(isset($criteoConfig) && isset($criteoConfig->enabled) && $criteoConfig->enabled && count($criteoConfig->merchants) > 0) {
					echo '<script type="text/javascript" src="//static.criteo.net/js/ld/ld.js" async="true"></script>';
					echo '<script type="text/javascript"><!--
					';
					echo ('window.criteo_q = window.criteo_q || []; 
					var deviceTypeCriteo = /iPad/.test(navigator.userAgent) ? "t" : /Mobile|iP(hone|od)|Android|BlackBerry|IEMobile|Silk/.test(navigator.userAgent) ? "m" : "d";
					window.criteo_q.push( 
						{ event: "setAccount", account: '. $criteoConfig->campaignid .'}, 
						{ event: "setSiteType", type: deviceTypeCriteo }, 
						{ event: "setEmail", email: "" }, 
						{ event: "viewSearch", checkin_date: "' . $pars["checkin"]->format('Y-m-d') . '", checkout_date: "' . $pars["checkout"]->format('Y-m-d') . '"},
						{ event: "viewList", item: ' . json_encode($criteoConfig->merchants) .' }
					);');
					echo "//--></script>";
				}	
			
		}

		/*-- criteo --*/

		$totalItems = array();
		$sendData = true;
			
		if(!empty($items)) {
			if($merchantResults) {
//				$resIndex = 0;
				$listNameAnalytics = 9; // old 1;
				$listName = BFCHelper::$listNameAnalytics[$listNameAnalytics];//"Merchants Group List";
				$itmCounter = 1;
				foreach($items as $itemkey => $itemValue) {
					$listResources = array();
					foreach ($itemValue->Results as $keyRes=>$singleResource) {
						$id = $singleResource->ResourceId;

						if (isset($listResources[$id])) {
							$listResources[$id][] = $singleResource;
						} else {
							$listResources[$id] = array($singleResource);
						}
					}

					array_multisort(array_map('count', $listResources), SORT_DESC, $listResources);
					foreach ($listResources as $resourceId=>$singleResource) // foreach $listMerchantsCart
					{
						$currResource = $singleResource[0];
						$objRes = new stdClass();
						$objRes->MerchantId = $currResource->MerchantId;
						$objRes->MrcName = $itemValue->MerchantName;
						$objRes->MrcCategoryName = $itemValue->DefaultLangCategoryName;
						$objRes->Position = $itmCounter;// $resIndex;
						$objRes->Id = $currResource->ResourceId . " - Resource";
						$objRes->Name = $itemValue->Name;
						$itmCounter++;
											
						$totalItems[] = $objRes;
					}
				}
			} else if ($resourcegroupsResults) {
				$resIndex = 0;
				$listNameAnalytics = 2;
				$listName = BFCHelper::$listNameAnalytics[$listNameAnalytics];// "Resources Group List";
				
				$itmCounter = 1;
				foreach($items as $itemkey => $itemValue) {
					$listResources = array();
					foreach ($itemValue->Results as $keyRes=>$singleResource) {
						$id = $singleResource->ResourceId;

						if (isset($listResources[$id])) {
							$listResources[$id][] = $singleResource;
						} else {
							$listResources[$id] = array($singleResource);
						}
					}

					array_multisort(array_map('count', $listResources), SORT_DESC, $listResources);
					foreach ($listResources as $resourceId=>$singleResource) // foreach $listMerchantsCart
					{
						$currResource = $singleResource[0];
						$objRes = new stdClass();
						$objRes->MerchantId = $currResource->MerchantId;
						$objRes->MrcName = $itemValue->MerchantName;
						$objRes->MrcCategoryName = $itemValue->DefaultLangCategoryName;
						$objRes->Position = $itmCounter;// $resIndex;
						$objRes->Id = $currResource->ResourceId . " - Resource";
						$objRes->Name = $itemValue->Name;
						$itmCounter++;
											
						$totalItems[] = $objRes;
					}
				}
			} else {
				$listNameAnalytics = 3;
				$listName = BFCHelper::$listNameAnalytics[$listNameAnalytics];// "Resources Search List";
				foreach($items as $mrckey => $mrcValue) {
					$obj = new stdClass();
					$obj->Id = $mrcValue->ItemId . " - Resource";
					$obj->MerchantId = $mrcValue->MerchantId;
					$obj->MrcCategoryName = $mrcValue->DefaultLangCategoryName;
					$obj->Name = $mrcValue->Name;
					$obj->MrcName = $mrcValue->MerchantName;
					$obj->Position = $mrckey;
					$totalItems[] = $obj;
				}
			}
		}

		$analyticsEnabled = COM_BOOKINGFORCONNECTOR_GAENABLED == 1 && !empty(COM_BOOKINGFORCONNECTOR_GAACCOUNT) && COM_BOOKINGFORCONNECTOR_EECENABLED == 1;
//		if(count($totalItems) > 0 && $analyticsEnabled) {
		add_action('bfi_head', 'bfi_google_analytics_EEc', 10, 1);
		do_action('bfi_head', $listName);
		if(count($totalItems) > 0 && $analyticsEnabled) {

			$allobjects = array();
			$initobjects = array();
			foreach ($totalItems as $key => $value) {
				$obj = new stdClass;
				$obj->id = "" . $value->Id;
				if(isset($value->GroupId) && !empty($value->GroupId)) {
					$obj->groupid = $value->GroupId;
				}
				$obj->name = $value->Name;
				$obj->category = $value->MrcCategoryName;
				$obj->brand = $value->MrcName;
				$obj->position = $value->Position;
				if(!isset($value->ExcludeInitial) || !$value->ExcludeInitial) {
					$initobjects[] = $obj;
				} else {
					///$obj->merchantid = $value->MerchantId;
					//$allobjects[] = $obj;
				}
			}
//			$document->addScriptDeclaration('var currentResources = ' .json_encode($allobjects) . ';
//			var initResources = ' .json_encode($initobjects) . ';
//			' . ($sendData ? 'callAnalyticsEEc("addImpression", initResources, "list");' : ''));
			echo '<script type="text/javascript"><!--
			';
			echo ('var currentResources = ' .json_encode($allobjects) . ';
			var initResources = ' .json_encode($initobjects) . ';
			' . ($sendData ? 'callAnalyticsEEc("addImpression", initResources, "list");' : ''));
			echo "//--></script>";

		}
		
		//event tracking	
		?>
		<?php
		$page = bfi_get_current_page() ;
		bfi_get_template("search/rental.php",array("totalAvailable"=>$totalAvailable,"total"=>$total,"items"=>$items,"pages"=>$pages,"page"=>$page,"currencyclass"=>$currencyclass,"listNameAnalytics"=>$listNameAnalytics,"totPerson"=>$totPerson,"currSorting"=>$currSorting));	
		do_action( 'bookingfor_after_main_content' );
		?>
		</div>
		<?php 
		
		return ob_get_clean();
	}else{
		return '<div class="bfi-resultview" ></div>';
	}

	}

	/**
	 * shortcode for result.
	 *
	 * @param mixed $atts
	 * @return string
	 */
	public static function bfi_shortcode_search_result( $atts ) {
		if ( is_admin() ) {
			return '';
		}
		if (COM_BOOKINGFORCONNECTOR_ISBOT) {
			return '';
		}
		
        if(isset($_GET) && !empty($_GET["resultinsamepg"])) {
		ob_start();
		//switch layout
		$layoutresult= ( ! empty( $_REQUEST['resview'] ) ) ? ($_REQUEST['resview']) : ''; 

		?><div class="bfi-resultview" ><?php 
		
		$currencyclass = bfi_get_currentCurrency();
		$page = bfi_get_current_page() ;
		$start = ($page - 1) * COM_BOOKINGFORCONNECTOR_ITEMPERPAGE;
		$listName = "";
		$listNameAnalytics = 0;
		$sessionkeysearch = 'search.params';
		switch ($layoutresult) {
			case 'rental':
				$sessionkeysearch = 'search.params.rental';
				break;
			case 'mapsells':
				$sessionkeysearch = 'search.params.mapsells';
				break;
			case 'slot':
				$sessionkeysearch = 'search.params.slot';
				break;
			case 'experience':
				$sessionkeysearch = 'search.params.experience';
				break;
			default:      
		}

		bfi_setSessionFromSubmittedData($sessionkeysearch);
		$searchmodel = new BookingForConnectorModelSearch;
				
		$pars = BFCHelper::getSearchParamsSession($sessionkeysearch);
		$filterinsession = null;

		$items =  array();
		$total = 0;
		$currSorting = "";
		$totalAvailable = 0;
		$paxages = array();
		$nrooms = 1;
		$searchterm = '';

		if (isset($pars['checkin']) && isset($pars['checkout'])){
			$now = new DateTime('UTC');
			$now->setTime(0,0,0);
			$checkin = isset($pars['checkin']) ? $pars['checkin'] : new DateTime('UTC');
			$checkout = isset($pars['checkout']) ? $pars['checkout'] : new DateTime('UTC');
			$paxages = !empty($pars['paxages'])? $pars['paxages'] :  array('18','18');
			$nrooms = !empty($pars['minrooms'])? $pars['minrooms'] :  1;
			$searchterm = !empty($pars['searchterm']) ? $pars['searchterm'] :'';

			$availabilitytype = isset($pars['availabilitytype']) ? $pars['availabilitytype'] : "1";
			
			$availabilitytype = explode(",",$availabilitytype);
			if (($checkin == $checkout && (!in_array("0",$availabilitytype) && !in_array("2",$availabilitytype)&& !in_array("3",$availabilitytype) ) ) || $checkin->diff($checkout)->format("%a") <0 || $checkin < $now ){
				$nodata = true;
			}else{
				if (empty($GLOBALS['bfSearched'])) {
					
					$filterinsession = BFCHelper::getFilterSearchParamsSession($sessionkeysearch);
					$items = $searchmodel->getItems(false, false, $start,COM_BOOKINGFORCONNECTOR_ITEMPERPAGE);
					
					$items = is_array($items) ? $items : array();
							
					$total=$searchmodel->getTotal();
					$totalAvailable=$searchmodel->getTotalAvailable();
					$currSorting=$searchmodel->getOrdering() . "|" . $searchmodel->getDirection();
					$GLOBALS['bfSearched'] = 1;
				}else{
					$items = $GLOBALS['bfSearchedItems'];
					$total = $GLOBALS['bfSearchedItemsTotal'];
					$totalAvailable = $GLOBALS['bfSearchedItemsTotalAvailable'];
					$currSorting = $GLOBALS['bfSearchedItemsCurrSorting'];
				}
			}

		}

		// calcolo persone
		$nad = 0;
		$nch = 0;
		$nse = 0;
		$countPaxes = 0;
		$maxchildrenAge = (int)BFCHelper::$defaultAdultsAge-1;

		$nchs = array(null,null,null,null,null,null);

		if (empty($paxages)){
			$nad = 2;
			$paxages = array(BFCHelper::$defaultAdultsAge, BFCHelper::$defaultAdultsAge);

		}else{
			if(is_array($paxages)){
				$countPaxes = array_count_values($paxages);
				$nchs = array_values(array_filter($paxages, function($age) {
					if ($age < (int)BFCHelper::$defaultAdultsAge)
						return true;
					return false;
				}));
			}
		}
		array_push($nchs, null,null,null,null,null,null);

		if($countPaxes>0){
			foreach ($countPaxes as $key => $count) {
				if ($key >= BFCHelper::$defaultAdultsAge) {
					if ($key >= BFCHelper::$defaultSenioresAge) {
						$nse += $count;
					} else {
						$nad += $count;
					}
				} else {
					$nch += $count;
				}
			}
		}
	?>
						<div class="bfi-row ">
							<div class="bfi-col-md-3 bfi-col-sm-3 bfi-col-xs-12  bfi-sidebar">

								<?php 
									if (COM_BOOKINGFORCONNECTOR_ISMOBILE) {
										$currParam = BFCHelper::getSearchParamsSession($sessionkeysearch);
								?>

									<div class="bfi-summary-search">
										<?php if(!empty($searchterm)) { ?>
										<div class="bfi-summary-search-searchterm">
											<i class="fa fa-search"></i> <?php echo $searchterm ?>
										</div>
										<?php } ?>
										
										<div class="bfi-summary-search-other">
											<i class="fa fa-calendar-alt"></i> <?php echo $checkin->format("d") . ' ' . date_i18n('M',$checkin->getTimestamp())?> 
											<?php 
											if ($availabilitytype != 3) {
												 echo  ' - ' . $checkout->format("d") . ' ' . date_i18n('M',$checkout->getTimestamp());
												 echo  ' <b>(' . $checkout->diff($checkin)->format('%d') . ' ' . __('nights', 'bfi') . ')</b> ';

											}
											?>
											<span class="bfi-summary-search-persons">
												<span id="bfi-room-info-calculator" class="bfi-comma bfi-hide"><i class="fa fa-caret-down"></i> <span><?php echo $nrooms ?></span> <?php _e('Resource', 'bfi'); ?></span>
												<i class="fa fa-user"></i> <span id="bfi-adult-info-calculator" class="bfi-comma"><span><?php echo $nad ?></span> <?php _e('Adults', 'bfi'); ?></span>
												<?php if($nse>0) { ?>
													<span id="bfi-senior-info-calculator" class="bfi-comma"><span><?php echo $nse ?></span> <?php _e('Seniores', 'bfi'); ?></span>
												<?php } ?>
												<?php if($nch>0) { ?>
													<span id="bfi-child-info-calculator" class="bfi-comma"><span><?php echo $nch ?></span> <?php _e('Children', 'bfi'); ?> (<?php echo implode(',', array_slice($nchs,0,$nch)) ?>)</span>
												<?php } ?>
											</span>
										</div>
									</div>
								<div class="bfisearchdialog">		
								<?php 
									dynamic_sidebar('bfisidebar'); 
								?>
								</div>
		<ul class="bfi-menu-search">
			<li><div class="bfiopenpopupsors"><i class="fas fa-exchange-alt fa-rotate-90"></i> <?php echo _e('Order by' , 'bfi') ?>
	<select class="bfi-orderby-content" name="currsorting" style="display: block; opacity: 0; position: absolute; left: 0; top: 0; height: 100%; padding: 0 10px; width: 100%; font-size: 16px">
		<option value="" rel="" style="display:none;"  ><?php echo _e('Lowest price first' , 'bfi'); ?></option>
		<option value="price|asc" rel="price|asc" <?php echo $currSorting=="price|asc" ? 'selected="selected"': '' ; ?>  ><?php echo _e('Lowest price first' , 'bfi'); ?></option>
		<option value="rating|asc" rel="rating|asc" <?php echo $currSorting=="rating|asc" ? 'selected="selected"': '' ; ?>  ><?php echo _e('Review score' , 'bfi'); ?></option>
		<option value="offer|asc" rel="offer|asc" <?php echo $currSorting=="offer|asc" ? 'selected="selected"': '' ; ?>  ><?php echo _e('Best offers' , 'bfi'); ?></option>
						<?php if($currParam != null && !empty($currParam['points'])) { 
							if (!empty($currParam['searchTermValue']) && strpos($currParam['searchTermValue'], "cityIds|") === 0) { ?>
							<option value="distance|asc" rel="distance|asc" <?php echo $currSorting=="distance|asc" ? 'selected="selected"': '' ; ?>  ><?php echo _e('Distance from center' , 'bfi'); ?></option>
							<?php
							} else if (!empty($currParam['searchTermValue']) && strpos($currParam['searchTermValue'], "poiIds|") === 0) { ?>
							<option value="distance|asc" rel="distance|asc" <?php echo $currSorting=="distance|asc" ? 'selected="selected"': '' ; ?>  ><?php echo _e('Distance from point of interest' , 'bfi'); ?></option>
							<?php 
							}
						}			
						?>
	</select>
				</div>
			</li>
			<li><a class="bfi-panel-toggle"><i class="fas fa-filter"></i> <?php echo _e('Filter' , 'bfi') ?></a></li>
			<li><a class="bfiopenpopupmap"><i class="fas fa-map-marker-alt"></i> <?php echo _e('Map' , 'bfi') ?></a></li>
		</ul>
		<span class="bficurrentfilter"></span>
	<script type="text/javascript">
	var dialogForm;
	var bfi_wuiP_width= 800;

	function bfishowsearch() {
		if(jQuery(window).width()<bfi_wuiP_width){
			bfi_wuiP_width = jQuery(window).width();
		}
		if (!!jQuery.uniform){
			jQuery.uniform.restore(jQuery("#bfi-calculatorForm select"));
		}
		dialogForm = jQuery( ".bfisearchdialog" ).dialog({
				closeText: "",
				title:"<?php _e('Change your details', 'bfi'); ?>",
				autoOpen: false,
				width:bfi_wuiP_width,
				modal: true,
				dialogClass: 'bfi-dialog bfi-dialog-search',
	//			clickOutside: true,

		});
		dialogForm.dialog( "open" );
	}

	jQuery(document).ready(function() {
		jQuery(".bfi-summary-search").on('click tap', function (e) {
			if (typeof dialogForm !=='undefined' && dialogForm.hasClass("ui-dialog-content"))
			{
				dialogForm.dialog( "close" ).dialog('destroy');
			}

			bfishowsearch();
		});

		var currDetailsFiltered = [];
		var currFilterActive = jQuery(".bfi-orderby-content option:selected").first();
		if(currFilterActive.length && jQuery(".bfi-orderby-content").val() != "" ){
			currDetailsFiltered.push("<span>" + currFilterActive.html() + ' <i class="fa fa-times-circle bfi-removesort" aria-hidden="true" ></i></span>' );
		}

		jQuery('.bfi-option-title').each(function(){
			var currFilterActive = jQuery(this).parent("div").first().find(".bfi-filter-active");
			if(currFilterActive.length){
				var currfilter = [];
				currFilterActive.each(function(){
					var rel = jQuery(this).attr("rel");
					var rel1 = jQuery(this).attr("rel1");
					currfilter.push("<span>" +jQuery(this).find(".bfi-filter-label").first().html() + ' <i class="fa fa-times-circle bfi-removefilter" aria-hidden="true" rel="'+rel+'" rel1="'+rel1+'"></i></span>' );
				});
	//			currDetailsFiltered.push(jQuery(this).text() + ": " + currfilter.join(", "));
				currDetailsFiltered.push(currfilter.join(" "));
			}
		});
		if (currDetailsFiltered.length){
			jQuery('.bficurrentfilter').append( currDetailsFiltered.join(" "));
		}else{
			jQuery('.bficurrentfilter').hide();
		}
		jQuery('.bfi-orderby-content').change(function() {
			var rel = jQuery(this).val();
			var vals = rel.split("|"); 
			jQuery('#bookingforsearchFilterForm .filterOrder').val(vals[0]);
			jQuery('#bookingforsearchFilterForm .filterOrderDirection').val(vals[1]);

			if(jQuery('#searchformfilter').length){
				jQuery('#searchformfilter').submit();
			}else{
				jQuery('#bookingforsearchFilterForm').submit();
			}
		});
		jQuery('.bfi-removesort').click(function() {
			jQuery('#bookingforsearchFilterForm .filterOrder').val("");
			jQuery('#bookingforsearchFilterForm .filterOrderDirection').val("");

			if(jQuery('#searchformfilter').length){
				jQuery('#searchformfilter').submit();
			}else{
				jQuery('#bookingforsearchFilterForm').submit();
			}
		});

	});

	</script>
		<div class="bfifilterlisttab">
			<div class="bfi-slide-panel">
				<div class="bfi-slide-panel-title"><span class="bfi-slide-panel-title-span"><?php _e('Filters', 'bfi'); ?></span><span class="bfi-panel-close bfi-panel-toggle"></span></div>
				<?php bfi_get_template("widgets/search-filter.php"); ?>
				<div class="bfi-slide-panel-bottom"><span class="bfi-btn bfi-panel-toggle"><?php echo sprintf( __('Show %s results', 'bfi'),$totalAvailable ); ?></span></div>
			</div>
		</div>
										
									<?php 
																			
									}else{
									
									switch ($layoutresult) {
										case 'rental':
											dynamic_sidebar('bfisidebarrental'); 
											bfi_get_template("widgets/search-filter-rental.php");	
											break;
										case 'slot':
											dynamic_sidebar('bfisidebarslot'); 
											bfi_get_template("widgets/search-filter.php");	
											break;
										case 'experience':
											dynamic_sidebar('bfisidebarexperience'); 
											bfi_get_template("widgets/search-filter-experience.php");	
											break;
										case 'mapsells':
											dynamic_sidebar('bfisidebarmapsells'); 
											bfi_get_template("widgets/search-filter-rental.php");	
											break;
										default:      
											dynamic_sidebar('bfisidebar'); 
											bfi_get_template("widgets/smallmap.php");	
											bfi_get_template("widgets/search-filter.php");	
									}

//										dynamic_sidebar('bfisidebar'); 
//										bfi_get_template("widgets/smallmap.php");	
//										bfi_get_template("widgets/search-filter.php");	
									}
								?>
								<!-- <div class="bfilastmerchants">
									<h3><?php _e('Recently seen', 'bfi') ?></h3>
								</div> -->
							</div>
							<div class="bfi-col-md-9 bfi-col-xs-12 bfi-col-sm-9  bfi-body">
			<?php
				/**
				 * bookingfor_before_main_content hook.
				 *
				 * @hooked bookingfor_output_content_wrapper - 10 (outputs opening divs for the content)
				 * @hooked bookingfor_breadcrumb - 20
				 */
				do_action( 'bookingfor_before_main_content' );
			?>
			<?php if ( apply_filters( 'bookingfor_show_page_title', true ) ) { ?>
			<?php } ?>
		<?php

		$pages = ceil($total / COM_BOOKINGFORCONNECTOR_ITEMPERPAGE);

		$merchant_ids = '';


		$currParam = BFCHelper::getSearchParamsSession($sessionkeysearch);
		$merchantResults = $currParam['merchantResults'];
		$resourcegroupsResults = $currParam['resourcegroupsResults'];
		$totPerson = (isset($currParam)  && isset($currParam['paxes']))? $currParam['paxes']:0 ;
		/*-- criteo --*/
		$criteoConfig = null;
		if(COM_BOOKINGFORCONNECTOR_CRITEOENABLED){
				$merchantsCriteo = array();
				if(!empty($items)) {
					$merchantsCriteo = array_unique(array_map(function($a) { return $a->MerchantId; }, $items));
				}
				$criteoConfig = BFCHelper::getCriteoConfiguration(1, $merchantsCriteo);
				if(isset($criteoConfig) && isset($criteoConfig->enabled) && $criteoConfig->enabled && count($criteoConfig->merchants) > 0) {
					echo '<script type="text/javascript" src="//static.criteo.net/js/ld/ld.js" async="true"></script>';
					echo '<script type="text/javascript"><!--
					';
					echo ('window.criteo_q = window.criteo_q || []; 
					var deviceTypeCriteo = /iPad/.test(navigator.userAgent) ? "t" : /Mobile|iP(hone|od)|Android|BlackBerry|IEMobile|Silk/.test(navigator.userAgent) ? "m" : "d";
					window.criteo_q.push( 
						{ event: "setAccount", account: '. $criteoConfig->campaignid .'}, 
						{ event: "setSiteType", type: deviceTypeCriteo }, 
						{ event: "setEmail", email: "" }, 
						{ event: "viewSearch", checkin_date: "' . $pars["checkin"]->format('Y-m-d') . '", checkout_date: "' . $pars["checkout"]->format('Y-m-d') . '"},
						{ event: "viewList", item: ' . json_encode($criteoConfig->merchants) .' }
					);');
					echo "//--></script>";
				}	
			
		}

		/*-- criteo --*/

		$totalItems = array();
		$sendData = true;
			
		if(!empty($items)) {
			if($merchantResults) {
//				$resIndex = 0;
				$listNameAnalytics = 9; // old 1;
				$listName = BFCHelper::$listNameAnalytics[$listNameAnalytics];//"Merchants Group List";
				$itmCounter = 1;
				foreach($items as $itemkey => $itemValue) {
					$listResources = array();
					foreach ($itemValue->Results as $keyRes=>$singleResource) {
						$id = $singleResource->ResourceId;

						if (isset($listResources[$id])) {
							$listResources[$id][] = $singleResource;
						} else {
							$listResources[$id] = array($singleResource);
						}
					}

					array_multisort(array_map('count', $listResources), SORT_DESC, $listResources);
					foreach ($listResources as $resourceId=>$singleResource) // foreach $listMerchantsCart
					{
						$currResource = $singleResource[0];
						$objRes = new stdClass();
						$objRes->MerchantId = $currResource->MerchantId;
						$objRes->MrcName = $itemValue->MerchantName;
						$objRes->MrcCategoryName = $itemValue->DefaultLangCategoryName;
						$objRes->Position = $itmCounter;// $resIndex;
						$objRes->Id = $currResource->ResourceId . " - Resource";
						$objRes->Name = $itemValue->Name;
						$itmCounter++;
											
						$totalItems[] = $objRes;
					}
				}
			} else if ($resourcegroupsResults) {
				$resIndex = 0;
				$listNameAnalytics = 2;
				$listName = BFCHelper::$listNameAnalytics[$listNameAnalytics];// "Resources Group List";
				
				$itmCounter = 1;
				foreach($items as $itemkey => $itemValue) {
					$listResources = array();
					foreach ($itemValue->Results as $keyRes=>$singleResource) {
						$id = $singleResource->ResourceId;

						if (isset($listResources[$id])) {
							$listResources[$id][] = $singleResource;
						} else {
							$listResources[$id] = array($singleResource);
						}
					}

					array_multisort(array_map('count', $listResources), SORT_DESC, $listResources);
					foreach ($listResources as $resourceId=>$singleResource) // foreach $listMerchantsCart
					{
						$currResource = $singleResource[0];
						$objRes = new stdClass();
						$objRes->MerchantId = $currResource->MerchantId;
						$objRes->MrcName = $itemValue->MerchantName;
						$objRes->MrcCategoryName = $itemValue->DefaultLangCategoryName;
						$objRes->Position = $itmCounter;// $resIndex;
						$objRes->Id = $currResource->ResourceId . " - Resource";
						$objRes->Name = $itemValue->Name;
						$itmCounter++;
											
						$totalItems[] = $objRes;
					}
				}
			} else {
				$listNameAnalytics = 3;
				$listName = BFCHelper::$listNameAnalytics[$listNameAnalytics];// "Resources Search List";
				foreach($items as $mrckey => $mrcValue) {
					$obj = new stdClass();
					$obj->Id = $mrcValue->ItemId . " - Resource";
					$obj->MerchantId = $mrcValue->MerchantId;
					$obj->MrcCategoryName = $mrcValue->DefaultLangCategoryName;
					$obj->Name = $mrcValue->Name;
					$obj->MrcName = $mrcValue->MerchantName;
					$obj->Position = $mrckey;
					$totalItems[] = $obj;
				}
			}
		}

		$analyticsEnabled = COM_BOOKINGFORCONNECTOR_GAENABLED == 1 && !empty(COM_BOOKINGFORCONNECTOR_GAACCOUNT) && COM_BOOKINGFORCONNECTOR_EECENABLED == 1;
//		if(count($totalItems) > 0 && $analyticsEnabled) {
		add_action('bfi_head', 'bfi_google_analytics_EEc', 10, 1);
		do_action('bfi_head', $listName);
		if(count($totalItems) > 0 && $analyticsEnabled) {

			$allobjects = array();
			$initobjects = array();
			foreach ($totalItems as $key => $value) {
				$obj = new stdClass;
				$obj->id = "" . $value->Id;
				if(isset($value->GroupId) && !empty($value->GroupId)) {
					$obj->groupid = $value->GroupId;
				}
				$obj->name = $value->Name;
				$obj->category = $value->MrcCategoryName;
				$obj->brand = $value->MrcName;
				$obj->position = $value->Position;
				if(!isset($value->ExcludeInitial) || !$value->ExcludeInitial) {
					$initobjects[] = $obj;
				} else {
					///$obj->merchantid = $value->MerchantId;
					//$allobjects[] = $obj;
				}
			}
//			$document->addScriptDeclaration('var currentResources = ' .json_encode($allobjects) . ';
//			var initResources = ' .json_encode($initobjects) . ';
//			' . ($sendData ? 'callAnalyticsEEc("addImpression", initResources, "list");' : ''));
			echo '<script type="text/javascript"><!--
			';
			echo ('var currentResources = ' .json_encode($allobjects) . ';
			var initResources = ' .json_encode($initobjects) . ';
			' . ($sendData ? 'callAnalyticsEEc("addImpression", initResources, "list");' : ''));
			echo "//--></script>";

		}
		
		//event tracking	
		?>
		<?php
		$page = bfi_get_current_page() ;
//		bfi_get_template("search/default.php",array("totalAvailable"=>$totalAvailable,"total"=>$total,"items"=>$items,"pages"=>$pages,"page"=>$page,"currencyclass"=>$currencyclass,"listNameAnalytics"=>$listNameAnalytics,"totPerson"=>$totPerson,"currSorting"=>$currSorting));	

	switch ($layoutresult) {
	    case 'rental':
			bfi_get_template("search/rental.php",array("totalAvailable"=>$totalAvailable,"total"=>$total,"items"=>$items,"pages"=>$pages,"page"=>$page,"currencyclass"=>$currencyclass,"listNameAnalytics"=>$listNameAnalytics,"totPerson"=>$totPerson,"currSorting"=>$currSorting));	
	        break;
	    case 'mapsells':
			bfi_get_template("search/mapsells.php",array("totalAvailable"=>$totalAvailable,"total"=>$total,"items"=>$items,"pages"=>$pages,"page"=>$page,"currencyclass"=>$currencyclass,"listNameAnalytics"=>$listNameAnalytics,"totPerson"=>$totPerson,"currSorting"=>$currSorting));	
	        break;
	    case 'slot':
			bfi_get_template("search/slot.php",array("totalAvailable"=>$totalAvailable,"total"=>$total,"items"=>$items,"pages"=>$pages,"page"=>$page,"currencyclass"=>$currencyclass,"listNameAnalytics"=>$listNameAnalytics,"totPerson"=>$totPerson,"currSorting"=>$currSorting));	
	        break;
	    case 'experience':
			bfi_get_template("search/experience.php",array("totalAvailable"=>$totalAvailable,"total"=>$total,"items"=>$items,"pages"=>$pages,"page"=>$page,"currencyclass"=>$currencyclass,"listNameAnalytics"=>$listNameAnalytics,"totPerson"=>$totPerson,"currSorting"=>$currSorting));	
	        break;
	    default:      
			bfi_get_template("search/default.php",array("totalAvailable"=>$totalAvailable,"total"=>$total,"items"=>$items,"pages"=>$pages,"page"=>$page,"currencyclass"=>$currencyclass,"listNameAnalytics"=>$listNameAnalytics,"totPerson"=>$totPerson,"currSorting"=>$currSorting));	
	}
		
		do_action( 'bookingfor_after_main_content' );
		?>
		</div>
		<?php 
		
		return ob_get_clean();
	}else{
		return '<div class="bfi-resultview" ></div>';
	}

	}
		

	
	/**
	 * Resources on sells page shortcode.
	 *
	 * @param mixed $atts
	 * @return string
	 */
	public static function bfi_shortcode_onsells( $atts ) {

		$atts = shortcode_atts( array(
			'per_page' => COM_BOOKINGFORCONNECTOR_ITEMPERPAGE,
			'orderby'  => 'AddedOn',
			'order'    => 'desc',
			'category' => '',  // Slugs
		), $atts );

		if ( is_admin() ) {
			return '';
		}

        $language = $GLOBALS['bfi_lang'];

		$fileNameCached = 'bfi_shortcode_onsells' . '_' . $language . '_' . implode("_", array_values($atts)). '_' . $page. '_' . COM_BOOKINGFORCONNECTOR_ITEMPERPAGE ;
		
		if (COM_BOOKINGFORCONNECTOR_ISBOT) {
			$currContent = BFI_Cache::getCachedContent($fileNameCached);
			if (!empty($currContent)) {
			    return $currContent;
			}
		}


		$resourcesmodel = new BookingForConnectorModelOnSellUnits();
		$resourcesmodel->populateState();
		$resourcesmodel->setItemPerPage(COM_BOOKINGFORCONNECTOR_ITEMPERPAGE);
//

		 $resourcesmodel->setOrdering($atts['orderby']);
		 $resourcesmodel->setDirection($atts['order']);

		$filter_order = $resourcesmodel->getOrdering();
		$filter_order_Dir = $resourcesmodel->getDirection();

		$items = $resourcesmodel->getItems();

		$resources = is_array($items) ? $items : array();
		$total = $resourcesmodel->getTotal();

		ob_start();
		include(BFI()->plugin_path().'/templates/onsellunits.php');
		$output =  ob_get_clean();
		if (COM_BOOKINGFORCONNECTOR_ISBOT) {
			BFI_Cache::setCachedContent($fileNameCached,$output);
		}
		return $output;
	}

	public static function bfi_shortcode_events( $atts ) {
		$atts = shortcode_atts( array(
			'per_page' => COM_BOOKINGFORCONNECTOR_ITEMPERPAGE,
			'orderby'  => '',
			'order'    => 'desc',
			'category' => '',  // Slugs
			'tagid' => '',  // Slugs
			'cityids' => '',  // Slugs
			'onlylist' => '0',  // Slugs
		), $atts );

		if ( is_admin() ) {
			return '';
		}
		if (COM_BOOKINGFORCONNECTOR_ISBOT) {
			$atts['onlylist'] = '1';
		}

		$page = bfi_get_current_page() ;
		$start = ($page - 1) * COM_BOOKINGFORCONNECTOR_ITEMPERPAGE;

		$onlylist =  !empty($atts['onlylist']) ? $atts['onlylist'] : '0';
		$language = $GLOBALS['bfi_lang'];

		$fileNameCached = 'bfi_shortcode_events' . '_' . $language . '_' . implode("_", array_values($atts)). '_' . $page. '_' . COM_BOOKINGFORCONNECTOR_ITEMPERPAGE ;
		
		if (COM_BOOKINGFORCONNECTOR_ISBOT) {
			$currContent = BFI_Cache::getCachedContent($fileNameCached);
			if (!empty($currContent)) {
			    return $currContent;
			}
		}

		bfi_setSessionFromSubmittedDataEvent();
		$searchmodel = new BookingForConnectorModelSearchEvent();
		$searchmodel->setItemPerPage(COM_BOOKINGFORCONNECTOR_ITEMPERPAGE);
		$currParam = BFCHelper::getSearchEventParamsSession();
		$currParam['categoryIds'] = !empty($atts['category'])?$atts['category']:'';
		$currParam['tagids'] = !empty($atts['tagid']) ? $atts['tagid'] : '';
		$currParam['cityIds'] =  !empty($atts['cityids']) ? $atts['cityids'] : '';
		$newsearch = isset($_REQUEST['newsearch']) ? $_REQUEST['newsearch'] : '1';
		$currParam['newsearch'] = $newsearch;
		$checkin = new DateTime('UTC');
		$checkout = new DateTime('UTC');
		$checkin->setTime(0,0,0);
		$checkout->setTime(0,0,0);
		$checkout->modify( '+1 year');

		$currParam['checkin'] = $checkin;
		$currParam['checkout'] = $checkout;

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
						$paramRef = array(
							"total"=>$total,
							"items"=>$items,
							"page"=>$page,
							"pages"=>$pages,
							"currParam"=>$currParam,
							"filter_order"=>$filter_order,
							"filter_order_Dir"=>$filter_order_Dir,
							"currSorting"=>$currSorting,
							"listNameAnalytics"=>$listNameAnalytics,
							"showfilter"=>1
							);
		ob_start();
			if(empty( $onlylist )){
			?>		
					<div class="bfi-row ">
						<div class="bfi-col-md-3 bfi-col-sm-3 bfi-col-xs-12  bfi-sidebar">
							<?php 
							if (!COM_BOOKINGFORCONNECTOR_ISMOBILE) {							
								bfi_get_template("widgets/booking-searchevents.php");	
								$setLat = COM_BOOKINGFORCONNECTOR_GOOGLE_POSX;
								$setLon = COM_BOOKINGFORCONNECTOR_GOOGLE_POSY;
								bfi_get_template("widgets/smallmap.php",array("setLat"=>$setLat,"setLon"=>$setLon));	
							}
							bfi_get_template("widgets/search-filter-events.php",$paramRef);	
							?>
						</div>
						<div class="bfi-col-md-9 bfi-col-xs-12 bfi-col-sm-9  bfi-body">
							<?php 
					
			} //if onlylist
								bfi_get_template("search/event.php",$paramRef);
			if(empty( $onlylist )){
							?>
						</div>
					<?php 
						if (COM_BOOKINGFORCONNECTOR_ISMOBILE) {
					?>
						<div class="bfi-col-md-3 bfi-col-sm-3 bfi-col-xs-12  bfi-sidebar">
							<?php 
								bfi_get_template("widgets/booking-searchevents.php");	
							?>
						</div>
					<?php 
						}
					?>						
					</div>
					<?php 
			} //if onlylist
		}
            $output =  ob_get_clean();
            if (COM_BOOKINGFORCONNECTOR_ISBOT) {
                BFI_Cache::setCachedContent($fileNameCached,$output);
            }
            return $output;
	}

	
	/**
	 * Short description.
	 * @param   type    $varname    description
	 * @return  type    description
	 * @access  public or private
	 * @static  makes the class property accessible without needing an instantiation of the class
	 */
	public static function bfi_shortcode_pointsofinterests($atts)
	{
		$atts = shortcode_atts( array(
			'per_page' => COM_BOOKINGFORCONNECTOR_ITEMPERPAGE,
			'orderby'  => '',
			'order'    => 'desc',
			'category' => '',  // Slugs
			'tagid' => '',  // Slugs
			'cityids' => '',  // Slugs
			'onlylist' => '0',  // Slugs
		), $atts );

		if ( is_admin() ) {
			return "";
		}


		$page = bfi_get_current_page() ;
		$start = ($page - 1) * COM_BOOKINGFORCONNECTOR_ITEMPERPAGE;;

		$fileNameCached = 'bfi_shortcode_merchant' . '_' . $language . '_' . implode("_", array_values($atts)). '_' . $page. '_' . COM_BOOKINGFORCONNECTOR_ITEMPERPAGE ;
		
		if (COM_BOOKINGFORCONNECTOR_ISBOT) {
			$atts['onlylist'] = '1';
		}
		$onlylist =  !empty($atts['onlylist']) ? $atts['onlylist'] : '0';

		$fileNameCached = 'bfi_shortcode_pointsofinterests' . '_' . $language . '_' . implode("_", array_values($atts)). '_' . $page. '_' . COM_BOOKINGFORCONNECTOR_ITEMPERPAGE ;
		
		if (COM_BOOKINGFORCONNECTOR_ISBOT) {
			$currContent = BFI_Cache::getCachedContent($fileNameCached);
			if (!empty($currContent)) {
			    return $currContent;
			}
		}


		bfi_setSessionFromSubmittedDataPoi();
		$searchmodel = new BookingForConnectorModelPointsofinterests();
		$searchmodel->setItemPerPage(COM_BOOKINGFORCONNECTOR_ITEMPERPAGE);
		$currParam = BFCHelper::getSearchPoiParamsSession();
		$currParam['categoryIds'] = !empty($atts['category'])?$atts['category']:'';
		$currParam['tagids'] = !empty($atts['tagid']) ? $atts['tagid'] : '';
		$currParam['cityIds'] =  !empty($atts['cityids']) ? $atts['cityids'] : '';
		$newsearch = (isset($_REQUEST['newsearch']) ? $_REQUEST['newsearch'] : ($page==1))? '1' : '0' ;
		$currParam['newsearch'] = $newsearch;
		$searchid = !empty($currParam['searchid']) ? $currParam['searchid'] : uniqid('', true);
		$currParam['searchid'] = $searchid;

        $searchmodel->setParam($currParam);
		BFCHelper::setSearchPoiParamsSession($currParam);

		$listNameAnalytics = 12;
		$items = $searchmodel->getItems(false, false, $start,COM_BOOKINGFORCONNECTOR_ITEMPERPAGE);
		$items = is_array($items) ? $items : array();
		$total=$searchmodel->getTotal();

		$filter_order = $searchmodel->getOrdering();
		$filter_order_Dir = $searchmodel->getDirection();
		$currSorting = $filter_order ."|".$filter_order_Dir ;
		$pages = ceil($total / COM_BOOKINGFORCONNECTOR_ITEMPERPAGE);

		$GLOBALS['bfPoiSearchedItems'] = $items;
		$GLOBALS['bfPoiSearchedItemsTotal'] = $total;
		$GLOBALS['bfPoiSearchedItemsCurrSorting'] = $currSorting;
		$GLOBALS['bfPoiSearched'] = 1;

			if ($total>0) {
						$paramRef = array(
							"total"=>$total,
							"items"=>$items,
							"page"=>$page,
							"pages"=>$pages,
							"currParam"=>$currParam,
							"filter_order"=>$filter_order,
							"filter_order_Dir"=>$filter_order_Dir,
							"currSorting"=>$currSorting,
							"listNameAnalytics"=>$listNameAnalytics,
							"showfilter"=>1
							);
		ob_start();
			if(empty( $onlylist )){
			?>		
					<div class="bfi-row ">
						<div class="bfi-col-md-3 bfi-col-sm-3 bfi-col-xs-12  bfi-sidebar">
							<?php 
							$setLat = COM_BOOKINGFORCONNECTOR_GOOGLE_POSX;
							$setLon = COM_BOOKINGFORCONNECTOR_GOOGLE_POSY;
							bfi_get_template("widgets/smallmap.php",array("setLat"=>$setLat,"setLon"=>$setLon));	

							bfi_get_template("widgets/search-filter-poi.php",$paramRef);	
							?>
						</div>
						<div class="bfi-col-md-9 bfi-col-xs-12 bfi-col-sm-9  bfi-body">
							<?php 
					
			} //if onlylist
                                bfi_get_template("search/poi.php",$paramRef);
			if(empty( $onlylist )){
							?>
						</div>
					</div>
					<?php 
			} //if onlylist
		}
            $output =  ob_get_clean();
            if (COM_BOOKINGFORCONNECTOR_ISBOT) {
                BFI_Cache::setCachedContent($fileNameCached,$output);
            }
            return $output;
	} // end func

	/**
	 * Tag page shortcode.
	 *
	 * @param mixed $atts
	 * @return string
	 */
	public static function bfi_shortcode_tag( $atts ) {

		$atts = shortcode_atts( array(
			'per_page' => COM_BOOKINGFORCONNECTOR_ITEMPERPAGE,
			'orderby'  => 'Order',
			'order'    => 'asc',
			'tagid' => '',  // Slugs
			'scope' => '1',  // Slugs
			'grouped' => '0',  // Slugs
		), $atts );

		if ( is_admin() ) {
			return '';
		}
		if ( ! $atts['tagid'] ||  ! $atts['scope']) {
			return '';
		}

		$language = $GLOBALS['bfi_lang'];

		$fileNameCached = 'bfi_shortcode_merchant' . '_' . $language . '_' . implode("_", array_values($atts)). '_' . $page. '_' . COM_BOOKINGFORCONNECTOR_ITEMPERPAGE ;
		
		if (COM_BOOKINGFORCONNECTOR_ISBOT) {
			$currContent = BFI_Cache::getCachedContent($fileNameCached);
			if (!empty($currContent)) {
			    return $currContent;
			}
		}

		$resourcesmodel = new BookingForConnectorModelTags();
		$resourcesmodel->populateState();
		$resourcesmodel->setItemPerPage(COM_BOOKINGFORCONNECTOR_ITEMPERPAGE);


		$currParam = $resourcesmodel->getParam();
		$currParam['tagId'] = $atts['tagid'];
		$currParam['scope'] = $atts['scope'];
		$currParam['show_grouped'] = $atts['grouped'];
		$resourcesmodel->setParam($currParam);

//
//		$filter_order = $resourcesmodel->getOrdering();
//		$filter_order_Dir = $resourcesmodel->getDirection();

		 $resourcesmodel->setOrdering($atts['orderby']);
		 $resourcesmodel->setDirection($atts['order']);
		 $item = $resourcesmodel->getItem();
//		$items = $resourcesmodel->getItems();
		$category = bfi_TagsScope::Merchant;
		switch (intval($atts['scope'])) {
			case 1: // Merchants
				$category = bfi_TagsScope::Merchant;
				break;
			case 2: // OnSellUnit
				$category = bfi_TagsScope::OnSellUnit;
				break;
			case 3: // Resource
				$category = bfi_TagsScope::Resource;
				break;
			case 4: // ResourceGroup
				$category = bfi_TagsScope::ResourceGroup;
				break;
			case 5: // Offert
				$category = bfi_TagsScope::Offert;
				break;
			case 6: // Event
				$category = bfi_TagsScope::Event;
				break;
			case 7: // Poi
				$category = bfi_TagsScope::Poi;
				break;
		}
		if(!empty($item) && ($item->SelectionCategory & $category) ) {
			ob_start();

			$showGrouped = 0;
			$list = "";
			$listNameAnalytics = 0;
			$totalItems = array();
			$sendData = true;
			$return = "";

			switch ($category) {
				case bfi_TagsScope::Merchant: // Merchants


					$listNameAnalytics = 1;
					$items = $resourcesmodel->getItemsMerchants();
					$total = $resourcesmodel->getTotalMerchants();
					$filter_order = $resourcesmodel->getOrdering();
					$filter_order_Dir = $resourcesmodel->getDirection();

					$merchants = is_array($items) ? $items : array();

					$paramRef = array(
						"merchants"=>$merchants,
						"total"=>$total,
						"items"=>$items,
						"listNameAnalytics"=>$listNameAnalytics,
						"filter_order"=>$filter_order,
						"filter_order_Dir"=>$filter_order_Dir
						);
					bfi_get_template("merchantslist/merchantslist.php",$paramRef);

					break;
				case bfi_TagsScope::Onsellunit: // Onsellunit
					$listNameAnalytics = 7;
					$items = $resourcesmodel->getItemsOnSellUnit();
					$total = $resourcesmodel->getTotalResources();
					$filter_order = $resourcesmodel->getOrdering();
					$filter_order_Dir = $resourcesmodel->getDirection();
					$resources = is_array($items) ? $items : array();
					$paramRef = array(
						"resources"=>$resources,
						"total"=>$total,
						"items"=>$items,
						"listNameAnalytics"=>$listNameAnalytics,
						"filter_order"=>$filter_order,
						"filter_order_Dir"=>$filter_order_Dir
						);
					bfi_get_template("onsellunits.php",$paramRef);

					break;
				case bfi_TagsScope::Resource: // Resource
					$listNameAnalytics = 5;
					$items = $resourcesmodel->getItemsResources();
					$total = $resourcesmodel->getTotalResources();
					if (!empty($items)) {
						foreach($items as $mrckey => $mrcValue) {
							$obj = new stdClass();
							$obj->Id = $mrcValue->ResourceId . " - Resource";
							$obj->MerchantId = $mrcValue->MerchantId;
							$obj->MrcCategoryName = $mrcValue->DefaultLangMrcCategoryName;
							$obj->Name = $mrcValue->ResName;
							$obj->MrcName = $mrcValue->MrcName;
							$obj->Position = $mrckey;
							$totalItems[] = $obj;
						}
						if  ($currParam['show_grouped'] == true) {
							$merchants = is_array($items) ? $items : array();
							$paramRef = array(
								"merchants"=>$merchants,
								"total"=>$total,
								"items"=>$items,
								"listNameAnalytics"=>$listNameAnalytics,
								);
							bfi_get_template("resources_grouped.php",$paramRef);
						}else{
							$resources = is_array($items) ? $items : array();
							$paramRef = array(
								"resources"=>$resources,
								"total"=>$total,
								"items"=>$items,
								"listNameAnalytics"=>$listNameAnalytics,
								);
							bfi_get_template("resources.php",$paramRef);
						}
					}

					break;
				case bfi_TagsScope::ResourceGroup: // ResourceGroup
							$currencyclass = bfi_get_currentCurrency();
							$page = bfi_get_current_page() ;
							$start = ($page - 1) * COM_BOOKINGFORCONNECTOR_ITEMPERPAGE;

							$listName = "";
							$listNameAnalytics = 0;
							$sessionkeysearch = "search.params";
							bfi_setSessionFromSubmittedData($sessionkeysearch);
							$searchmodel = new BookingForConnectorModelSearch;
									
							$pars = BFCHelper::getSearchParamsSession($sessionkeysearch);
							$filterinsession = null;
							
							$pars['resourcegroupsResults'] = 1;
							$pars['onlystay'] = 1;
							$pars['groupresulttype'] = 2;
							$pars['merchantResults'] = 0;
							$pars['groupTagsIds'] = $atts['tagid'];
							BFCHelper::setSearchParamsSession($pars, $sessionkeysearch);

							$items =  array();
							$total = 0;
							$currSorting = "";
							$totalAvailable = 0;

							if (isset($pars['checkin']) && isset($pars['checkout'])){
								$now = new DateTime('UTC');
								$now->setTime(0,0,0);
								$checkin = isset($pars['checkin']) ? $pars['checkin'] : new DateTime('UTC');
								$checkout = isset($pars['checkout']) ? $pars['checkout'] : new DateTime('UTC');

								$availabilitytype = isset($pars['availabilitytype']) ? $pars['availabilitytype'] : "1";
								
								$availabilitytype = explode(",",$availabilitytype);
								if (($checkin == $checkout && (!in_array("0",$availabilitytype) && !in_array("2",$availabilitytype)&& !in_array("3",$availabilitytype) ) ) || $checkin->diff($checkout)->format("%a") <0 || $checkin < $now ){
									$nodata = true;
								}else{
									if (empty($GLOBALS['bfSearched'])) {
										
										$filterinsession = BFCHelper::getFilterSearchParamsSession($sessionkeysearch);
										$items = $searchmodel->getItems(false, false, $start,COM_BOOKINGFORCONNECTOR_ITEMPERPAGE,$sessionkeysearch);
										
										$items = is_array($items) ? $items : array();
												
										$total=$searchmodel->getTotal($sessionkeysearch);
										$totalAvailable=$searchmodel->getTotalAvailable($sessionkeysearch);
										$currSorting=$searchmodel->getOrdering() . "|" . $searchmodel->getDirection();
										$GLOBALS['bfSearched'] = 1;
									}else{
										
										$items = isset($GLOBALS['bfSearchedItems'])? $GLOBALS['bfSearchedItems'] : null;
										$total = isset($GLOBALS['bfSearchedItemsTotal'])? $GLOBALS['bfSearchedItemsTotal'] : null;
										$totalAvailable = isset($GLOBALS['bfSearchedItemsTotalAvailable'])? $GLOBALS['bfSearchedItemsTotalAvailable'] : null;
										$currSorting = isset($GLOBALS['bfSearchedItemsCurrSorting'])? $GLOBALS['bfSearchedItemsCurrSorting'] : null;
									}
								}

							}
							$pages = ceil($total / COM_BOOKINGFORCONNECTOR_ITEMPERPAGE);

							$merchant_ids = '';


                            $currParam = BFCHelper::getSearchParamsSession($sessionkeysearch);
									$currParam['resourcegroupsResults'] = 1;
									$merchantResults = $currParam['merchantResults'];
									$resourcegroupsResults = $currParam['resourcegroupsResults'];
									$totPerson = (isset($currParam)  && isset($currParam['paxes']))? $currParam['paxes']:0 ;
							/*-- criteo --*/
							$criteoConfig = null;
							if(COM_BOOKINGFORCONNECTOR_CRITEOENABLED){
									$merchantsCriteo = array();
									if(!empty($items)) {
										$merchantsCriteo = array_unique(array_map(function($a) { return $a->MerchantId; }, $items));
									}
									$criteoConfig = BFCHelper::getCriteoConfiguration(1, $merchantsCriteo);
									if(isset($criteoConfig) && isset($criteoConfig->enabled) && $criteoConfig->enabled && count($criteoConfig->merchants) > 0) {
										echo '<script type="text/javascript" src="//static.criteo.net/js/ld/ld.js" async="true"></script>';
										echo '<script type="text/javascript"><!--
										';
										echo ('window.criteo_q = window.criteo_q || []; 
										var deviceTypeCriteo = /iPad/.test(navigator.userAgent) ? "t" : /Mobile|iP(hone|od)|Android|BlackBerry|IEMobile|Silk/.test(navigator.userAgent) ? "m" : "d";
										window.criteo_q.push( 
											{ event: "setAccount", account: '. $criteoConfig->campaignid .'}, 
											{ event: "setSiteType", type: deviceTypeCriteo }, 
											{ event: "setEmail", email: "" }, 
											{ event: "viewSearch", checkin_date: "' . $pars["checkin"]->format('Y-m-d') . '", checkout_date: "' . $pars["checkout"]->format('Y-m-d') . '"},
											{ event: "viewList", item: ' . json_encode($criteoConfig->merchants) .' }
										);');
										echo "//--></script>";
									}	
								
							}

							/*-- criteo --*/

									$totalItems = array();
									$sendData = true;
										
									if(!empty($items)) {
										if($merchantResults) {
							//				$resIndex = 0;
											$listNameAnalytics = 9; // old 1;
											$listName = BFCHelper::$listNameAnalytics[$listNameAnalytics];//"Merchants Group List";
											$itmCounter = 1;
											foreach($items as $itemkey => $itemValue) {
												$listResources = array();
												foreach ($itemValue->Results as $keyRes=>$singleResource) {
													$id = $singleResource->ResourceId;

													if (isset($listResources[$id])) {
														$listResources[$id][] = $singleResource;
													} else {
														$listResources[$id] = array($singleResource);
													}
												}

												array_multisort(array_map('count', $listResources), SORT_DESC, $listResources);
												foreach ($listResources as $resourceId=>$singleResource) // foreach $listMerchantsCart
												{
													$currResource = $singleResource[0];
													$objRes = new stdClass();
													$objRes->MerchantId = $currResource->MerchantId;
													$objRes->MrcName = $itemValue->MerchantName;
													$objRes->MrcCategoryName = $itemValue->DefaultLangCategoryName;
													$objRes->Position = $itmCounter;// $resIndex;
													$objRes->Id = $currResource->ResourceId . " - Resource";
													$objRes->Name = $itemValue->Name;
													$itmCounter++;
																		
													$totalItems[] = $objRes;
												}
											}
										} else if ($resourcegroupsResults) {
							//				$sendData = false;
											$resIndex = 0;
											$listNameAnalytics = 2;
											$listName = BFCHelper::$listNameAnalytics[$listNameAnalytics];// "Resources Group List";
											
											$itmCounter = 1;
											foreach($items as $itemkey => $itemValue) {
												$listResources = array();
												foreach ($itemValue->Results as $keyRes=>$singleResource) {
													$id = $singleResource->ResourceId;

													if (isset($listResources[$id])) {
														$listResources[$id][] = $singleResource;
													} else {
														$listResources[$id] = array($singleResource);
													}
												}

												array_multisort(array_map('count', $listResources), SORT_DESC, $listResources);
												foreach ($listResources as $resourceId=>$singleResource) // foreach $listMerchantsCart
												{
													$currResource = $singleResource[0];
													$objRes = new stdClass();
													$objRes->MerchantId = $currResource->MerchantId;
													$objRes->MrcName = $itemValue->MerchantName;
													$objRes->MrcCategoryName = $itemValue->DefaultLangCategoryName;
													$objRes->Position = $itmCounter;// $resIndex;
													$objRes->Id = $currResource->ResourceId . " - Resource";
													$objRes->Name = $itemValue->Name;
													$itmCounter++;
																		
													$totalItems[] = $objRes;
												}
											}
											
											/*
											foreach($items as $itemkey => $itemValue) {
												$objRes = new stdClass();
												$objRes->Id = $itemValue->ItemId . " - Resource";
												$objRes->CondominiumId= $itemValue->ItemId;
												$objRes->MerchantId = $itemValue->MerchantId;
												$objRes->Name = $itemValue->Name;
												$objRes->MrcName = $itemValue->MerchantName;
												$objRes->MrcCategoryName = $itemValue->CategoryName;
												$objRes->Position = $itemkey;// $resIndex;
												$totalItems[] = $objRes;
											}
											*/
										} else {
											$listNameAnalytics = 3;
											$listName = BFCHelper::$listNameAnalytics[$listNameAnalytics];// "Resources Search List";
											foreach($items as $mrckey => $mrcValue) {
												$obj = new stdClass();
												$obj->Id = $mrcValue->ItemId . " - Resource";
												$obj->MerchantId = $mrcValue->MerchantId;
												$obj->MrcCategoryName = $mrcValue->DefaultLangCategoryName;
												$obj->Name = $mrcValue->Name;
												$obj->MrcName = $mrcValue->MerchantName;
												$obj->Position = $mrckey;
												$totalItems[] = $obj;
											}
										}
									}

									$analyticsEnabled = COM_BOOKINGFORCONNECTOR_GAENABLED == 1 && !empty(COM_BOOKINGFORCONNECTOR_GAACCOUNT) && COM_BOOKINGFORCONNECTOR_EECENABLED == 1;
							//		if(count($totalItems) > 0 && $analyticsEnabled) {
									add_action('bfi_head', 'bfi_google_analytics_EEc', 10, 1);
									do_action('bfi_head', $listName);
									if(count($totalItems) > 0 && $analyticsEnabled) {

										$allobjects = array();
										$initobjects = array();
										foreach ($totalItems as $key => $value) {
											$obj = new stdClass;
											$obj->id = "" . $value->Id;
											if(isset($value->GroupId) && !empty($value->GroupId)) {
												$obj->groupid = $value->GroupId;
											}
											$obj->name = $value->Name;
											$obj->category = $value->MrcCategoryName;
											$obj->brand = $value->MrcName;
											$obj->position = $value->Position;
											if(!isset($value->ExcludeInitial) || !$value->ExcludeInitial) {
												$initobjects[] = $obj;
											} else {
												///$obj->merchantid = $value->MerchantId;
												//$allobjects[] = $obj;
											}
										}
										echo '<script type="text/javascript"><!--
										';
										echo ('var currentResources = ' .json_encode($allobjects) . ';
										var initResources = ' .json_encode($initobjects) . ';
										' . ($sendData ? 'callAnalyticsEEc("addImpression", initResources, "list");' : ''));
										echo "//--></script>";

									}
		
						bfi_get_template("search/default.php",array("totalAvailable"=>$totalAvailable,"total"=>$total,"items"=>$items,"pages"=>$pages,"page"=>$page,"currencyclass"=>$currencyclass,"listNameAnalytics"=>$listNameAnalytics,"totPerson"=>$totPerson,"currSorting"=>$currSorting));	


					break;
				case bfi_TagsScope::Offert: // Offert

					break;
				case bfi_TagsScope::Event: // Event
					$listNameAnalytics = 11;
					$results = $resourcesmodel->getItemsEvents();
					if(isset($results->TotalItemsCount)){
						$items = json_decode($results->ItemsString);
						$total= $results->TotalItemsCount;
						$filter_order = $resourcesmodel->getOrdering();
						$filter_order_Dir = $resourcesmodel->getDirection();
						$currSorting = $filter_order ."|".$filter_order_Dir ;
						$page = bfi_get_current_page() ;
						$pages = ceil($total / COM_BOOKINGFORCONNECTOR_ITEMPERPAGE);

						$GLOBALS['bfEventSearchedItems'] = $items;
						$GLOBALS['bfEventSearchedItemsTotal'] = $total;
						$GLOBALS['bfEventSearchedItemsCurrSorting'] = $currSorting;
						$GLOBALS['bfEventSearched'] = 1;

					if ($total>0) {
    
							?>		
					<div class="bfi-row ">
						<div class="bfi-col-md-3 bfi-col-sm-3 bfi-col-xs-12  bfi-sidebar">
							<?php 
							bfi_get_template("widgets/booking-searchevents.php");	
							$setLat = COM_BOOKINGFORCONNECTOR_GOOGLE_POSX;
							$setLon = COM_BOOKINGFORCONNECTOR_GOOGLE_POSY;
							bfi_get_template("widgets/smallmap.php",array("setLat"=>$setLat,"setLon"=>$setLon));	

							bfi_get_template("widgets/search-filter-events.php",array("showfilter"=>1,"total"=>$total,"items"=>$items,"pages"=>$pages,"page"=>$page,"listNameAnalytics"=>$listNameAnalytics,"currSorting"=>$currSorting));	
							?>
						</div>
						<div class="bfi-col-md-9 bfi-col-xs-12 bfi-col-sm-9  bfi-body">
							<?php 
								bfi_get_template("search/event.php",array("total"=>$total,"items"=>$items,"pages"=>$pages,"page"=>$page,"listNameAnalytics"=>$listNameAnalytics,"currSorting"=>$currSorting));
							?>
						</div>
					</div>

					<?php 
						}
					}
					break;
				case bfi_TagsScope::Poi: // Poi

					break;
			}

			$listName = BFCHelper::$listNameAnalytics[$listNameAnalytics];
			if(count($totalItems) > 0 && COM_BOOKINGFORCONNECTOR_GAENABLED == 1 && !empty(COM_BOOKINGFORCONNECTOR_GAACCOUNT) && COM_BOOKINGFORCONNECTOR_EECENABLED == 1) {

			add_action('bfi_head', 'bfi_google_analytics_EEc', 10, 1);
			do_action('bfi_head', $listName);

				$allobjects = array();
				$initobjects = array();
				foreach ($totalItems as $key => $value) {
					$obj = new stdClass;
					$obj->id = "" . $value->Id;
					if(isset($value->GroupId) && !empty($value->GroupId)) {
						$obj->groupid = $value->GroupId;
					}
					$obj->name = $value->Name;
					$obj->category = $value->MrcCategoryName;
					$obj->brand = $value->MrcName;
					$obj->position = $value->Position;
					if(!isset($value->ExcludeInitial) || !$value->ExcludeInitial) {
						$initobjects[] = $obj;
					} else {
						///$obj->merchantid = $value->MerchantId;
						//$allobjects[] = $obj;
					}
				}
				echo '<script type="text/javascript"><!--
				';
				echo ('var currentResources = ' .json_encode($allobjects) . ';
				var initResources = ' .json_encode($initobjects) . ';
				' . ($sendData ? 'callAnalyticsEEc("addImpression", initResources, "list");' : ''));
				echo "//--></script>";

//			$return = ob_get_contents();
//			ob_end_clean();
		}

            $output =  ob_get_clean();
            if (COM_BOOKINGFORCONNECTOR_ISBOT) {
                BFI_Cache::setCachedContent($fileNameCached,$output);
            }
            return $output;

		}
	}



public static function bfi_shortcode_dowidget($atts) {

global $wp_registered_widgets, $_wp_sidebars_widgets, $wp_registered_sidebars;

/* check if the widget is in  the shortcode x sidebar  if not , just use generic, 
if it is in, then get the instance  data and use that */

	if (is_admin()) {return '';}  // eg in case someone decides to apply content filters when apost is saved, and not all widget stuff is there.
	extract(shortcode_atts(array(
		'sidebar' => 'Widgets for Shortcodes', //default
		'id' => '',
		'name' => '', /* MKM added explicit 'name' attribute.  For existing users we still need to allow prev method, else too many support queries will happen */
		'title' => '',   /* do the default title unless they ask us not to - use string here not boolean */
		'class' => 'bfi_widget', /* the widget class is picked up automatically.  If we want to add an additional class at the wrap level to try to match a theme, use this */
		'wrap' => '', /* wrap the whole thing - title plus widget in a div - maybe the themes use a div, maybe not, maybe we want that styling, maybe not */
		'widget_classes' =>  ''  /* option to disassociate from themes widget styling */
	), $atts));
	
	if (isset($_wp_sidebars_widgets) ) {
//		amr_show_widget_debug('which one', $name, $id, $sidebar);  //check for debug prompt and show widgets in shortcode sidebar if requested and logged in etc
	}
	else { 
		$output = '<br />No widgets defined at all in any sidebar!'; 
		return ($output);
	}
	
	/* compatibility check - if the name is not entered, then the first parameter is the name */
	if (empty($name) and !empty($atts[0]))  
		$name = $atts[0];
	/* the widget need not be specified, [do_widget widgetname] is adequate */
	if (!empty($name)) {  // we have a name
		$widget = $name;
		
		foreach ($wp_registered_widgets as $i => $w) { /* get the official internal name or id that the widget was registered with  */
			if (strtolower($w['name']) === strtolower($widget)) 
				$widget_ids[] = $i;
			//if ($debug) {echo '<br /> Check: '.$w['name'];}
		}	
		
		if (!($sidebarid = amr_get_sidebar_id ($sidebar))) {
			$sidebarid=$sidebar;   /* get the official sidebar id for this widget area - will take the first one */		
		}	
		
		
	}	
	else { /* check for id if we do not have a name */
//	$id="bookingfor_booking_search-".$id;
			if (!empty($id))  { 	/* if a specific id has been specified */			
				foreach ($wp_registered_widgets as $i => $w) { /* get the official internal name or id that the widget was registered with  */
					if ($w['id'] === $id) {
						$widget_ids[] = $id;
					}	
				}
//				echo '<h2>We have an id: '.$id.'</h2>'; 	if (!empty($widget_ids)) var_dump($widget_ids);	
//				return $output;		
			}
			else {
				$output = '<br />No valid widget name or id given in shortcode parameters';	
			
				return $output;		
			}
			// if we have id, get the sidebar for it
						
			$sidebarid = amr_get_widgets_sidebar($id);
			if (!$sidebarid) {
				$output =  '<br />Widget not in any sidebars<br />';
				return $output;
			}	
	}
	
	if (empty($widget)) 	$widget = '';
	if (empty($id)) 		$id = '';
	
	if (empty ($widget_ids)) { 
		$output =  '<br />Error: Your Requested widget "'.$widget.' '.$id.'" is not in the widget list.<br />';
//		$output .= amr_show_widget_debug('empty', $name, $id, $sidebar);
		return ($output) ;
	}		

		
	if (empty($widget)) 
		$widget = '';

	$content = ''; 			
	/* if the widget is in our chosen sidebar, then use the options stored for that */

//	if ((!isset ($_wp_sidebars_widgets[$sidebarid])) or (empty ($_wp_sidebars_widgets[$sidebarid]))) { // try upgrade
//		amr_upgrade_sidebar();
//	}
	
	//if we have a specific sidebar selected, use that
	if ((isset ($_wp_sidebars_widgets[$sidebarid])) and (!empty ($_wp_sidebars_widgets[$sidebarid]))) {
			/* get the intersect of the 2 widget setups so we just get the widget we want  */

		$wid = array_intersect ($_wp_sidebars_widgets[$sidebarid], $widget_ids );
	
	}
	else { /* the sidebar is not defined or selected - should not happen */
			if (isset($debug)) {  // only do this in debug mode
				if (!isset($_wp_sidebars_widgets[$sidebarid]))
					$output =  '<p>Error: Sidebar "'.$sidebar.'" with sidebarid "'.$sidebarid.'" is not defined.</p>'; 
				 // shouldnt happen - maybe someone running content filters on save
				else 
					$output =  '<p>Error: Sidebar "'.$sidebar.'" with sidebarid "'.$sidebarid.'" is empty (no widgets)</p>'; 
			}		
		}
	
	$output = '';
	if (empty ($wid) or (!is_array($wid)) or (count($wid) < 1)) { 

		$output = '<p>Error: Your requested Widget "'.$widget.'" is not in the "'.$sidebar.'" sidebar</p>';
//		$output .= amr_show_widget_debug('empty', $name, $id, $sidebar);

		unset($sidebar); 
		unset($sidebarid);

		}
	else {	
		/*  There may only be one but if we have two in our chosen widget then it will do both */
		$output = '';
		
		foreach ($wid as $i=>$widget_instance) {
			ob_start();  /* catch the echo output, so we can control where it appears in the text  */
			amr_shortcode_sidebar($widget_instance, $sidebar, $title, $class, $wrap, $widget_classes);
			$output .= ob_get_clean();
			}
	}
			
	return ($output);
	}
}
};

/*
Reference to wordpress plugin amr shortcode any widget 
url: https://it.wordpress.org/plugins/amr-shortcode-any-widget/
*/
if ( ! function_exists( 'amr_shortcode_sidebar' ) ) {
	function amr_shortcode_sidebar( $widget_id, 
		$name="widgets_for_shortcode", 
		$title=true, 
		$class='', 
		$wrap='', 
		$widget_classes='') { /* This is basically the wordpress code, slightly modified  */
		global $wp_registered_sidebars, $wp_registered_widgets;
		
	//	$debug = amr_check_if_widget_debug();

		$sidebarid = amr_get_sidebar_id ($name);

	//	$amr_sidebars_widgets = wp_get_sidebars_widgets(); //201711 do we need?
		$sidebar =null;
		if (in_array($sidebarid, $wp_registered_sidebars)) {
			$sidebar = $wp_registered_sidebars[$sidebarid];  // has the params etc
		}
		$did_one = false;
		
	//	echo "<pre>";
	//	echo $widget_id;
	//	echo "</pre>";
	//	
	//	echo "<pre>";
	//	echo print_r($wp_registered_widgets[$widget_id]);
	//	echo "</pre>";
		
	//		 echo "<pre>";
	//		 echo print_r(get_option('widget_bookingfor_booking_search'));
	//		 echo "</pre>";

		/* lifted from wordpress code, keep as similar as possible for now */

			if ( !isset($wp_registered_widgets[$widget_id]) ) return; // wp had c o n t i n u e

			$params = array_merge(
							array( 
								array_merge( 
	//								$sidebar, 
									array('widget_id' => $widget_id, 
										'widget_name' => $wp_registered_widgets[$widget_id]['name']
										) 
								) 
							),
							(array) $wp_registered_widgets[$widget_id]['params']
						);	
				
			$validtitletags = array ('h1','h2','h3','h4','h5','header','strong','em');
			$validwraptags = array ('div','p','main','aside','section');
			
			if (!empty($wrap)) { /* then folks want to 'wrap' with their own html tag, or wrap = yes  */		
				if ((!in_array( $wrap, $validwraptags))) 
					$wrap = ''; 
				  /* To match a variety of themes, allow for a variety of html tags. */
				  /* May not need if our sidebar match attempt has worked */
			}

			if (!empty ($wrap)) {
				$params[0]['before_widget'] = '<'.$wrap.' id="%1$s" class="%2$s">';
				$params[0]['after_widget'] = '</'.$wrap.'>';
			}
			
			// wp code to get classname
			$classname_ = '';
			//foreach ( (array) $wp_registered_widgets[$widget_id]['classname'] as $cn ) {
				$cn = $wp_registered_widgets[$widget_id]['classname'];
				if ( is_string($cn) )
					$classname_ .= '_' . $cn;
				elseif ( is_object($cn) )
					$classname_ .= '_' . get_class($cn);
			//}
			$classname_ = ltrim($classname_, '_');
			
			// add MKM and others requested class in to the wp classname string
			// if no class specfied, then class will = amrwidget.  These classes are so can reverse out unwanted widget styling.

			// $classname_ .= ' widget '; // wordpress seems to almost always adds the widget class
			

			$classname_ .= ' '.$class;

			// we are picking up the defaults from the  thems sidebar ad they have registered heir sidebar to issue widget classes?
			
			
			// Substitute HTML id and class attributes into before_widget		
			if (!empty($params[0]['before_widget'])) 
				$params[0]['before_widget'] = sprintf($params[0]['before_widget'], $widget_id, $classname_);
			else 
				$params[0]['before_widget'] = '';
			
			if (empty($params[0]['before_widget'])) 
				$params[0]['after_widget'] = '';

			$params = apply_filters( 'dynamic_sidebar_params', $params );  
			// allow, any pne usingmust ensure they apply to the correct sidebars
			
			if (!empty($title)) {
				if ($title=='false') { /* amr switch off the title html, still need to get rid of title separately */
					$params[0]['before_title'] = '<span style="display: none">';
					$params[0]['after_title'] = '</span>';
					}
				else {
					if (in_array( $title, $validtitletags)) {
						$class = ' class="widget-title" ';					
							
						$params[0]['before_title'] = '<'.$title.' '.$class.' >';
						$params[0]['after_title'] = '</'.$title.'>';
					}
				}			
			}
			
			if (!empty($widget_classes) and ($widget_classes == 'none') ) {
				$params = amr_remove_widget_class($params);  // also called in widget area shortcode
			}
			

			$callback = $wp_registered_widgets[$widget_id]['callback'];
			if ( is_callable($callback) ) {
				call_user_func_array($callback, $params);
				$did_one = true;
			}
	//	}
		return $did_one;
}
}

if ( ! function_exists( 'amr_get_sidebar_id' ) ) {
	function amr_get_sidebar_id ($name) { 
	/* walk through the registered sidebars with a name and find the id - will be something like sidebar-integer.  
	take the first one that matches */
	global $wp_registered_sidebars;	

		foreach ($wp_registered_sidebars as $i => $a) {
			if ((isset ($a['name'])) and ( $a['name'] === $name)) 
			return ($i);
		}
		return (false);
	}
}
if ( ! function_exists( 'amr_get_widgets_sidebar' ) ) {
	function amr_get_widgets_sidebar($wid) { 
	/* walk through the registered sidebars with a name and find the id - will be something like sidebar-integer.  
	take the first one that matches */
	global $_wp_sidebars_widgets;	
		foreach ($_wp_sidebars_widgets as $sidebarid => $sidebar) {	
			
			if (is_array($sidebar) ) { // ignore the 'array version' sidebarid that isnt actually a sidebar
				foreach ($sidebar as $i=> $w) {						
					if ($w == $wid) { 
						return 	$sidebarid;
					}	
				};	
			}	
		}
		return (false); // widget id not in any sidebar
	}
}
if ( ! function_exists( 'amr_remove_widget_class' ) ) {
	function amr_remove_widget_class($params) {  // remove the widget classes
		if (!empty($params[0]['before_widget'])) {
			$params[0]['before_widget'] = 
				str_replace ('"widget ','"',$params[0]['before_widget']);
		}
		
		if (!empty($params[0]['before_title'])) {  

			$params[0]['before_title'] = 
				$params[0]['before_title'] = str_replace ('widget-title','',$params[0]['before_title']);
				
		}
		
		return ($params);
	}
}
