<?php /** Template version: 1.0.0 */ ?>

<?php

$parts = array('search', 'author', 'owner', 'taxonomies', 'timestamp', 'submit');
$all_users = get_users(array('orderby' => 'display_name', 'fields' => 'all_with_meta'));

foreach( $parts as $p)
{
    /** @noinspection PhpIncludeInspection */
    include($this->plugin->get_template_file_path(
        CUAR_INCLUDES_DIR . '/core-addons/admin-area',
        'private-post-list-filters-row-' . $p . '.template.php',
        'templates'));
}
