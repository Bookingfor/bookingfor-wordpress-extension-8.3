<?php
/**
 * The Template for displaying all merchant list
 *
 *
 * @see 	   
 * @author 		Bookingfor
 * @package 	        Bookingfor/Templates
 * @version             2.0.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
bfi()->define( "DONOTCACHEPAGE", true ); // Do not cache this page

$language = $GLOBALS['bfi_lang'];
$languageForm ='';
$base_url = get_site_url();
$cultureCode = strtolower(substr($language, 0, 2));
if (( defined( 'ICL_SITEPRESS_VERSION' ) && version_compare( ICL_SITEPRESS_VERSION, '3.2', '>=' ) ) || defined( 'POLYLANG_VERSION' )) {
		$base_url .= "/" . $cultureCode;
//		global $sitepress;
//		if($sitepress->get_current_language() != $sitepress->get_default_language()){
//		}
}
wp_enqueue_style('bfiicomoon');
$bedtype_text = array(
						'1' => __('single bed', 'bfi'),   
						'2' => __('double bed', 'bfi'),
						'3' => __('large double bed', 'bfi'),
						'4' => __('extra-large double bed', 'bfi'),
						'5' => __('bunk bed', 'bfi'),  
						'6' => __('sofa bed', 'bfi'),
						'7' => __('futon', 'bfi')                               
					);

$bedtypes_text = array(
						'1' => __('single beds', 'bfi'),   
						'2' => __('double beds', 'bfi'),
						'3' => __('large double beds', 'bfi'),
						'4' => __('extra-large double beds', 'bfi'),
						'5' => __('bunk beds', 'bfi'),  
						'6' => __('sofa beds', 'bfi'),
						'7' => __('futons', 'bfi')                               
					);
$persontype_text = array(
						'1' => __('Seniores', 'bfi'),   
						'2' => __('Adults', 'bfi'),
						'3' => __('Youth', 'bfi'),
						'4' => __('Children', 'bfi'),
						'5' => __('Infant', 'bfi'),  
					);

$currencyclass = bfi_get_currentCurrency();
$isportal = COM_BOOKINGFORCONNECTOR_ISPORTAL;
$usessl = COM_BOOKINGFORCONNECTOR_USESSL;
$enablecoupon = COM_BOOKINGFORCONNECTOR_ENABLECOUPON;
$showlogincart = COM_BOOKINGFORCONNECTOR_SHOWLOGINCART;

$currPolicy = null;
$currPolicyId = null;
$totalOrder = 0;
$totalCouponDiscount = 0;
$checkAnalytics = true;
$listName = "Cart Page";			

$layout = get_query_var( 'bfi_layout', '' );
$itemType = 0;

$portalinfo =  BFCHelper::getSubscriptionInfos();

$traceOrder = false;
$merchantIdsArr = array();
$allobjects = array();
$orderid = 0;
$orderDetails = null;
$merchanOrders = array();
$criteoConfigMerchants = null;
$criteoConfig = null;

$defaultAdultsAge = BFCHelper::$defaultAdultsAge;
$defaultSenioresAge = BFCHelper::$defaultSenioresAge;


if ($layout == 'thanks' || $layout == _x('thanks', 'Page slug', 'bfi' )) {
	$listName = "Cart Page";
	$checkAnalytics = true;
	$itemType = 2;
			$orderid = 	BFCHelper::getVar('orderid');
			$traceOrder = BFCHelper::IsInCookieOrders($orderid);
			if (!$traceOrder) {
				BFCHelper::AddToCookieOrders($orderid);
			}

			$orderDetails = BFCHelper::GetOrderDetailsById($orderid,$language);
			if (!empty($orderDetails) && !empty($orderDetails->ResourcesString)) {
				$order_resource_summary = json_decode($orderDetails->ResourcesString);
				$merchantIdsArr = array_unique(array_map(function ($i) { return $i->MerchantId; }, $order_resource_summary));
				foreach($order_resource_summary as $orderItem) {
					if(!array_key_exists($orderItem->MerchantId, $merchanOrders)){
						$merchanOrders[$orderItem->MerchantId] = $orderItem->TotalAmount;
					}else{
						$merchanOrders[$orderItem->MerchantId] +=  $orderItem->TotalAmount;
					}
				}
			}

}								
$analyticsEnabled = COM_BOOKINGFORCONNECTOR_GAENABLED == 1 && !empty(COM_BOOKINGFORCONNECTOR_GAACCOUNT) && COM_BOOKINGFORCONNECTOR_EECENABLED == 1;
add_action('wp_head', 'bfi_google_analytics_EEc', 10, 1);
do_action('wp_head', $listName);

if($checkAnalytics && $analyticsEnabled ) {
	$checkAnalytics = true;
	switch($itemType) {
		case 2:
//			$orderid = 	BFCHelper::getVar('orderid');
//			$traceOrder = BFCHelper::IsInCookieOrders($orderid);
//			if (!$traceOrder) {
//				BFCHelper::AddToCookieOrders($orderid);
//			}
			$act = 	BFCHelper::getVar('act');
			if(!empty($orderid) && $act!="Contact" && !$traceOrder ){
//						if(!empty($orderid)){
//				$order = BFCHelper::getSingleOrderFromService($orderid);
				$purchaseObject = new stdClass;
				$purchaseObject->id = "" . $orderDetails->OrderId;
				$purchaseObject->affiliation = COM_BOOKINGFORCONNECTOR_FORM_KEY;
				$purchaseObject->revenue = $orderDetails->Price;
				$purchaseObject->tax = 0.00;
				if (!empty($orderDetails->DiscountCodesString)) {
					$allDiscountCodes = json_decode($orderDetails->DiscountCodesString);
					$allCodes = implode('|',array_unique(array_map(function ($i) { return "coup-".$i->Code; }, $allDiscountCodes)));
				    if (!empty($allCodes)) {
						$purchaseObject->coupon = $allCodes;
				    }
				}
				$allobjects = array();
				$allservices = array();
				$svcTotal = 0;

//				$orderDetails = BFCHelper::GetOrderDetailsById($orderid,$language);
				if (!empty($orderDetails) && !empty($orderDetails->ResourcesString)) {
					$order_resource_summary = json_decode($orderDetails->ResourcesString);
					$merchantIdsArr = array_unique(array_map(function ($i) { return $i->MerchantId; }, $order_resource_summary));
					$merchantIds = implode(',',$merchantIdsArr);
					$merchantDetails = json_decode(BFCHelper::getMerchantsByIds($merchantIds,$language));

					foreach($order_resource_summary as $orderItem) {
						$currMerchant = null;
						foreach($merchantDetails as $merchantDetail) {
							if ($merchantDetail->MerchantId == $orderItem->MerchantId) {
								$currMerchant = $merchantDetail;
								break;
							}
						}								
						$brand = BFCHelper::string_sanitize($currMerchant->Name);
						$mainCategoryName = BFCHelper::string_sanitize($currMerchant->MainCategoryName);
						foreach($orderItem->Items as $currKey=>$res) {
							if ($currKey==0) {
								$mainObj = new stdClass;
								$mainObj->id = "" . $res->ResourceId . " - Resource";
								$mainObj->name = BFCHelper::string_sanitize($res->Name);
//										$mainObj->variant = (string)BFCHelper::getItem($order->NotesData, 'refid', 'rateplan');
								$mainObj->category = $mainCategoryName;
								$mainObj->brand = $brand;
								$mainObj->price = $res->TotalPrice;
								$mainObj->quantity = $res->Qt;
								$allVariationCodes = [];
								if (!empty($res->DiscountCodesString)) {
									$allDiscountCodes = json_decode($res->DiscountCodesString);
									$allVariationCodes = array_merge($allVariationCodes ,array_unique(array_map(function ($i) { return "coup-".$i->Code; }, $allDiscountCodes)));
//									$allCodes = implode('|',array_unique(array_map(function ($i) { return "coup-".$i->Code; }, $allDiscountCodes))) . '|';
								}
								if (!empty($res->ItemConfiguration)) {
									$itemConfigurations = json_decode($res->ItemConfiguration);
									foreach ($itemConfigurations->prices as $price ) {
										$allVariationCodes = array_merge($allVariationCodes ,array_unique(array_map(function ($i) { return "var-".$i->Name; }, $price->Variations)));
									}
								}
								if (count($allVariationCodes)>0) {
									$mainObj->coupon = implode('|',array_unique($allVariationCodes));
								}
								$allobjects[] = $mainObj;
							}else{
								$svcObj = new stdClass;
								$svcObj->id = "" . $res->ResourceId . " - Service";
								$svcObj->name = BFCHelper::string_sanitize($res->Name);
								$svcObj->category = "Services";
								$svcObj->brand = $brand;
//										$svcObj->variant = (string)BFCHelper::getItem($order->NotesData, 'nome', 'unita');
								$svcObj->price = $res->TotalPrice;
								$svcObj->quantity = $res->Qt;
								$allVariationCodes = [];
								if (!empty($res->DiscountCodesString)) {
									$allDiscountCodes = json_decode($res->DiscountCodesString);
									$allVariationCodes = array_merge($allVariationCodes ,array_unique(array_map(function ($i) { return "coup-".$i->Code; }, $allDiscountCodes)));
//									$allCodes = implode('|',array_unique(array_map(function ($i) { return "coup-".$i->Code; }, $allDiscountCodes))) . '|';
								}
								if (!empty($res->ItemConfiguration)) {
									$itemConfigurations = json_decode($res->ItemConfiguration);
									foreach ($itemConfigurations->prices as $price ) {
										$allVariationCodes = array_merge($allVariationCodes ,array_unique(array_map(function ($i) { return "var-".$i->Name; }, $price->Variations)));
									}
								}
								if (count($allVariationCodes)>0) {
									$svcObj->coupon = implode('|',array_unique($allVariationCodes));
								}
								$allobjects[] = $svcObj;
							}
						}

					}

//						$document->addScriptDeclaration('
//						callAnalyticsEEc("addProduct", ' . json_encode($allobjects) . ', "purchase", "", ' . json_encode($purchaseObject) . ');');	
			echo '<script type="text/javascript"><!--
			';
			echo ('callAnalyticsEEc("addProduct", ' . json_encode($allobjects) . ', "purchase", "", ' . json_encode($purchaseObject) . ');');
			echo "//--></script>";
					
				}
				

				
			}

			break;
	}
}
		if(COM_BOOKINGFORCONNECTOR_CRITEOENABLED && !$traceOrder && ($layout == _x('thanks', 'Page slug', 'bfi' ) || $layout == "thanks" || $layout == "default" )) {
			if($layout == "thanks" || $layout == _x('thanks', 'Page slug', 'bfi' )) {
				$criteoConfigMerchants = BFCHelper::getCriteoConfiguration(2, $merchantIdsArr);
				$criteoConfig = BFCHelper::getCriteoConfiguration(4, $merchantIdsArr, $orderid);	
//			} else if ($layout == "default") {
//				$criteoConfigMerchants = BFCHelper::getCriteoConfiguration(2, $merchantIdsArr);
			}


			if(isset($criteoConfig) && isset($criteoConfig->enabled) && $criteoConfig->enabled ) {
				$trCriteo = $criteoConfig->transactionid;
				$idPortal = substr($trCriteo, 0, strpos($trCriteo, "-")) . "-";
				$criteoOrderdetails = array();
				foreach($merchanOrders as $idmerchant=>$singleOrder) {
					if (in_array($idPortal. $idmerchant, $criteoConfigMerchants->merchants)) {
						$criteoOrderdetails[] = array(
											'id' => $idPortal. $idmerchant,
											'price' => $singleOrder,
											'quantity' => 1,
							);
					}
				}
				echo '<script type="text/javascript" src="//static.criteo.net/js/ld/ld.js" async="true"></script>';
				if( ($layout == "thanks" || $layout == _x('thanks', 'Page slug', 'bfi' )) && count($criteoOrderdetails)>0) {
					echo '<script type="text/javascript"><!--
					';
					echo('window.criteo_q = window.criteo_q || []; 
					var deviceTypeCriteo = /iPad/.test(navigator.userAgent) ? "t" : /Mobile|iP(hone|od)|Android|BlackBerry|IEMobile|Silk/.test(navigator.userAgent) ? "m" : "d";
					window.criteo_q.push( 
						{ event: "setAccount", account: ' . $criteoConfig->campaignid . '}, 
						{ event: "setSiteType", type: deviceTypeCriteo }, 
						{ event: "setEmail", email: "" }, 
						{ event: "trackTransaction", id: "' . $criteoConfig->transactionid . '",  item: ' . json_encode($criteoOrderdetails) . ' }
					);');
					echo "//--></script>";
				} 
//				else if ($layout == "default") {
//					$document->addScriptDeclaration('window.criteo_q = window.criteo_q || []; 
//					var deviceTypeCriteo = /iPad/.test(navigator.userAgent) ? "t" : /Mobile|iP(hone|od)|Android|BlackBerry|IEMobile|Silk/.test(navigator.userAgent) ? "m" : "d";
//					window.criteo_q.push( 
//						{ event: "setAccount", account: ' . $criteoConfig->campaignid . '}, 
//						{ event: "setSiteType", type: deviceTypeCriteo }, 
//						{ event: "setEmail", email: "" }, 
//						{ event: "viewItem", item: "' . $criteoConfig->merchants[0] . '" }
//					);');
//				}
			}
		}

?>
	<?php
		get_header();
		/**
		 * bookingfor_before_main_content hook.
		 */
		do_action( 'bookingfor_before_main_content' );
	?>
<?php 
$currCart = null;
$tmpUserId = BFCHelper::bfi_get_userId();
// se vengono mandati dei dati li inserisco nel carrello...
	$OrderJson = stripslashes(BFCHelper::getVar("hdnOrderData"));
	$bfiResetCart = (BFCHelper::getVar("bfiResetCart","0"));
	if(!empty($OrderJson)){
		$tmpUserId = BFCHelper::bfi_get_userId();
		$currCart = BFCHelper::AddToCartSimple($tmpUserId, $language, $OrderJson, $bfiResetCart);
	}else{
		$currCart = BFCHelper::GetCartByExternalUserSimple($tmpUserId, $language, true);
	}

$baseFieldForm= (object) 
[
    "FirstName_Enabled" => 1,
    "LastName_Enabled" => 1,
    "Email_Enabled" => 1,
    "Phone_Enabled" => 1,
    "Nationality_Enabled" => 1,
    "PersonalId_Enabled" => 1, //"PersonalId_Enabled" => 0,
    "FiscalCode_Enabled" => 1,
    "Gender_Enabled" => 1, //"Gender_Enabled" => 0,
    "Organization_Enabled" => 1, //"Organization_Enabled" => 0,
    "Address_Enabled" => 1,
    "PassportId_Enabled" => 1, //"PassportId_Enabled" => 0,
    "PassportExpiration_Enabled" => 1, //"PassportExpiration_Enabled" => 0,
    "BirthDate_Enabled" => 1, //"BirthDate_Enabled" => 0,
    "BirthLocation_Enabled" => 1, //"BirthLocation_Enabled" => 0,
    "Document_Enabled" => 1, //"Document_Enabled" => 0,
    "DocumentRelease_Enabled" => 1, //"DocumentRelease_Enabled" => 0,
    "DocumentExpiration_Enabled" => 1, //"DocumentExpiration_Enabled" => 0,
    "Language_Enabled" => 1,
    "FirstName_Required" => 1,
    "LastName_Required" => 1,
    "Email_Required" => 1,
    "Phone_Required" => 1,
    "Nationality_Required" => 1,
    "PersonalId_Required" => 0,
    "FiscalCode_Required" => 0,
    "Gender_Required" => 0,
    "Organization_Required" => 0,
    "Address_Required" => 0,
    "PassportId_Required" => 0,
    "PassportExpiration_Required" => 0,
    "BirthDate_Required" => 0,
    "BirthLocation_Required" => 0,
    "Document_Required" => 0,
    "DocumentRelease_Required" => 0,
    "DocumentExpiration_Required" => 0,
    "Language_Required" => 1,
];
if(!empty( $currCart->CheckOutMainContactFieldsString )){
	$baseFieldForm = json_decode($currCart->CheckOutMainContactFieldsString); 	
}
$crewRequests = [];
$crewRequestsForm = [];
if(!empty( $currCart->CheckOutCrewRequestDataString ) && !empty( $currCart->CheckOutCrewFieldsString )){
	$crewRequests = json_decode($currCart->CheckOutCrewRequestDataString,true); 	
	$crewRequestsForm = json_decode($currCart->CheckOutCrewFieldsString,true); 	
}
$questionsExperienceRequests = [];
$questionsRequests = [];
$simpleQuestionsRequests = [];
$questionsRequestsCrew = [];
if(!empty( $currCart->QuestionsString ) ){
	$questionsRequests = json_decode($currCart->QuestionsString,true); 	
	// filtro tutte le domande per la crew
	$questionsRequestsCrew = array_map(function($v) {
					   return array_filter($v, function($v) {
											   return $v['Scope'] === 1; }); }, $questionsRequests);	
	// filtro tutte le domande per la prenotazione
	$questionsRequests = array_map(function($v) {
					   return array_filter($v, function($v) {
											   return $v['Scope'] === 0; }); }, $questionsRequests);
	// semplifico tutte le domande per la prenotazione
	if (count($questionsRequests)>0) {
		foreach ($questionsRequests as $questionsRequestRes ) {
			foreach ($questionsRequestRes as $questionsRequest ) {
				$simpleQuestionsRequests[] = $questionsRequest;
			}
		}
	}										   

}

$resources = [];
//echo "<pre>crewRequest: ";
//echo print_r($crewRequests);
//echo "</pre>";
//echo "<pre>crewRequestForm: ";
//echo print_r($crewRequestsForm);
//echo "</pre>";

$currentCartsItems = BFCHelper::getSession('totalItems', 0, 'bfi-cart');
?>
<script type="text/javascript">
<!--
 //Prevent POST Resubmit 
if ( window.history.replaceState ) {
	window.history.replaceState( null, null, window.location.href );
}
jQuery(".bfibadge").html('<?php echo ($currentCartsItems>0) ?$currentCartsItems:"";?>');	
//-->
</script>
<?php 

switch ( $layout) {
	case _x('thanks', 'Page slug', 'bfi' ):
	case 'thanks':
		bfi_get_template("thanks.php"); 

	break;
	case _x('errors', 'Page slug', 'bfi' ):
	case 'errors':
		bfi_get_template("errors.php"); 
		$sendAnalytics = false;
	break;
}

