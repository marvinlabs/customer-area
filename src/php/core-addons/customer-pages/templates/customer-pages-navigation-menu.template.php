<?php
/** Template version: 3.0.0
 *
 * -= 3.0.0 =-
 * - Improve UI for new master-skin
 * - Removed contextual toolbar
 * - Added container to wrap the menu
 *
 * -= 2.0.0 =-
 * - Add cuar- prefix to bootstrap classes
 *
 * -= 1.0.0 =-
 * - Initial version
 *
 */
?>

<div class="cuar-menu-container">
    <nav class="navbar" role="navigation">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".nav-container">
                <span class="sr-only"><?php _e('Toggle navigation', 'cuar'); ?></span> <span class="icon-bar"></span>
                <span class="icon-bar"></span> <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#"><?php _e('Menu', 'cuar'); ?></a>
        </div>
        <?php wp_nav_menu($nav_menu_args); ?>
    </nav>
</div>
