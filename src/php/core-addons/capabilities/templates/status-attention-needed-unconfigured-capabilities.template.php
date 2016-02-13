<?php
/** Template version: 2.0.0
 *
 * -= 2.0.0 =-
 * - Add cuar- prefix to bootstrap classes
 *
 * -= 1.0.0 =-
 * - Initial version
 *
 */
?>

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
    <h3><?php _e('Configure permissions', 'cuar'); ?></h3>

    <p><?php _e('You have not yet configured the permissions. By default, the plugin will allows the administrators to do everything but the other roles will not be able to use the customer area. '
            . 'The permissions can easily be configured from the Customer Area settings.', 'cuar'); ?></p>

    <p class="cuar-suggested-action"><span class="cuar-text"><?php _e('Suggested action', 'cuar'); ?></span>
        <a href="<?php echo admin_url('admin.php?page=wpca-settings&tab=cuar_capabilities'); ?>"
           class="cuar-button cuar-button-primary"><?php _e('Configure permissions', 'cuar'); ?> &raquo;</a>
        <input type="submit" id="cuar-ignore-unconfigured-capabilities" name="cuar-ignore-unconfigured-capabilities" class="button button-primary" value="<?php esc_attr_e('Ignore this warning', 'cuar'); ?> &raquo;"/>
        <?php wp_nonce_field('cuar-ignore-unconfigured-capabilities', 'cuar-ignore-unconfigured-capabilities_nonce'); ?>
    </p>
</div>