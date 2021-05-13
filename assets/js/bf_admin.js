jQuery(document).ready(function() {
	jQuery(document).ajaxSuccess(function(e, xhr, settings) {
		var widget_id_base = 'bookingfor_booking_search';
		if(typeof settings.data === "string" && settings.data.search('action=save-widget') != -1 && settings.data.search('id_base=bookingfor_booking_search') != -1) {
			var widgetid = bfi_getParameterByName('widget-id', settings.data);
			var cForm = jQuery('input[value="' + widgetid + '"]').parents("form");
			bfi_adminInit(cForm); 
		}
		if(typeof settings.data === "string" && settings.data.search('action=save-widget') != -1 && settings.data.search('id_base=bookingfor_carousel') != -1) {
			var widgetid = bfi_getParameterByName('widget-id', settings.data);
			var cForm = jQuery('input[value="' + widgetid + '"]').parents("form");
			bfi_adminselect2Init(cForm);
		}

	});
	jQuery(document).on('click','.bfitabmoretab input',function(){
		bfi_CheckTabs(jQuery(this));
		currForm = jQuery(jQuery(this)).closest("form");
		jQuery(currForm).find(".bfitabsearch input").each(function() {
			bfi_ShowHideoptSearch(jQuery(this))
		});
	});

	jQuery(document).on('click','.bfiadvance-cb',function(){
		bfi_CheckAdvance(jQuery(this));
	});

	jQuery(document).on('click','.bfitabsearch input',function(){
		bfi_CheckTabs(jQuery(this))
		currForm = jQuery(jQuery(this)).closest("form");
		jQuery(currForm).find(".bfitabsearch input").each(function() {
			bfi_ShowHideoptSearch(jQuery(this))
		});
//		bfi_ShowHideoptSearch(jQuery(this))
	});
	jQuery(document).on('click','.bfi_showpersons input',function(){
		bfi_Showpersons(jQuery(this))
	});
	
	jQuery(".bfitabmoretab").each(function() {
		currForm = jQuery(jQuery(this)).closest("form");
		bfi_adminInit(currForm);
	});
	jQuery(".bfi-select2").each(function() {
		currForm = jQuery(jQuery(this)).closest("form");
		bfi_adminselect2Init(currForm);
	});

	
	jQuery.widget.bridge('bfiTabsDetails', jQuery.ui.tabs);
	jQuery("#bfiadminsetting").bfiTabsDetails();

	jQuery(document).on('click','#bfi_enablegooglemapsapi_key',function(){
		bfiShowMapSetting();
	});
	
	bfiShowMapSetting();
	jQuery(document).on('change','#bfi_openstreetmap_key',function(){
		bfiShowGooglemapskey();
	});

	jQuery(document).on('change','.bfi-starttime',function(){
		var currStartTime = jQuery(this);
		var currContainer = currStartTime.closest(".bookingoptions").first();
		var currEndTime = currContainer.find(".bfi-endtime").first();
		if(currEndTime.length){
			currEndTime.val(currStartTime.val()); 
			currEndTime.timepicker('option', 'minTime', currStartTime.val()); 
			currEndTime.timepicker('option', 'maxTime', '00:00'); 
			// da trovare il select corretto
			var currEndTimeSelect = currContainer.find("select[name*='endDateTimeRange']").first();
//			console.log(currEndTimeSelect);
			currEndTimeSelect.find("option").first().remove();
		}
	});
	jQuery(document).on('click','.bfiCkbminmaxresource',function(){
		bfi_ShowHideMinMaxresource(jQuery(this));
	});
	if (typeof elementor !== "undefined")
	{
		elementor.hooks.addAction( 'panel/open_editor/widget', function( panel, model, view ) {
			//codice per elementor
			setTimeout(function(){
				jQuery(".bfitabmoretab").each(function() {
					currForm = jQuery(jQuery(this)).closest("form");
					bfi_adminInit(currForm);
				});
				jQuery(".bfi-select2").each(function() {
					currForm = jQuery(jQuery(this)).closest("form");
					bfi_adminselect2Init(currForm);
				});
			},3000);
		} );
	}
});

function bfi_CheckTabs(obj){
		firstCheched= jQuery(obj).closest("p").find('.bfitabsearch input:checked').first();
		var group = jQuery(obj).closest("p").find(".bfitabsearch input");
		if (jQuery(obj).closest("p").find('.bfitabmoretab input').is(":checked")) {
		}else{
			if (jQuery(obj).hasClass('bfitabsearch_cb'))
			{
				firstCheched = jQuery(obj);
			}
			jQuery(group).prop("checked", false);
			if (firstCheched)
			{
				firstCheched.prop("checked", true);
			}
		}
}
function bfi_CheckAdvance(obj){
		currForm = jQuery(obj).closest("form");
		currForm.find(".bfiadvance").hide();
		if (jQuery(obj).is(":checked"))
		{
			currForm.find(".bfiadvance").show();
		}
}
function bfi_ShowHideMinMaxresource(obj){
	jQuery(obj).closest("p").find(".bfiminmaxresource").first().hide();
	if(jQuery(obj).attr("checked")) {
		jQuery(obj).closest("p").find(".bfiminmaxresource").first().show();
	}
}

