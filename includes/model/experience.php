<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BookingForConnectorModelMerchants Model
 */
if ( ! class_exists( 'BookingForConnectorModelResource' ) ) :

class BookingForConnectorModelExperience
{
	private $urlResource = null;
	private $urlStay = null;
	private $urlStayRatePlan = null;
	private $urlBookingTypes = null;
	private $helper = null;
	private $urlVariations = null;
	private $urlStartDate = null;
	private $urlStartDateByMerchantId = null;
	private $urlEndDate = null;
	private $urlEndDateByMerchantId = null;
	private $urlCheckInDates = null;
	private $urlCheckOutDates = null;
	private $urlCheckOutDatesPerTimes = null;
	private $urlCheckAvailabilityCalendar = null;
	private $urlUnitCategories = null;
	private $urlRating = null;
	private $urlRatingCount = null;
	private $urlRatingAverage = null;
	private $urlDiscountVariationDetails = null;
	private $urlListDiscounts = null;
	private $urlListVariations = null;
	private $urlGetRatePlansByResourceId = null;
	private $urlGetPrivacy = null;
	private $urlSearchAllCalculate = null;
	private $resourceid = null;
	private $itemPerPage = 10;
	private $urlGetCheckInDatesPerTimes = null;
	private $urlGetCheckInDatesTimeSlot = null;
	private $urlGetListCheckInDayPerTimes = null;
	private $urlGetMostRestrictivePolicyByIds = null;
	private $urlGetPolicyById = null;
	private $urlGetPolicyByIds = null;

	private $urlSearch = null;

	public function __construct($config = array())
	{
      $ws_url = COM_BOOKINGFORCONNECTOR_WSURL;
		$api_key = COM_BOOKINGFORCONNECTOR_API_KEY;
		$this->helper = new wsQueryHelper($ws_url, $api_key);
		$this->urlResource = '/GetExperienceById';// 
		$this->urlStay = '/GetStay';
		$this->urlStayRatePlan = '/GetRatePlansStay';
		$this->urlCompleteStayRatePlan = '/GetCompleteRatePlansStay';
		$this->urlBookingTypes = '/GetMerchantBookingTypes';
		$this->urlStartDate = '/GetStartDate';
		$this->urlStartDateByMerchantId = '/GetStartDateByMerchantId';
		$this->urlEndDate = '/GetEndDate';
		$this->urlEndDateByMerchantId = '/GetEndDateByMerchantId';
		$this->urlCheckInDates = '/GetCheckInDates';
		$this->urlCheckOutDates = '/GetCheckOutDateAvailabilitiesDetailed';
		$this->urlCheckOutDatesPerTimes = '/GetCheckOutDatesPerTimes';
		$this->urlCheckAvailabilityCalendar = '/CheckAvailabilityCalendar';
		$this->urlUnitCategories ='/ProductCategories'; // 
		$this->urlRating = '/GetReviews';
		$this->urlRatingCount = '/GetReviewsCount';
		$this->urlRatingAverage = '/GetResourceAverage';
		$this->urlDiscountVariationDetails = '/GetDiscountsByIds';
		$this->urlListDiscounts = '/Discounts';
		$this->urlListVariations = '/VariationPlans';
		$this->urlGetRatePlansByResourceId = '/GetRatePlansByResourceId';
		$this->urlGetPolicy = '/GetPolicy';
		$this->urlSearchAllCalculate = '/SearchAllNew';
		$this->urlGetCheckInDatesPerTimes = '/GetCheckInDatesPerTimes';
		$this->urlGetCheckInDatesTimeSlot = '/GetCheckInDatesTimeSlot';
		$this->urlGetListCheckInDayPerTimes = '/GetListCheckInDayPerTimes';
		$this->urlGetMostRestrictivePolicyByIds = '/GetMostRestrictivePolicyByIds';
		$this->urlGetPolicyById = '/GetPolicyById';
		$this->urlGetPolicyByIds = '/GetPolicyByIds';
		$this->urlSearch = '/SearchResources'; //'/SearchAllLiteNew';
		$this->urlGetRelatedResourceStays ='/SearchAllLiteNew'; // '/GetRelatedResourceStaysAll';

	}

	public function setResourceId($resourceid) {
		if(!empty($resourceid)){
			$this->resourceid = $resourceid;
		}
	}
	public function setItemPerPage($itemPerPage) {
		if(!empty($itemPerPage)){
			$this->itemPerPage = $itemPerPage;
		}
	}

