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

$currParam = BFCHelper::getSearchParamsSession();
$merchantResults = false;
$resourcegroupsResults = false;
$variationPlanIds = '';
$currencyclass = bfi_get_currentCurrency();
$checkin = BFCHelper::getStayParam('checkin', new DateTime('UTC'));
$checkout = BFCHelper::getStayParam('checkout', new DateTime('UTC'));
$duration = $checkin->diff($checkout)->format('%a');
$paxes = 2;
$paxages = array();
$points =  '';
$merchantCategoryId = '';
$masterTypeId = '';
$availabilitytype = 1;
$itemtypes = '0';
$merchantTagIds = '';
$resourcegroupId = '';
$merchantIds = '';
$stateIds = '';
$regionIds = '';
$cityIds = '';
$newsearch = 0;
$minqt = 1;
$maxqt = 1;

$groupResultType = 0;

if (!empty($currParam)){
	$merchantResults = isset($currParam['merchantResults']) ? $currParam['merchantResults']: $merchantResults ;
	$resourcegroupsResults = isset($currParam['resourcegroupsResults']) ? $currParam['resourcegroupsResults']: $resourcegroupsResults ;
	$variationPlanIds = !empty($currParam['variationPlanIds']) ? $currParam['variationPlanIds'] : $variationPlanIds;
//	if (!empty($pars['paxes'])) {
//		$paxes = $pars['paxes'];
//	}
//	if (!empty($pars['paxages'])) {
//		$paxages = $pars['paxages'];
//	}
	$paxes = !empty($currParam['paxes']) ? $currParam['paxes'] : $paxes ;
	$paxages = !empty($currParam['paxages']) ? $currParam['paxages'] : $paxages ;
	$points = !empty($currParam['points']) ? $currParam['points'] : $points ;
	$merchantCategoryId = !empty($currParam['merchantCategoryId']) ? $currParam['merchantCategoryId'] : $merchantCategoryId ;
	$masterTypeId = !empty($currParam['masterTypeId']) ? $currParam['masterTypeId'] : $masterTypeId ;
	$availabilitytype = isset($currParam['availabilitytype']) ? $currParam['availabilitytype'] : $availabilitytype ;
	$itemtypes = !empty($currParam['itemtypes']) ? $currParam['itemtypes'] : $itemtypes ;
	$merchantTagIds = !empty($currParam['merchantTagIds']) ? $currParam['merchantTagIds'] : $merchantTagIds ;
	$merchantIds = !empty($currParam['merchantIds']) ? $currParam['merchantIds'] : $merchantIds ;
	$stateIds = !empty($currParam['stateIds']) ? $currParam['stateIds'] : $stateIds ;
	$regionIds = !empty($currParam['regionIds']) ? $currParam['regionIds'] : $regionIds ;
	$cityIds = !empty($currParam['cityIds']) ? $currParam['cityIds'] : $cityIds ;
	$newsearch = !empty($currParam['newsearch']) ? $currParam['newsearch'] : $newsearch ;
	$minqt = !empty($currParam['minqt']) ? $currParam['minqt'] : 1;
	$maxqt = !empty($currParam['maxqt']) ? $currParam['maxqt'] : 1;
	$groupResultType = !empty($currParam['groupresulttype']) ? $currParam['groupresulttype'] : 0;
}

if (empty($paxages)){
	$paxes = 2;
	$paxages = array(BFCHelper::$defaultAdultsAge, BFCHelper::$defaultAdultsAge);
}
if(empty( $resourcegroupId )){
	$resourcegroupId = BFCHelper::getVar('resourcegroupId','');
}
$nad = 0;
$nch = 0;
$nse = 0;

