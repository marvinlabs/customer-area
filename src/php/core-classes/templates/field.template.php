<?php /** Template version: 1.0.0 */ ?>

<?php	 
	$class = implode( ' ', $field['classes'] );
	$label = $field['label'];
	$placeholder = $field['placeholder']; 
	$value = empty( $field['value'] ) && $placeholder!==false ? $placeholder : $field['value'];
		
	if ( !empty( $value ) || $placeholder!==false ) :
?>
	<p class="cuar-field <?php echo $class; ?>">
		<span class="cuar-field-name"><?php echo $label; ?></span>
		<span class="cuar-field-value"><?php echo $value; ?></span>
	</p>
<?php 
	endif;
?>