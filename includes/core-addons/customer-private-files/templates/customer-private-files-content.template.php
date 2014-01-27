<h3><?php echo $page_subtitle; ?></h3>
<table class="cuar-private-file-list cuar-item-list">
	<tbody>
<?php 	
		while ( $files_query->have_posts() ) {
			$files_query->the_post(); 
			global $post;
			
			include( $item_template ); 
		}
?>
	</tbody>
</table>
