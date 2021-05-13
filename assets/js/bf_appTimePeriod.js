var timeloadedItems = {};
var availabilityCheckoutTimePeriod = [];
var checkOutDaysTimeToEnable = [];

function dateTimePeriodChanged(obj) {
    var currTr = jQuery("#bfimodaltimeperiod");
    bookingfor.waitSimpleWhiteBlock(currTr);
    var currProdId = obj.attr("data-resid");
    var currDate = obj.datepicker('getDate');
    //	var maxDate = obj.datepicker("option", "maxDate");
    //	var dateFormat = obj.datepicker("option", "dateFormat");
    //	var currMaxDate = jQuery.datepicker.parseDate(dateFormat, maxDate );
    //	var timeDiff = Math.abs(currMaxDate.getTime() - currDate.getTime());
    //	var diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1; 
    var diffDays = 1;
    var intDate = bookingfor.convertDateToInt(currDate);
    updateTimePeriodRange(intDate, currProdId, obj, diffDays);
}

function updateTimePeriodRange(currDate, currProdId, obj, maxDays) {
	bfi_consolelog("updateTimePeriodRange");
    var currTr = jQuery("#bfimodaltimeperiod");
    var currCheckin = currTr.find(".bfi-checkin-field").first();
    var currCheckout = currTr.find(".bfi-checkout-field").first();
    var curSelStart = currTr.find('.selectpickerTimePeriodStart').first();
    var curSelEnd = currTr.find('.selectpickerTimePeriodEnd').first();

    bfi_timeStepCheckin(currCheckin, curSelStart, daysToEnableTimePeriod[currProdId], parseInt(jQuery("#bfi-timeperiod-select").attr("data-timelength")), currTr.attr("data-timestart"));

    currTr.unblock();
}

jQuery(document).on('click', ".ChkAvailibilityFromDateTimePeriod.bfi-checkin-field, .ChkAvailibilityFromDateTimePeriod.bfi-checkout-field", function (e) {
    jQuery(this).datepicker("show");
});

