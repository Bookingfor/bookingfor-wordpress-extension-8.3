<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BookingForConnectorModelMerchants Model
 */
if ( ! class_exists( 'BookingForConnectorModelEvent' ) ) {

	class BookingForConnectorModelEvent
	{
		private $urlDetails = null;
	private $urlSearch = null;
		private $helper = null;
	private $currentOrdering = null;
	private $currentDirection = null;
	private $count = null;
	private $availableCount = null;

		public function __construct($config = array())
		{
		  $ws_url = COM_BOOKINGFORCONNECTOR_WSURL;
			$api_key = COM_BOOKINGFORCONNECTOR_API_KEY;
			$this->helper = new wsQueryHelper($ws_url, $api_key);
			$this->urlDetails = '/GetEventById';
		$this->urlSearch = '/SearchAllLiteNew';
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

		public function getDetails($resourceId, $language='') {
			if (empty($resourceId)) {
				return null;
			}
			if (empty($language)){
	//			$language = JFactory::getLanguage()->getTag();
				$language = $GLOBALS['bfi_lang'];
			}

			$resourceIdRef = $resourceId;
			$options = array(
					'path' => $this->urlDetails,
					'data' => array(
						'$format' => 'json',
						'cultureCode' => BFCHelper::getQuotedString($language),
						'id' =>$resourceId
					)
				);

			$url = $this->helper->getQuery($options);

			$resource = null;

//			$r = $this->helper->executeQuery($url,null,null,false);
			$r = $this->helper->executeQuery($url,null,null,false,"","",bfi_TagsScope::Event,$resourceId );
			if (isset($r)) {
				$res = json_decode($r);
				if (!empty($res->d->GetEventById)){
					$resource = $res->d->GetEventById;
				}elseif(!empty($res->d)){
					$resource = $res->d;
				}
				if(!empty($resource->Address->XPos) && !empty($resource->Address->YPos)){
					$resourceLat = $resource->Address->XPos;
					$resourceLon = $resource->Address->YPos;
					$currPoint = "0|" . $resourceLat . " " . $resourceLon . " 10000";
					$resource->Poi = BFCHelper::GetProximityPoi($currPoint);
				}

			}
			return $resource;
		}

		protected function populateState() {
			$resourceId = JRequest::getInt('resourceId');
			$defaultRequest =  array(
				'resourceId' => JRequest::getInt('resourceId'),
				'state' => BFCHelper::getStayParam('state'),
			);

			//echo var_dump($defaultRequest);die();
			$this->setState('params', $defaultRequest);

//			return parent::populateState();
		}

		public function getItem($resourceId)
		{
			$item = $this->getDetails($resourceId);
			return $item;
		}

	public function applyDefaultFilter(&$options) {
		$params = BFCHelper::getSearchParamsSessionforEvent();
		$searchid = !empty($params['searchid']) ? $params['searchid'] : uniqid('', true);
		$masterTypeId = $params['masterTypeId'];
		$checkin = $params['checkin'];
		$checkout = $params['checkout'];
		$checkFullPeriod = $params['checkFullPeriod'];
		$duration = $params['duration'];
		$persons = $params['paxes'];
		$calculateperson = $params['calculateperson'];
		$merchantCategoryId = $params['merchantCategoryId'];
		$paxages = $params['paxages'];
		$merchantId = $params['merchantId'];
		$tags = isset($params['tags'])?$params['tags']:"";
		$searchtypetab = $params['searchtypetab'];
		$stateIds = $params['stateIds'];
		$regionIds = $params['regionIds'];
		$cityIds = $params['cityIds'];
		$merchantIds = isset($params['merchantIds']) ? $params['merchantIds'] : '';
		$groupresourceIds = isset($params['groupresourceIds']) ? $params['groupresourceIds'] : '';

		$merchantTagIds = $params['merchantTagIds'];
		$productTagIds = $params['productTagIds'];
		$groupTagsIds = $params['groupTagsIds'];
		$minqt = isset($params['minqt']) ? $params['minqt'] : 1;
		$maxqt = isset($params['maxqt']) ? $params['maxqt'] : 1;
		$availabilitytype = $params['availabilitytype'];
		$itemtypes = $params['itemtypes'];
		$groupresulttype = $params['groupresulttype'];
		$merchantResults = $params['merchantResults'];
		if (isset($merchantResults) ) {
			if ($merchantResults==1 ) { //onbly for merchants
				$groupresulttype = $merchantResults ;
			}
		}

		$cultureCode = $params['cultureCode'];

		$filters = $params['filters'];
        //				$filtersselected = BFCHelper::getFilterSearchParamsSession();
		if(empty($filters)){
			$filters = BFCHelper::getFilterSearchEventParamsSession();
		}
		$resourceName = $params['resourceName'].'';
		$refid = $params['refid'].'';

		$variationPlanIds = isset($params['variationPlanIds']) ? $params['variationPlanIds'] : '';

		if (!empty($refid) or !empty($resourceName))  {
			$options['data']['calculate'] = 0;
			$options['data']['checkAvailability'] = 0;

			if (isset($refid) && $refid <> "" ) {
				$options['data']['refId'] = '\''.$refid.'\'';
			}
			if (isset($resourceName) && $resourceName <> "" ) {
				$options['data']['resourceName'] = '\''. $resourceName.'\'';
			}
		}else{

			$onlystay = $params['onlystay'];
			$options['data']['calculate'] = $onlystay;

//			$options['data']['checkAvailability'] = $onlystay;
			$bookableonly = $params['bookableonly'];
			if(empty($onlystay )){
				$options['data']['checkAvailability'] = 0;
			}
			if(!empty($bookableonly )){
				$options['data']['calculate'] = $bookableonly;
				$options['data']['checkAvailability'] = $bookableonly;
			}
//			$getallresults = $params['getallresults'];
//			if(!empty($getallresults )){
//				$options['data']['getAllResults'] = 0 ; //work inverse
//				$options['data']['checkAvailability'] = 1;
//			}

//			if (isset($params['locationzone']) ) {
//				$locationzone = $params['locationzone'];
//			}
			if (isset($params['zoneIds']) ) {
				$locationzone = $params['zoneIds'];
			}

			if (isset($masterTypeId) && $masterTypeId > 0) {
				$options['data']['masterTypeIds'] = '\'' .$masterTypeId.'\'';
			}

			if (!empty($merchantCategoryId) && $merchantCategoryId > 0) {
				$currmerchantCategoryId = array();
				if(!is_array($merchantCategoryId)){
					$merchantCategoryId = urldecode ($merchantCategoryId);
					$merchantCategoryId = explode(',',$merchantCategoryId);
				}
				$currmerchantCategoryId = array_values(array_filter($merchantCategoryId, function($a) {
					if (ctype_digit($a))
						return true;
					return false;
				}));
				$merchantCategoryId = implode(',',$currmerchantCategoryId);
				$options['data']['merchantCategoryIds'] = '\'' .$merchantCategoryId.'\'';
			}
			if (!empty($variationPlanIds)) {
				$options['data']['variationPlanIds'] = '\'' .$variationPlanIds.'\'';
			}

			if(empty($duration)){
				$duration = 0;
			}
			if ((isset($checkin))) {
				$options['data']['checkin'] = '\'' . $checkin->format('YmdHis') . '\'';
				$options['data']['duration'] = $duration;
			}
			if ((!empty($checkout))) {
				$options['data']['checkout'] = '\'' . $checkout->format('YmdHis') . '\'';
			}
//			if ((!empty($checkFullPeriod))) {
//				// se viene fatta una ricerca per ora deve essere tolta la durata!!!!
//				$options['data']['checkFullPeriod'] = $checkFullPeriod;
//				unset($options['data']['duration']);
//			}


			if (isset($availabilitytype) ) {
				$options['data']['availabilityTypes'] = '\'' .$availabilitytype .'\'';
			}
			if (isset($itemtypes) ) {
				$options['data']['itemTypeIds'] = '\'' .$itemtypes .'\'';
			}

			if (isset($groupresulttype) ) {
				$options['data']['groupResultType'] = $groupresulttype;
//				if ($groupresulttype==1 || $groupresulttype==2) { //onbly for merchants
//					$options['data']['getBestGroupResult'] = 1;
//				}
			}

			$points = isset($params['points']) ? $params['points'] : '' ;
			if (isset($points) && $points != '') {
				$options['data']['points'] = '\'' . $points. '\'';
			}

// NON passo le persone

//			$pckpaxages = BFCHelper::getStayParam('pckpaxages');
//			if (!empty($pckpaxages) ) {
//				$options['data']['paxages'] = '\'' .$pckpaxages .'\'';
//				$options['data']['paxes'] = count(explode("|",$pckpaxages));
//			}else {

				if (!empty($calculateperson) && isset($persons) && $persons > 0) {
					if ($persons >30) {
					    $persons = 30;
					}
					$options['data']['paxes'] = $persons;
					if (isset($paxages)) {
						$options['data']['paxages'] = '\'' . implode('|',$paxages) . '\'';
						// ciclo per aggiungere i dati
						$newpaxages = array();
						foreach ($paxages as $age) {
							if ($age >= BFCHelper::$defaultAdultsAge) {
								if ($age >= BFCHelper::$defaultSenioresAge) {
									array_push($newpaxages, $age.":".bfiAgeType::$Seniors);
								} else {
									array_push($newpaxages, $age.":".bfiAgeType::$Adult);
								}
							} else {
								array_push($newpaxages, $age.":".bfiAgeType::$Reduced);
							}
						}

						$options['data']['paxages'] = '\'' . implode('|',$newpaxages) . '\'';
					}else{
						$px = array_fill(0,$persons,BFCHelper::$defaultAdultsAge.":".bfiAgeType::$Adult);
						$options['data']['paxages'] = '\'' . implode('|',$px) . '\'';
					}
				}
//			}
//
//				$options['data']['pricetype'] = '\'' . 'rateplan' . '\'';

			if (isset($locationzone) && $locationzone !='' && $locationzone !='0') {
				$options['data']['zoneIds'] = '\''. $locationzone . '\'';
			}

			if (!empty($tags)) {
				$options['data']['tagids'] = '\'' . $tags . '\'';
			}
			if (!empty($minqt)) {
				$options['data']['minqt'] = $minqt ;
			}
			if (!empty($maxqt)) {
				$options['data']['maxqt'] = $maxqt ;
			}

		}

		if (isset($params['getBaseFiltersFor']) && !empty($params['getBaseFiltersFor'])) {
			$options['data']['getBaseFiltersFor'] = BFCHelper::getQuotedString($params['getBaseFiltersFor']);
		}

		if (isset($cultureCode) && $cultureCode !='') {
			$options['data']['cultureCode'] = '\'' . $cultureCode. '\'';
		}
		if (isset($searchid) && $searchid !='') {
			$options['data']['searchid'] = '\'' . $searchid. '\'';
		}

		if (isset($merchantId) && $merchantId > 0) {
			$options['data']['merchantid'] = $merchantId;
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

		if (isset($merchantIds) && $merchantIds !='') {
			$options['data']['merchantsList'] = '\'' . $merchantIds. '\'';
		}
		if (isset($groupresourceIds) && $groupresourceIds !='') {  // ricerca pe resourcegroupid
			$options['data']['resourceGroupId'] = $groupresourceIds;
			$options['data']['condominiumId'] = $groupresourceIds;
		}

		if (isset($merchantTagIds) && $merchantTagIds !='') {
			$options['data']['merchantTagsIds'] = '\'' . $merchantTagIds. '\'';
		}

		if (isset($productTagIds) && $productTagIds !='') {
			$options['data']['tagids'] = '\'' . $productTagIds. '\'';
		}
		if (isset($groupTagsIds) && $groupTagsIds !='') {
			$options['data']['groupTagsIds'] = '\'' . $groupTagsIds. '\'';
		}

		if (!empty($this->currentOrdering )) {
			$options['data']['orderby'] = '\'' . $this->currentOrdering . '\'';
			$options['data']['ordertype'] = '\'' . $this->currentDirection . '\'';
		}

//filters[price]:200;
//filters[resourcescategories]:6
//filters[rating]:0
//filters[avg]:0
//filters[meals]:
//filters[merchantsservices]:
//filters[resourcesservices]:
//filters[zones]:
//filters[bookingtypes]:
//filters[offers]:
//filters[tags]:
//filters[rooms]:
//filters[paymodes]:


		if(!empty( $filters )){
			$groupresulttypefilter = $merchantResults ;
			if(!empty( $filters['price'] )){
				$options['data']['priceRange'] = BFCHelper::getQuotedString($filters['price']) ;
				$options['data']['checkAvailability'] = 1 ;
			}

			if(!empty( $filters['merchantcategory'] )){
				$options['data']['merchantCategoryIds'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['merchantcategory'])) ;
			}
			if(!empty( $filters['productcategory'] )){
				$options['data']['masterTypeIds'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['productcategory'])) ;
			}
			if(!empty( $filters['rating'] )){
				if (isset($groupresulttypefilter) ) {
					if ($groupresulttypefilter==1 ) { //onbly for merchants
						$options['data']['mrcRatingIds'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['rating'])) ;
					}else{
						$options['data']['resRatingIds'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['rating'])) ;
					}
				}else{
					$options['data']['ratingIds'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['rating'])) ;
				}
			}
			if(!empty( $filters['mrcrating'] )){
				$options['data']['mrcRatingIds'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['mrcrating'])) ;
			}
			if(!empty( $filters['resrating'] )){
				$options['data']['resRatingIds'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['resrating'])) ;
			}
			if(!empty( $filters['grouprating'] )){
				$options['data']['groupRatingIds'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['grouprating'])) ;
			}
			if(!empty( $filters['tags'] )){
				if (isset($groupresulttypefilter) ) {
					if ($groupresulttypefilter==1 ) { //onbly for merchants
						$options['data']['merchantTagsIds'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['tags'])) ;
					}else{
						$options['data']['tagids'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['tags'])) ;
					}
				}else{
					$options['data']['tagids'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['tags'])) ;
				}
			}
			if(!empty( $filters['mrctags'] )){
				$options['data']['merchantTagsIds'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['mrctags'])) ;
			}
			if(!empty( $filters['restags'] )){
				$options['data']['tagids'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['restags'])) ;
			}
			if(!empty( $filters['grouptags'] )){
				$options['data']['groupTagsIds'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['grouptags'])) ;
			}
			if(!empty( $filters['mrcavg'] )){
				$options['data']['mrcAvgs'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['mrcavg'])) ;
			}

			if(!empty( $filters['resavg'] )){
				$options['data']['resAvgs'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['resavg'])) ;
			}

			if(!empty( $filters['groupavg'] )){
				$options['data']['groupAvgs'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['groupavg'])) ;
			}

