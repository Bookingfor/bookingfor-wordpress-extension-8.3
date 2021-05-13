
function initDatepickerTimeSlot() {
    jQuery(".ChkAvailibilityFromDateTimeSlot").datepicker({
        numberOfMonths: 1,
        defaultDate: "+0d",
        dateFormat: "dd/mm/yy",
        minDate: strAlternativeDateToSearch,
        //maxDate: strEndDate,
        onSelect: function (date) {
            dateTimeSlotChanged(jQuery(this));
        },
        //showOn: "button",
        beforeShowDay: function (date) {
            var currResourceid = jQuery("#bfi-timeslot-select").attr("data-resid");
            var currTmpForm = jQuery(this).closest(".bfi-timeslot-change");
            //var currDays = [];
            //jQuery.each(daysToEnable[jQuery(this).attr("data-resid")], function (key, value) {
            //    currDays.push(Number(key));
            //});
            return bfi_closedBooking(date, 1, daysToEnable[currResourceid], currTmpForm, currResourceid);
        },
        //buttonText: strbuttonTextTimeSlot,
        firstDay: 1,
        beforeShow: function (input, inst) {
            bficalculatordpmode = 'checkin';
            //jQuery(this).attr("disabled", true);
            //jQuery(this).attr("readonly", true);
            jQuery(inst.dpDiv).addClass('bfi-calendar');
            setTimeout(function () {
                bfiCalendarCheck();
                jQuery("#ui-datepicker-div").addClass("bfi-checkin");
                jQuery("#ui-datepicker-div").removeClass("bfi-checkout");
                jQuery("#ui-datepicker-div div.bfi-title-arrow").remove();
                jQuery("#ui-datepicker-div").prepend("<div class=\"bfi-title-arrow\">" + "Checkin" + "</div>");
            }, 1);

        }
    });


}
function dateTimeSlotChanged(obj) {
    var currTr = jQuery("#bfimodaltimeslot");
    bookingfor.waitSimpleBlock(currTr);
    var currDate = obj.datepicker('getDate');
    var intDate = bookingfor.convertDateToInt(currDate);
    updateTimeSlotRange(intDate);
    obj.next().html(jQuery.datepicker.formatDate("D d M yy", currDate));
    jQuery(currTr).unblock();
}