function initDatepickerTimePeriod() {
    //console.log("inizializzazione TimePeriod");
    jQuery(".ChkAvailibilityFromDateTimePeriod.bfi-checkin-field").datepicker({
        numberOfMonths: 1,
        defaultDate: "+0d",
        dateFormat: "dd/mm/yy",
        minDate: strAlternativeDateToSearch,
        //        maxDate: strEndDate,
        onSelect: function (date) {
//            console.log("ChkAvailibilityFromDateTimePeriod onSelect")
            dateTimePeriodChanged(jQuery(this));
        },
        //        showOn: "button",
        beforeShow: function (dateText, inst) {
//            console.log("ChkAvailibilityFromDateTimePeriod beforeShow")
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
            var currResourceid = jQuery("#bfi-timeperiod-select").attr("data-resid");
            var currTmpForm = jQuery(this).closest(".bfi-timeperiod-change");
            //var currDays = [];
            //jQuery.each(daysToEnable[jQuery(this).attr("data-resid")], function (key, value) {
            //    currDays.push(Number(key));
            //});
            return bfi_closedBooking(date, 1, daysToEnable[currResourceid], currTmpForm, currResourceid);
        },
        firstDay: 1,
    });

    jQuery(".ChkAvailibilityFromDateTimePeriod.bfi-checkout-field").datepicker({
        dateFormat: "dd/mm/yy",
        numberOfMonths: 1,
        onClose: function (dateText, inst) {
//            console.log("checkout  onClose")
        },
        beforeShow: function (dateText, inst) {
//            console.log("checkout  beforeShow")
            var currTmpForm = jQuery(this).closest(".bfi-timeperiod-change");
            var currTmpCheckin = jQuery(currTmpForm).find("input[name='checkin']").first();
            var date = currTmpCheckin.val();
            bfi_checkDate(jQuery, currTmpCheckin, date);

            bfidpmode = 'checkout';
            //			jQuery(this).attr("disabled", true); 
            jQuery(inst.dpDiv).addClass('bfi-calendar');
            setTimeout(function () { bfi_updateTitle(currTmpForm, "bfi-checkout", "bfi-checkin", "Checkout"); }, 1);
            var windowsize = jQuery(window).width();
            jQuery(inst.dpDiv)[0].className = jQuery(inst.dpDiv)[0].className.replace(/(^|\s)bfi-calendar-affixtop\S+/g, '');
            if (jQuery(this).closest("div.bfiAffixTop").length || windowsize < 767) {
                jQuery(inst.dpDiv).addClass('bfi-calendar-affixtop' + currModId);
            }
            //			bfi_printChangedDateTimePeriod(); 

        },
        onSelect: function (date, inst) {
//            console.log("checkout  onSelect");
            bfi_updateCheckOutDaysTimesHours();
            bfi_printChangedDateTimePeriod();
            jQuery(this).trigger("change");
        },
        onChangeMonthYear: function (dateText, inst) {
            var currTmpForm = jQuery(this).closest(".bfi-timeperiod-change");
            setTimeout(function () { bfi_updateTitle(currTmpForm, "bfi-checkout", "bfi-checkin", "Checkout"); }, 1);
        },
        minDate: '+0d',
        beforeShowDay: function (date) {
//			console.log("checkout beforeShowDay");
            //console.log(availabilityCheckoutTimePeriod);
            var currResourceid = jQuery("#bfi-timeperiod-select").attr("data-resid");
            var currTmpForm = jQuery(this).closest(".bfi-timeperiod-change");
			return bfi_closedBooking(date, 0, checkOutDaysToEnable[currResourceid], currTmpForm, currResourceid);
        },
        firstDay: 1
    });

//    //var evntSelect = "change";
//    jQuery(document).on('change', ".selectpickerTimePeriodStart", function (e) {
//        console.log("selectpickerTimePeriodStart  change")
//
//        var currTr = jQuery("#bfimodaltimeperiod");
//        var currCheckin = currTr.find(".bfi-checkin-field").first();
//        var currCheckout = currTr.find(".bfi-checkout-field").first();
//        var curSelStart = currTr.find('.selectpickerTimePeriodStart').first();
//        var curSelEnd = currTr.find('.selectpickerTimePeriodEnd').first();
//        var currResourceid = jQuery("#bfi-timeperiod-select").attr("data-resid");
////        bfi_getAjaxDateHourCheckout(currResourceid, currCheckin, currCheckout, productAvailabilityType, curSelStart);
//    });
}

function bfi_printChangedDateTimePeriod(currForm) {
    //	var currCheckin = jQuery("#bfimodaltimeperiod").find("input[name='checkin']").first();
    //	var currCheckout = jQuery("#bfimodaltimeperiod").find("input[name='checkout']").first();
    //	var currDateFormat = "D, dd M yy";
    //	var currDateFormatChild = "dd M yy";
    //	var windowsize =  jQuery(window).width();
    //	if(windowsize > 769 && windowsize < 1300){
    //		currDateFormat = " dd mm yy";
    //		currDateFormatChild = " dd mm yy";
    //	}
    //	currCheckin.datepicker( "option", "dateFormat",currDateFormat );
    //	currCheckout.datepicker( "option", "dateFormat", currDateFormat );
}




function bfi_getAjaxDateHourCheckout(resourceId, checkinObj, checkoutObj, productAvailabilityType, checkinTime, callback) {
    var checkoutContainer = jQuery(checkoutObj).closest(".bfi-checkout-field-container").first();
    checkoutContainer.block({ message: '' });
    //task = "listDateHours";
    var datereformat = jQuery.datepicker.formatDate("yymmdd", jQuery(checkinObj).datepicker("getDate"));
    datereformat += checkinTime.val().replace(":", "") + "00";
    jQuery(checkoutObj).block({ message: '' });
    var options = {
        url: bookingfor.getActionUrl(null, null, "GetCheckOutDatesPerTimes", 'resourceId=' + resourceId + '&checkin=' + datereformat),
        dataType: 'json',
        success: function (data) {
            checkOutDaysToEnable[resourceId + ""] = [];
            availabilityTimePeriodCheckOut[resourceId + ""] = {};
            availabilityValues[resourceId + ""] = {};
            jQuery.each(data, function (i, dt) {
                checkOutDaysToEnable[resourceId + ""].push(dt.StartDate);
                availabilityTimePeriodCheckOut[resourceId + ""][dt.StartDate + ""] = JSON.parse(dt.TimeRangesString);
                jQuery.each(dt.Availabilities, function (j, av) {
					availabilityValues[resourceId + ""][av.Time] = av.Availability;
                });
            });

            bfi_onEnsureCheckOutDaysToEnableSuccess(checkOutDaysToEnable[resourceId + ""], resourceId, checkoutObj, checkoutContainer);
            bfi_updateCheckOutDaysTimesHours();
			//bfi_onEnsureCheckOutDaysTimesToEnable(data || [], resourceId, checkoutObj, checkoutContainer); //in file search_details.php

        }
    };
    jQuery.ajax(options);
}

