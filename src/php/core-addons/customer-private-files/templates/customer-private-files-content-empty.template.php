<?php
/** Template version: 2.0.0
 *
 * -= 2.0.0 =-
 * - Improve UI
 * - Add cuar- prefix to bootstrap classes
 *
 * -= 1.0.0 =-
 * - Initial version
 *
 */
?>

<div class="cuar-panel cuar-panel-default cuar-project-list cuar-dashboard-list-item">

    <header class="cuar-panel-heading">

        <?php
        global $cpf_addon;
        if(!$cpf_addon)
            $cpf_addon = $this->plugin->get_addon('customer-private-files');
        $page_id = $cpf_addon->get_page_id( $this->get_slug() );
        ?>
        <h2 class="cuar-panel-title">
            <a href="<?php echo get_permalink($page_id); ?>" title="<?php esc_attr_e( 'View all', 'cuarfl_text' ); ?>">
                <i class="fa fa-puzzle-piece"></i> <?php echo $page_subtitle; ?>
            </a>
        </h2>

        <div class="cuar-panel-options">

            <a href="#" data-toggle="panel" class="cuar-btn cuar-btn-xs cuar-btn-default" title="<?php esc_attr_e( 'Toggle this panel', 'cuarfl_text' ); ?>">
                <span class="cuar-collapse-icon"><i class="dashicons dashicons-arrow-up-alt2"></i></span>
                <span class="cuar-expand-icon"><i class="dashicons dashicons-arrow-down-alt2"></i></span>
            </a>

            <a href="#" data-toggle="remove" class="cuar-btn cuar-btn-xs cuar-btn-default" title="<?php esc_attr_e( 'Remove this panel', 'cuarfl_text' ); ?>">
                <i class="dashicons dashicons-no-alt"></i>
            </a>

        </div>

    </header>
    <div class="cuar-panel-body">
        <p><?php _e( 'You currently have no files.', 'cuar' ); ?></p>
    </div>
</div>