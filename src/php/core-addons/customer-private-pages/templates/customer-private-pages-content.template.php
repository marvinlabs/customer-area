<?php /** Template version: 1.1.0 

-= 1.1.0 =-
- Updated markup

-= 1.0.0 =-
- Initial version

*/ ?>

<div class="cuar-content-block cuar-private-pages-block">
	<h3><?php echo $page_subtitle; ?></h3>
	<div class="cuar-private-page-list cuar-item-list">
	<?php 	
			while ( $content_query->have_posts() ) {
				$content_query->the_post(); 
				global $post;
				
				include( $item_template ); 
			}
	?>
	</div>
</div>