<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// If CategoryPress is not activated, switch back to the default WP theme and bail out
if ( ! function_exists( 'cp_version' ) ) {
	switch_theme( WP_DEFAULT_THEME, WP_DEFAULT_THEME );
	return;
}

if ( !function_exists( 'cp_dtheme_show_pagination' ) ) :
function cp_dtheme_show_pagination( $url_pattern, $page_index, $page_size, $data_total ) {
	$total_page = (int)($data_total / $page_size);
	if ( $data_total %  $page_size != 0 )
		$total_page ++;
		
	$start_index = $page_index - 4;
	if ( $start_index <= 0 ) 
		$start_index = 1;
		
	$end_index = $start_index + 9;
	if ( $end_index > $total_page ) {
		$end_index = $total_page;
		
		$start_index = $end_index - 9;
	}
	
	if ( $start_index <= 0 ) 
		$start_index = 1;
			
	$pre_index = $page_index - 1;
	$next_index = $page_index + 1;
	
	$pages = array();
	if ( $total_page != 0 ) {
		for ( $page = $start_index; $page <= $end_index; $page++ ) 
			$pages[] = array( 
					'page_num'     => $page,
					'page_url'     => sprintf( __( $url_pattern ) , $page ), 
					'is_current'   => $page_index == $page );
	}
	?>
	<div class="pull-right"><ul class="pagination">
	<?php 
	if ( 1 != $start_index ) :
	?>
	<li><a href="<?php echo sprintf( __( $url_pattern ) , $pre_index ); ?>">&laquo;</a></li>
	<?php 
	endif;
	
	foreach ( $pages as $page ) : if ( $page['is_current'] ) :
	?>
	<li class="active"><a href="#"><?php echo $page['page_num']; ?></a></li>
	<?php else : ?>
	<li><a href="<?php echo $page['page_url']; ?>"><?php echo $page['page_num']; ?></a></li>
	<?php
	endif; endforeach;
	
	if ( $end_index != $total_page ) :
	?>
	<li><a href="<?php echo sprintf( __( $url_pattern ) , $next_index ); ?>">&raquo;</a></li>
	<?php 
	endif;
	?>
	</ul><!--/ui.pagination--></div><!--/div.pagination-->
	<?php 
}
endif;