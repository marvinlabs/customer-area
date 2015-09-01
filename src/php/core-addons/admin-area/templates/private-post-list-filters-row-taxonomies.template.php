<?php /** Template version: 1.0.0
 *
 * -=1.1.0=-
 *
 * Updated the dropdown to show tax hierarchy
 *
 */ ?>

<?php /** @var CUAR_ListTable $list_table */ ?>

<?php if (!empty($associated_taxonomies)) : ?>
    <div class="cuar-filter-row">
        <?php foreach ($associated_taxonomies as $tax_slug => $tax_obj) : ?>
            <label for="<?php echo $tax_slug; ?>"><?php echo $tax_obj->labels->name; ?> </label>

            <?php
                wp_dropdown_categories(array(
                    'show_option_all'    => '',
                    'show_option_none'   => __('Any', 'cuar'),
                    'option_none_value'  => '',
                    'orderby'            => 'ID',
                    'order'              => 'ASC',
                    'show_count'         => 0,
                    'hide_empty'         => 1,
                    'child_of'           => 0,
                    'exclude'            => '',
                    'echo'               => 1,
                    'selected'           => $list_table->get_parameter($tax_slug),
                    'hierarchical'       => 1,
                    'name'               => $tax_slug,
                    'id'                 => $tax_slug,
                    'class'              => 'postform',
                    'depth'              => 0,
                    'tab_index'          => 0,
                    'taxonomy'           => $tax_slug,
                    'hide_if_empty'      => true,
                    'value_field'	     => 'slug',
                ));
            ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>