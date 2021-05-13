<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BookingForConnectorModelMerchants Model
 */
if ( ! class_exists( 'BookingForConnectorModelMerchantDetails' ) ) :

class BookingForConnectorModelMerchantDetails
{
	private $urlMerchant = null;
	private $urlMerchantResources = null;
	private $urlMerchantResourcesCount = null;
	private $helper = null;
	private $urlMerchantOnSellUnits = null;
	private $urlMerchantOnSellUnit = null;
	private $urlMerchantOnSellUnitsCount = null;
	private $urlMerchantRating = null;
	private $urlMerchantRatingCount = null;
	private $urlMerchantRatingAverage = null;
	private $urlMerchantMerchantGroups = null;
	private $urlMerchantCounter = null;
	private $urlMerchantOffers = null;
	private $urlMerchantOffer = null;
	private $urlMerchantOffersCount = null;
	private $merchantid = null;
	private $resourceid = null;
	private $parentid = null;
	private $itemPerPage = 10;
	private $urlgetTagsByElementId = null;
	private $urlMerchantPolicies = null;
	private $urlSearch = null;

	public function __construct($config = array())
	{
      $ws_url = COM_BOOKINGFORCONNECTOR_WSURL;
		$api_key = COM_BOOKINGFORCONNECTOR_API_KEY;
		$this->helper = new wsQueryHelper($ws_url, $api_key);
		$this->urlMerchant = '/GetMerchantsById';
		$this->urlMerchantResources = '/GetMerchantResources';
		$this->urlMerchantResourcesCount = '/GetMerchantResourcesCount';
		$this->urlMerchantOnSellUnits = '/GetResourceonsellsByMerchantId';
		$this->urlMerchantOnSellUnitsCount = '/GetResourceonsellsByMerchantIdCount';
		$this->urlMerchantOnSellUnit = '/ResourceonsellView(%d)';
		$this->urlMerchantRatingAverage = '/GetMerchantAverage';
		$this->urlMerchantMerchantGroups = '/GetMerchantMerchantGroups';
		$this->urlMerchantCounter = '/MerchantCounter';
		$this->urlMerchantRating = '/GetReviews';
		$this->urlMerchantRatingCount = '/GetReviewsCount';
		$this->urlMerchantOffers = '/GetVariationPlans';
		$this->urlMerchantOffersCount = '/GetVariationPlansCount';
		$this->urlMerchantOffer = '/GetVariationPlanById';
		$this->urlgetTagsByElementId = '/GetTagsByElementId';
		$this->urlMerchantPolicies = '/GetPolicyByMerchantId';
		$this->urlSearch = '/SearchResources'; //'/SearchAllLiteNew';
}


	public function setMerchantId($merchantId) {
		if(!empty($merchantId)){
			$this->merchantid = $merchantId;
		}
	}
	
	public function setParentId($parentId) {
		if(!empty($parentId)){
			$this->parentid = $parentId;
		}
	}
	
	public function setResourceId($resourceId) {
		if(!empty($resourceId)){
			$this->resourceid = $resourceId;
		}
	}
	
	public function setItemPerPage($itemPerPage) {
		if(!empty($itemPerPage)){
			$this->itemPerPage = $itemPerPage;
		}
	}


	protected function populateState($ordering = NULL, $direction = NULL) {
		$searchseed = BFCHelper::getSession('searchseed', rand(), 'com_bookingforconnector');
		if (!$session->has('searchseed','com_bookingforconnector')) {
			BFCHelper::setSession('searchseed', $searchseed, 'com_bookingforconnector');
		}

		$this->setState('params', array(
			'merchantId' => JRequest::getInt('merchantId'),
			'offerId' => JRequest::getInt('offerId'),
            'onSellUnitId' => JRequest::getInt('onsellunitid'),
			'searchseed' => $searchseed,
			'filters' => JRequest::getVar('filters')
		));

	}

	public function getMerchantFromServicebyId($merchantId) {

		return $this->getMerchantFromService($merchantId);
	}



