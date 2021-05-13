<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if (COM_BOOKINGFORCONNECTOR_ISBOT) {
    return '';
}

if (!empty($showfilter) || ( is_single() && get_post_type() == 'merchantlist' )) {

$base_url = get_site_url();
$language = $GLOBALS['bfi_lang'];
$languageForm ='';
if(defined('ICL_LANGUAGE_CODE') &&  class_exists('SitePress')){
		global $sitepress;
		if($sitepress->get_current_language() != $sitepress->get_default_language()){
			$languageForm = "/" .ICL_LANGUAGE_CODE;
		}
}
$groupResultType = isset($pars['groupresulttype']) ? $pars['groupresulttype'] : 1;

$currencyclass = bfi_get_currentCurrency();

//$searchAvailability_page = get_post( bfi_get_page_id( 'searchavailability' ) );
//$formAction = get_permalink( $searchAvailability_page->ID );

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


$formAction = (isset($_SERVER['HTTPS']) ? "https" : "http") . ':' ."//" .$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

$page = bfi_get_current_page() ;
if(!empty($page)){
	$formAction = str_replace('/page/'.$page."/","/",$formAction);
}
$currSorting = "";


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

					$currParam['categoryId'] = !empty($merchantCategories)?$merchantCategories:[];
					$currParam['rating'] = !empty($rating)?$rating:'';
					$currParam['cityids'] = !empty($cityids)?$cityids:[];
					$model->setParam($currParam);


					$total = $model->getTotal();
					$items = $model->getItems();

					$GLOBALS['bfSearchedMerchantsItems'] = $items;
					$GLOBALS['bfSearchedMerchantsItemsTotal'] = $total;
					$GLOBALS['bfSearchedMerchantsItemsCurrSorting'] = $currSorting;
					$GLOBALS['bfSearchedMerchants'] = 1;

}

$formAction = filter_input( INPUT_GET, 'newsearch' )
       ? remove_query_arg( 'newsearch', $formAction )
       : $formAction;

$pars = BFCHelper::getSearchMerchantParamsSession();
$newsearch = isset($pars['newsearch']) ? $pars['newsearch'] : '0';

$merchantCategoryId = isset($pars['merchantCategoryId']) ? $pars['merchantCategoryId'] : '';
//$searchid = isset($_GET['searchid']) ? $_GET['searchid'] : '';
$searchid = !empty($pars['searchid']) ? $pars['searchid'] : uniqid('', true);


