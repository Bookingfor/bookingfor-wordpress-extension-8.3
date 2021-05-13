/*!
 * Bookingfor by Ipertrade 
 * Version: 8.0.1
 * Author: BookingFor 
 * Author URI: http://www.bookingfor.com/
 * Copyright (c) 2015-2019 Ipertrade
 */

var bfidpmode = '';
var bfi_totalRooms = 0;
var bfi_totalQuote = 0;
var bfi_totalQuoteDiscount = 0;
var Leaflet;
var dialogFormResult;

var bookingfor = new function () {
	this.version = "8.3.0";
	this.offersLoaded = [];
	this.adsBlocked = false;
	this.adsBlockedChecked = false;
	this.loadedholydays = false;
	this.holydays = "";
	this.holydaysTitle = "";
	this.loadedAllTags = false;
	this.tagLoaded = [];
	this.tagNameLoaded = [];
	this.tagFullLoaded = [];
	this.loadedAllPois = false;
	this.Pois = [];
	this.PoiCategories = [];
	this.currMap;
	this.currLatLng;
	this.markersLoaded = false;
	this.markersLoading = false;
	this.bfiCurrMarkerId = 0;
	this.strAddress = '[indirizzo] - [cap] - [comune] [provincia]';
	this.strAddressmobile = '[comune] [provincia]';
    this.mobileViewMode = false;
    this.tabletViewMode = false;

    this.checkMobileView = function () {
        bookingfor.mobileViewMode = window.innerWidth <= 768;
    }
    this.checkTabletView = function () {
        bookingfor.tabletViewMode = window.innerWidth <= 1024;
    }
	


	this.getActionUrl = function (controller, action, task, queryString) {
		var baseUrl = bfi_variables.bfi_urlCheck;
		var hasQueryString = false;
		if (typeof task !== "undefined" && task && task.length > 0) {
			baseUrl += "?task=" + task;
			hasQueryString = true;
		} else {
			baseUrl = bfi_variables.bfi_urlCheck + "/{controller}/{action}".replace("{controller}", controller).replace("{action}", action);
		}
		if (typeof queryString === "undefined" || !queryString || queryString.length == 0) return baseUrl;
		return baseUrl + (hasQueryString ? "&" : "?") + queryString;
	};
	this.getDiscountAjaxInformations = function (discountId, hasRateplans) {
		var query = "discountId=" + discountId + "&hasRateplans=" + hasRateplans + "&language=en-gb";
		jQuery.getJSON(bookingfor.getActionUrl(null, null, "getDiscountDetails", query), function (data) {
			var variation = data[0];
			jQuery("#divoffersTitle" + discountId).html(nl2br(jQuery("<p>" + variation.Name + "</p>").text()));
			jQuery("#divoffersDescr" + discountId).html(nl2br(jQuery("<p>" + variation.Description + "</p>").text()));
			jQuery("#divoffersDescr" + discountId).removeClass("com_bookingforconnector_loading");
		});

	};

	this.getData = function (urlCheck, elem, name, act) {
		//query += '&simple=1';		
		if (typeof (ga) !== 'undefined' && !bookingfor.adsBlocked) {
			ga('send', 'event', 'Bookingfor', act, name);
			ga(function () {
				jQuery.get(urlCheck, function (data) {
					jQuery(elem).parent().html(data);
					jQuery(elem).remove();
				});
			});
		} else {
			jQuery.get(urlCheck, function (data) {
				jQuery(elem).parent().html(data);
				jQuery(elem).remove();
			});
		}
	};

	this.make_slug = function (str) {
		str = str.toLowerCase();
		str = str.replace(/\&+/g, 'and');
		str = str.replace(/[^a-z0-9]+/g, '-');
		str = str.replace(/^-|-$/g, '');
		return str;
	};

	this.nl2br = function (str, is_xhtml) {
		var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
		return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
	};

	this.nomore1br = function (str) {
		return (str + '').replace(new RegExp('(\n){2,}', 'gim'), '\n');
	};

	this.stripbbcode = function (str, is_xhtml) {
		str = (str + '').replace(/\[(\w+)[^\]]*](.*?)\[\/\1]/g, '$2');
		str = str.replace(/(\[size\=[\d]\]|\[\/size\])+/g, '');
		str = str.replace(/(\[color\=[\d]\]|\[\/color\])+/g, '');
		str = str.replace(/(\[img\=[\d]\]|\[\/img\])+/g, '');
		str = str.replace(/(\[url\=[\d]\]|\[\/url\])+/g, '');
		str = str.replace(/(\[ul\]|\[\/ul\]|\[ol\]|\[\/ol\])+/g, '');
		return str;
	};

	this.parseODataDate = function (date) {
		return new Date(parseInt(date.match(/\/Date\(([0-9]+)(?:.*)\)\//)[1]));
	};

	this.priceFormat = function (number, decimals, dec_point, thousands_sep) {
		number = (number + '')
			.replace(/[^0-9+\-Ee.]/g, '');
		number = !isFinite(+number) ? 0 : +number,
			//conversion valuta;
			defaultcurrency = bfi_variables.defaultCurrency;//  bfi_get_defaultCurrency();
		currentcurrency = bfi_variables.currentCurrency;//  bfi_get_currentCurrency();

		if (defaultcurrency != currentcurrency) {
			//try to convert
			currencyExchanges = bfi_variables.CurrencyExchanges;// BFCHelper::getCurrencyExchanges();
			if (currencyExchanges.hasOwnProperty(currentcurrency)) {
				number = number * currencyExchanges[currentcurrency];
			}
		}
		return bookingfor.number_format(number, decimals, dec_point, thousands_sep);
	};

	this.number_format = function (number, decimals, dec_point, thousands_sep) {
		//  discuss at: http://phpjs.org/functions/number_format/
		// original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
		// improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
		// improved by: davook
		// improved by: Brett Zamir (http://brett-zamir.me)
		// improved by: Brett Zamir (http://brett-zamir.me)
		// improved by: Theriault
		// improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
		// bugfixed by: Michael White (http://getsprink.com)
		// bugfixed by: Benjamin Lupton
		// bugfixed by: Allan Jensen (http://www.winternet.no)
		// bugfixed by: Howard Yeend
		// bugfixed by: Diogo Resende
		// bugfixed by: Rival
		// bugfixed by: Brett Zamir (http://brett-zamir.me)
		//  revised by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
		//  revised by: Luke Smith (http://lucassmith.name)
		//    input by: Kheang Hok Chin (http://www.distantia.ca/)
		//    input by: Jay Klehr
		//    input by: Amir Habibi (http://www.residence-mixte.com/)
		//    input by: Amirouche
		//   example 1: number_format(1234.56);
		//   returns 1: '1,235'
		//   example 2: number_format(1234.56, 2, ',', ' ');
		//   returns 2: '1 234,56'
		//   example 3: number_format(1234.5678, 2, '.', '');
		//   returns 3: '1234.57'
		//   example 4: number_format(67, 2, ',', '.');
		//   returns 4: '67,00'
		//   example 5: number_format(1000);
		//   returns 5: '1,000'
		//   example 6: number_format(67.311, 2);
		//   returns 6: '67.31'
		//   example 7: number_format(1000.55, 1);
		//   returns 7: '1,000.6'
		//   example 8: number_format(67000, 5, ',', '.');
		//   returns 8: '67.000,00000'
		//   example 9: number_format(0.9, 0);
		//   returns 9: '1'
		//  example 10: number_format('1.20', 2);
		//  returns 10: '1.20'
		//  example 11: number_format('1.20', 4);
		//  returns 11: '1.2000'
		//  example 12: number_format('1.2000', 3);
		//  returns 12: '1.200'
		//  example 13: number_format('1 000,50', 2, '.', ' ');
		//  returns 13: '100 050.00'
		//  example 14: number_format(1e-8, 8, '.', '');
		//  returns 14: '0.00000001'

		number = (number + '')
			.replace(/[^0-9+\-Ee.]/g, '');
		var n = !isFinite(+number) ? 0 : +number,
			prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
			sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
			dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
			s = '',
			toFixedFix = function (n, prec) {
				var k = Math.pow(10, prec);
				return '' + (Math.round(n * k) / k)
					.toFixed(prec);
			};
		// Fix for IE parseFloat(0.55).toFixed(0) = 0;
		s = (prec ? toFixedFix(n, prec) : '' + Math.round(n))
			.split('.');
		if (s[0].length > 3) {
			s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
		}
		if ((s[1] || '')
			.length < prec) {
			s[1] = s[1] || '';
			s[1] += new Array(prec - s[1].length + 1)
				.join('0');
		}
		return s.join(dec);
	};

	this.getUrlParameter = function (name) {
		name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
		var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
		var results = regex.exec(location.search);
		return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
	};

	this.updateQueryStringParameter = function (uri, key, value) {
		return uri
			.replace(new RegExp("([?&]" + key + "(?=[=&#]|$)[^#&]*|(?=#|$))"), "&" + key + "=" + encodeURIComponent(value))
			.replace(/^([^?&]+)&/, "$1?");
		//	  var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
		//	  var separator = uri.indexOf('?') !== -1 ? "&" : "?";
		//	  if (uri.match(re)) {
		//		return uri.replace(re, '$1' + key + "=" + value + '$2');
		//	  }
		//	  else {
		//		return uri + separator + key + "=" + value;
		//	  }
	};



	this.waitBlockUI = function (msg1, msg2, img1) {
		msg1 = msg1 ? msg1 : "";
		msg2 = msg2 ? msg2 : "";
		var msggeneral = jQuery.trim(msg1).length && jQuery.trim(msg2).length ? msg1 + '<br />' + msg2 : (jQuery.trim(msg1).length ? msg1 : msg2);
		jQuery.blockUI({
			message: (jQuery.trim(msggeneral).length ? '<h1 class="bfi-wait">' + msggeneral + '</h1><br />' : "") + '<i class="fa fa-spinner fa-spin fa-3x fa-fw margin-bottom"></i><span class="sr-only">Loading...</span>',
			css: { border: '2px solid #1D668B', padding: '20px', backgroundColor: '#fff', '-webkit-border-radius': '10px', '-moz-border-radius': '10px', color: '#1D668B' },
			overlayCSS: { backgroundColor: '#1D668B', opacity: .7 }
		});
	};

	this.waitBlock = function (msg1, msg2, obj) {
		obj.block({
			message: '<h1 class="bfi-wait">' + msg1 + '<br />' + msg2 + '</h1><br /><i class="fa fa-spinner fa-spin fa-3x fa-fw margin-bottom"></i><span class="sr-only">Loading...</span>',
			css: { border: '2px solid #1D668B', padding: '20px', backgroundColor: '#fff', '-webkit-border-radius': '10px', '-moz-border-radius': '10px', color: '#1D668B', width: '80%' },
			//		overlayCSS: {backgroundColor: '#1D668B', opacity: .7}  
			overlayCSS: { backgroundColor: '#1D668B', opacity: 0 }
		});
	};

	this.waitSimpleBlock = function (obj) {
		obj.block({
			message: '<i class="fa fa-spinner fa-spin fa-3x fa-fw margin-bottom"></i><span class="sr-only">Loading...</span>',
			css: { border: '2px solid #1D668B', padding: '10px 20px', backgroundColor: '#fff', '-webkit-border-radius': '10px', '-moz-border-radius': '10px', color: '#1D668B', width: '80%' },
			overlayCSS: { backgroundColor: '#1D668B', opacity: .7 }
		});
	};

	this.waitSimpleWhiteBlock = function (obj) {
		obj.block({
			message: '<i class="fa fa-spinner fa-spin fa-3x fa-fw margin-bottom"></i><span class="sr-only">Loading...</span>',
			css: { border: 'none', width: '100%' },
			overlayCSS: { backgroundColor: '#ffffff', opacity: 0.7 }
		});
	};

	this.dateAdd = function (date, interval, units) {
		var ret = new Date(date); //don't change original date
		units = Number(units+""); 
		switch (interval.toLowerCase()) {
			case 'year': ret.setFullYear(ret.getFullYear() + units); break;
			case 'quarter': ret.setMonth(ret.getMonth() + 3 * units); break;
			case 'month': ret.setMonth(ret.getMonth() + units); break;
			case 'week': ret.setDate(ret.getDate() + 7 * units); break;
			case 'day': ret.setDate(ret.getDate() + units); break;
			case 'hour': ret.setTime(ret.getTime() + units * 3600000); break;
			case 'minute': ret.setTime(ret.getTime() + units * 60000); break;
			case 'second': ret.setTime(ret.getTime() + units * 1000); break;
			default: ret = undefined; break;
		}
		return ret;
	};
	this.startOfWeek = function (date) {
		var diff = date.getDate() - date.getDay() + (date.getDay() === 0 ? -6 : 1);
		return new Date(date.setDate(diff));
	};

	this.endOfWeek = function (date) {
		var lastday = date.getDate() - (date.getDay() - 1) + 6;
		return new Date(date.setDate(lastday));
	};

	this.lastdayOfMonth = function (date) {
		return new Date(date.getFullYear(), date.getMonth() + 1, 0);
	};
	this.firstdayOfMonth = function (date) {
		return new Date(date.getFullYear(), date.getMonth(), 1);
	};

	this.convertDateToInt = function (currDate) {
		var month = currDate.getMonth() + 1;
		var day = currDate.getDate();
		var year = currDate.getFullYear();
		var datereformat = year + '' + bookingfor.pad(month, 2) + '' + bookingfor.pad(day, 2);
		var intDate = Number(datereformat);
		return (intDate)
	};
	this.convertDateToIta = function (currDate) {
		var month = currDate.getMonth() + 1;
		var day = currDate.getDate();
		var year = currDate.getFullYear();
		var datereformat = bookingfor.pad(day, 2) + '/' + bookingfor.pad(month, 2) + '/' + year;
		return (datereformat)
	};

	this.getDisplayDate = function (date) {
		return date == null ? "" : bookingfor.pad(date.getDate(),2) + '/' + bookingfor.pad((date.getMonth() + 1),2) + '/' + date.getFullYear();
	};

	this.pad = function (str, max) {
		if (!str) {
			str = "";
		}
		str = str.toString();
		return str.length < max ? this.pad("0" + str, max) : str;
	};
	this.createAutocomplete = function (currForm){

				var currAutocomplete = jQuery(currForm).find(".bfi-autocomplete").first();
				var currScope = currAutocomplete.attr("data-scope");
				var originalMerchantCategories = jQuery(currForm).find("[name=merchantCategoryId]").val();
				var originalProductCategories = jQuery(currForm).find("[name=masterTypeId]").val();
				var originalGroupCategories = jQuery(currForm).find("[name=groupCategoryId]").val();
				var originalMerchantTags = jQuery(currForm).find("[name=merchantTagIds]").val();
				var originalProductTags = jQuery(currForm).find("[name=productTagIds]").val();
				var originalGroupTags = jQuery(currForm).find("[name=groupTagIds]").val();
				var currItemtypeid = currAutocomplete.attr("data-itemtypeid");
				if (typeof currScope === "undefined" || !currScope.length){
					currScope = "";
				}
				if (typeof currItemtypeid === "undefined" || !currItemtypeid.length){
					currItemtypeid = "";
				}
				var lastCorrectSearch = null;
				var lastCorrectName = null;
				var lastCorrectItem = null;

				currAutocomplete.blur(function(){
					 var keyEvent = jQuery.Event("keydown");
					 keyEvent.keyCode = jQuery.ui.keyCode.ENTER;
					 jQuery(this).trigger(keyEvent);
				 }).autocomplete({
					autoFocus: true,
					source: function (request, response) {
						var previous_request = currAutocomplete.data( "jqXHR" );
						if( previous_request ) {
							previous_request.abort();
						}
						
						var allParams = {
							action: "SearchByText",
							term: request.term,
							resultClasses: currScope,
							maxresults: "5",
							minMatchingPercentage: "90",
							cultureCode: bfi_variables.bfi_cultureCode,
							limitregions: "1",
							itemtypeid: currItemtypeid,
							productcategories: jQuery(currForm).find("[name=masterTypeId]").val(),
							merchantcategories: jQuery(currForm).find("[name=merchantCategoryId]").val(),
						};
						var urlSearch =  bfi_variables.bfi_urlSearchByText + "?" + jQuery.param(allParams);
						
//						var urlSearch =  bfi_variables.bfi_urlCheck + "?task=SearchByText&term=" + request.term + "&resultClasses=" + currScope + "&maxresults=5&minMatchingPercentage=90&cultureCode=" + bfi_variables.bfi_cultureCode;
						currAutocomplete.data( "jqXHR",
							jQuery.getJSON(urlSearch, function (data) {
								if (data.length) {
									jQuery.each(data, function (key, item) {
										var currentVal = "";
										item.AdditionaInfos = JSON.parse(item.AdditionaInfosString);
										if (item.ItemTypeOrder == 0 || item.ItemTypeOrder == 2 || item.ItemTypeOrder == 4 || item.ItemTypeOrder == 6) { currentVal = "zoneIds|" + item.ZoneId; }
										if (item.ItemTypeOrder == 1) { currentVal = "stateIds|" + item.StateId; }
										if (item.ItemTypeOrder == 3) { currentVal = "regionIds|" + item.RegionId; }
										if (item.ItemTypeOrder == 5) { currentVal = "cityIds|" + item.CityId + ":" + item.XPos + ":" + item.YPos+ "|" +  item.AdditionaInfos.CityId + "|" +  item.AdditionaInfos.Name  + "|" + item.AdditionaInfos.Level1RegionName  + "|" + item.AdditionaInfos.StateName ;}
										if (item.ItemTypeOrder == 7) { currentVal = "poiIds|" + item.PointOfInterestId + ":" + item.XPos + ":" + item.YPos;  }
										if (item.ItemTypeOrder == 8) { currentVal = "evtCategoryids|" + item.EventCategoryId; }
										if (item.ItemTypeOrder == 9) { currentVal = "merchantCategoryId|" + item.MerchantCategoryId; }
										if (item.ItemTypeOrder == 10) { currentVal = "groupCategoryId|" + item.ProductGroupCategoryId; }
										if (item.ItemTypeOrder == 11) { currentVal = "masterTypeId|" + item.ProductCategoryId; }
										if (item.ItemTypeOrder == 12) { currentVal = "evtTagIds|" + item.EventTagId; }
										if (item.ItemTypeOrder == 13) { currentVal = "merchantTagIds|" + item.MerchantTagId; }
										if (item.ItemTypeOrder == 14) { currentVal = "groupTagIds|" + item.ProductGroupTagId; }
										if (item.ItemTypeOrder == 15) { currentVal = "resourceTagIds|" + item.ProductTagId; }
										if (item.ItemTypeOrder == 16) { currentVal = "eventIds|" + item.EventId + ":" + item.XPos + ":" + item.YPos; }
										if (item.ItemTypeOrder == 17) { currentVal = "merchantIds|" + item.MerchantId + "|" +  item.AdditionaInfos.CityId + "|" +  item.AdditionaInfos.CityName  + "|" + item.AdditionaInfos.Level1RegionName  + "|" + item.AdditionaInfos.StateName ; }
										if (item.ItemTypeOrder == 18) { currentVal = "groupresourceIds|" + item.ProductGroupId; }
										if (item.ItemTypeOrder == 19) { currentVal = "resourceIds|" + item.ProductId + "|" +  item.AdditionaInfos.ItemTypeId + "|" +  item.AdditionaInfos.CityId + "|" +  item.AdditionaInfos.CityName  + "|" + item.AdditionaInfos.Level1RegionName  + "|" + item.AdditionaInfos.StateName ;  }
										item.Value = currentVal;
										item.key = key;
									});
									response(data);
									currAutocomplete.removeClass("ui-autocomplete-loading");
								} else {
									response([{
										Name: bfi_variables.bfi_txtnoresult
									}]);
									currAutocomplete.removeClass("ui-autocomplete-loading");
								}
							})
						);
					},
					minLength: 2,
					delay: 250,
					close: function (event, ui) {
						if (jQuery(this).val() != "" && lastCorrectName !="" && lastCorrectName!= jQuery(this).val())
						{
							var selectedVal = lastCorrectSearch;
							var name = lastCorrectName;
							ui.item = lastCorrectItem;
							
							currAutocomplete.closest("form").find("[name=stateIds],[name=regionIds],[name=cityIds],[name=zoneIds],[name=merchantCategoryId],[name=masterTypeId],[name=filters\\[productcategory\\]],[name=groupCategoryId],[name=merchantTagIds],[name=groupTagIds],[name=productTagIds],[name=merchantIds],[name=groupresourceIds],[name=resourceIds],[name=points],[name=filter_order],[name=getBaseFiltersFor]").val("");
							currAutocomplete.closest("form").find("[name=merchantCategoryId]").val(originalMerchantCategories);
							currAutocomplete.closest("form").find("[name=masterTypeId]").val(originalProductCategories);
							currAutocomplete.closest("form").find("[name=groupCategoryId]").val(originalGroupCategories);
							currAutocomplete.closest("form").find("[name=merchantTagIds]").val(originalMerchantTags);
							currAutocomplete.closest("form").find("[name=productTagIds]").val(originalProductTags);
							currAutocomplete.closest("form").find("[name=groupTagIds]").val(originalGroupTags);
							currAutocomplete.closest("form").find("[name=searchTermValue]").val(selectedVal);
							switch (selectedVal.split('|')[0]) {
								case "stateIds":
									var completeValue = selectedVal.split('|')[1];
									currAutocomplete.closest("form").find("[name=" + selectedVal.split('|')[0] + "]").val(selectedVal.split('|')[1]);
									break;
								case "regionIds":
									var completeValue = selectedVal.split('|')[1];
									currAutocomplete.closest("form").find("[name=" + selectedVal.split('|')[0] + "]").val(selectedVal.split('|')[1]);
									break;
								case "cityIds":
                                    var completeValue = selectedVal.split('|')[1];
									selectedVals  = selectedVal.split('|');
									currAutocomplete.closest("form").find("[name=" + selectedVal.split('|')[0] + "]").val(completeValue.split(":")[0]);
                                    break;
								case "poiIds":
									var completeValue = selectedVal.split('|')[1];
									if($.trim(ui.item.AdditionalInfos["XPos"]).length > 0 && $.trim(ui.item.AdditionalInfos["YPos"]).length > 0) {
										currAutocomplete.closest("form").find("[name=points]").val("0|" + ui.item.AdditionalInfos["XPos"] + " " + ui.item.AdditionalInfos["YPos"]);
									}
									break;
								case "merchantIds":
									currAutocomplete.closest("form").find("[name=filter_order]").val("merchantid:" + ui.item.AdditionaInfos["MerchantId"]);
									if (currScope.split(',').indexOf('5') > -1 && ui.item.AdditionaInfos["CityId"])
									{
										currAutocomplete.closest("form").find("[name=cityIds]").val(ui.item.AdditionaInfos["CityId"]);
										currAutocomplete.closest("form").find("[name=searchTermValue]").val('cityIds|' + ui.item.AdditionaInfos["CityId"]);
										currAutocomplete.closest("form").find("[name=searchtermoverride]").val(ui.item.AdditionaInfos["CityName"]);
									}
									break;
								case "groupresourceIds":
									currAutocomplete.closest("form").find("[name=filter_order]").val("parentid:" + selectedVal.split('|')[1]);
                                    break;
								case "resourceIds":
									currAutocomplete.closest("form").find("[name=filter_order]").val("resourceid:" + ui.item.AdditionaInfos["ProductId"]);
									if (currScope.split(',').indexOf('5') > -1 && ui.item.AdditionaInfos["CityId"])
									{
										currAutocomplete.closest("form").find("[name=cityIds]").val(ui.item.AdditionaInfos["CityId"]);
										currAutocomplete.closest("form").find("[name=searchTermValue]").val('cityIds|' + ui.item.AdditionaInfos["CityId"]);
										currAutocomplete.closest("form").find("[name=searchtermoverride]").val(ui.item.AdditionaInfos["CityName"]);
									}
									break;
								case "masterTypeId":
									currAutocomplete.closest("form").find("[name=filters\\[productcategory\\]]").val(selectedVal.split('|')[1]);
									currAutocomplete.closest("form").find("[name=getBaseFiltersFor]").val("productcategory");
									break;
								default:
									currAutocomplete.closest("form").find("[name=" + selectedVal.split('|')[0] + "]").val(selectedVal.split('|')[1]);
									break;
							}
							jQuery(this).val(name);
						}
					},
					select: function (event, ui) {
						var selectedVal = ui.item.Value;
						if (typeof selectedVal !== "undefined" && selectedVal.length) {
							var name = ui.item.VisibleName;
							currAutocomplete.closest("form").find("[name=stateIds],[name=regionIds],[name=cityIds],[name=zoneIds],[name=merchantCategoryId],[name=masterTypeId],[name=filters\\[productcategory\\]],[name=groupCategoryId],[name=merchantTagIds],[name=groupTagIds],[name=productTagIds],[name=merchantIds],[name=groupresourceIds],[name=resourceIds],[name=points],[name=filter_order],[name=getBaseFiltersFor]").val("");
							currAutocomplete.closest("form").find("[name=merchantCategoryId]").val(originalMerchantCategories);
							currAutocomplete.closest("form").find("[name=masterTypeId]").val(originalProductCategories);
							currAutocomplete.closest("form").find("[name=groupCategoryId]").val(originalGroupCategories);
							currAutocomplete.closest("form").find("[name=merchantTagIds]").val(originalMerchantTags);
							currAutocomplete.closest("form").find("[name=productTagIds]").val(originalProductTags);
							currAutocomplete.closest("form").find("[name=groupTagIds]").val(originalGroupTags);
							currAutocomplete.closest("form").find("[name=searchTermValue]").val(selectedVal);
							switch (selectedVal.split('|')[0]) {
								case "stateIds":
									var completeValue = selectedVal.split('|')[1];
									currAutocomplete.closest("form").find("[name=" + selectedVal.split('|')[0] + "]").val(selectedVal.split('|')[1]);
									break;
								case "regionIds":
									var completeValue = selectedVal.split('|')[1];
									currAutocomplete.closest("form").find("[name=" + selectedVal.split('|')[0] + "]").val(selectedVal.split('|')[1]);
									break;
								case "cityIds":
                                    var completeValue = selectedVal.split('|')[1];
									selectedVals  = selectedVal.split('|');
									currAutocomplete.closest("form").find("[name=" + selectedVal.split('|')[0] + "]").val(completeValue.split(":")[0]);
                                    break;
								case "poiIds":
									var completeValue = selectedVal.split('|')[1];
									if($.trim(ui.item.AdditionalInfos["XPos"]).length > 0 && $.trim(ui.item.AdditionalInfos["YPos"]).length > 0) {
										currAutocomplete.closest("form").find("[name=points]").val("0|" + ui.item.AdditionalInfos["XPos"] + " " + ui.item.AdditionalInfos["YPos"]);
									}
									break;
								case "merchantIds":
									currAutocomplete.closest("form").find("[name=filter_order]").val("merchantid:" + ui.item.AdditionaInfos["MerchantId"]);
									if (currScope.split(',').indexOf('5') > -1 && ui.item.AdditionaInfos["CityId"])
									{
										currAutocomplete.closest("form").find("[name=cityIds]").val(ui.item.AdditionaInfos["CityId"]);
										currAutocomplete.closest("form").find("[name=searchTermValue]").val('cityIds|' + ui.item.AdditionaInfos["CityId"]);
										currAutocomplete.closest("form").find("[name=searchtermoverride]").val(ui.item.AdditionaInfos["CityName"]);
									}
									break;
								case "groupresourceIds":
									currAutocomplete.closest("form").find("[name=filter_order]").val("parentid:" + selectedVal.split('|')[1]);
                                    break;
								case "resourceIds":
									currAutocomplete.closest("form").find("[name=filter_order]").val("resourceid:" + ui.item.AdditionaInfos["ProductId"]);
									if (currScope.split(',').indexOf('5') > -1 && ui.item.AdditionaInfos["CityId"])
									{
										currAutocomplete.closest("form").find("[name=cityIds]").val(ui.item.AdditionaInfos["CityId"]);
										currAutocomplete.closest("form").find("[name=searchTermValue]").val('cityIds|' + ui.item.AdditionaInfos["CityId"]);
										currAutocomplete.closest("form").find("[name=searchtermoverride]").val(ui.item.AdditionaInfos["CityName"]);
									}
									break;
								case "masterTypeId":
									currAutocomplete.closest("form").find("[name=filters\\[productcategory\\]]").val(selectedVal.split('|')[1]);
									currAutocomplete.closest("form").find("[name=getBaseFiltersFor]").val("productcategory");
									break;
								default:
									currAutocomplete.closest("form").find("[name=" + selectedVal.split('|')[0] + "]").val(selectedVal.split('|')[1]);
									break;
							}
							jQuery(this).val(name);
							lastCorrectSearch = selectedVal;
							lastCorrectName = name;
							lastCorrectItem = ui.item;
							event.preventDefault();
						}
					},
					change: function( event, ui ) {
						if (currAutocomplete.val().length<2) {
							currAutocomplete.closest("form").find("[name=stateIds],[name=regionIds],[name=cityIds],[name=zoneIds],[name=merchantCategoryId],[name=masterTypeId],[name=filters\\[productcategory\\]],[name=groupCategoryId],[name=merchantTagIds],[name=groupTagIds],[name=productTagIds],[name=merchantIds],[name=groupresourceIds],[name=resourceIds],[name=points],[name=filter_order],[name=getBaseFiltersFor]").val("");
							currAutocomplete.closest("form").find("[name=merchantCategoryId]").val(originalMerchantCategories);
							currAutocomplete.closest("form").find("[name=masterTypeId]").val(originalProductCategories);
							currAutocomplete.closest("form").find("[name=groupCategoryId]").val(originalGroupCategories);
							currAutocomplete.closest("form").find("[name=merchantTagIds]").val(originalMerchantTags);
							currAutocomplete.closest("form").find("[name=productTagIds]").val(originalProductTags);
							currAutocomplete.closest("form").find("[name=groupTagIds]").val(originalGroupTags);
						}
					},
					open: function () {
						jQuery(this).data("uiAutocomplete").menu.element.addClass("bfi-autocomplete");
					},
				});


				currAutocomplete.data("ui-autocomplete")._renderItem = function (ul, item) {
					
					var currentVal = "";
					var htmlContent = item.Name;
					if (item.ItemTypeOrder == 0 || item.ItemTypeOrder == 2 || item.ItemTypeOrder == 4 || item.ItemTypeOrder == 6) { 
						currentVal = "zoneIds|" + item.ZoneId;
					}
					item.VisibleName = item.Name;
					if (item.ItemTypeOrder == 1) { currentVal = "stateIds|" + item.StateId + ":" + item.XPos + ":" + item.YPos; }
					if (item.ItemTypeOrder == 3) { currentVal = "regionIds|" + item.RegionId + ":" + item.XPos + ":" + item.YPos; }
					if (item.ItemTypeOrder == 5) { currentVal = "cityIds|" + item.CityId + ":" + item.XPos + ":" + item.YPos; }
					if (item.ItemTypeOrder == 7) { currentVal = "poiIds|" + item.PointOfInterestId + ":" + item.XPos + ":" + item.YPos;  }
					if (item.ItemTypeOrder == 8) { currentVal = "evtCategoryids|" + item.EventCategoryId; }
					if (item.ItemTypeOrder == 9) { currentVal = "merchantCategoryId|" + item.MerchantCategoryId; }
					if (item.ItemTypeOrder == 10) { currentVal = "groupCategoryId|" + item.ProductGroupCategoryId; }
					if (item.ItemTypeOrder == 11) { currentVal = "masterTypeId|" + item.ProductCategoryId; }
					if (item.ItemTypeOrder == 12) { currentVal = "evtTagIds|" + item.EventTagId; }
					if (item.ItemTypeOrder == 13) { currentVal = "merchantTagIds|" + item.MerchantTagId; }
					if (item.ItemTypeOrder == 14) { currentVal = "groupTagIds|" + item.ProductGroupTagId; }
					if (item.ItemTypeOrder == 15) { currentVal = "resourceTagIds|" + item.ProductTagId; }
					if (item.ItemTypeOrder == 16) { currentVal = "eventIds|" + item.EventId + ":" + item.XPos + ":" + item.YPos; }
					if (item.ItemTypeOrder == 17) { currentVal = "merchantIds|" + item.MerchantId; }
					if (item.ItemTypeOrder == 18) { currentVal = "groupresourceIds|" + item.ProductGroupId; }
					if (item.ItemTypeOrder == 19) { currentVal = "resourceIds|" + item.ProductId; }
					
					switch (item.ItemTypeOrder) {
						case 0: //
						case 1: //stateIds
						case 2: //
						case 3: //regionIds
						case 4: //
						case 6: //
							var name = item.Name;
							if (item.ItemTypeOrder == 3 && item.AdditionaInfos["Level1RegionId"]) name = "Provincia di " + item.AdditionaInfos["Name"] + ", " + item.AdditionaInfos["StateName"];
							item.VisibleName = name;
							htmlContent = '<i class="fas fa-map-marked-alt"></i>&nbsp;' + name;
							break;
						case 5: //cityIds
							htmlContent = '<i class="fa fa-map-marker"></i>&nbsp;' + item.Name;
							var selectedVal = item.AdditionaInfos["Name"];
							selectedVals  = selectedVal.split('|');
							var addr = [];
							addr.push(item.AdditionaInfos["Name"]);
							if (item.AdditionaInfos["Level1RegionName"]) addr.push(item.AdditionaInfos["Level1RegionName"]);
							if (item.AdditionaInfos["StateName"]) addr.push(item.AdditionaInfos["StateName"]);
							item.VisibleName = addr.join(', ');
							htmlContent =  '<i class="fas fa-map-marker-alt"></i>&nbsp;' + addr.join(', ');

							break;
						case 7: //poiIds
							htmlContent = '<i class="fas fa-street-view"></i>&nbsp;' + item.Name;
							break;
						case 8: //evtCategoryids
						case 9: //merchantCategoryId
						case 10: //groupCategoryId
						case 11: //masterTypeId
							htmlContent = '<i class="fas fa-building"></i>&nbsp;' + item.Name;
							break;
						case 12: //evtTagIds
						case 13: //merchantTagIds
						case 14: //groupTagIds
						case 15: //resourceTagIds
							htmlContent = '<i class="fas fa-tag"></i>&nbsp;' + item.Name;
							break;
						case 16: //eventIds
							htmlContent = item.Name;
							break;
						case 17: //merchantIds
							var selectedVal = item.Value;
							selectedVals  = selectedVal.split('|');
							var addr = [];
							addr.push(item.AdditionaInfos["Name"]);
							if (item.AdditionaInfos["CityName"]) addr.push(item.AdditionaInfos["CityName"]);
							if (item.AdditionaInfos["Level1RegionName"]) addr.push(item.AdditionaInfos["Level1RegionName"]);
							if (item.AdditionaInfos["StateName"]) addr.push(item.AdditionaInfos["StateName"]);
							item.VisibleName = addr.join(', ');
							htmlContent = '<i class="fas fa-building"></i>&nbsp;' + item.VisibleName;
							break;
						case 18: //groupresourceIds
							htmlContent = '<i class="fas fa-bed"></i>&nbsp;' + item.Name;
							break;
						case 19: //resourceIds
							var selectedVal = item.Value;
							selectedVals  = selectedVal.split('|');
							var addr = [];
							addr.push(item.AdditionaInfos["Name"]);
							if (item.AdditionaInfos["CityName"]) addr.push(item.AdditionaInfos["CityName"]);
							if (item.AdditionaInfos["Level1RegionName"]) addr.push(item.AdditionaInfos["Level1RegionName"]);
							if (item.AdditionaInfos["StateName"]) addr.push(item.AdditionaInfos["StateName"]);
							item.VisibleName = addr.join(', ');
							if (item.AdditionaInfos["ItemTypeId"] == "0" ) //accomodation
							{
								htmlContent = '<i class="fas fa-bed"></i>&nbsp;' + item.VisibleName;
							} else if (item.AdditionaInfos["ItemTypeId"] == "6" ) // experience
							{
								htmlContent = '<i class="fas fa-ticket-alt"></i>&nbsp;' + item.VisibleName;
							} else {
								htmlContent = item.VisibleName;
							}
							break;
					}
					
					if (currentVal.length) {
						if (item.key ==0)
						{
							lastCorrectSearch = currentVal;
							lastCorrectName = item.Name;
							lastCorrectItem = item;
						}
						return jQuery("<li>").attr("data-value", currentVal).html(htmlContent).appendTo(ul);
					} else {
						return jQuery("<li>").attr("data-value", "").html(htmlContent).addClass("ui-state-disabled").appendTo(ul);
					}
										
				};
	};

	this.addToCart = function (objSource, gotoCart, resetCart, currResources) {
		gotoCart = (typeof gotoCart !== 'undefined') ? gotoCart : 0;
		resetCart = (typeof resetCart !== 'undefined') ? resetCart : 0;
		bookingfor.waitBlockUI();
		// invio i dati direttamente al carrello
		if (gotoCart == 1) {
			var frmOrder= jQuery('.frm-order').first();
			frmOrder.attr('action',bfi_variables.bfi_carturl);
			jQuery('<input>').attr({
				type: 'hidden',
				id: 'hdnOrderData',
				name: 'hdnOrderData',
				value: JSON.stringify(currResources).replace(/'/g, "$$$")
			}).appendTo(frmOrder);
			jQuery('<input>').attr({
				type: 'hidden',
				id: 'bfiResetCart',
				name: 'bfiResetCart',
				value: resetCart
			}).appendTo(frmOrder);
			frmOrder.submit();
			return;
		}
		//		jQuery.blockUI({ message: ''});
		var cart = jQuery('.bfi-shopping-cart').first();
		if (!jQuery(cart).length) {
			cart = jQuery('.bfi-content').first();
		}

		var recalculareOrder = 0;
		var orderDetailSummarytodrag = jQuery("#orderDetailSummary");
		if (jQuery(objSource).length) {
			orderDetailSummarytodrag = objSource;
			//			recalculateOrder = 1;
		}
		if (cart.length) {
			if (orderDetailSummarytodrag.length) {
				var divClone = orderDetailSummarytodrag.clone();
				divClone.offset({
					top: orderDetailSummarytodrag.offset().top,
					left: orderDetailSummarytodrag.offset().left
				});
				divClone.css({
					'opacity': '0.5',
					'width': orderDetailSummarytodrag.width() + "px",
					'height': orderDetailSummarytodrag.height() + "px",
					'position': 'absolute',
					'z-index': '100',
					'overflow': 'hidden'
				});
				divClone.appendTo(jQuery('body'))
					.animate({
						'top': cart.offset().top + 10,
						'left': cart.offset().left,
						'width': 0,
						'height': 0
					}, 1000, 'easeInOutExpo', function () {
						jQuery(this).remove();
						//cartModel();
						var currData = {
							//							"hdnOrderData":jQuery("#hdnOrderDataCart").val(),
							//							"recalculateOrder":recalculateOrder,
							//							"hdnBookingType":jQuery("#hdnBookingType").val(),
							"hdnOrderData": JSON.stringify(currResources).replace(/'/g, "$$$"),
							"bfiResetCart": resetCart
						};
						jQuery.ajax({
							cache: false,
							type: 'POST',
							url: bookingfor.getActionUrl(null, null, "addToCart"),
							//url: bfi_variables.bfi_urlCheck + ((bfi_variables.bfi_urlCheck.indexOf('?') > -1) ? "&" : "?") + 'task=addToCart',
							data: currData,
							success: function (data) {
								//							console.log(data);
								if (gotoCart == 1) {
									window.location.assign(bfi_variables.bfi_carturl);
								} else {
									jQuery.unblockUI();

									jQuery(".bfibadge").html(data);
									var currModalCart = jQuery(".bfimodalcart").first();
									var currTitle = currModalCart.find(".bfi-title").first().html();
									var currHtml = currModalCart.find(".bfi-body").first().html();
									var currFooter = currModalCart.find(".bfi-footer").first().html();

									var thisHtml = currHtml;
									jQuery(".bf-summary-body-resourcename").each(function () {
										var cuttTitle = jQuery(this).find("strong").first();
										if (cuttTitle.length) {
											thisHtml += "<div>" + cuttTitle.html() + "</div>";
										}
									});
									thisHtml += currFooter;
									cart.webuiPopover({
										title: currTitle,
										content: thisHtml,
										container: document.body,
										cache: false,
										closeable: true,
										arrow: false,
										backdrop: true,
										placement: 'auto-bottom',
										type: 'html',
										style: 'bfi-webuipopover'
									});
									jQuery('html,body').animate({
										scrollTop: cart.offset().top
									},
										'slow', function () {
											// Animation complete.
											cart.webuiPopover("show");
										});

								}

							}
						});

					});

			} else {
				var currData = {
					"hdnOrderData": JSON.stringify(currResources).replace(/'/g, "$$$"),
					"bfiResetCart": resetCart
				};
				jQuery.ajax({
					cache: false,
					type: 'POST',
					url: bookingfor.getActionUrl(null, null, "addToCart"),
					data: currData,
					success: function (data) {
						if (gotoCart == 1) {
							window.location.assign(bfi_variables.bfi_carturl);
						} else {

							jQuery.unblockUI();

							jQuery(".bfibadge").html(data);

							var currModalCart = jQuery(".bfimodalcart").first();
							var currTitle = currModalCart.find(".bfi-title").first().html();
							var currHtml = currModalCart.find(".bfi-body").first().html();
							var currFooter = currModalCart.find(".bfi-footer").first().html();

							var thisHtml = currHtml;
							thisHtml += currFooter;
							cart.webuiPopover({
								title: currTitle,
								content: thisHtml,
								container: document.body,
								cache: false,
								closeable: true,
								arrow: false,
								backdrop: true,
								placement: "auto",
								html: "true",
								style: 'bfi-webuipopover'
							});
							jQuery('html,body').animate({
								scrollTop: cart.offset().top
							},
								'slow', function () {
									// Animation complete.
									cart.webuiPopover("show");
								});
						}
					}
				});
			}
		}


	};

	this.removeFromCart = function () {
		jQuery.ajax({
			cache: false,
			type: 'POST',
			url: removeFromCartUrl,
			beforeSend: function () {
				bookingfor.waitBlockUI();
				//blockui();
			},
			data: {
				cartOrderId: jQuery(this).attr("data-cartorderid")
			},
			success: function (data) {
				jQuery("#LoginRegisterModel").html(data);
				//$("#LoginRegisterModel").modal({ backdrop: 'static' });
				jQuery.unblockUI();
			}
		});
	};
	this.GetDiscountsInfo = function (discountIds, language, obj, fn) {
		jQuery.post(bookingfor.getActionUrl(null, null, "getDiscountDetails", "discountIds=" + discountIds + "&language=" + language), function (data) {
			$html = '';
			jQuery.each(data || [], function (key, val) {
				var name = val.Name;
				var descr = val.Description;
				name = bookingfor.nl2br(jQuery("<p>" + name + "</p>").text());
				$html += '<p class="title">' + name + '</p>';
				descr = bookingfor.nl2br(jQuery("<p>" + bookingfor.stripbbcode(descr) + "</p>").text());
				$html += '<p class="description ">' + descr + '</p>';
			});
			bookingfor.offersLoaded[discountIds] = $html;
			fn(obj, $html);
		}, 'json');
	};
	this.checkBookable = function (currSelect) {
		var isbookable = jQuery(currSelect).attr("data-isbookable");
		jQuery(".ddlrooms.ddlrooms-indipendent[data-isbookable!='" + isbookable + "']").each(function (index) {
			jQuery(this).val(0);
			bookingfor.checkMaxSelect(this);
		});
	};

	this.checkMaxSelect = function (currSelect) {
		var currContainer = jQuery(currSelect).closest(".bfi-table-resources").first();
		var maxSelectable = Number(jQuery(currSelect).attr("data-availability") || 0);
		var maxSelectableQt = Number(jQuery(currSelect).attr("data-maxqt") || 0);
		maxSelectable = Math.min(maxSelectable, maxSelectableQt);

		var isbookable = jQuery(currSelect).attr("data-isbookable");
		var resourceId = jQuery(currSelect).attr("data-resid");
		var realAvailProductId = jQuery(currSelect).attr("data-realavailproductid");
		if (jQuery(".ddlroomsrealav-" + realAvailProductId + "[data-isbookable='" + isbookable + "']").length > 1) {
			var occupancyResource = bookingfor.getOccupancy(realAvailProductId, isbookable, currContainer);
			var remainingResource = maxSelectable - occupancyResource;
			jQuery(".ddlroomsrealav-" + realAvailProductId + "[data-isbookable='" + isbookable + "']").each(function () {
				var currentValue = parseInt(jQuery(this).val());
				var maxValue = parseInt(jQuery(this).find("option:last-child").attr("value"));
				if ((currentValue + remainingResource) < maxValue) {
					maxValue = currentValue + remainingResource;
				}
				var lastIndx = jQuery(this).find("option[value='" + maxValue + "']").index();
				jQuery(this).find('option:lt(' + lastIndx + ')').prop('disabled', false);
				jQuery(this).find('option:gt(' + lastIndx + ')').prop('disabled', true);
				jQuery(this).find('option:eq(' + lastIndx + ')').prop('disabled', false);
			});
		}

	};
	this.getOccupancy = function (resourceId, isbookable, targetContainer) {
		var occupancy = 0;
		targetContainer.find(".ddlroomsrealav-" + resourceId + "[data-isbookable='" + isbookable + "']").each(function () {
			occupancy += Number(jQuery(this).val() || 0);
		});
		return occupancy;
	};

	this.checkListDisplay = function () {
		if (localStorage.getItem('display')) {
			if (localStorage.getItem('display') == 'list') {
				jQuery('#bfi-list-view').trigger('click');
			} else {
				jQuery('#bfi-grid-view').trigger('click');
			}
		} else {
			if (typeof bfi_variables === 'undefined' || bfi_variables.bfi_defaultdisplay === 'undefined') {
				jQuery('#bfi-list-view').trigger('click');
			} else {
				if (bfi_variables.bfi_defaultdisplay == '1') {
					jQuery('#bfi-grid-view').trigger('click');
				} else {
					jQuery('#bfi-list-view').trigger('click');
				}
			}
		}
	};

    this.inizializeListView = function () {
        jQuery('#bfi-list-view').click(function () {
            jQuery('.bfi-view-changer-selected').html(jQuery(this).html());
            jQuery('#bfi-list').removeClass('bfi-grid-group');
            jQuery('#bfi-list .bfi-item').addClass('bfi-list-group-item');
            jQuery('#bfi-list .bfi-item > .bfi-row').removeClass('bfi-sameheight');
            jQuery('#bfi-list .bfi-item > .bfi-row > .bfi-img-container').addClass('bfi-col-sm-3');
            jQuery('#bfi-list .bfi-item > .bfi-row > .bfi-details-container').addClass('bfi-col-sm-9');
            jQuery('#bfi-list').trigger('cssClassChanged');
            localStorage.setItem('display', 'list');
        });
        jQuery('#bfi-grid-view').click(function () {
            jQuery('.bfi-view-changer-selected').html(jQuery(this).html());
            jQuery('#bfi-list').addClass('bfi-grid-group');
            jQuery('#bfi-list .bfi-item').removeClass('bfi-list-group-item');
            jQuery('#bfi-list .bfi-item > .bfi-row').addClass('bfi-sameheight');
            jQuery('#bfi-list .bfi-item > .bfi-row > .bfi-img-container').removeClass('bfi-col-sm-3');
            jQuery('#bfi-list .bfi-item > .bfi-row > .bfi-details-container').removeClass('bfi-col-sm-9');
            jQuery('#bfi-list').trigger('cssClassChanged');
            localStorage.setItem('display', 'grid');
        });
        jQuery('#bfi-list .bfi-item').addClass('bfi-grid-group-item');
        bookingfor.checkListDisplay();
    };

	this.easterForYear = function (year) {
		var a = year % 19;
		var b = Math.floor(year / 100);
		var c = year % 100;
		var d = Math.floor(b / 4);
		var e = b % 4;
		var f = Math.floor((b + 8) / 25);
		var g = Math.floor((b - f + 1) / 3);
		var h = (19 * a + b - d - g + 15) % 30;
		var i = Math.floor(c / 4);
		var k = c % 4;
		var l = (32 + 2 * e + 2 * i - h - k) % 7;
		var m = Math.floor((a + 11 * h + 22 * l) / 451);
		var n0 = (h + l + 7 * m + 114)
		var n = Math.floor(n0 / 31) - 1;
		var p = n0 % 31 + 1;
		var date = new Date(year, n, p);
		return date;
	};

	this.loadHolidays = function () {
		if (!bookingfor.loadedholydays) {
			var cultureCode = bfi_variables.bfi_cultureCode;
			if (cultureCode.length > 1) {
				cultureCode = cultureCode.substring(0, 2).toLowerCase();
			}
			bookingfor.holydaysTitle = ["New Year", "Epiphany", "Liberation", "Labor Day", "Republic Day", "Mid-August", "All saints", "Immaculate Conception", "Natale", "St. Stephen", "Easter", "Easter Monday", "Easter", "Easter Monday"];
			if (cultureCode == 'it') {
				bookingfor.holydaysTitle = ["Capodanno", "Epifania", "Liberazione", "Festa dei lavoratori", "Festa della Repubblica", "Ferragosto", "Tutti Santi", "Immacolata concezione", "Natale", "St. Stefano", "Pasqua", "Lunedì dell'angelo", "Pasqua", "Lunedì dell'angelo"];
			}
			bookingfor.holydays = ["0101", "0601", "2504", "0105", "0206", "1508", "0111", "0812", "2512", "2612"];
			var date = new Date;
			// Set the timestamp to midnight.
			date.setHours(0, 0, 0, 0);
			var currYear = date.getFullYear();
			var easterForCurrYear = bookingfor.easterForYear(currYear);
			var easterForNextYear = bookingfor.easterForYear(currYear + 1);

			bookingfor.holydays.push(("0" + easterForCurrYear.getDate()).slice(-2) + "" + ("0" + (easterForCurrYear.getMonth() + 1)).slice(-2) + easterForCurrYear.getFullYear());
			easterForCurrYear.setDate(easterForCurrYear.getDate() + 1);
			bookingfor.holydays.push(("0" + easterForCurrYear.getDate()).slice(-2) + "" + ("0" + (easterForCurrYear.getMonth() + 1)).slice(-2) + easterForCurrYear.getFullYear());
			bookingfor.holydays.push(("0" + easterForNextYear.getDate()).slice(-2) + "" + ("0" + (easterForNextYear.getMonth() + 1)).slice(-2) + easterForNextYear.getFullYear());
			easterForNextYear.setDate(easterForNextYear.getDate() + 1);
			bookingfor.holydays.push(("0" + easterForNextYear.getDate()).slice(-2) + "" + ("0" + (easterForNextYear.getMonth() + 1)).slice(-2) + easterForNextYear.getFullYear());
			bookingfor.loadedholydays = true;
		}
	};



	this.checkAdsBlocked = function () {
		if (!bookingfor.adsBlockedChecked) {
			this.isAdsBlocked();
			bookingfor.adsBlockedChecked = true;
		}
	};
	this.isFetchAPIsupported = function () {
		return 'fetch' in window;
	};
	this.isAdsBlocked = function () {
		if (!window.MooTools && typeof Request != 'undefined' && bookingfor.isFetchAPIsupported()) {

			var testURL = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js'

			var myInit = {
				method: 'HEAD',
				mode: 'no-cors'
			};

			var myRequest = new Request(testURL, myInit);

			fetch(myRequest).then(function (response) {
				return response;
			}).then(function (response) {
				//			console.log(response);
				//			callback(false)
				bookingfor.adsBlocked = false;
			}).catch(function (e) {
				//			console.log(e)
				//			callback(true)
				bookingfor.adsBlocked = true;
			});
		}
	};

	this.BookNow = function (obj) {
		//       debugger;
		var sendtocart = 0;

		var Order = { Resources: [], ExtraServices: [], SearchModel: {}, TotalAmount: 0, TotalDiscountedAmount: 0 };
//		Order.SearchModel = jQuery('#bfi-calculatorForm').serializeObject();
//		Order.SearchModel.MerchantId = bfi_currMerchantId;
//		Order.SearchModel.AdultCount = new Number(Order.SearchModel.adults || 0);
//		Order.SearchModel.ChildrenCount = new Number(Order.SearchModel.children || 0);
//		Order.SearchModel.SeniorCount = new Number(Order.SearchModel.seniores || 0);
//		Order.SearchModel.ChildAges = [Order.SearchModel.childages1, Order.SearchModel.childages2, Order.SearchModel.childages3, Order.SearchModel.childages4, Order.SearchModel.childages5];

		var FirstResourceId = 0;
		var ResetCart = 0;
		var currPolicy = [];
		jQuery(obj).closest(".bfi-result-list").find(".ddlrooms-indipendent ").each(function (index, ddlroom) {
			var currResId = jQuery(this).attr('data-resid');
			var currReferenceId = jQuery(this).attr('data-referenceid');
			var currRateplanId = new Number(jQuery(this).attr('data-ratePlanId') || 0);
			var currRateplanTypeId = new Number(jQuery(this).attr('data-ratePlanTypeId') || 0);
			var currQtSelected = jQuery(this).val();
			var currAvailabilityType = new Number(jQuery(this).attr('data-availabilitytype') || 1);

			if (currQtSelected > 0) {
				for (var i = 1; i <= currQtSelected; i++) {
					currPolicy.push(new Number(jQuery(this).attr('data-policyId') || 0));
					var currPaxes = jQuery(this).attr('data-paxes').split('|');
					var currPaxesAges = [];
					currPaxes.forEach(function (currPax) {
						currPaxTot = currPax.split(':')[1];
						currPaxAge = parseInt(currPax.split(':')[2]);
						for (var j = 0; j < currPaxTot; j++) {
							currPaxesAges.push(currPaxAge);
						}
					});
					var currResourceRequest = {
						ResourceId: new Number(currResId || 0),
						Name: jQuery(this).attr('data-name'),
						Brand: jQuery(this).attr('data-brand'),
						ListName: jQuery(this).attr('data-lna'),
						Category: jQuery(this).attr('data-category'),
						FromDate: jQuery(this).attr('data-checkin'),
						ToDate: jQuery(this).attr('data-checkout'),
						PolicyId: new Number(jQuery(this).attr('data-policyId') || 0),
						IsBookable: new Number(jQuery(this).attr('data-isbookable') || 0),
						PaxNumber: currPaxesAges.length,
						PaxAges: currPaxesAges,
						IncludedMeals: jQuery(this).attr('data-includedmeals'),
						TouristTaxValue: jQuery(this).attr('data-touristtaxvalue'),
						VATValue: jQuery(this).attr('data-vatvalue'),
						MerchantId: jQuery(this).attr('data-mrcid'),
						RatePlanId: currRateplanId,
						RatePlanName: jQuery(this).attr('data-ratePlanName'),
						RatePlanTypeId: currRateplanTypeId,
						AvailabilityType: currAvailabilityType,
						SelectedQt: 1,
						TotalDiscounted: jQuery(this).attr('data-baseprice'),
						TotalAmount: jQuery(this).attr('data-basetotalprice'),
						AllVariations: jQuery(this).attr('data-allvariations'),
						PercentVariation: jQuery(this).attr('data-percentvariation'),
						MinPaxes: jQuery(this).attr('data-minpaxes'),
						MaxPaxes: jQuery(this).attr('data-maxpaxes'),
						ComputedPaxes: jQuery(this).attr('data-computedpaxes'),
						PricesExtraIncluded: JSON.stringify((typeof pricesExtraIncluded[currReferenceId] !== 'undefined' && Object.keys(pricesExtraIncluded[currReferenceId]).length > 0) ? pricesExtraIncluded[currReferenceId] : {}),
						PolicyValue: jQuery(this).attr('data-policy'),
						HidePeopleAge: jQuery(this).attr('data-hidePeopleAge') == "1",
						BedConfig: jQuery(this).attr('data-bedconfig'),
						BedConfigIndex: new Number(jQuery(this).attr('data-bedconfigindex') || 0),
						ExtraServices: []
					};

					var currResourceForm = jQuery(this).closest(".bfi-table-responsive").find("form.bfi-groupdform").first();
					if (currResourceForm.length) {
						currResourceRequest.ResourceGroupItemId =  new Number(currResourceForm.find("input[name='ResourceGroupItemId']").first().val() || 0);
						currResourceRequest.ResourceGroupItemName = currResourceForm.find("input[name='ResourceGroupItemName']").first().val();
						currResourceRequest.ResourceGroupItemXPos = currResourceForm.find("input[name='ResourceGroupItemXPos']").first().val();
						currResourceRequest.ResourceGroupItemYPos = currResourceForm.find("input[name='ResourceGroupItemYPos']").first().val();
						currResourceRequest.ResourceGroupItemRowName = currResourceForm.find("input[name='ResourceGroupItemRowName']").first().val();
						currResourceRequest.ResourceGroupItemColumnName = currResourceForm.find("input[name='ResourceGroupItemColumnName']").first().val();
						currResourceRequest.ResourceGroupSectorId = currResourceForm.find("input[name='ResourceGroupSectorId']").first().val();
						currResourceRequest.ResourceGroupItemTagsIdList = currResourceForm.find("input[name='ResourceGroupItemTagsIdList']").first().val();
						currResourceRequest.ResourceGroupSectorName = currResourceForm.find("input[name='ResourceGroupSectorName']").first().val();
						currResourceRequest.ResourceGroupId =  new Number(currResourceForm.find("input[name='ResourceGroupId']").first().val() || 0);
						currResourceRequest.ResourceGroupName = currResourceForm.find("input[name='ResourceGroupName']").first().val();
					}

					if (currAvailabilityType == 2) {
						var currTr = jQuery("#bfi-timeperiod-" + currResId);
						currResourceRequest.TimeMinStart = currTr.attr("data-timestart");
						currResourceRequest.TimeMinEnd = currTr.attr("data-timeend");
						currResourceRequest.CheckInTime = currTr.attr("data-checkintime");
						currResourceRequest.TimeDuration = currTr.attr("data-duration");
						currResourceRequest.TimeLength = currTr.attr("data-timelength");
					}
					if (currAvailabilityType == 3) {
						var currTr = jQuery("#bfi-timeslot-" + currResId);
						currResourceRequest.FromDate = currTr.attr('data-checkin-ext');
						currResourceRequest.ToDate = currTr.attr('data-checkin-ext');
						currResourceRequest.TimeSlotId = currTr.attr("data-timeslotid");
						currResourceRequest.TimeSlotStart = currTr.attr("data-timeslotstart");
						currResourceRequest.TimeSlotEnd = currTr.attr("data-timeslotend");
					}

					//--------recupero extras....

					jQuery(obj).closest(".bfi-result-list").find(".services-room-" + i + "-" + currResId + "-" + currReferenceId).find(".ddlrooms").each(function (index, element) {
						var currValue = jQuery(this).val();

						var currPriceId = jQuery(this).attr("data-resid");
						var currPriceAvailabilityType = jQuery(this).attr("data-availabilityType");
						if (currValue != "0") {
							var extraValue = currPriceId + ":" + currValue;
							if (currPriceAvailabilityType == "2") {
								var currSelectData = jQuery(this).closest("tr").find(".bfi-timeperiod").first();
								extraValue += ":" + currSelectData.attr("data-checkin") + currSelectData.attr("data-timestart") + ":" + currSelectData.attr("data-duration") + "::::"
							}
							if (currPriceAvailabilityType == "3") {
								var currSelectData = jQuery(this).closest("tr").find(".bfi-timeslot").first();
								extraValue += ":::" + currSelectData.attr("data-timeslotid") + ":" + currSelectData.attr("data-timeslotstart") + ":" + currSelectData.attr("data-timeslotend") + ":" + currSelectData.attr("data-checkin") + "::::"
							}

							var currExtraService = {
								Value: extraValue,
								Name: jQuery(this).attr("data-name"),
								PriceId: currPriceId,
								CalculatedQt: currValue,
								ResourceId: currPriceId,
								TotalDiscounted: parseFloat(jQuery(this).attr('data-baseprice')) * currValue,
								TotalAmount: parseFloat(jQuery(this).attr('data-basetotalprice')) * currValue,
								Brand: jQuery(this).attr('data-brand'),
								ListName: jQuery(this).attr('data-lna'),
								Category: jQuery(this).attr('data-category'),
								RatePlanName: jQuery(this).attr('data-rateplanname'),
							}
							if (currPriceAvailabilityType == 0 || currPriceAvailabilityType == 0) {
								var currTr = jQuery(this).closest("tr").find(".bfi-period").first();
								var currDateint = currTr.attr("data-checkin");
								currExtraService.FromDate = currDateint.substr(6, 2) + "/" + currDateint.substr(4, 2) + "/" + currDateint.substr(0, 4);
								currDateint = currTr.attr("data-checkout");
								currExtraService.ToDate = currDateint.substr(6, 2) + "/" + currDateint.substr(4, 2) + "/" + currDateint.substr(0, 4);
							}
							if (currPriceAvailabilityType == 2) {
								var currTr = jQuery(this).closest("tr").find(".bfi-timeperiod").first();
								currExtraService.TimeMinStart = currTr.attr("data-timestart");
								currExtraService.TimeMinEnd = currTr.attr("data-timeend");
								currExtraService.CheckInTime = currTr.attr("data-checkintime");
								currExtraService.TimeDuration = currTr.attr("data-duration");
							}
							if (currPriceAvailabilityType == 3) {
								var currTr = jQuery(this).closest("tr").find(".bfi-timeslot").first();
								currExtraService.TimeSlotId = currTr.attr("data-timeslotid");
								currExtraService.TimeSlotStart = currTr.attr("data-timeslotstart");
								currExtraService.TimeSlotEnd = currTr.attr("data-timeslotend");
								var currDateint = currTr.attr("data-checkin");
								currExtraService.TimeSlotDate = currDateint.substr(6, 2) + "/" + currDateint.substr(4, 2) + "/" + currDateint.substr(0, 4);
							}
							var minSelectable = parseInt(jQuery(this).find('option:first').val());
							if (minSelectable > 0) {
								currResourceRequest.TotalDiscounted -= (parseFloat(jQuery(this).attr('data-baseprice'))) * minSelectable;
								currResourceRequest.TotalAmount -= (parseFloat(jQuery(this).attr('data-basetotalprice'))) * minSelectable;
							}

							currResourceRequest.ExtraServices.push(currExtraService);
							Order.ExtraServices.push(currExtraService);

						}
					});

					Order.Resources.push(currResourceRequest);
					// se ci sono sconti li aggiungo qui
					Order.Promos = [];
					if (currResourceRequest.AllVariations!="" &&currResourceRequest.AllVariations!="[]")
					{
						var discounts = JSON.parse(currResourceRequest.AllVariations.replace(/\&quot;/gm, '"'));
						for (var di = 0; di < discounts.length; di++) {
							var  promo = {};
							promo.id = discounts[di].VariationPlanId;
							promo.name = discounts[di].Name;
							if (!Order.Promos.includes(promo))
							{
								Order.Promos.push(promo);
							}
						}
					}

				}

			}
		});

		if (Order.Resources.length > 0) {
			FirstResourceId = Order.Resources[0].ResourceId;
			jQuery('.frm-order').first().html('');
			jQuery('.frm-order').first().empty();

			if (bfi_variables.bfi_eecenabled == 1) {
				var currAllItems = jQuery.makeArray(jQuery.map(Order.Resources, function (elm, idx) {
					return {
						"id": elm.ResourceId + " - Resource",
						"name": elm.Name,
						"category": elm.Category,
						"brand": elm.Brand,
						"price": elm.TotalDiscounted,
						"quantity": elm.SelectedQt,
						"variant": elm.RatePlanName.toUpperCase(),
						"list": elm.ListName,
					};
				}));
				if (typeof callAnalyticsEEc !== "undefined") {
					var currListName = currAllItems[0].list;
					callAnalyticsEEc("addProduct", currAllItems, "addToCart", "", {
						"step": 1,
						"list": currListName
					},
						"Add to Cart"
					);
					callAnalyticsEEc("addProduct", jQuery.makeArray(jQuery.map(Order.ExtraServices, function (elm, idx) {
						return {
							"id": elm.PriceId + " - Service",
							"name": elm.Name,
							"category": elm.Category,
							"brand": elm.Brand,
							"price": elm.TotalDiscounted,
							"quantity": elm.CalculatedQt,
							"variant": elm.RatePlanName.toUpperCase(),
						};
					})), "addToCart", "", {
						"step": 2,
						"list": currListName
					},
						"Add to Cart"
					);
					if (typeof Order.Promos !== "undefined" && Order.Promos.length>0 )
					{
						// se ci sono sconti li registro come un click
						callAnalyticsEEc("addPromo", Order.Promos, "promo_click", "", "", "Add to Cart");
					}
				}
			}
			var sent2Cart =  bfi_variables.bfi_sendtocart;
			if (typeof jQuery(obj).attr("data-sentocart") !== 'undefined')
			{
				sent2Cart = jQuery(obj).attr("data-sentocart");
			}
			
			bookingfor.addToCart(jQuery(obj), sent2Cart, ResetCart, Order.Resources);
//			bookingfor.addToCart(jQuery(obj), bfi_variables.bfi_sendtocart, ResetCart, Order.Resources);
		} else {
//			alert("Error, You must select a quantity!");
			jQuery(".bfiselectrooms").addClass("bfiselectrooms-error");
			var firstDDL = jQuery(".ddlrooms-indipendent").first();
			firstDDL.webuiPopover({
				content: bfi_variables.bfi_txterrorqta,
				container: document.body,
				cache: false,
				placement: "right",
				maxWidth: "150px",
				type: 'html',
				style: 'bfi-webuipopover bfi-webuipopover-error',
				onHide: function($element){
					firstDDL.webuiPopover('destroy');
					},
//				onShow: function($element){
//						setTimeout(function(){firstDDL.webuiPopover('hide');},3000);
//					},
			});
			firstDDL.webuiPopover("show");

//			alert(bfi_variables.bfi_txterrorqta);
			
		}

	};



	this.GetResourcesByIds = function (listToCheck) {
		if (listToCheck != '')
			jQuery.post(bookingfor.getActionUrl(null, null, "GetResourcesByIds", "resourcesId=" + listToCheck + "&language=" + bfi_variables.bfi_cultureCode), function (data) {

				if (typeof callfilterloading === 'function') {
					callfilterloading();
					callfilterloading = null;
				}
				jQuery.each(data || [], function (key, val) {
					$html = '';

					var $indirizzo = "";
					var $cap = "";
					var $comune = "";
					var $provincia = "";

					$indirizzo = val.Resource.AddressData;
					$cap = val.Resource.ZipCode;
					$comune = val.Resource.CityName;
					$provincia = val.Resource.RegionName;
					if (typeof val.Resource.AddressInfos !== 'undefined')
					{
						$provincia = val.Resource.AddressInfos.Level1RegionName;
					}
					if ($comune == $provincia)
					{
						$provincia = '';
					}

					addressData = bookingfor.strAddress.replace("[indirizzo]", $indirizzo);
					if (bookingfor.mobileViewMode)
					{
						addressData = bookingfor.strAddressmobile.replace("[indirizzo]", $indirizzo);
					}
					addressData = addressData.replace("[cap]", $cap);
					addressData = addressData.replace("[comune]", $comune);
					if ($provincia!='')
					{
						addressData = addressData.replace("[provincia]", '('+$provincia+')');
					}else{
						addressData = addressData.replace("[provincia]", '');
					}
					jQuery("#address" + val.Resource.ResourceId).html(addressData);
					jQuery("#mapaddress" + val.Resource.ResourceId).html(addressData);
					jQuery("#addressdist" + val.Resource.ResourceId).show();

					if (val.Resource.TagsIdList != null && val.Resource.TagsIdList != '') {
						var mglist = val.Resource.TagsIdList.split(',');
						$htmlmg = '';
						jQuery.each(mglist, function (key, mgid) {
							if (typeof bookingfor.tagLoaded[mgid] !== 'undefined') {
								$htmlmg += bookingfor.tagLoaded[mgid];
							}
						});
						jQuery("#bfitags" + val.Resource.ResourceId).html($htmlmg);
					}
					if (val.Resource.TermsOfUse!= null)
					{
						jQuery("#bfitermofuse" + val.Resource.ResourceId).show();
						jQuery("#bfitermofusecontent" + val.Resource.ResourceId).html(val.Resource.TermsOfUse);
						jQuery("#bfitermofuse" + val.Resource.ResourceId + " .bfi-result-singleitem-termofuse-title").on( "click", function() {
							var bfi_wuiP_width = 800;
							if (jQuery(window).width() < bfi_wuiP_width) {
								bfi_wuiP_width = jQuery(window).width() * 0.9;
							}
							jQuery("#bfitermofusecontent" + val.Resource.ResourceId).dialog({
								closeText: "",
								title: jQuery("#bfitermofuse" + val.Resource.ResourceId + " .bfi-result-singleitem-termofuse-title").attr("title"),
								height: 'auto',
								width: bfi_wuiP_width,
								resizable: true,
								modal: true,
								dialogClass: 'bfi-dialog bfi-termofuse',
								clickOutside: true,
								clickOutsideTrigger: ".bfi-result-singleitem-termofuse",
							});
						});
					}

					if (val.Resource.Description!= null && val.Resource.Description != '' && jQuery("#bfiresdescription"+val.Resource.ResourceId)){
						$html += bookingfor.nl2br(jQuery("<p>" + bookingfor.stripbbcode(val.Resource.Description) + "</p>").text());
						jQuery("#bfiresdescription"+val.Resource.ResourceId).data('jquery.shorten', false);
						jQuery("#bfiresdescription"+val.Resource.ResourceId).html($html);
						bookingfor.shortenText(jQuery("#bfiresdescription"+val.Resource.ResourceId),250);
					}
					if (val.Resource.ExperienceTime!= null && val.Resource.ExperienceTime != '' && jQuery("#bfiexperiencetimelength"+val.Resource.ResourceId)){

						jQuery("#bfiexperiencetimelength"+val.Resource.ResourceId).html(val.Resource.ExperienceTime);
						jQuery("#bfiexperiencetimelength"+val.Resource.ResourceId).show();
					}
					if (val.Resource.LiveGuideLanguages!= null && val.Resource.LiveGuideLanguages != '' && jQuery("#bfiliveguidelanguages"+val.Resource.ResourceId)){

						jQuery("#bfiliveguidelanguages"+val.Resource.ResourceId).html(val.Resource.LiveGuideLanguages);
						jQuery("#bfiliveguidelanguages"+val.Resource.ResourceId).show();
					}

					jQuery(".container" + val.Resource.ResourceId).click(function (e) {
						var $target = jQuery(e.target);
						if ($target.is("div") || $target.is("p")) {
							document.location = jQuery(".nameAnchor" + val.Resource.ResourceId).attr("href");
						}
					});
				});
				if (typeof bfiTooltip  !== "function") {
					jQuery.widget.bridge('bfiTooltip', jQuery.ui.tooltip );
				}
				if (typeof bfiTooltip  !== "function") {
					jQuery.widget.bridge('bfiTooltip', jQuery.ui.tooltip );
				}
				jQuery('[data-toggle="tooltip"]').bfiTooltip({
					position: { my: 'center bottom', at: 'center top-10' },
					tooltipClass: 'bfi-tooltip bfi-tooltip-top '
				});
			}, 'json');
	};

	this.GetMerchantsByIds = function (listToCheck) {
		if (listToCheck != '')
			jQuery.post(bookingfor.getActionUrl(null, null, "GetMerchantsByIds", "merchantsId=" + listToCheck + "&language=" + bfi_variables.bfi_cultureCode), function (data) {

				if (typeof callfilterloading === 'function') {
					callfilterloading();
					callfilterloading = null;
				}
				jQuery.each(data || [], function (key, val) {
					$html = '';

					if (val.AddressData != '') {
						var merchAddress = "";
						var $indirizzo = "";
						var $cap = "";
						var $comune = "";
						var $provincia = "";

						$indirizzo = val.AddressData.Address;
						$cap = val.AddressData.ZipCode;
						$comune = val.AddressData.CityName;
						$provincia = val.AddressData.Level1RegionName;

						merchAddress = bookingfor.strAddress.replace("[indirizzo]", $indirizzo.toLowerCase());
						if (bookingfor.mobileViewMode)
						{
							merchAddress = bookingfor.strAddressmobile.replace("[indirizzo]", $indirizzo.toLowerCase());
						}
						merchAddress = merchAddress.replace("[cap]", $cap);
						merchAddress = merchAddress.replace("[comune]", $comune.toLowerCase());
						merchAddress = merchAddress.replace("[provincia]", '('+ $provincia.toLowerCase() +')' );
						jQuery("#address" + val.MerchantId).append(merchAddress + ' <span class="bfi-item-address-dot-separator"></span>');
						jQuery("#mapaddress" + val.MerchantId).append(merchAddress);
						jQuery("#addressdist" + val.MerchantId).show();
					}
					if (val.TagsIdList != null && val.TagsIdList != '') {
						var mglist = val.TagsIdList.split(',');
						$htmlmg = '';
						jQuery.each(mglist, function (key, mgid) {
							if (typeof bookingfor.tagLoaded[mgid] !== 'undefined') {
								$htmlmg += bookingfor.tagLoaded[mgid] + " ";
							}
						});
						jQuery("#bfitags" + val.MerchantId).html($htmlmg);
					}
// TODO : nuova
					if (val.Description!= null && val.Description != ''){
						$html += bookingfor.nl2br(jQuery("<p>" + bookingfor.stripbbcode(val.Description) + "</p>").text());
						jQuery("#bfidescription"+val.MerchantId).data('jquery.shorten', false);
						jQuery("#bfidescription"+val.MerchantId).html($html);
//						jQuery("#bfidescription"+val.MerchantId).shorten(bfishortenOption);
						bookingfor.shortenText(jQuery("#bfidescription"+val.MerchantId),250);

						jQuery("#bfiresdescription"+val.MerchantId).data('jquery.shorten', false);
						jQuery("#bfiresdescription"+val.MerchantId).html($html);
						bookingfor.shortenText(jQuery("#bfiresdescription"+val.MerchantId),250);

					}
					jQuery("#container" + val.MerchantId).click(function (e) {
						var $target = jQuery(e.target);
						if ($target.is("div") || $target.is("p")) {
							document.location = jQuery("#nameAnchor" + val.MerchantId).attr("href");
						}
					});
				});
				if (typeof bfiTooltip  !== "function") {
					jQuery.widget.bridge('bfiTooltip', jQuery.ui.tooltip );
				}
				jQuery('[data-toggle="tooltip"]').bfiTooltip({
					position: { my: 'center bottom', at: 'center top-10' },
					tooltipClass: 'bfi-tooltip bfi-tooltip-top '
				});
			}, 'json');
	};

	this.getResourcegroupByIds = function (listToCheck) {
		if (listToCheck != '')
			jQuery.post(bookingfor.getActionUrl(null, null, "getResourcegroupByIds", "ids=" + listToCheck + "&language=" + bfi_variables.bfi_cultureCode), function (data) {

				if (typeof callfilterloading === 'function') {
					callfilterloading();
					callfilterloading = null;
				}
				jQuery.each(data || [], function (key, val) {
					$html = '';

					if (val.AddressData != '') {
						var $indirizzo = "";
						var $cap = "";
						var $comune = "";
						var $provincia = "";

						$indirizzo += val.Address;
						$cap += val.ZipCode;
						$comune += val.CityName;
						$provincia += val.RegionName;

						addressData = bookingfor.strAddress.replace("[indirizzo]", $indirizzo.toLowerCase());
						if (bookingfor.mobileViewMode)
						{
							addressData = bookingfor.strAddressmobile.replace("[indirizzo]", $indirizzo);
						}
						addressData = addressData.replace("[cap]", $cap);
						addressData = addressData.replace("[comune]", $comune.toLowerCase());
//						addressData = addressData.replace("[provincia]", $provincia.toLowerCase());
						if ($comune == $provincia)
						{
							$provincia = '';
						}
						if ($provincia!='')
						{
							addressData = addressData.replace("[provincia]", '('+$provincia.toLowerCase()+')');
						}else{
							addressData = addressData.replace("[provincia]", '');
						}

						jQuery("#address" + val.CondominiumId).append(addressData);
						jQuery("#mapaddress" + val.CondominiumId).append(addressData);
						jQuery("#addressdist" + val.CondominiumId).show();
					}
					if (val.TagsIdList != null && val.TagsIdList != '') {
						var mglist = val.TagsIdList.split(',');
						$htmlmg = '';
						jQuery.each(mglist, function (key, mgid) {
							if (typeof bookingfor.tagLoaded[mgid] !== 'undefined') {
								$htmlmg += bookingfor.tagLoaded[mgid];
							}
						});
						jQuery("#bfitags" + val.CondominiumId).html($htmlmg);
					}
// TODO : nuova
					if (val.Description!= null && val.Description != ''){
						$html += bookingfor.nl2br(jQuery("<p>" + bookingfor.stripbbcode(val.Description) + "</p>").text());
						jQuery("#bfidescription"+val.CondominiumId).data('jquery.shorten', false);
						jQuery("#bfidescription"+val.CondominiumId).html($html);
						bookingfor.shortenText(jQuery("#bfidescription"+val.CondominiumId),250);

						jQuery("#bfiresdescription"+val.CondominiumId).data('jquery.shorten', false);
						jQuery("#bfiresdescription"+val.CondominiumId).html($html);
						bookingfor.shortenText(jQuery("#bfiresdescription"+val.CondominiumId),250);

					}
					jQuery("#container" + val.CondominiumId).click(function (e) {
						var $target = jQuery(e.target);
						if ($target.is("div") || $target.is("p")) {
							document.location = jQuery("#nameAnchor" + val.CondominiumId).attr("href");
						}
					});
				});
				if (typeof bfiTooltip  !== "function") {
					jQuery.widget.bridge('bfiTooltip', jQuery.ui.tooltip );
				}
				jQuery('[data-toggle="tooltip"]').bfiTooltip({
					position: { my: 'center bottom', at: 'center top-10' },
					tooltipClass: 'bfi-tooltip bfi-tooltip-top '
				});
			}, 'json');
	};

};


bookingfor.bfiCheckgroup = function (obj) {
	var currContainer = jQuery(obj).closest(".bfi-table-resources-step1").first();
	var targetContainer = jQuery(".bfi-table-resources-step1").last();
	targetContainer.find(".ddlrooms-indipendent").each(function (index, objDdl) {
		jQuery(this).val(0);
	});
	var allQt = new Array();
	currContainer.find(".ddlrooms-indipendent").each(function (index, objDdl) {

		var currRealavailproductid = jQuery(this).attr('data-realavailproductid');
		var currPaxes = jQuery(this).attr('data-paxes');
		var currRateplantypeid = jQuery(this).attr('data-rateplantypeid');
		var currResid = jQuery(this).attr('data-resid');
		var indx = ".ddlrooms-indipendent[data-resid='" + currResid + "'][data-realavailproductid='" + currRealavailproductid + "'][data-paxes='" + currPaxes + "'][data-rateplantypeid='" + currRateplantypeid + "']";
		if (!(indx in allQt)) {
			allQt[indx] = 1;
		} else {
			allQt[indx] += 1;
		}
	});
	for (var key in allQt) {
		var currTrgDll = targetContainer.find(key);
		currTrgDll.val(allQt[key]);
		currTrgDll.closest("tr").first().addClass("bfi-tr-selected");
	}
	bfi_UpdateQuote(targetContainer);
	jQuery([document.documentElement, document.body]).animate({
		scrollTop: targetContainer.offset().top
	}, 1000);
};

bookingfor.ddlroomsgroupclk = function (obj) {
	jQuery(".bfi-tr-selected").removeClass("bfi-tr-selected");
};

bookingfor.isEquivalent = function (a, b, c) {
	// Create arrays of property names
	var aProps = Object.getOwnPropertyNames(a);
	var bProps = Object.getOwnPropertyNames(b);
	var cProps = c.split(',');

	// If number of properties is different,
	// objects are not equivalent
	if (aProps.length != bProps.length) {
		return false;
	}

	for (var i = 0; i < aProps.length; i++) {
		var propName = aProps[i];
		if (cProps.length > 0 && cProps.indexOf(propName) > -1) {
			continue;
		}
		// If values of same property are not equal,
		// objects are not equivalent
		if (a[propName] !== b[propName]) {
			return false;
		}
	}

	// If we made it this far, objects
	// are considered equivalent
	return true;
};
bookingfor.removeDuplicates = function (str, separator) {
           if (separator=="")
           {
			   separator = ",";
           }
		   var unic = (str+"").split(separator).filter(function(item, pos,self) {
              return self.indexOf(item) == pos;
           });
		   return unic.join(separator);
}
function bficheckshowhideSelector(obj) {
		var currTd = jQuery(obj).closest("tr").find(".bfiselectrooms").first(); 
		var currAction = jQuery(obj).attr('data-type');
		if (currAction =="plus")
		{
			currTd.attr("style", "display: block !important");
			jQuery(obj).closest("td").find(".bfi-btn-mobile-minus").show();
			jQuery(obj).closest("tr").addClass("bfi-mobile-view-selected");
		}else{
			currTd.attr("style", "display: none !important");
			jQuery(obj).closest("td").find(".bfi-btn-mobile-plus").show();
			jQuery(obj).closest("tr").removeClass("bfi-mobile-view-selected");
		}
		jQuery(obj).hide();
};

jQuery(document).ready(function () {
	bookingfor.checkMobileView();

	jQuery(document).on('click tap', '.bfi-mobile-step-next', function (e) {
		bfi_ChangeVariation(this);
	});
	jQuery(document).on('click tap', '.bfi-mobile-step2-next', function (e) {
		bookingfor.BookNow(this);
	});
	jQuery(document).on('click tap', '.bfi-btn-mobile', function (e) {
		bficheckshowhideSelector(jQuery(this));
	});
	// add/remove value on input 
	jQuery(document).on('click tap', '.btn-number-person', function (e) {
		e.preventDefault();
		type = jQuery(this).attr('data-type');
		currField = jQuery(this).attr('data-field');
		var input = jQuery(this).closest("div").find(currField).first(); 
		var currentVal = parseInt(input.val());
		var defaultVal = input.prop("defaultValue");;
		var currentMinVal = parseInt(input.attr('min'));
		var defaultMaxVal = parseInt(input.attr('max'));

		if (!isNaN(currentVal) && currentVal >= currentMinVal && currentVal <= defaultMaxVal) {
			var newVal = currentVal;
			if (type == 'minus') {
				newVal -= 1;
			} else if (type == 'plus') {
				newVal += 1;
			}else if (type == 'remove') {
				newVal = defaultVal;
			}
//			if (newVal == defaultVal)
//			{
//				bficheckshowhideSelector(input.closest("div").find(".bfi-btn-mobile-minus"));
//			}
			input.val(newVal);
			input.trigger("click");
			input.trigger("onchange");
		}
	});
	// check correct value
	jQuery(document).on('click onchange keyup', '.bfi-input-number', function (e) {
		var input = jQuery(this); 
		var currentVal = parseInt(input.val());
		var defaultVal = input.prop("defaultValue");
		var lastVal = input.attr('data-lastval') || defaultVal;
		
		var divShowPerson = jQuery(this).closest(".bfi-showperson");
		var maxPerson = new Number(divShowPerson.attr('data-max') || 10);
		var currModID =divShowPerson.attr('data-currmodid');

		var numAdults = new Number(jQuery("#bfi-adult" + currModID).val() || 0);
		var numSeniores = new Number(jQuery("#bfi-senior" + currModID).val() || 0);
		var numChildren = new Number(jQuery("#bfi-child" + currModID).val() || 0);
		
		var currentMinVal = parseInt(input.attr('min'));
		var defaultMaxVal = parseInt(input.attr('max'));
		if (!isNaN(currentVal) && currentVal < currentMinVal) {
			input.val(currentMinVal);
		}
		if (!isNaN(currentVal) && currentVal > defaultMaxVal) {
			input.val(defaultMaxVal);
		}

		//conto tutte le persone
		var currContainer =input.closest(".bfi-showperson");
		var totPerson = 0;
		jQuery.each(currContainer.find(".bfi-input-number"), function (i, itm) {
			totPerson += new Number(jQuery(itm).val() || 0);
		});

//		if ( (numAdults+numSeniores+numChildren) > maxPerson) {
		if ( (totPerson) > maxPerson) {
			input.val(lastVal);
		}
		input.attr('data-lastval',input.val());
	});

	// add/remove value on select
	jQuery(document).on('click tap', '.btn-number', function (e) {
		e.preventDefault();
		type = jQuery(this).attr('data-type');
		var input = jQuery(this).closest("tr").find(".ddlrooms").first(); 
		var currentVal = parseInt(input.val());
		var defaultVal = input.find("option:first-child").val();
		if (!isNaN(currentVal)) {
			var newVal = currentVal;
			if (type == 'minus') {
				newVal -= 1;

				//if (currentVal > input.attr('min')) {
				//	input.val(currentVal - 1).change();
				//}
				//if (parseInt(input.val()) == input.attr('min')) {
				//	jQuery(this).attr('disabled', true);
				//}

			} else if (type == 'plus') {
				newVal += 1;

				//if (currentVal < input.attr('max')) {
				//	input.val(currentVal + 1).change();
				//}
				//if (parseInt(input.val()) == input.attr('max')) {
				//	jQuery(this).attr('disabled', true);
				//}

			}else if (type == 'remove') {
				newVal = defaultVal;
			}
			if (newVal == defaultVal)
			{
				bficheckshowhideSelector(input.closest("tr").find(".bfi-btn-mobile-minus"));
			}
			var lastIndx = jQuery(input).find("option[value='" + newVal + "']");
			if (lastIndx.length && !lastIndx.prop('disabled')) {
				input.val(newVal);
				input.trigger("click");
				input.trigger("onchange");
				//bookingfor.checkMaxSelect(input);
				//bookingfor.checkBookable(input);
				//bfi_UpdateQuote(input);
			}
			//} else {
			//	input.val(0);
		}
	});
	if (typeof jQuery.fn.button !== 'undefined' && typeof jQuery.fn.button.noConflict !== 'undefined') {
		var btn = jQuery.fn.button.noConflict(); // reverts $.fn.button to jqueryui btn
		jQuery.fn.btn = btn; // assigns bootstrap button functionality to $.fn.btn
	}
	jQuery('.bfi-options-help i').webuiPopover({trigger:'hover',style:'bfi-webuipopover'});
//	jQuery('.bfi-options-help.bfimobile i').webuiPopover({trigger:'hover',placement:'auto',style:'bfi-webuipopover'});
//	jQuery('.bfi-options-help:not(.bfimobile) i').webuiPopover({ trigger: 'hover', placement: 'right-bottom', style: 'bfi-webuipopover' });
	jQuery(document).on('click tap', ".bfi-percent-discount, .bfi-discounted-price", function (e) {
		e.preventDefault();
		e.stopPropagation();
		var bfi_wuiP_width = 400;
		if (jQuery(window).width() < bfi_wuiP_width) {
			bfi_wuiP_width = jQuery(window).width() * 0.7;
		}
		var showdiscount = function (obj, text) {
			obj.find("i").first().switchClass("fa-spinner fa-spin", "fa-question-circle")
			obj.webuiPopover({
				content: text,
				container: document.body,
				closeable: true,
				placement: bookingfor.mobileViewMode? 'bottom-left' : 'auto-bottom',
				dismissible: true,
				trigger: 'manual',
				type: 'html',
				width: bfi_wuiP_width,
				style: 'bfi-webuipopover'
			});
			obj.webuiPopover('show');

		};
		var discountIds = jQuery(this).attr('rel');

		if (!bookingfor.offersLoaded.hasOwnProperty(discountIds)) {
			jQuery(this).find("i").first().switchClass("fa-question-circle", "fa-spinner fa-spin")
			bookingfor.GetDiscountsInfo(discountIds, bfi_variables.bfi_cultureCode, jQuery(this), showdiscount);

		} else {
			showdiscount(jQuery(this), bookingfor.offersLoaded[discountIds]);
		}
	});
	jQuery(".bfi-percent-discount, .bfi-discounted-price").focusout(function () {
		jQuery(this).webuiPopover('hide');
	});


	jQuery('a.boxedpopup').on('click', function (e) {
		var width = jQuery(window).width() * 0.9;
		var height = jQuery(window).height() * 0.9;
		if (width > 800) { width = 870; }
		if (height > 600) { height = 600; }

		e.preventDefault();
		var page = jQuery(this).attr("href")
		//		var pagetitle = jQuery(this).attr("title")

		jQuery.post(page, function (data) {
			jQuery.unblockUI();
			var $dialog = jQuery('<div id="boxedpopupopen"></div>')
				.html(data)
				.dialog({
					autoOpen: false,
					modal: true,
					height: height,
					width: width,
					fluid: true, //new option
					//				title: pagetitle
					dialogClass: 'bfi-dialog bfi-dialog-contact'
				});
			$dialog.dialog('open');
			if (typeof window.BFIInitReCaptcha2 === "function") {
				// safe to use the function
				BFIInitReCaptcha2();
			}
		});
	});

	jQuery(window).resize(function () {
		var bpOpen = jQuery("#boxedpopupopen");
		var wWidth = jQuery(window).width();
		var dWidth = wWidth * 0.9;
		var wHeight = jQuery(window).height();
		var dHeight = wHeight * 0.9;
		if (dWidth > 800) { dWidth = 870; }
		if (dHeight > 600) { dHeight = 600; }
		bpOpen.dialog("option", "width", dWidth);
		bpOpen.dialog("option", "height", dHeight);
		bpOpen.dialog("option", "position", "center");
		if (!bookingfor.mobileViewMode)
		{
			jQuery("table.bfi-table-resources-sticked").each(function () {
				var existSticked = jQuery(this).find("thead.bfi-sticked").first();
				var $currDivBook = jQuery(this).find(".bfi-book-now").first();
				if (existSticked.length) { existSticked.remove(); };
				if ($currDivBook.length) { $currDivBook.removeClass("bfi-sticked"); };
			});
		}

	});

	if (!bookingfor.mobileViewMode) // solo se non è mobile!
	{
		jQuery(window).scroll(function () {
			jQuery("table.bfi-table-resources-sticked").each(function () {
				var existSticked = jQuery(this).find("thead.bfi-sticked").first();
				var $currDivBook = jQuery(this).find(".bfi-book-now").first();
				var corr = 0;
				if (jQuery(this).hasClass("bfi-table-selectableprice-container")) {
					corr = 75;
				}
				if ((jQuery(this).closest(".bfi-result-list").first().offset().top + corr) < jQuery(window).scrollTop()) {

					if (!$currDivBook.hasClass("bfi-sticked")) {
						$currDivBook.addClass("bfi-sticked");
					}
					if (!existSticked.length) {
						var $currthead = jQuery(this).find("thead").first();
						var newthead = jQuery($currthead.clone());
						newthead.appendTo(jQuery(this));
						newthead.width(jQuery(this).width());
						newthead.css('top', 0);
						newthead.addClass("bfi-sticked");
					}


					if ((jQuery(this).closest(".bfi-result-list").offset().top + jQuery(".bfi-result-list").height()) < (jQuery(window).scrollTop() + $currDivBook.height())) {
						//						console.log("corr" +corr )
						$currDivBook.css('top', ((jQuery(".bfi-result-list").offset().top + jQuery(".bfi-result-list").height()) - (jQuery(window).scrollTop() + $currDivBook.height())) + 'px');
					} else {
						$currDivBook.css('top', '50px');
					}
					if ((jQuery(this).closest(".bfi-result-list").offset().top + jQuery(".bfi-result-list").height()) < jQuery(window).scrollTop()) {
						existSticked.hide();
						$currDivBook.hide();
					} else {
						existSticked.show();
						$currDivBook.show();
					}
				} else {
					existSticked.remove();
					$currDivBook.removeClass("bfi-sticked");
				}
			});
		});
	}

	bookingfor.checkAdsBlocked();
	bookingfor.loadHolidays();

});

if (typeof String.prototype.endsWith !== 'function') {
	String.prototype.endsWith = function (suffix) {
		return this.indexOf(suffix, this.length - suffix.length) !== -1;
	};
}

if (typeof jQuery.fn.serializeObject !== 'function') {
	jQuery.fn.serializeObject = function () {
		var o = {};
		var a = this.serializeArray();
		jQuery.each(a, function () {
			if (o[this.name]) {
				if (!o[this.name].push) {
					o[this.name] = [o[this.name]];
				}
				o[this.name].push(this.value || '');
			} else {
				o[this.name] = this.value || '';
			}
		});
		return o;
	};
}


function bfi_quoteCalculatorServiceChanged(el) {

	var selectedExtra = parseInt(jQuery(el).val());

	var currProdRelatedId = jQuery(el).attr("data-resid");
	var currMaxAvailability = servicesAvailability[currProdRelatedId];
	jQuery(".ddlextras.ddlrooms-" + currProdRelatedId).each(function () {
		var currselectableprice = jQuery(this).val();
		currMaxAvailability -= parseInt(currselectableprice);
	});

	//rebuild ddl
	jQuery(".ddlextras.ddlrooms-" + currProdRelatedId).not(this).each(function () {
		var currMaxValue = jQuery(this).children("option:last").val();
		var currValue = parseInt(jQuery(this).val());

		jQuery(this).children("option").prop('disabled', false);
		var maxValue = currValue + currMaxAvailability;

		if (currMaxValue > maxValue) {
			var prodRelatedId = jQuery(this).attr("data-resid");
			var selIndx = jQuery(this).children("option").index(jQuery(this).children("option[value='" + maxValue + "']"));
			if (selIndx > -1) {
				jQuery(this).children("option:gt(" + selIndx + ")").prop('disabled', true);

			}
		}
	});

	bfi_getcompleterateplansstaybyrateplanid(jQuery(el));
	//	console.log("Recalc");
}

function parsePaxAges(strPaxes) {
	var splPaxes = strPaxes.split('|');
	var returnObj = {
		TotalPaxes: 0,
		AllAges: [],
		Adults: 0,
		Seniors: 0,
		Children: 0,
		ChildrenAges: [],
	};
	jQuery.each(strPaxes.split('|'), function(i, px) {
		returnObj.TotalPaxes++;
		returnObj.AllAges.push(px.split(':')[2]);
		for (var qt = 0; qt < parseInt(px.split(':')[1]); qt++) {
			switch(px.split(':')[3]) {
				case "0":
					returnObj.Adults++;
					break;
				case "1":
					returnObj.Seniors++;
					break;
				case "2":
					returnObj.Children++;
					returnObj.ChildrenAges.push(px.split(':')[2]);
					break;
			}
		}
	});
	return returnObj;
}

function bfi_getcompleterateplansstaybyrateplanid($el) {
	//console.log("calcolo prezzo per id: " + priceId);

	//	debugger;
	var selectedExtra = parseInt($el.val());
	var priceId = $el.attr("data-resid");
	var resId = $el.attr("data-bindingproductid");
	var availabilityTypeRes = $el.attr("data-availabilityTypeRes");
	var rateplanId = $el.attr("data-rateplanid");
	var currTable = $el.closest("table");
	bookingfor.waitSimpleWhiteBlock(currTable);

	var extrasselect = [];
	jQuery(currTable).find(".ddlextras.ddlrooms").each(function (index, element) {
		jQuery(this).closest("tr").removeClass("bfi-mobile-view-selected");
		var currValue = jQuery(this).val();
		var currResId = jQuery(this).attr("data-resid");
		var currAvailabilityType = jQuery(this).attr("data-availabilityType");
		if (currValue != "0") {
			jQuery(this).closest("tr").addClass("bfi-mobile-view-selected");

			var extraValue = currResId + ":1"; // + currValue;
			if (currAvailabilityType == "0" || currAvailabilityType == "1") {
				var currSelectData = jQuery(this).closest("tr").find(".bfi-period").first();
				extraValue += ":::::::" + currSelectData.attr("data-checkin") + ":" + currSelectData.attr("data-checkout") + "::"
			}
			if (currAvailabilityType == "2") {
				var currSelectData = jQuery(this).closest("tr").find(".bfi-timeperiod").first();
				extraValue += ":" + currSelectData.attr("data-checkin") + currSelectData.attr("data-timestart").replace(":","") + "00:" + currSelectData.attr("data-duration") + "::::"
			}
			if (currAvailabilityType == "3") {
				var currSelectData = jQuery(this).closest("tr").find(".bfi-timeslot").first();
				extraValue += ":::" + currSelectData.attr("data-timeslotid") + ":" + currSelectData.attr("data-timeslotstart") + ":" + currSelectData.attr("data-timeslotend") + ":" + currSelectData.attr("data-checkin") + "::::"
			}

			extrasselect.push(extraValue);
		}
	});

	obj = jQuery("tr[id^=data-id-" + resId + "-" + rateplanId + "]");
	var ddlroom = jQuery(obj).find(".ddlrooms");

	//	var searchModel = jQuery('#bfi-calculatorForm').serializeObject();
	currForm = $el.closest(".bfi-table-responsive").find("form.bfi-groupdform").first();
	if (currForm.length == 0) {
		currForm = jQuery('#bfi-calculatorForm');
	}
	//	var dataarray = jQuery('#bfi-calculatorForm').serializeArray();
	var dataarray = currForm.serializeArray();
	dataarray.push({ name: 'resourceId', value: resId });
	dataarray.push({ name: 'id', value: resId });

	if ($el.attr("data-paxes") && $el.attr("data-paxes").length > 0) {
		var paxesConfig = parsePaxAges($el.attr("data-paxes"));
		dataarray.push({ name: 'adults', value: paxesConfig.Adults });
		dataarray.push({ name: 'seniores', value: paxesConfig.Seniors });
		dataarray.push({ name: 'children', value: paxesConfig.Children });
		jQuery.each(paxesConfig.ChildrenAges, function(i, px) {
			dataarray.push({ name: 'childages' + (i + 1), value: px });
		});
	}
	
	var accomodation = {
		ResourceId: resId,
		RatePlanId: rateplanId,
		AvailabilityType: availabilityTypeRes,
		ProductAvailabilityType: availabilityTypeRes,
		TimeMinStart: 0,
		TimeMinEnd: 0,
		FromDate: "",
		ExtraServices: extrasselect
	};

	if (availabilityTypeRes == 2) {

		var currTr = jQuery("#bfi-timeperiod-" + resId);
		dataarray.push({ name: 'timeMinStart', value: currTr.attr("data-timestart") });
		dataarray.push({ name: 'timeMinEnd', value: currTr.attr("data-timeend") });
		dataarray.push({ name: 'CheckInTime', value: currTr.attr("data-checkintime") });
		dataarray.push({ name: 'duration', value: currTr.attr("data-duration") });
	}
	if (availabilityTypeRes == 3) {

		var currTr = jQuery("#bfi-timeslot-" + resId);
		accomodation.TimeSlotId = currTr.attr("data-timeslotid");
		accomodation.TimeSlotStart = currTr.attr("data-timeslotstart");
		accomodation.TimeSlotEnd = currTr.attr("data-timeslotend");
		accomodation.checkin = currTr.attr("data-checkin");
		var fromDatestr = jQuery.datepicker.formatDate("yy-mm-dd", jQuery.datepicker.parseDate("yymmdd", accomodation.checkin));
		var fromDate = new Date(fromDatestr+ 'T00:00:00Z');
		var tmpDate = new Date();
		tmpDate.setHours(0, 0, 0, 0);
		var newTmpDateStart = bookingfor.dateAdd(tmpDate, "minute", Number(accomodation.TimeSlotStart));
		var newTmpDateEnd = bookingfor.dateAdd(tmpDate, "minute", Number(accomodation.TimeSlotEnd ));
		var newValStartHours = bookingfor.pad(newTmpDateStart.getHours(), 2) + ":" + bookingfor.pad(newTmpDateStart.getMinutes(), 2);
		var newValEndHours = bookingfor.pad(newTmpDateEnd.getHours(), 2) + ":" + bookingfor.pad(newTmpDateEnd.getMinutes(), 2);
		var newValStart = new Date(fromDatestr+ "T" + newValStartHours + ":00Z");
		var newValEnd = new Date(fromDatestr+ "T" + newValEndHours + ":00Z");
		var diffMs = (newValEnd - newValStart);
		var duration = Math.floor(diffMs / 60000);
	
		dataarray.push({ name: 'duration', value: duration });
		dataarray.push({ name: 'timeSlotId', value: currTr.attr("data-timeslotid") });

	}

	dataarray.push({ name: 'pricetype', value: accomodation.RatePlanId });
	dataarray.push({ name: 'rateplanid', value: accomodation.RatePlanId });
	//	dataarray.push({name: 'timeMinStart', value: accomodation.TimeMinStart});
	//	dataarray.push({name: 'timeMinEnd', value: accomodation.TimeMinEnd});
	dataarray.push({ name: 'selectableprices', value: accomodation.ExtraServices.join("|") });
	dataarray.push({ name: 'availabilitytype', value: accomodation.AvailabilityType });
	dataarray.push({ name: 'ProductAvailabilityType', value: accomodation.AvailabilityType });
	dataarray.push({ name: 'getAllPaxConfigurations', value: "false"});
	
	//	dataarray.push({name: 'searchModel', value: searchModel});

	var jqxhr = jQuery.ajax({
		url: bookingfor.getActionUrl(null, null, "getCompleteRateplansStay"),
		type: "POST",
		dataType: "json",
		data: dataarray
	});

	jqxhr.done(function (result, textStatus, jqXHR) {
		if (result) {

			bfi_UpdateQuote($el);

			if (result.length > 0) {
				//                    debugger;
				var currResult = jQuery.grep(result, function (rs) {
					return (rs.RatePlanId == parseInt(accomodation.RatePlanId));
				});

				currStay = currResult[0].SuggestedStay;
				var currTr = $el.closest("tr");
				var CalculatedPrices = JSON.parse(currResult[0].CalculatedPricesString);
				//                    console.log(CalculatedPrices)
				var showPrice = false;

				var currentDivPrice = jQuery(currTr).find(".bfi-totalextrasselect");
				currentDivPrice.hide();

				var currTotalPriceDivPrice = 0;
				var currDiscountedPriceDivPrice = 0;
				var simpleDiscountIdsDivPrice = "";


				CalculatedPrices.forEach(function (cprice) {
					//if (cprice.PriceId == priceId) {
					if (cprice.RelatedProductId == priceId) {

						//                            console.log("Visualizzo prezzo id: " + priceId);

						showPrice = true;
						//						cprice.TotalPrice = cprice.TotalAmount;
						//						cprice.DiscountedPrice = cprice.TotalDiscounted;
						currTotalPriceDivPrice += cprice.TotalAmount;
						currDiscountedPriceDivPrice += cprice.TotalDiscounted;
						var simpleDiscountIds = [];
						cprice.Variations.forEach(function (variation) {
							simpleDiscountIds.push(variation.VariationPlanId);
						});
						simpleDiscountIdsDivPrice += simpleDiscountIds.join(",");
					}
				});

				if (showPrice) {
					var curr_bfi_price = currTr.find(".bfi-price");
					var curr_bfi_discounted_price = currTr.find(".bfi-discounted-price");
					var curr_percent_discount = currTr.find(".bfi-percent-discount");

					var ddlroom = currTr.find(".ddlextras.ddlrooms");
					ddlroom.attr("data-baseprice", bookingfor.number_format(currDiscountedPriceDivPrice, 2, '.', ''));
					ddlroom.attr("data-basetotalprice", bookingfor.number_format(currTotalPriceDivPrice, 2, '.', ''));
					ddlroom.attr("data-price", bookingfor.priceFormat(currDiscountedPriceDivPrice, 2, '.', ''));
					ddlroom.attr("data-totalprice", bookingfor.priceFormat(currTotalPriceDivPrice, 2, '.', ''));

					curr_bfi_price.html(bookingfor.priceFormat(currDiscountedPriceDivPrice, 2, ',', '.'));
					curr_bfi_discounted_price.html(bookingfor.priceFormat(currTotalPriceDivPrice, 2, ',', '.'));

					if (currDiscountedPriceDivPrice >= currTotalPriceDivPrice) {
						curr_bfi_price.removeClass("bfi-red");
						curr_bfi_discounted_price.hide();
						curr_percent_discount.hide();
					} else {
						curr_bfi_price.addClass("bfi-red");
						curr_bfi_discounted_price.show();
						curr_percent_discount.show();
						curr_percent_discount.attr("rel", simpleDiscountIdsDivPrice);
						var variationPercent = currTotalPriceDivPrice > 0 ? parseInt(((currDiscountedPriceDivPrice - currTotalPriceDivPrice) * 100) / currTotalPriceDivPrice) : 0;
						curr_percent_discount.find(".bfi-percent").html(variationPercent);
					}
					currentDivPrice.show();
				}

				bfi_updateQuoteService();

			}
		}
		$el.unblock();

	});


	jqxhr.always(function () {
		jQuery(currTable).unblock();
	});
}

function bfi_updateQuoteService() {
	var totalServices = 0;
	var currTotalServices = 0;
	var currTotalNotDiscoutedServices = 0;
	jQuery(".ddlextras:visible").each(function (index, element) {
		var nExtras = parseInt(jQuery(this).val());
		if (nExtras > 0) {
			var minSelectable = parseInt(jQuery(this).find('option:first').val());

			totalServices += nExtras;
			var currTr = jQuery(this).closest("tr");

			currTotalServices += (parseFloat(currTr.find(".bfi-price").html().replace(".", "").replace(",", ".")) * nExtras);
			currTotalNotDiscoutedServices += (parseFloat(currTr.find(".bfi-discounted-price").html().replace(".", "").replace(",", ".")) * nExtras);
			if (minSelectable > 0) {
				currTotalServices -= (parseFloat(currTr.find(".bfi-price").html().replace(".", "").replace(",", ".")) * minSelectable);
				currTotalNotDiscoutedServices -= (parseFloat(currTr.find(".bfi-discounted-price").html().replace(".", "").replace(",", ".")) * minSelectable);
			}
		}
	});

	jQuery(".bfi-extras-total span").html(totalServices);
	if (totalServices > 0) {
		jQuery(".bfi-extras-total").show();
	} else {
		jQuery(".bfi-extras-total").hide();
	}

	jQuery(".bfi-price-total").html(bookingfor.number_format(bfi_totalQuote + currTotalServices, 2, ',', '.'));
	jQuery(".bfi-discounted-price-total").html(bookingfor.number_format(bfi_totalQuoteDiscount + currTotalNotDiscoutedServices, 2, ',', '.'));
	jQuery(".bfi-discounted-price-total").hide();
	if (bfi_totalQuoteDiscount == 0)
	{
		jQuery(".bfi-price-total").hide();
	}else{
		jQuery(".bfi-price-total").show();
	}

	if ((bfi_totalQuoteDiscount + currTotalNotDiscoutedServices) <= (bfi_totalQuote + currTotalServices)) {
		jQuery(".bfi-discounted-price-total").hide();
		jQuery(".bfi-price-total").removeClass("bfi-red");
	} else {
		jQuery(".bfi-discounted-price-total").show();
		jQuery(".bfi-price-total").addClass("bfi-red");
	}

}
/* jQuery UI dialog clickoutside */

/*
The MIT License (MIT)

Copyright (c) 2013 - AGENCE WEB COHERACTIO

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
the Software, and to permit persons to whom the Software is furnished to do so,
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
jQuery.widget('ui.dialog', jQuery.ui.dialog, {
	options: {
		// Determine if clicking outside the dialog shall close it
		clickOutside: false,
		// Element (id or class) that triggers the dialog opening 
		clickOutsideTrigger: ''
	},
	open: function () {
		var clickOutsideTriggerEl = jQuery(this.options.clickOutsideTrigger),
			that = this;
		if (this.options.clickOutside) {
			// Add document wide click handler for the current dialog namespace
			jQuery(document).on('click.ui.dialogClickOutside' + that.eventNamespace, function (event) {
				var $target = jQuery(event.target);
				if ($target.closest(jQuery(clickOutsideTriggerEl)).length === 0 &&
					$target.closest(jQuery(that.uiDialog)).length === 0 && $target.closest('.ui-datepicker-header').length === 0) {
					that.close();
				}
			});
		}
		// Invoke parent open method
		this._super();
	},
	close: function () {
		// Remove document wide click handler for the current dialog
		jQuery(document).off('click.ui.dialogClickOutside' + this.eventNamespace);
		// Invoke parent close method 
		this._super();
	},
});

//old file bfi_calendar.js
var calTopCorr = 5;
function bfiCalendarCheck() {
	if (jQuery("#ui-datepicker-div.bfi-calendar").is(":visible")) {
		jQuery("#ui-datepicker-div.bfi-calendar").css("max-width", "500px");
		jQuery(".ui-icon-circle-triangle-w").addClass("fa fa-angle-left").removeClass("ui-icon ui-icon-circle-triangle-w").html("");
		jQuery(".ui-icon-circle-triangle-e").addClass("fa fa-angle-right").removeClass("ui-icon ui-icon-circle-triangle-e").html("");
		if (jQuery(".ui-datepicker-trigger.activeclass").length) {
			jQuery("#ui-datepicker-div.bfi-calendar").css("top", jQuery(".ui-datepicker-trigger.activeclass").offset().top + calTopCorr + "px");
		}
	} else {
		jQuery(".ui-datepicker-trigger").removeClass("activeclass");
	}
}


//-------------last search
function bfiShowLastSearch(where) {
	if (typeof (Storage) !== "undefined" && localStorage.lastsearch) {
		currSearch = JSON.parse(localStorage.getItem("lastsearch"));
		//		console.log(typeof currSearch)
		if (currSearch.length > 1) {
			// ho effettuato almento una ricerca precedentemente e quindi la visualizzo
			var cList = jQuery(where);
			cList.html("");
			var table = jQuery('<table/>');
			table.appendTo(cList);
			jQuery.each(currSearch, function (i) {
				var checkinDate = jQuery.datepicker.parseDate('dd/mm/yy', currSearch[i].checkin)
				var checkoutDate = jQuery.datepicker.parseDate('dd/mm/yy', currSearch[i].checkout)
				month1 = bookingfor.pad((checkinDate.getMonth() + 1), 2);
				month2 = bookingfor.pad((checkoutDate.getMonth() + 1), 2);
				day1 = bookingfor.pad((checkinDate.getDate()), 2);
				day2 = bookingfor.pad((checkoutDate.getDate()), 2);
				if (typeof Intl == 'object' && typeof Intl.NumberFormat == 'function') {
					month1 = checkinDate.toLocaleString(bfi_variables.bfi_cultureCodeBase, { month: "short" });
					month2 = checkoutDate.toLocaleString(bfi_variables.bfi_cultureCodeBase, { month: "short" });
				}
				if (checkinDate > Date.now()) {
					var row = jQuery('<tr/>');
					row.appendTo(table);

					//Add data cell.
					var cell = jQuery(row[0].insertCell(-1));
					cell[0].style.width = '40%';
					cell[0].style.borderRight = 'none';
					var aaa = jQuery('<a/>')
						.attr('href', currSearch[i].url)
						.text(day1 + " " + month1 + " - " + day2 + " " + month2)
						.appendTo(cell);

					//Add paxes cell.
					cell = jQuery(row[0].insertCell(-1));
					var currPax = "";
					if (currSearch[i].maxqt > 1) {
						currPax += currSearch[i].maxqt + " " + bfi_variables.bfi_resources + ", ";
					}
					currPax += currSearch[i].nad + " " + bfi_variables.bfi_adults;
					if (currSearch[i].nch > 0) {
						currPax += ", " + currSearch[i].nch + " " + bfi_variables.bfi_children;
					}
					cell[0].style.borderLeft = 'none';
					cell[0].style.borderRight = 'none';
					cell.html(currPax);

					//Add Button cell.
					cell = jQuery(row[0].insertCell(-1));
					cell[0].style.width = '25px';
					var btnRemove = jQuery("<i/>")
						.addClass('fa fa-times-circle')
						.css('cursor', 'pointer')
						.attr("aria-hidden", "true");

					//					btnRemove.attr("type", "button");
					btnRemove.attr("onclick", "bfiRemoveSearch('" + currSearch[i].id + "','" + where + "');");
					//					btnRemove.val("Remove");
					cell[0].style.borderLeft = 'none';
					cell.append(btnRemove);
					//				}else{
					//					bfiRemoveSearch(currSearch[i].id ,where);
				}
			});

		}

	}
}
function bfiRemoveSearch(idSearch, where) {
	if (typeof (Storage) !== "undefined" && localStorage.lastsearch) {
		currSearch = JSON.parse(localStorage.getItem("lastsearch"));
		var currIndex = -1;
		for (i = 0; i < currSearch.length; i++) {
			if (currSearch[i].id === idSearch) {
				currIndex = i;
			}
		}
		if (currIndex > -1) {
			currSearch.splice(currIndex, 1);
		}
		localStorage.setItem("lastsearch", JSON.stringify(currSearch));
		bfiShowLastSearch(where);
	}
}
//------------------------------------------
//-------------last search
function bfiShowLastMerchants(where) {
	if (typeof (Storage) !== "undefined" && localStorage.lastmerchants) {
		currMerchants = JSON.parse(localStorage.getItem("lastmerchants"));
		//		console.log(typeof currSearch)
		if (currMerchants.length > 0) {
			// ho effettuato almento una ricerca precedentemente e quindi la visualizzo
			var cList = jQuery(where);
			//cList.html("");
			cList.find("table").first().remove();
			var table = jQuery('<table/>');
			table.appendTo(cList);
			jQuery.each(currMerchants, function (i) {

				var row = jQuery('<tr/>');
				row.appendTo(table);

				//Add data cell.
				var cell = jQuery(row[0].insertCell(-1));
				cell[0].style.width = '30%';
				cell[0].style.borderRight = 'none';
				if ((currMerchants[i].img + '') !== '') {
					var mrcLink = jQuery('<a/>')
						.attr('href', currMerchants[i].url)
						//							.text(currMerchants[i].name )
						.appendTo(cell);
					var mrcLogo = jQuery('<img/>')
						.attr("src", currMerchants[i].img);
					mrcLogo.appendTo(mrcLink);
				}

				//Add paxes cell.
				cell = jQuery(row[0].insertCell(-1));
				var mrcName = jQuery('<a/>')
					.attr('href', currMerchants[i].url)
					.text(currMerchants[i].name)
					.appendTo(cell);
				cell[0].style.borderLeft = 'none';
				cell[0].style.borderRight = 'none';
				cell.html(mrcName);

				//Add Button cell.
				cell = jQuery(row[0].insertCell(-1));
				cell[0].style.width = '25px';
				var btnRemove = jQuery("<i/>")
					.addClass('fa fa-times-circle')
					.css('cursor', 'pointer')
					.attr("aria-hidden", "true");

				//					btnRemove.attr("type", "button");
				btnRemove.attr("onclick", "bfiRemoveMerchants('" + currMerchants[i].id + "','" + where + "');");
				//					btnRemove.val("Remove");
				cell[0].style.borderLeft = 'none';
				cell.append(btnRemove);
			});
		}else{
			jQuery(where).hide();
		}

	}
}
function bfiRemoveMerchants(idMerchant, where) {
	if (typeof (Storage) !== "undefined" && localStorage.lastmerchants) {
		currMerchants = JSON.parse(localStorage.getItem("lastmerchants"));
		var currIndex = -1;
		for (i = 0; i < currMerchants.length; i++) {
			if (currMerchants[i].id === idMerchant) {
				currIndex = i;
			}
		}
		if (currIndex > -1) {
			currMerchants.splice(currIndex, 1);
		}
		localStorage.setItem("lastmerchants", JSON.stringify(currMerchants));
		bfiShowLastMerchants(where);
	}
}

(function ($) {
	$.fn.outerHTML = function (s) {
		return (s)
			? this.before(s).remove()
			: $('<p>').append(this.eq(0).clone()).html();
	}
})(jQuery);

function bfi_updateHiddenValue(who, whohidden) {
	var allVals = [];
	jQuery(who).each(function () {
		allVals.push(jQuery(this).val());
	});
	jQuery(whohidden).val(allVals.join(","));
}

function bfi_changeBaths(currObj) {
	var bathsselect = jQuery(currObj).val();
	var vals = bathsselect.split("|");
	var closestDiv = jQuery(currObj).closest("div");
	closestDiv.find("input[name='bathsmin']").first().val(vals[0]);
	closestDiv.find("input[name='bathsmax']").first().val(vals[1]);
}

function bfi_insertNight(currForm) {
	var currCalendarnight = jQuery(currForm).find(".bfi-calendarnight").first();
	var resbynight = jQuery(currForm).find(".resbynighthd").first();
	var resbynightDivContainer = jQuery(currForm).find(".bfi-calendarnightsearch").first();
	var currCheckin = jQuery(currForm).find("input[name='checkin']").first();
	var currCheckout = jQuery(currForm).find("input[name='checkout']").first();

	var checkindate = currCheckin.val();
	var checkoutdate = currCheckout.val();
	var d1 = checkindate.split("/");
	var d2 = checkoutdate.split("/");

	var from = new Date(Date.UTC(d1[2], d1[1] - 1, d1[0]));
	var to = new Date(Date.UTC(d2[2], d2[1] - 1, d2[0]));

	diff = new Date(to - from),
		days = Math.ceil(diff / 1000 / 60 / 60 / 24);

	if (resbynight.length) {
		var resbynight_str = resbynight.val().split(",");
		if (resbynight_str.indexOf("0") !== -1 || resbynight_str.indexOf("1") !== -1) {
			resbynightDivContainer.show();
			var strSummaryDays = "" + days + " " + bfi_txtNights;
			if (jQuery(resbynight).val() == 0) {
				days += 1;
				strSummaryDays = "" + days + " " + bfi_txtDays;
			}
			if (days < 1) { strSummaryDays = ""; }
			currCalendarnight.html(strSummaryDays);
		}
	}
}
function bfi_initializer() {

	jQuery('.bfi-login-container').each(function () {
		var currModule = jQuery(this);
		var loginMsg = currModule.find(".bfi-text-login-msg").first();
		var currForm = jQuery(this).find(".bfi-login-form").first();
		if (currForm.length) {
			currForm.validate({
				errorClass: "bfi-error",
				cache : false,
				submitHandler: function (form) {
					var $form = jQuery(form);
					bookingfor.waitSimpleWhiteBlock($form);
					jQuery(form).ajaxSubmit({
						dataType: 'json',
						success: function (data) {
							jQuery($form).unblock();
							var pchlogin = $form.find(".bfi-pchlogin").first();
							var pchTwoFactorAuthentication = $form.find(".bfi-pchtwofactorauthentication").first();
							var pchtwofactorauthenticationerror = $form.find(".bfi-pchtwofactorauthenticationerror").first();
							var btnforgotpassword = $form.find(".bfi-btnforgotpassword").first();
							var btnsendconfirm = $form.find(".bfi-btnsendconfirm").first();
							var btnsendlogin = $form.find(".bfi-btnsendlogin").first();
							var loginemail = $form.find(".bfi-loginemail").first();
							var twofactorauthcode = $form.find(".bfi-twofactorauthcode").first();
							//							var btnresendcode = $form.find(".bfi-btnresendcode").first();

							if (data == "-1") {
								pchTwoFactorAuthentication.hide();
								loginMsg.removeClass("bfi-error");
								loginMsg.html(bfi_variables.bfi_txtloginsuccess);
								pchlogin.hide();
								jQuery('.bfi-form-sep').hide();
								location.reload();
							}
							else {
								if (data == "1") {
									pchTwoFactorAuthentication.show();
									//									btnresendcode.show();
									btnforgotpassword.hide();
									btnsendconfirm.show();
									btnsendlogin.hide();

									var currmsg = pchtwofactorauthenticationerror.html();
									currmsg = currmsg.replace('{0}', loginemail.val());
									pchtwofactorauthenticationerror.html(currmsg);
									pchlogin.hide();
								} else if (data == "2") {
									pchtwofactorauthenticationerror.html(bfi_variables.bfi_txtcodenotvalid);
								} else if (data.length > 3 && data.substring(0, 1) == "3") {
									var timelock = data.substring(2, data.length);
									var d = new Date(timelock);
									var timelockStr = d.toLocaleString(bfi_variables.bfi_cultureCodeBase)
									var currmsg = bfi_variables.bfi_txtaccessnotvaliduntil;
									currmsg = currmsg.replace('{0}', timelockStr);
									pchlogin.show();
									pchTwoFactorAuthentication.hide();
									btnforgotpassword.show();
									//									btnresendcode.hide();
									twofactorauthcode.val('');
									pchtwofactorauthenticationerror.html(currmsg);
									pchtwofactorauthenticationerror.show();
								} else {
									loginMsg.addClass("bfi-error");
									loginMsg.html(bfi_variables.bfi_txtloginfailed);
								}
							}
						}
					});
				}
			});
		}// end if form

		var currFormLostpass = jQuery(this).find(".bfi-lostpass-form").first();
		if (currFormLostpass.length) {
			currFormLostpass.validate({
				errorClass: "bfi-error",
				cache : false,
				submitHandler: function (form) {
					var $form = jQuery(form);
					bookingfor.waitSimpleWhiteBlock($form);
					jQuery(form).ajaxSubmit({
						success: function (data) {
							bfi_lostpassback($form);
							$form.unblock();
							if (data == true) {
								loginMsg.removeClass("bfi-error");
								loginMsg.html(bfi_variables.bfi_txtlinksent);
							} else {
								loginMsg.addClass("bfi-error");
								loginMsg.html(bfi_variables.bfi_txtmsgvalidemail);
							}
						}
					});
				}
			});
		}// end if form

	});

}
function bfi_openpopupmap() {
		var width = jQuery(window).width() * 0.8;
		var height = jQuery(window).height()  * 0.8;
		if (bookingfor.mobileViewMode)
		{
			width = window.innerWidth;
			height = window.innerHeight;
		}
		if (jQuery("#bfi-maps-popup").length == 0) {
			jQuery("body").append("<div id='bfi-maps-popup'></div>");
		}
		jQuery( "#bfi-maps-popup" ).dialog({
			closeText: "",
			open: function( event, ui ) {
                bookingfor.bfiOpenGoogleMapSearch();
			},
			width: width,
			height: height,
            dialogClass: 'bfi-dialog bfi-dialog-map',
            resize: function (event, ui) {
                if (bfi_variables.bfiMapsFree) {
                    mapSearch.invalidateSize();
                }
            }

		});
}

bookingfor.shortenIcon = function (obj) {
		obj.shorten({
		moreText: '<i class="fa fa-chevron-down"></i>',
		lessText: '<i class="fa fa-chevron-up"></i>',
		showChars: '300'
	});
}
bookingfor.shortenText = function (obj,nChars) {
		obj.shorten({
		moreText: bfi_variables.bfi_txtMoreText,
		lessText: bfi_variables.bfi_txtLessText,
		showChars: nChars,
	});
}
bookingfor.checkonedays = function (obj) {
	var currForm = jQuery(obj).closest("form");
	var currCheckin = jQuery(currForm).find("input[name='checkin']").first();
	var currCheckout = jQuery(currForm).find("input[name='checkout']").first();
		if (jQuery(obj).val() == '1') {
//			var newDate = bookingfor.dateAdd(currCheckin.datepicker("getDate"), "day", 1);
//			currCheckout.datepicker().datepicker('setDate', newDate);
			currCheckout.datepicker().datepicker('setDate', currCheckin.datepicker("getDate"));
			jQuery(currForm).find(".bfi-checkout-field-container").hide();
			jQuery(currForm).find(".bfi-showdaterange").addClass("bfi-showdaterange98");
			jQuery(currForm).find(".bfi_destination").addClass("bfi-oneday");
		}
		else if (jQuery(obj).val() == '2') {
			jQuery(currForm).find(".bfi-checkout-field-container").show();
			jQuery(currForm).find(".bfi-showdaterange").removeClass("bfi-showdaterange98");
			jQuery(currForm).find(".bfi_destination").removeClass("bfi-oneday");
		}
}
bookingfor.checkreturnsamelocation = function (obj) {
	var currForm = jQuery(obj).closest("form");
	var currSearchterm = jQuery(currForm).find("input[name='searchterm']").first();
	var dropoff = jQuery(currForm).find("input[name='dropoff']").first();
		if (jQuery(obj).val() == '1') {
			dropoff.hide();
			currSearchterm.removeClass("bfi-pickup50");
		}
		else if (jQuery(obj).val() == '2') {
			dropoff.show();
			currSearchterm.addClass("bfi-pickup50");
		}
}

jQuery(document).ready(function () {
	if (!!jQuery.uniform) {
		jQuery.uniform.restore(jQuery(".bfi-orderby-content"));
	}

	jQuery.widget.bridge('bfiTabs', jQuery.ui.tabs);
	jQuery.widget.bridge('bfiTooltip', jQuery.ui.tooltip );
	// popup map
	jQuery('.bfishowpopupmap,.bfiopenpopupmap, .bfi-search-view-maps ').click(function(e) {
		e.stopPropagation();
		bfi_openpopupmap();
	});

	jQuery(document).on('click', ".bfi-panel-toggle", function (e) {
		jQuery(".bfi-slide-panel").toggleClass('visible');
	});
	jQuery(document).on('click', ".bfi-menu-top li a, .bfi-btn-calc", function (e) {
		var attr = jQuery(this).attr("rel");
		if (typeof attr !== typeof undefined && attr !== false) {
			e.preventDefault();
			jQuery('html, body').animate({ scrollTop: jQuery(jQuery(this).attr("rel")).offset().top }, 2000);
		}
	});
	jQuery(document).on('change', ".bfi-changedays-widget", function (e) {
		bookingfor.checkonedays(this);
	});
	jQuery(document).on('change', ".bfi-returnsamelocation-widget", function (e) {
		bookingfor.checkreturnsamelocation(this);
	});
	bookingfor.inizializeListView();

	jQuery('.bfi-shortenicon').each(function () {
	if (bookingfor.mobileViewMode)
		{
			bookingfor.shortenIcon(jQuery(this));
		}
	});

	jQuery('.bfi-shortentext').each(function () {
		bookingfor.shortenText(jQuery(this),150);
	});
	jQuery('.bfi-shortentextlong').each(function () {
		bookingfor.shortenText(jQuery(this),250);
	});
	
	jQuery.validator.addMethod('date',
		function (value, element) {
			if (this.optional(element)) {
				return true;
			}

			var ok = true;
			try {
				jQuery.datepicker.parseDate(currDateFormat, value);
			}
			catch (err) {
				ok = false;
			}
			return ok;
		});
	if (!!jQuery.uniform) {
		jQuery.uniform.restore(jQuery("bfi-showperson select"));
		jQuery.uniform.restore(jQuery(".bfi-childrenages select"));
	}
	bfi_initializer();

	jQuery('.bfi-mod-bookingforsearchevent').each(function () {
		var currModule = jQuery(this);
		var currForm = jQuery(this).find(".bfi-form-event").first();
				var currAutocomplete = jQuery(currForm).find(".bfi-autocomplete").first();
				var currScope = currAutocomplete.attr("data-scope");
				if (typeof currScope === "undefined" || !currScope.length){
					currScope = "";
				}

				currAutocomplete.blur(function(){
         var keyEvent = jQuery.Event("keydown");
         keyEvent.keyCode = jQuery.ui.keyCode.ENTER;
         jQuery(this).trigger(keyEvent);
     }).autocomplete({
					autoFocus: true,
					source: function (request, response) {
						var previous_request = currAutocomplete.data( "jqXHR" );
						if( previous_request ) {
							previous_request.abort();
						}
						var urlSearch =  bfi_variables.bfi_urlSearchByText + "?term=" + request.term + "&resultClasses=" + currScope + "&maxresults=5&limitregions=1&cultureCode=" + bfi_variables.bfi_cultureCode;
						currAutocomplete.data( "jqXHR",
//							jQuery.getJSON(bookingfor.getActionUrl(null, null, "SearchByText", "bfi_term=" + request.term + "&bfi_resultclasses=" + currScope+ "&bfi_maxresults=5"), function (data) {
							jQuery.getJSON(urlSearch, function (data) {
								if (data.length) {
									jQuery.each(data, function (key, item) {
										var currentVal = "";
										if (item.StateId) { currentVal = "stateIds|" + item.StateId; }
										if (item.RegionId) { currentVal = "regionIds|" + item.RegionId; }
										if (item.CityId) { currentVal = "cityIds|" + item.CityId; }
										if (item.ZoneId) { currentVal = "zoneIds|" + item.ZoneId; }
										if (item.EventId) { currentVal = "eventId|" + item.EventId; }
										if (item.EventCategoryId) { currentVal = "categoryIds|" + item.EventCategoryId; }
										if (item.EventTagId) { currentVal = "tagids|" + item.EventTagId; }
										if (item.PointOfInterestId) { currentVal = "pointOfInterestId|" + item.PointOfInterestId; }
										if (item.ProductGroupId) { currentVal = "productGroupId|" + item.ProductGroupId; }
	//									if (item.MerchantCategoryId) { currentVal = "merchantCategoryId|" + item.MerchantCategoryId; }
	//									if (item.ProductCategoryId) { currentVal = "masterTypeId|" + item.ProductCategoryId; }
	//									if (item.MerchantId) { currentVal = "merchantIds|" + item.MerchantId; }
	//									if (item.MerchantTagId) { currentVal = "merchantTagIds|" + item.MerchantTagId; }
	//									if (item.ProductTagId) { currentVal = "productTagIds|" + item.ProductTagId; }
										item.Value = currentVal;
									});
									response(data);
									currAutocomplete.removeClass("ui-autocomplete-loading");
								} else {
									response([{
										Name: bfi_variables.bfi_txtnoresult
									}]);
									currAutocomplete.removeClass("ui-autocomplete-loading");
								}
							})
						);
					},
					/*response: function( event, ui ) {
						jQuery(this).removeClass("ui-autocomplete-loading");
					},*/
					minLength: 2,
					delay: 200,
					select: function (event, ui) {
						var selectedVal = ui.item.Value;
						//			var selectedVal = jQuery(event.srcElement).attr("data-value");
						if (selectedVal.length) {
							currAutocomplete.closest("form").find("[name=stateIds],[name=regionIds],[name=cityIds],[name=zoneIds],[name=categoryIds],[name=eventId],[name=eventTagId],[name=pointOfInterestId]").val("");
							currAutocomplete.closest("form").find("[name=searchTermValue]").val(selectedVal);
//							currAutocomplete.closest("form").find("[name=" + selectedVal.split('|')[0] + "]").val(selectedVal.split('|')[1]);
							switch (selectedVal.split('|')[0]) {
								case "stateIds":
								case "regionIds":
                                    var completeValue = selectedVal.split('|')[1];
                                    currAutocomplete.closest("form").find("[name=" + selectedVal.split('|')[0] + "]").val(completeValue.split(":")[0]);
                                    break;
								case "cityIds":
								case "poiIds":
									var completeValue = selectedVal.split('|')[1];
									currAutocomplete.closest("form").find("[name=points]").val("0|" + completeValue.split(":")[1] + " " + completeValue.split(":")[2]);
									break;
								case "eventId":
									currAutocomplete.closest("form").find("[name=eventId]").val(selectedVal.split('|')[1]);
									currAutocomplete.closest("form").find("[name=filter_order]").val("eventId:" + selectedVal.split('|')[1]);
									break;
								case "merchantIds":
									currAutocomplete.closest("form").find("[name=filter_order]").val("merchantid:" + selectedVal.split('|')[1]);
									break;
								case "groupresourceIds":
									currAutocomplete.closest("form").find("[name=filter_order]").val("parentid:" + selectedVal.split('|')[1]);
									break;
								case "resourceIds":
									currAutocomplete.closest("form").find("[name=filter_order]").val("productid:" + selectedVal.split('|')[1]);
									break;
								default:
									currAutocomplete.closest("form").find("[name=" + selectedVal.split('|')[0] + "]").val(selectedVal.split('|')[1]);
									break;
							}
							jQuery(this).val(ui.item.Name);
							event.preventDefault();
						}
						//log( "Selected: " + ui.item.value + " aka " + ui.item.id );
					},
					change: function( event, ui ) {
						if (currAutocomplete.val().length<2) {
							currAutocomplete.closest("form").find("[name=stateIds],[name=regionIds],[name=cityIds],[name=zoneIds],[name=categoryIds],[name=eventId],[name=eventTagId],[name=pointOfInterestId]").val("");
						}
					},
					open: function () {
						jQuery(this).data("uiAutocomplete").menu.element.addClass("bfi-autocomplete");
					}
				});


				currAutocomplete.data("ui-autocomplete")._renderItem = function (ul, item) {
					var currentVal = "";
					if (item.StateId) { currentVal = "stateIds|" + item.StateId; }
					if (item.RegionId) { currentVal = "regionIds|" + item.RegionId; }
					if (item.CityId) { currentVal = "cityIds|" + item.CityId; }
					if (item.ZoneId) { currentVal = "zoneIds|" + item.ZoneId; }
					if (item.EventId) { currentVal = "eventId|" + item.EventId; }
					if (item.EventCategoryId) { currentVal = "categoryIds|" + item.EventCategoryId; }
					if (item.EventTagId) { currentVal = "eventTagId|" + item.EventTagId; }
					if (item.PointOfInterestId) { currentVal = "pointOfInterestId|" + item.PointOfInterestId; }

					var text = item.Name;
					if (item.StateId || item.RegionId || item.CityId || item.ZoneId) { text = '<i class="fa fa-map-marker"></i>&nbsp;' + text; }
					if (item.EventId || item.EventCategoryId) { text = '<i class="fa fa-calendar"></i>&nbsp;' + text; }
					if (item.PointOfInterestId) { text = '<i class="fa fa-street-view"></i>&nbsp;' + text; }
					if (item.EventTagId) { text = '<i class="fa fa-tag"></i>&nbsp;' + text; }
					if (currentVal.length) {
						return jQuery("<li>").attr("data-value", currentVal).html(text).appendTo(ul);
					} else {
						return jQuery("<li>").attr("data-value", "").html(text).addClass("ui-state-disabled").appendTo(ul);
					}
				};

		});

	// time slot form
	jQuery('.bfi-mod-bookingforsearch-slot').each(function () {
		var currModule = jQuery(this);
		var currForm = jQuery(this).find(".bfi-form-default").first();
		var fixedontop = jQuery(this).attr("data-fixedontop");
		var fixedontopcorrection = jQuery(this).attr("data-fixedontopcorrection");

		if (currForm.length) {
			var currShowsearchtext = jQuery(currForm).attr("data-showsearchtext");
			var currResultinsamepg = jQuery(currForm).find("input[name='resultinsamepg']").first();
			var currResultview = jQuery(".bfi-resultview").first();
			var currDefaultaction = currForm.attr("data-defaultaction");
			if (currResultinsamepg.length && currResultinsamepg.val() == "1" && currResultview.length==0) {
				currForm.attr('action', currDefaultaction);
			}
		};
		var currCheckin = jQuery(currForm).find("input[name='checkin']").first();
		var currCheckout = jQuery(currForm).find("input[name='checkout']").first();
		currCheckin.datepicker({
			dateFormat: "dd/mm/yy",
			numberOfMonths: bfi_variables.bfi_numberOfMonths,
			minDate: '+0d',
			onClose: function (dateText, inst) {
				jQuery(this).attr("disabled", false);
			},
			beforeShow: function (dateText, inst) {
				var currTmpForm = jQuery(this).closest("form");
				bfidpmode = 'checkin';
				jQuery(this).attr("disabled", true);
				jQuery(inst.dpDiv).addClass('bfi-calendar');
				setTimeout(function () {
					bfi_updateTitle(currTmpForm, "bfi-checkin", "bfi-checkout", "Checkin")
				}, 1);
				var windowsize = jQuery(window).width();
				jQuery(inst.dpDiv)[0].className = jQuery(inst.dpDiv)[0].className.replace(/(^|\s)bfi-calendar-affixtop\S+/g, '');
				if (jQuery(this).closest("div.bfiAffixTop").length || windowsize < 767) {
					jQuery(inst.dpDiv).addClass('bfi-calendar-affixtop' + ((typeof currModId !== 'undefined') ? currModId : ""));
				}
			},
			onChangeMonthYear: function (dateText, inst) {
				var currTmpForm = jQuery(this).closest("form");
				setTimeout(function () { bfi_updateTitle(currTmpForm, "bfi-checkin", "bfi-checkout", "Checkin"); }, 1);
			},
			beforeShowDay: function (date) {
				var currTmpForm = jQuery(this).closest("form");
				return bfi_closed(date, currTmpForm);
			},
			onSelect: function (date, inst) {
				var currTmpForm = jQuery(this).closest("form");	
				var defaultduration = Number(currTmpForm.attr("data-defaultduration")||1);

				bfi_printChangedDate(currTmpForm);
				var currTmpCheckin = jQuery(currTmpForm).find("input[name='checkin']").first();
				var currTmpCheckout = jQuery(currTmpForm).find("input[name='checkout']").first();
				var currDate = currTmpCheckin.datepicker('getDate');
				currTmpCheckout.datepicker().datepicker('setDate', currDate.getDate() + defaultduration);
				currTmpCheckout.val(bookingfor.getDisplayDate(bookingfor.dateAdd(currDate, "day", defaultduration)));

				if (currTmpCheckout.is(':visible')){
					setTimeout(function () { currTmpCheckout.datepicker("show"); }, 1);
				}
				jQuery(this).trigger("change");

			},
			firstDay: 1
		});
		/** **/

		currCheckout.datepicker({
			dateFormat: "dd/mm/yy",
			numberOfMonths: bfi_variables.bfi_numberOfMonths,
			onClose: function (dateText, inst) {
				jQuery(this).attr("disabled", false);
				bfi_printChangedDate(jQuery(this).closest("form"));
			},
			beforeShow: function (dateText, inst) {
				var currTmpForm = jQuery(this).closest("form");
				var currTmpCheckin = jQuery(currTmpForm).find("input[name='checkin']").first();
				var date = currTmpCheckin.val();
				bfi_checkDate(jQuery, currTmpCheckin, date);

				bfidpmode = 'checkout';
				jQuery(this).attr("disabled", true);
				jQuery(inst.dpDiv).addClass('bfi-calendar');
				setTimeout(function () { bfi_updateTitle(currTmpForm, "bfi-checkout", "bfi-checkin", "Checkout"); }, 1);
				var windowsize = jQuery(window).width();
				jQuery(inst.dpDiv)[0].className = jQuery(inst.dpDiv)[0].className.replace(/(^|\s)bfi-calendar-affixtop\S+/g, '');
				if (jQuery(this).closest("div.bfiAffixTop").length || windowsize < 767) {
					jQuery(inst.dpDiv).addClass('bfi-calendar-affixtop' + ((typeof currModId !== 'undefined') ? currModId : ""));
				}
				bfi_printChangedDate(currTmpForm);

			},
			onSelect: function (date, inst) {
				var currTmpForm = jQuery(this).closest("form");
//				bfiresetsearchterm(currTmpForm);
				bfi_printChangedDate(currTmpForm);
				jQuery(this).trigger("change");
			},
			onChangeMonthYear: function (dateText, inst) {
				var currTmpForm = jQuery(this).closest("form");
				setTimeout(function () { bfi_updateTitle(currTmpForm, "bfi-checkout", "bfi-checkin", "Checkout"); }, 1);
			},
			minDate: '+0d',
			//showOn: "button", 
			beforeShowDay: function (date) {
				var currTmpForm = jQuery(this).closest("form");
				return bfi_closed(date, currTmpForm);
			},
			//buttonText: "<div class='checkoutli'><span class='bfi-weekdayname'> </span> <span class='bfi-year'>aaa </span></div>", 
			firstDay: 1
		});
			currForm.validate(
				{
//					invalidHandler: function (form, validator) {
//						var errors = validator.numberOfInvalids();
//						if (errors) {
//							validator.errorList[0].element.focus();
//						}
//					},
					invalidHandler: function (form, validator) {
						var errors = validator.numberOfInvalids();
						if (errors) {
//							alert(validator.errorList[0].message);

							validator.errorList[0].element.focus();
						}
					},
					showErrors: function (errorMap, errorList) {

						// Clean up any tooltips for valid elements
						jQuery.each(this.validElements(), function (index, element) {
							var $element = jQuery(element);

							$element.prop("title", "") // Clear the title - there is no error associated anymore
								.removeClass("bfi-error")
							if ($element.is(':data(tooltip)')) {
								$element.bfiTooltip('destroy');
							}
						});
						// Create new tooltips for invalid elements
						jQuery.each(errorList, function (index, error) {
							var $element = jQuery(error.element);
							if ($element.is(':data(tooltip)')) {
								$element.bfiTooltip('destroy');
							}

							$element.prop("title", error.message)
								.addClass("bfi-error")
							if (typeof bfiTooltip  !== "function") {
								jQuery.widget.bridge('bfiTooltip', jQuery.ui.tooltip );
							}
							if (typeof bfiTooltip  !== "function") {
								jQuery.widget.bridge('bfiTooltip', jQuery.ui.tooltip );
							}
							$element.bfiTooltip({
								position: { my: 'center bottom', at: 'center top-10' },
								tooltipClass: 'bfi-tooltip bfi-tooltip-top '
							});
							$element.bfiTooltip("open");

						});
					},
					focusCleanup: true,
					errorClass: "bfi-error",
					highlight: function (label) {
					},
					success: function (label) {
						jQuery(label).remove();
					},
					cache : false,
					submitHandler: function (form) {
						var $form = jQuery(form);
						var currDateFormat = "dd/mm/yy";
						var currTmpCheckin = jQuery(form).find("input[name='checkin']").first();
						var currTmpCheckout = jQuery(form).find("input[name='checkout']").first();

						currTmpCheckin.datepicker("option", "dateFormat", currDateFormat);
						currTmpCheckout.datepicker("option", "dateFormat", currDateFormat);

						if (currTmpCheckout.is(':visible')){
						}else{
						var currDate = currTmpCheckin.datepicker('getDate');
						
						var defaultduration = Number(jQuery(form).attr("data-defaultduration")||1);

						currTmpCheckout.datepicker().datepicker('setDate', currDate.getDate() + defaultduration);
						currTmpCheckout.val(bookingfor.getDisplayDate(bookingfor.dateAdd(currDate, "day", defaultduration)));
						}

//						currCheckin.datepicker("option", "dateFormat", currDateFormat);
//						currCheckout.datepicker("option", "dateFormat", currDateFormat);

						var $btnresource = $form.find(".bfi-btnsendform").first();
						if ($form.valid()) {
							if ($form.data('submitted') === true) {
								return false;
							} else {
								// Mark it so that the next submit can be ignored
								$form.data('submitted', true);
								var isMacLike = navigator.platform.match(/(Mac|iPhone|iPod|iPad)/i) ? true : false;
								var isIOS = navigator.platform.match(/(iPhone|iPod|iPad)/i) ? true : false;
								if (!isMacLike) {
									var iconBtn = $btnresource.find("i").first();
									iconBtn.removeClass("fa-search").addClass("fa-spinner fa-spin ");
									$btnresource.prop('disabled', true);

								}
								form.submit();
							}

						}
					}

				});
			if (currShowsearchtext != "0") {
				bookingfor.createAutocomplete(currForm);				
			}
	});
	
	// time slot form END

	// Experience form
	jQuery('.bfi-mod-bookingforsearch-experience').each(function () {
		var currModule = jQuery(this);
		var currForm = jQuery(this).find(".bfi-form-default").first();
		var fixedontop = jQuery(this).attr("data-fixedontop");
		var fixedontopcorrection = jQuery(this).attr("data-fixedontopcorrection");

		if (currForm.length) {
			var currShowsearchtext = jQuery(currForm).attr("data-showsearchtext");
			var currResultinsamepg = jQuery(currForm).find("input[name='resultinsamepg']").first();
			var currResultview = jQuery(".bfi-resultview").first();
			var currDefaultaction = currForm.attr("data-defaultaction");
			if (currResultinsamepg.length && currResultinsamepg.val() == "1" && currResultview.length==0) {
				currForm.attr('action', currDefaultaction);
			}
		};
		var currCheckin = jQuery(currForm).find("input[name='checkin']").first();
		var currCheckout = jQuery(currForm).find("input[name='checkout']").first();

		var currDateselected = jQuery(currForm).find("input[name='dateselected']").first();
				
				jQuery(document).on('click tap', ".bfi-datepicker-clear", function (e) {
					e.preventDefault();
					e.stopPropagation();
					var currdatepicker= jQuery(this).closest('.bfidaterangepicker');
					var currTmpForm = jQuery(this).closest("form");
					var currCheckin = jQuery(currForm).find("input[name='checkin']").first();
					var currCheckout = jQuery(currForm).find("input[name='checkout']").first();
					var currDateselected = jQuery(currForm).find("input[name='dateselected']").first();
					currCheckin.val('');
					currCheckout.val('');
					currDateselected.val('0');
					jQuery(currForm).find('.bfidaterangepicker-checkin span').html(bfi_variables.bfi_txtselectdates);
					jQuery(currForm).find('.bfidaterangepicker-checkout').hide();
					jQuery(currdatepicker).data('daterangepicker').hide();
					jQuery(this).hide();

				});

				// new calendar
				var currCalendarRangeT = jQuery(currForm).find(".t-datepicker");

				currCalendarRangeT.tDatePicker({
					dateCheckIn: currCalendarRangeT.attr('data-checkin'), //'2020-03-10',
					dateCheckOut: currCalendarRangeT.attr('data-checkout'), //'2020-03-11',
					numCalendar:12,
					valiDation : true,
					iconArrowTop: false,
					titleCheckIn: '',
					titleCheckOut: '',
					iconDate: '<i class="fa fa-calendar"></i> ',
					titleDateRange: bfi_variables.bfi_txtNights ,
					titleDateRanges: bfi_variables.bfi_txtNights ,
					showDateTheme:'dd-mm-yy',
					toDayShowTitle: false,
					titleCheckOutSelect: bfi_variables.bfi_txtTitleCheckOutSelect,
					titleBtnOk: bfi_variables.bfi_txtTitleBtnOk,
					titleToday:'Today',
					titleDays: JSON.parse(JSON.stringify(bfi_variables.bfi_txtTitleDaysT)) ,
					titleMonths: JSON.parse(JSON.stringify(bfi_variables.bfi_txtTitleMonths)) ,
					autoClose: false,
					sameDate : true,
				}).on('beforeShowDay',function() {
//					jQuery(".ui-tooltip").bfiTooltip("hide");
					jQuery(".ui-tooltip-content").parents('div').remove();
//					console.log('beforeShowDay do something')
				}).on('selectedCI',function(e, slDateCI) {
//					currCalendarRangeT.find('button.t-table-close').first().attr("disabled",true);
					currCheckin.datepicker('setDate', new Date(slDateCI));
					var currTmpForm = jQuery(this).closest("form");
					var currCheckinF = jQuery(currForm).find("input[name='checkin']").first();
					currCheckinF.val(jQuery.datepicker.formatDate( "dd/mm/yy", new Date(slDateCI)));
//					 console.log('selectedCI do something')
//					 console.log(new Date(slDateCI))
				 }).on('selectedCO',function(e, slDateCO) {
//					currCalendarRangeT.find('button.t-table-close').first().removeAttr("disabled");
					currCheckout.datepicker('setDate', new Date(slDateCO));
					var currTmpForm = jQuery(this).closest("form");
					var currCheckoutF = jQuery(currForm).find("input[name='checkout']").first();
					currCheckoutF.val(jQuery.datepicker.formatDate( "dd/mm/yy", new Date(slDateCO)));

//					 console.log('selectedCO do something')
//					 console.log(new Date(slDateCO))
				  });
				
				// new calendar
				var currCalendarRange = jQuery(currForm).find(".bfidaterangepicker");
				currCalendarRange.daterangepicker({
					 "minDate": moment(),
					 "maxDate": moment().add(1,'year'),
					 "minYear": parseInt(moment().format('YYYY')),
					 "maxYear": parseInt(moment().format('YYYY'),1),
					"autoApply": true,
					"timePicker": false,
					"timePicker24Hour": true,
					"timePickerIncrement": 15,
					"locale": {
						"format": "DD/MM/YYYY HH:mm",
						"separator": " - ",
						"applyLabel": bfi_variables.bfi_txtTitleBtnOk,
						"cancelLabel": "Cancel",
						"fromLabel": "From",
						"toLabel": "To",
						"customRangeLabel": "Custom",
						"weekLabel": "W",
						"daysOfWeek": bfi_variables.bfi_txtTitleDays,
						"monthNames": bfi_variables.bfi_txtTitleMonths,
						"firstDay": 1
					},
//					isInvalidDate: function(date) {
//						//compare to your list of dates, return true if date is in the list
//					}
					"startDate": currCalendarRange.attr('data-checkin'),
					"endDate": currCalendarRange.attr('data-checkout')
				}, function(start, end) {
//						var currDateFormat = "D, dd M";
						var currDateFormat = "dd M";
						var windowsize = jQuery(window).width();
						if (windowsize > 769 && windowsize < 1300) {
							currDateFormat = " dd mm";
						}
					var currTmpForm = jQuery(this).closest("form");
//					var currCheckin = jQuery(currForm).find("input[name='checkin']").first();
//					var currCheckout = jQuery(currForm).find("input[name='checkout']").first();

					currCheckin.val(start.format('DD/MM/YYYY'));
					currCheckout.val(end.format('DD/MM/YYYY'));
					jQuery(this.element).find('.bfidaterangepicker-checkin span').html(jQuery.datepicker.formatDate(currDateFormat,start.toDate()) );
					jQuery(this.element).find('.bfidaterangepicker-checkout span').html(jQuery.datepicker.formatDate(currDateFormat,end.toDate()));
					jQuery(this.element).find('.bfidaterangepicker-checkout').show();
					if (currCheckin.val()== currCheckout.val())
					{
						jQuery(this.element).find('.bfidaterangepicker-checkout').hide();
					}
					jQuery(this.element).find('.bfi-datepicker-clear').show();
					var currDateselected = jQuery(currForm).find("input[name='dateselected']").first();
					currDateselected.val('1');
//					console.log("selected")


				});
				if (currDateselected.val()==0)
				{
					var currCheckin = jQuery(currForm).find("input[name='checkin']").first();
					var currCheckout = jQuery(currForm).find("input[name='checkout']").first();
					var currDateselected = jQuery(currForm).find("input[name='dateselected']").first();
					var currdatepicker= jQuery(currForm).find('.bfidaterangepicker');
					var currdatepickerclear= jQuery(currForm).find('.bfi-datepicker-clear');
					currCheckin.val('');
					currCheckout.val('');
					jQuery(currForm).find('.bfidaterangepicker-checkin span').html(bfi_variables.bfi_txtselectdates);
					jQuery(currForm).find('.bfidaterangepicker-checkout').hide();
					jQuery(currdatepicker).data('daterangepicker').hide();
					jQuery(currdatepickerclear).hide();

				}
					var currCheckin = jQuery(currForm).find("input[name='checkin']").first();
					var currCheckout = jQuery(currForm).find("input[name='checkout']").first();
					if (currCheckin.val()== currCheckout.val())
					{
						jQuery(currForm).find('.bfidaterangepicker-checkout').hide();
					}
// da vedere per selezione di 1 sola data
				currCalendarRange.on('hide.daterangepicker', function(ev, picker) {
					if (!picker.rangeSelected && picker.lastDateSelected != null)
					{
						var currDateFormat = "dd M";
						var currCheckin = jQuery(currForm).find("input[name='checkin']").first();
						var currCheckout = jQuery(currForm).find("input[name='checkout']").first();
						currCheckin.val(picker.lastDateSelected.format('DD/MM/YYYY'));
						currCheckout.val(picker.lastDateSelected.format('DD/MM/YYYY'));
						picker.setStartDate(picker.lastDateSelected);
						picker.setEndDate(picker.lastDateSelected);
						jQuery(currForm).find('.bfidaterangepicker-checkin span').html(jQuery.datepicker.formatDate(currDateFormat,picker.lastDateSelected.toDate()) );
						jQuery(currForm).find('.bfidaterangepicker-checkout span').html(jQuery.datepicker.formatDate(currDateFormat,picker.lastDateSelected.toDate()));
						jQuery(currForm).find('.bfidaterangepicker-checkout').show();
						if (currCheckin.val()== currCheckout.val())
						{
							jQuery(currForm).find('.bfidaterangepicker-checkout').hide();
//							currCheckout.val(bookingfor.getDisplayDate(bookingfor.dateAdd(picker.lastDateSelected, "day", 1)));
//
						}
						jQuery(currForm).find('.bfi-datepicker-clear').show();

					}
//					console.log('rangeSelected')
//					console.log(picker.rangeSelected)
//					console.log(picker.lastDateSelected)
//					console.log(picker.startDate.format('YYYY-MM-DD'))
//					console.log(picker.endDate.format('YYYY-MM-DD'))
				  //do something, like clearing an input
//					alert("hide" + picker.startDate.format('YYYY-MM-DD'));
				});
		
			currForm.validate(
				{
//					invalidHandler: function (form, validator) {
//						var errors = validator.numberOfInvalids();
//						if (errors) {
//							validator.errorList[0].element.focus();
//						}
//					},
					invalidHandler: function (form, validator) {
						var errors = validator.numberOfInvalids();
						if (errors) {
//							alert(validator.errorList[0].message);

							validator.errorList[0].element.focus();
						}
					},
					showErrors: function (errorMap, errorList) {

						// Clean up any tooltips for valid elements
						jQuery.each(this.validElements(), function (index, element) {
							var $element = jQuery(element);

							$element.prop("title", "") // Clear the title - there is no error associated anymore
								.removeClass("bfi-error")
							if ($element.is(':data(tooltip)')) {
								$element.bfiTooltip('destroy');
							}
						});
						// Create new tooltips for invalid elements
						jQuery.each(errorList, function (index, error) {
							var $element = jQuery(error.element);
							if ($element.is(':data(tooltip)')) {
								$element.bfiTooltip('destroy');
							}

							$element.prop("title", error.message)
								.addClass("bfi-error")
							if (typeof bfiTooltip  !== "function") {
								jQuery.widget.bridge('bfiTooltip', jQuery.ui.tooltip );
							}
							$element.bfiTooltip({
								position: { my: 'center bottom', at: 'center top-10' },
								tooltipClass: 'bfi-tooltip bfi-tooltip-top '
							});
							$element.bfiTooltip("open");

						});
					},
					focusCleanup: true,
					errorClass: "bfi-error",
					highlight: function (label) {
					},
					success: function (label) {
						jQuery(label).remove();
					},
					cache : false,
					submitHandler: function (form) {
						var $form = jQuery(form);
						var currDateFormat = "dd/mm/yy";
						var currTmpCheckin = jQuery(form).find("input[name='checkin']").first();
						var currTmpCheckout = jQuery(form).find("input[name='checkout']").first();
						var currTmpCheckinDate = jQuery.datepicker.parseDate("dd/mm/yy", currTmpCheckin.val());
						var currTmpCheckoutDate = jQuery.datepicker.parseDate("dd/mm/yy", currTmpCheckout.val());
						if (currTmpCheckoutDate<currTmpCheckinDate)
						{
							currTmpCheckout.val(currTmpCheckin.val()) ;
						}
//						if (currTmpCheckin.val()== currTmpCheckout.val())
//						{
//							jQuery(currForm).find('.bfidaterangepicker-checkout').hide();
//							currTmpCheckout.val(bookingfor.getDisplayDate(bookingfor.dateAdd( jQuery.datepicker.parseDate("dd/mm/yy", currTmpCheckin.val()), "day", 1)));
//
//						}


						var $btnresource = $form.find(".bfi-btnsendform").first();
						if ($form.valid()) {
							if ($form.data('submitted') === true) {
								return false;
							} else {
								// Mark it so that the next submit can be ignored
								$form.data('submitted', true);
								var isMacLike = navigator.platform.match(/(Mac|iPhone|iPod|iPad)/i) ? true : false;
								var isIOS = navigator.platform.match(/(iPhone|iPod|iPad)/i) ? true : false;
								if (!isMacLike) {
									var iconBtn = $btnresource.find("i").first();
									iconBtn.removeClass("fa-search").addClass("fa-spinner fa-spin ");
									$btnresource.prop('disabled', true);

								}
								form.submit();
							}

						}
					}

				});
			if (currShowsearchtext != "0") {
				bookingfor.createAutocomplete(currForm);	
//				var currAutocomplete = jQuery(currForm).find(".bfi-autocomplete").first();
//				var currScope = currAutocomplete.attr("data-scope");
//				if (typeof currScope === "undefined" || !currScope.length){
//					currScope = "";
//				}
//				
//
//				currAutocomplete.blur(function(){
//					var keyEvent = jQuery.Event("keydown");
//					keyEvent.keyCode = jQuery.ui.keyCode.ENTER;
//					jQuery(this).trigger(keyEvent);
//				}).autocomplete({
//					autoFocus: true,
//					source: function (request, response) {
//						var previous_request = currAutocomplete.data( "jqXHR" );
//						if( previous_request ) {
//							previous_request.abort();
//						}
//						var urlSearch =  bfi_variables.bfi_urlSearchByText + "?term=" + request.term + "&resultClasses=" + currScope + "&maxresults=5&minMatchingPercentage=90&cultureCode=" + bfi_variables.bfi_cultureCode;
//						currAutocomplete.data( "jqXHR",
//							jQuery.getJSON(urlSearch, function (data) {
//								if (data.length) {
//									jQuery.each(data, function (key, item) {
//										var currentVal = "";
//										if (item.ItemTypeOrder == 0 || item.ItemTypeOrder == 2 || item.ItemTypeOrder == 4 || item.ItemTypeOrder == 6) { currentVal = "zoneIds|" + item.ZoneId; }
//										if (item.ItemTypeOrder == 1) { currentVal = "stateIds|" + item.StateId + ":" + item.XPos + ":" + item.YPos; }
//										if (item.ItemTypeOrder == 3) { currentVal = "regionIds|" + item.RegionId + ":" + item.XPos + ":" + item.YPos; }
//										if (item.ItemTypeOrder == 5) { currentVal = "cityIds|" + item.CityId + ":" + item.XPos + ":" + item.YPos; }
//										if (item.ItemTypeOrder == 7) { currentVal = "poiIds|" + item.PointOfInterestId + ":" + item.XPos + ":" + item.YPos;  }
//										if (item.ItemTypeOrder == 8) { currentVal = "evtCategoryids|" + item.EventCategoryId; }
//										if (item.ItemTypeOrder == 9) { currentVal = "merchantCategoryId|" + item.MerchantCategoryId; }
//										if (item.ItemTypeOrder == 10) { currentVal = "groupCategoryId|" + item.ProductGroupCategoryId; }
//										if (item.ItemTypeOrder == 11) { currentVal = "masterTypeId|" + item.ProductCategoryId; }
//										if (item.ItemTypeOrder == 12) { currentVal = "evtTagIds|" + item.EventTagId; }
//										if (item.ItemTypeOrder == 13) { currentVal = "merchantTagIds|" + item.MerchantTagId; }
//										if (item.ItemTypeOrder == 14) { currentVal = "groupTagIds|" + item.ProductGroupTagId; }
//										if (item.ItemTypeOrder == 15) { currentVal = "resourceTagIds|" + item.ProductTagId; }
//										if (item.ItemTypeOrder == 16) { currentVal = "eventIds|" + item.EventId + ":" + item.XPos + ":" + item.YPos; }
//										if (item.ItemTypeOrder == 17) { currentVal = "merchantIds|" + item.MerchantId; }
//										if (item.ItemTypeOrder == 18) { currentVal = "groupresourceIds|" + item.ProductGroupId; }
//										if (item.ItemTypeOrder == 19) { currentVal = "resourceIds|" + item.ProductId; }
//										item.Value = currentVal;
//									});
//									response(data);
//									currAutocomplete.removeClass("ui-autocomplete-loading");
//								} else {
//									response([{
//										Name: bfi_variables.bfi_txtnoresult
//									}]);
//									currAutocomplete.removeClass("ui-autocomplete-loading");
//								}
//							})
//						);
//					},
//					/*response: function( event, ui ) {
//						jQuery(this).removeClass("ui-autocomplete-loading");
//					},*/
//					minLength: 2,
//					delay: 250,
//					select: function (event, ui) {
//						var selectedVal = ui.item.Value;
//						//			var selectedVal = jQuery(event.srcElement).attr("data-value");
//						if (selectedVal.length) {
//							currAutocomplete.closest("form").find("[name=stateIds],[name=regionIds],[name=cityIds],[name=zoneIds],[name=merchantCategoryId],[name=masterTypeId],[name=filters\\[productcategory\\]],[name=groupCategoryId],[name=merchantTagIds],[name=groupTagIds],[name=productTagIds],[name=merchantIds],[name=groupresourceIds],[name=resourceIds],[name=points],[name=filter_order],[name=getBaseFiltersFor]").val("");
//							currAutocomplete.closest("form").find("[name=searchTermValue]").val(selectedVal);
//							switch (selectedVal.split('|')[0]) {
//								case "stateIds":
//								case "regionIds":
//                                    var completeValue = selectedVal.split('|')[1];
//                                    currAutocomplete.closest("form").find("[name=" + selectedVal.split('|')[0] + "]").val(completeValue.split(":")[0]);
//                                    break;
//								case "cityIds":
//								case "poiIds":
//									var completeValue = selectedVal.split('|')[1];
//									currAutocomplete.closest("form").find("[name=points]").val("0|" + completeValue.split(":")[1] + " " + completeValue.split(":")[2]);
//									break;
//								case "merchantIds":
//									currAutocomplete.closest("form").find("[name=filter_order]").val("merchantid:" + selectedVal.split('|')[1]);
//									break;
//								case "groupresourceIds":
//									currAutocomplete.closest("form").find("[name=filter_order]").val("parentid:" + selectedVal.split('|')[1]);
//									break;
//								case "resourceIds":
//									currAutocomplete.closest("form").find("[name=filter_order]").val("productid:" + selectedVal.split('|')[1]);
//									break;
//								case "masterTypeId":
//									//currAutocomplete.closest("form").find("[name=" + selectedVal.split('|')[0] + "]").val(selectedVal.split('|')[1]);
//									currAutocomplete.closest("form").find("[name=filters\\[productcategory\\]]").val(selectedVal.split('|')[1]);
//									currAutocomplete.closest("form").find("[name=getBaseFiltersFor]").val("productcategory");
//									break;
//								default:
//									currAutocomplete.closest("form").find("[name=" + selectedVal.split('|')[0] + "]").val(selectedVal.split('|')[1]);
//									break;
//							}
//							jQuery(this).val(ui.item.Name);
//							event.preventDefault();
//						}
//						//log( "Selected: " + ui.item.value + " aka " + ui.item.id );
//					},
//					change: function( event, ui ) {
//						if (currAutocomplete.val().length<2) {
//							currAutocomplete.closest("form").find("[name=stateIds],[name=regionIds],[name=cityIds],[name=zoneIds],[name=merchantCategoryId],[name=masterTypeId],[name=filters\\[productcategory\\]],[name=groupCategoryId],[name=merchantTagIds],[name=groupTagIds],[name=productTagIds],[name=merchantIds],[name=groupresourceIds],[name=resourceIds],[name=points],[name=filter_order],[name=getBaseFiltersFor]").val("");
//						}
//					},
//					open: function () {
//						jQuery(this).data("uiAutocomplete").menu.element.addClass("bfi-autocomplete");
//					},
//				});
//
//
//				currAutocomplete.data("ui-autocomplete")._renderItem = function (ul, item) {
//					
//					var currentVal = "";
//					var htmlContent = item.Name;
//					if (item.ItemTypeOrder == 0 || item.ItemTypeOrder == 2 || item.ItemTypeOrder == 4 || item.ItemTypeOrder == 6) { 
//						currentVal = "zoneIds|" + item.ZoneId;
//					}
//					if (item.ItemTypeOrder == 1) { currentVal = "stateIds|" + item.StateId + ":" + item.XPos + ":" + item.YPos; }
//					if (item.ItemTypeOrder == 3) { currentVal = "regionIds|" + item.RegionId + ":" + item.XPos + ":" + item.YPos; }
//					if (item.ItemTypeOrder == 5) { currentVal = "cityIds|" + item.CityId + ":" + item.XPos + ":" + item.YPos; }
//					if (item.ItemTypeOrder == 7) { currentVal = "poiIds|" + item.PointOfInterestId + ":" + item.XPos + ":" + item.YPos;  }
//					if (item.ItemTypeOrder == 8) { currentVal = "evtCategoryids|" + item.EventCategoryId; }
//					if (item.ItemTypeOrder == 9) { currentVal = "merchantCategoryId|" + item.MerchantCategoryId; }
//					if (item.ItemTypeOrder == 10) { currentVal = "groupCategoryId|" + item.ProductGroupCategoryId; }
//					if (item.ItemTypeOrder == 11) { currentVal = "masterTypeId|" + item.ProductCategoryId; }
//					if (item.ItemTypeOrder == 12) { currentVal = "evtTagIds|" + item.EventTagId; }
//					if (item.ItemTypeOrder == 13) { currentVal = "merchantTagIds|" + item.MerchantTagId; }
//					if (item.ItemTypeOrder == 14) { currentVal = "groupTagIds|" + item.ProductGroupTagId; }
//					if (item.ItemTypeOrder == 15) { currentVal = "resourceTagIds|" + item.ProductTagId; }
//					if (item.ItemTypeOrder == 16) { currentVal = "eventIds|" + item.EventId + ":" + item.XPos + ":" + item.YPos; }
//					if (item.ItemTypeOrder == 17) { currentVal = "merchantIds|" + item.MerchantId; }
//					if (item.ItemTypeOrder == 18) { currentVal = "groupresourceIds|" + item.ProductGroupId; }
//					if (item.ItemTypeOrder == 19) { currentVal = "resourceIds|" + item.ProductId; }
//					
//					switch (item.ItemTypeOrder) {
//						case 0:
//						case 1:
//						case 2:
//						case 3:
//						case 4:
//						case 5:
//						case 6:
//							htmlContent = '<i class="fa fa-map-marker"></i>&nbsp;' + item.Name;
//							break;
//						case 7:
//							htmlContent = '<i class="fa fa-street-view"></i>&nbsp;' + item.Name;
//							break;
//						case 8:
//						case 9:
//						case 10:
//						case 11:
//						case 17:
//							htmlContent = '<i class="fa fa-building"></i>&nbsp;' + item.Name;
//							break;
//						case 18:
//						case 19:
//							htmlContent = '<i class="fa fa-bed"></i>&nbsp;' + item.Name;
//							break;
//						case 12:
//						case 13:
//						case 14:
//						case 15:
//							htmlContent = '<i class="fa fa-tag"></i>&nbsp;' + item.Name;
//							break;
//					}
//					
//					if (currentVal.length) {
//						return jQuery("<li>").attr("data-value", currentVal).html(htmlContent).appendTo(ul);
//					} else {
//						return jQuery("<li>").attr("data-value", "").html(htmlContent).addClass("ui-state-disabled").appendTo(ul);
//					}
//
//				};
	
			}
	});
	
	// time slot form END


	// rental form
	jQuery('.bfi-mod-bookingforsearch-rental').each(function () {
		var currModule = jQuery(this);
		var currForm = jQuery(this).find(".bfi-form-default").first();
		var currModId = jQuery(this).attr("data-currmodid");
		var fixedontop = jQuery(this).attr("data-fixedontop");
		var fixedontopcorrection = jQuery(this).attr("data-fixedontopcorrection");

		if (currForm.length) {
			var currShowsearchtext = jQuery(currForm).attr("data-showsearchtext");
			var currResultinsamepg = jQuery(currForm).find("input[name='resultinsamepg']").first();
			var currResultview = jQuery(".bfi-resultview").first();
			var currDefaultaction = currForm.attr("data-defaultaction");
			if (currResultinsamepg.length && currResultinsamepg.val() == "1" && currResultview.length==0) {
				currForm.attr('action', currDefaultaction);
			}
			var currShowdirection = jQuery(currForm).attr("data-showdirection");
			var currFixedontop = jQuery(currForm).attr("data-fixedontop");
			currForm.validate(
				{
//					invalidHandler: function (form, validator) {
//						var errors = validator.numberOfInvalids();
//						if (errors) {
//							validator.errorList[0].element.focus();
//						}
//					},
					invalidHandler: function (form, validator) {
						var errors = validator.numberOfInvalids();
						if (errors) {
//							alert(validator.errorList[0].message);

							validator.errorList[0].element.focus();
						}
					},
					showErrors: function (errorMap, errorList) {

						// Clean up any tooltips for valid elements
						jQuery.each(this.validElements(), function (index, element) {
							var $element = jQuery(element);

							$element.prop("title", "") // Clear the title - there is no error associated anymore
								.removeClass("bfi-error")
							if ($element.is(':data(tooltip)')) {
								$element.bfiTooltip('destroy');
							}
						});
						// Create new tooltips for invalid elements
						jQuery.each(errorList, function (index, error) {
							var $element = jQuery(error.element);
							if ($element.is(':data(tooltip)')) {
								$element.bfiTooltip('destroy');
							}

							$element.prop("title", error.message)
								.addClass("bfi-error")
							if (typeof bfiTooltip  !== "function") {
								jQuery.widget.bridge('bfiTooltip', jQuery.ui.tooltip );
							}
							if (typeof bfiTooltip  !== "function") {
								jQuery.widget.bridge('bfiTooltip', jQuery.ui.tooltip );
							}
							$element.bfiTooltip({
								position: { my: 'center bottom', at: 'center top-10' },
								tooltipClass: 'bfi-tooltip bfi-tooltip-top '
							});
							$element.bfiTooltip("open");

						});
					},
					focusCleanup: true,
					errorClass: "bfi-error",
					highlight: function (label) {
					},
					success: function (label) {
						jQuery(label).remove();
					},
					cache : false,
					submitHandler: function (form) {
						var $form = jQuery(form);
						var $btnresource = $form.find(".bfi-btnsendform").first();
						if ($form.valid()) {
							if ($form.data('submitted') === true) {
								return false;
							} else {
								// Mark it so that the next submit can be ignored
								$form.data('submitted', true);
								var isMacLike = navigator.platform.match(/(Mac|iPhone|iPod|iPad)/i) ? true : false;
								var isIOS = navigator.platform.match(/(iPhone|iPod|iPad)/i) ? true : false;
								if (!isMacLike) {
									var iconBtn = $btnresource.find("i").first();
									iconBtn.removeClass("fa-search").addClass("fa-spinner fa-spin ");
									$btnresource.prop('disabled', true);

								}
								form.submit();
							}

						}
					}

				});

				// new calendar
				var currCalendarRange = jQuery(currForm).find(".bfidaterangepicker");
				currCalendarRange.daterangepicker({
					 "minDate": moment(),
					 "maxDate": moment().add(1,'year'),
					 "minYear": parseInt(moment().format('YYYY')),
					 "maxYear": parseInt(moment().format('YYYY'),1),
					"autoApply": true,
					"timePicker": true,
					"timePicker24Hour": true,
					"timePickerIncrement": 15,
					"locale": {
						"format": "DD/MM/YYYY HH:mm",
						"separator": " - ",
						"applyLabel": bfi_variables.bfi_txtTitleBtnOk,
						"cancelLabel": "Cancel",
						"fromLabel": "From",
						"toLabel": "To",
						"customRangeLabel": "Custom",
						"weekLabel": "W",
						"daysOfWeek": bfi_variables.bfi_txtTitleDays,
						"monthNames": bfi_variables.bfi_txtTitleMonths,
						"firstDay": 1
					},
//					isInvalidDate: function(date) {
//						//compare to your list of dates, return true if date is in the list
//					}
					"startDate": currCalendarRange.attr('data-checkin'),
					"endDate": currCalendarRange.attr('data-checkout')
				}, function(start, end) {
						var currDateFormat = "D, dd M";
						var windowsize = jQuery(window).width();
						if (windowsize > 769 && windowsize < 1300) {
							currDateFormat = " dd mm";
						}
					var currTmpForm = jQuery(this).closest("form");
					var currCheckin = jQuery(currForm).find("input[name='checkin']").first();
					var currCheckout = jQuery(currForm).find("input[name='checkout']").first();
					var currCheckinTime = jQuery(currForm).find("input[name='checkintime']").first();
					var currCheckoutTime = jQuery(currForm).find("input[name='checkouttime']").first();

					currCheckin.val(start.format('DD/MM/YYYY'));
					currCheckout.val(end.format('DD/MM/YYYY'));
					currCheckinTime.val(start.format('HH:mm'));
					currCheckoutTime.val(end.format('HH:mm'));
					jQuery(this.element).find('.bfidaterangepicker-checkin span').html(jQuery.datepicker.formatDate(currDateFormat,start.toDate()) + ', ' + start.format('HH:mm') );
					jQuery(this.element).find('.bfidaterangepicker-checkout span').html(jQuery.datepicker.formatDate(currDateFormat,end.toDate()) + ', ' + end.format('HH:mm'));
				});

				var currDateFormat = "D, dd M";
				var windowsize = jQuery(window).width();
				if (windowsize > 769 && windowsize < 1300) {
					currDateFormat = " dd mm";
				}
				var startDate = currCalendarRange.data('daterangepicker').startDate;
				var endDate = currCalendarRange.data('daterangepicker').endDate;
				jQuery(currForm).find('.bfidaterangepicker-checkin span').html(jQuery.datepicker.formatDate(currDateFormat,startDate.toDate()) + ', ' + startDate.format('HH:mm') );
				jQuery(currForm).find('.bfidaterangepicker-checkout span').html(jQuery.datepicker.formatDate(currDateFormat,endDate.toDate()) + ', ' + endDate.format('HH:mm'));
			
			if (currShowdirection != "0" && currFixedontop != "0") {
				window.onscroll = function () { bfi_affix(currModId) };
			}

			if (currShowsearchtext != "0") {
				var currAutocomplete = jQuery(currForm).find(".bfi-autocomplete").first();
				var currScope = currAutocomplete.attr("data-scope");
				if (typeof currScope === "undefined" || !currScope.length){
					currScope = "";
				}
				

				currAutocomplete.blur(function(){
					var keyEvent = jQuery.Event("keydown");
					keyEvent.keyCode = jQuery.ui.keyCode.ENTER;
					jQuery(this).trigger(keyEvent);
				}).autocomplete({
					autoFocus: true,
					source: function (request, response) {
						var previous_request = currAutocomplete.data( "jqXHR" );
						if( previous_request ) {
							previous_request.abort();
						}
						var urlSearch =  bfi_variables.bfi_urlSearchByText + "?term=" + request.term + "&resultClasses=" + currScope + "&maxresults=5&limitregions=1&minMatchingPercentage=90&cultureCode=" + bfi_variables.bfi_cultureCode;
						currAutocomplete.data( "jqXHR",
							jQuery.getJSON(urlSearch, function (data) {
								if (data.length) {
									jQuery.each(data, function (key, item) {
										var currentVal = "";
										if (item.ItemTypeOrder == 0 || item.ItemTypeOrder == 2 || item.ItemTypeOrder == 4 || item.ItemTypeOrder == 6) { currentVal = "zoneIds|" + item.ZoneId; }
										if (item.ItemTypeOrder == 1) { currentVal = "stateIds|" + item.StateId + ":" + item.XPos + ":" + item.YPos; }
										if (item.ItemTypeOrder == 3) { currentVal = "regionIds|" + item.RegionId + ":" + item.XPos + ":" + item.YPos; }
										if (item.ItemTypeOrder == 5) { currentVal = "cityIds|" + item.CityId + ":" + item.XPos + ":" + item.YPos; }
										if (item.ItemTypeOrder == 7) { currentVal = "poiIds|" + item.PointOfInterestId + ":" + item.XPos + ":" + item.YPos;  }
										if (item.ItemTypeOrder == 8) { currentVal = "evtCategoryids|" + item.EventCategoryId; }
										if (item.ItemTypeOrder == 9) { currentVal = "merchantCategoryId|" + item.MerchantCategoryId; }
										if (item.ItemTypeOrder == 10) { currentVal = "groupCategoryId|" + item.ProductGroupCategoryId; }
										if (item.ItemTypeOrder == 11) { currentVal = "masterTypeId|" + item.ProductCategoryId; }
										if (item.ItemTypeOrder == 12) { currentVal = "evtTagIds|" + item.EventTagId; }
										if (item.ItemTypeOrder == 13) { currentVal = "merchantTagIds|" + item.MerchantTagId; }
										if (item.ItemTypeOrder == 14) { currentVal = "groupTagIds|" + item.ProductGroupTagId; }
										if (item.ItemTypeOrder == 15) { currentVal = "resourceTagIds|" + item.ProductTagId; }
										if (item.ItemTypeOrder == 16) { currentVal = "eventIds|" + item.EventId + ":" + item.XPos + ":" + item.YPos; }
										if (item.ItemTypeOrder == 17) { currentVal = "merchantIds|" + item.MerchantId; }
										if (item.ItemTypeOrder == 18) { currentVal = "groupresourceIds|" + item.ProductGroupId; }
										if (item.ItemTypeOrder == 19) { currentVal = "resourceIds|" + item.ProductId; }
										item.Value = currentVal;
									});
									response(data);
									currAutocomplete.removeClass("ui-autocomplete-loading");
								} else {
									response([{
										Name: bfi_variables.bfi_txtnoresult
									}]);
									currAutocomplete.removeClass("ui-autocomplete-loading");
								}
							})
						);
					},
					/*response: function( event, ui ) {
						jQuery(this).removeClass("ui-autocomplete-loading");
					},*/
					minLength: 2,
					delay: 250,
					select: function (event, ui) {
						var selectedVal = ui.item.Value;
						//			var selectedVal = jQuery(event.srcElement).attr("data-value");
						if (selectedVal.length) {
							currAutocomplete.closest("form").find("[name=stateIds],[name=regionIds],[name=cityIds],[name=zoneIds],[name=merchantCategoryId],[name=masterTypeId],[name=filters\\[productcategory\\]],[name=groupCategoryId],[name=merchantTagIds],[name=groupTagIds],[name=productTagIds],[name=merchantIds],[name=groupresourceIds],[name=resourceIds],[name=points],[name=filter_order],[name=getBaseFiltersFor]").val("");
							currAutocomplete.closest("form").find("[name=searchTermValue]").val(selectedVal);
							switch (selectedVal.split('|')[0]) {
								case "stateIds":
								case "regionIds":
                                    var completeValue = selectedVal.split('|')[1];
                                    currAutocomplete.closest("form").find("[name=" + selectedVal.split('|')[0] + "]").val(completeValue.split(":")[0]);
                                    break;
								case "cityIds":
								case "poiIds":
									var completeValue = selectedVal.split('|')[1];
									currAutocomplete.closest("form").find("[name=points]").val("0|" + completeValue.split(":")[1] + " " + completeValue.split(":")[2]);
									break;
								case "merchantIds":
									currAutocomplete.closest("form").find("[name=filter_order]").val("merchantid:" + selectedVal.split('|')[1]);
									break;
								case "groupresourceIds":
									currAutocomplete.closest("form").find("[name=filter_order]").val("parentid:" + selectedVal.split('|')[1]);
									break;
								case "resourceIds":
									currAutocomplete.closest("form").find("[name=filter_order]").val("productid:" + selectedVal.split('|')[1]);
									break;
								case "masterTypeId":
									//currAutocomplete.closest("form").find("[name=" + selectedVal.split('|')[0] + "]").val(selectedVal.split('|')[1]);
									currAutocomplete.closest("form").find("[name=filters\\[productcategory\\]]").val(selectedVal.split('|')[1]);
									currAutocomplete.closest("form").find("[name=getBaseFiltersFor]").val("productcategory");
									break;
								default:
									currAutocomplete.closest("form").find("[name=" + selectedVal.split('|')[0] + "]").val(selectedVal.split('|')[1]);
									break;
							}
							jQuery(this).val(ui.item.Name);
							event.preventDefault();
						}
						//log( "Selected: " + ui.item.value + " aka " + ui.item.id );
					},
					change: function( event, ui ) {
						if (currAutocomplete.val().length<2) {
							currAutocomplete.closest("form").find("[name=stateIds],[name=regionIds],[name=cityIds],[name=zoneIds],[name=merchantCategoryId],[name=masterTypeId],[name=filters\\[productcategory\\]],[name=groupCategoryId],[name=merchantTagIds],[name=groupTagIds],[name=productTagIds],[name=merchantIds],[name=groupresourceIds],[name=resourceIds],[name=points],[name=filter_order],[name=getBaseFiltersFor]").val("");
						}
					},
					open: function () {
						jQuery(this).data("uiAutocomplete").menu.element.addClass("bfi-autocomplete");
					},
				});


				currAutocomplete.data("ui-autocomplete")._renderItem = function (ul, item) {
					
					var currentVal = "";
					var htmlContent = item.Name;
					if (item.ItemTypeOrder == 0 || item.ItemTypeOrder == 2 || item.ItemTypeOrder == 4 || item.ItemTypeOrder == 6) { 
						currentVal = "zoneIds|" + item.ZoneId;
					}
					if (item.ItemTypeOrder == 1) { currentVal = "stateIds|" + item.StateId + ":" + item.XPos + ":" + item.YPos; }
					if (item.ItemTypeOrder == 3) { currentVal = "regionIds|" + item.RegionId + ":" + item.XPos + ":" + item.YPos; }
					if (item.ItemTypeOrder == 5) { currentVal = "cityIds|" + item.CityId + ":" + item.XPos + ":" + item.YPos; }
					if (item.ItemTypeOrder == 7) { currentVal = "poiIds|" + item.PointOfInterestId + ":" + item.XPos + ":" + item.YPos;  }
					if (item.ItemTypeOrder == 8) { currentVal = "evtCategoryids|" + item.EventCategoryId; }
					if (item.ItemTypeOrder == 9) { currentVal = "merchantCategoryId|" + item.MerchantCategoryId; }
					if (item.ItemTypeOrder == 10) { currentVal = "groupCategoryId|" + item.ProductGroupCategoryId; }
					if (item.ItemTypeOrder == 11) { currentVal = "masterTypeId|" + item.ProductCategoryId; }
					if (item.ItemTypeOrder == 12) { currentVal = "evtTagIds|" + item.EventTagId; }
					if (item.ItemTypeOrder == 13) { currentVal = "merchantTagIds|" + item.MerchantTagId; }
					if (item.ItemTypeOrder == 14) { currentVal = "groupTagIds|" + item.ProductGroupTagId; }
					if (item.ItemTypeOrder == 15) { currentVal = "resourceTagIds|" + item.ProductTagId; }
					if (item.ItemTypeOrder == 16) { currentVal = "eventIds|" + item.EventId + ":" + item.XPos + ":" + item.YPos; }
					if (item.ItemTypeOrder == 17) { currentVal = "merchantIds|" + item.MerchantId; }
					if (item.ItemTypeOrder == 18) { currentVal = "groupresourceIds|" + item.ProductGroupId; }
					if (item.ItemTypeOrder == 19) { currentVal = "resourceIds|" + item.ProductId; }
					
					switch (item.ItemTypeOrder) {
						case 0:
						case 1:
						case 2:
						case 3:
						case 4:
						case 5:
						case 6:
							htmlContent = '<i class="fa fa-map-marker"></i>&nbsp;' + item.Name;
							break;
						case 7:
							htmlContent = '<i class="fa fa-street-view"></i>&nbsp;' + item.Name;
							break;
						case 8:
						case 9:
						case 10:
						case 11:
						case 17:
							htmlContent = '<i class="fa fa-building"></i>&nbsp;' + item.Name;
							break;
						case 18:
						case 19:
							htmlContent = '<i class="fa fa-bed"></i>&nbsp;' + item.Name;
							break;
						case 12:
						case 13:
						case 14:
						case 15:
							htmlContent = '<i class="fa fa-tag"></i>&nbsp;' + item.Name;
							break;
					}
					
					if (currentVal.length) {
						return jQuery("<li>").attr("data-value", currentVal).html(htmlContent).appendTo(ul);
					} else {
						return jQuery("<li>").attr("data-value", "").html(htmlContent).addClass("ui-state-disabled").appendTo(ul);
					}

				};

//***************dropoff
				var currAutocompletedropoff = jQuery(currForm).find(".bfi-dropoff").first();				

				currAutocompletedropoff.blur(function(){
					var keyEvent = jQuery.Event("keydown");
					keyEvent.keyCode = jQuery.ui.keyCode.ENTER;
					jQuery(this).trigger(keyEvent);
				}).autocomplete({
					autoFocus: true,
					source: function (request, response) {
						var previous_request = currAutocompletedropoff.data( "jqXHR" );
						if( previous_request ) {
							previous_request.abort();
						}
						var urlSearch =  bfi_variables.bfi_urlSearchByText + "?term=" + request.term + "&resultClasses=" + currScope + "&maxresults=5&limitregions=1&minMatchingPercentage=90&cultureCode=" + bfi_variables.bfi_cultureCode;
						currAutocompletedropoff.data( "jqXHR",
							jQuery.getJSON(urlSearch, function (data) {
								if (data.length) {
									jQuery.each(data, function (key, item) {
										var currentVal = "";
										if (item.ItemTypeOrder == 0 || item.ItemTypeOrder == 2 || item.ItemTypeOrder == 4 || item.ItemTypeOrder == 6) { currentVal = "zoneIds|" + item.ZoneId; }
										if (item.ItemTypeOrder == 1) { currentVal = "stateIds|" + item.StateId + ":" + item.XPos + ":" + item.YPos; }
										if (item.ItemTypeOrder == 3) { currentVal = "regionIds|" + item.RegionId + ":" + item.XPos + ":" + item.YPos; }
										if (item.ItemTypeOrder == 5) { currentVal = "cityIds|" + item.CityId + ":" + item.XPos + ":" + item.YPos; }
										if (item.ItemTypeOrder == 7) { currentVal = "poiIds|" + item.PointOfInterestId + ":" + item.XPos + ":" + item.YPos;  }
										if (item.ItemTypeOrder == 8) { currentVal = "evtCategoryids|" + item.EventCategoryId; }
										if (item.ItemTypeOrder == 9) { currentVal = "merchantCategoryId|" + item.MerchantCategoryId; }
										if (item.ItemTypeOrder == 10) { currentVal = "groupCategoryId|" + item.ProductGroupCategoryId; }
										if (item.ItemTypeOrder == 11) { currentVal = "masterTypeId|" + item.ProductCategoryId; }
										if (item.ItemTypeOrder == 12) { currentVal = "evtTagIds|" + item.EventTagId; }
										if (item.ItemTypeOrder == 13) { currentVal = "merchantTagIds|" + item.MerchantTagId; }
										if (item.ItemTypeOrder == 14) { currentVal = "groupTagIds|" + item.ProductGroupTagId; }
										if (item.ItemTypeOrder == 15) { currentVal = "resourceTagIds|" + item.ProductTagId; }
										if (item.ItemTypeOrder == 16) { currentVal = "eventIds|" + item.EventId + ":" + item.XPos + ":" + item.YPos; }
										if (item.ItemTypeOrder == 17) { currentVal = "merchantIds|" + item.MerchantId; }
										if (item.ItemTypeOrder == 18) { currentVal = "groupresourceIds|" + item.ProductGroupId; }
										if (item.ItemTypeOrder == 19) { currentVal = "resourceIds|" + item.ProductId; }
										item.Value = currentVal;
									});
									response(data);
									currAutocompletedropoff.removeClass("ui-autocomplete-loading");
								} else {
									response([{
										Name: bfi_variables.bfi_txtnoresult
									}]);
									currAutocompletedropoff.removeClass("ui-autocomplete-loading");
								}
							})
						);
					},
					/*response: function( event, ui ) {
						jQuery(this).removeClass("ui-autocomplete-loading");
					},*/
					minLength: 2,
					delay: 250,
					select: function (event, ui) {
						var selectedVal = ui.item.Value;
						//			var selectedVal = jQuery(event.srcElement).attr("data-value");
						if (selectedVal.length) {
							currAutocompletedropoff.closest("form").find("[name=dropoffValue]").val(selectedVal);
														jQuery(this).val(ui.item.Name);
							event.preventDefault();
						}
						//log( "Selected: " + ui.item.value + " aka " + ui.item.id );
					},
					change: function( event, ui ) {
					},
					open: function () {
						jQuery(this).data("uiAutocomplete").menu.element.addClass("bfi-autocomplete");
					},
				});

				currAutocompletedropoff.data("ui-autocomplete")._renderItem = function (ul, item) {
					
					var currentVal = "";
					var htmlContent = item.Name;
					if (item.ItemTypeOrder == 0 || item.ItemTypeOrder == 2 || item.ItemTypeOrder == 4 || item.ItemTypeOrder == 6) { 
						currentVal = "zoneIds|" + item.ZoneId;
					}
					if (item.ItemTypeOrder == 1) { currentVal = "stateIds|" + item.StateId + ":" + item.XPos + ":" + item.YPos; }
					if (item.ItemTypeOrder == 3) { currentVal = "regionIds|" + item.RegionId + ":" + item.XPos + ":" + item.YPos; }
					if (item.ItemTypeOrder == 5) { currentVal = "cityIds|" + item.CityId + ":" + item.XPos + ":" + item.YPos; }
					if (item.ItemTypeOrder == 7) { currentVal = "poiIds|" + item.PointOfInterestId + ":" + item.XPos + ":" + item.YPos;  }
					if (item.ItemTypeOrder == 8) { currentVal = "evtCategoryids|" + item.EventCategoryId; }
					if (item.ItemTypeOrder == 9) { currentVal = "merchantCategoryId|" + item.MerchantCategoryId; }
					if (item.ItemTypeOrder == 10) { currentVal = "groupCategoryId|" + item.ProductGroupCategoryId; }
					if (item.ItemTypeOrder == 11) { currentVal = "masterTypeId|" + item.ProductCategoryId; }
					if (item.ItemTypeOrder == 12) { currentVal = "evtTagIds|" + item.EventTagId; }
					if (item.ItemTypeOrder == 13) { currentVal = "merchantTagIds|" + item.MerchantTagId; }
					if (item.ItemTypeOrder == 14) { currentVal = "groupTagIds|" + item.ProductGroupTagId; }
					if (item.ItemTypeOrder == 15) { currentVal = "resourceTagIds|" + item.ProductTagId; }
					if (item.ItemTypeOrder == 16) { currentVal = "eventIds|" + item.EventId + ":" + item.XPos + ":" + item.YPos; }
					if (item.ItemTypeOrder == 17) { currentVal = "merchantIds|" + item.MerchantId; }
					if (item.ItemTypeOrder == 18) { currentVal = "groupresourceIds|" + item.ProductGroupId; }
					if (item.ItemTypeOrder == 19) { currentVal = "resourceIds|" + item.ProductId; }
					
					switch (item.ItemTypeOrder) {
						case 0:
						case 1:
						case 2:
						case 3:
						case 4:
						case 5:
						case 6:
							htmlContent = '<i class="fa fa-map-marker"></i>&nbsp;' + item.Name;
							break;
						case 7:
							htmlContent = '<i class="fa fa-street-view"></i>&nbsp;' + item.Name;
							break;
						case 8:
						case 9:
						case 10:
						case 11:
						case 17:
							htmlContent = '<i class="fa fa-building"></i>&nbsp;' + item.Name;
							break;
						case 18:
						case 19:
							htmlContent = '<i class="fa fa-bed"></i>&nbsp;' + item.Name;
							break;
						case 12:
						case 13:
						case 14:
						case 15:
							htmlContent = '<i class="fa fa-tag"></i>&nbsp;' + item.Name;
							break;
					}
					
					if (currentVal.length) {
						return jQuery("<li>").attr("data-value", currentVal).html(htmlContent).appendTo(ul);
					} else {
						return jQuery("<li>").attr("data-value", "").html(htmlContent).addClass("ui-state-disabled").appendTo(ul);
					}

				};



			}				

			var currChecked = jQuery(currForm).find(".bfi-returnsamelocation-widget:checked");
			if (currChecked.length>0)
			{
				bookingfor.checkreturnsamelocation(currChecked);
			}
		};

	});
	
	// resources form
	jQuery('.bfi-mod-bookingforsearch-resources').each(function () {
		var currModule = jQuery(this);
		var currForm = currModule.find(".bfi-form-default").first();
		var currModId = currModule.attr("data-currmodid");

		var fixedontop = currModule.attr("data-fixedontop");
		var fixedontopcorrection = currModule.attr("data-fixedontopcorrection");

		if (currForm.length) {
			var currResultinsamepg = jQuery(currForm).find("input[name='resultinsamepg']").first();
			var currResultview = jQuery(".bfi-resultview").first();
			var currDefaultaction = currForm.attr("data-defaultaction");
			if (currResultinsamepg.length && currResultinsamepg.val() == "1" && currResultview.length==0) {
				currForm.attr('action', currDefaultaction);
			}

			var currCheckin = jQuery(currForm).find("input[name='checkin']").first();
			var currCheckout = jQuery(currForm).find("input[name='checkout']").first();
			var currShowdaterange = jQuery(currForm).attr("data-showdaterange");
			var currCheckinTime = jQuery(currForm).find("input[name='checkintime']").first();
			var currCheckoutTime = jQuery(currForm).find("input[name='checkouttime']").first();
			var currShowdatetimerange = jQuery(currForm).attr("data-showdatetimerange");
			var currShowdirection = jQuery(currForm).attr("data-showdirection");
			var currFixedontop = jQuery(currForm).attr("data-fixedontop");
			var currShowsearchtext = jQuery(currForm).attr("data-showsearchtext");

			currForm.validate(
				{
					invalidHandler: function (form, validator) {
						var errors = validator.numberOfInvalids();
						if (errors) {
							validator.errorList[0].element.focus();
						}
					},
					showErrors: function (errorMap, errorList) {
						// Clean up any tooltips for valid elements
						jQuery.each(this.validElements(), function (index, element) {
							var $element = jQuery(element);
							$element.prop("title", "") // Clear the title - there is no error associated anymore
								.removeClass("bfi-error")
							if ($element.is(':data(tooltip)')) {
								$element.bfiTooltip('destroy');
							}
						});
						// Create new tooltips for invalid elements
						jQuery.each(errorList, function (index, error) {
							var $element = jQuery(error.element);
							if ($element.is(':data(tooltip)')) {
								$element.bfiTooltip('destroy');
							}

							$element.prop("title", error.message)
								.addClass("bfi-error")
							if (typeof bfiTooltip  !== "function") {
								jQuery.widget.bridge('bfiTooltip', jQuery.ui.tooltip );
							}
							if (typeof bfiTooltip  !== "function") {
								jQuery.widget.bridge('bfiTooltip', jQuery.ui.tooltip );
							}
							$element.bfiTooltip({
								position: { my: 'center bottom', at: 'center top-10' },
								tooltipClass: 'bfi-tooltip bfi-tooltip-top '
							});
							$element.bfiTooltip("open");

						});
					},
					focusCleanup: true,
					errorClass: "bfi-error",
					highlight: function (label) {
					},
					success: function (label) {
						jQuery(label).remove();
					},
					cache : false,
					submitHandler: function (form) {
						var $form = jQuery(form);
						var $btnresource = $form.find(".bfi-btnsendform").first();
						if ($form.valid()) {
							if ($form.data('submitted') === true) {
								return false;
							} else {
								// Mark it so that the next submit can be ignored
								$form.data('submitted', true);
								if (currShowdaterange == "1" && currCheckin.length && currCheckout.length) {
									var currDateFormat = "dd/mm/yy";
									currCheckin.datepicker("option", "dateFormat", currDateFormat);
									currCheckout.datepicker("option", "dateFormat", currDateFormat);
								}

								var isMacLike = navigator.platform.match(/(Mac|iPhone|iPod|iPad)/i) ? true : false;
								var isIOS = navigator.platform.match(/(iPhone|iPod|iPad)/i) ? true : false;
								if (!isMacLike) {
									var iconBtn = $btnresource.find("i").first();
									iconBtn.removeClass("fa-search").addClass("fa-spinner fa-spin ");
									$btnresource.prop('disabled', true);

								}
								form.submit();
							}

						}
					}

				});

			if (currShowdaterange == "1" && currCheckin.length && currCheckout.length) {
				
				// new calendar
				var currCalendarRangeT = jQuery(currForm).find(".t-datepicker");

				currCalendarRangeT.tDatePicker({
					dateCheckIn: currCalendarRangeT.attr('data-checkin'), //'2020-03-10',
					dateCheckOut: currCalendarRangeT.attr('data-checkout'), //'2020-03-11',
					numCalendar:12,
					valiDation : true,
					iconArrowTop: false,
					titleCheckIn: 'Check In',
					titleCheckOut: 'Check Out',
					iconDate: '<i class="fa fa-calendar"></i> ',
					titleDateRange: bfi_variables.bfi_txtNights ,
					titleDateRanges: bfi_variables.bfi_txtNights ,
					showDateTheme:'dd-mm-yy',
					toDayShowTitle: false,
					titleCheckOutSelect: bfi_variables.bfi_txtTitleCheckOutSelect,
					titleBtnOk: bfi_variables.bfi_txtTitleBtnOk,
					titleToday:'Today',
					titleDays: JSON.parse(JSON.stringify(bfi_variables.bfi_txtTitleDaysT)) ,
					titleMonths: JSON.parse(JSON.stringify(bfi_variables.bfi_txtTitleMonths)) ,
					autoClose: false,
				}).on('beforeShowDay',function() {
//					jQuery(".ui-tooltip").bfiTooltip("hide");
					jQuery(".ui-tooltip-content").parents('div').remove();
					// console.log('beforeShowDay do something')
				}).on('selectedCI',function(e, slDateCI) {
					currCalendarRangeT.find('button.t-table-close').first().attr("disabled",true);
					currCheckin.datepicker('setDate', new Date(slDateCI));
					var currTmpForm = jQuery(this).closest("form");
					var currCheckinF = jQuery(currForm).find("input[name='checkin']").first();
					currCheckinF.val(jQuery.datepicker.formatDate( "dd/mm/yy", new Date(slDateCI)));

					// console.log('selectedCI do something')
					// console.log(new Date(slDateCI))
				 }).on('selectedCO',function(e, slDateCO) {
					currCalendarRangeT.find('button.t-table-close').first().removeAttr("disabled");
					currCheckout.datepicker('setDate', new Date(slDateCO));
					var currTmpForm = jQuery(this).closest("form");
					var currCheckoutF = jQuery(currForm).find("input[name='checkout']").first();
					currCheckoutF.val(jQuery.datepicker.formatDate( "dd/mm/yy", new Date(slDateCO)));

					// console.log('selectedCO do something')
					// console.log(new Date(slDateCO))
				  });
				
				// new calendar
				var currCalendarRange = jQuery(currForm).find(".bfidaterangepicker");
				currCalendarRange.daterangepicker({
					"minDate": moment(),
					"maxDate": moment().add(1,'year'),
					"minYear": parseInt(moment().format('YYYY')),
					"maxYear": parseInt(moment().format('YYYY'),1),
					"showLabel": true,
					"autoApply": true,
					"timePicker": currShowdatetimerange,
					"timePicker24Hour": true,
					"timePickerIncrement": 15,
					"maxSpan": {
							"days": 20
						},
					"locale": {
						"format": (currShowdatetimerange==0?"DD/MM/YYYY":"DD/MM/YYYY HH:mm"),
						"formatdisplay": (currShowdatetimerange==0?"ddd, DD MMM":"ddd, DD MMM HH:mm"),
						"separator": " - ",
						"applyLabel": bfi_variables.bfi_txtTitleBtnOk,
						"cancelLabel": "Cancel",
						"fromLabel": "From",
						"toLabel": "To",
						"customRangeLabel": "Custom",
						"weekLabel": "W",
						"daysOfWeek": bfi_variables.bfi_txtTitleDays,
						"monthNames": bfi_variables.bfi_txtTitleMonths,
						"firstDay": 1
					},
//					isInvalidDate: function(date) {
//						//compare to your list of dates, return true if date is in the list
//					}
					"startDate": currCalendarRange.attr('data-checkin'),
					"endDate": currCalendarRange.attr('data-checkout')
				}, function(start, end) {
						var currDateFormat = "D, dd M";
						var windowsize = jQuery(window).width();
						if (windowsize > 769 && windowsize < 1300) {
							currDateFormat = " dd mm";
						}
						var currTmpForm = jQuery(this).closest("form");
						var currCheckin = jQuery(currForm).find("input[name='checkin']").first();
						var currCheckout = jQuery(currForm).find("input[name='checkout']").first();
						var currCheckinTime = jQuery(currForm).find("input[name='checkintime']").first();
						var currCheckoutTime = jQuery(currForm).find("input[name='checkouttime']").first();

						currCheckin.val(start.format('DD/MM/YYYY'));
						currCheckout.val(end.format('DD/MM/YYYY'));
						currCheckinTime.val(start.format('HH:mm'));
						currCheckoutTime.val(end.format('HH:mm'));
						jQuery(this.element).find('.bfidaterangepicker-checkin span').html(jQuery.datepicker.formatDate(currDateFormat,start.toDate()) + (currShowdatetimerange==0 ? '' :', ' + start.format('HH:mm')));
						jQuery(this.element).find('.bfidaterangepicker-checkout span').html(jQuery.datepicker.formatDate(currDateFormat,end.toDate()) + (currShowdatetimerange ==0 ? '' :', ' + end.format('HH:mm')));
					}
				);

				var currDateFormat = "D, dd M";
				var windowsize = jQuery(window).width();
				if (windowsize > 769 && windowsize < 1300) {
					currDateFormat = " dd mm";
				}
				var startDate = currCalendarRange.data('daterangepicker').startDate;
				var endDate = currCalendarRange.data('daterangepicker').endDate;
				jQuery(currForm).find('.bfidaterangepicker-checkin span').html(jQuery.datepicker.formatDate(currDateFormat,startDate.toDate()) + (currShowdatetimerange==0 ? '' :', ' + start.format('HH:mm')));
				jQuery(currForm).find('.bfidaterangepicker-checkout span').html(jQuery.datepicker.formatDate(currDateFormat,endDate.toDate()) + (currShowdatetimerange ==0 ? '' :', ' + end.format('HH:mm')));
			}


			var nch = new Number(jQuery(this).attr("data-nch") || 0);
			var showChildrenagesmsg = new Number(jQuery(this).attr("data-showChildrenagesmsg") || 0);
			bfi_checkChildrenSearch(nch, showChildrenagesmsg, currModId);


			var windowsizeStart = jQuery(window).width();
			var currBtnBottom = jQuery(this).find(".bfi-mod-bookingforsearch-bottom").first();
			var currContent = currModule.find(".tab-pane").first();
			if (currBtnBottom.length)
			{
				jQuery(document).on('click tap', ".bfi-mod-bookingforsearch-bottom", function (e) {
					e.preventDefault();
//					currModule.find(".tab-pane").first().show();
					if (currContent.is(":visible"))
					{
						currContent.hide();
					}else{
						currContent.show();
					}

				});

			}else{
				currContent.show();
			}

//			if (windowsizeStart > 767) {
//				var index = jQuery('#bfisearch' + currModId + ' li[data-searchtypeid="' + currSearchtypetab + '"] a').parent().index();
//				if (index != -1) {
//					jQuery("#bfisearch" + currModId).bfiTabs("option", "active", index);
//				}
//			} else {
//				jQuery("#bfisearch" + currModId).bfiTabs("option", "active", false);
//			}

			try {
				currModule.find("select").chosen("destroy");
			}
			catch (e) {
			}

			if (currShowdirection != "0" && currFixedontop != "0") {
				window.onscroll = function () { bfi_affix(currModId) };
			}

			if (currShowsearchtext != "0") {
				bookingfor.createAutocomplete(currForm);
//				var currAutocomplete = jQuery(currForm).find(".bfi-autocomplete").first();
//				var currScope = currAutocomplete.attr("data-scope");
//				if (typeof currScope === "undefined" || !currScope.length){
//					currScope = "";
//				}
//				
//
//				currAutocomplete.blur(function(){
//					 var keyEvent = jQuery.Event("keydown");
//					 keyEvent.keyCode = jQuery.ui.keyCode.ENTER;
//					 jQuery(this).trigger(keyEvent);
//				 }).autocomplete({
//					autoFocus: true,
//					source: function (request, response) {
//						var previous_request = currAutocomplete.data( "jqXHR" );
//						if( previous_request ) {
//							previous_request.abort();
//						}
////						var urlSearch =  bfi_variables.bfi_urlSearchByText + "?term=" + request.term + "&resultClasses=" + currScope + "&maxresults=5&minMatchingPercentage=90&cultureCode=" + bfi_variables.bfi_cultureCode;
//						var urlSearch =  bfi_variables.bfi_urlSearchByText + "?action=SearchByText&term=" + request.term + "&resultClasses=" + currScope + "&maxresults=5&minMatchingPercentage=90&cultureCode=" + bfi_variables.bfi_cultureCode;
//						currAutocomplete.data( "jqXHR",
//							jQuery.getJSON(urlSearch, function (data) {
//								if (data.length) {
//									jQuery.each(data, function (key, item) {
//										var currentVal = "";
//										if (item.ItemTypeOrder == 0 || item.ItemTypeOrder == 2 || item.ItemTypeOrder == 4 || item.ItemTypeOrder == 6) { currentVal = "zoneIds|" + item.ZoneId; }
//										if (item.ItemTypeOrder == 1) { currentVal = "stateIds|" + item.StateId + ":" + item.XPos + ":" + item.YPos; }
//										if (item.ItemTypeOrder == 3) { currentVal = "regionIds|" + item.RegionId + ":" + item.XPos + ":" + item.YPos; }
//										if (item.ItemTypeOrder == 5) { currentVal = "cityIds|" + item.CityId + ":" + item.XPos + ":" + item.YPos; }
//										if (item.ItemTypeOrder == 7) { currentVal = "poiIds|" + item.PointOfInterestId + ":" + item.XPos + ":" + item.YPos;  }
//										if (item.ItemTypeOrder == 8) { currentVal = "evtCategoryids|" + item.EventCategoryId; }
//										if (item.ItemTypeOrder == 9) { currentVal = "merchantCategoryId|" + item.MerchantCategoryId; }
//										if (item.ItemTypeOrder == 10) { currentVal = "groupCategoryId|" + item.ProductGroupCategoryId; }
//										if (item.ItemTypeOrder == 11) { currentVal = "masterTypeId|" + item.ProductCategoryId; }
//										if (item.ItemTypeOrder == 12) { currentVal = "evtTagIds|" + item.EventTagId; }
//										if (item.ItemTypeOrder == 13) { currentVal = "merchantTagIds|" + item.MerchantTagId; }
//										if (item.ItemTypeOrder == 14) { currentVal = "groupTagIds|" + item.ProductGroupTagId; }
//										if (item.ItemTypeOrder == 15) { currentVal = "resourceTagIds|" + item.ProductTagId; }
//										if (item.ItemTypeOrder == 16) { currentVal = "eventIds|" + item.EventId + ":" + item.XPos + ":" + item.YPos; }
//										if (item.ItemTypeOrder == 17) { currentVal = "merchantIds|" + item.MerchantId; }
//										if (item.ItemTypeOrder == 18) { currentVal = "groupresourceIds|" + item.ProductGroupId; }
//										if (item.ItemTypeOrder == 19) { currentVal = "resourceIds|" + item.ProductId; }
//										item.Value = currentVal;
//									});
//									response(data);
//									currAutocomplete.removeClass("ui-autocomplete-loading");
//								} else {
//									response([{
//										Name: bfi_variables.bfi_txtnoresult
//									}]);
//									currAutocomplete.removeClass("ui-autocomplete-loading");
//								}
//							})
//						);
//					},
//					minLength: 2,
//					delay: 250,
//					select: function (event, ui) {
//						var selectedVal = ui.item.Value;
//						if (typeof selectedVal !== "undefined" && selectedVal.length) {
//							currAutocomplete.closest("form").find("[name=stateIds],[name=regionIds],[name=cityIds],[name=zoneIds],[name=merchantCategoryId],[name=masterTypeId],[name=filters\\[productcategory\\]],[name=groupCategoryId],[name=merchantTagIds],[name=groupTagIds],[name=productTagIds],[name=merchantIds],[name=groupresourceIds],[name=resourceIds],[name=points],[name=filter_order],[name=getBaseFiltersFor]").val("");
//							currAutocomplete.closest("form").find("[name=searchTermValue]").val(selectedVal);
//							switch (selectedVal.split('|')[0]) {
//								case "stateIds":
//								case "regionIds":
//                                    var completeValue = selectedVal.split('|')[1];
//                                    currAutocomplete.closest("form").find("[name=" + selectedVal.split('|')[0] + "]").val(completeValue.split(":")[0]);
//                                    break;
//								case "cityIds":
//								case "poiIds":
//									var completeValue = selectedVal.split('|')[1];
//									currAutocomplete.closest("form").find("[name=points]").val("0|" + completeValue.split(":")[1] + " " + completeValue.split(":")[2]);
//									break;
//								case "merchantIds":
//									currAutocomplete.closest("form").find("[name=filter_order]").val("merchantid:" + selectedVal.split('|')[1]);
//									break;
//								case "groupresourceIds":
//									currAutocomplete.closest("form").find("[name=filter_order]").val("parentid:" + selectedVal.split('|')[1]);
//									break;
//								case "resourceIds":
//									currAutocomplete.closest("form").find("[name=filter_order]").val("productid:" + selectedVal.split('|')[1]);
//									break;
//								case "masterTypeId":
//									currAutocomplete.closest("form").find("[name=filters\\[productcategory\\]]").val(selectedVal.split('|')[1]);
//									currAutocomplete.closest("form").find("[name=getBaseFiltersFor]").val("productcategory");
//									break;
//								default:
//									currAutocomplete.closest("form").find("[name=" + selectedVal.split('|')[0] + "]").val(selectedVal.split('|')[1]);
//									break;
//							}
//							jQuery(this).val(ui.item.Name);
//							event.preventDefault();
//						}
//					},
//					change: function( event, ui ) {
//						if (currAutocomplete.val().length<2) {
//							currAutocomplete.closest("form").find("[name=stateIds],[name=regionIds],[name=cityIds],[name=zoneIds],[name=merchantCategoryId],[name=masterTypeId],[name=filters\\[productcategory\\]],[name=groupCategoryId],[name=merchantTagIds],[name=groupTagIds],[name=productTagIds],[name=merchantIds],[name=groupresourceIds],[name=resourceIds],[name=points],[name=filter_order],[name=getBaseFiltersFor]").val("");
//							}
//					},
//					open: function () {
//						jQuery(this).data("uiAutocomplete").menu.element.addClass("bfi-autocomplete");
//					},
//				});
//
//
//				currAutocomplete.data("ui-autocomplete")._renderItem = function (ul, item) {
//					
//					var currentVal = "";
//					var htmlContent = item.Name;
//					if (item.ItemTypeOrder == 0 || item.ItemTypeOrder == 2 || item.ItemTypeOrder == 4 || item.ItemTypeOrder == 6) { 
//						currentVal = "zoneIds|" + item.ZoneId;
//					}
//					if (item.ItemTypeOrder == 1) { currentVal = "stateIds|" + item.StateId + ":" + item.XPos + ":" + item.YPos; }
//					if (item.ItemTypeOrder == 3) { currentVal = "regionIds|" + item.RegionId + ":" + item.XPos + ":" + item.YPos; }
//					if (item.ItemTypeOrder == 5) { currentVal = "cityIds|" + item.CityId + ":" + item.XPos + ":" + item.YPos; }
//					if (item.ItemTypeOrder == 7) { currentVal = "poiIds|" + item.PointOfInterestId + ":" + item.XPos + ":" + item.YPos;  }
//					if (item.ItemTypeOrder == 8) { currentVal = "evtCategoryids|" + item.EventCategoryId; }
//					if (item.ItemTypeOrder == 9) { currentVal = "merchantCategoryId|" + item.MerchantCategoryId; }
//					if (item.ItemTypeOrder == 10) { currentVal = "groupCategoryId|" + item.ProductGroupCategoryId; }
//					if (item.ItemTypeOrder == 11) { currentVal = "masterTypeId|" + item.ProductCategoryId; }
//					if (item.ItemTypeOrder == 12) { currentVal = "evtTagIds|" + item.EventTagId; }
//					if (item.ItemTypeOrder == 13) { currentVal = "merchantTagIds|" + item.MerchantTagId; }
//					if (item.ItemTypeOrder == 14) { currentVal = "groupTagIds|" + item.ProductGroupTagId; }
//					if (item.ItemTypeOrder == 15) { currentVal = "resourceTagIds|" + item.ProductTagId; }
//					if (item.ItemTypeOrder == 16) { currentVal = "eventIds|" + item.EventId + ":" + item.XPos + ":" + item.YPos; }
//					if (item.ItemTypeOrder == 17) { currentVal = "merchantIds|" + item.MerchantId; }
//					if (item.ItemTypeOrder == 18) { currentVal = "groupresourceIds|" + item.ProductGroupId; }
//					if (item.ItemTypeOrder == 19) { currentVal = "resourceIds|" + item.ProductId; }
//					
//					switch (item.ItemTypeOrder) {
//						case 0:
//						case 1:
//						case 2:
//						case 3:
//						case 4:
//						case 5:
//						case 6:
//							htmlContent = '<i class="fa fa-map-marker"></i>&nbsp;' + item.Name;
//							break;
//						case 7:
//							htmlContent = '<i class="fa fa-street-view"></i>&nbsp;' + item.Name;
//							break;
//						case 8:
//						case 9:
//						case 10:
//						case 11:
//						case 17:
//							htmlContent = '<i class="fa fa-building"></i>&nbsp;' + item.Name;
//							break;
//						case 18:
//						case 19:
//							htmlContent = '<i class="fa fa-bed"></i>&nbsp;' + item.Name;
//							break;
//						case 12:
//						case 13:
//						case 14:
//						case 15:
//							htmlContent = '<i class="fa fa-tag"></i>&nbsp;' + item.Name;
//							break;
//					}
//					
//					if (currentVal.length) {
//						return jQuery("<li>").attr("data-value", currentVal).html(htmlContent).appendTo(ul);
//					} else {
//						return jQuery("<li>").attr("data-value", "").html(htmlContent).addClass("ui-state-disabled").appendTo(ul);
//					}
//										
//				};

			}
		}
	});

	
	
	
	if (jQuery('.bfi-tabs-resource').length>0 )
	{
		jQuery("#navbookingfordetails").bfiTabs();
	};

	jQuery('.bfi-mod-bookingforsearch').each(function () {
		var currModule = jQuery(this);
		var currForm = jQuery(this).find(".bfi-form-default").first();
		currModule.bfiTabs();
		currModule.on('tabsactivate', function (event, ui) {
			bfi_showhideCategories(currModule);
		})


		var currSearchtypetab = jQuery(this).attr("data-searchtypetab");
		var currModId = jQuery(this).attr("data-currmodid");

		var fixedontop = jQuery(this).attr("data-fixedontop");
		var fixedontopcorrection = jQuery(this).attr("data-fixedontopcorrection");

		if (currForm.length) {
			var currResultinsamepg = jQuery(currForm).find("input[name='resultinsamepg']").first();
			var currResultview = jQuery(".bfi-resultview").first();
			var currDefaultaction = currForm.attr("data-defaultaction");
			if (currResultinsamepg.length && currResultinsamepg.val() == "1" && currResultview.length==0) {
				currForm.attr('action', currDefaultaction);
			}

			var currCheckin = jQuery(currForm).find("input[name='checkin']").first();
			var currCheckout = jQuery(currForm).find("input[name='checkout']").first();
			var currShowdaterange = jQuery(currForm).attr("data-showdaterange");
			var currCheckinTime = jQuery(currForm).find("input[name='checkintime']").first();
			var currCheckoutTime = jQuery(currForm).find("input[name='checkouttime']").first();
			var currShowdatetimerange = jQuery(currForm).attr("data-showdatetimerange");
			var currShowdirection = jQuery(currForm).attr("data-showdirection");
			var currFixedontop = jQuery(currForm).attr("data-fixedontop");
			var currShowsearchtext = jQuery(currForm).attr("data-showsearchtext");

			var currSelectMasterTypeId = jQuery(currForm).find("select[name='masterTypeId']").first();
			if (currSelectMasterTypeId.length) {
				currSelectMasterTypeId.on('change', function () { 
					var currFiltersproductcategory = jQuery(currForm).find(".bfi-filtersproductcategory").first();
					if (currFiltersproductcategory.length) {
						currFiltersproductcategory.val(this.value)
					}
				
				})	
			}

			currForm.validate(
				{
//					invalidHandler: function (form, validator) {
//						var errors = validator.numberOfInvalids();
//						if (errors) {
//							validator.errorList[0].element.focus();
//						}
//					},
					invalidHandler: function (form, validator) {
						var errors = validator.numberOfInvalids();
						if (errors) {
//							alert(validator.errorList[0].message);

							validator.errorList[0].element.focus();
						}
					},
					showErrors: function (errorMap, errorList) {

						// Clean up any tooltips for valid elements
						jQuery.each(this.validElements(), function (index, element) {
							var $element = jQuery(element);

							$element.prop("title", "") // Clear the title - there is no error associated anymore
								.removeClass("bfi-error")
							if ($element.is(':data(tooltip)')) {
								$element.bfiTooltip('destroy');
							}
						});
						// Create new tooltips for invalid elements
						jQuery.each(errorList, function (index, error) {
							var $element = jQuery(error.element);
							if ($element.is(':data(tooltip)')) {
								$element.bfiTooltip('destroy');
							}

							$element.prop("title", error.message)
								.addClass("bfi-error")
							if (typeof bfiTooltip  !== "function") {
								jQuery.widget.bridge('bfiTooltip', jQuery.ui.tooltip );
							}
							if (typeof bfiTooltip  !== "function") {
								jQuery.widget.bridge('bfiTooltip', jQuery.ui.tooltip );
							}
							$element.bfiTooltip({
								position: { my: 'center bottom', at: 'center top-10' },
								tooltipClass: 'bfi-tooltip bfi-tooltip-top '
							});
							$element.bfiTooltip("open");

						});
					},
					focusCleanup: true,
					errorClass: "bfi-error",
					highlight: function (label) {
					},
					success: function (label) {
						jQuery(label).remove();
					},
					cache : false,
					submitHandler: function (form) {
						var $form = jQuery(form);
						var $btnresource = $form.find(".bfi-btnsendform").first();
						if ($form.valid()) {
							if ($form.data('submitted') === true) {
								return false;
							} else {
								// Mark it so that the next submit can be ignored
								$form.data('submitted', true);
								if (currShowdaterange == "1" && currCheckin.length && currCheckout.length) {
									var currDateFormat = "dd/mm/yy";
									currCheckin.datepicker("option", "dateFormat", currDateFormat);
									currCheckout.datepicker("option", "dateFormat", currDateFormat);
								}

								var isMacLike = navigator.platform.match(/(Mac|iPhone|iPod|iPad)/i) ? true : false;
								var isIOS = navigator.platform.match(/(iPhone|iPod|iPad)/i) ? true : false;
								if (!isMacLike) {
									var iconBtn = $btnresource.find("i").first();
									iconBtn.removeClass("fa-search").addClass("fa-spinner fa-spin ");
									$btnresource.prop('disabled', true);

								}
								form.submit();
							}

						}
					}

				});

			if (currShowdaterange == "1" && currCheckin.length && currCheckout.length) {
				
				// new calendar
				var currCalendarRange = jQuery(currForm).find(".t-datepicker");

				currCalendarRange.tDatePicker({
					dateCheckIn: currCalendarRange.attr('data-checkin'), //'2020-03-10',
					dateCheckOut: currCalendarRange.attr('data-checkout'), //'2020-03-11',
					numCalendar:12,
					valiDation : true,
					iconArrowTop: false,
					titleCheckIn: 'Check In',
					titleCheckOut: 'Check Out',
					iconDate: '<i class="fa fa-calendar"></i> ',
					titleDateRange: bfi_variables.bfi_txtNights ,
					titleDateRanges: bfi_variables.bfi_txtNights ,
					showDateTheme:'dd-mm-yy',
					toDayShowTitle: false,
					titleCheckOutSelect: bfi_variables.bfi_txtTitleCheckOutSelect,
					titleBtnOk: bfi_variables.bfi_txtTitleBtnOk,
					titleToday:'Today',
					titleDays: JSON.parse(JSON.stringify(bfi_variables.bfi_txtTitleDaysT)) ,
					titleMonths: JSON.parse(JSON.stringify(bfi_variables.bfi_txtTitleMonths)) ,
					autoClose: false,
				}).on('beforeShowDay',function() {
//					jQuery(".ui-tooltip").bfiTooltip("hide");
					jQuery(".ui-tooltip-content").parents('div').remove();
					// console.log('beforeShowDay do something')
				}).on('selectedCI',function(e, slDateCI) {
					  currCalendarRange.find('button.t-table-close').first().attr("disabled",true);
					currCheckin.datepicker('setDate', new Date(slDateCI));

					// console.log('selectedCI do something')
					// console.log(new Date(slDateCI))
				  }).on('selectedCO',function(e, slDateCO) {
					 currCalendarRange.find('button.t-table-close').first().removeAttr("disabled");
					currCheckout .datepicker('setDate', new Date(slDateCO));
					// console.log('selectedCO do something')
					// console.log(new Date(slDateCO))
				  });
				
				/** **/
				currCheckin.datepicker({
					//		defaultDate: "+2d",
					dateFormat: "dd/mm/yy"
					, numberOfMonths: bfi_variables.bfi_numberOfMonths
					, minDate: '+0d'
					, onClose: function (dateText, inst) {
						jQuery(this).attr("disabled", false);
					}
					, beforeShow: function (dateText, inst) {
						var currTmpForm = jQuery(this).closest("form");
						bfidpmode = 'checkin';
						jQuery(this).attr("disabled", true);
						jQuery(inst.dpDiv).addClass('bfi-calendar');
						setTimeout(function () {
							bfi_updateTitle(currTmpForm, "bfi-checkin", "bfi-checkout", "Checkin")
						}, 1);
						var windowsize = jQuery(window).width();
						jQuery(inst.dpDiv)[0].className = jQuery(inst.dpDiv)[0].className.replace(/(^|\s)bfi-calendar-affixtop\S+/g, '');
						if (jQuery(this).closest("div.bfiAffixTop").length || windowsize < 767) {
							jQuery(inst.dpDiv).addClass('bfi-calendar-affixtop' + currModId);
						}
					}
					, onChangeMonthYear: function (dateText, inst) {
						var currTmpForm = jQuery(this).closest("form");
						setTimeout(function () { bfi_updateTitle(currTmpForm, "bfi-checkin", "bfi-checkout", "Checkin"); }, 1);
					}
					//					//, showOn: "button"
					, beforeShowDay: function (date) {
						var currTmpForm = jQuery(this).closest("form");
						return bfi_closed(date, currTmpForm);
					}
					//					, buttonText: "<div class='checkinli'><span class='bfi-weekdayname'> </span> <span class='bfi-year'> </span></div>"
					, onSelect: function (date, inst) {
						var currTmpForm = jQuery(this).closest("form");
						//				bfi_checkDate(jQuery, jQuery(this), date); 
						bfi_printChangedDate(currTmpForm);
						if (currCheckout.is(':visible')){
							setTimeout(function () { currCheckout.datepicker("show"); }, 1);
						}
						jQuery(this).trigger("change");
					}
					, firstDay: 1
				});
				/** **/

				currCheckout.datepicker({
					dateFormat: "dd/mm/yy"
					, numberOfMonths: bfi_variables.bfi_numberOfMonths
					, onClose: function (dateText, inst) {
						jQuery(this).attr("disabled", false);
						bfi_printChangedDate(jQuery(this).closest("form"));
					}
					, beforeShow: function (dateText, inst) {
						var currTmpForm = jQuery(this).closest("form");
						var currTmpCheckin = jQuery(currTmpForm).find("input[name='checkin']").first();
						var date = currTmpCheckin.val();
						bfi_checkDate(jQuery, currTmpCheckin, date);

						bfidpmode = 'checkout';
						jQuery(this).attr("disabled", true);
						jQuery(inst.dpDiv).addClass('bfi-calendar');
						setTimeout(function () { bfi_updateTitle(currTmpForm, "bfi-checkout", "bfi-checkin", "Checkout"); }, 1);
						var windowsize = jQuery(window).width();
						jQuery(inst.dpDiv)[0].className = jQuery(inst.dpDiv)[0].className.replace(/(^|\s)bfi-calendar-affixtop\S+/g, '');
						if (jQuery(this).closest("div.bfiAffixTop").length || windowsize < 767) {
							jQuery(inst.dpDiv).addClass('bfi-calendar-affixtop' + currModId);
						}
						bfi_printChangedDate(currTmpForm);

					}
					, onSelect: function (date, inst) {
						bfi_printChangedDate(jQuery(this).closest("form"));
						jQuery(this).trigger("change");
					}
					, onChangeMonthYear: function (dateText, inst) {
						var currTmpForm = jQuery(this).closest("form");
						setTimeout(function () { bfi_updateTitle(currTmpForm, "bfi-checkout", "bfi-checkin", "Checkout"); }, 1);
					}
					, minDate: '+0d'
					//, showOn: "button"
					, beforeShowDay: function (date) {
						var currTmpForm = jQuery(this).closest("form");
						return bfi_closed(date, currTmpForm);
					}
					//					, buttonText: "<div class='checkoutli'><span class='bfi-weekdayname'> </span> <span class='bfi-year'>aaa </span></div>"
					, firstDay: 1
				});
				/** **/

				bfi_printChangedDate(currForm);

				/** hightligth selected --***/
				currCheckout.datepicker('widget').on('mouseover', 'tr td', function () {
					var currTmpForm = jQuery(this).closest("form");
					var currTmpCheckin = jQuery(currTmpForm).find("input[name='checkin']").first();
					if (bfidpmode == 'checkin' || !currTmpCheckin.datepicker("getDate")) {
						return;
					}//this is hard code for start date
					var calendarId = jQuery(this).closest('.ui-datepicker').attr('id')
					// clear up highlight-day class
					jQuery('#' + calendarId + ' .ui-datepicker-calendar tr td.date-selected').removeClass('date-selected');
					jQuery('#' + calendarId + ' .ui-datepicker-calendar tr td.date-end-selected').removeClass('date-end-selected');
					jQuery('#' + calendarId + ' .ui-datepicker-calendar tr td.highlight-day').each(function (index, item) {
						jQuery(item).removeClass('highlight-day');
					})
					// loop& add highligh-day class until reach $(this)
					var tds = jQuery('#' + calendarId + ' .ui-datepicker-calendar tr td')
					for (var index = 0; index < tds.size(); ++index) {
						var item = tds[index];
						jQuery(item).addClass('highlight-day');
						if (jQuery(item)[0].outerHTML === jQuery(this)[0].outerHTML) {
							break;
						}
					}
				});
			}

			if (currShowdatetimerange == "1" && currCheckinTime.length && currCheckoutTime.length) {
				var currStartdatetimerange = jQuery(currForm).attr("data-startdatetimerange");
				var currEnddatetimerange = jQuery(currForm).attr("data-enddatetimerange");

				currCheckinTime.timepicker({
					'show2400': true,
					'step': 15,
					'useSelect': true,
					'showDuration': false,
					'minTime': currStartdatetimerange,
					'maxTime': currEnddatetimerange,
					'timeFormat': 'H:i'
				});
				currCheckoutTime.timepicker({
					'show2400': true,
					'step': 15,
					'useSelect': true,
					'showDuration': false,
					'minTime': currStartdatetimerange,
					'maxTime': currEnddatetimerange,
					'timeFormat': 'H:i'
				});
				var currShowdaterangeDiv = jQuery(currForm).find(".bfi-row").first();
				currShowdaterangeDiv.datepair({
					startClass: 'bfistart',
					endClass: 'bfiend',
					timeClass: 'bfitime',
					dateClass: 'bfidate',
					//					'defaultTimeDelta':900000,
					parseDate: function (el) {
						//							console.log('parseDate');
						var val = jQuery(el).datepicker('getDate');
						if (!val) {
							return null;
						}
						var utc = new Date(val);
						return utc && new Date(utc.getTime());
					},
					updateDate: function (el, v) {
						//							console.log('updateDate');
						//							console.log(v.getTime());							
						jQuery(el).datepicker('setDate', new Date(v.getTime()));
					},
					setMinTime: function (input, dateObj) {
						///override function
						var currForm = jQuery(input).closest("form");
						var currStartdatetimerange = jQuery(currForm).attr("data-startdatetimerange");
						var currEnddatetimerange = jQuery(currForm).attr("data-enddatetimerange");

						var currCheckin = jQuery(currForm).find("input[name='checkin']").first();
						var currCheckout = jQuery(currForm).find("input[name='checkout']").first();
						var currStartdatetimerange = jQuery(currForm).attr("data-startdatetimerange");

						if (jQuery.datepicker.formatDate("dd M yy", currCheckin.datepicker("getDate")) != jQuery.datepicker.formatDate("dd M yy", currCheckout.datepicker("getDate"))) {
							//							console.log("diversi");
							var currCheckinTime = jQuery(currForm).find("input[name='checkintime']").first();
							jQuery(currCheckinTime).timepicker('option', 'maxTime', currEnddatetimerange);
							jQuery(input).timepicker('option', 'minTime', currStartdatetimerange);
						} else {
							//							console.log("UGUALI");
							jQuery(input).timepicker('option', 'minTime', dateObj);
							var currEndTimeSelect = currForm.find("select[name*='checkouttime']").first();
							currEndTimeSelect.find("option").first().remove();
							var currStartTimeSelect = currForm.find("select[name*='checkintime']").first();
							currStartTimeSelect.find("option").last().remove();

						}
					}
				});
			}

			bfi_showhideCategories(currModule);

			var nch = new Number(jQuery(this).attr("data-nch") || 0);
			var showChildrenagesmsg = new Number(jQuery(this).attr("data-showChildrenagesmsg") || 0);
			bfi_checkChildrenSearch(nch, showChildrenagesmsg, currModId);

			if (typeof bfi_CheckTabsCollapsible !== "undefined") { bfi_CheckTabsCollapsible(currModId); }

			var windowsizeStart = jQuery(window).width();
			if (windowsizeStart > 767) {
				var index = jQuery('#bfisearch' + currModId + ' li[data-searchtypeid="' + currSearchtypetab + '"] a').parent().index();
				if (index != -1) {
					jQuery("#bfisearch" + currModId).bfiTabs("option", "active", index);
				}
			} else {
				jQuery("#bfisearch" + currModId).bfiTabs("option", "active", false);
			}

			try {
				currModule.find("select").chosen("destroy");
			}
			catch (e) {
			}

			if (currShowdirection != "0" && currFixedontop != "0") {
				window.onscroll = function () { bfi_affix(currModId) };
			}

			if (currShowsearchtext != "0") {
				bookingfor.createAutocomplete(currForm);
//				var currAutocomplete = jQuery(currForm).find(".bfi-autocomplete").first();
//				var currScope = currAutocomplete.attr("data-scope");
//				if (typeof currScope === "undefined" || !currScope.length){
//					currScope = "";
//				}
//				
//
//				currAutocomplete.blur(function(){
//					 var keyEvent = jQuery.Event("keydown");
//					 keyEvent.keyCode = jQuery.ui.keyCode.ENTER;
//					 jQuery(this).trigger(keyEvent);
//				 }).autocomplete({
//					autoFocus: true,
//					source: function (request, response) {
//						var previous_request = currAutocomplete.data( "jqXHR" );
//						if( previous_request ) {
//							previous_request.abort();
//						}
//						var urlSearch =  bfi_variables.bfi_urlSearchByText + "?term=" + request.term + "&resultClasses=" + currScope + "&maxresults=5&minMatchingPercentage=90&cultureCode=" + bfi_variables.bfi_cultureCode;
//						currAutocomplete.data( "jqXHR",
////							jQuery.getJSON(bookingfor.getActionUrl(null, null, "SearchByText", "bfi_term=" + request.term + "&bfi_resultclasses=" + currScope+ "&bfi_maxresults=5"), function (data) {
//							jQuery.getJSON(urlSearch, function (data) {
//								if (data.length) {
//									jQuery.each(data, function (key, item) {
//										var currentVal = "";
//										if (item.ItemTypeOrder == 0 || item.ItemTypeOrder == 2 || item.ItemTypeOrder == 4 || item.ItemTypeOrder == 6) { currentVal = "zoneIds|" + item.ZoneId; }
//										if (item.ItemTypeOrder == 1) { currentVal = "stateIds|" + item.StateId + ":" + item.XPos + ":" + item.YPos; }
//										if (item.ItemTypeOrder == 3) { currentVal = "regionIds|" + item.RegionId + ":" + item.XPos + ":" + item.YPos; }
//										if (item.ItemTypeOrder == 5) { currentVal = "cityIds|" + item.CityId + ":" + item.XPos + ":" + item.YPos; }
//										if (item.ItemTypeOrder == 7) { currentVal = "poiIds|" + item.PointOfInterestId + ":" + item.XPos + ":" + item.YPos;  }
//										if (item.ItemTypeOrder == 8) { currentVal = "evtCategoryids|" + item.EventCategoryId; }
//										if (item.ItemTypeOrder == 9) { currentVal = "merchantCategoryId|" + item.MerchantCategoryId; }
//										if (item.ItemTypeOrder == 10) { currentVal = "groupCategoryId|" + item.ProductGroupCategoryId; }
//										if (item.ItemTypeOrder == 11) { currentVal = "masterTypeId|" + item.ProductCategoryId; }
//										if (item.ItemTypeOrder == 12) { currentVal = "evtTagIds|" + item.EventTagId; }
//										if (item.ItemTypeOrder == 13) { currentVal = "merchantTagIds|" + item.MerchantTagId; }
//										if (item.ItemTypeOrder == 14) { currentVal = "groupTagIds|" + item.ProductGroupTagId; }
//										if (item.ItemTypeOrder == 15) { currentVal = "resourceTagIds|" + item.ProductTagId; }
//										if (item.ItemTypeOrder == 16) { currentVal = "eventIds|" + item.EventId + ":" + item.XPos + ":" + item.YPos; }
//										if (item.ItemTypeOrder == 17) { currentVal = "merchantIds|" + item.MerchantId; }
//										if (item.ItemTypeOrder == 18) { currentVal = "groupresourceIds|" + item.ProductGroupId; }
//										if (item.ItemTypeOrder == 19) { currentVal = "resourceIds|" + item.ProductId; }
//										/*
//										if (item.StateId) { currentVal = "stateIds|" + item.StateId; }
//										if (item.RegionId) { currentVal = "regionIds|" + item.RegionId; }
//										if (item.CityId) { currentVal = "cityIds|" + item.CityId; }
//										if (item.ZoneId) { currentVal = "zoneIds|" + item.ZoneId; }
//										if (item.MerchantCategoryId) { currentVal = "merchantCategoryId|" + item.MerchantCategoryId; }
//										if (item.ProductCategoryId) { currentVal = "masterTypeId|" + item.ProductCategoryId; }
//										if (item.MerchantId) { currentVal = "merchantIds|" + item.MerchantId; }
//										if (item.ProductGroupId) { currentVal = "groupresourceIds|" + item.ProductGroupId; }
//										if (item.MerchantTagId) { currentVal = "merchantTagIds|" + item.MerchantTagId; }
//										if (item.ProductTagId) { currentVal = "productTagIds|" + item.ProductTagId; }
//										*/
//										item.Value = currentVal;
//									});
//									response(data);
//									currAutocomplete.removeClass("ui-autocomplete-loading");
//								} else {
//									response([{
//										Name: bfi_variables.bfi_txtnoresult
//									}]);
//									currAutocomplete.removeClass("ui-autocomplete-loading");
//								}
//							})
//						);
//					},
//					/*response: function( event, ui ) {
//						jQuery(this).removeClass("ui-autocomplete-loading");
//					},*/
//					minLength: 2,
//					delay: 250,
//					select: function (event, ui) {
//						var selectedVal = ui.item.Value;
//						//			var selectedVal = jQuery(event.srcElement).attr("data-value");
//						if (typeof selectedVal !== "undefined" && selectedVal.length) {
//							currAutocomplete.closest("form").find("[name=stateIds],[name=regionIds],[name=cityIds],[name=zoneIds],[name=merchantCategoryId],[name=masterTypeId],[name=filters\\[productcategory\\]],[name=groupCategoryId],[name=merchantTagIds],[name=groupTagIds],[name=productTagIds],[name=merchantIds],[name=groupresourceIds],[name=resourceIds],[name=points],[name=filter_order],[name=getBaseFiltersFor]").val("");
//							currAutocomplete.closest("form").find("[name=searchTermValue]").val(selectedVal);
//							switch (selectedVal.split('|')[0]) {
//								case "stateIds":
//								case "regionIds":
//                                    var completeValue = selectedVal.split('|')[1];
//                                    currAutocomplete.closest("form").find("[name=" + selectedVal.split('|')[0] + "]").val(completeValue.split(":")[0]);
//                                    break;
//								case "cityIds":
//								case "poiIds":
//									var completeValue = selectedVal.split('|')[1];
//									currAutocomplete.closest("form").find("[name=points]").val("0|" + completeValue.split(":")[1] + " " + completeValue.split(":")[2]);
//									break;
//								case "merchantIds":
//									currAutocomplete.closest("form").find("[name=filter_order]").val("merchantid:" + selectedVal.split('|')[1]);
//									break;
//								case "groupresourceIds":
//									currAutocomplete.closest("form").find("[name=filter_order]").val("parentid:" + selectedVal.split('|')[1]);
//									break;
//								case "resourceIds":
//									currAutocomplete.closest("form").find("[name=filter_order]").val("productid:" + selectedVal.split('|')[1]);
//									break;
//								case "masterTypeId":
//									//currAutocomplete.closest("form").find("[name=" + selectedVal.split('|')[0] + "]").val(selectedVal.split('|')[1]);
//									currAutocomplete.closest("form").find("[name=filters\\[productcategory\\]]").val(selectedVal.split('|')[1]);
//									currAutocomplete.closest("form").find("[name=getBaseFiltersFor]").val("productcategory");
//									break;
//								default:
//									currAutocomplete.closest("form").find("[name=" + selectedVal.split('|')[0] + "]").val(selectedVal.split('|')[1]);
//									break;
//							}
//							jQuery(this).val(ui.item.Name);
//							event.preventDefault();
//						}
//						//log( "Selected: " + ui.item.value + " aka " + ui.item.id );
//					},
//					change: function( event, ui ) {
//						if (currAutocomplete.val().length<2) {
//							currAutocomplete.closest("form").find("[name=stateIds],[name=regionIds],[name=cityIds],[name=zoneIds],[name=merchantCategoryId],[name=masterTypeId],[name=filters\\[productcategory\\]],[name=groupCategoryId],[name=merchantTagIds],[name=groupTagIds],[name=productTagIds],[name=merchantIds],[name=groupresourceIds],[name=resourceIds],[name=points],[name=filter_order],[name=getBaseFiltersFor]").val("");
//						}
//					},
//					open: function () {
//						jQuery(this).data("uiAutocomplete").menu.element.addClass("bfi-autocomplete");
//					},
//				});
//
//
//				currAutocomplete.data("ui-autocomplete")._renderItem = function (ul, item) {
//					
//					var currentVal = "";
//					var htmlContent = item.Name;
//					if (item.ItemTypeOrder == 0 || item.ItemTypeOrder == 2 || item.ItemTypeOrder == 4 || item.ItemTypeOrder == 6) { 
//						currentVal = "zoneIds|" + item.ZoneId;
//					}
//					if (item.ItemTypeOrder == 1) { currentVal = "stateIds|" + item.StateId + ":" + item.XPos + ":" + item.YPos; }
//					if (item.ItemTypeOrder == 3) { currentVal = "regionIds|" + item.RegionId + ":" + item.XPos + ":" + item.YPos; }
//					if (item.ItemTypeOrder == 5) { currentVal = "cityIds|" + item.CityId + ":" + item.XPos + ":" + item.YPos; }
//					if (item.ItemTypeOrder == 7) { currentVal = "poiIds|" + item.PointOfInterestId + ":" + item.XPos + ":" + item.YPos;  }
//					if (item.ItemTypeOrder == 8) { currentVal = "evtCategoryids|" + item.EventCategoryId; }
//					if (item.ItemTypeOrder == 9) { currentVal = "merchantCategoryId|" + item.MerchantCategoryId; }
//					if (item.ItemTypeOrder == 10) { currentVal = "groupCategoryId|" + item.ProductGroupCategoryId; }
//					if (item.ItemTypeOrder == 11) { currentVal = "masterTypeId|" + item.ProductCategoryId; }
//					if (item.ItemTypeOrder == 12) { currentVal = "evtTagIds|" + item.EventTagId; }
//					if (item.ItemTypeOrder == 13) { currentVal = "merchantTagIds|" + item.MerchantTagId; }
//					if (item.ItemTypeOrder == 14) { currentVal = "groupTagIds|" + item.ProductGroupTagId; }
//					if (item.ItemTypeOrder == 15) { currentVal = "resourceTagIds|" + item.ProductTagId; }
//					if (item.ItemTypeOrder == 16) { currentVal = "eventIds|" + item.EventId + ":" + item.XPos + ":" + item.YPos; }
//					if (item.ItemTypeOrder == 17) { currentVal = "merchantIds|" + item.MerchantId; }
//					if (item.ItemTypeOrder == 18) { currentVal = "groupresourceIds|" + item.ProductGroupId; }
//					if (item.ItemTypeOrder == 19) { currentVal = "resourceIds|" + item.ProductId; }
//					
//					switch (item.ItemTypeOrder) {
//						case 0:
//						case 1:
//						case 2:
//						case 3:
//						case 4:
//						case 5:
//						case 6:
//							htmlContent = '<i class="fa fa-map-marker"></i>&nbsp;' + item.Name;
//							break;
//						case 7:
//							htmlContent = '<i class="fa fa-street-view"></i>&nbsp;' + item.Name;
//							break;
//						case 8:
//						case 9:
//						case 10:
//						case 11:
//						case 17:
//							htmlContent = '<i class="fa fa-building"></i>&nbsp;' + item.Name;
//							break;
//						case 18:
//						case 19:
//							htmlContent = '<i class="fa fa-bed"></i>&nbsp;' + item.Name;
//							break;
//						case 12:
//						case 13:
//						case 14:
//						case 15:
//							htmlContent = '<i class="fa fa-tag"></i>&nbsp;' + item.Name;
//							break;
//					}
//					
//					if (currentVal.length) {
//						return jQuery("<li>").attr("data-value", currentVal).html(htmlContent).appendTo(ul);
//					} else {
//						return jQuery("<li>").attr("data-value", "").html(htmlContent).addClass("ui-state-disabled").appendTo(ul);
//					}
//										
//										
//					/*
//					var currentVal = "";
//					if (item.StateId) { currentVal = "stateIds|" + item.StateId; }
//					if (item.RegionId) { currentVal = "regionIds|" + item.RegionId; }
//					if (item.CityId) { currentVal = "cityIds|" + item.CityId; }
//					if (item.ZoneId) { currentVal = "locationzone|" + item.ZoneId; }
//					if (item.MerchantCategoryId) { currentVal = "merchantCategoryId|" + item.MerchantCategoryId; }
//					if (item.ProductCategoryId) { currentVal = "masterTypeId|" + item.ProductCategoryId; }
//					if (item.MerchantId) { currentVal = "merchantIds|" + item.MerchantId; }
//					if (item.ProductGroupId) { currentVal = "groupresourceIds|" + item.ProductGroupId; }
//					if (item.MerchantTagId) { currentVal = "merchantTagIds|" + item.MerchantTagId; }
//					if (item.ProductTagId) { currentVal = "productTagIds|" + item.ProductTagId; }
//
//					var text = item.Name;
//					if (item.StateId || item.RegionId || item.CityId || item.ZoneId) { text = '<i class="fa fa-map-marker"></i>&nbsp;' + text; }
//					if (item.MerchantCategoryId || item.ProductCategoryId || item.MerchantId) { text = '<i class="fa fa-building"></i>&nbsp;' + text; }
//					if (item.MerchantTagId || item.ProductTagId) { text = '<i class="fa fa-tag"></i>&nbsp;' + text; }
//					if (currentVal.length) {
//						return jQuery("<li>").attr("data-value", currentVal).html(text).appendTo(ul);
//					} else {
//						return jQuery("<li>").attr("data-value", "").html(text).addClass("ui-state-disabled").appendTo(ul);
//					}
//					*/
//				};

			}
		}

		//			var currFormOnSell = jQuery(this).find(".bfi-form-onsell").first();
		//-------------on sell	
		var currFormOnSell = jQuery(this).find(".bfi-form-onsell").first();
		if (currFormOnSell.length) {
			var currShowSearchTextOnSell = jQuery(currFormOnSell).attr("data-showsearchtextonsell");
			currFormOnSell.validate(
				{
					invalidHandler: function (form, validator) {
						var errors = validator.numberOfInvalids();
						if (errors) {
							alert(validator.errorList[0].message);

							validator.errorList[0].element.focus();
						}
					},
					showErrors: function (errorMap, errorList) {

						// Clean up any tooltips for valid elements
						jQuery.each(this.validElements(), function (index, element) {
							var $element = jQuery(element);

							$element.prop("title", "") // Clear the title - there is no error associated anymore
								.removeClass("bfi-error")
							if ($element.is(':data(tooltip)')) {
								$element.bfiTooltip('destroy');
							}
						});
						// Create new tooltips for invalid elements
						jQuery.each(errorList, function (index, error) {
							var $element = jQuery(error.element);
							if ($element.is(':data(tooltip)')) {
								$element.bfiTooltip('destroy');
							}

							$element.prop("title", error.message)
								.addClass("bfi-error")
							if (typeof bfiTooltip  !== "function") {
								jQuery.widget.bridge('bfiTooltip', jQuery.ui.tooltip );
							}
							if (typeof bfiTooltip  !== "function") {
								jQuery.widget.bridge('bfiTooltip', jQuery.ui.tooltip );
							}
							$element.bfiTooltip({
								position: { my: 'center bottom', at: 'center top-10' },
								tooltipClass: 'bfi-tooltip bfi-tooltip-top '
							});
							$element.bfiTooltip("open");

						});
					},
					errorClass: "bfi-error",
					highlight: function (label) {
					},
					success: function (label) {
						jQuery(label).remove();
					},
					cache : false,
					submitHandler: function (form) {
						var $form = jQuery(form);
						var $btnresource = $form.find(".bfi-btnsendform").first();
						if ($form.valid()) {
							if ($form.data('submitted') === true) {
								return false;
							} else {
								// Mark it so that the next submit can be ignored
								$form.data('submitted', true);
								var isMacLike = navigator.platform.match(/(Mac|iPhone|iPod|iPad)/i) ? true : false;
								var isIOS = navigator.platform.match(/(iPhone|iPod|iPad)/i) ? true : false;
								if (!isMacLike) {
									var iconBtn = $btnresource.find("i").first();
									iconBtn.removeClass("fa-search").addClass("fa-spinner fa-spin ");
									$btnresource.prop('disabled', true);

								}
								form.submit();
							}
						}

					}
				});

			if (currShowSearchTextOnSell != "0") {

				var currAutocomplete = jQuery(currFormOnSell).find(".bfi-autocomplete").first();
				var currScope = currAutocomplete.attr("data-scope");
				if (typeof currScope === "undefined" || !currScope.length){
					currScope = "";
				}

				currAutocomplete.blur(function(){
         var keyEvent = jQuery.Event("keydown");
         keyEvent.keyCode = jQuery.ui.keyCode.ENTER;
         jQuery(this).trigger(keyEvent);
     }).autocomplete({
					autoFocus: true,
					source: function (request, response) {
						var urlSearch =  bfi_variables.bfi_urlSearchByText + "?term=" + request.term + "&resultClasses=" + currScope + "&maxresults=5&onlyLocations=1&cultureCode=" + bfi_variables.bfi_cultureCode;
//						jQuery.getJSON(bookingfor.getActionUrl(null, null, "SearchByText", "bfi_term=" + request.term + "&bfi_resultclasses=" + currScope+ "&bfi_maxresults=5&bfi_onlyLocations=1"), function (data) {
						jQuery.getJSON(urlSearch, function (data) {
							if (data.length) {
								jQuery.each(data, function (key, item) {
									var currentVal = "";
									if (item.StateId) { currentVal = "stateIds|" + item.StateId; }
									if (item.RegionId) { currentVal = "regionIds|" + item.RegionId; }
									if (item.CityId) { currentVal = "cityIds|" + item.CityId; }
									if (item.ZoneId) { currentVal = "locationzone|" + item.ZoneId; }
									item.Value = currentVal;
								});
								response(data);
							} else {
								response([{
									Name: bfi_variables.bfi_txtnoresult
								}]);
							}
						});
					},
					minLength: 2,
					delay: 200,
					select: function (event, ui) {
						var selectedVal = ui.item.Value;
						if (selectedVal.length) {
							currAutocomplete.closest("form").find("[name=stateIds],[name=regionIds],[name=cityIds],[name=locationzone]").val("");
							currAutocomplete.closest("form").find("[name=searchTermValue]").val(selectedVal);
							currAutocomplete.closest("form").find("[name=" + selectedVal.split('|')[0] + "]").val(selectedVal.split('|')[1]);
							jQuery(this).val(ui.item.Name);
							event.preventDefault();
						}
					},
					open: function () {
						jQuery(this).data("uiAutocomplete").menu.element.addClass("bfi-autocomplete");
					}
				});
				currAutocomplete.data("ui-autocomplete")._renderItem = function (ul, item) {
					var currentVal = "";
					if (item.StateId) { currentVal = "stateIds|" + item.StateId; }
					if (item.RegionId) { currentVal = "regionIds|" + item.RegionId; }
					if (item.CityId) { currentVal = "cityIds|" + item.CityId; }
					if (item.ZoneId) { currentVal = "locationzone|" + item.ZoneId; }

					var text = item.Name;
					if (item.StateId || item.RegionId || item.CityId || item.ZoneId) { text = '<i class="fa fa-map-marker"></i>&nbsp;' + text; }
					if (currentVal.length) {
						return jQuery("<li>").attr("data-value", currentVal).html(text).appendTo(ul);
					} else {
						return jQuery("<li>").attr("data-value", "").html(text).addClass("ui-state-disabled").appendTo(ul);
					}
				};

			}
		}

	});

});


function bfi_lostpass(currObj) {
	var currContainer = jQuery(currObj).closest(".bfi-mod-bookingforlogin-content").first();
	var currForm = currContainer.find(".bfi-login-form").first();
	var currFormLostpass = currContainer.find(".bfi-lostpass-form").first();
	if (currForm.length) {
		currForm.hide();
	}
	if (currFormLostpass.length) {
		currFormLostpass.show();
	}
}
function bfi_lostpassback(currObj) {
	var currContainer = jQuery(currObj).closest(".bfi-mod-bookingforlogin-content").first();
	var currForm = currContainer.find(".bfi-login-form").first();
	var currFormLostpass = currContainer.find(".bfi-lostpass-form").first();
	if (currForm.length) {
		currForm.show();
	}
	if (currFormLostpass.length) {
		currFormLostpass.hide();
	}
}


jQuery(window).on("resize orientationchange", function () {
	jQuery('.bfi-form-default ').each(function () {
		var currForm = jQuery(this);
		var currModId = jQuery(this).attr("data-currmodid");
		var currShowdaterange = jQuery(this).attr("data-showdaterange");
		jQuery("#bfi_lblchildrenages" + currModId).webuiPopover("hide");
		if (typeof bfi_CheckTabsCollapsible !== "undefined") { bfi_CheckTabsCollapsible(currModId); }
		if (currShowdaterange != "0") {
			var currCheckin = jQuery(currForm).find("input[name='checkin']").first();
			var currCheckout = jQuery(currForm).find("input[name='checkout']").first();
			currCheckin.datepicker("hide");
			currCheckout.datepicker("hide");

			if (typeof bfi_printChangedDate !== "undefined") { bfi_printChangedDate(currForm); }
		}
	});
});
jQuery(document).on('change', "input[name='childrensel']", function (e) {
	var currForm = jQuery(this).closest("form");
	var currModID = jQuery(currForm).attr("data-currmodid");
	bfi_checkChildrenSearch(Query(this).val(), 0, currModID);
});

jQuery(document).on('click tap', ".bfi-btnsendform", function (e) {
	e.preventDefault();
	var currForm = jQuery(this).closest("form");
	currForm.submit();
});

jQuery(document).on('click tap', ".bfi-btnlogout", function (e) {
	e.preventDefault();
	var currContainer = jQuery(this);
	var bookingforlogin = currContainer.find(".bfi-mod-bookingforlogin-content").first();
	var queryMG = "task=bfilogout";
	bookingfor.waitSimpleWhiteBlock(bookingforlogin);
	jQuery.post(bfi_variables.bfi_urlCheck, queryMG, function (data) {
		if (data == "-1") {
			bookingforlogin.hide();
			if (typeof tmpDialogOpen !== 'undefined' && tmpDialogOpen.hasClass("ui-dialog-content")) {
				tmpDialogOpen.dialog("close").dialog('destroy');
			}
			location.reload();
		};
		bookingforlogin.unblock();
	}, 'json');
});

var tmpDialogOpen;
jQuery(document).on('click tap', ".bfi-mod-bookingforlogin-popup", function (e) {
	var currContainer = jQuery(this);
	var bfi_wuiP_width = 450;
	if (jQuery(window).width() < bfi_wuiP_width) {
		bfi_wuiP_width = jQuery(window).width() * 0.8;
	}
	if (typeof tmpDialogOpen !== 'undefined' && tmpDialogOpen.hasClass("ui-dialog-content")) {
		tmpDialogOpen.dialog("close").dialog('destroy');
	}

	var bookingforlogin = currContainer.find(".bfi-mod-bookingforlogin-content").first();
	var bookingforlogged = currContainer.find(".bfi-mod-bookingforlogin-menu-content").first();
	var bookingforlogintitle = currContainer.find(".bfi-mod-bookingforlogin-title").first();

	if (bookingforlogin.length) {
		tmpDialogOpen = bookingforlogin.dialog({
			closeText: "",
			title: bookingforlogintitle.html(),
			height: 'auto',
			width: bfi_wuiP_width,
			resizable: true,
			modal: true,
			dialogClass: 'bfi-dialog bfi-login',
			clickOutside: true,
			clickOutsideTrigger: ".bfi-mod-bookingforlogin-popup",
		});
	}else if (bookingforlogged.length) {
			currContainer.webuiPopover({
				title :  '',
				content : bookingforlogged.html(),
				container: "body",
				placement:"bottom",
				style:'bfi-webuipopover'
			}); 
			currContainer.webuiPopover("show");
	}
});


jQuery(document).on('click tap', ".checkboxservices", function (e) {
	var currForm = jQuery(this).closest("form");
	var currHdInp = currForm.find("input[name='servicesonsell']").first();
	var currChecked = currForm.find(".checkboxservices:checked");
	bfi_updateHiddenValue(currChecked, currHdInp);
});
var tmpDialogPersonOpen
jQuery(document).on('click tap', ".bfi-showperson-text", function (e) {
	var currForm = jQuery(this).closest("form");
	var currShowperson = currForm.find(".bfi-showperson").first();

	if (!!jQuery.uniform) {
		currForm.find(".bfi-showperson select").each(function () {
			jQuery.uniform.restore(jQuery(this));
		});
	}

	if (typeof tmpDialogPersonOpen !== 'undefined' && tmpDialogPersonOpen.hasClass("ui-dialog-content")) {
		tmpDialogPersonOpen.dialog('open');
//		tmpDialogPersonOpen.dialog("close").dialog('destroy');
	}else{
		var title = bfi_variables.bfi_txtGuest;
		var currTitleShowperson = currForm.find(".bfi-title-showprerson").first();
		if (currTitleShowperson.length >0)
		{
			title = currTitleShowperson.text();
		}
		tmpDialogPersonOpen = jQuery(currShowperson).dialog({
			title: title,
			height: 'auto',
			width: bookingfor.mobileViewMode? jQuery(window).width() * 0.9 : 'auto',
			resizable: true,
			position:{
				my: "center top",
				at: "center bottom",
				of: jQuery(this)
			},
			dialogClass: 'bfi-dialog bfi-guest',
			clickOutside: true,
			clickOutsideTrigger: ".bfi-showperson-text",
		});
	}


});
function bfi_checkDate($, obj, selectedDate) {
	var currForm = jQuery(obj).closest(".bfi-dateform-container");
	instance = obj.data("datepicker");
	date = $.datepicker.parseDate(
		instance.settings.dateFormat ||
		$.datepicker._defaults.dateFormat,
		selectedDate, instance.settings);
	var d = new Date(date);
	d.setDate(d.getDate());
	var currCheckout = jQuery(currForm).find("input[name='checkout']").first();
    if (currCheckout.datepicker("getDate") <= d) {
        currCheckout.datepicker("setDate", d);
    }
	currCheckout.datepicker("option", "minDate", d);
}
function bfi_closed(date, currForm) {
	var currCheckin = jQuery(currForm).find("input[name='checkin']").first();
	var currCheckout = jQuery(currForm).find("input[name='checkout']").first();
	var strDate = ("0" + date.getDate()).slice(-2) + "/" + ("0" + (date.getMonth() + 1)).slice(-2) + "/" + date.getFullYear();
	var c = strDate.split("/");
	var from = currCheckin.datepicker("getDate");
	var to = currCheckout.datepicker("getDate");
	var dayEnabled = true
	var holydayTitle = "";
	var holydayCss = "";

	if (from != null && to  != null)
	{
		var check = new Date(c[2], c[1] - 1, c[0]);
		var daysToDisable = jQuery(currForm).attr("data-blockdays").split(",").map(Number);
		var monthsToDisable = jQuery(currForm).attr("data-blockmonths").split(",").map(Number);
		var day = date.getDay();
		if (jQuery.inArray(day, daysToDisable) != -1) {
			dayEnabled = false;
		}
		var month = date.getMonth() + 1;
		if (jQuery.inArray(month, monthsToDisable) != -1) {
			dayEnabled = false;
		}

		var currDay = ("0" + date.getDate()).slice(-2) + "" + ("0" + (date.getMonth() + 1)).slice(-2);
		var currIdxHoliday = jQuery.inArray(currDay, bookingfor.holydays);

		if (currIdxHoliday != -1) {
			holydayTitle = bookingfor.holydaysTitle[currIdxHoliday];
			holydayCss = "bfi-date-holidays ";
		}
		currDay = ("0" + date.getDate()).slice(-2) + "" + ("0" + (date.getMonth() + 1)).slice(-2) + date.getFullYear();
		currIdxHoliday = jQuery.inArray(currDay, bookingfor.holydays);

		if (currIdxHoliday != -1) {
			holydayTitle = bookingfor.holydaysTitle[currIdxHoliday];
			holydayCss = "bfi-date-holidays ";
		}

		arr = [dayEnabled, holydayCss, holydayTitle];
		if (check.getTime() == from.getTime()) {
			arr = [dayEnabled, holydayCss + ' date-start-selected', holydayTitle];
		}
		if (check.getTime() == to.getTime()) {
			arr = [dayEnabled, holydayCss + ' date-end-selected', holydayTitle];
		}
		if (check > from && check < to) {
			arr = [dayEnabled, holydayCss + ' date-selected', holydayTitle];
		}
	}else{
		arr = [dayEnabled, holydayCss, holydayTitle];
	}
	return arr;
}

function bfi_updateTitle(currForm, classToAdd, classToRemove, title) {
	setTimeout(function () {
		bfiCalendarCheck();
		jQuery("#ui-datepicker-div").addClass(classToAdd);
		jQuery("#ui-datepicker-div").removeClass(classToRemove);

		jQuery("#ui-datepicker-div").prepend("<div class=\"bfi-title-arrow\"></div>");

		var resbynight = jQuery(currForm).find(".resbynighthd").first();
		var currCheckin = jQuery(currForm).find(".bfi-checkin-field").first();
		var currCheckout = jQuery(currForm).find(".bfi-checkout-field").first();
		var from = currCheckin.datepicker("getDate");
		var to = currCheckout.datepicker("getDate");
		var diff = new Date(to - from);
		var days = Math.ceil(diff / 1000 / 60 / 60 / 24);
		var strSummary = 'Check-in ' + (jQuery.datepicker.formatDate("dd M", from));
		var strSummaryDays = " (" + days + " " + bfi_variables.bfi_txtNights + ")";
		if (jQuery(resbynight).val() == 0) {
			days += 1;
			strSummaryDays = " (" + days + " " + bfi_variables.bfi_txtDays + ")";
		}
		if (days < 1) { strSummaryDays = ""; }

		strSummary += ' Check-out ' + jQuery.datepicker.formatDate("dd M yy", to) + ' ' + strSummaryDays;
		jQuery('#ui-datepicker-div').attr('data-before', strSummary);


	}, 1);
}

function bfi_printChangedDate(currForm) {
	var currCheckin = jQuery(currForm).find("input[name='checkin']").first();
	var currCheckout = jQuery(currForm).find("input[name='checkout']").first();
	var currDateFormat = "D, dd M yy";
	var currDateFormatChild = "dd M yy";
	var windowsize = jQuery(window).width();
	if (windowsize > 769 && windowsize < 1300) {
		currDateFormat = " dd mm yy";
		currDateFormatChild = " dd mm yy";
	}
	currCheckin.datepicker("option", "dateFormat", currDateFormat);
	currCheckout.datepicker("option", "dateFormat", currDateFormat);

	var btnTextChildrenagesat = bfi_variables.bfi_txtThe + " " + jQuery.datepicker.formatDate(currDateFormatChild, currCheckin.datepicker("getDate"));
	var currLabelChildrenagesat = jQuery(currForm).find(".bfi_lblchildrenagesat").first().html(btnTextChildrenagesat);
}



function initDatepickerPeriod() {
	//console.log("inizializzazione TimePeriod");
	jQuery(".ChkAvailibilityPeriod.bfi-checkin-field").datepicker({
		numberOfMonths: 1,
		defaultDate: "+0d",
		dateFormat: "dd/mm/yy",
		//        maxDate: strEndDate,
		onSelect: function (date) {
			//console.log("ChkAvailibilityPeriod onSelect")
			dateTimeChanged(jQuery(this));
		},
		//        showOn: "button",
		beforeShow: function (dateText, inst) {
			//console.log("ChkAvailibilityPeriodbeforeShow")
			bficalculatordpmode = 'checkin';
			//			jQuery(this).attr("disabled", true);
			//			jQuery(this).attr("readonly", true); 
			jQuery(inst.dpDiv).addClass('bfi-calendar');
			setTimeout(function () {
				bfiCalendarCheck();
				jQuery("#ui-datepicker-div").addClass("bfi-checkin");
				jQuery("#ui-datepicker-div").removeClass("bfi-checkout");
				jQuery("#ui-datepicker-div div.bfi-title-arrow").remove();
				jQuery("#ui-datepicker-div").prepend("<div class=\"bfi-title-arrow\">" + "Checkin" + "</div>");
			}, 1);
		},
		beforeShowDay: function (date) {
			var currResourceid = jQuery("#bfi-period-select").attr("data-resid");
			var currTmpForm = jQuery(this).closest(".bfi-period-change");
			//var currDays = [];
			//jQuery.each(daysToEnable[jQuery(this).attr("data-resid")], function (key, value) {
			//    currDays.push(Number(key));
			//});
			//			console.log(currDays);
			return bfi_closedBooking(date, 1, daysToEnable[jQuery(this).attr("data-resid")], currTmpForm, currResourceid);
			//			return enableSpecificDatesTimePeriod(date, 1, daysToEnable[jQuery(this).attr("data-resid")]);
		},
		//        buttonText: strbuttonTextTimePeriod,
		firstDay: 1,
	});

	jQuery(".ChkAvailibilityPeriod.bfi-checkout-field").datepicker({
		dateFormat: "dd/mm/yy",
		numberOfMonths: 1,
		onClose: function (dateText, inst) {
			//			jQuery(this).attr("disabled", false); 
			//console.log("checkout  onClose")
			//			bfi_printChangedDateTimePeriod(); 
		},
		beforeShow: function (dateText, inst) {
			//console.log("checkout  beforeShow")
			var currTmpForm = jQuery(this).closest(".bfi-period-change");
			var currTmpCheckin = jQuery(currTmpForm).find(".bfi-checkin-field").first();
			var date = currTmpCheckin.val();
			bfi_checkDate(jQuery, currTmpCheckin, date);

			bfidpmode = 'checkout';
			//			jQuery(this).attr("disabled", true); 
			jQuery(inst.dpDiv).addClass('bfi-calendar');
			//setTimeout(function () { bfi_updateTitle(currTmpForm, "bfi-checkout", "bfi-checkin", "Checkout"); }, 1);
			var windowsize = jQuery(window).width();
			jQuery(inst.dpDiv)[0].className = jQuery(inst.dpDiv)[0].className.replace(/(^|\s)bfi-calendar-affixtop\S+/g, '');
			if (jQuery(this).closest("div.bfiAffixTop").length || windowsize < 767) {
				jQuery(inst.dpDiv).addClass('bfi-calendar-affixtop' + currModId);
			}
			//			bfi_printChangedDateTimePeriod(); 

		},
		onSelect: function (date, inst) {
			//console.log("checkout  onSelect");
			//bfi_updateCheckOutDaysTimesHours();
			bfi_printChangedDateTimePeriod();
			jQuery(this).trigger("change");
		},
		onChangeMonthYear: function (dateText, inst) {
			var currTmpForm = jQuery(this).closest(".bfi-period-change");
			setTimeout(function () { bfi_updateTitle(currTmpForm, "bfi-checkout", "bfi-checkin", "Checkout"); }, 1);
		},
		minDate: '+0d',
		beforeShowDay: function (date) {
			//console.log("checkout beforeShowDay")
			//console.log(availabilityCheckoutTimePeriod);
			var currResourceid = jQuery("#bfi-period-select").attr("data-resid");
			var currTmpForm = jQuery(this).closest(".bfi-period-change");
			return bfi_closedBooking(date, 1, checkOutDaysToEnable[currResourceid], currTmpForm, currResourceid);
		},
		firstDay: 1
	});

	//var evntSelect = "change";
	jQuery(document).on('change', ".selectpickerTimePeriodStart", function (e) {
		//console.log("selectpickerTimePeriodStart  change")
		bfi_consolelog("selectpickerTimePeriodStart  change");
		var currTr = jQuery("#bfimodaltimeperiod");
		var currCheckin = currTr.find(".bfi-checkin-field").first();
		var currCheckout = currTr.find(".bfi-checkout-field").first();
		var curSelStart = currTr.find('.selectpickerTimePeriodStart').first();
		var curSelEnd = currTr.find('.selectpickerTimePeriodEnd').first();
		var currResourceid = jQuery("#bfi-timeperiod-select").attr("data-resid");
		bfi_getAjaxDateHourCheckout(currResourceid, currCheckin, currCheckout, productAvailabilityType, curSelStart);
	});
}


function dateTimeChanged(currForm, callback) {
	var currTr = jQuery("#bfimodalperiod");
	var checkoutContainer = jQuery(currTr).find(".bfi-checkout-field-container").first();
	checkoutContainer.block({ message: '' });
	var currCheckin = currTr.find(".bfi-checkin-field").first();
	var currCheckout = currTr.find(".bfi-checkout-field").first();
	var currProdId = jQuery("#bfi-period-select").attr("data-resid");
	var currDate = currCheckin.datepicker('getDate');

	bookingfor.waitSimpleWhiteBlock(currTr);

	jQuery(currCheckout).block({ message: '' });
	var diffDays = 0;
	var datereformat = jQuery.datepicker.formatDate("yymmdd", jQuery(currCheckin).datepicker("getDate"));
	//jQuery(currForm).block({ message: '' });
	var options = {
		url: bookingfor.getActionUrl("Resource", "GetCheckOutDatesDetailed", null, "resourceId=" + currProdId + "&fromDate=" + datereformat),
		dataType: 'json',
		cache: false,
		success: function (data) {
			checkOutDaysToEnable[currProdId + ""] = [];
			availabilityValues[currProdId + ""] = {};
			jQuery.each(data, function (j, av) {
				checkOutDaysToEnable[currProdId + ""].push(parseInt(av.PeriodDate));
				availabilityValues[currProdId + ""][av.PeriodDate] = av.Availability;
			});
			//checkOutDaysToEnable[currProdId + ""] = data;
			if (callback) {
				callback();
			}
			currTr.unblock();
			bfi_onEnsureCheckOutDaysToEnableSuccess(checkOutDaysToEnable[currProdId + ""], currProdId, currCheckout, checkoutContainer);
		}
	};
	jQuery.ajax(options);
}


function bfi_selectperiod(currEl) {
	var currFromDate = jQuery("#bfimodalperiod").find(".ChkAvailibilityPeriod.bfi-checkin-field").first();
	var currToDate = jQuery("#bfimodalperiod").find(".ChkAvailibilityPeriod.bfi-checkout-field").first();
	var currTr = jQuery(bfi_currTRselected).find(".bfi-period");
	var currCheckin = currTr.find(".bfi-period-checkin").first();
	var currCheckout = currTr.find(".bfi-period-checkout").first();
	var currduration = currTr.find(".bfi-total-duration").first();
	//var currTimeSlotSelect = currContainer.find("#selectpickerTimeSlotRange option:selected");
	//var currSelect = currTimeSlotSelect.text().split(" - ");
	//var currTimeSlotId =currTimeSlotSelect.val();

	var resourceId = currEl.attr("data-resid");
	var sourceId = currEl.attr("data-sourceid");


	//var mcurrFromDate = jQuery(currFromDate).datepicker("getDate");
	//var fromDate = jQuery.datepicker.formatDate("yy-mm-dd", jQuery(currFromDate).datepicker( "getDate" ));
	var duration = (jQuery(currToDate).datepicker("getDate") - jQuery(currFromDate).datepicker("getDate")) / 86400000;
	if (currTr.attr("data-availabilitytype") == "0") {
		duration++;
	}
	var durationStr = currduration.attr("data-dayname").replace("%d", duration);

	currTr.attr("data-checkin", jQuery.datepicker.formatDate("yymmdd", jQuery(currFromDate).datepicker("getDate")));
	currTr.attr("data-checkout", jQuery.datepicker.formatDate("yymmdd", jQuery(currToDate).datepicker("getDate")));
	currCheckin.html(jQuery.datepicker.formatDate("D d M yy", jQuery(currFromDate).datepicker("getDate")));
	currCheckout.html(jQuery.datepicker.formatDate("D d M yy", jQuery(currToDate).datepicker("getDate")));
	currduration.html(durationStr);
	jQuery("#bfimodalperiod").dialog("close");

	jQuery.unblockUI();
	updateTotalSelectable(jQuery(currEl));
}


function updateTotalSelectable(currEl) {
	var currTr = currEl.closest("div.bfi-period-change");
	var resid = currEl.data("resid");
	//var rateplanid = currEl.data("rateplanid");

	var currSel = jQuery(bfi_currTRselected).find(".ddlrooms").first();

	var currentSelection = currSel.val();
	//debugger;
	var startDate = jQuery.datepicker.formatDate("yymmdd", currTr.find(".bfi-checkin-field").datepicker("getDate"));
	var endDate = jQuery.datepicker.formatDate("yymmdd", currTr.find(".bfi-checkout-field").datepicker("getDate"));

	var maxSelectable = parseInt(jQuery(currSel).attr("data-maxvalue"));
	jQuery.each(jQuery.grep(Object.keys(availabilityValues[resid]), function (k) {
		return startDate <= k && endDate > k;
	}), function (i, dt) {
		if (availabilityValues[resid][dt] < maxSelectable) {
			maxSelectable = availabilityValues[resid][dt];
		}
	});
	maxSelectable = Math.min(bfi_MaxQtSelectable, maxSelectable);

	//var correction = 1;
	if (jQuery(currSel).hasClass('ddlextras')) {
		currSel.empty();
		for (var i = parseInt(jQuery(currSel).attr("data-minvalue")); i <= maxSelectable; i++) {
			var opt = jQuery('<option>').text(i).attr('value', i);
			if (currentSelection <= maxSelectable ? i == currentSelection : i == 0) { opt.attr("selected", "selected"); }
			currSel.append(opt);
		}
		currSel.trigger("change");
	} else {
		jQuery.each(jQuery(".ddlrooms-" + resid), function (j, itm) {
			jQuery(itm).find('option').remove();
			jQuery(itm).attr("data-availability", maxSelectable);
			for (var i = 0; i <= maxSelectable; i++) {
				var opt = jQuery('<option>').text(i).attr('value', i);
				if (i == 0) { opt.attr("selected", "selected"); }
				jQuery(itm).append(opt);
			}
		});
		bfi_UpdateQuote(); //set service price default value
		bfi_updateQuoteService();
	}


}


/*------------------------------------------------------------------------------------------*/
function bfi_showhideCategories(currModule) {
	var currForm = jQuery(currModule).find(".bfi-form-default-resources").first();
	var currModID = jQuery(currModule).attr("data-currmodid");
	var currCheckin = jQuery(currForm).find("input[name='checkin']").first();
	var currCheckout = jQuery(currForm).find("input[name='checkout']").first();
	var currShowdaterange = jQuery(currForm).attr("data-showdaterange");
	var currShowdatetimerange = jQuery(currForm).attr("data-showdatetimerange");
	var currTab = jQuery('#navbookingforsearch' + currModID + ' li.ui-tabs-active a[data-toggle="tab"]').first();
	var target = jQuery(currTab).attr("class");

if (currForm.length > 0)
{
	var merchantCategoriesResource = JSON.parse(jQuery(currModule).find(".bfi-merchantcategoriesresource").first().html());

	var merchantCategoriesSelectedBooking = jQuery(currModule).find(".bfi-merchantcategoriesselectedbooking").first().html().split(",").map(Number);
	var merchantCategoriesSelectedServices = jQuery(currModule).find(".bfi-merchantcategoriesselectedservices").first().html().split(",").map(Number);
	var merchantCategoriesSelectedActivities = jQuery(currModule).find(".bfi-merchantcategoriesselectedactivities").first().html().split(",").map(Number);
	var merchantCategoriesSelectedOthers = jQuery(currModule).find(".bfi-merchantCategoriesSelectedOthers").first().html().split(",").map(Number);

	var unitCategoriesResource = JSON.parse(jQuery(currModule).find(".bfi-unitcategoriesresource").first().html());

	var unitCategoriesSelectedBooking = jQuery(currModule).find(".bfi-unitcategoriesselectedbooking").first().html().split(",").map(Number);
	var unitCategoriesSelectedServices = jQuery(currModule).find(".bfi-unitcategoriesselectedservices").first().html().split(",").map(Number);
	var unitCategoriesSelectedActivities = jQuery(currModule).find(".bfi-unitcategoriesselectedactivities").first().html().split(",").map(Number);
	var unitCategoriesSelectedOthers = jQuery(currModule).find(".bfi-unitcategoriesselectedothers").first().html().split(",").map(Number);

	var currentMerchantCategoriesSelected = jQuery("#merchantCategoryId" + currModID).val() ? jQuery("#merchantCategoryId" + currModID).val() : "0";
	var currentUnitCategoriesSelected = jQuery("#masterTypeId" + currModID).val() ? jQuery("#masterTypeId" + currModID).val() : "0";

	jQuery("#merchantCategoryId" + currModID).val("0");
	jQuery("#masterTypeId" + currModID).val("0");

	var currMerchantCategory = jQuery("#merchantCategoryId" + currModID);
	currMerchantCategory.find('option:gt(0)').remove().end();
	var currUnitCategory = jQuery("#masterTypeId" + currModID);
	currUnitCategory.find('option:gt(0)').remove().end();

	var resbynight = jQuery(currForm).find(".resbynighthd").first();
	var availabilityTypesSelectedBooking = jQuery(currModule).find(".bfi-availabilitytypesselectedbooking").first().html();
	var availabilityTypesSelectedServices = jQuery(currModule).find(".bfi-availabilitytypesselectedservices").first().html();
	var availabilityTypesSelectedActivities = jQuery(currModule).find(".bfi-availabilitytypesselectedactivities").first().html();
	var availabilityTypesSelectedOthers = jQuery(currModule).find(".bfi-availabilitytypesselectedothers").first().html();

	var itemTypes = jQuery(currForm).find(".itemtypeshd").first();
	var itemTypesSelectedBooking = jQuery(currModule).find(".bfi-itemtypesselectedbooking").first().html();
	var itemTypesSelectedServices = jQuery(currModule).find(".bfi-itemtypesselectedservices").first().html();
	var itemTypesSelectedActivities = jQuery(currModule).find(".bfi-itemtypesselectedactivities").first().html();
	var itemTypesSelectedOthers = jQuery(currModule).find(".bfi-itemtypesselectedothers").first().html();

	var groupResultType = jQuery(currForm).find(".groupresulttypehd").first();
	var groupBySelectedBooking = jQuery(currModule).find(".bfi-groupbyselectedbooking").first().html();
	var groupBySelectedServices = jQuery(currModule).find(".bfi-groupbyselectedservices").first().html();
	var groupBySelectedActivities = jQuery(currModule).find(".bfi-groupbyselectedactivities").first().html();
	var groupBySelectedOthers = jQuery(currModule).find(".bfi-groupbyselectedothers").first().html();

	var resultView = jQuery(currForm).find(".resviewhd").first();
	var resultViewSelectedBooking = jQuery(currModule).find(".bfi-resultviewsselectedbooking").first().html();
	var resultViewSelectedServices = jQuery(currModule).find(".bfi-resultviewsselectedservices").first().html();
	var resultViewSelectedActivities = jQuery(currModule).find(".bfi-resultviewsselectedactivities").first().html();
	var resultViewSelectedOthers = jQuery(currModule).find(".bfi-resultviewsselectedothers").first().html();


	/* -- searchtab 0 -- */
	if (currTab.hasClass("searchResources")) {
		if (availabilityTypesSelectedBooking.length > 0) {
			resbynight.val(availabilityTypesSelectedBooking);
		}
		jQuery("#searchtypetab" + currModID).val("0");
		if (currShowdaterange != "0") {
			var currDate = currCheckin.datepicker('getDate');
			if (currDate == null)
			{
				currDate = new Date();
			}
			if (jQuery(resbynight).val() == 1) {
				currDate.setDate(currDate.getDate() + 1);
			}
			currCheckout.datepicker("option", "minDate", currDate);
			currCheckout.datepicker("option", "maxDate", Infinity);
			if (currCheckout.datepicker("getDate") <= currDate) {
				currCheckout.datepicker("setDate", Date.UTC(currDate.getFullYear(), currDate.getMonth(), currDate.getDate()));
			}
			bfi_printChangedDate(currForm);
		}

		if (itemTypesSelectedBooking.length > 0) {
			itemTypes.val(itemTypesSelectedBooking);
		}
		if (groupBySelectedBooking.length > 0) {
			groupResultType.val(groupBySelectedBooking);
		}
		if (resultViewSelectedBooking.length > 0) {
			resultView.val(resultViewSelectedBooking);
		}

		if (merchantCategoriesSelectedBooking.length > 0) {
			jQuery("#merchantCategoryId" + currModID).closest("div").show();
			for (var i = 0; i < merchantCategoriesSelectedBooking.length; i++) {
				var currMC = merchantCategoriesResource[merchantCategoriesSelectedBooking[i]];
				currMerchantCategory.append(jQuery('<option>').text(currMC).attr('value', merchantCategoriesSelectedBooking[i]));
			}
			currMerchantCategory.find('option:eq(0)').val(merchantCategoriesSelectedBooking.join(","));

		} else {
			jQuery("#merchantCategoryId" + currModID).closest("div").hide();
			currMerchantCategory.find('option:eq(0)').val("0");
		}
		if (unitCategoriesSelectedBooking.length > 0) {
			jQuery("#masterTypeId" + currModID).closest("div").show();
			for (var i = 0; i < unitCategoriesSelectedBooking.length; i++) {
				var currUC = unitCategoriesResource[unitCategoriesSelectedBooking[i]];
				currUnitCategory.append(jQuery('<option>').text(currUC).attr('value', unitCategoriesSelectedBooking[i]));
			}
			currUnitCategory.find('option:eq(0)').val(unitCategoriesSelectedBooking.join(","));
		} else {
			jQuery("#masterTypeId" + currModID).closest("div").hide();
			currUnitCategory.find('option:eq(0)').val("0");
		}
		if (currentMerchantCategoriesSelected.indexOf(",") == -1 && jQuery.inArray(Number(currentMerchantCategoriesSelected), merchantCategoriesSelectedBooking) != -1) {
			jQuery("#merchantCategoryId" + currModID).val(currentMerchantCategoriesSelected);
		}
		if (currentUnitCategoriesSelected.indexOf(",") == -1 && jQuery.inArray(Number(currentUnitCategoriesSelected), unitCategoriesSelectedBooking) != -1) {
			jQuery("#masterTypeId" + currModID).val(currentUnitCategoriesSelected);
		}
			var merchantCategoryId = jQuery(currForm).find(".bfi-merchantCategoryId").first();
			if (merchantCategoryId.length > 0)
			{
				merchantCategoryId.val(jQuery(currModule).find(".bfi-merchantcategoriesselectedbooking").first().html());
			}
			var masterTypeId = jQuery(currForm).find(".bfi-masterTypeId").first();
			if (masterTypeId.length > 0)
			{
				masterTypeId.val(jQuery(currModule).find(".bfi-unitcategoriesselectedbooking").first().html());
			}
			var masterTypeIdfilters = jQuery(currForm).find(".bfi-filtersproductcategory").first();
			if (masterTypeIdfilters.length > 0)
			{
				masterTypeIdfilters.val(jQuery(currModule).find(".bfi-unitcategoriesselectedbooking").first().html());
			}
	}
	/* -- searchtab 1 -- */
	if (currTab.hasClass("searchServices")) {
		if (availabilityTypesSelectedServices.length > 0) {
			resbynight.val(availabilityTypesSelectedServices);
		}
		jQuery("#searchtypetab" + currModID).val("1");

		if (itemTypesSelectedServices.length > 0) {
			itemTypes.val(itemTypesSelectedServices);
		}
		if (groupBySelectedServices.length > 0) {
			groupResultType.val(groupBySelectedServices);
		}
		if (resultViewSelectedServices.length > 0) {
			resultView.val(resultViewSelectedServices);
		}
		if (merchantCategoriesSelectedServices.length > 0) {
			jQuery("#merchantCategoryId" + currModID).closest("div").show();
			for (var i = 0; i < merchantCategoriesSelectedServices.length; i++) {
				var currMC = merchantCategoriesResource[merchantCategoriesSelectedServices[i]];
				currMerchantCategory.append(jQuery('<option>').text(currMC).attr('value', merchantCategoriesSelectedServices[i]));
			}
			currMerchantCategory.find('option:eq(0)').val(merchantCategoriesSelectedServices.join(","));
		} else {
			jQuery("#merchantCategoryId" + currModID).closest("div").hide();
			currMerchantCategory.find('option:eq(0)').val("0");
		}
		if (unitCategoriesSelectedServices.length > 0) {
			jQuery("#masterTypeId" + currModID).closest("div").show();
			for (var i = 0; i < unitCategoriesSelectedServices.length; i++) {
				var currUC = unitCategoriesResource[unitCategoriesSelectedServices[i]];
				currUnitCategory.append(jQuery('<option>').text(currUC).attr('value', unitCategoriesSelectedServices[i]));
			}
			currUnitCategory.find('option:eq(0)').val(unitCategoriesSelectedServices.join(","));
		} else {
			jQuery("#masterTypeId" + currModID).closest("div").hide();
			currUnitCategory.find('option:eq(0)').val("0");
		}
		if (currentMerchantCategoriesSelected.indexOf(",") == -1 && jQuery.inArray(Number(currentMerchantCategoriesSelected), merchantCategoriesSelectedServices) != -1) {
			jQuery("#merchantCategoryId" + currModID).val(currentMerchantCategoriesSelected);
		}
		if (currentUnitCategoriesSelected.indexOf(",") == -1 && jQuery.inArray(Number(currentUnitCategoriesSelected), unitCategoriesSelectedServices) != -1) {
			jQuery("#masterTypeId" + currModID).val(currentUnitCategoriesSelected);
		}
			var merchantCategoryId = jQuery(currForm).find(".bfi-merchantCategoryId").first();
			if (merchantCategoryId.length > 0)
			{
				merchantCategoryId.val(jQuery(currModule).find(".bfi-merchantcategoriesselectedservices").first().html());
			}
			var masterTypeId = jQuery(currForm).find(".bfi-masterTypeId").first();
			if (masterTypeId.length > 0)
			{
				masterTypeId.val(jQuery(currModule).find(".bfi-unitcategoriesselectedservices").first().html());
			}
			var masterTypeIdfilters = jQuery(currForm).find(".bfi-filtersproductcategory").first();
			if (masterTypeIdfilters.length > 0)
			{
				masterTypeIdfilters.val(jQuery(currModule).find(".bfi-unitcategoriesselectedservices").first().html());
			}
	}
	/* -- searchtab 2 -- */
	if (currTab.hasClass("searchTimeSlots")) {
		if (availabilityTypesSelectedActivities.length > 0) {
			resbynight.val(availabilityTypesSelectedActivities);
		}
		jQuery("#searchtypetab" + currModID).val("2");

		if (itemTypesSelectedActivities.length > 0) {
			itemTypes.val(itemTypesSelectedActivities);
		}
		if (groupBySelectedActivities.length > 0) {
			groupResultType.val(groupBySelectedActivities);
		}
		if (resultViewSelectedActivities.length > 0) {
			resultView.val(resultViewSelectedActivities);
		}
		if (merchantCategoriesSelectedActivities.length > 0) {
			jQuery("#merchantCategoryId" + currModID).closest("div").show();
			for (var i = 0; i < merchantCategoriesSelectedActivities.length; i++) {
				var currMC = merchantCategoriesResource[merchantCategoriesSelectedActivities[i]];
				currMerchantCategory.append(jQuery('<option>').text(currMC).attr('value', merchantCategoriesSelectedActivities[i]));
			}
			currMerchantCategory.find('option:eq(0)').val(merchantCategoriesSelectedActivities.join(","));
		} else {
			jQuery("#merchantCategoryId" + currModID).closest("div").hide();
			currMerchantCategory.find('option:eq(0)').val("0");
		}
		if (unitCategoriesSelectedActivities.length > 0) {
			jQuery("#masterTypeId" + currModID).closest("div").show();
			for (var i = 0; i < unitCategoriesSelectedActivities.length; i++) {
				var currUC = unitCategoriesResource[unitCategoriesSelectedActivities[i]];
				currUnitCategory.append(jQuery('<option>').text(currUC).attr('value', unitCategoriesSelectedActivities[i]));
			}
			currUnitCategory.find('option:eq(0)').val(unitCategoriesSelectedActivities.join(","));
		} else {
			jQuery("#masterTypeId" + currModID).closest("div").hide();
			currUnitCategory.find('option:eq(0)').val("0");
		}
		if (currentMerchantCategoriesSelected.indexOf(",") == -1 && jQuery.inArray(Number(currentMerchantCategoriesSelected), merchantCategoriesSelectedActivities) != -1) {
			jQuery("#merchantCategoryId" + currModID).val(currentMerchantCategoriesSelected);
		}
		if (currentUnitCategoriesSelected.indexOf(",") == -1 && jQuery.inArray(Number(currentUnitCategoriesSelected), unitCategoriesSelectedActivities) != -1) {
			jQuery("#masterTypeId" + currModID).val(currentUnitCategoriesSelected);
		}
			var merchantCategoryId = jQuery(currForm).find(".bfi-merchantCategoryId").first();
			if (merchantCategoryId.length > 0)
			{
				merchantCategoryId.val(jQuery(currModule).find(".bfi-merchantcategoriesselectedactivities").first().html());
			}
			var masterTypeId = jQuery(currForm).find(".bfi-masterTypeId").first();
			if (masterTypeId.length > 0)
			{
				masterTypeId.val(jQuery(currModule).find(".bfi-unitcategoriesselectedactivities").first().html());
			}
			var masterTypeIdfilters = jQuery(currForm).find(".bfi-filtersproductcategory").first();
			if (masterTypeIdfilters.length > 0)
			{
				masterTypeIdfilters.val(jQuery(currModule).find(".bfi-unitcategoriesselectedactivities").first().html());
			}
	}
	/* -- searchtab 4 -- */
	if (currTab.hasClass("searchOthers")) {
		if (availabilityTypesSelectedOthers.length > 0) {
			resbynight.val(availabilityTypesSelectedOthers);
		}
		jQuery("#searchtypetab" + currModID).val("4");

		if (itemTypesSelectedOthers.length > 0) {
			itemTypes.val(itemTypesSelectedOthers);
		}
		if (groupBySelectedOthers.length > 0) {
			groupResultType.val(groupBySelectedOthers);
		}
		if (resultViewSelectedOthers.length > 0) {
			resultView.val(resultViewSelectedOthers);
		}
		if (merchantCategoriesSelectedOthers.length > 0) {
			jQuery("#merchantCategoryId" + currModID).closest("div").show();
			for (var i = 0; i < merchantCategoriesSelectedOthers.length; i++) {
				var currMC = merchantCategoriesResource[merchantCategoriesSelectedOthers[i]];
				currMerchantCategory.append(jQuery('<option>').text(currMC).attr('value', merchantCategoriesSelectedOthers[i]));
			}
			currMerchantCategory.find('option:eq(0)').val(merchantCategoriesSelectedOthers.join(","));
		} else {
			jQuery("#merchantCategoryId" + currModID).closest("div").hide();
			currMerchantCategory.find('option:eq(0)').val("0");
		}
		if (unitCategoriesSelectedOthers.length > 0) {
			jQuery("#masterTypeId" + currModID).closest("div").show();
			for (var i = 0; i < unitCategoriesSelectedOthers.length; i++) {
				var currUC = unitCategoriesResource[unitCategoriesSelectedOthers[i]];
				currUnitCategory.append(jQuery('<option>').text(currUC).attr('value', unitCategoriesSelectedOthers[i]));
			}
			currUnitCategory.find('option:eq(0)').val(unitCategoriesSelectedOthers.join(","));
		} else {
			jQuery("#masterTypeId" + currModID).closest("div").hide();
			currUnitCategory.find('option:eq(0)').val("0");
		}
		if (currentMerchantCategoriesSelected.indexOf(",") == -1 && jQuery.inArray(Number(currentMerchantCategoriesSelected), merchantCategoriesSelectedOthers) != -1) {
			jQuery("#merchantCategoryId" + currModID).val(currentMerchantCategoriesSelected);
		}
		if (currentUnitCategoriesSelected.indexOf(",") == -1 && jQuery.inArray(Number(currentUnitCategoriesSelected), unitCategoriesSelectedOthers) != -1) {
			jQuery("#masterTypeId" + currModID).val(currentUnitCategoriesSelected);
		}
			var merchantCategoryId = jQuery(currForm).find(".bfi-merchantCategoryId").first();
			if (merchantCategoryId.length > 0)
			{
				merchantCategoryId.val(jQuery(currModule).find(".bfi-merchantCategoriesSelectedOthers").first().html());
			}
			var masterTypeId = jQuery(currForm).find(".bfi-masterTypeId").first();
			if (masterTypeId.length > 0)
			{
				masterTypeId.val(jQuery(currModule).find(".bfi-unitcategoriesselectedothers").first().html());
			}
			var masterTypeIdfilters = jQuery(currForm).find(".bfi-filtersproductcategory").first();
			if (masterTypeIdfilters.length > 0)
			{
				masterTypeIdfilters.val(jQuery(currModule).find(".bfi-unitcategoriesselectedothers").first().html());
			}
	}

	jQuery(currModule).find(".bfi-datetimepicker").hide();
	//jQuery(currModule).find("[name=checkFullPeriod]").val("0");
	if (resbynight.val() == "2" && itemTypes.val() == "1" && currShowdatetimerange == "1") {
		jQuery(currModule).find(".bfi-datetimepicker").show();
		//jQuery(currModule).find("[name=checkFullPeriod]").val("1");
	}

	// da nascondere se solo 1;
	var currMerchantCategoryOption = jQuery("#merchantCategoryId" + currModID + " option").length;
	var currUnitCategoryOption = jQuery("#masterTypeId" + currModID + " option").length;
	if (currMerchantCategoryOption < 3) {
		jQuery("#merchantCategoryId" + currModID).closest("div").hide();
	}
	if (currUnitCategoryOption < 3) {
		jQuery("#masterTypeId" + currModID).closest("div").hide();
	}

}
	if (currTab.hasClass("searchSelling")) {
		jQuery("#searchtypetab" + currModID).val("3");
	}
}

function bfi_showpopoversearch(currModID) {
	jQuery("#bfi_lblchildrenages" + currModID).webuiPopover({
		content: jQuery("#bfi_childrenagesmsg" + currModID).html(),
		container: document.body,
		cache: false,
		placement: "auto-bottom",
		maxWidth: "300px",
		type: 'html',
		style: 'bfi-webuipopover'
	});
	jQuery("#bfi_lblchildrenages" + currModID).webuiPopover("show");
}

function bfi_checkChildrenSearch(nch, showMsg, currModID) {
	jQuery("#mod_bookingforsearch-childrenages" + currModID).hide();
	jQuery("#mod_bookingforsearch-childrenages" + currModID + " select").hide();
	if (nch > 0) {
		jQuery("#mod_bookingforsearch-childrenages" + currModID + " select").each(function (i) {
			if (i < nch) {
				var id = jQuery(this).attr('id');
				jQuery(this).css('display', 'inline-block');
			}
		});
		jQuery("#mod_bookingforsearch-childrenages" + currModID).show();
		if (showMsg === 1) {
			bfi_showpopoversearch(currModID);
		}
	}
}
function bfi_countPersone(currModID) {
	jQuery("#bfi_lblchildrenages" + currModID).webuiPopover("hide");
	if(jQuery("#bfi-minqt" + currModID).length > 0) {
		var numResources = new Number(jQuery("#bfi-minqt" + currModID).val() || 0);
		jQuery("#bfi-resource-info" + currModID + " span").html(numResources);
	jQuery("#searchformminqt" + currModID).val(numResources);
	}
	if(jQuery("#bfi-minrooms" + currModID).length > 0) {
		var numResources = new Number(jQuery("#bfi-minrooms" + currModID).val() || 0);
		jQuery("#bfi-room-info" + currModID + " span").html(numResources);
	jQuery("#searchformminrooms" + currModID).val(numResources);
	}
	var numAdults = new Number(jQuery("#bfi-adult" + currModID).val() || 0);
	var numSeniores = new Number(jQuery("#bfi-senior" + currModID).val() || 0);
	var numChildren = new Number(jQuery("#bfi-child" + currModID).val() || 0);
	jQuery("#bfi-adult-info" + currModID + " span").html(numAdults);
	jQuery("#bfi-senior-info" + currModID + " span").html(numSeniores);
	jQuery("#bfi-child-info" + currModID + " span").html(numChildren);


	bfi_checkChildrenSearch(numChildren, 0, currModID);
	jQuery("#searchformpersons" + currModID).val(numAdults + numChildren + numSeniores);
	jQuery("#searchformpersonsadult" + currModID).val(numAdults);
	jQuery("#searchformpersonssenior" + currModID).val(numSeniores);
	jQuery("#searchformpersonschild" + currModID).val(numChildren);

	jQuery("#mod_bookingforsearch-childrenages" + currModID + " select").each(function (i) {
		jQuery("#searchformpersonschild" + (i + 1) + currModID).val(jQuery(this).val());
	});

	jQuery("#showmsgchildage" + currModID).val("0");
	jQuery("#mod_bookingforsearch-childrenages" + currModID + " select:visible option:selected").each(function (i) {
		if (jQuery(this).text() == "") {
			jQuery("#showmsgchildage" + currModID).val(1);
			return;
		}
	});
}
function bfi_quoteChanged(currModID) {
	bfi_countPersone(currModID);
}
function bfi_countRangePersone(obj, currModID) {
	// recupero dati di ogni persona e ricreo la qt corretta
		var currObj = jQuery(obj);
		var numCurrPerson = new Number(currObj.val() || 0);
		jQuery("#" + currObj.attr("data-ref") + " span").html(numCurrPerson);
		var currAge = currObj.attr("data-age");
		// ciclo per tutte le persone con classe bfi-paxages 
		var currContainer =currObj.closest(".bfi-showperson");
		var arrAges = [];
		jQuery.each(currContainer.find(".bfi-paxages"), function (i, itm) {
			var numCurrItemPerson = new Number(jQuery(itm).val() || 0);
			refObj = jQuery("#" + jQuery(itm).attr("data-ref")); 
			if (numCurrItemPerson>0)
			{
				refObj.show();
				for (i=0;i< numCurrItemPerson; i++)
				{
					arrAges.push(jQuery(itm).attr("data-age"));
				}
			}else{
//				refObj.hide();
			}
		});

		// invio le età scelte
		jQuery("#searchformpersonspaxages" + currModID).val(arrAges.join(","));
}

function bfi_CheckTabsCollapsible(currModID) {
	var windowsize = jQuery(window).width();
	var windororientation = window.orientation;
	var collapsibleTabs = true;
	var isMacLike = navigator.platform.match(/(Mac|iPhone|iPod|iPad)/i) ? true : false;

	var tabCount = jQuery("#bfisearch" + currModID + " >ul >li").length;
	var currSerachFormContainer = jQuery("#bfisearch" + currModID).closest("div.bfi-searchwidget ");
	var isaffixbottom = currSerachFormContainer.attr("data-affixbottom");
	if (jQuery().tDatePicker &&  jQuery('.t-datepicker').length>0)
	{
		jQuery('.t-datepicker').tDatePicker('hide');
	}
	if (tabCount > 1) {
		if ((jQuery("#bfisearch" + currModID).closest("div.bfialwaisopen").length || windowsize > 767) && !isMacLike) {
			collapsibleTabs = false;
		}
		if ((jQuery("#bfisearch" + currModID).closest("div.bfiAffixBottom").length && windowsize < 769)) {
			collapsibleTabs = true;
		}
	} else {

		collapsibleTabs = false;
		if ((jQuery("#bfisearch" + currModID).closest("div.bfiAffixBottom").length && windowsize < 769)) {
			collapsibleTabs = true;
			jQuery("#bfisearch" + currModID).find(".bfi-tabs").first().show();
			
		}else{
			jQuery("#bfisearch" + currModID).find(".bfi-tabs").first().hide();
		}
	}
	if (windororientation!=0)
	{
		currSerachFormContainer.removeClass("bfiAffixBottom");
		collapsibleTabs = false;
	}else{
		if (isaffixbottom=="1")
		{
			currSerachFormContainer.addClass("bfiAffixBottom");
		}
	}
	if (jQuery("#bfisearch" + currModID).hasClass("ui-tabs"))
	{
		jQuery("#bfisearch" + currModID).bfiTabs("option", "collapsible", collapsibleTabs);
	}
}

function bfi_affix(currModID) {
	var windowsize = jQuery(window).width();
	if (windowsize > 767) {
		if (window.pageYOffset >= 180) {
			jQuery(".bfi-affix-top" + currModID).addClass("bfiAffixTop");
		} else {
			jQuery(".bfi-affix-top" + currModID).removeClass("bfiAffixTop");
		}
	} else {
		jQuery(".bfi-affix-top" + currModID).removeClass("bfiAffixTop");
	}
	//hide calendar
	var currForm = jQuery(this).find(".bfi-form-default [data-currmodid!='" + currModID + "']").first();
	var currCheckin = jQuery(currForm).find("input[name='checkin']").first();
	var currCheckout = jQuery(currForm).find("input[name='checkout']").first();
	currCheckin.datepicker("hide");
	currCheckout.datepicker("hide");
}


/*------------------- timeperiod --------------------*/
function bfi_getAjaxDateCheckout(currProdId, checkinObj, checkoutObj, productAvailabilityType, checkinTime, callback) {
	var checkoutContainer = jQuery(checkoutObj).closest(".bfi-checkout-field-container").first();
	checkoutContainer.block({ message: '' });
	//	jQuery('#calcheckout').block({message: ''});
	var task = "GetCheckOutDatesDetailed";
	var datereformat = jQuery.datepicker.formatDate("yymmdd", jQuery(checkinObj).datepicker("getDate"));
	if (productAvailabilityType == 2) {
		task = "GetCheckOutDatesPerTimes";
		var datereformat = jQuery.datepicker.formatDate("yymmdd", jQuery(checkinObj).datepicker("getDate"));
		datereformat += checkinTime.val().replace(":", "") + "00";
	}
	jQuery(checkoutObj).block({ message: '' });
	var options = {
		url: bookingfor.getActionUrl(null, null, task, 'resourceId=' + currProdId + '&checkin=' + datereformat),
		dataType: 'json',
		success: function (data) {
			checkOutDaysToEnable[currProdId + ""] = [];
			availabilityValues[currProdId + ""] = {};
			if (productAvailabilityType == 2) {
				availabilityTimePeriodCheckOut[currProdId + ""] = {};
				for (var i = 0; i < data.length; i++) {
					checkOutDaysToEnable[currProdId + ""].push(data[i].StartDate);
					availabilityTimePeriodCheckOut[currProdId + ""][data[i].StartDate + ""] = JSON.parse(data[i].TimeRangesString);
					jQuery.each(JSON.parse(data[i].AvailabilitiesString), function (j, av) {
						availabilityValues[currProdId + ""][av.Time] = av.Availability;
					});
				}
				bfi_onEnsureCheckOutDaysTimesToEnableSuccess(data || [], currProdId, checkoutObj, checkoutContainer); //in file search_details.php
			} else {
				jQuery.each(data, function (j, av) {
					checkOutDaysToEnable[currProdId + ""].push(parseInt(av.PeriodDate));
					availabilityValues[currProdId + ""][av.PeriodDate] = av.Availability;
				});
				//checkOutDaysToEnable = data || [];
				/*
				for (var i = 0; i < checkOutDaysToEnable.length; i++) { 
					checkOutDaysToEnable[i] = +checkOutDaysToEnable[i]; 
				} 
				*/
				//                onEnsureCheckOutDaysToEnableSuccess();
				bfi_onEnsureCheckOutDaysToEnableSuccess(checkOutDaysToEnable[currProdId + ""], null, checkoutObj, checkoutContainer);
			}
			if (callback) {
				callback();
			}
		},
		error: function (xhr, ajaxOptions, thrownError) {
			checkoutContainer.unblock();
		}
	};
	jQuery.ajax(options);
}

function bfi_onEnsureCheckOutDaysTimesToEnableSuccess(currcheckOutDaysToEnable, currCheckoutContainer) {
	if (!currcheckOutDaysToEnable || currcheckOutDaysToEnable.length == 0) {
		jQuery(currCheckoutContainer).unblock();
		return;
	}
	if (currcheckOutDaysToEnable[0] == 0) {
		jQuery(currCheckoutContainer).unblock();
		return;
	}
	//	var date = jQuery.datepicker.parseDate('yyyymmdd', currcheckOutDaysToEnable[0]);
	var strDate = '' + currcheckOutDaysToEnable[0].StartDate;
	var timeLength = currcheckOutDaysToEnable[0].TimeLength;

	//	console.log("bfi_onEnsureCheckOutDaysTimesToEnableSuccess");
	//	console.log(strDate);

	var date = new Date(strDate.substr(0, 4), strDate.substr(4, 2) - 1, strDate.substr(6, 2));

	currCheckout.datepicker("option", "minDate", date);
	var datetocheck = currCheckout.datepicker("getDate");
	//checkout.datepicker("option", "minDate", date);
	//var datetocheck = checkout.datepicker("getDate");
	//	var checkOutDaysToEnable = [];
	//TODO:
	//    availabilityTimeOutPeriod = {};
	//    for (var i = 0; i < currcheckOutDaysToEnable.length; i++) {
	//        checkOutDaysToEnable[i] = currcheckOutDaysToEnable[i].StartDate;
	//        availabilityTimeOutPeriod[currcheckOutDaysToEnable[i].StartDate + ""] = JSON.parse(currcheckOutDaysToEnable[i].TimeRangesString);
	//   }
	//	console.log(currcheckOutDaysToEnable);

	if (!bfi_enableSpecificDates(datetocheck, 0, checkOutDaysToEnable)) {
		currCheckout.val(getDisplayDate(date));
		//		printChangedDateBooking(date, currCheckout)
	}

	bfi_timeStepCheckin(currCheckout, "#checkouttimedetailsselect", availabilityTimePeriodCheckOut, timeLength, jQuery("#checkouttimedetailsselect").val(), "1");

	//		printChangedDateBooking(date, currCheckout)
	//	printChangedDateBooking(date, currCheckout)
	//	bfi_printChangedDateBooking();

	var currForm = jQuery("#bfi-calculatorForm");
	if (typeof bfi_printChangedDate !== "undefined") { bfi_printChangedDate(currForm); }

	jQuery("#calcheckout").unblock();

	//	if (raiseUpdate) {
	//		btnClick().click();
	//	}
}

function bfi_timeStepCheckin(currDatepicker, currSelect, currTimes, currStep, selectedvalue, setlast) {
	if (!currStep || currStep == 0) currStep = 15;
	//	console.log(currKey);
	jQuery(currSelect).find('option').remove().end();
	if (currTimes && (currTimes.length || (typeof currTimes == "object" && Object.keys(currTimes).length))) {
		var currKey = jQuery.datepicker.formatDate("yymmdd", jQuery(currDatepicker).datepicker("getDate"));
		if (currKey in currTimes) {
			//clear options..
			jQuery.each(currTimes[currKey], function (key, val) {
				bfi_timestepoptions(jQuery(currSelect), bfi_string2minute(val.StartTime), bfi_string2minute(val.EndTime), currStep);
			});
		}
	} else {
		bfi_timestepoptions(jQuery(currSelect), 0, 1440 - currStep, currStep);
	}
	if (typeof selectedvalue == "string") {
		jQuery(currSelect).find('option[value="' + selectedvalue + '"]').first().attr('selected', 'selected');
	} else if (typeof setlast == "string") {
		jQuery(currSelect).find('option').last().attr('selected', 'selected');
	} else {
		jQuery(currSelect).find('option').first().attr('selected', 'selected');
	}
	jQuery(currSelect).trigger('change');
}

function bfi_timestepoptions(list, start, end, step) { // step in minuti
	for (var i = start, j = 0; i <= end; j++ , i += step) {
		var timeInt = i * 60;
		var timeString = bfi_int2time(timeInt, 'H:i');

		var row = jQuery("<option />", { value: timeString });
		row.text(timeString);
		list.append(row);
	}
}
function bfi_string2minute(timeString) {
	if (typeof timeString != "string" || !timeString.length == 6 || !timeString.length == 4) {
		return null;
	}
	if (timeString.length == 6) {
		timeString = timeString.slice(0, -2);
	}
	var hour = timeString.substring(0, 2);
	var minute = timeString.substring(2, 4);
	//	console.log(timeString + " -> " + hour + ":" + minute);
	return (parseInt(hour) * 60) + parseInt(minute)
}

function bfi_int2time(timeInt, timeFormat) {
	if (typeof timeInt != "number") {
		return null;
	}

	var seconds = parseInt(timeInt % 60),
		minutes = parseInt((timeInt / 60) % 60),
		hours = parseInt((timeInt / (60 * 60)) % 24);

	var time = new Date(1970, 0, 2, hours, minutes, seconds, 0);

	if (isNaN(time.getTime())) {
		return null;
	}

	if (jQuery.type(timeFormat) === "function") {
		return timeFormat(time);
	}

	var output = "";
	var hour, code;
	for (var i = 0; i < timeFormat.length; i++) {
		code = timeFormat.charAt(i);
		switch (code) {
			case "a":
				output += time.getHours() > 11 ? "pm" : "am";
				break;

			case "A":
				output += time.getHours() > 11 ? "PM" : "AM";
				break;

			case "g":
				hour = time.getHours() % 12;
				output += hour === 0 ? "12" : hour;
				break;

			case "G":
				hour = time.getHours();
				//          if (timeInt === _ONE_DAY) hour = settings.show2400 ? 24 : 0;
				output += hour;
				break;

			case "h":
				hour = time.getHours() % 12;

				if (hour !== 0 && hour < 10) {
					hour = "0" + hour;
				}

				output += hour === 0 ? "12" : hour;
				break;

			case "H":
				hour = time.getHours();
				//          if (timeInt === _ONE_DAY) hour = settings.show2400 ? 24 : 0;
				output += hour > 9 ? hour : "0" + hour;
				break;

			case "i":
				minutes = time.getMinutes();
				output += minutes > 9 ? minutes : "0" + minutes;
				break;

			case "s":
				seconds = time.getSeconds();
				output += seconds > 9 ? seconds : "0" + seconds;
				break;

			case "\\":
				// escape character; add the next character and skip ahead
				i++;
				output += timeFormat.charAt(i);
				break;

			default:
				output += code;
		}
	}

	return output;
}
/*------------------- end timeperiod --------------------*/
function bfi_closedBooking(date, offset, enableDays, currform, currResourceId) {
	currform = jQuery(currform);
	var currCheckin = currform.find(".bfi-checkin-field").first();
	var currCheckout = currform.find(".bfi-checkout-field").first();
	var productAvailabilityType = parseInt(currform.attr("data-productavailabilitytype"));
	//
	var strdate = ("0" + date.getDate()).slice(-2) + "/" + ("0" + (date.getMonth() + 1)).slice(-2) + "/" + date.getFullYear();
	var from = currCheckin.datepicker("getDate");
	var to = currCheckout.datepicker("getDate");

	var c = strdate.split("/");
	var check = new Date(c[2], c[1] - 1, c[0]);
	if (productAvailabilityType == 3) {
		to = from;
	}

	var dayEnabled = false;
	var month = date.getMonth() + 1;
	var day = date.getDate();
	var year = date.getFullYear();
	if (currResourceId > 0) {
		var copyarray = jQuery.extend(true, [], enableDays);
		for (var i = 0; i < offset; i++)
			copyarray.pop();
		var datereformat = year + '' + bookingfor.pad(month, 2) + '' + bookingfor.pad(day, 2);
		if (jQuery.inArray(Number(datereformat), copyarray) != -1) {
			dayEnabled = true;
			//return [true, 'greenDay'];
		}
	} else {
		dayEnabled = true;
	}

	var holydayTitle = "";
	var holydayCss = "";

	var currDay = ("0" + date.getDate()).slice(-2) + "" + ("0" + (date.getMonth() + 1)).slice(-2);
	var currIdxHoliday = jQuery.inArray(currDay, bookingfor.holydays);
	if (currIdxHoliday != -1) {
		holydayTitle = bookingfor.holydaysTitle[currIdxHoliday];
		holydayCss = "bfi-date-holidays ";
	}
	currDay = ("0" + date.getDate()).slice(-2) + "" + ("0" + (date.getMonth() + 1)).slice(-2) + date.getFullYear();
	currIdxHoliday = jQuery.inArray(currDay, bookingfor.holydays);
	if (currIdxHoliday != -1) {
		holydayTitle = bookingfor.holydaysTitle[currIdxHoliday];
		holydayCss = "bfi-date-holidays ";
	}

	arr = [dayEnabled, holydayCss, holydayTitle];
	if (check.getTime() == from.getTime()) {
		arr = [dayEnabled, holydayCss + ' date-start-selected', holydayTitle];
	}
	if (check.getTime() == to.getTime()) {
		arr = [dayEnabled, holydayCss + ' date-end-selected', holydayTitle];
	}
	if (check > from && check < to) {
		arr = [dayEnabled, holydayCss + ' date-selected', holydayTitle];
	}
	return arr;
}
function bfi_closedBookingExperience(currdate, offset, enableDays, currform, currResourceId) {
	var date =currdate._d;
	currform = jQuery(currform);
	var strdate = ("0" + date.getDate()).slice(-2) + "/" + ("0" + (date.getMonth() + 1)).slice(-2) + "/" + date.getFullYear();
	var c = strdate.split("/");
	var check = new Date(c[2], c[1] - 1, c[0]);
	var dayEnabled = false;
	var month = date.getMonth() + 1;
	var day = date.getDate();
	var year = date.getFullYear();
	if (currResourceId > 0) {
		var copyarray = jQuery.extend(true, [], enableDays);
		for (var i = 0; i < offset; i++)
			copyarray.pop();
		var datereformat = year + '' + bookingfor.pad(month, 2) + '' + bookingfor.pad(day, 2);
		if (jQuery.inArray(Number(datereformat), copyarray) != -1) {
			dayEnabled = true;
			//return [true, 'greenDay'];
		}
	} else {
		dayEnabled = true;
	}
	return dayEnabled;
//	var holydayTitle = "";
//	var holydayCss = "";
//
//	var currDay = ("0" + date.getDate()).slice(-2) + "" + ("0" + (date.getMonth() + 1)).slice(-2);
//	var currIdxHoliday = jQuery.inArray(currDay, bookingfor.holydays);
//	if (currIdxHoliday != -1) {
//		holydayTitle = bookingfor.holydaysTitle[currIdxHoliday];
//		holydayCss = "bfi-date-holidays ";
//	}
//	currDay = ("0" + date.getDate()).slice(-2) + "" + ("0" + (date.getMonth() + 1)).slice(-2) + date.getFullYear();
//	currIdxHoliday = jQuery.inArray(currDay, bookingfor.holydays);
//	if (currIdxHoliday != -1) {
//		holydayTitle = bookingfor.holydaysTitle[currIdxHoliday];
//		holydayCss = "bfi-date-holidays ";
//	}
//
//	arr = [dayEnabled, holydayCss, holydayTitle];
//	if (check.getTime() == from.getTime()) {
//		arr = [dayEnabled, holydayCss + ' date-start-selected', holydayTitle];
//	}
//	if (check.getTime() == to.getTime()) {
//		arr = [dayEnabled, holydayCss + ' date-end-selected', holydayTitle];
//	}
//	if (check > from && check < to) {
//		arr = [dayEnabled, holydayCss + ' date-selected', holydayTitle];
//	}
//	return arr;
}

/*-------------------------- Googlemaps extend --------------------------*/
/**
 * Returns the Popup class.
 *
 * Unfortunately, the Popup class can only be defined after
 * google.maps.OverlayView is defined, when the Maps API is loaded.
 * This function should be called by initMap.
 */
bookingfor.bfiCreatePopupClass = function () {
	/**
	 * A customized popup on the map.
	 * @param {!google.maps.LatLng} position
	 * @param {!Element} content The bubble div.
	 * @constructor
	 * @extends {google.maps.OverlayView}
	 */
	function Popup(position, content) {
		this.position = position;

		content.classList.add('popup-bubble');

		// This zero-height div is positioned at the bottom of the bubble.
		var bubbleAnchor = document.createElement('div');
		bubbleAnchor.classList.add('popup-bubble-anchor');
		bubbleAnchor.appendChild(content);

		// This zero-height div is positioned at the bottom of the tip.
		this.containerDiv = document.createElement('div');
		this.containerDiv.classList.add('popup-container');
		this.containerDiv.appendChild(bubbleAnchor);

		// Optionally stop clicks, etc., from bubbling up to the map.
		google.maps.OverlayView.preventMapHitsAndGesturesFrom(this.containerDiv);
	}
	// ES5 magic to extend google.maps.OverlayView.
	Popup.prototype = Object.create(google.maps.OverlayView.prototype);

	/** Called when the popup is added to the map. */
	Popup.prototype.onAdd = function () {
		this.getPanes().floatPane.appendChild(this.containerDiv);
	};

	/** Called when the popup is removed from the map. */
	Popup.prototype.onRemove = function () {
		if (this.containerDiv.parentElement) {
			this.containerDiv.parentElement.removeChild(this.containerDiv);
		}
	};

	/** Called each frame when the popup needs to draw itself. */
	Popup.prototype.draw = function () {
		var divPosition = this.getProjection().fromLatLngToDivPixel(this.position);

		// Hide the popup when it is far out of view.
		var display =
			Math.abs(divPosition.x) < 4000 && Math.abs(divPosition.y) < 4000 ?
				'block' :
				'none';

		if (display === 'block') {
			this.containerDiv.style.left = divPosition.x + 'px';
			this.containerDiv.style.top = divPosition.y + 'px';
		}
		if (this.containerDiv.style.display !== display) {
			this.containerDiv.style.display = display;
		}
	};

	return Popup;
}
function createElementFromHTML(htmlString) {
	var div = document.createElement('div');
	div.innerHTML = htmlString.trim();

	// Change this to div.childNodes to support multiple top-level nodes
	return div;
}

function bfi_consolelog(msg) {
	if (window.console) {
		console.log(msg);
	}
}

/*------------ overrides jquery dialog ----------------------*/
/*------------ udage:  var x = new Date().YYYYMMDDHHMMSS(); ------------*/
jQuery.widget("ui.dialog", jQuery.extend({}, jQuery.ui.dialog.prototype, {
	_title: function (title) {
		if (!this.options.title) {
			title.html("&#160;");
		} else {
			title.html(this.options.title);
		}
	}
}));
/*------------extend Date ----------------------*/
Object.defineProperty(Date.prototype, 'YYYYMMDDHHMMSS', {
	value: function () {
		function pad2(n) {  // always returns a string
			return (n < 10 ? '0' : '') + n;
		}

		return this.getFullYear() +
			pad2(this.getMonth() + 1) +
			pad2(this.getDate()) +
			pad2(this.getHours()) +
			pad2(this.getMinutes()) +
			pad2(this.getSeconds());
	}
});
//bfi-favorites
function bfi_getFavorites() {
	jQuery.post(bookingfor.getActionUrl(null, null, "GetContactFavoriteGroups", null), function (data) {
		data.forEach(function (currFavGroup) {
			if (currFavGroup.Name==null)
			{
				currFavGroup.Name = bfi_variables.bfi_txtFavDefList;
			}
		});
		localStorage.setItem('bfiFavorites', JSON.stringify(data));
		bfi_setFavorites();
	}, 'json');
}

function bfi_setFavorites() {
	var data = JSON.parse(localStorage.getItem("bfiFavorites"));
	if ((typeof data !== 'undefined')  && data!=null && data.length && data[0].FavoritesString.length) {
		var currFavoritesCount = 0;
		data.forEach(function (currFavGroup) {
			var currFavorites = JSON.parse(currFavGroup.FavoritesString);
			currFavorites.forEach(function (currFav) {
				var currLink;
				currFavoritesCount ++;
				if (currFav.TypeId == 2) {
					var startDate = new Date(currFav.StartDate).YYYYMMDDHHMMSS();
					var endDate = new Date(currFav.EndDate).YYYYMMDDHHMMSS();

					var currLink = jQuery(".bfi-icon-favorite[data-itemid='" + currFav.ItemId + "'][data-itemtype='" + currFav.TypeId + "'][data-startdate='" + startDate + "'][data-enddate='" + endDate + "']").addClass("bfi-favclicked");

				} else {
					var currLink = jQuery(".bfi-icon-favorite[data-itemid='" + currFav.ItemId + "'][data-itemtype='" + currFav.TypeId + "']").addClass("bfi-favclicked");
				}
				currLink.attr("data-favoriteid", currFav.FavoriteId);
			});		});
	

	}

	if (currFavoritesCount>0)
	{
		jQuery(".bfi-travelplanner").addClass("bfi-travelplanner-selected");
		jQuery(".bfi-travelplanner").find("i").addClass("fa-heart");
		jQuery(".bfi-travelplanner").find("i").removeClass("fa-heart-o");

	}else{
		jQuery(".bfi-travelplanner").removeClass("bfi-travelplanner-selected");
		jQuery(".bfi-travelplanner").find("i").removeClass("fa-heart");
		jQuery(".bfi-travelplanner").find("i").addClass("fa-heart-o");
	}
}
var tmpDialogFavoriteOpen;
jQuery(document).on('click tap', ".bfi-icon-favorite", function (e) {
	e.preventDefault();
	var elm = jQuery(this);
	elm.ItemId = parseInt(elm.attr("data-itemid"));
	elm.TypeId = parseInt(elm.attr("data-itemtype"));
	var classIcon = "fa-square-o";
	var classIconselected = "fa-check-square";
	var classIconLoagind = "fa-spinner fa-pulse";

	var data = JSON.parse(localStorage.getItem("bfiFavorites"));
	if (typeof data == 'undefined' || data==null ) {
		bfi_getFavorites();
		data = JSON.parse(localStorage.getItem("bfiFavorites"));
	}
		var dialogContainer = jQuery(document.createElement("div"));
	if ((typeof data !== 'undefined')  && data!=null && data.length ) {

		data.forEach(function (currFavGroup) {
				 elm.find(".bfi-favoritegroups-container").first();
					var favoriteId = 0;

					if (currFavGroup.FavoritesString.length) {
						var currFavorites = JSON.parse(currFavGroup.FavoritesString);
						currFavorites.forEach(function (currFav) {
							
							if (currFav.TypeId == 2) {
								var startDate = new Date(currFav.StartDate).YYYYMMDDHHMMSS();
								var endDate = new Date(currFav.EndDate).YYYYMMDDHHMMSS();
								if (currFav.ItemId == elm.ItemId && currFav.TypeId == elm.TypeId && startDate == elm.attr("data-startdate") && endDate == elm.attr("data-enddate") )
								{
									favoriteId = currFav.FavoriteId; 
								}
							}else 
								if (currFav.ItemId == elm.ItemId && currFav.TypeId == elm.TypeId && (currFav.StartDate+"").replace("null", "") == elm.attr("data-startdate") &&  (currFav.EndDate+"").replace("null", "") == elm.attr("data-enddate") )
								{
									favoriteId = currFav.FavoriteId; 
								}
						});
					}
					var newFavoriteGroup = jQuery(document.createElement("div"));
					newFavoriteGroup.addClass("bfi-singlefg bfi-iconcontainer bfi-sendto-favorite");
					newFavoriteGroup.attr("data-newfavoritegroup", "0");
					newFavoriteGroup.attr("data-itemid", elm.ItemId );
					newFavoriteGroup.attr("data-itemname", elm.attr("data-itemname"));
					newFavoriteGroup.attr("data-itemurl", elm.attr("data-itemurl"));
					newFavoriteGroup.attr("data-groupid", currFavGroup.FavoriteGroupId);
					newFavoriteGroup.attr("data-itemtype", elm.attr("data-itemtype"));
					newFavoriteGroup.attr("data-startdate", elm.attr("data-startdate"));
					newFavoriteGroup.attr("data-enddate", elm.attr("data-enddate"));
					newFavoriteGroup.attr("data-favoriteid", favoriteId);
					newFavoriteGroup.attr("data-operation-type", favoriteId>0?"remove":"add");
					newFavoriteGroup.attr("data-fromtravelplanner", "0");
					newFavoriteGroup.html("<i class='fa "+(favoriteId>0?classIconselected:classIcon)+"'></i>&nbsp;" + currFavGroup.Name);
					dialogContainer.append(newFavoriteGroup);
				});
		}else{
					var newFavoriteGroup = jQuery(document.createElement("div"));
					newFavoriteGroup.addClass("bfi-singlefg bfi-iconcontainer bfi-sendto-favorite");
					newFavoriteGroup.attr("data-newfavoritegroup", "0");
					newFavoriteGroup.attr("data-itemid", elm.ItemId );
					newFavoriteGroup.attr("data-itemname", elm.attr("data-itemname"));
					newFavoriteGroup.attr("data-itemurl", elm.attr("data-itemurl"));
					newFavoriteGroup.attr("data-groupid", 0);
					newFavoriteGroup.attr("data-itemtype", elm.attr("data-itemtype"));
					newFavoriteGroup.attr("data-startdate", elm.attr("data-startdate"));
					newFavoriteGroup.attr("data-enddate", elm.attr("data-enddate"));
					newFavoriteGroup.attr("data-favoriteid", 0);
					newFavoriteGroup.attr("data-operation-type","add");
					newFavoriteGroup.attr("data-fromtravelplanner", "0");
					newFavoriteGroup.html("<i class='fa "+classIcon+"'></i>&nbsp;" + currFavGroup.Name);
					dialogContainer.append(newFavoriteGroup);
		}

				var newFavoriteGroupAdd = jQuery(document.createElement("div"));
				newFavoriteGroupAdd.addClass("bfi-singlefg bfi-sendto-favorite bfi-add-favgroup");
				newFavoriteGroupAdd.attr("data-newfavoritegroup", "1");
				newFavoriteGroupAdd.attr("data-itemid", elm.ItemId );
				newFavoriteGroupAdd.attr("data-itemname", elm.attr("data-itemname"));
				newFavoriteGroupAdd.attr("data-itemurl", elm.attr("data-itemurl"));
				newFavoriteGroupAdd.attr("data-groupid", 0);
				newFavoriteGroupAdd.attr("data-itemtype", elm.attr("data-itemtype"));
				newFavoriteGroupAdd.attr("data-startdate", elm.attr("data-startdate"));
				newFavoriteGroupAdd.attr("data-enddate", elm.attr("data-enddate"));
				newFavoriteGroupAdd.attr("data-favoriteid", 0);
				newFavoriteGroupAdd.attr("data-operation-type","addgroup");
				newFavoriteGroupAdd.attr("data-fromtravelplanner", "0");
				newFavoriteGroupAdd.html("<i class='fa "+classIcon+"'></i>&nbsp;" + '<input type="text" class="bfi-form-control bfi-form-add-favgroup" '+ (bfi_variables.bfi_userLogged==0?' disabled ':'') +' placeholder="'+bfi_variables.bfi_txtFavAddList+'" />' );
				dialogContainer.append(newFavoriteGroupAdd);


//	if (bookingfor.mobileViewMode) {
		if (typeof tmpDialogFavoriteOpen !== 'undefined' && tmpDialogFavoriteOpen.hasClass("ui-dialog-content")) {
			tmpDialogFavoriteOpen.dialog("close").dialog('destroy');
		}
		tmpDialogFavoriteOpen= jQuery('<div></div>').html(
				dialogContainer.html()
			).dialog({
				closeText: "",
				title: bfi_variables.bfi_txtFavTitle,
				dialogClass: 'bfi-dialog',
				position: {
					my: "center top",
					at: "center bottom",
					of: elm
				},
				clickOutside: true,
			});
		tmpDialogFavoriteOpen.dialog('open');//	} else {
//		elm.addClass("bfi-favselected");
//		if (elm.parent().find(".bfi-favoritegroups-container").length) {
//			elm.parent().find(".bfi-favoritegroups-container").show();
//		}
//	}

//	jQuery(this).addClass("bfi-favclicked");
//	if (jQuery(this).hasClass(".bfi-sendto-favorite")) return;
//	jQuery(this).parent().find(".bfi-favoritegroups-container").show();
	return false;
});

//jQuery(document).on('click tap', ".bfi-sendto-favorite", function (e) {
//	e.preventDefault();
//	var elm = jQuery(this);
//
//	var icn = elm.find("i:visible").first();
//	icn.addClass("bfiimgspinH");
//	var favAction = "AddItemToFavorites";
//	if (elm.attr("data-favoriteid")) {
//		favAction = "RemoveItemToFavorites";
//	}
//	jQuery.ajax({
//		cache: false,
//		type: 'POST',
//		url: bookingfor.getActionUrl(null, null, favAction),
//		data: {
//			itemid: parseInt(elm.attr("data-itemid")),
//			favoriteid: parseInt(elm.attr("data-favoriteid")),
//			itemtypeid: parseInt(elm.attr("data-itemtype")),
//			itemurl: elm.attr("data-itemurl"),
//			itemname: elm.attr("data-itemname"),
//			groupid: elm.attr("data-groupid").length ? parseInt(elm.attr("data-groupid")) : null,
//			startdate: elm.attr("data-startdate"),
//			enddate: elm.attr("data-enddate"),
//		},
//		success: function (data) {
//			icn.removeClass("bfiimgspinH");
//			if (favAction == "RemoveItemToFavorites") {
//				elm.removeClass("bfi-favclicked");
//				elm.removeAttr("data-favoriteid");
//			}
//			//			if (data == 1) {
//			//				alert("Favorite successfully added!");
//			//			} else if (data == -1) {
//			//				alert("Favorite already added!");
//			//			} else {
//			//				alert("You cannot add a favorite!");
//			//			}
//			bfi_getFavorites();
//		}
//	});
//});
    jQuery(document).on("keypress", ".bfi-form-add-favgroup", function (e) {
	var key = e.which;
	if(key == 13)  // the enter key code
	{
		var elm = jQuery(this);
		if (elm.val() == "")
		{
			return false;
		}
        elm.closest(".bfi-sendto-favorite").find("i").trigger("click");
	}
  });

    jQuery(document).on("click tap", ".bfi-sendto-favorite i", function (e) {
        e.preventDefault();
        var elmIcon = jQuery(this);
		var classIcon = "fa-square-o";
		var classIconselected = "fa-check-square";
		var classIconLoagind = "fa-spinner fa-pulse";

		elmIcon.removeClass(classIcon);
		elmIcon.removeClass(classIconselected);
        elmIcon.addClass(classIconLoagind);

		var elm = elmIcon.closest(".bfi-sendto-favorite");

        if (elm.attr("data-fromtravelplanner") == "1" && !confirm("Sei sicuro di voler eliminare?")) {
            return;
        }

        if (elm.attr("data-fromtravelplanner") == "1") {
            jQuery("#travelplanner").block({ message: '' });
        }
        switch (elm.attr("data-operation-type")) {
            case "addgroup":
                var newGroupName = elm.closest(".bfi-add-favgroup").find("input[type=text]").val();
                if (newGroupName !== "")
                {
					jQuery.post(bookingfor.getActionUrl(null, null,"AddFavoriteGroup"), {
						name: newGroupName
					}, function (data) {
						elmIcon.removeClass(classIconLoagind);
						elmIcon.addClass(classIcon);
						if (elm.attr("data-fromtravelplanner") == "1") {
							jQuery("#travelplanner").unblock();
						}
						data = Number(data || 0);
						if (data > 0) {
							elm.closest(".bfi-icon-favorite").addClass("bfi-favselected");
	//                        jQuery.each(jQuery(".bfi-favoritegroups-container"), function (i, itm) {
								
								var newFavoriteGroup = jQuery(document.createElement("div"));
								newFavoriteGroup.addClass("bfi-singlefg bfi-iconcontainer bfi-sendto-favorite")
								newFavoriteGroup.attr("data-newfavoritegroup", "0");
								newFavoriteGroup.attr("data-itemid", elm.attr("data-itemid"));
								newFavoriteGroup.attr("data-itemname", elm.attr("data-itemname"));
								newFavoriteGroup.attr("data-itemurl", elm.attr("data-itemurl"));
								newFavoriteGroup.attr("data-groupid", data + "");
								newFavoriteGroup.attr("data-itemtype", elm.attr("data-itemtype"));
								newFavoriteGroup.attr("data-startdate", elm.attr("data-startdate"));
								newFavoriteGroup.attr("data-enddate", elm.attr("data-enddate"));
								newFavoriteGroup.attr("data-favoriteid", "0");
								newFavoriteGroup.attr("data-operation-type", "add");
								newFavoriteGroup.attr("data-fromtravelplanner", "0");
								newFavoriteGroup.html("<i class='fa "+ classIcon +"'></i>&nbsp;" + newGroupName);
								elm.closest(".ui-dialog-content").find(".bfi-singlefg:last").before(newFavoriteGroup);
	//                        });
							elm.closest(".ui-dialog-content").find(".bfi-sendto-favorite[data-groupid=" + data + "] i").trigger("click");
							elm.closest(".bfi-add-favgroup").find("input[type=text]").val("");
						} else {
							alert("You cannot add a favorite!");
						}
						bfi_getFavorites();
					});
                } else {
						elmIcon.removeClass(classIconLoagind);
						elmIcon.addClass(classIcon);
				}

                break;
            case "add":
                jQuery.post(bookingfor.getActionUrl(null, null,"AddItemToFavorites"), {
                    itemid: parseInt(elm.attr("data-itemid")),
                    itemtypeid: parseInt(elm.attr("data-itemtype")),
                    itemurl: elm.attr("data-itemurl"),
                    itemname: elm.attr("data-itemname"),
                    groupid: elm.attr("data-groupid").length ? parseInt(elm.attr("data-groupid")) : null,
                    startdate: elm.attr("data-startdate"),
                    enddate: elm.attr("data-enddate"),
                    hasfromtime: elm.attr("data-hasfromtime") == "1",
                    hastotime: elm.attr("data-hastotime") == "1",
                }, function (data) {
                    if (elm.attr("data-fromtravelplanner") == "1") {
                        jQuery("#travelplanner").unblock();
                    }
                    elm.closest(".bfi-icon-favorite").addClass("bfi-favselected");
                    if (data > 1) {
                        elm.attr("data-favoriteid", data);
                        elm.attr("data-operation-type", "remove");
						elmIcon.removeClass(classIconLoagind);
		                elmIcon.addClass(classIconselected);
                        if (elm.hasClass("bfi-iconcontainer")) {
							elm.addClass("bfi-favselected");
						}
                    } else if (data == -1) {
                        alert("Favorite already added!");
                    } else {
                        alert("You cannot add a favorite!");
                    }
					bfi_getFavorites();
                });
                break;
            case "remove":
                jQuery.post(bookingfor.getActionUrl(null, null, "RemoveItemFromFavorites"), {
                    favoriteid: parseInt(elm.attr("data-favoriteid")),
                    groupid: elm.attr("data-groupid").length ? parseInt(elm.attr("data-groupid")) : null,
                }, function (data) {
                    if (elm.attr("data-fromtravelplanner") == "1") {
                        jQuery("#travelplanner").unblock();
                    }
                    if (elm.hasClass("bfi-iconcontainer")) elm.removeClass("bfi-favselected");
                    elm.closest(".bfi-icon-favorite").removeClass("bfi-favselected");
					var currLink = jQuery(".bfi-favclicked[data-itemid='" + elm.attr("data-itemid") + "'][data-itemtype='" + elm.attr("data-itemtype")+ "']").removeClass("bfi-favclicked");

					if (data == 1) {
                        if (elm.attr("data-fromtravelplanner") == "1") {
//                            var referenceContainer = elm.closest(".bfi-favgroupitems");
//                            var prevCounter = parseInt(elm.closest(".itemtype").find(".bfi-favgroup-counter").text());
//                            prevCounter--;
//                            elm.closest(".itemtype").find(".bfi-favgroup-counter").text(prevCounter);
//                            if (referenceContainer.hasClass('slick-initialized')) { referenceContainer.slick('unslick'); }
//                            referenceContainer.find(".favitem[data-favoriteid=" + elm.attr("data-favoriteid") + "]").remove();
//                            referenceContainer.slick({
//                                infinite: true,
//                                slidesToShow: 3,
//                                slidesToScroll: 1,
//                                adaptiveHeight: true,
//                                swipeToSlide: true,
//                                prevArrow: '<a class="slick-prev"></a>',
//                                nextArrow: '<a class="slick-next"></a>',
//                                responsive: [{
//                                    breakpoint: 1500,
//                                    settings: {
//                                        slidesToShow: 2,
//                                        slidesToScroll: 1,
//                                        infinite: true,
//                                    }
//                                }, {
//                                    breakpoint: 1200,
//                                    settings: {
//                                        slidesToShow: 1,
//                                        slidesToScroll: 1
//                                    }
//                                }]
//                            });
                        } else {
							elmIcon.removeClass(classIconLoagind);
							elmIcon.addClass(classIcon);
                            elm.attr("data-favoriteid", "0");
                            elm.attr("data-operation-type", "add");
                        }
                    } else if (data == -1) {
                        //alert("Favorite already removed!");
                    } else {
                        alert("Operation not allowed!");
                    }
					bfi_getFavorites();
                });
                break;
        }
    });

/*--------------------------------------------- event    */
jQuery(document).ready(function () {
	//		bfi_consolelog("bfi-form-event");
	// block zoom in mobile device
	jQuery("[name='viewport']").attr('content', 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no');
	bfi_setFavorites();
	bfiShowLastMerchants('.bfilastmerchants');

	jQuery(".bfi-form-infocontacts").each(function (i, currForm) { //infocontact
		var currCheckin = jQuery(currForm).find("input[name='checkin']").first();
		var currCheckout = jQuery(currForm).find("input[name='checkout']").first();
		if (currCheckin.length && currCheckout.length)
		{
			currCheckin.datepicker({
				dateFormat: "dd/mm/yy",
				numberOfMonths: bfi_variables.bfi_numberOfMonths,
				minDate: '+0d',
				onClose: function (dateText, inst) {
					jQuery(this).attr("disabled", false);
				},
				beforeShow: function (dateText, inst) {
					var currTmpForm = jQuery(this).closest("form");
					bfidpmode = 'checkin';
					jQuery(this).attr("disabled", true);
					jQuery(inst.dpDiv).addClass('bfi-calendar');
					setTimeout(function () {
						bfi_updateTitle(currTmpForm, "bfi-checkin", "bfi-checkout", "Checkin")
					}, 1);
					var windowsize = jQuery(window).width();
					jQuery(inst.dpDiv)[0].className = jQuery(inst.dpDiv)[0].className.replace(/(^|\s)bfi-calendar-affixtop\S+/g, '');
					if (jQuery(this).closest("div.bfiAffixTop").length || windowsize < 767) {
						jQuery(inst.dpDiv).addClass('bfi-calendar-affixtop' + ((typeof currModId !== 'undefined') ? currModId : ""));
					}
				},
				onChangeMonthYear: function (dateText, inst) {
					var currTmpForm = jQuery(this).closest("form");
					setTimeout(function () { bfi_updateTitle(currTmpForm, "bfi-checkin", "bfi-checkout", "Checkin"); }, 1);
				},
				//showOn: "button", 
				beforeShowDay: function (date) {
					var currTmpForm = jQuery(this).closest("form");
					return bfi_closed(date, currTmpForm);
				},
				//buttonText: "<div class='checkinli'><span class='bfi-weekdayname'> </span> <span class='bfi-year'> </span></div>", 
				onSelect: function (date, inst) {
					var currTmpForm = jQuery(this).closest("form");
					bfi_printChangedDate(currTmpForm);
					setTimeout(function () { currCheckout.datepicker("show"); }, 1);
					jQuery(this).trigger("change");
				},
				firstDay: 1
			});
			/** **/
			currCheckout.datepicker({
				dateFormat: "dd/mm/yy",
				minDate: '+1d',
				numberOfMonths: bfi_variables.bfi_numberOfMonths,
				onClose: function (dateText, inst) {
					jQuery(this).attr("disabled", false);
					bfi_printChangedDate(jQuery(this).closest("form"));
				},
				beforeShow: function (dateText, inst) {
					var currTmpForm = jQuery(this).closest("form");
					var currTmpCheckin = jQuery(currTmpForm).find("input[name='checkin']").first();
					var date = currTmpCheckin.val();
					bfi_checkDate(jQuery, currTmpCheckin, date);

					bfidpmode = 'checkout';
					jQuery(this).attr("disabled", true);
					jQuery(inst.dpDiv).addClass('bfi-calendar');
					setTimeout(function () { bfi_updateTitle(currTmpForm, "bfi-checkout", "bfi-checkin", "Checkout"); }, 1);
					var windowsize = jQuery(window).width();
					jQuery(inst.dpDiv)[0].className = jQuery(inst.dpDiv)[0].className.replace(/(^|\s)bfi-calendar-affixtop\S+/g, '');
					if (jQuery(this).closest("div.bfiAffixTop").length || windowsize < 767) {
						jQuery(inst.dpDiv).addClass('bfi-calendar-affixtop' + ((typeof currModId !== 'undefined') ? currModId : ""));
					}
					bfi_printChangedDate(currTmpForm);

				},
				onSelect: function (date, inst) {
					bfi_printChangedDate(jQuery(this).closest("form"));
					jQuery(this).trigger("change");
				},
				onChangeMonthYear: function (dateText, inst) {
					var currTmpForm = jQuery(this).closest("form");
					setTimeout(function () { bfi_updateTitle(currTmpForm, "bfi-checkout", "bfi-checkin", "Checkout"); }, 1);
				},
				minDate: '+0d',
				//showOn: "button", 
				beforeShowDay: function (date) {
					var currTmpForm = jQuery(this).closest("form");
					return bfi_closed(date, currTmpForm);
				},
				//buttonText: "<div class='checkoutli'><span class='bfi-weekdayname'> </span> <span class='bfi-year'>aaa </span></div>", 
				firstDay: 1
			});
			/** **/
			if (typeof bfi_printChangedDate !== "undefined") { bfi_printChangedDate(currForm); }
		}
		jQuery(currForm).validate(
			{
				invalidHandler: function (form, validator) {
					var errors = validator.numberOfInvalids();
					if (errors) {
						validator.errorList[0].element.focus();
					}
				},
				errorClass: "bfi-error",
				highlight: function (label) {
				},
				success: function (label) {
					jQuery(label).remove();
				},
				cache : false,
				submitHandler: function (form) {
					var $form = jQuery(form);
							if (typeof grecaptcha === 'object') {
								switch (bfi_variables.googleRecaptchaVersion.toLowerCase()) {
									case 'v3': 
										grecaptcha.ready(function() {
											grecaptcha.execute(bfi_variables.googleRecaptchaKey, {action: 'submit'}).then(function(token) {
												 // add token to form
								                $form.prepend('<input type="hidden" name="g-recaptcha-response" value="' + token + '">');
											});
										});
										break;
									default: 
										var currRecaptcha = $form.find(".bfi-recaptcha").first();
										var currRecaptchaId = currRecaptcha.id;
										var response = grecaptcha.getResponse(window.bfirecaptcha[currRecaptchaId]);
										if(response.length == 0) {
											jQuery('#recaptcha-error-'+currRecaptchaId).show();
											return false;
										}
										else {
											jQuery('#recaptcha-error-'+currRecaptchaId).hide();
										}					 
										break;
								}
							}

					var $btnresource = $form.find(".bfi-btnsendform").first();
					if ($form.valid()) {
						if ($form.data('submitted') === true) {
							return false;
						} else {
							// Mark it so that the next submit can be ignored
							$form.data('submitted', true);
							var currDateFormat = "dd/mm/yy";
							currCheckin.datepicker("option", "dateFormat", currDateFormat);
							currCheckout.datepicker("option", "dateFormat", currDateFormat);

							var isMacLike = navigator.platform.match(/(Mac|iPhone|iPod|iPad)/i) ? true : false;
							var isIOS = navigator.platform.match(/(iPhone|iPod|iPad)/i) ? true : false;
							if (!isMacLike) {
								var iconBtn = $btnresource.find("i").first();
								iconBtn.removeClass("fa-search").addClass("fa-spinner fa-spin ");
								$btnresource.prop('disabled', true);

							}
							form.submit();
						}

					}
				}

			});

		if (!!jQuery.uniform){
			jQuery( window ).load(function() {
				jQuery.uniform.restore(jQuery(currForm).find("select"));
				jQuery.uniform.restore(jQuery(currForm).find("input"));
			});
		}

	});
	jQuery(".bfi-form-event").each(function (i, currForm) {
		//var currSearchtypetab = jQuery(currForm).closest(".bfi-mod-bookingforsearch").attr("data-searchtypetab");
		var currCheckin = jQuery(currForm).find("input[name='checkin']").first();
		var currCheckout = jQuery(currForm).find("input[name='checkout']").first();

		currCheckin.datepicker({
			dateFormat: "dd/mm/yy",
			numberOfMonths: bfi_variables.bfi_numberOfMonths,
			minDate: '+0d',
			onClose: function (dateText, inst) {
				jQuery(this).attr("disabled", false);
			},
			beforeShow: function (dateText, inst) {
				var currTmpForm = jQuery(this).closest("form");
				bfidpmode = 'checkin';
				jQuery(this).attr("disabled", true);
				jQuery(inst.dpDiv).addClass('bfi-calendar');
				setTimeout(function () {
					bfi_updateTitle(currTmpForm, "bfi-checkin", "bfi-checkout", "Checkin")
				}, 1);
				var windowsize = jQuery(window).width();
				jQuery(inst.dpDiv)[0].className = jQuery(inst.dpDiv)[0].className.replace(/(^|\s)bfi-calendar-affixtop\S+/g, '');
				if (jQuery(this).closest("div.bfiAffixTop").length || windowsize < 767) {
					jQuery(inst.dpDiv).addClass('bfi-calendar-affixtop' + ((typeof currModId !== 'undefined') ? currModId : ""));
				}
			},
			onChangeMonthYear: function (dateText, inst) {
				var currTmpForm = jQuery(this).closest("form");
				setTimeout(function () { bfi_updateTitle(currTmpForm, "bfi-checkin", "bfi-checkout", "Checkin"); }, 1);
			},
			//showOn: "button", 
			beforeShowDay: function (date) {
				var currTmpForm = jQuery(this).closest("form");
				return bfi_closed(date, currTmpForm);
			},
			//buttonText: "<div class='checkinli'><span class='bfi-weekdayname'> </span> <span class='bfi-year'> </span></div>", 
			onSelect: function (date, inst) {
				var currTmpForm = jQuery(this).closest("form");
				bfiresetsearchterm(currTmpForm);
				bfi_printChangedDate(currTmpForm);
				setTimeout(function () { currCheckout.datepicker("show"); }, 1);
				jQuery(this).trigger("change");
			},
			firstDay: 1
		});
		/** **/

		currCheckout.datepicker({
			dateFormat: "dd/mm/yy",
			numberOfMonths: bfi_variables.bfi_numberOfMonths,
			onClose: function (dateText, inst) {
				jQuery(this).attr("disabled", false);
				bfi_printChangedDate(jQuery(this).closest("form"));
			},
			beforeShow: function (dateText, inst) {
				var currTmpForm = jQuery(this).closest("form");
				var currTmpCheckin = jQuery(currTmpForm).find("input[name='checkin']").first();
				var date = currTmpCheckin.val();
				bfi_checkDate(jQuery, currTmpCheckin, date);

				bfidpmode = 'checkout';
				jQuery(this).attr("disabled", true);
				jQuery(inst.dpDiv).addClass('bfi-calendar');
				setTimeout(function () { bfi_updateTitle(currTmpForm, "bfi-checkout", "bfi-checkin", "Checkout"); }, 1);
				var windowsize = jQuery(window).width();
				jQuery(inst.dpDiv)[0].className = jQuery(inst.dpDiv)[0].className.replace(/(^|\s)bfi-calendar-affixtop\S+/g, '');
				if (jQuery(this).closest("div.bfiAffixTop").length || windowsize < 767) {
					jQuery(inst.dpDiv).addClass('bfi-calendar-affixtop' + ((typeof currModId !== 'undefined') ? currModId : ""));
				}
				bfi_printChangedDate(currTmpForm);

			},
			onSelect: function (date, inst) {
				var currTmpForm = jQuery(this).closest("form");
				bfiresetsearchterm(currTmpForm);
				bfi_printChangedDate(currTmpForm);
				jQuery(this).trigger("change");
			},
			onChangeMonthYear: function (dateText, inst) {
				var currTmpForm = jQuery(this).closest("form");
				setTimeout(function () { bfi_updateTitle(currTmpForm, "bfi-checkout", "bfi-checkin", "Checkout"); }, 1);
			},
			minDate: '+0d',
			//showOn: "button", 
			beforeShowDay: function (date) {
				var currTmpForm = jQuery(this).closest("form");
				return bfi_closed(date, currTmpForm);
			},
			//buttonText: "<div class='checkoutli'><span class='bfi-weekdayname'> </span> <span class='bfi-year'>aaa </span></div>", 
			firstDay: 1
		});
		/** **/

		if (typeof bfi_printChangedDate !== "undefined") { bfi_printChangedDate(currForm); }
		jQuery(currForm).validate(
			{
				invalidHandler: function (form, validator) {
					var errors = validator.numberOfInvalids();
					if (errors) {
						validator.errorList[0].element.focus();
					}
				},
				errorClass: "bfi-error",
				highlight: function (label) {
				},
				success: function (label) {
					jQuery(label).remove();
				},
				submitHandler: function (form) {
					var $form = jQuery(form);
					var $btnresource = $form.find(".bfi-btnsendform").first();
					if ($form.valid()) {
						if ($form.data('submitted') === true) {
							return false;
						} else {
							// Mark it so that the next submit can be ignored
							$form.data('submitted', true);
							var currDateFormat = "dd/mm/yy";
							currCheckin.datepicker("option", "dateFormat", currDateFormat);
							currCheckout.datepicker("option", "dateFormat", currDateFormat);

							var isMacLike = navigator.platform.match(/(Mac|iPhone|iPod|iPad)/i) ? true : false;
							var isIOS = navigator.platform.match(/(iPhone|iPod|iPad)/i) ? true : false;
							if (!isMacLike) {
								var iconBtn = $btnresource.find("i").first();
								iconBtn.removeClass("fa-search").addClass("fa-spinner fa-spin ");
								$btnresource.prop('disabled', true);

							}
							form.submit();
						}

					}
				}

			});
		if (!!jQuery.uniform){
			jQuery( window ).load(function() {
				jQuery.uniform.restore(jQuery(currForm).find("select"));
				jQuery.uniform.restore(jQuery(currForm).find("input"));
			});
		}
	});
});

function bfisetrange(currobj) {
	var JCurrobj = jQuery(currobj);
	var currForm = JCurrobj.closest("form");
	var currCheckin = jQuery(currForm).find("input[name='checkin']").first();
	var currCheckout = jQuery(currForm).find("input[name='checkout']").first();
	var today = new Date();
	switch (JCurrobj.val()) {
		case "today":
			currCheckin.datepicker().datepicker('setDate', today);
			currCheckout.datepicker().datepicker('setDate', today);
			break;
		case "thisweek":
			currCheckin.datepicker().datepicker('setDate', bookingfor.startOfWeek(today));
			currCheckout.datepicker().datepicker('setDate', bookingfor.endOfWeek(today));
			break;
		case "thismonth":
			currCheckin.datepicker().datepicker('setDate', bookingfor.firstdayOfMonth(today));
			currCheckout.datepicker().datepicker('setDate', bookingfor.lastdayOfMonth(today));
			break;
		case "nextmonth":
			today = bookingfor.dateAdd(today, "month", 1);
			currCheckin.datepicker().datepicker('setDate', bookingfor.firstdayOfMonth(today));
			currCheckout.datepicker().datepicker('setDate', bookingfor.lastdayOfMonth(today));
			break;
		case "nextweek":
			today = bookingfor.dateAdd(today, "day", 7);
			currCheckin.datepicker().datepicker('setDate', bookingfor.startOfWeek(today));
			currCheckout.datepicker().datepicker('setDate', bookingfor.endOfWeek(today));
			break;
		default:
	}
	// reset campo ricerca testuale
	bfiresetsearchterm(currForm);
}
function bfiresetsearchterm(currForm) {
	currForm.find("[name=searchterm],[name=searchTermValue],[name=stateIds],[name=regionIds],[name=cityIds],[name=zoneIds],[name=categoryIds],[name=eventId],[name=eventTagId],[name=pointOfInterestId]").val("");
}


/*------------MAP Function-------------*/
bookingfor.bfiOpenGoogleMapSearch = function () {
	var r = jQuery.Deferred();
	if (jQuery("#bfi-maps-popup").length == 0) {
		jQuery("body").append("<div id='bfi-maps-popup'></div>");
	}
	if (bfi_variables.bfiMapsFree) {
		if (typeof mapSearch !== 'object') {
			Leaflet = L.noConflict();
			mapSearch = Leaflet.map('bfi-maps-popup').setView([bfi_variables.bfi_mapx, bfi_variables.bfi_mapy], bfi_variables.bfi_mapstartzoom);
			mapSearch.zoomControl.setPosition('bottomright');
			var OpenStreetMap_Mapnik = Leaflet.tileLayer(bfi_variables.bfi_freemaptileurl, {
				maxZoom: 19,
				attribution: bfi_variables.bfi_freemaptileattribution
			});
			OpenStreetMap_Mapnik.addTo(mapSearch);
            // custom sidebar
			var htmlContent = '';
			var controlPosition = 'topleft';
			if (bookingfor.mobileViewMode)
			{
				 controlPosition = 'bottomleft';
				 mapSearch.removeControl(mapSearch.zoomControl);
			}
			L.control.custom({
                position: controlPosition,
                content : htmlContent,
                classes : 'bfi-maplayers',
           })
            .addTo(mapSearch);

			bookingfor.bfiLoadMarkers();
		}
	} else {
		if (typeof google !== 'object' || typeof google.maps !== 'object') {
			var script = document.createElement("script");
			script.type = "text/javascript";
			script.src = bfi_variables.bfi_googlemapsscript + "&callback=bookingfor.bfihandleApiReadySearch";
			document.body.appendChild(script);
		} else {
			if (typeof mapSearch !== 'object') {
				bookingfor.bfihandleApiReadySearch();
			}
		}
	}
	
	return r;
};
bookingfor.bfihandleApiReadySearch = function () {
	if (!bfi_variables.bfiMapsFree && typeof MarkerWithLabel !== 'function') {
		jQuery.getScript(bfi_variables.markerwithlabel, function (data, textStatus, jqxhr) {

			myLatlngsearch = new google.maps.LatLng(bfi_variables.bfi_mapx, bfi_variables.bfi_mapy);
			var myOptions = {
				zoom: bfi_variables.bfi_mapstartzoom,
				center: myLatlngsearch,
				mapTypeId: google.maps.MapTypeId.ROADMAP,
				mapTypeControl: false,
			}
			mapSearch = new google.maps.Map(document.getElementById("bfi-maps-popup"), myOptions);
			bookingfor.bfiLoadMarkers();
			mapSearch.addListener('click', function (e) {
				if (typeof infowindow!== 'undefined') infowindow.set("map", null);
			});
		});
	}
}
bookingfor.bfiLoadMarkers = function () {
	var isVisible = jQuery('#bfi-maps-popup').is(":visible");
	bookingfor.waitSimpleBlock(jQuery('#bfi-maps-popup'));
	if (mapSearch != null && !bookingfor.markersLoaded && isVisible) {
		if (typeof oms !== 'object') {
			jQuery.getScript(bfi_variables.urlOmsScript, function (data, textStatus, jqxhr) {
				var bounds;
				oms = new OverlappingMarkerSpiderfier(mapSearch, {
					keepSpiderfied: true,
					nearbyDistance: 1,
					markersWontHide: true,
					markersWontMove: true
				});
				if (bfi_variables.bfiMapsFree) {
					bounds = new Leaflet.latLngBounds();
					oms.addListener('click', function (marker) {
						bookingfor.bfiShowMarkerInfo(marker);
					});
				} else {
					bounds = new google.maps.LatLngBounds();
					oms.addListener('click', function (marker) {
						bookingfor.bfiShowMarkerInfo(marker);
					});
				}

				if (!bookingfor.markersLoading) {
					if (typeof listResourceMaps !== 'undefined' && listResourceMaps != null) {
						bookingfor.bfiCreateMarkers(listResourceMaps||[], oms, bounds, mapSearch);
						if (oms.getMarkers().length > 0) {
							mapSearch.fitBounds(bounds);
						}
					}
					bookingfor.markersLoaded = true;
					jQuery(jQuery('#bfi-maps-popup')).unblock();
					if (bookingfor.bfiCurrMarkerId > 0) {
						setTimeout(function () {
							bookingfor.bfiShowMarker(bookingfor.bfiCurrMarkerId);
							bookingfor.bfiCurrMarkerId = 0;
						}, 10);
					}
					if (listResourceMaps.length == 1)
					{
						bookingfor.bfiShowMarker(listResourceMaps[0].Id);
					}
					bookingfor.bfiLoadSearchMap(mapSearch);

//					bookingfor.bfiLoadPois(mapSearch);


				}
				bookingfor.markersLoading = true;

			});
		}
	}
};

bookingfor.bfiCreateMarkers = function (data, oms, bounds, currentMap) {
	jQuery.each(data, function (key, val) {
		if (val.Long == '' || val.Lat == null || val.Long == null || (val.Lat == 0 && val.Long == 0))
			return true;

		var marker;
		var loc;
		var instanceclass;
		if (bfi_variables.bfiMapsFree) {
			loc = new L.LatLng(val.Lat, val.Long);
			//marker = new L.Marker(loc);
			if (val.Price != undefined && val.Price != null && val.Price != 0) {
				instanceclass = "blutheme";
				var bfiIcon = Leaflet.divIcon({
					iconSize: null,
					html: '<div class="bfi-map-label ' + instanceclass + '" id="bfi-marker-icon-'+val.Id+'"><div class="bfi-map-label-content bfi_' + val.Currencyclass + '">' + bookingfor.priceFormat(val.Price, 2, ',', '.') + '</div><div class="bfi-map-label-arrow"></div></div>'
				});
				marker = new L.Marker(loc, { icon: bfiIcon });
			} else {
				if (val.MarkerType != undefined && val.MarkerType != null )
				{
					var additionalClass = "";
					if (val.CssClass != undefined && val.CssClass != null)
					{
						additionalClass = val.CssClass ;
					}
					var bfiIcon = Leaflet.divIcon({
						iconSize: [20, 40],
						iconAnchor: [10, 39],
						html: '<i class="fa fa-map-marker '+additionalClass+'" id="bfi-marker-icon-'+val.Id+'"></i>',
						className: 'bfi-mapIcon bfi-mapIcon0'
					});  // default value
					if (val.MarkerType =="0")
					{
						var bfiIcon = Leaflet.divIcon({
							iconSize: [20, 40],
							iconAnchor: [10, 39],
							html: '<i class="fa fa-map-marker '+additionalClass+'" id="bfi-marker-icon-'+val.Id+'"></i>',
							className: 'bfi-mapIcon bfi-mapIcon0'
						});
					}
					if (val.MarkerType =="1")
					{
						var bfiIcon = Leaflet.divIcon({
							iconSize: [20, 40],
							iconAnchor: [10, 39],
							html: '<i class="fa fa-map-marker '+additionalClass+'" id="bfi-marker-icon-'+val.Id+'"></i>',
							className: 'bfi-mapIcon bfi-mapIcon1'
						});
					}
					if (val.MarkerType =="2") //evento 
					{
						var bfiIcon = Leaflet.divIcon({
							iconSize:null,
							html: '<div class="bfi-map-label blulighttheme" id="bfi-marker-icon-'+val.Id+'"><div class="bfi-map-label-content bfi-map-event">'+val.NameDay+' <div class="bfi-map-event-day">'+val.Day+'</div>'+val.Month+' </div><div class="bfi-map-label-arrow"></div></div>'
						});
					}
					if (val.MarkerType =="3") //poi 
					{
						var bfiIcon = Leaflet.divIcon({
							iconSize:null,
							html: '<div class="bfi-map-label blulighttheme" id="bfi-marker-icon-'+val.Id+'"><div class="bfi-map-label-content bfi-map-poi">'+val.Name+'</div><div class="bfi-map-label-arrow"></div></div>'
						});

					
					}
					marker = new L.Marker(loc, { icon: bfiIcon });
				}else{
					var additionalClass = "";
					var bfiIcon = Leaflet.divIcon({
						iconSize: [20, 40],
						iconAnchor: [10, 39],
						html: '<i class="fa fa-map-marker '+additionalClass+'" id="bfi-marker-icon-'+val.Id+'"></i>',
						className: 'bfi-mapIconDefault'
					});  // default value
					marker = new L.Marker(loc, { icon: bfiIcon });
//					marker = new L.Marker(loc);
				}
			}
			currentMap.addLayer(marker);
		} else {
			loc = new google.maps.LatLng(val.Lat, val.Long);
			if (val.Price != undefined && val.Price != null && val.Price != 0) {
				instanceclass = " bfi-map-label bfi-googlemap blutheme";
				marker = new MarkerWithLabel({
					position: loc,
					draggable: false,
					raiseOnDrag: false,
					map: currentMap,
					labelContent: '<div class="bfi-map-label-content bfi_' + val.Currencyclass + '">' + bookingfor.priceFormat(val.Price, 2, ',', '.') + '</div><div class="bfi-map-label-arrow"></div>',
					labelAnchor: new google.maps.Point(22, 22),
					labelClass: instanceclass, // the CSS class for the label
					icon: {
						url: 'https://maps.gstatic.com/mapfiles/api-3/images/spotlight-poi2.png',
						scaledSize: new google.maps.Size(1, 1)
					},
				});
			} else {
				marker = new google.maps.Marker({
					position: loc,
					map: currentMap
				});
			}
		}
		marker.extId = val.Id;
		oms.addMarker(marker, true);
		bounds.extend(loc);

	});
};

bookingfor.bfiShowMarker = function (extId) {
	if (jQuery("#bfi-maps-popup").length == 0) {
		jQuery("body").append("<div id='bfi-maps-popup'></div>");
	}

//	if (jQuery("#bfi-maps-popup").length) {
		if (jQuery("#bfi-maps-popup").hasClass("ui-dialog-content") && jQuery("#bfi-maps-popup").dialog("isOpen")) {
			jQuery(oms.getMarkers()).each(function () {
				if (this.extId != extId) return true;
				bookingfor.bfiShowMarkerInfo(this);
				return false;
			});

		} else {
			var width = jQuery(window).width() * 0.98;
			var height = jQuery(window).height()  * 0.98;
			jQuery("#bfi-maps-popup").dialog({
				closeText: "",
				open: function (event, ui) {
					if (!bookingfor.markersLoaded) {
						bookingfor.bfiCurrMarkerId = extId;
					}
					bookingfor.bfiOpenGoogleMapSearch();
					if (!bookingfor.markersLoaded) {
						return;
					}
					jQuery(oms.getMarkers()).each(function () {
						if (this.extId != extId) return true;
						bookingfor.bfiShowMarkerInfo(this);
						return false;
					});
				},
				width: width,
				height: height,
				dialogClass: 'bfi-dialog bfi-dialog-map',
				resize: function (event, ui) {
					if (bfi_variables.bfiMapsFree) {
						mapSearch.invalidateSize();
					}
				}
			});
		}
//	}
};

bookingfor.bfiShowMarkerInfo = function (marker) {
	var customPopupOptions =
	{
		'maxWidth': '500',
		'className': 'bfi-custompopup',
		closeButton: false
	};
	var currSidebar = jQuery(".bfi-maplayers");
	var currDetails = jQuery(".bfi-maplayers-details");
	var data = jQuery("#markerInfo" + marker.extId).html();
	if (bfi_variables.bfiMapsFree) {
		jQuery('.bfi-marker-icon-selected').removeClass('bfi-marker-icon-selected');
		jQuery("#bfi-marker-icon-"+marker.extId).addClass("bfi-marker-icon-selected");
		if (jQuery("#bfi-marker-icon-"+marker.extId).parent().hasClass("bfi-mapIconDefault"))
		{
			jQuery("#bfi-marker-icon-"+marker.extId).parent().addClass("bfi-marker-icon-selected");
			jQuery("#bfi-marker-icon-"+marker.extId).removeClass("bfi-marker-icon-selected");
		}
		if (currSidebar && currSidebar.length)
		{
			if(currDetails && currDetails.length)
			{	
			}else{
				currDetails = jQuery('<div class="bfi-maplayers-details"></div>');
				currSidebar.prepend(currDetails);
			}
				currDetails.html(data);

		}else{

			marker.unbindPopup();
			//		marker.bindPopup(data).openPopup();
			marker.bindPopup(data, customPopupOptions).openPopup();
		}
		
		mapSearch.setView(marker.getLatLng());

	} else {
		//if (infowindow) infowindow.close();
		if (typeof infowindow!== 'undefined' ) infowindow.set("map", null);
		mapSearch.setCenter(marker.position);
		//infowindow = new google.maps.InfoWindow({ content: data });
		//infowindow.open(mapSearch, marker);
		Popup = bookingfor.bfiCreatePopupClass();
		infowindow = new Popup(marker.position, createElementFromHTML(data));
		infowindow.setMap(mapSearch);

	}
};

/* ------------------- Cart ------------------- */
function bfi_UpdateQuote(obj) //multiresource
{
	var totalRooms = 0;
	var totalQuote = 0;
	var totalQuoteDiscount = 0;
	var onlybookable = 0;
	var totalMaxPaxes = 0;

	var currContainer = jQuery(obj).closest(".bfi-result-list").first();
	var grpTotalPaxes = Number(currContainer.attr('data-grptotalpaxes') || 0);

	currContainer.find(".bfi-request-now").hide();
	currContainer.find(".bfi-btn-book-now").hide();

	currContainer.find("tr[id^=data-id-]").each(function (index, obj) {
		var ddlroom = jQuery(obj).find(".ddlrooms-indipendent");
		var roomTotalselected = jQuery(obj).find(".bfi-mobile-view-totalselected span");
		var roomTotalprice = jQuery(obj).find(".bfi-mobile-view-totalprice");
		var roomTotalpriceDiscounted = jQuery(obj).find(".bfi-mobile-view-totalpricediscounted");
		roomTotalprice.html(""); // reset priceview
		var nRoom = parseInt(ddlroom.val());
		var resId = jQuery(ddlroom).attr("data-resid");
		var discountRate = parseFloat(jQuery(ddlroom).attr("data-totalprice"));
		var rate = parseFloat(jQuery(ddlroom).attr("data-price"));
		var totalQuoteRoom = nRoom * rate;
		var totalQuoteRoomDiscount = nRoom * discountRate;
		if (totalQuoteRoom>0)
		{
//			if (totalQuoteRoomDiscount <= totalQuoteRoom) {
//				roomTotalpriceDiscounted.hide();
//				roomTotalprice.removeClass("bfi-red");
//			} else {
//				roomTotalpriceDiscounted.show();
//				roomTotalprice.addClass("bfi-red");
//			}

			roomTotalselected.html(nRoom);
			roomTotalpriceDiscounted.html(bookingfor.number_format(totalQuoteRoom, 2, ',', '.'));
//			roomTotalprice.html(bookingfor.number_format(totalQuoteRoom, 2, ',', '.'));
		}
		totalRooms += nRoom;
//		totalQuote += nRoom * rate;
//		totalQuoteDiscount += nRoom * discountRate;
		totalQuote += totalQuoteRoom;
		totalQuoteDiscount += totalQuoteRoomDiscount;
		jQuery(this).attr('IsSelected', (nRoom > 0));
		if (nRoom > 0) {
			onlybookable = Number(jQuery(ddlroom).attr("data-isbookable") || 0);
			var currMaxpaxes = Number(jQuery(ddlroom).attr('data-maxpaxes') || 0);
			totalMaxPaxes += currMaxpaxes * nRoom;
		}
	});

	bfi_totalRooms = totalRooms;
	bfi_totalQuote = totalQuote;
	bfi_totalQuoteDiscount = totalQuoteDiscount;

	currContainer.find(".bfi-resource-total span").html(bfi_totalRooms);
	currContainer.find(".bfi-price-total").html(bookingfor.number_format(bfi_totalQuote, 2, ',', '.'));
	currContainer.find(".bfi-discounted-price-total").html(bookingfor.number_format(bfi_totalQuoteDiscount, 2, ',', '.'));

	currContainer.find(".bfi-book-now-content").hide();
	if (!bookingfor.mobileViewMode)
	{
		currContainer.find(".bfi-request-now-first").show();
	}

	jQuery(".bfiselectrooms-error").removeClass("bfiselectrooms-error");

	if (bfi_totalRooms > 0) {
		currContainer.find(".bfi-book-now-content").show();
		currContainer.find(".bfi-request-now-first").hide();
		if (bookingfor.mobileViewMode)
		{
			currContainer.find(".bfi-book-now").addClass("bfi-sticked");
		}
		if (onlybookable == 1) {
			currContainer.find(".bfi-btn-book-now").show();
			currContainer.find(".bfi-request-now").hide();
		} else {
			currContainer.find(".bfi-btn-book-now").hide();
			currContainer.find(".bfi-request-now").show();
		}
	}

	if (bfi_totalQuoteDiscount == 0)
	{
		currContainer.find(".bfi-price-total").hide();
	}else{
		currContainer.find(".bfi-price-total").show();
	}
	if (bfi_totalQuoteDiscount <= bfi_totalQuote) {
		currContainer.find(".bfi-discounted-price-total").hide();
		currContainer.find(".bfi-price-total").removeClass("bfi-red");
	} else {
		currContainer.find(".bfi-discounted-price-total").show();
		currContainer.find(".bfi-price-total").addClass("bfi-red");
	}

	if (grpTotalPaxes > 0 && totalMaxPaxes >= grpTotalPaxes) {
		currContainer.find(".bfi-tooltip-allok-container").show("slow", function () {
			jQuery(this).delay(5000).hide(0)
		});
	} else {
		currContainer.find(".bfi-tooltip-allok-container").hide();
	}


}

function bfi_ChangeVariation(obj) //multiresource
{
	bfi_UpdateQuote(obj); //set service price default value

	var showServices = false;
	var noResources = 0;
	jQuery(".bfi-result-list .ddlrooms-indipendent ").each(function (index, objDdl) {
		var currResId = jQuery(this).attr('data-resid');
		//                var currRateplanId = jQuery(this).attr('data-ratePlanId');
		var currRateplanId = jQuery(this).attr('data-referenceid');
		var currQtSelected = jQuery(this).val();

		if (currQtSelected > 0 && jQuery(obj).closest(".bfi-result-list").find(".services-room-1-" + currResId + "-" + currRateplanId).length) {

			//					console.log("exist extras");
			var firstResourceServices = jQuery(obj).closest(".bfi-result-list").find(".services-room-1-" + currResId + "-" + currRateplanId).first();
			var currTitle = firstResourceServices.find('.bfi-resname-extra').first().html();

			for (var i = 1; i <= currQtSelected; i++) {

				firstResourceServices.find('.bfi-resname-extra').first().html((noResources + 1) + ') ' + currTitle);
				if (i != jQuery(this).val() && jQuery(obj).closest(".bfi-result-list").find(".services-room-" + i + '-' + currResId + "-" + currRateplanId).length) {

					var nextservice = firstResourceServices.clone();
					nextservice.attr('class', "services-room-" + (i + 1) + '-' + currResId + "-" + currRateplanId) + " bfi-table-responsive";
					nextservice.find(".bfi-timeslot").attr('data-sourceid', "services-room-" + (i + 1) + '-' + currResId + "-" + currRateplanId);
					nextservice.find(".ddlrooms").attr('data-sourceid', "services-room-" + (i + 1) + '-' + currResId + "-" + currRateplanId);
					nextservice.insertAfter(jQuery(obj).closest(".bfi-result-list").find(".services-room-" + i + '-' + currResId + "-" + currRateplanId));
				}
				if (jQuery(obj).closest(".bfi-result-list").find(".services-room-" + i + '-' + currResId + "-" + currRateplanId).length) {
					showServices = true;
					jQuery(obj).closest(".bfi-result-list").find(".services-room-" + i + '-' + currResId + "-" + currRateplanId).show();
					noResources++;

				}
			}
		}
	});

	if (showServices) {
		jQuery(".bfi-menu-booking a:eq(1)").removeClass(" bfi-alternative3"); //set menu to "Extra service"
		jQuery(".bfi-hideonextra").hide();

		jQuery(".bfi-table-resources-step1").hide();

		var srvtargetContainer = jQuery(obj).closest(".bfi-result-list").find(".bfi-table-resources-step2").first();
		if (srvtargetContainer.length) {
			srvtargetContainer.show();
			jQuery([document.documentElement, document.body]).animate({
				scrollTop: srvtargetContainer.offset().top
			}, 1000);
		}


		if (typeof daysToEnableTimeSlot !== 'undefined' && typeof strAlternativeDateToSearch !== 'undefined' && typeof initDatepickerTimeSlot !== 'undefined' && typeof initDatepickerTimeSlot === "function" ) {
			initDatepickerTimeSlot();
		}
		if (typeof daysToEnableTimePeriod !== 'undefined' && typeof initDatepickerTimePeriod !== 'undefined' && typeof initDatepickerTimePeriod === "function") {
			initDatepickerTimePeriod();
		}
		var currTotalExtras = jQuery(".totalextrasstay");
		if (!currTotalExtras.is(":visible")) {
			var currTableVisible = jQuery(".bfi-table-selectableprice:visible").first();
			currTableVisible.find('tr:eq(1)').find('td:last').append(currTotalExtras.clone(true));
			currTotalExtras.remove();
		}
		bfi_UpdateQuote(obj); //set service price default value

		bfi_updateQuoteService();
	}
	else {
		bookingfor.BookNow(obj);
	}
}
/* ------------------- end cart ------------------- */

/* ------------------- Carousel ------------------- */
bookingfor.carouselMerchants = function () {
	jQuery.each(jQuery('.bficarouselmerchants'), function () {
		var currCarusel = jQuery(this);
		var currID = this.id;
		var currTags = currCarusel.attr('data-tags');
		var currMaxitems = currCarusel.attr('data-maxitems');
		var currDescmaxchars = currCarusel.attr('data-descmaxchars');
		var currCols = currCarusel.attr('data-cols');
		var currTheme = currCarusel.attr('data-theme');
		var currDetails = currCarusel.attr('data-details');

		var currquery = "tags=" + currTags + "&maxitems=" + currMaxitems + "&descmaxchars=" + currDescmaxchars + "&language=" + bfi_variables.bfi_cultureCode + "&task=GetMerchantsSlick";
		jQuery.post(bfi_variables.bfi_urlCheck, currquery, function (data) {
			if (data != null) {
				var ncolslick = currCols;
				var currwidth = currCarusel.width();
				if (currwidth < 427 && currCols > 2) {
					ncolslick = 2;
				}
				if (currwidth <= 375 && currCols > 1) {
					ncolslick = 1;
				}
				//calcolo il rapporto in altezza 9/11
				minHeight = (currwidth - ((ncolslick - 1) * 10)) / ncolslick * 11 / 9;

				var carHtml = "";
				var initMerchant = new Array();
				jQuery.each(data || [], function (key, val) {
					if (bfi_variables.analyticsEnabled) {
						var obj = { name: val.Name, category: val.category, brand: val.brand, position: val.position };
						initMerchant.push(obj);
					}
					if (currTheme == 1) {

						carHtml += '';
						carHtml += '			<div class="bfi-bookingforconnector-merchants bfi-bookingforcarousel" >';
						carHtml += '				<a style="background: url(' + val.currMerchantImageUrl + ') no-repeat;background-size: cover;background-position:center center;min-height:' + minHeight + 'px;" href="' + val.routeMerchant + '" class="eectrack bfi-carouser-click" data-type="Merchant" data-id="' + val.MerchantId + '" data-index="' + val.mrcKey + '" data-itemname="' + val.merchantNameTrack + '" data-brand="' + val.merchantNameTrack + '" data-category="' + val.merchantCategoryNameTrack + '">';
						carHtml += '					<div class="bfi-slick-content">';
						carHtml += '					<div class="bfi-title">' + val.Name + '</div>';
						carHtml += '					</div>';
		if (bookingfor.mobileViewMode || bookingfor.tabletViewMode)
		{

		}else{
						carHtml += '					<div class="bfi-slick-overlay">';
						carHtml += '						<div class="bfi-slick-overlay-content">';
						carHtml += '							<div class="bfi-slick-overlay-title">' + val.Name + '</div>';
						carHtml += '							<div class="bfi-slick-overlay-description">' + val.merchantDescription + '</div>';
						carHtml += '							<div class="bfi-btn bfi-alternative5">' + currDetails + '</div>';
						carHtml += '						</div>';
						carHtml += '					</div>';
		}
						carHtml += '					<div class="bfi-icon-favorite-container  bfi-pull-right">';
						carHtml += '					<span class="bfi-icon-favorite bfi-iconcontainer" ';
						carHtml += '					          data-itemid="' + val.MerchantId + '"  ';
						carHtml += '							  data-itemname="' + val.Name + '"  ';
						carHtml += '							  data-itemurl="' + val.routeMerchant + '" ';
						carHtml += '					          data-groupid=""  ';
						carHtml += '							  data-itemtype="0" ';
						carHtml += '					          data-startdate="" ';
						carHtml += '					          data-enddate="" ';
						carHtml += '					          data-toggle="tooltip"  ';
						carHtml += '							  data-fromtravelplanner="0" ';
						carHtml += '							  data-hasfromtime="0" ';
						carHtml += '							  data-hastotime="0" ';
						carHtml += '							  data-favoriteid="0" ';
						carHtml += '							  data-operation-type="" ';
						carHtml += '							  title="Add to favorites"> ';
						carHtml += '					        <i class="fa fa-heart-o"></i>';
						carHtml += '					        <i class="fa fa-heart"></i>';
						carHtml += '					    </span>';
						carHtml += '						<div class="bfi-favoritegroups-container">';
						carHtml += '						</div>';
						carHtml += '					</div>';
						carHtml += '				</a>';
						carHtml += '			</div>	';

					} else {

						carHtml += '';
						carHtml += '			<div class="bfi-bookingforconnector-merchants bfi-bookingforcarousel" >';
						carHtml += '				<div class="bfi-row" style="height:100%">';
						carHtml += '					<div class="bfi-row" >';
						carHtml += '						<div class="bfi-col-md-12"><a href="' + val.routeMerchant + '" class="eectrack" data-type="Merchant" data-id="' + val.MerchantId + '" data-index="' + val.mrcKey + '" data-itemname="' + val.merchantNameTrack + '" data-brand="' + val.merchantNameTrack + '" data-category="' + val.merchantCategoryNameTrack + '"><img src="' + val.currMerchantImageUrl + '" class="bfi-img-responsive center-block" /></a>';
						carHtml += '						</div>';
						carHtml += '					</div>';
						carHtml += '					<div class="bfi-row" >';
						carHtml += '						<div class="bfi-col-md-12 bfi-item-title" style="padding: 10px!important;">';
						carHtml += '						<a class="eectrack" href="' + val.routeMerchant + '" id="nameAnchor' + val.MerchantId + '" data-type="Merchant" data-id="' + val.MerchantId + '" data-index="' + val.mrcKey + '" data-itemname="' + val.merchantNameTrack + '" data-brand="' + val.merchantNameTrack + '" data-category="' + val.merchantCategoryNameTrack + '">' + val.Name + '</a> ';
						if (val.rating > 0) {
							carHtml += '						<span class="bfi-item-rating">';
							for (i = 0; i < val.rating; i++) {
								carHtml += '						<i class="fa fa-star"></i>';
							}
							carHtml += '						</span>';
						}
						if (val.hasSuperior) {
							carHtml += '						&nbsp;S';
						}
						carHtml += '						</div>';
						carHtml += '					</div>';
						carHtml += '					<div class="bfi-row bfi-hide" >';
						carHtml += '						<div class="bfi_merchant-description bfi-col-md-12" style="padding-left: 10px!important;padding-right: 10px!important;">';
						carHtml += '							<a href="' + val.routeMerchant + '" id="nameAnchor' + val.MerchantId + '" class="eectrack" data-type="Merchant" data-id="' + val.MerchantId + '" data-index="' + val.mrcKey + '" data-itemname="' + val.merchantNameTrack + '" data-brand="' + val.merchantNameTrack + '" data-category="' + val.merchantCategoryNameTrack + '">' + val.Name + '</a> ';
						carHtml += '						</div>';
						carHtml += '					</div>';
						carHtml += '					<div class="bfi-row" >';
						carHtml += '						<div class="bfi_merchant-description bfi-col-md-12" style="padding: 10px!important;" id="descr' + val.MerchantId + '">' + val.merchantDescription + '</div>';
						carHtml += '					</div>';
						carHtml += '					<div class="bfi-row secondarysection">';
						carHtml += '						<div class="bfi-col-md-1  secondarysectionitem">';
						carHtml += '							&nbsp;';
						carHtml += '						</div>';
						carHtml += '						<div class="bfi-col-md-11 secondarysectionitem" style="padding: 10px!important;">';
						carHtml += '								<a href="' + val.routeMerchant + '" class="bfi-btn bfi-pull-right eectrack" data-type="Merchant" data-id="' + val.MerchantId + '" data-index="' + val.mrcKey + '" data-itemname="' + val.merchantNameTrack + '" data-brand="' + val.merchantNameTrack + '"  data-category="' + val.merchantCategoryNameTrack + '">' + currDetails + '</a>';
						carHtml += '						</div>';
						carHtml += '					</div>';
						carHtml += '				</div>';
						carHtml += '			</div>	';
					}




				});
//console.log(carHtml);
				currCarusel.html(carHtml);
				if (bfi_variables.analyticsEnabled && initMerchant.length > 0) {
					if (typeof callAnalyticsEEc !== "undefined") {
						callAnalyticsEEc("addImpression", initMerchant, "list", "Merchant Highlight");
					}
				}

				currCarusel.on('afterChange', function (event, slick, currentSlide) {
					var maxHeight = 0;
					currCarusel.find('.slick-slide').each(function () { maxHeight = Math.max(maxHeight, jQuery(this).height()); })
						.height(maxHeight);
				});

				var currslick = currCarusel.slick({
					rtl: (bfi_variables.bfi_cultureCodeBase == "ar"),
					dots: false,
					draggable: false,
					arrows: true,
					infinite: true,
					slidesToShow: ncolslick,
					slidesToScroll: 1,
				});
				bfi_setFavorites();
			}
		}, 'json');

	});
};
bookingfor.carouselEvents = function () {
	jQuery.each(jQuery('.bficarouselevents'), function () {
		var currCarusel = jQuery(this);
		var currID = this.id;
		var currTags = currCarusel.attr('data-tags');
		var currMaxitems = currCarusel.attr('data-maxitems');
		var currDescmaxchars = currCarusel.attr('data-descmaxchars');
		var currCols = currCarusel.attr('data-cols');
		var currTheme = currCarusel.attr('data-theme');
		var currDetails = currCarusel.attr('data-details');

		var currquery = "tags=" + currTags + "&maxitems=" + currMaxitems + "&descmaxchars=" + currDescmaxchars + "&language=" + bfi_variables.bfi_cultureCode + "&task=GetEventsSlick";
		jQuery.post(bfi_variables.bfi_urlCheck, currquery, function (data) {
			if (data != null) {
				var ncolslick = currCols;
				var currwidth = currCarusel.width();
				if (currwidth < 427 && currCols > 2) {
					ncolslick = 2;
				}
				if (currwidth <= 375 && currCols > 1) {
					ncolslick = 1;
				}
				//calcolo il rapporto in altezza 9/11
				minHeight = (currwidth - ((ncolslick - 1) * 10)) / ncolslick * 11 / 9;

				var carHtml = "";
				var initMerchant = new Array();
				jQuery.each(data || [], function (key, val) {
					if (bfi_variables.analyticsEnabled) {
						var obj = { name: val.Name, category: val.category, brand: val.brand, position: val.position };
						initMerchant.push(obj);
					}
					if (currTheme == 1) {

						carHtml += '';
						carHtml += '			<div class="bfi-bookingforconnector-events bfi-bookingforcarousel" >';
						carHtml += '				<a style="background: url(' + val.ImageUrl + ') no-repeat;background-size: cover;background-position:center center;min-height:' + minHeight + 'px;" href="' + val.Route + '" class="eectrack bfi-carouser-click" data-type="Event" data-id="' + val.Id + '" data-index="' + val.mrcKey + '" data-itemname="' + val.nameTrack + '" data-brand="' + val.nameTrack + '" data-category="' + val.categoryNameTrack + '">';
						carHtml += '					<div class="bfi-slick-content">';
						if(typeof val.CategoryNames !== 'undefined' && val.CategoryNames!=null && val.CategoryNames!='' ){
							var currTags = val.CategoryNames.split(",");
							jQuery.each(currTags || [], function (keyT, valT) {
								carHtml += '<span class="bfi-title-tags-label">' + valT + '</span>';

							});
						}
						if(typeof val.Tags !== 'undefined' && val.Tags!=null && val.Tags!='' ){
							var currTags = val.Tags.split(",");
							jQuery.each(currTags || [], function (keyT, valT) {
								carHtml += '<span class="bfi-title-tags-label">' + valT + '</span>';

							});
						}
						carHtml += '					<div class="bfi-title">' + val.Name + '</div>';
						if(typeof val.StartDateLoc !== 'undefined' && val.StartDate!='' ){
							carHtml += '<div class="bfi-title-date"><i class="fa fa-calendar" aria-hidden="true"></i> ' + val.StartDateLoc ;
							if(typeof val.EndDateLoc !== 'undefined' && val.EndDateLoc!='' && val.EndDateLoc!=val.StartDateLoc ){
								carHtml += ' - ' + val.EndDateLoc;
							}
							carHtml += '</div>';
						}
						carHtml += '					</div>';
		if (bookingfor.mobileViewMode || bookingfor.tabletViewMode)
		{

		}else{
						carHtml += '					<div class="bfi-slick-overlay">';
						carHtml += '						<div class="bfi-slick-overlay-content">';
						carHtml += '							<div class="bfi-slick-overlay-title">' + val.Name + '</div>';
						if(typeof val.StartDateLoc !== 'undefined' && val.StartDate!='' ){
							carHtml += '<div class="bfi-title-date"><i class="fa fa-calendar" aria-hidden="true"></i> ' + val.StartDateLoc ;
							if(typeof val.EndDateLoc !== 'undefined' && val.EndDateLoc!='' && val.EndDateLoc!=val.StartDateLoc ){
								carHtml += ' - ' + val.EndDateLoc;
							}
							carHtml += '</div>';
						}
						carHtml += '							<div class="bfi-slick-overlay-description">' + val.Description + '</div>';
						carHtml += '							<div class="bfi-btn bfi-alternative5">' + currDetails + '</div>';
						carHtml += '						</div>';
						carHtml += '					</div>';		
		}
						carHtml += '					<div class="bfi-icon-favorite-container  bfi-pull-right">';
						carHtml += '					<span class="bfi-icon-favorite bfi-iconcontainer" ';
						carHtml += '					          data-itemid="' + val.Id + '"  ';
						carHtml += '							  data-itemname="' + val.Name + '"  ';
						carHtml += '							  data-itemurl="' + val.Route + '" ';
						carHtml += '					          data-groupid=""  ';
						carHtml += '							  data-itemtype="2" ';
						carHtml += '					          data-startdate="' + val.StartDate + '" ';
						carHtml += '					          data-enddate="' + val.EndDate + '" ';
						carHtml += '					          data-toggle="tooltip"  ';
						carHtml += '							  data-fromtravelplanner="0" ';
						carHtml += '							  data-hasfromtime="0" ';
						carHtml += '							  data-hastotime="0" ';
						carHtml += '							  data-favoriteid="0" ';
						carHtml += '							  data-operation-type="" ';
						carHtml += '							  title="Add to favorites"> ';
						carHtml += '					        <i class="fa fa-heart-o"></i>';
						carHtml += '					        <i class="fa fa-heart"></i>';
						carHtml += '					    </span>';
						carHtml += '						<div class="bfi-favoritegroups-container">';
						carHtml += '						</div>';
						carHtml += '					</div>';
						carHtml += '				</a>';
						carHtml += '			</div>	';

					} else {

						carHtml += '';
						carHtml += '			<div class="bfi-bookingforconnector-events bfi-bookingforcarousel" >';
						carHtml += '				<div class="bfi-row" style="height:100%">';
						carHtml += '					<div class="bfi-row" >';
						carHtml += '						<div class="bfi-col-md-12"><a href="' + val.Route + '" class="eectrack" data-type="Merchant" data-id="' + val.Id + '" data-index="' + val.mrcKey + '" data-itemname="' + val.nameTrack + '" data-brand="' + val.nameTrack + '" data-category="' + val.categoryNameTrack + '"><img src="' + val.ImageUrl + '" class="bfi-img-responsive center-block" /></a>';
						carHtml += '						</div>';
						carHtml += '					</div>';
						carHtml += '					<div class="bfi-row" >';
						carHtml += '						<div class="bfi-col-md-12 bfi-item-title" style="padding: 10px!important;">';
						carHtml += '						<a class="eectrack" href="' + val.Route + '" id="nameAnchor' + val.Id + '" data-type="Merchant" data-id="' + val.Id + '" data-index="' + val.mrcKey + '" data-itemname="' + val.nameTrack + '" data-brand="' + val.nameTrack + '" data-category="' + val.categoryNameTrack + '">' + val.Name + '</a> ';
						if (val.rating > 0) {
							carHtml += '						<span class="bfi-item-rating">';
							for (i = 0; i < val.rating; i++) {
								carHtml += '						<i class="fa fa-star"></i>';
							}
							carHtml += '						</span>';
						}
						if (val.hasSuperior) {
							carHtml += '						&nbsp;S';
						}
						carHtml += '						</div>';
						carHtml += '					</div>';
						carHtml += '					<div class="bfi-row bfi-hide" >';
						carHtml += '						<div class="bfi_merchant-description bfi-col-md-12" style="padding-left: 10px!important;padding-right: 10px!important;">';
						carHtml += '							<a href="' + val.Route + '" id="nameAnchor' + val.Id + '" class="eectrack" data-type="Merchant" data-id="' + val.Id + '" data-index="' + val.mrcKey + '" data-itemname="' + val.nameTrack + '" data-brand="' + val.nameTrack + '" data-category="' + val.categoryNameTrack + '">' + val.Name + '</a> ';
						carHtml += '						</div>';
						carHtml += '					</div>';
						carHtml += '					<div class="bfi-row" >';
						carHtml += '						<div class="bfi_merchant-description bfi-col-md-12" style="padding: 10px!important;" id="descr' + val.Id + '">' + val.Description + '</div>';
						carHtml += '					</div>';
						carHtml += '					<div class="bfi-row secondarysection">';
						carHtml += '						<div class="bfi-col-md-1  secondarysectionitem">';
						carHtml += '							&nbsp;';
						carHtml += '						</div>';
						carHtml += '						<div class="bfi-col-md-11 secondarysectionitem" style="padding: 10px!important;">';
						carHtml += '								<a href="' + val.Route + '" class="bfi-btn bfi-pull-right eectrack" data-type="Merchant" data-id="' + val.Id + '" data-index="' + val.mrcKey + '" data-itemname="' + val.nameTrack + '" data-brand="' + val.nameTrack + '"  data-category="' + val.categoryNameTrack + '">' + currDetails + '</a>';
						carHtml += '						</div>';
						carHtml += '					</div>';
						carHtml += '				</div>';
						carHtml += '			</div>	';
					}




				});

				currCarusel.html(carHtml);
				if (bfi_variables.analyticsEnabled && initMerchant.length > 0) {
					if (typeof callAnalyticsEEc !== "undefined") {
						callAnalyticsEEc("addImpression", initMerchant, "list", "Event Highlight");
					}
				}

				currCarusel.on('afterChange', function (event, slick, currentSlide) {
					var maxHeight = 0;
					currCarusel.find('.slick-slide').each(function () { maxHeight = Math.max(maxHeight, jQuery(this).height()); })
						.height(maxHeight);
				});

				var currslick = currCarusel.slick({
					rtl: (bfi_variables.bfi_cultureCodeBase == "ar"),
					dots: false,
					draggable: false,
					arrows: true,
					infinite: true,
					slidesToShow: ncolslick,
					slidesToScroll: 1,
				});
				bfi_setFavorites();

			}
		}, 'json');

	});
};
bookingfor.carouselPoi = function () {
	jQuery.each(jQuery('.bficarouselpoi'), function () {
		var currCarusel = jQuery(this);
		var currID = this.id;
		var currTags = currCarusel.attr('data-tags');
		var currMaxitems = currCarusel.attr('data-maxitems');
		var currDescmaxchars = currCarusel.attr('data-descmaxchars');
		var currCols = currCarusel.attr('data-cols');
		var currTheme = currCarusel.attr('data-theme');
		var currDetails = currCarusel.attr('data-details');

		var currquery = "tags=" + currTags + "&maxitems=" + currMaxitems + "&descmaxchars=" + currDescmaxchars + "&language=" + bfi_variables.bfi_cultureCode + "&task=GetPoiSlick";
		jQuery.post(bfi_variables.bfi_urlCheck, currquery, function (data) {
			if (data != null) {
				var ncolslick = currCols;
				var currwidth = currCarusel.width();
				if (currwidth < 427 && currCols > 2) {
					ncolslick = 2;
				}
				if (currwidth <= 375 && currCols > 1) {
					ncolslick = 1;
				}
				//calcolo il rapporto in altezza 9/11
				minHeight = (currwidth - ((ncolslick - 1) * 10)) / ncolslick * 11 / 9;

				var carHtml = "";
				var initMerchant = new Array();
				jQuery.each(data || [], function (key, val) {
					if (bfi_variables.analyticsEnabled) {
						var obj = { name: val.Name, category: val.category, brand: val.brand, position: val.position };
						initMerchant.push(obj);
					}
					if (currTheme == 1) {

						carHtml += '';
						carHtml += '			<div class="bfi-bookingforconnector-poi bfi-bookingforcarousel" >';
						carHtml += '				<a style="background: url(' + val.ImageUrl + ') no-repeat;background-size: cover;background-position:center center;min-height:' + minHeight + 'px;" href="' + val.Route + '" class="eectrack bfi-carouser-click" data-type="Poi" data-id="' + val.Id + '" data-index="' + val.mrcKey + '" data-itemname="' + val.nameTrack + '" data-brand="' + val.nameTrack + '" data-category="' + val.categoryNameTrack + '">';
						carHtml += '					<div class="bfi-slick-content">';
						if(typeof val.CategoryNames !== 'undefined' && val.CategoryNames!=null && val.CategoryNames!='' ){
							var currTags = val.CategoryNames.split(",");
							jQuery.each(currTags || [], function (keyT, valT) {
								carHtml += '<span class="bfi-title-tags-label">' + valT + '</span>';

							});
						}
						if(typeof val.Tags !== 'undefined' && val.Tags!=null && val.Tags!='' ){
							var currTags = val.Tags.split(",");
							jQuery.each(currTags || [], function (keyT, valT) {
								carHtml += '<span class="bfi-title-tags-label">' + valT + '</span>';

							});
						}
						carHtml += '					<div class="bfi-title">' + val.Name + '</div>';

						carHtml += '					</div>';
		if (bookingfor.mobileViewMode || bookingfor.tabletViewMode)
		{

		}else{
						carHtml += '					<div class="bfi-slick-overlay">';
						carHtml += '						<div class="bfi-slick-overlay-content">';
						carHtml += '							<div class="bfi-slick-overlay-title">' + val.Name + '</div>';
						carHtml += '							<div class="bfi-slick-overlay-description">' + val.Description + '</div>';
						carHtml += '							<div class="bfi-btn bfi-alternative5">' + currDetails + '</div>';
						carHtml += '						</div>';
						carHtml += '					</div>';		
		}
						carHtml += '					<div class="bfi-icon-favorite-container  bfi-pull-right">';
						carHtml += '					<span class="bfi-icon-favorite bfi-iconcontainer" ';
						carHtml += '					          data-itemid="' + val.Id + '"  ';
						carHtml += '							  data-itemname="' + val.Name + '"  ';
						carHtml += '							  data-itemurl="' + val.Route + '" ';
						carHtml += '					          data-groupid=""  ';
						carHtml += '							  data-itemtype="2" ';
						carHtml += '					          data-startdate="" ';
						carHtml += '					          data-enddate="" ';
						carHtml += '					          data-toggle="tooltip"  ';
						carHtml += '							  data-fromtravelplanner="0" ';
						carHtml += '							  data-hasfromtime="0" ';
						carHtml += '							  data-hastotime="0" ';
						carHtml += '							  data-favoriteid="0" ';
						carHtml += '							  data-operation-type="" ';
						carHtml += '							  title="Add to favorites"> ';
						carHtml += '					        <i class="fa fa-heart-o"></i>';
						carHtml += '					        <i class="fa fa-heart"></i>';
						carHtml += '					    </span>';
						carHtml += '						<div class="bfi-favoritegroups-container">';
						carHtml += '						</div>';
						carHtml += '					</div>';
						carHtml += '				</a>';
						carHtml += '			</div>	';

					} else {

						carHtml += '';
						carHtml += '			<div class="bfi-bookingforconnector-poi  bfi-bookingforcarousel" >';
						carHtml += '				<div class="bfi-row" style="height:100%">';
						carHtml += '					<div class="bfi-row" >';
						carHtml += '						<div class="bfi-col-md-12"><a href="' + val.Route + '" class="eectrack" data-type="Poi" data-id="' + val.Id + '" data-index="' + val.mrcKey + '" data-itemname="' + val.nameTrack + '" data-brand="' + val.nameTrack + '" data-category="' + val.categoryNameTrack + '"><img src="' + val.ImageUrl + '" class="bfi-img-responsive center-block" /></a>';
						carHtml += '						</div>';
						carHtml += '					</div>';
						carHtml += '					<div class="bfi-row" >';
						carHtml += '						<div class="bfi-col-md-12 bfi-item-title" style="padding: 10px!important;">';
						carHtml += '						<a class="eectrack" href="' + val.Route + '" id="nameAnchor' + val.Id + '" data-type="Poi" data-id="' + val.Id + '" data-index="' + val.mrcKey + '" data-itemname="' + val.nameTrack + '" data-brand="' + val.nameTrack + '" data-category="' + val.categoryNameTrack + '">' + val.Name + '</a> ';
						if (val.rating > 0) {
							carHtml += '						<span class="bfi-item-rating">';
							for (i = 0; i < val.rating; i++) {
								carHtml += '						<i class="fa fa-star"></i>';
							}
							carHtml += '						</span>';
						}
						if (val.hasSuperior) {
							carHtml += '						&nbsp;S';
						}
						carHtml += '						</div>';
						carHtml += '					</div>';
						carHtml += '					<div class="bfi-row bfi-hide" >';
						carHtml += '						<div class="bfi_merchant-description bfi-col-md-12" style="padding-left: 10px!important;padding-right: 10px!important;">';
						carHtml += '							<a href="' + val.Route + '" id="nameAnchor' + val.Id + '" class="eectrack" data-type="Poi" data-id="' + val.Id + '" data-index="' + val.mrcKey + '" data-itemname="' + val.nameTrack + '" data-brand="' + val.nameTrack + '" data-category="' + val.categoryNameTrack + '">' + val.Name + '</a> ';
						carHtml += '						</div>';
						carHtml += '					</div>';
						carHtml += '					<div class="bfi-row" >';
						carHtml += '						<div class="bfi_merchant-description bfi-col-md-12" style="padding: 10px!important;" id="descr' + val.Id + '">' + val.Description + '</div>';
						carHtml += '					</div>';
						carHtml += '					<div class="bfi-row secondarysection">';
						carHtml += '						<div class="bfi-col-md-1  secondarysectionitem">';
						carHtml += '							&nbsp;';
						carHtml += '						</div>';
						carHtml += '						<div class="bfi-col-md-11 secondarysectionitem" style="padding: 10px!important;">';
						carHtml += '								<a href="' + val.Route + '" class="bfi-btn bfi-pull-right eectrack" data-type="Poi" data-id="' + val.Id + '" data-index="' + val.mrcKey + '" data-itemname="' + val.nameTrack + '" data-brand="' + val.nameTrack + '"  data-category="' + val.categoryNameTrack + '">' + currDetails + '</a>';
						carHtml += '						</div>';
						carHtml += '					</div>';
						carHtml += '				</div>';
						carHtml += '			</div>	';
					}




				});

				currCarusel.html(carHtml);
				if (bfi_variables.analyticsEnabled && initMerchant.length > 0) {
					if (typeof callAnalyticsEEc !== "undefined") {
						callAnalyticsEEc("addImpression", initMerchant, "list", "Poi Highlight");
					}
				}

				currCarusel.on('afterChange', function (event, slick, currentSlide) {
					var maxHeight = 0;
					currCarusel.find('.slick-slide').each(function () { maxHeight = Math.max(maxHeight, jQuery(this).height()); })
						.height(maxHeight);
				});

				var currslick = currCarusel.slick({
					rtl: (bfi_variables.bfi_cultureCodeBase == "ar"),
					dots: false,
					draggable: false,
					arrows: true,
					infinite: true,
					slidesToShow: ncolslick,
					slidesToScroll: 1,
				});
				bfi_setFavorites();

			}
		}, 'json');

	});
};
bookingfor.carouselResources = function () {
	jQuery.each(jQuery('.bficarouselresources'), function () {
		var currCarusel = jQuery(this);
		var currID = this.id;
		var currTags = currCarusel.attr('data-tags');
		var currMaxitems = currCarusel.attr('data-maxitems');
		var currDescmaxchars = currCarusel.attr('data-descmaxchars');
		var currCols = currCarusel.attr('data-cols');
		var currTheme = currCarusel.attr('data-theme');
		var currDetails = currCarusel.attr('data-details');

		var currquery = "tags=" + currTags + "&maxitems=" + currMaxitems + "&descmaxchars=" + currDescmaxchars + "&language=" + bfi_variables.bfi_cultureCode + "&task=GetResourcesSlick";
		jQuery.post(bfi_variables.bfi_urlCheck, currquery, function (data) {
			if (data != null) {
				var ncolslick = currCols;
				var currwidth = currCarusel.width();
				if (currwidth < 427 && currCols > 2) {
					ncolslick = 2;
				}
				if (currwidth <= 375 && currCols > 1) {
					ncolslick = 1;
				}
				//calcolo il rapporto in altezza 9/11
				minHeight = (currwidth - ((ncolslick - 1) * 10)) / ncolslick * 11 / 9;

				var carHtml = "";
				var initMerchant = new Array();
				jQuery.each(data || [], function (key, val) {
					if (bfi_variables.analyticsEnabled) {
						var obj = { name: val.Name, category: val.category, brand: val.brand, position: val.position };
						initMerchant.push(obj);
					}
					if (currTheme == 1) {

						carHtml += '';
						carHtml += '			<div class="bfi-bookingforconnector-resources bfi-bookingforcarousel" >';
						carHtml += '				<a style="background: url(' + val.ImageUrl + ') no-repeat;background-size: cover;background-position:center center;min-height:' + minHeight + 'px;" href="' + val.Route + '" class="eectrack bfi-carouser-click" data-type="Resource" data-id="' + val.Id + '" data-index="' + val.Key + '" data-itemname="' + val.nameTrack + '" data-brand="' + val.nameTrack + '" data-category="' + val.categoryNameTrack + '">';
						carHtml += '					<div class="bfi-slick-content">';
						if(typeof val.CategoryNames !== 'undefined' && val.CategoryNames!=null && val.CategoryNames!='' ){
							var currTags = val.CategoryNames.split(",");
							jQuery.each(currTags || [], function (keyT, valT) {
								carHtml += '<span class="bfi-title-tags-label">' + valT + '</span>';

							});
						}
						if(typeof val.Tags !== 'undefined' && val.Tags!=null && val.Tags!='' ){
							var currTags = val.Tags.split(",");
							jQuery.each(currTags || [], function (keyT, valT) {
								carHtml += '<span class="bfi-title-tags-label">' + valT + '</span>';

							});
						}
						carHtml += '					<div class="bfi-title">' + val.Name + '</div>';
						carHtml += '					</div>';
		if (bookingfor.mobileViewMode || bookingfor.tabletViewMode)
		{

		}else{
						carHtml += '					<div class="bfi-slick-overlay">';
						carHtml += '						<div class="bfi-slick-overlay-content">';
						carHtml += '							<div class="bfi-slick-overlay-title">' + val.Name + '</div>';
						carHtml += '							<div class="bfi-slick-overlay-description">' + val.Description + '</div>';
						carHtml += '							<div class="bfi-btn bfi-alternative5">' + currDetails + '</div>';
						carHtml += '						</div>';
						carHtml += '					</div>';
		}
						carHtml += '					<div class="bfi-icon-favorite-container  bfi-pull-right">';
						carHtml += '					<span class="bfi-icon-favorite bfi-iconcontainer" ';
						carHtml += '					          data-itemid="' + val.Id + '"  ';
						carHtml += '							  data-itemname="' + val.Name + '"  ';
						carHtml += '							  data-itemurl="' + val.Route + '" ';
						carHtml += '					          data-groupid=""  ';
						carHtml += '							  data-itemtype="1" ';
						carHtml += '					          data-startdate="" ';
						carHtml += '					          data-enddate="" ';
						carHtml += '					          data-toggle="tooltip"  ';
						carHtml += '							  data-fromtravelplanner="0" ';
						carHtml += '							  data-hasfromtime="0" ';
						carHtml += '							  data-hastotime="0" ';
						carHtml += '							  data-favoriteid="0" ';
						carHtml += '							  data-operation-type="" ';
						carHtml += '							  title="Add to favorites"> ';
						carHtml += '					        <i class="fa fa-heart-o"></i>';
						carHtml += '					        <i class="fa fa-heart"></i>';
						carHtml += '					    </span>';
						carHtml += '						<div class="bfi-favoritegroups-container">';
						carHtml += '						</div>';
						carHtml += '					</div>';
						carHtml += '				</a>';
						carHtml += '			</div>	';

					} else {

						carHtml += '';
						carHtml += '			<div class="bfi-bookingforconnector-resources bfi-bookingforcarousel" >';
						carHtml += '				<div class="bfi-row" style="height:100%">';
						carHtml += '					<div class="bfi-row" >';
						carHtml += '						<div class="bfi-col-md-12"><a href="' + val.Route + '" class="eectrack" data-type="Resource" data-id="' + val.Id + '" data-index="' + val.Key + '" data-itemname="' + val.nameTrack + '" data-brand="' + val.nameTrack + '" data-category="' + val.categoryNameTrack + '"><img src="' + val.ImageUrl + '" class="bfi-img-responsive center-block" /></a>';
						carHtml += '						</div>';
						carHtml += '					</div>';
						carHtml += '					<div class="bfi-row" >';
						carHtml += '						<div class="bfi-col-md-12 bfi-item-title" style="padding: 10px!important;">';
						carHtml += '						<a class="eectrack" href="' + val.Route + '" id="nameAnchor' + val.Id + '" data-type="Resource" data-id="' + val.Id + '" data-index="' + val.Key + '" data-itemname="' + val.nameTrack + '" data-brand="' + val.nameTrack + '" data-category="' + val.categoryNameTrack + '">' + val.Name + '</a> ';
						if (val.rating > 0) {
							carHtml += '						<span class="bfi-item-rating">';
							for (i = 0; i < val.rating; i++) {
								carHtml += '						<i class="fa fa-star"></i>';
							}
							carHtml += '						</span>';
						}
						if (val.hasSuperior) {
							carHtml += '						&nbsp;S';
						}
						carHtml += '						</div>';
						carHtml += '					</div>';
						carHtml += '					<div class="bfi-row bfi-hide" >';
						carHtml += '						<div class="bfi_merchant-description bfi-col-md-12" style="padding-left: 10px!important;padding-right: 10px!important;">';
						carHtml += '							<a href="' + val.Route + '" id="nameAnchor' + val.Id + '" class="eectrack" data-type="Resource" data-id="' + val.Id + '" data-index="' + val.Key + '" data-itemname="' + val.nameTrack + '" data-brand="' + val.nameTrack + '" data-category="' + val.categoryNameTrack + '">' + val.Name + '</a> ';
						carHtml += '						</div>';
						carHtml += '					</div>';
						carHtml += '					<div class="bfi-row" >';
						carHtml += '						<div class="bfi_merchant-description bfi-col-md-12" style="padding: 10px!important;" id="descr' + val.Id + '">' + val.Description + '</div>';
						carHtml += '					</div>';
						carHtml += '					<div class="bfi-row secondarysection">';
						carHtml += '						<div class="bfi-col-md-1  secondarysectionitem">';
						carHtml += '							&nbsp;';
						carHtml += '						</div>';
						carHtml += '						<div class="bfi-col-md-11 secondarysectionitem" style="padding: 10px!important;">';
						carHtml += '								<a href="' + val.Route + '" class="bfi-btn bfi-pull-right eectrack" data-type="Resource" data-id="' + val.Id + '" data-index="' + val.Key + '" data-itemname="' + val.nameTrack + '" data-brand="' + val.nameTrack + '"  data-category="' + val.categoryNameTrack + '">' + currDetails + '</a>';
						carHtml += '						</div>';
						carHtml += '					</div>';
						carHtml += '				</div>';
						carHtml += '			</div>	';
					}




				});

				currCarusel.html(carHtml);
				if (bfi_variables.analyticsEnabled && initMerchant.length > 0) {
					if (typeof callAnalyticsEEc !== "undefined") {
						callAnalyticsEEc("addImpression", initMerchant, "list", "Event Highlight");
					}
				}

				currCarusel.on('afterChange', function (event, slick, currentSlide) {
					var maxHeight = 0;
					currCarusel.find('.slick-slide')
						.each(function () { maxHeight = Math.max(maxHeight, jQuery(this).height()); })
						.height(maxHeight);
				});

				var currslick = currCarusel.slick({
					rtl: (bfi_variables.bfi_cultureCodeBase == "ar"),
					dots: false,
					draggable: false,
					arrows: true,
					infinite: true,
					slidesToShow: ncolslick,
					slidesToScroll: 1,
				});
				bfi_setFavorites();

			}
		}, 'json');

	});
};
bookingfor.carouselCrossSellResources = function () {
	jQuery.each(jQuery('.bficarouselcrosssellresources'), function () {
		var currCarusel = jQuery(this);
		var currID = this.id;
		var currIds = currCarusel.attr('data-ids');
		var currMaxitems = currCarusel.attr('data-maxitems');
		var currDescmaxchars = currCarusel.attr('data-descmaxchars');
		var currCols = currCarusel.attr('data-cols');
		var currTheme = currCarusel.attr('data-theme');
		var currDetails = currCarusel.attr('data-details');
		
		if (currIds.length > 0)
		{
			var currquery = "ids=" + currIds + "&maxitems=" + currMaxitems + "&descmaxchars=" + currDescmaxchars + "&language=" + bfi_variables.bfi_cultureCode + "&task=GetCrossSellResourcesSlick";
			jQuery.post(bfi_variables.bfi_urlCheck, currquery, function (data) {
				if (data != null) {
					currCarusel.closest(".bficarouselcrosssellcontainer").show();
					var ncolslick = currCols;
					var currwidth = currCarusel.width();
					if (currwidth < 427 && currCols > 2) {
						ncolslick = 2;
					}
					if (currwidth <= 375 && currCols > 1) {
						ncolslick = 1;
					}
					//calcolo il rapporto in altezza 9/11
					minHeight = (currwidth - ((ncolslick - 1) * 10)) / ncolslick * 11 / 9;

					var carHtml = "";
					var initMerchant = new Array();
					jQuery.each(data || [], function (key, val) {
						if (bfi_variables.analyticsEnabled) {
							var obj = { name: val.Name, category: val.category, brand: val.brand, position: val.position };
							initMerchant.push(obj);
						}
						if (currTheme == 1) {

							carHtml += '';
							carHtml += '			<div class="bfi-bookingforconnector-resources bfi-bookingforcarousel" >';
							carHtml += '				<a style="background: url(' + val.ImageUrl + ') no-repeat;background-size: cover;background-position:center center;min-height:' + minHeight + 'px;" href="' + val.Route + '" class="eectrack bfi-carouser-click" data-type="Resource" data-id="' + val.Id + '" data-index="' + val.Key + '" data-itemname="' + val.nameTrack + '" data-brand="' + val.nameTrack + '" data-category="' + val.categoryNameTrack + '">';
							carHtml += '					<div class="bfi-slick-content">';
							if(typeof val.CategoryNames !== 'undefined' && val.CategoryNames!=null && val.CategoryNames!='' ){
								var currTags = val.CategoryNames.split(",");
								jQuery.each(currTags || [], function (keyT, valT) {
									carHtml += '<span class="bfi-title-tags-label">' + valT + '</span>';

								});
							}
							if(typeof val.Tags !== 'undefined' && val.Tags!=null && val.Tags!='' ){
								var currTags = val.Tags.split(",");
								jQuery.each(currTags || [], function (keyT, valT) {
									carHtml += '<span class="bfi-title-tags-label">' + valT + '</span>';

								});
							}
							carHtml += '					<div class="bfi-title">' + val.Name + '</div>';
							carHtml += '					</div>';
			if (bookingfor.mobileViewMode || bookingfor.tabletViewMode)
			{

			}else{
							carHtml += '					<div class="bfi-slick-overlay">';
							carHtml += '						<div class="bfi-slick-overlay-content">';
							carHtml += '							<div class="bfi-slick-overlay-title">' + val.Name + '</div>';
							carHtml += '							<div class="bfi-slick-overlay-description">' + val.Description + '</div>';
							carHtml += '							<div class="bfi-btn bfi-alternative5">' + currDetails + '</div>';
							carHtml += '						</div>';
							carHtml += '					</div>';
			}
							carHtml += '					<div class="bfi-icon-favorite-container  bfi-pull-right">';
							carHtml += '					<span class="bfi-icon-favorite bfi-iconcontainer" ';
							carHtml += '					          data-itemid="' + val.Id + '"  ';
							carHtml += '							  data-itemname="' + val.Name + '"  ';
							carHtml += '							  data-itemurl="' + val.Route + '" ';
							carHtml += '					          data-groupid=""  ';
							carHtml += '							  data-itemtype="1" ';
							carHtml += '					          data-startdate="" ';
							carHtml += '					          data-enddate="" ';
							carHtml += '					          data-toggle="tooltip"  ';
							carHtml += '							  data-fromtravelplanner="0" ';
							carHtml += '							  data-hasfromtime="0" ';
							carHtml += '							  data-hastotime="0" ';
							carHtml += '							  data-favoriteid="0" ';
							carHtml += '							  data-operation-type="" ';
							carHtml += '							  title="Add to favorites"> ';
							carHtml += '					        <i class="fa fa-heart-o"></i>';
							carHtml += '					        <i class="fa fa-heart"></i>';
							carHtml += '					    </span>';
							carHtml += '						<div class="bfi-favoritegroups-container">';
							carHtml += '						</div>';
							carHtml += '					</div>';
							carHtml += '				</a>';
							carHtml += '			</div>	';

						} else {

							carHtml += '';
							carHtml += '			<div class="bfi-bookingforconnector-resources bfi-bookingforcarousel" >';
							carHtml += '				<div class="bfi-row" style="height:100%">';
							carHtml += '					<div class="bfi-row" >';
							carHtml += '						<div class="bfi-col-md-12"><a href="' + val.Route + '" class="eectrack" data-type="Resource" data-id="' + val.Id + '" data-index="' + val.Key + '" data-itemname="' + val.nameTrack + '" data-brand="' + val.nameTrack + '" data-category="' + val.categoryNameTrack + '"><img src="' + val.ImageUrl + '" class="bfi-img-responsive center-block" /></a>';
							carHtml += '						</div>';
							carHtml += '					</div>';
							carHtml += '					<div class="bfi-row" >';
							carHtml += '						<div class="bfi-col-md-12 bfi-item-title" style="padding: 10px!important;">';
							carHtml += '						<a class="eectrack" href="' + val.Route + '" id="nameAnchor' + val.Id + '" data-type="Resource" data-id="' + val.Id + '" data-index="' + val.Key + '" data-itemname="' + val.nameTrack + '" data-brand="' + val.nameTrack + '" data-category="' + val.categoryNameTrack + '">' + val.Name + '</a> ';
							if (val.rating > 0) {
								carHtml += '						<span class="bfi-item-rating">';
								for (i = 0; i < val.rating; i++) {
									carHtml += '						<i class="fa fa-star"></i>';
								}
								carHtml += '						</span>';
							}
							if (val.hasSuperior) {
								carHtml += '						&nbsp;S';
							}
							carHtml += '						</div>';
							carHtml += '					</div>';
							carHtml += '					<div class="bfi-row bfi-hide" >';
							carHtml += '						<div class="bfi_merchant-description bfi-col-md-12" style="padding-left: 10px!important;padding-right: 10px!important;">';
							carHtml += '							<a href="' + val.Route + '" id="nameAnchor' + val.Id + '" class="eectrack" data-type="Resource" data-id="' + val.Id + '" data-index="' + val.Key + '" data-itemname="' + val.nameTrack + '" data-brand="' + val.nameTrack + '" data-category="' + val.categoryNameTrack + '">' + val.Name + '</a> ';
							carHtml += '						</div>';
							carHtml += '					</div>';
							carHtml += '					<div class="bfi-row" >';
							carHtml += '						<div class="bfi_merchant-description bfi-col-md-12" style="padding: 10px!important;" id="descr' + val.Id + '">' + val.Description + '</div>';
							carHtml += '					</div>';
							carHtml += '					<div class="bfi-row secondarysection">';
							carHtml += '						<div class="bfi-col-md-1  secondarysectionitem">';
							carHtml += '							&nbsp;';
							carHtml += '						</div>';
							carHtml += '						<div class="bfi-col-md-11 secondarysectionitem" style="padding: 10px!important;">';
							carHtml += '								<a href="' + val.Route + '" class="bfi-btn bfi-pull-right eectrack" data-type="Resource" data-id="' + val.Id + '" data-index="' + val.Key + '" data-itemname="' + val.nameTrack + '" data-brand="' + val.nameTrack + '"  data-category="' + val.categoryNameTrack + '">' + currDetails + '</a>';
							carHtml += '						</div>';
							carHtml += '					</div>';
							carHtml += '				</div>';
							carHtml += '			</div>	';
						}




					});

					currCarusel.html(carHtml);
					if (bfi_variables.analyticsEnabled && initMerchant.length > 0) {
						if (typeof callAnalyticsEEc !== "undefined") {
							callAnalyticsEEc("addImpression", initMerchant, "list", "Event Highlight");
						}
					}

					currCarusel.on('afterChange', function (event, slick, currentSlide) {
						var maxHeight = 0;
						currCarusel.find('.slick-slide')
							.each(function () { maxHeight = Math.max(maxHeight, jQuery(this).height()); })
							.height(maxHeight);
					});

					var currslick = currCarusel.slick({
					rtl: (bfi_variables.bfi_cultureCodeBase == "ar"),
						dots: false,
						draggable: false,
						arrows: true,
						infinite: true,
						slidesToShow: ncolslick,
						slidesToScroll: 1,
					});
					bfi_setFavorites();
					
				}
		}, 'json');
		} // end if ids empty

	});
};

/* Banner */
bookingfor.addNth = (function () {
    var len, i = 0, className, prevIndexes = [];

    function isNew (el) {
         return el.hasClass(className); // removed unnecessary parenthesis
    }

    return function (selector, html, nth, className ) {
        var els = jQuery( selector );
        className = className || 'test';

        if ( jQuery.inArray(nth, prevIndexes) === -1 ) {
            prevIndexes.push(nth);

            jQuery.each(els, function( index, el ) {
                el = jQuery(el);
                if ( (i % nth) === 0 && i !== 0 ) {
                    if ( ! isNew(el) ) {
                        el.before( html );
                    }
                }
                i++;
            });
            i = 0;
        }
    }
})();
bookingfor.bannerEvents = function () {
	jQuery.each(jQuery('.bfilistwithbanner'), function () {
		var currList = jQuery(this);
		var currID = this.id;
		var nBanner =  Number(currList.attr('data-banner')||0);
		var totalitems =  Number(currList.attr('data-totalitems')||0);
		var currPos = nBanner;
		var isrepeated = 0;
		var currMaxitems = 1;
		if (nBanner>0 && totalitems>0)
		{
			isrepeated = currList.attr("data-banner-repeated")||0;
			if (isrepeated)
			{
				currMaxitems = Math.floor(totalitems/nBanner);
			}
			var currCheckin = currList.attr('data-checkin');
			var currCheckout = currList.attr('data-checkout');

			var currTheme = currList.attr('data-theme')||0;

			var currquery = "checkin=" + currCheckin + "&checkout=" + currCheckout + "&maxitems=" + currMaxitems + "&language=" + bfi_variables.bfi_cultureCode + "&task=GetEventsBanner";
			jQuery.post(bfi_variables.bfi_urlCheck, currquery, function (data) {
				if (data != null) {
					var initMerchant = new Array();
					jQuery.each(data || [], function (key, val) {
						var carHtml = "";
						if (bfi_variables.analyticsEnabled) {
							var obj = { name: val.Name, category: val.category, brand: val.brand, position: val.position };
							initMerchant.push(obj);
						}
						if (val.Theme == 1) {

							carHtml += '';
							carHtml += '			<div class="bfi-bookingforconnector-bannerevents bfi-col-sm-6 bfi-item bfi-list-group-item " style="width:100%" >';
							carHtml += '				<a href="' + val.Route + '" class="eectrack bfi-carouser-click" data-type="Event" data-id="' + val.Id + '" data-index="' + val.mrcKey + '" data-itemname="' + val.nameTrack + '" data-brand="' + val.nameTrack + '" data-category="' + val.categoryNameTrack + '">';
							carHtml += '					<img src="'+val.ImageUrl+'" alt="' + val.Name + '" class="bfi-img-responsive bfi-img-desktop" style="max-width:100%;width: auto;margin: auto;" />';
							carHtml += '					<img src="'+val.ImageMobileUrl+'" alt="' + val.Name + '" class="bfi-img-responsive bfi-img-mobile" style="max-width:100%;width: auto;margin: auto;" />';
							carHtml += '				</a>';
							carHtml += '			</div>	';

						} else { // no banner image

							carHtml += '';
							carHtml += '			<div class="bfi-bookingforconnector-bannerevents bfi-col-sm-6 bfi-item bfi-list-group-item" style="width:100%" >';
							carHtml += '				<div class="bfi-row bfi-sameheight">';
							carHtml += '					<div class="bfi-col-sm-9 bfi-details-container" >';
							carHtml += '						<div class="bfi-item-title">';
							carHtml += '							<a class="eectrack" href="' + val.Route + '" id="nameAnchor' + val.Id + '" data-type="Event" data-id="' + val.Id + '" data-index="' + val.mrcKey + '" data-itemname="' + val.nameTrack + '" data-brand="' + val.nameTrack + '" data-category="' + val.categoryNameTrack + '">' + val.Name + '</a> ';
							carHtml += '						</div>';
							carHtml += '						<div class="bfi-item-descr bfi-description">' + val.Description + '</div>';
							carHtml += '					</div>';
							carHtml += '					<div class="bfi-col-sm-3 bfi-img-container" >';
							carHtml += '							<a style="background: url(' + val.ImageUrl + ') no-repeat;background-size: cover;background-position:center 25%;" href="' + val.Route + '" class="eectrack" data-type="Event" data-id="' + val.Id + '" data-index="' + val.mrcKey + '" data-itemname="' + val.nameTrack + '" data-brand="' + val.nameTrack + '" data-category="' + val.categoryNameTrack + '">';
							carHtml += '								<img src="' + val.ImageUrl + '" class="bfi-img-responsive center-block" />';
							carHtml += '							</a>';
							carHtml += '					</div>';
							carHtml += '				</div>';
							carHtml += '			</div>	';
						}



						currList.find(".bfi-item:eq("+currPos+")").before(carHtml);
						currPos += nBanner;
						if (!isrepeated)
						{
							return false; // breaks
						}

					});
					
					if (bfi_variables.analyticsEnabled && initMerchant.length > 0) {
						if (typeof callAnalyticsEEc !== "undefined") {
							callAnalyticsEEc("addImpression", initMerchant, "list", "Event Highlight");
						}
					}
				}
			}, 'json');
		} // endif nbanner>0

	});
};

/* END Banner */
/*--filter-*/
bookingfor.applyfilterdata = function () {
	// ------- function filter options ---------
	jQuery(document).on('click tap', ".bfi-searchfilter h3", function (e) {
		var windowsize = jQuery(window).width();
		if (windowsize < 769) {
			jQuery(this).toggleClass("bfi-searchfilter-active");
			jQuery(".bfi-filtercontainer").slideToggle("normal", function () {
				if (jQuery.prototype.masonry) {
					jQuery('.main-siderbar, .main-siderbar1').masonry('reload');
				}
			});
		}
	});
	jQuery(document).on('click tap', ".bfi-searchfilter .bfi-addfilter", function (e) {
			jQuery(this).toggleClass("bfi-searchfilter-active");
			jQuery(".bfi-filtercontainer").slideToggle("normal", function () {
				if (jQuery.prototype.masonry) {
					jQuery('.main-siderbar, .main-siderbar1').masonry('reload');
				}
			});
	});
	/*
	jQuery(document).on('click tap', ".bfi-option-title ", function (e) {
		jQuery(this).toggleClass("bfi-option-active");
		jQuery(this).next("div").stop('true', 'true').slideToggle("normal", function () {
			if (jQuery.prototype.masonry) {
				jQuery('.main-siderbar, .main-siderbar1').masonry('reload');
			}
		});
	});
	*/
	jQuery(document).on('click tap', ".bfititletoggle", function (e) {
		jQuery(this).toggleClass("bfititletoggle-active");
		jQuery(this).next("div").stop('true', 'true').slideToggle("normal");
	});
	jQuery(document).on('click tap', ".bfiitinerarytitle", function (e) {
		jQuery(this).toggleClass("bfiitinerarytitle-active");
		jQuery(this).next("div").stop('true', 'true').slideToggle("normal");
	});
	
	jQuery(document).on('mouseenter', ".bfi-filter-label", function (e) {
		var $this = jQuery(this);
		var divWidthBefore = $this.width();
		$this.css('width', 'auto');
		$this.css('white-space', 'nowrap');
		var divWidth = $this.width();
		$this.width(divWidthBefore + 1);
		$this.css('white-space', 'normal');
		if (divWidthBefore < divWidth && !$this.attr('title')) {
			$this.attr('title', $this.text());
			if (typeof bfiTooltip  !== "function") {
				jQuery.widget.bridge('bfiTooltip', jQuery.ui.tooltip );
			}
			$this.bfiTooltip({
				position: { my: 'center bottom', at: 'center top-10' },
				tooltipClass: 'bfi-tooltip bfi-tooltip-top '
			});
			$this.bfiTooltip("open");
		}
	});
	jQuery(document).on('click tap', ".bfi-filteroptions a", function (e) {
		listname = jQuery(this).attr("rel1");
		currValue = jQuery(this).attr("rel");
		if (bfi_variables.analyticsEnabled) {
			currValueText = jQuery(this).find(".bfi-filter-label").first().text();
			listname = jQuery(this).attr("rel1");
			listdata = jQuery(this).attr("data-list");

			currAction = jQuery(this).hasClass("bfi-filter-active") ? "Remove" : "Add";
			if (typeof callAnalyticsEEc !== "undefined") {
				callAnalyticsEEc("", "", listname + "|" + currValueText, null, currAction, listdata);
			}
		}
		if (jQuery(this).hasClass("bfi-filter-active"))
		{
			var currForm = jQuery(this).closest("form");
			jQuery(currForm).find("a[rel1^='"+listname+"']").each(function (index, element) {
				if (jQuery(element).attr("rel") == currValue)
				{
					jQuery(element).removeClass("bfi-filter-active");
				}
			});
		}else{
			jQuery(this).toggleClass("bfi-filter-active");
		}

		jQuery(this).closest('form').submit();
	});
	jQuery(document).on('click tap', ".bfi-removefilter", function (e) {
		var rel = jQuery(this).attr("rel");
		var rel1 = jQuery(this).attr("rel1");
		jQuery(".bfi-filteroptions a[rel='"+ rel + "'][rel1='"+ rel1 + "'] ").click();
	});

	// ------- end function filter options ---------

};

jQuery(document).ready(function ($) {
	setTimeout( bookingfor.carouselMerchants(), 1 );
	setTimeout( bookingfor.carouselEvents(), 1 );
	setTimeout( bookingfor.carouselResources(), 1 );
	setTimeout( bookingfor.carouselPoi(), 1 );

	setTimeout( bookingfor.bannerEvents(), 1000 );

	jQuery(document).on('click tap', ".bfi-item", function (e) {
		var currUrl = jQuery(this).attr("data-href");
		if (bookingfor.mobileViewMode && currUrl && currUrl.length && currUrl !== "") {
			e.preventDefault();
			window.location.href = currUrl;
		}		
	});
	jQuery(document).on('click tap', ".bfiviewform", function (e) {
		var currRel = jQuery(this).attr("rel");
		jQuery("."+currRel).toggle();
	});
	jQuery(document).on('click tap', ".bfi-carouser-click", function (e) {
		jQuery.blockUI({
			css: {
				border: 'none;',
				backgroundColor:'none'
				},
			message: '<div class="bfi-spinner"><div class="rect1"></div><div class="rect2"></div><div class="rect3"></div><div class="rect4"></div><div class="rect5"></div></div>',
			overlayCSS: { backgroundColor: '#fff', opacity: .7 }
		});
	});

	//	bookingfor.carouselMerchants();
//	bookingfor.carouselEvents();
//	bookingfor.carouselResources();
	bookingfor.applyfilterdata();
	jQuery(document).on('click tap', ".bfi-currency-switcher-selector", function (e) {
		var currCurrency = bfi_variables.currentCurrency;
		var newCurrency = jQuery(this).attr("rel");
		if (currCurrency !== newCurrency) {
			window.location.href = bookingfor.updateQueryStringParameter(window.location.href, "bfiselectedcurrency", newCurrency);
		}
	});
	
});

/* ------------------- Calculation ------------------- */
bookingfor.bfiCheckOtherAvailability = function (currDuration, currCheckinValue, currPaxes, currPaxages, currResourcegroupId, currAvailabilityTypes, curritemTypeIds, callAjax, currCheckin, currCheckout, currMerchantCategoryIds, currMasterTypeIds, currMerchantTagsIds, currPoints) {
	var currMerchantsList = [];
	var currResourcesList = [];
	var loadAlt = false;

	jQuery(".bfi-check-more:not(.bfi-check-more-initialized)").each(function () {
		jQuery(this).addClass("bfi-check-more-initialized");
		bookingfor.waitSimpleWhiteBlock(jQuery(this));
		var currType = jQuery(this).attr("data-type") || "";
		var currId = jQuery(this).attr("data-id");
		if (currType == "merchant") {
			currMerchantsList.push(currId);
		}
		if (currType == "resource") {
			currResourcesList.push(currId);
		}
	});
	if (currMerchantsList.length > 0) {
		bookingfor.bfiCallOtherAvailability(currDuration, currCheckinValue, currPaxes, currPaxages, currResourcegroupId, currAvailabilityTypes, curritemTypeIds, currMerchantCategoryIds, currMasterTypeIds, currMerchantTagsIds, currPoints, currMerchantsList, currResourcesList, '1', 'merchant', currMerchantsList, currCheckin, currCheckout, callAjax);
	}
	if (currResourcesList.length > 0) {
		bookingfor.bfiCallOtherAvailability(currDuration, currCheckinValue, currPaxes, currPaxages, currResourcegroupId, currAvailabilityTypes, curritemTypeIds, currMerchantCategoryIds, currMasterTypeIds, currMerchantTagsIds, currPoints, currMerchantsList, currResourcesList, '0', 'resource', currResourcesList, currCheckin, currCheckout, callAjax);
	}
};

bookingfor.bfiCallOtherAvailability = function (currDuration, currCheckinValue, currPaxes, currPaxages, currResourcegroupId, currAvailabilityTypes, curritemTypeIds, currMerchantCategoryIds, currMasterTypeIds, currMerchantTagsIds, currPoints, currMerchantsList, currResourcesList, currGroupResultType, currResultType, currList, currCheckin, currCheckout, currResultView, callAjax) {
	var currdata = {
		checkin: currCheckinValue,
		duration: currDuration,
		paxes: currPaxes,
		paxages: currPaxages,
		resourcegroupId: currResourcegroupId,
		cultureCode: bfi_variables.bfi_cultureCode,
		availabilityTypes: currAvailabilityTypes,
		itemTypeIds: curritemTypeIds,
		merchantsList: currMerchantsList.join(","),
		resourcesList: currResourcesList.join(","),
		groupResultType: currGroupResultType,
		resview: currResultView,
	};
	
	if(typeof merchantCategoryIds !== 'undefined' && merchantCategoryIds!='' ){
		currdata.merchantCategoryIds = currMerchantCategoryIds;
	}
	if(typeof masterTypeIds !== 'undefined' && masterTypeIds!='' ){
		currdata.masterTypeIds = currMasterTypeIds;
	}
	if(typeof merchantTagsIds !== 'undefined' && merchantTagsIds!='' ){
		currdata.merchantTagsIds = currMerchantTagsIds;
	}
	if(typeof points !== 'undefined' && points!='' ){
		currdata.points = currPoints;
	}

	xhralternativeMrc = jQuery.get(bookingfor.getActionUrl(null, null, "GetAlternativeDates", jQuery.param(currdata)), function (data) {
		var resultDate = JSON.parse(data) || [];
		jQuery.each(currList, function (key, value) {
			var currSourceId = parseInt(value);
			var listAlternativeDates = jQuery.grep(resultDate, function (d) {
				if (currResultType == "merchant") {
					return (d.MerchantId == currSourceId);
				}
				if (currResultType == "resource") {
					return (d.ProductId == currSourceId);
				}
				return (d.MerchantId == currSourceId);
			});
			bookingfor.bfiBuildSlide(currSourceId, currResultType, listAlternativeDates,currDuration, callAjax);
			jQuery(document).on('click tap', ".bfi-alt-dates", function (e) {
				e.preventDefault();
				currCheckin.val(jQuery(this).attr("data-checkin"));
				currCheckout.val(jQuery(this).attr("data-checkout"));
				calculateQuote();
			});
			jQuery(".bfi-alt-dates-search").on('click tap', function (e) {
				e.stopPropagation();
				e.preventDefault();
				document.location = jQuery(this).attr("href");
			});

		});

		jQuery('.bfi-item').each(function(){
			if ( jQuery(this).find(".slick-slider").length>0  )
			{
				jQuery(this).css('opacity','1');
			}
		})
	});
};

bookingfor.bfiBuildSlide = function (dataid,datatype,datasources,currDuration, callAjax){
		var currThis = jQuery(".bfi-check-more[data-type='" + datatype+ "'][data-id='" + dataid+ "'] ");
		jQuery(currThis).unblock();
		var currSlider = jQuery(currThis).find(".bfi-check-more-slider").first();
		var initialSlide = 0;
		var initialSlideDuration = 0;
		
//		jQuery(currThis).closest(".bfi-item").show();
		
		var totalAlt = 0;
		jQuery.each(datasources, function(key, val) {			
			totalAlt = key;
			var price = val.BestValue ;
			var minstay = val.Duration ;
//			if (val.Duration  <= currDuration)
//			{
//				initialSlide = key;
//			}
			if (initialSlideDuration !=val.Duration  && val.Duration  <= currDuration)
			{
				initialSlide = key;
				initialSlideDuration = val.Duration;
			}
			var checkinDate =  bookingfor.parseODataDate(val.StartDate);
			var checkoutDate = bookingfor.parseODataDate(val.EndDate);
			
			month1 = bookingfor.pad((checkinDate.getMonth() + 1),2);
			month2 = bookingfor.pad((checkoutDate.getMonth() + 1),2);
			day1 = bookingfor.pad((checkinDate.getDate()),2);
			day2 = bookingfor.pad((checkoutDate.getDate()),2);
			day1week = bookingfor.pad((checkinDate.getDay()),2);
			day2week = bookingfor.pad((checkoutDate.getDay()),2);
			if (typeof Intl == 'object' && typeof Intl.NumberFormat == 'function') {
				month1 = checkinDate.toLocaleString(bfi_variables.bfi_cultureCodeBase, { month: "short" });              
				month2 = checkoutDate.toLocaleString(bfi_variables.bfi_cultureCodeBase, { month: "short" });            
				day1week = checkinDate.toLocaleString(bfi_variables.bfi_cultureCodeBase, { weekday: "short" });   
				day2week = checkoutDate.toLocaleString(bfi_variables.bfi_cultureCodeBase, { weekday: "short" }); 
			}

			diff  = new Date(checkoutDate - checkinDate),
			days  = Math.ceil(diff/1000/60/60/24);
			var currSearchUrl = window.location.href; 
				currSearchUrl = currSearchUrl.replace(/(\/page\/\d\/)/gm ,"")
			currSearchUrl = bookingfor.updateQueryStringParameter(currSearchUrl,"checkin",bookingfor.convertDateToIta(checkinDate));
			currSearchUrl = bookingfor.updateQueryStringParameter(currSearchUrl,"checkout",bookingfor.convertDateToIta(checkoutDate));
			currSearchUrl = bookingfor.updateQueryStringParameter(currSearchUrl,"limitstart",0);
			currSearchUrl = bookingfor.updateQueryStringParameter(currSearchUrl,"start",0);
			
			currSearchUrl = bookingfor.updateQueryStringParameter(currSearchUrl,"minqt",1);
			currSearchUrl = bookingfor.updateQueryStringParameter(currSearchUrl,"maxqt",1);

			if(datatype=="merchant"){
				currSearchUrl = bookingfor.updateQueryStringParameter(currSearchUrl,"filter_order",'merchantid:'+dataid);
			}else{
				currSearchUrl = bookingfor.updateQueryStringParameter(currSearchUrl,"filter_order",'resourceid:'+dataid);
			}
			var curravailabilitytype = bookingfor.getUrlParameter("availabilitytype");
			var strSummaryDays = '<span class="bfi-check-more-los">'+minstay+' '+ bfi_variables.bfi_txtNights + ', '+day1week+' – '+day2week+'</span>'; 
			if (curravailabilitytype == "0") {
				minstay += 1;
				strSummaryDays ='<span class="bfi-check-more-los">'+minstay+' '+ bfi_variables.bfi_txtDays + ', '+day1week+' – '+day2week+'</span>'; 
			}
			if(days<1){strSummaryDays ="";}
if(typeof callAjax !== 'undefined' && callAjax){
			currSlider.append('<a  class="bfi-alt-dates" href="'+currSearchUrl+'" data-checkin="'+bookingfor.convertDateToIta(checkinDate)+'"  data-checkout="'+bookingfor.convertDateToIta(checkoutDate)+'" ><span class="bfi-check-more-dates">'+day1+' '+month1+' - '+day2+' '+month2+'</span>'+strSummaryDays+'<span class="bfi-check-more-price">' + bfi_variables.bfi_txtFrom + ' <span class="bfi_' + bfi_variables.currentCurrency + '">'+bookingfor.priceFormat(price, 2, ',', '.')+'</span></span></a>');
}else{
			currSlider.append('<a class="bfi-alt-dates-search" href="'+currSearchUrl+'" data-checkin="'+bookingfor.convertDateToIta(checkinDate)+'"  data-checkout="'+bookingfor.convertDateToIta(checkoutDate)+'" ><span class="bfi-check-more-dates">'+day1+' '+month1+' - '+day2+' '+month2+'</span>'+strSummaryDays+'<span class="bfi-check-more-price">' + bfi_variables.bfi_txtFrom + ' <span class="bfi_' + bfi_variables.currentCurrency + '">'+bookingfor.priceFormat(price, 2, ',', '.')+'</span></span></a>');
}
		});	
		var currSliderWidth = jQuery(currThis).width()-80;
		jQuery(currSlider).width(currSliderWidth);
		var ncolslick = Math.round(currSliderWidth/140);
		if (totalAlt>0)
		{
			if(totalAlt<ncolslick){
				ncolslick = totalAlt+1;
				initialSlide = 0;
			}
			jQuery(currSlider).slick({
				dots: false,
				draggable: false,
				arrows: true,
				initialSlide: initialSlide,
				infinite: false,
				slidesToShow: ncolslick,
				slidesToScroll: ncolslick,
			});
			jQuery(this).css('opacity','1');

		}else{
			jQuery(currThis).hide();
//			jQuery(currThis).closest(".bfi-item").hide()
		}
}
/* ------------------- END Calculation ------------------- */

/* ------------------- bfiGetAllTags------------------- */

bookingfor.bfiGetAllTags = function (callback){
	if (!bookingfor.loadedAllTags)
	{
		jQuery.get(bookingfor.getActionUrl(null, null, "GetAllTags"), function(data) {
			bookingfor.loadedAllTags=true;
			if(data!=null){
				jQuery.each(data || [], function(key, val) {
					if (val.ImageUrl!= null && val.ImageUrl!= '') {
						var $imageurl = bfi_variables.imgPathTags.replace("[img]", val.ImageUrl );		
						var $imageurlError = bfi_variables.imgPathErrorTags.replace("[img]", val.ImageUrl );		
						/*--------getName----*/
						var $name = val.Name;
						/*--------getName----*/
						bookingfor.tagLoaded[val.TagId] = '<img src="' + $imageurl + '" onerror="this.onerror=null;this.src=\'' + $imageurlError + '\';" alt="' + $name + '" data-toggle="tooltip" title="' + $name + '" />';
					} else  if (val.IconSrc != null && val.IconSrc != '') {
						if (val.IconType != null && val.IconType != '')
						{
							var fontIcons = val.IconType .split(";");
							if (fontIcons[0] == 'fontawesome5')
							{
								bookingfor.tagLoaded[val.TagId] = '<i class="' + val.IconSrc + '" data-toggle="tooltip" title="' + val.Name + '"> </i> ';
							}
							if (fontIcons[0] == 'fontawesome4')
							{
								bookingfor.tagLoaded[val.TagId] = '<i class="fa ' + val.IconSrc + '" data-toggle="tooltip" title="' + val.Name + '"> </i> ';
							}

						}else{
							bookingfor.tagLoaded[val.TagId] = '<i class="fa ' + val.IconSrc + '" data-toggle="tooltip" title="' + val.Name + '"> </i> ';
						}
					} else {
						bookingfor.tagLoaded[val.TagId] = val.Name;
					}
					bookingfor.tagNameLoaded[val.TagId] = val.Name; //solo il nome!
					bookingfor.tagFullLoaded[val.TagId] = val; //Tutto!
				});	
				if (callback) {
					callback();
				}
			}
			if (typeof bfiTooltip  !== "function") {
				jQuery.widget.bridge('bfiTooltip', jQuery.ui.tooltip );
			}
			jQuery('[data-toggle="tooltip"]').bfiTooltip({
				position : { my: 'center bottom', at: 'center top-10' },
				tooltipClass: 'bfi-tooltip bfi-tooltip-top '
			}); 

		},'json');
	}else if (callback) {
		callback();
	}
}
/* ------------------- END bfiGetAllTags------------------- */
// Accepts the array and key
//bookingfor.groupBy =  function(array, key){
//  // Return the end result
//  return array.reduce((result, currentValue) => {
//    // If an array already present for key, push it to the array. Else create an array and push the object
//    (result[currentValue[key]] = result[currentValue[key]] || []).push(
//      currentValue
//    );
//    // Return the current iteration `result` value, this will be taken as next iteration `result` value and accumulate
//    return result;
//  }, {}); // empty object is the initial value for result object
//};

/* ------------------- bfiGetAllTags------------------- */
bookingfor.bfiGetAllPoiCategories = function (callback){
	if (!bookingfor.loadedPoiCategories)
	{
		jQuery.get(bookingfor.getActionUrl(null, null, "GetPointOfInterestCategories"), function(data) {
			bookingfor.loadedPoiCategories=true;
			if(data!=null){
				jQuery.each(data || [], function(key, val) {
					val.overlayMaps = L.featureGroup([]);
					bookingfor.PoiCategories[val.PointOfInterestCategoryId] = val;
				});	
				if (callback) {
					callback();
				}
			}
		},'json');
	}else if (callback) {
		callback();
	}
};


bookingfor.bfiGetAllPois = function (callback){
	if (!bookingfor.loadedAllPois)
	{
		jQuery.get(bookingfor.getActionUrl(null, null, "GetPointsOfInterest"), function(data) {
			bookingfor.loadedAllPois=true;
			if(data!=null){
				jQuery.each(data || [], function(key, val) {
					bookingfor.Pois[key] = val;
				});	
				if (callback) {
					callback();
				}
			}
		},'json');
	}else if (callback) {
		callback();
	}
};

bookingfor.bfiLoadPoisByCategoryId = function(currId, callback){
//		var currPois = [];
//		jQuery.get(bookingfor.getActionUrl(null, null, "GetPointsOfInterest"), function(data) {
//			if(data!=null){
//				jQuery.each(data || [], function(key, val) {
//					currPois[] = val;
//				});	
//				if (callback) {
//					callback();
//				}
//			}
//		},'json');
//
//		if (bfi_variables.bfiMapsFree) {
//
//			for (i=0; i<bookingfor.Pois.length ;i++ )
//			{
//				var currpoi = bookingfor.Pois[i];
//				var currLatLng = new Leaflet.LatLng(currpoi.Address.XPos, currpoi.Address.YPos);
//				var currMarker = new L.Marker(currLatLng);
//				var imgUrl = bfi_variables.defaultImage;
//				if (currpoi.DefaultImg!="null")
//				{
//					imgUrl = currpoi.DefaultImg;
//				}
//				var currTmplHtml = '';
//					currTmplHtml += '<a href="' + currpoi.route + '" target="_blank"><img src="' + imgUrl+ '" class="bfi-img-responsive" /></a> ';
//					currTmplHtml += '<div style="padding:5px;">';
//					currTmplHtml += '	<div class="bfi-item-title">';
//					currTmplHtml += '		<a href="' + currpoi.route + '" target="_blank">' + currpoi.Name + '</a> ';
//					currTmplHtml += '	</div>';
//					currTmplHtml += '</div>';
//
//				currMarker.Data = currTmplHtml;
//				var singleCategory = currpoi.CategoryNames.split(',');
////				console.log("single poi Category");
////				console.log(singleCategory);
////				console.log("---------------------");
//
//				jQuery.each(singleCategory, function(i, el){
//					var currKey = el.split(' ').join('');
//					if(currKey!="" && jQuery.inArray(currKey, overlayMaps)){
//						overlayMaps[currKey].addLayer(currMarker);
//					}
//				});
//				currMarker.on('click', function(event) {
//					var currMarkerClicked = event.sourceTarget;
//					bookingfor.bfiShowMarkerInfoPois(currMap, currMarkerClicked, currMarkerClicked.Data);
//				});
//
//			}
};


bookingfor.bfiLoadPoisByCategories = function(currMap, callback){
		bookingfor.bfiGetAllPoiCategories(function () {
		if (bfi_variables.bfiMapsFree && bookingfor.PoiCategories.length>0) {
			//----------Sidebar---------
			var htmlContent = '<div class="bfi-maplayers-items-title">'+ bfi_variables.bfi_txttitlepois +'<i class="fa fa-chevron-down bfi-pull-right"></i></div>';
			jQuery.each(bookingfor.PoiCategories || [], function(key, val) {
				if (typeof(val) !== "undefined")
				{
					htmlContent += '<div class="bfi-maplayers-item" data-layer="' + key +'" style="display:none;"><i class="fa fa-street-view"></i> ' + val.Name +' <i class="fa fa-angle-right bfi-pull-right"></i></div>';
				}
			});

			var currSidebar = jQuery(".bfi-maplayers");
			var currContaideritems = jQuery(".bfi-maplayers-items");

			if (currSidebar && currSidebar.length)
			{
				if (currContaideritems && currContaideritems.length)
				{

				}else{
					currContaideritems = jQuery('<div class="bfi-maplayers-items"></div>');
					currSidebar.append(currContaideritems);
					
				}
				currContaideritems.html(htmlContent);
			}else{
				
				L.control.custom({
					position: 'topleft',
					content : '<div class="bfi-maplayers-items">'+htmlContent+'</div>' ,
					classes : 'bfi-maplayers',
			   })
				.addTo(currMap);
			}

			jQuery(document).on('click tap', ".bfi-maplayers-items-title", function (e) {
					currentTitleDiv=jQuery(this);
					if (currentTitleDiv.hasClass("bfi-maplayers-items-title-active"))
					{
						jQuery(".bfi-maplayers-item").hide();
						currentTitleDiv.removeClass("bfi-maplayers-items-title-active");
					}else{
						jQuery(".bfi-maplayers-item").show();
						currentTitleDiv.addClass("bfi-maplayers-items-title-active");
					}
			});

			//click su categoria
			jQuery(document).on('click tap', ".bfi-maplayers-item", function (e) {
					currentDiv=jQuery(this).first();
					currKey = currentDiv.attr("data-layer");
					if (currentDiv.hasClass("bfi-maplayers-item-active"))
					{
						currMap.removeLayer(bookingfor.PoiCategories[currKey].overlayMaps);
					}else{
						bookingfor.PoiCategories[currKey].overlayMaps =
						currMap.addLayer(overlayMaps[currKey]);
						var currBounds = overlayMaps[currKey].getBounds();
						currMap.fitBounds(currBounds);
					}
					currentDiv.toggleClass("bfi-maplayers-item-active");
			});
		
		}else{
			// TODO: googlemaps
		}; 
		if (callback) {
			callback();
		}
	});
};
bookingfor.lastMarker;
bookingfor.lastMarkerCurrMap;
bookingfor.bfiLoadSearchMap = function(currMap, callback){
		var txtSearch = '<div class="bfi-input-group">';
			txtSearch += '                                            <span class="bfi-input-group-btn">';
			txtSearch += '                                                <button type="button" class="btn btn-default bfi-btn bfi-alternative2">';
			txtSearch += '                                                    <i class="fa fa-search" aria-hidden="true"></i>';
			txtSearch += '                                                </button>';
			txtSearch += '                                            </span>';
			txtSearch += '		<input type="text" name="searchterm" value="" class="bfi-inputtext bfi-autocomplete bfi-autocomplete-map" placeholder="'+bfi_variables.bfi_txtsearchterm+'..."  data-scope="0,1,2,3,4,5,6,7" />'
			txtSearch += '                                            <span class="bfi-input-group-btn">';
			txtSearch += '                                                <button type="button" class="btn btn-default bfi-btn bfi-alternative2 bfi-autocomplete-map-delete">';
			txtSearch += '                                                    <i class="fa fa-times-circle" aria-hidden="true"></i>';
			txtSearch += '                                                </button>';
			txtSearch += '                                            </span>';
			txtSearch += '</div>';
			bookingfor.lastMarkerCurrMap = currMap;
			jQuery(document).on('click tap', ".bfi-autocomplete-map-delete ", function (e) {
					jQuery(".bfi-autocomplete-map").val("");
					if (bfi_variables.bfiMapsFree) {
						if (typeof bookingfor.lastMarker !== "undefined")
						{
							bookingfor.lastMarkerCurrMap.removeLayer(bookingfor.lastMarker );
						}
					} else {
					}
			});			
		if (bfi_variables.bfiMapsFree) {
			var currSidebar = jQuery(".bfimapsearch");
			if (currSidebar && currSidebar.length)
			{
			}else{  //create controll
				
				L.control.custom({
					position: 'topleft',
					content : '<div class="bfimapsearch">'+txtSearch+'</div>' ,
					classes : 'bfi-maplayerssearch',
			   })
				.addTo(currMap);
			}
		}
//load autocomplete
				var currAutocompleteMap = jQuery(".bfi-autocomplete-map").first();
				var currScope = currAutocompleteMap.attr("data-scope");
				if (typeof currScope === "undefined" || !currScope.length){
					currScope = "";
				}
				var currFormFilter = jQuery("#searchformfilter");

				currAutocompleteMap.blur(function(){
         var keyEvent = jQuery.Event("keydown");
         keyEvent.keyCode = jQuery.ui.keyCode.ENTER;
         jQuery(this).trigger(keyEvent);
     }).autocomplete({
					autoFocus: true,
					source: function (request, response) {
						var previous_request = currAutocompleteMap.data( "jqXHR" );
						if( previous_request ) {
							previous_request.abort();
						}
						var urlSearch =  bfi_variables.bfi_urlSearchByText + "?action=SearchByText&term=" + request.term + "&resultClasses=" + currScope + "&maxresults=5&cultureCode=" + bfi_variables.bfi_cultureCode;
						currAutocompleteMap.data( "jqXHR",
//							jQuery.getJSON(bookingfor.getActionUrl(null, null, "SearchByText", "bfi_term=" + request.term + "&bfi_resultclasses=" + currScope+ "&bfi_maxresults=5"), function (data) {
							jQuery.getJSON(urlSearch, function (data) {
								if (data && data.length) {
									jQuery.each(data, function (key, item) {
										var currentVal = "";
										if (item.ItemTypeOrder == 0 || item.ItemTypeOrder == 2 || item.ItemTypeOrder == 4 || item.ItemTypeOrder == 6) { currentVal = "zoneIds|" + item.ZoneId; }
										if (item.ItemTypeOrder == 1) { currentVal = "stateIds|" + item.StateId + ":" + item.XPos + ":" + item.YPos; }
										if (item.ItemTypeOrder == 3) { currentVal = "regionIds|" + item.RegionId + ":" + item.XPos + ":" + item.YPos; }
										if (item.ItemTypeOrder == 5) { currentVal = "cityIds|" + item.CityId + ":" + item.XPos + ":" + item.YPos; }
										if (item.ItemTypeOrder == 7) { currentVal = "poiIds|" + item.PointOfInterestId + ":" + item.XPos + ":" + item.YPos;  }
//										if (item.ItemTypeOrder == 8) { currentVal = "evtCategoryids|" + item.EventCategoryId; }
//										if (item.ItemTypeOrder == 9) { currentVal = "merchantCategoryId|" + item.MerchantCategoryId; }
//										if (item.ItemTypeOrder == 10) { currentVal = "groupCategoryId|" + item.ProductGroupCategoryId; }
//										if (item.ItemTypeOrder == 11) { currentVal = "masterTypeId|" + item.ProductCategoryId; }
//										if (item.ItemTypeOrder == 12) { currentVal = "evtTagIds|" + item.EventTagId; }
//										if (item.ItemTypeOrder == 13) { currentVal = "merchantTagIds|" + item.MerchantTagId; }
//										if (item.ItemTypeOrder == 14) { currentVal = "groupTagIds|" + item.ProductGroupTagId; }
//										if (item.ItemTypeOrder == 15) { currentVal = "resourceTagIds|" + item.ProductTagId; }
//										if (item.ItemTypeOrder == 16) { currentVal = "eventIds|" + item.EventId + ":" + item.XPos + ":" + item.YPos; }
//										if (item.ItemTypeOrder == 17) { currentVal = "merchantIds|" + item.MerchantId; }
//										if (item.ItemTypeOrder == 18) { currentVal = "groupresourceIds|" + item.ProductGroupId; }
//										if (item.ItemTypeOrder == 19) { currentVal = "resourceIds|" + item.ProductId; }
										/*
										if (item.StateId) { currentVal = "stateIds|" + item.StateId; }
										if (item.RegionId) { currentVal = "regionIds|" + item.RegionId; }
										if (item.CityId) { currentVal = "cityIds|" + item.CityId; }
										if (item.ZoneId) { currentVal = "zoneIds|" + item.ZoneId; }
//										if (item.MerchantCategoryId) { currentVal = "merchantCategoryId|" + item.MerchantCategoryId; }
//										if (item.ProductCategoryId) { currentVal = "masterTypeId|" + item.ProductCategoryId; }
//										if (item.MerchantId) { currentVal = "merchantIds|" + item.MerchantId; }
//										if (item.ProductGroupId) { currentVal = "groupresourceIds|" + item.ProductGroupId; }
//										if (item.MerchantTagId) { currentVal = "merchantTagIds|" + item.MerchantTagId; }
//										if (item.ProductTagId) { currentVal = "productTagIds|" + item.ProductTagId; }
										*/
										item.Value = currentVal;
									});
									response(data);
									currAutocompleteMap.removeClass("ui-autocomplete-loading");
								} else {
									response([{
										Name: bfi_variables.bfi_txtnoresult
									}]);
									currAutocompleteMap.removeClass("ui-autocomplete-loading");
								}
							})
						);
					},
					/*response: function( event, ui ) {
						jQuery(this).removeClass("ui-autocomplete-loading");
					},*/
					minLength: 2,
					delay: 250,
					select: function (event, ui) {
						var selectedVal = ui.item.Value;
						//			var selectedVal = jQuery(event.srcElement).attr("data-value");
						if (selectedVal.length) {
							
							currFormFilter.find("[name=stateIds],[name=regionIds],[name=cityIds],[name=zoneIds],[name=merchantCategoryId],[name=groupCategoryId],[name=masterTypeId],[name=merchantTagIds],[name=groupTagIds],[name=productTagIds],[name=merchantIds],[name=groupresourceIds],[name=resourceIds],[name=points],[name=filter_order]").val("");
							currFormFilter.find("[name=searchTermValue]").val(selectedVal);
							switch (selectedVal.split('|')[0]) {
								case "stateIds":
								case "regionIds":
                                    var completeValue = selectedVal.split('|')[1];
                                    currAutocomplete.closest("form").find("[name=" + selectedVal.split('|')[0] + "]").val(completeValue.split(":")[0]);
                                    break;
								case "cityIds":
								case "poiIds":
									var completeValue = selectedVal.split('|')[1];
									var geoPos = completeValue.split(":");
									currFormFilter.find("[name=points]").val("0|" + geoPos[1] + " " + geoPos[2]);
									var currLat = geoPos[1];
									var currLon =geoPos[2];
									if (bfi_variables.bfiMapsFree) {
										var currLatLng = new Leaflet.LatLng(currLat,currLon);
										currMap.setView(currLatLng,16);
										var bfiIconClass="bfi-mapIcon1";
										var bfiIcon = Leaflet.divIcon({
												iconSize: [20, 40],
												iconAnchor: [10, 39],
												html: '<i class="fa fa-map-marker"></i>',
												className: 'bfi-mapIcon ' + bfiIconClass
											});
											if (typeof bookingfor.lastMarker !== "undefined")
											{
												currMap.removeLayer(bookingfor.lastMarker );
											}
											bookingfor.lastMarker = new L.Marker(currLatLng, { icon: bfiIcon });
											currMap.addLayer(bookingfor.lastMarker );
									} else {
										var currLatLng = new google.maps.LatLng(currLat, currLon);
										currMap.setCenter(currLatLng);

									}
									break;
//								case "merchantIds":
//									currFormFilter.find("[name=filter_order]").val("merchantid:" + selectedVal.split('|')[1]);
//									break;
//								case "groupresourceIds":
//									currFormFilter.find("[name=filter_order]").val("parentid:" + selectedVal.split('|')[1]);
//									break;
//								case "resourceIds":
//									currFormFilter.find("[name=filter_order]").val("productid:" + selectedVal.split('|')[1]);
//									break;
								default:
									currFormFilter.find("[name=" + selectedVal.split('|')[0] + "]").val(selectedVal.split('|')[1]);
									break;
							}
							jQuery(this).val(ui.item.Name);
							event.preventDefault();
						}
						//log( "Selected: " + ui.item.value + " aka " + ui.item.id );
					},
					change: function( event, ui ) {
						if (currAutocompleteMap.val().length<2) {
							currFormFilter.find("[name=stateIds],[name=regionIds],[name=cityIds],[name=zoneIds],[name=merchantCategoryId],[name=groupCategoryId],[name=masterTypeId],[name=merchantTagIds],[name=groupTagIds],[name=productTagIds],[name=merchantIds],[name=groupresourceIds],[name=resourceIds],[name=points],[name=filter_order]").val("");
						}
					},
					open: function () {
						jQuery(this).data("uiAutocomplete").menu.element.addClass("bfi-autocomplete");
					},
					clearButton: true
				});


				currAutocompleteMap.data("ui-autocomplete")._renderItem = function (ul, item) {
					
					var currentVal = "";
					var htmlContent = item.Name;
					if (item.ItemTypeOrder == 0 || item.ItemTypeOrder == 2 || item.ItemTypeOrder == 4 || item.ItemTypeOrder == 6) { 
						currentVal = "zoneIds|" + item.ZoneId;
					}
					if (item.ItemTypeOrder == 1) { currentVal = "stateIds|" + item.StateId + ":" + item.XPos + ":" + item.YPos; }
					if (item.ItemTypeOrder == 3) { currentVal = "regionIds|" + item.RegionId + ":" + item.XPos + ":" + item.YPos; }
					if (item.ItemTypeOrder == 5) { currentVal = "cityIds|" + item.CityId + ":" + item.XPos + ":" + item.YPos; }
					if (item.ItemTypeOrder == 7) { currentVal = "poiIds|" + item.PointOfInterestId + ":" + item.XPos + ":" + item.YPos;  }
					if (item.ItemTypeOrder == 8) { currentVal = "evtCategoryids|" + item.EventCategoryId; }
					if (item.ItemTypeOrder == 9) { currentVal = "merchantCategoryId|" + item.MerchantCategoryId; }
					if (item.ItemTypeOrder == 10) { currentVal = "groupCategoryId|" + item.ProductGroupCategoryId; }
					if (item.ItemTypeOrder == 11) { currentVal = "masterTypeId|" + item.ProductCategoryId; }
					if (item.ItemTypeOrder == 12) { currentVal = "evtTagIds|" + item.EventTagId; }
					if (item.ItemTypeOrder == 13) { currentVal = "merchantTagIds|" + item.MerchantTagId; }
					if (item.ItemTypeOrder == 14) { currentVal = "groupTagIds|" + item.ProductGroupTagId; }
					if (item.ItemTypeOrder == 15) { currentVal = "resourceTagIds|" + item.ProductTagId; }
					if (item.ItemTypeOrder == 16) { currentVal = "eventIds|" + item.EventId + ":" + item.XPos + ":" + item.YPos; }
					if (item.ItemTypeOrder == 17) { currentVal = "merchantIds|" + item.MerchantId; }
					if (item.ItemTypeOrder == 18) { currentVal = "groupresourceIds|" + item.ProductGroupId; }
					if (item.ItemTypeOrder == 19) { currentVal = "resourceIds|" + item.ProductId; }
					
					switch (item.ItemTypeOrder) {
						case 0:
						case 1:
						case 2:
						case 3:
						case 4:
						case 5:
						case 6:
							htmlContent = '<i class="fa fa-map-marker"></i>&nbsp;' + item.Name;
							break;
						case 7:
							htmlContent = '<i class="fa fa-street-view"></i>&nbsp;' + item.Name;
							break;
						case 8:
						case 9:
						case 10:
						case 11:
						case 17:
							htmlContent = '<i class="fa fa-building"></i>&nbsp;' + item.Name;
							break;
						case 18:
						case 19:
							htmlContent = '<i class="fa fa-bed"></i>&nbsp;' + item.Name;
							break;
						case 12:
						case 13:
						case 14:
						case 15:
							htmlContent = '<i class="fa fa-tag"></i>&nbsp;' + item.Name;
							break;
					}
					
					if (currentVal.length) {
						return jQuery("<li>").attr("data-value", currentVal).html(htmlContent).appendTo(ul);
					} else {
						return jQuery("<li>").attr("data-value", "").html(htmlContent).addClass("ui-state-disabled").appendTo(ul);
					}
										
										
					/*
					var currentVal = "";
					if (item.StateId) { currentVal = "stateIds|" + item.StateId; }
					if (item.RegionId) { currentVal = "regionIds|" + item.RegionId; }
					if (item.CityId) { currentVal = "cityIds|" + item.CityId; }
					if (item.ZoneId) { currentVal = "locationzone|" + item.ZoneId; }
					if (item.MerchantCategoryId) { currentVal = "merchantCategoryId|" + item.MerchantCategoryId; }
					if (item.ProductCategoryId) { currentVal = "masterTypeId|" + item.ProductCategoryId; }
					if (item.MerchantId) { currentVal = "merchantIds|" + item.MerchantId; }
					if (item.ProductGroupId) { currentVal = "groupresourceIds|" + item.ProductGroupId; }
					if (item.MerchantTagId) { currentVal = "merchantTagIds|" + item.MerchantTagId; }
					if (item.ProductTagId) { currentVal = "productTagIds|" + item.ProductTagId; }

					var text = item.Name;
					if (item.StateId || item.RegionId || item.CityId || item.ZoneId) { text = '<i class="fa fa-map-marker"></i>&nbsp;' + text; }
					if (item.MerchantCategoryId || item.ProductCategoryId || item.MerchantId) { text = '<i class="fa fa-building"></i>&nbsp;' + text; }
					if (item.MerchantTagId || item.ProductTagId) { text = '<i class="fa fa-tag"></i>&nbsp;' + text; }
					if (currentVal.length) {
						return jQuery("<li>").attr("data-value", currentVal).html(text).appendTo(ul);
					} else {
						return jQuery("<li>").attr("data-value", "").html(text).addClass("ui-state-disabled").appendTo(ul);
					}
					*/
				};


};

bookingfor.bfiLoadPois = function(currMap, callback){
//		bookingfor.bfiGetAllPois(function () {
//		if (bfi_variables.bfiMapsFree) {
////			var poi = L.layerGroup([]);
//			var categories = bookingfor.groupBy(bookingfor.Pois,"CategoryNames");	
//
//			var keys = Object.keys(categories);
//			var listKeys = [];
//			var overlayMaps = [];
//			for (var key of keys) {
//				var singlekeys = key.split(',');
//				jQuery.each(singlekeys, function(i, el){
//					var currKey = el; //.split(' ').join('');
//					if(currKey!="" && jQuery.inArray(currKey, listKeys) === -1) listKeys.push(currKey);
//				});
//			}
//			for (var singleKey of listKeys) {
//				overlayMaps[singleKey]=L.featureGroup([]);
//			}
//
//
//			for (i=0; i<bookingfor.Pois.length ;i++ )
//			{
//				var currpoi = bookingfor.Pois[i];
//				var currLatLng = new Leaflet.LatLng(currpoi.Address.XPos, currpoi.Address.YPos);
//				var currMarker = new L.Marker(currLatLng);
//				var imgUrl = bfi_variables.defaultImage;
//				if (currpoi.DefaultImg!="null")
//				{
//					imgUrl = currpoi.DefaultImg;
//				}
//				var currTmplHtml = '';
//					currTmplHtml += '<a href="' + currpoi.route + '" target="_blank"><img src="' + imgUrl+ '" class="bfi-img-responsive" /></a> ';
//					currTmplHtml += '<div style="padding:5px;">';
//					currTmplHtml += '	<div class="bfi-item-title">';
//					currTmplHtml += '		<a href="' + currpoi.route + '" target="_blank">' + currpoi.Name + '</a> ';
//					currTmplHtml += '	</div>';
//					currTmplHtml += '</div>';
//
//				currMarker.Data = currTmplHtml;
//				var singleCategory = currpoi.CategoryNames.split(',');
////				console.log("single poi Category");
////				console.log(singleCategory);
////				console.log("---------------------");
//
//				jQuery.each(singleCategory, function(i, el){
//					var currKey = el; //.split(' ').join('');
//					if(currKey!="" && jQuery.inArray(currKey, overlayMaps)){
//						overlayMaps[currKey].addLayer(currMarker);
//					}
//				});
//				currMarker.on('click', function(event) {
//					var currMarkerClicked = event.sourceTarget;
//					bookingfor.bfiShowMarkerInfoPois(currMap, currMarkerClicked, currMarkerClicked.Data);
//				});
//
//			}
//
//
//			//----------Sidebar---------
//			var htmlContent = '<div class="bfi-maplayers-items-title">'+ bfi_variables.bfi_txttitlepois +'<i class="fa fa-chevron-down bfi-pull-right"></i></div>';
//
//			Object.keys(overlayMaps).forEach(function (item, index) {
////				console.log(item);
//				var countLayer = overlayMaps[item].getLayers().length;
//				htmlContent += '<div class="bfi-maplayers-item" data-layer="' + item +'" style="display:none;"><i class="fa fa-street-view"></i> ' + item +' (' + countLayer + ') <i class="fa fa-angle-right bfi-pull-right"></i></div>';
//				});
//
//			var currSidebar = jQuery(".bfi-maplayers");
//			var currContaideritems = jQuery(".bfi-maplayers-items");
//
//			if (currSidebar && currSidebar.length)
//			{
//				if (currContaideritems && currContaideritems.length)
//				{
//
//				}else{
//					currContaideritems = jQuery('<div class="bfi-maplayers-items"></div>');
//					currSidebar.append(currContaideritems);
//					
//				}
//				currContaideritems.html(htmlContent);
//			}else{
//				
//				L.control.custom({
//					position: 'topleft',
//					content : '<div class="bfi-maplayers-items">'+htmlContent+'</div>' ,
//					classes : 'bfi-maplayers',
//			   })
//				.addTo(currMap);
//			}
//
//			jQuery(document).on('click tap', ".bfi-maplayers-items-title", function (e) {
//					currentTitleDiv=jQuery(this);
//					if (currentTitleDiv.hasClass("bfi-maplayers-items-title-active"))
//					{
//						jQuery(".bfi-maplayers-item").hide();
//						currentTitleDiv.removeClass("bfi-maplayers-items-title-active");
//					}else{
//						jQuery(".bfi-maplayers-item").show();
//						currentTitleDiv.addClass("bfi-maplayers-items-title-active");
//					}
//			});
//
//
//			jQuery(document).on('click tap', ".bfi-maplayers-item", function (e) {
////					console.log("bfi-maplayers-item clicked");
////					e.preventDefault();
//					currentDiv=jQuery(this).first();
//					currKey = currentDiv.attr("data-layer");
//					if (currentDiv.hasClass("bfi-maplayers-item-active"))
//					{
//						currMap.removeLayer(overlayMaps[currKey]);
//					}else{
//						currMap.addLayer(overlayMaps[currKey]);
//						var currBounds = overlayMaps[currKey].getBounds();
////						console.log(currBounds);
//						currMap.fitBounds(currBounds);
//					}
//					currentDiv.toggleClass("bfi-maplayers-item-active");
//			});
////			var sidebar = L.control.sidebar({ container: 'bfisidebar' }).addTo(currMap);
////			var currid=0;
////			var openFirst="";
////
////			for (var key of listKeys) {
//////				console.log(key)
////				if (key!="")
////				{
////					 var currKey = key.split(' ').join('');
////					 if (openFirst=="")
////					 {
////						 openFirst=currKey;
////					 }
////					  sidebar
////							.addPanel({
////							id: currKey,                     // UID, used to access the panel
////							tab: '<i class="fa fa-street-view"></i>',  // content can be passed as HTML string,
////							pane: key,
////							title: key,              // an optional pane header
////							});
////					currid+=1;
////				}	
////			}
////			sidebar.open(openFirst);
//			//sidebar.open('poi');			
//
//			//----------Sidebar---------
//
//
////			var overlayMaps = {
////					"Poi": poi
////				};
////			var markersLayerPoi =  L.control.layers(null, overlayMaps, {collapsed:false}).addTo(currMap);
//		}else{
//			// TODO: googlemaps
//		}; 
//		if (callback) {
//			callback();
//		}
//	});
}; 
bookingfor.bfiShowOverlayMaps = function (currMap, layerKey ) {

}

bookingfor.bfiShowMarkerInfoPois = function (currMap, marker, data) {
	var customPopupOptions =
	{
		'maxWidth': '250',
		'className': 'bfi-custompopup',
		closeButton: false
	};
//	var data = jQuery("#markerInfo" + marker.extId).html();
	if (bfi_variables.bfiMapsFree) {
		currMap.setView(marker.getLatLng());
		marker.unbindPopup();
		//		marker.bindPopup(data).openPopup();
		marker.bindPopup(data, customPopupOptions).openPopup();

	} else {
		//if (infowindow) infowindow.close();
		if (typeof infowindow!== 'undefined') infowindow.set("map", null);
		currMap.setCenter(marker.position);
		//infowindow = new google.maps.InfoWindow({ content: data });
		//infowindow.open(mapSearch, marker);
		Popup = bookingfor.bfiCreatePopupClass();
		infowindow = new Popup(marker.position, createElementFromHTML(data));
		infowindow.setMap(mapSearch);
	}
};


/* ------------------- END bfiGetAllTags------------------- */

/* ------------------- LocalStorage------------------- */
bookingfor.bfiAddMerchant = function (newMerchant){
	if (typeof(Storage) !== "undefined" && typeof window.bfiLastMerchantLoaded === "undefined" ) {
		// Code for localStorage/sessionStorage.
		window.bfiLastMerchantLoaded =1;
		var currMerchants = [];
		if (localStorage.lastmerchants) {
			currMerchants = JSON.parse(localStorage.getItem("lastmerchants"));
			if (currMerchants.length>4)
			{
				currMerchants.pop();
			}
			var currIndex = -1;
			for (i=0;i<currMerchants.length ;i++ )
			{
				if (bookingfor.isEquivalent(currMerchants[i], newMerchant, 'id,url'))
				{
					currIndex = i;
				}
			}
			if (currIndex > -1) {
				 currMerchants.splice(currIndex, 1);
			}
			currMerchants.unshift(newMerchant);
		} else {
			currMerchants = [newMerchant];
		}
		localStorage.setItem("lastmerchants", JSON.stringify(currMerchants));
	}
}
bookingfor.bfiAddLastSearched = function (newSearch){
	if (typeof(Storage) !== "undefined" && typeof window.bfiLastMerchantLoaded === "undefined" ) {
		// Code for localStorage/sessionStorage.
		window.bfiLastSearchedLoaded =1;
		var currSearch = [];
		if (localStorage.lastsearch) {
			currSearch = JSON.parse(localStorage.getItem("lastsearch"));
			if (currSearch.length>4)
			{
				currSearch.pop();
			}
			var currIndex = -1;
			for (i=0;i<currSearch.length ;i++ )
			{
				if (bookingfor.isEquivalent(currSearch[i], newSearch, 'id,url'))
				{
					currIndex = i;
				}
			}
			if (currIndex > -1) {
				 currSearch.splice(currIndex, 1);
			}
			currSearch.unshift(newSearch);
		} else {
			currSearch = [newSearch];
		}
		localStorage.setItem("lastsearch", JSON.stringify(currSearch));
	}
}
/* ------------------- END LocalStorage ------------------- */
/* ------------------- mappa merchant ------------------- */
bookingfor.loadSingleMap = function (divid){
	var currMapContainer = jQuery("#"+divid);
	if (currMapContainer && currMapContainer.length)
	{
		var currLat = currMapContainer.attr("data-lat");
		var currLon = currMapContainer.attr("data-lon");
		var currMarkerLat = currMapContainer.attr("data-markerlat");
		var currMarkerLon = currMapContainer.attr("data-markerlon");
		var currMarkerType = currMapContainer.attr("data-markettype");
		var currMarker;

		if (bfi_variables.bfiMapsFree) { //leaflet
			Leaflet = L.noConflict();
			bookingfor.currLatLng = new Leaflet.LatLng(currLat,currLon);
			var container = Leaflet.DomUtil.get(divid);
			if(container != null){
				container._leaflet_id = null;
			}			
			bookingfor.currMap = Leaflet.map(divid).setView(bookingfor.currLatLng, bfi_variables.bfi_mapstartzoom);
			bookingfor.currMap.zoomControl.setPosition('topright');			
			var OpenStreetMap_Mapnik = Leaflet.tileLayer(bfi_variables.bfi_freemaptileurl, {
				maxZoom: 19,
				attribution: bfi_variables.bfi_freemaptileattribution
			});
			OpenStreetMap_Mapnik.addTo(bookingfor.currMap );

			if (currMarkerLat && currMarkerLat.length && currMarkerLon && currMarkerLon.length )
			{
				var markerLatLng = new Leaflet.LatLng(currMarkerLat,currMarkerLon);
				if (currMarkerType && currMarkerType.length )
				{
					var bfiIconClass="";
					if (currMarkerType=="0")
					{
						bfiIconClass = "bfi-mapIcon0"
					}
					if (currMarkerType=="1")
					{
						bfiIconClass = "bfi-mapIcon1"
					}

					var bfiIcon = Leaflet.divIcon({
						iconSize: [20, 40],
						iconAnchor: [10, 39],
						html: '<i class="fa fa-map-marker"></i>',
						className: 'bfi-mapIcon ' + bfiIconClass
					});
					if (currMarkerType=="2") //evento 
					{
						var currnNameDay = currMapContainer.attr("data-nameday");
						var currnDay = currMapContainer.attr("data-day");
						var currnMonth = currMapContainer.attr("data-month");
						var bfiIcon = Leaflet.divIcon({
							iconSize:null,
							html: '<div class="bfi-map-label blulighttheme"><div class="bfi-map-label-content bfi-map-event">'+currnNameDay+' <div class="bfi-map-event-day">'+currnDay+'</div>'+currnMonth+' </div><div class="bfi-map-label-arrow"></div></div>'
						});
					}
					currMarker = new L.Marker(markerLatLng, { icon: bfiIcon, draggable: false });
				}else{
					currMarker = new L.Marker(markerLatLng, { draggable: false });
				}
				bookingfor.currMap.addLayer(currMarker);
			}

		}else{ //google
			if (typeof google !== 'object' || typeof google.maps !== 'object') {
				jQuery.getScript("//maps.google.com/maps/api/js?key=" + bfi_variables.bfi_googlemapskey + "&libraries=drawing,places", function (data, textStatus, jqxhr) {
					bookingfor.currLatLng = new google.maps.LatLng(currLat ,currLon);
					var myOptions = {
							zoom: bfi_variables.bfi_mapstartzoom,
							center: bookingfor.currLatLng,
							mapTypeId: google.maps.MapTypeId.ROADMAP,
							mapTypeControl: false,
						}
					bookingfor.currMap  = new google.maps.Map(document.getElementById(divid), myOptions);
					if (currMarkerLat && currMarkerLat.length && currMarkerLon && currMarkerLon.length )
					{
						var markerLatLng = new google.maps.LatLng(currMarkerLat,currMarkerLon);
						var marker = new google.maps.Marker({
							  position: markerLatLng,
							  map: bookingfor.currMap 
						  });
					}
					bookingfor.redrawmap ();

				});
			}

		}

		var loadPoi = currMapContainer.attr("data-poi");
		if (loadPoi && loadPoi.length && loadPoi=="true")
		{
			bookingfor.bfiLoadPois(bookingfor.currMap);
		}
//		var loadAutocomplete = currMapContainer.attr("data-autocomplete");
//		if (loadAutocomplete && loadAutocomplete.length && loadAutocomplete=="true")
//		{
//			bookingfor.bfiLoadSearchMap(bookingfor.currMap);
//		}
	}
}
bookingfor.redrawmap = function () {
	if (typeof google !== "undefined")
	{
		if (typeof google === 'object' || typeof google.maps === 'object'){
			google.maps.event.trigger(bookingfor.currMap , 'resize');
			bookingfor.currMap.setCenter(bookingfor.currLatLng );
		}
	}
}
bookingfor.hasMap = function (id) {
	return !! document.getElementById(id).firstChild;
}
bookingfor.createSingleEventMap = function (refId, posx, posy, startzoom, currNameDay, currDay, currMonth) {
	jQuery(".bfi-map-single-event").not(jQuery("#" + refId)).hide();
	jQuery("#" + refId).toggle(200, function () {
		if (bfi_variables.bfiMapsFree) { //leaflet
			var currLatLng = new Leaflet.LatLng(posx, posy);
			if (!this._leaflet_id) {
				var currMap = Leaflet.map(refId).setView(currLatLng, startzoom);
				currMap.zoomControl.setPosition('bottomright');			
				var OpenStreetMap_Mapnik = Leaflet.tileLayer(bfi_variables.bfi_freemaptileurl, {
					maxZoom: 19,
					attribution: bfi_variables.bfi_freemaptileattribution
				});
				OpenStreetMap_Mapnik.addTo(currMap);
				var instanceclass = "blulighttheme";
				var bfiIcon = Leaflet.divIcon({
						iconSize:null,
						html: '<div class="bfi-map-label ' + instanceclass + '"><div class="bfi-map-label-content bfi-map-event">'+currNameDay+' <div class="bfi-map-event-day">'+currDay+'</div>'+currMonth+' </div><div class="bfi-map-label-arrow"></div></div>'
					});
					currMap.addLayer(new L.Marker(currLatLng,{icon: bfiIcon}));
			}
		}else{ //google
			var currLatLng = new google.maps.LatLng(posx, posy);
			var myOptions = {
				zoom: startzoom,
				center: currLatLng,
				mapTypeId: google.maps.MapTypeId.ROADMAP,
				mapTypeControl: false,
			}
			if (!bookingfor.hasMap(refId)) {
				var currMap = new google.maps.Map(document.getElementById(refId), myOptions);
				var marker = new google.maps.Marker({
					position: currLatLng,
					map: currMap,
					title: '<?php echo BFCHelper::string_sanitize($resource->Name) ?>'
				});
				var instanceclass = " bfi-map-label bfi-googlemap blulighttheme bfi-map-events";
				marker = new MarkerWithLabel({
					position: currLatLng,
					draggable: false,
					raiseOnDrag: false,
					map: currMap,
					labelContent: '<div class="bfi-map-label-content bfi-map-event">'+currNameDay+' <div class="bfi-map-event-day">'+currDay+'</div>'+currMonth+'</div><div class="bfi-map-label-arrow"></div>',
					labelAnchor: new google.maps.Point(22, 22),
					labelClass: instanceclass, // the CSS class for the label
					icon: {
					url: 'https://maps.gstatic.com/mapfiles/api-3/images/spotlight-poi2.png',
					scaledSize : new google.maps.Size(1, 1)
					},
				});
			}
		}
	});
}

/* ------------------- END mappa merchant ------------------- */
bookingfor.bfishowotherdates = function (currId, currTitle) {
	var bfi_wuiP_width = 800;
	var bfi_wuiP_height = 600;
	var dialogEvents;
	if(jQuery(window).width()<bfi_wuiP_width){
		bfi_wuiP_width = jQuery(window).width()*0.8;
	}
	if(jQuery(window).height()<bfi_wuiP_height){
		bfi_wuiP_height = jQuery(window).height()*0.8;
	}
	if(jQuery(window).width()<465){
		bfi_wuiP_width = jQuery(window).width();
	}
	if (jQuery("#"+currId).hasClass("ui-dialog-content") ) {
		jQuery("#"+currId).dialog("close").dialog('destroy');
	}

	dialogEvents = jQuery( "#"+currId ).dialog({
		closeText: "",
		title:'<i class="fa fa-list-ul" aria-hidden="true"></i> '+currTitle,
		autoOpen: false,
		width: bfi_wuiP_width,
		maxHeight: bfi_wuiP_height,
		modal: true,
		dialogClass: 'bfi-dialog bfi-dialog-event',
		clickOutside: false,
		resizable: false,
	});
	dialogEvents.dialog("open");
}

/**------------- MAPPA OPENLAYERS -------------**/

bookingfor.loadedMapSells = false;
bookingfor.loadMapSells = function (divid,configurationObj,customImageUrl,divOverlayDetails,customImage){
	var currMapContainer = jQuery("#"+divid);
	if (currMapContainer && currMapContainer.length)
	{
			/**
			 * Elements that make up the popup.
			 */
//			var popupContainer = jQuery('#'+divOverlayDetails);
			var popupContainer = document.getElementById('bfi-ol-popup');
			var popupContent = jQuery(popupContainer).find('#bfi-ol-popup-content').first();
			bookingfor.popupCloser = jQuery(popupContainer).find('#bfi-ol-popup-closer').first();


			/**
			 * Create an overlay to anchor the popup to the map.
			 */
			var overlay = new ol.Overlay({
			  element: popupContainer,
			  autoPan: true,
			  autoPanAnimation: {
				duration: 250
			  }
			});


			/**
			 * Add a click handler to hide the popup.
			 * @return {boolean} Don't follow the href.
			 */
			bookingfor.popupCloser.on("click tap" ,function() {
			  overlay.setPosition(undefined);
			  bookingfor.popupCloser.blur();
			  return false;
			});
			//settori
            var figures = [];
            var source = new ol.source.Vector({
                zIndex: 1,
                features: figures
            });
            if (configurationObj.Sectors.length > 0) {
                jQuery.each(configurationObj.Sectors, function (i, sct) {
                    var allPoints = [];
                    jQuery.each(sct.Vertices, function (j, vtx) {
                        allPoints.push(configurationObj.Type == 0 ? [parseFloat(vtx[1]), parseFloat(vtx[0])] : [parseFloat(vtx[0]), parseFloat(vtx[1])]);
                    });
                    var geom = new ol.geom.Polygon([allPoints]);
                    if (configurationObj.Type == 0) geom = geom.transform('EPSG:4326', 'EPSG:3857');
                    var polygon = new ol.Feature({
                        type: 'Polygon',
                        geometry: geom,
                    });
                    polygon.setId(sct.Id);
                    source.addFeature(polygon);
                });
            }
            var vector = new ol.layer.Vector({
                source: source,
                style: new ol.style.Style({
                    fill: new ol.style.Fill({
                        color: 'rgba(255, 255, 255, 0.2)'
                    }),
                    stroke: new ol.style.Stroke({
                        color: '#ffcc33',
                        width: 2
                    }),
                    image: new ol.style.Circle({
                        radius: 7,
                        fill: new ol.style.Fill({
                            color: '#ffcc33'
                        })
                    })
                })
            });

			// posti spiaggia
			var seats = [];
//            if (configurationObj.ResourceSetPositions.length > 0) {
//                jQuery.each(configurationObj.ResourceSetPositions, function (i, pnt) {
//                    var marker = new ol.Feature({
//                        geometry: new ol.geom.Point(configurationObj.Type == 0 ? ol.proj.fromLonLat([pnt.YPos, pnt.XPos]) : [pnt.XPos, pnt.YPos]),
//                    });
//                    marker.setId(pnt.Id);
//                    seats.push(marker);
//                });
//            }
//
//
            var seatSource = new ol.source.Vector({
                zIndex: 1.8,
                features: seats
            });
			bookingfor.getFeatureZoom = function(feature,resolution) {
				var currZoom = 1.5;
				var limitResolution = 20;
				if (configurationObj.Type == 1) {
					limitResolution = 4;
				}
				if (resolution > bookingfor.currentMapSells.getView().getResolutionForZoom(limitResolution) ) { //setta la visibilità della label sotto il 20 zoom
					currZoom = 1;
				}
				return currZoom;
			};
			bookingfor.getFeatureRadius = function(feature,resolution) {
				var radius = 12;
				var limitResolution = 20;
				if (configurationObj.Type == 1) {
					limitResolution = 4;
				}
				if (resolution > bookingfor.currentMapSells.getView().getResolutionForZoom(limitResolution) ) { //setta la visibilità della label sotto il 20 zoom
					radius = 8;
				}
				return radius;
			};
			bookingfor.getFeatureText = function(feature,resolution) {
				var text = '';
				if (feature.labelText && feature.labelText.length > 0  )
				{
					text = feature.labelText;
				}
				var limitResolution = 20;
				if (configurationObj.Type == 1) {
					limitResolution = 5;
				}
				if (resolution > bookingfor.currentMapSells.getView().getResolutionForZoom(limitResolution) ) { //setta la visibilità della label sotto il 20 zoom
					text = "";
				}
				return text;
			};
			bookingfor.TextStyle = new ol.style.Text({
				textAlign: 'center',
				textBaseline: 'top',
				offsetY: 15,
				font: '11px Verdana',
				fill: new ol.style.Fill({color: 'black'}),
				backgroundFill:new ol.style.Fill({color: '#ffffff'}),
				stroke: new ol.style.Stroke({color: 'white', width: 0.5})
			  });
				// stili icone 
			bookingfor.seatStyles =[ 
				new ol.style.Style({
					zIndex: 3,
				}),
				new ol.style.Style({ // ombrellone
					image: new ol.style.Icon({
						src: 'data:image/svg+xml;utf8,' + '<svg  width="15" height="15" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 15 15"><path fill="white" d="M14.16,7a5,5,0,0,0-2.07-3.66,7.34,7.34,0,0,0-4-1.48h0V1.29A.77.77,0,0,0,7.83.74a.51.51,0,0,0-.66,0,.77.77,0,0,0-.23.55v.58h0a7.34,7.34,0,0,0-4,1.48A5,5,0,0,0,.84,7,.75.75,0,0,0,1,7.67a.75.75,0,0,0,.68.09l1.1-.26a5.18,5.18,0,0,1,2.3-.2l.81.17,1,.21h.06V9.8H5.74a.56.56,0,1,0,0,1.11H6.92v2.36H4.3a.56.56,0,1,0,0,1.11h6.4a.56.56,0,1,0,0-1.11H8.08V10.91H9.26a.56.56,0,1,0,0-1.11H8.08V7.69h.06l1-.21.81-.17a5.18,5.18,0,0,1,2.3.2l1.1.26A.75.75,0,0,0,14,7.67.75.75,0,0,0,14.16,7ZM3.94,6h0c0,.09,0,.18,0,.18l-.25,0-1.22.26L2,6.55A4.09,4.09,0,0,1,3.33,4.41a4.88,4.88,0,0,1,1.45-.9A5.58,5.58,0,0,0,3.94,6Zm5.79.25-2.2.5H7.47l-2.2-.5L5,6.16A4,4,0,0,1,6.39,3.34,1.66,1.66,0,0,1,7.5,3a1.66,1.66,0,0,1,1.11.39A4,4,0,0,1,10,6.16Zm2.8.25L11.31,6.2l-.25,0s0-.09,0-.18h0a5.58,5.58,0,0,0-.84-2.45,4.88,4.88,0,0,1,1.45.9A4.09,4.09,0,0,1,13,6.55Z"/></svg>',
					  }),
					zIndex: 3,
				}),
				new ol.style.Style({ // gazebo
					image: new ol.style.Icon({
						src: 'data:image/svg+xml;utf8,' + '<svg  width="15" height="15" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 15 15"><path fill="white" d="M2,2.43v9.7a.44.44,0,0,0,.44.44.43.43,0,0,0,.44-.44v0h9.16v0a.44.44,0,0,0,.88,0V2.43Zm2.76.75h5.4a7,7,0,0,0,.1.8.73.73,0,0,1-.71.56.74.74,0,0,1-.67-.45l0-.06a.38.38,0,0,0-.34-.22A.4.4,0,0,0,8.19,4v0a.75.75,0,0,1-.66.46h0a.75.75,0,0,1-.66-.46V4a.4.4,0,0,0-.36-.23A.38.38,0,0,0,6.11,4l0,.06a.74.74,0,0,1-.67.45A.73.73,0,0,1,4.7,4,7,7,0,0,0,4.8,3.18Zm-2,0H4.17A5.38,5.38,0,0,1,2.8,6.46Zm9.28,8H2.92v-.36a.82.82,0,0,1,.8-.65h7.57a.82.82,0,0,1,.79.62ZM4.17,9.34A.81.81,0,0,1,5,8.65H6.25a.82.82,0,0,1,.8.69ZM8,9.34a.82.82,0,0,1,.8-.69H10a.82.82,0,0,1,.81.69Zm4.15.21a1.44,1.44,0,0,0-.36-.15h0A1.69,1.69,0,0,0,10,7.77H8.75a1.68,1.68,0,0,0-1.25.56,1.68,1.68,0,0,0-1.25-.56H5A1.69,1.69,0,0,0,3.28,9.39h0a1.61,1.61,0,0,0-.36.15V7.42l.15-.14A6.14,6.14,0,0,0,4.54,5a1.48,1.48,0,0,0,.87.28,1.53,1.53,0,0,0,1-.43,1.47,1.47,0,0,0,1,.43h.06a1.44,1.44,0,0,0,1-.43,1.53,1.53,0,0,0,1.05.43A1.48,1.48,0,0,0,10.46,5,6.14,6.14,0,0,0,12,7.28l.15.14Zm.1-3.09a5.38,5.38,0,0,1-1.37-3.28H12.2Z"/></svg>',
					  }),
					zIndex: 3,
				}),
				new ol.style.Style({ //lettino
					image: new ol.style.Icon({
						src: 'data:image/svg+xml;utf8,' + '<svg  width="15" height="15" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 15 15"><path fill="white" d="M13.83,6,12,3.54l0-.07a.37.37,0,0,0-.41-.13L9.4,4.08a.35.35,0,0,0-.22.24.37.37,0,0,0,.06.32l.82,1.05,0,.07H10v.09l-.52.86L8,6.82H8l-6.51.44H1.36l0,0-.07,0a.4.4,0,0,0-.11.14.17.17,0,0,0,0,0v0a0,0,0,0,0,0,0v.16L1.2,9.19a.43.43,0,0,0,.13.26.42.42,0,0,0,.28.1.43.43,0,0,0,.26-.13A.42.42,0,0,0,2,9.14l0-.25L2.79,10l.09,1.32a.38.38,0,0,0,.13.26.39.39,0,0,0,.25.1h0a.37.37,0,0,0,.35-.4l-.07-1.06L11,9.72l.07,1a.43.43,0,0,0,.13.26.35.35,0,0,0,.28.09.37.37,0,0,0,.26-.12.37.37,0,0,0,.09-.28l-.08-1.28,1.71-2.82.25-.1a.4.4,0,0,0,.2-.24A.35.35,0,0,0,13.83,6ZM1.42,7.52s0,0,0,.06l0,.08v0l-.13,0V7.6a0,0,0,0,1,0,0,.06.06,0,0,1,0,0h0l.08-.08,0,0h.06l0,.12ZM8.2,9.26,6.89,7.53l1-.06L9.23,9.19Zm-1.64.11L5.25,7.64l1-.06L7.59,9.3ZM4.64,7.69,6,9.41l-1,.07L3.61,7.76ZM3.28,9.59,2,7.87,3,7.8,4.31,9.53Zm6.56-.44L8.53,7.42l1-.06,1.31,1.72Zm1.5-.3L10,7.07l.39-.63,1.34,1.78ZM12,7.75l-.35-.47h0L12.25,7l.11.15Zm1.15-1.64L13,6.2l-.52.22L12,6.64l-.3.13-.84-1.06-.34-.44-.57-.74,1.3-.44L11.54,4h0l.17.22L13.09,6l.09.12Z"/></svg>',
					  }),
					zIndex: 3,
				}),
				new ol.style.Style({ //baldacchino
					image: new ol.style.Icon({
						src: 'data:image/svg+xml;utf8,' + '<svg  width="15" height="15" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30"><path fill="white" d="MM2.65,3.52V25.57a.91.91,0,0,0,1.82,0v-.18H25.53v.18a.91.91,0,0,0,1.82,0v-22ZM25.53,5.34V12.1a12.13,12.13,0,0,1-2.72-6.76ZM9,5.34H21A14.82,14.82,0,0,0,21.19,7a1.47,1.47,0,0,1-2.76.21v0l0,0,0-.08V7a1.08,1.08,0,0,0-2,0V7l0,.09A1.49,1.49,0,0,1,15,8.05H15a1.48,1.48,0,0,1-1.32-.92l0-.09V7a1.08,1.08,0,0,0-2,0V7l0,.08,0,0v.05A1.47,1.47,0,0,1,8.82,7C8.91,6.43,9,5.89,9,5.34Zm-4.55,0H7.19A12.13,12.13,0,0,1,4.47,12.1ZM25.53,23.57H4.47v-.95a2,2,0,0,1,1.9-1.52H23.63a2,2,0,0,1,1.9,1.52ZM7.29,19.28a2,2,0,0,1,1.94-1.76h2.91a2,2,0,0,1,1.94,1.76Zm8.63,0a2,2,0,0,1,1.94-1.76h2.91a2,2,0,0,1,1.94,1.76Zm9.29-4.84.37.33v5.05a3.73,3.73,0,0,0-.94-.41l0,0h-.08a3.78,3.78,0,0,0-3.77-3.69H17.86A3.82,3.82,0,0,0,15,17a3.8,3.8,0,0,0-2.86-1.31H9.23a3.78,3.78,0,0,0-3.77,3.69H5.39l0,0a3.79,3.79,0,0,0-1,.41V14.77l.37-.34A12.75,12.75,0,0,0,6.05,13,13.71,13.71,0,0,0,8.21,9.18a3.31,3.31,0,0,0,4.41-.33,3.23,3.23,0,0,0,2.32,1h.12a3.23,3.23,0,0,0,2.32-1,3.31,3.31,0,0,0,2.39,1,3.27,3.27,0,0,0,2-.69A13.71,13.71,0,0,0,24,13,15.17,15.17,0,0,0,25.21,14.44Z"/></svg>',
					  }),
					zIndex: 3,
				}),
				new ol.style.Style({ //tenda
					image: new ol.style.Icon({
						src: 'data:image/svg+xml;utf8,' + '<svg  width="15" height="15" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30"><path fill="white" d="M27.35,6V4.34a.82.82,0,0,0-.82-.82H3.47a.82.82,0,0,0-.82.82V6a.82.82,0,0,0,.82.82V18.42a1.23,1.23,0,0,0,0,2.32v5.74H7.12l0-.56-.24-5.21a1.23,1.23,0,0,0,.73-1,1.26,1.26,0,0,0-.45-1.12,14.11,14.11,0,0,0,4.57-10,4.37,4.37,0,0,0,6.61,0,14.07,14.07,0,0,0,4.57,10,1.24,1.24,0,0,0,.27,2.09l-.24,5.21,0,.56h3.64V20.74a1.23,1.23,0,0,0,0-2.32V6.81A.82.82,0,0,0,27.35,6ZM4.29,25.92V20.81H6l.24,5.11ZM6.35,20H3.88a.41.41,0,1,1,0-.82H6.35a.41.41,0,1,1,0,.82ZM7.59,8.46A2.41,2.41,0,0,0,10,6.81h.84A3.19,3.19,0,0,1,7.59,9.28,3.18,3.18,0,0,1,4.33,6.81h.84A2.4,2.4,0,0,0,7.59,8.46ZM6,6.81H9.13a1.67,1.67,0,0,1-1.54.83A1.69,1.69,0,0,1,6,6.81Zm.23,3.1a14.93,14.93,0,0,1-2,3.95V8.62A4.18,4.18,0,0,0,6.27,9.91Zm.81.17.51,0a4.62,4.62,0,0,0,.74-.06,14.56,14.56,0,0,1-4,7.7V15.26A16.36,16.36,0,0,0,7.08,10.08ZM6.2,18.34H4.87c1.56-1.64,4-4.76,4.31-8.52a4.14,4.14,0,0,0,1.69-1.19A13.26,13.26,0,0,1,6.2,18.34ZM15,9.28a3.18,3.18,0,0,1-3.26-2.47h.84A2.41,2.41,0,0,0,15,8.46a2.41,2.41,0,0,0,2.42-1.65h.84A3.18,3.18,0,0,1,15,9.28ZM13.46,6.81h3.09A1.69,1.69,0,0,1,15,7.64,1.67,1.67,0,0,1,13.46,6.81Zm5.67,1.82a4.2,4.2,0,0,0,1.69,1.19c.36,3.76,2.76,6.88,4.31,8.52H23.81A13.27,13.27,0,0,1,19.13,8.63Zm3.28-.17a2.39,2.39,0,0,0,2.42-1.65h.84a3.18,3.18,0,0,1-3.26,2.47,3.18,3.18,0,0,1-3.25-2.47H20A2.41,2.41,0,0,0,22.41,8.46ZM20.87,6.81H24a1.87,1.87,0,0,1-1.55.83A1.84,1.84,0,0,1,20.87,6.81Zm2.87,3.1a4.2,4.2,0,0,0,2-1.29v5.24A14.61,14.61,0,0,1,23.74,9.91Zm2,5.35v2.49a14.55,14.55,0,0,1-4-7.7,4.49,4.49,0,0,0,1.25,0A16.33,16.33,0,0,0,25.71,15.26Zm-2,10.66.05-1.18.11-2.3h0L24,20.81h1.73v5.11ZM26.12,20H23.65a.41.41,0,1,1,0-.82h2.47a.41.41,0,1,1,0,.82ZM3.47,6V4.34H26.53V6Z"/></svg>',
					  }),
					zIndex: 3,
				}),
				new ol.style.Style({ // ombrellone XL
					image: new ol.style.Icon({
						src: 'data:image/svg+xml;utf8,' + '<svg  width="15" height="15" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30"><path fill="white" d="M28.19,12.76l-.12-.55a8.68,8.68,0,0,0-1.5-3.33,13.35,13.35,0,0,0-8.16-5c-.61-.14-1.23-.22-1.84-.3-.23,0-.46-.06-.69-.09,0-.16,0-.32,0-.48,0-.34,0-.68,0-1,0-.63-.29-1-.8-1a.62.62,0,0,0-.49.17,1.26,1.26,0,0,0-.37.91c0,.42,0,.84,0,1.25v.25l-.2,0h-.12a14.91,14.91,0,0,0-8.19,3A10.17,10.17,0,0,0,1.44,14a1.31,1.31,0,0,0,.26,1.12,1.27,1.27,0,0,0,1.13.11L3.93,15l1-.25.15,0A11.54,11.54,0,0,1,10,14.28l1.72.37c.64.14,1.3.29,2,.42.32.06.39.15.39.48V20.1H11.33a.85.85,0,0,0,0,1.7h2.78c0,2.17,0,4.35,0,5.51h-9a.85.85,0,0,0,0,1.7H24.86a.85.85,0,0,0,0-1.7h-9V23.87a5.27,5.27,0,1,0,0-6.15V15.29c4.48-1.89,8.77-.9,12.38.32l.26.09,0-.28a2.16,2.16,0,0,1,0-.28A6.54,6.54,0,0,0,28.19,12.76Zm-20.3-.93v0a1.45,1.45,0,0,1-.12.57,1.42,1.42,0,0,1-.59.17h0c-.85.16-1.7.34-2.53.52l-1.11.23-.3.06A2.86,2.86,0,0,1,3.26,13,8.72,8.72,0,0,1,6.11,8.35a10.87,10.87,0,0,1,4.06-2.27A10.9,10.9,0,0,0,7.89,11.83Zm7.23,9a5,5,0,0,1,.75-2.63,5,5,0,1,1,0,5.25A4.89,4.89,0,0,1,15.12,20.8Zm3.93-8.09c-1.22.31-2.47.6-3.67.88l-.24.06a.61.61,0,0,1-.27,0l-4.37-1-1-.23c.31-2.94,1.24-4.9,3-6.34a3.7,3.7,0,0,1,2.76-.85,3.13,3.13,0,0,1,2.17,1.46,10.93,10.93,0,0,1,1.79,5.79A.44.44,0,0,1,19.05,12.71ZM21,12.34c-.06-.31-.12-.62-.17-.95a18.7,18.7,0,0,0-.6-2.62,17.13,17.13,0,0,0-1.06-2.44c-.11-.22-.22-.44-.32-.66a14.56,14.56,0,0,1,5.1,2.73,8.66,8.66,0,0,1,2.87,5Z"/><path fill="white" d="M18.54,21.54l-.9,1.53h-1l1.44-2.3-1.46-2.26h1.05l1,1.53.89-1.53h1l-1.43,2.3,1.45,2.26H19.52Z"/><path fill="white" d="M22.19,18.51v3.84h1.5v.72H21.28V18.51Z"/></svg>',
					  }),
					zIndex: 3,
				}),
				new ol.style.Style({ // lettino XL
					image: new ol.style.Icon({
						src: 'data:image/svg+xml;utf8,' + '<svg  width="15" height="15" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30"><path fill="white"  d="M28.33,12.86,27.77,12,24.48,7.71h0l-.11-.16a.66.66,0,0,0-.74-.22L19.06,8.89a.63.63,0,0,0-.41.44.64.64,0,0,0,.11.58l1.81,2.32,0,0-.05.1-.07.09-.07.12L19.2,14.48l-3.06.22H16l-1.67.11A5.28,5.28,0,1,0,9,15.17l-3.31.23H5.64l-3.4.23-.13,0H2.05l0,0a.08.08,0,0,0-.07,0,.19.19,0,0,0-.12.07.59.59,0,0,0-.2.24h0a.76.76,0,0,0,0,.11s0,.06,0,.07,0,.06,0,.07v.14s0,0,0,.06v.1l.21,3A.72.72,0,0,0,2,20.1a.78.78,0,0,0,.51.18A.73.73,0,0,0,3,20a.69.69,0,0,0,.17-.5l-.06-.87,2,2.64.19,2.84a.68.68,0,0,0,.24.47.72.72,0,0,0,.46.18h0a.68.68,0,0,0,.49-.24A.72.72,0,0,0,6.71,24l-.15-2.32,15.91-1.07L22.63,23a.7.7,0,0,0,.24.5.72.72,0,0,0,.51.17.82.82,0,0,0,.49-.24.8.8,0,0,0,.17-.53l-.18-2.75,3.66-6,.55-.24a.64.64,0,0,0,.37-.44A.67.67,0,0,0,28.33,12.86Zm-22-2.39a5,5,0,1,1,7.49,4.37,4.92,4.92,0,0,1-2.47.65,5.38,5.38,0,0,1-1.85-.34A5,5,0,0,1,6.37,10.47ZM6.13,20.3,3.6,17l1.78-.12,2.54,3.34Zm3.48-.22L7.07,16.72l1.78-.12L11.39,20Zm3.47-.24-2.53-3.36,1.78-.12,2.54,3.36Zm3.48-.24L14,16.26l1.78-.12,2.54,3.34ZM20,19.38,17.5,16l1.78-.13,2.53,3.36Zm3.08-.77-2.64-3.48.63-1,2.65,3.49Zm1.43-2.33-.47-.63.94-.43.09.12Zm-.68-2L20.37,9.84l3.22-1.12L26.78,13Z"/><polygon fill="white" points="10.37 10.48 11.82 12.76 10.77 12.76 9.79 11.21 8.89 12.76 7.86 12.76 9.3 10.46 7.84 8.2 8.89 8.2 9.88 9.72 10.77 8.2 11.8 8.2 10.37 10.48"/><polygon fill="white" points="14.94 12.03 14.94 12.76 12.52 12.76 12.52 8.2 13.44 8.2 13.44 12.03 14.94 12.03"/></svg>',
					  }),
					zIndex: 3,
				}),
				new ol.style.Style({ // ombrellone paglia
					image: new ol.style.Icon({
						src: 'data:image/svg+xml;utf8,' + '<svg  width="15" height="15" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30"><path fill="white" d="M25.21,5.71a11.15,11.15,0,0,0-4-2.62A16,16,0,0,0,16,2V1H14V2C9.1,2.27,6.3,4.15,4.78,5.73a8.73,8.73,0,0,0-2.57,5.53,3.36,3.36,0,0,0,2.68,3.29,3.23,3.23,0,0,0,.69.07,3.36,3.36,0,0,0,2.36-1,3.36,3.36,0,0,0,1.67.89,3.82,3.82,0,0,0,.68.07,3.35,3.35,0,0,0,2.36-1,3.29,3.29,0,0,0,1.34.81h.12v5.63H11.34a.85.85,0,0,0,0,1.69h2.77v5.51H8.35a.85.85,0,0,0,0,1.7h13.3a.85.85,0,0,0,0-1.7H15.88V21.78h2.78a.85.85,0,0,0,0-1.69H15.88V14.46H16a3.64,3.64,0,0,0,1.35-.81,3.36,3.36,0,0,0,1.67.89,3.82,3.82,0,0,0,.68.07,3.4,3.4,0,0,0,2.36-1,3.37,3.37,0,0,0,5.72-2.41A8.72,8.72,0,0,0,25.21,5.71ZM6.53,12.21a1.35,1.35,0,0,1-1.9,0,1.28,1.28,0,0,1-.33-.53H6.85A1.26,1.26,0,0,1,6.53,12.21Zm.63-2.56H4.6A7.81,7.81,0,0,1,6.24,7.13a9.3,9.3,0,0,1,3.39-2.2c-.18.22-.36.45-.53.69A12.86,12.86,0,0,0,7.16,9.65Zm3.13,2.95A1.35,1.35,0,0,1,9,11.67h2.55A1.34,1.34,0,0,1,10.29,12.6Zm1.38-2.95H9.27A11.24,11.24,0,0,1,10.74,6.8a8.11,8.11,0,0,1,2.19-2.13A14,14,0,0,0,11.67,9.65Zm4.21,2.61a1.31,1.31,0,0,1-.88.33,1.34,1.34,0,0,1-.91-.35,1.56,1.56,0,0,1-.37-.57h2.56A1.35,1.35,0,0,1,15.88,12.26ZM13.7,9.65A11.83,11.83,0,0,1,15,5.05,11.87,11.87,0,0,1,16.3,9.64Zm3.35-5a8.17,8.17,0,0,1,2.2,2.13,11.46,11.46,0,0,1,1.48,2.85h-2.4A13.92,13.92,0,0,0,17.05,4.66Zm3.61,7.53a1.35,1.35,0,0,1-.95.4,1.32,1.32,0,0,1-.95-.4,1.26,1.26,0,0,1-.32-.53H21A1.28,1.28,0,0,1,20.66,12.19Zm.23-6.58q-.27-.36-.54-.69a9.2,9.2,0,0,1,3.4,2.19,7.62,7.62,0,0,1,1.64,2.52H22.84A13.12,13.12,0,0,0,20.89,5.61Zm3.53,7a1.35,1.35,0,0,1-1.27-.92H25.7A1.35,1.35,0,0,1,24.42,12.58Z" /></svg>',
					  }),
					zIndex: 3,
				}),

			]

			bookingfor.shadowStyle = new ol.style.Style({
				image: new ol.style.Circle({
					radius: 8,
					fill: new ol.style.Fill({
					color: [0, 0, 0, 0.4],
					})
				}),
				zIndex: 1
			});
			bookingfor.seatStyle = new ol.style.Style({
				image: new ol.style.Circle({
					radius: 8,
					fill: new ol.style.Fill({
					color: '#f73434'
					})
				}),
				zIndex: 2,
			});
			bookingfor.seatStyleOpen = new ol.style.Style({
				image: new ol.style.Circle({
					radius: 8,
					fill: new ol.style.Fill({
					color: '#1ca953'
					})
				}),
				zIndex: 2,
			});
			bookingfor.seatStyleSelected = new ol.style.Style({
				image: new ol.style.Circle({
					radius: 8,
					fill: new ol.style.Fill({
					color: '#0893FC'
					})
				}),
				zIndex: 2,
			});
            bookingfor.seatVector = new ol.layer.Vector({
                source: seatSource,
                style: function(feature) {
					bookingfor.TextStyle.getText().setText(bookingfor.getFeatureText(feature));
					bookingfor.seatStyleOpen.getImage().setRadius(bookingfor.getFeatureRadius(feature,resolution));
					var styles = [bookingfor.shadowStyle,bookingfor.seatStyleOpen,bookingfor.TextStyle]
					return styles;
				},
				 declutter: true					
            });
                if (configurationObj.Type == 0) {
                    bookingfor.currentMapSells = new ol.Map({
                        controls: ol.control.defaults().extend([ new ol.control.LayerSwitcher({
                            groupSelectStyle: 'group' // Can be 'children' [default], 'group' or 'none'
                        })]),
                        target: divid,
                        interactions: ol.interaction.defaults({
                            doubleClickZoom: false,
							mouseWheelZoom: false,
                        }),      //.extend([selectFeatureClick]),
						overlays: [overlay],
						layers: [
                            new ol.layer.Group({
                                'title': 'Maps',
                                layers: [
                                    new ol.layer.Tile({
                                        title: "Satellite",
                                        visible: true,
                                        type: 'base',
                                        source: new ol.source.XYZ({
                                            attributions: ['Powered by Esri'],
                                            attributionsCollapsible: true,
                                            url: 'https://services.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
                                            maxZoom: 19
                                        })
                                    }),
                                    new ol.layer.Tile({
                                        title: "OpenStreetMap",
                                        visible: false,
                                        type: 'base',
                                        source: new ol.source.OSM({
                                            url: "https://a.tile.openstreetmap.org/{z}/{x}/{y}.png",
                                            attributions: [ol.source.OSM.ATTRIBUTION],
                                            attributionsCollapsible: true,
                                            maxZoom: 19
                                        })
                                    })
                                ]
                            }), vector, bookingfor.seatVector
                        ],
                        view: new ol.View({
                            center: configurationObj.Type == 0 ? new ol.proj.fromLonLat([parseFloat(configurationObj.YPos), parseFloat(configurationObj.XPos)]) : ol.proj.fromLonLat([parseFloat(configurationObj.YPos), parseFloat(configurationObj.XPos)]),
                            zoom: configurationObj.DefaultZoom,
                            constrainResolution: true,
                            rotation: (configurationObj.RotationDegrees * Math.PI / 180)
                        })
                    });
                } else {
					var extent = [0, 0, customImage.width, customImage.height];
                    var projection = new ol.proj.Projection({
                        code: 'xkcd-image',
                        units: 'pixels',
                        extent: extent
                    });
var attribution = new ol.control.Attribution({
  collapsible: false
});
                    bookingfor.currentMapSells = new ol.Map({
					controls:  ol.control.defaults({attribution: false}).extend([attribution]),
//                        controls: ol.control.defaults().extend([ new ol.control.LayerSwitcher({
//                            groupSelectStyle: 'group' // Can be 'children' [default], 'group' or 'none'
//                        })]),
                        target: divid,
                        interactions: ol.interaction.defaults({
                            doubleClickZoom: false,
							mouseWheelZoom: false,
                        }),      //.extend([selectFeatureClick]),
						overlays: [overlay],
                        layers: [
                            new ol.layer.Image({
                                source: new ol.source.ImageStatic({
                                    url: customImageUrl,
                                    projection: projection,
                                    imageExtent: extent,
									attributions: '<a onclick="bfigotocarttemp();"><i class="fas fa-angle-double-down fa-2x"></i></a>',
                                })
                            })
                        ],
                        view: new ol.View({
                            projection: projection,
                            center: ol.extent.getCenter(extent),
//                            center: ol.proj.fromLonLat([parseFloat(configurationObj.YPos), parseFloat(configurationObj.XPos)]),
                            zoom: 2,
							extent:extent,
                            maxZoom: 8
                        })
                    });
                }	

				// change mouse cursor when over marker
				bookingfor.currentMapSells.on('pointermove', function(evt) {
                    var seat = evt.map.forEachFeatureAtPixel(evt.pixel, function (feature, layer) {
                        return feature;
                    }, {
                        layerFilter: function (layer) {
                            return layer === bookingfor.seatVector;
                        }
                    });
					var available = false;
					if(seat){
						available = seat.Available
					}
						
                    jQuery("#" + bookingfor.currentMapSells.getTarget()).css("cursor", available ? 'pointer' : '');
//					if (e.dragging) {
////						$(element).popover('destroy');
//						overlay.setPosition(undefined);
//						popupCloser.blur();
//						return;
//					}
//					var pixel = bookingfor.currentMapSells.getEventPixel(e.originalEvent);
//					var hit = bookingfor.currentMapSells.hasFeatureAtPixel(pixel);
//					bookingfor.currentMapSells.getTarget().style.cursor = hit ? 'pointer' : '';
				});

				//click sui punti
				bookingfor.currentMapSells.on('singleclick', function (evt) {
					if (configurationObj.Type == 1) {
						bookingfor.currentMapSells.getView().setZoom(5);
					}
                    var seat = evt.map.forEachFeatureAtPixel(evt.pixel, function (feature, layer) {
                        return feature;
                    }, {
                        layerFilter: function (layer) {
                            return layer === bookingfor.seatVector;
                        }
                    });
                    if (seat) {
                        currentPoint = seat;
                        var currentPos = jQuery.grep(configurationObj.ResourceSetPositions, function (pos) {
                            return pos.Id == seat.getId();
                        })[0];
						var coordinate = evt.coordinate;
//						var hdms = toStringHDMS(toLonLat(coordinate));
//						popupContent.innerHTML = '';

						currHtml = '<span class="bfi-price"> Posto n. ' + currentPos.Name  + '</span> ' ;
						//Tags
						if (currentPos.Tags && currentPoint.Tags.length>0) {
							jQuery.each(currentPos.Tags, function (key, tagid) {
								if (typeof bookingfor.tagLoaded[tagid] !== 'undefined') {
									currHtml += '<span class="bfi-seat-tag-name">' + bookingfor.tagLoaded[tagid] + '</span>';
								}
							});
						}
						if (currentPoint.from && currentPoint.to )
						{
									currHtml += '<span class="bfi-seat-dates">' +jQuery.datepicker.formatDate("dd/mm", currentPoint.from );
									if (currentPoint.to > currentPoint.from )
									{
									currHtml += "-" + jQuery.datepicker.formatDate("dd/mm", currentPoint.to ) ;
									}
									currHtml += '</span>';
						}
						currHtml += '<form>' ;
						if (!currentPoint.Selected)
						{
							if (currentPoint.PriceResult && currentPoint.PriceResult.length>0)
							{
								 jQuery.each(currentPoint.PriceResult, function (i, price) {
									 if (price.RatePlan.CalculablePrices && price.RatePlan.CalculablePrices.length>0 )
									 {
										jQuery.each(price.RatePlan.CalculablePrices, function (i, calculablePrice) {
											var selectable = [];
											servicesAvailability[calculablePrice.PriceId] = calculablePrice.Availability ? Math.min(calculablePrice.Availability,bfi_MaxQtSelectable): 0;
											for (var i=calculablePrice.MinQt;i<= calculablePrice.MaxQt;i++ )
											{
												selectable.push(i);
											}
											calculablePrice.Selectable = selectable;
										});
									 }
									currHtml += '<div class="bfi-item-seat"> <input type="radio" name="bfi_seatSelected" value="' + price.ProductId + '_' + currentPos.Id + '" dataProductId="' + price.ProductId + '" dataseatid="' + currentPos.Id + '" dataPrice="' + price.Price + '" dataName="' + price.Name + '" ' + (currentPoint.PriceSelected==(price.ProductId + '_' + currentPos.Id)?'checked':'') + '>' + price.Name + ' <span class="bfi-price bfi_' + bfi_variables.currentCurrency + '">'+bookingfor.priceFormat(price.Price, 2, ',', '.')+'</span></input> </div>';
									 if (currentPoint.PriceSelected==(price.ProductId + '_' + currentPos.Id))
									 {
										currentPoint.Rateplan= price;
									 }
								 });

								currHtml += '<button type="button" value="" class="bfi-btn bfi_select_place">seleziona</button>';
	//							popupContent.html('PriceResult');
							}
						}else{
								 jQuery.each(currentPoint.PriceResult, function (i, price) {
									 if (currentPoint.PriceSelected==(price.ProductId + '_' + currentPos.Id))
									 {
										currHtml += '<div class="bfi-item-seat"> <input type="radio" name="bfi_seatSelected" value="' + price.ProductId + '_' + currentPos.Id + '" dataProductId="' + price.ProductId + '" dataseatid="' + currentPos.Id + '" ' + (currentPoint.PriceSelected==(price.ProductId + '_' + currentPos.Id)?'checked':'') + '>' + price.Name + ' <span class="bfi-price bfi_' + bfi_variables.currentCurrency + '">'+bookingfor.priceFormat(price.Price, 2, ',', '.')+'</span></input> </div>';
									 }
								 });
								currHtml += '<button type="button" value="" class="bfi-btn bfi_remove_select_place">Rimuovi</button>';

						}
						currHtml += ' </form>';
						popupContent.html(currHtml);
//						overlay.setPosition(coordinate);
						setTimeout(function() { 
							overlay.setPosition(coordinate);
						 }, 20);
//                        $("#modalSeat").modal("show");
//                        $("#modalSeat .title-new, #modalSeat .title-edit, #modalSeat .geomapconfig, #modalSeat .custommapconfig").hide();
//                        $("#modalSeat .title-edit").show();
//                        $("#modalSeat .btn-removeseat").show();
//                        if (configurationObj.Type == 0) $("#modalSeat .geomapconfig").show();
//                        if (configurationObj.Type == 1) $("#modalSeat .custommapconfig").show();
//                        $("#modalSeat").attr("data-existingpoint", "1");
//                        $("#seatlatitude").val(currentPos.XPos);
//                        $("#seatlongitude").val(currentPos.YPos);
//                        $("#seatxpos").val(currentPos.XPos);
//                        $("#seatypos").val(currentPos.YPos);
//                        $("#modalSeat").attr("data-pointid", seat.getId());
//                        $("#<%=lbResourceSet.ClientID%>").select2("val", currentPos.ResourceSets).trigger("change");
//                        $("#seatname").val(currentPos.Name);
//                        $("#hascoordinates").val(currentPos.RowName != null && currentPos.ColumnName != null ? "1" : "0").trigger("change");
//                        $("#rowname").val(currentPos.RowName);
//                        $("#columnname").val(currentPos.ColumnName);
                    } else {
                        var figure = evt.map.forEachFeatureAtPixel(evt.pixel, function (feature, layer) {
                            return feature;
                        }, {
                            layerFilter: function (layer) {
                                return layer === vector;
                            }
                        });
                        if (figure) {
                            currentFigure = figure;
                            var sector = jQuery.grep(configurationObj.Sectors, function (sct) {
                                return sct.Id == figure.getId();
                            })[0];
							//chiamata a disponibilità settore:

//                            $("#modalFigure").modal("show");
//                            $("#modalFigure .btn-removefigure").show();
//                            $("#modalFigure .title-new, #modalFigure .title-edit, #modalFigure .newonly").hide();
//                            $("#modalFigure .title-edit").show();
//                            $("#modalFigure").attr("data-existingfigure", "1");
//                            $("#modalFigure").attr("data-figureid", figure.getId());
//                            $("#sectionname").val(sector.Name);
                        }
                    }
                });
				
				}
				bookingfor.loadedMapSells =true;
}
/**------------- MAPPA OPENLAYERS -------------**/
	jQuery(".bfi-form-mapsells").each(function (i, currForm) {
		//var currSearchtypetab = jQuery(currForm).closest(".bfi-mod-bookingforsearch").attr("data-searchtypetab");
		var currCheckin = jQuery(currForm).find("input[name='checkin']").first();
		var currCheckout = jQuery(currForm).find("input[name='checkout']").first();
		currCheckin.datepicker({
			dateFormat: "dd/mm/yy",
			numberOfMonths: bfi_variables.bfi_numberOfMonths,
			minDate: '+0d',
			onClose: function (dateText, inst) {
				jQuery(this).attr("disabled", false);
			},
			beforeShow: function (dateText, inst) {
				var currTmpForm = jQuery(this).closest("form");
				bfidpmode = 'checkin';
				jQuery(this).attr("disabled", true);
				jQuery(inst.dpDiv).addClass('bfi-calendar');
				setTimeout(function () {
					bfi_updateTitle(currTmpForm, "bfi-checkin", "bfi-checkout", "Checkin")
				}, 1);
				var windowsize = jQuery(window).width();
				jQuery(inst.dpDiv)[0].className = jQuery(inst.dpDiv)[0].className.replace(/(^|\s)bfi-calendar-affixtop\S+/g, '');
				if (jQuery(this).closest("div.bfiAffixTop").length || windowsize < 767) {
					jQuery(inst.dpDiv).addClass('bfi-calendar-affixtop' + ((typeof currModId !== 'undefined') ? currModId : ""));
				}
			},
			onChangeMonthYear: function (dateText, inst) {
				var currTmpForm = jQuery(this).closest("form");
				setTimeout(function () { bfi_updateTitle(currTmpForm, "bfi-checkin", "bfi-checkout", "Checkin"); }, 1);
			},
			//showOn: "button", 
			beforeShowDay: function (date) {
				var currTmpForm = jQuery(this).closest("form");
				return bfi_closed(date, currTmpForm);
			},
			//buttonText: "<div class='checkinli'><span class='bfi-weekdayname'> </span> <span class='bfi-year'> </span></div>", 
			onSelect: function (date, inst) {
				var currTmpForm = jQuery(this).closest("form");
//				bfiresetsearchterm(currTmpForm);
				bfi_printChangedDate(currTmpForm);
				if (jQuery(currTmpForm).find(".bfi-checkout-field-container").is(":visible"))
				{
					setTimeout(function () { currCheckout.datepicker("show"); }, 1);
				}else{
					var currTmpCheckin = jQuery(currTmpForm).find("input[name='checkin']").first();
					var date = currTmpCheckin.val();
					bfi_checkDate(jQuery, currTmpCheckin, date);

					// autopostback			
					if (typeof bfi_getSeats === "function")
					{
						bfi_getSeats(jQuery(currForm).find("#calculateButton").first());
					}
				
				}
				jQuery(this).trigger("change");
							

			},
			firstDay: 1
		});
		/** **/

		currCheckout.datepicker({
			dateFormat: "dd/mm/yy",
			numberOfMonths: bfi_variables.bfi_numberOfMonths,
			onClose: function (dateText, inst) {
				jQuery(this).attr("disabled", false);
				bfi_printChangedDate(jQuery(this).closest("form"));
			},
			beforeShow: function (dateText, inst) {
				var currTmpForm = jQuery(this).closest("form");
				var currTmpCheckin = jQuery(currTmpForm).find("input[name='checkin']").first();
				var date = currTmpCheckin.val();
				bfi_checkDate(jQuery, currTmpCheckin, date);

				bfidpmode = 'checkout';
				jQuery(this).attr("disabled", true);
				jQuery(inst.dpDiv).addClass('bfi-calendar');
				setTimeout(function () { bfi_updateTitle(currTmpForm, "bfi-checkout", "bfi-checkin", "Checkout"); }, 1);
				var windowsize = jQuery(window).width();
				jQuery(inst.dpDiv)[0].className = jQuery(inst.dpDiv)[0].className.replace(/(^|\s)bfi-calendar-affixtop\S+/g, '');
				if (jQuery(this).closest("div.bfiAffixTop").length || windowsize < 767) {
					jQuery(inst.dpDiv).addClass('bfi-calendar-affixtop' + ((typeof currModId !== 'undefined') ? currModId : ""));
				}
				bfi_printChangedDate(currTmpForm);

			},
			onSelect: function (date, inst) {
				var currTmpForm = jQuery(this).closest("form");
//				bfiresetsearchterm(currTmpForm);
				bfi_printChangedDate(currTmpForm);
				jQuery(this).trigger("change");

				// autopostback			
				if (typeof bfi_getSeats === "function")
				{
					bfi_getSeats(jQuery(currForm).find("#calculateButton").first());
				}
			},
			onChangeMonthYear: function (dateText, inst) {
				var currTmpForm = jQuery(this).closest("form");
				setTimeout(function () { bfi_updateTitle(currTmpForm, "bfi-checkout", "bfi-checkin", "Checkout"); }, 1);
			},
			minDate: '+0d',
			//showOn: "button", 
			beforeShowDay: function (date) {
				var currTmpForm = jQuery(this).closest("form");
				return bfi_closed(date, currTmpForm);
			},
			//buttonText: "<div class='checkoutli'><span class='bfi-weekdayname'> </span> <span class='bfi-year'>aaa </span></div>", 
			firstDay: 1
		});
		/** **/

//		if (typeof bfi_printChangedDate !== "undefined") { bfi_printChangedDate(currForm); }
		jQuery(currForm).validate(
			{
				invalidHandler: function (form, validator) {
					var errors = validator.numberOfInvalids();
					if (errors) {
						validator.errorList[0].element.focus();
					}
				},
				errorClass: "bfi-error",
				highlight: function (label) {
				},
				success: function (label) {
					jQuery(label).remove();
				},
				submitHandler: function (form) {
					var $form = jQuery(form);
					var $btnresource = $form.find(".bfi-btnsendform").first();
					if ($form.valid()) {
						if ($form.data('submitted') === true) {
							return false;
						} else {
							// Mark it so that the next submit can be ignored
							$form.data('submitted', true);
							var currDateFormat = "dd/mm/yy";
							currCheckin.datepicker("option", "dateFormat", currDateFormat);
							currCheckout.datepicker("option", "dateFormat", currDateFormat);

							var isMacLike = navigator.platform.match(/(Mac|iPhone|iPod|iPad)/i) ? true : false;
							var isIOS = navigator.platform.match(/(iPhone|iPod|iPad)/i) ? true : false;
							if (!isMacLike) {
								var iconBtn = $btnresource.find("i").first();
								iconBtn.removeClass("fa-search").addClass("fa-spinner fa-spin ");
								$btnresource.prop('disabled', true);

							}
							form.submit();
						}

					}
				}

			});
		var currChecked = jQuery(currForm).find(".bfi-changedays-widget:checked");
		if (currChecked.length>0)
		{
			bookingfor.checkonedays(currChecked);
		}
	});

	//--------------------------------------------------------------------------------------------------------------------------------//
jQuery(document).on('click tap', ".bfi_open_details_search", function (e) {
	e.stopPropagation();
	e.preventDefault();

	var bfi_wuiP_width = 800;
	var currFormCalculator = jQuery(jQuery(this).attr("data-form"));
	if(jQuery(window).width()<bfi_wuiP_width){
		bfi_wuiP_width = jQuery(window).width();
	}
	if (!!jQuery.uniform){
		jQuery.uniform.restore(currFormCalculator.find("select"));
	}
//	if (currFormCalculator.hasClass("ui-dialog-content") ) {
//		currFormCalculator.dialog("close").dialog('destroy');
//	}
//	bfi_inizializeDialog(obj);
	var initFunction = currFormCalculator.attr("data-initializer");
		window[initFunction]();
	if (typeof initFunction === "function")
	{
	}
	dialogFormResult = currFormCalculator.dialog({
			closeText: "",
			title:bfi_variables.bfi_txtTitleDialogForm,
			autoOpen: false,
			width:bfi_wuiP_width,
			modal: true,
			dialogClass: 'bfi-dialog',
			clickOutside: true,

	});
	dialogFormResult.dialog( "open" );
});