	public function getMerchantFromService($merchantId='') {

		if(empty($merchantId)){
			return null;
		}

		$cultureCode = $GLOBALS['bfi_lang'];

		$sessionkey = 'merchant.' . $merchantId . $cultureCode ;			
		$merchant = null;

		if ($merchant == null) {

			$options = array(
					'path' => $this->urlMerchant,
					'data' => array(
						'id' => $merchantId,
						'cultureCode' => BFCHelper::getQuotedString($cultureCode),
						'$format' => 'json'
					)
			);
			
			$url = $this->helper->getQuery($options);

//			$r = $this->helper->executeQuery($url,null,null,false);
			$r = $this->helper->executeQuery($url,null,null,false,"","",bfi_TagsScope::Merchant,$merchantId );
			if (isset($r)) {
				$res = json_decode($r);
				if (!empty($res->d->GetMerchantsById)){
					$merchant = $res->d->GetMerchantsById;
				}elseif(!empty($res->d)){
					$merchant = $res->d;
				}
				if (!empty($merchant)) {
					$merchant->Tags = json_decode($merchant->TagsString);
//					$merchant->Policies = $this->GetPolicyByMerchantId($merchantId);
					if(!empty($merchant->XGooglePos) && !empty($merchant->YGooglePos)){
						$resourceLat = $merchant->XGooglePos;
						$resourceLon = $merchant->YGooglePos;
						$currPoint = "0|" . $resourceLat . " " . $resourceLon . " 10000";
						$merchant->Poi = BFCHelper::GetProximityPoi($currPoint);					
					}

				}
			}
			//BFCHelper::setSession($sessionkey, $merchant);
				
		}
		
		return $merchant;
	}

	public function GetPolicyByMerchantId($merchantId, $cultureCode=null) {
		if(empty($cultureCode)){
			$cultureCode = $GLOBALS['bfi_lang'];
		}
		$options = array(
			'path' => $this->urlMerchantPolicies,
			'data' => array(
				'merchantId' => $merchantId,
				'cultureCode' => '\'' . $cultureCode . '\'',
				'$format' => 'json'
			)
		);
		$url = $this->helper->getQuery($options);
		$types = null;

//		$r = $this->helper->executeQuery($url,null,null,false);
		$r = $this->helper->executeQuery($url,null,null,false,"","",bfi_TagsScope::Resource,$resourceId );
		if (isset($r)) {
			$res = json_decode($r);
			if (!empty($res->d->GetPolicyByMerchantId)){
				$types = $res->d->GetPolicyByMerchantId;
			}elseif(!empty($res->d)){
				$types = $res->d;
			}
		}
		return $types;
	}

