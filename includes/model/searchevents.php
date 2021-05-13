<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BookingForConnectorModelMerchants Model
 */
if ( ! class_exists( 'BookingForConnectorModelSearchEvent' ) ) {

class BookingForConnectorModelSearchEvent
{
	private $urlSearch = null;

	private $helper = null;
	private $currentOrdering = null;
	private $currentDirection = null;
	private $count = null;
	private $availableCount = null;

	private $currentData = null;
	private $params = null;
	private $itemPerPage = null;
	private $direction = null;

	public function __construct($config = array())
	{
		$this->helper = new wsQueryHelper(null, null);
		$this->urlSearch = '/SearchEvents'; 
	}

	public function setItemPerPage($itemPerPage) {
		if(!empty($itemPerPage)){
			$this->itemPerPage = $itemPerPage;
		}
	}
	public function setOrdering($ordering) {
		if(!empty($ordering)){
			$this->currentOrdering = $ordering;
		}
	}
	public function setDirection($direction) {
		if(!empty($direction)){
			$this->currentDirection = $direction;
		}
	}
	public function getOrdering() {
		return $this->currentOrdering;
	}
	public function getDirection() {
		return $this->currentDirection;
	}

	public function getParam() {
		return $this->params;
	}

	public function setParam($param) {
		$this->params = $param ;
	}

	public function applyDefaultFilter(&$options) {
		$params = BFCHelper::getSearchEventParamsSession();	

		$checkin = $params['checkin'];
		$checkout = $params['checkout'];
		$eventName = $params['eventName'].'';
		$cultureCode = $params['cultureCode'];
		$merchantId =  isset($params['merchantId']) ? $params['merchantId'] : 0; 
		$points = isset($params['points']) ? $params['points'] : '' ;
		$tagids = isset($params['tagids'])?$params['tagids']:"";
		$searchid = !empty($params['searchid']) ? $params['searchid'] : uniqid('', true);
		$merchantIds = isset($params['merchantIds']) ? $params['merchantIds'] : '';
		$stateIds = $params['stateIds'];
		$regionIds = $params['regionIds'];
		$cityIds = $params['cityIds'];
		$zoneIds = $params['zoneIds'];
		$getFilters = !empty($params['getFilters']) ? $params['getFilters'] : 1;
		$categoryIds = isset($categoryIds['categoryIds'])?$params['categoryIds']:"";
		$eventId = $params['eventId'];

		if ((isset($checkin))) {
			$options['data']['checkin'] = '\'' . $checkin->format('YmdHis') . '\'';
		}
		if ((!empty($checkout))) {
			$options['data']['checkout'] = '\'' . $checkout->format('YmdHis') . '\'';
		}
		if (isset($eventName) && $eventName !='') {
			$options['data']['eventName'] = '\'' . $eventName. '\'';
		}
		if (isset($cultureCode) && $cultureCode !='') {
			$options['data']['cultureCode'] = '\'' . $cultureCode. '\'';
		}
		if (isset($merchantId) && $merchantId > 0) {
			$options['data']['merchantid'] = $merchantId;
		}
		if (isset($eventId) && $eventId > 0) {
//			$options['data']['eventId'] = $eventId;
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
		}
		
		if ((!empty($points))) {
			$options['data']['points'] = '\'' . $points . '\'';
		}
		if (isset($tagids) && $tagids !='') {
			$options['data']['tagids'] = '\'' . $tagids. '\'';
		}
		if (isset($searchid) && $searchid !='') {
			$options['data']['searchid'] = '\'' . $searchid. '\'';
		}
		if (isset($merchantIds) && $merchantIds !='') {
			$options['data']['merchantIds'] = '\'' . $merchantIds. '\'';
		}
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
		if (isset($categoryIds) && $categoryIds !='') {
			$options['data']['categoryIds'] = '\'' . $categoryIds. '\'';
		}

		if (!empty($this->currentOrdering )) {
			$options['data']['orderby'] = '\'' . $this->currentOrdering . '\'';
			$options['data']['ordertype'] = '\'' . $this->currentDirection . '\'';
		}

		$filters = BFCHelper::getFilterSearchEventParamsSession();		
		if(!empty( $filters )){
			if(!empty( $filters['category'] )){
				$options['data']['categoryIds'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['category'])) ;
			}
			if(!empty( $filters['tags'] )){
					$options['data']['tagids'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['tags'])) ;
			}
			if(!empty( $filters['zones'] )){
				$options['data']['zoneIds'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['zones'])) ;
			}
		}

	}

	public function getSearchResults($start, $limit, $ordering, $direction, $ignorePagination = false, $jsonResult = false,$cultureCode = "", $getFilters=1) {
		if (empty($cultureCode)){
//			$cultureCode = JFactory::getLanguage()->getTag();
			$cultureCode = $GLOBALS['bfi_lang'];
		}

		$this->currentOrdering = $ordering;
		$this->currentDirection = $direction;

		$params = BFCHelper::getSearchEventParamsSession();
				
		$searchid = !empty($params['searchid']) ? $params['searchid'] : uniqid('', true);
		$newsearch = isset($params['newsearch']) ? $params['newsearch'] : '0';

		//$session = JFactory::getSession();
		$results = $this->currentData;

		if($newsearch == "1"){
			BFCHelper::setFilterSearchEventParamsSession(null);
		}else{
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
						'top' => 0,
						'cultureCode' =>  BFCHelper::getQuotedString($cultureCode),
						'getFilters' => $getFilters

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

			$currUser = BFCHelper::getSession('bfiUser',null, 'bfi-User');
			if($currUser!=null && !empty($currUser->Email)) {
					$options['data']['userid'] = '\'' . $currUser->Email . '\'';
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
	public function getTotalAvailable()
	{
		if ($this->availableCount !== null){
			return $this->availableCount;
		}
		else{
			$this->retrieveItems();
			return $this->availableCount;
		}
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
	
}
}