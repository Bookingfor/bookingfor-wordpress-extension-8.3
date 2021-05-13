<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$base_url = get_site_url();

$language = $GLOBALS['bfi_lang'];
$languageForm ='';
if(defined('ICL_LANGUAGE_CODE') &&  class_exists('SitePress')){
		global $sitepress;
		if($sitepress->get_current_language() != $sitepress->get_default_language()){
			$languageForm = "/" .ICL_LANGUAGE_CODE;
		}
}
$isportal = COM_BOOKINGFORCONNECTOR_ISPORTAL;
$showdata = COM_BOOKINGFORCONNECTOR_SHOWDATA;

$showmap = !empty(COM_BOOKINGFORCONNECTOR_ENABLEGOOGLEMAPSAPI);

if($total<1){
	$showmap = false;
}
if ($showmap && COM_BOOKINGFORCONNECTOR_USE_OPENSTREETMAP) {
	wp_enqueue_script('bfileaflet');
	wp_enqueue_script('bfileafletcontrolcustom');
}

$currParam = BFCHelper::getSearchEventParamsSession();
$currencyclass = bfi_get_currentCurrency();
$checkin = BFCHelper::getStayParam('checkin', new DateTime('UTC'));
$checkout = BFCHelper::getStayParam('checkout', new DateTime('UTC'));
$points =  '';
$merchantIds = '';
$stateIds = '';
$regionIds = '';
$cityIds = '';
$newsearch = 0;
$tagids = '';
$searchid = uniqid('', true);

if (!empty($currParam)){

		$checkin = isset($currParam['checkin']) ? $currParam['checkin'] : $checkin ;
		$checkout = isset($currParam['checkout']) ? $currParam['checkout'] : $checkout ;
		$eventName = isset($currParam['eventName']) ? $currParam['eventName'] : '' ;
		$cultureCode = $currParam['cultureCode'];
		$merchantId = isset($currParam['merchantId']) ? $currParam['merchantId'] : 0 ;
		$points = isset($currParam['points']) ? $currParam['points'] : '' ;
		$tagids = isset($currParam['tagids'])?$currParam['tagids']:"";
		$searchid = !empty($currParam['searchid']) ? $currParam['searchid'] : uniqid('', true);
		$merchantIds = isset($currParam['merchantIds']) ? $currParam['merchantIds'] : '';
		$stateIds = isset($currParam['stateIds']) ? $currParam['stateIds'] : '' ; 
		$regionIds = isset($currParam['regionIds']) ? $currParam['regionIds'] : '' ; 
		$cityIds = isset($currParam['cityIds']) ? $currParam['cityIds'] : '' ;
		$zoneIds = isset($currParam['zoneIds']) ? $currParam['zoneIds'] : '' ; 
		$getFilters = !empty($currParam['getFilters']) ? $currParam['getFilters'] : 1;
		$categoryIds = isset($currParam['categoryIds'])?$currParam['categoryIds']:"";

}

/*---------- track analytics   ---*/
if(!empty( $newsearch )){

	$track = array($checkin->format('d/m/Y'),$checkout->format('d/m/Y'),$total,$tagids,$merchantIds,$stateIds,$regionIds,$cityIds);
	$trackstr = implode('|',$track);
	if(strlen($trackstr) > 500){
		$trackstr = substr($trackstr, 0, 500);
	}
?>
<script type="text/javascript">
<!--
if (typeof(ga) !== 'undefined') {
	ga('send', 'event', 'Bookingfor - SearchEvent', 'Search', '<?php echo $trackstr ?>');
}
	
//-->
</script>
<?php 
	}
/*---------- END track analytics   ---*/
?>
<div id="bfi-merchantlist"> 
	<div>
