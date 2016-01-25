<?php /** Template version: 1.0.0

-= 1.0.0 =-
- Initial version

*/ ?>

<div class="cuar-content-block cuar-container-content-block">
	<h3><?php echo $page_subtitle; ?></h3>
	<div class="cuar-container-content-list cuar-item-list">
	<?php 	
			while ( $content_query->have_posts() ) {
				$content_query->the_post(); 
				global $post;
				
				include( $item_template ); 
			}
	?>
	</div>
</div>