<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BookingForConnectorModelMerchants Model
categoryId:
	1: Merchant
	2: Vendite
	4: Risorse
	8: Pacchetti
	16: Offerte 


 */
if ( ! class_exists( 'BookingForConnectorModelTags' ) ) :

class BookingForConnectorModelTags
{
	private $urlTags = null;
	private $urlTagForSearch = null;
	private $urlTagsCount = null;
	private $urlTagsbyids = null;
	private $urlTagbyid = null;

	private $urlMerchant = null;
	private $urlMerchantCount = null;
	private $urlResources = null;
	private $urlResourcesCount = null;
	private $urlOffers = null;
	private $urlOffersCount = null;
	private $urlPackages = null;
	private $urlPackagesCount = null;
	private $count = null;
	private $params = null;
	private $itemPerPage = null;
	private $ordering = null;
	private $direction = null;

		

	private $helper = null;
	
	public function __construct($config = array())
	{
//		$this->helper = new wsQueryHelper(COM_BOOKINGFORCONNECTOR_WSURL, COM_BOOKINGFORCONNECTOR_APIKEY);
		$this->helper = new wsQueryHelper(null, null);
		$this->urlTags = '/GetTags';
		$this->urlTagForSearch = '/GetTagsForSearch';
		$this->urlTagsCount = '/Tags/$count/';
		$this->urlTagsbyids = '/GetTagsByIds';
		$this->urlTagbyid = '/GetTagById';
	
		$this->urlMerchants = '/GetMerchantsByTagIds';
		$this->urlMerchantsExt = '/SearchAllMerchants'; // '/GetMerchantsByTagIdsExt';
		$this->urlResourcesByTagIds = '/GetResourcesByTagIds';
		
		$this->urlMerchantsCount = '/GetMerchantsByTagIds';
		$this->urlResources = '/SearchAllLiteNew';
		$this->urlResourcesCount = '/GetMerchantResourcesCount';
		$this->urlOffers = null;
		$this->urlOffersCount = null;
		$this->urlPackages = null;
		$this->urlPackagesCount = null;

		$this->urlEvents = '/SearchEvents';

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
//		$params = $this->params;
//		
//		$typeId = $params['typeId'];
//		$categoryIds = $params['categoryId'];
//		
//		$filter = '';
//		// get only viewable services
//		//$this->helper->addFilter($filter, 'Viewable eq true', 'and');
//
//		if (isset($typeId) && $typeId > 0) {
//			$this->helper->addFilter(
//				$filter, 
//				'TypologyId eq ' . $typeId, 
//				'and'
//			);
//		}
//				
//		if ($filter!='')
//			$options['data']['$filter'] = $filter;
//
//		if (count($categoryIds) > 0)
//			$options['data']['categoryIds'] = '\''.implode('|',$categoryIds).'\'';
	}
	
//	public function getTags($language='', $categoryIds='', $start, $limit)  {
//		$session = JFactory::getSession();
//		$results = BFCHelper::getSession('getTags'.$language.$categoryIds, null , 'com_bookingforconnector');
//		if (!$session->has('getMerchantCategories','com_bookingforconnector')) {
//		if ($results==null) {
//			$results = $this->getTagsFromService($language, $categoryIds, $start, $limit);
//			BFCHelper::setSession('getTags'.$language.$categoryIds, $results, 'com_bookingforconnector');
//		}
//		return $results;
//	}
	public function getTags($language='', $categoryIds='', $start, $limit)  {




		$results = $this->getTagsFromService($language, $categoryIds, $start, $limit);


		return $results;
	}


	public function getTagsFromService($language='', $categoryIds='', $start, $limit) {
		if (empty($language)){
//			$language = JFactory::getLanguage()->getTag();
			$language = $GLOBALS['bfi_lang'];
		}
		$options = array(
				'path' => $this->urlTags,
				'data' => array(
					'cultureCode' => BFCHelper::getQuotedString($language),
					'$format' => 'json'
				)
			);

		if (!empty($categoryIds) ) {
			$options['data']['categoryIds'] = BFCHelper::getQuotedString($categoryIds);
		}

		if (isset($start) && $start >= 0) {
			$options['data']['skip'] = $start;
		}
		
		if (isset($limit) && $limit > 0) {
			$options['data']['top'] = $limit;
		}
		
		$this->applyDefaultFilter($options);
				
		$url = $this->helper->getQuery($options);
		
		$ret = null;
		
		$r = $this->helper->executeQuery($url,null,null,false);
		if (isset($r)) {
			$res = json_decode($r);
			if (!empty($res->d->results)){
				$ret = $res->d->results;
			}elseif(!empty($res->d)){
				$ret = $res->d;
			}
		}

		return $ret;
	}
	
