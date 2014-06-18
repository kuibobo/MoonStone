				<?php
				$cur_category_slug = cp_current_category_slug();
				$cur_category_id = cp_categories_get_id( $cur_category_slug );

				// get page index
				$path = esc_url( $_SERVER['REQUEST_URI'] );
				$chunks = explode( '/', $path );
				$chunks = array_filter( $chunks );
				
				if ( count( $chunks ) == 4 )
					$page_index = $chunks[4];
				else
					$page_index = 1;

				$url_pattern = cp_get_root_domain() . '/' . join( '/', $chunks);

				// get data
				$datas = cp_posts_get_posts( array( 
							'category_id'    => $cur_category_id,
							'area_id'        => 0,
							'price_from'     => 0,
							'price_to'       => 0,
							'status'         => 0,
							'page'           => $page_index
						) );
				$posts = $datas['posts'];?>

				<ul class="thumbItem large clearfix">
				<?php
				foreach ( $posts as $post ) : ?>
					<li>
						<a class="thumbnail" href="<?php echo cp_posts_get_permalink( 'all', $cur_category_slug, $post->id ); ?>"><img src="<?php echo cp_get_thumbnail_url( $post->thumb, array( 105, 75 ) ); ?>" /></a>
						<p><a href="<?php echo cp_posts_get_permalink( 'all', $cur_category_slug, $post->id ); ?>"><?php echo $post->name;?></a><?php if ( !empty( $post->img_count ) ) :?><span>[<?php echo $post->img_count; ?>图]</span><?php endif;?></p>
						<p class="excerpt"><?php echo $post->excerpt; ?></p>
						<p><span><?php echo cp_human_time_diff( strtotime( $post->date_created ), current_time( 'timestamp' ) ); ?></span></p>
					</li>
				<?php
				endforeach;?>
				</ul>
				<?php cp_dtheme_show_pagination( $url_pattern, $page_index, 40, $datas['total'] ); ?>