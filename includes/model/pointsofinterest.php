<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BookingForConnectorModelMerchants Model
 */
if ( ! class_exists( 'BookingForConnectorModelPointsofinterest' ) ) {

	class BookingForConnectorModelPointsofinterest
	{
		private $urlDetails = null;
		private $urltCategories = null;
		
		private $helper = null;
		
		public function __construct($config = array())
		{
		  $ws_url = COM_BOOKINGFORCONNECTOR_WSURL;
			$api_key = COM_BOOKINGFORCONNECTOR_API_KEY;
			$this->helper = new wsQueryHelper($ws_url, $api_key);
			$this->urlDetails = '/GetPointOfinterestById';
			$this->urltCategories = '/GetPointOfInterestCategories';
		}
		
		public function getCategories() {
			$language = $GLOBALS['bfi_lang'];

			$options = array(
					'path' => $this->urltCategories,
					'data' => array(
						'$format' => 'json',
						'cultureCode' => BFCHelper::getQuotedString($language),
						'id' =>$resourceId
					)
				);
			
			$url = $this->helper->getQuery($options);
			
			$resource = null;
			
//			$r = $this->helper->executeQuery($url,null,null,false);
			$r = $this->helper->executeQuery($url,null,null,false,"","",bfi_TagsScope::Poi,$resourceId );
			if (isset($r)) {
				$res = json_decode($r);
				if (!empty($res->d->GetPointOfInterestCategories)){
					$resource = $res->d->GetPointOfInterestCategories;
				}elseif(!empty($res->d)){
					$resource = $res->d;
				}
			}
			return $resource;
		}
		
		public function getDetails($resourceId) {
			$language = $GLOBALS['bfi_lang'];

			$resourceIdRef = $resourceId;
			$options = array(
					'path' => $this->urlDetails,
					'data' => array(
						'$format' => 'json',
						'cultureCode' => BFCHelper::getQuotedString($language),
						'getNearbyItems' => 1,
						'id' =>$resourceId
					)
				);
			
			$url = $this->helper->getQuery($options);
			
			$resource = null;
			
//			$r = $this->helper->executeQuery($url,null,null,false);
			$r = $this->helper->executeQuery($url,null,null,false,"","",bfi_TagsScope::Poi,$resourceId );
			if (isset($r)) {
				$res = json_decode($r);
				if (!empty($res->d->GetPointOfinterestById)){
					$resource = $res->d->GetPointOfinterestById;
				}elseif(!empty($res->d)){
					$resource = $res->d;
				}
				if(!empty($resource->Address->XPos) && !empty($resource->Address->YPos)){
					$resourceLat = $resource->Address->XPos;
					$resourceLon = $resource->Address->YPos;
					$currPoint = "0|" . $resourceLat . " " . $resourceLon . " 10000";
					$resource->Poi = BFCHelper::GetProximityPoi($currPoint);
					foreach ($resource->Poi as $key => $item ) {
						if ($item->PointOfInterestId  == $resourceId) {
						    unset($resource->Poi[$key]);
						}
					}

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
		
	}
}