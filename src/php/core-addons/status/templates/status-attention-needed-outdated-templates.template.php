<?php /** Template version: 1.0.0 */ ?>

<?php
/*  Copyright 2013 MarvinLabs (contact@marvinlabs.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

?>

<div class="cuar-needs-attention">
	<h3><?php _e( 'Outdated template files', 'cuar' ); ?></h3>

	<p><?php printf( __('Some template files you have overridden have been modified in the current version of WP Customer Area. You have the list of these files below, but you can also '
							. 'find a list of all template files and their respective status on the <a href="%s">corresponding templates status page</a>', 'cuar'),
					admin_url('admin.php?page=wpca-status&tab=templates')
		 		); ?></p>
	
<?php if ( version_compare( $this->plugin->get_version(), '4.4.0', '<=' ) ) : ?>	

	<p><em><?php _e('Since WP Customer Area 4.4.0, we are providing a way to make sure your template files are always up-to-date. For you, this is a great way to know when template '
				. 'files get changed when Customer Area is updated.', 'cuar'); ?></em></p>

	<p><em><?php _e('If you still do not have any version number in your template files, please paste the following line of '
				. 'code as the first line of your template and make sure it is compatible with the the current template version.', 'cuar'); ?></em></p>
	
	<pre><code>&lt;?php /** Template version: 1.0.0 */ ?&gt;</code></pre>

	
<?php  	endif; ?>
	
	<ol>	 
<?php
        $dirs_to_scan = apply_filters( 'cuar/core/status/directories-to-scan', array( CUAR_PLUGIN_DIR => __( 'WP Customer Area', 'cuar' ) ) );
        foreach ( $dirs_to_scan as $dir => $title ) :
			$template_finder = new CUAR_TemplateFinder( $this->plugin->get_template_engine() );
			$template_finder->scan_directory( $dir );
			$outdated = $template_finder->get_outdated_templates();

			if ( empty( $outdated ) ) continue;
?>
		<li>
			<h4><?php echo $title; ?></h4>
			<ul>
<?php 		foreach ( $outdated as $name => $t ) : ?>
				<li><code><?php echo $name; ?></code> <?php printf( 'Current is %s (you have %s)', $t->get_original_version(), $t->get_current_version() ); ?></li>
<?php 		endforeach; ?>
			</ul>
		</li>
<?php 	endforeach; ?>
	</ol>
		 
		 
	<p class="cuar-suggested-action"><span class="text"><?php _e('Other action', 'cuar' ); ?></span> 
		<input type="submit" id="cuar-ignore-outdated-templates" name="cuar-ignore-outdated-templates" class="button button-primary" value="<?php esc_attr_e( 'Ignore until next update', 'cuar' ); ?> &raquo;" />
		<?php wp_nonce_field( 'cuar-ignore-outdated-templates', 'cuar-ignore-outdated-templates_nonce' ); ?>
	</p>	
</div>