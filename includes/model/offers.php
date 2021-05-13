<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BookingForConnectorModelOffers Model
 */
 if ( ! class_exists( 'BookingForConnectorModelOffers' ) ) :

class BookingForConnectorModelOffers
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
	
	
	
	/*private $urlMerchants = null;
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
	private $urlAllMerchants = null;*/

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

//		$this->urlMerchantRating = '/RatingsView';
//		$this->urlMerchantRatingCount = '/RatingsView/$count';
		$this->urlMerchantRating = '/GetReviews';
		$this->urlMerchantRatingCount = '/GetReviewsCount';

		$this->urlMerchantOffers = '/GetVariationPlans';
		$this->urlMerchantOffersCount = '/GetVariationPlansCount';
		$this->urlMerchantOffer = '/GetVariationPlanById';
		
//		$this->urlMerchantPackages = '/GetPackages';
//		$this->urlMerchantPackagesCount = '/GetPackagesCount';
//		$this->urlMerchantPackage = '/GetPackageById';
		$this->urlgetTagsByElementId = '/GetTagsByElementId';

}







    public function getAllOffers($start, $limit, $language = null) {

		if ($language==null) {
			$language = $GLOBALS['bfi_lang'];

		}
		$options = array(
				'path' => $this->urlMerchantOffers,
				'data' => array(
					/*'merchantId' => $this->merchantid,*/
					'cultureCode' => BFCHelper::getQuotedString($language),
					/*'$filter' => 'MerchantId eq ' . $merchantId . ' and Enabled eq true',
					'$expand' => 'Photos',
					'$skip' => $start,
					'$top' => $limit,*/
					'$format' => 'json'
				)
		);
		/*
		if (isset($start) && $start > 0) {
			$options['data']['$skip'] = $start;
		}
		
		if (isset($limit) && $limit > 0) {
			$options['data']['$top'] = $limit;
		}		
		*/
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
	


    public function getItemsOffer()
	{
		return $this->getItems('offers');
	}

	public function getOffer()
	{
		return $this->getItems('offer');
	}

	/* public function getItems($type = '', $ext_data = 0, $merchant_id = '', $parent_id = '')
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
	} */	
	
	
	


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


}
endif;