<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * BookingForConnectorModelOrders Model
 */
if ( ! class_exists( 'BookingForConnectorModelPortal' ) ) {

class BookingForConnectorModelPortal
{
		
	private $urlGetPrivacy = null;
	private $urlGetAdditionalPurpose = null;
	private $urlGetProductCategoryForSearch = null;
	private $urlGetCartmultimerchantenabled = null;
	private $urlGetDefaultCurrency = null;
	private $urlGetCurrencyExchanges = null;

	private $urlGetLoginTwoFactor = null;
	private $urlLogoutUser = null;
	private $urlCheckDeviceToken = null;
	private $urlGetTwoFactorUniqueNumber = null;
	private $urlGetContactFavoriteGroups = null;
	private $urlAddToFavorites = null;
	private $urlAddFavoriteGroup = null;
	private $urlUpdateUserFavorites = null;
	private $urlRemoveFromFavorites = null;
	private $urlGetPointsOfInterest = null;
	private $helper = null;

	private $urlGetSubscriptionInfos = null;


	public function __construct($config = array())
	{
      $ws_url = COM_BOOKINGFORCONNECTOR_WSURL;
		$api_key = COM_BOOKINGFORCONNECTOR_API_KEY;
		$this->helper = new wsQueryHelper($ws_url, $api_key);
		$this->urlGetPrivacy = '/GetPrivacy';
		$this->urlGetAdditionalPurpose = '/GetAdditionalPurpose';
		$this->urlGetProductCategoryForSearch = '/GetProductCategoryForSearch';
		$this->urlGetCartmultimerchantenabled = '/HasMultiMerchantCart';
		$this->urlGetDefaultCurrency = '/GetDefaultCurrency';
		$this->urlGetCurrencyExchanges = '/GetCurrencyExchanges';
		$this->urlGetLoginTwoFactor = '/LoginTwoFactor';
		$this->urlLogoutUser = '/LogoutUser';
		$this->urlCheckDeviceToken = '/CheckDeviceToken';
		$this->urlGetTwoFactorUniqueNumber = '/GetTwoFactorUniqueNumber';
		$this->urlGetContactFavoriteGroups = '/GetContactFavoriteGroups';
		$this->urlAddToFavorites = '/AddToFavorites';
		$this->urlUpdateUserFavorites = '/UpdateUserFavorites';
		$this->urlRemoveFromFavorites = '/RemoveFromFavorites';
		$this->urlGetPointsOfInterest = '/SearchPointsOfInterest';
		$this->urlAddFavoriteGroup = '/AddFavoriteGroup';
		$this->urlGetSubscriptionInfos = '/GetSubscriptionInfos';
	}

	public function getSubscriptionInfos($language='') {		
		$options = array(
				'path' => $this->urlGetSubscriptionInfos,
				'data' => array(
//					'cultureCode' => BFCHelper::getQuotedString($language),
					'$format' => 'json'
				)
		);
		
		$url = $this->helper->getQuery($options);
		
		$return = null;
		
		$r = $this->helper->executeQuery($url,null,null,false);
		if (isset($r)) {
			$res = json_decode($r);
			if (!empty($res->d->results)){
				$return = $res->d->results->GetSubscriptionInfos;
			}elseif(!empty($res->d)){
				$return = $res->d->GetSubscriptionInfos;
			}
			if (!empty($return) && isset($return->SettingsString)) {
				$return->Settings = json_decode($return->SettingsString);
			}
		}

		return $return;
	}
	
	
	public function checkDeviceToken($email, $deviceCodeAuthCode = '', $deviceAuthToken = '') {
		$data = array(
			'email' => BFCHelper::getQuotedString($email),
			'deviceAuthCode' => BFCHelper::getQuotedString($deviceCodeAuthCode),
			'deviceToken' => BFCHelper::getQuotedString($deviceAuthToken),
			'$format' => 'json'
		);
		$options = array(
			'path' => $this->urlCheckDeviceToken,
			'data' => $data
		);
		$url = $this->helper->getQuery($options);
	
		$results = false;
	
		$r = $this->helper->executeQuery($url, "GET");
		if (isset($r)) {
			$res = json_decode($r);
			if (isset($res->d->CheckDeviceToken)) {
				$results = strval($res->d->CheckDeviceToken);
			} elseif(isset($res->d)) {
				$results = strval($res->d);
			}
		}
		return $results;
	}
	
	public function logoutUser($email, $deviceCodeAuthCode = '', $deviceAuthToken = '') {
		$data = array(
			'email' => BFCHelper::getQuotedString($email),
			'deviceCodeAuthCode' => BFCHelper::getQuotedString($deviceCodeAuthCode),
			'deviceAuthToken' => BFCHelper::getQuotedString($deviceAuthToken),
			'$format' => 'json'
		);
		$options = array(
			'path' => $this->urlLogoutUser,
			'data' => $data
		);
		$url = $this->helper->getQuery($options);
	
		$results = false;
	
		$r = $this->helper->executeQuery($url, "POST");
		if (isset($r)) {
			$res = json_decode($r);
			if (isset($res->d->LogoutUser)) {
				$results = strval($res->d->LogoutUser);
			} elseif(isset($res->d)) {
				$results = strval($res->d);
			}
		}
		return $results;
	}

	public function getLoginTwoFactor($email, $password, $twoFactorAuthCode = '', $twoFactorDeviceCode = '', $deviceCode = '', $deviceAuthToken = '') {
		$cultureCode = $GLOBALS['bfi_lang'];
		$data = array(
				'email' => BFCHelper::getQuotedString($email),
				'password' => BFCHelper::getQuotedString($password),
				'twoFactorAuthCode' => BFCHelper::getQuotedString($twoFactorAuthCode),
				'deviceAuthToken' => BFCHelper::getQuotedString($deviceAuthToken),
				'deviceCode' => BFCHelper::getQuotedString($deviceCode),
				'$format' => 'json'
		);

		$options = array(
				'path' => $this->urlGetLoginTwoFactor,
				'data' => $data
		);

		if (!empty($twoFactorDeviceCode)) {
			$options['data']['twoFactorDeviceCode'] = BFCHelper::getQuotedString($twoFactorDeviceCode);
		    
		}

		$url = $this->helper->getQuery($options);
	
		$results= "0";
	
		$r = $this->helper->executeQuery($url,"POST");
		if (isset($r)) {
			$res = json_decode($r);
			if (isset($res->d->LoginTwoFactor)){
				$results = strval($res->d->LoginTwoFactor);
			}elseif(isset($res->d)){
				$results = strval($res->d);
			}
		}
		$strUser ="";
		
		if (strrpos($results, "-1:{")  === false ) {
			if (strrpos($results, "4:{") === false  ) {
			}else{

				$strUser = stripslashes(substr($results, 2));
				$currUser = json_decode($strUser);
				BFCHelper::SetTwoFactorCookie($currUser->DeviceToken);
				$results = "-1";
				///$strUser = "";
//				if(!empty( $deviceCodeAuthCode )){
//					$uniqueNumber = self::GetTwoFactorUniqueNumber($email);
//					if(!empty( $uniqueNumber )){
//						BFCHelper::SetTwoFactorCookie($uniqueNumber);
//						$strUser = stripslashes(substr($results, 2));
//						$results = "-1";
//					}
//				}
			}
			if (strrpos($results, "6:{") === false  ) {
			}else{
				$strUser = stripslashes(substr($results, 2));
				$results = "-1";
			}
		}else{
			$strUser = stripslashes(substr($results, 3));
			$results = "-1";
		}
		if(!empty( $strUser )){
			$currUser = json_decode($strUser);
			$currUserId = BFCHelper::bfi_get_userId();
			BFCHelper::setSession('bfiUser', $currUser, 'bfi-User');
			BFCHelper::UpdateCartExternalUser($currUserId);
			self::UpdateUserFavorites($currUserId);
		}
	
		
//		if ($results == 4) {
//		    $uniqueNumber = self::GetTwoFactorUniqueNumber($email);
//			if(!empty( $uniqueNumber )){
//				BFCHelper::SetTwoFactorCookie($uniqueNumber);
//				$results = -1;
//			}
//
//		}	
		return $results;
	}

	public function GetTwoFactorUniqueNumber($email="") {
		$data = array(
				'email' => BFCHelper::getQuotedString($email),
				'$format' => 'json'
		);
		$options = array(
				'path' => $this->urlGetTwoFactorUniqueNumber,
				'data' => $data
		);
		$url = $this->helper->getQuery($options);
	
		$results= "0";
	
		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);
			if (!empty($res->d->GetTwoFactorUniqueNumber)){
				$results = $res->d->GetTwoFactorUniqueNumber;
			}elseif(!empty($res->d)){
				$results = $res->d;
			}
		}
		return $results;
	}

	public function UpdateUserFavorites($oldUserId) {
		$userId = BFCHelper::bfi_get_userId();
		$data = array(
			'UserId' => BFCHelper::getQuotedString($oldUserId),
			'domainLabel' => BFCHelper::getQuotedString(COM_BOOKINGFORCONNECTOR_FORM_KEY),
			'newUserId' => BFCHelper::getQuotedString($userId),
			'$format' => 'json'
		);
		$options = array(
				'path' => $this->urlUpdateUserFavorites,
				'data' => $data
		);
		$results= null;
		$url = $this->helper->getQuery($options);
		$r = $this->helper->executeQuery($url,'POST');
		if (isset($r)) {
			$res = json_decode($r);
			if (!empty($res->d->UpdateUserFavorites)){
				$results = $res->d->UpdateUserFavorites;
			}elseif(!empty($res->d)){
				$results = $res->d;
			}
		}
		return $results;
	}

	public function GetContactFavoriteGroups() {

		if(!empty( COM_BOOKINGFORCONNECTOR_CRAWLER )){
			$listCrawler = json_decode(COM_BOOKINGFORCONNECTOR_CRAWLER , true);
			foreach( $listCrawler as $key=>$crawler){
			if (preg_match('/'.$crawler['pattern'].'/', $_SERVER['HTTP_USER_AGENT'])) return null;
			}
		}


		$userId = BFCHelper::bfi_get_userId();
//		$cultureCode = JFactory::getLanguage()->getTag();
		$cultureCode = $GLOBALS['bfi_lang'];

		$data = array(
			'UserId' => BFCHelper::getQuotedString($userId),
			'domainLabel' => BFCHelper::getQuotedString(COM_BOOKINGFORCONNECTOR_FORM_KEY),
			'cultureCode' => BFCHelper::getQuotedString($cultureCode),
			'getFullDetails' => 1,
			'$format' => 'json'
		);

		$options = array(
				'path' => $this->urlGetContactFavoriteGroups,
				'data' => $data
		);
		$url = $this->helper->getQuery($options);
	
		$results= null;
	
		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);
			if (!empty($res->d->GetContactFavoriteGroups)){
				$results = $res->d->GetContactFavoriteGroups;
			}elseif(!empty($res->d)){
				$results = $res->d;
			}
		}
		return $results;
	}

	public function AddToFavorites($itemId, $itemType, $itemName, $itemUrl, $groupId = 0, $startDate = "", $endDate = "") {

		$userId = BFCHelper::bfi_get_userId();
//		$cultureCode = JFactory::getLanguage()->getTag();
		$cultureCode = $GLOBALS['bfi_lang'];
		$data = array(
			'UserId' => BFCHelper::getQuotedString($userId),
			'domainLabel' => BFCHelper::getQuotedString(COM_BOOKINGFORCONNECTOR_FORM_KEY),
			'cultureCode' => BFCHelper::getQuotedString($cultureCode),
			'itemId' => $itemId,
			'itemType' => $itemType,
			'itemName' => BFCHelper::getQuotedString($itemName),
			'itemUrl' => BFCHelper::getQuotedString($itemUrl),
			'$format' => 'json'
		);
		$options = array(
				'path' => $this->urlAddToFavorites,
				'data' => $data
		);

		if(!empty($groupId)){
			$options['data']['groupId'] = $groupId;
		}
		if(!empty($startDate)){
			$options['data']['startDate'] = BFCHelper::getQuotedString($startDate);
		}
		if(!empty($endDate)){
			$options['data']['endDate'] = BFCHelper::getQuotedString($endDate);
		}

		$url = $this->helper->getQuery($options);

		$return = 0;

		$r = $this->helper->executeQuery($url,'POST');
		if (isset($r)) {
			$res = json_decode($r);
			if (!empty($res->d->AddToFavorites)){
				$return = $res->d->AddToFavorites;
			}elseif(!empty($res->d)){
				$return = $res->d;
			}
		}

		return $return;
	}

	public function RemoveItemToFavorites($favoriteId, $groupId = 0) {

		$userId = BFCHelper::bfi_get_userId();
//		$cultureCode = JFactory::getLanguage()->getTag();
		$cultureCode = $GLOBALS['bfi_lang'];
		$data = array(
			'UserId' => BFCHelper::getQuotedString($userId),
			'domainLabel' => BFCHelper::getQuotedString(COM_BOOKINGFORCONNECTOR_FORM_KEY),
			'favoriteId' => $favoriteId,
			'$format' => 'json'
		);
		$options = array(
				'path' => $this->urlRemoveFromFavorites,
				'data' => $data
		);

		if(!empty($groupId)){
			$options['data']['groupId'] = $groupId;
		}

		$url = $this->helper->getQuery($options);

		$return = 0;

		$r = $this->helper->executeQuery($url,'POST');
		if (isset($r)) {
			$res = json_decode($r);
			if (!empty($res->d->RemoveFromFavorites)){
				$return = $res->d->RemoveFromFavorites;
			}elseif(!empty($res->d)){
				$return = $res->d;
			}
		}
		return $return;
	}
	public function AddFavoriteGroup($groupName) {

		$userId = BFCHelper::bfi_get_userId();
//		$cultureCode = JFactory::getLanguage()->getTag();
		$cultureCode = $GLOBALS['bfi_lang'];
		$data = array(
			'UserId' => BFCHelper::getQuotedString($userId),
			'domainLabel' => BFCHelper::getQuotedString(COM_BOOKINGFORCONNECTOR_FORM_KEY),
			'name' => BFCHelper::getQuotedString($groupName),
			'cultureCode' => BFCHelper::getQuotedString($cultureCode),
			'$format' => 'json'
		);
		$options = array(
				'path' => $this->urlAddFavoriteGroup,
				'data' => $data
		);
		$url = $this->helper->getQuery($options);

		$return = 0;

		$r = $this->helper->executeQuery($url,'POST');
		if (isset($r)) {
			$res = json_decode($r);
			if (!empty($res->d->AddFavoriteGroup)){
				$return = $res->d->AddFavoriteGroup;
			}elseif(!empty($res->d)){
				$return = $res->d;
			}
		}
		return $return;
	}

	public function GetPointsOfInterest() {
//		$cultureCode = JFactory::getLanguage()->getTag();
		$cultureCode = $GLOBALS['bfi_lang'];

		$data = array(
			'cultureCode' => BFCHelper::getQuotedString($cultureCode),
			'getFilters' => 0, // per non recuperare i filtri
			'simpleResult' => 0,  // per visualizzazione ridotta
			'$format' => 'json'
		);

		$options = array(
				'path' => $this->urlGetPointsOfInterest,
				'data' => $data
		);
		$url = $this->helper->getQuery($options);
	
		$results= null;
	
		$r = $this->helper->executeQuery($url,null,null,false);
		if (isset($r)) {
			$res = json_decode($r);
			if (!empty($res->d->SearchPointsOfInterest)){
				$results = $res->d->SearchPointsOfInterest;
			}elseif(!empty($res->d)){
				$results = $res->d;
			}
		}

		$resultsItems = null;
		if(isset($results->TotalItemsCount)){
			$resultsItems = json_decode($results->ItemsString);
		}

		return $resultsItems;
	}

	public function getCurrencyExchanges() {
//		$results = BFCHelper::getSession('getCurrencyExchanges', null , 'com_bookingforconnector');
//		$results=null;
//		if ($results==null) {
			$results = $this->getCurrencyExchangesFromService();
//			BFCHelper::setSession('getCurrencyExchanges', $results, 'com_bookingforconnector');
//		}
		return $results;
	}
	
	public function getCurrencyExchangesFromService() {		
		$options = array(
				'path' => $this->urlGetCurrencyExchanges,
				'data' => array(
					'$format' => 'json',
					'getDefaultOnly' => 'false'
			
				)
		);
		
		$url = $this->helper->getQuery($options);
		
		$return = null;
		
		$r = $this->helper->executeQuery($url,null,null,false);
		if (isset($r)) {
			$res = json_decode($r);
			if (!empty($res->d->results)){
				$return = $res->d->results;
			}elseif(!empty($res->d)){

				$return = $res->d;
			}
		}
		if(!empty($return)){
			$aCurrencyExchanges = array();
			foreach ($return as $key => $CurrencyExchange ) {
				$aCurrencyExchanges[$CurrencyExchange->ToCurrencyCode] = $CurrencyExchange->ConversionValue;
			}
			return $aCurrencyExchanges;
		}
		return $return;
	}