	public function applyDefaultFilter(&$options, $overrideFilters = array()) {
		$params = $_SESSION['search.params'];
		$checkoutspan = '+1 day';

		if (empty($params['checkin'])){
			$params['checkin'] = new DateTime('UTC');
		}
		if (empty($params['checkout'])){
			$params['checkout'] = new DateTime('UTC');
			$params['checkout']->modify($checkoutspan);
		}
		if (empty($params['duration'])){
			$params['duration']= $params['checkin']->diff($params['checkout'])->days;
		}
		if (empty($params['paxes'])){
			$params['paxes'] = 2;
		}
		if (empty($params['paxages'])){
			$params['paxages'] = array(BFCHelper::$defaultAdultsAge,BFCHelper::$defaultAdultsAge);
		}
		$checkin = isset($overrideFilters) && isset($overrideFilters['checkin']) && !empty($overrideFilters['checkin']) ? $overrideFilters['checkin'] : $params['checkin'];
		$checkout = isset($overrideFilters) && isset($overrideFilters['checkout']) && !empty($overrideFilters['checkout']) ? $overrideFilters['checkout'] : $params['checkout'];
		$duration = isset($overrideFilters) && isset($overrideFilters['duration']) && !empty($overrideFilters['duration']) ? $overrideFilters['duration'] : $params['duration'];
		$persons = isset($overrideFilters) && isset($overrideFilters['paxes']) && !empty($overrideFilters['paxes']) ? $overrideFilters['paxes'] : $params['paxes'];
		$paxages = isset($overrideFilters) && isset($overrideFilters['paxages']) && !empty($overrideFilters['paxages']) ? $overrideFilters['paxages'] : $params['paxages'];
		$availabilityTypes = isset($overrideFilters) && isset($overrideFilters['availabilityTypes']) && !empty($overrideFilters['availabilityTypes']) ? $overrideFilters['availabilityTypes'] : (isset($params['availabilityTypes'])?$params['availabilityTypes']:null);
		$filter = '';

		if (!empty($refid) or !empty($resourceName))  {
			$options['data']['calculate'] = 0;

			if (isset($refid) && $refid <> "" ) {
				$options['data']['refId'] = '\''.$refid.'\'';
			}
			if (isset($resourceName) && $resourceName <> "" ) {
				$options['data']['resourceName'] = '\''. $resourceName.'\'';
			}
		}else{

			$onlystay = true;


			$options['data']['calculate'] = $onlystay;

//			if (isset($params['availabilitytype']) ) {
//				$availabilityTypes = $params['availabilitytype'];
//				$options['data']['availabilityTypes'] = '\''. $availabilityTypes.'\'';
//			}

			if (isset($availabilityTypes) ) {
				$options['data']['availabilityTypes'] = '\''. $availabilityTypes.'\'';
			}



			if (isset($params['locationzone']) ) {
				$locationzone = $params['locationzone'];
			}
			if (isset($masterTypeId) && $masterTypeId > 0) {
				$options['data']['masterTypeId'] = $masterTypeId;
			}

			if (isset($merchantCategoryId) && $merchantCategoryId > 0) {
				$options['data']['merchantCategoryId'] = $merchantCategoryId;
			}

			if ((isset($checkin) && !empty($checkin) ) && (isset($duration) && $duration > 0)) {
				$options['data']['checkin'] = '\'' . $checkin->format('Ymd') . '\'';
				$options['data']['duration'] = $duration;
			}

			$pckpaxages = BFCHelper::getStayParam('pckpaxages');
			if (!empty($pckpaxages) ) {
				$options['data']['paxages'] = '\'' .$pckpaxages .'\'';
				$options['data']['paxes'] = count(explode("|",$pckpaxages));
			}else {

			if (isset($persons) && $persons > 0) {
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
			}


				$options['data']['pricetype'] = '\'' . 'rateplan' . '\'';

			if (isset($locationzone) && $locationzone > 0) {
				$options['data']['zoneId'] = $locationzone;
			}
		}




		if (isset($cultureCode) && $cultureCode !='') {
			$options['data']['cultureCode'] = '\'' . $cultureCode. '\'';
		}

		if (isset($merchantId) && $merchantId > 0) {
			$options['data']['merchantid'] = $merchantId;
		}

		if ($filter!='')
			$options['data']['$filter'] = $filter;

	}

	public function getDiscountDetails($ids, $language='') {
		if ($ids == null) return null;
		if (empty($language)){
//			$language = JFactory::getLanguage()->getTag();
			$language = $GLOBALS['bfi_lang'];
		}
			$options = array(
					'path' => $this->urlDiscountVariationDetails,
					'data' => array(
						'$format' => 'json',
						'ids' => BFCHelper::getQuotedString($ids),
						'cultureCode' => BFCHelper::getQuotedString($language)
					)
				);

		$url = $this->helper->getQuery($options);
		$discount = null;
		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);
			//$resource = $res->d->results ?: $res->d;
			if (!empty($res->d->results)){
				$discount = json_encode($res->d->results);
			}elseif(!empty($res->d)){
				$discount = json_encode($res->d);
			}
		}
		return $discount;
	}


	public function GetCheckInDatesPerTimes($resourcesId, $checkin, $limitTotDays = 0) {
		if ($resourcesId == null) return null;
		$urlGetCheckInDatesPerTimes =  $this->urlGetCheckInDatesPerTimes;
		$options = array(
				'path' => $urlGetCheckInDatesPerTimes,
				'data' => array(
					'$format' => 'json',
					'resourceId' => $resourcesId,
					'checkin' => '\'' . $checkin->format('Ymd') . '\''
				)
			);
		if(!empty($limitTotDays)){
			$options['data']['limitTotDays'] = $limitTotDays;
		}
		$simpleTimePeriod = null;
		$url = $this->helper->getQuery($options);
		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);


			//$resource = $res->d->results ?: $res->d;
			if (!empty($res->d->results)){
				$simpleTimePeriod = $res->d->results;
			}elseif(!empty($res->d)){
				$simpleTimePeriod = $res->d;
			}
		}

		return $simpleTimePeriod;
	}

	public function GetListCheckInDayPerTimes($resourcesId, $checkin, $limitTotDays = 0) {
		if ($resourcesId == null) return null;
		$urlGetCheckInDatesPerTimes =  $this->urlGetListCheckInDayPerTimes;
		$options = array(
				'path' => $urlGetCheckInDatesPerTimes,
				'data' => array(
					'$format' => 'json',
					'resourceId' => $resourcesId,
					'checkin' => '\'' . $checkin . '\''
				)
			);
		if(!empty($limitTotDays)){
			$options['data']['limitTotDays'] = $limitTotDays;
		}
		$simpleTimePeriod = null;
		$url = $this->helper->getQuery($options);
		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);


			//$resource = $res->d->results ?: $res->d;
			if (!empty($res->d->results)) {
				$simpleTimePeriod = ($res->d->results);
			} else if(!empty($res->d)) {
				$simpleTimePeriod = ($res->d);
			}
		}

		return $simpleTimePeriod;
	}

	public function GetCheckInDatesTimeSlot($resourcesId, $checkin) {
		if ($resourcesId == null) return null;
		$urlGetCheckInDatesPerTimes =  $this->urlGetCheckInDatesTimeSlot;
		$options = array(
				'path' => $urlGetCheckInDatesPerTimes,
				'data' => array(
					'$format' => 'json',
					'resourceId' => $resourcesId,
					'checkin' => '\'' . $checkin->format('Ymd') . '\''
				)
			);
		$simpleTimeSlot = null;
		$url = $this->helper->getQuery($options);
		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);


			//$resource = $res->d->results ?: $res->d;
			if (!empty($res->d->results)){
				$simpleTimeSlot = json_encode($res->d->results);
			}elseif(!empty($res->d)){
				$simpleTimeSlot = json_encode($res->d);
			}
		}

		return $simpleTimeSlot;
	}

	public function GetDiscountsByResourceId($resourcesId, $hasRateplans) {


		if ($resourcesId == null) return null;
		$urlDiscounts =  $this->urlListDiscounts;
		$filter =  "Enabled eq true and substringof(',".$resourcesId.",', concat(concat(',',MerchantTypology),',') ) eq true and ((soloSulSito eq false) or (soloSulSito eq null) ) and EndDate ge datetime'".date("Y-m-d")."T00:00:00' " /* Tags eq \'extra\' and */;
			$options = array(
					'path' => $urlDiscounts,
					'data' => array(
						'$format' => 'json',
						'$filter' => $filter
					)
				);

		if ($hasRateplans) {
			//$urlDiscounts = $this->urlListVariations;
			$urlDiscounts = $this->urlGetRatePlansByResourceId;
			//$filter =  "Enabled eq true and VariationPlanType eq 'discount' and RatePlans/any(r: r/Units/any(u: u/UnitId  eq ".$resourcesId.")) and (ActivationEndDate ge datetime'".date("Y-m-d")."T00:00:00'  or ActivationEndDate eq null) " /* Tags eq \'extra\' and */;
			//$filter =  " RatePlans/any(r: r/Units/any(u: u/UnitId  eq ".$resourcesId.")) " /* Tags eq \'extra\' and */;
			$filter ='';
			$options = array(
					'path' => $urlDiscounts,
					'data' => array(
						'$format' => 'json',
						'resourceId' => $resourcesId,
						'$expand' => 'VariationPlans',
						'$filter' => $filter
					)
				);
		}
//		if ($hasRateplans) {
//			$options['data']['$expand'] = 'RatePlans/VariationPlans ';
//
//		}
//MerchantTypology
//		((data_fine >= GETDATE()) or ( data_fine is null) ) AND (Abilitato = 1)
//			AND IDmerchant = @IDmerchant
//			AND (solosulsito is NULL or solosulsito = @solosulsito)
//			AND ','+tipologiemerchant+',' like '%,'+@tipologiaMerchant+',%'
// ResourcesId=3353,3356,3359,3371,3386,3439,3479,3488,3494,3500,3502,4135,4201,4212,7053,8341,8342

		$url = $this->helper->getQuery($options);
		$discounts = null;
		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);


			//$resource = $res->d->results ?: $res->d;
			if (!empty($res->d->results)){
				$discounts = json_encode($res->d->results);
			}elseif(!empty($res->d)){
				$discounts = json_encode($res->d);
			}
		}

		return $discounts;
	}

	public function getResourceFromService($resource_id) {
		$resourceId = $resource_id;
		$resourceIdRef = $resource_id;
//		if (empty($language)){
//			$language = JFactory::getLanguage()->getTag();
			$language = $GLOBALS['bfi_lang'];
//		}
		$options = array(
				'path' => $this->urlResource, // sprintf($this->urlResource, $resourceId),
				'data' => array(
					'$format' => 'json',
					//'expand' => 'Merchant',
					'id' => $resourceId,
					'cultureCode' => BFCHelper::getQuotedString($language)
				)
			);

		$url = $this->helper->getQuery($options);

		$resource = null;

//		$r = $this->helper->executeQuery($url,null,null,false);
		$r = $this->helper->executeQuery($url,null,null,false,"","",bfi_TagsScope::Resource,$resourceId );
		if (isset($r)) {
			$res = json_decode($r);
			//$resource = $res->d->results ?: $res->d;
			if (!empty($res->d->GetExperienceById)){
				$resource = $res->d->GetExperienceById;
			}elseif(!empty($res->d)){
				$resource = $res->d;
			}
		}
		if(!empty($resource)){
			$resource->Merchant=BFCHelper::getMerchantFromServicebyId($resource->MerchantId);
			$resource->Tags = json_decode($resource->TagsString);
		}
		return $resource;
	}

	public function GetPolicyById($policyId, $cultureCode, $userId = null) {
		if(empty($cultureCode)){
			$cultureCode = $GLOBALS['bfi_lang'];
		}
		$options = array(
			'path' => $this->urlGetPolicyById,
			'data' => array(
				'policyId' => $policyId,
				'cultureCode' => '\'' . $cultureCode . '\'',
				'$format' => 'json'
			)
		);
		if(isset($userId) && !empty($userId)) {
			$options['data']['UserId'] = BFCHelper::getQuotedString($userId);
			$options['data']['domainLabel'] = BFCHelper::getQuotedString(COM_BOOKINGFORCONNECTOR_FORM_KEY);
		}
		$url = $this->helper->getQuery($options);
		$types = null;

		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);
			if (!empty($res->d->GetPolicyById)){
				$types = $res->d->GetPolicyById;
			}elseif(!empty($res->d)){
				$types = $res->d;
			}
		}
		return $types;
	}
	public function GetPolicyByIds($ids, $cultureCode, $userId = null) {
		if(empty($cultureCode)){
			$cultureCode = $GLOBALS['bfi_lang'];
		}
		$options = array(
			'path' => $this->urlGetPolicyByIds,
			'data' => array(
				'ids' => '\'' . $ids. '\'',
				'cultureCode' => '\'' . $cultureCode . '\'',
				'$format' => 'json'
			)
		);
		if(isset($userId) && !empty($userId)) {
			$options['data']['UserId'] = BFCHelper::getQuotedString($userId);
			$options['data']['domainLabel'] = BFCHelper::getQuotedString(COM_BOOKINGFORCONNECTOR_FORM_KEY);
		}

		$url = $this->helper->getQuery($options);
		$types = null;

		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);
			if (!empty($res->d->GetPolicyByIds)){
				$types = $res->d->GetPolicyByIds;
			}elseif(!empty($res->d)){
				$types = $res->d;
			}
		}
		return $types;
	}


	public function GetMostRestrictivePolicyByIds($policyIds, $cultureCode, $stayConfiguration = '', $priceValue = null, $days = null, $userId = null) {
		if(empty($cultureCode)){
			$cultureCode = $GLOBALS['bfi_lang'];
		}
		$options = array(
			'path' => $this->urlGetMostRestrictivePolicyByIds,
			'data' => array(
					'policyIds' => '\'' .$policyIds. '\'',
					'cultureCode' => '\'' . $cultureCode . '\'',
					'$format' => 'json'
				)
			);
		if (!empty($stayConfiguration) ) {
			$options['data']['stayConfiguration'] = '\'' . $stayConfiguration . '\'';
		}
		if(isset($userId) && !empty($userId)) {
			$options['data']['UserId'] = BFCHelper::getQuotedString($userId);
			$options['data']['domainLabel'] = BFCHelper::getQuotedString(COM_BOOKINGFORCONNECTOR_FORM_KEY);
		}
		if (!empty($priceValue) ) {
			$options['data']['priceValue'] = $priceValue;
		}
		if (!empty($days) ) {
			$options['data']['days'] = $days;
		}
		$url = $this->helper->getQuery($options);
		$types = null;

		$r = $this->helper->executeQuery($url,"POST");
		if (isset($r)) {
			$res = json_decode($r);
			if (!empty($res->d->GetMostRestrictivePolicyByIds)){
				$types = $res->d->GetMostRestrictivePolicyByIds;
			}elseif(!empty($res->d)){
				$types = $res->d;
			}
		}
		return $types;
	}


	public function GetRelatedResourceStays($merchantId,$relatedProductid,$excludedIds,$checkin,$duration,$paxages,$variationPlanId,$language="",$resourcegroupId ,$checkout,$checkFullPeriod,$itemTypeIds="") {

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
        $tmpUserId = BFCHelper::bfi_get_userId();

		$options = array(
//				'path' =>  $this->urlGetRelatedResourceStays,
				'path' =>  $this->urlSearch,
				'data' => array(
					'getAllResults' => 1,
					'lite' => 1,
					'calculate' => 1,
					'top' => 10,
					'skip' => 0,
					'checkin' => '\'' . $checkin->format('YmdHis') . '\'',
					'checkout' => '\'' . $checkout->format('YmdHis') . '\'',
					'duration' => $duration,
					'paxages' => '\'' . implode('|',$newpaxages) . '\'',
					'paxes' => count($paxages),
					'getSingleResultOnly' => 1,
					'getFilters' => 1,  // per recuperare i filtri
					'simpleResult' => 0,  // per recuperare i filtri
					'checkAvailability' => 1,  // per recuperare i filtri
					'getBestGroupResult' => 0,  // per recuperare i filtri
					'groupResultType' => 0,  // per recuperare i filtri
					'checkStays' => 1,  // per recuperare i filtri
					'getUpSellproducts' => 1, // per recuperare i prodotti in upsell
					'domainLabel' => BFCHelper::getQuotedString(COM_BOOKINGFORCONNECTOR_FORM_KEY),
					'$format' => 'json',
					'viewContextType' => 1, // contesto per la visualizzazione dei tag....
				)
			);

			//orderby
			$orderby = 'priority';
			$ordertype = 'asc';
			if(isset( $relatedProductid ) && !empty($relatedProductid)){
				$orderby = 'resourceid:' . $relatedProductid . "|priority|price";
				$options['data']['orderby'] = '\'' . $orderby . '\'';
			}else{
				$options['data']['orderby'] = '\'' . $orderby . ';' . $ordertype.  '|priority|price\'';
//				$options['data']['ordertype'] = '\'' . $ordertype . '\'';
			}

		if (!empty($merchantId)) {
			$options['data']['merchantId'] = $merchantId;
		}
		if (!empty($language)) {
			$options['data']['cultureCode'] = '\'' . $language . '\'';
		}
		if (!empty($variationPlanId)) {
			$options['data']['variationPlanIds'] = '\'' .$variationPlanId . '\'';
		}
		if (!empty($resourcegroupId)) {
			$options['data']['resourceGroupId'] = $resourcegroupId;
			$options['data']['condominiumId'] = $resourcegroupId;
		}
		if ((!empty($checkFullPeriod))) {
			// se viene fatta una ricerca per ora deve essere tolta la durata!!!!
			$options['data']['checkFullPeriod'] = $checkFullPeriod;
			$options['data']['availabilityTypes'] = BFCHelper::getQuotedString("2");
			//unset($options['data']['duration']);
		}

		$currUser = BFCHelper::getSession('bfiUser',null, 'bfi-User');
		if($currUser!=null && !empty($currUser->Email)) {
			$options['data']['userid'] = '\'' . $currUser->Email . '\'';
		}
				
		if ((isset($itemTypeIds))) {
			$options['data']['itemTypeIds'] =  '\'' .$itemTypeIds . '\'';
		}

		$filters = BFCHelper::getVar('filters');
		if(!empty( $filters )){
			$groupresulttypefilter = 0; // $merchantResults ;
			if(!empty( $filters['price'] )){
				$options['data']['priceRange'] = BFCHelper::getQuotedString($filters['price']) ;
//				$options['data']['checkAvailability'] = 1 ;
			}
			if(!empty( $filters['resourcescategories'] )){
				$options['data']['masterTypeIds'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['resourcescategories'])) ;
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

			if(!empty( $filters['avg'] )){
				if (isset($groupresulttypefilter) ) {
					if ($groupresulttypefilter==1 ) { //onbly for merchants
						$options['data']['mrcAvgs'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['avg'])) ;
					}else{
						$options['data']['resAvgs'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['avg'])) ;
					}
				}else{
					$options['data']['resAvgs'] = BFCHelper::getQuotedString(str_replace("|",",",$filters['avg'])) ;
				}
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
			if(!empty( $filters['bookingtypes'] )){
				$options['data']['requirePaymentsOnly'] = 1 ;
//				$options['data']['calculate'] = 1;
//				$options['data']['checkAvailability'] = 1 ;
			}
			if(!empty( $filters['offers'] )){
				$options['data']['discountedPriceOnly'] = 1 ;
//				$options['data']['calculate'] = 1;
//				$options['data']['checkAvailability'] = 1 ;
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
//			if(!empty( $filters['checkAvailability'] )){
//				$options['data']['checkAvailability'] = 1 ;
//			}
		}


		$url = $this->helper->getQuery($options);

		$resultsItems = null;

		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);