if(empty($layout)){

$totalOrder = 0;

$listStayConfigurations = array();
$dateTimeNow =  new DateTime('UTC');



$cartEmpty=empty($currCart);
$cartConfig = null;
if(!$cartEmpty){
	$cartConfig = $currCart->CartDetails;
	if (!empty($cartConfig->ResourcesString)) {
		$cartConfig->Resources = json_decode($cartConfig->ResourcesString);
	}
	if (!empty($cartConfig->DiscountCodesString)) {
		$cartConfig->DiscountCodes = json_decode($cartConfig->DiscountCodesString);
	}

	//$cartConfig = json_decode(($currCart->CartConfiguration));
	if(isset($cartConfig->Resources) && count($cartConfig->Resources)>0){
		$cartEmpty = false;
	}else{
		$cartEmpty = true;
	}

}

	if($cartEmpty){
		echo '<div class="bfi-content">'.__('Your Cart is empty! ', 'bfi').'</div>';
	}else{
$allResourceId = array();
$allServiceIds = array();
$allPolicyHelp = array();
$allResourceBookable = array();
$allResourceNoBookable = array();
$allPolicies = array();

//		$modelMerchant = new BookingForConnectorModelMerchantDetails;
//		$modelResource = new BookingForConnectorModelResource;
//		$merchantdetails_page = get_post( bfi_get_page_id( 'merchantdetails' ) );
//		$url_merchant_page = get_permalink( $merchantdetails_page->ID );
//
//		$accommodationdetails_page = get_post( bfi_get_page_id( 'accommodationdetails' ) );
//		$url_resource_page = get_permalink( $accommodationdetails_page->ID );
//		
//		$experiencedetails_page = get_post( bfi_get_page_id( 'experiencedetails' ) );	
//		$url_resource_page_experience = get_permalink( $experiencedetails_page->ID );
//
//		$cartdetails_page = get_post( bfi_get_page_id( 'cartdetails' ) );
//		$url_cart_page = get_permalink( $cartdetails_page->ID );
//		if($usessl){
//			$url_cart_page = str_replace( 'http:', 'https:', $url_cart_page );
//		}
//		$groupresourcedetails_page = get_post( bfi_get_page_id( 'resourcegroupdetails' ) );
//		$url_groupresource_page = get_permalink( $groupresourcedetails_page->ID );

		$url_merchant_page = BFCHelper::getPageUrl('merchantdetails');
		$url_resource_page = BFCHelper::getPageUrl('accommodationdetails');
		$url_resource_page_experience = BFCHelper::getPageUrl('experiencedetails');
		$url_cart_page = BFCHelper::getPageUrl('cartdetails');
		$url_groupresource_page = BFCHelper::getPageUrl('resourcegroupdetails');

		$cartId= isset($currCart->CartId)?$currCart->CartId:0;
//		$cartConfig = json_decode($currCart->CartConfiguration);

		$cCCTypeList = array("Visa","MasterCard");
		$minyear = date("y");
		$maxyear = $minyear+99;
		$formRoute = $base_url .'/bfi-api/v1/task?task=sendOrders'; 
		$formRouteDelete = $base_url .'/bfi-api/v1/task?task=DeleteFromCart'; 
		$formRouteaddDiscountCodes = $base_url .'/bfi-api/v1/task?task=addDiscountCodesToCart'; 

//		$privacy = BFCHelper::GetPrivacy($language);
//		$additionalPurpose = BFCHelper::GetAdditionalPurpose($language);

		$policyId = $cartConfig->PolicyId;
//		$currPolicy =  BFCHelper::GetPolicyById($policyId, $language);

		$deposit = 0;
		$totalWithVariation = 0;
		$totalRequestedWithVariation = 0;
		$totalRequest = 0;
		$totalAmount = 0;	
		$totalQt = 0;	
		$listMerchantsCart = array();
		$listResourcesCart = array();
		$listResourceIdsToDelete = array();
		$listMerchantBookingTypes = array();
		$listPolicyIds = array();
		$listPolicyIdsBookable = array();
		$listPolicyIdsNoBookable = array();

		$now = new DateTime('UTC');
		$now->setTime(0,0,0);
		
		$resourceDetail = array();
		$merchantDetail = array();

		// cerco risorse scadute come checkin
		foreach ($cartConfig->Resources as $keyRes=>$resource) {
			$id = $resource->ResourceId;
			$resources[$resource->CartOrderId] = $resource;
		
			$merchantId = $resource->MerchantId;
			$listResourcesCart[] = $id;
			$tmpCheckinDate = new DateTime('UTC');
			if($cartId==0){
				$tmpCheckinDate = DateTime::createFromFormat('d/m/Y\TH:i:s', $resource->FromDate,new DateTimeZone('UTC'));
//				$tmpCheckinDate->setTime(0,0,1);
			}else{
				$tmpCheckinDate = new DateTime($resource->FromDate,new DateTimeZone('UTC'));
			}
			if($tmpCheckinDate < $now){
				if($cartId==0){
					unset($cartConfig->Resources[$keyRes]);  
				}else{
					$listResourceIdsToDelete[] = $resource->CartOrderId;
				}
			}
						
			if (isset($listMerchantsCart[$merchantId])) {
				$listMerchantsCart[$merchantId][] = $resource;
			} else {
				$listMerchantsCart[$merchantId] = array($resource);
			}
			
			if(!empty($resource->ExtraServices)) { 
				foreach($resource->ExtraServices as $sdetail) {					
					$listResourcesCart[] = $sdetail->PriceId;
				}
			}
			
			
			
			$res = new stdClass();
			$res->ResourceId = $resource->ResourceId;
			$res->MerchantId = $resource->MerchantId;
			$res->Name = $resource->Name;
			$res->Description = $resource->ResourceDescription;
			$res->TagsIdList = $resource->ResourceTagsIdList;
			$res->ImgUrl = $resource->ImgUrl;
			$res->Rating = $resource->Rating;
			$res->RatingSubValue = $resource->RatingSubValue;
			$res->RatingIconSrc = $resource->RatingIconSrc;
			$res->RatingIconType = $resource->RatingIconType;
			$res->RatingImgUrl = $resource->RatingImgUrl;
			$resourceId = $resource->ResourceId;
			$allResourceId[]=$resourceId;
			if (!isset($resourceDetail[$resourceId])) {
				$resourceDetail[$resourceId] = $res;
			}
			$merchantId = $resource->MerchantId;
			
			$mrc = new stdClass();
			$mrc->MerchantId = $resource->MerchantId;
			$mrc->Name = $resource->MerchantName;
			$mrc->Description = $resource->MerchantDescription;
			$mrc->TagsIdList = $resource->MerchantTagsIdList;
			$mrc->Address = $resource->MerchantAddress;
			$mrc->ZipCode = $resource->MerchantZipCode;
			$mrc->StateName = $resource->MerchantStateName;
			$mrc->RegionName = $resource->MerchantRegionName;
			$mrc->CityName = $resource->MerchantCityName;
			$mrc->ImgUrl = $resource->MerchantImgUrl;
			$mrc->Rating = $resource->MerchantRating;
			$mrc->RatingSubValue = $resource->MerchantRatingSubValue;
			$mrc->RatingIconSrc = $resource->MerchantRatingIconSrc;
			$mrc->RatingIconType = $resource->MerchantRatingIconType;
			$mrc->RatingImgUrl = $resource->MerchantRatingImgUrl;
			
			if (!isset($merchantDetail[$merchantId])) {
				$merchantDetail[$merchantId] = $mrc;
			}

		}
		if(count($listResourceIdsToDelete)>0){
			$tmpUserId = BFCHelper::bfi_get_userId();
			$currCart = BFCHelper::DeleteFromCartByExternalUser($tmpUserId, $language, implode(",", $listResourceIdsToDelete));
//			$cartdetails_page = get_post( bfi_get_page_id( 'cartdetails' ) );
//			$url_cart_page = get_permalink( $cartdetails_page->ID );
//			if($usessl){
//				$url_cart_page = str_replace( 'http:', 'https:', $url_cart_page );
//			}
			$url_cart_page = BFCHelper::getPageUrl('cartdetails');
			wp_redirect($url_cart_page);
			exit;
		}
		if($cartId==0){
			BFCHelper::setSession('hdnOrderData', json_encode($cartConfig->Resources), 'bfi-cart');
		}

		//$tmpResources =  json_decode(BFCHelper::GetResourcesByIds(implode(",", $listResourcesCart),$language));

				

?>
<div class="bfi-content bfi-rowcontainer">
<div class="bfi-cart-title"><?php _e('Secure booking. We protect your information', 'bfi') ?></div>
						

	<?php bfi_get_template("shared/menu_small_booking.php"); ?>
<script type="text/javascript">
<!--
	jQuery(function()
	{
		jQuery(".bfi-menu-booking a:eq(2)").removeClass(" bfi-alternative3");
	});
//-->
</script>
	<div class="bfi-border bfi-cart-title2"><i class="fa fa-check-square"></i> <?php _e('Your reservation includes', 'bfi') ?></div>
<div class="bfi-table-responsive">
		<table class="bfi-table bfi-table-bordered bfi-table-cart" style="margin-top: 20px;">
			<thead>
				<tr>
					<th><div><?php _e('Information', 'bfi') ?></div></th>
					<th><div><?php _e('For', 'bfi') ?></div></th>
					<th><div><?php _e('Price', 'bfi') ?></div></th>
					<th><div><?php _e('Options', 'bfi') ?></div></th>
					<th><div><?php _e('Qt.', 'bfi') ?></div></th>
					<th><div><!-- <?php _e('Total price', 'bfi') ?> --></div></th>
				</tr>
			</thead>
<?php
	foreach ($listMerchantsCart as $merchant_id=>$merchantResources) // foreach $listMerchantsCart
	{
		$MerchantDetail = $merchantDetail[$merchant_id];  //$modelMerchant->getItem($merchant_id);	 
		
//		echo "<pre>";
//		echo print_r($MerchantDetail);
//		echo "</pre>";

		// recupero le fasce di età del merchant 
		$defaultAdultsAge = BFCHelper::$defaultAdultsAge;
		$defaultSenioresAge = BFCHelper::$defaultSenioresAge;
		//$useSeniores= isset($_REQUEST['seniores']);
		$useSeniores = 0;
		$merchant = BFCHelper::getMerchantbyId($MerchantDetail->MerchantId);
		// controllo se sono passate fasce di età nel merchant, allora sovrascrivo i dati
		if (!empty($merchant->PaxRangesString)) {
			$paxRanges = json_decode($merchant->PaxRangesString);	
			$fullPaxs =  array_values(array_filter($paxRanges, function($ages) {
					return $ages->FullPax;
				}));
			if (!empty($fullPaxs) && count($fullPaxs)>0) {
				$defaultAdultsAge = ($defaultAdultsAge<$fullPaxs[0]->MinAge)?$fullPaxs[0]->MinAge:$defaultAdultsAge;
				$defaultOversAge = $fullPaxs[0]->MaxAge;
				$overPaxs =  array_values(array_filter($paxRanges, function($ages) use ($defaultOversAge) {
				return !$ages->FullPax && $ages->MinAge>=$defaultOversAge;
					}));

				if (!empty($overPaxs) && count($overPaxs)>0) {
					$useSeniores = 1;
					$defaultSenioresAge = $fullPaxs[0]->MaxAge;
				}
			}
		}

		$routeMerchant = $url_merchant_page . $MerchantDetail->MerchantId.'-'.BFI()->seoUrl($MerchantDetail->Name);
		$nRowSpan = 1;
				
		$mrcindirizzo = "";
		$mrccap = "";
		$mrccomune = "";
		$mrcstate = "";

		if (empty($MerchantDetail->AddressData)){
			$mrcindirizzo = isset($MerchantDetail->Address)?$MerchantDetail->Address:""; 
			$mrccap = isset($MerchantDetail->ZipCode)?$MerchantDetail->ZipCode:""; 
			$mrccomune = isset($MerchantDetail->CityName)?$MerchantDetail->CityName:""; 
			$mrcstate = isset($MerchantDetail->StateName)?$MerchantDetail->StateName:""; 
		}else{
			$addressData = isset($MerchantDetail->AddressData)?$MerchantDetail->AddressData:"";
			$mrcindirizzo = isset($addressData->Address)?$addressData->Address:""; 
			$mrccap = isset($addressData->ZipCode)?$addressData->ZipCode:""; 
			$mrccomune = isset($addressData->CityName)?$addressData->CityName:""; 
			$mrcstate = isset($addressData->StateName)?$addressData->StateName:"";
		}
		
		foreach ($merchantResources as $res )
		{
			$nRowSpan += 1;
			if(!empty($res->ExtraServices)) { 
				foreach($res->ExtraServices as $sdetail) {					
					$nRowSpan += 1;
				}
			}
		}
//$mrcAcceptanceCheckInHours=0;
//$mrcAcceptanceCheckInMins=0;
//$mrcAcceptanceCheckInSecs=1;
//$mrcAcceptanceCheckOutHours=0;
//$mrcAcceptanceCheckOutMins=0;
//$mrcAcceptanceCheckOutSecs=1;
//if(!empty($MerchantDetail->AcceptanceCheckIn) && !empty($MerchantDetail->AcceptanceCheckOut) && $MerchantDetail->AcceptanceCheckIn != "-" && $MerchantDetail->AcceptanceCheckOut != "-"){
//	$tmpAcceptanceCheckIn=$MerchantDetail->AcceptanceCheckIn;
//	$tmpAcceptanceCheckOut=$MerchantDetail->AcceptanceCheckOut;
//	$tmpAcceptanceCheckIns = explode('-', $tmpAcceptanceCheckIn);
//	$tmpAcceptanceCheckOuts = explode('-', $tmpAcceptanceCheckOut);	
//	$correctAcceptanceCheckIns = $tmpAcceptanceCheckIns[0];
//	if(empty( $correctAcceptanceCheckIns )){
//		$correctAcceptanceCheckIns = $tmpAcceptanceCheckIns[1];
//	}
//	if(empty( $correctAcceptanceCheckIns )){
//		$correctAcceptanceCheckIns = "0:0";
//	}
//	$correctAcceptanceCheckOuts = $tmpAcceptanceCheckOuts[1];
//	if(empty( $correctAcceptanceCheckOuts )){
//		$correctAcceptanceCheckOuts = $tmpAcceptanceCheckOuts[0];
//	}
//	if(empty( $correctAcceptanceCheckOuts )){
//		$correctAcceptanceCheckOuts = "0:0";
//	}
//	if (strpos($correctAcceptanceCheckIns, ":") === false) {
//		$correctAcceptanceCheckIns .= ":0";
//	}
//	if (strpos($correctAcceptanceCheckOuts, ":") === false) {
//		$correctAcceptanceCheckOuts .= ":0";
//	}
//
//	list($mrcAcceptanceCheckInHours,$mrcAcceptanceCheckInMins,$mrcAcceptanceCheckInSecs) = explode(':',$correctAcceptanceCheckIns.":1");
//	list($mrcAcceptanceCheckOutHours,$mrcAcceptanceCheckOutMins,$mrcAcceptanceCheckOutSecs) = explode(':',$correctAcceptanceCheckOuts.":1");
//}

?>
			<tr >
				<td colspan="6" class="bfi-merchant-cart">
					<div class="bfi-item-title">
						<a href="<?php echo $isportal?$routeMerchant:"#";?>" ><?php echo $MerchantDetail->Name?></a>
						<span class="bfi-item-rating">
<?php 
				$ratingModel = array(
					"ratingModel"=>$MerchantDetail
				);
				bfi_get_template("shared/stars_rating.php",$ratingModel);
?>
						</span>
					</div>
					<br />
					<span class="street-address"><?php echo $mrcindirizzo ?></span>, <span class="postal-code "><?php echo $mrccap ?></span> <span class="locality"><?php echo $mrccomune ?></span> <span class="state">, <?php echo $mrcstate ?></span><br />

				</td>
			</tr>
			<?php 
			foreach ($merchantResources as $keyRes=>$res )
			{
				$nad = 0;
				$nch = 0;
				$nse = 0;
				$countPaxes = 0;

				if($cartId==0){
					$res->CartOrderId = $keyRes;  
				}
				$nchs = array(null,null,null,null,null,null);
					$paxages = $res->PaxAges;
					if(is_array($paxages)){
						$countPaxes = array_count_values($paxages);
						$nchs = array_values(array_filter($paxages, function($age) use ($defaultAdultsAge){
							if ($age < (int)$defaultAdultsAge)
								return true;
							return false;
						}));
					}
				array_push($nchs, null,null,null,null,null,null);
				if($countPaxes>0){
					foreach ($countPaxes as $key => $count) {
						if ($key >= $defaultAdultsAge) {
							if ($key >= $defaultSenioresAge) {
								$nse += $count;
							} else {
								$nad += $count;
							}
						} else {
							$nch += $count;
						}
					}
				}

				
				$countPaxes = $res->PaxNumber;

				$nchs = array_slice($nchs,0,$nch);
				$resource = $resourceDetail[$res->ResourceId];  //$modelMerchant->getItem($merchant_id);
				$resourceDescription = BFCHelper::getLanguage($resource->Description, $language, null, array('ln2br'=>'ln2br', 'bbcode'=>'bbcode', 'striptags'=>'striptags'));											
				$currMerchant = BFCHelper::getMerchantFromServicebyId($res->MerchantId);
																																				
				if(!empty($res->ResourceGroupId)) {
				    if ($res->ResourceGroupId == $currMerchant->DefaultProductGroupId) {
						$routeResource = $routeMerchant ;
					}else{
						
						$routeResource = $url_groupresource_page . $res->ResourceGroupId .'-'.BFI()->seoUrl($res->ResourceGroupName);
				    }
				}else{
					switch ($res->ItemTypeId ) {
						case bfi_ItemType::Experience :
							$routeResource  = $url_resource_page_experience.$res->ResourceId.'-'.BFI()->seoUrl($resource->Name);
						break;
						default:      
							$routeResource = $url_resource_page . $resource->ResourceId .'-'.BFI()->seoUrl($resource->Name);
						break;
					} // end switch

//					$routeResource = $url_resource_page . $resource->ResourceId .'-'.BFI()->seoUrl($resource->Name);
				}
				$totalPricesExtraIncluded = 0;
				$totalAmountPricesExtraIncluded = 0;
				$pricesExtraIncluded = null;

				if(!empty( $res->PricesExtraIncluded )){
					$pricesExtraIncluded = json_decode($res->PricesExtraIncluded);
					foreach($pricesExtraIncluded as $sdetail) {					
						$totalPricesExtraIncluded +=$sdetail->TotalDiscounted;
						$totalAmountPricesExtraIncluded +=$sdetail->TotalAmount;
					}
				}
				$currExcludedPriceTypeValues = isset($res->ExcludedPriceTypeValues)?(array)$res->ExcludedPriceTypeValues:[];

				$IsBookable = $res->IsBookable;
				if ($IsBookable) {
				    $allResourceBookable[] = $resource->Name;
				}else{
				    $allResourceNoBookable[] = $resource->Name;
				}
				$hidePeopleAge = 0;
				if (!empty($res->HidePeopleAge)) {
					$hidePeopleAge = 1;
				}
$mrcAcceptanceCheckInHours=0;
$mrcAcceptanceCheckInMins=0;
$mrcAcceptanceCheckInSecs=1;
$mrcAcceptanceCheckOutHours=0;
$mrcAcceptanceCheckOutMins=0;
$mrcAcceptanceCheckOutSecs=1;

if(!empty($res->MerchantAcceptanceCheckIn) && !empty($res->MerchantAcceptanceCheckOut) && $res->MerchantAcceptanceCheckIn != "-" && $res->MerchantAcceptanceCheckOut != "-"){
	$tmpAcceptanceCheckIn=$res->MerchantAcceptanceCheckIn;
	$tmpAcceptanceCheckOut=$res->MerchantAcceptanceCheckOut;
	$tmpAcceptanceCheckIns = explode('-', $tmpAcceptanceCheckIn);
	$tmpAcceptanceCheckOuts = explode('-', $tmpAcceptanceCheckOut);	
	$correctAcceptanceCheckIns = $tmpAcceptanceCheckIns[0];
	if(empty( $correctAcceptanceCheckIns )){
		$correctAcceptanceCheckIns = $tmpAcceptanceCheckIns[1];
	}
	if(empty( $correctAcceptanceCheckIns )){
		$correctAcceptanceCheckIns = "0:0";
	}
	$correctAcceptanceCheckOuts = $tmpAcceptanceCheckOuts[1];
	if(empty( $correctAcceptanceCheckOuts )){
		$correctAcceptanceCheckOuts = $tmpAcceptanceCheckOuts[0];
	}
	if(empty( $correctAcceptanceCheckOuts )){
		$correctAcceptanceCheckOuts = "0:0";
	}
	if (strpos($correctAcceptanceCheckIns, ":") === false) {
		$correctAcceptanceCheckIns .= ":0";
	}
	if (strpos($correctAcceptanceCheckOuts, ":") === false) {
		$correctAcceptanceCheckOuts .= ":0";
	}

	list($mrcAcceptanceCheckInHours,$mrcAcceptanceCheckInMins,$mrcAcceptanceCheckInSecs) = explode(':',$correctAcceptanceCheckIns.":1");
	list($mrcAcceptanceCheckOutHours,$mrcAcceptanceCheckOutMins,$mrcAcceptanceCheckOutSecs) = explode(':',$correctAcceptanceCheckOuts.":1");
}
	$defaultAdultsAge = BFCHelper::$defaultAdultsAge;

			?>
                                <tr>
                                    <td>
										<div class="bfi-resname">
											<a href="<?php echo $routeResource?>" <?php echo COM_BOOKINGFORCONNECTOR_TARGETURL ?>><?php echo $resource->Name ?></a>
										</div>
										
										<?php if(!empty($res->ResourceGroupItemId)) { ?>
												<?php echo $res->ResourceGroupSectorName  ?> <?php _e('seat', 'bfi') ?> <?php echo $res->ResourceGroupItemName  ?>
										<?php } ?>
										<?php if(!empty($resourceDescription)) { ?>
											<div class="bfi-description bfi-shortentext"><?php echo $resourceDescription ?></div>
											<br />
										<?php } ?>
										<?php if(empty($res->ResourceGroupItemId)) { ?>
											<div class="bfi-cart-person">
											<?php 
											if(!empty( $hidePeopleAge )){
												echo $countPaxes . " " . __('People', 'bfi');
											} else{
												if ($res->ItemTypeId == bfi_ItemType::Experience) {
													$experience = BFCHelper::GetExperienceById($res->ResourceId);
													if (!empty($experience->PaxRangesString)) {
													    
														$paxRanges = json_decode($experience->PaxRangesString);	
														$fullPaxs =  array_values(array_filter($paxRanges, function($ages) {
																return $ages->FullPax;
															}));
														// riordino le età e prima gli adulti
														usort($paxRanges, function($a, $b)
														{
															return $b->MaxAge>$a->MaxAge;
														});
														usort($paxRanges, function($a, $b)
														{
															return $b->FullPax>$a->FullPax;
														});
														
														if (!empty($fullPaxs) && count($fullPaxs)>0) {
															$defaultAdultsAge = ($defaultAdultsAge<$fullPaxs[0]->MinAge)?$fullPaxs[0]->MinAge:$defaultAdultsAge;
														}
														// se ho range di età ciclo per associare le giuste quantità

														if (count($paxages)>0 && count($paxRanges)>0) {	
																foreach ($paxages as $PaxAge) {	
																	foreach ($paxRanges as $keypr => $paxRange) {										
																		if ($paxRange->MinAge<= $PaxAge && $paxRange->MaxAge > $PaxAge) {
																			$paxRanges[$keypr]->value += 1;						
																		}
																	}
																}
														}

														foreach ($paxRanges as $key => $paxRange) {										
																$persontype="";
																switch (true) {
																	case $paxRange->FullPax  : //Adulti
																		$persontype = $persontype_text[2];
																		break;
																	case !$paxRange->FullPax && $paxRange->MaxAge <= $defaultAdultsAge && $paxRange->MinAge >=13 : //Youth
																		$persontype = $persontype_text[3];
																		break;
																	case !$paxRange->FullPax &&$paxRange->MaxAge <= $defaultAdultsAge && $paxRange->MinAge >=2  && $paxRange->MinAge <13 : //Youth
																		$persontype = $persontype_text[4];
																		break;
																	case !$paxRange->FullPax &&$paxRange->MaxAge <= $defaultAdultsAge && $paxRange->MinAge >=0 && $paxRange->MinAge <2  : //Youth
																		$persontype = $persontype_text[5];
																		break;
																	default: //Seniores
																		$persontype = $persontype_text[2]; 
																}
																?>
																<span class="bfi-comma" ><span><?php echo $paxRange->value ?></span> <?php _e($persontype, 'bfi'); ?> (<?php echo $paxRange->MinAge ?>-<?php echo $paxRange->MaxAge?> <?php _e('Years', 'bfi'); ?>)</span>
																<?php 
														}													}

												}else{
													?>
														<?php if ($nad > 0){ ?><?php echo $nad ?> <?php _e('Adults', 'bfi') ?> <?php } ?>
														<?php if ($nse > 0){ ?><?php if ($nad > 0){ ?>, <?php } ?>
															<?php echo $nse ?> <?php echo sprintf(__('Over %s', 'bfi'), $defaultSenioresAge); ?>
														<?php } ?>
														<?php if ($nch > 0){ ?>
															, <?php echo $nch ?> <?php _e('Children', 'bfi') ?> (<?php echo implode(" ".__('Years', 'bfi') .', ',$nchs) ?> <?php _e('Years', 'bfi') ?> )
														<?php } ?>
													<?php 
												
												}
											
											}
											?>
										   </div>
										<?php } ?>
								<?php																
								/*-----------checkin/checkout--------------------*/	
									if ($res->AvailabilityType == 0 )
									{
										$currCheckIn = new DateTime('UTC');
										$currCheckOut = new DateTime('UTC');
										if($cartId==0){
											$currCheckIn = DateTime::createFromFormat('d/m/Y\TH:i:s', $res->FromDate,new DateTimeZone('UTC'));
											$currCheckOut = DateTime::createFromFormat('d/m/Y\TH:i:s', $res->ToDate,new DateTimeZone('UTC'));

										}else{
											$currCheckIn = new DateTime($res->FromDate,new DateTimeZone('UTC'));
											$currCheckOut = new DateTime($res->ToDate,new DateTimeZone('UTC'));
											$currCheckIn->setTime($mrcAcceptanceCheckInHours,$mrcAcceptanceCheckInMins,$mrcAcceptanceCheckInSecs);
											$currCheckOut->setTime($mrcAcceptanceCheckOutHours,$mrcAcceptanceCheckOutMins,$mrcAcceptanceCheckOutSecs);
										}										
										$currCheckInFull = clone $currCheckIn;
										$currCheckOutFull =clone $currCheckOut;
										$currCheckInFull->setTime(0,0,1);
										$currCheckOutFull->setTime(0,0,1);
										
										$currDiff = $currCheckOutFull->diff($currCheckInFull);
										
										$strDuration = "";
										if ($currDiff->d >= 1) {
											$strDuration .= sprintf(__(' %d day/s' ,'bfi'), $currDiff->d);
										}
										if ($currDiff->h > 0 || $currDiff->i % 60 > 0) {
											$strDuration .= (!empty($strDuration) ? ", " : "") . ($currDiff->h . ":" . $currDiff->i) . " " . __('hours', 'bfi');
										}
										

									?>
										<div class="bfi-timeperiod " >
											<div class="bfi-row ">
												<div class="bfi-col-md-3 bfi-col-xs-3 bfi-title"><?php _e('Check-in', 'bfi') ?>
												</div>	
												<div class="bfi-col-md-9 bfi-col-xs-9 bfi-time bfi-text-right"><span class="bfi-time-checkin"><?php echo date_i18n('D',$currCheckIn->getTimestamp()) ?> <?php echo $currCheckIn->format("d") ?> <?php echo date_i18n('M',$currCheckIn->getTimestamp()).' '.$currCheckIn->format("Y") ?></span> <?php _e('from', 'bfi') ?> <span class="bfi-time-checkin-hours"><?php echo $currCheckIn->format('H:i') ?></span>
												</div>	
											</div>	
											<div class="bfi-row ">
												<div class="bfi-col-md-3 bfi-col-xs-3 bfi-title"><?php _e('Check-out', 'bfi') ?>
												</div>	
												<div class="bfi-col-md-9 bfi-col-xs-9 bfi-time bfi-text-right"><span class="bfi-time-checkout"><?php echo date_i18n('D',$currCheckOut->getTimestamp()) ?> <?php echo $currCheckOut->format("d") ?> <?php echo date_i18n('M',$currCheckOut->getTimestamp()).' '.$currCheckOut->format("Y") ?></span> <?php _e('until', 'bfi') ?> <span class="bfi-time-checkout-hours"><?php echo $currCheckOut->format('H:i') ?></span>
												</div>	
											</div>	
											<div class="bfi-row">
												<div class="bfi-col-md-3 bfi-col-xs-3 "><?php _e('Total', 'bfi') ?>:
												</div>	
												<div class="bfi-col-md-9 bfi-col-xs-9 bfi-text-right">
													<span class="bfi-total-duration"><?php echo $strDuration ?></span> 
												</div>	
											</div>	
										</div>
								<?php
									}
									if ($res->AvailabilityType == 1 )
									{
										$currCheckIn = new DateTime('UTC');
										$currCheckOut = new DateTime('UTC');
										if($cartId==0){
											$currCheckIn = DateTime::createFromFormat('d/m/Y\TH:i:s', $res->FromDate,new DateTimeZone('UTC'));
											$currCheckOut = DateTime::createFromFormat('d/m/Y\TH:i:s', $res->ToDate,new DateTimeZone('UTC'));
										}else{
											$currCheckIn = new DateTime($res->FromDate,new DateTimeZone('UTC'));
											$currCheckOut = new DateTime($res->ToDate,new DateTimeZone('UTC'));
											$currCheckIn->setTime($mrcAcceptanceCheckInHours,$mrcAcceptanceCheckInMins,$mrcAcceptanceCheckInSecs);
											$currCheckOut->setTime($mrcAcceptanceCheckOutHours,$mrcAcceptanceCheckOutMins,$mrcAcceptanceCheckOutSecs);
										}										

										$currCheckInFull = clone $currCheckIn;
										$currCheckOutFull =clone $currCheckOut;
										$currCheckInFull->setTime(0,0,1);
										$currCheckOutFull->setTime(0,0,1);

										$currDiff = $currCheckOutFull->diff($currCheckInFull);
									?>
										<div class="bfi-timeperiod " >
											<div class="bfi-row ">
												<div class="bfi-col-md-3 bfi-col-xs-3 bfi-title"><?php _e('Check-in', 'bfi') ?>
												</div>	
												<div class="bfi-col-md-9 bfi-col-xs-9 bfi-time bfi-text-right"><span class="bfi-time-checkin"><?php echo date_i18n('D',$currCheckIn->getTimestamp()) ?> <?php echo $currCheckIn->format("d") ?> <?php echo date_i18n('M',$currCheckIn->getTimestamp()).' '.$currCheckIn->format("Y") ?></span> <?php _e('from', 'bfi') ?> <span class="bfi-time-checkin-hours"><?php echo $currCheckIn->format('H:i') ?></span>
												</div>	
											</div>	
											<div class="bfi-row ">
												<div class="bfi-col-md-3 bfi-col-xs-3 bfi-title"><?php _e('Check-out', 'bfi') ?>
												</div>	
												<div class="bfi-col-md-9 bfi-col-xs-9 bfi-time bfi-text-right"><span class="bfi-time-checkout"><?php echo date_i18n('D',$currCheckOut->getTimestamp()) ?> <?php echo $currCheckOut->format("d") ?> <?php echo date_i18n('M',$currCheckOut->getTimestamp()).' '.$currCheckOut->format("Y") ?></span> <?php _e('until', 'bfi') ?> <span class="bfi-time-checkout-hours"><?php echo $currCheckOut->format('H:i') ?></span>
												</div>	
											</div>	
											<div class="bfi-row">
												<div class="bfi-col-md-3 bfi-col-xs-3 "><?php _e('Total', 'bfi') ?>:
												</div>	
												<div class="bfi-col-md-9 bfi-col-xs-9 bfi-text-right"><span class="bfi-total-duration"><?php echo $currDiff->d; ?></span> <?php _e('nights', 'bfi') ?>
												</div>	
											</div>	
										</div>
								<?php
									}
									if ($res->AvailabilityType == 2)
									{
										
										$currCheckIn = DateTime::createFromFormat("YmdHis", $res->CheckInTime,new DateTimeZone('UTC'));
										$currCheckOut = DateTime::createFromFormat("YmdHis", $res->CheckInTime,new DateTimeZone('UTC'));
										$currCheckOut->add(new DateInterval('PT' . $res->TimeDuration . 'M'));

										$currDiff = $currCheckOut->diff($currCheckIn);
										$timeDuration = $currDiff->h + round(($currDiff->i/60), 2);
										
										
										$strDuration = "";
										if ($currDiff->d >0) {
											$strDuration .= sprintf(__(' %d day/s' ,'bfi'), $currDiff->d);
										}
										if ($currDiff->h > 0 || round(($currDiff->i / 60), 2) > 0) {
											$strDuration .= (!empty($strDuration) ? ", " : "") . ($currDiff->h + round(($currDiff->i / 60), 2)) . " " . __('hours', 'bfi');
										}
										
									?>
										<div class="bfi-timeperiod " >
											<div class="bfi-row ">
												<div class="bfi-col-md-3 bfi-col-xs-3 bfi-title"><?php _e('Check-in', 'bfi') ?>
												</div>	
												<div class="bfi-col-md-9 bfi-col-xs-9 bfi-time bfi-text-right"><span class="bfi-time-checkin"><?php echo date_i18n('D',$currCheckIn->getTimestamp()) ?> <?php echo $currCheckIn->format("d") ?> <?php echo date_i18n('M',$currCheckIn->getTimestamp()).' '.$currCheckIn->format("Y") ?></span> - <span class="bfi-time-checkin-hours"><?php echo $currCheckIn->format('H:i') ?></span>
												</div>	
											</div>	
											<div class="bfi-row ">
												<div class="bfi-col-md-3 bfi-col-xs-3 bfi-title"><?php _e('Check-out', 'bfi') ?>
												</div>	
												<div class="bfi-col-md-9 bfi-col-xs-9 bfi-time bfi-text-right"><span class="bfi-time-checkout"><?php echo date_i18n('D',$currCheckOut->getTimestamp()) ?> <?php echo $currCheckOut->format("d") ?> <?php echo date_i18n('M',$currCheckOut->getTimestamp()).' '.$currCheckOut->format("Y") ?></span> - <span class="bfi-time-checkout-hours"><?php echo $currCheckOut->format('H:i') ?></span>
												</div>	
											</div>	
											<div class="bfi-row">
												<div class="bfi-col-md-3 bfi-col-xs-3 "><?php _e('Total', 'bfi') ?>:
												</div>	
												<div class="bfi-col-md-9 bfi-col-xs-9 bfi-text-right">
													<span class="bfi-total-duration"><?php echo $strDuration ?></span>
												</div>	
											</div>	
										</div>
								<?php
									}
/*-------------------------------*/	
									if ($res->AvailabilityType == 3)
									{

										$currCheckIn = new DateTime($res->FromDate,new DateTimeZone('UTC'));										
										$currCheckOut = clone $currCheckIn;
										$currCheckIn->setTime(0,0,1);
										$currCheckOut->setTime(0,0,1);
										$currCheckIn->add(new DateInterval('PT' . $res->TimeSlotStart . 'M'));
										$currCheckOut->add(new DateInterval('PT' . $res->TimeSlotEnd . 'M'));

										$currDiff = $currCheckOut->diff($currCheckIn);
										$strDuration = "";
										if ($currDiff->d >0) {
											$strDuration .= sprintf(__(' %d day/s' ,'bfi'), $currDiff->d);
										}
										if ($currDiff->h > 0 || round(($currDiff->i / 60), 2) > 0) {
											$strDuration .= (!empty($strDuration) ? ", " : "") . ($currDiff->h + round(($currDiff->i / 60), 2)) . " " . __('hours', 'bfi');
										}

									?>
										<div class="bfi-slot-list-title"><?php _e('Time Table', 'bfi')  ?>: <span><?php echo date_i18n('D',$currCheckIn->getTimestamp()) ?> <?php echo $currCheckIn->format("d") ?> <?php echo date_i18n('M',$currCheckIn->getTimestamp()).' '.$currCheckIn->format("Y") ?> <span class="bfi-time-checkin-hours"><?php echo $currCheckIn->format('H:i') ?></span></span> </div>
										<div class="bfi-timeslot ">
											<div class="bfi-row ">
												<div class="bfi-col-md-3 bfi-col-xs-3 bfi-title"><?php _e('Check-in', 'bfi') ?>
												</div>	
												<div class="bfi-col-md-9 bfi-col-xs-9 bfi-time bfi-text-right"><span class="bfi-time-checkin"><?php echo date_i18n('D',$currCheckIn->getTimestamp()) ?> <?php echo $currCheckIn->format("d") ?> <?php echo date_i18n('M',$currCheckIn->getTimestamp()).' '.$currCheckIn->format("Y") ?></span> - <span class="bfi-time-checkin-hours"><?php echo $currCheckIn->format('H:i') ?></span>
												</div>	
											</div>	
											<div class="bfi-row ">
												<div class="bfi-col-md-3 bfi-col-xs-3 bfi-title"><?php _e('Check-out', 'bfi') ?>
												</div>	
												<div class="bfi-col-md-9 bfi-col-xs-9 bfi-time bfi-text-right"><span class="bfi-time-checkout"><?php echo date_i18n('D',$currCheckOut->getTimestamp()) ?> <?php echo $currCheckOut->format("d") ?> <?php echo date_i18n('M',$currCheckOut->getTimestamp()).' '.$currCheckOut->format("Y") ?></span> - <span class="bfi-time-checkout-hours"><?php echo $currCheckOut->format('H:i') ?></span>
												</div>	
											</div>	
											<div class="bfi-row">
												<div class="bfi-col-md-3 bfi-col-xs-3 "><?php _e('Total', 'bfi') ?>:
												</div>	
												<div class="bfi-col-md-9 bfi-col-xs-9 bfi-text-right"><span class="bfi-total-duration"><?php echo $strDuration ?></span>
												</div>	
											</div>	
										</div>

								<?php
									}								

/*-------------------------------*/									
							?>
												<?php 
													$resAlltag = array();
													$resAlltagClose = true;
													if (!empty($resource->TagsIdList)) {
														$resAlltag = explode(",", $resource->TagsIdList);
													}
													if(!empty($res->ResourceGroupItemTagsIdList)){
														$resItemAlltag = explode(",", $res->ResourceGroupItemTagsIdList);
														array_merge($resAlltag, $resItemAlltag );
													}
													$resArea = (isset($resource->Area) && $resource->Area>0) ?$resource->Area:0;

													if (!empty($resource->ResServiceIdList) && $resArea>0 ) {
														$bfiresourcetagshighlighted = explode(",", $resource->ResServiceIdList);
														if (count($bfiresourcetagshighlighted) >4) {
															$resAlltag = array_diff($resAlltag ,$bfiresourcetagshighlighted);
															?>
															<div class="bfiresourcetagshighlighted" rel="<?php echo $resource->ResServiceIdList ?>" data-area="<?php echo $resArea ?>"></div>
															<?php
															$resArea = 0; // resetto l'area così non compare successivamente
															if (count($resAlltag) > 0) {
																?>
																<hr class="bfiresourcetagshighlighted-divider">
																<?php 
																
															}
														}
													}
													if (count($resAlltag) > 0) {
														$resAlltagClose = false;
													}
													if (count($resAlltag)>0 || $resArea>0 ) {
														?>
														<div class="bfiresourcetags" rel="<?php echo implode(',',$resAlltag) ?>" data-area="<?php echo $resArea ; ?>" style="<?php echo ($resAlltagClose) ?"display:none": "" ?>"></div>
														<?php 
													}					

												?>
<?php
/*-----------bed list--------------------*/
if(isset($res->BedConfig) && !empty($res->BedConfig)) { 
	$currBedsConfiguration = json_decode($res->BedConfig);
?>
		<ul class="bfi-bedroomslist">
		<?php
		foreach($currBedsConfiguration as $bedrooms) {
			?>
			<li><span class="bfi-bedroom <?php echo (count($currBedsConfiguration)>1) ?"":"bfi-hide";?>"><?php _e('Room', 'bfi') ?> <?php echo  $bedrooms->index ?>: </span>
				<?php
				$currBeds = $bedrooms->beds;
				BFCHelper::osort($currBeds, 'type');
				foreach($currBeds as $beds) {?>
					<span class="bfi-comma"><?php echo $beds->quantity ?> <?php echo ($beds->quantity>1?$bedtypes_text[$beds->type]:$bedtype_text[$beds->type])  ?> <i class="bfi-bedtypes bfi-bedtypes<?php echo $beds->type ?>"></i></span>
				<?php }
			?>
			</li>
		<?php } ?>
		</ul>
<?php } ?>										
										
                                    </td>
									<td><!-- Min/Max -->
									<?php 
									if(!empty( $res->ComputedPaxes )){
										$computedPaxes = explode("|", $res->ComputedPaxes);
										$nadult =0;
										$nsenior =0;
										$nchild =0;
										
										foreach($computedPaxes as $computedPax) {
											$currComputedPax =  explode(":", $computedPax."::::");
											
											if ($currComputedPax[3] == "0") {
												$nadult += $currComputedPax[1];
											}
											if ($currComputedPax[3] == "1") {
												$nsenior += $currComputedPax[1];
											}
											if ($currComputedPax[3] == "2") {
												$nchild += $currComputedPax[1];
											}
										}

										if (($nadult + $nsenior) >0) {
											?>
											<div class="bfi-icon-paxes">
												<i class="fa fa-user"></i> x <b><?php echo ($nadult + $nsenior) ?></b>
											<?php 
												if ( $nchild >0) {
													?>
													+ <br />
														<span class="bfi-redux"><i class="fa fa-user"></i></span> x <b><?php echo $nchild ?></b>
													<?php 
													
												}
											?>
											
											</div>
											
											<?php 
											
										}


									}else{
									?>
										
										<?php if ($res->MaxPaxes>0){?>
											<div class="bfi-icon-paxes">
												<i class="fa fa-user"></i> 
												<?php if ($res->MaxPaxes==2){?>
												<i class="fa fa-user"></i> 
												<?php }?>
												<?php if ($res->MaxPaxes>2){?>
													<?php echo ($res->MinPaxes != $res->MaxPaxes)? $res->MinPaxes . "-" : "" ?><?php echo  $res->MaxPaxes ?>
												<?php }?>
											</div>
										<?php } ?>
									<?php } ?>
									</td>
                                    <td class="text-nowrap bfi-hidden-xs"><!-- Unit price -->
                                        <?php if ($res->TotalDiscounted < $res->TotalAmount) { ?>
                                            <span class="text-nowrap bfi_strikethrough  bfi_<?php echo $currencyclass ?>"><?php echo BFCHelper::priceFormat(($res->TotalAmount - $totalAmountPricesExtraIncluded)/$res->SelectedQt); ?></span>
                                        <?php } ?>
                                        <?php if ($res->TotalDiscounted > 0) { ?>
                                            <span class="text-nowrap bfi-summary-body-resourceprice-total bfi_<?php echo $currencyclass ?>"><?php echo BFCHelper::priceFormat(($res->TotalDiscounted - $totalPricesExtraIncluded)/$res->SelectedQt); ?></span>
                                        <?php } ?>

							<?php 

							$currVat = isset($res->VATValue)?$res->VATValue:"";					
							$currTouristTaxValue = isset($res->TouristTaxValue)?$res->TouristTaxValue:0;				
							
							if ((!empty($currExcludedPriceTypeValues) && count($currExcludedPriceTypeValues)>0) || (!empty($currTouristTaxValue) && $currTouristTaxValue !="-1" ) || (!empty($currVat) && $currVat !="-1" ) ) {
							?>
							<i class="fal fa-info-circle bfi-cursor-helper bfi-info-price" aria-hidden="true" data-placement="auto"></i>
										<div class="webui-popover-content">
										   <div class="bfi-options-popover">
												<?php 
													if ( (!empty($currVat) && $currVat !="-1" )  ) {
														echo '<div class="bfi-price-incuded"><strong>'.__('Included', 'bfi') .'</strong></div>';
														if (!empty($currVat) && $currVat !="-1" ) {
															echo __('VAT', 'bfi') ." " . '<span>'. $currVat .'</span>' ;
														}
													}
																		
													if ((!empty($currExcludedPriceTypeValues) && count($currExcludedPriceTypeValues)>0) || (!empty($currTouristTaxValue) && $currTouristTaxValue !="-1" )  ) {
														echo '<div class="bfi-price-excluding"><strong>'.__('Not included', 'bfi') .'</strong></div>';
														if (!empty($currTouristTaxValue) && $currTouristTaxValue !="-1" ) {
															echo __('City tax per person per night', 'bfi') .":" . '<span class="bfi_' . $currencyclass .'" >'. BFCHelper::priceFormat($currTouristTaxValue) .'</span>' ;
														}
														if (!empty($currExcludedPriceTypeValues) && count($currExcludedPriceTypeValues)>0) {
															foreach ($currExcludedPriceTypeValues as $currExcludedPriceType=>$currExcludedPriceTypeValue ) {
															?><div class="bfi-price-excluding-list"><?php 
																switch ($currExcludedPriceType) {
																	case "extrabed":
																		?>
																		<?php echo __('Extra bed', 'bfi') ?>: <span class="bfi_<?php echo $currencyclass ?>"><?php echo BFCHelper::priceFormat($currExcludedPriceTypeValue) ?></span>
																		<?php 
																		
																		break;
																	case "cot":
																		?>
																		<?php echo __('Cot', 'bfi') ?>: <span class="bfi_<?php echo $currencyclass ?>"><?php echo BFCHelper::priceFormat($currExcludedPriceTypeValue) ?></span>
																		<?php 
																		break;
																	default:
																		?>
																		<?php $currExcludedPriceType ?>: <span class="bfi_<?php echo $currencyclass ?>"><?php echo BFCHelper::priceFormat($currExcludedPriceTypeValue) ?></span>
																		<?php 
																		break;
																}
															?></div><?php 
															}	    
														}

													}
													?>
													<?php if(!empty($currVat)) { 
														if ($currVat =="-1") {
															?>
															<div class="bfi-exempt"><?php _e('VAT', 'bfi') ?>: <?php _e('Exempt', 'bfi') ?> </div>
															<?php 
														}
													} 
													?>
													<?php if(!empty($currTouristTaxValue)) { 
														if ($currTouristTaxValue =="-1") {
															?>
															<div class="bfi-exempt"><?php _e('City tax', 'bfi') ?>: <?php _e('Exempt', 'bfi') ?></div>
															<?php 
														}
													} 
													?>
										   </div>
										</div>
									<?php } ?>


<?php 
if (!empty($currExcludedPriceTypeValues) && count($currExcludedPriceTypeValues)>0) {
    echo '<div class="bfi-price-excluding">'.__('Not included', 'bfi') .'</div>';
	foreach ($currExcludedPriceTypeValues as $currExcludedPriceType=>$currExcludedPriceTypeValue ) {
	?><div class="bfi-price-excluding-list"><?php 
		switch ($currExcludedPriceType) {
	        case "extrabed":
				?>
				<?php echo __('Extra bed', 'bfi') ?>: <span class="bfi_<?php echo $currencyclass ?>"><?php echo BFCHelper::priceFormat($currExcludedPriceTypeValue) ?></span>
				<?php 
				
				break;
	        case "cot":
				?>
				<?php echo __('Cot', 'bfi') ?>: <span class="bfi_<?php echo $currencyclass ?>"><?php echo BFCHelper::priceFormat($currExcludedPriceTypeValue) ?></span>
				<?php 
				break;
	        default:
				?>
				<?php $currExcludedPriceType ?>: <span class="bfi_<?php echo $currencyclass ?>"><?php echo BFCHelper::priceFormat($currExcludedPriceTypeValue) ?></span>
				<?php 
				break;
	    }
	?></div><?php 
	}
}
?>
                                    </td>
                                    <td class="text-nowrap">
<!-- options  -->									
					<div style="position:relative;">
					<?php 

$currPolicy = json_decode($res->PolicyValue);

$policy = $currPolicy;
$policyId= 0;
$policyHelp = "";
if(!empty( $policy )){

	$currValue = $policy->CancellationBaseValue;
	$policyId= $policy->PolicyId;
	$listPolicyIds[] = $policyId;
//	if($IsBookable){
//		$listPolicyIdsBookable[] = $policyId;
//
//	}else{
//		$listPolicyIdsNoBookable[] = $policyId;
//	}

	switch (true) {
		case strstr($policy->CancellationBaseValue ,'%'):
			$currValue = $policy->CancellationBaseValue;
			break;
		case strstr($policy->CancellationBaseValue ,'d'):
			$currValue = rtrim($policy->CancellationBaseValue,"d") .' '. __('days', 'bfi');
			break;
		case strstr($policy->CancellationBaseValue ,'n'):
			$currValue = rtrim($policy->CancellationBaseValue,"n") .' '. __('days', 'bfi');
			break;
		default:
			$currValue = '<span class="bfi_' . $currencyclass .'">'. BFCHelper::priceFormat($policy->CancellationBaseValue) .'</span>' ;
	}
	$currValuebefore = $policy->CancellationValue;
	switch (true) {
		case strstr($policy->CancellationValue ,'%'):
			$currValuebefore = $policy->CancellationValue;
			break;
		case strstr($policy->CancellationValue ,'d'):
			$currValuebefore = rtrim($policy->CancellationValue,"d") .' '. __('days', 'bfi');
			break;
		case strstr($policy->CancellationValue ,'n'):
			$currValuebefore = rtrim($policy->CancellationValue,"n") .' '. __('days', 'bfi');
			break;
		default:
			$currValuebefore = '<span class="bfi_' . $currencyclass .'">'. BFCHelper::priceFormat($policy->CancellationValue) .'</span>' ;
	}
	if($policy->CanBeCanceled){
		$currTimeBefore = "";
		$currDateBefore = "";
		$currDatePolicy =  new DateTime('UTC');
		if($cartId==0){
			$currDatePolicy = DateTime::createFromFormat('d/m/Y\TH:i:s', $res->FromDate,new DateTimeZone('UTC'));
		}else{
			$currDatePolicy = new DateTime($res->FromDate,new DateTimeZone('UTC'));
		}										
		if(!empty( $policy->CancellationTime )){		
			switch (true) {
				case strstr($policy->CancellationTime ,'d'):
					$currDatePolicy->modify('-'. rtrim($policy->CancellationTime,"d") .' days'); 
					break;
				case strstr($policy->CancellationTime ,'h'):
					$currDatePolicy->modify('-'. rtrim($policy->CancellationTime,"h") .' hours'); 
					break;
				case strstr($policy->CancellationTime ,'w'):
					$currDatePolicy->modify('-'. rtrim($policy->CancellationTime,"w") .' weeks'); 
					break;
				case strstr($policy->CancellationTime ,'m'):
					$currDatePolicy->modify('-'. rtrim($policy->CancellationTime,"m") .' months'); 
					break;
			}
		}
		if($currDatePolicy > $dateTimeNow){
				if(!empty( $policy->CancellationTime )){					
					switch (true) {
						case strstr($policy->CancellationTime ,'d'):
							$currTimeBefore = rtrim($policy->CancellationTime,"d") .' '. __('days', 'bfi');
							break;
						case strstr($policy->CancellationTime ,'h'):
							$currTimeBefore = rtrim($policy->CancellationTime,"h") .' '. __('hours', 'bfi');	
							break;
						case strstr($policy->CancellationTime ,'w'):
							$currTimeBefore = rtrim($policy->CancellationTime,"w") .' '. __('weeks', 'bfi');	
							break;
						case strstr($policy->CancellationTime ,'m'):
							$currTimeBefore = rtrim($policy->CancellationTime,"m") .' '. __('months', 'bfi');
							break;
					}
				}

				if($policy->CancellationValue=="0" || $policy->CancellationValue=="0%"){
					?>
					<div class="bfi-policy-green"><?php _e('Cancellation FREE', 'bfi') ?>
					<?php 
					if(!empty( $policy->CancellationTime )){
						echo '<br />'.__('until', 'bfi') ;
						echo ' '.$currDatePolicy->format("d").' '.date_i18n('M',$currDatePolicy->getTimestamp()).' '.$currDatePolicy->format("Y");
						$policyHelp = sprintf(__('You may cancel free of charge until %1$s before arrival. You will be charged %2$s if you cancel in the %1$s before arrival.', 'bfi'),$currTimeBefore,$currValue);
					}
					?>
					</div>
					<?php 

					
				}else{
				if($policy->CancellationBaseValue=="0%" || $policy->CancellationBaseValue=="0"){
					?>
					<div class="bfi-policy-green"><?php _e('Cancellation FREE', 'bfi') ?></div>
					<?php 
					$policyHelp = __('You may cancel free of charge until arrival.', 'bfi');
				}else{
					?>
					<div class="bfi-policy-blue"><?php _e('Special conditions', 'bfi') ?></div>
					<?php 
					$policyHelp = sprintf(__('You may cancel with a charge of %3$s  until %1$s before arrival. You will be charged %2$s if you cancel in the %1$s before arrival.', 'bfi'),$currTimeBefore,$currValue,$currValuebefore);
				}
				}

			
		}else{
				if($policy->CancellationBaseValue=="0%" || $policy->CancellationBaseValue=="0"){
					?>
					<div class="bfi-policy-green"><?php _e('Cancellation FREE', 'bfi') ?></div>
					<?php 
					$policyHelp = __('You may cancel free of charge until arrival.', 'bfi');
				}else{
					?>
					<div class="bfi-policy-blue"><?php _e('Special conditions', 'bfi') ?></div>
					<?php 
					$policyHelp = sprintf(__('You will be charged %1$s if you cancel before arrival.', 'bfi'),$currValue);
				}
		}
				
	}else{ 
		// no refundable
		?>
			<div class="bfi-policy-none"><?php _e('Non refundable', 'bfi') ?></div>
		<?php 
		$policyHelp = sprintf(__('You will be charged all if you cancel before arrival.', 'bfi'));
	
	}
}
if(!empty($policyHelp)){
	$allPolicyHelp[] = $resource->Name . ": " . $policyHelp;
}
$allPolicies[] = array(
	'merchantid' =>$MerchantDetail->MerchantId,
	'merchantname' =>$MerchantDetail->Name,
	'resourcename' =>$resource->Name ,
	'policyHelp' =>$policyHelp,
	'policyid' =>$policyId,
	);
//$currMerchantBookingTypes = array();
$prepayment = "";
$prepaymentHelp = "";
//
//if(!empty( $policy->MerchantBookingTypesString )){
//	$currMerchantBookingTypes = json_decode($policy->MerchantBookingTypesString);
//	$currBookingTypeId = $currRateplan->RatePlan->MerchantBookingTypeId;
//	$currMerchantBookingType = array_filter($currMerchantBookingTypes, function($bt) use($currBookingTypeId) {return $bt->BookingTypeId == $currBookingTypeId;});
//	if(count($currMerchantBookingType)>0){
//		if($currMerchantBookingType[0]->PayOnArrival){
//			$prepayment = __("Pay at the property – NO PREPAYMENT NEEDED", 'bfi');
//			$prepaymentHelp = __("No prepayment is needed.", 'bfi');
//		}
//		if($currMerchantBookingType[0]->AcquireCreditCardData){
//			$prepayment = "";
//			if($currMerchantBookingType[0]->DepositRelativeValue=="100%"){
//				$prepaymentHelp = __('You will be charged a prepayment of the total price at any time.', 'bfi');
//			}else if(strpos($currMerchantBookingType[0]->DepositRelativeValue, '%') !== false  ) {
//				$prepaymentHelp = sprintf(__('You will be charged a prepayment of %1$s of the total price at any time.', 'bfi'),$currMerchantBookingType[0]->DepositRelativeValue);
//			}else{
//				$prepaymentHelp = sprintf(__('You will be charged a prepayment of %1$s at any time.', 'bfi'),$currMerchantBookingType[0]->DepositRelativeValue);
//			}
//		}
//	}
//}
$allMeals = array();
$cssclassMeals = "bfi-meals-base";
$mealsHelp = "";
$res->ItemTypeId = 1;
if($res->ItemTypeId==0 && $res->IncludedMeals >-1){
	$mealsHelp = __("There is no meal option with this room.", 'bfi');
	if ($res->IncludedMeals & bfi_Meal::Breakfast){
		$allMeals[]= __("Breakfast", 'bfi');
	}
	if ($res->IncludedMeals & bfi_Meal::Lunch){
		$allMeals[]= __("Lunch", 'bfi');
	}
	if ($res->IncludedMeals & bfi_Meal::Dinner){
		$allMeals[]= __("Dinner", 'bfi');
	}
	if ($res->IncludedMeals & bfi_Meal::AllInclusive){
		$allMeals[]= __("All Inclusive", 'bfi');
	}
	if(in_array(__("Breakfast", 'bfi'), $allMeals)){
		$cssclassMeals = "bfi-meals-bb";
	}
	if(in_array(__("Lunch", 'bfi'), $allMeals) || in_array(__("Dinner", 'bfi'), $allMeals) || in_array(__("All Inclusive", 'bfi'), $allMeals)  ){
		$cssclassMeals = "bfi-meals-fb";
	}
	if(count($allMeals)>0){
		$mealsHelp = implode(", ",$allMeals). " " . __('included', 'bfi');
	}
	if(count($allMeals)==2){
		$mealsHelp = implode(" & ",$allMeals). " " . __('included', 'bfi');
	}
}
?>
<?php if(!empty($prepayment)) { ?>
						<div class="bfi-prepayment"><?php echo $prepayment ?></div>
<?php } ?>

						<div class="bfi-meals <?php echo $cssclassMeals?>"><?php echo $mealsHelp ?></div>
						<div class="bfi-options-help">
							<i class="fa fa-question-circle bfi-cursor-helper" aria-hidden="true"></i>
							<div class="webui-popover-content">
							   <div class="bfi-options-popover">
							   <?php if(!empty($mealsHelp)) { ?>
								   <p><b><?php _e('Meals', 'bfi') ?>:</b> <?php echo $mealsHelp; ?></p>
							   <?php } ?>
							   <p><b><?php _e('Cancellation', 'bfi') ?>:</b> <?php echo $policyHelp; ?></p>
							   <?php if(!empty($prepaymentHelp)) { ?>
								   <p><b><?php _e('Prepayment', 'bfi') ?>:</b> <?php echo $prepaymentHelp; ?></p>
							   <?php } ?>
							   </div>
							</div>
						</div>
						<?php if(!$IsBookable) { ?>
							<div class="bfi-bookingenquiry"><?php _e('Non-binding booking request', 'bfi') ?></div>
						<?php }else{ ?>
							<div class="bfi-bookingreservationy"><?php _e('Instant Book', 'bfi') ?></div>
						<?php } ?>
						<?php if(!empty( $policy->OtherInfo)){ echo $policy->OtherInfo; }?>
					</div>

<!-- end options  -->									

									</td>
                                    <td class="bfi-text-right">
										<span class="bfi-hidden-sm bfi-hidden-md bfi-hidden-lg"><?php _e('Qt.', 'bfi') ?></span> <?php echo $res->SelectedQt ?>
									</td>
                                    <td class="text-nowrap bfi-text-right">
                                        <?php if ($res->TotalDiscounted < $res->TotalAmount) { ?>
                                            <span class="text-nowrap bfi_strikethrough  bfi_<?php echo $currencyclass ?>"><?php echo BFCHelper::priceFormat(($res->TotalAmount - $totalAmountPricesExtraIncluded)); ?></span>
                                        <?php } ?>
                                        <?php if ($res->TotalDiscounted > 0) { ?>
                                            <span class="text-nowrap bfi-summary-body-resourceprice-total bfi_<?php echo $currencyclass ?>"><?php echo BFCHelper::priceFormat(($res->TotalDiscounted - $totalPricesExtraIncluded)); ?></span>

                                        <?php } ?>
											<form action="<?php echo $formRouteDelete ?>" method="POST" style="display: inline-block;" class="bfi-cartform-delete">
											<input type="hidden" name="bfi_CartOrderId"  value="<?php echo $res->CartOrderId ?>" />
											<input type="hidden" name="bfi_cartId"  value="<?php echo $cartId ?>" />
											<input type="hidden" name="bficurrRes"  value="<?php echo htmlspecialchars(json_encode($res), ENT_COMPAT, 'UTF-8')?>" />
											<button class="bfi-btn-delete" data-title="Delete" type="submit" name="remove_order" value="delete">x</button></form>
									</td>
                                </tr>


								<?php if(!empty($res->PricesExtraIncluded)) { 
									foreach($pricesExtraIncluded as $sdetail) {
										$sdetailName = $sdetail->Name;
										$sdetailName = substr($sdetailName, 0, strrpos($sdetailName, ' - '));
										$sdetailName = str_replace("$$", "'", $sdetailName);
								 ?>	
                                        <tr class="bfi-cart-extra">
                                            <td>
													<div class="bfi-item-title">
														<?php echo  $sdetailName?>
													</div>
													<?php 
													if (($sdetail->AvailabilityType == 0 || $sdetail->AvailabilityType == 1) && isset($sdetail->FromDate)&& isset($sdetail->ToDate) && ($sdetail->FromDate != $res->FromDate || $sdetail->ToDate != $res->ToDate)) {
														$currCheckIn = DateTime::createFromFormat("YmdHis", !empty($sdetail->FromDate) ? $sdetail->FromDate : $res->FromDate,new DateTimeZone('UTC'));
														$currCheckOut = DateTime::createFromFormat("YmdHis", !empty($sdetail->ToDate) ? $sdetail->ToDate : $res->ToDate,new DateTimeZone('UTC'));
														$currDiff = $currCheckOut->diff($currCheckIn);
														
														$strDuration = "";
														if ($sdetail->AvailabilityType == 0) {
															$strDuration .= sprintf(__(' %d day/s' ,'bfi'), $currDiff->d + 1);
														} else{
															$strDuration .= sprintf(__(' %d night/s' ,'bfi'), $currDiff->d);
														}
														
													?>
														<div class="bfi-timeperiod " >
															<div class="bfi-row ">
																<div class="bfi-col-md-3 bfi-col-xs-3 bfi-title"><?php _e('Check-in', 'bfi') ?>
																</div>	
																<div class="bfi-col-md-9 bfi-col-xs-9 bfi-time bfi-text-right"><span class="bfi-time-checkin"><?php echo $currCheckIn->format("D") ?> <?php echo $currCheckIn->format("d") ?> <?php echo $currCheckIn->format("M").' '.$currCheckIn->format("Y") ?></span> - <span class="bfi-time-checkin-hours"><?php echo $currCheckIn->format('H:i') ?></span>
																</div>	
															</div>	
															<div class="bfi-row ">
																<div class="bfi-col-md-3 bfi-col-xs-3 bfi-title"><?php _e('Check-out', 'bfi') ?>
																</div>	
																<div class="bfi-col-md-9 bfi-col-xs-9 bfi-time bfi-text-right"><span class="bfi-time-checkout"><?php echo $currCheckOut->format("D") ?>  <?php echo $currCheckOut->format("d") ?> <?php echo $currCheckOut->format("M").' '.$currCheckOut->format("Y") ?></span> - <span class="bfi-time-checkout-hours"><?php echo $currCheckOut->format('H:i') ?></span>
																</div>	
															</div>	
															<div class="bfi-row">
																<div class="bfi-col-md-3 bfi-col-xs-3 "><?php _e('Total', 'bfi') ?>:
																</div>	
																<div class="bfi-col-md-9 bfi-col-xs-9 bfi-text-right">
																	<span class="bfi-total-duration"><?php echo $strDuration ?></span>
																</div>	
															</div>	
														</div>
													<?php 
													
													}
													if (!empty($sdetail->CheckInTime) && !empty($sdetail->TimeDuration) && $sdetail->TimeDuration>0)
													{
														$currCheckIn = DateTime::createFromFormat("YmdHis", $sdetail->CheckInTime,new DateTimeZone('UTC'));
														$currCheckOut = DateTime::createFromFormat("YmdHis", $sdetail->CheckInTime,new DateTimeZone('UTC'));
														$currCheckOut->add(new DateInterval('PT' . $sdetail->TimeDuration . 'M'));
														$currDiff = $currCheckOut->diff($currCheckIn);
														$timeDuration = $currDiff->h + ($currDiff->i/60);;
//														$startHour = DateTime::createFromFormat("YmdHis", $sdetail->CheckInTime);
//														$endHour = DateTime::createFromFormat("YmdHis", $sdetail->CheckInTime);
//														$endHour->add(new DateInterval('PT' . $sdetail->TimeDuration . 'M'));

														$strDuration = "";
														if ($currDiff->d >= 1) {
															$strDuration .= sprintf(__(' %d day/s' ,'bfi'), $currDiff->d);
														}
														if ($currDiff->h > 0 || round(($currDiff->i / 60), 2) > 0) {
															$strDuration .= (!empty($strDuration) ? ", " : "") . ($currDiff->h . ":" . round(($currDiff->i / 60), 2)) . " " . __('hours', 'bfi');
														}

													?>
														<div class="bfi-timeperiod " >
															<div class="bfi-row ">
																<div class="bfi-col-md-3 bfi-col-xs-3 bfi-title"><?php _e('Check-in', 'bfi') ?>
																</div>	
																<div class="bfi-col-md-9 bfi-col-xs-9 bfi-time bfi-text-right"><span class="bfi-time-checkin"><?php echo date_i18n('D',$currCheckIn->getTimestamp()) ?> <?php echo $currCheckIn->format("d") ?> <?php echo date_i18n('M',$currCheckIn->getTimestamp()).' '.$currCheckIn->format("Y") ?></span> - <span class="bfi-time-checkin-hours"><?php echo $currCheckIn->format('H:i') ?></span>
																</div>	
															</div>	
															<div class="bfi-row ">
																<div class="bfi-col-md-3 bfi-col-xs-3 bfi-title"><?php _e('Check-out', 'bfi') ?>
																</div>	
																<div class="bfi-col-md-9 bfi-col-xs-9 bfi-time bfi-text-right"><span class="bfi-time-checkout"><?php echo date_i18n('D',$currCheckOut->getTimestamp()) ?> <?php echo $currCheckOut->format("d") ?> <?php echo date_i18n('M',$currCheckOut->getTimestamp()).' '.$currCheckOut->format("Y") ?></span> - <span class="bfi-time-checkout-hours"><?php echo $currCheckOut->format('H:i') ?></span>
																</div>	
															</div>	
															<div class="bfi-row">
																<div class="bfi-col-md-3 bfi-col-xs-3 "><?php _e('Total', 'bfi') ?>:
																</div>	
																<div class="bfi-col-md-9 bfi-col-xs-9 bfi-text-right">
																	<span class="bfi-total-duration"><?php echo $strDuration ?></span>
																</div>	
															</div>	
														</div>
													<?php 
													}
													if (isset($sdetail->TimeSlotId) && $sdetail->TimeSlotId > 0)
													{									
														$currCheckIn = new DateTime('UTC'); 
														if($cartId==0){
															$currCheckIn = DateTime::createFromFormat('d/m/Y', $sdetail->TimeSlotDate,new DateTimeZone('UTC'));
														}else{
															$currCheckIn = new DateTime($sdetail->TimeSlotDate,new DateTimeZone('UTC')); 
														}
														$currCheckOut = clone $currCheckIn;
														$currCheckIn->setTime(0,0,1);
														$currCheckOut->setTime(0,0,1);
														$currCheckIn->add(new DateInterval('PT' . $sdetail->TimeSlotStart . 'M'));
														$currCheckOut->add(new DateInterval('PT' . $sdetail->TimeSlotEnd . 'M'));

														$currDiff = $currCheckOut->diff($currCheckIn);

//														$TimeSlotDate = new DateTime($sdetail->TimeSlotDate); 
//														$startHour = new DateTime("2000-01-01 0:0:00.1"); 
//														$endHour = new DateTime("2000-01-01 0:0:00.1"); 
//														$startHour->add(new DateInterval('PT' . $sdetail->TimeSlotStart . 'M'));
//														$endHour->add(new DateInterval('PT' . $sdetail->TimeSlotEnd . 'M'));
													?>
														<div class="bfi-timeslot ">
															<div class="bfi-row ">
																<div class="bfi-col-md-3 bfi-col-xs-3 bfi-title"><?php _e('Check-in', 'bfi') ?>
																</div>	
																<div class="bfi-col-md-9 bfi-col-xs-9 bfi-time bfi-text-right"><span class="bfi-time-checkin"><?php echo date_i18n('D',$currCheckIn->getTimestamp()) ?> <?php echo $currCheckIn->format("d") ?> <?php echo date_i18n('M',$currCheckIn->getTimestamp()).' '.$currCheckIn->format("Y") ?></span> - <span class="bfi-time-checkin-hours"><?php echo $currCheckIn->format('H:i') ?></span>
																</div>	
															</div>	
															<div class="bfi-row ">
																<div class="bfi-col-md-3 bfi-col-xs-3 bfi-title"><?php _e('Check-out', 'bfi') ?>
																</div>	
																<div class="bfi-col-md-9 bfi-col-xs-9 bfi-time bfi-text-right"><span class="bfi-time-checkout"><?php echo date_i18n('D',$currCheckOut->getTimestamp()) ?> <?php echo $currCheckOut->format("d") ?> <?php echo date_i18n('M',$currCheckOut->getTimestamp()).' '.$currCheckOut->format("Y") ?></span> - <span class="bfi-time-checkout-hours"><?php echo $currCheckOut->format('H:i') ?></span>
																</div>	
															</div>	
															<div class="bfi-row">
																<div class="bfi-col-md-3 bfi-col-xs-3 "><?php _e('Total', 'bfi') ?>:
																</div>	
																<div class="bfi-col-md-9 bfi-col-xs-9 bfi-text-right"><span class="bfi-total-duration"><?php echo $currDiff->format('%h') ?></span> <?php _e('hours', 'bfi') ?>
																</div>	
															</div>	
														</div>
													<?php 
													}

													?>
                                            </td>
                                            <td class="bfi-hidden-xs"><!-- paxes --></td>
                                            <td class="text-nowrap bfi-text-right bfi-hidden-xs"><!-- Unit price -->
                                                <?php if($sdetail->TotalDiscounted < $sdetail->TotalAmount){ ?>
                                                    <span class="text-nowrap bfi_strikethrough  bfi_<?php echo $currencyclass ?>"><?php echo BFCHelper::priceFormat($sdetail->TotalAmount/$sdetail->CalculatedQt);?></span>
                                                <?php } ?>
                                                <?php if($sdetail->TotalDiscounted > 0){ ?>
                                                    <span class="text-nowrap bfi-summary-body-resourceprice-total bfi_<?php echo $currencyclass ?>"><?php echo BFCHelper::priceFormat($sdetail->TotalDiscounted/$sdetail->CalculatedQt );?></span>
                                                <?php } ?>
                                            </td>
                                            <td class="text-nowrap bfi-hidden-xs"> </td>
                                            <td class="bfi-text-right">
												<span class="bfi-hidden-sm bfi-hidden-md bfi-hidden-lg"><?php _e('Qt.', 'bfi') ?></span> <?php echo $sdetail->CalculatedQt ?>
											</td>
                                            <td class="text-nowrap bfi-text-right">
                                                <?php if($sdetail->TotalDiscounted < $sdetail->TotalAmount){ ?>
                                                    <span class="text-nowrap bfi_strikethrough  bfi_<?php echo $currencyclass ?>"><?php echo BFCHelper::priceFormat($sdetail->TotalAmount);?></span>
                                                <?php } ?>
                                                <?php if($sdetail->TotalDiscounted > 0){ ?>
                                                    <span class="text-nowrap bfi-summary-body-resourceprice-total bfi_<?php echo $currencyclass ?>"><?php echo BFCHelper::priceFormat($sdetail->TotalDiscounted );?></span>
                                                <?php } ?>
                                            </td>
                                        </tr>
                            <?php 
                                    } // foreach $svc
                                } // if res->ExtraServices
								 ?>	
								<?php if(!empty($res->ExtraServices)) { 
									foreach($res->ExtraServices as $sdetail) {					
								 ?>	
                                        <tr class="bfi-cart-extra">
                                            <td>
													<div class="bfi-item-title">
														<?php echo  $sdetail->Name ?>
													</div>
													<?php
													
													
													if (($sdetail->AvailabilityType == 0 || $sdetail->AvailabilityType == 1) && isset($sdetail->FromDate ) && isset($sdetail->ToDate) && (substr($sdetail->FromDate, 0, 10) != substr($res->FromDate, 0, 10) || substr($sdetail->ToDate, 0, 10) != substr($res->ToDate, 0, 10))) {
														$currCheckIn = DateTime::createFromFormat("Y-m-d\TH:i:s", !empty($sdetail->FromDate) ? $sdetail->FromDate : $res->FromDate,new DateTimeZone('UTC'));
														$currCheckOut = DateTime::createFromFormat("Y-m-d\TH:i:s", !empty($sdetail->ToDate) ? $sdetail->ToDate : $res->ToDate,new DateTimeZone('UTC'));
														$currDiff = $currCheckOut->diff($currCheckIn);
														
														$strDuration = "";
														if ($sdetail->AvailabilityType == 0) {
															$strDuration .= sprintf(__(' %d day/s' ,'bfi'), $currDiff->d + 1);
														} else{
															$strDuration .= sprintf(__(' %d night/s' ,'bfi'), $currDiff->d);
														}
														
													?>
														<div class="bfi-timeperiod " >
															<div class="bfi-row ">
																<div class="bfi-col-md-3 bfi-col-xs-3 bfi-title"><?php _e('Check-in', 'bfi') ?>
																</div>	
																<div class="bfi-col-md-9 bfi-col-xs-9 bfi-time bfi-text-right"><span class="bfi-time-checkin"><?php echo $currCheckIn->format("D") ?> <?php echo $currCheckIn->format("d") ?> <?php echo $currCheckIn->format("M").' '.$currCheckIn->format("Y") ?></span> - <span class="bfi-time-checkin-hours"><?php echo $currCheckIn->format('H:i') ?></span>
																</div>	
															</div>	
															<div class="bfi-row ">
																<div class="bfi-col-md-3 bfi-col-xs-3 bfi-title"><?php _e('Check-out', 'bfi') ?>
																</div>	
																<div class="bfi-col-md-9 bfi-col-xs-9 bfi-time bfi-text-right"><span class="bfi-time-checkout"><?php echo $currCheckOut->format("D") ?>  <?php echo $currCheckOut->format("d") ?> <?php echo $currCheckOut->format("M").' '.$currCheckOut->format("Y") ?></span> - <span class="bfi-time-checkout-hours"><?php echo $currCheckOut->format('H:i') ?></span>
																</div>	
															</div>	
															<div class="bfi-row">
																<div class="bfi-col-md-3 bfi-col-xs-3 "><?php _e('Total', 'bfi') ?>:
																</div>	
																<div class="bfi-col-md-9 bfi-col-xs-9 bfi-text-right">
																	<span class="bfi-total-duration"><?php echo $strDuration ?></span>
																</div>	
															</div>	
														</div>
													<?php 
													
													}
													if (!empty($sdetail->CheckInTime) && !empty($sdetail->TimeDuration) && $sdetail->TimeDuration>0)
													{
														$currCheckIn = DateTime::createFromFormat("YmdHis", $sdetail->CheckInTime,new DateTimeZone('UTC'));
														$currCheckOut = DateTime::createFromFormat("YmdHis", $sdetail->CheckInTime,new DateTimeZone('UTC'));
														$currCheckOut->add(new DateInterval('PT' . $sdetail->TimeDuration . 'M'));
														$currDiff = $currCheckOut->diff($currCheckIn);
														$timeDuration = $currDiff->h + ($currDiff->i/60);
//														$startHour = DateTime::createFromFormat("YmdHis", $sdetail->CheckInTime);
//														$endHour = DateTime::createFromFormat("YmdHis", $sdetail->CheckInTime);
//														$endHour->add(new DateInterval('PT' . $sdetail->TimeDuration . 'M'));
													?>
														<div class="bfi-timeperiod " >
															<div class="bfi-row ">
																<div class="bfi-col-md-3 bfi-col-xs-3 bfi-title"><?php _e('Check-in', 'bfi') ?>
																</div>	
																<div class="bfi-col-md-9 bfi-col-xs-9 bfi-time bfi-text-right"><span class="bfi-time-checkin"><?php echo date_i18n('D',$currCheckIn->getTimestamp()) ?> <?php echo $currCheckIn->format("d") ?> <?php echo date_i18n('M',$currCheckIn->getTimestamp()).' '.$currCheckIn->format("Y") ?></span> - <span class="bfi-time-checkin-hours"><?php echo $currCheckIn->format('H:i') ?></span>
																</div>	
															</div>	
															<div class="bfi-row ">
																<div class="bfi-col-md-3 bfi-col-xs-3 bfi-title"><?php _e('Check-out', 'bfi') ?>
																</div>	
																<div class="bfi-col-md-9 bfi-col-xs-9 bfi-time bfi-text-right"><span class="bfi-time-checkout"><?php echo date_i18n('D',$currCheckOut->getTimestamp()) ?> <?php echo $currCheckOut->format("d") ?> <?php echo date_i18n('M',$currCheckOut->getTimestamp()).' '.$currCheckOut->format("Y") ?></span> - <span class="bfi-time-checkout-hours"><?php echo $currCheckOut->format('H:i') ?></span>
																</div>	
															</div>	
															<div class="bfi-row">
																<div class="bfi-col-md-3 bfi-col-xs-3 "><?php _e('Total', 'bfi') ?>:
																</div>	
																<div class="bfi-col-md-9 bfi-col-xs-9 bfi-text-right"><span class="bfi-total-duration"><?php echo  $timeDuration ?></span> <?php _e('hours', 'bfi') ?>
																</div>	
															</div>	
														</div>
													<?php 
													}
													if (isset($sdetail->TimeSlotId) && $sdetail->TimeSlotId > 0)
													{									
														$currCheckIn = new DateTime('UTC'); 
														if($cartId==0){
															$currCheckIn = DateTime::createFromFormat('d/m/Y', $sdetail->TimeSlotDate,new DateTimeZone('UTC'));
														}else{
															$currCheckIn = new DateTime($sdetail->TimeSlotDate,new DateTimeZone('UTC')); 
														}
														$currCheckOut = clone $currCheckIn;
														$currCheckIn->setTime(0,0,1);
														$currCheckOut->setTime(0,0,1);
														$currCheckIn->add(new DateInterval('PT' . $sdetail->TimeSlotStart . 'M'));
														$currCheckOut->add(new DateInterval('PT' . $sdetail->TimeSlotEnd . 'M'));

														$currDiff = $currCheckOut->diff($currCheckIn);

//														$TimeSlotDate = new DateTime($sdetail->TimeSlotDate); 
//														$startHour = new DateTime("2000-01-01 0:0:00.1"); 
//														$endHour = new DateTime("2000-01-01 0:0:00.1"); 
//														$startHour->add(new DateInterval('PT' . $sdetail->TimeSlotStart . 'M'));
//														$endHour->add(new DateInterval('PT' . $sdetail->TimeSlotEnd . 'M'));
													?>
														<div class="bfi-timeslot ">
															<div class="bfi-row ">
																<div class="bfi-col-md-3 bfi-col-xs-3 bfi-title"><?php _e('Check-in', 'bfi') ?>
																</div>	
																<div class="bfi-col-md-9 bfi-col-xs-9 bfi-time bfi-text-right"><span class="bfi-time-checkin"><?php echo date_i18n('D',$currCheckIn->getTimestamp()) ?> <?php echo $currCheckIn->format("d") ?> <?php echo date_i18n('M',$currCheckIn->getTimestamp()).' '.$currCheckIn->format("Y") ?></span> - <span class="bfi-time-checkin-hours"><?php echo $currCheckIn->format('H:i') ?></span>
																</div>	
															</div>	
															<div class="bfi-row ">
																<div class="bfi-col-md-3 bfi-col-xs-3 bfi-title"><?php _e('Check-out', 'bfi') ?>
																</div>	
																<div class="bfi-col-md-9 bfi-col-xs-9 bfi-time bfi-text-right"><span class="bfi-time-checkout"><?php echo date_i18n('D',$currCheckOut->getTimestamp()) ?> <?php echo $currCheckOut->format("d") ?> <?php echo date_i18n('M',$currCheckOut->getTimestamp()).' '.$currCheckOut->format("Y") ?></span> - <span class="bfi-time-checkout-hours"><?php echo $currCheckOut->format('H:i') ?></span>
																</div>	
															</div>	
															<div class="bfi-row">
																<div class="bfi-col-md-3 bfi-col-xs-3 "><?php _e('Total', 'bfi') ?>:
																</div>	
																<div class="bfi-col-md-9 bfi-col-xs-9 bfi-text-right"><span class="bfi-total-duration"><?php echo $currDiff->format('%h') ?></span> <?php _e('hours', 'bfi') ?>
																</div>	
															</div>	
														</div>
													<?php 
													}

													?>
                                            </td>
                                            <td class="bfi-hidden-xs"><!-- paxes --></td>
                                            <td class="text-nowrap bfi-text-right bfi-hidden-xs"><!-- Unit price -->
                                                <?php if($sdetail->TotalDiscounted < $sdetail->TotalAmount){ ?>
                                                    <span class="text-nowrap bfi_strikethrough  bfi_<?php echo $currencyclass ?>"><?php echo BFCHelper::priceFormat($sdetail->TotalAmount/$sdetail->CalculatedQt);?></span>
                                                <?php } ?>
                                                <?php if($sdetail->TotalDiscounted > 0){ ?>
                                                    <span class="text-nowrap bfi-summary-body-resourceprice-total bfi_<?php echo $currencyclass ?>"><?php echo BFCHelper::priceFormat($sdetail->TotalDiscounted/$sdetail->CalculatedQt );?></span>
                                                <?php } ?>
                                            </td>
                                            <td class="text-nowrap bfi-hidden-xs"> </td>
                                            <td class="bfi-text-right">
												<span class="bfi-hidden-sm bfi-hidden-md bfi-hidden-lg"><?php _e('Qt.', 'bfi') ?></span> <?php echo $sdetail->CalculatedQt ?>
											</td>
                                            <td class="text-nowrap bfi-text-right">
                                                <?php if($sdetail->TotalDiscounted < $sdetail->TotalAmount){ ?>
                                                    <span class="text-nowrap bfi_strikethrough  bfi_<?php echo $currencyclass ?>"><?php echo BFCHelper::priceFormat($sdetail->TotalAmount);?></span>
                                                <?php } ?>
                                                <?php if($sdetail->TotalDiscounted > 0){ ?>
                                                    <span class="text-nowrap bfi-summary-body-resourceprice-total bfi_<?php echo $currencyclass ?>"><?php echo BFCHelper::priceFormat($sdetail->TotalDiscounted );?></span>
                                                <?php } ?>
                                            </td>
                                        </tr>
                            <?php 
								$totalWithVariation +=$sdetail->TotalDiscounted ;
								
								if(!$IsBookable) {
									$totalRequestedWithVariation +=$sdetail->TotalDiscounted ;
								}

                                    } // foreach $svc
                                } // if res->ExtraServices
							$totalWithVariation +=$res->TotalDiscounted ;

							if(!$IsBookable) {
								$totalRequestedWithVariation +=$res->TotalDiscounted ;
							}
							if ($IsBookable && !empty($res->DiscountCodes)) {
							    foreach($res->DiscountCodes as $discountCode){
									$totalRequestedWithVariation +=$discountCode->Value ;
								} 
							}


$currStayConfiguration = array("productid"=>$res->ResourceId,"price"=>$res->TotalAmount,"start"=>$currCheckIn->format("Y-m-d H:i:s"),"end"=> $currCheckOut->format("Y-m-d H:i:s"));

$listStayConfigurations[] = $currStayConfiguration;
                            }
                            ?>
<?php 

						
//		} // foreach $itm


	} // foreach $listMerchantsCart

//$totalRequest = BFCHelper::priceFormat($totalWithVariation);
$totalRequest = $totalWithVariation - $totalRequestedWithVariation;

?>
		</table>
	</div>	

<?php

$allCoupon = null;
$allDiscountCodes = array();
$countCoupon = 0;
if($enablecoupon && isset($cartConfig->DiscountCodes) && count($cartConfig->DiscountCodes)>0) {
	$allCoupon = $cartConfig->DiscountCodes;
	$countCoupon=count($allCoupon);
	foreach ($allCoupon as $singeCoupon) {
		$totalCouponDiscount += $singeCoupon->Value;
		$allDiscountCodes[] =  $singeCoupon->Code;
	}
} 
if($enablecoupon){
	// controllo coupon passato se valido
	$cpn = BFCHelper::getVar("cpn");
	if (!empty($cpn) && (!in_array($cpn, $allDiscountCodes) || count($allDiscountCodes)==0)) {
	    ?>
			<div class="bfi-errorcoupon bfi-alert bfi-alert-danger">
			 <?php echo sprintf(__('The code is not valid', 'bfi'),$cpn) ?>
			</div>
	    <?php 
	}
$textbtnCoupon = __('Apply', 'bfi');
if($countCoupon>0){
	$textbtnCoupon = '<i class="fa fa-plus-circle" aria-hidden="true"></i> ' . __('Add', 'bfi');	
}

?>
	<div class="bfi-content bfi-border bfi-cart-coupon"><a name="bficoupon"></a>
	<form method="post" action="<?php echo $formRouteaddDiscountCodes ?>" class="form-validate bfi-nomargin"  >
		<input type="hidden" name="bfilanguage"  value="<?php echo $language ?>" />
		<span class="bfi-simple-label"><?php _e('Gift cards or Promotional codes', 'bfi') ?></span>	
		<input type="text" name="bficoupons"  id="bficoupons" size="25" style="width:auto;display:inline-block;" placeholder="<?php _e('Enter code', 'bfi') ?>"  aria-required="true" required title="<?php _e('Mandatory', 'bfi') ?>"/>
		<button class="bfi-btn bfi-alternative" data-title="Add" type="submit" name="addcoupon" value="" id="bfiaddcoupon"><?php echo $textbtnCoupon ?></button>
	</form>
	</div>	
<?php
} 
?>
		
	
	<div class="bfi-content bfi-border bfi-text-right bfi-pad0-10">
	<?php 

	if($enablecoupon && isset($cartConfig->DiscountCodes) && count($cartConfig->DiscountCodes)>0) {
		foreach ($allCoupon as $singeCoupon) { 
	?>
	<span class="bfi-coupon-details"><?php _e('Discount', 'bfi') ?> <?php echo $singeCoupon->Name ?> - <span class="text-nowrap bfi_<?php echo $currencyclass ?>"> <?php echo BFCHelper::priceFormat($singeCoupon->Value);?></span></span>
		<div class="webui-popover-content">
			<div class="bfi-options-popover">
				<strong><?php echo $singeCoupon->MerchantName ?></strong><br />
				<?php echo BFCHelper::getLanguage($singeCoupon->Description , $language, null, array( 'striptags'=>'striptags', 'bbcode'=>'bbcode','ln2br'=>'ln2br')) ; ?>
			</div>
		</div><br />
	<?php 
		}
	}
	?>
		<?php if ($totalWithVariation>0) { ?>	
			<span class="text-nowrap bfi-summary-body-resourceprice-total"><?php _e('Total', 'bfi') ?></span>	
			<?php if ($totalCouponDiscount>0) { ?>
				<span class="text-nowrap bfi_strikethrough  bfi_<?php echo $currencyclass ?>"><?php echo BFCHelper::priceFormat(($totalWithVariation)); ?></span>
			<?php } ?>

			<span class="text-nowrap bfi-summary-body-resourceprice-total bfi_<?php echo $currencyclass ?>"> <?php echo BFCHelper::priceFormat($totalWithVariation - $totalCouponDiscount);?></span>	
		<?php } ?>
			<div id="totaldepositrequested" style="display:none;">
				<span class="text-nowrap bfi-summary-body-resourceprice-total"><?php _e('Deposit', 'bfi') ?></span>	
				<span class="text-nowrap bfi-summary-body-resourceprice-total bfi_<?php echo $currencyclass ?>"  id="totaldeposit"></span>	
			</div>	
	</div>	

<br />



<!-- bficarouselcrosssellresources -->
<?php if(count($allResourceId)>0){ ?>
<div class="bficarouselcrosssellcontainer" style="display:none;">
	<div class="bfi-border bfi-cart-title2"><?php _e('Customers who bought this product also viewed', 'bfi') ?></div>
	<div class="bficarouselcrosssellresources" 
		data-ids="<?php echo implode(",", $allResourceId) ?>" 
		data-maxitems="10" 
		data-cols="4"
		data-descmaxchars="150" 
		data-theme="0" 
		data-details="<?php _e('Details', 'bfi') ?>"
		></div>
	<!-- end bficarouselcrosssellresources -->
</div>	
<?php } ?>




<?php
//---------------------FORM


//	$current_user = wp_get_current_user();
	$current_user = BFCHelper::getSession('bfiUser',null, 'bfi-User');

	if ($current_user==null) {
		$current_user = new stdClass;
		$current_user->CustomerId = 0; 
		$current_user->Name = ""; 
		$current_user->Surname = ""; 
		$current_user->Email = ""; 
		$current_user->Phone = "+39"; 
		$current_user->VATNumber = ""; 
		$current_user->MainAddress = new stdClass;
		$current_user->MainAddress->Address = ""; 
		$current_user->MainAddress->Country = $cultureCode; 
		$current_user->MainAddress->City = ""; 
		$current_user->MainAddress->ZipCode = ""; 
		$current_user->MainAddress->Province = ""; 	    
	}
	$sitename = get_bloginfo();

	$isportal = COM_BOOKINGFORCONNECTOR_ISPORTAL;
	$ssllogo = COM_BOOKINGFORCONNECTOR_SSLLOGO;
	$formlabel = COM_BOOKINGFORCONNECTOR_FORM_KEY;
	$idrecaptcha = uniqid("bfirecaptcha");
	$formlabel = COM_BOOKINGFORCONNECTOR_FORM_KEY;
	$tmpSearchModel = new stdClass;
	$tmpSearchModel->FromDate = new DateTime('UTC');
	$tmpSearchModel->ToDate = new DateTime('UTC');
	

//	$routeThanks = $routeMerchant .'/'. _x('thanks', 'Page slug', 'bfi' );
//	$routeThanksKo = $routeMerchant .'/'. _x('errors', 'Page slug', 'bfi' );
//	$routeThanks = $url_cart_page .'/'. _x('thanks', 'Page slug', 'bfi' );
//	$routeThanksKo = $url_cart_page .'/'. _x('errors', 'Page slug', 'bfi' );
	$routeThanks = $url_cart_page . 'thanks';
	$routeThanksKo = $url_cart_page .'errors';

	$routePrivacy = str_replace("{language}", substr($language,0,2), COM_BOOKINGFORCONNECTOR_PRIVACYURL);
	$routeTermsofuse = str_replace("{language}", substr($language,0,2), COM_BOOKINGFORCONNECTOR_TERMSOFUSEURL);
	$routeNewsletter = str_replace("{language}", substr($language,0,2), COM_BOOKINGFORCONNECTOR_NEWSLETTERURL);
	$routeMarketing = str_replace("{language}", substr($language,0,2), COM_BOOKINGFORCONNECTOR_MARKETINGURL);
	$routeDataprofiling = str_replace("{language}", substr($language,0,2), COM_BOOKINGFORCONNECTOR_DATAPROFILINGURL);

//$infoSendBtn = sprintf(__('Choosing <b>Send</b> means that you agree to <a href="%3$s" target="_blank">Terms of use</a> of %1$s and <a href="%2$s" target="_blank">privacy and cookies statement.</a>.' ,'bfi'),$sitename,$routePrivacy,$routeTermsofuse);
$infoSendBtn = sprintf(__('Choosing <b>Send</b> means that you agree to <a href="%3$s" target="_blank">Terms of use</a> of %1$s.' ,'bfi'),$sitename,$routePrivacy,$routeTermsofuse);

$listPolicyIdsstr = implode(",",$listPolicyIds);
$currPolicies = json_decode($currCart->CartDetails->PolicyDetailsString);
//$currPolicies = BFCHelper::GetPolicyByIds($listPolicyIdsstr, $language, $tmpUserId);
$currPoliciesNoBookable = array();
$currPoliciesBookable = array();
$currPoliciesDescriptions="";

if(!empty( $currPolicies )){
	$currPoliciesNoBookable = array_filter($currPolicies, function ($currPolicy) {
		return (!$currPolicy->RequirePayment);
	});
	$currPoliciesBookable = array_filter($currPolicies, function ($currPolicy) {
		return ($currPolicy->RequirePayment);
	});
	foreach ($currPolicies as $currentPolicy ) {
		$currPoliciesDescriptions .= $currentPolicy->Description;
	}

}



$bookingTypes = null;
$showCC = false;
if(count($currPoliciesNoBookable) >0 ){
	foreach ($currPoliciesNoBookable as $currPolicyNoBookable) {
		if(strrpos($currPolicyNoBookable->MerchantBookingTypesString,'"AcquireCreditCardData":true')>0){
			$showCC = true;
			break;
		}
	}
}

//$firstCCRequiredBookingTypeIds=[];
if(!empty($currPoliciesBookable) && count($currPoliciesBookable) == 1){
	$currPolicy = array_shift($currPoliciesBookable);
	$bookingTypes = json_decode($currPolicy->MerchantBookingTypesString);
}
if(Count($currPoliciesBookable) >1){
	$allMerchantBookingTypes = (array_map(function ($i) { return json_decode($i->MerchantBookingTypesString); }, $currPoliciesBookable));
	$allMerchantBookingTypes = array_map('unserialize', array_unique(array_map('serialize', $allMerchantBookingTypes)));	
	if(Count($allMerchantBookingTypes) == 1){
		$bookingTypes = array_shift($allMerchantBookingTypes);
	} else{		
		$bookingTypeIds = array();
		foreach($allMerchantBookingTypes as $merchantBookingType){
			 usort($merchantBookingType, "BFCHelper::bfi_sortOrder");
			 $bookingTypeIds[] = array_unique(array_map(function ($i) { return $i->BookingTypeId; }, $merchantBookingType));
			 $firstMBT = reset($merchantBookingType); 
			 if ($firstMBT->AcquireCreditCardData) {
				$showCC = true;	
//				$firstCCRequiredBookingTypeIds[]=$firstMBT->BookingTypeId;
			 }
		}
		$availableBookingTypeIds = call_user_func_array('array_intersect', $bookingTypeIds);
		if(!empty($availableBookingTypeIds)){
			$allbookingTypes = array();
			foreach ($allMerchantBookingTypes as $merchantBookingTypes ) {
				foreach ($merchantBookingTypes as $merchantBookingType ) {
					$bookingTypeId = $merchantBookingType->BookingTypeId;
					if (!isset($allbookingTypes[$bookingTypeId])) {
						$allbookingTypes[$bookingTypeId] = $merchantBookingType;
					}
				}
			}
			$bookingTypes = array_filter($allbookingTypes, function ($merchantBookingType) use ($availableBookingTypeIds) {
				return in_array($merchantBookingType->BookingTypeId,$availableBookingTypeIds) ;
			});
		}
	}
 
}

if(isset($currCart->CartDetails->PaymentDetailsString) && !empty(json_decode($currCart->CartDetails->PaymentDetailsString))) {
    $bookingTypes = json_decode($currCart->CartDetails->PaymentDetailsString);
}

$bookingTypedefault ="";
$bookingTypesoptions = array();
$bookingTypesValues = array();
$bookingTypeFrpmForm = isset($_REQUEST['bookingType'])?$_REQUEST['bookingType']:"";
$bookingTypeIddefault = 0;

if(!empty($bookingTypes)){
	$bookingTypesDescArray = array();
	foreach($bookingTypes as $bt)
	{
		$currDesc =  BFCHelper::getLanguage($bt->Name, $language) . "<div class='bfi-ccdescr'>" . BFCHelper::getLanguage($bt->Description, $language, null, array('ln2br'=>'ln2br', 'striptags'=>'striptags')) . "</div>";
		if($bt->AcquireCreditCardData && !empty($bt->Data)){

			$ccimgages = explode("|", $bt->Data);
			$cCCTypeList = array();
			$currDesc .= "<div class='bfi-ccimages'>";
			foreach($ccimgages as $ccimgage){
				$currDesc .= '<i class="fab fa-cc-' . strtolower($ccimgage) . '" title="'. $ccimgage .'"></i>&nbsp;&nbsp;';
				$cCCTypeList[$ccimgage] = $ccimgage; // JHTML::_('select.option', $ccimgage, $ccimgage);
			}
			$currDesc .= "</div>";
 		}
//		if($bt->AcquireCreditCardData==1 && !BFCHelper::isUnderHTTPS() ){
//			continue;
//		}
if ($totalWithVariation>0 || $bt->AcquireCreditCardData ) {
		$bookingTypesoptions[$bt->BookingTypeId.":".$bt->AcquireCreditCardData] =  $currDesc;//  JHTML::_('select.option', $bt->BookingTypeId.":".$bt->AcquireCreditCardData, $currDesc );
    
}

		$calculatedBookingType = $bt;
/*
		$calculatedBookingType->Deposit = 0;
		
		if(isset($calculatedBookingType->DepositRelativeValue) && !empty($calculatedBookingType->DepositRelativeValue)) {
			if($calculatedBookingType->DepositRelativeValue!='0' && $calculatedBookingType->DepositRelativeValue!='0%' && $calculatedBookingType->DepositRelativeValue!='100%')
			{
				if (strpos($calculatedBookingType->DepositRelativeValue,'%') !== false) {
					$calculatedBookingType->Deposit = (float)str_replace("%","",$calculatedBookingType->DepositRelativeValue) *(float) $totalRequest/100;
				}else{
					$calculatedBookingType->Deposit = $calculatedBookingType->DepositRelativeValue;
				}
			}
			if($calculatedBookingType->DepositRelativeValue==='100%'){
				$calculatedBookingType->Deposit = $totalRequest;
			}
		}
*/
if ($totalWithVariation>0 || $bt->AcquireCreditCardData ) {
		$bookingTypesValues[$bt->BookingTypeId] = $calculatedBookingType;
}

		if($bt->IsDefault == true ){
			$bookingTypedefault = $bt->BookingTypeId.":".$bt->AcquireCreditCardData;
			$deposit = $calculatedBookingType->DepositValue;
			$bookingTypeIddefault = $bt->BookingTypeId;
		}

//		$bookingTypesDescArray[] = BFCHelper::getLanguage($bt->Description, $language);;
	}
//	$bookingTypesDesc = implode("|",$bookingTypesDescArray);

	if(empty($bookingTypedefault)){
		$bt = array_values($bookingTypesValues)[0];
		$bookingTypedefault = $bt->BookingTypeId.":".$bt->AcquireCreditCardData;
		$deposit = $bt->DepositValue;
		$bookingTypeIddefault = $bt->BookingTypeId;

	}

	if(!empty($bookingTypeFrpmForm)){
			if (array_key_exists($bookingTypeFrpmForm, $bookingTypesValues)) {
				$bt = $bookingTypesValues[$bookingTypeFrpmForm];
				$bookingTypedefault = $bt->BookingTypeId.":".$bt->AcquireCreditCardData;
				$deposit = $bt->DepositValue;
				$bookingTypeIddefault = $bt->BookingTypeId;
			}
	}

}

?>
<div class="bfi-payment-form bfi-form-field">
<form method="post" id="bfi-resourcedetailsrequest" class="form-validate" action="<?php echo $formRoute; ?>">

<div class="bfi-border bfi-cart-title2">
	<?php if($showlogincart && $current_user->CustomerId <1) { ?>
	<a href="javascript:bfishowlogin()" class="bfi-btn bfi-alternative bfi-pull-right " ><?php _e('Log in to book faster', 'bfi') ?>
	  <span><i id="bfiarrowlogindisplay" class="fa fa-angle-right"></i></span>
	</a>
	<?php } ?>
	<i class="fa fa-user"></i> <?php _e('Enter your details', 'bfi') ?>
</div>
<div id="bfiLoginModule" style="display:<?php echo ($current_user->CustomerId>0)?"":"none"; ?>;">
	<?php bfi_get_template("widgets/login.php", array('moduleclass_sfx'=>'','showpopup'=>1)); ?>
</div>
	<div class="bfi-form-field">
		<div class="bfi-row">
			<div class="bfi-col-md-6">
			<?php if(isset($baseFieldForm->FirstName_Enabled) && !empty($baseFieldForm->FirstName_Enabled)) { ?>
				<div class="bfi_form_txt">
					<input placeholder="<?php _e('Name', 'bfi'); ?> *" type="text" value="<?php echo $current_user->Name ; ?>" size="50" name="form[Name]" id="Name" <?php echo (isset($baseFieldForm->FirstName_Required) && !empty($baseFieldForm->FirstName_Required))? "required": ""; ?>  title="<?php _e('This field is required.', 'bfi') ?>">
				</div><!--/span-->
			<?php } ?>			
			<?php if(isset($baseFieldForm->LastName_Enabled) && !empty($baseFieldForm->LastName_Enabled)) { ?>
				<div class="bfi_form_txt" >
					<input placeholder="<?php _e('Surname', 'bfi'); ?> *" type="text" value="<?php echo $current_user->Surname ; ?>" size="50" name="form[Surname]" id="Surname" <?php echo (isset($baseFieldForm->LastName_Required) && !empty($baseFieldForm->LastName_Required))? "required": ""; ?>  title="<?php _e('This field is required.', 'bfi') ?>">
				</div><!--/span-->
			<?php } ?>			
			<?php if(isset($baseFieldForm->Gender_Enabled) && !empty($baseFieldForm->Gender_Enabled)) { ?>
				<div class="bfi_form_txt" >
					<select id="Gender" name="form[Gender]" <?php echo (isset($baseFieldForm->Gender_Required) && !empty($baseFieldForm->Gender_Required))? "required": ""; ?> class="bfi_input_select width90percent">
						<option value="0" selected><?php _e('Man', 'bfi') ?></option>
						<option value="1"><?php _e('Woman', 'bfi') ?></option>
					</select>
				</div><!--/span-->
			<?php } ?>			
			<div class="bfi_form_txt" >
				<input placeholder="<?php _e('Email', 'bfi'); ?> *"  type="email" value="<?php echo $current_user->Email; ?>" size="50" name="form[Email]" id="formemail" required  title="<?php _e('This field is required.', 'bfi') ?>">
			</div><!--/span-->
			<div class="bfi_form_txt" >
				<input placeholder="<?php _e('Reenter email', 'bfi'); ?> *" type="email" value="<?php echo $current_user->Email; ?>" size="50" name="form[EmailConfirm]" id="formemailconfirm" required equalTo="#formemail" title="<?php _e('This field is required.', 'bfi') ?>">
			</div><!--/span-->
			<?php if(isset($baseFieldForm->Phone_Enabled) && !empty($baseFieldForm->Phone_Enabled)) { ?>
				<div class="bfi_form_txt" >
					<input placeholder="<?php _e('Phone', 'bfi'); ?> *" type="text" value="<?php echo $current_user->Phone; ?>" data-rule-minlength="4" size="20" name="form[Phone]" id="Phone"  <?php echo (isset($baseFieldForm->Phone_Required) && !empty($baseFieldForm->Phone_Required))? "required": ""; ?>  title="<?php _e('This field is required.', 'bfi') ?>" data-msg-minlength="<?php _e('This field is required.', 'bfi') ?>">
				</div><!--/span-->
			<?php } ?>			
			<?php if(isset($baseFieldForm->FiscalCode_Enabled) && !empty($baseFieldForm->FiscalCode_Enabled)) { ?>
				<div class="bfi-vatcode-required bfi_form_txt">
					<input placeholder="<?php _e('Fiscal code', 'bfi'); ?>" type="text" value="<?php echo $current_user->VATNumber; ?>" size="20" name="form[VatCode]" id="VatCode" class="vatCode" <?php echo (isset($baseFieldForm->FiscalCode_Required) && !empty($baseFieldForm->FiscalCode_Required))? "required": ""; ?> title="<?php _e('This field is required.', 'bfi') ?>">
				</div><!--/span-->
			<?php } ?>			
			<?php if(isset($baseFieldForm->Organization_Enabled) && !empty($baseFieldForm->Organization_Enabled)) { ?>
				<div class="bfi_form_txt" >
					<input placeholder="<?php _e('Organization', 'bfi'); ?>" type="text" value="" size="20" name="form[Organization]" id="Organization" <?php echo (isset($baseFieldForm->Organization_Required) && !empty($baseFieldForm->Organization_Required))? "required": ""; ?> title="<?php _e('This field is required.', 'bfi') ?>">
				</div><!--/span-->
			<?php } ?>		
			<div class="bfi-hide bfi_form_txt" style="display:none;">
				<input placeholder="<?php _e('Password', 'bfi'); ?> *" type="password" value="<?php echo $current_user->Email; ?>" size="50" name="form[Password]" id="Password"   title="">
			</div><!--/span-->
	    </div>
	    <div class="bfi-col-md-6">
			<?php if(isset($baseFieldForm->Address_Enabled) && !empty($baseFieldForm->Address_Enabled)) { ?>
				<div class="bfi_form_txt" >
					<input placeholder="<?php _e('Address', 'bfi'); ?>" type="text" value="<?php echo $current_user->MainAddress->Address; ?>" <?php echo (isset($baseFieldForm->Address_Required) && !empty($baseFieldForm->Address_Required))? "required": ""; ?> size="50" name="form[Address]" id="Address"   title="<?php _e('This field is required.', 'bfi') ?>">
				</div><!--/span-->
				<div class="bfi_form_txt" >
					<input placeholder="<?php _e('Postal Code', 'bfi'); ?>" type="text" value="<?php echo $current_user->MainAddress->ZipCode; ?>" <?php echo (isset($baseFieldForm->Address_Required) && !empty($baseFieldForm->Address_Required))? "required": ""; ?> size="20" name="form[Cap]" id="Cap"   title="<?php _e('This field is required.', 'bfi') ?>">
				</div><!--/span-->
				<div class="bfi_form_txt" >
					<input placeholder="<?php _e('City', 'bfi'); ?>" type="text" value="<?php echo $current_user->MainAddress->City ; ?>" <?php echo (isset($baseFieldForm->Address_Required) && !empty($baseFieldForm->Address_Required))? "required": ""; ?> size="50" name="form[City]" id="City"   title="<?php _e('This field is required.', 'bfi') ?>">
				</div><!--/span-->
				<div class="bfi_form_txt" >
					<input placeholder="<?php _e('Province', 'bfi'); ?>" type="text" value="<?php echo $current_user->MainAddress->Province ; ?>" <?php echo (isset($baseFieldForm->Address_Required) && !empty($baseFieldForm->Address_Required))? "required": ""; ?> size="20" name="form[Provincia]" id="Provincia"   title="<?php _e('This field is required.', 'bfi') ?>">
				</div><!--/span-->
			<?php } ?>		
			<?php if(isset($baseFieldForm->Nationality_Enabled) && !empty($baseFieldForm->Nationality_Enabled)) { ?>
				<div class="bfi_form_txt" >
					<select id="formNation" name="form[Nation]" <?php echo (isset($baseFieldForm->Nationality_Required) && !empty($baseFieldForm->Nationality_Required))? "required": ""; ?> class="bfi_input_select width90percent">
							<option value="AR" <?php if(strtolower($current_user->MainAddress->Country) == "ar") {echo "selected";}?> >Argentina</option>
							<option value="AM" <?php if(strtolower($current_user->MainAddress->Country) == "am") {echo "selected";}?> >Armenia</option>
							<option value="AU" <?php if(strtolower($current_user->MainAddress->Country) == "au") {echo "selected";}?> >Australia</option>
							<option value="AZ" <?php if(strtolower($current_user->MainAddress->Country) == "az") {echo "selected";}?> >Azerbaigian</option>
							<option value="BE" <?php if(strtolower($current_user->MainAddress->Country) == "be") {echo "selected";}?> >Belgium</option>
							<option value="BY" <?php if(strtolower($current_user->MainAddress->Country) == "by") {echo "selected";}?> >Bielorussia</option>
							<option value="BA" <?php if(strtolower($current_user->MainAddress->Country) == "ba") {echo "selected";}?> >Bosnia-Erzegovina</option>
							<option value="BR" <?php if(strtolower($current_user->MainAddress->Country) == "br") {echo "selected";}?> >Brazil</option>
							<option value="BG" <?php if(strtolower($current_user->MainAddress->Country) == "bg") {echo "selected";}?> >Bulgaria</option>
							<option value="CA" <?php if(strtolower($current_user->MainAddress->Country) == "ca") {echo "selected";}?> >Canada</option>
							<option value="CN" <?php if(strtolower($current_user->MainAddress->Country) == "cn") {echo "selected";}?> >China</option>
							<option value="HR" <?php if(strtolower($current_user->MainAddress->Country) == "hr") {echo "selected";}?> >Croatia</option>
							<option value="CY" <?php if(strtolower($current_user->MainAddress->Country) == "cy") {echo "selected";}?> >Cyprus</option>
							<option value="CZ" <?php if(strtolower($current_user->MainAddress->Country) == "cz") {echo "selected";}?> >Czech Republic</option>
							<option value="DK" <?php if(strtolower($current_user->MainAddress->Country) == "dk") {echo "selected";}?> >Denmark</option>
							<option value="DE" <?php if(strtolower($current_user->MainAddress->Country) == "de") {echo "selected";}?> >Deutschland</option>
							<option value="EG" <?php if(strtolower($current_user->MainAddress->Country) == "eg") {echo "selected";}?> >Egipt</option>
							<option value="EE" <?php if(strtolower($current_user->MainAddress->Country) == "ee") {echo "selected";}?> >Estonia</option>
							<option value="FI" <?php if(strtolower($current_user->MainAddress->Country) == "fi") {echo "selected";}?> >Finland</option>
							<option value="FR" <?php if(strtolower($current_user->MainAddress->Country) == "fr") {echo "selected";}?> >France</option>
							<option value="GE" <?php if(strtolower($current_user->MainAddress->Country) == "ge") {echo "selected";}?> >Georgia</option>
							<option value="EN" <?php if(strtolower($current_user->MainAddress->Country) == "en") {echo "selected";}?> >Great Britain</option>
							<option value="GR" <?php if(strtolower($current_user->MainAddress->Country) == "gr") {echo "selected";}?> >Greece</option>
							<option value="HU" <?php if(strtolower($current_user->MainAddress->Country) == "hu") {echo "selected";}?> >Hungary</option>
							<option value="IS" <?php if(strtolower($current_user->MainAddress->Country) == "is") {echo "selected";}?> >Iceland</option>
							<option value="IN" <?php if(strtolower($current_user->MainAddress->Country) == "in") {echo "selected";}?> >Indian</option>
							<option value="IE" <?php if(strtolower($current_user->MainAddress->Country) == "ie") {echo "selected";}?> >Ireland</option>
							<option value="IL" <?php if(strtolower($current_user->MainAddress->Country) == "il") {echo "selected";}?> >Israel</option>
							<option value="IT" <?php if(strtolower($current_user->MainAddress->Country) == "it") {echo "selected";}?> >Italia</option>
							<option value="JP" <?php if(strtolower($current_user->MainAddress->Country) == "jp") {echo "selected";}?> >Japan</option>
							<option value="LV" <?php if(strtolower($current_user->MainAddress->Country) == "lv") {echo "selected";}?> >Latvia</option>
							<option value="LI" <?php if(strtolower($current_user->MainAddress->Country) == "li") {echo "selected";}?> >Liechtenstein</option>
							<option value="LT" <?php if(strtolower($current_user->MainAddress->Country) == "lt") {echo "selected";}?> >Lithuania</option>
							<option value="LU" <?php if(strtolower($current_user->MainAddress->Country) == "lu") {echo "selected";}?> >Luxembourg</option>
							<option value="MK" <?php if(strtolower($current_user->MainAddress->Country) == "mk") {echo "selected";}?> >Macedonia</option>
							<option value="MT" <?php if(strtolower($current_user->MainAddress->Country) == "mt") {echo "selected";}?> >Malt</option>
							<option value="MX" <?php if(strtolower($current_user->MainAddress->Country) == "mx") {echo "selected";}?> >Mexico</option>
							<option value="MD" <?php if(strtolower($current_user->MainAddress->Country) == "md") {echo "selected";}?> >Moldavia</option>
							<option value="NL" <?php if(strtolower($current_user->MainAddress->Country) == "nl") {echo "selected";}?> >Netherlands</option>
							<option value="NZ" <?php if(strtolower($current_user->MainAddress->Country) == "nz") {echo "selected";}?> >New Zealand</option>
							<option value="NO" <?php if(strtolower($current_user->MainAddress->Country) == "no") {echo "selected";}?> >Norvay</option>
							<option value="AT" <?php if(strtolower($current_user->MainAddress->Country) == "at") {echo "selected";}?> >Österreich</option>
							<option value="PL" <?php if(strtolower($current_user->MainAddress->Country) == "pl") {echo "selected";}?> >Poland</option>
							<option value="PT" <?php if(strtolower($current_user->MainAddress->Country) == "pt") {echo "selected";}?> >Portugal</option>
							<option value="RO" <?php if(strtolower($current_user->MainAddress->Country) == "ro") {echo "selected";}?> >Romania</option>
							<option value="SM" <?php if(strtolower($current_user->MainAddress->Country) == "sm") {echo "selected";}?> >San Marino</option>
							<option value="SK" <?php if(strtolower($current_user->MainAddress->Country) == "sk") {echo "selected";}?> >Slovakia</option>
							<option value="SI" <?php if(strtolower($current_user->MainAddress->Country) == "si") {echo "selected";}?> >Slovenia</option>
							<option value="ZA" <?php if(strtolower($current_user->MainAddress->Country) == "za") {echo "selected";}?> >South Africa</option>
							<option value="KR" <?php if(strtolower($current_user->MainAddress->Country) == "kr") {echo "selected";}?> >South Korea</option>
							<option value="ES" <?php if(strtolower($current_user->MainAddress->Country) == "es") {echo "selected";}?> >Spain</option>
							<option value="SE" <?php if(strtolower($current_user->MainAddress->Country) == "se") {echo "selected";}?> >Sweden</option>
							<option value="CH" <?php if(strtolower($current_user->MainAddress->Country) == "ch") {echo "selected";}?> >Switzerland</option>
							<option value="TJ" <?php if(strtolower($current_user->MainAddress->Country) == "tj") {echo "selected";}?> >Tagikistan</option>
							<option value="TR" <?php if(strtolower($current_user->MainAddress->Country) == "tr") {echo "selected";}?> >Turkey</option>
							<option value="TM" <?php if(strtolower($current_user->MainAddress->Country) == "tm") {echo "selected";}?> >Turkmenistan</option>
							<option value="US" <?php if(strtolower($current_user->MainAddress->Country) == "us") {echo "selected";}?> >USA</option>
							<option value="UA" <?php if(strtolower($current_user->MainAddress->Country) == "ua") {echo "selected";}?> >Ukraine</option>
							<option value="UZ" <?php if(strtolower($current_user->MainAddress->Country) == "uz") {echo "selected";}?> >Uzbekistan</option>
							<option value="VE" <?php if(strtolower($current_user->MainAddress->Country) == "ve") {echo "selected";}?> >Venezuela</option>
						</select>
				</div><!--/span-->
			<?php } ?>	
			</div>
		</div>
	</div>
	<div>
<!-- dati anagrafici -->
	<div class="bfi-border bfi-cart-title2"><?php _e('Personal data', 'bfi') ?></div>
		<div class="bfi-form-personaldata">
			<?php if(isset($baseFieldForm->BirthDate_Enabled) && !empty($baseFieldForm->BirthDate_Enabled)) { ?>
				<div class="bfi_form_txt" >
					<input placeholder="<?php _e('Birth date', 'bfi'); ?>" class="bfi-question-datetime" data-hourenable="0" placeholder="" type="text" value="" size="50" name="form[BirthDate]" id="BirthDate" <?php echo (isset($baseFieldForm->BirthDate_Required) && !empty($baseFieldForm->BirthDate_Required))? "required": ""; ?>  title="<?php _e('This field is required.', 'bfi') ?>">
				</div><!--/span-->
			<?php } ?>	
			<?php if(isset($baseFieldForm->BirthLocation_Enabled) && !empty($baseFieldForm->BirthLocation_Enabled)) { ?>
				<div class="bfi_form_txt" >
					<input placeholder="<?php _e('Birth location', 'bfi'); ?>" type="text" <?php echo (isset($baseFieldForm->BirthLocation_Required) && !empty($baseFieldForm->BirthLocation_Required))? "required": ""; ?> size="20" name="form[BirthLocation]" id="BirthLocation"   title="<?php _e('This field is required.', 'bfi') ?>">
				</div><!--/span-->
			<?php } ?>	
			<?php if(isset($baseFieldForm->PassportId_Enabled) && !empty($baseFieldForm->PassportId_Enabled)) { ?>
				<div class="bfi_form_txt" >
					<input placeholder="<?php _e('Passport ID', 'bfi'); ?>" type="text" <?php echo (isset($baseFieldForm->PassportId_Required) && !empty($baseFieldForm->PassportId_Required))? "required": ""; ?> size="20" name="form[PassportId]" id="PassportId"   title="<?php _e('This field is required.', 'bfi') ?>">
				</div><!--/span-->
			<?php } ?>	
			<?php if(isset($baseFieldForm->PassportExpiration_Enabled) && !empty($baseFieldForm->PassportExpiration_Enabled)) { ?>
				<div class="bfi_form_txt" >
					<input placeholder="<?php _e('Passport expiration', 'bfi'); ?>" class="bfi-question-datetime" data-hourenable="0" placeholder="" type="text" value="" size="50" name="form[PassportExpiration]" id="PassportExpiration" <?php echo (isset($baseFieldForm->PassportExpiration_Required) && !empty($baseFieldForm->PassportExpiration_Required))? "required": ""; ?>  title="<?php _e('This field is required.', 'bfi') ?>">
				</div><!--/span-->
			<?php } ?>		
			<?php if(isset($baseFieldForm->Document_Enabled) && !empty($baseFieldForm->Document_Enabled)) { ?>
				<div class="bfi_form_txt" >
					<input type="hidden" name="form[DocumentType]" id="DocumentType" value="0" />
					<input placeholder="<?php _e('Document ID', 'bfi'); ?>" type="text" <?php echo (isset($baseFieldForm->Document_Required) && !empty($baseFieldForm->Document_Required))? "required": ""; ?> size="20" name="form[DocumentNumber]" id="DocumentNumber"   title="<?php _e('This field is required.', 'bfi') ?>">
				</div><!--/span-->
			<?php } ?>	
			<?php if(isset($baseFieldForm->DocumentRelease_Enabled) && !empty($baseFieldForm->DocumentRelease_Enabled)) { ?>
				<div class="bfi_form_txt" >
					<input placeholder="<?php _e('Document release place', 'bfi'); ?>" type="text" <?php echo (isset($baseFieldForm->DocumentRelease_Required) && !empty($baseFieldForm->DocumentRelease_Required))? "required": ""; ?> size="20" name="form[DocumentRelease]" id="DocumentRelease" title="<?php _e('This field is required.', 'bfi') ?>">
				</div><!--/span-->
				<div class="bfi_form_txt" >
					<input placeholder="<?php _e('Document release date', 'bfi'); ?>" class="bfi-question-datetime" data-hourenable="0" placeholder="" type="text" value="" size="50" name="form[DocumentReleaseDate]" id="DocumentReleaseDate" <?php echo (isset($baseFieldForm->DocumentRelease_Required) && !empty($baseFieldForm->DocumentRelease_Required))? "required": ""; ?>  title="<?php _e('This field is required.', 'bfi') ?>">
				</div><!--/span-->
			<?php } ?>	
			<?php if(isset($baseFieldForm->DocumentExpiration_Enabled) && !empty($baseFieldForm->DocumentExpiration_Enabled)) { ?>
				<div class="bfi_form_txt" >
					<input placeholder="<?php _e('Document expiration', 'bfi'); ?>" class="bfi-question-datetime" data-hourenable="0" placeholder="" type="text" value="" size="50" name="form[DocumentDate]" id="DocumentDate" <?php echo (isset($baseFieldForm->DocumentExpiration_Required) && !empty($baseFieldForm->DocumentExpiration_Required))? "required": ""; ?>  title="<?php _e('This field is required.', 'bfi') ?>">
				</div><!--/span-->
			<?php } ?>	
			<?php if(isset($baseFieldForm->Language_Enabled) && !empty($baseFieldForm->Language_Enabled)) { ?>
				<div class="bfi_form_txt">
					<select name="form[CultureCode]" class="bfi_input_select" >
						<?php 
						foreach (BFCHelper::$listLanguages  as $valueOpt => $textOpt ) {
						?>
							<option value="<?php echo $valueOpt ?>" <?php echo ($cultureCode==$valueOpt ) ?"selected":""; ?> ><?php echo $textOpt  ?></option>
						<?php 
						} // end foreach
						?>
					</select>
				</div>
			<?php }else{ ?>	
				<input type="hidden" id="cultureCode" name="form[cultureCode]" value="<?php echo $language; ?>" />
			<?php } ?>	
		</div>
<!-- dati anagrafici -->

		<div class="bfi-border bfi-cart-title2"><?php _e('Special Requests', 'bfi') ?></div>
			<div class="bfi-row">
				<div class="bfi-col-md-6">
					<div class="bfi_form_txt" >
						<label><?php _e('Notes', 'bfi'); ?></label>
						<textarea placeholder="<?php _e('Note', 'bfi'); ?>" name="form[note]" class="bfi-col-md-12 bfi-cart-note" style="height:104px;" data-rule-nourl="true"  data-msg-nourl="<?php _e('No URLs allowed!', 'bfi') ?>" ></textarea>    
					</div>
				</div>
				<div class="bfi-col-md-6">
					<div class="bfi_form_txt" >
						<label><?php _e('Your estimated time of arrival', 'bfi') ?></label>
						<select name="form[checkin_eta_hour]" class="bfi_input_select" >
							<option value="N.D."><?php _e('I don\'t know', 'bfi') ?></option>
							<option value="00.00 - 01.00">00:00 - 01:00</option>
							<option value="01.00 - 02.00">01:00 - 02:00</option>
							<option value="02.00 - 03.00">02:00 - 03:00</option>
							<option value="03.00 - 04.00">03:00 - 04:00</option>
							<option value="04.00 - 05.00">04:00 - 05:00</option>
							<option value="05.00 - 06.00">05:00 - 06:00</option>
							<option value="06.00 - 07.00">06:00 - 07:00</option>
							<option value="07.00 - 08.00">07:00 - 08:00</option>
							<option value="08.00 - 09.00">08:00 - 09:00</option>
							<option value="09.00 - 10.00">09:00 - 10:00</option>
							<option value="10.00 - 11.00">10:00 - 11:00</option>
							<option value="11.00 - 12.00">11:00 - 12:00</option>
							<option value="12.00 - 13.00">12:00 - 13:00</option>
							<option value="13.00 - 14.00">13:00 - 14:00</option>
							<option value="14.00 - 15.00">14:00 - 15:00</option>
							<option value="15.00 - 16.00">15:00 - 16:00</option>
							<option value="16.00 - 17.00">16:00 - 17:00</option>
							<option value="17.00 - 18.00">17:00 - 18:00</option>
							<option value="18.00 - 19.00">18:00 - 19:00</option>
							<option value="19.00 - 20.00">19:00 - 20:00</option>
							<option value="20.00 - 21.00">20:00 - 21:00</option>
							<option value="21.00 - 22.00">21:00 - 22:00</option>
							<option value="22.00 - 23.00">22:00 - 23:00</option>
							<option value="23.00 - 00.00">23:00 - 00:00</option>
							<!-- <option value="00:00 - 01:00 (del giorno dopo)">00:00 - 01:00 (del giorno dopo)</option>
							<option value="01:00 - 02:00 (del giorno dopo)">01:00 - 02:00 (del giorno dopo)</option> -->
						</select>
					</div><!--/span-->
				</div>
			</div>

	</div>

<!-- Simple questions -->
<?php 
				if (count($simpleQuestionsRequests)>0) {
?>
				<div class="bfi-form-crew">
<?php 

					foreach ( $simpleQuestionsRequests  as $currQuestion ) {
								?><div class="bfi_form_txt">
									<?php echo $currQuestion['Question'] ?> <?php echo (isset($currQuestion['Required']) && !empty($currQuestion['Required']))? "*": ""; ?>
									<?php if(!empty($currQuestion['Description'])) { ?>
											<div class="bfi-question-details">
												<i class="fa fa-question-circle bfi-cursor-helper" aria-hidden="true" data-placement="auto"></i>
												<div class="webui-popover-content">
												   <div class="bfi-options-popover">
												   <?php echo  $currQuestion['Description']  ?>
												   </div>
												</div>
											</div>
									<?php }
						switch ($currQuestion['Type'] ) {
						    case bfi_InputType::yesno : // scelta si/no
								?>
								<select name="form[Question][<?php echo $currQuestion['Id'] ?>]" class="bfi_input_select" >
										<option value="0" <?php echo ($currQuestion['DefaultValue']=='0' ) ?"selected":""; ?> ><?php _e('no', 'bfi') ?></option>
										<option value="1" <?php echo ($currQuestion['DefaultValue']=='1' ) ?"selected":""; ?> ><?php _e('yes', 'bfi') ?></option>
									</select>
						        <?php 
								break;
						    case bfi_InputType::number : // numero
								?>
								<input placeholder="" type="number" value="" size="50" name="form[Question][<?php echo $currQuestion['Id'] ?>]" <?php echo (isset($currQuestion['Required']) && !empty($currQuestion['Required']))? "required": ""; ?>  title="<?php _e('This field is required.', 'bfi') ?>">
						        <?php 
								break;
						    case bfi_InputType::text : // testo
								?>
								<input placeholder="" type="text" value="" size="50" name="form[Question][<?php echo $currQuestion['Id'] ?>]" <?php echo (isset($currQuestion['Required']) && !empty($currQuestion['Required']))? "required": ""; ?>  title="<?php _e('This field is required.', 'bfi') ?>">
						        <?php 
								break;
						    case bfi_InputType::data : // data
						    case bfi_InputType::datahours : // data e ora
								?>DATA E ORA
								<input class="bfi-question-datetime" data-hourenable="<?php echo ($currQuestion['Type'] == bfi_InputType::datahours) ?"1":"0"; ?>" placeholder="" type="text" value="" size="50" name="form[Question][<?php echo $currQuestion['Id'] ?>]" <?php echo (isset($currQuestion['Required']) && !empty($currQuestion['Required']))? "required": ""; ?>  title="<?php _e('This field is required.', 'bfi') ?>">
						        <?php 
								break;
						    case bfi_InputType::textarea : // textarea
								?>
								<input class="bfi-question-datetime" placeholder="" type="text" value="" size="50" name="form[Question][<?php echo $currQuestion['Id'] ?>]" <?php echo (isset($currQuestion['Required']) && !empty($currQuestion['Required']))? "required": ""; ?>  title="<?php _e('This field is required.', 'bfi') ?>">
								<textarea placeholder="" name="form[Question][<?php echo $currQuestion['Id'] ?>]" class="bfi-col-md-12 bfi-cart-note" style="height:104px;" data-rule-nourl="true"  data-msg-nourl="<?php _e('No URLs allowed!', 'bfi') ?>" ></textarea>    
						        <?php 
								break;
						    case bfi_InputType::dropdown : // scelta con opzioni
						    case bfi_InputType::dropdownmultiple : // scelta con opzioni
								?>
									<select name="form[Question][<?php echo $currQuestion['Id'] ?>]" class="bfi_input_select" <?php echo ($currQuestion['Type']==bfi_InputType::dropdownmultiple ) ?"multiple":""; ?>>
										<?php 
										foreach ($currQuestion['Options']  as $valueOpt => $textOpt ) {
										?>
											<option value="<?php echo $valueOpt ?>" <?php echo ($currQuestion['DefaultValue']==$valueOpt ) ?"selected":""; ?> ><?php echo $textOpt  ?></option>
										<?php 
										} // end foreach
										?>
									</select>
						        <?php 
								break;
							
						        						        
						} //end switch
					    	?></div><?php 
					} // end foreach
?>
				</div>
<?php 
				} //end if
?>
<!-- Experience questions -->
<?php 
$listResourcesExperienceRequests = array_filter($resources, function($resource) {
															if ((isset($resource->ExperiencePickUpAllowCustomAddress) && !empty($resource->ExperiencePickUpAllowCustomAddress)) || (isset($resource->ExperienceDropOffAllowCustomAddress) && !empty($resource->ExperienceDropOffAllowCustomAddress))) {
																return true;
															}
															return false;
															});

if (count($listResourcesExperienceRequests)>0) {
?>
	<div class="bfi-crew">
		<div class="bfi-border bfi-cart-title2"><?php _e('Other information', 'bfi') ?></div>
			<?php 
				$countResource = 1;
				foreach ($listResourcesExperienceRequests as $index => $resource ) { 
			?>
				<div class="bfi-experience-container">
					<div class="bfi-resourcename"><?php echo ($countResource)?>) <?php echo $resource->Name ?>: <?php echo $resource->PaxNumber ?> <?php _e('Persons', 'bfi') ?></div>
					<div class="bfi-cartform-experiencecontainer">
						<div class="bfi-cartform-experience">
							<?php if(isset($resource->ExperiencePickUpAllowCustomAddress) && !empty($resource->ExperiencePickUpAllowCustomAddress)) { ?>
								<div class="bfi_form_txt">
									<?php _e('We can pick up you at the desired address', 'bfi') ?>:
									<input placeholder="<?php _e('Address', 'bfi'); ?>" type="text" value="" size="50" name="form[Experience][<?php echo $index ?>][PickUpCustomAddress]"  title="<?php _e('This field is required.', 'bfi') ?>">
								</div>
							<?php } ?>
							<?php if(isset($resource->ExperienceDropOffAllowCustomAddress) && !empty($resource->ExperienceDropOffAllowCustomAddress)) { ?>
								<div class="bfi_form_txt">
									<?php _e('At experience end, we can take you at the desired address', 'bfi') ?>:
									<input placeholder="<?php _e('Address', 'bfi'); ?>" type="text" value="" size="50" name="form[Experience][<?php echo $index ?>][DropOffCustomAddress]"  title="<?php _e('This field is required.', 'bfi') ?>">
								</div>
							<?php } ?>
						</div>
					</div>
				</div><?php 
		    $countResource++;				    
		}// end foreach
?>
	</div>
<?php 
} // end if 
?>

<!-- CREW -->
<?php 
//				$listResourcesCart[] = $id;
// filtro solo per quelle che vogliono i componenti 
$crewRequests = array_keys( array_filter($crewRequests, function($crewRequest) {
															if ($crewRequest)
																return true;
															return false;
															})
			);


// recupero solo quelle risorse che richiedono un equipaggio
$listResourcesCrew = array_filter($resources, function($resource) use ($crewRequests ) {
															if (in_array($resource->ResourceId, $crewRequests )){
																return true;
															}
															return false;
															});

if (count($listResourcesCrew)>0) {

?>
	<div class="bfi-crew">
		<div class="bfi-border bfi-cart-title2"><i class="fas fa-users"></i> <?php _e('Crew', 'bfi') ?></div>
			<?php 
				$countCrewRes = 1;
				foreach ($listResourcesCrew as $index => $resCrew ) { 
				if (isset($crewRequestsForm[$resCrew->ResourceId])) {
					$idResCrew = $resCrew->ResourceId;
			?>
				<div class="bfi-crew-container">
				<div class="bfi-resourcename"><?php echo ($countCrewRes)?>) <?php echo $resCrew->Name ?>: <?php echo $resCrew->PaxNumber ?> <?php _e('Persons', 'bfi') ?></div>
				<?php 
				for ($i=0; $i<$resCrew->PaxNumber ;$i++ ) {
				?>
				<div class="bfi-form-crew-container">
					<div class="bfi-form-crew-title"><?php echo sprintf(__('Component %s of %s' , 'bfi'), ($i+1), $resCrew->PaxNumber) ?></div>
					<div class="bfi-form-crew">
						<?php if(isset($crewRequestsForm[$idResCrew]['FirstName_Enabled']) && !empty($crewRequestsForm[$idResCrew]['FirstName_Enabled'])) { ?>
							<div class="bfi_form_txt">
								<input placeholder="<?php _e('Name', 'bfi'); ?> <?php echo (isset($crewRequestsForm[$idResCrew]['FirstName_Required']) && !empty($crewRequestsForm[$idResCrew]['FirstName_Required']))? "*": ""; ?>" type="text" value="" size="50" name="crew[<?php echo $index ?>][<?php echo $i ?>][Name]" <?php echo (isset($crewRequestsForm[$idResCrew]['FirstName_Required']) && !empty($crewRequestsForm[$idResCrew]['FirstName_Required']))? "required": ""; ?>  title="<?php _e('This field is required.', 'bfi') ?>">
							</div>
						<?php } ?>
						<?php if(isset($crewRequestsForm[$idResCrew]['LastName_Enabled']) && !empty($crewRequestsForm[$idResCrew]['LastName_Enabled'])) { ?>
							<div class="bfi_form_txt">
								<input placeholder="<?php _e('Surname', 'bfi'); ?> <?php echo (isset($crewRequestsForm[$idResCrew]['LastName_Required']) && !empty($crewRequestsForm[$idResCrew]['LastName_Required']))? "*": ""; ?>" type="text" value="" size="50" name="crew[<?php echo $index ?>][<?php echo $i ?>][Surname]" <?php echo (isset($crewRequestsForm[$idResCrew]['LastName_Required']) && !empty($crewRequestsForm[$idResCrew]['LastName_Required']))? "required": ""; ?>  title="<?php _e('This field is required.', 'bfi') ?>">
							</div>
						<?php } ?>			
						<?php if(isset($crewRequestsForm[$idResCrew]['Gender_Enabled']) && !empty($crewRequestsForm[$idResCrew]['Gender_Enabled'])) { ?>
							<div class="bfi_form_txt" >
								<select name="crew[<?php echo $index ?>][<?php echo $i ?>][Gender]" <?php echo (isset($crewRequestsForm[$idResCrew]['Gender_Required']) && !empty($crewRequestsForm[$idResCrew]['Gender_Required']))? "required": ""; ?> class="bfi_input_select width90percent">
									<option value="0" selected><?php _e('Man', 'bfi') ?></option>
									<option value="1"><?php _e('Woman', 'bfi') ?></option>
								</select>
							</div><!--/span-->
						<?php } ?>			
						<?php if(isset($crewRequestsForm[$idResCrew]['Email_Enabled']) && !empty($crewRequestsForm[$idResCrew]['Email_Enabled'])) { ?>
							<div class="bfi_form_txt">
								<input placeholder="<?php _e('Email', 'bfi'); ?> <?php echo (isset($crewRequestsForm[$idResCrew]['Email_Required']) && !empty($crewRequestsForm[$idResCrew]['Email_Required']))? "*": ""; ?>"  type="email" value="" size="50" name="crew[<?php echo $index ?>][<?php echo $i ?>][Email]" <?php echo (isset($crewRequestsForm[$idResCrew]['Email_Required']) && !empty($crewRequestsForm[$idResCrew]['Email_Required']))? "required": ""; ?>  title="<?php _e('This field is required.', 'bfi') ?>">
							</div>
						<?php } ?>			
						<?php if(isset($crewRequestsForm[$idResCrew]['Phone_Enabled']) && !empty($crewRequestsForm[$idResCrew]['Phone_Enabled'])) { ?>
							<div class="bfi_form_txt">
								<input placeholder="<?php _e('Phone', 'bfi'); ?> <?php echo (isset($crewRequestsForm[$idResCrew]['Phone_Required']) && !empty($crewRequestsForm[$idResCrew]['Phone_Required']))? "*": ""; ?>" value="" type="text" size="50" name="crew[<?php echo $index ?>][<?php echo $i ?>][Phone]" <?php echo (isset($crewRequestsForm[$idResCrew]['Phone_Required']) && !empty($crewRequestsForm[$idResCrew]['Phone_Required']))? "required": ""; ?>  title="<?php _e('This field is required.', 'bfi') ?>">
							</div>
						<?php } ?>		
						<?php if(isset($crewRequestsForm[$idResCrew]['FiscalCode_Enabled']) && !empty($crewRequestsForm[$idResCrew]['FiscalCode_Enabled'])) { ?>
							<div class="bfi_form_txt">
								<input placeholder="<?php _e('Fiscal code', 'bfi'); ?> <?php echo (isset($crewRequestsForm[$idResCrew]['FiscalCode_Required']) && !empty($crewRequestsForm[$idResCrew]['FiscalCode_Required']))? "*": ""; ?>" value="" type="text" size="50" name="crew[<?php echo $index ?>][<?php echo $i ?>][VatCode]" <?php echo (isset($crewRequestsForm[$idResCrew]['FiscalCode_Required']) && !empty($crewRequestsForm[$idResCrew]['FiscalCode_Required']))? "required": ""; ?>  title="<?php _e('This field is required.', 'bfi') ?>">
							</div>
						<?php } ?>		
						<?php if(isset($crewRequestsForm[$idResCrew]['Organization_Enabled']) && !empty($crewRequestsForm[$idResCrew]['Organization_Enabled'])) { ?>
							<div class="bfi_form_txt">
								<input placeholder="<?php _e('Organization', 'bfi'); ?> <?php echo (isset($crewRequestsForm[$idResCrew]['Organization_Required']) && !empty($crewRequestsForm[$idResCrew]['Organization_Required']))? "*": ""; ?>" value="" type="text" size="150" name="crew[<?php echo $index ?>][<?php echo $i ?>][Organization]" <?php echo (isset($crewRequestsForm[$idResCrew]['Organization_Required']) && !empty($crewRequestsForm[$idResCrew]['Organization_Required']))? "required": ""; ?>  title="<?php _e('This field is required.', 'bfi') ?>">
							</div>
						<?php } ?>
						<?php if(isset($crewRequestsForm[$idResCrew]['Address_Enabled']) && !empty($crewRequestsForm[$idResCrew]['Address_Enabled'])) { ?>
							<div class="bfi_form_txt" >
								<input placeholder="<?php _e('Address', 'bfi'); ?> <?php echo (isset($crewRequestsForm[$idResCrew]['Address_Required']) && !empty($crewRequestsForm[$idResCrew]['Address_Required']))? "*": ""; ?>" type="text"<?php echo (isset($crewRequestsForm[$idResCrew]['Address_Required']) && !empty($crewRequestsForm[$idResCrew]['Address_Required']))? "required": ""; ?> size="50" name="crew[<?php echo $index ?>][<?php echo $i ?>][Address]" title="<?php _e('This field is required.', 'bfi') ?>">
							</div><!--/span-->
							<div class="bfi_form_txt" >
								<input placeholder="<?php _e('Postal Code', 'bfi'); ?> <?php echo (isset($crewRequestsForm[$idResCrew]['Address_Required']) && !empty($crewRequestsForm[$idResCrew]['Address_Required']))? "*": ""; ?>" type="text" <?php echo (isset($crewRequestsForm[$idResCrew]['Address_Required']) && !empty($crewRequestsForm[$idResCrew]['Address_Required']))? "required": ""; ?> size="20" name="crew[<?php echo $index ?>][<?php echo $i ?>][Cap]" title="<?php _e('This field is required.', 'bfi') ?>">
							</div><!--/span-->
							<div class="bfi_form_txt" >
								<input placeholder="<?php _e('City', 'bfi'); ?> <?php echo (isset($crewRequestsForm[$idResCrew]['Address_Required']) && !empty($crewRequestsForm[$idResCrew]['Address_Required']))? "*": ""; ?>" type="text" <?php echo (isset($crewRequestsForm[$idResCrew]['Address_Required']) && !empty($crewRequestsForm[$idResCrew]['Address_Required']))? "required": ""; ?> size="50" name="crew[<?php echo $index ?>][<?php echo $i ?>][City]" title="<?php _e('This field is required.', 'bfi') ?>">
							</div><!--/span-->
							<div class="bfi_form_txt" >
								<input placeholder="<?php _e('Province', 'bfi'); ?> <?php echo (isset($crewRequestsForm[$idResCrew]['Address_Required']) && !empty($crewRequestsForm[$idResCrew]['Address_Required']))? "*": ""; ?>" type="text" <?php echo (isset($crewRequestsForm[$idResCrew]['Address_Required']) && !empty($crewRequestsForm[$idResCrew]['Address_Required']))? "required": ""; ?> size="20" name="crew[<?php echo $index ?>][<?php echo $i ?>][Provincia]" title="<?php _e('This field is required.', 'bfi') ?>">
							</div><!--/span-->
						<?php } ?>	
						<?php if(isset($crewRequestsForm[$idResCrew]['Nationality_Enabled']) && !empty($crewRequestsForm[$idResCrew]['Nationality_Enabled'])) { ?>
							<div class="bfi_form_txt" >
								<select name="crew[<?php echo $index ?>][<?php echo $i ?>][Nation]" <?php echo (isset($crewRequestsForm[$idResCrew]['Nationality_Required']) && !empty($crewRequestsForm[$idResCrew]['Nationality_Required']))? "required": ""; ?> class="bfi_input_select width90percent">
									<option value="AR" <?php if(strtolower($current_user->MainAddress->Country) == "ar") {echo "selected";}?> >Argentina</option>
									<option value="AM" <?php if(strtolower($current_user->MainAddress->Country) == "am") {echo "selected";}?> >Armenia</option>
									<option value="AU" <?php if(strtolower($current_user->MainAddress->Country) == "au") {echo "selected";}?> >Australia</option>
									<option value="AZ" <?php if(strtolower($current_user->MainAddress->Country) == "az") {echo "selected";}?> >Azerbaigian</option>
									<option value="BE" <?php if(strtolower($current_user->MainAddress->Country) == "be") {echo "selected";}?> >Belgium</option>
									<option value="BY" <?php if(strtolower($current_user->MainAddress->Country) == "by") {echo "selected";}?> >Bielorussia</option>
									<option value="BA" <?php if(strtolower($current_user->MainAddress->Country) == "ba") {echo "selected";}?> >Bosnia-Erzegovina</option>
									<option value="BR" <?php if(strtolower($current_user->MainAddress->Country) == "br") {echo "selected";}?> >Brazil</option>
									<option value="BG" <?php if(strtolower($current_user->MainAddress->Country) == "bg") {echo "selected";}?> >Bulgaria</option>
									<option value="CA" <?php if(strtolower($current_user->MainAddress->Country) == "ca") {echo "selected";}?> >Canada</option>
									<option value="CN" <?php if(strtolower($current_user->MainAddress->Country) == "cn") {echo "selected";}?> >China</option>
									<option value="HR" <?php if(strtolower($current_user->MainAddress->Country) == "hr") {echo "selected";}?> >Croatia</option>
									<option value="CY" <?php if(strtolower($current_user->MainAddress->Country) == "cy") {echo "selected";}?> >Cyprus</option>
									<option value="CZ" <?php if(strtolower($current_user->MainAddress->Country) == "cz") {echo "selected";}?> >Czech Republic</option>
									<option value="DK" <?php if(strtolower($current_user->MainAddress->Country) == "dk") {echo "selected";}?> >Denmark</option>
									<option value="DE" <?php if(strtolower($current_user->MainAddress->Country) == "de") {echo "selected";}?> >Deutschland</option>
									<option value="EG" <?php if(strtolower($current_user->MainAddress->Country) == "eg") {echo "selected";}?> >Egipt</option>
									<option value="EE" <?php if(strtolower($current_user->MainAddress->Country) == "ee") {echo "selected";}?> >Estonia</option>
									<option value="FI" <?php if(strtolower($current_user->MainAddress->Country) == "fi") {echo "selected";}?> >Finland</option>
									<option value="FR" <?php if(strtolower($current_user->MainAddress->Country) == "fr") {echo "selected";}?> >France</option>
									<option value="GE" <?php if(strtolower($current_user->MainAddress->Country) == "ge") {echo "selected";}?> >Georgia</option>
									<option value="EN" <?php if(strtolower($current_user->MainAddress->Country) == "en") {echo "selected";}?> >Great Britain</option>
									<option value="GR" <?php if(strtolower($current_user->MainAddress->Country) == "gr") {echo "selected";}?> >Greece</option>
									<option value="HU" <?php if(strtolower($current_user->MainAddress->Country) == "hu") {echo "selected";}?> >Hungary</option>
									<option value="IS" <?php if(strtolower($current_user->MainAddress->Country) == "is") {echo "selected";}?> >Iceland</option>
									<option value="IN" <?php if(strtolower($current_user->MainAddress->Country) == "in") {echo "selected";}?> >Indian</option>
									<option value="IE" <?php if(strtolower($current_user->MainAddress->Country) == "ie") {echo "selected";}?> >Ireland</option>
									<option value="IL" <?php if(strtolower($current_user->MainAddress->Country) == "il") {echo "selected";}?> >Israel</option>
									<option value="IT" <?php if(strtolower($current_user->MainAddress->Country) == "it") {echo "selected";}?> >Italia</option>
									<option value="JP" <?php if(strtolower($current_user->MainAddress->Country) == "jp") {echo "selected";}?> >Japan</option>
									<option value="LV" <?php if(strtolower($current_user->MainAddress->Country) == "lv") {echo "selected";}?> >Latvia</option>
									<option value="LI" <?php if(strtolower($current_user->MainAddress->Country) == "li") {echo "selected";}?> >Liechtenstein</option>
									<option value="LT" <?php if(strtolower($current_user->MainAddress->Country) == "lt") {echo "selected";}?> >Lithuania</option>
									<option value="LU" <?php if(strtolower($current_user->MainAddress->Country) == "lu") {echo "selected";}?> >Luxembourg</option>
									<option value="MK" <?php if(strtolower($current_user->MainAddress->Country) == "mk") {echo "selected";}?> >Macedonia</option>
									<option value="MT" <?php if(strtolower($current_user->MainAddress->Country) == "mt") {echo "selected";}?> >Malt</option>
									<option value="MX" <?php if(strtolower($current_user->MainAddress->Country) == "mx") {echo "selected";}?> >Mexico</option>
									<option value="MD" <?php if(strtolower($current_user->MainAddress->Country) == "md") {echo "selected";}?> >Moldavia</option>
									<option value="NL" <?php if(strtolower($current_user->MainAddress->Country) == "nl") {echo "selected";}?> >Netherlands</option>
									<option value="NZ" <?php if(strtolower($current_user->MainAddress->Country) == "nz") {echo "selected";}?> >New Zealand</option>
									<option value="NO" <?php if(strtolower($current_user->MainAddress->Country) == "no") {echo "selected";}?> >Norvay</option>
									<option value="AT" <?php if(strtolower($current_user->MainAddress->Country) == "at") {echo "selected";}?> >Österreich</option>
									<option value="PL" <?php if(strtolower($current_user->MainAddress->Country) == "pl") {echo "selected";}?> >Poland</option>
									<option value="PT" <?php if(strtolower($current_user->MainAddress->Country) == "pt") {echo "selected";}?> >Portugal</option>
									<option value="RO" <?php if(strtolower($current_user->MainAddress->Country) == "ro") {echo "selected";}?> >Romania</option>
									<option value="SM" <?php if(strtolower($current_user->MainAddress->Country) == "sm") {echo "selected";}?> >San Marino</option>
									<option value="SK" <?php if(strtolower($current_user->MainAddress->Country) == "sk") {echo "selected";}?> >Slovakia</option>
									<option value="SI" <?php if(strtolower($current_user->MainAddress->Country) == "si") {echo "selected";}?> >Slovenia</option>
									<option value="ZA" <?php if(strtolower($current_user->MainAddress->Country) == "za") {echo "selected";}?> >South Africa</option>
									<option value="KR" <?php if(strtolower($current_user->MainAddress->Country) == "kr") {echo "selected";}?> >South Korea</option>
									<option value="ES" <?php if(strtolower($current_user->MainAddress->Country) == "es") {echo "selected";}?> >Spain</option>
									<option value="SE" <?php if(strtolower($current_user->MainAddress->Country) == "se") {echo "selected";}?> >Sweden</option>
									<option value="CH" <?php if(strtolower($current_user->MainAddress->Country) == "ch") {echo "selected";}?> >Switzerland</option>
									<option value="TJ" <?php if(strtolower($current_user->MainAddress->Country) == "tj") {echo "selected";}?> >Tagikistan</option>
									<option value="TR" <?php if(strtolower($current_user->MainAddress->Country) == "tr") {echo "selected";}?> >Turkey</option>
									<option value="TM" <?php if(strtolower($current_user->MainAddress->Country) == "tm") {echo "selected";}?> >Turkmenistan</option>
									<option value="US" <?php if(strtolower($current_user->MainAddress->Country) == "us") {echo "selected";}?> >USA</option>
									<option value="UA" <?php if(strtolower($current_user->MainAddress->Country) == "ua") {echo "selected";}?> >Ukraine</option>
									<option value="UZ" <?php if(strtolower($current_user->MainAddress->Country) == "uz") {echo "selected";}?> >Uzbekistan</option>
									<option value="VE" <?php if(strtolower($current_user->MainAddress->Country) == "ve") {echo "selected";}?> >Venezuela</option>
								</select>
							</div><!--/span-->
						<?php } ?>	
						<?php if(isset($crewRequestsForm[$idResCrew]['BirthDate_Enabled']) && !empty($crewRequestsForm[$idResCrew]['BirthDate_Enabled'])) { ?>
							<div class="bfi_form_txt" >
								<input placeholder="<?php _e('Birth date', 'bfi'); ?> <?php echo (isset($crewRequestsForm[$idResCrew]['BirthDate_Required']) && !empty($crewRequestsForm[$idResCrew]['BirthDate_Required']))? "*": ""; ?>" class="bfi-question-datetime" data-hourenable="0" placeholder="" type="text" value="" size="50" name="crew[<?php echo $index ?>][<?php echo $i ?>][BirthDate]" <?php echo (isset($crewRequestsForm[$idResCrew]['BirthDate_Required']) && !empty($crewRequestsForm[$idResCrew]['BirthDate_Required']))? "required": ""; ?>  title="<?php _e('This field is required.', 'bfi') ?>">
							</div><!--/span-->
						<?php } ?>	
						<?php if(isset($crewRequestsForm[$idResCrew]['BirthLocation_Enabled']) && !empty($crewRequestsForm[$idResCrew]['BirthLocation_Enabled'])) { ?>
							<div class="bfi_form_txt" >
								<input placeholder="<?php _e('Birth location', 'bfi'); ?> <?php echo (isset($crewRequestsForm[$idResCrew]['BirthLocation_Required']) && !empty($crewRequestsForm[$idResCrew]['BirthLocation_Required']))? "*": ""; ?>" type="text" <?php echo (isset($crewRequestsForm[$idResCrew]['BirthLocation_Required']) && !empty($crewRequestsForm[$idResCrew]['BirthLocation_Required']))? "required": ""; ?> size="20" name="crew[<?php echo $index ?>][<?php echo $i ?>][BirthLocation]" title="<?php _e('This field is required.', 'bfi') ?>">
							</div><!--/span-->
						<?php } ?>	
						<?php if(isset($crewRequestsForm[$idResCrew]['PassportId_Enabled']) && !empty($crewRequestsForm[$idResCrew]['PassportId_Enabled'])) { ?>
							<div class="bfi_form_txt" >
								<input placeholder="<?php _e('Passport ID', 'bfi'); ?> <?php echo (isset($crewRequestsForm[$idResCrew]['PassportId_Required']) && !empty($crewRequestsForm[$idResCrew]['PassportId_Required']))? "*": ""; ?>" type="text" <?php echo (isset($crewRequestsForm[$idResCrew]['PassportId_Required']) && !empty($crewRequestsForm[$idResCrew]['PassportId_Required']))? "required": ""; ?> size="20" name="crew[<?php echo $index ?>][<?php echo $i ?>][PassportId]" title="<?php _e('This field is required.', 'bfi') ?>">
							</div><!--/span-->
						<?php } ?>	
						<?php if(isset($crewRequestsForm[$idResCrew]['PassportExpiration_Enabled']) && !empty($crewRequestsForm[$idResCrew]['PassportExpiration_Enabled'])) { ?>
							<div class="bfi_form_txt" >
								<input placeholder="<?php _e('Passport expiration', 'bfi'); ?> <?php echo (isset($crewRequestsForm[$idResCrew]['PassportExpiration_Required']) && !empty($crewRequestsForm[$idResCrew]['PassportExpiration_Required']))? "*": ""; ?>" class="bfi-question-datetime" data-hourenable="0" placeholder="" type="text" value="" size="50" name="crew[<?php echo $index ?>][<?php echo $i ?>][PassportExpiration]" <?php echo (isset($crewRequestsForm[$idResCrew]['PassportExpiration_Required']) && !empty($crewRequestsForm[$idResCrew]['PassportExpiration_Required']))? "required": ""; ?>  title="<?php _e('This field is required.', 'bfi') ?>">
							</div><!--/span-->
						<?php } ?>		
						<?php if(isset($crewRequestsForm[$idResCrew]['Document_Enabled']) && !empty($crewRequestsForm[$idResCrew]['Document_Enabled'])) { ?>
							<div class="bfi_form_txt" >
								<input type="hidden" name="crew[<?php echo $index ?>][<?php echo $i ?>][DocumentType]" value="0" />
								<input placeholder="<?php _e('Document ID', 'bfi'); ?> <?php echo (isset($crewRequestsForm[$idResCrew]['Document_Required']) && !empty($crewRequestsForm[$idResCrew]['Document_Required']))? "*": ""; ?>" type="text" <?php echo (isset($crewRequestsForm[$idResCrew]['Document_Required']) && !empty($crewRequestsForm[$idResCrew]['Document_Required']))? "required": ""; ?> size="20" name="crew[<?php echo $index ?>][<?php echo $i ?>][DocumentNumber]" title="<?php _e('This field is required.', 'bfi') ?>">
							</div><!--/span-->
						<?php } ?>	
						<?php if(isset($crewRequestsForm[$idResCrew]['DocumentRelease_Enabled']) && !empty($crewRequestsForm[$idResCrew]['DocumentRelease_Enabled'])) { ?>
							<div class="bfi_form_txt" >
								<input placeholder="<?php _e('Document release place', 'bfi'); ?> <?php echo (isset($crewRequestsForm[$idResCrew]['DocumentRelease_Required']) && !empty($crewRequestsForm[$idResCrew]['DocumentRelease_Required']))? "*": ""; ?>" type="text" <?php echo (isset($crewRequestsForm[$idResCrew]['DocumentRelease_Required']) && !empty($crewRequestsForm[$idResCrew]['DocumentRelease_Required']))? "required": ""; ?> size="20" name="crew[<?php echo $index ?>][<?php echo $i ?>][DocumentRelease]" title="<?php _e('This field is required.', 'bfi') ?>">
							</div><!--/span-->
							<div class="bfi_form_txt" >
								<input placeholder="<?php _e('Document release date', 'bfi'); ?> <?php echo (isset($crewRequestsForm[$idResCrew]['DocumentRelease_Required']) && !empty($crewRequestsForm[$idResCrew]['DocumentRelease_Required']))? "*": ""; ?>" class="bfi-question-datetime" data-hourenable="0" placeholder="" type="text" value="" size="50" name="crew[<?php echo $index ?>][<?php echo $i ?>][DocumentReleaseDate]" <?php echo (isset($crewRequestsForm[$idResCrew]['DocumentRelease_Required']) && !empty($crewRequestsForm[$idResCrew]['DocumentRelease_Required']))? "required": ""; ?>  title="<?php _e('This field is required.', 'bfi') ?>">
							</div><!--/span-->
						<?php } ?>	
						<?php if(isset($crewRequestsForm[$idResCrew]['DocumentExpiration_Enabled']) && !empty($crewRequestsForm[$idResCrew]['DocumentExpiration_Enabled'])) { ?>
							<div class="bfi_form_txt" >
								<input placeholder="<?php _e('Document expiration', 'bfi'); ?> <?php echo (isset($crewRequestsForm[$idResCrew]['DocumentExpiration_Required']) && !empty($crewRequestsForm[$idResCrew]['DocumentExpiration_Required']))? "*": ""; ?>" class="bfi-question-datetime" data-hourenable="0" placeholder="" type="text" value="" size="50" name="crew[<?php echo $index ?>][<?php echo $i ?>][DocumentDate]" <?php echo (isset($crewRequestsForm[$idResCrew]['DocumentExpiration_Required']) && !empty($crewRequestsForm[$idResCrew]['DocumentExpiration_Required']))? "required": ""; ?>  title="<?php _e('This field is required.', 'bfi') ?>">
							</div><!--/span-->
						<?php } ?>
						<?php if(isset($crewRequestsForm[$idResCrew]['Language_Enabled']) && !empty($crewRequestsForm[$idResCrew]['Language_Enabled'])) { ?>
							<div class="bfi_form_txt">
								<select name="<?php echo 'crew'. $index .']['.$i.'][Language]' ?>" class="bfi_input_select" >
									<?php 
									foreach (BFCHelper::$listLanguages  as $valueOpt => $textOpt ) {
									?>
										<option value="<?php echo $valueOpt ?>" <?php echo ($cultureCode==$valueOpt ) ?"selected":""; ?> ><?php echo $textOpt  ?></option>
									<?php 
									} // end foreach
									?>
								</select>
							</div>
						<?php } ?>
					</div>
<?php 
				if (isset($questionsRequestsCrew[$resCrew->ResourceId])) {
					$currQuestions = $questionsRequestsCrew[$resCrew->ResourceId];
?>
				<div class="bfi-form-crew">
<?php 

					foreach ( $currQuestions  as $currQuestion ) {
								?><div class="bfi_form_txt">
									<?php echo $currQuestion['Question'] ?> <?php echo (isset($currQuestion['Required']) && !empty($currQuestion['Required']))? "*": ""; ?>
									<?php if(!empty($currQuestion['Description'])) { ?>
											<div class="bfi-question-details">
												<i class="fa fa-question-circle bfi-cursor-helper" aria-hidden="true" data-placement="auto"></i>
												<div class="webui-popover-content">
												   <div class="bfi-options-popover">
												   <?php echo  $currQuestion['Description']  ?>
												   </div>
												</div>
											</div>
									<?php }

						switch ($currQuestion['Type'] ) {
						    case bfi_InputType::yesno : // scelta si/no
									?>
									<select name="crew[<?php echo $index ?>][<?php echo $i ?>][Question][<?php echo $currQuestion['Id'] ?>]" class="bfi_input_select" >
										<option value="0" <?php echo ($currQuestion['DefaultValue']=='0' ) ?"selected":""; ?> ><?php _e('no', 'bfi') ?></option>
										<option value="1" <?php echo ($currQuestion['DefaultValue']=='1' ) ?"selected":""; ?> ><?php _e('yes', 'bfi') ?></option>
									</select>
						        <?php 
								break;
						    case bfi_InputType::number : // numero
								?>
									<input placeholder="" type="number" value="" size="50" name="crew[<?php echo $index ?>][<?php echo $i ?>][Question][<?php echo $currQuestion['Id'] ?>]" <?php echo (isset($currQuestion['Required']) && !empty($currQuestion['Required']))? "required": ""; ?>  title="<?php _e('This field is required.', 'bfi') ?>">
						        <?php 
								break;
						    case bfi_InputType::text : // testo
								?>
									<input placeholder="" type="text" value="" size="50" name="crew[<?php echo $index ?>][<?php echo $i ?>][Question][<?php echo $currQuestion['Id'] ?>]" <?php echo (isset($currQuestion['Required']) && !empty($currQuestion['Required']))? "required": ""; ?>  title="<?php _e('This field is required.', 'bfi') ?>">
						        <?php 
								break;
						    case bfi_InputType::data : // data
						    case bfi_InputType::datahours : // data e ora
								?>DATE E ORA
									<input class="bfi-question-datetime" data-hourenable="<?php echo ($currQuestion['Type'] == bfi_InputType::datahours) ?"1":"0"; ?>" placeholder="" type="text" value="" size="50" name="crew[<?php echo $index ?>][<?php echo $i ?>][Question][<?php echo $currQuestion['Id'] ?>]" <?php echo (isset($currQuestion['Required']) && !empty($currQuestion['Required']))? "required": ""; ?>  title="<?php _e('This field is required.', 'bfi') ?>">
						        <?php 
								break;
						    case bfi_InputType::textarea : // textarea
								?>
									<textarea placeholder="" name="crew[<?php echo $index ?>][<?php echo $i ?>][Question][<?php echo $currQuestion['Id'] ?>]" class="bfi-col-md-12 bfi-cart-note" style="height:104px;" data-rule-nourl="true"  data-msg-nourl="<?php _e('No URLs allowed!', 'bfi') ?>" ></textarea>    
						        <?php 
								break;
						    case bfi_InputType::dropdown : // scelta con opzioni
						    case bfi_InputType::dropdownmultiple : // scelta con opzioni
								?>
									<select name="crew[<?php echo $index ?>][<?php echo $i ?>][Question][<?php echo $currQuestion['Id'] ?>]" class="bfi_input_select" <?php echo ($currQuestion['Type']==bfi_InputType::dropdownmultiple ) ?"multiple":""; ?>>
<?php 
										foreach ($currQuestion['Options']  as $valueOpt => $textOpt ) {
										?>
											<option value="<?php echo $valueOpt ?>" <?php echo ($currQuestion['DefaultValue']==$valueOpt ) ?"selected":""; ?> ><?php echo $textOpt  ?></option>
										<?php 
										} // end foreach
										?>
									</select>
						        <?php 
								break;
							
						        						        
						} //end switch
					    	?></div><?php 
					    					    
					} // end foreach
?>
				</div>
<?php 
				} //end if
?>
				</div>
				    <?php 
				    $countCrewRes++;				    
				} // end for paxes
				?>
				</div>
		<?php 
				} // end if 
		?>
			</div>
		<?php 

			} // end foreach ?>
	</div>

<?php 
        
}