//			if(!empty( $filters['avg'] )){
//				if (isset($groupresulttypefilter) ) {
//					if ($groupresulttypefilter==1 ) { //onbly for merchants
//						$options['data']['mrcAvgs'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['avg'])) ;
//					}else{
//						$options['data']['resAvgs'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['avg'])) ;
//					}
//				}else{
//					$options['data']['resAvgs'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['avg'])) ;
//				}
//			}
			if(!empty( $filters['mrcavgs'] )){
				$options['data']['mrcAvgs'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['mrcavgs'])) ;
			}
			if(!empty( $filters['resavg'] )){
				$options['data']['resAvgs'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['resavg'])) ;
			}


			if(!empty( $filters['meals'] )){
				$options['data']['includedMeals'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['meals'])) ;
			}
			if(!empty( $filters['merchantsservices'] )){
				$options['data']['merchantServiceIds'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['merchantsservices'])) ;
			}
			if(!empty( $filters['resourcesservices'] )){
				$options['data']['resourceServiceIds'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['resourcesservices'])) ;
			}
			if(!empty( $filters['zones'] )){
				$options['data']['zoneIds'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['zones'])) ;
			}
			if(!empty( $filters['mrczones'] )){
				$options['data']['mrcZoneIds'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['mrczones'])) ;
			}
			if(!empty( $filters['groupzones'] )){
				$options['data']['groupZoneIds'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['groupzones'])) ;
			}
			if(!empty( $filters['isbookable'] )){
				$options['data']['requirePaymentsOnly'] = 1 ;
				$options['data']['calculate'] = 1;
				$options['data']['checkAvailability'] = 1 ;
			}
			if(!empty( $filters['offers'] )){
				$options['data']['discountedPriceOnly'] = 1 ;
				$options['data']['calculate'] = 1;
				$options['data']['checkAvailability'] = 1 ;
			}
