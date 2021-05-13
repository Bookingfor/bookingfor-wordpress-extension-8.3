<?php
//mimic the actuall admin-ajax
//define('DOING_AJAX', true);
//define( 'SHORINIT', true );

if (!isset( $_GET['action']))
    die('-1');

//make sure you update this line 
//to the relative location of the wp-load.php
//require_once('../../../../../wp-load.php'); 
//Typical headers
header('Content-Type: text/html');
//send_nosniff_header();

//Disable caching
header('Cache-Control: no-cache');
header('Pragma: no-cache');


//$action = esc_attr(trim($_GET['action']));
$action = (trim($_GET['action']));

//A bit of security
$allowed_actions = array(
    'SearchByText',
);


if(in_array($action, $allowed_actions)){
			SearchByText();
//			$term = stripslashes($_REQUEST['term']);
//			$resultClasses = stripslashes($_REQUEST["resultClasses"]);
//			$maxresults = stripslashes($_REQUEST["maxresults"]);
//			$minMatchingPercentage = stripslashes($_REQUEST["minMatchingPercentage"]);
//			if(!isset($maxresults) || empty($maxresults)) {
//				$maxresults = 5;
//			} else {
//				$maxresults = (int)$maxresults;
//			}
//			$language = isset($_REQUEST['cultureCode']) ? $_REQUEST['cultureCode'] : '' ;
//			$resultsurl = "https://marsdemo.bookingfor.com/modules/bookingfor/services/bookingservice.svc/SearchResult?$"."format=json&term='$term'&resultClasses='$resultClasses'&minMatchingPercentage=$minMatchingPercentage&cultureCode='$language'&top=$maxresults&apikey=MTdhZDNhM2YtMGY5Ny00YmZmLWFkYjYtY2VmMTNiMTA2YmY4OkFDbnQ0SW5kK1VpMDhTVkxsUUxhY3ZpU3cvOHVsaTF4L3ZmRUswV1g3emUyQ0pTRE1WZ0pBcUdZOUxuVWtISzQ5UT09";
//			echo $resultsurl ;
//			$resultsurl = file_get_contents ("https://marsdemo.bookingfor.com/modules/bookingfor/services/bookingservice.svc/SearchResult?'. '$'.'format=json&term='$term'&resultClasses='$resultClasses'&minMatchingPercentage=$minMatchingPercentage&cultureCode='$language'&top=$maxresults&apikey=MTdhZDNhM2YtMGY5Ny00YmZmLWFkYjYtY2VmMTNiMTA2YmY4OkFDbnQ0SW5kK1VpMDhTVkxsUUxhY3ZpU3cvOHVsaTF4L3ZmRUswV1g3emUyQ0pTRE1WZ0pBcUdZOUxuVWtISzQ5UT09");

//	echo file_get_contents ($resultsurl);
//	echo "ok";
}
else{
    die('-1');
} 
 function getVar($string, $defaultValue=null) {			
	$currVal= isset($_REQUEST[$string]) ? $_REQUEST[$string] : $defaultValue;
	if (!is_array($currVal) ) {
		$currVal = str_ireplace(' ', '+', $currVal);
		$currVal = htmlspecialchars($currVal, ENT_QUOTES, 'UTF-8');
	}else{
		foreach ($currVal as $key=>$val  ) {
			$val=htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
		}
	}
	return $currVal;
}

function SearchByText() {
		require_once('../bfi_const.php'); 
		$results = array();

		$term = stripslashes(getVar("term"));		
//		$term = str_ireplace(' ', '+', $term);
		$resultClasses = stripslashes(getVar("resultClasses"));
		$maxresults = stripslashes(getVar("maxresults"));
		$minMatchingPercentage = stripslashes(getVar("minMatchingPercentage"));
		if(!isset($maxresults) || empty($maxresults)) {
			$maxresults = 5;
		} else {
			$maxresults = (int)$maxresults;
		}
		$language = isset($_REQUEST['cultureCode']) ? $_REQUEST['cultureCode'] : '' ;
		$itemtypeid = isset($_REQUEST['itemtypeid']) ? $_REQUEST['itemtypeid'] : '' ;
		$limitregions = isset($_REQUEST['limitregions']) ? $_REQUEST['limitregions'] : '' ;
		$productcategories = isset($_REQUEST['productcategories']) ? $_REQUEST['productcategories'] : '' ;
		$merchantcategories = isset($_REQUEST['merchantcategories']) ? $_REQUEST['merchantcategories'] : '' ;
		if(!empty($term)) {
			$resultsurl = "https://$key.bookingfor.com/modules/bookingfor/services/bookingservice.svc/SearchResult?$"."format=json&term='$term'&resultClasses='$resultClasses'&minMatchingPercentage=$minMatchingPercentage&cultureCode='$language'&top=$maxresults";
			$filtersArray = array();
			if (!empty($limitregions) && $limitregions == "1") {
				$obj = array();
				$obj["Level1RegionOnly"] = true;
				$filtersArray[3] = $obj;
			}
			if (!empty($productcategories) || isset($itemtypeid)) {
				$obj = array();
				if (isset($itemtypeid)) {
					$obj["ItemTypeId"] = array();
					$obj["ItemTypeId"][] = intval($itemtypeid);
				}
				if (!empty($productcategories)) {
					$obj["Categories"] = array();
					$strings_array = explode(',', $productcategories);
					foreach ($strings_array as $each_number) {
						$obj["Categories"][] = (int) $each_number;
					}
				}
				$filtersArray[19] = $obj;
			}
			if (!empty($merchantcategories)) {
				$obj = array();
				$obj["Categories"] = array();
				$strings_array = explode(',', $merchantcategories);
				foreach ($strings_array as $each_number) {
					$obj["Categories"][] = (int) $each_number;
				}
				$filtersArray[18] = $obj;
			}
			$resultsurl .= "&additionalFilters='" . json_encode($filtersArray) . "'";
			$resultsurl .= "&apikey=$apikey";
			$r = file_get_contents ($resultsurl);
	
//			$postdata = http_build_query(
//				array(
//					'term' => '\'' .$term . '\'' ,
//					'resultClasses' => '\'' .$resultClasses. '\'',
//					'minMatchingPercentage' => $minMatchingPercentage ,
//					'cultureCode' => '\'' .$language. '\'',
//					'top' => $maxresults ,
//					'apikey' => $apikey ,
//				)
//			);
//
//			$opts = array('http' =>
//				array(
//					'method'  => 'GET',
//					'header'  => 'Content-Type: application/x-www-form-urlencoded',
//					'content' => $postdata
//				)
//			);
//
//			$context  = stream_context_create($opts);
//			$resultsurl ="https://".$key.".bookingfor.com/modules/bookingfor/services/bookingservice.svc/SearchResult?$"."format=json";
//			$r = file_get_contents($resultsurl , false, $context);


			$res = json_decode($r);

			if (!empty($res->d->SearchResult)){
				$results = $res->d->SearchResult;
			}elseif(!empty($res->d)){
				$results = $res->d;
			}
			
//			if(!empty($results)){
//				$results = json_encode($results);
//			}
		}
		echo  json_encode($results);
	}