function bfi_selecttimeslot(currEl) {
    var currContainer = jQuery("#bfimodaltimeslot");
    var currDiv = jQuery(currEl).closest(".bfi-timeslot-change");
    var currFromDate = currContainer.find(".ChkAvailibilityFromDateTimeSlot").first();
    var currTimeSlotSelect = currContainer.find("#selectpickerTimeSlotRange option:selected");
    var currSelect = currTimeSlotSelect.text().split(" - ");
    var currTimeSlotId = currTimeSlotSelect.val();

    var resourceId = currEl.getAttribute("data-resid");
    var sourceId = currEl.getAttribute("data-sourceid");

    jQuery.unblockUI();
    updateTotalTimeSlotSelectable(jQuery(currEl));

    var curr = currContainer.find("#selectpickerTimeSlotRange");

    var mcurrFromDate = jQuery(currFromDate).datepicker("getDate");
    var fromDate = jQuery.datepicker.formatDate("yy-mm-dd", jQuery(currFromDate).datepicker("getDate"));
    var newValStart = new Date(fromDate + "T" + currSelect[0] + ":00Z");
    var newValEnd = new Date(fromDate + "T" + currSelect[1] + ":00Z");
    var diffMs = (newValEnd - newValStart);
//    var duration = Math.round(Math.floor((diffMs / 1000) / 60 / 60) * 100) / 100;
    var currTr = jQuery(bfi_currTRselected).find(".bfi-timeslot");
    var currCheckin = currTr.find(".bfi-time-checkin").first();
    var currCheckinhours = currTr.find(".bfi-time-checkin-hours").first();
    var currCheckout = currTr.find(".bfi-time-checkout").first();
    var currCheckouthours = currTr.find(".bfi-time-checkout-hours").first();
    var currduration = currTr.find(".bfi-total-duration").first();

    var duration = Math.floor(diffMs / 60000);
    var durationHours = duration / 60;
    var durationStr = "";
    if ((durationHours) >= 24) {
        durationStr += currduration.attr("data-dayname").replace("%d", Math.floor(durationHours / 24));
    }
    if ((durationHours % 24) > 0) {
//MVC:        durationStr += (durationStr.length ? ", " : "") + currduration.attr("data-hourname").replace("{0}", (Math.round(durationHours % 24) + Math.round(durationHours / (24 * 60))).toFixed(2));
        durationStr += (durationStr.length ? ", " : "") + durationHours.toFixed(2) + " " + currduration.attr("data-hourname");
    }

	
	
	
	
	//	var currTr = jQuery('.bfi-timeslot[data-sourceid="'+sourceId+'"]');
    currTr.attr("data-checkin", jQuery.datepicker.formatDate("yymmdd", mcurrFromDate));
    currTr.attr("data-checkin-ext", jQuery.datepicker.formatDate("dd/mm/yy", mcurrFromDate));
    currCheckin.html(jQuery.datepicker.formatDate("D d M yy", mcurrFromDate));
    currCheckinhours.html(currSelect[0]);
    currCheckout.html(jQuery.datepicker.formatDate("D d M yy", mcurrFromDate));
    currCheckouthours.html(currSelect[1]);
//    currduration.html(duration);
    currduration.html(durationStr);

    currTr.attr("data-timeslotid", currTimeSlotSelect.val());
    currTr.attr("data-timeslotstart", currTimeSlotSelect.attr("data-timeslotstart"));
    currTr.attr("data-timeslotend", currTimeSlotSelect.attr("data-timeslotend"));

	var currSlotListTitle = jQuery(bfi_currTRselected).find(".bfi-slot-list-title span").first();
	if (currSlotListTitle.length>0)
	{
		currSlotListTitle.html(jQuery.datepicker.formatDate("D d M yy", mcurrFromDate));
	}
    var currSlotList = jQuery(bfi_currTRselected).find(".bfi-slot-list ");
	currSlotList.find("li").remove();
	jQuery('#selectpickerTimeSlotRange').find('option').each(function (index, item) {
		var currItem = jQuery(item);
		var currTimes = item.text.split(" - ");
		var liHtml = '<li><a href="javascript:void(0);" onclick="updateTotalTimeSlotList(this)" ';
			if(currItem.val() != currTimeSlotId){
				liHtml += ' class="selectable"';
			}
			liHtml += 'data-resid="' + resourceId + '" ';
			liHtml += 'data-ProductId="' + currItem.val() + '"  ';
			liHtml += 'data-Availability="' + currItem.attr("data-availability") + '"';
			liHtml += 'data-checkin="' + jQuery.datepicker.formatDate("yy-mm-dd", mcurrFromDate) + '" ';
			liHtml += 'data-StartDate="' + currItem.attr("data-startdate") + '"  ';
			liHtml += 'data-TimeSlotStart="' + currItem.attr("data-timeslotstart") + '"  ';
			liHtml += 'data-TimeSlotEnd="' + currItem.attr("data-timeslotend") + '"  ';
			liHtml += '>';
			liHtml += currTimes[0];
			liHtml += '</a></li>';
			currSlotList.append(liHtml);
	})

    dialogTimeslot.dialog("close");
}

function updateTimeSlotRange(currDate) {
    var slotToEnableTimeSlot = [];
    var currSel = jQuery('#selectpickerTimeSlotRange')
        .find('option')
        .remove()
        .end();
    var currProdId = currSel.attr("data-resid");
    var copyarray = jQuery.extend(true, [], daysToEnableTimeSlot[currProdId]);
    slotToEnableTimeSlot = jQuery.grep(copyarray, function (ts) {
        return ts.StartDate == currDate;
    });
    slotToEnableTimeSlot.sort(function (a, b) { return a.TimeSlotStart - b.TimeSlotStart });
    jQuery.each(slotToEnableTimeSlot, function (i, currTimeSlot) {
        var tmpDate = new Date();
        tmpDate.setHours(0, 0, 0, 0);
        var newTmpDateStart = bookingfor.dateAdd(tmpDate, "minute", Number(currTimeSlot.TimeSlotStart));
        var newTmpDateEnd = bookingfor.dateAdd(tmpDate, "minute", Number(currTimeSlot.TimeSlotEnd));
        var newValStart = bookingfor.pad(newTmpDateStart.getHours(), 2) + ":" + bookingfor.pad(newTmpDateStart.getMinutes(), 2);
        var newValEnd = bookingfor.pad(newTmpDateEnd.getHours(), 2) + ":" + bookingfor.pad(newTmpDateEnd.getMinutes(), 2);
        var currOpt = jQuery('<option>').text(newValStart + " - " + newValEnd).attr('value', currTimeSlot.ProductId);
        jQuery(currOpt).attr("data-startdate", currTimeSlot.StartDate);
        jQuery(currOpt).attr("data-timeslotstart", currTimeSlot.TimeSlotStart);
        jQuery(currOpt).attr("data-timeslotend", currTimeSlot.TimeSlotEnd);
        jQuery(currOpt).attr("data-availability", currTimeSlot.Availability);
        currSel.append(currOpt);
        currTimeSlotDisp[currTimeSlot.ProductId] = currTimeSlot.Availability;
    });
}

