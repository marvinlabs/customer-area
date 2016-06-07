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
    <h3><?php _e('Navigation menu is out of sync', 'cuar'); ?></h3>

    <p>
        <?php _e('Some pages of the customer area have been changed and our current navigation menu probably references the previous pages. We recommend to synchronize the menu with the new pages. ', 'cuar'); ?>
        <?php printf(__('You can also manually edit that menu in the <a href="%1$s">Appearance &raquo; Menus</a> page.', 'cuar'), admin_url('nav-menus.php')); ?>
    </p>

    <p class="cuar-suggested-action"><span class="text"><?php _e('Suggested actions', 'cuar'); ?></span>
        <input type="submit" id="cuar-synchronize-menu" name="cuar-synchronize-menu" value="<?php esc_attr_e('Synchronize menu', 'cuar'); ?> &raquo;" class="button button-primary"/>
        <?php wp_nonce_field('cuar-synchronize-menu', 'cuar-synchronize-menu_nonce'); ?>

        <input type="submit" id="cuar-clear-sync-nav-warning" name="cuar-clear-sync-nav-warning" value="<?php esc_attr_e('Ignore warning', 'cuar'); ?> &raquo;" class="button"/>
        <?php wp_nonce_field('cuar-clear-sync-nav-warning', 'cuar-clear-sync-nav-warning_nonce'); ?>

        <script type="text/javascript">
            <!--
            jQuery(document).ready(function ($) {
                $('#cuar-synchronize-menu').click('click', function () {
                    var answer = confirm("<?php echo str_replace( '"', '\\"', __('Are you sure that you want to clear current menu and create it again from scratch (this operation cannot be undone)?', 'cuar') ); ?>");
                    return answer;
                });

                $('#cuar-clear-sync-nav-warning').click('click', function () {
                    var answer = confirm("<?php echo str_replace( '"', '\\"', __('Are you sure that you want ignore this warning? You can also manually edit your menu in the page: "Appearance > Menus".', 'cuar') ); ?>");
                    return answer;
                });
            });
            //-->
        </script>
    </p>
</div>