function bfi_ShowHideMinMaxrooms(obj){
	jQuery(obj).closest("p").find(".bfiminmaxrooms").first().hide();
	if(jQuery(obj).attr("checked")) {
		jQuery(obj).closest("p").find(".bfiminmaxrooms").first().show();
	}
}

function bfiShowMapSetting(){
	var settingRequired = false;
	if (jQuery("#bfi_enablegooglemapsapi_key:checked").length)
	{
		jQuery("#bfi_enablegooglemapsapi_key").closest("table").find("tr").not(':first').show();
		settingRequired = true;
	}else{
		jQuery("#bfi_enablegooglemapsapi_key").closest("table").find("tr").not(':first').hide();
	}
	bfiShowGooglemapskey();
	jQuery("#bfi_posx_key").prop('required', settingRequired);
	jQuery("#bfi_posy_key").prop('required', settingRequired);

}

function bfiShowGooglemapskey(){
	var settingRequired = false;
	if (jQuery("#bfi_openstreetmap_key").val()=="0" && jQuery("#bfi_enablegooglemapsapi_key:checked").length)
	{
		jQuery("#bfi_googlemapskey_key").closest("tr").show();
		settingRequired = true;
	}else{
		jQuery("#bfi_googlemapskey_key").closest("tr").hide();
	}
	jQuery("#bfi_posy_key").prop('required', settingRequired);

}
function bfi_adminselect2Init(currForm){
		if(currForm!= null && jQuery(currForm).length){
			jQuery(currForm).find(".select2").select2();
			jQuery(currForm).find(".select2full").select2({ width: '100%' });
		}else{
			jQuery(".select2").not('[name*="__i__"]').select2();
			jQuery(".select2full").not('[name*="__i__"]').select2({ width: '100%' });
		}
}


function bfi_adminInit(currForm){
	bfi_adminselect2Init(currForm);
//	if(!jQuery("#g5-container").length){
//	}
//	jQuery(".bfitabsearch input").click(function() {
//		bfi_ShowHideoptSearch(jQuery(this))
//	});
	bfi_CheckTabs(jQuery(currForm).find('.bfitabmoretab input').first());
	bfi_CheckAdvance(jQuery(currForm).find('.bfiadvance-cb').first());
	jQuery(currForm).find(".bfitabsearch input").each(function() {
		bfi_ShowHideoptSearch(jQuery(this))
	});
	jQuery(currForm).find(".bfi_showpersons input").each(function() {
		bfi_Showpersons(jQuery(this))
	});
	jQuery(currForm).find(".ui-timepicker-input").timepicker({
		'show2400':true,
		'step':15,
		'useSelect':true,
		'timeFormat': 'H:i'
	});
	jQuery(currForm).find(".bfiCkbminmaxresource").each(function() {
		bfi_ShowHideMinMaxresource(jQuery(this))
	});
	jQuery(currForm).find(".bfiCkbminmaxrooms").each(function() {
		bfi_ShowHideMinMaxrooms(jQuery(this))
	});
		jQuery(document).on('change', ".bfiselminresource", function (e) {
			var currSelMin = jQuery(this);
			var currSelMax = currSelMin.closest("span.bfiminmaxresource").find(".bfiselmaxresource").first();
			if(currSelMin.prop('selectedIndex')>currSelMax.prop('selectedIndex') ){
				currSelMax.prop('selectedIndex',currSelMin.prop('selectedIndex'));
			}
		});
		jQuery(document).on('change', ".bfiselmaxresource", function (e) {
			var currSelMax = jQuery(this);
			var currSelMin = currSelMax.closest("span.bfiminmaxresource").find(".bfiselminresource").first();
			if(currSelMax.prop('selectedIndex')<currSelMin.prop('selectedIndex') ){
				currSelMin.prop('selectedIndex',currSelMax.prop('selectedIndex'));
			}
		});
}

function bfi_Showpersons(obj){
	if(jQuery(obj).attr("checked")) {
		jQuery(obj).closest("p").find(".bfi_nopersons").hide();
	}else{
		jQuery(obj).closest("p").find(".bfi_nopersons").show();
	}
}

function bfi_ShowHideoptSearch(obj){
	currForm = jQuery(obj).closest("form");
	if(currForm.find(".bfickbbooking input:checked").length){
		currForm.find(".bookingoptions").show();
	}else{
		currForm.find(".bookingoptions").hide();
	}

	if(currForm.find(".bfickbrealestate input:checked").length){
		currForm.find(".realestateoptions").show();
	}else{
		currForm.find(".realestateoptions").hide();
	}
	if(currForm.find(".bfickbevent input:checked").length){
		currForm.find(".eventoptions").show();
	}else{
		currForm.find(".eventoptions").hide();
	}
	if(currForm.find(".bfickbevent input:checked").length){
		currForm.find(".eventoptions").show();
	}else{
		currForm.find(".eventoptions").hide();
	}
	if(currForm.find(".bfickbmapsell input:checked").length){
		currForm.find(".mapsellptions").show();
	}else{
		currForm.find(".mapsellptions").hide();
	}
	if(jQuery(obj).attr("checked")) {
		currForm.find(".bfitabsearch"+jQuery(obj).val()).show();
	}else{
		currForm.find(".bfitabsearch"+jQuery(obj).val()).hide();
	}

}

function bfi_getParameterByName(name, url) {
    if (!url) {
      url = window.location.href;
    }
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}