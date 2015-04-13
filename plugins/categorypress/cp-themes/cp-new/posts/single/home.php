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
				$category_id = cp_posts_get_parent_category_id( $cur_post_id, 1 );
				$cur_category_slug = cp_categories_get_slug( $category_id );
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
										<?php $post_prev = cp_posts_get_prev_post( $cur_post_id );?>
                                        <i class="icon-black icon-prev"></i>
										
										<?php if ( is_object( $post_prev ) ) : ?>
										<a href="<?php echo cp_posts_get_permalink( 'all', $cur_category_slug, $post_prev->id ); ?>" title="<?php echo $post_prev->name;?>"><?php echo mb_strimwidth( $post_prev->name, 0, 30, '...', 'UTF-8' ); ?></a>
										<?php else : ?>
										没有了
										<?php endif;?>
										
									</p>
                                    <p class="fr">
										<?php $post_next = cp_posts_get_next_post( $cur_post_id );?>
										
										<?php if ( is_object( $post_next ) ) : ?>
										<a href="<?php echo cp_posts_get_permalink( 'all', $cur_category_slug, $post_next->id ); ?>" title="<?php echo $post_next->name;?>"><?php echo mb_strimwidth( $post_next->name, 0, 30, '...', 'UTF-8' ); ?></a>
										<?php else : ?>
										没有了
										<?php endif;?>
										
										<i class="icon-black icon-next"></i>
									</p>
                                </div>
							</div>
							<div class="con-right">
								<h1><?php echo $post->name;?></h1>
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
											$url_type = cp_posts_get_postmeta( $cur_post_id, 'url_type_' . $idx );
											$option_name = 'cp-meta-'.$url_type.'-name';
										?>
										 <a href="<?php echo $url;?>" target="_blank" class="button btn-down" title="<?php echo $url_name;?>">
											<i class="icon-down icon-white"></i><i class="icon-white icon-down-transiton"></i>
											<?php echo cp_get_option( $option_name, $url_type ); ?>
											下载<?php echo $url_name;?> (<?php echo $idx + 1;?>)
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
							<h2>您可能还对以下内容感兴趣</h2>
						</div>
						<div class="page-article">
						<div class="page">
							<?php
							// get data
							$datas = cp_posts_get_posts( array( 
										'category_id'    => $category_id,
										'area_id'        => 0,
										'price_from'     => 0,
										'price_to'       => 0,
										'status'         => 0,
										'page'           => 1,
										'per_page'       => 5
										) );
							$posts = $datas['posts'];?>
							<ul class="thumbItem small clearfix">
							<?php
							foreach ( $posts as $post ) : ?>
								<li><a href="<?php echo cp_posts_get_permalink( 'all', $cur_category_slug, $post->id ); ?>" title="<?php echo $post->name;?>">
									<img src="<?php echo cp_get_thumbnail_url( $post->thumb, array( 234, 137 ) ); ?>" width="234" height="137" alt="<?php echo $post->name;?>"/></a>
									<p align="center"><a href="<?php echo cp_posts_get_permalink( 'all', $cur_category_slug, $post->id ); ?>" title="<?php echo $post->name;?>"><?php echo $post->name;?></a></p>
								</li>
							<?php
							endforeach;?>
							</ul>
						</div>
					</div>
				</div>
<?php get_footer( 'categorypress' ); ?>