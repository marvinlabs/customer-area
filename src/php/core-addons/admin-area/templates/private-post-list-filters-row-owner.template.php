<?php /** Template version: 1.0.0 */ ?>
<?php $this->plugin->enable_library('jquery.select2'); ?>

<div class="cuar-filter-row">
    <?php
    $visible_by = $list_table->get_parameter('visible-by');
    $is_locked = !current_user_can($post_type_object->cap->read_private_posts);
    ?>
    <label for="visible-by"><?php _e('Visible by', 'cuar'); ?> </label>
    <select id="visible-by" data-width="100%" name="visible-by" class="cuar-ajax-users postform"<?php echo $is_locked ? ' disabled="disabled"' : '' ?>>
        <?php if ($is_locked) : ?>
            <option value="<?php echo get_current_user_id(); ?>"><?php _e('Yourself', 'cuar'); ?></option>
        <?php endif; ?>
        <?php if(!empty($visible_by)): ?>
            <option value="<?php echo $visible_by; ?>" selected="selected"><?php echo get_userdata($visible_by)->display_name; ?></option>
        <?php endif; ?>
    </select>
</div>
<script type="text/javascript">
(function ($) {
    $(document).ready(function(){
        $('.cuar-ajax-users').select2(getSelect2UserSelectorOpts());
    });
})(jQuery);
</script>
