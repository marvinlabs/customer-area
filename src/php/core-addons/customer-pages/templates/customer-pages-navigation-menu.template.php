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

    <nav class="cuar-navbar cuar-navbar-default" role="navigation">
        <div class="cuar-navbar-header">
            <button type="button" class="cuar-navbar-toggle" data-toggle="collapse"
                    data-target=".cuar-nav-container">
                <span class="cuar-sr-only"><?php __('Toggle navigation', 'cuar'); ?></span>
                <span class="cuar-icon-bar"></span>
                <span class="cuar-icon-bar"></span>
                <span class="cuar-icon-bar"></span>
            </button>
        </div>
        <?php wp_nav_menu($nav_menu_args); ?>
    </nav>

<?php echo $this->get_subpages_menu(); ?>