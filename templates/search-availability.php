<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
	if(!empty( COM_BOOKINGFORCONNECTOR_CRAWLER )){
		$listCrawler = json_decode(COM_BOOKINGFORCONNECTOR_CRAWLER , true);
		foreach( $listCrawler as $key=>$crawler){
		if (preg_match('/'.$crawler['pattern'].'/', $_SERVER['HTTP_USER_AGENT'])) exit;
		}
		
	}
$currencyclass = bfi_get_currentCurrency();
if (COM_BOOKINGFORCONNECTOR_USE_OPENSTREETMAP) {
	wp_enqueue_script('bfileaflet');
	wp_enqueue_script('bfileafletcontrolcustom');
}

//if(!isset($_GET['task'])) {
if(!isset($_POST['format'])) {
	$page = bfi_get_current_page() ;
	$start = ($page - 1) * COM_BOOKINGFORCONNECTOR_ITEMPERPAGE;
	$listName = "";
	$listNameAnalytics = 0;

	//switch layout
	$layoutresult= ( ! empty( $_REQUEST['resview'] ) ) ? ($_REQUEST['resview']) : ''; 
	$sessionkeysearch = 'search.params';
	switch ($layoutresult) {
		case 'rental':
			$sessionkeysearch = 'search.params.rental';
			break;
		case 'mapsells':
			$sessionkeysearch = 'search.params.mapsells';
			break;
		case 'slot':
			$sessionkeysearch = 'search.params.slot';
			break;
		case 'experience':
			$sessionkeysearch = 'search.params.experience';
			break;
		default:      
	}
	bfi_setSessionFromSubmittedData($sessionkeysearch);
	$searchmodel = new BookingForConnectorModelSearch;
			
	$pars = BFCHelper::getSearchParamsSession($sessionkeysearch);
	$filterinsession = null;

	$items =  array();
	$total = 0;
	$currSorting = "";
	$totalAvailable = 0;
	$paxages = array();
	$nrooms = 1;
	$searchterm = '';

	if (isset($pars['checkin']) && isset($pars['checkout'])){
		$now = new DateTime('UTC');
		$now->setTime(0,0,0);
		$checkin = isset($pars['checkin']) ? $pars['checkin'] : null;
		$checkout = isset($pars['checkout']) ? $pars['checkout'] : null;
		$paxages = !empty($pars['paxages'])? $pars['paxages'] :  array('18','18');
		$nrooms = !empty($pars['minrooms'])? $pars['minrooms'] :  1;
		$searchterm = !empty($pars['searchterm']) ? $pars['searchterm'] :'';

		$availabilitytype = isset($pars['availabilitytype']) ? $pars['availabilitytype'] : "1";
	
		$availabilitytype = explode(",",$availabilitytype);
		//if (!empty($pars['checkAvailability']) && ((!in_array("0",$availabilitytype) && !in_array("2",$availabilitytype)&& !in_array("3",$availabilitytype) ) || $checkin->diff($checkout)->format("%a") <0 || $checkin < $now)) {
		if (!empty($pars['checkAvailability']) && !empty($checkin) && ($checkin->diff($checkout)->format("%a") <0 || $checkin < $now)) {
			$nodata = true;
		}else{
			if (empty($GLOBALS['bfSearched'])) {
				
				$filterinsession = BFCHelper::getFilterSearchParamsSession($sessionkeysearch);
				$items = $searchmodel->getItems(false, false, $start,COM_BOOKINGFORCONNECTOR_ITEMPERPAGE, $sessionkeysearch);
				
				$items = is_array($items) ? $items : array();
						
				$total=$searchmodel->getTotal($sessionkeysearch);
				$totalAvailable=$searchmodel->getTotalAvailable($sessionkeysearch);
				$currSorting=$searchmodel->getOrdering() . "|" . $searchmodel->getDirection();
				$GLOBALS['bfSearched'] = 1;
			}else{ 
				$items = $GLOBALS['bfSearchedItems'];
				$total = $GLOBALS['bfSearchedItemsTotal'];
				$totalAvailable = $GLOBALS['bfSearchedItemsTotalAvailable'];
				$currSorting = $GLOBALS['bfSearchedItemsCurrSorting'];
			}
		}

	}

	// calcolo persone
	$nad = 0;
	$nch = 0;
	$nse = 0;
	$countPaxes = 0;
	$maxchildrenAge = (int)BFCHelper::$defaultAdultsAge-1;

	$nchs = array(null,null,null,null,null,null);

	if (empty($paxages)){
		$nad = 2;
		$paxages = array(BFCHelper::$defaultAdultsAge, BFCHelper::$defaultAdultsAge);

	}else{
		if(is_array($paxages)){
			$countPaxes = array_count_values($paxages);
			$nchs = array_values(array_filter($paxages, function($age) {
				if ($age < (int)BFCHelper::$defaultAdultsAge)
					return true;
				return false;
			}));
		}
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

	get_header( 'searchavailability' ); 
	do_action( 'bookingfor_before_main_content' );
	?>
					<div class="bfi-row bfi-searchresults bfi-rowcontainer">
						<div class="bfi-col-md-3 bfi-col-sm-3 bfi-col-xs-12  bfi-sidebar">
							<?php 

								if (COM_BOOKINGFORCONNECTOR_ISMOBILE) {
									$currParam = BFCHelper::getSearchParamsSession($sessionkeysearch);
							?>

								<div class="bfi-summary-search">
									<?php if(!empty($searchterm)) { ?>
									<div class="bfi-summary-search-searchterm">
										<i class="fa fa-search"></i> <?php echo $searchterm ?>
									</div>
									<?php } ?>
									
									<div class="bfi-summary-search-other">
										<?php if(!empty($checkin)) { ?>										
											<i class="fa fa-calendar-alt"></i> <?php echo $checkin->format("d") . ' ' . date_i18n('M',$checkin->getTimestamp())?> 
										<?php 
										if ($availabilitytype != 3 && $checkin!=$checkout) {
										     echo  ' - ' . $checkout->format("d") . ' ' . date_i18n('M',$checkout->getTimestamp());
//										     echo  ' <b>(' . $checkout->diff($checkin)->format('%d') . ' ' . __('nights', 'bfi') . ')</b> ';

										}
										?>
										<?php } ?>
										<span class="bfi-summary-search-persons">
											<span id="bfi-room-info-calculator" class="bfi-comma bfi-hide"><i class="fa fa-caret-down"></i> <span><?php echo $nrooms ?></span> <?php _e('Resource', 'bfi'); ?></span>
											<i class="fa fa-user"></i> <span id="bfi-adult-info-calculator" class="bfi-comma"><span><?php echo $nad ?></span> <?php _e('Adults', 'bfi'); ?></span>
											<?php if($nse>0) { ?>
												<span id="bfi-senior-info-calculator" class="bfi-comma"><span><?php echo $nse ?></span> <?php _e('Seniores', 'bfi'); ?></span>
											<?php } ?>
											<?php if($nch>0) { ?>
												<span id="bfi-child-info-calculator" class="bfi-comma"><span><?php echo $nch ?></span> <?php _e('Children', 'bfi'); ?> (<?php echo implode(',', array_slice($nchs,0,$nch)) ?>)</span>
											<?php } ?>
										</span>
									</div>
								</div>
							<div class="bfisearchdialog">		
							<?php 

									switch ($layoutresult) {
										case 'rental':
											dynamic_sidebar('bfisidebarrental'); 
											break;
										case 'slot':
											dynamic_sidebar('bfisidebarslot'); 
											break;
										case 'experience':
											dynamic_sidebar('bfisidebarexperience'); 
											break;
										case 'mapsells':
											dynamic_sidebar('bfisidebarmapsells'); 
											break;
										default:      
											dynamic_sidebar('bfisidebar'); 
									}
										
							?>
							</div>
	<ul class="bfi-menu-search">
		<li><div class="bfiopenpopupsors"><i class="fas fa-exchange-alt fa-rotate-90"></i> <?php echo _e('Order by' , 'bfi') ?>
		<select class="bfi-orderby-content" name="currsorting" style="display: block; opacity: 0; position: absolute; left: 0; top: 0; height: 100%; padding: 0 10px; width: 100%; font-size: 16px">
			<option value="" rel="" style="display:none;"  ><?php echo _e('Lowest price first' , 'bfi'); ?></option>
			<option value="price|asc" rel="price|asc" <?php echo $currSorting=="price|asc" ? 'selected="selected"': '' ; ?>  ><?php echo _e('Lowest price first' , 'bfi'); ?></option>
			<option value="rating|asc" rel="rating|asc" <?php echo $currSorting=="rating|asc" ? 'selected="selected"': '' ; ?>  ><?php echo _e('Review score' , 'bfi'); ?></option>
			<option value="offer|asc" rel="offer|asc" <?php echo $currSorting=="offer|asc" ? 'selected="selected"': '' ; ?>  ><?php echo _e('Best offers' , 'bfi'); ?></option>
							<?php if($currParam != null && !empty($currParam['points'])) { 
								if (!empty($currParam['searchTermValue']) && strpos($currParam['searchTermValue'], "cityIds|") === 0) { ?>
								<option value="distance|asc" rel="distance|asc" <?php echo $currSorting=="distance|asc" ? 'selected="selected"': '' ; ?>  ><?php echo _e('Distance from center' , 'bfi'); ?></option>
								<?php
								} else if (!empty($currParam['searchTermValue']) && strpos($currParam['searchTermValue'], "poiIds|") === 0) { ?>
								<option value="distance|asc" rel="distance|asc" <?php echo $currSorting=="distance|asc" ? 'selected="selected"': '' ; ?>  ><?php echo _e('Distance from point of interest' , 'bfi'); ?></option>
								<?php 
								}
							}			
							?>
		</select>
			</div>
		</li>
		<li><a class="bfi-panel-toggle"><i class="fas fa-filter"></i> <?php echo _e('Filter' , 'bfi') ?></a></li>
		<li><a class="bfiopenpopupmap"><i class="fas fa-map-marker-alt"></i> <?php echo _e('Map' , 'bfi') ?></a></li>
	</ul>
	<span class="bficurrentfilter"></span>
<script type="text/javascript">
var dialogForm;
var bfi_wuiP_width= 800;

function bfishowsearch() {
	if(jQuery(window).width()<bfi_wuiP_width){
		bfi_wuiP_width = jQuery(window).width();
	}
	if (!!jQuery.uniform){
		jQuery.uniform.restore(jQuery("#bfi-calculatorForm select"));
	}
	dialogForm = jQuery( ".bfisearchdialog" ).dialog({
			closeText: "",
			title:"<?php _e('Change your details', 'bfi'); ?>",
			autoOpen: false,
			width:bfi_wuiP_width,
			modal: true,
			position: ['center',0], 
			dialogClass: 'bfi-dialog bfi-dialog-search',
//			clickOutside: true,

	});
	dialogForm.dialog( "open" );
}

jQuery(document).ready(function() {
	jQuery(".bfi-summary-search").on('click tap', function (e) {
		if (typeof dialogForm !=='undefined' && dialogForm.hasClass("ui-dialog-content"))
		{
			dialogForm.dialog( "close" ).dialog('destroy');
		}

		bfishowsearch();
	});

	var currDetailsFiltered = [];
	var currFilterActive = jQuery(".bfi-orderby-content option:selected").first();
	if(currFilterActive.length && jQuery(".bfi-orderby-content").val() != "" ){
		currDetailsFiltered.push("<span>" + currFilterActive.html() + ' <i class="fa fa-times-circle bfi-removesort" aria-hidden="true" ></i></span>' );
	}

	jQuery('.bfi-option-title').each(function(){
		var currFilterActive = jQuery(this).parent("div").first().find(".bfi-filter-active");
		if(currFilterActive.length){
			var currfilter = [];
			currFilterActive.each(function(){
				var rel = jQuery(this).attr("rel");
				var rel1 = jQuery(this).attr("rel1");
				currfilter.push("<span>" +jQuery(this).find(".bfi-filter-label").first().html() + ' <i class="fa fa-times-circle bfi-removefilter" aria-hidden="true" rel="'+rel+'" rel1="'+rel1+'"></i></span>' );
			});
//			currDetailsFiltered.push(jQuery(this).text() + ": " + currfilter.join(", "));
			currDetailsFiltered.push(currfilter.join(" "));
		}
	});
	if (currDetailsFiltered.length){
		jQuery('.bficurrentfilter').append( currDetailsFiltered.join(" "));
	}else{
		jQuery('.bficurrentfilter').hide();
	}
	jQuery('.bfi-orderby-content').change(function() {
		var rel = jQuery(this).val();
		var vals = rel.split("|"); 
		jQuery('#bookingforsearchFilterForm .filterOrder').val(vals[0]);
		jQuery('#bookingforsearchFilterForm .filterOrderDirection').val(vals[1]);

		if(jQuery('#searchformfilter').length){
			jQuery('#searchformfilter').submit();
		}else{
			jQuery('#bookingforsearchFilterForm').submit();
		}
	});
	jQuery('.bfi-removesort').click(function() {
		jQuery('#bookingforsearchFilterForm .filterOrder').val("");
		jQuery('#bookingforsearchFilterForm .filterOrderDirection').val("");

		if(jQuery('#searchformfilter').length){
			jQuery('#searchformfilter').submit();
		}else{
			jQuery('#bookingforsearchFilterForm').submit();
		}
	});

});
jQuery(window).load(function() {
	if (!!jQuery.uniform){
	jQuery.uniform.restore(jQuery('.bfi-menu-search select'));
	}
});

</script>
	<div class="bfifilterlisttab">
		<div class="bfi-slide-panel">
			<div class="bfi-slide-panel-title"><span class="bfi-slide-panel-title-span"><?php _e('Filters', 'bfi'); ?></span><span class="bfi-panel-close bfi-panel-toggle"></span></div>
			<?php bfi_get_template("widgets/search-filter.php"); ?>
			<div class="bfi-slide-panel-bottom"><span class="bfi-btn bfi-panel-toggle"><?php echo sprintf( __('Show %s results', 'bfi'),$totalAvailable ); ?></span></div>
		</div>
	</div>
								    
								<?php 
																	    								    								    
								}else{
									switch ($layoutresult) {
										case 'rental':
											dynamic_sidebar('bfisidebarrental'); 
											bfi_get_template("widgets/smallmap.php");	
											bfi_get_template("widgets/search-filter-rental.php");	
											break;
										case 'mapsells':
											dynamic_sidebar('bfisidebarmapsells'); 
											bfi_get_template("widgets/smallmap.php");	
											bfi_get_template("widgets/search-filter-mapsells.php");	
											break;
										case 'slot':
											dynamic_sidebar('bfisidebarslots'); 
											bfi_get_template("widgets/smallmap.php");	
											bfi_get_template("widgets/search-filter-slot.php");	
											break;
										case 'experience':
											dynamic_sidebar('bfisidebarexperience'); 
											bfi_get_template("widgets/smallmap.php");	
											bfi_get_template("widgets/search-filter-experience.php");	
											break;
										default:      
											dynamic_sidebar('bfisidebar'); 
											bfi_get_template("widgets/smallmap.php");	
											bfi_get_template("widgets/search-filter.php");	
									}

								}
							?>
							<div class="bfilastmerchants bfi-hide">
								<h3><?php _e('Recently seen', 'bfi') ?></h3>
							</div>
						</div>
						<div class="bfi-col-md-9 bfi-col-xs-12 bfi-col-sm-9  bfi-body">

		<?php if ( apply_filters( 'bookingfor_show_page_title', true ) ) { ?>
		<?php } ?>
	<?php
}

$pages = ceil($total / COM_BOOKINGFORCONNECTOR_ITEMPERPAGE);

$merchant_ids = '';


		$currParam = BFCHelper::getSearchParamsSession($sessionkeysearch);
		$merchantResults = $currParam['merchantResults'];
		$resourcegroupsResults = $currParam['resourcegroupsResults'];
		$totPerson = (isset($currParam)  && isset($currParam['paxes']))? $currParam['paxes']:0 ;
/*-- criteo --*/
$criteoConfig = null;
if(COM_BOOKINGFORCONNECTOR_CRITEOENABLED){
		$merchantsCriteo = array();
		if(!empty($items)) {
			$merchantsCriteo = array_unique(array_map(function($a) { return $a->MerchantId; }, $items));
		}
		$criteoConfig = BFCHelper::getCriteoConfiguration(1, $merchantsCriteo);
		if(isset($criteoConfig) && isset($criteoConfig->enabled) && $criteoConfig->enabled && count($criteoConfig->merchants) > 0) {
			echo '<script type="text/javascript" src="//static.criteo.net/js/ld/ld.js" async="true"></script>';
			echo '<script type="text/javascript"><!--
			';
			echo ('window.criteo_q = window.criteo_q || []; 
			var deviceTypeCriteo = /iPad/.test(navigator.userAgent) ? "t" : /Mobile|iP(hone|od)|Android|BlackBerry|IEMobile|Silk/.test(navigator.userAgent) ? "m" : "d";
			window.criteo_q.push( 
				{ event: "setAccount", account: '. $criteoConfig->campaignid .'}, 
				{ event: "setSiteType", type: deviceTypeCriteo }, 
				{ event: "setEmail", email: "" }, 
				{ event: "viewSearch", checkin_date: "' . $pars["checkin"]->format('Y-m-d') . '", checkout_date: "' . $pars["checkout"]->format('Y-m-d') . '"},
				{ event: "viewList", item: ' . json_encode($criteoConfig->merchants) .' }
			);');
			echo "//--></script>";
		}	
	
}

/*-- criteo --*/

		$totalItems = array();
		$sendData = true;
			
		if(!empty($items)) {
			if($merchantResults) {
//				$resIndex = 0;
				$listNameAnalytics = 9; // old 1;
				$listName = BFCHelper::$listNameAnalytics[$listNameAnalytics];//"Merchants Group List";
				$itmCounter = 1;
				foreach($items as $itemkey => $itemValue) {
					$listResources = array();
					foreach ($itemValue->Results as $keyRes=>$singleResource) {
						$id = $singleResource->ResourceId;

						if (isset($listResources[$id])) {
							$listResources[$id][] = $singleResource;
						} else {
							$listResources[$id] = array($singleResource);
						}
					}

					array_multisort(array_map('count', $listResources), SORT_DESC, $listResources);
					foreach ($listResources as $resourceId=>$singleResource) // foreach $listMerchantsCart
					{
						$currResource = $singleResource[0];
						$objRes = new stdClass();
						$objRes->MerchantId = $currResource->MerchantId;
						$objRes->MrcName = $itemValue->MerchantName;
						$objRes->MrcCategoryName = $itemValue->DefaultLangCategoryName;
						$objRes->Position = $itmCounter;// $resIndex;
						$objRes->Id = $currResource->ResourceId . " - Resource";
						$objRes->Name = $itemValue->Name;
						$itmCounter++;
											
						$totalItems[] = $objRes;
					}
				}
			} else if ($resourcegroupsResults) {
//				$sendData = false;
				$resIndex = 0;
				$listNameAnalytics = 2;
				$listName = BFCHelper::$listNameAnalytics[$listNameAnalytics];// "Resources Group List";
				
				$itmCounter = 1;
				foreach($items as $itemkey => $itemValue) {
					$listResources = array();
					foreach ($itemValue->Results as $keyRes=>$singleResource) {
						$id = $singleResource->ResourceId;

						if (isset($listResources[$id])) {
							$listResources[$id][] = $singleResource;
						} else {
							$listResources[$id] = array($singleResource);
						}
					}

					array_multisort(array_map('count', $listResources), SORT_DESC, $listResources);
					foreach ($listResources as $resourceId=>$singleResource) // foreach $listMerchantsCart
					{
						$currResource = $singleResource[0];
						$objRes = new stdClass();
						$objRes->MerchantId = $currResource->MerchantId;
						$objRes->MrcName = $itemValue->MerchantName;
						$objRes->MrcCategoryName = $itemValue->DefaultLangCategoryName;
						$objRes->Position = $itmCounter;// $resIndex;
						$objRes->Id = $currResource->ResourceId . " - Resource";
						$objRes->Name = $itemValue->Name;
						$itmCounter++;
											
						$totalItems[] = $objRes;
					}
				}
				
			} else {
				$listNameAnalytics = 3;
				$listName = BFCHelper::$listNameAnalytics[$listNameAnalytics];// "Resources Search List";
				foreach($items as $mrckey => $mrcValue) {
					$obj = new stdClass();
					$obj->Id = $mrcValue->ItemId . " - Resource";
					$obj->MerchantId = $mrcValue->MerchantId;
					$obj->MrcCategoryName = $mrcValue->DefaultLangCategoryName;
					$obj->Name = $mrcValue->Name;
					$obj->MrcName = $mrcValue->MerchantName;
					$obj->Position = $mrckey;
					$totalItems[] = $obj;
				}
			}
		}

		$analyticsEnabled = COM_BOOKINGFORCONNECTOR_GAENABLED == 1 && !empty(COM_BOOKINGFORCONNECTOR_GAACCOUNT) && COM_BOOKINGFORCONNECTOR_EECENABLED == 1;
//		if(count($totalItems) > 0 && $analyticsEnabled) {
		add_action('bfi_head', 'bfi_google_analytics_EEc', 10, 1);
		do_action('bfi_head', $listName);
		if(count($totalItems) > 0 && $analyticsEnabled) {

			$allobjects = array();
			$initobjects = array();
			foreach ($totalItems as $key => $value) {
				$obj = new stdClass;
				$obj->id = "" . $value->Id;
				if(isset($value->GroupId) && !empty($value->GroupId)) {
					$obj->groupid = $value->GroupId;
				}
				$obj->name = $value->Name;
				$obj->category = $value->MrcCategoryName;
				$obj->brand = $value->MrcName;
				$obj->position = $value->Position;
				if(!isset($value->ExcludeInitial) || !$value->ExcludeInitial) {
					$initobjects[] = $obj;
				} else {
					///$obj->merchantid = $value->MerchantId;
					//$allobjects[] = $obj;
				}
			}
//			$document->addScriptDeclaration('var currentResources = ' .json_encode($allobjects) . ';
//			var initResources = ' .json_encode($initobjects) . ';
//			' . ($sendData ? 'callAnalyticsEEc("addImpression", initResources, "list");' : ''));
			echo '<script type="text/javascript"><!--
			';
			echo ('var currentResources = ' .json_encode($allobjects) . ';
			var initResources = ' .json_encode($initobjects) . ';
			' . ($sendData ? 'callAnalyticsEEc("addImpression", initResources, "list");' : ''));
			echo "//--></script>";

		}
		
		//event tracking	
?>
 <?php

	$page = bfi_get_current_page() ;


	switch ($layoutresult) {
	    case 'rental':
			bfi_get_template("search/rental.php",array("totalAvailable"=>$totalAvailable,"total"=>$total,"items"=>$items,"pages"=>$pages,"page"=>$page,"currencyclass"=>$currencyclass,"listNameAnalytics"=>$listNameAnalytics,"totPerson"=>$totPerson,"currSorting"=>$currSorting));	
	        break;
	    case 'mapsells':
			bfi_get_template("search/mapsells.php",array("totalAvailable"=>$totalAvailable,"total"=>$total,"items"=>$items,"pages"=>$pages,"page"=>$page,"currencyclass"=>$currencyclass,"listNameAnalytics"=>$listNameAnalytics,"totPerson"=>$totPerson,"currSorting"=>$currSorting));	
	        break;
	    case 'slot':
			bfi_get_template("search/slot.php",array("totalAvailable"=>$totalAvailable,"total"=>$total,"items"=>$items,"pages"=>$pages,"page"=>$page,"currencyclass"=>$currencyclass,"listNameAnalytics"=>$listNameAnalytics,"totPerson"=>$totPerson,"currSorting"=>$currSorting));	
	        break;
	    case 'experience':
			bfi_get_template("search/experience.php",array("totalAvailable"=>$totalAvailable,"total"=>$total,"items"=>$items,"pages"=>$pages,"page"=>$page,"currencyclass"=>$currencyclass,"listNameAnalytics"=>$listNameAnalytics,"totPerson"=>$totPerson,"currSorting"=>$currSorting));	
	        break;
		default:      
			bfi_get_template("search/default.php",array("totalAvailable"=>$totalAvailable,"total"=>$total,"items"=>$items,"pages"=>$pages,"page"=>$page,"currencyclass"=>$currencyclass,"listNameAnalytics"=>$listNameAnalytics,"totPerson"=>$totPerson,"currSorting"=>$currSorting));	
	}

if(!isset($_POST['format'])) {

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

						</div>
						</div>

<?php get_footer( 'searchavailability' ); ?>
					</div>
<?php
}
?>	
