<?php
/** Template version: 3.0.0
 *
 * -= 3.0.0 =-
 * - Initial version
 *
 */
?>

<?php /** @var array $toolbar_groups */ ?>

<div class="cuar-toolbar">
    <?php do_action('cuar/core/page/before-toolbar-groups'); ?>

    <?php foreach ($toolbar_groups as $id => $group) : ?>
        <?php if (isset($group['type']) && $group['type']=='raw') : ?>
            <?php echo $group['html']; ?>
        <?php else: ?>
        <div class="cuar-toolbar-group cuar-js-toolbar-group-<?php echo $id; ?> btn-group <?php echo $group['extra_class']; ?>">
            <?php foreach ($group['items'] as $item_id => $item) : ?>
                <a href="<?php echo $item['url']; ?>" title="<?php echo esc_attr( $item['tooltip'] ); ?>" class="cuar-js-contextal-action-<?php echo $item_id; ?> btn btn-default <?php echo $item['extra_class']; ?>"><?php
                    echo $item['title'];
                ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php do_action('cuar/core/page/after-toolbar-groups'); ?>
</div>

<script type="text/javascript">
    <!--
    jQuery(document).ready(function($) {
<?php foreach ($toolbar_groups as $id => $group) :
        if (isset($group['type']) && $group['type']=='raw') continue;
        foreach ($group['items'] as $item_id => $item) : ?>
        <?php if ( isset( $item['confirm_message'] ) && !empty( $item['confirm_message'] ) ) : ?>
            $('.cuar-js-toolbar-group-<?php echo $id; ?> .cuar-js-contextal-action-<?php echo $item_id; ?>').click('click', function(){
                return confirm( "<?php echo str_replace( '"', '\\"', $item['confirm_message'] ); ?>" );
            });
        <?php endif; ?>
    <?php endforeach; ?>
<?php endforeach; ?>
    });
    //-->
</script>