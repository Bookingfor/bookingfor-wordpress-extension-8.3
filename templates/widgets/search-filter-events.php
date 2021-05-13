<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if (COM_BOOKINGFORCONNECTOR_ISBOT) {
    return '';
}
if(BFI()->isSearchEventsPage() || !empty($showfilter) || ( is_single() && get_post_type() == 'eventlist' )) {

    $base_url = get_site_url();
    $language = $GLOBALS['bfi_lang'];
    $languageForm ='';
    if(defined('ICL_LANGUAGE_CODE') &&  class_exists('SitePress')){
		global $sitepress;
		if($sitepress->get_current_language() != $sitepress->get_default_language()){
			$languageForm = "/" .ICL_LANGUAGE_CODE;
		}
    }

    $formAction = (isset($_SERVER['HTTPS']) ? "https" : "http") . ':' ."//" .$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

    bfi_setSessionFromSubmittedDataEvent();

	$page = bfi_get_current_page() ;
    if(!empty($page)){
        $formAction = str_replace('/page/'.$page."/","/",$formAction);
    }
    $currSorting = "";
    $pars = BFCHelper::getSearchEventParamsSession();


    $newsearch = isset($pars['newsearch']) ? $pars['newsearch'] : '0';
	$groupResultType = isset($pars['groupresulttype']) ? $pars['groupresulttype'] : 0;

	$searchid = !empty($pars['searchid']) ? $pars['searchid'] : uniqid('', true);

    if (empty($GLOBALS['bfEventSearched'])) {
        if($newsearch == "1"){
            BFCHelper::setFilterSearchEventParamsSession(null);
            $searchmodel = new BookingForConnectorModelSearchEvent;
            $items =  array();
            $total = 0;
            $currSorting = "";
            $start = 0;

            $now = new DateTime('UTC');
            $now->setTime(0,0,0);
            $checkin = isset($pars['checkin']) ? $pars['checkin'] : new DateTime('UTC');
            $checkout = isset($pars['checkout']) ? $pars['checkout'] : new DateTime('UTC');

            if ( $checkin == $checkout || $checkin->diff($checkout)->format("%a") <0 || $checkin < $now ){
                $nodata = true;
            }else{
                $items = $searchmodel->getItems(false, false, $start,COM_BOOKINGFORCONNECTOR_ITEMPERPAGE);
                $items = is_array($items) ? $items : array();
                $total=$searchmodel->getTotal();
                $currSorting=$searchmodel->getOrdering() . "|" . $searchmodel->getDirection();
            }

            $GLOBALS['bfEventSearchedItems'] = $items;
            $GLOBALS['bfEventSearchedItemsTotal'] = $total;
            $GLOBALS['bfEventSearchedItemsCurrSorting'] = $currSorting;
            $GLOBALS['bfEventSearched'] = 1;
        }else{
            $filtersselected = BFCHelper::getVar('filters', null);

            if ($filtersselected == null) { //provo a recuperarli dalla sessione...
                $filtersselected = BFCHelper::getFilterSearchEventParamsSession();
            }
            BFCHelper::setFilterSearchEventParamsSession($filtersselected);
        }
    }



    $formAction = filter_input( INPUT_GET, 'newsearch' )
           ? remove_query_arg( 'newsearch', $formAction )
           : $formAction;

    $pars = BFCHelper::getSearchEventParamsSession();


    //$filtersZones = array();
    //$filtersCategories = array();

    $filtersSelected = BFCHelper::getFilterSearchEventParamsSession();
    $firstFilters = BFCHelper::getFirstFilterSearchEventParamsSession();
    $currentFilters = BFCHelper::getEnabledFilterSearchEventParamsSession();

    $minvaluetoshow=1;

    if (!empty($firstFilters) && count($firstFilters)>0) {
        $firstFilters = array_values(array_filter($firstFilters));
        foreach ($firstFilters as $filter){
            if (!empty($filter->Name)) {
           switch ($filter->PropertyName) {
                case stripos($filter->PropertyName,'tags_') !== false:
                    if(!empty( $filter->Items )){
                        $allItems = $filter->Items;
                        usort($allItems, function($a, $b)
                        {
                            return strcmp($a->Name,$b->Name);
                        });

                        $currfilter = explode (",",!empty( $filtersSelected[ 'tags' ] ) ? str_replace("|",",",$filtersSelected[ 'tags' ]) : "");
						foreach ($allItems as $item ) {
                            $item->Selected = in_array(strval($item->Id), $currfilter, true);
                        }
                    }
                    break;
                case 'category':
                    if(!empty( $filter->Items )){
                        $allItems = $filter->Items;
                        usort($allItems, function($a, $b)
                        {
                            return strcmp($a->Name,$b->Name);
                        });
                        $currfilter = explode (",",!empty( $filtersSelected[ 'category' ] ) ? $filtersSelected[ 'category' ] : "");
                        foreach ($allItems as $item ) {
                            $item->Selected = in_array(strval($item->Id), $currfilter, true);
                        }
                    }
                    break;
                case 'zones':
                    if(!empty( $filter->Items )){
                        $currfilter = explode (",",!empty( $filtersSelected[ 'zones' ] ) ? $filtersSelected[ 'zones' ] : "");
                        foreach ($filter->Items as $item ) {
                            $item->Selected = in_array(strval($item->Id), $currfilter, true);
                        }
                    }
                    break;
                case 'poi':
                    if(!empty( $filter->Items )){
                        $currfilter = explode (",",!empty( $filtersSelected[ 'poi' ] ) ? $filtersSelected[ 'poi' ] : "");
                        foreach ($filter->Items as $item ) {
                            $item->Selected = in_array(strval($item->Id), $currfilter, true);
                        }
                    }
                    break;
                }
            }
        }
    }

    $orderFiltersView = Array('category', 'zones');
    //Ordinamento filtri in base al peso
    usort($firstFilters, function($a, $b)
    {
        return $a->ViewOrder>$b->ViewOrder;
    });

    if (!function_exists("customShiftOrder"))
    {
        function customShiftOrder($array, $nameOrder){
            foreach($array as $key => $val){     // loop all elements


                if($val->PropertyName == $nameOrder){             // check for id $id
                    unset($array[$key]);         // unset the $array with id $id
                    array_unshift($array, $val); // unshift the array with $val to push in the beginning of array
                    return $array;               // return new $array
                }
            }
        }
    }

    foreach ($orderFiltersView as $orderView) {
        if (!empty($firstFilters))
        {
            $firstFilters = customShiftOrder($firstFilters, $orderView);
        }
    }

    if (!empty($before_widget)) {
        echo $before_widget;
    }
	$currFiltersTagsSelected = array();
?>
<div class="bfi-searchfilter">
    <h3>
        <?php _e('Filter by', 'bfi'); ?>
    </h3>
    <form action="<?php echo $formAction; ?>" method="get" id="searchEventformfilter" name="searchEventformfilter">
        <input type="hidden" value="<?php echo $searchid ?>" name="searchid" />
        <input type="hidden" value="<?php echo uniqid('', true)?>" name="events" />
        <input type="hidden" value="0" name="limitstart" />
        <input type="hidden" value="0" name="newsearch" />
        <input type="hidden" name="filter_order" class="filterOrder" id="filter_order_filter" value="stay" />
        <input type="hidden" name="filter_order_Dir" class="filterOrderDirection" id="filter_order_Dir_filter" value="asc" />
        <div class="bfi-filtercontainer">

            <?php foreach ($firstFilters as $itemId => $filter){
              
			  $propName = $filter->PropertyName;
              $currTitle = $filter->Name;
              if (stripos($filter->PropertyName,'tags_')!== false) {
                  $currTitle = ucfirst(__($filter->Name, 'bfi'));
				  $propName = "tags";
              }

              $showFilters = true;
              switch ($filter->PropertyName) {
                  case "zones":
                       $currTitle = __('Zones', 'bfi');
                       if ( count($filter->Items) <= $minvaluetoshow) { $showFilters = false; }
                      break;
                case "category":
                      if ( count($filter->Items) <= $minvaluetoshow) { $showFilters = false; }
                       $currTitle = __('Category', 'bfi');
                      break;
				case "poi":
                      if ( count($filter->Items) <= $minvaluetoshow) { $showFilters = false; }
                      $currTitle = __('Points of interest', 'bfi');
                      break;
              }
              //		if ( count($filter->Items) <= $minvaluetoshow) {
              //			$showFilters =false;
              //		}
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
                    $viewResidual = true;
                }
              foreach ($filter->Items  as $currItem){
                  if ($currItem->Selected) {
					switch ($propName) {
						case "tags":
							$currFiltersTagsSelected[]=$currItem->Id;
							break;
						default:
							$currFiltersSelected[]=$currItem->Id;
							break;
					}
                  }

                  $viewCount = $viewResidual?($currItem->ResidualCount >0 ? "+" . $currItem->ResidualCount : ""): ($currItem->Count >0 ? $currItem->Count : "")
                    ?>
                    <a href="javascript:void(0);" rel="<?php echo $currItem->Id ?>" rel1="<?php echo  $propName?>" data-list="Event Filters" class="<?php echo $currItem->Selected ?"bfi-filter-active":"";?> ">
                        <span class="bfi-filter-label">
                            <?php echo $currItem->Name; ?>
                        </span>
                        <span class="bfi-filter-count">
                            (<?php echo $viewCount;?>)
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
            ?>
            <input type="hidden" rel="filters[tags]" name="filters[tags]" id="filtersHiddentags" value="<?php echo implode(",", $currFiltersTagsSelected )?>" />
        </div>
    </form>
</div>
<script type="text/javascript">
function bfi_applyfilterEventdata(){

	jQuery('#searchEventformfilter').submit(function( event ) {
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
		jQuery("#searchEventformfilter").find("input[rel^='filters\\[']").val("");
		jQuery.each(Object.keys(allFilters), function (i, itm) {
			jQuery("#searchEventformfilter").find("input[rel='filters\\[" + itm + "\\]']").val(allFilters[itm].map(function (gr) { return gr.value; }).join(','));
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
	bfi_applyfilterEventdata();
});
</script>

<?php
if (!empty($after_widget)) {
	echo $after_widget;
    }
?>
<div class="bfi-clearfix"></div>
<?php } ?>