//			if (!empty($res->d->SearchAllLiteNew)){
//				$resultsItems = $res->d->SearchAllLiteNew;
			if (!empty($res->d->SearchResources)){
				$resultsItems = $res->d->SearchResources;
			}elseif(!empty($res->d)){
				$resultsItems = $res->d;
			}
		}
		return $resultsItems;
	}

	public function GetCompleteRatePlansStayWP($resourceId,$checkin,$duration,$paxages,$selectablePrices,$packages,$pricetype,$ratePlanId,$variationPlanId,$language="",$merchantBookingTypeId = "", $getAllResults=false,$resourceItemId=null ,$timeSlotId=null, $getAllPaxConfigurations = null) {
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
		$options = array(
				'path' =>  $this->urlCompleteStayRatePlan,
				'data' => array(
					'resourceId' => $resourceId,
					'checkin' => '\'' . $checkin->format('YmdHis') . '\'',
					'duration' => $duration,
//					'paxages' => '\'' . implode('|',$paxages) . '\'',
					'paxages' => '\'' . implode('|',$newpaxages) . '\'',
					'$format' => 'json'
				)
			);
		if (!empty($language)) {
			$options['data']['language'] = '\'' . $language . '\'';
		}
		if (!empty($resourceItemId)) { // per spiaggia
			$options['data']['resourceItemId'] = $resourceItemId;
		}
		if (!empty($variationPlanId)) {
			$options['data']['variationPlanId'] = $variationPlanId;
		}
		if (!empty($ratePlanId)) {
			$options['data']['ratePlanId'] = $ratePlanId;
		}

		if(!empty($selectablePrices)){
			$options['data']['selectablePrices'] = '\'' . $selectablePrices . '\'';
		}
		if(!empty($merchantBookingTypeId)){
			$options['data']['merchantBookingTypeId'] = $merchantBookingTypeId;
		}
		if(!empty($getAllResults)){
			$options['data']['exploded'] = $getAllResults ? 1: 0;
		}

		$currUser = BFCHelper::getSession('bfiUser',null, 'bfi-User');
		if($currUser!=null && !empty($currUser->Email)) {
			$options['data']['userid'] = '\'' . $currUser->Email . '\'';
		}
		if(!empty($timeSlotId)){
			$options['data']['timeSlotId'] = $timeSlotId ;
		}
		if(!empty($getAllPaxConfigurations)){
			$options['data']['getAllPaxConfigurations'] = $getAllPaxConfigurations ;
		}

		$url = $this->helper->getQuery($options);


		$ratePlansStay = new stdClass;

		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);
			if (!empty($res->d->results)){
				$ratePlansStay = $res->d->results;
			}elseif(!empty($res->d)){
				$ratePlansStay = $res->d;
			}

		}
		return $ratePlansStay;
	}




	public function getCompleteRateplansStayFromParameter($resourceId,$checkin,$duration,$paxages,$selectablePrices,$packages,$pricetype,$ratePlanId,$variationPlanId,$language="",$availabilitytype=1 ) {
		$checkIn = $checkin->format('YmdHis');

		if ($availabilitytype == 0 || $availabilitytype ==1 ) // product TimePeriod
		{
			$checkIn = $checkin->format('Ymd');
		}

		$options = array(
				'path' =>  $this->urlCompleteStayRatePlan,
				'data' => array(
					'resourceId' => $resourceId,
					'checkin' => '\'' . $checkIn . '\'',
					'duration' => $duration,
					'paxages' => '\'' . implode('|',$paxages) . '\'',
					'$format' => 'json'
				)
			);
		if (!empty($language)) {
			$options['data']['language'] = '\'' . $language . '\'';
		}
		if (!empty($variationPlanId)) {
			$options['data']['variationPlanId'] = $variationPlanId;
		}
		if (!empty($ratePlanId)) {
			$options['data']['ratePlanId'] = $ratePlanId;
		}

		if(!empty($selectablePrices)){
			$options['data']['selectablePrices'] = '\'' . $selectablePrices . '\'';
		}


		$url = $this->helper->getQuery($options);

		$stay = new stdClass;

		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);
				$foundStay = false;
				$ratePlans =  array();
				if (!empty($res->d)) {
					foreach ($res->d as $ratePlanStay) {
						$rs = $ratePlanStay;
						if (!$foundStay && $ratePlanStay != null && $ratePlanStay->SuggestedStay != null) {
							$rs->CalculatedPricesDetails = json_decode($ratePlanStay->CalculatedPricesString);
							$rs->SelectablePrices = json_decode($ratePlanStay->CalculablePricesString);
							$rs->CalculatedPackages = json_decode($ratePlanStay->PackagesString);
							$rs->DiscountVariation = null;
							if(!empty($ratePlanStay->Discount)){
								$rs->DiscountVariation = $ratePlanStay->Discount;

							}
							$rs->SupplementVariation =null;
							if(!empty($ratePlanStay->Supplement)){
								$rs->SupplementVariation = $ratePlanStay->Supplement;
							}

							$allVar = json_decode($ratePlanStay->AllVariationsString);
							$rs->Variations= [];
							foreach ($allVar as $currVar) {
								$rs->Variations[] = $currVar;
							}

							$foundStay = true;
						}
						$ratePlans[] = $rs;
					}
				}
				$stay = $ratePlans;
			}
		return $stay;
	}

	public function getStayFromServiceFromParameter($resourceId,$ci,$du,$px,$ex,$pkgs,$pt,$rpId,$vpId,$hasRateplans ) {
			$options = array(
				'path' =>  $hasRateplans ? $this->urlStayRatePlan : $this->urlStay, //  BFCHelper::isRatePlanStay() ? $this->urlStayRatePlan : $this->urlStay,
				'data' => array(
					'resourceId' => $resourceId,
					'checkin' => '\'' . $ci->format('Ymd') . '\'',
					'duration' => $du,
					'paxages' => '\'' . implode('|',$px) . '\'',
					'$format' => 'json'
				)
			);

//		if ($hasRateplans) {
			$options['data']['ratePlanId'] = $rpId;
			if(!empty($ex)){
			$options['data']['selectablePrices'] = '\'' . $ex . '\'';
			}
//		} else {
//			$options['data']['extras'] = '\'' . $ex . '\'';
//			$options['data']['priceType'] = '\'' . $pt . '\'';
//		}

		$url = $this->helper->getQuery($options);

		$stay = null;

		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			if ($hasRateplans) {
				$foundStay = false;
				$ratePlans =  array();
				foreach ($res->d as $ratePlanStay) {
					$ratePlans[] = $ratePlanStay;
					if (!$foundStay && $ratePlanStay != null && $ratePlanStay->SuggestedStay != null) {
						$stay = $ratePlanStay->SuggestedStay;
						$stay->RatePlanStay = $ratePlanStay;
						unset($stay->RatePlanStay->SuggestedStay); // remove for encoding json. prevent php5.5 JSON_ERROR_RECURSION
						$foundStay = true;
					}
				}
				$stay->RatePlans = $ratePlans;
			} else {
				$stay = $res->d->GetStay;
			}
		}

		return $stay;
	}


	public function getStayFromService($language='',$exploded = false) {
		$params = $_SESSION['search.params'];

		$resourceId =  isset($params['resourceId'])? $params['resourceId'] : 0;
		if(empty($resourceId)){
			$resourceId = $this->resourceid;
		}
		$ci = !empty($params['checkin'])? $params['checkin'] : new DateTime('UTC');
		if(new DateTime('UTC') >$ci){
			$ci =  new DateTime('UTC');
		}

		$du = !empty($params['duration'])? $params['duration'] : 7;
		$px = isset($params['paxages'])? $params['paxages'] :  array('18','18');
		$ex = isset($params['extras']) ? $params['extras'] : '';
		$pkgs = isset($params['packages']) ? $params['packages'] : '';
		$pt = isset($params['pricetype']) ? $params['pricetype'] : BFCHelper::getVar('pricetype','');
		$rpId = isset($params['rateplanId']) ? $params['rateplanId'] : $pt;
		$vpId = isset($params['variationPlanId']) ? $params['variationPlanId'] : '';
		$hasRateplans = 1;
		$options = array(
				'path' =>  $this->urlCompleteStayRatePlan, //$hasRateplans ? $this->urlStayRatePlan : $this->urlStay, //  BFCHelper::isRatePlanStay() ? $this->urlStayRatePlan : $this->urlStay,
				'data' => array(
					'resourceId' => $resourceId,
					'checkin' => '\'' . $ci->format('Ymd') . '\'',
					'duration' => $du,
					'paxages' => '\'' . implode('|',$px) . '\'',
					'$format' => 'json'
				)
			);
		if (!empty($language)) {
			$options['data']['language'] = '\'' . $language . '\'';
		}
		if($exploded){
			$options['data']['exploded'] = $exploded;
		}
		if (!empty($vpId)) {
			$options['data']['variationPlanId'] = $vpId;
		}

		$options['data']['ratePlanId'] = $rpId;
		if(!empty($ex)){
			$options['data']['selectablePrices'] = '\'' . $ex . '\'';
		}

		$url = $this->helper->getQuery($options);

		$stay = new stdClass;

		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);
			if ($hasRateplans) {
				$foundStay = false;
				$ratePlans =  array();
				if (!empty($res->d)) {
					foreach ($res->d as $ratePlanStay) {
						$rs = $ratePlanStay;
						if (!$foundStay && $ratePlanStay != null && $ratePlanStay->SuggestedStay != null) {
							$rs->CalculatedPricesDetails = json_decode($ratePlanStay->CalculatedPricesString);
							$rs->SelectablePrices = json_decode($ratePlanStay->CalculablePricesString);
							$rs->CalculatedPackages = json_decode($ratePlanStay->PackagesString);
							$rs->DiscountVariation = null;
							if(!empty($ratePlanStay->Discount)){
								$rs->DiscountVariation = $ratePlanStay->Discount;

							}
							$rs->SupplementVariation =null;
							if(!empty($ratePlanStay->Supplement)){
								$rs->SupplementVariation = $ratePlanStay->Supplement;
							}
							$allVar = json_decode($ratePlanStay->AllVariationsString);
							$rs->Variations= [];
							foreach ($allVar as $currVar) {
								//if(empty($currVar->IsExclusive)){
								//if($currVar->IsExclusive){
									$rs->Variations[] = $currVar;
								//}
							}
							$foundStay = true;
						}
						$ratePlans[] = $rs;
					}
				}
				$stay = $ratePlans;
			} else {
				if (!empty($res->d->GetStay)){
				$stay = $res->d->GetStay;
			}
			}
		}


		return $stay;
	}


	public function getStartDateFromService() {
		date_default_timezone_set('UTC');
		return date("d/m/Y");

//		$options = array(
//				'path' => $this->urlStartDate,
//				'data' => array(
//					'$format' => 'json'
//				)
//			);
//
//		$url = $this->helper->getQuery($options);
//
//		$formatDate = 'd/m/Y';
//		$startDate = date($formatDate); // returns 09/15/2007
//
//
//		$r = $this->helper->executeQuery($url);
//		if (isset($r)) {
//			$res = json_decode($r);
////			$dateReturn = $res->d->results ?: $res->d;
//			if (!empty($res->d->results)){
//				$dateReturn = $res->d->results;
//			}elseif(!empty($res->d)){
//				$dateReturn = $res->d;
//			}
//			if (!empty($dateReturn)){
//			$dateparsed = BFCHelper::parseJsonDate($dateReturn->GetStartDate,"");
//			//if ($dateparsed>$startDate) $startDate = $dateparsed;
//			$d1 =DateTime::createFromFormat('d/m/Y',$dateparsed);
//			$d2 =DateTime::createFromFormat('d/m/Y',$startDate);
//			if ($d1>$d2) {
//				$startDate = $dateparsed;
//			}
//			}
//
//		}
//
//		return $startDate;
	}

	public function getEndDate() {
//		return true; //TODO: da fare
		$endDate = BFCHelper::getSession('getEndDate', null , 'com_bookingforconnector');
		if ($endDate==null) {
			$endDate = $this->getEndDateFromService();
			BFCHelper::setSession('getEndDate', $endDate, 'com_bookingforconnector');
		}
		return $endDate;
	}

	public function getEndDateFromService() {
		$options = array(
				'path' => $this->urlEndDate,
				'data' => array(
					'$format' => 'json'
				)
			);

		$url = $this->helper->getQuery($options);

		$formatDate = 'd/m/Y';
		$endDate = date($formatDate); // returns 09/15/2007


		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);
