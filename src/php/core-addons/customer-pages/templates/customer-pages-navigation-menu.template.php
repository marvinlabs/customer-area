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

    <nav class="navbar navbar-default" role="navigation">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse"
                        data-target=".nav-container">
                    <span class="sr-only"><?php _e('Toggle navigation', 'cuar'); ?></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#"><?php _e('Menu', 'cuar'); ?></a>
            </div>
            <?php wp_nav_menu($nav_menu_args); ?>
        </div>
    </nav>

<?php echo $this->get_subpages_menu(); ?>