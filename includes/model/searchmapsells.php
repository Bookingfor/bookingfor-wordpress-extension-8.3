<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BookingForConnectorModelMerchants Model
 */
if ( ! class_exists( 'BookingForConnectorModelSearchMapSells' ) ) {

class BookingForConnectorModelSearchMapSells
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
		$this->urlSearch = '/GetMapAvailabilitiesByProductGroupId'; 
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
		$params = BFCHelper::getSearchMapSellsParamsSession();	

		$checkin = $params['checkin'];
		$checkout = $params['checkout'];
//		$persons = $params['paxes'];
//		$paxages = $params['paxages'];
		$productGroupId = $params['productGroupId'];
		$sectorId = $params['sectorId'];
		$cultureCode = $params['cultureCode'];

		if ((isset($checkin))) {
			$options['data']['checkIn'] = '\'' . $checkin->format('YmdHis') . '\'';
		}
		if ((!empty($checkout))) {
			$options['data']['checkOut'] = '\'' . $checkout->format('YmdHis') . '\'';
		}
		if (isset($productGroupId) && $productGroupId > 0) {
			$options['data']['productGroupId'] = $productGroupId;
		}
		if (isset($cultureCode) && $cultureCode !='') {
			$options['data']['cultureCode'] = '\'' . $cultureCode. '\'';
		}
		
//		$filters = BFCHelper::getFilterSearchMapSellsParamsSession();		
	}

	/*------ recupero disponibilità per settore --------*/
	public function GetMapAvailabilitiesByProductGroupId($productGroupId,$checkIn,$checkOut,$paxes,$paxages,$sectorId, $cultureCode = NULL){

		if(empty($cultureCode)){
			$cultureCode = $GLOBALS['bfi_lang'];
		}
		$options = array(
			'path' => $this->urlSearch,
			'data' => array(
					'$format' => 'json',
					'calculate' => 1,
					'checkAvailability' => 1,
					'checkStays' => 1,
					'cultureCode' =>  BFCHelper::getQuotedString($cultureCode),

			)
		);
			
		if ((isset($checkIn))) {
			$options['data']['checkIn'] = '\'' . $checkIn . '\'';
		}
		if ((!empty($checkOut))) {
			$options['data']['checkOut'] = '\'' . $checkOut . '\'';
		}
		if (isset($productGroupId) && $productGroupId > 0) {
			$options['data']['productGroupId'] = $productGroupId;
		}
		if (isset($cultureCode) && $cultureCode !='') {
			$options['data']['cultureCode'] = '\'' . $cultureCode. '\'';
		}
//		if (isset($sectorId) && $sectorId > 0) {
//			$options['data']['sectorId'] = '\'' . $sectorId. '\'';
//		}
//		if (isset($persons) && $persons > 0) {
//			$options['data']['persons'] = $persons;
//		}
//		if (isset($paxages) && $paxages !='') {
//			$options['data']['paxages'] = '\'' . $paxages. '\'';
//		}

		$currUser = BFCHelper::getSession('bfiUser',null, 'bfi-User');
		if($currUser!=null && !empty($currUser->Email)) {
				$options['data']['userId'] = '\'' . $currUser->Email . '\'';
		}


		$url = $this->helper->getQuery($options);

		$results = null;

		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);
			if (!empty($res->d->GetMapAvailabilitiesByProductGroupId)){
				$results = $res->d->GetMapAvailabilitiesByProductGroupId;
			}elseif(!empty($res->d)){
				$results = $res->d;
			}
		}
		if(isset($results->MapPositionsString)){
			$results->MapPositions = json_decode($results->MapPositionsString);
		}
		if(isset($results->PriceResultsString)){
			$results->PriceResults = json_decode($results->PriceResultsString);
		}
		if(isset($results->TagsString)){
			$results->Tags = json_decode($results->TagsString);
		}
		return $results;
	}

	public function getSearchResults($start, $limit, $ordering, $direction, $ignorePagination = false, $jsonResult = false,$cultureCode = "") {
		if (empty($cultureCode)){
//			$cultureCode = JFactory::getLanguage()->getTag();
			$cultureCode = $GLOBALS['bfi_lang'];
		}

		$this->currentOrdering = $ordering;
		$this->currentDirection = $direction;

		$params = BFCHelper::getSearchMapSellsParamsSession();
				
		$searchid = !empty($params['searchid']) ? $params['searchid'] : uniqid('', true);
		$newsearch = isset($params['newsearch']) ? $params['newsearch'] : '0';

		//$session = JFactory::getSession();
		$results = $this->currentData;

		if($newsearch == "1"){
			BFCHelper::setFilterSearchMapSellsParamsSession(null);
		}else{
			$filtersselected = BFCHelper::getVar('filters', null);
			if ($filtersselected == null) { //provo a recuperarli dalla sessione...
				$filtersselected = BFCHelper::getFilterSearchMapSellsParamsSession();
			}
			BFCHelper::setFilterSearchMapSellsParamsSession($filtersselected);
		}
			

		if ($results == null) {
			$options = array(
				'path' => $this->urlSearch,
				'data' => array(
						'$format' => 'json',
						'top' => 0,
						'cultureCode' =>  BFCHelper::getQuotedString($cultureCode),
//						'getFilters' => 1

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
					$options['data']['userId'] = '\'' . $currUser->Email . '\'';
			}

			$this->applyDefaultFilter($options);

			$url = $this->helper->getQuery($options);

			$results = null;

			$r = $this->helper->executeQuery($url);
			if (isset($r)) {
				$res = json_decode($r);
				if (!empty($res->d->GetMapAvailabilitiesByProductGroupId)){
					$results = $res->d->GetMapAvailabilitiesByProductGroupId;
				}elseif(!empty($res->d)){
					$results = $res->d;
				}
			}

						
			BFCHelper::setSearchMapSellsParamsSession($params);
			
		}
		return $results;
	}
	

//	public function getTotal()
//	{
//		if ($this->count !== null){
//			return $this->count;
//		}
//		else{
//			$this->retrieveItems();
//			return $this->count;
//		}
//
//	}
//	public function getTotalAvailable()
//	{
//		if ($this->availableCount !== null){
//			return $this->availableCount;
//		}
//		else{
//			$this->retrieveItems();
//			return $this->availableCount;
//		}
//	}
 
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