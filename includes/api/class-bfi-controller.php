<?php
/**
 * Contains the query functions for Bookingfor which alter the front-end post queries and loops
 *
 * @class 		BFI_Controller
 * @version             2.0.5
 * @package		
 * @category	        Class
 * @author 		Bookingfor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'BFI_Controller' ) ) {
	/**
	 * BFI_Controller Class.
	 */
	class BFI_Controller {

		/**
		 * Constructor for the query class. Hooks in methods.
		 *
		 * @access public
		 */
		private $formlabel = null;
		public function __construct() {
			$this->formlabel = COM_BOOKINGFORCONNECTOR_FORM_KEY;
		}
		
		public function handle_request(){ 
			global $wp; 
			
			$task = isset($_REQUEST['task']) ? $_REQUEST['task'] :null ;

			if(!empty($task)){
				BFI()->define( "DONOTCACHEPAGE", true ); // Do not cache this page
				if (method_exists($this, $task)){
					$message = $this->$task();
					$simple = isset($_REQUEST['simple']) ? $_REQUEST['simple'] :null ;
					if(!empty($simple)){
						$this->send_text_response($message);  
					}else{
						$this->send_json_response($message);  
					}

				}
			}else{
				$this->send_response('Method not allowed'); 
			}
		} 

		protected function send_response($msg){ 
			$response['message'] = $msg; 
	// 		header('content-type: application/json; charset=utf-8'); 
			echo json_encode($response)."\n"; 
			die();
	//		exit; 
		} 
		protected function send_json_response($msg){ 
	// 		header('content-type: application/json; charset=utf-8'); 
			echo $msg."\n"; 
			die();
	//		exit; 
		} 
		protected function send_text_response($msg){ 
	// 		header('content-type: text/plain; charset=utf-8'); 
			echo $msg."\n"; 
			die();
	//		exit; 
		} 

//		protected function searchjson(){
//			bfi_setSessionFromSubmittedData();
//			$model = new BookingForConnectorModelSearch;
//			$items = $model->getItems(true,true);
//			echo $items;	
//		}
		protected function searchonselljson(){
			$model = new BookingForConnectorModelSearchOnSell;
			$items = $model->getItems(true,true);
			echo $items;	
		}
		
		protected function getMerchantGroups(){
			$return = BFCHelper::getTags("","1"); //getTags
			if (!empty($return)){
					$return = json_encode($return);
			}
			echo json_encode($return);      

		}

		protected function getResourceGroups(){
			$return = BFCHelper::getTags("","4"); //getTags
			if (!empty($return)){
					$return = json_encode($return);
			}
			echo json_encode($return);      

		}
		protected function getDiscountDetails(){
			$ids=$_REQUEST['discountIds'];
			$language=$_REQUEST['language'];
			$return = BFCHelper::getDiscountDetails($ids,$language);
			echo $return;	
		}

		protected function GetPhoneByMerchantId(){
			$merchantId = isset($_REQUEST['merchantid']) ? $_REQUEST['merchantid'] :0 ;
			$number = isset($_REQUEST['n']) ? $_REQUEST['n'] : '' ;
			$language = isset($_REQUEST['language']) ? $_REQUEST['language'] : '' ;
			$return = BFCHelper::GetPhoneByMerchantId($merchantId,$language,$number);
			echo $return;      
		}
		
		function GetServicesByIds(){
			$listsId=isset($_REQUEST['ids']) ? $_REQUEST['ids'] : '' ;  
			$language= isset($_REQUEST['language']) ? $_REQUEST['language'] : '' ;
			$return = BFCHelper::GetServicesByIds($listsId,$language);
			echo json_encode($return);      	
		}
		function GetResourcesByIds(){
			$listsId=isset($_REQUEST['resourcesId']) ? $_REQUEST['resourcesId'] : '' ;  
			$language= isset($_REQUEST['language']) ? $_REQUEST['language'] : '' ;
			$return = BFCHelper::GetResourcesByIds($listsId,$language);
			echo $return;      	
		}
		function GetMerchantsByIds(){
			$listsId=BFCHelper::getVar('merchantsId');
			$language=BFCHelper::getVar('language');
			$return = BFCHelper::getMerchantsByIds($listsId,$language);
			echo $return;      
		
		}
		function getResourcegroupByIds(){
			$listsId=BFCHelper::getVar('ids');
			$language=BFCHelper::getVar('language');
			$return = BFCHelper::getResourcegroupByIds($listsId,$language);
			echo $return;      
		
		}
		function GetTagsByIds(){
			$listsId=BFCHelper::getVar('ids');
			$language=BFCHelper::getVar('language');
			$viewContextType=BFCHelper::getVar('viewContextType');
			$return = BFCHelper::GetTagsByIds($listsId,$language,$viewContextType);
			if (!empty($return)){
				$return = json_encode($return);
			}

			echo $return;      
		
		}
		function GetAllTags(){
			$language=BFCHelper::getVar('language');
			$categoryIds=BFCHelper::getVar('categoryIds');
			$return = BFCHelper::getTags($language,$categoryIds);
			if (!empty($return)){
				$return = json_encode($return);
			}

			echo $return;      
		
		}
		function GetResourcesOnSellByIds(){
			$listsId=BFCHelper::getVar('resourcesId');
			$language=BFCHelper::getVar('language');
			$return = BFCHelper::GetResourcesOnSellByIds($listsId,$language);
			echo $return;      	
		}

		function GetContactFavoriteGroups(){
			$return = BFCHelper::GetContactFavoriteGroups();
			if (!empty($return)){
				$return = json_encode($return);
			}
			echo $return;      	
		}
		function AddItemToFavorites(){
			$itemId=BFCHelper::getVar('itemid');
			$itemType=BFCHelper::getVar('itemtypeid');
			$itemName=BFCHelper::getVar('itemname');
			$itemUrl=BFCHelper::getVar('itemurl');
			$groupId=BFCHelper::getVar('groupid');
			$startDate=BFCHelper::getVar('startdate');
			$endDate=BFCHelper::getVar('enddate');

			$return = BFCHelper::AddToFavorites($itemId, $itemType, $itemName, $itemUrl, $groupId, $startDate, $endDate);
			if (!empty($return)){
				$return = json_encode($return);
			}
			echo $return;      	
		}
		function RemoveItemFromFavorites(){
			$favoriteid=BFCHelper::getVar('favoriteid');
			$groupId=BFCHelper::getVar('groupid');
			$return = BFCHelper::RemoveItemToFavorites($favoriteid,$groupId);
			if (!empty($return)){
				$return = json_encode($return);
			}
			echo $return;      	
		}

		function AddFavoriteGroup(){
			$groupName=BFCHelper::getVar('name');
			$return = BFCHelper::AddFavoriteGroup($groupName);
			echo $return;      	
		}

		function getlanguagelinks(){
			$scope=BFCHelper::getVar('scope');
			$routing = [];
			$slupage="";
			switch ( $scope) {
				case 0 : //Merchant
					$slupage =  'merchantdetails';
					break;
				case 1 : //Product 
					$slupage =  'accommodationdetails';
					break;
				case 2 : //GroupProduct  
					$slupage =  'resourcegroupdetails';
					break;
				case 3 : //Event   
					$slupage =  'eventdetails';
					break;
				case 4 : //GenericPOI    
					$slupage =  'pointsofinterestdetails';
					break;
				case 5 : //GenericPOI    
					$slupage =  'searchavailability';
					break;
			}
				if(!empty($slupage)){

			// ciclo per lingua
					//wpml plugin
					if(defined( 'ICL_SITEPRESS_VERSION' ) && !ICL_PLUGIN_INACTIVE ){
						global $sitepress;
						$languages = $sitepress->get_active_languages();
						$currpageId =  bfi_get_page_id($slupage) ;
						if(!empty($currpageId)){					
							foreach ( $languages as $key => $language ) {
								$translPageid = apply_filters( 'translate_object_id', $currpageId, 'page', true, $key );
								$urlpage = get_permalink( $translPageid);
								if (!empty($urlpage)) {
									$routing[$key] = $urlpage . '[ID]' .'-'.'[NAME]';
								}
							}
						}
					}
					//end wpml plugin

					//polylang plugin
					if(defined( 'POLYLANG_VERSION' ) ){
						global $polylang;
						$languages = pll_languages_list();
						$languagenames = pll_languages_list(array( 'fields' => 'name'));
						$languagelocales = pll_languages_list(array( 'fields' => 'locale'));
						foreach ( $languages as $key => $language ) {
							$currpageId =  pll_get_post( bfi_get_page_id($slupage) , $language);
							if(!empty($currpageId)){					
								$urlpage = get_permalink( $currpageId);
								if (!empty($urlpage)) {
									$routing[$language] = $urlpage . '[ID]' .'-'.'[NAME]';
								}
							}
						}
					}
					// end polylang plugin
					//qTranslate plugin
					if(defined( 'QTS_VERSION' ) ){
						global $post;
						$languages = get_option('qtranslate_enabled_languages');
						$defaultLang = qtranxf_getLanguageDefault();
								
						$currpageId =  bfi_get_page_id($slupage) ;
									
						$defaultSlug = qts_get_slug($currpageId,$defaultLang);
						$urlpage = get_permalink( $currpageId);

						if(!empty($currpageId)){	
							foreach ( $languages as $key => $lang ) {
								$currSlug = qts_get_slug($currpageId,$lang);
								$page = get_page_by_path($currSlug);
								if (!empty($currSlug)) {
									$currUurlpage = str_replace($defaultSlug, $currSlug,$urlpage);			
									if (!empty($currUurlpage)) {
										$routing[$lang] = $currUurlpage . '/[ID]' .'-'.'[NAME]';
									}
									
								}
							}
						}
					}
					// end qTranslate plugin
				}

				$return = json_encode($routing);
				echo $return;      	

		}

		function GetPointOfInterestCategories(){
			$return = BFCHelper::GetPointOfInterestCategories();
			if (!empty($return)){
//				$currdetails_page = get_post( bfi_get_page_id( 'pointsofinterestdetails' ) );
//				$urlpage = get_permalink( $currdetails_page->ID );

//				$merchantdetails_page = get_post( bfi_get_page_id( 'merchantdetails' ) );
//				$url_merchant_page = get_permalink( $merchantdetails_page->ID );
//
//				$accommodationdetails_page = get_post( bfi_get_page_id( 'accommodationdetails' ) );
//				$url_accommodationdetails_page = get_permalink( $accommodationdetails_page->ID );

//				$eventdetails_page = get_post( bfi_get_page_id( 'eventdetails' ) );
//				$url_eventdetails_page = get_permalink( $eventdetails_page->ID );

				$urlpage = BFCHelper::GetPageUrl('pointsofinterestdetails');
				$url_merchant_page = BFCHelper::GetPageUrl('merchantdetails');
				$url_accommodationdetails_page = BFCHelper::GetPageUrl('accommodationdetails');
				$url_eventdetails_page = BFCHelper::GetPageUrl('eventdetails');

				foreach($return as $mrcKey => $obj){
					$itemRoute= $urlpage . $obj->PointOfInterestId.'-'.BFI()->seoUrl($obj->Name);
					$ImageUrl = BFI()->plugin_url() . "/assets/images/defaults/default-s6.jpeg";	
					if(!empty($obj->DefaultImg)){
						$ImageUrl = BFCHelper::getImageUrlResized('poi',$obj->DefaultImg, 'medium');
					}
					$obj->DefaultImg = $ImageUrl;

					if (isset($obj->PointOfInterestType) && !empty($obj->ReferrerObjectId) && !empty($obj->ReferrerObjectName) ) {
						switch ( $obj->PointOfInterestType ) {
							case 0 : //Merchant
								$itemRoute = $url_merchant_page . $obj->ReferrerObjectId  .'-'.BFI()->seoUrl($obj->ReferrerObjectName );
								break;
							case 1 : //Product 
								$itemRoute = $url_accommodationdetails_page.$obj->ReferrerObjectId .'-'.BFI()->seoUrl($obj->ReferrerObjectName );
								break;
							case 2 : //Event  
								$itemRoute = $url_eventdetails_page.$obj->ReferrerObjectId .'-'.BFI()->seoUrl($obj->ReferrerObjectName );
								break;
							case 3 : //EventDate   
								$itemRoute = $url_eventdetails_page.$obj->ReferrerObjectId .'-'.BFI()->seoUrl($obj->ReferrerObjectName );
								break;
							case 4 : //GenericPOI    
								break;
						}
					}
					$obj->route = $itemRoute;
				}

				$return = json_encode($return);
			}
			echo $return;      	
		}

		function GetPointsOfInterest(){
			$return = BFCHelper::GetPointsOfInterest();
			if (!empty($return)){
//				$currdetails_page = get_post( bfi_get_page_id( 'pointsofinterestdetails' ) );
//				$urlpage = get_permalink( $currdetails_page->ID );
				$urlpage = BFCHelper::GetPageUrl('pointsofinterestdetails');

//				$merchantdetails_page = get_post( bfi_get_page_id( 'merchantdetails' ) );
//				$url_merchant_page = get_permalink( $merchantdetails_page->ID );
				$url_merchant_page = BFCHelper::GetPageUrl('merchantdetails');

//				$accommodationdetails_page = get_post( bfi_get_page_id( 'accommodationdetails' ) );
//				$url_accommodationdetails_page = get_permalink( $accommodationdetails_page->ID );
				$url_accommodationdetails_page = BFCHelper::GetPageUrl('accommodationdetails');
//				$eventdetails_page = get_post( bfi_get_page_id( 'eventdetails' ) );
//				$url_eventdetails_page = get_permalink( $eventdetails_page->ID );
				$url_eventdetails_page = BFCHelper::GetPageUrl('eventdetails');

				foreach($return as $mrcKey => $obj){
					$itemRoute= $urlpage . $obj->PointOfInterestId.'-'.BFI()->seoUrl($obj->Name);
					$ImageUrl = BFI()->plugin_url() . "/assets/images/defaults/default-s6.jpeg";	
					
					if(!empty($obj->DefaultImg)){
						$ImageUrl = BFCHelper::getImageUrlResized('poi',$obj->DefaultImg, 'medium');
					}
					$obj->DefaultImg = $ImageUrl;
					if (isset($obj->PointOfInterestType) && !empty($obj->ReferrerObjectId) && !empty($obj->ReferrerObjectName) ) {
						switch ( $obj->PointOfInterestType ) {
							case 0 : //Merchant
								$itemRoute = $url_merchant_page . $obj->ReferrerObjectId  .'-'.BFI()->seoUrl($obj->ReferrerObjectName );
								break;
							case 1 : //Product 
								$itemRoute = $url_accommodationdetails_page.$obj->ReferrerObjectId .'-'.BFI()->seoUrl($obj->ReferrerObjectName );
								break;
							case 2 : //Event  
								$itemRoute = $url_eventdetails_page.$obj->ReferrerObjectId .'-'.BFI()->seoUrl($obj->ReferrerObjectName );
								break;
							case 3 : //EventDate   
								$itemRoute = $url_eventdetails_page.$obj->ReferrerObjectId .'-'.BFI()->seoUrl($obj->ReferrerObjectName );
								break;
							case 4 : //GenericPOI    
								break;
						}
					}
					$obj->route = $itemRoute;
				}

				$return = json_encode($return);
			}
			echo $return;      	
		}

		function GetMerchantsSlick(){
//			$app = JFactory::getApplication();
			$tags=BFCHelper::getVar('tags');
			$maxitems=BFCHelper::getVar('maxitems');
			$descmaxchars=BFCHelper::getVar('descmaxchars');
			$language=BFCHelper::getVar('language');
			$merchants = BFCHelper::getMerchantsByTagIdsExt(explode(',', $tags), 0, $maxitems);
			
			$return = null;
			$allMerchants = array();
//			$uriMerchant = COM_BOOKINGFORCONNECTOR_URIMERCHANTDETAILS;
//			$merchantImageUrl = Juri::root() . "components/com_bookingforconnector/assets/images/defaults/default-s6.jpeg";
			
			if (!empty($merchants)) {
//				$merchantdetails_page = get_post( bfi_get_page_id( 'merchantdetails' ) );
//				$url_merchant_page = get_permalink( $merchantdetails_page->ID );
				$url_merchant_page = BFCHelper::GetPageUrl('merchantdetails');

				$merchantImageUrl = BFI()->plugin_url() . "/assets/images/defaults/default-s6.jpeg";

			
				foreach($merchants as $mrcKey => $merchant){
					$obj = new stdClass;
		
					$hasSuperior = !empty($merchant->RatingSubValue);
					$rating = (int)$merchant->Rating;
					if ($rating>9 )
					{
						$rating = $rating/10;
						$hasSuperior = ($MerchantDetail->Rating%10)>0;
					} 

//						$currUriMerchant = $uriMerchant. '&merchantId=' . $merchant->MerchantId . ':' . BFCHelper::getSlug($merchant->Name);
					$currUriMerchant = $url_merchant_page . $merchant->MerchantId.'-'.BFI()->seoUrl($merchant->Name);
					$merchantDescription = BFCHelper::shorten_string(BFCHelper::getLanguage($merchant->Description, $language, null, array('bbcode'=>'bbcode', 'striptags'=>'striptags')), $descmaxchars);
					
					$routeMerchant= $url_merchant_page . $merchant->MerchantId.'-'.BFI()->seoUrl($merchant->Name);
//						$routeMerchant = JRoute::_($currUriMerchant);
					$currMerchantImageUrl = $merchantImageUrl;
					if(!empty($merchant->DefaultImg)){
						$currMerchantImageUrl = BFCHelper::getImageUrlResized('merchant',$merchant->DefaultImg, 'medium');
					}
					if(!empty($merchant->ImageData)) {
						$images = explode(",", $merchant->ImageData);
						$currMerchantImageUrl = BFCHelper::getImageUrlResized('merchant',$images[0], 'medium');
					}
					$merchantNameTrack =  BFCHelper::string_sanitize($merchant->Name);
					$merchantCategoryNameTrack = BFCHelper::string_sanitize($merchant->MainCategoryName);
					
					$obj->MerchantId = $merchant->MerchantId;
					$obj->Name = $merchant->Name;
					$obj->mrcKey = $mrcKey;
					$obj->hasSuperior = $hasSuperior;
					$obj->rating = $rating;
					$obj->routeMerchant = $routeMerchant;
					$obj->merchantDescription = $merchantDescription;
					$obj->currMerchantImageUrl = $currMerchantImageUrl;
					$obj->merchantNameTrack = $merchantNameTrack;
					$obj->merchantCategoryNameTrack = $merchantCategoryNameTrack;
					$obj->category = $merchant->MainCategoryName;
					$obj->brand = $merchant->Name;
					$obj->position = $mrcKey;
					array_push($allMerchants, $obj);
				}
				$return = json_encode($allMerchants);
			}
			echo $return;      
//			$app = JFactory::getApplication();
//			$app->close();
		}

		function GetResourcesSlick(){
            //			$app = JFactory::getApplication();
			$tags=BFCHelper::getVar('tags');
			$maxitems=BFCHelper::getVar('maxitems');
			$descmaxchars=BFCHelper::getVar('descmaxchars');
			$language=BFCHelper::getVar('language');
			$items = BFCHelper::getResourcesByTagIds($tags, 0, $maxitems);
		
			$return = null;
			$allResources = array();
			if (!empty($items)) {
					
//				$details_page = get_post( bfi_get_page_id( 'accommodationdetails' ) );
//				$url_page = get_permalink( $details_page->ID );								
				$url_page = BFCHelper::GetPageUrl('accommodationdetails');
				$url_resource_page_experience = BFCHelper::GetPageUrl('experiencedetails');

				$imageUrl = BFI()->plugin_url() . "/assets/images/defaults/default-s6.jpeg";

				foreach($items as $currKey => $currValue) {
					$ss = BFCHelper::getLanguage($currValue->Description, $language, null, array('bbcode'=>'bbcode', 'striptags'=>'striptags'));
                    $description = BFCHelper::shorten_string(BFCHelper::getLanguage($currValue->Description, $language, null, array('bbcode'=>'bbcode', 'striptags'=>'striptags')), $descmaxchars);
						
					$route = $url_page . $currValue->ResourceId .'-'.BFI()->seoUrl($currValue->Name);
					if (isset($currValue->ItemTypeId)) {
						switch ($currValue->ItemTypeId ) {
							case bfi_ItemType::Experience :
								$route  = $url_resource_page_experience.$currValue->ResourceId.'-'.BFI()->seoUrl($currValue->Name);
							break;
							default:      
								$route = $url_page . $currValue->ResourceId .'-'.BFI()->seoUrl($currValue->Name);
							break;
						}
					}
					$currImageUrl = $imageUrl;
					if(!empty($currValue->DefaultImg)){
						$currImageUrl = BFCHelper::getImageUrlResized('resources',$currValue->DefaultImg, 'medium');
					}
					if(!empty($currValue->ImageData)) {
						$images = explode(",", $currValue->ImageData);
						$currImageUrl = BFCHelper::getImageUrlResized('resources',$images[0], 'medium');
					}
					$nameTrack =  BFCHelper::string_sanitize($currValue->Name);
					$categoryNameTrack = BFCHelper::string_sanitize($currValue->MerchantCategoryName);

					$obj = new stdClass();
					$obj->Id = $currValue->ResourceId;
					$obj->Name = $currValue->Name;
					$obj->Description = $description;
					$obj->Route = $route;
                    $obj->ImageUrl = $currImageUrl;
                    $obj->category = $currValue->CategoryName;
					$obj->MerchantId = $currValue->MerchantId;
					$obj->MrcName = $currValue->MerchantName;
					$obj->MrcCategoryName = $currValue->MerchantCategoryName;
					$obj->currKey = $currKey;
					$obj->nameTrack = $nameTrack;
					$obj->categoryNameTrack = $categoryNameTrack;
					$obj->brand = $currValue->MerchantName;
					array_push($allResources, $obj);					
				}
				$return = json_encode((object)$allResources);
			}
			echo $return;      
            //			$app = JFactory::getApplication();
            //			$app->close();
        }
		function GetEventsSlick(){

//			$app = JFactory::getApplication();
			$tags=BFCHelper::getVar('tags');
			$merchantId=BFCHelper::getVar('merchantid');
			$maxitems=BFCHelper::getVar('maxitems');
			$descmaxchars=BFCHelper::getVar('descmaxchars');
			$language=BFCHelper::getVar('language');
			$events=null;
			if (!empty($tags)) {
				$events = BFCHelper::getEventsByTagIds($tags, 0, $maxitems);
			}else{
				$events = BFCHelper::getEventsByMerchantId($merchantId, 0, $maxitems);
			}
			$return = null;
			$allEvents = array();
			
			if (!empty($events)) {
//				$details_page = get_post( bfi_get_page_id( 'eventdetails' ) );
//				$url_page = get_permalink( $details_page->ID );								
				
				$url_page = BFCHelper::GetPageUrl('eventdetails');

				$imageUrl = BFI()->plugin_url() . "/assets/images/defaults/default-s6.jpeg";

			
				foreach($events as $mrcKey => $event){
					$obj = new stdClass;
		
					$description = BFCHelper::shorten_string(BFCHelper::getLanguage($event->Description, $language, null, array('bbcode'=>'bbcode', 'striptags'=>'striptags')), $descmaxchars);
					
					$route = $url_page . $event->EventId.'-'.BFI()->seoUrl($event->Name);
					$currImageUrl = $imageUrl;
					if(!empty($event->DefaultImg)){
						$currImageUrl = BFCHelper::getImageUrlResized('events',$event->DefaultImg, 'medium');
					}else{
						if(!empty($event->ImageData)) {
							$images = explode(",", $event->ImageData);
							$currImageUrl = BFCHelper::getImageUrlResized('events',$images[0], 'medium');
						}
					}
					$nameTrack =  BFCHelper::string_sanitize($event->Name);
					//TEMP:
//					$categoryNameTrack = BFCHelper::string_sanitize($event->MainCategoryName);
					$categoryNameTrack = BFCHelper::string_sanitize($event->CategoryNames);
					$startDate = BFCHelper::parseStringDateTime($event->StartDate);
					$endDate = BFCHelper::parseStringDateTime($event->EndDate);										

					$obj->Id = $event->EventId;
					$obj->Name = $event->Name;
					$obj->Key = $mrcKey;
					$obj->Route = $route;
					$obj->Description = $description;
					$obj->ImageUrl = $currImageUrl;
					$obj->nameTrack = $nameTrack;
					$obj->categoryNameTrack = $categoryNameTrack;
					//TEMP:
//					$obj->category = $event->MainCategoryName;
					$obj->category = $event->CategoryNames;
					$obj->brand = $event->Name;
					$obj->position = $mrcKey;
					$obj->StartDate = $startDate->format("YmdHis");
					$obj->EndDate = $endDate->format("YmdHis");
					$obj->StartDateLoc = date_i18n('D',$startDate->getTimestamp()) . " " . $startDate->format("d") . " " . date_i18n('M',$startDate->getTimestamp()) . " " . $startDate->format("Y");
					$obj->EndDateLoc = date_i18n('D',$endDate->getTimestamp()) . " " . $endDate->format("d") . " " . date_i18n('M',$endDate->getTimestamp()) . " " . $endDate->format("Y");
					$obj->Tags = $event->Tags;
					$obj->CategoryNames = $event->CategoryNames;

					array_push($allEvents, $obj);
				}
				$return = json_encode($allEvents);
			}
			echo $return;      
//			$app = JFactory::getApplication();
//			$app->close();
		}

		function GetPoiSlick(){

//			$app = JFactory::getApplication();
			$tags=BFCHelper::getVar('tags');
			$maxitems=BFCHelper::getVar('maxitems');
			$descmaxchars=BFCHelper::getVar('descmaxchars');
			$language=BFCHelper::getVar('language');
			$poi=null;
			if (!empty($tags)) {
				$poi = BFCHelper::getPointsofinterestsByTagIds($tags, 0, $maxitems);
			}
			$return = null;
			$allPoi = array();
			
			if (!empty($poi)) {
//				$details_page = get_post( bfi_get_page_id( 'pointsofinterestdetails' ) );
//				$url_page = get_permalink( $details_page->ID );								
				$url_page = BFCHelper::GetPageUrl('pointsofinterestdetails');
				
				$imageUrl = BFI()->plugin_url() . "/assets/images/defaults/default-s6.jpeg";

			
				foreach($poi as $mrcKey => $pointofinterest){
					$obj = new stdClass;
		
					$description = BFCHelper::shorten_string(BFCHelper::getLanguage($pointofinterest->Description, $language, null, array('bbcode'=>'bbcode', 'striptags'=>'striptags')), $descmaxchars);
					
					$route = $url_page . $pointofinterest->PointOfInterestId.'-'.BFI()->seoUrl($pointofinterest->Name);
					$currImageUrl = $imageUrl;
					if(!empty($pointofinterest->DefaultImg)){
						$currImageUrl = BFCHelper::getImageUrlResized('poi',$pointofinterest->DefaultImg, 'medium');
					}else{
						if(!empty($pointofinterest->ImageData)) {
							$images = explode(",", $pointofinterest->ImageData);
							$currImageUrl = BFCHelper::getImageUrlResized('poi',$images[0], 'medium');
						}
					}
					$nameTrack =  BFCHelper::string_sanitize($pointofinterest->Name);
					//TEMP:
//					$categoryNameTrack = BFCHelper::string_sanitize($pointofinterest->MainCategoryName);
					$categoryNameTrack = BFCHelper::string_sanitize($pointofinterest->CategoryNames);

					$obj->Id = $pointofinterest->PointOfInterestId;
					$obj->Name = $pointofinterest->Name;
					$obj->Key = $mrcKey;
					$obj->Route = $route;
					$obj->Description = $description;
					$obj->ImageUrl = $currImageUrl;
					$obj->nameTrack = $nameTrack;
					$obj->categoryNameTrack = $categoryNameTrack;
					//TEMP:
//					$obj->category = $pointofinterest->MainCategoryName;
					$obj->category = $pointofinterest->CategoryNames;
					$obj->brand = $pointofinterest->Name;
					$obj->position = $mrcKey;
					$obj->Tags = $pointofinterest->Tags;
					$obj->CategoryNames = $pointofinterest->CategoryNames;

					array_push($allPoi, $obj);
				}
				$return = json_encode($allPoi);
			}
			echo $return;      
//			$app = JFactory::getApplication();
//			$app->close();
		}

		
	function GetEventsBanner(){

//			$app = JFactory::getApplication();
			$checkinvar=BFCHelper::getVar('checkin');
			$checkoutvar=BFCHelper::getVar('checkout');
			$maxitems=BFCHelper::getVar('maxitems');
			$language=BFCHelper::getVar('language');
			$checkin = BFCHelper::parseStringDateTime($checkinvar,'YmdHis');
			$checkout = BFCHelper::parseStringDateTime($checkoutvar,'YmdHis');
			$checkin->modify('-7 day');
			$checkout->modify('+7 day');
			$events = BFCHelper::getEventsSearch($checkin, $checkout, $maxitems, $language);
			
			$return = null;
			$allEvents = array();
			$descmaxchars = 300;
			if (!empty($events)) {
//				$details_page = get_post( bfi_get_page_id( 'eventdetails' ) );
//				$url_page = get_permalink( $details_page->ID );	
				$url_page = BFCHelper::GetPageUrl('eventdetails');

				$imageUrl = BFI()->plugin_url() . "/assets/images/defaults/default-s6.jpeg";
				$url_page = rtrim($url_page, '/') . '/';
			
				foreach($events as $mrcKey => $event){
					$obj = new stdClass;
		
					$description = BFCHelper::shorten_string(BFCHelper::getLanguage($event->ShortDescription, $language, null, array('bbcode'=>'bbcode', 'striptags'=>'striptags')), $descmaxchars);
					
					$route = $url_page . $event->EventId.'-'.BFI()->seoUrl($event->Name);
					if(!empty($event->BannerLinkUrl)){
						$route = get_site_url() . $event->BannerLinkUrl;
						if (strpos($event->BannerLinkUrl, "http") === 0) {
							$route = $event->BannerLinkUrl;
						}

					}
					$currTheme = 0;
					$currImageUrl = $imageUrl;
					
					if(!empty($event->BannerImage)){
						$currImageUrl = BFCHelper::getImageUrlResized('eventbanners',$event->BannerImage, '');
						$currTheme = 1;
					}else{
						if(!empty($event->DefaultImg)){
							$currImageUrl = BFCHelper::getImageUrlResized('events',$event->DefaultImg, 'medium');
						}else{
							if(!empty($event->ImageData)) {
								$images = explode(",", $event->ImageData);
								$currImageUrl = BFCHelper::getImageUrlResized('events',$images[0], 'medium');
							}
						}				    
					}
					$currImageMobileUrl = $currImageUrl;
					if(!empty($event->SecondaryBannerImage)){
						$currImageMobileUrl = BFCHelper::getImageUrlResized('eventbanners',$event->SecondaryBannerImage, '');
						if ($currTheme ==0) {
						    $currImageUrl = $currImageMobileUrl;
						}
						$currTheme = 1;
					}

					$nameTrack =  BFCHelper::string_sanitize($event->Name);
					//TEMP:
//					$categoryNameTrack = BFCHelper::string_sanitize($event->MainCategoryName);
					$categoryNameTrack = BFCHelper::string_sanitize($event->CategoryNames);
					$startDate = BFCHelper::parseStringDateTime($event->StartDate);
					$endDate = BFCHelper::parseStringDateTime($event->EndDate);										

					$obj->Id = $event->EventId;
					$obj->Name = $event->Name;
					$obj->Key = $mrcKey;
					$obj->Route = $route;
					$obj->Description = $description;
					$obj->ImageUrl = $currImageUrl;
					$obj->ImageMobileUrl = $currImageMobileUrl;
					$obj->nameTrack = $nameTrack;
					$obj->categoryNameTrack = $categoryNameTrack;
					$obj->Theme = $currTheme;
					
					//TEMP:
//					$obj->category = $event->MainCategoryName;
					$obj->category = $event->CategoryNames;
					$obj->brand = $event->Name;
					$obj->position = $mrcKey;
					$obj->StartDate = $startDate->format("YmdHis");
					$obj->EndDate = $endDate->format("YmdHis");
					$obj->StartDateLoc = date_i18n('D',$startDate->getTimestamp()) . " " . $startDate->format("d") . " " . date_i18n('M',$startDate->getTimestamp()) . " " . $startDate->format("Y");
					$obj->EndDateLoc = date_i18n('D',$endDate->getTimestamp()) . " " . $endDate->format("d") . " " . date_i18n('M',$endDate->getTimestamp()) . " " . $endDate->format("Y");
					$obj->Tags = $event->Tags;
					$obj->CategoryNames = $event->CategoryNames;

					array_push($allEvents, $obj);
				}
				$return = json_encode($allEvents);
			}
			echo $return;      
//			$app = JFactory::getApplication();
//			$app->close();
		}

		function GetCrossSellResourcesSlick(){
            //			$app = JFactory::getApplication();
			$ids=BFCHelper::getVar('ids');
			$maxitems=BFCHelper::getVar('maxitems');
			$descmaxchars=BFCHelper::getVar('descmaxchars');
			$language=BFCHelper::getVar('language');
			$items = BFCHelper::GetCrossSellResourcesByIds($ids,$language, 0, $maxitems);
			$listNameAnalytics =  BFCHelper::getVar('lna','0');
			if(empty( $listNameAnalytics )){
				$listNameAnalytics = 13; // from cart
			}

			$return = null;
			$allResources = array();
			if (!empty($items)) {
					
//				$details_page = get_post( bfi_get_page_id( 'accommodationdetails' ) );
//				$url_page = get_permalink( $details_page->ID );	
				$url_page = BFCHelper::GetPageUrl('accommodationdetails');

				$imageUrl = BFI()->plugin_url() . "/assets/images/defaults/default-s6.jpeg";

				foreach($items as $currKey => $currValue) {
                    $description = BFCHelper::shorten_string(BFCHelper::getLanguage($currValue->Resource->Description, $language, null, array('bbcode'=>'bbcode', 'striptags'=>'striptags')), $descmaxchars);
						
					$route = $url_page . $currValue->Resource->ResourceId .'-'.BFI()->seoUrl($currValue->Resource->Name). "?fromsearch=1&lna=".$listNameAnalytics ;
					
					$currImageUrl = $imageUrl;
					if(!empty($currValue->Resource->DefaultImg)){
						$currImageUrl = BFCHelper::getImageUrlResized('resources',$currValue->Resource->DefaultImg, 'medium');
					}
					if(!empty($currValue->Resource->ImageData)) {
						$images = explode(",", $currValue->Resource->ImageData);
						$currImageUrl = BFCHelper::getImageUrlResized('resources',$images[0], 'medium');
					}
					$nameTrack =  BFCHelper::string_sanitize($currValue->Resource->Name);
//					$categoryNameTrack = BFCHelper::string_sanitize($currValue->Resource->MerchantCategoryName);
					$categoryNameTrack = "";

					$obj = new stdClass();
					$obj->Id = $currValue->Resource->ResourceId;
					$obj->Name = $currValue->Resource->Name;
					$obj->Description = $description;
					$obj->Route = $route;
                    $obj->ImageUrl = $currImageUrl;
                    $obj->category = ""; // $currValue->Resource->CategoryName;
					$obj->MerchantId = $currValue->Merchant->MerchantId;
					$obj->MrcName = $currValue->Merchant->Name;
					$obj->MrcCategoryName = ""; // $currValue->Resource->MerchantCategoryName;
					$obj->currKey = $currKey;
					$obj->nameTrack = $nameTrack;
					$obj->categoryNameTrack = $categoryNameTrack;
					$obj->brand = $currValue->Merchant->Name;
					array_push($allResources, $obj);					
				}
				$return = json_encode((object)$allResources);
			}
			echo $return;      
            //			$app = JFactory::getApplication();
            //			$app->close();
        }
		
		function sendRating(){
			global $user;
			$redirect= $_POST['Redirect'];
			$redirecterror =  $_POST['Redirecterror']; 
			$name= $_POST['name'];
			$city= $_POST['city'];
			$typologyid= $_POST['typologyid'];
			$nation= $_POST['nation']; // BFCHelper::getVar('nation');
			$email= $_POST['email'];
			$value1=$_POST['hfvalue1'];
			$value2= $_POST['hfvalue2'];
			$value3= $_POST['hfvalue3'];
			$value4= $_POST['hfvalue4'];
			$value5= $_POST['hfvalue5'];
			$totale= $_POST['hftotale'];
			$pregi= $_POST['pregi'];
			$difetti= $_POST['difetti'];
			$merchantId= $_POST['merchantid'];
			$label= $_POST['label'];
			$user = $user;
			$cultureCode = $GLOBALS['bfi_lang'];
			$userId=null;
			if (!empty($user) && $user->uid != 0) {
				$userId=$user->uid ;
			}
			$checkin= $_POST['checkin'];
			$resourceId= $_POST['resourceId'];
			$hashorder= $_POST['hashorder'];
			$orderId = null;

			if(BFCHelper::containsUrl($pregi) || BFCHelper::containsUrl($difetti) || !empty($_POST['Fax'])) {
				wp_redirect($redirecterror);
				exit;
			}

			$otherData = "optinemail:".(isset($_POST['optinemail'])?$_POST['optinemail']:'')
				."|"."optinmarketing:".(isset($formData['optinmarketing'])?$formData['optinmarketing']:'')
				."|"."optinprofiling:".(isset($formData['optinprofiling'])?$formData['optinprofiling']:'')
				."|"."optinprivacy:".(isset($formData['optinprivacy'])?$formData['optinprivacy']:'')
				."|".BFCHelper::bfi_get_clientdata();

			if (empty($resourceId)){
				$resourceId = null;
			}
			if (!empty($hashorder)){
				$orderId = BFCHelper::decrypt($hashorder);
				if (!is_numeric($orderId))
				{
					$orderId = null;
				}
			}

			$return = BFCHelper::setRating($name, $city, $typologyid, $email, $nation, $merchantId,$value1, $value2, $value3, $value4, $value5, $totale, $pregi, $difetti, $userId, $cultureCode, $checkin, $resourceId, $orderId, $label, $otherData);	
			if ($return < 1){
				$return ="";
				$redirect = $redirecterror;
			}
			if ($return >0 && !empty($redirect)){
				if(strpos($redirect, "?")=== false){
					$redirect = $redirect . '?';
				}else{
					$redirect = $redirect . '&';
				}
				$redirect = $redirect . 'act=Rating';
			}		
	//		if ($return < 1){
	//			set_transient( 'temporary_message', __( 'There was some issue posting your review. Please try back later.' ), 60*60*12 );
	//			$redirect = $redirecterror;
	//		}
	//		else {
	//			set_transient( 'temporary_message', __( 'Your review was succesfully posted.' ), 60*60*12 );		
	//		}
			wp_redirect($redirect);
			exit;
		}

		function getLocationZone(){
			$model = new BookingForConnectorModelMerchants;
			$items = $model->getItemsJson(true);
			echo $items;	
		}

		function listDateCheckin(){
			$resourceId = $_REQUEST['resourceId'];
			date_default_timezone_set('UTC');		
			$startDate = DateTime::createFromFormat('d/m/Y',date("d/m/Y"),new DateTimeZone('UTC'));
			$return = BFCHelper::getCheckInDates($resourceId ,$startDate);
			if (!empty($return)){
				$return = json_encode($return);
			}
			echo $return;      
		}

		function GetCheckInDatesTimeSlot(){
			$resourceId = $_REQUEST['resourceId'];
			$ci = $_REQUEST['checkin'];
			$checkin = DateTime::createFromFormat('Ymd',$ci,new DateTimeZone('UTC'));
			$return = BFCHelper::GetCheckInDatesTimeSlot($resourceId,$checkin);
//			if (!empty($return)){
//				$return = json_encode($return);
//			}
			echo $return;      
		}

		function GetCheckOutDatesDetailed(){
			$resourceId = $_REQUEST['resourceId'];
			$ci = $_REQUEST['checkin'];
			$checkin = DateTime::createFromFormat('Ymd',$ci,new DateTimeZone('UTC'));
			$return = BFCHelper::getCheckOutDates($resourceId ,$checkin);
			if (!empty($return)){
				$return = json_encode($return);
			}
			echo $return;      
		}
		function listCheckInDateHours(){
			$resourceId = $_REQUEST['resourceId'];
			if(isset($_REQUEST['checkin'])) {
				$checkin = DateTime::createFromFormat('YmdHis',$_REQUEST['checkin'],new DateTimeZone('UTC'));
			} else {
				$checkin = new DateTime();
			}
//			echo print_r(date_get_last_errors());
//			die();
			$return = BFCHelper::GetCheckInDatesPerTimes($resourceId ,$checkin, isset($_REQUEST["limittotdays"]) ? $_REQUEST["limittotdays"] : 365);
			
			if (!empty($return)){
					$return = json_encode($return);
			}
			
			echo $return;      
		}
		
		function GetCheckOutDatesPerTimes(){
			$resourceId = $_REQUEST['resourceId'];
			$ci = $_REQUEST['checkin'];
			$checkin = DateTime::createFromFormat('YmdHis',$ci,new DateTimeZone('UTC'));
//			echo print_r(date_get_last_errors());
//			die();
			$return = BFCHelper::GetCheckOutDatesPerTimes($resourceId ,$checkin);
			if (!empty($return)){
				$return = json_encode($return);
			}
			echo $return;      
		}

		function getCompleteRateplansStay(){
	//		$language=$_REQUEST['language'];
			$resourceId = isset($_REQUEST['resourceId'])?$_REQUEST['resourceId']:null;
			$ratePlanId=isset($_REQUEST['pricetype'])?$_REQUEST['pricetype']:null;
			$variationPlanId=isset($_REQUEST['variationPlanId'])?$_REQUEST['variationPlanId']:null;
			$getAllPaxConfigurations=isset($_REQUEST['getAllPaxConfigurations'])?$_REQUEST['getAllPaxConfigurations']:null;
			$selectablePrices=BFCHelper::getStayParam('extras');
			
			$resourceItemId = isset($_REQUEST['resourceItemId'])?$_REQUEST['resourceItemId']:null;

			$pricetype=isset($_REQUEST['pricetype'])?$_REQUEST['pricetype']:null;

			$selectablePrices=isset($_REQUEST['selectableprices'])?$_REQUEST['selectableprices']:$selectablePrices;
			
			$availabilitytype=isset($_REQUEST['availabilitytype'])?$_REQUEST['availabilitytype']:(isset($_REQUEST['ProductAvailabilityType'])?$_REQUEST['ProductAvailabilityType']:(isset($_REQUEST['AvailabilityType'])?$_REQUEST['AvailabilityType']:null)); 

			$checkIn =  BFCHelper::getStayParam('checkin',null);
			$duration  =  BFCHelper::getStayParam('duration');
			$timeSlotId	= '';								

			if ($availabilitytype == 0 || $availabilitytype ==1 ) // product TimePeriod
			{
				$checkIn->setTime(0,0,0);
			}
			if ($availabilitytype ==2 ) // product TimePeriod
			{
				$duration = isset($_REQUEST['duration'])?$_REQUEST['duration']:null; 
//				$checkIn = DateTime::createFromFormat("YmdHis", $_REQUEST['CheckInTime'],new DateTimeZone('UTC'));
			}
			if ($availabilitytype ==3 ) // product TimeSlot
			{
				$duration = isset($_REQUEST['duration'])?$_REQUEST['duration']:null; 
				$timeSlotId = isset($_REQUEST['timeSlotId'])?$_REQUEST['timeSlotId']:''; 
//				$checkIn = DateTime::createFromFormat("YmdHis", $_REQUEST['CheckInTime'],new DateTimeZone('UTC'));
			}
			if ($availabilitytype == 0 ) 
			{
				$duration +=1;
			}

			if(!isset($duration)){
				$duration =  BFCHelper::$defaultDaysSpan;
			}
			$packages  =  BFCHelper::getStayParam('packages');
			$paxages =  BFCHelper::getStayParam('paxages',null);
			$return = null;
						
			$return = BFCHelper::GetCompleteRatePlansStayWP($resourceId,$checkIn,$duration,$paxages,$selectablePrices,$packages,$pricetype,$ratePlanId,$variationPlanId,null,null,false,$resourceItemId,$timeSlotId,$getAllPaxConfigurations);
			
			if (!empty($return)){
					$return = json_encode($return);
			}
			echo $return;      
		}

		function getListCheckInDayPerTimes(){
	//		$language=$_REQUEST['language'];
			$resourceId = isset($_REQUEST['resourceId'])?$_REQUEST['resourceId']:null;
			$fromDate=isset($_REQUEST['fromDate'])?$_REQUEST['fromDate']:null;
			$limitTotDays=isset($_REQUEST['limitTotDays'])?$_REQUEST['limitTotDays']:null;

			$return = null;
			$return = BFCHelper::GetListCheckInDayPerTimes($resourceId,$fromDate,$limitTotDays);
			if (!empty($return)){
					$return = json_encode($return);
			}
			echo $return;      
		}

		function sendOnSellrequest(){
			$formData = $_POST['form'];

			$customer = BFCHelper::getCustomerData($formData);
			$suggestedStay = null;
			$redirect = $formData['Redirect'];
			$redirecterror = $formData['Redirecterror'];
			
			$userNotes = $formData['note'];
			if(BFCHelper::containsUrl($userNotes) || !empty($formData['Fax'])) {
				wp_redirect($redirecterror);
				exit;
			}

			$otherData = array();
			if (!empty($formData['resourceId']))  {
					$sStay = array(
								'UnitId' => $formData['resourceId']
							);

					$suggestedStay = new stdClass(); 
					foreach ($sStay as $key => $value) 
					{ 
						$suggestedStay->$key = $value; 
					}
					$otherData["UnitId:"] = "UnitId:" . $formData['resourceId'];
				}
			if (!empty($formData['pageurl']))  {
					$otherData["pageurl:"] = "pageurl:" . $formData['pageurl'];
			}
			if (!empty($formData['title']))  {
					$otherData["title:"] = "title:" . $formData['title'];
			}
			if (!empty($formData['resourceId']))  {
					$otherData["onsellunitid:"] = "onsellunitid:" . $formData['resourceId'];
			}
			if (!empty($formData['accettazione']))  {
					$otherData["accettazione:"] = "accettazione:" . BFCHelper::getOptionsFromSelect($formData,'accettazione');
			}
			
			if (!empty($formData['optinemail']))  {
					$otherData["optinemail:"] = "optinemail:" . BFCHelper::getOptionsFromSelect($formData,'optinemail');
			}

						$otherData["clientdata:"] = BFCHelper::bfi_get_clientdata();
					

			$orderData =  BFCHelper::prepareOrderData($formData, $customer, $suggestedStay, implode("|",$otherData), null);

			$orderData['processOrder'] = true;
			$orderData['label'] = $this->formlabel;

			$return = BFCHelper::setInfoRequest(
						$orderData['customerData'], 
						$orderData['suggestedStay'],
						$orderData['otherNoteData'], 
						$orderData['merchantId'], 
						$orderData['orderType'], 
						$orderData['userNotes'], 
						$orderData['label'], 
						$orderData['cultureCode'],
						$orderData['processOrder']
						);	

			if (empty($return)){
				$return ="";
				$redirect = $redirecterror;
			}
			if (!empty($return)){

					if(strpos($redirect, "?")=== false){
						$redirect = $redirect . '?';
					}else{
						$redirect = $redirect . '&';
					}
					$redirect = $redirect . 'act=ContactSale&orderid=' . $return->OrderId 
					 . '&merchantid=' . $return->MerchantId 
					 . '&OrderType=' . $return->OrderType 
					 . '&OrderTypeId=' . $return->OrderTypeId
					 . '&RequestType=' . $return->RequestType
					;
			}
	//		echo json_encode($return);      
	//		$app = JFactory::getApplication();
	//		if (empty($redirect)){
	//			echo json_encode($return);      
	//		}else{
	//			$app->redirect($redirect, false);
	//		}
	//		$app->close();
			wp_redirect($redirect);
			exit;

		}
		function sendInforequest(){
			$formData = $_POST['form'];
	//		JPluginHelper::importPlugin('captcha');
	//		$dispatcher = JDispatcher::getInstance();
	//		$result = $dispatcher->trigger('onCheckAnswer',$formData['recaptcha_response_field']);
	//		if(!$result[0]){
	//			die('Invalid Captcha Code');
	//		}else{


			$customer = BFCHelper::getCustomerData($formData);
			$suggestedStay = null;
			$redirect = $formData['Redirect'];
			$redirecterror = $formData['Redirecterror'];
			$userNotes = $formData['note'];
			if(BFCHelper::containsUrl($userNotes) || !empty($formData['Fax'])) {
				wp_redirect($redirecterror);
				exit;
			}
			// create otherData (string)
					$otherData = "persone:".BFCHelper::getOptionsFromSelect($formData,'Totpersons')
						."|"."accettazione:".BFCHelper::getOptionsFromSelect($formData,'accettazione')
						."|"."optinemail:".(isset($formData['optinemail'])?$formData['optinemail']:'')
						."|"."optinmarketing:".(isset($formData['optinmarketing'])?$formData['optinmarketing']:'')
						."|"."optinprofiling:".(isset($formData['optinprofiling'])?$formData['optinprofiling']:'')
						."|"."optinprivacy:".(isset($formData['optinprivacy'])?$formData['optinprivacy']:'')
						."|".BFCHelper::bfi_get_clientdata();
			// create SuggestedStay
			$startDate = null;
			$endDate = null;

			if ($_POST['checkin'] != null && $_POST['checkout'] != null) {
				$formData['CheckIn'] = $_POST['checkin'];
				$formData['CheckOut'] = $_POST['checkout'];
			}

					
					if (!empty($formData['CheckIn']) && !empty($formData['CheckOut'])) {
					$startDate = DateTime::createFromFormat('d/m/Y',$formData['CheckIn'],new DateTimeZone('UTC'));
					$endDate = DateTime::createFromFormat('d/m/Y',$formData['CheckOut'],new DateTimeZone('UTC'));
						$sStay = array(
									'CheckIn' => DateTime::createFromFormat('d/m/Y',$formData['CheckIn'],new DateTimeZone('UTC'))->format('Y-m-d\TH:i:sO'),
									'CheckOut' => DateTime::createFromFormat('d/m/Y',$formData['CheckOut'],new DateTimeZone('UTC'))->format('Y-m-d\TH:i:sO'),
									'UnitId' => $formData['resourceId']
								);

						$suggestedStay = new stdClass(); 
						foreach ($sStay as $key => $value) 
						{ 
							$suggestedStay->$key = $value; 
						}
						$otherData .= "|" . "CheckIn:" . DateTime::createFromFormat('d/m/Y',$formData['CheckIn'],new DateTimeZone('UTC'))->format('Y-m-d') . "|" ."CheckOut:" . DateTime::createFromFormat('d/m/Y',$formData['CheckOut'])->format('Y-m-d') . "|" . "UnitId:" . $formData['resourceId'];
					}else{
				if (!empty($formData['resourceId']))  {
						$sStay = array(
									'UnitId' => $formData['resourceId']
								);

						$suggestedStay = new stdClass(); 
						foreach ($sStay as $key => $value) 
						{ 
							$suggestedStay->$key = $value; 
						}
						$otherData .= "|" . "UnitId:" . $formData['resourceId'];
					}
				}
						
			$orderData =  BFCHelper::prepareOrderData($formData, $customer, $suggestedStay, $otherData, null);

			$orderData['processOrder'] = true;
			$orderData['label'] = $this->formlabel;

			$return = BFCHelper::setInfoRequest(
						$orderData['customerData'], 
						$orderData['suggestedStay'],
						$orderData['otherNoteData'], 
						$orderData['merchantId'], 
						$orderData['orderType'], 
						$orderData['userNotes'], 
						$orderData['label'], 
						$orderData['cultureCode'],
						$orderData['processOrder']
						);	
	//}

			if (empty($return)){
				$return ="";
				$redirect = $redirecterror;
			}else{
				
				if(strpos($redirect, "?")=== false){
					$redirect = $redirect . '?';
				}else{
					$redirect = $redirect . '&';
				}

				$redirect = $redirect . 'act=ContactResource&orderid=' . $return->OrderId 
						 . '&merchantid=' . $return->MerchantId 
						 . '&OrderType=' . $return->OrderType 
						 . '&OrderTypeId=' . $return->OrderTypeId
						 . '&RequestType=' . $return->RequestType
						 . '&numAdults=' . $numAdults
						;
					if (!empty($startDate)){
						$redirect = $redirect . '&startDate=' . $startDate->format('Y-m-d')
						 . '&endDate=' . $endDate->format('Y-m-d')
						;
					}
			}
	//		echo json_encode($return);      
	//		$app = JFactory::getApplication();
	//		$app->redirect($redirect, false);
	//		$app->close();
			wp_redirect($redirect);
			exit;

		}
		function sendContact(){
			$formData = $_POST['form'];
	//		$checkrecaptcha = true;
	//		JPluginHelper::importPlugin('captcha');
	//		$dispatcher = JDispatcher::getInstance();
	//		if (!empty($formData['recaptcha_response_field'])) {
	//			$result = $dispatcher->trigger('onCheckAnswer',$formData['recaptcha_response_field']);
	//			if(!$result[0]){
	//				$checkrecaptcha = false;
	//				//die('Invalid Captcha Code');
	//			}
	//		}
	//		if($checkrecaptcha){
			$customer = BFCHelper::getCustomerData($formData);
			$suggestedStay = null;
			$redirect = $formData['Redirect'];
			$redirecterror = $formData['Redirecterror'];
			$userNotes = $formData['note'];
			if(BFCHelper::containsUrl($userNotes) || !empty($formData['Fax'])) {
				wp_redirect($redirecterror);
				exit;
			}
	
			// create otherData (string)
			$numAdults = BFCHelper::getOptionsFromSelect($formData,'Totpersons');
			
			$otherData = "persone:".$numAdults
				."|"."accettazione:".BFCHelper::getOptionsFromSelect($formData,'accettazione')
				."|"."optinemail:".(isset($formData['optinemail'])?$formData['optinemail']:'')
				."|"."optinmarketing:".(isset($formData['optinmarketing'])?$formData['optinmarketing']:'')
				."|"."optinprofiling:".(isset($formData['optinprofiling'])?$formData['optinprofiling']:'')
				."|"."optinprivacy:".(isset($formData['optinprivacy'])?$formData['optinprivacy']:'')
				."|".BFCHelper::bfi_get_clientdata();
			// create SuggestedStay
			$startDate = null;
			$endDate = null;


			if ($_POST['checkin'] != null && $_POST['checkout'] != null) {
				$formData['CheckIn'] = $_POST['checkin'];
				$formData['CheckOut'] = $_POST['checkout'];
			}


			if ($formData['CheckIn'] != null && $formData['CheckOut'] != null) {
				
				$startDate = DateTime::createFromFormat('d/m/Y',$formData['CheckIn'],new DateTimeZone('UTC'));
				$endDate = DateTime::createFromFormat('d/m/Y',$formData['CheckOut'],new DateTimeZone('UTC'));
				
				$sStay = array(
							'CheckIn' => DateTime::createFromFormat('d/m/Y',$formData['CheckIn'],new DateTimeZone('UTC'))->format('Y-m-d\TH:i:sO'),
							'CheckOut' => DateTime::createFromFormat('d/m/Y',$formData['CheckOut'],new DateTimeZone('UTC'))->format('Y-m-d\TH:i:sO')
						);

				$suggestedStay = new stdClass(); 
				foreach ($sStay as $key => $value) 
				{ 
					$suggestedStay->$key = $value; 
				}
				$otherData .= "|" . "CheckIn:" . $startDate->format('Y-m-d') ."|" ."CheckOut:" . $endDate->format('Y-m-d');
			}
						
			$orderData =  BFCHelper::prepareOrderData($formData, $customer, $suggestedStay, $otherData, null);

			$orderData['processOrder'] = true;
			$orderData['label'] = $this->formlabel;
			if(!empty( $formData['otherDetails'] )){
				$orderData['userNotes'] = "Interessato a:" . $formData['otherDetails'] ."\n". $orderData['userNotes'];
			}

			$return = BFCHelper::setInfoRequest(
						$orderData['customerData'], 
						$orderData['suggestedStay'],
						$orderData['otherNoteData'], 
						$orderData['merchantId'], 
						$orderData['orderType'], 
						$orderData['userNotes'], 
						$orderData['label'], 
						$orderData['cultureCode'],
						$orderData['processOrder']
						);	
			if (empty($return)){
				$return ="";
				$redirect = $redirecterror;
			}

			if (!empty($return)){

					if(strpos($redirect, "?")=== false){
						$redirect = $redirect . '?';
					}else{
						$redirect = $redirect . '&';
					}
					$redirect = $redirect . 'act=ContactMerchant&orderid=' . $return->OrderId 
					 . '&merchantid=' . $return->MerchantId 
					 . '&OrderType=' . $return->OrderType 
					 . '&OrderTypeId=' . $return->OrderTypeId
					 . '&RequestType=' . $return->RequestType
					 . '&numAdults=' . $numAdults
					;
				if (!empty($startDate)){
					$redirect = $redirect . '&startDate=' . $startDate->format('Y-m-d')
					 . '&endDate=' . $endDate->format('Y-m-d')
					;
				}
			}

	//		echo json_encode($return);      
	//		$app = JFactory::getApplication();
	////		$app->redirect($redirect, false);
	//		if (empty($redirect)){
	//			echo json_encode($return);      
	//		}else{
	//			$app->redirect($redirect, false);
	//		}
	//		$app->close();
	//		}
			wp_redirect($redirect);
			exit;
		}

		//new send orders...
		function sendOrders(){
			$formData = $_POST['form'];
			if(empty($formData)){
			}
			$language = isset($_REQUEST['language']) ? $_REQUEST['language'] : '' ;
			
			$customer = BFCHelper::getCustomerData($formData);

			$userNotes = $formData['note'];
			$token = isset($_POST['token']) ? $_POST['token'] : '' ;  
			$spamRecaptcha = false;
			if( !empty(COM_BOOKINGFORCONNECTOR_GOOGLE_GOOGLERECAPTCHAKEY) && !empty(COM_BOOKINGFORCONNECTOR_GOOGLE_GOOGLERECAPTCHASECRETKEY)  ){
				switch (COM_BOOKINGFORCONNECTOR_GOOGLE_GOOGLERECAPTCHAVERSION) {
					case "V3":
						// call curl to POST request
						$token = $_POST['g-recaptcha-response'];
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL,"https://www.google.com/recaptcha/api/siteverify");
						curl_setopt($ch, CURLOPT_POST, 1);
						curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('secret' => COM_BOOKINGFORCONNECTOR_GOOGLE_GOOGLERECAPTCHASECRETKEY, 'response' => $token)));
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						$response = curl_exec($ch);
						curl_close($ch);
						$arrResponse = json_decode($response, true);
						 
						// verify the response
						if($arrResponse["success"] == '1' && $arrResponse["score"] >= 0.5) {
							// valid submission
							// go ahead and do necessary stuff
						} else {
							// spam submission
							// show error message
							$spamRecaptcha = true;
						}
						break;
					default: //V2
				}
				
			}
			if($spamRecaptcha && BFCHelper::containsUrl($userNotes) || !empty($formData['Fax'])) {
				wp_redirect($redirecterror);
				exit;
			}

			$userNotes = $formData['note'];
			$cultureCode = $formData['cultureCode'];
			$merchantId = $formData['merchantId'];
			$orderType = $formData['orderType'];
			$label = $formData['label'];
			$label = $this->formlabel;
			$OrderJson = $formData['hdnOrderData'];
			$bookingTypeSelected = $formData['bookingtypeselected'];

	//		$suggestedStays =  BFCHelper::CreateOrder($OrderJson,$cultureCode,$bookingTypeSelected);
			$suggestedStays = null;

	//		$listCartorderid = array();
	//		// recupero tutti i cartorderid per la cancellazione del carrello
	//			$orderModel = json_decode(stripslashes($OrderJson));
	//            if ($orderModel->Resources != null && count($orderModel->Resources) > 0 )
	//            {
	//                foreach ($orderModel->Resources as $resource)
	//                {
	//					if(!empty($resource->CartOrderId)){
	//						$listCartorderid[] = $resource->CartOrderId;
	//					}
	//				}
	//			}
	//		$listCartorderidstr = implode(",",$listCartorderid);
			
	//		$suggestedStay = json_decode(stripslashes($formData['staysuggested']));
	//		$req = json_decode(stripslashes($formData['stayrequest']), true);

			$redirect = $formData['Redirect'];
			$redirecterror = $formData['Redirecterror'];
			$isgateway = $formData['isgateway'];


	//		$otherData = "paxages:". str_replace("]", "" ,str_replace("[", "" , $req['paxages'] ))
	//					."|"."checkin_eta_hour:".$formData['checkin_eta_hour'];
			$otherData = "checkin_eta_hour:".$formData['checkin_eta_hour']
						."|"."optinemail:".(isset($formData['optinemail'])?$formData['optinemail']:'')
						."|"."optinmarketing:".(isset($formData['optinmarketing'])?$formData['optinmarketing']:'')
						."|"."optinprofiling:".(isset($formData['optinprofiling'])?$formData['optinprofiling']:'')
						."|"."optinprivacy:".(isset($formData['optinprivacy'])?$formData['optinprivacy']:'')
						."|".BFCHelper::bfi_get_clientdata();
	//		$customerDatas = array($customerData);

			$ccdata = null;
	//		if (BFCHelper::canAcquireCCData($formData)) { 
			$ccdata = BFCHelper::getCCardData($formData);
			if (!empty($ccdata)) {
				$ccdata = BFCHelper::encrypt(json_encode($ccdata),$label.$customer['Email'] );
			}
	//			}
			$crewData = BFCHelper::getCrewData(isset( $_POST['crew']) ?  $_POST['crew'] : null );
			
			$orderData = array(
					'customerData' =>  array($customer),
					'crewData' => $crewData,
					'suggestedStay' =>$suggestedStays,
					'creditCardData' => $ccdata,
					'otherNoteData' => $otherData,
					'merchantId' => $merchantId,
					'orderType' => $orderType,
					'userNotes' => $userNotes,
					'label' => $label,
					'cultureCode' => $cultureCode,
					);

	//		$orderData =  BFCHelper::prepareOrderData($formData, $customer, $suggestedStay, $otherData, $ccdata);
			$orderData['pricetype'] = "";
			if(isset($formData['pricetype'])){
				$orderData['pricetype'] = $formData['pricetype'];
			}
			$orderData['label'] = $this->formlabel;;
	//		$orderData['checkin_eta_hour'] = $formData['checkin_eta_hour'];
			$orderData['merchantBookingTypeId'] = $formData['bookingtypeselected'];
			$orderData['policyId'] = $formData['policyId'];

			$processOrder = null;
			if(!empty($isgateway) && ($isgateway =="true" ||$isgateway =="1")){
				$processOrder=false;
			}

