<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BookingForConnectorModelMerchants Model
 */
 if ( ! class_exists( 'BookingForConnectorModelMerchants' ) ) :

class BookingForConnectorModelMerchants
{
	private $urlMerchants = null;
	private $urlMerchantsCount = null;
	private $merchantsCount = 0;
	private $urlMerchantCategories = null;
	private $urlGeographicZones = null;
	private $urlGetMerchantsByIds = null;
	private $urlMerchantCategoriesRequest = null;
	private $urlGetServicesByMerchantsCategoryId = null;
	private $params = null;
	private $itemPerPage = 20;
	private $ordering = null;
	private $direction = null;

	private $urlSearch = null;
	private $currentOrdering = null;
	private $currentDirection = null;
	private $count = null;
	private $currentData = null;

	private $helper = null;

	public function __construct($config = array())
	{
		$this->helper = new wsQueryHelper(COM_BOOKINGFORCONNECTOR_WSURL, COM_BOOKINGFORCONNECTOR_API_KEY);
		$this->urlMerchants = '/GetMerchantsByCategoryIds';
		$this->urlMerchantsCount = '/GetMerchantsByCategoryIds/$count';
		$this->merchantsCount = 0;
		$this->urlMerchantCategories = '/GetMerchantsCategory';
		$this->urlGeographicZones = '/GetGeographicZones';//'/LocationZones';
		$this->urlGetMerchantsByIds = '/GetMerchantsByIdsExt';
		$this->urlMerchantCategoriesRequest = '/GetMerchantsCategoryForRequest';
		$this->urlGetServicesByMerchantsCategoryId = '/GetServicesByMerchantsCategoryId';
		$this->urlSearch = '/SearchAllMerchants';
	}

	public function setItemPerPage($itemPerPage) {
		if(!empty($itemPerPage)){
			$this->itemPerPage = $itemPerPage;
		}
	}
	public function setOrdering($ordering) {
		if(!empty($ordering)){
			$this->ordering = $ordering;
		}
	}
	public function setDirection($direction) {
		if(!empty($direction)){
			$this->direction = $direction;
		}
	}
	public function getOrdering() {
		return $this->ordering;
	}
	public function getDirection() {
		return $this->direction;
	}

	public function getParam() {
		return $this->params;
	}

	public function setParam($param) {
		$this->params = $param ;
	}

