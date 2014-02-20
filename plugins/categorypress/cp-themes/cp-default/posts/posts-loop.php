<?php
$datas = cp_posts_get_posts();
$posts = $datas['posts'];

foreach ( $posts as $post ) : ?>
<div class="row">
  <div class="col-xs-2 col-sm-2">
    <a class="thumbnail"><img src="<?php echo cp_get_thumbnail_url( '', array( 105, 75 ) ); ?>" /></a>
  </div><!--/span-->
  <div class="col-xs-8 col-sm-8">
    <p><a><?php echo $post->name;?></a><span>[2图]</span></p>
    <p><smaal><?php echo mb_strimwidth( $post->description, 0, 90, '...', 'UTF-8' ); ?></smaal></p>
    <p><span>1月21日</span><span>美兰</span></p>
  </div><!--/span-->
  <div class="col-xs-1 col-sm-1">
    <span>2600元</span>
  </div>
</div><!--/row-->
<?php
endforeach;
?>