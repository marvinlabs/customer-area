<?php
/*  Copyright 2013 MarvinLabs (contact@marvinlabs.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

/**
 * Get the files attached to a post
 *
 * @param int $post_id Defaults to the current post ID from the loop
 *
 * @return array
 */
function cuar_get_the_attached_files($post_id = null)
{
    if ( !$post_id) {
        $post_id = get_the_ID();
    }
    if ( !$post_id) {
        return '';
    }

    /** @var CUAR_PrivateFileAddOn $pf_addon */
    $pf_addon = cuar_addon('private-files');

    $files = $pf_addon->get_attached_files($post_id);

    return apply_filters('cuar/private-content/files/the-files', $files, $post_id);
}

/**
 * Get the number of files attached to a post
 *
 * @param int $post_id Defaults to the current post ID from the loop
 *
 * @return string
 */
function cuar_get_the_attached_file_count($post_id = null)
{
    if ( !$post_id) {
        $post_id = get_the_ID();
    }
    if ( !$post_id) {
        return '';
    }

    /** @var CUAR_PrivateFileAddOn $pf_addon */
    $pf_addon = cuar_addon('private-files');

    $count = $pf_addon->get_attached_file_count($post_id);

    return apply_filters('cuar/private-content/files/the-count', $count, $post_id);
}

/**
 * Get the URL where the file linked to the specified post can be downloaded directly
 *
 * @param int    $post_id Defaults to the current post ID from the loop
 * @param array  $file    The file description
 * @param string $action  The action (download|view)
 *
 * @return string
 */
function cuar_get_the_attached_file_link($post_id = null, $file, $action = 'download')
{
    if ( !$post_id) {
        $post_id = get_the_ID();
    }
    if ( !$post_id) {
        return '';
    }

    /** @var CUAR_PrivateFileAddOn $pf_addon */
    $pf_addon = cuar_addon('private-files');

    $permalink = $pf_addon->get_file_permalink($post_id, $file['id'], $action, $file);

    return apply_filters('cuar/private-content/files/the-permalink', $permalink, $file, $post_id, $action);
}

/**
 * Prints the URL where the file linked to the specified post can be downloaded directly
 *
 * @see get_the_download_link
 *
 * @param int    $post_id Defaults to the current post ID from the loop
 * @param array  $file    The file description
 * @param string $action  The action (download|view)
 */
function cuar_the_attached_file_link($post_id = null, $file, $action = 'download')
{
    echo cuar_get_the_attached_file_link($post_id, $file, $action);
}

/**
 * Get the name of the file associated to the given post
 *
 * @param int   $post_id Defaults to the current post ID from the loop
 * @param array $file    The file description
 *
 * @return string|mixed
 */
function cuar_get_the_attached_file_caption($post_id = null, $file)
{
    if ( !$post_id) {
        $post_id = get_the_ID();
    }
    if ( !$post_id) {
        return '';
    }

    /** @var CUAR_PrivateFileAddOn $pf_addon */
    $pf_addon = cuar_addon('private-files');

    $caption = $pf_addon->get_file_caption($post_id, $file);

    return apply_filters('cuar/private-content/files/the-caption', $caption, $file, $post_id);
}

/**
 * Prints the name of the file associated to the given post
 *
 * @see get_the_attached_file_name
 *
 * @param int   $post_id Defaults to the current post ID from the loop
 * @param array $file    The file description
 *
 * @return string|mixed
 */
function cuar_the_attached_file_caption($post_id = null, $file)
{
    echo cuar_get_the_attached_file_caption($post_id, $file);
}

/**
 * Get the name of the file associated to the given post
 *
 * @param int   $post_id Defaults to the current post ID from the loop
 * @param array $file    The file description
 *
 * @return string|mixed
 */
function cuar_get_the_attached_file_name($post_id = null, $file)
{
    if ( !$post_id) {
        $post_id = get_the_ID();
    }
    if ( !$post_id) {
        return '';
    }

    /** @var CUAR_PrivateFileAddOn $pf_addon */
    $pf_addon = cuar_addon('private-files');

    $name = $pf_addon->get_file_name($post_id, $file);

    return apply_filters('cuar/private-content/files/the-name', $name, $file, $post_id);
}

/**
 * Prints the name of the file associated to the given post
 *
 * @see get_the_attached_file_name
 *
 * @param int   $post_id Defaults to the current post ID from the loop
 * @param array $file    The file description
 *
 * @return string|mixed
 */
function cuar_the_attached_file_name($post_id = null, $file)
{
    echo cuar_get_the_attached_file_name($post_id, $file);
}

/**
 * Get the type of the file associated to the given post
 *
 * @param int   $post_id Defaults to the current post ID from the loop
 * @param array $file    The file description
 *
 * @return string|mixed
 */
function cuar_get_the_attached_file_type($post_id = null, $file)
{
    if ( !$post_id) {
        $post_id = get_the_ID();
    }
    if ( !$post_id) {
        return '';
    }

    /** @var CUAR_PrivateFileAddOn $pf_addon */
    $pf_addon = cuar_addon('private-files');

    $type = $pf_addon->get_file_type($post_id, $file);

    return apply_filters('cuar/private-content/files/the-type', $type, $file, $post_id);
}

/**
 * Prints the type of the file associated to the given post
 *
 * @see get_the_attached_file_type
 *
 * @param int   $post_id Defaults to the current post ID from the loop
 * @param array $file    The file description
 *
 * @return string|mixed
 */
function cuar_the_attached_file_type($post_id = null, $file)
{
    echo cuar_get_the_attached_file_type($post_id, $file);
}