//			$dateReturn = $res->d->results ?: $res->d;
			if (!empty($res->d->results)){
				$dateReturn = $res->d->results;
			}elseif(!empty($res->d)){
				$dateReturn = $res->d;
			}
			if(!empty($dateReturn)){
				$endDate = BFCHelper::parseJsonDate($dateReturn->GetEndDate,$formatDate);
			}
		}

		return $endDate;
	}

	public function getStartDateByMerchantId($merchantId = 0) {

		date_default_timezone_set('UTC');
		return date("d/m/Y");

////		return true; //TODO: da fare
//		$startDateByMerchantId = BFCHelper::getSession('getStartDateByMerchantId_'.$merchantId, null , 'com_bookingforconnector');
//		if ($startDateByMerchantId==null) {
//			$startDateByMerchantId = $this->getStartDateByMerchantIdFromService($merchantId);
//			BFCHelper::setSession('getStartDateByMerchantId_'.$merchantId, $startDateByMerchantId, 'com_bookingforconnector');
//		}
//		return $startDateByMerchantId;
	}

	public function getStartDateByMerchantIdFromService($merchantId = 0) {
		$options = array(
				'path' => $this->urlStartDateByMerchantId,
				'data' => array(
					'$format' => 'json',
					'merchantId' => $merchantId
				)
			);

		$url = $this->helper->getQuery($options);

		$formatDate = 'd/m/Y';
		$startDate = date($formatDate); // returns 09/15/2007


		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);