?>

<!-- END CREW -->
<!-- VIEW_ORDER_PAYMENTMETHOD -->
		<div class="bfi-paymentoptions" style="display:none;" id="bfi-bookingTypesContainer">
			<div class="bfi-border bfi-cart-title2"><i class="fa fa-credit-card"></i> <?php _e('Payment method', 'bfi') ?></div>
			<p><?php _e('Please choose a payment method', 'bfi') ?>:
				<?php echo implode(", ", $allResourceBookable) ?>	
			</p>
			<?php  foreach ($bookingTypesoptions as $key => $value) { ?>
				<label for="form[bookingType]<?php echo $key ?>" id="form[bookingType]<?php echo $key ?>-lbl" class="radio">	
					<input type="radio" name="form[bookingType]" id="form[bookingType]<?php echo $key ?>" value="<?php echo $key ?>" <?php echo $bookingTypedefault == $key ? 'checked="checked"' : "";  ?>  ><?php echo $value ?>
				</label>
			<?php } ?>
		</div>
		<div class="bfi-paymentoptions" id="bfi-bookingTypesDescriptionContainer">
			<h2 id="bookingTypeTitle"></h2>
			<span id="bookingTypeDesc"></span>
			<div id="totaldepositrequested2" class="bfi-pad0-10" style="display:none;">
				<span class="text-nowrap bfi-summary-body-resourceprice-total"><?php _e('Deposit', 'bfi') ?></span>	
				<span class="text-nowrap bfi-summary-body-resourceprice-total bfi_<?php echo $currencyclass ?>"  id="totaldeposit2"></span>	
			</div>	
		</div>
		<div class="bfi-clearfix"></div>