function bfi_onEnsureCheckOutDaysTimesToEnable(currcheckOutDaysToEnable, currProdId, currCheckout, currCheckoutContainer) {
    if (!currcheckOutDaysToEnable || currcheckOutDaysToEnable.length == 0) {
        jQuery(currCheckoutContainer).unblock();
        return;
    }
    if (currcheckOutDaysToEnable[0] == 0) {
        jQuery(currCheckoutContainer).unblock();
        return;
    }

    var currTr = jQuery("#bfimodaltimeperiod");
    var curSelEnd = currTr.find('.selectpickerTimePeriodEnd').first();

    var strDate = '' + currcheckOutDaysToEnable[0].StartDate;
    var timeLength = currcheckOutDaysToEnable[0].TimeLength;
    var date = new Date(strDate.substr(0, 4), strDate.substr(4, 2) - 1, strDate.substr(6, 2));

    currCheckout.datepicker("option", "minDate", date);
    var datetocheck = currCheckout.datepicker("getDate");
    availabilityCheckoutTimePeriod = {};
    for (var i = 0; i < currcheckOutDaysToEnable.length; i++) {
		checkOutDaysToEnable[currProdId][i] = currcheckOutDaysToEnable[i].StartDate;
        availabilityCheckoutTimePeriod[currcheckOutDaysToEnable[i].StartDate + ""] = currcheckOutDaysToEnable[i].TimeRanges;
    }

	if (!bfi_enableSpecificDates(datetocheck, 1, checkOutDaysToEnable[currProdId])) {
        currCheckout.val(getDisplayDate(date));
    }

    //bfi_timeStepCheckin(currCheckout,curSelEnd,availabilityCheckoutTimePeriod,TimeLengthtOEnableTimePeriod[currProdId]);
    bfi_updateCheckOutDaysTimesHours();

    currCheckoutContainer.unblock();
}

function bfi_updateCheckOutDaysTimesHours() {
    var currTr = jQuery("#bfimodaltimeperiod");
    var currCheckout = currTr.find(".bfi-checkout-field").first();
    var curSelEnd = currTr.find('.selectpickerTimePeriodEnd').first();
    var currResourceid = jQuery("#bfi-timeperiod-select").attr("data-resid");
    var currSelectedChyeckOut = jQuery("#bfi-timeperiod-select").attr("data-resid");
	bfi_timeStepCheckin(currCheckout, curSelEnd, availabilityTimePeriodCheckOut[currResourceid + ""], parseInt(jQuery("#bfi-timeperiod-select").attr("data-timelength")), currTr.attr("data-timeend"));
}