//			$dateReturn = $res->d->results ?: $res->d;
			if (!empty($res->d->results)){
				$dateReturn = $res->d->results;
			}elseif(!empty($res->d)){
				$dateReturn = $res->d;
			}
			$dateparsed = BFCHelper::parseJsonDate($dateReturn->GetStartDateByMerchantId,"");
			//if ($dateparsed>$startDate) $startDate = $dateparsed;
			$d1 =DateTime::createFromFormat('d/m/Y',$dateparsed,new DateTimeZone('UTC'));
			$d2 =DateTime::createFromFormat('d/m/Y',$startDate,new DateTimeZone('UTC'));
			if ($d1>$d2) {
				$startDate = $dateparsed;
			}

		}

		return $startDate;
	}


	public function getEndDateByMerchantId($merchantId = 0) {
		date_default_timezone_set('UTC');
		return date("d/m/Y", strtotime('+1 years'));

////		return true; //TODO: da fare
//		$endDateByMerchantId = BFCHelper::getSession('getEndDateByMerchantId'.$merchantId, null , 'com_bookingforconnector');
//		if ($endDateByMerchantId==null) {
//			$endDateByMerchantId = $this->getEndDateByMerchantIdFromService($merchantId);
//			BFCHelper::setSession('getEndDateByMerchantId'.$merchantId, $endDateByMerchantId, 'com_bookingforconnector');
//		}
//		return $endDateByMerchantId;
	}

	public function getEndDateByMerchantIdFromService($merchantId = 0) {
		$options = array(
				'path' => $this->urlEndDateByMerchantId,
				'data' => array(
					'$format' => 'json',
					'merchantId' => $merchantId
				)
			);

		$url = $this->helper->getQuery($options);

		$formatDate = 'd/m/Y';
		$endDate = date($formatDate); // returns 09/15/2007


		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);