<div style="display:none;" id="bfi-ccInformations" class="borderbottom paymentoptions">
		<div class="bfi-border bfi-cart-title2"><i class="fa fa-credit-card"></i> <?php _e('Credit card details', 'bfi') ?></div>
<?php if($showCC) { ?>
		<div><?php _e('Warranty Request for', 'bfi') ?>: 
		<?php echo implode(", ", $allResourceNoBookable) ?>
		</div>
<?php } ?>
		<div class=" bfi-border2" style="margint-top:10px;">
			<div class="bfi-row">   
				<div class="bfi-col-md-6">
					<label><?php _e('Type', 'bfi') ?> </label>
						<select id="formcc_circuito" name="form[cc_circuito]" class="bfi_input_select">
							<?php 
								foreach($cCCTypeList as $ccCard) {
									?><option value="<?php echo $ccCard ?>"><?php echo $ccCard ?></option><?php 
								}
							?> 
						</select>
				</div>
				<div class="bfi-col-md-6">
					<label><?php _e('Holder', 'bfi') ?> </label>
					<input type="text" value="" size="50" name="form[cc_titolare]" id="cc_titolare" required  title="<?php _e('This field is required.', 'bfi') ?>">
				</div>
			</div>
			<div class="bfi-row bfi-payment-form">
				<div class="bfi-col-md-6">
					<label><?php _e('Number', 'bfi') ?> </label>
					<input type="text" value="" size="50" maxlength="50" name="form[cc_numero]" id="cc_numero" required  title="<?php _e('This field is required.', 'bfi') ?>">
				</div>
				<div class="bfi-col-md-6">
					<label><?php _e('Valid until', 'bfi') ?></label>
					<div class="bfi-ccdateinput">
							<span><?php _e('Month (MM)', 'bfi') ?></span> <span><input type="text" value="" size="2" maxlength="2" name="form[cc_mese]" id="cc_mese" required  title="<?php _e('This field is required.', 'bfi') ?>"></span>
							/
							<span><input type="text" value="" size="2" maxlength="2" name="form[cc_anno]" id="cc_anno" required  title="<?php _e('This field is required.', 'bfi') ?>"></span> <span><?php _e('Year (YY)', 'bfi') ?></span>
					</div><!--/span-->
				</div>
			</div>
		</div>
		<br />
		<div class="bfi-row ">   
			  <div class="bfi-col-md-2">
				 <?php echo $ssllogo ?>
			  </div>
		</div>