$currTypeAltDates = "resource";
switch ($groupResultType) {
	case 0: //resources
		$currTypeAltDates = "resource";
		break;
	case 1: //merchants
		$currTypeAltDates = "merchant";
		break;
	case 2: //grouped resources
		$currTypeAltDates = "resourcegroup";
		
		break;
}
/*---------- track analytics   ---*/
if(!empty( $newsearch )){

$countPaxes = 0;
$maxchildrenAge = (int)BFCHelper::$defaultAdultsAge-1;
$nchs = array(null,null,null,null,null,null);
if(is_array($paxages)){
	$countPaxes = array_count_values($paxages);
	$nchs = array_values(array_filter($paxages, function($age) {
		if ($age < (int)BFCHelper::$defaultAdultsAge)
			return true;
		return false;
	}));
}
array_push($nchs, null,null,null,null,null,null);
if($countPaxes>0){
	foreach ($countPaxes as $key => $count) {
		if ($key >= BFCHelper::$defaultAdultsAge) {
			if ($key >= BFCHelper::$defaultSenioresAge) {
				$nse += $count;
			} else {
				$nad += $count;
			}
		} else {
			$nch += $count;
		}
	}
}
$nchs = array_slice($nchs,0,$nch);


$track = array($merchantCategoryId,$masterTypeId,$checkin->format('d/m/Y'),$checkout->format('d/m/Y'),$nad,$nse,$nch,implode(',',$nchs),$itemtypes,$totalAvailable,$merchantIds,$stateIds,$regionIds,$cityIds);
$trackstr = implode('|',$track);
if(strlen($trackstr) > 500){
	$trackstr = substr($trackstr, 0, 500);
}
?>
<script type="text/javascript">
<!--
if (typeof(ga) !== 'undefined') {
	ga('send', 'event', 'Bookingfor - Search', 'Search', '<?php echo $trackstr ?>');
}
	
//-->
</script>
<?php } ?>
<div id="bfi-merchantlist"> 
	<div>
	<?php 
	if (!empty($variationPlanIds )) {
	    
	$offers = json_decode(BFCHelper::getDiscountDetails($variationPlanIds,$language));
	
	foreach ($offers as $offer ) {
		if (!empty($offer)){ ?>
		<div class="bfi-content">
			<div class="bfi-title-name"><h1><?php echo  $offer->Name?></h1> </div>
			<?php if (!empty($offer->Description)) {?>
			<div class="bfi-description bfi-shortentextlong">
					<?php echo BFCHelper::getLanguage($offer->Description, $language, null, array( 'striptags'=>'striptags', 'bbcode'=>'bbcode','ln2br'=>'ln2br')); ?>
			</div>
			<?php } ?>
		</div>
		<div class="bfi-clearfix "></div>
		<?php 
			} 
		}
	}
	?>

<?php if ($total > 0){ ?>
	<?php 
			$results = $items ;
			bfi_get_template("search/default_results.php",array("results"=>$results,"totalAvailable"=>$totalAvailable,"total"=>$total,"items"=>$items,"pages"=>$pages,"page"=>$page,"currencyclass"=>$currencyclass,"listNameAnalytics"=>$listNameAnalytics,"totPerson"=>$totPerson,"currSorting"=>$currSorting));	
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
			'add_args'        => false,
			'add_fragment'    => ''
		  );



		global $bfi_query_arg;
		$bfi_query_arg = array();
		add_filter( 'get_pagenum_link', 'bfi_remove_date_get_pagenum_link',1 );
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
				<?php 
				if($isportal ){
//					$merchantCategories = get_post_meta($post->ID, 'merchantcategories', true);
//					$rating = get_post_meta($post->ID, 'rating', true);
//					$cityids = get_post_meta($post->ID, 'cityids', true);
//					$currURL = esc_url( get_permalink() ); 
//

$page = bfi_get_current_page() ;
$start = ($page - 1) * COM_BOOKINGFORCONNECTOR_ITEMPERPAGE;			

if (empty($GLOBALS['bfSearchedMerchants'])) {
					
					$model = new BookingForConnectorModelMerchants;
					$model->populateState();	
					$model->setItemPerPage(COM_BOOKINGFORCONNECTOR_ITEMPERPAGE);

					$filter_order = $model->getOrdering();
					$filter_order_Dir = $model->getDirection();

					$currParam = $model->getParam();
					$pars = BFCHelper::getSearchParamsSession();

					$merchantCategories = array();
					$tmpMerchantCategories = array();

					if(!empty($pars) && isset($pars{"merchantCategoryId"})){
						if(is_array($pars{"merchantCategoryId"})){
							$tmpMerchantCategories =  $pars{"merchantCategoryId"};
						}else{
							array_push($tmpMerchantCategories,$pars{"merchantCategoryId"});
						}						
					}
										
					foreach($tmpMerchantCategories as $merchantCategory){
						if (strpos($merchantCategory, '|') !== false) {
							$aMerchantCategory = explode('|',$merchantCategory);
							
							array_push($merchantCategories,$aMerchantCategory[1]);
						}else{
							array_push($merchantCategories,$merchantCategory);
						}
					}
					$cityids = [];
					if(!empty($pars) && isset($pars{"cityIds"})){
						$cityids = [$pars["cityIds"]];
					}

					$currParam['categoryId'] = !empty($merchantCategories)?$merchantCategories:[];
					$currParam['rating'] = !empty($rating)?$rating:'';
					$currParam['cityids'] = !empty($cityids)?$cityids:[];
					$model->setParam($currParam);
						
					$items = $model->getItems(false, false, $start,COM_BOOKINGFORCONNECTOR_ITEMPERPAGE);
					$total = $model->getTotal();

					$GLOBALS['bfSearchedMerchantsItems'] = $items;
					$GLOBALS['bfSearchedMerchantsItemsTotal'] = $total;
					$GLOBALS['bfSearchedMerchantsItemsCurrSorting'] = $currSorting;
					$GLOBALS['bfSearchedMerchants'] = 1;
}else{
					$items = $GLOBALS['bfSearchedMerchantsItems'];
					$total = $GLOBALS['bfSearchedMerchantsItemsTotal'];
					$currSorting = $GLOBALS['bfSearchedMerchantsItemsCurrSorting'];
}
										
					$merchants = is_array($items) ? $items : array();
					$analiticsSubject = "'No Results Merchant List'";
					$nopopupmap=true;
					if (count((array)$merchants)>0) {
					?>
										<div class="bfi-content">
											<div class="bfi-check-more" data-type="merchant" data-id="<?php echo $merchants[0]->MerchantId?>" >
												<?php _e('Limited availability, but may sell out:', 'bfi') ?>
												<div class="bfi-check-more-slider">
												</div>
											</div>
										</div>

					<?php 
					}
					bfi_get_template("merchantslist/merchantslist.php",array("merchants"=>$merchants,"total"=>$total,"items"=>$items,"pages"=>$pages,"page"=>$page,"currencyclass"=>$currencyclass,"analiticsSubject"=>$analiticsSubject,"nopopupmap"=>$nopopupmap,"filter_order"=>$filter_order,"filter_order_Dir"=>$filter_order_Dir ));	
				}else{
					$showcontactbanner = COM_BOOKINGFORCONNECTOR_SHOWCONTACTBANNER;
					if ($showcontactbanner) {
						bfi_get_template("shared/contact_banner.php",array("showcontactbanner"=>$showcontactbanner));
						$showcontactbanner  = false; // lo faccio vedere solo una volta
					    
					}else{
						$url_merchant_page = BFCHelper::GetPageUrl('merchantdetails');

						$merchants = BFCHelper::getMerchantsSearch(null,0,1,null,null);
						if(!empty( $merchants )){
							foreach ($merchants as $merchant){							
								$routeMerchant = $url_merchant_page . $merchant->MerchantId.'-'.BFI()->seoUrl($merchant->Name);
								$routeRating = $routeMerchant .'/'._x('reviews', 'Page slug', 'bfi' );
								$routeRating = $routeMerchant .'/'._x('reviews', 'Page slug', 'bfi' );
								$routeInfoRequest = $routeMerchant .'/'._x('contactspopup', 'Page slug', 'bfi' );
								?>
									<a class="boxedpopup bfi-btn" href="<?php echo $routeInfoRequest?>" style="width: 100%;"><?php echo  _e('Request info' , 'bfi') ?></a>

								<?php 
							}
						}
					}

					
				}
				?>
		</div>
