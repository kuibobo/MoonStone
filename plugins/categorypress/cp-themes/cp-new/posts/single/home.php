<?php

/**
 * CategoryPress - Posts Directory
 *
 * @package CategoryPress
 * @subpackage cp-default
 */
?>
<?php get_header( 'categorypress' ); ?>

<?php

$cur_post_id = cp_current_post_id();
$post = cp_posts_get_post( array( 'post_id' => $cur_post_id ) );
?>
				<div class="content-wrap">
					<div class="content-inner">
						<div class="detail-ads"></div>
						<div class="content clearfix">
							<div class="con-left">
								<div class="large-Imgs">
                                    <img src="<?php echo $post->thumb;?>" alt="<?php echo $post->name;?>">
                                </div>
                                <div class="pre-bd">
                                    <p class="fl">
                                        <i class="icon-black icon-prev"></i>
										<a href="/cssthemes/5234.shtml" title="<?php echo $post->name;?>"><?php echo $post->name;?></a></p>
                                    <p class="fr">没有了<i class="icon-black icon-next"></i></p>
                                </div>
							</div>
							<div class="con-right">
								<h1><?php echo $post->name;?></h1>
                                    <div class="hits">
                                        <i class="icon-hit"></i>views<i class="icon-fav"></i>
										<span id="bdshare" style="float: none;" class="bdshare_b">收藏</span>
										<i class="icon-like"></i><span id="s5238"></span>
										<b id="d5238"><a href="javascript:digg(1,5238,'/');">很漂亮</a></b>
										<i class="icon-unlike"></i><span id="c5238"></span>
										<b id="d5238"><a href="javascript:cai(1,5238,'/');">很差劲</a></b></div>
									<div class="desc">
										<p>
										<?php
										$post_desc = cp_posts_get_postmeta( $cur_post_id, 'description' );
										echo $post_desc;
										?>
										</p>
									</div>
                                    <div class="down">
                                       <?php 
										$url_size = cp_posts_get_postmeta( $cur_post_id, 'url_size' );
										for( $idx = 0; $idx < $url_size; $idx++ ) :
											$url = cp_posts_get_postmeta( $cur_post_id, 'url_' . $idx );
											$url_name = cp_posts_get_postmeta( $cur_post_id, 'url_name_' . $idx );
										?>
										 <a href="<?php echo $url;?>" target="_blank" class="button btn-down" title="<?php echo $url_name;?>">
											<i class="icon-down icon-white"></i><i class="icon-white icon-down-transiton"></i>下载<?php echo $url_name;?>
										</a>
										<?php
										endfor;?>
                                    </div>
							</div>
						</div>
						<div class="detail-ads2"></div>
					</div>
					
					<div class="wide-main clearfix">
						<div class="page-header">
							<h2>你可能喜欢的模板</h2>
						</div>
						<div class="page-article">
						<div class="page">
							<ul class="thumbItem small clearfix">
								<li><a href="/cssthemes/4235.shtml" target="_blank" alt="蓝色圆角导航简洁的商务博客模板">
									<img src="/UploadFiles/2013/2/2013032109234195255.jpg" width="234" height="137" /></a>
									<palign="center"><a href="/cssthemes/4235.shtml" title="蓝色圆角导航简洁的商务博客模板">蓝色圆角导航简洁的商务博客模板</a></p>
								</li>
							</ul>
						</div>
					</div>
				</div>
<?php get_footer( 'categorypress' ); ?>