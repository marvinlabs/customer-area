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
    <h3><?php _e('Missing navigation menu', 'cuar'); ?></h3>

    <p>
        <?php _e('A navigation menu organizes the customer area. It allows to browse the files, the pages, to view the account details and much more. It seems that this menu has not yet been created.', 'cuar'); ?>
        <?php printf(__('You can review which menus are currently created on the <a href="%1$s">menus page</a>.', 'cuar'),
            admin_url('nav-menus.php')); ?>
    </p>

    <p class="cuar-suggested-action"><span class="cuar-text"><?php _e('Suggested action', 'cuar'); ?></span>
        <input type="submit" id="cuar-synchronize-menu" name="cuar-synchronize-menu" value="<?php esc_attr_e('Create the menu', 'cuar'); ?> &raquo;" class="button button-primary"/>
        <?php wp_nonce_field('cuar-synchronize-menu', 'cuar-synchronize-menu_nonce'); ?>

        <script type="text/javascript">
            <!--
            jQuery(document).ready(function ($) {
                $('#cuar-synchronize-menu').click('click', function () {
                    var answer = confirm("<?php echo str_replace( '"', '\\"', __('Are you sure that you want to create the menu (this operation cannot be undone)?', 'cuar') ); ?>");
                    return answer;
                });
            });
            //-->
        </script>
    </p>
</div>