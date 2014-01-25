<div class="cuar-header">
	
	<?php do_action( 'cuar_before_customer_page_actions' ); ?>
	<?php $this->print_main_menu(); ?>
	
	<?php do_action( 'cuar_before_customer_page_title' ); ?>

	<h2 class="cuar_page_title"><?php echo $this->title; ?></h2>
		
	<?php do_action( 'cuar_after_customer_page_title' ); ?>
	
<?php if ( isset( $this->top_level_action ) && isset( $this->top_level_action['children'] ) && !empty( $this->top_level_action['children'] ) ) : ?>
	<p class="cuar-action-children-container">
<?php 	$base_url = $this->get_customer_page_url(); ?>
<?php 	foreach ( $this->top_level_action['children'] as $action ) :
			$li_class = '';
			if ( isset( $this->top_level_action['slug'] ) && $this->top_level_action['slug']==$action['slug'] ) $li_class .= 'current';
			
			$href = isset( $action["url"] ) ? $action["url"] : $base_url . '?action=' . $action["slug"];
			$label = esc_html( $action["label"] );
			$hint = esc_attr( $action["hint"] );
?>
		<a href="<?php echo esc_attr( $href ); ?>" title="<?php echo $hint; ?>" class="cuar-action-child cuar_small_button <?php echo $li_class; ?>">
			<?php echo $label; ?> &raquo;
		</a>
<?php 	endforeach; ?>
	</p>
<?php endif; ?>

<?php do_action( 'cuar_after_customer_page_action_children' ); ?>
	
</div>

<?php do_action( 'cuar_after_customer_page_header' ); ?>