</div>	
<?php
if(!empty($currPolicy)) {

$policyHelp = "";
$policy = $currPolicy;
if(!empty( $policy )){
	$currValue = $policy->CancellationBaseValue;
	$policyId= $policy->PolicyId;

	switch (true) {
		case strstr($policy->CancellationBaseValue ,'%'):
			$currValue = $policy->CancellationBaseValue;
			break;
		case strstr($policy->CancellationBaseValue ,'d'):
			$currValue = sprintf(__(' %d day/s' ,'bfi'),rtrim($policy->CancellationBaseValue,"d"));
			break;
		case strstr($policy->CancellationBaseValue ,'n'):
			$currValue = sprintf(__(' %d day/s' ,'bfi'),rtrim($policy->CancellationBaseValue,"n"));
			break;
	}
	$currValuebefore = $policy->CancellationValue;
	switch (true) {
		case strstr($policy->CancellationValue ,'%'):
			$currValuebefore = $policy->CancellationValue;
			break;
		case strstr($policy->CancellationValue ,'d'):
			$currValuebefore = sprintf(__(' %d day/s' ,'bfi'),rtrim($policy->CancellationValue,"d"));
			break;
		case strstr($policy->CancellationValue ,'n'):
			$currValuebefore = sprintf(__(' %d day/s' ,'bfi'),rtrim($policy->CancellationValue,"n"));
			break;
		default:
			$currValuebefore = '<span class="bfi_' . $currencyclass .'">'. BFCHelper::priceFormat($policy->CancellationValue) .'</span>' ;
	}
}
?>
			<div class=" bfi-checkbox-wrapper">
					<input name="form[accettazionepolicy]" class="checkbox" id="agreepolicy" aria-invalid="true" aria-required="true" type="checkbox" required title="<?php _e('Mandatory', 'bfi') ?>">
					<label class="bfi-shownextelement"><?php _e('I agree to the conditions', 'bfi') ?></label>
					<div class="bfi-policies">
<?php
	$currMerchantName="";
	foreach ($allPolicies as  $key => $currSinglePolicy) { 
		$currPolicyDescription = "";
		foreach ($currPolicies as $currPolicy) { 
			if($currPolicy->PolicyId == $currSinglePolicy['policyid']){
				$currPolicyDescription = $currPolicy->Description;
				 break;
			}
		}
		if ($currSinglePolicy['merchantname']!=$currMerchantName) {
		    $currMerchantName = $currSinglePolicy['merchantname'];
			?>
			<div class="bfi-merchantname"><?php echo $currMerchantName ?></div>
			<?php 
		}
		?>
				<div class="bfi-resourcename"><?php echo $key+1 ?>) <?php echo $currSinglePolicy['resourcename'] ?>:</div>
				<p>
				<?php echo $currSinglePolicy['policyHelp'] ?><br />
				<?php echo $currPolicyDescription?><br />

				</p>

		<?php 
	}

					?>
