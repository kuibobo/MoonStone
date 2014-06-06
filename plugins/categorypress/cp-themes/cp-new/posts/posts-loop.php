<header class="page-header">
	<h2 class="hd-title">WORDPRESS模板下载</h2>
	<strong>模板之家为你收集国外海量精美Wordpress主题，Wordpress模板及教程。让您利用WordPress Theme瞬间建立强大的网站。Wordpress主题模板下载首选cssMoban.com。</strong>
</header>
<?php
$cur_category_slug = cp_current_category_slug();
$cur_area_slug      = cp_current_area_slug();
if ( empty( $cur_area_slug ) )
	$cur_area_slug = cp_current_city_slug();
	
$cur_price_slug     = cp_current_price_slug();


$cur_category_id = cp_categories_get_id( $cur_category_slug );
$cur_area_id = cp_categories_get_id( $cur_area_slug );

$price_from = false;
$price_to = false;
if ( !empty( $cur_price_slug ) ) {
	$price_from = CP_Post::$PRICES[$cur_price_slug][0];
	$price_to   = CP_Post::$PRICES[$cur_price_slug][1];
}

// get page index
$path = esc_url( $_SERVER['REQUEST_URI'] );
$chunks = explode( '/', $path );
$chunks = array_filter( $chunks );
$price_slug = cp_current_price_slug();

if ( !empty( $price_slug ) ) {
	$page_index = substr( $chunks[count( $chunks )], 1, strlen( $chunks[count( $chunks )] ) - 3 );
	
	$chunks[count( $chunks )] = 'o%s' . $price_slug . '/';
} else {
	$page_index = substr( $chunks[count( $chunks )], 1, strlen( $chunks[count( $chunks )] ) );
	
	if ( is_numeric( $page_index ) ) {
		$chunks[count( $chunks )] = 'o%s/';
	} else {
		$chunks[] = 'o%s/';
		$page_index = 1;
	}
}

if ( empty( $page_index ) )
	$page_index = 1;

$url_pattern = cp_get_root_domain() . '/' . join( '/', $chunks);

// get data
$datas = cp_posts_get_posts( array( 
			'category_id'    => $cur_category_id,
			'area_id'        => $cur_area_id,
			'price_from'     => $price_from,
			'price_to'       => $price_to,
			'status'         => 0,
			'page'           => $page_index
		) );
$posts = $datas['posts'];?>

<ul class="thumbItem large clearfix">
<?php
foreach ( $posts as $post ) : 
?>
<li>
<div class="row">
  <div class="col-xs-2 col-sm-2">
    <a class="thumbnail" href="<?php echo cp_posts_get_permalink( $cur_area_slug, $cur_category_slug, $post->id ); ?>"><img src="<?php echo cp_get_thumbnail_url( $post->thumb, array( 105, 75 ) ); ?>" /></a>
  </div><!--/span-->
  <div class="col-xs-8 col-sm-8">
    <p><a href="<?php echo cp_posts_get_permalink( $cur_area_slug, $cur_category_slug, $post->id ); ?>"><?php echo $post->name;?></a><?php if ( !empty( $post->img_count ) ) :?><span>[<?php echo $post->img_count; ?>图]</span><?php endif;?></p>
    <p><small><?php echo mb_strimwidth( $post->excerpt, 0, 80, '...', 'UTF-8' ); ?></small></p>
    <p><span><?php echo cp_human_time_diff( strtotime( $post->date_created ), current_time( 'timestamp' ) ); ?></span><span>美兰</span></p>
  </div><!--/span-->
  <div class="col-xs-1 col-sm-1">
    <span>2600元</span>
  </div>
</div><!--/row-->
</li>
<?php
endforeach;?>
</ul>
<?php cp_dtheme_show_pagination( $url_pattern, $page_index, 40, $datas['total'] ); ?>