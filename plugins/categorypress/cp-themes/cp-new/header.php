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
			<div class="logo"><a href="http://www.cssmoban.com/">模板之家</a></div>
			<nav class="menu">
				<ul>
					<li><a href="http://www.cssmoban.com/" target="_parent">首页</a></li>
					<li><a href="http://www.cssmoban.com/cssthemes/" class="active" target="_parent">网站模板</a></li>
					<li><a href="http://www.cssmoban.com/wpthemes/" class="active" target="_parent">WP模板</a></li>
					<li><a href="http://www.cssmoban.com/tags.asp" target="_parent">模板标签</a></li>
					<li><a href="http://jianzhan.cssmoban.com/" target="_parent">建站超市</a></li>
					<li><a href="http://www.cssmoban.com/showcase/" target="_parent">酷站欣赏</a></li>
					<li><a href="http://www.cssmoban.com/wpthemes/wphost.shtml" target="_parent">WP主机</a></li>
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
				