$filtersSelected = BFCHelper::getFilterSearchMerchantParamsSession();
$firstFilters = BFCHelper::getFirstFilterSearchMerchantParamsSession();
$currentFilters = BFCHelper::getEnabledFilterSearchMerchantParamsSession();
$minvaluetoshow=1;
    if (!empty($firstFilters) && count($firstFilters)>0) {
        $firstFilters = array_values(array_filter($firstFilters));
        foreach ($firstFilters as $filter){
            if (!empty($filter->Name)) {
           switch ($filter->PropertyName) {
                case stripos($filter->PropertyName,'mrctags') !== false:

					if(!empty( $filter->Items )){
						$currfilter = explode (",",!empty( $filtersSelected[ 'mrctags' ] ) ? $filtersSelected[ 'mrctags' ] : "");
                        $allItems = $filter->Items;
                        usort($allItems, function($a, $b)
                        {
                            return strcmp($a->Name,$b->Name);
                        });
//                        $currfilter = explode (",",!empty( $filtersSelected[ 'tags' ] ) ? $filtersSelected[ 'tags' ] : "");
                        foreach ($allItems as $item ) {
                            $item->Selected = in_array(strval($item->Id),$currfilter);
                        }
                    }
                    break;
                case 'mrccategory':
                    if(!empty( $filter->Items )){
                        $allItems = $filter->Items;
                        usort($allItems, function($a, $b)
                        {
                            return strcmp($a->Name,$b->Name);
                        });
                        $currfilter = explode (",",!empty( $filtersSelected[ 'mrccategory' ] ) ? $filtersSelected[ 'mrccategory' ] : "");
                        foreach ($allItems as $item ) {
                            $item->Selected = in_array(strval($item->Id), $currfilter, true);
                        }
                    }
                    break;
                case 'mrczones':
                    if(!empty( $filter->Items )){
                        $currfilter = explode (",",!empty( $filtersSelected[ 'mrczones' ] ) ? $filtersSelected[ 'mrczones' ] : "");
                        foreach ($filter->Items as $item ) {
                            $item->Selected = in_array(strval($item->Id), $currfilter, true);
                        }
                    }
                    break;
                case 'mrcrating':
                    if(!empty( $filter->Items )){
                        $currfilter = explode (",",!empty( $filtersSelected[ 'mrcrating' ] ) ? $filtersSelected[ 'mrcrating' ] : "");
                        foreach ($filter->Items as $item ) {
                            $item->Selected = in_array(strval($item->Id), $currfilter, true);
                        }
                    }
                    break;
                case 'mrcavg':
                    if(!empty( $filter->Items )){
                        $currfilter = explode (",",!empty( $filtersSelected[ 'mrcavg' ] ) ? $filtersSelected[ 'mrcavg' ] : "");
                        foreach ($filter->Items as $item ) {
                            $item->Selected = in_array(strval($item->Id), $currfilter, true);
                        }
                    }
                    break;

                }

            }
        }
    $orderFiltersView = Array('mrccategory', 'mrcrating', 'mrcavg', 'mrctags', 'mrczones');
	$orderFiltersPosition =[];
    //Ordinamento filtri in base al peso

	usort($firstFilters, function($a, $b)
    {
        return $a->ViewOrder>$b->ViewOrder;
    });
	foreach ($firstFilters as $key => $value) {
		$currPropTag = explode("_",$value->PropertyName );
		$value->PropertyNameReformatted = $currPropTag[0];
		$orderFiltersPosition[] = $value->ViewOrder;
	}
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

//	array_multisort($orderFiltersView, SORT_ASC, $orderFiltersPosition, SORT_ASC, $firstFilters);
	if (!empty($before_widget)) {
        echo $before_widget;
    }
	$currFiltersMrctagsSelected = array();

?>
<div class="bfi-searchfilter">
	<h3><?php _e('Filter by', 'bfi'); ?></h3>
	<form action="<?php echo $formAction; ?>" method="post" id="searchMerchantformfilter" name="searchMerchantformfilter" >
	<input type="hidden" value="<?php echo $searchid ?>" name="searchid">
	<input type="hidden" value="0" name="limitstart">
	<input type="hidden" value="0" name="newsearch">
	<input type="hidden" name="filter_order" class="filterOrder" id="filter_order_filter" value="stay">
	<input type="hidden"  name="filter_order_Dir" class= "filterOrderDirection"id="filter_order_Dir_filter" value="asc">
	<div class="bfi-filtercontainer">
 		<?php foreach ($firstFilters as $itemId => $filter){
			$propName = $filter->PropertyName;
			$currTitle = $filter->Name;
//			if (stripos($filter->PropertyName,'mrctags')!== false) {
//				$currTitle = ucfirst(__($filter->Name, 'bfi'));
//			}
              if (stripos($filter->PropertyName,'mrctags_')!== false) {
                  $currTitle = ucfirst(__($filter->Name, 'bfi'));
				  $propName = "mrctags";
              }
				
			$showFilters = true;
			switch ($filter->PropertyName) {
				case "mrczones":
				case "mrcavg":
				case "mrcrating":
					if ($groupResultType != 1 || count($filter->Items) <= $minvaluetoshow) { $showFilters = false; }
					break;
				case "mrccategory":
				case "mrctags":
					if (count($filter->Items) <= $minvaluetoshow) { $showFilters = false; }
					break;

			}
			switch ($filter->PropertyName) {
				case "mrczones":
					$currTitle = __('Destination', 'bfi');
					break;
				case "mrcavg":
					$currTitle = __('Review score', 'bfi');
					break;
				case "mrcrating":
					$currTitle = __('Star rating', 'bfi');
					break;
				case "mrccategory":
					$currTitle = __('Property type', 'bfi');
					break;
				case "meals":
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
				$viewResidual = true;
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
				$currLabel = $currItem->Name;
				$currLabelClass ="";
				switch ($filter->PropertyName) {
					case "mrczones":
						break;
					case "mrcavg":
						$currLabel = $avg_text[$currItem->Name];
						break;
					case "mrcrating":
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
					case "mrccategory":
						break;
				}
				$viewCount = $viewResidual?($currItem->ResidualCount >0 ? "+" . $currItem->ResidualCount : ""): ($currItem->Count >0 ? $currItem->Count : "")
				?>
                    <a href="javascript:void(0);" rel="<?php echo $currItem->Id ?>" rel1="<?php echo  $propName?>" data-list="Search Filters" class="<?php echo $currItem->Selected ?"bfi-filter-active":"";?> ">
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
              if (stripos($filter->PropertyName,'mrctags')=== false) {
            ?>
            <input type="hidden" rel="filters[<?php echo $filter->PropertyName ?>]" name="filters[<?php echo $propName?>]" id="filtersHidden<?php echo $filter->PropertyName ?>" value="<?php echo $currFiltersSelected ?>" />
            <?php
              }
          }
            ?>
            <input type="hidden" rel="filters[mrctags]" name="filters[mrctags]" id="filtersHiddenmrctags" value="<?php echo implode(",", $currFiltersMrctagsSelected )?>" />

        </div>
    </form>
</div>
<br />

<script type="text/javascript">
function bfi_applyfilterMerchantdata(){ 		
	jQuery('#searchMerchantformfilter').submit(function( event ) {
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
		jQuery("#searchMerchantformfilter").find("input[rel^='filters\\[']").val("");
		jQuery.each(Object.keys(allFilters), function (i, itm) {
			jQuery("#searchMerchantformfilter").find("input[rel='filters\\[" + itm + "\\]']").val(allFilters[itm].map(function (gr) { return gr.value; }).join('|'));
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
	bfi_applyfilterMerchantdata();
});  
</script>
<?php
if (!empty($after_widget)) {
	echo $after_widget;    
}
?>
<div class="bfi-clearfix"></div>
<?php }
    }

?>
