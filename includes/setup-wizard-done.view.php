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

$cp_addon = $this->plugin->get_addon('customer-page');
?>

<div class="wrap cuar-wizard">
	<?php screen_icon( 'icon32-settings' ); ?>
	
	<h2><?php _e('Well done!', 'cuar'); ?></h2>
	
	<div class="cuar-main cuar-welcome">
	
		<div class="cuar-block" style="background: #333">
			<h1><?php _e( "Your Customer Area is ready", 'cuar'); ?></h1>
		</div>
		
		<div class="cuar-block" style="background: #ddd">
			<p><?php _e( 'A page has been published on your site and will display the private content for the user who is logged-in. '
						. 'If you want, you can:', 'cuar' ); ?></p>
				
			<ul>
				<li><?php printf( __( 
						'<a href="%1$s" target="_blank">See how it looks</a> on your site.', 'cuar' ),
					$cp_addon->get_customer_page_url() ); ?></li>
				<li><?php printf( __( 
						'<a href="%1$s" target="_blank">Edit the page</a> if you want to change the title or maybe make it a child page of another one. ', 'cuar' ),
					admin_url( 'post.php?action=edit&post=' . $cp_addon->get_customer_page_id() ) ); ?></li>
			</ul>
				
			<p><?php _e( 
						'Of course, because the plugin has just been setup, you will not see any private content in there. A good way to get started is to create a few private files and private pages owned by yourself. '
						. 'This way, you will be able to see how it looks in your site and no one else but you will be able to see it.', 'cuar' ); ?></p>
		</div>
		
		<div class="cuar-block" style="background: #E6402A">
			<h1><?php _e( "Subscribe to our newsletter", 'cuar'); ?></h1>
		</div>
		
		<div class="cuar-block" style="background: #FBDFDC">			
			<p><?php _e( "You can get notified when we've got something exciting to say (plugin updates, news, etc.). Simply "
						. "subscribe to our newsletter, we won't spam, we send at most one email per month!", 'cuar' ); ?></p> 
					
			<!-- Begin MailChimp Signup Form -->
			<div id="mc_embed_signup">
				<form action="http://marvinlabs.us7.list-manage.com/subscribe/post?u=1bbbff0bec2e3841b42494431&amp;id=4b52ced231" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
					<p class="mc-field-group">
						<label for="mce-EMAIL"><?php _e('Email Address', 'cuar' ); ?>&nbsp;&nbsp;</label>
						<input type="email" value="" name="EMAIL" class="required email" id="mce-EMAIL" /> &nbsp;&nbsp;&nbsp;&nbsp;
						<input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="button button-primary">
					</p>
					<div id="mce-responses" class="clear">
						<div class="response" id="mce-error-response" style="display:none"></div>
						<div class="response" id="mce-success-response" style="display:none"></div>
					</div>	
					<div class="clear">
						
					</div>
				</form>
			</div>
		</div>
		
		<div class="cuar-block" style="background: #0066FF">
			<h1><?php _e( "Getting help", 'cuar'); ?></h1>
		</div>
		
		<div class="cuar-block" style="background: #D5E6FF">
			<p><?php _e( 'If you have any problem, we have some resources that could help you:', 'cuar' ); ?></p>
				
			<ul>
				<li><?php printf( __( 
						'<a href="%1$s" target="_blank">Documentation</a> for the main plugin and for the add-ons.', 'cuar' ),
					'http://customer-area.marvinlabs.com/documentation/'); ?></li>
				<li><?php printf( __( 
						'<a href="%1$s" target="_blank">Frequently Asked Questions</a>. ', 'cuar' ),
					'http://customer-area.marvinlabs.com/category/faq/'); ?></li>
				<li><?php printf( __( 
						'A dedicated <a href="%1$s" target="_blank">wordpress.org support forum</a>, only for the main plugin (English only as per the wordpress.org rules).', 'cuar' ),
					'http://wordpress.org/support/plugin/customer-area'); ?></li>
				<li><?php printf( __( 
						'<a href="%1$s" target="_blank">Support forums</a> on our website for the main plugin as well as for the add-ons (English, French and Spanish are ok there). ', 'cuar' ),
					'http://customer-area.marvinlabs.com/support/'); ?></li>
			</ul>
		</div>
		
<?php
	include_once(ABSPATH . WPINC . '/feed.php');
	
	$feed = fetch_feed('http://www.marvinlabs.com/downloads/category/customer-area/feed/rss/');

	if ( is_wp_error( $feed ) ) : 
?>
		<p><?php printf( __('There has been an error while getting the list of add-ons, please <a href="%s">visit our shop directly</a>.', 'cuar' ), 
					'http://www.marvinlabs.com/downloads/category/customer-area/' ); ?></p>
		<p class="description"><?php echo $feed->get_error_message(); ?></p>			
<?php 	
	else : ?>
			
		<div class="cuar-block" style="background: #339900">
			<h1><?php _e( 'Save money with our add-on bundles', 'cuar' ); ?></h1>
		</div>
<div class="cuar-add-ons" style="background: #DDEED5">
<?php 
	$force_line_break = false; 
	foreach ( $feed->get_items() as $item ) :
		if ( FALSE===stripos( $item->get_title(), 'bundle' ) ) continue;
?>
	<div class="cuar-addon">
		<?php if ( $enclosure = $item->get_enclosure() ) : ?>
		<a href="<?php echo $item->get_permalink(); ?>">
			<img src="<?php echo $enclosure->get_link(); ?>" />
		</a>
		<?php endif; ?>
		<div class="meta">
			<h2><a href="<?php echo $item->get_permalink(); ?>"><?php echo str_replace( 'Customer Area – ', '', $item->get_title() ); ?></a></h2>
			<?php echo $item->get_description(); ?>
		</div>
	</div>	
	
<?php 	if ($force_line_break) echo '<p class="forcelinebreak">&nbsp;</p>'; ?>
	
<?php
		$force_line_break = !$force_line_break;
	endforeach; ?>
	<p class="forcelinebreak">&nbsp;</p>
</div>

		<div class="cuar-block" style="background: #339900">
			<h1><?php _e( 'Get our add-ons individually', 'cuar' ); ?></h1>
		</div>

<div class="cuar-add-ons" style="background: #DDEED5">
<?php 
	$force_line_break = false; 
	foreach ( $feed->get_items() as $item ) :
		if ( FALSE!==stripos( $item->get_title(), 'bundle' ) ) continue;
?>
	<div class="cuar-addon">
		<?php if ( $enclosure = $item->get_enclosure() ) : ?>
		<a href="<?php echo $item->get_permalink(); ?>">
			<img src="<?php echo $enclosure->get_link(); ?>" />
		</a>
		<?php endif; ?>
		<div class="meta">
			<h2><a href="<?php echo $item->get_permalink(); ?>"><?php echo str_replace( 'Customer Area – ', '', $item->get_title() ); ?></a></h2>
			<?php echo $item->get_description(); ?>
		</div>
	</div>	
	
<?php 	if ($force_line_break) echo '<p class="forcelinebreak">&nbsp;</p>'; ?>
	
<?php
		$force_line_break = !$force_line_break;
	endforeach; ?>
	<p class="forcelinebreak">&nbsp;</p>
</div>

<?php 
	endif; ?>
	</div>
	
	<div class="cuar-side">
		<div class="dashboard-logo">
			<img src="<?php echo $this->plugin->get_admin_theme_url(); ?>/images/logo.png">
		</div>
		
	</div>
</div>