function updateTotalTimeSlotSelectable(currEl) {
    var currContainer = jQuery("#bfimodaltimeslot");
    var currTr = currEl.closest("div.bfi-timeslot-change");
    var resid = currEl.data("resid");
    var rateplanid = currEl.data("rateplanid");
    //		var currSel = jQuery("#ddlrooms-"+resid+"-"+rateplanid).first();

    //		var sourceId = currEl.data("sourceid");
    var sourceId = jQuery(currEl).attr("data-sourceid");

    var currSel = jQuery('.ddlrooms[data-sourceid="' + sourceId + '"]').first();

    var currentSelection = currSel.val();
    //debugger;
    jQuery(currSel)
        .find('option')
        .remove()
        .end();
    var currentTimeOpt = currTr.find(".selectpickerTimeSlotRange option:selected");
    var currentTime = currentTimeOpt.val();
    var maxSelectable = Math.min(bfi_MaxQtSelectable, currTimeSlotDisp[currentTime]);
    var currSelect = currentTimeOpt.text().split(" - ");
    var currFromDate = currContainer.find(".ChkAvailibilityFromDateTimeSlot").first();
    var mcurrFromDate = jQuery(currFromDate).datepicker("getDate");
    var fromDate = jQuery.datepicker.formatDate("yy-mm-dd", jQuery(currFromDate).datepicker("getDate"));
    var newValStart = new Date(fromDate + "T" + currSelect[0] + ":00Z");
    var newValEnd = new Date(fromDate + "T" + currSelect[1] + ":00Z");
    var diffMs = (newValEnd - newValStart);
    var duration = Math.round(Math.floor((diffMs / 1000) / 60 / 60) * 100) / 100;

    var currObjToLoock = jQuery(bfi_currTRselected).find("table"); //".bfi-table-resources";
    if (currObjToLoock.length == 0) {
        currObjToLoock = jQuery(bfi_currTRselected).closest("table");
    }
    bookingfor.waitSimpleWhiteBlock(currObjToLoock);

	var correction = 1;
    if (jQuery(currSel).hasClass('ddlextras')) {
        //           currentSelection = parseInt(currentSelection.split(":")[1]);
        for (var i = 0; i <= maxSelectable; i++) {
            //var opt = jQuery('<option>').text(i).attr('value', id + ":" + i + ":::" + currentTime + ":" + currentTimeOpt.attr("data-timeslotstart") + ":" + currentTimeOpt.attr("data-timeslotend") + ":" + currentTimeOpt.attr("data-startdate"));
            var opt = jQuery('<option>').text(i).attr('value', i);
            //                if (currentSelection == i) { opt.attr("selected", "selected"); }
            if (i == 0) { opt.attr("selected", "selected"); }
            currSel.append(opt);
        }
    } else {
        jQuery.each(jQuery(".ddlrooms-" + resid), function (j, itm) {
            jQuery(itm).find('option').remove();
            jQuery(itm).attr("data-availability", currTimeSlotDisp[currentTime]);
            for (var i = 0; i <= maxSelectable; i++) {
                var opt = jQuery('<option>').text(i).attr('value', i);
                if (i == 0) { opt.attr("selected", "selected"); }
                jQuery(itm).append(opt);
            }
        });
        /*
        for (var i = 0; i <= maxSelectable; i++) {
            var opt = jQuery('<option>').text(i).attr('value', i);
            if (currentSelection == i) { opt.attr("selected", "selected"); }
            currSel.append(opt);
        }
        */
    }
    if (jQuery(".ddlrooms-" + resid).first().hasClass("ddlrooms-indipendent")) // if is a extra...
    {
        var searchModel = jQuery('#bfi-calculatorForm').serializeObject();
        var dataarray = {};
        //var dataarray = jQuery('#bfi-calculatorForm').serializeArray();
        jQuery.each(Object.keys(searchModel), function (i, k) {
            dataarray[k] = searchModel[k];
        });

        dataarray["resourceId"] = resid;
        dataarray["id"] = resid;
        dataarray["ProductAvailabilityType"] = 3;
        dataarray["AvailabilityType"] = 3;
        dataarray["duration"] = duration;
        dataarray["timeSlotId"] = currentTime;

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
						var currRateplantypeid = st.RatePlanTypeId;
						var dllId = ".ddlrooms-indipendent[data-resid='" + resid + "'][data-rateplantypeid='" + currRateplantypeid + "']";
						var currDdl = jQuery(dllId);
						var currTrRateplan = currDdl.closest("tr");;
						
//						var currTrRateplan = jQuery("tr[id^=data-id-" + resid + "-" + st.RatePlanId + "]");
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
                        currSel.attr("data-rateplanid",st.RatePlanId);

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

    bfi_UpdateQuote(); //set service price default value
    bfi_updateQuoteService();

}

function enableSpecificDatesTimeSlot(date, offset, enableDays) {
    var month = date.getMonth() + 1;
    var day = date.getDate();
    var year = date.getFullYear();
    var copyarray = jQuery.extend(true, [], enableDays);
    var listDays = jQuery.map(copyarray, function (n, i) {
        return (n.StartDate);
    });
    //		console.log(copyarray);
    //		console.log(listDays);

    listDays = jQuery.unique(listDays);
    var listDaysunique = listDays.filter(function (elem, index, self) {
        return index == self.indexOf(elem);
    })
    for (var i = 0; i < offset; i++)
        listDaysunique.pop();
    var datereformat = year + '' + bookingfor.pad(month, 2) + '' + bookingfor.pad(day, 2);
    if (jQuery.inArray(Number(datereformat), listDaysunique) != -1) {
        return [true, 'greenDay'];
    }
    return [false, 'redDay'];
}

////**********-------------new function

function updateTotalTimeSlotList(obj) {
	var currEl = jQuery(obj);
    var bfi_currTRselected = currEl.closest("tr");
    var resid = currEl.attr("data-resid");
    var currentTime = currEl.attr("data-productid");
    var currSel = jQuery('.ddlrooms[data-sourceid="' + resid + '"]').first();
    var fromDatestr = jQuery(currEl).attr("data-checkin");
    var fromDate = new Date(fromDatestr + 'T00:00:00Z');
    var timeSlotStart = jQuery(currEl).attr("data-TimeSlotStart");
    var timeSlotEnd = jQuery(currEl).attr("data-TimeSlotEnd");
	
	var tmpDate = new Date();
	tmpDate.setHours(0, 0, 0, 0);
	var newTmpDateStart = bookingfor.dateAdd(tmpDate, "minute", Number(timeSlotStart));
	var newTmpDateEnd = bookingfor.dateAdd(tmpDate, "minute", Number(timeSlotEnd));
	var newValStartHours = bookingfor.pad(newTmpDateStart.getHours(), 2) + ":" + bookingfor.pad(newTmpDateStart.getMinutes(), 2);
	var newValEndHours = bookingfor.pad(newTmpDateEnd.getHours(), 2) + ":" + bookingfor.pad(newTmpDateEnd.getMinutes(), 2);
	var newValStart = new Date(fromDatestr + "T" + newValStartHours + ":00Z");
    var newValEnd = new Date(fromDatestr + "T" + newValEndHours + ":00Z");
	var diffMs = (newValEnd - newValStart);
    var duration = Math.floor(diffMs / 60000);

	bfi_currTRselected.find(".bfi-slot-list a").each(function (index, item) {
		jQuery(item).addClass("selectable");
		currTimeSlotDisp[jQuery(item).attr("data-productid")] = Math.min( jQuery(item).attr("data-Availability") ,currSel.attr("data-maxqt")) ;
	})
	currEl.removeClass("selectable");
    var maxSelectable = Math.min(bfi_MaxQtSelectable, currTimeSlotDisp[currentTime]);  

	var currTr = jQuery(bfi_currTRselected).find(".bfi-timeslot");
    var currCheckin = currTr.find(".bfi-time-checkin").first();
    var currCheckinhours = currTr.find(".bfi-time-checkin-hours").first();
    var currCheckout = currTr.find(".bfi-time-checkout").first();
    var currCheckouthours = currTr.find(".bfi-time-checkout-hours").first();
    var currduration = currTr.find(".bfi-total-duration").first();
    var durationHours = duration / 60;
    var durationStr = "";
    if ((durationHours) >= 24) {
        durationStr += currduration.attr("data-dayname").replace("%d", Math.floor(durationHours / 24));
    }
    if ((durationHours % 24) > 0) {
//MVC:        durationStr += (durationStr.length ? ", " : "") + currduration.attr("data-hourname").replace("{0}", (Math.round(durationHours % 24) + Math.round(durationHours / (24 * 60))).toFixed(2));
        durationStr += (durationStr.length ? ", " : "") + durationHours.toFixed(2) + " " + currduration.attr("data-hourname");
    }

	
	
	
	
	//	var currTr = jQuery('.bfi-timeslot[data-sourceid="'+sourceId+'"]');
    currTr.attr("data-checkin", jQuery.datepicker.formatDate("yymmdd", newValStart));
    currTr.attr("data-checkin-ext", jQuery.datepicker.formatDate("dd/mm/yy", newValStart));
    currCheckin.html(jQuery.datepicker.formatDate("D d M yy", newValStart));
    currCheckinhours.html(newValStartHours);
    currCheckout.html(jQuery.datepicker.formatDate("D d M yy", newValStart));
    currCheckouthours.html(newValEndHours);
//    currduration.html(duration);
    currduration.html(durationStr);
	
    var currentSelection = currSel.val();
    //debugger;
    jQuery(currSel)
        .find('option')
        .remove()
        .end();
    var currObjToLoock = jQuery(bfi_currTRselected).find("table"); //".bfi-table-resources";
    if (currObjToLoock.length == 0) {
        currObjToLoock = jQuery(bfi_currTRselected).closest("table");
    }
    bookingfor.waitSimpleWhiteBlock(currObjToLoock);

	var correction = 1;
    if (jQuery(currSel).hasClass('ddlextras')) {
        //           currentSelection = parseInt(currentSelection.split(":")[1]);
        for (var i = 0; i <= maxSelectable; i++) {
            //var opt = jQuery('<option>').text(i).attr('value', id + ":" + i + ":::" + currentTime + ":" + currentTimeOpt.attr("data-timeslotstart") + ":" + currentTimeOpt.attr("data-timeslotend") + ":" + currentTimeOpt.attr("data-startdate"));
            var opt = jQuery('<option>').text(i).attr('value', i);
            //                if (currentSelection == i) { opt.attr("selected", "selected"); }
            if (i == 0) { opt.attr("selected", "selected"); }
            currSel.append(opt);
        }
    } else {
        jQuery.each(jQuery(".ddlrooms-" + resid), function (j, itm) {
            jQuery(itm).find('option').remove();
            jQuery(itm).attr("data-availability", currTimeSlotDisp[currentTime]);
            for (var i = 0; i <= maxSelectable; i++) {
                var opt = jQuery('<option>').text(i).attr('value', i);
                if (i == 0) { opt.attr("selected", "selected"); }
                jQuery(itm).append(opt);
            }
        });
    }
    if (jQuery(".ddlrooms-" + resid).first().hasClass("ddlrooms-indipendent")) // if is a extra...
    {
        var searchModel = jQuery('#bfi-calculatorForm').serializeObject();
        var dataarray = {};
        //var dataarray = jQuery('#bfi-calculatorForm').serializeArray();
        jQuery.each(Object.keys(searchModel), function (i, k) {
            dataarray[k] = searchModel[k];
        });

        dataarray["resourceId"] = resid;
        dataarray["id"] = resid;
        dataarray["ProductAvailabilityType"] = 3;
        dataarray["AvailabilityType"] = 3;
        dataarray["duration"] = duration;
        dataarray["timeSlotId"] = currentTime;
        dataarray["getAllPaxConfigurations"] = true;

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
						var currRateplantypeid = st.RatePlanTypeId;
						var dllId = ".ddlrooms-indipendent[data-resid='" + resid + "'][data-rateplantypeid='" + currRateplantypeid + "'][data-paxes='" + st.SuggestedStay.Paxes + "']";
						var currDdl = jQuery(dllId);
						var currTrRateplan = currDdl.closest("tr");;
						
//						var currTrRateplan = jQuery("tr[id^=data-id-" + resid + "-" + st.RatePlanId + "]");
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
                        currSel.attr("data-rateplanid",st.RatePlanId);

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
						var currTS = jQuery("#bfi-timeslot-" + resid).first();
						if (currTS.length)
						{
							currTS.attr("data-timeslotid",currentTime);
                            currTS.attr("data-timeslotstart",timeSlotStart);
                            currTS.attr("data-timeslotend",timeSlotEnd);
                            currTS.attr("data-rateplanid",st.RatePlanId);
						}
						jQuery(".ddlextras[data-bindingproductid=" + resid + ']').each(function () {
							var currselectableprice = jQuery(this);
							currselectableprice.attr("data-rateplanid",st.RatePlanId);
						});

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

    bfi_UpdateQuote(); //set service price default value
    bfi_updateQuoteService();

}
