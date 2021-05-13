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

$merchantSiteUrl = '';
$mrcindirizzo = "";
$mrccap = "";
$mrccomune = "";
$mrcstate = "";

if (empty($merchant->AddressData)){
	$mrcindirizzo = isset($merchant->Address)?$merchant->Address:""; 
	$mrccap = isset($merchant->ZipCode)?$merchant->ZipCode:""; 
	$mrccomune = isset($merchant->CityName)?$merchant->CityName:""; 
	$mrcstate = isset($merchant->StateName)?$merchant->StateName:""; 
	$merchantSiteUrl = isset($merchant->SiteUrl)?$merchant->SiteUrl:""; 
}else{
	$addressData = isset($merchant->AddressData)?$merchant->AddressData:"";
	$mrcindirizzo = isset($addressData->Address)?$addressData->Address:""; 
	$mrccap = isset($addressData->ZipCode)?$addressData->ZipCode:""; 
	$mrccomune = isset($addressData->CityName)?$addressData->CityName:""; 
	$mrcstate = isset($addressData->StateName)?$addressData->StateName:"";
	$merchantSiteUrl = isset($addressData->SiteUrl)?$addressData->SiteUrl:""; 
}

$uriMerchant = $routeMerchant;
$uriMerchantResources = '';
$uriMerchantOffers = '';
$uriMerchantOnsellunits = '';
$uriMerchantRatings = '';
$uriMerchantRedirect = '';
$uriMerchantEvents = '';
if(!empty( $uriMerchant )){
	$uriMerchantResources = $uriMerchant .'/'._x( 'resources', 'Page slug', 'bfi' ).'?limitstart=0';
	$uriMerchantOffers = $uriMerchant .'/'._x('offers', 'Page slug', 'bfi' ).'?limitstart=0';
	$uriMerchantOnsellunits = $uriMerchant .'/'._x( 'onsellunits', 'Page slug', 'bfi' ).'?limitstart=0';
	$uriMerchantRatings = $uriMerchant .'/'._x('reviews', 'Page slug', 'bfi' );
	$uriMerchantRedirect = $uriMerchant .'/'._x('redirect', 'Page slug', 'bfi' );
	$uriMerchantEvents = $uriMerchant .'/'._x('events', 'Page slug', 'bfi' ).'?limitstart=0';
	
}



$merchantLogo = BFI()->plugin_url() . "/assets/images/defaults/default-s3.jpeg";
if (!empty($merchant->LogoUrl)){
	$merchantLogo = BFCHelper::getImageUrlResized('merchant',$merchant->LogoUrl, 'merchant_logo');
}