<textarea name="form[policy]" class="bfi-col-md-12" style="display:none;height:200px;margin-top:15px !important;" readonly ><?php
if (count($allPolicyHelp)>0) {
	foreach ($allPolicyHelp as $key => $value) { 
		echo ($key+1) . ") " . $value . "\r\n";
	}
}
?>

<?php echo $currPoliciesDescriptions; ?></textarea>
		</div>
		</div>
		<div class="bfi-clearfix"></div>
		<?php } ?>
 				<div class="bfi-checkbox-wrapper">
					<input name="form[optinprivacy]" id="optinprivacy" type="checkbox" required="" title="<?php _e('Mandatory', 'bfi'); ?>" aria-required="true" />
					<label for="optinprivacy"><?php echo sprintf(__('I have read the information on the processing of personal data', 'bfi'),$sitename) ?> - <a href="<?php echo $routePrivacy ?>" target="_blank"><?php _e('More information', 'bfi') ?></a></label>
				</div>
            <?php if(!empty($portalinfo) && BFCHelper::GetSettingValue($portalinfo->Settings,'system.gdpr.newsletter.enable')) { ?>            
				<div class="bfi-checkbox-wrapper">
					<input name="form[optinemail]" id="optinemail" type="checkbox">
					<label for="optinemail"><?php echo sprintf(__('I give my consent to send the newsletter', 'bfi'),$sitename) ?> - <a href="<?php echo $routeNewsletter ?>" target="_blank"><?php _e('More information', 'bfi') ?></a></label>
				</div>
            <?php } ?>
            <?php if(!empty($portalinfo) && BFCHelper::GetSettingValue($portalinfo->Settings,'system.gdpr.marketing.enable')) { ?>            
				<div class="bfi-checkbox-wrapper">
					<input name="form[optinmarketing]" id="optinmarketing" type="checkbox"/>
					<label for="optinmarketing"><?php echo sprintf(__('I give my consent for marketing purposes', 'bfi'),$sitename) ?> - <a href="<?php echo $routeMarketing ?>" target="_blank"><?php _e('More information', 'bfi') ?></a></label>
				</div>
            <?php } ?>
            <?php if(!empty($portalinfo) && BFCHelper::GetSettingValue($portalinfo->Settings,'system.gdpr.dataprofiling.enable')) { ?>            
				<div class="bfi-checkbox-wrapper">
					<input name="form[optinprofiling]" id="optinprofiling" type="checkbox"/>
					<label for="optinprofiling"><?php echo sprintf(__('I give my consent for profiling purposes', 'bfi'),$sitename) ?> - <a href="<?php echo $routeDataprofiling ?>" target="_blank"><?php _e('More information', 'bfi') ?></a></label>
				</div>
            <?php } ?>

		<?php bfi_display_captcha($idrecaptcha);  ?>

		<input type="hidden" id="actionform" name="actionform" value="<?php echo $formlabel ?>" />
		<input type="hidden" name="form[merchantId]" value="" /> 
		<input type="hidden" id="orderType" name="form[orderType]" value="a" />
		<input type="hidden" id="cultureCode" name="form[cultureCode]__" value="<?php echo $language; ?>" />
		<span style="display:none;"><input type="text" name="form[Fax]" value="" /></span>
		<input type="hidden" id="label" name="form[label]" value="<?php echo $formlabel ?>">
		<input type="hidden" id="resourceId" name="form[resourceId]" value="" /> 
		<input type="hidden" id="redirect" name="form[Redirect]" value="<?php echo $routeThanks; ?>">
		<input type="hidden" id="redirecterror" name="form[Redirecterror]" value="<?php echo $routeThanksKo;?>" />
		<input type="hidden" id="stayrequest" name="form[stayrequest]" value="<?php //echo $stayrequest ?>">
		<input type="hidden" id="staysuggested" name="form[staysuggested]" value="<?php //echo $staysuggested ?>">
		<input type="hidden" id="isgateway" name="form[isgateway]" value="0" />
		<input type="hidden" name="form[hdnOrderData]" id="hdnOrderData" value='<?php echo $currCart->CartConfiguration ?>' />
		<input type="hidden" name="form[hdnOrderDataCart]" id="hdnOrderDataCart" value='<?php echo $currCart->CartConfiguration ?>' />
		<input type="hidden" name="form[bookingtypeselected]" id="bookingtypeselected" value='<?php echo $bookingTypeIddefault ?>' />
		<input type="hidden" id="CartId" name="form[CartId]" value="<?php echo isset($currCart->CartId)?$currCart->CartId:''; ?>">
		<input type="hidden" id="policyId" name="form[policyId]" value="<?php echo $currPolicyId?>">

		</div>

		<div class="bfi-row bfi-footer-book" >
			<div class="bfi-col-md-10">
			<?php echo $infoSendBtn ?>
			</div>
			<div class="bfi-col-md-2 bfi_footer-send"><button type="submit" id="btnbfFormSubmit" class="bfi-btn" style="display:none;"><?php _e('Send', 'bfi') ?></button></div>
		</div>

