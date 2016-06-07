<?php
/** Template version: 3.0.0
 *
 * -= 3.0.0 =-
 * - Improve UI for new master-skin
 *
 * -= 1.0.0 =-
 * - Initial version
 *
 */ ?>

<?php /** @var string $title */ ?>
<?php /** @var string $type */ ?>

<div class="panel top <?php echo $type; ?>">
    <?php if ( !empty($title)): ?>
        <div class="panel-heading">
            <span class="panel-title">
                <?php echo $title; ?>
            </span>
        </div>
    <?php endif; ?>
    <div class="panel-body pn">
        <p><?php _e( 'There is no content to show', 'cuar' ); ?></p>
    </div>
</div>