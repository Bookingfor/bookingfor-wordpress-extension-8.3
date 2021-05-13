<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$nthumb = 6;
$images = array();

if(!empty($bfiImageData)) {
	$imageData = preg_replace('/\s+/', '', $bfiImageData);
	foreach(explode(',', $imageData) as $image) {
		if (!empty($image)){
			$images[] = array('type' => 'image', 'data' => $image, 'index' => count ($images));
		}
	}
}
$firstVideo = 0;

if(!empty($bfiVideoData) && empty(COM_BOOKINGFORCONNECTOR_ISMOBILE)) {	
	$videoData = preg_replace('/\s+/', '', $bfiVideoData);
	foreach(explode(',', $videoData) as $image) {
		if (!empty($image)){
			if ($firstVideo == 0) {
			    $firstVideo = count ($images);
			}
			$images[] =  array('type' => 'video', 'data' => $image, 'index' =>count ($images));
		}
	}
}
?>

<?php if (count ($images)>0){ 
	$main_img = $images[0];
	$second_img = "";
	$third_img = "";
	if(count ($images)>1){
		$second_img = $images[1];
		if(count ($images)>2){
			$third_img = $images[2];
		}
	}
	$sub_images = array_slice($images, 3, $nthumb);
	if ($firstVideo>($nthumb)) {
		$sub_images = array_slice($sub_images, 0,($nthumb-1));
		$sub_images[] =$images[$firstVideo];
	}

	if (COM_BOOKINGFORCONNECTOR_ISMOBILE) {
		?><div class="bfigalleryslick"><?php 
		foreach($images as $sub_img) {
			$srcImage = "";
			if($sub_img['type'] == 'image') {
				$srcImage = BFCHelper::getImageUrlResized($bfiSourceData, $sub_img['data'],'medium');
			}
			?><div class="bfislide"><img src="<?php echo $srcImage?>" alt=""></div><?php 
		}		
		?></div>
<script type="text/javascript">
<!--
jQuery(document).ready(function() {
jQuery(".bfigalleryslick").css("maxWidth", jQuery( window ).width())
				$bfigalleryslick = jQuery(".bfigalleryslick").slick({
					dots: false,
					draggable: true,
					arrows: true,
					infinite: true,
					slidesToShow: 1,
					slidesToScroll: 1,
						 variableWidth: true,
//    variableHeight: true
				});
});
jQuery('.bfislide').on("click tap", function() {
	$bfigalleryslick.slick('slickGoTo', parseInt($bfigalleryslick.slick('slickCurrentSlide'))+1);
  });
 //-->
</script>
<div class="bfi-clearfix"><br /></div>
		<?php 
	}else {
 ?>
<div class="bfi-launch-fullscreen">
	<a onclick="return false;" data-elementor-open-lightbox="no" class="bfi-launch-fullscreen-img1 <?php echo (count ($images)==1) ?"bfi-launch-fullscreen-img1-alt":""; ?>" href="<?php echo BFCHelper::getImageUrlResized($bfiSourceData, $main_img['data'],'big')?>" style="background-image: url(<?php echo BFCHelper::getImageUrlResized($bfiSourceData, $main_img['data'],'big')?>)" data-index="0" alt="">
		<img src="<?php echo BFCHelper::getImageUrlResized($bfiSourceData, $main_img['data'],'big')?>" data-index="0" alt="">
	</a>
	<?php if(count ($images)>1) { ?>
	<a onclick="return false;" data-elementor-open-lightbox="no" class="bfi-launch-fullscreen-img2 <?php echo (count ($images)==2) ?"bfi-launch-fullscreen-img2-alt":""; ?>" href="<?php echo BFCHelper::getImageUrlResized($bfiSourceData, $second_img['data'],'big')?>" style="background-image: url(<?php echo BFCHelper::getImageUrlResized($bfiSourceData, $second_img['data'],'big')?>)" data-index="1" alt="">
		<img src="<?php echo BFCHelper::getImageUrlResized($bfiSourceData, $second_img['data'],'big')?>"data-index="1" alt="" >
	</a>
	<?php if(count ($images)>2) { ?>
	<a onclick="return false;" data-elementor-open-lightbox="no" class="bfi-launch-fullscreen-img3" href="<?php echo BFCHelper::getImageUrlResized($bfiSourceData, $third_img['data'],'big')?>" style="background-image: url(<?php echo BFCHelper::getImageUrlResized($bfiSourceData, $third_img['data'],'big')?>)"  alt="" data-index="2">
		<img src="<?php echo BFCHelper::getImageUrlResized($bfiSourceData, $third_img['data'],'big')?>" data-index="2" alt="">
	</a>
	<?php } ?>
	<?php } ?>
</div>
<div class="bfi-table-responsive">
<?php 
	$widthtable = "";
	$totalsub_images= count($sub_images);
	if($totalsub_images<3){
		$widthtable = "width:auto;";
	}
	$tdWidth = 100;

	if(!empty($totalsub_images)){
		$tdWidth = 100/$totalsub_images;
	}
	
	$otherImg = count ($images) - 3 - $totalsub_images;

?>	
	<table class="bfi-table bfi-imgsmallgallery" style="<?php echo $widthtable ?>"> 
		<tr>
<?php
	foreach($sub_images as $sub_img) {
		$srcImage = "";
		if($sub_img['type'] == 'image' || $sub_img['type'] == 'planimetry') {
			$srcImage = BFCHelper::getImageUrlResized($bfiSourceData, $sub_img['data'],'small');
		}else{
			$url = $sub_img["data"];
			if (strpos($url,'www.google.com/maps') !== false) {			    
				$srcImage = BFI()->plugin_url() . "/assets/images/street-view.jpg";
			}else{
				parse_str( parse_url( $url, PHP_URL_QUERY ), $arrUrl );
				if (array_key_exists('v',$arrUrl)) {
					$idyoutube = $arrUrl['v'];
					$srcImage = "//img.youtube.com/vi/" . $idyoutube ."/mqdefault.jpg";
				}
			}
		}
?>
			<td style="width:calc(<?php echo $tdWidth ?>% - 10px);">
				<img src="<?php echo $srcImage?>" alt="" class="bfi-showfullscreen"  data-index="<?php echo $sub_img['index'] ?>">
				<?php if($otherImg >0) { ?>
					<div class="bfi-showall" data-index="<?php echo $sub_img['index'] ?>">
						<i class="fa fa-search tour-search"></i><br />
						<?php echo sprintf(__('+ %s photos', 'bfi'),$otherImg ) ?>
					</div>
				<?php } ?>
				
			</td>
<?php } ?>
		</tr>
	</table>
</div>
<script type="text/javascript">
<!--
jQuery(function() {
		jQuery('.bfi-showall, .bfi-launch-fullscreen, .bfi-showfullscreen').magnificPopup({
			mainClass: 'bfi-gallery',
			items: [
			<?php foreach ($images as $image){?>
			<?php if($image['type'] != 'video') { ?>
			  {
				src: '<?php echo BFCHelper::getImageUrlResized($bfiSourceData, $image['data'], '')?>'
			  },
			<?php  } else { ?>
			<?php
			$url='';
		   if(is_array($image['data'])){
			  $url = $image['data']["url"];
		   }else{
			  $url = $image['data'];
		   }
		   parse_str( parse_url( $url, PHP_URL_QUERY ), $arrUrl );
	//	   $idyoutube = $arrUrl['v'];	
			?>
			  {
				src: '<?php echo $url ?>',
				type: 'iframe' // this overrides default type
			  },
			<?php } ?>	
			<?php }?>
			],
			gallery: {
			  enabled: true
			},
			type: 'image' // this is default type,
		});
		jQuery(document).on('click tap', ".bfi-showall, .bfi-launch-fullscreen a, .bfi-showfullscreen", function (e) {
			openAt =  jQuery(this).attr('data-index') || 0;
			//alert(openAt);
			jQuery(this).magnificPopup('goTo', Number(openAt));
		});
	
});
 //-->
</script>


<?php 
	}
	
} elseif ($merchant!= null && $merchant->LogoUrl != '') { ?>
	<img src="<?php echo BFCHelper::getImageUrlResized('merchant', $merchant->LogoUrl , 'resource_mono_full')?>" onerror="this.onerror=null;this.src='<?php echo BFCHelper::getImageUrl('merchant', $merchant->LogoUrl, 'resource_mono_full')?>'" />
<?php } ?>