<?php
$selectedSystemType = array_values(array_filter($bookingTypesValues, function($bt) use($bookingTypedefault) {return $bt->BookingTypeId == $bookingTypedefault;}));
$currSelectedSystemType = 0;
if(!empty( $selectedSystemType )){
	$currSelectedSystemType = $selectedSystemType[0]->PaymentSystemRefId;
}
?>
<script type="text/javascript">
<!--
var bookingTypesValues = null;

var completeStay = <?php echo $currCart->CartConfiguration; ?>;
var bfiMerchants = <?php echo json_encode($merchantDetail) ?>;
var selectedSystemType = "<?php echo $currSelectedSystemType; ?>";
var allCartItems = [];
var bfiAllDiscountCodes = <?php echo json_encode($allDiscountCodes) ?>;

jQuery(function($)
		{

			jQuery('#bfiaddcoupon').on("click", function(e){
				var currCode= jQuery("#bficoupons").val();
				if(bfiAllDiscountCodes.length>0 && jQuery.inArray(currCode, bfiAllDiscountCodes) !== -1){
					alert("<?php _e('Code already used', 'bfi') ?>");
					jQuery("#bficoupons").val('');
					return false;
				}
				return true;
			});
			
			jQuery('.bfi-cartform-delete').on("submit", function(e){
				e.preventDefault();
					var conf = confirm('<?php _e('Are you sure?', 'bfi') ?>');
					if (conf)
					{
					<?php if(COM_BOOKINGFORCONNECTOR_GAENABLED == 1 && !empty(COM_BOOKINGFORCONNECTOR_GAACCOUNT) && COM_BOOKINGFORCONNECTOR_EECENABLED == 1){ ?>
						var currForm = jQuery(this);	
						var currRes= jQuery.parseJSON(currForm.find("input[name=bficurrRes]").val());
						var allDelItems = [ {
										"id": currRes.ResourceId + " - Resource",
										"name": currRes.Name ,
										"category": bfiMerchants[currRes.MerchantId].MainCategoryName,
										"brand": bfiMerchants[currRes.MerchantId].Name,
										"price": currRes.TotalDiscounted,
										"quantity": currRes.SelectedQt,
										"variant": currRes.RatePlanName.toUpperCase(),
									}];
						if(currRes.ExtraServices.length>0){
							for (i in currRes.ExtraServices) {
								currRes.ExtraServices[i].category = bfiMerchants[currRes.MerchantId].MainCategoryName;
								currRes.ExtraServices[i].brand = bfiMerchants[currRes.MerchantId].Name;
								currRes.ExtraServices[i].variant = currRes.RatePlanName.toUpperCase();
							}
							var allDelSrvItems = jQuery.makeArray(jQuery.map(currRes.ExtraServices, function(elm) {
											return {
												"id": elm.PriceId + " - Service",
												"name": elm.Name ,
												"category": elm.category,
												"brand": elm.brand,
												"price": elm.TotalDiscounted,
												"quantity": elm.CalculatedQt,
												"variant": elm.variant,
											};
										}));
							jQuery.merge( allDelItems, allDelSrvItems );
						}
						callAnalyticsEEc("addProduct", allDelItems, "removefromcart", "", {
							"step": 1
						},
						"Add to Cart"
					);

					<?php } ?>
						 this.submit();
					//form.submit();
					}
					return conf;
				});

			jQuery('.bfi-options-help i').webuiPopover({trigger:'hover',placement:'left-bottom',style:'bfi-webuipopover'});
			jQuery('i.bfi-info-price').webuiPopover({trigger:'hover',placement:'auto',style:'bfi-webuipopover'});
			jQuery('.bfi-coupon-details').webuiPopover({trigger:'hover',placement:'left-bottom',style:'bfi-webuipopover'});
			jQuery('.bfi-question-details i').webuiPopover({trigger:'hover',placement:'center-bottom',style:'bfi-webuipopover'});
			
			// inizializzazione calendario scelta date domande
			jQuery('.bfi-question-datetime').each(function(i, elm){
				jQuery(elm).daterangepicker({
					"singleDatePicker": true,
					"autoUpdateInput": false,
					"timePicker": jQuery(elm).attr("data-hourenable")=='1',
					"timePicker24Hour": true,
					"showDropdowns": true,
					"timePickerIncrement": 15,
					"locale": {
						"format": jQuery(elm).attr("data-hourenable")=='1' ? "DD/MM/YYYY HH:mm": "DD/MM/YYYY",
						//"separator": " - ",
						"applyLabel": bfi_variables.bfi_txtTitleBtnOk,
						//"cancelLabel": "Cancel",
						//"fromLabel": "From",
						//"toLabel": "To",
						//"customRangeLabel": "Custom",
						//"weekLabel": "W",
						"daysOfWeek": bfi_variables.bfi_txtTitleDays,
						"monthNames": bfi_variables.bfi_txtTitleMonths,
						"firstDay": 1
					},
				});
				jQuery(elm).on('apply.daterangepicker', function(ev, picker) {
//					console.log(picker);
					jQuery(elm).val(picker.startDate.format('DD/MM/YYYY'));
				});
				
				jQuery(elm).on('cancel.daterangepicker', function(ev, picker) {
					jQuery(elm).val('');
				});
				
			});

			var allItems = jQuery.makeArray(jQuery.map(completeStay.Resources, function(elm) {
							return {
								"id": elm.ResourceId + " - Resource",
								"name": elm.Name ,
								"category": bfiMerchants[elm.MerchantId].MainCategoryName,
								"brand": bfiMerchants[elm.MerchantId].Name,
								"price": elm.TotalDiscounted,
								"quantity": elm.SelectedQt,
								"variant": elm.RatePlanName.toUpperCase(),
							};
						}));
			var allSrvItems = jQuery.makeArray(jQuery.map(jQuery.map(jQuery.grep(completeStay.Resources, function(res) {
				return res.ExtraServices.length>0;
			}), function(resserv) {
							for (i in resserv.ExtraServices) {
								resserv.ExtraServices[i].category = bfiMerchants[resserv.MerchantId].MainCategoryName;
								resserv.ExtraServices[i].brand = bfiMerchants[resserv.MerchantId].Name;
								resserv.ExtraServices[i].variant = resserv.RatePlanName.toUpperCase();
							}
							return resserv.ExtraServices;
			}), function(elm) {
							return {
								"id": elm.PriceId + " - Service",
								"name": elm.Name ,
								"category": elm.category,
								"brand": elm.brand,
								"price": elm.TotalDiscounted,
								"quantity": elm.CalculatedQt,
								"variant": elm.variant,
							};
						}));
			allCartItems = jQuery.merge( jQuery.merge( [], allItems ), allSrvItems );
			if (bfi_variables.analyticsEnabled)
			{
				callAnalyticsEEc("addProduct", allCartItems, "checkout", "", {
					"step": 1
				});
			}
			jQuery("#btnbfFormSubmit").show();

			jQuery(".bfi-shownextelement").click(function(){
				jQuery(this).next().toggle();
			});
			
			<?php if(!empty($bookingTypesValues)) { ?>
			bookingTypesValues = <?php echo json_encode($bookingTypesValues) ?>;// don't use quotes
			<?php } ?>
			jQuery("#bfi-resourcedetailsrequest").validate(
		    {
				rules: {
					"form[cc_mese]": {
					  required: true,
					  range: [1, 12]
					},
					"form[cc_anno]": {
					  required: true,
					  range: [<?php echo $minyear ?>, <?php echo $maxyear ?>]
					},
					"form[cc_numero]": {
					  required: true,
					  creditcard: true
					},
//					"form[ConfirmEmail]": {
//					  email: true,
//					  required: true,
//					  equalTo: "form[Email]"
//					},
				},
		        messages:
		        {
		        	"form[VatCode]" : {
		        		required: "<?php _e('Mandatory', 'bfi') ?>",
		        		vatCode: "<?php _e('Please enter a valid code', 'bfi') ?>"
						},
					"form[cc_mese]": "<?php _e('Mandatory', 'bfi') ?>",
		        	"form[cc_anno]": "<?php _e('Mandatory', 'bfi') ?>",
		        	"form[cc_numero]": "<?php _e('Mandatory', 'bfi') ?>",
		        },

				invalidHandler: function(form, validator) {
                    var errors = validator.numberOfInvalids();
                    if (errors) {
                        /*alert(validator.errorList[0].message);*/
                        validator.errorList[0].element.focus();
                    }
                },
				errorClass: "bfi-error",
				highlight: function(label) {
			    	jQuery(label).removeClass('bfi-error').addClass('bfi-error');
			    	jQuery(label).closest('.control-group').removeClass('bfi-error').addClass('bfi-error');
			    },
			    success: function(label) {
					jQuery(label).remove();
			    },
				submitHandler: function(form) {
					var $form = jQuery(form);
					if($form.valid()){
											jQuery('.bfi-question-datetime').each(function(){
												if (jQuery(this).val()!='')
												{
													var drp = jQuery(this).data('daterangepicker');
													if (jQuery(this).attr("data-hourenable")=='1')
													{
														jQuery(this).val(drp.startDate.format('YYYY-MM-DD HH:mm') +':00');
													}else{
														jQuery(this).val(drp.startDate.format('YYYY-MM-DD') );
													}

												}
											});

						var formSend = true;
						if (typeof grecaptcha === 'object') {
								switch (bfi_variables.googleRecaptchaVersion.toLowerCase()) {
									case 'v3': 
										formSend = false;
										grecaptcha.ready(function() {
											grecaptcha.execute(bfi_variables.googleRecaptchaKey, {action: 'submit'}).then(function(token) {
												 // add token to form
								                $form.prepend('<input type="hidden" name="g-recaptcha-response" value="' + token + '">');
												bookingfor.waitBlockUI();
												if ($form.data('submitted') === true) {
													 return false;
												} else {
													// Mark it so that the next submit can be ignored
													$form.data('submitted', true);
													if (bfi_variables.analyticsEnabled)
													{
														callAnalyticsEEc("addProduct", allCartItems, "checkout", "", {
															"step": 2,
														});
														
														callAnalyticsEEc("addProduct", allCartItems, "checkout_option", "", {
															"step": 2,
															"option": selectedSystemType
														});	
													}
													form.submit();
												}

											});
										});
										break;
									default: 
										var response = grecaptcha.getResponse(window.bfirecaptcha['<?php echo $idrecaptcha ?>']);
										//recaptcha failed validation
										if(response.length == 0) {
											jQuery('#recaptcha-error-<?php echo $idrecaptcha ?>').show();
											return false;
										}
										//recaptcha passed validation
										else {
											jQuery('#recaptcha-error-<?php echo $idrecaptcha ?>').hide();
										}					 
										break;
								}

						}
						if (formSend)
						{
							bookingfor.waitBlockUI();
							if ($form.data('submitted') === true) {
								 return false;
							} else {
								// Mark it so that the next submit can be ignored
								$form.data('submitted', true);
								
								if (bfi_variables.analyticsEnabled)
								{
									callAnalyticsEEc("addProduct", allCartItems, "checkout", "", {
										"step": 2,
									});
									
									callAnalyticsEEc("addProduct", allCartItems, "checkout_option", "", {
										"step": 2,
										"option": selectedSystemType
									});	
								}
								form.submit();
							}
						}

					}
				}

			});
			jQuery("input[name='form[bookingType]']:checked").closest("label").addClass("checked");
			jQuery("input[name='form[bookingType]']").change(function(){
				jQuery(".bfi-paymentoptions label").removeClass("checked");
				var currentSelected = jQuery(this).val().split(':')[0];
				jQuery(this).closest("label").addClass("checked");
				selectedSystemType = Object.keys(bookingTypesValues).indexOf(currentSelected) > -1 ? bookingTypesValues[currentSelected].PaymentSystemRefId : "";
				checkBT();
			});
			var bookingTypeVal= jQuery("input[name='form[bookingType]']");
			var container = jQuery('#bfi-bookingTypesContainer');
			if(bookingTypeVal.length>0 && container.length>0){
					container.show();
			}
			function checkBT(){
					var ccInfo = jQuery('#bfi-ccInformations');
					if (ccInfo.length>0) {
						try
						{
							var currCC = jQuery("input[name='form[bookingType]']:checked");
							if (!currCC.length) {
								currCC = jQuery("input[name='BookingType']")[0];
								jQuery(currCC).prop("checked", true);
							}
							if (jQuery(currCC).length>0)
							{
								var cc = jQuery(currCC).val();
								var ccVal = cc.split(":");
								var reqCC = ccVal[1];
								if (reqCC || <?php echo ($showCC) ?"true":"false";
								 ?>) { 
									ccInfo.show();
								} else {
									ccInfo.hide();
								}
								var idBT = ccVal[0];
								jQuery("#bookingtypeselected").val(idBT);

								$.each(bookingTypesValues, function(key, value) {
									if (idBT == value.BookingTypeId) {
										jQuery("#isgateway").val(value.IsGateway ? "1" : "0");
										if (value.DepositValue != null && value.DepositValue>0) {
											jQuery("#totaldepositrequested").show();
											jQuery("#totaldeposit").html(bookingfor.priceFormat(value.DepositValue, 2, '.', ''));
										}else{
											jQuery("#totaldepositrequested").hide();
										}
									}
								});	
							}else{
								if (<?php echo ($showCC) ?"true":"false"; ?>) { 
									ccInfo.show();
								} else {
									ccInfo.hide();
								}
							}
						}
						catch (err)
						{
							alert(err)
						}
					}
			}
			function checkVatRequired(){
				if (jQuery("#formNation").val() == "IT")
				{
					jQuery(".bfi-vatcode-required").show();
				}else{
					jQuery(".bfi-vatcode-required").hide();
				}
			}
			jQuery("#formNation").change(function(){ checkVatRequired();});
			checkBT();
			checkVatRequired();
		});

