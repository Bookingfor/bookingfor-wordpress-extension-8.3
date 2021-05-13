<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if (isset($ratingModel->Rating)) {
    
$hasSuperior = !empty($ratingModel->RatingSubValue);
$rating = (int)$ratingModel->Rating;
if ($rating>9 )
{
	$rating = $rating/10;
	$hasSuperior = ($ratingModel->Rating%10)>0;
} 

// overraid stars
$currRating ='<i class="fa fa-star"></i>';
if(!empty( $ratingModel->RatingImgUrl )){
	$currRating ='<img src="'. BFCHelper::getImageUrlResized('rating',$ratingModel->RatingImgUrl , 'img24') .'" class="bfi-ratingimgurl" />';
} else {
	if(!empty( $ratingModel->RatingIconSrc ) && !empty( $ratingModel->RatingIconType)){
		$currType = explode(';',$ratingModel->RatingIconType);
		switch ($currType[0]) {
			case "fontawesome5":
				$currRating ='<i class="fas ' . $ratingModel->RatingIconSrc .'"></i>';
				break;
			case "fontawesome4":
			default:
				$currRating ='<i class="fa ' . $ratingModel->RatingIconSrc .'"></i>';
		}
	}
}
for($i = 0; $i < $rating; $i++) { echo $currRating; }
if ($hasSuperior) { ?>&nbsp;S<?php }
}
?>