function updateTotalSelectablePeriod(currEl, updateQuote) {
    var dialogDiv = currEl.closest("div.bfi-timeperiod-change");
    // 	var currTr = jQuery(bfi_currTRselected).find(".bfi-timeperiod-change");
    var resid = currEl.data("resid");
    var rateplanid = currEl.data("rateplanid");
    //	var currSel = jQuery("#ddlrooms-"+resid+"-"+rateplanid).first();

    var currTable = jQuery(bfi_currTRselected).closest("table");
    jQuery(currTable).find(".ddlrooms-" + resid).each(function (index, currSel) {
        jQuery(currSel)
            .find('option')
            .remove()
            .end();
        var isSelectable = true;
		var maxSelectable = parseInt(jQuery(currSel).attr("data-maxvalue"));

        var startDate = jQuery.datepicker.formatDate("yymmdd", dialogDiv.find(".bfi-checkin-field").datepicker("getDate")) + dialogDiv.find(".selectpickerTimePeriodStart").val().replace(":", "") + "00";
        var endDate = jQuery.datepicker.formatDate("yymmdd", dialogDiv.find(".bfi-checkout-field").datepicker("getDate")) + dialogDiv.find(".selectpickerTimePeriodEnd").val().replace(":", "") + "00";

        jQuery.each(jQuery.grep(Object.keys(availabilityValues[resid]), function (k) {
            return startDate <= k && endDate > k
        }), function (i, dt) {
            if (availabilityValues[resid][dt] < maxSelectable) {
                maxSelectable = availabilityValues[resid][dt];
            }
        });

        /*
        var selectStart = dialogDiv.find(".selectpickerTimePeriodStart").first();
        var selectEnd = dialogDiv.find(".selectpickerTimePeriodEnd").first();
        for (var i = selectStart.prop('selectedIndex'); i <= selectEnd.prop('selectedIndex'); i++) {
            var currOption = dialogDiv.find('.selectpickerTimePeriodStart option').eq(i);
            var currAvailability = Number(jQuery(currOption).attr('data-availability'));
            if (currAvailability == 0) {
                isSelectable = false;
                break;
            }
            if (currAvailability < maxSelectable || i == selectStart.prop('selectedIndex')) {
                maxSelectable = currAvailability;
            }
        }
        */
        var singleMaxSelectable = Math.min(bfi_MaxQtSelectable, maxSelectable);
        if (isSelectable) {
            jQuery(currSel).show();
            if (jQuery(".ddlrooms-" + resid).first().hasClass("ddlrooms-indipendent")) {
                jQuery.each(jQuery(".ddlrooms-" + resid), function (j, itm) {
                    jQuery(itm).find('option').remove();
                    jQuery(itm).attr("data-availability", maxSelectable);
                    for (var i = 0; i <= singleMaxSelectable; i++) {
                        var opt = jQuery('<option>').text(i).attr('value', i);
                        if (i == 0) { opt.attr("selected", "selected"); }
                        jQuery(itm).append(opt);
                    }
                });
            } else {
                var currentSelectedQt = parseInt(jQuery(currSel).val());
                if (currentSelectedQt == 0) { currentSelectedQt = 1; }
                for (var i = 0; i <= maxSelectable; i++) {
                    var opt = jQuery('<option>').text(i).attr('value', i);
                    if (currentSelectedQt == i) { opt.attr("selected", "selected"); }
                    jQuery(currSel).append(opt);
                }
            }

        } else {
            jQuery(currSel).hide();
        }
    });
}

function bfi_selecttimeperiod(currEl) {
    var currContainer = jQuery("#bfimodaltimeperiod");
    //    var currDiv = jQuery(currEl).closest(".bfi-timeperiod-change");
    //    var currDiv = jQuery(bfi_currTRselected).find(".bfi-timeperiod-change");

    var currFromDate = currContainer.find(".ChkAvailibilityFromDateTimePeriod").first();
    var resourceId = jQuery(currEl).attr("data-resid");
    jQuery.unblockUI();

    updateTotalSelectablePeriod(jQuery(currEl), true);
    var currdDlrooms = jQuery(bfi_currTRselected).find(".ddlrooms-" + resourceId).first();
    if (currdDlrooms.length == 0) {
        currdDlrooms = jQuery("#ddlrooms-" + resourceId + "-0");
		jQuery(".ddlrooms-" + resourceId +".ddlrooms-indipendent").each(function (index, currSel) { 
			jQuery(currSel).val(0);
			jQuery(currSel).trigger("change");
		});
    }
    getcompleterateplansstaybyidPerTime(resourceId, currdDlrooms);

    //	if (currdDlrooms.hasClass("ddlrooms-indipendent")) // if is a extra...
    //	{
    //	}else{
    //		bfi_quoteCalculatorServiceChanged(currdDlrooms);
    //	}

    currContainer.dialog("close");
}