//	public function getDefaultCurrency() {
//		$results = BFCHelper::getSession('getDefaultCurrency', null , 'com_bookingforconnector');
//		if ($results==null) {
//			$results = $this->getDefaultCurrencyFromService();
//			BFCHelper::setSession('getDefaultCurrency', $results, 'com_bookingforconnector');
//		}
//		return $results;
//	}
	public function getDefaultCurrency() {


		$results = $this->getDefaultCurrencyFromService();


		return $results;
	}
	
	public function getDefaultCurrencyFromService() {		
		$options = array(
				'path' => $this->urlGetDefaultCurrency,
				'data' => array(
					'$format' => 'json'
				)
		);
		
		$url = $this->helper->getQuery($options);
		
		$return = null;
		
		$r = $this->helper->executeQuery($url,null,null,false);
		if (isset($r)) {
			$res = json_decode($r);
			if (!empty($res->d->results)){
				$return = $res->d->results->GetDefaultCurrency;
			}elseif(!empty($res->d)){

				$return = $res->d->GetDefaultCurrency;
			}
		}
		return $return;
	}

	public function getProductCategoryForSearchFromService($language='', $typeId = 1,$merchantid=0) {
		$options = array(
				'path' => $this->urlGetProductCategoryForSearch,
				'data' => array(
					'typeId' => $typeId,
					'cultureCode' => BFCHelper::getQuotedString($language),
					'$format' => 'json'
				)
			);

//		if(!empty( $merchantid )){
//			$options['data']['merchantId'] = $merchantid;
//			
//		}
		
		$url = $this->helper->getQuery($options);
		
		$return = null;
		
		$r = $this->helper->executeQuery($url,null,null,false);
		if (isset($r)) {
			$res = json_decode($r);
			if (!empty($res->d->results)){
				$return = $res->d->results;
			}elseif(!empty($res->d)){

				$return = $res->d;
			}
		}
		return $return;
	}