</div>

<?php } ?>
	<div class="bfi-clearfix"></div>		
</div>
<script type="text/javascript">
<!--

jQuery(document).ready(function() {
	if(typeof jQuery.fn.button.noConflict !== 'undefined'){
		var btn = jQuery.fn.button.noConflict(); // reverts $.fn.button to jqueryui btn
		jQuery.fn.btn = btn; // assigns bootstrap button functionality to $.fn.btn
	}
	
	setTimeout(function () {
		bookingfor.bfiCheckOtherAvailability(<?php echo $duration ?>,'<?php echo $checkin->format('Ymd'); ?>',<?php echo $paxes ?>,'<?php echo implode('|',$paxages) ?>','<?php echo $resourcegroupId ?>','<?php echo $availabilitytype ?>','<?php echo $itemtypes ?>',false, null, null, '<?php echo $merchantCategoryId ?>', '<?php echo $masterTypeId ?>', '<?php echo $merchantTagIds ?>', '<?php echo $points ?>',);
	}, 1);

});

//-->
</script>
<div class="bfilastSearch"></div>
<script type="text/javascript">
<!--

//jQuery(document).ready(function() {
//		var currSearch = {
//			id:'<?php echo uniqid() ?>',
//			checkin: '<?php echo $checkin->format('d/m/Y'); ?>',
//			checkout: '<?php echo $checkout->format('d/m/Y'); ?>',
//			duration: <?php echo $duration ?>,
//			paxes:  <?php echo $paxes ?>,
//			nad:  <?php echo $nad ?>,
//			nch:  <?php echo $nch ?>,
//			nse:  <?php echo $nse ?>,
//			minqt:  <?php echo $minqt ?>,
//			paxages:  '<?php echo implode('|',$paxages) ?>',
//			url: document.location.href
//		}
//	bookingfor.bfiAddLastSearched(currSearch);
//	bfiShowLastSearch('.bfilastSearch');
//});

//-->
</script>

