<?php /*

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta http-equiv="content-type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php bloginfo( 'charset' ); ?>" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="author" content="">
<link rel="shortcut icon" href="../../docs-assets/ico/favicon.png">

<title><?php wp_title( '|', true, 'right' ); bloginfo( 'name' ); ?></title>

<!-- Bootstrap core CSS -->
<link href="/wp-content/plugins/categorypress/cp-themes/cp-default/css/main.css" rel="stylesheet">

<!-- Just for debugging purposes. Don't actually copy this line! -->
<!--[if lt IE 9]><script src="../../docs-assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
  <script src="http://cdn.bootcss.com/html5shiv/3.7.0/html5shiv.min.js"></script>
  <script src="http://cdn.bootcss.com/respond.js/1.3.0/respond.min.js"></script>
<![endif]-->
<?php cp_head(); ?>
<?php wp_head(); ?>
</head>

<body id="cp-default">

	<div class="navbar navbar-fixed-top navbar-inverse" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">海口</a>
		  [<a class="navbar-brand" href="#">切换城市</a>]
        </div>
        <div class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
			  <li class="active"><a href="#">招聘</a></li>
			  <li><a href="#">租房</a></li>
			  <li><a href="#">车辆</a></li>
			  <li><a href="#">二手</a></li>
			</ul><!--/.navbar-nav-->
			
		  <form class="navbar-form navbar-left" role="search">
			  <div class="form-group">
				<input type="text" class="form-control" placeholder="<?php _e( 'Search Item' , 'categorypress' ); ?>">
			  </div>
			  <button type="submit" class="btn btn-default"><?php _e( 'Search' , 'categorypress' ); ?></button>
			</form><!--/.navbar-form-->
		  <ul class="nav navbar-nav navbar-right">
			  <li><a class="btn btn-danger" href="#"><?php _e( 'Join' , 'categorypress' ); ?></a></li>
			  <li><a href="#"><?php _e( 'Sign In' , 'categorypress' ); ?></a></li>
			  <li class="dropdown">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown">kuibobo <b class="caret"></b></a>
				<ul class="dropdown-menu">
				  <li><a href="#"><?php _e( 'My Item' , 'categorypress' ); ?></a></li>
				  <li><a href="#"><?php _e( 'My Bookmark' , 'categorypress' ); ?></a></li>
				</ul>
			  </li>
			  <li><a href="#"><?php _e( 'Exit' , 'categorypress' ); ?></a></li>
			</ul><!--/.navbar-right-->
			

		  <a class="btn btn-danger" href="#"><?php _e( 'Post a Item' , 'categorypress' ); ?></a>

			
        </div><!-- /.nav-collapse -->
      </div><!-- /.container -->
    </div><!-- /.navbar -->
	
	<div class="container">
	
	*/ ?>
	
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta http-equiv="content-type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php bloginfo( 'charset' ); ?>" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="author" content="">
<title><?php wp_title( '|', true, 'right' ); bloginfo( 'name' ); ?></title>
<link href="/wp-content/plugins/categorypress/cp-themes/cp-default/css/style.css" rel="stylesheet">
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
			