	public function applyDefaultFilter(&$options) {
		$params = BFCHelper::getSearchMerchantParamsSession();

		$searchid = isset($params['searchid']) ? $params['searchid'] : '';
//		$masterTypeId = $params['masterTypeId'];
//		$checkin = $params['checkin'];
//		$checkout = $params['checkout'];
//		$duration = $params['duration'];
//		$persons = $params['paxes'];
		$merchantCategoryId = isset($params['merchantCategoryId']) ? $params['merchantCategoryId'] : '';
//		$paxages = $params['paxages'];
//		$merchantId = $params['merchantId'];
		$tags = isset($params['tags'])?$params['tags']:"";
//		$searchtypetab = $params['searchtypetab'];
		$stateIds = isset($params['stateIds']) ? $params['stateIds'] : '';
		$regionIds = isset($params['regionIds']) ? $params['regionIds'] : '';
		$cityIds = isset($params['cityIds']) ? $params['cityIds'] : '';
		$zoneIds = isset($params['zoneIds']) ? $params['zoneIds'] : '';

//		$merchantIds = $params['merchantIds'];
		$merchantTagIds = isset($params['merchantTagIds']) ? $params['merchantTagIds'] : '';
//		$productTagIds = $params['productTagIds'];

		$rating = isset($params['rating']) ? $params['rating'] : '';

//		$availabilitytype = $params['availabilitytype'];
//		$itemtypes = $params['itemtypes'];
//		$groupresulttype = $params['groupresulttype'];

		$cultureCode =  isset($params['cultureCode']) ? $params['cultureCode'] : '';
		if(empty( $cultureCode )){
//			$cultureCode = JFactory::getLanguage()->getTag();
			$cultureCode = $GLOBALS['bfi_lang'];
		}

		$options['data']['calculate'] = 0;
		$options['data']['checkAvailability'] = 0;
		$filters = isset($params['filters']) ? $params['filters'] : null;
//				$filtersselected = BFCHelper::getFilterSearchParamsSession();
		if(empty($filters)){
			$filters = BFCHelper::getFilterSearchMerchantParamsSession();
		}
//		$resourceName = $params['resourceName'].'';
//		$refid = $params['refid'].'';

		if (!empty($refid) or !empty($resourceName))  {
//			$options['data']['calculate'] = 0;
//			$options['data']['checkAvailability'] = 0;
//
			if (isset($refid) && $refid <> "" ) {
				$options['data']['refId'] = '\''.$refid.'\'';
			}
			if (isset($resourceName) && $resourceName <> "" ) {
				$options['data']['resourceName'] = '\''. $resourceName.'\'';
			}
		}else{

//			$onlystay = $params['onlystay'];
//
//			$options['data']['calculate'] = $onlystay;
//			$options['data']['checkAvailability'] = $onlystay;
//
			if (isset($params['locationzone']) ) {
				$locationzone = $params['locationzone'];
			}
			if (!empty($merchantCategoryId) && $merchantCategoryId > 0) {
				$options['data']['merchantCategoryIds'] = '\'' .$merchantCategoryId.'\'';
			}

			$points = isset($params['points']) ? $params['points'] : '' ;
			if (isset($points) && $points !='') {
				$options['data']['points'] = '\'' . $points. '\'';
			}

			if (isset($locationzone) && $locationzone !='' && $locationzone !='0') {
				$options['data']['zoneIds'] = '\''. $locationzone . '\'';
			}

			if (!empty($tags)) {
				$options['data']['tagids'] = '\'' . $tags . '\'';
			}
		}


		if (isset($cultureCode) && $cultureCode !='') {
			$options['data']['cultureCode'] = '\'' . $cultureCode. '\'';
		}
		if (isset($searchid) && $searchid !='') {
			$options['data']['searchid'] = '\'' . $searchid. '\'';
		}

//		if (isset($merchantId) && $merchantId > 0) {
//			$options['data']['merchantid'] = $merchantId;
//		}

		if (isset($stateIds) && $stateIds !='') {
			$options['data']['stateIds'] = '\'' . $stateIds. '\'';
		}

		if (isset($regionIds) && $regionIds !='') {
			$options['data']['regionIds'] = '\'' . $regionIds. '\'';
		}

		if (isset($cityIds) && $cityIds !='') {
			$options['data']['cityIds'] = '\'' . $cityIds. '\'';
		}

		if (isset($zoneIds) && $zoneIds !='') {
			$options['data']['zoneIds'] = '\'' . $zoneIds. '\'';
		}

//		if (isset($merchantIds) && $merchantIds !='') {
//			$options['data']['merchantsList'] = '\'' . $merchantIds. '\'';
//		}

		if (isset($tags) && $tags !='') {
			$options['data']['tagids'] = '\'' . $tags. '\'';
		}
		if (isset($rating) && $rating !='') {
			$options['data']['mrcRatingIds'] = '\'' . $rating. '\'';
		}

		if (!empty($this->currentOrdering )) {
			$options['data']['orderby'] = '\'' . $this->currentOrdering . '\'';
			$options['data']['ordertype'] = '\'' . $this->currentDirection . '\'';
		}

		if(!empty( $filters )){
			if(isset( $filters['mrcrating'] )){
				$currRating = str_replace("|",",",$filters['mrcrating']);
				if(isset($rating) && $rating !=''){
					if (!empty($currRating))
                    {
						$currRating .= "," . $rating;

                    }else
                    {
                        $currRating =$rating;
                    }
				}

				$options['data']['mrcRatingIds'] = BFCHelper::getQuotedString($currRating) ;
			}
            //if(isset( $filters['avg'] )){
            //    $options['data']['mrcAvgs'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['avg'])) ;
            //}
			if(isset( $filters['mrcavg'] )){
				$options['data']['mrcAvgs'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['mrcavg'])) ;
			}

			if(!empty( $filters['merchantsservices'] )){
				$options['data']['merchantServiceIds'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['merchantsservices'])) ;
			}

