<?php 
	$heading = '';
	if ( !empty( $breadcrumb ) ) $heading .= $breadcrumb . $breadcrumb_sep;
	$heading .= $category->name;
	
	// Print heading
	if ( $pages_query->have_posts() || !$hide_empty_categories ) {
?>
	<h4 class="accordion-section-title cuar-private-page-section cuar-section" title="<?php _e( 'Clic to show the pages in this category', 'cuar' );?>"><?php echo $heading; ?></h4>
	<div class="accordion-section-content">
		<table class="cuar-private-page-list"><tbody>
<?php
		if ( $pages_query->have_posts() ) {		
			// Print posts
			while ( $pages_query->have_posts() ) { 
				$pages_query->the_post(); 
				global $post; 
?>
			<?php	include( $item_template ); ?>
<?php 		
			}
		} else {
?>
			<tr class="cuar-private-page"><td><?php _e( 'No pages in this category', 'cuar' ); ?></td></tr>
<?php 		
		}
?>
		</tbody></table>
	</div>
<?php	
	}
	