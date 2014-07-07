<!DOCTYPE HTML>
<html <?php language_attributes(); ?>>
<head>
<meta http-equiv="content-type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php bloginfo( 'charset' ); ?>" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php cp_head(); ?>
<?php wp_head(); ?>
<title><?php wp_title( '|', true, 'right' ); bloginfo( 'name' ); ?></title>
<link href="/wp-content/plugins/categorypress/cp-themes/cp-new/css/style.css" rel="stylesheet">
</head>
<body>
<div id="main" class="wide-bg-dark clearfix">
	<section class="page-top">
		<div class="container col-media-main">
			<div class="logo"><a href="http://www.cssmoban.com/"><?php bloginfo( 'name' );?></a></div>
			<nav class="menu">
				<ul>
				<?php
				$category_slug = cp_current_category_slug();
				$city_slug = cp_current_city_slug();
				$datas = cp_categories_get_categories( array( 'parent_id' => 52 ) );
				$categories = $datas['categories'];
				foreach ( $categories as $category ) : ?>
					<li><a href="<?php echo cp_categories_get_permalink( $category->slug, $category_slug, $city_slug ) ;?>" target="_parent"><?php echo $category->name;?></a></li>
				<?php
				endforeach;?>
				</ul>
			</nav>
			<div class="search">
				
			</div>
		</div>
	</section>
	<div class="wide-bg-dark">
		<div class="wide-main wp-media-main clearfix">
			<div class="page-position">
				当前位置：
				<?php 
				$crumbs = cp_get_category_crumbs();
				
				foreach( $crumbs as $crumb ) : ?>
				<a href="<?php echo $crumb->link;?>"><?php echo $crumb->name;?></a> &gt;&gt; 
				<?php 
				endforeach;?>
			</div>
			<div class="page">
				