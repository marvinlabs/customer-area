<?php 
	$heading = '';
	if ( !empty( $breadcrumb ) ) $heading .= $breadcrumb . $breadcrumb_sep;
	$heading .= $category->name;
	
	// Print heading
	if ( $files_query->have_posts() || !$hide_empty_categories ) {
?>
	<h4 class="accordion-section-title cuar-private-file-section cuar-section" title="<?php _e( 'Clic to show the files in this category', 'cuar' );?>"><?php echo $heading; ?></h4>
	<div class="accordion-section-content">
		<table class="cuar-private-file-list"><tbody>
<?php
		if ( $files_query->have_posts() ) {		
			// Print posts
			while ( $files_query->have_posts() ) { 
				$files_query->the_post(); 
				global $post; 
?>
			<?php	include( $item_template ); ?>
<?php 		
			}
		} else {
?>
			<tr class="cuar-private-file"><td><?php _e( 'No files in this category', 'cuar' ); ?></td></tr>
<?php 		
		}
?>
		</tbody></table>
	</div>
<?php	
	}
	