			if(!empty( $filters['mrccategory'] )){
				$options['data']['merchantCategoryIds'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['mrccategory'])) ;
			}


			if(!empty( $filters['mrczones'] )){
				$options['data']['zoneIds'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['mrczones'])) ;
			}
			if(!empty( $filters['mrctags'] )){
				$currTags = str_replace("|",",",$filters['mrctags']);

				$options['data']['merchantTagsIds'] = BFCHelper::getQuotedString($currTags) ;
			}

		}

	}

	public function getGeographicZones() {
//			$cultureCode = JFactory::getLanguage()->getTag();
			$cultureCode = $GLOBALS['bfi_lang'];

		$data=array(
					'$format' => 'json',
					'cultureCode' => BFCHelper::getQuotedString($cultureCode),
			);
		$options = array(
				'path' => $this->urlGeographicZones,
				'data' => $data
			);
		$url = $this->helper->getQuery($options);

		$locationZones = null;

		$r = $this->helper->executeQuery($url,null,null,false);
		if (isset($r)) {
			$res = json_decode($r);
//			$locationZones = $res->d->results ?: $res->d;
			if (!empty($res->d->results)){
				$locationZones = $res->d->results;
			}elseif(!empty($res->d)){
				$locationZones = $res->d;
			}
			if (!empty($locationZones)){
				foreach( $locationZones as $result ) {
		//				LocationID
//						if (!empty($result->Name)){
//							$result->Name = $result->Name;
//						}
						if (!empty($result->GeographicZoneId)){
							$result->LocationZoneID = $result->GeographicZoneId;
						}
						if (!empty($result->Order)){
							$result->Weight = $result->Order;
						}

				}
			}
		}
		return $locationZones;

	}

	public function getMerchantCategoriesFromService($language='') {

		$options = array(
				'path' => $this->urlMerchantCategories,
				'data' => array(
						'$format' => 'json',
						'cultureCode' => BFCHelper::getQuotedString($language),
				)
		);
		$url = $this->helper->getQuery($options);

		$categoriesFromService = null;

		$r = $this->helper->executeQuery($url,null,null,false);
		if (isset($r)) {
			$res = json_decode($r);
//			$categories = $res->d->results ?: $res->d;
			if (!empty($res->d->results)){
				$categoriesFromService = $res->d->results;
			}elseif(!empty($res->d)){
				$categoriesFromService = $res->d;
			}
		}

		return $categoriesFromService;
	}

	public function getMerchantCategories($language='') {
//		$session = JFactory::getSession();
//		$categories = BFCHelper::getSession('getMerchantCategories'.$language, null , 'com_bookingforconnector');
//		if (!$session->has('getMerchantCategories','com_bookingforconnector')) {
//		if ($categories==null) {
			$categories = $this->getMerchantCategoriesFromService($language);
//			BFCHelper::setSession('getMerchantCategories'.$language, $categories, 'com_bookingforconnector');
//		}
		return $categories;
	}

	public function getMerchantCategoriesForRequest($language='') {
//		$session = JFactory::getSession();
//		$categories = isset($_SESSION['getMerchantCategoriesForRequest'.$language])?$_SESSION['getMerchantCategoriesForRequest'.$language]:null;// BFCHelper::getSession('getMerchantCategoriesForRequest'.$language, null , 'com_bookingforconnector');
//		if (!$session->has('getMerchantCategories','com_bookingforconnector')) {
//		$categories = BFCHelper::getSession('getMerchantCategoriesForRequest'.$language, null , 'com_bookingforconnector');
		$categories = null;
		if ($categories==null) {
			$options = array(
					'path' => $this->urlMerchantCategoriesRequest,
					'data' => array(
//							'$filter' => 'Enabled eq true and IsForRequest eq true ',
//							'$orderby' => 'Order asc ',
							'cultureCode' => BFCHelper::getQuotedString($language),
							'$format' => 'json'
					)
			);
			$url = $this->helper->getQuery($options);

			$categoriesFromService = null;

		$r = $this->helper->executeQuery($url,null,null,false);
			if (isset($r)) {
				$res = json_decode($r);
				//$categoriesFromService = $res->d->results ?: $res->d;
				if (!empty($res->d->results)){
					$categoriesFromService = $res->d->results;
				}elseif(!empty($res->d)){
					$categoriesFromService = $res->d;
				}
			}
			$categories=$categoriesFromService;
//			$categories=array();
			if (!empty($categoriesFromService)){
//				foreach( $categoriesFromService as $category) {
//					$newCat = new StdClass;
//					$newCat->MerchantCategoryId =  $category->MerchantCategoryId;
//					$newCat->Name = BFCHelper::getLanguage($category->Name, $language);
//
//	//				$newCat = array(
//	//					'MerchantCategoryId' => $category->MerchantCategoryId,
//	//					'Name' => BFCHelper::getLanguage($category->Name, $language)
//	//					);
//					$categories[]=$newCat;
//				}
//				$_SESSION['getMerchantCategoriesForRequest'.$language]=$categories;
//				BFCHelper::setSession('getMerchantCategoriesForRequest'.$language, $categories, 'com_bookingforconnector');
//				BFCHelper::setSession('getMerchantCategoriesForRequest'.$language, $categories, 'com_bookingforconnector');
			}
		}
		return $categories;
	}

	public function getServicesByMerchantsCategoryId($merchantCategoryId,$language='') {
//		$session = JFactory::getSession();
		$services = BFCHelper::getSession('getServicesByMerchantsCategoryId'.$language.$merchantCategoryId, null , 'com_bookingforconnector');
		if ($services==null) {
			$options = array(
					'path' => $this->urlGetServicesByMerchantsCategoryId,
					'data' => array(
							'merchantCategoryId' => $merchantCategoryId,
							'cultureCode' => BFCHelper::getQuotedString($language),
							'$format' => 'json'
					)
			);
			$url = $this->helper->getQuery($options);

			$services = null;

			$r = $this->helper->executeQuery($url);
			if (isset($r)) {
				$res = json_decode($r);
				if (!empty($res->d->results)){
					$services = $res->d->results;
				}elseif(!empty($res->d)){
					$services = $res->d;
				}
			}
			BFCHelper::setSession('getServicesByMerchantsCategoryId'.$language.$merchantCategoryId, $services, 'com_bookingforconnector');
		}
		return $services;
	}

	public function getMerchantsByIds($listsId,$language='') {
		$options = array(
				'path' => $this->urlGetMerchantsByIds,
				'data' => array(
					'ids' => '\'' .$listsId. '\'',
					'cultureCode' => BFCHelper::getQuotedString($language),
					'$format' => 'json'
				)
			);
		$url = $this->helper->getQuery($options);

		$merchants = null;

		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);
			if (!empty($res->d->results)){
				$merchants = json_encode($res->d->results);
			}elseif(!empty($res->d)){
				$merchants = json_encode($res->d);
			}
		}

		return $merchants;
	}

	public function getMerchantByCategoryId($merchanCategoryId) {// with random order is not possible to order by another field

		$options = array(
				'path' => $this->urlMerchants,
				'data' => array(
					/*'$skip' => $start,
					'$top' => $limit,*/
					'$format' => 'json'
//					,'$select' => 'MerchantId,Name,Rating'
				)
			);

		$options['data']['categoryIds'] = '\''.$merchanCategoryId .'\'';
		$startswith ="";
		$options['data']['startswith'] = '\'' . $startswith . '\'';

		$url = $this->helper->getQuery($options);

		$merchants = null;

		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);
			//$merchants = $res->d->results ?: $res->d;
			if (!empty($res->d->results)){
				$merchants = $res->d->results;
			}elseif(!empty($res->d)){
				$merchants = $res->d;
			}
			if(!empty($merchants)){
				shuffle($merchants);
			}

		}

		return $merchants;
	}