	public function getTotal($type = '')
	{
		switch($type) {
			case 'merchants':
				return $this->getTotalMerchants();				
			case 'resources':
			default:
				return $this->getTotalResources();
		}
	}	

	

	public function getTotalResources()
	{
		
//		$params = $this->params;
//		$tagId = $params['tagId'];
//				
//		$options = array(
//				'path' => $this->urlResourcesCount,
//				'data' => array(
//					'$format' => 'json',
//					'tagids' => BFCHelper::getQuotedString($tagId),
//				)
//			);
//		
//		$url = $this->helper->getQuery($options);
//		
//		$count = null;
//		$this->applyDefaultFilter($options);
//				
//		$url = $this->helper->getQuery($options);
//		
//		$count = 0;
//		
//		$r = $this->helper->executeQuery($url);
//		if (isset($r)) {
//			$res = json_decode($r);
//			if (isset($res->d->__count)){
//				$count = (int)$res->d->__count;
//			}elseif(isset($res->d)){
//				$count = (int)$res->d;
//			}
//		}
		return $this->count;
	}

	public function getTotalMerchants()
	{
//		$params = $this->params;
//		$tagId = $params['tagId'];
//				
//		$options = array(
//				'path' => $this->urlMerchants,
//				'data' => array(
//					'$format' => 'json',
//					'tagids' => BFCHelper::getQuotedString($tagId),
//				)
//			);
//		
//		$url = $this->helper->getQuery($options);
//		
//		$count = null;
//			$options['data']['$inlinecount'] = 'allpages';
//			$options['data']['$top'] = 0;
//		$this->applyDefaultFilter($options);
//				
//		$url = $this->helper->getQuery($options);
//		
//		$count = 0;
//		
//		$r = $this->helper->executeQuery($url);
//		if (isset($r)) {
//			$res = json_decode($r);
//			if (isset($res->d->__count)){
//				$count = (int)$res->d->__count;
//			}elseif(isset($res->d)){
//				$count = (int)$res->d;
//			}
//		}
//		$this->count =  $count;
//		
//		return $count;
		return $this->count;
	}

