<?php
$catetory_id = cp_categories_get_current_id();
$datas = cp_posts_get_posts( array( 
		'parent'    => $catetory_id
		) );
$posts = $datas['posts'];

foreach ( $posts as $post ) : 
?>
<div class="row">
  <div class="col-xs-2 col-sm-2">
    <a class="thumbnail"><img src="<?php echo cp_get_thumbnail_url( $post->thumb, array( 105, 75 ) ); ?>" /></a>
  </div><!--/span-->
  <div class="col-xs-8 col-sm-8">
    <p><a><?php echo $post->name;?></a><?php if ( !empty( $post->img_count ) ) :?><span>[<?php echo $post->img_count; ?>图]</span><?php endif;?></p>
    <p><small><?php echo mb_strimwidth( $post->excerpt, 0, 80, '...', 'UTF-8' ); ?></small></p>
    <p><span><?php echo cp_human_time_diff( strtotime( $post->date_created ), current_time( 'timestamp' ) ); ?></span><span>美兰</span></p>
  </div><!--/span-->
  <div class="col-xs-1 col-sm-1">
    <span>2600元</span>
  </div>
</div><!--/row-->
<?php
endforeach;
?>