//	public function getMerchantsFromService($start, $limit, $ordering, $direction) {// with random order is not possible to order by another field
//
//		$params = $this->params;
//		$seed = $params['searchseed'];
//
//		$options = array(
//				'path' => $this->urlMerchants,
//				'data' => array(
//					/*'$skip' => $start,
//					'$top' => $limit,*/
//					'seed' => $seed,
//					'$format' => 'json'
////					,'$select' => 'MerchantId,Name,Rating'
//				)
//			);
//
//		if (isset($start) && $start >= 0) {
//			$options['data']['skip'] = $start;
//		}
//
//		if (isset($limit) && $limit > 0) {
//			$options['data']['top'] = $limit;
//		}
//
//		$this->applyDefaultFilter($options);
//
//
//		// adding other ordering to allow grouping
//		//$options['data']['$orderby'] = 'Rating desc';
//		if (isset($ordering) && !empty($ordering)) {
//			$options['data']['$orderby'] =  $ordering . ' ' . strtolower($direction);
//		}
//
//		$url = $this->helper->getQuery($options);
//
//		$merchants = null;
//
//		$r = $this->helper->executeQuery($url);
//		if (isset($r)) {
//			$res = json_decode($r);
//			//$merchants = $res->d->results ?: $res->d;
//			if (!empty($res->d->results)){
//				$merchants = $res->d->results;
//			}elseif(!empty($res->d)){
//				$merchants = $res->d;
//			}
////			if(!empty($merchants) && empty($ordering)){
////				shuffle($merchants);
////			}
//
//		}
//
//		return $merchants;
//	}