function getcompleterateplansstaybyidPerTime(resourceId, currdDlrooms) {
    //debugger;
    //    var currTr = jQuery('#bfi-timeperiod-'+resourceId);
    var currTr = jQuery(bfi_currTRselected).find(".bfi-timeperiod");

    currTr.find(".bfi-hide").removeClass("bfi-hide");

    var currContainer = jQuery("#bfimodaltimeperiod");


    var currFromDate = currContainer.find(".ChkAvailibilityFromDateTimePeriod.bfi-checkin-field").first();
    var currToDate = currContainer.find(".ChkAvailibilityFromDateTimePeriod.bfi-checkout-field").first();

    var currentTimeStart = currContainer.find(".selectpickerTimePeriodStart option:selected");
    var currentTimeEnd = currContainer.find(".selectpickerTimePeriodEnd option:selected");
    var currentTimeStartVal = bookingfor.pad(currentTimeStart.val().replace(":", "") + "00", 6);
    var currentTimeEndVal = bookingfor.pad(currentTimeEnd.val().replace(":", "") + "00", 6);

    var currObjToLoock = jQuery(bfi_currTRselected).find("table"); //".bfi-table-resources";
    if (currObjToLoock.length == 0) {
        currObjToLoock = jQuery(bfi_currTRselected).closest("table");
    }
    bookingfor.waitSimpleWhiteBlock(currObjToLoock);

    var mcurrFromDate = jQuery(currFromDate).datepicker("getDate");
    var checkInTime = jQuery.datepicker.formatDate("yymmdd", mcurrFromDate) + currentTimeStartVal;
    var mcurrToDate = jQuery(currToDate).datepicker("getDate");
    var checkOutTime = jQuery.datepicker.formatDate("yymmdd", mcurrToDate) + currentTimeEndVal;

    var fromDate = jQuery.datepicker.formatDate("yy-mm-dd", jQuery(currFromDate).datepicker("getDate"));
    var toDate = jQuery.datepicker.formatDate("yy-mm-dd", jQuery(currToDate).datepicker("getDate"));
//    var newValStart = new Date(fromDate + "T" + currentTimeStartVal + "Z");
//    var newValEnd = new Date(toDate + "T" + currentTimeEndVal + "Z");
    var newValStart = new Date(fromDate + "T" + currentTimeStartVal.replace(/(.{2})(.{2})(.{2})/, '$1:$2:$3') + "Z");
    var newValEnd = new Date(toDate + "T" + currentTimeEndVal.replace(/(.{2})(.{2})(.{2})/, '$1:$2:$3') + "Z");

    var currCheckin = currTr.find(".bfi-time-checkin").first();
    var currCheckinhours = currTr.find(".bfi-time-checkin-hours").first();
    var currCheckout = currTr.find(".bfi-time-checkout").first();
    var currCheckouthours = currTr.find(".bfi-time-checkout-hours").first();
    var currduration = currTr.find(".bfi-total-duration").first();

    var diffMs = (newValEnd - newValStart);
    var duration = Math.floor(diffMs / 60000);
    var durationHours = duration / 60;
    var durationStr = "";
    if ((durationHours) >= 24) {
        durationStr += currduration.attr("data-dayname").replace("%d", Math.floor(durationHours / 24));
    }
    if ((durationHours % 24) > 0) {
//MVC:        durationStr += (durationStr.length ? ", " : "") + currduration.attr("data-hourname").replace("{0}", (Math.round(durationHours % 24) + Math.round(durationHours / (24 * 60))).toFixed(2));
        durationStr += (durationStr.length ? ", " : "") + (Math.round(durationHours % 24) + (durationHours / (24 * 60))).toFixed(2) + " " + currduration.attr("data-hourname");
    }

    currTr.attr("data-checkin", jQuery.datepicker.formatDate("yymmdd", mcurrFromDate));
    currTr.attr("data-checkintime", checkInTime);
    currTr.attr("data-timestart", currentTimeStart.val());
    currTr.attr("data-timeend", currentTimeEnd.val());
    currTr.attr("data-duration", duration);

    currCheckin.html(jQuery.datepicker.formatDate("D d M yy", mcurrFromDate));
    currCheckinhours.html(currentTimeStart.val());
    currCheckout.html(jQuery.datepicker.formatDate("D d M yy", mcurrToDate));
    currCheckouthours.html(currentTimeEnd.val());
    currduration.html(durationStr);

    if (jQuery(".ddlrooms-" + resourceId).first().hasClass("ddlrooms-indipendent")) // if is a extra...
    {
        var searchModel = jQuery('#bfi-calculatorForm').serializeObject();
        var dataarray = {};
        //var dataarray = jQuery('#bfi-calculatorForm').serializeArray();
        jQuery.each(Object.keys(searchModel), function (i, k) {
            dataarray[k] = searchModel[k];
        });

        dataarray["resourceId"] = resourceId;
        dataarray["id"] = resourceId;
        //dataarray["timeMinStart"] = currentTimeEnd.attr("data-timeminstart");
        //dataarray["timeMinEnd"] = currentTimeEnd.attr("data-timeminend");
        dataarray["FromTime"] = currentTimeStart.val() || "00:00";
        dataarray["ToTime"] = currentTimeEnd.val() || "00:00";
        dataarray["ProductAvailabilityType"] = 2;
        dataarray["AvailabilityType"] = 2;
        dataarray["duration"] = duration;

        //dataarray.push({ name: 'resourceId', value: resourceId });
        //dataarray.push({ name: 'id', value: resourceId });
        //dataarray.push({ name: 'timeMinStart', value: currentTimeStart.attr("data-TimeMinStart") });
        //dataarray.push({ name: 'timeMinEnd', value: currentTimeEnd.attr("data-TimeMinEnd") });
        //dataarray.push({ name: 'checkintime', value: currentTimeStart.val() });
        //dataarray.push({ name: 'checkouttime', value: currentTimeEnd.val() });
        //dataarray.push({ name: 'searchModel', value: searchModel });
        //dataarray.push({ name: 'availabilitytype', value: 2 });
        //dataarray.push({ name: 'duration', value: duration });

        var jqxhr = jQuery.ajax({
            url: bookingfor.getActionUrl(null, null, "GetCompleteRatePlansStay", null),
            type: "POST",
            dataType: "json",
            data: dataarray
        });

        jqxhr.done(function (result, textStatus, jqXHR) {
            if (result) {
                if (result.length > 0) {
                    jQuery.each(result, function (i, st) {
                        //debugger
                        currStay = st.SuggestedStay;
                        //						var currTrRateplan = jQuery("#data-id-" + resourceId + "-" + st.RatePlanId);
                        var currTrRateplan = jQuery("tr[id^=data-id-" + resourceId + "-" + st.RatePlanId + "]");
                        var currDivPrice = currTrRateplan.find(".bfi-price");
                        var currDivTotalPrice = currTrRateplan.find(".bfi-discounted-price");
                        var currDivPercentDiscount = currTrRateplan.find(".bfi-percent-discount");
                        var currSel = currTrRateplan.find(".ddlrooms");

                        currDivPrice.html(bookingfor.number_format(currStay.DiscountedPrice, 2, '.', ''))
                            .attr("data-value", currStay.DiscountedPrice)
                            .removeClass("red-color");

                        currSel.attr("data-baseprice", bookingfor.number_format(currStay.DiscountedPrice, 2, '.', ''));
                        currSel.attr("data-basetotalprice", bookingfor.number_format(currStay.TotalPrice, 2, '.', ''));
                        currSel.attr("data-price", bookingfor.priceFormat(currStay.DiscountedPrice, 2, '.', ''));
                        currSel.attr("data-totalprice", bookingfor.priceFormat(currStay.TotalPrice, 2, '.', ''));

                        //					currSel.attr("data-price",currStay.DiscountedPrice)
                        //						.attr("data-totalprice",currStay.TotalPrice);

                        currDivTotalPrice.hide();
                        currDivPercentDiscount.hide();
                        if (currStay.DiscountedPrice < currStay.TotalPrice) {
                            currDivTotalPrice.html(bookingfor.number_format(currStay.TotalPrice, 2, '.', ''))
                                .attr("data-value", currStay.TotalPrice)
                                .show();
                            currDivPrice.addClass("red-color");
//                            currDivTotalPrice.attr("rel", currStay.SimpleDiscountIds);
                            currDivPercentDiscount.attr("rel", currStay.DiscountId);
							currDivPercentDiscount.show();
                            currDivTotalPrice.find(".bfi-percent").html(currStay.VariationPercent);
                        }

                    });

                }
            }
        });


        jqxhr.always(function () {
            jQuery(currObjToLoock).unblock();
        });
    } else {
        bfi_quoteCalculatorServiceChanged(currdDlrooms);
    }

}
