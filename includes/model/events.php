<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BookingForConnectorModelEvents Model
 */
 if ( ! class_exists( 'BookingForConnectorModelEvents' ) ) {

	class BookingForConnectorModelEvents
	{
		private $params = null;
		private $itemPerPage = 20;
		private $ordering = null;
		private $direction = null;
		private $currentOrdering = null;
		private $currentDirection = null;
		private $count = null;
		private $currentData = null;

		private $urlGetEventsByIds = null;
		private $urlSearch = null;
		private $urlCategories = null;

		private $helper = null;

		public function __construct($config = array())
		{
			$this->helper = new wsQueryHelper(COM_BOOKINGFORCONNECTOR_WSURL, COM_BOOKINGFORCONNECTOR_API_KEY);
			$this->EventsCount = 0;
			$this->urlGetEventsByIds = '/GetEventsByIds';
			$this->urlSearch = '/SearchEvents';
			$this->urlCategories = '/GetEventsCategory';
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
			$params = BFCHelper::getSearchEventParamsSession();

			$searchid = isset($params['searchid']) ? $params['searchid'] : '';
	//		$masterTypeId = $params['masterTypeId'];
	//		$checkin = $params['checkin'];
	//		$checkout = $params['checkout'];
	//		$duration = $params['duration'];
	//		$persons = $params['paxes'];
			$categoryIds = isset($params['categoryIds']) ? $params['categoryIds'] : '';
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

			$checkin = new DateTime('UTC');
			$checkout = new DateTime('UTC');
			$checkin->setTime(0,0,0);
			$checkout->setTime(0,0,0);
			$checkout->modify( '+1 year');
			if ((isset($checkin))) {
				$options['data']['checkin'] = '\'' . $checkin->format('YmdHis') . '\'';
			}
			if ((!empty($checkout))) {
				$options['data']['checkout'] = '\'' . $checkout->format('YmdHis') . '\'';
			}

			if(empty($filters)){
				$filters = BFCHelper::getFilterSearchEventParamsSession();
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
				if (!empty($categoryIds)) {
					$options['data']['categoryIds'] = '\'' .$categoryIds.'\'';
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
				if(!empty( $filters['category'] )){
					$options['data']['categoryIds'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['category'])) ;
				}


				if(!empty( $filters['zones'] )){
					$options['data']['zoneIds'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['zones'])) ;
				}

	//			if(!empty( $filters['offers'] )){
	//				$options['data']['discountedPriceOnly'] = 1 ;
	//			}
				if(!empty( $filters['tags'] )){
					$currTags = str_replace("|",",",$filters['tags']);
	//				if(!empty($tags )){
	//					$currTags .= "," . $tags;
	//				}
					$options['data']['tagids'] = BFCHelper::getQuotedString($currTags) ;
				}

			}

		}

	public function getCategories($language='') {

		$options = array(
				'path' => $this->urlCategories,
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

		public function GetEventsByIds($listsId,$language='') {
		if ($language==null) {
			$language = $GLOBALS['bfi_lang'];
		}
			$options = array(
					'path' => $this->urlGetEventsByIds,
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

		public function populateState($ordering = NULL, $direction = NULL) {

			$searchseed = BFCHelper::getSession('searchseed', null, 'com_bookingforconnector');
			if (empty($searchseed)) {
				$searchseed = rand();
				BFCHelper::setSession('searchseed', $searchseed, 'com_bookingforconnector');
			}

			$curr_order = BFCHelper::getSession('filter_order', '', 'com_bookingforconnector_eventlist');
			$filter_order = BFCHelper::getVar('filter_order','');
			if (!empty($filter_order)) {
				BFCHelper::setSession('filter_order', $filter_order, 'com_bookingforconnector_eventlist');
				$curr_order = $filter_order;
			}
			$this->ordering = $curr_order;

			$curr_dir = BFCHelper::getSession('filter_order_Dir', '', 'com_bookingforconnector_eventlist');
			$filter_order_Dir = BFCHelper::getVar('filter_order_Dir','');

			if (!empty($filter_order_Dir) ) {
				BFCHelper::setSession('filter_order_Dir', $filter_order_Dir, 'com_bookingforconnector_eventlist');
				$curr_dir = $filter_order_Dir;
			}
			$this->direction = $curr_dir;
			$this->params = array(
				'newsearch' => BFCHelper::getVar('newsearch'),
				'typeId' => BFCHelper::getVar('typeId'),
				'startswith' => BFCHelper::getVar('startswith',''),
				'categoryIds' => BFCHelper::getVar('categoryIds'),
				'rating' => BFCHelper::getVar('rating'),
				'cityids' => BFCHelper::getVar('cityids'),
				'searchseed' => $searchseed
			);
		}

		public function getItems($ignorePagination = false, $jsonResult = false, $start = 0, $count = 20) {

			if ($this->currentData !== null){
				return $this->currentData;
			}
			else{
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

	public function getEventsByTagIds($tagids, $start = null, $limit = null,$merchantId=null) {
//		$cultureCode = JFactory::getLanguage()->getTag();
		$cultureCode = $GLOBALS['bfi_lang'];

		$options = array(
				'path' => $this->urlSearch,
				'data' => array(
					'cultureCode' => BFCHelper::getQuotedString($cultureCode),
					'$format' => 'json'
				)
		);
		// temporaneo
		$checkin = new DateTime('UTC');
		$checkout = new DateTime('UTC');
		$checkin->setTime(0,0,0);
		$checkout->setTime(0,0,0);
		$checkout->modify( '+1 year');

		$options['data']['checkin'] = '\'' . $checkin->format('YmdHis') . '\'';
		$options['data']['checkout'] = '\'' . $checkout->format('YmdHis') . '\'';

		if(!empty( $tagids )){
			$options['data']['tagids'] = BFCHelper::getQuotedString($tagids) ;
		}
		if(!empty( $merchantId )){
			$options['data']['merchantId'] = $merchantId ;
		}
		if (isset($start) && $start >= 0) {
			$options['data']['skip'] = $start;
		}

		if (isset($limit) && $limit > 0) {
			$options['data']['top'] = $limit;
		}

		$url = $this->helper->getQuery($options);

		$results = null;

		$r = $this->helper->executeQuery($url, null, null, false, null, "checkin,checkout");
		if (isset($r)) {
			$res = json_decode($r);
			if (!empty($res->d->SearchEvents)){
				$results = $res->d->SearchEvents;
			}elseif(!empty($res->d)){
				$results = $res->d;
			}
		}

		$resultsItems = null;

		if(isset($results->TotalItemsCount)){
//			$this->count = $results->TotalItemsCount;
			$resultsItems = json_decode($results->ItemsString);
		}

//echo "<pre>";
//echo print_r($results);
//echo "</pre>";
//echo "<pre>";
//echo print_r($resultsItems);
//echo "</pre>";
//
		return $resultsItems;
	}

		public function getSearchResults($start, $limit, $ordering, $direction, $ignorePagination = false, $jsonResult = false) {
			$this->currentOrdering = $ordering;
			$this->currentDirection = $direction;
			$params = array();
			$firstParams = $this->params;
			$newsearch = isset($firstParams['newsearch']) ? $firstParams['newsearch'] : '1';

			$results = $this->currentData;
			if($newsearch == "1"){

				BFCHelper::setSearchEventParamsSession(null);
				BFCHelper::setFilterSearchEventParamsSession(null);
				$params['startswith'] = $firstParams['startswith'];
				$params['rating'] = $firstParams['rating'];
				$params['categoryIds'] = isset($firstParams['categoryIds']) ? implode(",",$firstParams['categoryIds']) : '';
				$params['cityIds'] = isset($firstParams['cityids']) ? implode(",",array_filter($firstParams['cityids'])) : '';
				$params['tags'] = isset($firstParams['tagId']) ? implode(",",$firstParams['tagId']) : '';
				$params['searchid'] = $firstParams['searchseed'];

				BFCHelper::setSearchEventParamsSession($params);

			}else{
				$params = BFCHelper::getSearchEventParamsSession();
				$filtersselected = BFCHelper::getVar('filters', null);
				if ($filtersselected == null) { //provo a recuperarli dalla sessione...
					$filtersselected = BFCHelper::getFilterSearchEventParamsSession();
				}

				BFCHelper::setFilterSearchEventParamsSession($filtersselected);
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

				$url = $this->helper->getQuery($options);

				$results = null;

				$r = $this->helper->executeQuery($url);
				if (isset($r)) {
					$res = json_decode($r);
					if (!empty($res->d->SearchEvents)){
						$results = $res->d->SearchEvents;
					}elseif(!empty($res->d)){
						$results = $res->d;
					}
				}


				$filtersenabled = array();
				if(!empty($results)){
					$filtersenabled = json_decode($results->FiltersString);
				}
				BFCHelper::setSearchEventParamsSession($params);
				if($newsearch == "1"){
					BFCHelper::setFirstFilterSearchEventParamsSession($filtersenabled);
				}
				BFCHelper::setEnabledFilterSearchEventParamsSession($filtersenabled);
			}
			$resultsItems = null;

			if(isset($results->TotalItemsCount)){
				$this->count = $results->TotalItemsCount;
				$resultsItems = json_decode($results->ItemsString);
			}

			return $resultsItems;

		}

	}
}