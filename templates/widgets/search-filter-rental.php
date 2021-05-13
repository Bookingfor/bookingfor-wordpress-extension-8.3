<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if (COM_BOOKINGFORCONNECTOR_ISBOT) {
    return '';
}
if(BFI()->isSearchPage() || (isset($_GET) && !empty($_GET["resultinsamepg"]))){

    $base_url = get_site_url();
    $language = $GLOBALS['bfi_lang'];
    $languageForm ='';
    if(defined('ICL_LANGUAGE_CODE') &&  class_exists('SitePress')){
		global $sitepress;
		if($sitepress->get_current_language() != $sitepress->get_default_language()){
			$languageForm = "/" .ICL_LANGUAGE_CODE;
		}
    }

    // se events.....
    if(isset($_GET['events'])) {
        bfi_get_template("widgets/search-filter-events.php");
    }else{

        $currencyclass = bfi_get_currentCurrency();

        $paymodes_text = array('freecancellation' => __('Free cancellation', 'bfi'),
                                'freepayment' => __('No prepayment', 'bfi'),
                                'freecc' => __('Book without credit card', 'bfi')
                            );
        $meals_text = array('ai' => __('All inclusive', 'bfi'),
                                'fb' => __('Full board', 'bfi'),
                                'hb' => __('Half board', 'bfi'),
                                'bb' => __('Breakfast included', 'bfi')
                            );
        $rating_text = array('null' => __('Unrated', 'bfi'),
                                '-1' => __('Unrated', 'bfi'),
                                '0' => __('Unrated', 'bfi'),
                                '1' => __('1 star', 'bfi'),
                                '2' => __('2 stars', 'bfi'),
                                '3' => __('3 stars', 'bfi'),
                                '4' => __('4 stars', 'bfi'),
                                '5' => __('5 stars', 'bfi'),
                                '6' => __('6 stars', 'bfi'),
                                '7' => __('7 stars', 'bfi'),
                                '8' => __('8 stars', 'bfi'),
                                '9' => __('9 stars', 'bfi'),
                                '10' => __('10 stars', 'bfi'),
                            );
        $avg_text = array('-1' => __('Unrated', 'bfi'),
                                '0' => __('Very poor', 'bfi'),
                                '1' => __('Poor', 'bfi'),
                                '2' => __('Disappointing', 'bfi'),
                                '3' => __('Fair', 'bfi'),
                                '4' => __('Okay', 'bfi'),
                                '5' => __('Pleasant', 'bfi'),
                                '6' => __('Good', 'bfi'),
                                '7' => __('Very good', 'bfi'),
                                '8' => __('Fabulous', 'bfi'),
                                '9' => __('Exceptional', 'bfi'),
                                '10' => __('Exceptional', 'bfi'),
                            );


//        $formAction = (isset($_SERVER['HTTPS']) ? "https" : "http") . ':' ."//" .$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$formAction = "//$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $page = bfi_get_current_page() ;
        if(!empty($page)){
            $formAction = str_replace('/page/'.$page."/","/",$formAction);
        }

        $formAction = filter_input( INPUT_GET, 'newsearch' )
               ? remove_query_arg( 'newsearch', $formAction )
               : $formAction;

		$sessionkeysearch = 'search.params.rental';

		bfi_setSessionFromSubmittedData($sessionkeysearch);

        $pars = BFCHelper::getSearchParamsSession($sessionkeysearch);

        $newsearch = isset($pars['newsearch']) ? $pars['newsearch'] : '0';
		$groupResultType = isset($pars['groupresulttype']) ? $pars['groupresulttype'] : 0;

        if (empty($GLOBALS['bfSearched'])) {
            if($newsearch == "1"){
                BFCHelper::setFilterSearchParamsSession(null,$sessionkeysearch);
                $searchmodel = new BookingForConnectorModelSearch;
                $items =  array();
                $total = 0;
                $totalAvailable = 0;
                $currSorting = "";
                $filterinsession = null;
                $start = 0;
                if (isset($pars['checkin']) && isset($pars['checkout'])){
                    $now = new DateTime('UTC');
                    $now->setTime(0,0,0);
                    $checkin = isset($pars['checkin']) ? $pars['checkin'] : new DateTime('UTC');
                    $checkout = isset($pars['checkout']) ? $pars['checkout'] : new DateTime('UTC');
                    $availabilitytype = isset($pars['availabilitytype']) ? $pars['availabilitytype'] : "1";

                    $availabilitytype = explode(",",$availabilitytype);
                    if (($checkin == $checkout && (!in_array("0",$availabilitytype) && !in_array("2",$availabilitytype)&& !in_array("3",$availabilitytype) ) ) || $checkin->diff($checkout)->format("%a") <0 || $checkin < $now ){
                        $nodata = true;
                    }else{
                        $filterinsession = BFCHelper::getFilterSearchParamsSession($sessionkeysearch);
                        $items = $searchmodel->getItems(false, false, $start,COM_BOOKINGFORCONNECTOR_ITEMPERPAGE,$sessionkeysearch);

                        $items = is_array($items) ? $items : array();

                        $total=$searchmodel->getTotal($sessionkeysearch);
                        $totalAvailable=$searchmodel->getTotalAvailable($sessionkeysearch);
                        $currSorting=$searchmodel->getOrdering() . "|" . $searchmodel->getDirection();
                    }

                }
                $GLOBALS['bfSearchedItems'] = $items;
                $GLOBALS['bfSearchedItemsTotal'] = $total;
                $GLOBALS['bfSearchedItemsTotalAvailable'] = $totalAvailable;
                $GLOBALS['bfSearchedItemsCurrSorting'] = $currSorting;
                $GLOBALS['bfSearched'] = 1;
            }

			$filtersselected = BFCHelper::getVar('filters', null);
			if ($filtersselected == null) { //provo a recuperarli dalla sessione...
				$filtersselected = BFCHelper::getFilterSearchParamsSession($sessionkeysearch);
			}
			BFCHelper::setFilterSearchParamsSession($filtersselected,$sessionkeysearch);
        }

        $filtersSelected = BFCHelper::getFilterSearchParamsSession($sessionkeysearch);
        $firstFilters = BFCHelper::getFirstFilterSearchParamsSession($sessionkeysearch);
        $currentFilters = BFCHelper::getEnabledFilterSearchParamsSession($sessionkeysearch);

    $minvaluetoshow=0;
	$searchid = isset($_GET['searchid']) ? $_GET['searchid'] : '';
	$searchtypetab = isset($_GET['searchtypetab']) ? $_GET['searchtypetab'] : '';;
	$currFilterOrder = "";
	$currFilterOrderDirection = "";
	if (!empty($currSorting) &&strpos($currSorting, '|') !== false) {
		$acurrSorting = explode('|',$currSorting);
		$currFilterOrder = $acurrSorting[0];
		$currFilterOrderDirection = $acurrSorting[1];
	}

    $orderFiltersView = Array('isbookable', 'productcategory', 'price', 'resrating', 'mrcrating', 'grouprating',
		'distance', 'reszones', 'mrczones', 'groupzones', 'poi',  'resavg', 'mrcavg', 'groupavg', 'tags',
		'meals',
		//'merchantcategory'
		//'tags',
		//'bedrooms',
		//'checkavailability',
		//'offer',
		//'paymodes',
		);


	if (!empty($firstFilters) && count($firstFilters)>0) {
        $firstFilters = array_values(array_filter($firstFilters));

		foreach ($firstFilters as $filter) {
			if (empty($filter->PropertyName)) {
				$filter->PropertyName = $filter->Name;
			}
			$editedFilter = $filter;
			$currPropTag = explode("_",$filter->PropertyName);
			$editedFilter->PropertyNameReformatted = $currPropTag[0];
			if (strpos($filter->PropertyName, "tags_1") === 0) $editedFilter->PropertyNameReformatted = "mrctags";
			if (strpos($filter->PropertyName, "tags_4") === 0) $editedFilter->PropertyNameReformatted = "restags";
			if (strpos($filter->PropertyName, "tags_8") === 0) $editedFilter->PropertyNameReformatted = "grouptags";

			$editedFilter->FiltersViewOrder = array_search($currPropTag[0], $orderFiltersView);
			if ($editedFilter->FiltersViewOrder !== 0 && empty($editedFilter->FiltersViewOrder)) $editedFilter->FiltersViewOrder = -1;
			$currFilterValues = null;
			foreach($currentFilters as $itm) {
				if ($itm->PropertyName == $filter->PropertyName) {
					$currFilterValues = $itm->Items;
					break;
				}
			}

			//echo "<pre>currFilterValues";
			//print_r($currFilterValues);
			//echo "</pre>";


			if (!empty($filter->Items)) {
				$allItems = array_merge(array(), $filter->Items);
				$filterSelected = !empty( $filtersSelected[$editedFilter->PropertyNameReformatted]);
				$currfilter = explode (",", $filterSelected ? str_replace("|", ",", $filtersSelected[$editedFilter->PropertyNameReformatted]) : "");
				//$currfilter = explode (",",$filterSelected ? $filtersSelected[$editedFilter->PropertyNameReformatted] : "");
				foreach ($allItems as $item ) {
					$item->Selected = in_array(strval($item->Id), $currfilter, true);
					if(!empty($currFilterValues)) {
						$currCount = null;
						foreach($currFilterValues as $itm) {
							if ($itm->Id == $item->Id) {
								$currCount = $itm->Count;
								break;
							}
						}
						//$item->Count = 0;
						if (!$filterSelected || $filter->FilterType == 1) $item->Count = 0;
						if ($currCount != null) $item->Count = $currCount;
					} else {
						$item->Count = 0;
					}
				}

				switch ($editedFilter->PropertyNameReformatted) {
					case 'merchantcategory':
					case 'productcategory':
					case 'groupcategory':
					case 'category':
						usort($allItems, function($a, $b)
						{
							return strcmp($a->Name,$b->Name);
						});
						break;
				}
				$editedFilter->Items = $allItems;
			}
			$filter = $editedFilter;
		}
    }else{
		$firstFilters =[];
    }

	$orderFiltersPosition =[];
	foreach ($firstFilters as $key => $value) {
		$currPropTag = explode("_",$value->PropertyName );
		$value->PropertyNameReformatted = $currPropTag[0];
		$orderFiltersPosition[] = $value->ViewOrder;
	}
    //Ordinamento filtri in base al peso
    usort($firstFilters, function($a, $b)
    {
        return $a->ViewOrder>$b->ViewOrder;
    });
	$newfilter = [];
	foreach ($orderFiltersView as $orderView) {
		foreach ($firstFilters as $key => $value) {
			$currPropTag = explode("_",$value->PropertyName );
			if ($currPropTag[0] == $orderView) {
				$newfilter[] = $value;
				unset($firstFilters[$key]);
			}
		}
	}
	$firstFilters = array_merge($newfilter, $firstFilters);
	array_multisort(array_column($firstFilters, 'FiltersViewOrder'), SORT_ASC, array_column($firstFilters, 'ViewOrder'), SORT_ASC, $firstFilters);
//	array_multisort($orderFiltersView, SORT_ASC, $orderFiltersPosition, SORT_ASC, $firstFilters);

/*
echo "<pre>";
print_r($firstFilters);
echo "</pre>";
*/
	if (!empty($before_widget)) {
        echo $before_widget;
    }
$currFiltersMrctagsSelected = array();
$currFiltersRestagsSelected = array();
$currFiltersGrouptagsSelected = array();
$query = array();
$urlparts = parse_url($formAction);
if (!empty($urlparts['query'])) {
	parse_str($urlparts['query'], $query);
}
?>
<div class="bfi-searchfilter">
    <h3>
        <?php _e('Filter by', 'bfi'); ?>
    </h3>
    <form action="<?php echo $urlparts['path']; ?>" method="get" id="searchformfilter" name="searchformfilter">
		<?php 
		foreach($query as $key => $value) {
			if (!in_array($key, array("newsearch", "limitstart", "filter_order", "filter_order_Dir", "originalpoints", "extras")) && strpos($key, "filters") !== 0) { ?>
			<input type="hidden" value="<?php echo $value; ?>" name="<?php echo $key; ?>" />
		<?php }
		}
		?>
		
        <input type="hidden" value="0" name="limitstart" />
        <input type="hidden" value="0" name="newsearch" />
        <input type="hidden" name="filter_order" class="filterOrder" id="filter_order_filter" value="<?php echo $currFilterOrder ?>" />
        <input type="hidden" name="filter_order_Dir" class="filterOrderDirection" id="filter_order_Dir_filter" value="<?php echo $currFilterOrderDirection ?>" />
        <input type="hidden" name="originalpoints" value="<?php echo $newsearch == "1" ? (!empty($query["points"])?$query["points"] : "" ) : (!empty($query["originalpoints"])?$query["originalpoints"] : "" ); ?>" />
		
        <div class="bfi-filtercontainer">

		<?php
			if (!empty($firstFilters) && count($firstFilters)>0) {	
			foreach ($firstFilters as $itemId => $filter){
				if (empty($filter->PropertyName)) {
					$filter->PropertyName = $filter->Name;		    
				}
				$propName = $filter->PropertyName;
				$currTitle = $filter->Name;
				if ($filter->FiltersViewOrder === -1) continue;
				if (stripos($filter->PropertyName,'tags_')!== false) {
					$currTitle = ucfirst(__($filter->Name, 'bfi'));
					$currPropTag = explode("_",$filter->PropertyName);				
					switch ($currPropTag[1]) {
						case "1":
							$propName = "mrctags";
							break;
						case "4":
							$propName = "restags";
							break;
						case "8":
							$propName = "grouptags";
							break;
					}
				}
					
				$showFilters = true;
				switch ($propName) {
					case "reszones":
					case "resavg":
					case "resrating":
					//case "restags":
						if ($groupResultType != 0) { $showFilters = false; }
						if (count($filter->Items) <= ($minvaluetoshow+1)) { $showFilters = false; }
						break;
					case "mrczones":
					case "mrcavg":
					case "mrcrating":
						if ($groupResultType != 1) { $showFilters = false; }
						if (count($filter->Items) <= ($minvaluetoshow+1)) { $showFilters = false; }
						break;
					case "mrctags":
						if ($groupResultType == 0) { $showFilters = false; }
						break;
					case "groupzones":
					case "groupavg":
					case "grouprating":
						if ($groupResultType != 2) { $showFilters = false; }
						break;
					case "grouptags":
						if ($groupResultType != 2) { $showFilters = false; }
						if (count($filter->Items) <= ($minvaluetoshow+1)) { $showFilters = false; }
						break;
					case "isbookable":
						 $showFilters = false;					
						if (is_array($filter->Items) && count($filter->Items)>0 && $filter->Items[0]->Count >0 ) { $showFilters = true; }
						break;
					case "price":
					case "productcategory":
					case "merchantcategory":
					case "paymodes":
					case "poi":
						//if (count($filter->Items) <= $minvaluetoshow) { $showFilters = false; }
						break;
					case "meals":
					case "bedrooms":
					case "groupcategory": // ---->>>> filtri bloccati
						$showFilters = false;
						break;

				}
				switch ($propName) {
					case "reszones":
					case "mrczones":
					case "groupzones":
						$currTitle = __('Destination', 'bfi');
						break;
					case "mrcavg":
					case "resavg":
					case "groupavg":
						$currTitle = __('Supplier rating', 'bfi');
						break;
					case "mrcrating":
					case "resrating":
					case "grouprating":
						$currTitle = __('Star rating', 'bfi');
						break;
					case "price":
						$currTitle = __('Budget', 'bfi');
						break;
					case "productcategory":
						$currTitle = __('Tipology', 'bfi');
						break;
					case "merchantcategory":
						$currTitle = __('Property type', 'bfi');
						break;
					case "meals":
						$currTitle = __('Meals', 'bfi');
						break;
	//				case "tags":
	//					$currTitle = __('Budget', 'bfi');
	//					break;
					case "bedrooms":
						$currTitle = __('Rooms', 'bfi');
						break;
					case "paymodes":
						$currTitle = __('Book with ease ', 'bfi');
						break;
					case "offer":
						$currTitle = __('Offer', 'bfi');
						break;
					case "checkavailability":
						$currTitle = __('Availability', 'bfi');
						break;
					case "isbookable":
						$currTitle = __('Booking type', 'bfi');
						break;
					case "poi":
						$currTitle = __('Nearby points of interest', 'bfi');
						break;
					case "distance":
						if (!empty($pars['searchTermValue']) && strpos($pars['searchTermValue'], "cityIds|") === 0) { 
							$currTitle = __('Distance from center', 'bfi');
						} else if (!empty($pars['searchTermValue']) && strpos($pars['searchTermValue'], "poiIds|") === 0) {
							$currTitle = __('Distance from point of interest', 'bfi');
						}	
						break;
				}

				if ( count($filter->Items) <= $minvaluetoshow) {
					$showFilters = false;
				}
				  $currFiltersSelected =  array();
				?>
				<div class="bfi-filter-group <?php echo !$showFilters?"bfi-hide":"";?>" rel="<?php echo $filter->PropertyName?>">
					<div class="bfi-option-title bfi-option-active">
						<?php echo $currTitle ?>
					</div>
					<div class="bfi-filteroptions">
						<?php
				  //Ordinamento filtri in base al peso
				  usort($filter->Items , function($a, $b)
				  {
					  return $a->ViewOrder>$b->ViewOrder;
				  });
				  $viewResidual = false;
				if ($newsearch == "1" && in_array(true, array_map(function ($t) { return $t->Selected; }, $filter->Items))  && $filter->FilterType == 0) {
					//$viewResidual = true;
				}
				foreach ($filter->Items  as $currItem){
					
					if ($currItem->Selected) {
						switch ($propName) {
							case "mrctags":
								$currFiltersMrctagsSelected[]=$currItem->Id;
								break;
							case "restags":
								$currFiltersRestagsSelected[]=$currItem->Id;
								break;
							case "grouptags":
								$currFiltersGrouptagsSelected[]=$currItem->Id;
								break;
							default:
								$currFiltersSelected[]=$currItem->Id;
								break;
						}
					}
					//print_r($currItem);
					$currLabel = $currItem->Name;
					$currLabelClass ="";
					switch ($filter->PropertyName) {
						case "reszones":
						case "mrczones":
						case "groupzones":
							break;
						case "mrcavg":
						case "resavg":
						case "groupavg":
							$currLabel = $avg_text[$currItem->Name];
							break;
						case "mrcrating":
						case "resrating":
						case "grouprating":
								$rating = intval($currItem->Name);
								if (!isset($currItem->Sup)) {
									$currItem->Sup = "";
								}
								if ($rating>9 )
								{
									if(($rating%10)>0){
										$currItem->Sup = "S";
									}
									$rating = $rating/10;
									$currItem->Name = intval($rating);
								}										
							$currLabel = $rating_text[$currItem->Name].$currItem->Sup ;
							break;
						case "price":
							$currLabelClass = "bfi_".$currencyclass;
							$currItemValue = explode(";",$currItem->Id);
							$currLabel = (!empty( $currItemValue[0] ))? BFCHelper::priceFormat($currItemValue[0]) :BFCHelper::priceFormat(0);
							$currLabel .= (!empty( $currItemValue[1] ))? " - <span class=' bfi_".$currencyclass."' >" . BFCHelper::priceFormat($currItemValue[1])."</span>" :"+";
							break;
						case "productcategory":
							break;
						case "merchantcategory":
							break;
						case "meals":
							$currLabel = $meals_text[$currItem->Name];
							break;
		//				case "tags":
		//					$currTitle = __('Budget', 'bfi');
		//					break;
						case "bedrooms":
							if ($currItem->Id == "-1") $currLabel = __('Unspecified', 'bfi');
							//$currTitle = __('Rooms', 'bfi');
							break;
						case "paymodes":
							$currLabel = $paymodes_text[$currItem->Name];
							break;
						case "offer":
							$currLabel = __('Smart offer', 'bfi');
							break;
						case "checkavailability":
							$currLabel = __('Show only available resources', 'bfi');
							break;
						case "isbookable":
							$currLabel = __('Instant Book', 'bfi');
							break;
						case "distance":
							$currItemValue = explode(";",$currItem->Id);
							if (empty($currItemValue[0]) && !empty($currItemValue[1])) {
								$currLabel = __('Up to', 'bfi') . " " . BFCHelper::formatDistanceUnits(intval($currItemValue[1]));
							}
							break;
					}
					  $viewCount = $viewResidual?($currItem->ResidualCount >0 ? "+" . $currItem->ResidualCount : ""): ($currItem->Count >0 ? $currItem->Count : "")
						?>
						<a href="javascript:void(0);" rel="<?php echo $currItem->Id ?>" rel1="<?php echo  $propName?>" data-list="Search Filters" class="<?php echo $currItem->Selected ?"bfi-filter-active":"";?> " <?php echo empty($viewCount) ? 'style="display: none;"' : ''; ?>>
							<span class="bfi-filter-label <?php echo $currLabelClass; ?>">
								<?php echo $currLabel; ?>
							</span>
							<span class="bfi-filter-count">
								<?php echo $viewCount;?> 
							</span>
						</a>
						<?php
				}
						?>
					</div>
				</div>

				<?php
				  $currFiltersSelected = implode(",", $currFiltersSelected );
				  if (stripos($filter->PropertyName,'tags_')=== false) {
				?>
				<input type="hidden" rel="filters[<?php echo $filter->PropertyName ?>]" name="filters[<?php echo $propName?>]" id="filtersHidden<?php echo $filter->PropertyName ?>" value="<?php echo $currFiltersSelected ?>" />
				<?php
				  }
          }
          }
            ?>
            <input type="hidden" rel="filters[mrctags]" name="filters[mrctags]" id="filtersHiddenmrctags" value="<?php echo implode(",", $currFiltersMrctagsSelected )?>" />
            <input type="hidden" rel="filters[restags]" name="filters[restags]" id="filtersHiddenrestags" value="<?php echo implode(",", $currFiltersRestagsSelected )?>" />
            <input type="hidden" rel="filters[grouptags]" name="filters[grouptags]" id="filtersHiddengrouptags" value="<?php echo implode(",", $currFiltersGrouptagsSelected )?>" />

        </div>
    </form>
</div>
<br />

<script type="text/javascript">
ajaxFormAction = '<?php echo $formAction; ?>' + '';

function bfi_applyfilterdata(){

	jQuery('#searchformfilter').submit(function( event ) {
		try
		{
			jQuery("#filter_order_filter").val(jQuery("#bookingforsearchFilterForm input[name='filter_order']").val());
			jQuery("#filter_order_Dir_filter").val(jQuery("#bookingforsearchFilterForm input[name='filter_order_Dir']").val());

		}
		catch (e)
		{
		}
		var allFilters = {};
		jQuery("a.bfi-filter-active").each(function (i, itm) {
			if (typeof allFilters[jQuery(itm).attr("rel1")] === "undefined") {
				allFilters[jQuery(itm).attr("rel1")] = [];
			}
			var currentGroup = jQuery.grep(allFilters[jQuery(itm).attr("rel1")], function (gr) {
				return gr.group == jQuery(itm).closest(".bfi-filter-group").attr("rel");
			});
			if (currentGroup.length > 0) {
				currentGroup[0].value += (jQuery(itm).attr("rel1") == "price" ? '|' : ',') + jQuery(itm).attr("rel");
			} else {
				allFilters[jQuery(itm).attr("rel1")].push({
					group: jQuery(itm).closest(".bfi-filter-group").attr("rel"),
					value: jQuery(itm).attr("rel"),
				});
			}
		});
		jQuery("#searchformfilter").find("input[rel^='filters\\[']").val("");
		jQuery("#searchformfilter").find("[name=points]").val(jQuery("#searchformfilter").find("[name=originalpoints]").val());
		jQuery.each(Object.keys(allFilters), function (i, itm) {
			if (itm == "distance") {
				var selectedValue = allFilters[itm][0].value;
				var basePosition = jQuery("#searchformfilter").find("[name=searchTermValue]").val().split("|")[1];
					jQuery("#searchformfilter").find("[name=points]").val("0|" + basePosition.split(":")[1] + " " + basePosition.split(":")[2] + " " + selectedValue.split(";")[1]);
			}
			if (itm == "poi") {
				var selectedValue = allFilters[itm][0].value;
				jQuery("#searchformfilter").find("[name=points]").val("0|" + selectedValue.split(";")[1] + " " + selectedValue.split(";")[2]);
			}
			jQuery("#searchformfilter").find("input[rel='filters\\[" + itm + "\\]']").val(allFilters[itm].map(function (gr) { return gr.value; }).join('|'));
		});
		jQuery('body').block({
			message: "",
			overlayCSS: { backgroundColor: '#ffffff', opacity: 0.7 }
		});
	});
	if (jQuery.prototype.masonry){
		jQuery('.main-siderbar, .main-siderbar1').masonry('reload');
	}
}

jQuery(document).ready(function() {
	bfi_applyfilterdata();
});


</script>
<?php 
if (!empty($after_widget)) {
	echo $after_widget;
    }
?>
<div class="bfi-clearfix"></div>
<?php
    }

//}else{
//	if( is_single() && get_post_type() == 'eventlist' ) {
//		bfi_get_template("widgets/search-filter-events.php"); 
//	}
} ?>
<?php bfi_get_template("widgets/search-filter-merchants.php"); ?>