	public function getTagsByIds($listsId,$language='',$viewContextType="") {// with randor order is not possible to otrder by another field
		if (empty($language)){
//			$language = JFactory::getLanguage()->getTag();
			$language = $GLOBALS['bfi_lang'];
		}
		$options = array(
				'path' => $this->urlTagsbyids,
				'data' => array(
					'$format' => 'json',
					'ids' =>  '\'' .$listsId. '\'',
					'cultureCode' => BFCHelper::getQuotedString($language)
				)
			);
		if (!empty($viewContextType) ) {
			$options['data']['viewContextType'] = $viewContextType;
		}
  
		$url = $this->helper->getQuery($options);
		
		$ret = null;
		
		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);
			if (!empty($res->d->results)){
				$ret = $res->d->results;
			}elseif(!empty($res->d)){
				$ret = $res->d;
			}
		}

		return $ret;
	}

	public function getTagById($id,$language='') {// with randor order is not possible to otrder by another field
		if (empty($language)){
//			$language = JFactory::getLanguage()->getTag();
			$language = $GLOBALS['bfi_lang'];
		}
		$options = array(
				'path' => $this->urlTagbyid,
				'data' => array(
					'$format' => 'json',
					'id' =>  $id,
					'cultureCode' => BFCHelper::getQuotedString($language)
				)
			);
  
		$url = $this->helper->getQuery($options);
		
		$ret = null;
		
		$r = $this->helper->executeQuery($url,null,null,false);
		if (isset($r)) {
			$res = json_decode($r);
			if (!empty($res->d->GetTagById)){
				$ret = $res->d->GetTagById;
			}elseif(!empty($res->d)){
				$ret = $res->d;
			}
		}

		return $ret;
	}



	public function populateState($ordering = NULL, $direction = NULL) {
		$this->params  =  array(
			'tagId' => BFCHelper::getVar('tagId'),
			'category' => BFCHelper::getVar('category'),
			'show_grouped' => BFCHelper::getVar('show_grouped'),
			'newsearch' => BFCHelper::getVar('newsearch',1),
			'state' => BFCHelper::getStayParam('state'),
		);
		
		//echo var_dump($defaultRequest);die();
//		$this->setState('params', $defaultRequest);

//		$filter_order = JRequest::getCmd('filter_order','Order');
//		$filter_order_Dir = JRequest::getCmd('filter_order_Dir','asc');		
//		return parent::populateState($filter_order, $filter_order_Dir);
	}
	
	public function getItem()
	{
		// Get a storage key.
//		$store = $this->getStoreId('getItem');

		// Try to load the data from internal storage.
//		if (isset($this->cache[$store]))
//		{
//			return $this->cache[$store];
//		}

		$params = $this->params;
		$tagId = $params['tagId'];
		
		$item = $this->getTagById($tagId);
		
		return $item;
		// Add the items to the internal cache.
//		$this->cache[$store] = $item;
//
//		return $this->cache[$store];
	}
	public function getItemsEvents() 
	{
		return $this->getItems('events');
	}	

	public function getItemsMerchants() 
	{
		return $this->getItems('merchants');
	}	
	public function getItemsOnSellUnit() 
	{
		return $this->getItems('onsell');
	}	
	public function getItemsResources() 
	{
		return $this->getItems('resources');
	}	


	public function getItems($type = '')
	{
		$params = $this->params;
		$tagId = $params['tagId'];
		// Get a storage key.
//		$store = $this->getStoreId('getItems' . $type.$tagId);

		$ignorePagination = false;
		$jsonResult = false;

		// Try to load the data from internal storage.
//		if (isset($this->cache[$store]))
//		{
//
//						echo "<!-- cache -->";			
//			return $this->cache[$store];
//		}
//		$currpage = (get_query_var('page')) ? get_query_var('page') : 1;
		$currpage = bfi_get_current_page() ;
		$items = null;
		switch($type) {
			case 'resources':
				$items = $this->getResourcesFromService(
					(absint($currpage)-1)*$this->itemPerPage,
					$this->itemPerPage,
					$this->ordering,
					$this->direction,
					$ignorePagination,
					$jsonResult
				);
				break;
			case 'merchants':
				$items = $this->getMerchantsByTagIdsExt(
					$tagId,
					(absint($currpage)-1)*$this->itemPerPage,
					$this->itemPerPage
					);
				break;
			case 'onsell':
//				$items = $this->getOnSellUnitFromService(
//					(absint($currpage)-1)*$this->itemPerPage,
//					$this->itemPerPage
//				);
				break;
			case 'events':
				$items = $this->getEventsByTagIds(
					$tagId,
					(absint($currpage)-1)*$this->itemPerPage,
					$this->itemPerPage
					);
				break;
			case '':
			default:				
				$items = $this->getTags(
					(absint($currpage)-1)*$this->itemPerPage,
					$this->itemPerPage,
					$this->ordering,
					$this->direction
				);
				break;
		}
			
		// Add the items to the internal cache.
//		$this->cache[$store] = $items;
//
//		return $this->cache[$store];
		return $items;
	}

	public function getStart($type = '')
	{

		$store = $this->getStoreId('getstart'.$type);

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		$start = $this->getState('list.start');
		$limit = $this->getState('list.limit');
		$total = $this->getTotal($type);
		if ($start > $total - $limit)
		{
			$start = max(0, (int) (ceil($total / $limit) - 1) * $limit);
		}

		// Add the total to the internal cache.
		$this->cache[$store] = $start;

		return $this->cache[$store];
	}
	
	public function getMerchantsFromService($start, $limit) {
		$params = $this->params;
		
		$tagId = $params['tagId'];

//		$cultureCode = JFactory::getLanguage()->getTag();
		
		$options = array(
				'path' => $this->urlMerchants,
				'data' => array(
					'tagids' => BFCHelper::getQuotedString($tagId),
//					'cultureCode' => BFCHelper::getQuotedString($cultureCode),
					'$format' => 'json'
				)
		);
		
		if (isset($start) && $start >= 0) {
			$options['data']['$skip'] = $start;
		}
		
		if (isset($limit) && $limit > 0) {
			$options['data']['$top'] = $limit;
		}	
								
		$url = $this->helper->getQuery($options);
				
		$ret = null;
		
		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);
			if (!empty($res->d->results)){
				$ret = $res->d->results;
			}elseif(!empty($res->d)){
				$ret = $res->d;
			}
		}

		return $ret;
	}
	public function getEventsByTagIds($tagids, $start = null, $limit = null) {
//		$cultureCode = JFactory::getLanguage()->getTag();
		$cultureCode = $GLOBALS['bfi_lang'];
		$params = $this->params;
		$newsearch = isset($params['newsearch']) ? $params['newsearch'] : '0';

		$currParamInSession = BFCHelper::getSearchEventParamsSession();
		if(isset($currParamInSession) && $currParamInSession != null ){
//			$currParamInSession['onlystay'] == false;
			$searchid = isset($currParamInSession['searchid']) ? $currParamInSession['searchid'] :  uniqid('', true);;
		}else{
			$currParamInSession = null;
			$searchid =  uniqid('', true);
		}
		$currParamInSession['searchid'] = $searchid;
	
		if($newsearch == "1"){
			BFCHelper::setFilterSearchEventParamsSession(null);
		}else{
			$filtersselected = BFCHelper::getVar('filters', null);
			if ($filtersselected == null) { //provo a recuperarli dalla sessione...
				$filtersselected = BFCHelper::getFilterSearchEventParamsSession();
			}
			BFCHelper::setFilterSearchEventParamsSession($filtersselected);
		}
	
		$options = array(
				'path' => $this->urlEvents,
				'data' => array(
					'tagids' => BFCHelper::getQuotedString($tagids),
					'cultureCode' => BFCHelper::getQuotedString($cultureCode),
					'getFilters' => 1,
					'$format' => 'json'
				)
		);
		
		// bisogna settare delle date altrimenti non trovo nulla!!!!!!!!!!!
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

		if (isset($start) && $start >= 0) {
			$options['data']['skip'] = $start;
		}
		
		if (isset($limit) && $limit > 0) {
			$options['data']['top'] = $limit;
		}	

		$filters = isset($params['filters']) ? $params['filters'] : null;
		if(empty($filters)){
			$filters = BFCHelper::getFilterSearchEventParamsSession();
		}
		if(!empty( $filters )){
			if(!empty( $filters['category'] )){
				$options['data']['categoryIds'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['category'])) ;
			}


			if(!empty( $filters['zones'] )){
				$options['data']['zoneIds'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['zones'])) ;
			}

			if(!empty( $filters['tags'] )){
				$currTags = str_replace("|",",",$filters['tags']);
				$options['data']['tagids'] = BFCHelper::getQuotedString($currTags) ;
			}

		}

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

		return $results;
	}


	public function getResourcesByTagIds($tagids, $start = null, $limit = null) {
//		$cultureCode = JFactory::getLanguage()->getTag();
		$cultureCode = $GLOBALS['bfi_lang'];
		
		$options = array(
				'path' => $this->urlResourcesByTagIds,
				'data' => array(
					'tagids' => BFCHelper::getQuotedString($tagids),
					'cultureCode' => BFCHelper::getQuotedString($cultureCode),
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
				
		$ret = null;
		
		$r = $this->helper->executeQuery($url,null,null,false);
		if (isset($r)) {
			$res = json_decode($r);
			if (!empty($res->d->results)){
				$ret = $res->d->results;
			}elseif(!empty($res->d)){
				$ret = $res->d;
			}
		}

		return $ret;
	}



	public function getMerchantsByTagIdsExt($tagids, $start = null, $limit = null) {
//		$cultureCode = JFactory::getLanguage()->getTag();
		$cultureCode = $GLOBALS['bfi_lang'];
		$currTagIds = "";
		if (is_array($tagids)) {
			$currTagIds = implode(',', $tagids);
		}else{
			$currTagIds = $tagids;
		}
		$searchid = uniqid('', true);

		$options = array(
				'path' => $this->urlMerchantsExt,
				'data' => array(
					'tagids' => BFCHelper::getQuotedString($currTagIds),
					'cultureCode' => BFCHelper::getQuotedString($cultureCode),
					'calculate' => 0,
					'getFilters' => 0,
					'searchid' => BFCHelper::getQuotedString($searchid),
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
				
		$ret = null;
		
//		$r = $this->helper->executeQuery($url);
//		if (isset($r)) {
//			$res = json_decode($r);
//			if (!empty($res->d->results)){
//				$ret = $res->d->results;
//			}elseif(!empty($res->d)){
//				$ret = $res->d;
//			}
//		}
			$results = null;

			$r = $this->helper->executeQuery($url, null, null, false, null, "searchid");
			if (isset($r)) {
				$res = json_decode($r);
				if (!empty($res->d->SearchAllMerchants)){
					$results = $res->d->SearchAllMerchants;
				}elseif(!empty($res->d)){
					$results = $res->d;
				}
			}
		if(isset($results->ItemsCount)){
			$this->count = $results->ItemsCount;
			$ret = json_decode($results->ItemsString);
			if(!empty($ret)){
				shuffle($ret);
			}

		}

		return $ret;
	}

	public function getResourcesFromService($start, $limit, $ordering, $direction, $ignorePagination = false, $jsonResult = false) {

//		$store = $this->getStoreId();
		
//		$language = JFactory::getLanguage()->getTag();
		$language = $GLOBALS['bfi_lang'];

		$this->currentOrdering = $ordering;
		$this->currentDirection = $direction;
		
		$params = $this->params;
		$newsearch = $params['newsearch'];
		$tagId = $params['tagId'];
		$searchid = "resources".$tagId ;

		$merchantResults = $params['show_grouped'];

		$currParamInSession = BFCHelper::getSearchParamsSession();
				
		if(isset($currParamInSession) && $currParamInSession != null ){
			$currParamInSession['onlystay'] == false;
			$searchid = isset($currParamInSession['searchid']) ? $currParamInSession['searchid'] :  uniqid('', true);
		}else{
			$currParamInSession = null;
			$currParamInSession['onlystay'] == false;
			$searchid =  uniqid('', true);
		}
		$currParamInSession['searchid'] = $searchid;

		BFCHelper::setSearchParamsSession($currParamInSession);
//		$resourcegroupsResults = $params['resourcegroupsResults'];
		
		$sessionkey = 'tags.' . $searchid . '.results';

//		$session = JFactory::getSession();
		$results = null;
								
//		if($newsearch == "0"){
//			$cachedresults = BFCHelper::getSession($sessionkey); //$_SESSION[$sessionkey];
//			try {
//				if (isset($cachedresults) && !empty($cachedresults) )
//				$results = (array)json_decode(gzuncompress(base64_decode($cachedresults)));
//			} catch (Exception $e) {
//	//			echo 'Exception: ',   $e->getMessage(), "<br />";
//				//echo 'Caught exception: ',  $e->getMessage(), "\n";
//			}
////		}else{
////			BFCHelper::setFilterSearchParamsSession(null);
//		}
		
		$options = array(
			'path' => $this->urlResources,
			'data' => array(
					'$format' => 'json',
					'top' => 0,
					'calculate' => 0,
					'checkAvailability' => 0,
					'checkStays' => 0,
					'cultureCode' =>  BFCHelper::getQuotedString($language),
					'lite' => 1,
					'tagids' => BFCHelper::getQuotedString($tagId)
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

			if (!empty($merchantResults) ) {
				$options['data']['groupResultType'] = $merchantResults;
//				if ($groupresulttype==1 || $groupresulttype==2) { //onbly for merchants 
					$options['data']['getBestGroupResult'] = 1;
//				}
			}
		if (isset($searchid) && $searchid !='') {
			$options['data']['searchid'] = '\'' . $searchid. '\'';
		}

//			$this->applyDefaultFilter($options);

			$url = $this->helper->getQuery($options);

			$results = null;

			$r = $this->helper->executeQuery($url);
			if (isset($r)) {
				$res = json_decode($r);
//				$results = $res->d->results ?: $res->d;
				if (!empty($res->d->SearchAllLiteNew)){
					$results = $res->d->SearchAllLiteNew;
				}elseif(!empty($res->d)){
					$results = $res->d;
				}
			}
		if(!empty($results)){
			$params['show_grouped'] = ($results->GroupResultType==1);
			$params['groupresulttype'] = ($results->GroupResultType==1);
			if ($results->GroupResultType==1) {
			    $params['merchantTagIds'] = $tagId;
			}else{
			    $params['productTagIds'] = $tagId;
				}
		
		
//			$params['resourcegroupsResults'] = ($results->GroupResultType==2);
			$merchantResults = $params['show_grouped'];
//			$resourcegroupsResults = $params['resourcegroupsResults'];
		}
		$resultsItems = null;

		if(isset($results->ItemsCount)){
			$this->count = $results->ItemsCount;
			$resultsItems = json_decode($results->ItemsString);
		}
		BFCHelper::setSearchParamsSession($params);
				
	
//		if (! $ignorePagination && isset($start) && (isset($limit) && $limit > 0 ) && !empty($results)) {
//			$results = array_slice($results, $start, $limit);
//			$params = $this->params;
////			$checkin = $params['checkin'];
////			$duration = $params['duration'];
////			$persons = $params['paxes'];
////			$paxages = $params['paxages'];
//		}
		if($jsonResult && !empty($results))	{
			$arr = array();

			foreach($resultsItems as $result) {
				$val= new StdClass;
				if ($merchantResults) {

					$val->MerchantId = $result->MerchantId; 
					$val->XGooglePos = $result->MrcLat;
					$val->YGooglePos = $result->MrcLng;
					$val->MerchantName = BFCHelper::getSlug($result->MrcName);
				}
				elseif ($resourcegroupsResults){
					$val->Resource = new StdClass;
					$val->Resource->ResourceId = $result->CondominiumId;
					$val->Resource->XGooglePos = $result->XGooglePos;
					$val->Resource->YGooglePos = $result->YGooglePos;
				}
				else { 
					$val->Resource = new StdClass;
					$val->Resource->ResourceId = $result->ResourceId;
					$val->Resource->XGooglePos = $result->ResLat;
					$val->Resource->ResourceName = BFCHelper::getSlug($result->ResName);
					$val->Resource->YGooglePos = $result->ResLng;

				}
				$arr[] = $val;
			}
			
			
			return json_encode($arr);
				
		}
		return $resultsItems;

		//return $jsonResult ? json_encode($results) : $results;
	}
	private function groupResultsByMerchant($results, $ordering, $direction) {
		if (isset($ordering) && is_array($results)) {
			// 'stay' ordering should take place before grouping
			if (strtolower($ordering) == 'stay') {
				usort($results, function($a,$b) use ( $ordering, $direction) {
					return BFCHelper::orderBy($a, $b, 'TotalPrice', $direction);
				});
			}
			if (strtolower($ordering) == 'offer') {
				usort($results, function($a,$b) use ( $ordering, $direction) {
					return BFCHelper::orderBy($a, $b, 'PercentVariation', $direction);
//					return BFCHelper::orderBySingleDiscount($a, $b, $direction);
				});
			}
		}
		
		$arr = array();
		foreach($results as $result) {
			if (!array_key_exists($result->MerchantId, $arr)) {
				$merchant = new stdClass();
				$merchant->MerchantId = $result->MerchantId;
				$merchant->Name = $result->MrcName;
				$merchant->XGooglePos = $result->MrcLat;
				$merchant->YGooglePos = $result->MrcLng;
				$merchant->MerchantTypeId = $result->MerchantTypeId;
				$merchant->Rating = $result->MrcRating;
				$merchant->RatingsContext = $result->RatingsContext;
				$merchant->PaymentType = $result->PaymentType;
				$merchant->reviewValue = $result->MrcAVG;
				$merchant->reviewCount = $result->MrcAVGCount;
				$merchant->LogoUrl = $result->LogoUrl;
				$merchant->Weight = $result->MrcWeight;
				$merchant->MrcTagsIdList = $result->MrcTagsIdList;
				$merchant->ImageUrl = $result->MrcImageUrl;
				$merchant->Resources = array();
				$merchant->Resources[] = $result;
				$arr[$merchant->MerchantId] = $merchant;
			}
			else {
				$merchant = $arr[$result->MerchantId];
					$merchant->Resources[] = $result;
			}
		}

		if (isset($ordering)) {
			switch (strtolower($ordering)) {
				case 'stay':
					usort($arr, function($a,$b) use ( $ordering, $direction) {
						return BFCHelper::orderByStay($a, $b, $direction);
					});
					break;
				case 'rating':
					usort($arr, function($a,$b) use ( $ordering, $direction) {
						return BFCHelper::orderBy($a, $b, 'Rating', $direction);
					});
					break;
				case 'reviewvalue':
					usort($arr, function($a,$b) use ( $ordering, $direction) {
						return BFCHelper::orderBy($a, $b, 'reviewValue', $direction);
					});
					break;
			   case 'offer':
					usort($arr, function($a,$b) use ( $ordering, $direction) {
						return BFCHelper::orderBy($a->Resources[0], $b->Resources[0], 'PercentVariation', $direction);
//						return BFCHelper::orderByDiscount($a, $b, $direction);
					});
					break;
				default:
				usort($arr, function($a,$b) use ( $ordering, $direction) {
					return BFCHelper::orderBy($a, $b, 'PaymentType', 'desc');
				});
			}
		}else{
			usort($arr, function($a,$b) use ( $ordering, $direction) {
				return BFCHelper::orderBy($a, $b, 'Weight', 'asc');
			});
			usort($arr->Resources[], function($a,$b) use ( $ordering, $direction) {
				return BFCHelper::orderBy($a, $b, 'ResWeight', 'asc');
			});
		}
		
		return $arr;
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