//	public function getProductCategoryForSearch($language='', $typeId = 1,$merchantid=0) {
//		$session = JFactory::getSession();
//		$results = BFCHelper::getSession('getProductCategoryForSearch'.$language.$typeId.$merchantid, null , 'com_bookingforconnector');
//		if (!$session->has('getMerchantCategories','com_bookingforconnector')) {
//		if ($results==null) {
//			$results = $this->getProductCategoryForSearchFromService($language, $typeId,$merchantid);
//			BFCHelper::setSession('getProductCategoryForSearch'.$language.$typeId.$merchantid, $results, 'com_bookingforconnector');
//		}
//		return $results;
//	}
	public function getProductCategoryForSearch($language='', $typeId = 1,$merchantid=0) {
		$results = $this->getProductCategoryForSearchFromService($language, $typeId,$merchantid);
		return $results;
	}

	public function getAdditionalPurpose($language='') {
		$results = BFCHelper::getSession('getAdditionalPurpose'.$language, null , 'com_bookingforconnector');
//		if (!$session->has('getMerchantCategories','com_bookingforconnector')) {
		if ($results==null) {
			$results = $this->getAdditionalPurposeFromService($language);
			BFCHelper::setSession('getAdditionalPurpose'.$language, $results, 'com_bookingforconnector');
		}
		return $results;
//		$additionalPurpose = $this->getAdditionalPurposeFromService($language);
//		return $additionalPurpose;
	}

	public function getAdditionalPurposeFromService($language='') {		
		$options = array(
				'path' => $this->urlGetAdditionalPurpose,
				'data' => array(
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
				$return = $res->d->results->GetAdditionalPurpose;
			}elseif(!empty($res->d)){

				$return = $res->d->GetAdditionalPurpose;
			}
		}
		return $return;
	}

	public function getPrivacy($language='') {
		$privacy = BFCHelper::getSession('getPrivacy'.$language, null , 'com_bookingforconnector');
		if ($privacy==null) {
			$privacy = $this->getPrivacyFromService($language);
			BFCHelper::setSession('getPrivacy'.$language, $privacy, 'com_bookingforconnector');
		}
		return $privacy;
	}

	public function getPrivacyFromService($language='') {		
		$options = array(
				'path' => $this->urlGetPrivacy,
				'data' => array(
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
				$return = $res->d->results->GetPrivacy;
			}elseif(!empty($res->d)){

				$return = $res->d->GetPrivacy;
			}
		}
		return $return;
	}

	public function getCartMultimerchantEnabled() {
//		return true; //TODO: da fare
		$cartmultimerchantenabled = BFCHelper::getSession('cartmultimerchantenabled', null , 'com_bookingforconnector');
		if ($cartmultimerchantenabled==null) {
			$cartmultimerchantenabled = $this->getCartmultimerchantenabledFromService();
			BFCHelper::setSession('cartmultimerchantenabled', $cartmultimerchantenabled, 'com_bookingforconnector');
		}
		return $cartmultimerchantenabled;
	}

	public function getCartmultimerchantenabledFromService($language='') {		
		$options = array(
				'path' => $this->urlGetCartmultimerchantenabled,
				'data' => array(
					'$format' => 'json'
				)
		);
		
		$url = $this->helper->getQuery($options);
		
		$return = null;
		
		$r = $this->helper->executeQuery($url);
		if (isset($r)) {
			$res = json_decode($r);
			if (!empty($res->d->results)){
				$return = $res->d->results->HasMultiMerchantCart;
			}elseif(!empty($res->d)){

				$return = $res->d->HasMultiMerchantCart;
			}
		}
		return $return;
	}
	
//	protected function populateState($ordering = NULL, $direction = NULL) {
//		
//		return parent::populateState($filter_order, $filter_order_Dir);
//	}
	
	public function getItems()
	{
		// Get a storage key.
		$store = $this->getStoreId();

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		return $this->cache[$store];
	}
}
}