//			$dateReturn = $res->d->results ?: $res->d;
			if (!empty($res->d->results)){
				$dateReturn = $res->d->results;
			}elseif(!empty($res->d)){
				$dateReturn = $res->d;
			}
				$endDate = BFCHelper::parseJsonDate($dateReturn->GetEndDateByMerchantId,$formatDate);
			}

		return $endDate;
	}

	public function getCheckInDatesFromService($resourceId = null,$ci= null) {
//		if ($resourceId==null) {
//			$params = $this->getState('params');
//			$resourceId = $params['resourceId'];
//		}
		if ($ci==null) {
			$ci =  new DateTime('UTC');
		}
		//$ci = $params['checkin'];
		$options = array(
				'path' => $this->urlCheckInDates,
				'data' => array(
					'resourceId' => $resourceId,
					'checkin' => '\'' . $ci->format('Ymd') . '\'',
					'$format' => 'json'
				)
			);

		$url = $this->helper->getQuery($options);

		$listDate = null;


		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);
			//$listDate = $res->d->results ?: $res->d;
			if (!empty($res->d->results)){
				$listDate = $res->d->results;
			}elseif(!empty($res->d)){
				$listDate = $res->d;
			}
			/*
			if (!empty($listDate)){
				$listDate = implode(',',$listDate);
			}
			*/
		}

		return $listDate;
	}

	public function getCheckOutDatesFromService($resourceId = null,$ci= null) {
		//$params = $this->getState('params');
		if ($resourceId==null) {
			$params = $_SESSION['search.params'];
			$resourceId = $params['resourceId'];
		}
		if ($ci==null) {
			$ci =  new DateTime('UTC');
		}
		//$ci = $params['checkin'];
		$options = array(
				'path' => $this->urlCheckOutDates,
				'data' => array(
					'resourceId' => $resourceId,
					'checkin' => '\'' . $ci->format('Ymd') . '\'',
					'$format' => 'json'
				)
			);

		$url = $this->helper->getQuery($options);

		$listDate = '';


		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);
//			$listDate = $res->d->results ?: $res->d;
			if (!empty($res->d->results)){
				$listDate = $res->d->results;
			}elseif(!empty($res->d)){
				$listDate = $res->d;
			}
			/*
			if(!empty($listDate)){
				$listDate = implode(',',$listDate);
			}
			*/
		}

		return $listDate;
	}

	public function getCheckOutDatesPerTimesFromService($resourceId = null,$ci= null) {
		//$params = $this->getState('params');
		if ($resourceId==null) {
			$params = $_SESSION['search.params'];
			$resourceId = $params['resourceId'];
		}
		if ($ci==null) {
			$ci =  new DateTime('UTC');
		}
		//$ci = $params['checkin'];
		$options = array(
				'path' => $this->urlCheckOutDatesPerTimes,
				'data' => array(
					'resourceId' => $resourceId,
					'checkin' => '\'' . $ci->format('YmdHis') . '\'',
					'$format' => 'json'
				)
			);

		$url = $this->helper->getQuery($options);

		$listDate = null;


		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);
//			$listDate = $res->d->results ?: $res->d;
			if (!empty($res->d->results)){
				$listDate = $res->d->results;
			}elseif(!empty($res->d)){
				$listDate = $res->d;
			}