/**
 * Get the type of the file associated to the given post
 *
 * @param int   $post_id Defaults to the current post ID from the loop
 * @param array $file    The file description
 *
 * @return string|mixed
 */
function cuar_get_the_attached_file_size($post_id = null, $file, $human = true)
{
    if ( !$post_id) {
        $post_id = get_the_ID();
    }
    if ( !$post_id) {
        return '';
    }


    /** @var CUAR_PrivateFileAddOn $pf_addon */
    $pf_addon = cuar_addon('private-files');
    $size = $pf_addon->get_file_size($post_id, $file);

    if (false === $size) {
        return '';
    }

    if ($human) {
        $size = cuar_format_human_file_size($size);
    }

    return apply_filters('cuar/private-content/files/the-size', $size, $file, $post_id);
}

/**
 * Prints the type of the file associated to the given post
 *
 * @see cuar_get_the_attached_file_size
 *
 * @param int   $post_id Defaults to the current post ID from the loop
 * @param array $file    The file description
 *
 * @return string|mixed
 */
function cuar_the_attached_file_size($post_id = null, $file, $human = true)
{
    echo cuar_get_the_attached_file_size($post_id, $file, $human);
}


/** Helper function to format file size */
function cuar_format_human_file_size($size)
{
    $factor = 1;
    $unit = _x('b', 'bytes', 'cuar');

    if ($size >= 1024 * 1024 * 1024 * 1024) {
        $factor = 1024 * 1024 * 1024 * 1024;
        $unit = __('TB', 'cuar');
    } else if ($size >= 1024 * 1024 * 1024) {
        $factor = 1024 * 1024 * 1024;
        $unit = __('GB', 'cuar');
    } else if ($size >= 1024 * 1024) {
        $factor = 1024 * 1024;
        $unit = __('MB', 'cuar');
    } else if ($size >= 1024) {
        $factor = 1024;
        $unit = __('kB', 'cuar');
    }

    return sprintf('%1$s&nbsp;%2$s', number_format($size / $factor, 2), $unit);
}

/**
 * @param array $args The arguments to pass to each cuar_create_private_file function call.
 *
 * ´cuar_bulk_create_private_files(array(
 *      array(
 *          'post_data' => (...),
 *          'owners'     => (...),
 *          'files'     => array(
 *              array(
 *                 'name'   => 'example.txt',
 *                 'path'   => '/absolute/path/to/file/',
 *                 'method' => 'noop|copy|move'
 *              ),
 *              ...
 *          ),
 *      ),
 *      array(
 *          'post_data' => (...),
 *          'owner'     => (...),
 *          'files'     => (...),
 *      ),
 *      ...
 *   )
 * );´
 *
 * @return array An array containing the created post IDs and the errors
 */
function cuar_bulk_create_private_files($args)
{
    $result = array(
        'created' => array(),
        'errors'  => array()
    );

    foreach ($args as $a) {
        $res = cuar_create_private_file($a['post_data'], $a['owners'], $a['files']);
        if (is_wp_error($res)) {
            $result['errors'][] = $res;
        } else {
            $result['created'][] = $res;
        }
    }

    return $result;
}

/**
 * @param array $post_data  The same array you would give to wp_insert_post to create your post. No need to set the post
 *                          type, this will automatically be set.
 * @param array $owners     An array containing the owner description: type ('usr', 'grp', 'prj', 'rol', etc.) and IDs
 *                          of corresponding objects
 * @param array $files      An array containing the paths to the files to attache to the post object. Currently we only
 *                          support a single file.
 *
 * @return int¦WP_Error the post ID if the function could insert the post, else, a WP_Error object
 *
 * ´cuar_create_private_file(
 *      array(
 *          'post_title'   => 'Test file',
 *          'post_content' => 'This is the content',
 *          'post_status'  => 'publish'
 *      ),
 *      array(
 *          'usr' => array(1),
 *          'grp'  => array(10, 50)
 *      ),
 *      'files'     => array(
 *          array(
 *            'name'   => 'example.txt',
 *            'path'   => '/absolute/path/to/file/',
 *            'method' => 'noop|copy|move'
 *          ),
 *          ...
 *      )
 * );´
 *
 * IMPORTANT NOTE: The files have to be located in the plugin's FTP upload folder.
 */
function cuar_create_private_file($post_data, $owners, $files)
{
    // Create the post object
    $post_data['post_type'] = 'cuar_private_file';
    $post_id = wp_insert_post($post_data);
    if (is_wp_error($post_id)) {
        return $post_id;
    }

    // Assign the owner
    /** @var CUAR_PostOwnerAddOn $po_addon */
    $po_addon = cuar_addon('post-owner');
    $po_addon->save_post_owners($post_id, $owners);

    // Attach the file
    /** @var CUAR_PrivateFileAddOn $pf_addon */
    $pf_addon = cuar_addon('private-files');
    foreach ($files as $file) {
        $initial_filename = basename($file['name']);
        $filename = apply_filters('cuar/private-content/files/unique-filename?method=server',
            $initial_filename,
            $post_id,
            $file);

        $errors = apply_filters('cuar/private-content/files/on-attach-file?method=server',
            array(),
            $pf_addon,
            $initial_filename,
            $post_id,
            $filename,
            $filename,
            $file);

        $extra = array(
            'is_protected' => ($file['method'] == 'noop' ? 0 : 1),
            'abs_path'     => ($file['method'] == 'noop' ? trailingslashit($file['path']) . $file['name'] : ''),
        );

        $pf_addon->add_attached_file($post_id, $filename, $filename, 'server', $extra);

        if ( !empty($errors)) {
            wp_delete_post($post_id);

            return new WP_Error('upload_error', implode(', ', $errors));
        }
    }

    return $post_id;
}