//			if(!empty( $filters['tags'] )){
//				$options['data']['tagids'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['tags'])) ;
//			}


			if(!empty( $filters['rooms'] )){
				$options['data']['bedRooms'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['rooms'])) ;
			}
			if(!empty( $filters['paymodes'] )){
				if (strpos($filters['paymodes'],"freecancellation")!== FALSE) {
					$options['data']['freeCancellation'] = 1 ;
				}
				if (strpos($filters['paymodes'],"freepayment")!== FALSE) {
					$options['data']['freeDeposit'] = 1 ;
				}
				if (strpos($filters['paymodes'],"freecc")!== FALSE) {
					$options['data']['payOnArrival'] = 1 ;
				}
			}
			if(!empty( $filters['checkAvailability'] )){
				$options['data']['checkAvailability'] = 1 ;
			}
		}


//		if ($filters!='')
//			$options['data']['$filter'] = $filter;
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


	public function getSearchResults($start, $limit, $ordering, $direction, $ignorePagination = false, $jsonResult = false) {
		$cultureCode = $GLOBALS['bfi_lang'];

		$this->currentOrdering = $ordering;
		$this->currentDirection = $direction;

		$params = BFCHelper::getSearchParamsSessionforEvent();

		$searchid = !empty($params['searchid']) ? $params['searchid'] : uniqid('', true);
		$newsearch = isset($params['newsearch']) ? $params['newsearch'] : '0';
		$merchantResults = $params['merchantResults'];
		$resourcegroupsResults = $params['resourcegroupsResults'];
		$results = $this->currentData;


//		if($newsearch == "1"){
//			BFCHelper::setFilterSearchParamsSession(null);
//		}
//
//		$filtersselected = BFCHelper::getVar('filters', null);
//		if ($filtersselected == null) { //provo a recuperarli dalla sessione...
//			$filtersselected = BFCHelper::getFilterSearchParamsSession();
//		}
//		BFCHelper::setFilterSearchParamsSession($filtersselected);

        $tmpUserId = BFCHelper::bfi_get_userId();

		if ($results == null) {
			$options = array(
				'path' => $this->urlSearch,
				'data' => array(
						'$format' => 'json',
						'top' => 0,
						'lite' => 1,
					'calculate' => 1,
//						'getSingleResultOnly' => 0,
						'getRelatedProducts' => 0,
						'getBestGroupResult' => 0,  // per recuperare i filtri
					'simpleResult' => 0,  // per recuperare i filtri
					'getRelatedProducts' => 0,
//						'groupResultType' => 0,  // per recuperare i filtri
						'checkStays' => 1,  // per recuperare i filtri
						'domainLabel' => BFCHelper::getQuotedString(COM_BOOKINGFORCONNECTOR_FORM_KEY),
//						'UserId' => BFCHelper::getQuotedString($tmpUserId),
						'cultureCode' => BFCHelper::getQuotedString($cultureCode),
						'getAllResults' => 1

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
				if (!empty($res->d->SearchAllLiteNew)){
					$results = $res->d->SearchAllLiteNew;
				}elseif(!empty($res->d)){
					$results = $res->d;
				}
			}


//			$filtersenabled = array();
			if(!empty($results)){
//				$filtersenabled = json_decode($results->FiltersString);
				$params['merchantResults'] = ($results->GroupResultType==1);
				$params['resourcegroupsResults'] = ($results->GroupResultType==2);
				$params['groupresulttype'] = $results->GroupResultType;
				$merchantResults = $params['merchantResults'];
				$resourcegroupsResults = $params['resourcegroupsResults'];
			}
			BFCHelper::setSearchParamsSessionforEvent($params);
//			if($newsearch == "1"){
//				BFCHelper::setFirstFilterSearchParamsSession($filtersenabled);
//			}
//			BFCHelper::setEnabledFilterSearchParamsSession($filtersenabled);
		}
		$resultsItems = null;

		if(isset($results->ItemsCount)){
			$this->count = $results->ItemsCount;
			$this->availableCount = $results->AvailableItemsCount;
			$resultsItems = json_decode($results->ItemsString);
		}

		return $resultsItems;

	}
	}

}