<?php /** Template version: 1.0.0 */ ?>
<?php
wp_enqueue_script('cuar.admin');
$this->plugin->enable_library('jquery.select2');
?>

<div class="cuar-filter-row">
    <?php
    $author = $list_table->get_parameter('author');
    $is_locked = !current_user_can($post_type_object->cap->read_private_posts);
    ?>
    <label for="author"><?php _e('Created by', 'cuar'); ?> </label>
    <select id="author" data-width="100%" name="author" class="cuar-ajax-users postform"<?php echo $is_locked ? ' disabled="disabled"' : '' ?>>
        <?php if(!empty($author)): ?>
            <option value="<?php echo $author; ?>" selected="selected"><?php echo get_userdata($author)->display_name; ?></option>
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
