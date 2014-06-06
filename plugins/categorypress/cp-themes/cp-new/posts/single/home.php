l<?php get_header( 'categorypress' ); ?>

	<div class="well">
		<ol class="breadcrumb">
			<?php 
			$crumbs = cp_get_category_crumbs();
			
			foreach( $crumbs as $crumb ) : ?>
		    <li><a href="<?php echo $crumb->link;?>"><?php echo $crumb->name;?></a></li>
		    <?php 
			endforeach;?>
		</ol>
	</div>
	
	<div class="well">
	
	</div>
<?php get_footer( 'categorypress' ); ?>