?>
<!-- In short -->
			<div class="bfi-row bfi-row-background">
				<div class="bfi-col-md-12">
						<h4 class="bfi-title-content" ><?php echo  _e('House rules', 'bfi') ?></h4>
					<div class="bfi-merchant-inshort">
					<?php 
					if( ($merchant->AcceptanceCheckIn != "-" || $merchant->AcceptanceCheckOut != "-")  ){						
						switch ($merchant->TypeId) {
							case bfi_ItemType::Rental:
								if( ($merchant->AcceptanceCheckIn != "-" || $merchant->AcceptanceCheckOut != "-")  ){
										?>
										<div class="bfi-row bfi-margin-bottom20">
											<div class="bfi-col-md-3 ">
												<i class="fas fa-key"></i> <?php _e('Pick Up & Drop Off', 'bfi') ?></b>
											</div>	
											<div class="bfi-col-md-9 ">
												<?php echo $merchant->AcceptanceCheckIn  ?> 
												<?php
												if ($merchant->AcceptanceCheckOut != "-")
												{
													?>
														| <?php echo $merchant->AcceptanceCheckOut  ?> 				
													<?php
												}
												?> 
											</div>	
										</div>	
									<?php 
								}
								break;
							case bfi_ItemType::Beach:
								if( ($merchant->AcceptanceCheckIn != "-" || $merchant->AcceptanceCheckOut != "-")  ){
										?>
									<div class=" bfi-mrcacceptance">
										<div class="bfi-row">
											<div class="bfi-col-md-3 ">
											<i class="fa fa-calendar" data-toggle="tooltip" title="<?php _e('Open', 'bfi') ?>"> </i> <?php _e('Open', 'bfi') ?>
											</div>	
											<div class="bfi-col-md-9 ">
												<div id="bfi-checkin-merchant"></div>
											</div>	
										</div>	
                                            <?php
                                    if ($merchant->AcceptanceCheckOut != "-")
                                    {
										?>
										<div class="bfi-row">
											<div class="bfi-col-md-3 ">
											<i class="fa fa-calendar" data-toggle="tooltip" title="<?php _e('Check-out', 'bfi') ?>"> </i> <?php _e('Check-out', 'bfi') ?>
											</div>	
											<div class="bfi-col-md-9 ">
												<div id="bfi-checkout-merchant"></div>
											</div>	
										</div>	
										<?php
                                    }
											?> 
										</div>	
									<?php 
								}
								break;
							default: 
								if( ($merchant->AcceptanceCheckIn != "-" && $merchant->AcceptanceCheckOut != "-")  ){
					?>
						<div class=" bfi-mrcacceptance bfi-margin-bottom20">
							<div class="bfi-row">
								<div class="bfi-col-md-3 ">
								<i class="fa fa-calendar" data-toggle="tooltip" title="<?php _e('Check-in', 'bfi') ?>"> </i> <?php _e('Check-in', 'bfi') ?>
								</div>	
								<div class="bfi-col-md-9 ">
									<div id="bfi-checkin-merchant"></div>
								</div>	
							</div>	
							<div class="bfi-row">
								<div class="bfi-col-md-3 ">
								<i class="fa fa-calendar" data-toggle="tooltip" title="<?php _e('Check-out', 'bfi') ?>"> </i> <?php _e('Check-out', 'bfi') ?>
								</div>	
								<div class="bfi-col-md-9 ">
									<div id="bfi-checkout-merchant"></div>
								</div>	
							</div>	
						</div>
					<?php 
						}
					}
					?>
			<?php 
			}
			?>
					<?php if(!empty($merchant->OtherDetails) ){ ?>
							<div class="bfi-row bfi-margin-bottom20">
								<div class="bfi-col-md-3 ">
									<i class="fas fa-info-circle"></i> <?php _e('Other Details', 'bfi') ?>
								</div>	
								<div class="bfi-col-md-9 ">
									<div class="applyshorten"><?php echo BFCHelper::getLanguage($merchant->OtherDetails, $language, null, array('ln2br'=>'ln2br', 'bbcode'=>'bbcode', 'striptags'=>'striptags'))  ?></div>
								</div>	
							</div>	
					<?php } ?>
					<?php if ($merchant->HasResources){?>
					
							<div class="bfi-row bfi-margin-bottom20 bfi-cancellation-policy">
								<div class="bfi-col-md-3 ">
									<i class="fad fa-info-circle"></i> <?php _e('Cancellation Policy', 'bfi') ?>
								</div>	
								<div class="bfi-col-md-9 ">
									
<?php 
if (isset( $resource )  && $resource->ItemTypeId == bfi_ItemType::Experience) {
										echo __('Cancellation and prepayment policies vary according to the dates and the number of people.', 'bfi');
									}else{
										echo __('Cancellation and prepayment policies vary according to accommodation type. Please check what <a rel="#divcalculator" class="bfi-btn-calc">conditions</a> may apply to each option when making your selection.', 'bfi');
									}
?>
								</div>
							</div>
					<?php } ?>
