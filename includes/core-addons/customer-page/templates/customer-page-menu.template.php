<?php
if (!empty($this->actions)) :
	$is_last = count($this->actions);
	$separator = apply_filters( 'cuar_customer_page_actions_separator', '&bull;' );
	$base_url = trailingslashit( $this->get_customer_page_url() );		
?>	
	<nav class="cuar-menu">
		<ul class="cuar-actions-container"><?php 		
			foreach ($this->actions as $action) : 
				$li_class = '';
			
				if ( isset( $action['slug'] ) && $this->current_action==$action['slug'] ) {
					$li_class .= 'current';
				} else if ( isset( $action['children'] ) && array_key_exists( $this->current_action, $action['children'] ) ) {
					$li_class .= 'current-parent';
				} else if ( isset( $action['slug'] ) && empty( $this->current_action ) && $action['slug']=='show-dashboard' ) {
					$li_class .= 'current';
				}
				
				$href = isset( $action["url"] ) ? $action["url"] : $base_url . '?action=' . $action["slug"];
				$label = esc_html( $action["label"] ); 
				$hint = esc_attr( $action["hint"] );
			?><li class="<?php echo $li_class; ?>">
				<a href="<?php echo esc_attr( $href ); ?>" title="<?php echo $hint; ?>"><span><?php echo $label; ?></span></a>
				<?php $is_last--; if ( $is_last!=0 ) { echo $separator; } ?></li><?php 		
			endforeach; 
		?></ul>
	</nav>
<?php
endif;
?>