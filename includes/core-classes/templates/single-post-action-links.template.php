<?php /** Template version: 1.0.0 */ ?>

<?php if ( !empty( $links ) ) : ?>
<div class="cuar-single-post-actions btn-group">
<?php foreach ( $links as $id => $link ) : ?>
	<a id="single-post-action-<?php echo $id; ?>" href="<?php echo $link['url']; ?>" title="<?php echo esc_attr( $link['title'] ); ?>" class="btn btn-default <?php echo $link['extra_class']; ?>"><?php echo $link['title']; ?></a>

<?php 	if ( isset( $link['confirm_message'] ) && !empty( $link['confirm_message'] ) ) : ?>
	<script type="text/javascript">
	<!--
		jQuery(document).ready(function($) {
			$('#single-post-action-<?php echo $id; ?>').click('click', function(){
				var answer = confirm( "<?php echo str_replace( '"', '\\"', $link['confirm_message'] ); ?>" );
				return answer;
			});
		});
	//-->
	</script>
<?php 	endif; ?>
	
<?php endforeach; ?>
</div>
<?php endif; ?>