	public function getTagsByMerchantId($merchantId,$viewContextType=null){
		
		if(empty($merchantId)){
			return null;
		}

		$cultureCode = $GLOBALS['bfi_lang'];
		$selectionCategory = 1; //Fisso per merchant
//		$selectionCategory = 4; //Fisso per risorsa
//		$selectionCategory = 2; //Fisso per vendita
//		$selectionCategory = 8; //Fisso per gruppo di risorsa
		$options = array(
				'path' => $this->urlgetTagsByElementId,
				'data' => array(
					'elementId' => $merchantId,
					'selectionCategory' => $selectionCategory,
					'cultureCode' => BFCHelper::getQuotedString($cultureCode),
					'$format' => 'json'
				)
		);
		if (isset($viewContextType) && $viewContextType > 0) {
			$options['data']['viewContextType'] = $viewContextType;
		}

		$url = $this->helper->getQuery($options);
		$tags = null;
//		$r = $this->helper->executeQuery($url,null,null,false);
		$r = $this->helper->executeQuery($url,null,null,false,"","",bfi_TagsScope::Merchant,$merchantId);
		if (isset($r)) {
			$res = json_decode($r);
			if (!empty($res->d->GetTagsByElementId)){
				$tags = $res->d->GetTagsByElementId;
			}elseif(!empty($res->d)){
				$tags = $res->d;
			}
		}
		return $tags;

	}

//Risorse in vendita
	public function getMerchantOnSellUnitsFromService($start, $limit, $merchantId) {
		$cultureCode = $GLOBALS['bfi_lang'];
		$options = array(
				'path' => $this->urlMerchantOnSellUnits,
				'data' => array(
					'merchantid' =>  $merchantId ,
					'cultureCode' => BFCHelper::getQuotedString($cultureCode),
					'$format' => 'json'
				)
		);

		if (isset($start) && $start > 0) {
			$options['data']['skip'] = $start;
		}

		if (isset($limit) && $limit > 0) {
			$options['data']['top'] = $limit;
		}

		$url = $this->helper->getQuery($options);

		$resources = null;

		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);
			if (!empty($res->d->results)){
				$resources = $res->d->results;
			}elseif(!empty($res->d)){
				$resources = $res->d;
			}
		}

		return $resources;
	}

	public function getMerchantOnSellUnitFromService() {
		$params = $this->getState('params');

		$onSellUnitId = $params['onSellUnitId'];

		$options = array(
				'path' => sprintf($this->urlMerchantOnSellUnit, $onSellUnitId),
				'data' => array(
					'$format' => 'json'
				)
		);

		$url = $this->helper->getQuery($options);

		$onsellunit = null;

		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);
			$onsellunit = $res->d->results ?: $res->d;
		}

		return $onsellunit;
	}

	public function getItemsOnSellUnit()
	{
		return $this->getItems('onsellunits');
	}

	public function getOnSellUnit()
	{
		return $this->getItems('onsellunit');
	}

	public function getItem($merchantId = '') {
		if($merchantId != '') {
		  $item = $this->getMerchantFromService($merchantId);
		}
		else {
		  $item = $this->getMerchantFromService();
		}
		return $item;
	}
	public function getMerchantResourcesFromSearch($start, $limit, $merchantId = NULL, $parentId = NULL) {
      $merchantId = $merchantId != NULL ? $merchantId : $params['merchantId'];
		$language = $GLOBALS['bfi_lang'];
		$options = array(
//				'path' =>  $this->urlGetRelatedResourceStays,
				'path' =>  $this->urlSearch,
				'data' => array(
					'getAllResults' => 1,
					'lite' => 1,
					'calculate' => 0,
					'top' => 5,
					'skip' => 0,
//					'checkin' => '\'' . (new DateTime('UTC'))->format('Ymd') . '\'',
					'getSingleResultOnly' => 0,
					'getFilters' => 0,  // per recuperare i filtri
					'simpleResult' => 0,  // per recuperare i filtri
					'checkAvailability' => 0,  // per recuperare i filtri
					'getBestGroupResult' => 0,  // per recuperare i filtri
					'groupResultType' => 0,  // per recuperare i filtri
					'checkStays' => 0,  // per recuperare i filtri
					'getUpSellproducts' => 0, // per recuperare i prodotti in upsell
					'domainLabel' => BFCHelper::getQuotedString(COM_BOOKINGFORCONNECTOR_FORM_KEY),
					'$format' => 'json',
					'viewContextType' => 1, // contesto per la visualizzazione dei tag....
						
				)
			);

			//orderby
			$orderby = 'priority';
			$ordertype = 'asc';
			if(isset( $relatedProductid ) && !empty($relatedProductid)){
				$orderby = 'resourceid:' . $relatedProductid ;
				$options['data']['orderby'] = '\'' . $orderby . '\'';
			}else{
				$options['data']['orderby'] = '\'' . $orderby . ';' . $ordertype.  '\'';
//				$options['data']['ordertype'] = '\'' . $ordertype . '\'';
			}

		if (!empty($merchantId)) {
			$options['data']['merchantId'] = $merchantId;
		}
		if (!empty($language)) {
			$options['data']['cultureCode'] = '\'' . $language . '\'';
		}
			$options['data']['searchid'] = '\'' . uniqid('', true). '\'';
				$url = $this->helper->getQuery($options);

		$results = null;

		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);
			if (!empty($res->d->SearchResources)){
				$results = $res->d->SearchResources;
			}elseif(!empty($res->d)){
				$results = $res->d;
			}
		}
		$resultsItems = null;

		if(isset($results->ItemsCount)){
//			$this->count = $results->ItemsCount;
//			$this->availableCount = $results->AvailableItemsCount;
			$resultsItems = json_decode($results->ItemsString);
		}
		return $resultsItems;

	}
	public function getMerchantResourcesFromService($start, $limit, $merchantId = NULL, $parentId = NULL) {
      $merchantId = $merchantId != NULL ? $merchantId : $params['merchantId'];

		$seed = isset($_SESSION['search.params']['searchseed']) ? $_SESSION['search.params']['searchseed'] : '';
			$cultureCode = $GLOBALS['bfi_lang'];

		$options = array(
				'path' => $this->urlMerchantResources,
				'data' => array(
					'merchantId' => $merchantId,
					'parentProductId' => !empty($this->parentId) ? $this->parentId : null,
					'cultureCode' => BFCHelper::getQuotedString($cultureCode),
					'seed' => $seed,
					'$format' => 'json'
				)
		);

		if (isset($start) && $start >= 0) {
			$options['data']['skip'] = $start;
		}

		if (isset($limit) && $limit > 0) {
			$options['data']['top'] = $limit;
		}

		$url = $this->helper->getQuery($options);

		$resources = null;

//		$r = $this->helper->executeQuery($url);
//		$r = $this->helper->executeQuery($url,null,null,false);
		$r = $this->helper->executeQuery($url,null,null,false,"","",bfi_TagsScope::Merchant,$merchantId);
		if (isset($r)) {
			$res = json_decode($r);
			if (!empty($res->d->results)){
				$resources = $res->d->results;
			}elseif(!empty($res->d)){
				$resources = $res->d;
			}
		}

		return $resources;
	}

	public function getMerchantGroupsByMerchantIdFromService($merchantId = null) {
		$params = $this->getState('params');

		if ($merchantId==null) {
			$merchantId = $params['merchantId'];
		}

		$options = array(
				'path' => $this->urlMerchantMerchantGroups,
				'data' => array(
					'merchantId' => $merchantId,
					'$format' => 'json'
				)
		);

		$url = $this->helper->getQuery($options);
		
		$merchantGroups = null;

		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);
			//$merchantGroups = $res->d->results ?: $res->d;
			if (!empty($res->d->results)){
				$merchantGroups = $res->d->results;
			}elseif(!empty($res->d)){
				$merchantGroups = $res->d;
			}
		}

		return $merchantGroups;
	}
	public function getPhoneByMerchantId($merchantId = null,$language='',$number='') {
		if ($merchantId==null) {
			$merchantId = $_SESSION['search.params']['merchantId'];
		}

		$options = array(
				'path' => $this->urlMerchantCounter,
				'data' => array(
					'merchantId' => $merchantId,
					'what' => '\'phone'.$number.'\'',
					'cultureCode' => BFCHelper::getQuotedString($language),
					'$format' => 'json'
				)
		);

		$url = $this->helper->getQuery($options);

		$res = null;

		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);
			if (!empty($res->d->results)){
				$resReturn = $res->d->results;
			}elseif(!empty($res->d)){
				$resReturn = $res->d;
			}
			if (!empty($resReturn)){
				$res = $resReturn->MerchantCounter;
			}
		}

		return $res;
	}

	public function GetFaxByMerchantId($merchantId = null,$language='') {
		$params = $this->getState('params');

		if ($merchantId==null) {
			$merchantId = $params['merchantId'];
		}

		$options = array(
				'path' => $this->urlMerchantCounter,
				'data' => array(
					'merchantId' => $merchantId,
					'what' => '\'fax\'',
					'cultureCode' => BFCHelper::getQuotedString($language),
					'$format' => 'json'
				)
		);

		$url = $this->helper->getQuery($options);

		$res = null;

		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);
			if (!empty($res->d->results)){
				$resReturn = $res->d->results;
			}elseif(!empty($res->d)){
				$resReturn = $res->d;
			}
			if (!empty($resReturn)){
				$res = $resReturn->MerchantCounter;
			}
		}

		return $res;
	}

	public function setCounterByMerchantId($merchantId = null, $what='', $language='') {
		$params = $this->getState('params');

		if ($merchantId==null) {
			$merchantId = $params['merchantId'];
		}

		$options = array(
				'path' => $this->urlMerchantCounter,
				'data' => array(
					'merchantId' => $merchantId,
					'what' => '\''.$what.'\'',
					'cultureCode' => BFCHelper::getQuotedString($language),
					'$format' => 'json'
				)
		);

		$url = $this->helper->getQuery($options);

		$res = null;

		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);
			if (!empty($res->d->results)){
				$resReturn = $res->d->results;
			}elseif(!empty($res->d)){
				$resReturn = $res->d;
			}
			if (!empty($resReturn)){
				$res = $resReturn->MerchantCounter;
			}
		}

		return $res;
	}

	public function getMerchantRatingAverageFromService($merchantId = null) {
		
		if ($merchantId==null) {
			$merchantId = $_SESSION['search.params']['merchantId'];
		}

		$options = array(
				'path' => $this->urlMerchantRatingAverage,
				'data' => array(
					'merchantId' => $merchantId,
					'$format' => 'json'
				)
		);

		$url = $this->helper->getQuery($options);

		$ratings = null;

//		$r = $this->helper->executeQuery($url);
//		$r = $this->helper->executeQuery($url,null,null,false);
		$r = $this->helper->executeQuery($url,null,null,false,"","",bfi_TagsScope::Merchant,$merchantId);
		if (isset($r)) {
			$res = json_decode($r);
			$resRatings = null;
			if (!empty($res->d->results)){
				$resRatings = $res->d->results;
			}elseif(!empty($res->d)){
				$resRatings = $res->d;
			}
			if (!empty($resRatings)){
				$ratings = $resRatings->GetMerchantAverage;
			}
		}

		return $ratings;
	}

	//CHANGED
	public function getMerchantRatingsFromService($start, $limit, $merchantId = null, $resourceId = null, $language='',$fromCache=1) {
		if ($merchantId==null) {
			$merchantId = $_SESSION['search.params']['merchantId'];
		}
		if ($language==null) {
			$language = $GLOBALS['bfi_lang'];
		}

		$options = array(
				'path' => $this->urlMerchantRating,
				'data' => array(
					'cultureCode' => BFCHelper::getQuotedString($language),
					'$format' => 'json'
				)
		);
				
		if (isset($merchantId) && $merchantId > 0) {
			$options['data']['merchantId'] = $merchantId;
		}

		$filters = isset($_SESSION['ratings']['filters']) ? $_SESSION['ratings']['filters'] : array();


		if ($filters != null && $filters['typologyid'] != null && $filters['typologyid']!= "0") {
			$options['data']['tipologyId'] = $filters['typologyid'];
		}

		$url = $this->helper->getQuery($options);

		$ratings = null;

		$r = null;
		if(!empty( $fromCache )){
//			$r = $this->helper->executeQuery($url,null,null,false);
			$r = $this->helper->executeQuery($url,null,null,false,"","",bfi_TagsScope::Merchant,$merchantId);
		}else{
			$r = $this->helper->executeQuery($url);
		}
		if (isset($r)) {

			$res = json_decode($r);
			if (!empty($res->d->results)){
				$ratings = $res->d->results;
			}elseif(!empty($res->d)){
				$ratings = $res->d;
			}
		}
		
		if (isset($ratings) && !empty($ratings)) {
			if (isset($this->resourceid) && $this->resourceid > 0) {
				$ratings = array_filter($ratings, function($obj){
					return $obj->ResourceId == $this->resourceid;
				});
			}
			if (isset($start) && $start > 0) {
				$ratings = array_slice($ratings, $start);
			}
			if (isset($limit) && $limit > 0) {
				$ratings = array_slice($ratings, 0, $limit);
			}
		}
		return $ratings;
	}

	private function filterRatingResults($results) {
		$params = $this->getState('params');
		$filters = $params['filters'];
		if ($filters == null) return $results;

		// typologyid filtering
		if ($filters['typologyid'] != null) {
			$typologyid = (int)$filters['typologyid'];
			if ($typologyid > 0) {
				$results = array_filter($results, function($result) use ($typologyid) {
					return $result->TypologyId == $typologyid;
				});
			}
		}

		return $results;
	}

	public function getMerchantOffersFromService($start, $limit, $language = null) {

		if ($language==null) {
			$language = $GLOBALS['bfi_lang'];

		}
		$options = array(
				'path' => $this->urlMerchantOffers,
				'data' => array(
					'merchantId' => $this->merchantid,
					'cultureCode' => BFCHelper::getQuotedString($language),
					'$format' => 'json'
				)
		);
		$url = $this->helper->getQuery($options);
		
		$resources = null;
		
		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);
