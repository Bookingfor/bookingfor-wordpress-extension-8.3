<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
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


$formAction = (isset($_SERVER['HTTPS']) ? "https" : "http") . ':' ."//" .$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

$duration = 1;
$groupResultType = 0;
if (empty($duration)) {
	$duration =1;
}

$searchid = isset($_GET['searchid']) ? $_GET['searchid'] : '';
$allmerchants = BFCHelper::getVar('allmerchants',0);

//i possibili filtri passati dalla ricersa
$filtersSelected = BFCHelper::getFilterDetailsParamsSession($cachekey);
$firstFilters = BFCHelper::getFirstFilterDetailsParamsSession($cachekey);

$isMerchantResults=0;

$currentFilters = BFCHelper::getEnabledFilterDetailsParamsSession($cachekey);
    $minvaluetoshow=0;
    if (!empty($firstFilters) && count($firstFilters)>0) {
        foreach ($firstFilters as $filter){
            if (!empty($filter->Name)) {
           switch ($filter->PropertyName) {
                case stripos($filter->PropertyName,'tags_') !== false:
                    
					if(!empty( $filter->Items )){
						$currfilter = array();
						$currPropTag = explode("_",$filter->PropertyName);				
						switch ($currPropTag[1]) {
							case "1":
								$currfilter = explode (",",!empty( $filtersSelected[ 'mrctags' ] ) ? $filtersSelected[ 'mrctags' ] : "");
								break;
							case "4":
								$currfilter = explode (",",!empty( $filtersSelected[ 'restags' ] ) ? $filtersSelected[ 'restags' ] : "");
								break;
							case "8":
								$currfilter = explode (",",!empty( $filtersSelected[ 'grouptags' ] ) ? $filtersSelected[ 'grouptags' ] : "");
								break;
						}
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
                case 'typology':
                    if(!empty( $filter->Items )){
                        $currfilter = explode (",",!empty( $filtersSelected[ 'typology' ] ) ? $filtersSelected[ 'typology' ] : "");
                        foreach ($filter->Items as $item ) {
                            $item->Selected = in_array(strval($item->Id), $currfilter, true);
                        }
                    }
                    break;
                case 'reszones':
                    if(!empty( $filter->Items )){
                        $currfilter = explode (",",!empty( $filtersSelected[ 'reszones' ] ) ? $filtersSelected[ 'reszones' ] : "");
                        foreach ($filter->Items as $item ) {
                            $item->Selected = in_array(strval($item->Id), $currfilter, true);
                        }
                    }
                    break;
                case 'resavg':
                    if(!empty( $filter->Items )){
                        $currfilter = explode (",",!empty( $filtersSelected[ 'resavg' ] ) ? $filtersSelected[ 'resavg' ] : "");
                        foreach ($filter->Items as $item ) {
                            $item->Selected = in_array(strval($item->Id), $currfilter, true);
                        }
                    }
                    break;
                case 'resrating':
                    if(!empty( $filter->Items )){
                        $currfilter = explode (",",!empty( $filtersSelected[ 'resrating' ] ) ? $filtersSelected[ 'resrating' ] : "");
                        foreach ($filter->Items as $item ) {
                            $item->Selected = in_array(strval($item->Id), $currfilter, true);
                        }
                    }
                    break;
                case 'meals':
                    if(!empty( $filter->Items )){
                        $currfilter = explode (",",!empty( $filtersSelected[ 'meals' ] ) ? $filtersSelected[ 'meals' ] : "");
                        foreach ($filter->Items as $item ) {
							$currfilterValue = explode (",",$item->Id);
							$item->Selected = count(array_intersect($currfilterValue, $currfilter)) == count($currfilterValue);

//                            $item->Selected = in_array(strval($item->Id), $currfilter, true);
                        }
                    }
                    break;
                case 'price':
                    if(!empty( $filter->Items )){
                        $currfilter = explode (",",!empty( $filtersSelected[ 'price' ] ) ? $filtersSelected[ 'price' ] : "");
                        foreach ($filter->Items as $item ) {
                            $item->Selected = in_array(strval($item->Id), $currfilter, true);
                        }
                    }
                    break;
                case 'paymodes':
                    if(!empty( $filter->Items )){
                        $currfilter = explode (",",!empty( $filtersSelected[ 'paymodes' ] ) ? $filtersSelected[ 'paymodes' ] : "");
                        foreach ($filter->Items as $item ) {
                            $item->Selected = in_array(strval($item->Id), $currfilter, true);
                        }
                    }
                    break;
                case 'bedrooms':
                    if(!empty( $filter->Items )){
                        $currfilter = explode (",",!empty( $filtersSelected[ 'bedrooms' ] ) ? $filtersSelected[ 'bedrooms' ] : "");
                        foreach ($filter->Items as $item ) {
                            $item->Selected = in_array(strval($item->Id), $currfilter, true);
                        }
                    }
                    break;
                }
				
            }
        }
    }
    $orderFiltersView = Array('isbookable', 'price', 'merchantcategory', 'productcategory', 'resrating', 'mrcrating', 'grouprating',
        'resavg', 'mrcavg', 'groupavg', 'meals', 'tags',
        'reszones', 'mrczones', 'groupzones', 'bedrooms', 'checkavailability', 'offer', 'paymodes');
	$orderFiltersPosition =[];
	$newfilter = [];
    //Ordinamento filtri in base al peso
	if(!empty( $firstFilters )){
		usort($firstFilters, function($a, $b)
		{
			return $a->ViewOrder>$b->ViewOrder;
		});
		foreach ($firstFilters as $key => $value) {
			$currPropTag = explode("_",$value->PropertyName );
			$value->PropertyNameReformatted = $currPropTag[0];
			$orderFiltersPosition[] = $value->ViewOrder;
		}
	
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
	} else {
	
	}
//	array_multisort($orderFiltersView, SORT_ASC, $orderFiltersPosition, SORT_ASC, $firstFilters);
$currFiltersMrctagsSelected = array();
$currFiltersRestagsSelected = array();
$currFiltersGrouptagsSelected = array();
$urlparts = parse_url($formAction);
parse_str($urlparts['query'], $query);
?>
<div class="bfi-searchfilter bfi-searchfilterdetails">
	<div class="bfi-addfilter  bfi-btn bfi-alternative ">
		<i class="fa fa-filter" aria-hidden="true"></i> <?php _e('Filter by', 'bfi'); ?>
	</div>
<span class="bficurrentfilter"></span>
<form action="<?php echo $formAction; ?>" method="post" id="searchformfilter" name="searchformfilter" >

	<?php 
	foreach($query as $key => $value) {
		if (!in_array($key, array("newsearch", "limitstart", "filter_order", "filter_order_Dir", "originalpoints")) && strpos($key, "filters") !== 0) { ?>
	<input type="hidden" value="<?php echo $value; ?>" name="<?php echo $key; ?>" />
	<?php }
	}
	?>
		
	<input type="hidden" value="<?php echo $searchid ?>" name="searchid">
	<input type="hidden" value="0" name="limitstart">
	<input type="hidden" value="0" name="newsearch">
	<input type="hidden" value="" name="originalpoints">
	<input type="hidden" name="merchantResults" id="filtersmerchantResultseHidden" value="<?php echo $isMerchantResults ?>" />
        <div class="bfi-filtercontainer">
		<?php 
		if (!empty($firstFilters) && count($firstFilters)>0) {
		    
		foreach ($firstFilters as $itemId => $filter){
			$propName = $filter->PropertyName;
			$currTitle = $filter->Name;
			if (stripos($filter->PropertyName,'tags_')!== false) {
				$currTitle = ucfirst(__($filter->Name, 'bfi'));
				$currPropTag = explode("_",$filter->PropertyName);				
				switch ($currPropTag[1]) {
					case "1":						$propName = "mrctags";
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
				case "restags":
					if ($groupResultType != 0) { $showFilters = false; }
					break;
				case "mrczones":
				case "mrcavg":
				case "mrcrating":
				case "mrctags":
					if ($groupResultType != 1) { $showFilters = false; }
					break;
				case "groupzones":
				case "groupavg":
				case "grouprating":
				case "grouptags":
					if ($groupResultType != 2) { $showFilters = false; }
					break;
				case "price":
				case "productcategory":
				case "merchantcategory":
				case "meals":
				case "tags":
				case "bedrooms":
				case "paymodes":
				case "poi":
					//if (count($filter->Items) <= $minvaluetoshow) { $showFilters = false; }
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
					$currTitle = __('Review score', 'bfi');
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
					if (!empty($query['searchTermValue']) && strpos($query['searchTermValue'], "cityIds|") === 0) { 
						$currTitle = __('Distance from center', 'bfi');
					} else if (!empty($query['searchTermValue']) && strpos($query['searchTermValue'], "poiIds|") === 0) {
						$currTitle = __('Distance from point of interest', 'bfi');
					}	
					break;
			}

			if ( count($filter->Items) <= $minvaluetoshow) {
				$showFilters =false;
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
//			if ($newsearch == "1" && in_array(true, array_map(function ($t) { return $t->Selected; }, $filter->Items))  && $filter->FilterType == 0) {
//				$viewResidual = true;
//			}
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
						$currLabel = __('Show only online booking', 'bfi');
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
jQuery(document).ready(function() {
	if (typeof bfiTooltip  !== "function") {
		jQuery.widget.bridge('bfiTooltip', jQuery.ui.tooltip );
	}
	var currDetailsFiltered = [];
	jQuery('.bfi-option-title').each(function(){
		var currFilterActive = jQuery(this).parent("div").first().find(".bfi-filter-active");
		if(currFilterActive.length){
			var currfilter = [];
			currFilterActive.each(function(){
				var rel = jQuery(this).attr("rel");
				var rel1 = jQuery(this).attr("rel1");
				currfilter.push(jQuery(this).find(".bfi-filter-label").first().html() + ' <i class="fa fa-times-circle bfi-removefilter" aria-hidden="true" rel="'+rel+'" rel1="'+rel1+'"></i> ' );
			});
			currDetailsFiltered.push(jQuery(this).text() + ": " + currfilter.join(", "));
		}
	});
	if (currDetailsFiltered.length){
		jQuery('.bficurrentfilter').append( currDetailsFiltered.join(" + "));
	}else{
		jQuery('.bficurrentfilter').hide();
	}
		
	jQuery('#searchformfilter').submit(function( event ) {
		event.preventDefault();
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
		var opt = bfiGetAjaxOptions(<?php echo ($allmerchants=='1')?"1":"" ?>);
		var data = jQuery("#searchformfilter").serialize();
		opt.url += "&" + data;
		jQuery("#bfi-calculatorForm input[name='newsearch']").val(0);
		jQuery('#bfi-calculatorForm').ajaxSubmit(opt);
	});
});
</script>

<div class="bfi-clearfix"></div>