<?php 
if (isset( $resource )  && $resource->ItemTypeId != bfi_ItemType::Experience) {
		$tagsIncluded = array();
		$tagsNotIncluded = array();
		if(!empty( $resource->Tags)){
			foreach ( $resource->Tags as $gr ) {
				$subGroupRefIds = array_filter(array_map(function ($i) { return $i->SubGroupRefId; }, $gr->Tags));
				$gr->subGroupRefIds = $subGroupRefIds;
				if (!empty($gr->subGroupRefIds )) {
					$currTag = $gr->Tags;
					foreach($currTag as $item) {
						if ($item->TagValue=="0") {
							$tagsIncluded[] = $item;
						}
						if ($item->TagValue=="-1") {
							$tagsNotIncluded[] = $item;
						}
					}
				}
			}
		}
	if(!empty( $tagsIncluded) ||  !empty( $resource->Inclusion) ){
	?>
							<div class="bfi-row bfi-margin-bottom20">
								<div class="bfi-col-md-3 ">
									<i class="fad fa-info-circle"></i> <?php _e('What is Included', 'bfi') ?>
								</div>	
								<div class="bfi-col-md-9 applyshorten ">
									<?php if(!empty( $resource->Inclusion)) { ?>
										<div class="bfiexperienceincluded">
										<?php echo BFCHelper::getLanguage($resource->Inclusion, $GLOBALS['bfi_lang'], null, array('striptags'=>'striptags', 'bbcode'=>'bbcode')); ?>
										 
										</div>
									<?php } ?>
									<?php 
									foreach($tagsIncluded as $tg) {
										echo '<div class="bfiexperienceincludedtag"> ' . $tg->Name .'</div>';
									}			
									?>
								</div>
							</div>
					<?php	
	}
	if(!empty( $tagsNotIncluded) || !empty( $resource->Exclusion) ){
	?>
							<div class="bfi-row bfi-margin-bottom20">
								<div class="bfi-col-md-3 ">
									<i class="fad fa-info-circle"></i> <?php _e('What is not Included', 'bfi') ?>
								</div>	
								<div class="bfi-col-md-9 applyshorten ">
									<?php if(!empty( $resource->Exclusion)) { ?>
										<div class="bfiexperienceexclusion">
										<?php echo BFCHelper::getLanguage($resource->Exclusion, $GLOBALS['bfi_lang'], null, array('striptags'=>'striptags', 'bbcode'=>'bbcode')); ?>
										</div>
									<?php } ?>
									
									<?php 
									foreach($tagsNotIncluded as $tg) {
										echo '<div><i class="fa fa-times"></i> ' . $tg->Name .'</div>';
									}			
									?>
								</div>
							</div>
					<?php	
	}
	if(!empty($resource->WhatToKnow)) {
	?>
							<div class="bfi-row bfi-margin-bottom20">
								<div class="bfi-col-md-3 ">
									<i class="fad fa-info-circle"></i> <?php _e('Additional Info', 'bfi') ?>
								</div>	
								<div class="bfi-col-md-9 applyshorten  bfi-additionalinfo-merchant">
									<?php if(!empty($resource->WhatToKnow)) { ?>
										<div><?php echo BFCHelper::getLanguage($resource->WhatToKnow, $GLOBALS['bfi_lang'], null, array('striptags'=>'striptags', 'bbcode'=>'bbcode')); ?></div>
									<?php } ?>
								</div>
							</div>
	<?php } 
	if( !empty($resource->WhatToBring)) {
	?>
							<div class="bfi-row bfi-margin-bottom20">
								<div class="bfi-col-md-3 ">
									<i class="fad fa-info-circle"></i> <?php _e('What to bring', 'bfi') ?>
								</div>	
								<div class="bfi-col-md-9 applyshorten  bfi-whattobring-merchant">
									<?php if(!empty($resource->WhatToBring)) { ?>
										<div><?php echo BFCHelper::getLanguage($resource->WhatToBring, $GLOBALS['bfi_lang'], null, array('striptags'=>'striptags', 'bbcode'=>'bbcode')); ?></div>
									<?php } ?>
								</div>
							</div>
	<?php } 	
}
?>

					</div>	
				</div>	
			</div>	