<?php if ($total > 0){ ?>
	<?php 
			$results = $items ;			
			
			bfi_get_template("search/default_results_experience.php",array("results"=>$results,"totalAvailable"=>$totalAvailable,"total"=>$total,"items"=>$items,"pages"=>$pages,"page"=>$page,"currencyclass"=>$currencyclass,"listNameAnalytics"=>$listNameAnalytics,"totPerson"=>$totPerson,"currSorting"=>$currSorting));	

		?>
		<?php
		if( get_option('permalink_structure') ) {
			$format = 'page/%#%/';
		} else {
			$format = '?paged=%#%';
		}
			$url = esc_url( get_permalink() ); 
		  $pagination_args = array(
			'base'            => $url. '%_%',
			'format'          => $format, //'?page=%#%',
			'total'           => $pages,
			'current'         => $page,
			'show_all'        => false,
			'end_size'        => 5,
			'mid_size'        => 2,
			'prev_next'       => true,
			'prev_text'       => __('&laquo;'),
			'next_text'       => __('&raquo;'),
			'type'            => 'plain',
			'add_args'        => array( 'searchid' => $searchid ),
			'add_fragment'    => ''
		  );



		global $bfi_query_arg;
		$bfi_query_arg = array();
		add_filter( 'get_pagenum_link', 'bfi_remove_date_get_pagenum_link',1 );
		if (!function_exists('bfi_remove_date_get_pagenum_link')) {
			function bfi_remove_date_get_pagenum_link( $url ) {
				global $bfi_query_arg;
				$checkinFromUrl = filter_input( INPUT_GET, 'checkin');
				if(!empty($checkinFromUrl)){
					$bfi_query_arg['checkin'] = rawurlencode($checkinFromUrl);
					$url = remove_query_arg( 'checkin', $url );
				}
				$checkoutFromUrl = filter_input( INPUT_GET, 'checkout');
				if(!empty($checkoutFromUrl)){
					$bfi_query_arg['checkout'] = rawurlencode($checkoutFromUrl);
					$url = remove_query_arg( 'checkout', $url );
				}
				
				return  $url;
			}	    
		}

		add_filter( 'paginate_links', function( $link )
		{
			global $bfi_query_arg;
			$filter_order = filter_input( INPUT_POST, 'filter_order');
			if(!empty($filter_order)){
				$link = remove_query_arg( 'filter_order', $link );
				$link = add_query_arg('filterorder' , $filter_order , $link);
			}else{
				$filter_order = filter_input( INPUT_GET, 'filter_order');
				if(!empty($filter_order)){
					$link = remove_query_arg( 'filter_order', $link );
					$link = add_query_arg('filterorder' , $filter_order , $link);
				}
			}
			$filter_order_dir = filter_input( INPUT_POST, 'filter_order_Dir');
			if(!empty($filter_order_dir)){
				$link = remove_query_arg( 'filter_order_Dir', $link );
				$link = add_query_arg('filterorderdir' , $filter_order_dir , $link);
			}else{
				$filter_order_dir = filter_input( INPUT_GET, 'filter_order_Dir');
				if(!empty($filter_order_dir)){
					$link = remove_query_arg( 'filter_order_Dir', $link );
					$link = add_query_arg('filterorderdir' , $filter_order_dir , $link);
				}
			}

			if(!empty($bfi_query_arg)){
				$link =  add_query_arg($bfi_query_arg , $link);
			}

			$link = filter_input( INPUT_GET, 'newsearch' ) ? remove_query_arg( 'newsearch', $link ) : $link;

			$link =  add_query_arg('newsearch' , 0 , $link);
			$link = str_replace('filterorder=',"filter_order=",$link);
			$link = str_replace('filterorderdir=',"filter_order_Dir=",$link);

			return $link;
		} );

		  $paginate_links = paginate_links($pagination_args);
			if ($paginate_links) {
			  echo "<nav class='bfi-pagination'>";
		//      echo "<span class='page-numbers page-num'>Page " . $page . " of " . $numpages . "</span> ";
			  echo "<span class='page-numbers page-num'>".__('Page', 'bfi')." </span> ";
			  print $paginate_links;
			  echo "</nav>";
			}
			 ?>
	<?php }else{ ?>
<div class="bfi-content">
		<div class="bfi-noresults">
			<?php _e('No result available', 'bfi') ?>
		</div>
</div>

<?php } ?>
	<div class="bfi-clearfix"></div>		
</div>
<?php if ($showmap) {  
$posx = COM_BOOKINGFORCONNECTOR_GOOGLE_POSX;
$posy = COM_BOOKINGFORCONNECTOR_GOOGLE_POSY;
$startzoom = COM_BOOKINGFORCONNECTOR_GOOGLE_STARTZOOM;
$googlemapsapykey = COM_BOOKINGFORCONNECTOR_GOOGLE_GOOGLEMAPSKEY;
	
?>
<div class="bfi-clearboth"></div>
<div id="bfi-maps-popup"></div>

<script type="text/javascript">
<!--
		var mapSearch;
		var myLatlngsearch;
		var oms;
		var markersLoading = false;
		var infowindow = null;
		var markersLoaded = false;
		var bfiCurrMarkerId = 0;
		var bfiLastZIndexMarker = 1000;
		var Leaflet;
		if (bfi_variables.bfiMapsFree)
		{
			Leaflet = L.noConflict();
		}
//-->
</script>
<?php }else{ // showmap 
	if ($total > 0){ 
?>
<?php
	}
} // showmap ?>