//			$tmpUserId = BFCHelper::bfi_get_userId();
//			$currCart = BFCHelper::GetCartByExternalUser($tmpUserId, $language, true);


			$order = BFCHelper::setOrder(
					$orderData['customerData'], 
					$orderData['suggestedStay'], 
					$orderData['creditCardData'], 
					$orderData['otherNoteData'], 
					$orderData['merchantId'], 
					$orderData['orderType'], 
					$orderData['userNotes'], 
					$orderData['label'], 
					$orderData['cultureCode'], 
					$processOrder,
					$orderData['pricetype'],
					$orderData['merchantBookingTypeId'],
					$orderData['policyId'],
					$orderData['crewData']
					);
			if (empty($order)){
				$order ="";
				$redirect = $redirecterror;
			}
			if (!empty($order)){
				
				$orderPayment = BFCHelper::getLastOrderPayment($order->OrderId);
				if (!empty($orderPayment) && $orderPayment->PaymentType == 3 && $orderPayment->Status == 4) {
					$url_payment_page = BFCHelper::GetPageUrl('payment');
					$redirect = $url_payment_page . $order->OrderId;
				}else{
					$numAdults = 0;

					$act = "OrderResource";
					if(!empty($order->OrderType) && strtolower($order->OrderType) =="b"){
						$act = "QuoteRequest";
					}

					$startDate = DateTime::createFromFormat('Y-m-d',BFCHelper::parseJsonDate($order->StartDate,'Y-m-d'),new DateTimeZone('UTC'));
					$endDate = DateTime::createFromFormat('Y-m-d',BFCHelper::parseJsonDate($order->EndDate,'Y-m-d'),new DateTimeZone('UTC'));
					
					if(strpos($redirect, "?")=== false){
						$redirect = $redirect . '?';
					}else{
						$redirect = $redirect . '&';
					}

					$redirect = $redirect . 'act=' . $act  
					 . '&orderid=' . $order->OrderId 
					 . (!empty($order->MerchantId )?'&merchantid=' . $order->MerchantId :"") 
					 . '&OrderType=' . $order->OrderType 
					 . '&OrderTypeId=' . $order->OrderTypeId 
					 . '&totalamount=' . ($order->TotalAmount *100)
					 . '&startDate=' . $startDate->format('Y-m-d')
					 . '&endDate=' . $endDate->format('Y-m-d')
					 . (!empty($numAdults)?'&numAdults=' . $numAdults:"")
					;
				}
			}
			wp_redirect($redirect);
			exit;

		}
		
		function addToCart(){
			//clear session data from request
			BFCHelper::setSession('hdnBookingType', '', 'bfi-cart');
			BFCHelper::setSession('hdnOrderData', '', 'bfi-cart');
			$OrderJson = stripslashes(BFCHelper::getVar("hdnOrderData"));
			$bfiResetCart = (BFCHelper::getVar("bfiResetCart","0"));
			$language = isset($_REQUEST['language']) ? $_REQUEST['language'] : '' ;
			$return = null;
			if(!empty($OrderJson)){
				$tmpUserId = BFCHelper::bfi_get_userId();
				$model = new BookingForConnectorModelOrders;
	//			$currCart = BFCHelper::AddToCartByExternalUser($tmpUserId, $language, $OrderJson, $bfiResetCart);
				$currCart = BFCHelper::AddToCart($tmpUserId, $language, $OrderJson, $bfiResetCart);
				if(!empty($currCart)){
					$return = json_encode($currCart);
				}
			}
			echo $return;      
		}
		function DeleteFromCart(){
			$return = null;
			$CartOrderId = stripslashes(BFCHelper::getVar("bfi_CartOrderId"));
			$language = isset($_REQUEST['language']) ? $_REQUEST['language'] : '' ;
//			$cartdetails_page = get_post( bfi_get_page_id( 'cartdetails' ) );
//			$url_cart_page = get_permalink( $cartdetails_page->ID );
			$url_cart_page = BFCHelper::GetPageUrl('cartdetails');

			$usessl = COM_BOOKINGFORCONNECTOR_USESSL;
			if($usessl){
				$url_cart_page = str_replace( 'http:', 'https:', $url_cart_page );
			}

			if(!empty($CartOrderId)){
				$tmpUserId = BFCHelper::bfi_get_userId();
				$currCart = BFCHelper::DeleteFromCartByExternalUser($tmpUserId, $language, $CartOrderId);
				wp_redirect($url_cart_page);
				exit;

	//			if(!empty($currCart)){
	//				$return = json_encode($currCart);
	//			}
	//		}else{
	//			$resources = BFCHelper::getSession('hdnOrderData', '', 'bfi-cart');
	//			if(!empty($resources)){
	//				$resources = json_decode($resources,true);
	//				unset($resources[$CartOrderId]);
	//				$resources = array_values($resources);
	//				$currResourcesStr = json_encode($resources);
	//				BFCHelper::setSession('hdnOrderData', $currResourcesStr, 'bfi-cart');
	//			}
	//			wp_redirect($url_cart_page);
	//			exit;
			
			}
			$base_url = get_site_url();
			if(defined('ICL_LANGUAGE_CODE') &&  class_exists('SitePress')){
					global $sitepress;
					if($sitepress->get_current_language() != $sitepress->get_default_language()){
						$base_url = "/" .ICL_LANGUAGE_CODE;
					}
			}
			wp_redirect($base_url);
			exit;
		}

		function addDiscountCodesToCart(){		
			$bficoupons = BFCHelper::getVar("bficoupons");
			$language = BFCHelper::getVar("bfilanguage");
	//		$redirect = JRoute::_('index.php?option=com_bookingforconnector&view=cart');
//			$cartdetails_page = get_post( bfi_get_page_id( 'cartdetails' ) );
//			$url_cart_page = get_permalink( $cartdetails_page->ID );
			$url_cart_page = BFCHelper::GetPageUrl('cartdetails');
			
			$cartdetails_page = BFCHelper::GetPageUrl('cartdetails');

			$usessl = COM_BOOKINGFORCONNECTOR_USESSL;
			if($usessl){
				$url_cart_page = str_replace( 'http:', 'https:', $url_cart_page );
			}
			if(!empty($bficoupons)){
				$bficoupons = str_replace(" ","",$bficoupons);
				$tmpUserId = BFCHelper::bfi_get_userId();
				$currCart = BFCHelper::AddDiscountCodesCartByExternalUser($tmpUserId, $language, $bficoupons);
				$url_cart_page .= '?cpn='.$bficoupons;
			}
			wp_redirect($url_cart_page);
			exit;
	//		$app = JFactory::getApplication();
	//		$app->redirect($redirect, false);
	//		$app->close();
		}

	 function SearchByText() {
			$return = '[]';
			$term = stripslashes(BFCHelper::getVar("term"));
			$resultClasses = stripslashes(BFCHelper::getVar("resultClasses"));
			$maxresults = stripslashes(BFCHelper::getVar("maxresults"));
			$minMatchingPercentage = stripslashes(BFCHelper::getVar("minMatchingPercentage"));
			if(!isset($maxresults) || empty($maxresults)) {
				$maxresults = 5;
			} else {
				$maxresults = (int)$maxresults;
			}
			$language = isset($_REQUEST['cultureCode']) ? $_REQUEST['cultureCode'] : '' ;
			if(!empty($term)) {
				$model = new BookingForConnectorModelSearch;
				$results = $model->SearchByText($term, $language, $maxresults, $minMatchingPercentage, $resultClasses);
				if(!empty($results)){
					$return = json_encode($results);
				}
			}
			echo $return;
		}

		function getmarketinfomerchant(){
			$resource_id=BFCHelper::getVar('merchantId');
			$language=BFCHelper::getVar('language');
			$merchant = BFCHelper::getMerchantFromServicebyId($resource_id);
			$merchantName = BFCHelper::getLanguage($merchant->Name, $language, null, array('ln2br'=>'ln2br', 'striptags'=>'striptags')); 
			$indirizzo = isset($merchant->AddressData->Address)?$merchant->AddressData->Address:"";
			$cap = isset($merchant->AddressData->ZipCode)?$merchant->AddressData->ZipCode:""; 
			$comune = isset($merchant->AddressData->CityName)?$merchant->AddressData->CityName:"";
			$stato = isset($merchant->AddressData->StateName)?$merchant->AddressData->StateName:"";
			
	//		$db   = JFactory::getDBO();
	//		$uri  = 'index.php?option=com_bookingforconnector&view=merchantdetails';
	//		$db->setQuery('SELECT id FROM #__menu WHERE link LIKE '. $db->Quote( $uri ) .' AND (language='. $db->Quote($language) .' OR language='.$db->Quote('*').') AND published = 1 LIMIT 1' );
	//		$itemId = intval($db->loadResult());
	//		$currUriMerchant = $uri.'&merchantId=' . $merchant->MerchantId . ':' . BFCHelper::getSlug($merchantName);
	//		if ($itemId<>0)
	//			$currUriMerchant.='&Itemid='.$itemId;
	//		$routeMerchant = JRoute::_($currUriMerchant.'&fromsearch=1');

//			$merchantdetails_page = get_post( bfi_get_page_id( 'merchantdetails' ) );
//			$url_merchant_page = get_permalink( $merchantdetails_page->ID );
			$url_merchant_page = BFCHelper::GetPageUrl('merchantdetails');

			$routeMerchant = $url_merchant_page . $merchant->MerchantId.'-'.BFI()->seoUrl($merchant->Name).'?fromsearch=1';

			$output = '<div class="bfi-mapdetails">
						<div class="bfi-item-title">
							<a href="'.$routeMerchant.'" '.COM_BOOKINGFORCONNECTOR_TARGETURL.'>'.$merchant->Name.'</a> 
						</div>
						<div class="bfi-item-address"><span class="street-address">'.$indirizzo .'</span>, <span class="postal-code ">'.$cap .'</span> <span class="locality">'.$comune .'</span>, <span class="region">'.$stato .'</span></div>
					</div>';    
			die($output);    	
		}

		function getmarketinforesource(){
			$resource_id=BFCHelper::getVar('resourceId');
			$language=BFCHelper::getVar('language');
			$resource = BFCHelper::GetResourcesById($resource_id);
			$merchant = $resource->Merchant;
			$resourceName = BFCHelper::getLanguage($resource->Name, $language, null, array('ln2br'=>'ln2br', 'striptags'=>'striptags')); 
			$indirizzo = isset($resource->Address)?$resource->Address:"";
			$cap = isset($resource->ZipCode)?$resource->ZipCode:""; 
			$comune = isset($resource->CityName)?$resource->CityName:"";
			$stato = isset($resource->StateName)?$resource->StateName:"";
			
	//		$db   = JFactory::getDBO();
	//		$uri  = 'index.php?option=com_bookingforconnector&view=resource';
	//		$db->setQuery('SELECT id FROM #__menu WHERE link LIKE '. $db->Quote( $uri ) .' AND (language='. $db->Quote($language) .' OR language='.$db->Quote('*').') AND published = 1 LIMIT 1' );
	//		$itemId = intval($db->loadResult());
	//		$currUriresource = $uri.'&resourceId=' . $resource->ResourceId . ':' . BFCHelper::getSlug($resourceName);
	//		if ($itemId<>0)
	//			$currUriresource.='&Itemid='.$itemId;
	//		if (!empty($resource->RateplanId)){
	//			 $currUriresource .= "&pricetype=" . $resource->RateplanId;
	//		}
	//		$resourceRoute = JRoute::_($currUriresource.'&fromsearch=1');

//			$accommodationdetails_page = get_post( bfi_get_page_id( 'accommodationdetails' ) );
//			$url_resource_page = get_permalink( $accommodationdetails_page->ID );
			$url_resource_page = BFCHelper::GetPageUrl('accommodationdetails');

			$resourceRoute = $url_resource_page . $resource->ResourceId .'-'.BFI()->seoUrl($resourceName).'?fromsearch=1';
			if (!empty($resource->RateplanId)){
				 $resourceRoute .= "&pricetype=" . $resource->RateplanId;
			}

			$output = '<div class="bfi-mapdetails">
						<div class="bfi-item-title">
							<a href="'.$resourceRoute.'" '.COM_BOOKINGFORCONNECTOR_TARGETURL.'>'.$resource->Name.'</a> 
						</div>
						<div class="bfi-item-address"><span class="street-address">'.$indirizzo .'</span>, <span class="postal-code ">'.$cap .'</span> <span class="locality">'.$comune .'</span>, <span class="region">'.$stato .'</span></div>
					</div>';    
			die($output);    	
		}
		function getmarketinforesourcegroup(){
			$resource_id=BFCHelper::getVar('resourceId');
			$language=BFCHelper::getVar('language');
			$resource = BFCHelper::getResourcegroupFromServicebyId($resource_id);
			$resourceName = BFCHelper::getLanguage($resource->Name, $language, null, array('ln2br'=>'ln2br', 'striptags'=>'striptags')); 
			$indirizzo = isset($resource->Address)?$resource->Address:"";
			$cap = isset($resource->ZipCode)?$resource->ZipCode:""; 
			$comune = isset($resource->CityName)?$resource->CityName:"";
			$stato = isset($resource->StateName)?$resource->StateName:"";
			
	//		$db   = JFactory::getDBO();
	//		$uri  = 'index.php?option=com_bookingforconnector&view=resourcegroup';
	//		$db->setQuery('SELECT id FROM #__menu WHERE link LIKE '. $db->Quote( $uri ) .' AND (language='. $db->Quote($language) .' OR language='.$db->Quote('*').') AND published = 1 LIMIT 1' );
	//		$itemId = intval($db->loadResult());
	//		$currUriresource = $uri.'&resourceId=' . $resource->CondominiumId . ':' . BFCHelper::getSlug($resourceName);
	//		if ($itemId<>0)
	//			$currUriresource.='&Itemid='.$itemId;
	//		$resourceRoute = JRoute::_($currUriresource.'&fromsearch=1');

//			$resourcegroupdetails_page = get_post( bfi_get_page_id( 'resourcegroupdetails' ) );
//			$url_resourcegroup_page = get_permalink( $resourcegroupdetails_page->ID );
			$url_resourcegroup_page = BFCHelper::GetPageUrl('resourcegroupdetails');
			
			$resourceRoute = $url_resourcegroup_page . $resource->CondominiumId.'-'.BFI()->seoUrl($resourceName).'?fromsearch=1';

			$output = '<div class="bfi-mapdetails">
						<div class="bfi-item-title">
							<a href="'.$resourceRoute.'" '.COM_BOOKINGFORCONNECTOR_TARGETURL.'>'.$resource->Name.'</a> 
						</div>
						<div class="bfi-item-address"><span class="street-address">'.$indirizzo .'</span>, <span class="postal-code ">'.$cap .'</span> <span class="locality">'.$comune .'</span>, <span class="region">'.$stato .'</span></div>
					</div>';    
			die($output);    	
		}

		function GetAlternativeDates(){
			$checkin = BFCHelper::getVar('checkin');
			$duration = BFCHelper::getVar('duration');
			$paxes = BFCHelper::getVar('paxes');
			$paxages = BFCHelper::getVar('paxages');
			$merchantId = BFCHelper::getVar('merchantId');
			$resourcegroupId = BFCHelper::getVar('resourcegroupId');
			$resourceId = BFCHelper::getVar('resourceId');
			$cultureCode = BFCHelper::getVar('cultureCode');
			$points = BFCHelper::getVar('points');
			$userid = BFCHelper::getVar('userid');
			$tagids = BFCHelper::getVar('tagids');
			$merchantsList = BFCHelper::getVar('merchantsList');
			$resourcesList = BFCHelper::getVar('resourcesList');
			$availabilityTypes = BFCHelper::getVar('availabilityTypes');
			$itemTypeIds = BFCHelper::getVar('itemTypeIds');
			$domainLabel = BFCHelper::getVar('domainLabel');
			$merchantCategoryIds = BFCHelper::getVar('merchantCategoryIds');
			$masterTypeIds = BFCHelper::getVar('masterTypeIds');
			$merchantTagsIds = BFCHelper::getVar('merchantTagsIds');
			$groupResultType = BFCHelper::getVar('groupResultType');
			$return = BFCHelper::GetAlternativeDates($checkin, $duration, $paxes, $paxages, $merchantId, $resourcegroupId, $resourceId, $cultureCode, $points, $userid, $tagids, $merchantsList, $availabilityTypes, $itemTypeIds, $domainLabel, $merchantCategoryIds, $masterTypeIds, $merchantTagsIds, $groupResultType, $resourcesList);
			echo json_encode($return);      
			// use die() because in IIS $mainframe->close() raise a 500 error 
	//		$app = JFactory::getApplication();
	//		$app->close();
			//$mainframe->close();
		}
		function bfilogin(){
			$return = "0";
			$email = BFCHelper::getVar('email');
			$password = BFCHelper::getVar('password');
			$userToken = BFCHelper::getVar('userToken');
			$twoFactorAuthCode = BFCHelper::getVar('twoFactorAuthCode');
			$deviceCodeAuthCode = BFCHelper::GetTwoFactorCookie();
			$return = BFCHelper::getLoginTwoFactor($email, $password, $twoFactorAuthCode, BFCHelper::GetUniqueDeviceCookie(), BFCHelper::GetUniqueDeviceCookie(), $userToken);		
			echo json_encode($return);      
		}

		function bfilogout(){
	//		BFCHelper::DeleteTwoFactorCookie();
			$currUserId = BFCHelper::bfi_get_userId();
			$currUser = BFCHelper::getSession('bfiUser',null, 'bfi-User');
			$return = "0";
			if ($currUser != null && BFCHelper::logoutUser($currUserId, BFCHelper::GetUniqueDeviceCookie(), $currUser->UserToken)
				) {
				BFCHelper::setSession('bfiUser', null, 'bfi-User');
				BFCHelper::UpdateCartExternalUser($currUserId);
				$return = "-1";
			}
			echo json_encode($return);      
		}
		function bficookie(){

	//echo "<pre>_COOKIE ";
	//echo print_r($_COOKIE);
	//echo "</pre>";

		}
		function bficurrUser(){
				$currUser = BFCHelper::getSession('bfiUser',null, 'bfi-User');
	//echo "<pre>currUser: ";
	//echo print_r($currUser);
	//echo "</pre>";
				return $currUser;
		}


		function loadlogin()
		{
			bfi_get_template("shared/login.php");	
		}

		function GetMapAvailabilitiesByProductGroupId(){
	//		$language=$_REQUEST['language'];
			$productGroupId = isset($_REQUEST['productGroupId'])?$_REQUEST['productGroupId']:null;
			$checkIn = BFCHelper::getVar('checkIn');
			$checkOut = BFCHelper::getVar('checkOut');
			$paxes = BFCHelper::getVar('paxes');
			$paxages = BFCHelper::getVar('paxages');
			$sectorId = BFCHelper::getVar('sectorId');
			$return = BFCHelper::GetMapAvailabilitiesByProductGroupId($productGroupId,$checkIn,$checkOut,$paxes,$paxages,$sectorId);
			if (!empty($return)){
					$return = json_encode($return);
			}
			echo $return;      
		}
		function DeleteCacheByIds(){
			$scope = BFCHelper::getVar('scope');
			$currScope = 0;
			switch ($scope) {
			    case "0":
					$currScope = bfi_TagsScope::Merchant;
					break;
			    case "1":
					$currScope = bfi_TagsScope::Resource;
					break;
			    case "2":
					$currScope = bfi_TagsScope::Offert;
					break;
			    case "3":
					$currScope = bfi_TagsScope::Event;
					break;
			    case "4":
					$currScope = bfi_TagsScope::Poi;
					break;
				default:      
					 $currScope = 0;
			}
			$ids = BFCHelper::getVar('ids');
			if (file_exists (COM_BOOKINGFORCONNECTOR_CACHEDIR) && !BFCHelper::is_dir_empty(COM_BOOKINGFORCONNECTOR_CACHEDIR)) {
				if (!empty($currScope) && !empty($ids)) {
					$ids = str_replace(" ", "",$ids);
				    $aIds = explode(",",$ids);
					foreach ($aIds as $id ) {
					    $mask = 'bfi_' . $currScope . '_' . $id.'_*.cache';
						array_map('unlink', glob(COM_BOOKINGFORCONNECTOR_CACHEDIR . '/' . $mask));
					}
				}
			}
			echo "1";      
		}

	} //end class
}