//			if(!empty($listDate)){
//				$listDate = implode(',',$listDate);
//			}
		}

		return $listDate;
	}

	public function getCheckAvailabilityCalendarFromService($resourceId = null,$checkIn= null,$checkOut= null) {
		if ($resourceId==null || $checkIn ==null  || $checkOut ==null ) {
			$params = $this->getState('params');
		}
		if ($resourceId==null) {
			$resourceId = $params['resourceId'];
		}
		if ($checkIn==null) {
			//$defaultDate = DateTime::createFromFormat('d/m/Y',BFCHelper::getStartDate());
			$checkIn =  BFCHelper::getStayParam('checkin', DateTime::createFromFormat('d/m/Y',BFCHelper::getStartDate(),new DateTimeZone('UTC')));
		}
		if ($checkOut==null) {
			$checkOut =   BFCHelper::getStayParam('checkout', $checkIn->modify(BFCHelper::$defaultDaysSpan));
		}
		//calcolo le settimane necessarie

		//$ci = $params['checkin'];
		$options = array(
				'path' => $this->urlCheckAvailabilityCalendar,
				'data' => array(
					'resourceId' => $resourceId,
					'checkin' => '\'' . $checkIn->format('Ymd') . '\'',
					'checkout' => '\'' . $checkOut->format('Ymd') . '\'',
					'$format' => 'json'
				)
			);

		$url = $this->helper->getQuery($options);

		$resultCheck = false;


		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);
