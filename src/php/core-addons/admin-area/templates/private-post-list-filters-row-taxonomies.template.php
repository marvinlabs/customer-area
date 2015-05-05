<?php /** Template version: 1.0.0 */ ?>

<?php if (!empty($associated_taxonomies)) : ?>
    <div class="cuar-filter-row">
        <?php foreach ($associated_taxonomies as $tax_slug => $tax_obj) : ?>
            <label for="<?php echo $tax_slug; ?>"><?php echo $tax_obj->labels->name; ?> </label>
            <select name="<?php echo $tax_slug; ?>" id="<?php echo $tax_slug; ?>" class='postform'>
                <option value=''><?php _e('Any', 'cuar'); ?></option>
                <?php
                $terms = get_terms($tax_slug);
                foreach ($terms as $term)
                {
                    $selected = selected($list_table->get_parameter($tax_slug), $term->slug, false);
                    printf('<option value="%1$s" %2$s>%3$s</option>', $term->slug, $selected, $term->name);
                }
                ?>
            </select>
        <?php endforeach; ?>
    </div>
<?php endif; ?>