<!-- END In short -->			


	<div class="bfi-merchant-small-details">
		<div class="bfi-merchant-simple bfi-hideonextra">
<!-- bfi-merchant-details -->			
			
			<div class="bfi-merchant-details bfi-text-center">
					<div class="bfi-vcard-logo"><a <?php echo !empty($merchant->MerchantId)?'':' target="_blank"'; ?> href="<?php echo ($isportal)?$routeMerchant :"/";?>"><img src="<?php echo $merchantLogo?>" /></a></div>	
					<div class="bfi-vcard-name">
						<span class="bfi-item-rating">
<?php 
				$ratingModel = array(
					"ratingModel"=>$merchant
				);
				bfi_get_template("shared/stars_rating.php",$ratingModel);
?>
						</span>
						<a <?php echo !empty($merchant->MerchantId)?'':' target="_blank"'; ?> href="<?php echo ($isportal)?$routeMerchant :"/";?>"><?php echo  $merchant->Name?></a>
					</div>
					<div class="bfi-row bfi-merchant-ref">
						<span class="bfi-street-address"><?php echo strtolower($mrcindirizzo) ?></span>, <span class="postal-code "><?php echo $mrccap ?></span> <span class="locality"><?php echo strtolower($mrccomune) ?></span> <span class="state">, <?php echo strtolower($mrcstate) ?></span>
						<?php if($isportal && (isset($merchant->ViewPublicInfos) && $merchant->ViewPublicInfos)) { ?>
							<?php if (!empty($merchant->MerchantId)){?>
								<span class="tel "><a  href="javascript:void(0);" onclick="bookingfor.getData(bookingfor.getActionUrl(null, null, 'GetPhoneByMerchantId', 'merchantid=<?php echo $merchant->MerchantId?>&simple=1&language=' + bfi_variables.bfi_cultureCode),this,'<?php echo  addslashes($merchant->Name) ?>','PhoneView')"  id="phone<?php echo $merchant->MerchantId?>" class=""><?php _e('Show phone', 'bfi'); ?></a></span>
							<?php } else if (!empty($merchant->Phone)) { ?>
								<span class="tel "><a ><?php echo $merchant->Phone; ?></a></span>
							<?php } ?>
							<?php if ($merchantSiteUrl != ''){?>
							<span class="website"><a target="_blank" href="<?php echo !empty($merchant->MerchantId)?$uriMerchantRedirect:$merchantSiteUrl; ?>" class=""><?php _e('Web site', 'bfi'); ?></a></span>
							<?php } ?>
						<?php } ?>
					</div>
					<?php if($isportal) { ?>
						<ul class="bfi-menu-small">
							<?php if ($merchant->HasResources){?>
								<li><a href="<?php echo $uriMerchantResources; ?>" class="bfi-btn bfi-alternative6"><?php _e('Proposals', 'bfi'); ?></a></li>
							<?php } ?>
							<?php if ($merchant->HasOnSellUnits){?>
								<li><a href="<?php echo $uriMerchantOnsellunits; ?>" class="bfi-btn bfi-alternative6"><?php _e('Real Estate', 'bfi'); ?></a></li>
							<?php } ?>	
							<?php if ($merchant->HasResources){?>
								<?php if ($merchant->HasOffers || true){?>
									<li><a href="<?php echo $uriMerchantOffers; ?>" class="bfi-btn bfi-alternative6"><?php _e('Offers', 'bfi'); ?></a></li>
								<?php } ?>
							<?php } ?>
							<?php if ($merchant->RatingsContext !== 0) {?>
								<li><a href="<?php echo $uriMerchantRatings; ?>" class="bfi-btn bfi-alternative6"><?php _e('Reviews', 'bfi'); ?></a></li>
							<?php } ?>	
							<?php if (!empty($merchant->MerchantId ) && $merchant->MerchantId >0 && !empty($uriMerchantEvents)){ // events?>
								<li><a href="<?php echo $uriMerchantEvents; ?>" class="bfi-btn bfi-alternative6"><?php _e('Events', 'bfi'); ?></a></li>
							<?php } ?>
						</ul>
					<?php } ?>
		<div class="bfi-clearfix bfi-hr-separ-gray"></div>
			</div>	
