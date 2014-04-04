<?php
$datas = cp_posts_get_posts();
$posts = $datas['posts'];

foreach ( $posts as $post ) : 
	$img_count = cp_posts_get_postmeta( $post->id, 'img_count' );
?>
<div class="row">
  <div class="col-xs-2 col-sm-2">
    <a class="thumbnail"><img src="<?php echo cp_get_thumbnail_url( $post->thumb, array( 105, 75 ) ); ?>" /></a>
  </div><!--/span-->
  <div class="col-xs-8 col-sm-8">
    <p><a><?php echo $post->name;?></a><?php if ( !empty( $img_count ) ) :?><span>[<?php echo $img_count; ?>图]</span><?php endif;?></p>
    <p><small><?php echo mb_strimwidth( $post->description, 0, 90, '...', 'UTF-8' ); ?></small></p>
    <p><span><?php echo human_time_diff( $post->date_created, current_time( 'timestamp' ) ); ?></span><span>美兰x</span></p>
  </div><!--/span-->
  <div class="col-xs-1 col-sm-1">
    <span>2600元</span>
  </div>
</div><!--/row-->
<?php
endforeach;
?>