<?php /** Template version: 1.0.0 */ ?>

<ul class="pagination pagination-sm">
<?php 
foreach ( $page_links as $num => $page_args ) :
	if ( $page_args['is_current'] ) : 
?>
		<li class="active"><span><?php echo $num; ?> <span class="sr-only"><?php _e( '(current)', 'cuar' ); ?></span></span></li>
<?php 	
	else: ?>
		<li><a href="<?php echo esc_attr( $page_args['link'] ); ?>" title="<?php printf( esc_attr__( 'Page %1$s', 'cuar' ), $num ); ?>"><?php echo $num; ?></a></li>
<?php 	
	endif; ?>
<?php 
endforeach; ?>
</ul>