//			$checkDate = $res->d->results ?: $res->d;
			if (!empty($res->d->results)){
				$checkDate = $res->d->results;
			}elseif(!empty($res->d)){
				$checkDate = $res->d;
			}
			$resultCheck = $checkDate->CheckAvailabilityCalendar;
		}

		return $resultCheck;
	}


	public function getUnitCategoriesFromService() {

		$options = array(
				'path' => $this->urlUnitCategories,
				'data' => array(
						'$select' => 'ProductCategoryId,Name,ParentCategoryId',
						'$filter' => 'Enabled eq true',
						'$orderby' => 'Order asc',
						'$format' => 'json'
				)
		);
		$url = $this->helper->getQuery($options);

		$categoriesFromService = null;

		$r = $this->helper->executeQuery($url);
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

	public function getUnitCategories() {
//		$session = JFactory::getSession();
		$categories = BFCHelper::getSession('getUnitCategories', null , 'com_bookingforconnector');
//		if (!$session->has('getMerchantCategories','com_bookingforconnector')) {
		if ($categories==null) {
			$categories = $this->getUnitCategoriesFromService();
			BFCHelper::setSession('getUnitCategories', $categories, 'com_bookingforconnector');
		}
		return $categories;
	}

	protected function populateState($ordering = NULL, $direction = NULL) {
		//$ci = clone BFCHelper::getStayParam('checkin', new DateTime('UTC'));

		//recupero la prima data disponibile per la risorsa se riesco altrimenti recupero la prima data disponibile
		$resourceId = BFCHelper::getInt('resourceId');
		if(!empty($resourceId)){
			$dates = $this->getCheckInDatesFromService($resourceId,null);
			if (($pos = strpos($dates, ','))!==false)
				$dates = explode(",",$dates);

			if (is_array($dates)){
				$tmpDate1 = array_values($dates);
				$tmpDate = array_shift($tmpDate1);
				$defaultDate = DateTime::createFromFormat('Ymd',$tmpDate,new DateTimeZone('UTC'));
//				$defaultDate = DateTime::createFromFormat('Ymd',array_shift(array_values($dates)));
			}elseif($dates != ''){
				$defaultDate = DateTime::createFromFormat('Ymd',$dates,new DateTimeZone('UTC'));
			}
		}
		if (!isset($defaultDate)){
			$defaultDate = DateTime::createFromFormat('d/m/Y',BFCHelper::getStartDate(),new DateTimeZone('UTC'));
		}


		if(new DateTime('UTC') >$defaultDate){
			$defaultDate =  new DateTime('UTC');
		}
		$ci = clone BFCHelper::getStayParam('checkin', $defaultDate);
		$defaultRequest =  array(
			'resourceId' => BFCHelper::getInt('resourceId'),
			'checkin' => BFCHelper::getStayParam('checkin', $defaultDate),
			'checkout' => BFCHelper::getStayParam('checkout', $ci->modify(BFCHelper::$defaultDaysSpan)),
			'duration' => BFCHelper::getStayParam('duration'),
			'paxages' => BFCHelper::getStayParam('paxages'),
			'extras' => BFCHelper::getStayParam('extras'),
			'packages' => BFCHelper::getStayParam('packages'),
			'pricetype' => BFCHelper::getStayParam('pricetype'),
			'rateplanId' => BFCHelper::getStayParam('rateplanId'),
			'variationPlanId' => BFCHelper::getStayParam('variationPlanId'),
			'state' => BFCHelper::getStayParam('state'),
			'gotCalculator' => BFCHelper::getBool('calculate'),
			'paxes' => count(BFCHelper::getStayParam('paxages'))
		);

//		echo "<pre>defaultRequest";
//		echo print_r($defaultRequest);
//		echo "</pre>";

		$stayrequest = BFCHelper::getVar('stayrequest');

		// support for rsforms!
		if ($stayrequest == null || $stayrequest == '') {
			$form = BFCHelper::getVar('form');
			$stayrequest = htmlspecialchars_decode($form['stayrequest'], ENT_COMPAT);
		}

		if ($stayrequest != null && $stayrequest != '') {
			try {
				$params = json_decode($stayrequest);
				$stayCheckin = DateTime::createFromFormat('d/m/Y',$params->checkin,new DateTimeZone('UTC'));
				if(new DateTime('UTC') <$stayCheckin){
				$defaultRequest = array(
					'resourceId' => $params->resourceId,
					'checkin' => DateTime::createFromFormat('d/m/Y',$params->checkin,new DateTimeZone('UTC')),
					'checkout' => DateTime::createFromFormat('d/m/Y',$params->checkout,new DateTimeZone('UTC')),
					'duration' => $params->duration,
					'paxages' => $params->paxages,
					'extras' => $params->extras,
					'packages' => $params->packages,
					'pricetype' => $params->pricetype,
					'rateplanId' => $params->rateplanId,
					'variationPlanId' => $params->variationPlanId,
					'state' => $params->state,
					'gotCalculator' => false,
					'fromExtForm' => true,
						'hasRateplans' => false,
						'paxes' => count($params->paxages)
					);
				}

			} catch (Exception $e) {

			}
		}

		//echo var_dump($defaultRequest);die();
		$this->setState('params', $defaultRequest);

//		return parent::populateState();
	}

	public function getItem($resource_id) {
		$item = $this->getResourceFromService($resource_id);
		return $item;
	}

	public function getStay($language='',$forceGet = false,$exploded = false){
//		$rsidg = $rsid ;
	  $stay = $this->getStayFromService($language,$exploded);
	  return $stay;
	}

/*----------rating--------------*/
	public function getItems($type = '') {

		switch($type) {
			case 'ratings':
				$items = $this->getRatingsFromService(
					$this->getStart($type),
					$this->getState('list.limit')
				);
				break;
			default:
				break;
		}

		return $items;
	}



	public function getItemsRating()
	{
		return $this->getItems('ratings');
	}

	public function getRating()
	{
		return $this->getItems('ratings');
	}


	public function getTotal($type = '')
	{
		switch($type) {
			case 'ratings':
				return $this->getTotalRatings();
				break;
			case '':
			default:
				return 0;
		}
	}

	public function getTotalRatings()
	{
		$resourceId = BFCHelper::getInt('resourceId');
		$options = array(
				'path' => $this->urlRatingCount,
				'data' => array(
					'ResourceId' => $resourceId 
			)
		);

		$url = $this->helper->getQuery($options);

		$count = null;

//		$r = $this->helper->executeQuery($url,null,null,false);
		$r = $this->helper->executeQuery($url,null,null,false,"","",bfi_TagsScope::Resource,$resourceId );
		if (isset($r)) {
			$count = (int)$r;
			$res = json_decode($r);
			$count = (int)$res->d->GetReviewsCount;
		}

		return $count;
	}

	public function getRatingAverageFromService($merchantId = null,$resourceId = null) {
//		$params = $this->getState('params');
//		$params = $_SESSION['search.params'];
//
//		if ($merchantId==null) {
//			$merchantId = $params['merchantId'];
//			$resourceId = $params['resourceId'];
//		}

		$options = array(
				'path' => $this->urlRatingAverage,
				'data' => array(
					'merchantId' => $merchantId,
					'resourceId' => $resourceId,
					'$format' => 'json'
				)
		);

		$url = $this->helper->getQuery($options);

		$ratings = null;

//		$r = $this->helper->executeQuery($url,null,null,false);
		$r = $this->helper->executeQuery($url,null,null,false,"","",bfi_TagsScope::Resource,$resourceId );
		if (isset($r)) {
			$res = json_decode($r);
			//$resRatings = $res->d->results ?: $res->d;
			$resRatings = null;
			if (!empty($res->d->results)){
				$resRatings = $res->d->results;
			}elseif(!empty($res->d)){
				$resRatings = $res->d;
			}
			if (!empty($resRatings)){
				$ratings = $resRatings->GetResourceAverage;
			}
		}

		return $ratings;
	}

	public function getRatingsFromService($start, $limit, $resourceId = null) {

		if ($resourceId==null) {
			$resourceId = $_SESSION['search.params']['resourceId'];
		}
		if (empty($language)){
			$language = $GLOBALS['bfi_lang'];
		}

		$options = array(
				'path' => $this->urlRating,
				'data' => array(
//					'$filter' => 'ResourceId eq ' . $resourceId . ' and Enabled eq true',
//					'$orderby' => 'CreationDate desc',
//					'$format' => 'json'
					'culturecode' => BFCHelper::getQuotedString($language),
					'$format' => 'json'
				)
		);

		if (isset($start) && $start > 0) {
			$options['data']['skip'] = $start;
		}

		if (isset($limit) && $limit > 0) {
			$options['data']['top'] = $limit;
		}
		if (isset($resourceId) && $resourceId > 0) {
			$options['data']['ResourceId'] = $resourceId;
		}

		$filters = null;
		if (isset($_SESSION['params.rating']['filters'])) {
		$filters = $_SESSION['params.rating']['filters'];
		}

		if ($filters != null && $filters['typologyid'] != null && $filters['typologyid']!= "0") {
			$options['data']['$filter'] .= ' and TypologyId eq ' .$filters['typologyid'];
		}

		$url = $this->helper->getQuery($options);

		$ratings = null;

		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);
			if (!empty($res->d->results)){
				$ratings = $res->d->results;
			}elseif(!empty($res->d)){
				$ratings = $res->d;
			}
		}
		return $ratings;
	}

	private function filterRatingResults($results) {
		$params = $_SESSION['search.params'];
//		$params = $this->getState('params');
		$filters = $params['filters'];
		if ($filters == null) return $results;

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

	function getPaginationRatings()
	{
		return $this->getPagination('ratings');
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

	public function getPolicy($resourcesId, $language='') {
		$options = array(
				'path' => $this->urlGetPolicy,
				'data' => array(
					'resourceId' => $resourcesId,
					'cultureCode' => BFCHelper::getQuotedString($language),
					'$format' => 'json'
				)
		);

		$url = $this->helper->getQuery($options);

		$return = null;

		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);
			if (!empty($res->d->results)){
				$return = $res->d->results->GetPolicy;
			}elseif(!empty($res->d)){

				$return = $res->d->GetPolicy;
			}
		}
		return $return;
	}


	public function getSearchPackages($merchantId,$relatedProductid,$excludedIds,$checkin,$duration,$paxages,$variationPlanId,$language="",$resourcegroupId,$availabilityTypes,$itemTypeIds,$minqt,$maxqt ) {

		$options = array(
				'path' =>  $this->urlSearch,
				'data' => array(
					'getAllResults' => 1,
					'checkAvailability' => 1,
					'lite' => 1,
					'calculate' => 1,
					'top' => 10,
					'skip' => 0,
					'getFilters' => 0,
//					'relatedProductid' => $relatedProductid,
//					'excludedIds' => '\'' . $excludedIds . '\'',
//					'pricetype' => 'rateplan',
//					'checkin' => '\'' . $checkin->format('YmdHis') . '\'',
					'checkin' => '\'' . $checkin->format('Ymd') . '\'',
					'duration' => $duration,
//					'paxages' => '\'' . implode('|',$paxages) . '\'',
					'paxages' => '\'' . $paxages . '\'',
					'paxes' => count(explode("|",$paxages)),
//					'getRelatedProducts' => 0,
					'getBestGroupResult' => 0,
					'simpleResult' => 0,
					'groupResultType' => 0,
					'getSingleResultOnly' => 1,
					'getUpSellProducts' => 1,
					'$format' => 'json'
				)
			);

		$options['data']['checkAvailability'] = 1;

//		if (isset($searchid) && $searchid !='') {
			$options['data']['searchid'] = '\'' . uniqid('', true). '\'';
//		}
		if (!empty($merchantId)) {
			$options['data']['merchantId'] = $merchantId;
		}
		if (!empty($language)) {
			$options['data']['cultureCode'] = '\'' . $language . '\'';
		}
		if (!empty($variationPlanId)) {
			$options['data']['variationPlanIds'] = '\'' .$variationPlanId . '\'';
		}
		if (!empty($resourcegroupId)) {
			$options['data']['resourceGroupId'] = $resourcegroupId;
			$options['data']['condominiumId'] = $resourcegroupId;
		}
		if (!empty($availabilityTypes)) {
			$options['data']['availabilityTypes'] =  '\'' .$availabilityTypes . '\'';
		}
		if ((isset($itemTypeIds) && $itemTypeIds!='')) {
			$options['data']['itemTypeIds'] =  '\'' .$itemTypeIds . '\'';
		}
		if (!empty($minqt)) {
			$options['data']['minqt'] = $minqt ;
		}
		if (!empty($maxqt)) {
			$options['data']['maxqt'] = $maxqt ;
		}
		
		$currUser = BFCHelper::getSession('bfiUser',null, 'bfi-User');
		if($currUser!=null && !empty($currUser->Email)) {
			$options['data']['userid'] = '\'' . $currUser->Email . '\'';
		}

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
}
endif;