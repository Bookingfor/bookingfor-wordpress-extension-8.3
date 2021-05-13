<?php
/**
 * The Template for displaying all merchant list
 *
 *
 * @see 	   
 * @author 		Bookingfor
 * @package 	        Bookingfor/Templates
 * @version             2.0.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

	get_header();
	/**
	 * bookingfor_before_main_content hook.
	 */
	do_action( 'bookingfor_before_main_content' );
	?>

		<?php while ( have_posts() ) : the_post(); ?>
			<article id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
				<header class="page-header">
					<h1 class="page-title"><?php the_title(); ?></h1>
				</header>

				<div class="page-content">
					<?php the_content(); ?>
				</div>
				<?php edit_post_link( __( 'Edit', 'bfi' ), '<span class="edit-link">', '</span>' ); ?>
			</article><!-- #post-<?php the_ID(); ?> -->

		<?php endwhile; // end of the loop. ?>
<?php 
	$currCategories = get_post_meta($post->ID, 'eventcategories', true);
	$cityids = get_post_meta($post->ID, 'cityids', true);
	$currURL = esc_url( get_permalink() ); 

    $model = new BookingForConnectorModelEvents;
    $model->populateState();	
	$model->setItemPerPage(COM_BOOKINGFORCONNECTOR_ITEMPERPAGE);

	$filter_order = $model->getOrdering();
	$filter_order_Dir = $model->getDirection();

	$currParam = $model->getParam();
	$currParam['categoryIds'] = !empty($currCategories)?$currCategories:[];
	$currParam['cityids'] = !empty($cityids)?$cityids:[];
	$model->setParam($currParam);

		
	$total = $model->getTotal();
	$items = $model->getItems();
				
	$results = is_array($items) ? $items : array();
	add_action('wp_head', 'bfi_google_analytics_EEc', 10, 1);
	do_action('wp_head', "Merchants List");
	if(!empty($items) && count($items) > 0){

		$page = bfi_get_current_page() ;
		$start = ($page - 1) * COM_BOOKINGFORCONNECTOR_ITEMPERPAGE;
		$pages = ceil($total / COM_BOOKINGFORCONNECTOR_ITEMPERPAGE);
		$listName = "";
		$listNameAnalytics = 0;
        $currSorting = $filter_order ."|".$filter_order_Dir ;

		$paramRef = array(
			"results"=>$results,
			"total"=>$total,
			"items"=>$items,
			"currParam"=>$currParam,
			"filter_order"=>$filter_order,
			"filter_order_Dir"=>$filter_order_Dir,
			);
//		bfi_get_template("search/event_results.php",$paramRef);
		?>		
<div class="bfi-row">
	<div class="bfi-col-md-3">
		<?php 
		$setLat = COM_BOOKINGFORCONNECTOR_GOOGLE_POSX;
		$setLon = COM_BOOKINGFORCONNECTOR_GOOGLE_POSY;
		bfi_get_template("widgets/smallmap.php",array("setLat"=>$setLat,"setLon"=>$setLon));	

		bfi_get_template("widgets/search-filter-events.php",array("$showfilter"=>1,"total"=>$total,"items"=>$items,"pages"=>$pages,"page"=>$page,"listNameAnalytics"=>$listNameAnalytics,"currSorting"=>$currSorting));	
		?>
	</div>
	<div class="bfi-col-md-9">
		<?php 
		bfi_get_template("search/event_results.php",array("results"=>$results,"total"=>$total,"items"=>$items,"pages"=>$pages,"page"=>$page,"listNameAnalytics"=>$listNameAnalytics,"currSorting"=>$currSorting));	
		?>
	</div>
</div>
<?php 
	}


	if(COM_BOOKINGFORCONNECTOR_CRITEOENABLED){
		$merchantsCriteo = isset($items) && !empty($items) ? array_unique(array_map(function($a) { return $a->MerchantId; }, $items)) : array();
		$criteoConfig = BFCHelper::getCriteoConfiguration(1, $merchantsCriteo);
		if(isset($criteoConfig) && isset($criteoConfig->enabled) && $criteoConfig->enabled && count($criteoConfig->merchants) > 0) {
			echo '<script type="text/javascript" src="//static.criteo.net/js/ld/ld.js" async="true"></script>';
				echo '<script type="text/javascript"><!--
				';
				echo ('window.criteo_q = window.criteo_q || []; 
					var deviceTypeCriteo = /iPad/.test(navigator.userAgent) ? "t" : /Mobile|iP(hone|od)|Android|BlackBerry|IEMobile|Silk/.test(navigator.userAgent) ? "m" : "d";
					window.criteo_q.push( 
						{ event: "setAccount", account: '. $criteoConfig->campaignid .'}, 
						{ event: "setSiteType", type: deviceTypeCriteo }, 
						{ event: "setEmail", email: "" }, 
						{ event: "viewList", item: '. json_encode($criteoConfig->merchants) .' }
					);');
				echo "//--></script>";

				
	//			$document->addScript('//static.criteo.net/js/ld/ld.js');
	//			$document->addScriptDeclaration('window.criteo_q = window.criteo_q || []; 
	//			window.criteo_q.push( 
	//				{ event: "setAccount", account: '. $criteoConfig->campaignid .'}, 
	//				{ event: "setSiteType", type: "d" }, 
	//				{ event: "setEmail", email: "" }, 
	//				{ event: "viewList", item: '. json_encode($criteoConfig->merchants) .' }
	//			);');
			}
		
	}
	

?>
		<?php
		if( get_option('permalink_structure') ) {
			$format = 'page/%#%/';
		} else {
			$format = '?paged=%#%';
		}
			$url = esc_url( get_permalink() ); 
		  $pagination_args = array(
			'base'            => $url. '%_%',
			'format'          => $format, //'?page=%#%',
			'total'           => $pages,
			'current'         => $page,
			'show_all'        => false,
			'end_size'        => 5,
			'mid_size'        => 2,
			'prev_next'       => true,
			'prev_text'       => __('&laquo;'),
			'next_text'       => __('&raquo;'),
			'type'            => 'plain',
			'add_args'        => false,
			'add_fragment'    => ''
		  );



		global $bfi_query_arg;
		$bfi_query_arg = array();
		add_filter( 'get_pagenum_link', 'bfi_remove_date_get_pagenum_link',1 );
		if (!function_exists('bfi_remove_date_get_pagenum_link')) {
			function bfi_remove_date_get_pagenum_link( $url ) {
				global $bfi_query_arg;
				$checkinFromUrl = filter_input( INPUT_GET, 'checkin');
				if(!empty($checkinFromUrl)){
					$bfi_query_arg['checkin'] = rawurlencode($checkinFromUrl);
					$url = remove_query_arg( 'checkin', $url );
				}
				$checkoutFromUrl = filter_input( INPUT_GET, 'checkout');
				if(!empty($checkoutFromUrl)){
					$bfi_query_arg['checkout'] = rawurlencode($checkoutFromUrl);
					$url = remove_query_arg( 'checkout', $url );
				}
				
				return  $url;
			}	    
		}

		add_filter( 'paginate_links', function( $link )
		{
			global $bfi_query_arg;
			$filter_order = filter_input( INPUT_POST, 'filter_order');
			if(!empty($filter_order)){
				$link = remove_query_arg( 'filter_order', $link );
				$link = add_query_arg('filterorder' , $filter_order , $link);
			}else{
				$filter_order = filter_input( INPUT_GET, 'filter_order');
				if(!empty($filter_order)){
					$link = remove_query_arg( 'filter_order', $link );
					$link = add_query_arg('filterorder' , $filter_order , $link);
				}
			}
			$filter_order_dir = filter_input( INPUT_POST, 'filter_order_Dir');
			if(!empty($filter_order_dir)){
				$link = remove_query_arg( 'filter_order_Dir', $link );
				$link = add_query_arg('filterorderdir' , $filter_order_dir , $link);
			}else{
				$filter_order_dir = filter_input( INPUT_GET, 'filter_order_Dir');
				if(!empty($filter_order_dir)){
					$link = remove_query_arg( 'filter_order_Dir', $link );
					$link = add_query_arg('filterorderdir' , $filter_order_dir , $link);
				}
			}

			if(!empty($bfi_query_arg)){
				$link =  add_query_arg($bfi_query_arg , $link);
			}

			$link = filter_input( INPUT_GET, 'newsearch' ) ? remove_query_arg( 'newsearch', $link ) : $link;

			$link = str_replace('filterorder=',"filter_order=",$link);
			$link = str_replace('filterorderdir=',"filter_order_Dir=",$link);

			return $link;
		} );

		  $paginate_links = paginate_links($pagination_args);
			if ($paginate_links) {
			  echo "<nav class='bfi-pagination'>";
		//      echo "<span class='page-numbers page-num'>Page " . $page . " of " . $numpages . "</span> ";
			  echo "<span class='page-numbers page-num'>".__('Page', 'bfi')." </span> ";
			  print $paginate_links;
			  echo "</nav>";
			}
			 ?>
	<?php
		/**
		 * bookingfor_after_main_content hook.
		 *
		 * @hooked bookingfor_output_content_wrapper_end - 10 (outputs closing divs for the content)
		 */
		do_action( 'bookingfor_after_main_content' );
	?>

	<?php
		/**
		 * bookingfor_sidebar hook.
		 *
		 * @hooked bookingfor_get_sidebar - 10
		 */
		do_action( 'bookingfor_sidebar' );
	?>

<?php get_footer( ); ?>