<!-- END bfi-merchant-details -->			
		</div>	
	</div>


<script type="text/javascript">
<!--
jQuery(function($){

	var bfishortenOption = {
		moreText: "+ <?php _e('Details', 'bfi') ?>",
		lessText: " - <?php _e('Details', 'bfi') ?>",
		showChars: '250'
	};
	jQuery(".applyshorten").shorten(bfishortenOption);
});
<?php 
if(($merchant->AcceptanceCheckIn != "-" || $merchant->AcceptanceCheckOut != "-") ){

	$mrcAcceptanceCheckInStr1="00:00";
	$mrcAcceptanceCheckInStr2="00:00";
	$mrcAcceptanceCheckIn1=0;
	$mrcAcceptanceCheckIn2=24;
	$mrcAcceptanceCheckOutStr1="00:00";
	$mrcAcceptanceCheckOutStr2="00:00";
	$mrcAcceptanceCheckOut1=0;
	$mrcAcceptanceCheckOut2=24;

	$tmpAcceptanceCheckIns = explode('-', $merchant->AcceptanceCheckIn );
	$tmpAcceptanceCheckOuts = explode('-', $merchant->AcceptanceCheckOut );
	
	if(!empty($tmpAcceptanceCheckIns[0])){
		$mrcAcceptanceCheckInStr1 = $tmpAcceptanceCheckIns[0];
		$mrcAcceptanceCheckHours=0;
		$mrcAcceptanceCheckMins=0;
		list($mrcAcceptanceCheckHours,$mrcAcceptanceCheckMins) = explode(':',$tmpAcceptanceCheckIns[0].":0");
		$mrcAcceptanceCheckIn1 = (int)$mrcAcceptanceCheckHours +  round((int)$mrcAcceptanceCheckMins/60,2);
	}
	if(!empty($tmpAcceptanceCheckIns[1])){
		$mrcAcceptanceCheckInStr2 = $tmpAcceptanceCheckIns[1];
		$mrcAcceptanceCheckHours=0;
		$mrcAcceptanceCheckMins=0;
		list($mrcAcceptanceCheckHours,$mrcAcceptanceCheckMins) = explode(':',$tmpAcceptanceCheckIns[1].":0");
		$mrcAcceptanceCheckIn2 = (int)$mrcAcceptanceCheckHours +  round((int)$mrcAcceptanceCheckMins/60,2);
	}
	if(!empty($tmpAcceptanceCheckOuts[0])){
		$mrcAcceptanceCheckOutStr1 = $tmpAcceptanceCheckOuts[0];
		$mrcAcceptanceCheckHours=0;
		$mrcAcceptanceCheckMins=0;
		list($mrcAcceptanceCheckHours,$mrcAcceptanceCheckMins) = explode(':',$tmpAcceptanceCheckOuts[0].":0");
		$mrcAcceptanceCheckOut1 = (int)$mrcAcceptanceCheckHours +  round((int)$mrcAcceptanceCheckMins/60,2);
	}
	if(!empty($tmpAcceptanceCheckOuts[1])){
		$mrcAcceptanceCheckOutStr2 = $tmpAcceptanceCheckOuts[1];
		$mrcAcceptanceCheckHours=0;
		$mrcAcceptanceCheckMins=0;
		list($mrcAcceptanceCheckHours,$mrcAcceptanceCheckMins) = explode(':',$tmpAcceptanceCheckOuts[1].":0");
		$mrcAcceptanceCheckOut2 = (int)$mrcAcceptanceCheckHours + round((int)$mrcAcceptanceCheckMins/60,2);
	}

?>

			
			var checkinmerchant = jQuery("#bfi-checkin-merchant").multibar(
                {
                    min:0,
                    max:24,
					size:"small",
                    multiBarValue:[
                        {
							visibility:"hidden",
							val:<?php echo $mrcAcceptanceCheckIn1 ?>,
                            bgColor:"#CCC"
                        },
                        <?php if(!empty($mrcAcceptanceCheckIn2 )) { ?>
						{
							visibility:"hidden",
                            val:<?php echo $mrcAcceptanceCheckIn2 ?>,
                            bgColor:"#0ab21b"
                        },
						{
							visibility:"hidden",
                            val:24,
                            bgColor:"#CCC"
                        }
                        <?php }else{ ?>
 						{
							visibility:"hidden",
                            val:24,
                            bgColor:"#0ab21b"
                        }
                       <?php } ?>
                    ]
                }
            );
			var checkoutmerchant = jQuery("#bfi-checkout-merchant").multibar(
                {
                    min:0,
                    max:24,
					size:"small",
                    multiBarValue:[
                        {
							visibility:"hidden",
							val:<?php echo $mrcAcceptanceCheckOut1 ?>,
                            bgColor:"#CCC"
                        },
                        <?php if(!empty($mrcAcceptanceCheckOut2 )) { ?>
						{
							visibility:"hidden",
                            val:<?php echo $mrcAcceptanceCheckOut2 ?>,
                            bgColor:"#0ab21b"
                        },
						{
							visibility:"hidden",
                            val:24,
                            bgColor:"#CCC"
                        }
                        <?php }else{ ?>
 						{
							visibility:"hidden",
                            val:24,
                            bgColor:"#0ab21b"
                        }
                       <?php } ?>
                    ]
                }
            );
			var values1 = [
					{
						label:"<?php echo $mrcAcceptanceCheckInStr1 ?>",
						value:<?php echo $mrcAcceptanceCheckIn1 ?>,
						color:"#444444"
						<?php if(($mrcAcceptanceCheckIn2- $mrcAcceptanceCheckIn1)<2) { ?>
								,textalign:"left"
						<?php } ?>
					}
                    <?php if(!empty($mrcAcceptanceCheckIn2 )) { ?>
					,
					{
						label:"<?php echo $mrcAcceptanceCheckInStr2 ?>",
						value:<?php echo $mrcAcceptanceCheckIn2 ?>,
						color:"#FF0000"
						<?php if(($mrcAcceptanceCheckIn2- $mrcAcceptanceCheckIn1)<2) { ?>
								,textalign:"right"
						<?php } ?>
					}
                        <?php } ?>
				];
			var values2 = [
					{
						label:"<?php echo $mrcAcceptanceCheckOutStr1 ?>",
						value:<?php echo $mrcAcceptanceCheckOut1 ?>,
						color:"#444444"
						<?php if(($mrcAcceptanceCheckOut2- $mrcAcceptanceCheckOut1)<2) { ?>
								,textalign:"left"
						<?php } ?>
					}
                    <?php if(!empty($mrcAcceptanceCheckOut2 )) { ?>
					,
					{
						label:"<?php echo $mrcAcceptanceCheckOutStr2 ?>",
						value:<?php echo $mrcAcceptanceCheckOut2 ?>,
						color:"#FF0000"
						<?php if(($mrcAcceptanceCheckOut2- $mrcAcceptanceCheckOut1)<2) { ?>
								,textalign:"right"
						<?php } ?>
					}
                        <?php } ?>
				];
			checkinmerchant.multibar('setValue',values1);
			checkoutmerchant.multibar('setValue',values2);
//            bar2.multibar("setValue",[15,18]);
<?php 
}
?>
//-->
</script>