//	public function getTotal()
//	{
//		//$typeId = $this->getTypeId();
//		$options = array(
//				'path' => $this->urlMerchantsCount,
//				'data' => array()
//			);
//
//		if (isset($limit) && $limit > 0) {
//			$options['data']['top'] = $limit;
//		}
//
//		$this->applyDefaultFilter($options);
//
//		$url = $this->helper->getQuery($options);
//
//		$count = null;
//
//		$r = $this->helper->executeQuery($url);
//		if (isset($r)) {
//			$count = (int)$r;
//		}
//
//		return $count;
//	}

	public function populateState($ordering = NULL, $direction = NULL) {
//		$filter_order = JRequest::getCmd('filter_order');
//		$filter_order_Dir = JRequest::getCmd('filter_order_Dir');

		$searchseed = BFCHelper::getSession('searchseed', null, 'com_bookingforconnector');
		if (empty($searchseed)) {
			$searchseed = rand();
			BFCHelper::setSession('searchseed', $searchseed, 'com_bookingforconnector');
		}
//		$session = JFactory::getSession();
//		$searchseed = BFCHelper::getSession('searchseed', rand(), 'com_bookingforconnector');
//		if (!$session->has('searchseed','com_bookingforconnector')) {
//		if (empty($_SESSION['com_bookingforconnector'.'searchseed'])) {
//			BFCHelper::setSession('searchseed', $searchseed, 'com_bookingforconnector');
//		}
//	$filter_order = BFCHelper::getVar('filter_order','');
//	if(!empty($filter_order)){
//		$model->setOrdering($filter_order);
//	}
//
//	$filter_order_Dir = BFCHelper::getVar('filter_order_Dir','');
//
//	if(!empty($filter_order_Dir)){
//		$model->setDirection($filter_order_Dir);
//	}

		$curr_order = BFCHelper::getSession('filter_order', '', 'com_bookingforconnector_merchantlist');
		$filter_order = BFCHelper::getVar('filter_order','');
		if (!empty($filter_order)) {
			BFCHelper::setSession('filter_order', $filter_order, 'com_bookingforconnector_merchantlist');
			$curr_order = $filter_order;
		}
		$this->ordering = $curr_order;

		$curr_dir = BFCHelper::getSession('filter_order_Dir', '', 'com_bookingforconnector_merchantlist');
		$filter_order_Dir = BFCHelper::getVar('filter_order_Dir','');

		if (!empty($filter_order_Dir) ) {
			BFCHelper::setSession('filter_order_Dir', $filter_order_Dir, 'com_bookingforconnector_merchantlist');
			$curr_dir = $filter_order_Dir;
		}
		$this->direction = $curr_dir;
		$this->params = array(
			'newsearch' => BFCHelper::getVar('newsearch'),
			'typeId' => BFCHelper::getVar('typeId'),
			'startswith' => BFCHelper::getVar('startswith',''),
//			'show_rating' => BFCHelper::getVar('show_rating','1'),
//			'default_display' => BFCHelper::getVar('default_display','0'),
			'categoryId' => BFCHelper::getVar('categoryId'),
			'rating' => BFCHelper::getVar('rating'),
			'cityids' => BFCHelper::getVar('cityids'),
			'searchseed' => $searchseed
		);
//		return parent::populateState($filter_order, $filter_order_Dir);
	}

	public function getItems($ignorePagination = false, $jsonResult = false, $start = 0, $count = 20) {

		if ($this->currentData !== null){
			return $this->currentData;
		}
		else{
//			$start = $this->getState('list.start');
//			$count = $this->getState('list.limit');
			$this->retrieveItems($ignorePagination, $jsonResult, $start, $count);
		}
		return $this->currentData;
	}

	public function retrieveItems($ignorePagination = false, $jsonResult = false, $start = 0, $count = 20) {

		$count = $this->itemPerPage;
		$page = bfi_get_current_page() ;
		$start = (absint($page)-1)*$this->itemPerPage;
		if(!empty($_REQUEST['filter_order']) ){
			$items = $this->getSearchResults(
				$start,
				$count,
				$_REQUEST['filter_order'],
				$_REQUEST['filter_order_Dir'],
				$ignorePagination,
				$jsonResult
			);
		} else {
			$items = $this->getSearchResults(
				$start,
				$count,
				'',
				'',
				$ignorePagination,
				$jsonResult
			);
		}
		$this->currentData = $items;
	}

	public function getTotal()
	{
		if ($this->count !== null){
			return $this->count;
		}
		else{
			$this->retrieveItems();
			return $this->count;
		}

	}

	public function getSearchResults($start, $limit, $ordering, $direction, $ignorePagination = false, $jsonResult = false) {
		$this->currentOrdering = $ordering;
		$this->currentDirection = $direction;
		$params = array();
		$firstParams = $this->params;
//		$searchid = isset($params['searchid']) ? $params['searchid'] : '';
		$newsearch = isset($firstParams['newsearch']) ? $firstParams['newsearch'] : '1';

		$results = $this->currentData;
		if($newsearch == "1"){

			BFCHelper::setSearchMerchantParamsSession(null);
			BFCHelper::setFilterSearchMerchantParamsSession(null);
//			$start = 0;
//			$this->setState('list.start',$start);
			$params['startswith'] = $firstParams['startswith'];
			$params['rating'] = $firstParams['rating'];
			$params['merchantCategoryId'] = isset($firstParams['categoryId']) ? implode(",",$firstParams['categoryId']) : '';
			$params['cityIds'] = isset($firstParams['cityids']) ? implode(",",array_filter($firstParams['cityids'])) : '';
			$params['zoneIds'] = isset($firstParams['zoneIds']) ? implode(",",array_filter($firstParams['zoneIds'])) : '';
			$params['tags'] = isset($firstParams['tagId']) ? implode(",",$firstParams['tagId']) : '';
			$params['searchid'] = $firstParams['searchseed'];

			BFCHelper::setSearchMerchantParamsSession($params);

		}else{
			$params = BFCHelper::getSearchMerchantParamsSession();
			$filtersselected = BFCHelper::getVar('filters', null);
			if ($filtersselected == null) { //provo a recuperarli dalla sessione...
				$filtersselected = BFCHelper::getFilterSearchMerchantParamsSession();
			}

			BFCHelper::setFilterSearchMerchantParamsSession($filtersselected);
		}


		if ($results == null) {
			$options = array(
				'path' => $this->urlSearch,
				'data' => array(
						'$format' => 'json',
						'top' => 0
				)
			);

			if(!$ignorePagination){

				if (isset($start) && $start >= 0) {
					$options['data']['skip'] = $start;
				}

				if (isset($limit) && $limit > 0) {
					$options['data']['top'] = $limit;
				}
			}

			$this->applyDefaultFilter($options);

			// se bot setto un searchseed fisso in modo da diminuire le chiamate
			if (COM_BOOKINGFORCONNECTOR_ISBOT) {
				$options['data']['searchid'] = BFCHelper::getQuotedString('123456789');
			}

			$url = $this->helper->getQuery($options);

			$results = null;
			$r= null;
			if (COM_BOOKINGFORCONNECTOR_ISBOT) {
				$r = $this->helper->executeQuery($url,null,null,false);
			}else{
				$r = $this->helper->executeQuery($url);
			}
			if (isset($r)) {
				$res = json_decode($r);
				if (!empty($res->d->SearchAllMerchants)){
					$results = $res->d->SearchAllMerchants;
				}elseif(!empty($res->d)){
					$results = $res->d;
				}
			}


			$filtersenabled = array();
			if(!empty($results)){
				$filtersenabled = json_decode($results->FiltersString);
			}
			BFCHelper::setSearchMerchantParamsSession($params);
			if($newsearch == "1"){
				BFCHelper::setFirstFilterSearchMerchantParamsSession($filtersenabled);
			}
			BFCHelper::setEnabledFilterSearchMerchantParamsSession($filtersenabled);
		}
		$resultsItems = null;

		if(isset($results->ItemsCount)){
			$this->count = $results->ItemsCount;
			$resultsItems = json_decode($results->ItemsString);
		}

		return $resultsItems;

	}

	public function getMerchantsForSearch($text, $start, $limit, $ordering, $direction) {

//			$cultureCode = JFactory::getLanguage()->getTag();
			$cultureCode = $GLOBALS['bfi_lang'];
				//$typeId = $this->getTypeId();
		$options = array(
				'path' => $this->urlSearch,
				'data' => array(
					'cultureCode' => '\''. $cultureCode.'\'',
					'$format' => 'json'

				)
			);
		$options['data']['searchid'] = '\'' . uniqid('', true). '\'';

		if (isset($start) && $start >= 0) {
			$options['data']['skip'] = $start;
		}
		

		if (isset($limit) && $limit > 0) {
			$options['data']['top'] = $limit;
		}

		//$this->applyDefaultFilter($options);

//		$filter = '';

////		// get only enabled merchants because disabled are of no use
//		$this->helper->addFilter($filter, 'Enabled eq true', 'and');

		if (isset($text)) {
				$options['data']['resourceName'] = '\''. $text.'\'';
//			$this->helper->addFilter(
//				$filter,
//				'substringof(\'' . $text . '\',Name) eq true',
//				'and'
//			);
		}

//		if ($filter!='')
//			$options['data']['$filter'] = $filter;

		// adding other ordering to allow grouping
//		$options['data']['orderby'] = 'Rating';
//		$options['data']['ordertype'] = 'desc';
		if (isset($ordering)) {
//			$options['data']['$orderby'] .= ", " . $ordering . ' ' . strtolower($direction);
			$options['data']['orderby'] = '\'' . $ordering. '\'';
			$options['data']['ordertype'] = '\'' . strtolower($direction) . '\'';
		}

		$url = $this->helper->getQuery($options);

		$merchants = null;
		$results = null;

		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);
//			$merchants = $res->d->results ?: $res->d;
			if (!empty($res->d->SearchAllMerchants)){
				$results = $res->d->SearchAllMerchants;
			}elseif(!empty($res->d)){
				$results = $res->d;
			}
			if(isset($results->ItemsCount)){
				$merchants = json_decode($results->ItemsString);
			}
		}


		return $merchants;
	}

}
endif;