var bfisrv = [];
var imgPathMG = "<?php echo BFCHelper::getImageUrlResized('tag','[img]', 'merchant_merchantgroup') ?>";
var imgPathMGError = "<?php echo BFCHelper::getImageUrl('tag','[img]', 'merchant_merchantgroup') ?>";
var resGrp = [];
var loadedResGrp=false;

function bfiUpdateInfoResGrp(){
	jQuery(".bfiresourcetags, .bfiresourcetagshighlighted").each(function(){
		var currObj = jQuery(this);
		var currList = currObj.attr("rel");
		var currArea = Number(currObj.attr("data-area"));
		if (currList!= null && currList!= '' || currArea>0)
		{
			var srvArr = [];
			if (currArea>0)
			{
				srvArr.push('<i class="fa fa-exchange"></i> '+ currArea + ' m²');
			}
			if (currList!= null && currList!= '')
			{
				var srvlist = currList.split(',');
				jQuery.each(srvlist, function(key, srvid) {
					if(typeof bookingfor.tagFullLoaded[srvid] !== 'undefined' ){
						val = bookingfor.tagFullLoaded[srvid];
						if (currObj.hasClass("bfiresourcetags"))
						{
							srvArr.push('<i class="fa fa-check"></i> '+val.Name);
						}else {
							if (val.ImageUrl != null && val.ImageUrl != '') {
								var $imageurl = imgPathMG.replace("[img]", val.ImageUrl );
								var $imageurlError = imgPathMGError.replace("[img]", val.ImageUrl );
								srvArr.push('<img src="' + $imageurl + '" onerror="this.onerror=null;this.src=\'' + $imageurlError + '\';" alt="' + val.Name + '" data-toggle="tooltip" title="' + val.Name + '" /> ' + val.Name);
							} else if (val.IconSrc != null && val.IconSrc != '') {
								if (val.IconType != null && val.IconType != '')
								{
									var fontIcons = val.IconType .split(";");
									if (fontIcons[0] == 'fontawesome5')
									{
										srvArr.push('<i class="' + val.IconSrc + '" data-toggle="tooltip" title="' + val.Name + '"> </i> '+ val.Name);
									}
									if (fontIcons[0] == 'fontawesome4')
									{
										srvArr.push('<i class="fa ' + val.IconSrc + '" data-toggle="tooltip" title="' + val.Name + '"> </i> '+ val.Name);
									}

								}else{
									srvArr.push('<i class="fa ' + val.IconSrc + '" data-toggle="tooltip" title="' + val.Name + '"> </i> '+ val.Name);
								}
							} else {
								srvArr.push('<i class="fa fa-check"></i> '+val.Name);
							}
						}
					}
				});
			}
			jQuery(this).html(srvArr.join(" "));
		}
	});
}

function bfiUpdateInfo(){
	jQuery(".bfisimpleservices").each(function(){
		var currList = jQuery(this).attr("rel");
		if (currList!= null && currList!= '')
		{
			var srvlist = currList.split(',');
			var srvArr = [];
			jQuery.each(srvlist, function(key, srvid) {
				if(typeof bfisrv[srvid] !== 'undefined' ){
					srvArr.push(bfisrv[srvid]);
				}
			});
			jQuery(this).html(srvArr.join(", "));
		}
	});
	bookingfor.shortenText(jQuery(".bfisimpleservices"),150);
}

function bfishowlogin(){
	jQuery("#bfiLoginModule").toggle();
	if (jQuery("#bfiLoginModule").css('display') != 'none')
	{
		jQuery("#bfiarrowlogindisplay").removeClass("fa-angle-right");
		jQuery("#bfiarrowlogindisplay").addClass("fa-angle-down");
		var tmpPop = jQuery(".bfi-payment-form .bfi-mod-bookingforlogin-popup").first();
		if (typeof tmpPop !== 'undefined')
		{
			tmpPop.click();
		}
	}else{
		jQuery("#bfiarrowlogindisplay").addClass("fa-angle-right");
		jQuery("#bfiarrowlogindisplay").removeClass("fa-angle-down");
	}

}
jQuery(document).ready(function () {
	bookingfor.bfiGetAllTags(bfiUpdateInfoResGrp);

});

jQuery(window).load(function() {
	if (!!jQuery.uniform){
	jQuery.uniform.restore(jQuery('#bfi-resourcedetailsrequest input[type="checkbox"]'));
		jQuery.uniform.restore(jQuery("#bfi-resourcedetailsrequest select"));
	}

	bookingfor.carouselCrossSellResources();
});

	//-->

	</script>	
</form>
</div>		
</div>		
		<?php 
		
		}
//}else{
//	echo __('Cart Not enabled! ', 'bfi');
}

?>
	<?php
		/**
		 * bookingfor_after_main_content hook.
		 *
		 * @hooked bookingfor_output_content_wrapper_end - 10 (outputs closing divs for the content)
		 */
		do_action( 'bookingfor_after_main_content' );
	?>

	<?php
		/**
		 * bookingfor_sidebar hook.
		 *
		 * @hooked bookingfor_get_sidebar - 10
		 */
//		do_action( 'bookingfor_sidebar' );
	?>

<?php get_footer( ); ?>