//			$resources = $res->d->results ?: $res->d;
			if (!empty($res->d->results)){
				$resources = $res->d->results;
			}elseif(!empty($res->d)){
				$resources = $res->d;
			}
		}

		return $resources;
	}	

	public function getMerchantOfferFromService($offerId=0 , $language = null) {
		
		if ($language==null) {
			$language = $GLOBALS['bfi_lang'];

		}
		
		$options = array(
				'path' => $this->urlMerchantOffer,
				'data' => array(
					'id' => $offerId,
					'cultureCode' => BFCHelper::getQuotedString($language),
					'$format' => 'json'
				)
		);
		
		$url = $this->helper->getQuery($options);
		
		$offer = null;
		
		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);
//			$offer = $res->d->results ?: $res->d;
			if (!empty($res->d->GetVariationPlanById)){
				$offer = $res->d->GetVariationPlanById;
			}elseif(!empty($res->d)){
				$offer = $res->d;
			}
		}

		return $offer;
	}	

	public function getItemsRating($merchant_id)
	{
		return $this->getItems('ratings', '', $merchant_id);
	}

	public function getRating()
	{
		return $this->getItems('ratings');
	}

	public function getItemsOffer()
	{
		return $this->getItems('offers');
	}

	public function getOffer()
	{
		return $this->getItems('offer');
	}

	public function getItemsResourcesAjax()
	{
		return $this->getItems('resourcesajax');
	}

	public function getItems($type = '', $ext_data = 0, $merchant_id = '', $parent_id = '')
	{
		$page = bfi_get_current_page() ;

		switch($type) {
			case 'offers':
				   $items = $this->getMerchantOffersFromService(
					(absint($page)-1)*$this->itemPerPage,
					$this->itemPerPage,
					null
				);
				break;
			case 'offer':
				$items = $this->getMerchantOfferFromService();
				break;
			case 'onsellunits':
				$items = $this->getMerchantOnSellUnitsFromService(
					(absint($page)-1)*$this->itemPerPage,
					$this->itemPerPage,
					$merchant_id
				);
				break;
			case 'onsellunit':
				$items = $this->getMerchantOnSellUnitFromService();
				break;
			case 'ratings':
				$items = $this->getMerchantRatingsFromService(
					(absint($page)-1)*$this->itemPerPage,
					$this->itemPerPage,
					isset($this->merchantid) ? $this->merchantid : null,
					isset($this->resourceid) ? $this->resourceid : null,
					"",
					true
				);
				break;
			case 'resourcesajax':
				$items = $this->getMerchantResourcesFromService(
					0,
					20,
					$ext_data
				);
				break;
			case '':
			default:
				$items = $this->getMerchantResourcesFromService(
					(absint($page)-1)*$this->itemPerPage,
					$this->itemPerPage,
					$merchant_id,
					$parent_id
				);
				break;
		}


		return $items;
	}

	public function getTotal($type = '')
	{
		switch($type) {
			case 'offers':
				return $this->getTotalOffers();
				break;
			case 'onsellunits':
				return $this->getTotalOnSellUnits($this->merchantid);
				break;
			case 'ratings':
				$ratings = $this->getMerchantRatingsFromService(
					0,
					0,
					isset($this->merchantid) ? $this->merchantid : null,
					isset($this->resourceid) ? $this->resourceid : null,
					"",
					true
				);
				return !empty($ratings) ? count($ratings) : 0;
				//return $this->getTotalRatings();
				break;
			case '':
			default:
				return $this->getTotalResources();
		}
	}

	public function getTotalOnSellUnits($merchantId)
	{
		if(empty($merchantId)){
			$merchantId=$this->merchantid;
		}
		$options = array(
				'path' => $this->urlMerchantOnSellUnitsCount,
				'data' => array(
					'$format' => 'json',
					'merchantid' =>  $merchantId
			)
		);

		$url = $this->helper->getQuery($options);

		$count = null;

		$r = $this->helper->executeQuery($url);
//		if (isset($r)) {
//			$count = (int)$r;
//		}
		if (isset($r)) {
			$res = json_decode($r);
			$count = (int)$res->d->GetResourceonsellsByMerchantIdCount;
		}

		return $count;
	}

	//CHANGED
	public function getTotalRatings()
	{
		$options = array(
				'path' => $this->urlMerchantRatingCount,
				'data' => array(
					'$format' => 'json',
					'MerchantId' =>  $this->merchantid 
			)
		);
		$filters = isset($_SESSION['ratings']['filters']) ? $_SESSION['ratings']['filters'] : array();
		if ($filters != null && $filters['typologyid'] != null && $filters['typologyid']!= "0") {
			$options['data']['tipologyId'] = $filters['typologyid'];
		}

		$url = $this->helper->getQuery($options);

		$count = null;

//		$r = $this->helper->executeQuery($url);
//		$r = $this->helper->executeQuery($url,null,null,false);
		$r = $this->helper->executeQuery($url,null,null,false,"","",bfi_TagsScope::Merchant,$this->merchantid );
		if (isset($r)) {
			$count = (int)$r;
			$res = json_decode($r);
			$count = (int)$res->d->GetReviewsCount;
		}

		return $count;
	}

	public function getTotalOffers()
	{
		$options = array(
				'path' => $this->urlMerchantOffersCount,
				'data' => array(
					'merchantId' => $this->merchantid ,
					'$format' => 'json'
//					'$filter' => 'MerchantId eq ' . $this->merchantid . '  and Enabled eq true  and Viewable eq true '
			)
		);

		$url = $this->helper->getQuery($options);

		$count = null;

		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
//			$count = (int)$r;
			$res = json_decode($r);
			$count = (int)$res->d->GetVariationPlansCount;
		}

		return $count;
	}

	public function getTotalResources()
	{
		$count = null;				
		if(!empty($this->merchantid)){
			$options = array(
					'path' => $this->urlMerchantResourcesCount,
					'data' => array(
						'$format' => 'json',
						'merchantId' => $this->merchantid,
						'parentProductId' => !empty($this->parentId) ? $this->parentId : null
				)
			);

			$url = $this->helper->getQuery($options);


//			$r = $this->helper->executeQuery($url);
			$r = $this->helper->executeQuery($url,null,null,false,"","",bfi_TagsScope::Merchant,$this->merchantid );
			$r = $this->helper->executeQuery($url,null,null,false);
			if (isset($r)) {
				$res = json_decode($r);
				$count = (int)$res->d->GetMerchantResourcesCount;
			}
		}

		return $count;
	}
	public function getTotalResourcesAjax()
	{
		$options = array(
				'path' => $this->urlMerchantResourcesCount,
				'data' => array(
					'$format' => 'json',
					'merchantId' => BFCHelper::getInt('merchantId')
			)
		);

		$url = $this->helper->getQuery($options);

		$count = null;

		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);
			$count = (int)$res->d->GetMerchantResourcesCount;
		}

		return $count;
	}
	public function getStart($type = '')
	{
		$start =  isset($_REQUEST['limitstart']) ? $_REQUEST['limitstart'] :0 ;  
		$limit = 20;
		$total = $this->getTotal($type);
		if ($start > $total - $limit)
		{
			$start = max(0, (int) (ceil($total / $limit) - 1) * $limit);
		}

		// Add the total to the internal cache.
		$this->cache[$store] = $start;

		return $this->cache[$store];
	}
	function getPaginationRatings()
	{
		return $this->getPagination('ratings');
	}
	function getPaginationOnSellUnits()
	{
		return $this->getPagination('onsellunits');
	}
	function getPagination($type = '')
	{
		// Load the content if it doesn't already exist
		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal($type), $this->getState('list.start'), $this->getState('list.limit') );
		}
		return $this->_pagination;
	}
}
endif;