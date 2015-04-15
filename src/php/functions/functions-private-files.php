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
 * Get the URL where the file linked to the specified post can be downloaded directly
 *
 * @param int $post_id Defaults to the current post ID from the loop
 *
 * @return string
 */
function cuar_get_the_file_link($post_id = null, $action = 'download')
{
    if ( !$post_id)
    {
        $post_id = get_the_ID();
    }
    if ( !$post_id)
    {
        return '';
    }

    $cuar_plugin = CUAR_Plugin::get_instance();
    $pf_addon = $cuar_plugin->get_addon('private-files');

    return $pf_addon->get_file_permalink($post_id, $action);
}

/**
 * Prints the URL where the file linked to the specified post can be downloaded directly
 *
 * @see get_the_download_link
 *
 * @param int $post_id Defaults to the current post ID from the loop
 */
function cuar_the_file_link($post_id = null, $action = 'download')
{
    echo cuar_get_the_file_link($post_id, $action);
}

/**
 * Get the name of the file associated to the given post
 *
 * @param int $post_id
 *
 * @return string|mixed
 */
function cuar_get_the_file_name($post_id = null)
{
    if ( !$post_id)
    {
        $post_id = get_the_ID();
    }
    if ( !$post_id)
    {
        return '';
    }

    $file = get_post_meta($post_id, 'cuar_private_file_file', true);

    if ( !$file || empty($file))
    {
        return '';
    }

    return apply_filters('cuar/private-content/files/the-name', $file['file'], $post_id);
}

/**
 * Prints the name of the file associated to the given post
 *
 * @see get_the_file_name
 *
 * @param int $post_id
 *
 * @return string|mixed
 */
function cuar_the_file_name($post_id = null)
{
    echo cuar_get_the_file_name($post_id);
}

/**
 * Get the type of the file associated to the given post
 *
 * @param int $post_id
 *
 * @return string|mixed
 */
function cuar_get_the_file_type($post_id = null)
{
    if ( !$post_id)
    {
        $post_id = get_the_ID();
    }
    if ( !$post_id)
    {
        return '';
    }

    $file = get_post_meta($post_id, 'cuar_private_file_file', true);

    if ( !$file || empty($file))
    {
        return '';
    }

    return apply_filters('cuar/private-content/files/the-type', pathinfo($file['file'], PATHINFO_EXTENSION), $post_id);
}

/**
 * Prints the type of the file associated to the given post
 *
 * @see get_the_file_type
 *
 * @param int $post_id
 *
 * @return string|mixed
 */
function cuar_the_file_type($post_id = null)
{
    echo cuar_get_the_file_type($post_id);
}

/**
 * Get the type of the file associated to the given post
 *
 * @param int $post_id
 *
 * @return string|mixed
 */
function cuar_get_the_file_size($post_id = null, $human = true)
{
    if ( !$post_id)
    {
        $post_id = get_the_ID();
    }
    if ( !$post_id)
    {
        return '';
    }

    $cuar_plugin = CUAR_Plugin::get_instance();
    $pf_addon = $cuar_plugin->get_addon('private-files');

    $size = $pf_addon->get_file_size($post_id);
    if (false === $size)
    {
        return '';
    }

    if ($human)
    {
        $size = cuar_format_human_file_size($size);
    }

    return apply_filters('cuar/private-content/files/the-size', $size, $post_id);
}

/**
 * Prints the type of the file associated to the given post
 *
 * @see get_the_file_type
 *
 * @param int $post_id
 *
 * @return string|mixed
 */
function cuar_the_file_size($post_id = null, $human = true)
{
    echo cuar_get_the_file_size($post_id, $human);
}


/** Helper function to format file size */
function cuar_format_human_file_size($size)
{
    $factor = 1;
    $unit = __('bytes', 'cuar');

    if ($size >= 1024 * 1024 * 1024 * 1024)
    {
        $factor = 1024 * 1024 * 1024 * 1024;
        $unit = __('TB', 'cuar');
    }
    else if ($size >= 1024 * 1024 * 1024)
    {
        $factor = 1024 * 1024 * 1024;
        $unit = __('GB', 'cuar');
    }
    else if ($size >= 1024 * 1024)
    {
        $factor = 1024 * 1024;
        $unit = __('MB', 'cuar');
    }
    else if ($size >= 1024)
    {
        $factor = 1024;
        $unit = __('kB', 'cuar');
    }
    else
    {
        $unit = __('bytes', 'cuar');
    }

    return sprintf('%1$s %2$s', number_format($size / $factor, 2), $unit);
}

/**
 * @param array $args The arguments to pass to each cuar_create_private_file function call.
 *
 * ´cuar_bulk_create_private_files(array(
 *      array(
 *          'post_data' => (...),
 *          'owner'     => (...),
 *          'files'     => (...),
 *      ),
 *      array(
 *          'post_data' => (...),
 *          'owner'     => (...),
 *          'files'     => (...),
 *      ))
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

    foreach ($args as $a)
    {
        $res = cuar_create_private_file($a['post_data'], $a['owner'], $a['files']);
        if (is_wp_error($res))
        {
            $result['errors'][] = $res;
        }
        else
        {
            $result['created'][] = $res;
        }
    }

    return $result;
}

/**
 * @param array $post_data The same array you would give to wp_insert_post to create your post. No need to set the post
 *                         type, this will automatically be set.
 * @param array $owner     An array containing the owner description: type ('usr', 'grp', 'prj', 'rol', etc.) and IDs
 *                         of corresponding objects
 * @param array $files     An array containing the paths to the files to attache to the post object. Currently we only
 *                         support a single file.
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
 *          'type' => 'usr',
 *          'ids'  => array(1)
 *      ),
 *      array(
 *          '/path/to/file/on/server/the-file.txt'
 *      )
 * );´
 */
function cuar_create_private_file($post_data, $owner, $files)
{
    if (count($files) != 1)
    {
        return new WP_Error(0, 'cuar_create_private_file only support a single private file');
    }

    if ( !isset($owner['type']) || !isset($owner['ids']) || empty($owner['ids']))
    {
        return new WP_Error(0, 'cuar_create_private_file needs owner data to create the private file');
    }

    // Create the post object
    $post_data['post_type'] = 'cuar_private_file';
    $post_id = wp_insert_post($post_data);
    if (is_wp_error($post_id))
    {
        return $post_id;
    }

    // Assign the owner
    /** @var CUAR_PostOwnerAddOn $po_addon */
    $po_addon = cuar_addon('post-owner');
    $po_addon->save_post_owners($post_id, $owner['ids'], $owner['type']);

    // Attach the file
    /** @var CUAR_PrivateFileAddOn $pf_addon */
    $pf_addon = cuar_addon('private-files');
    foreach ($files as $file)
    {
        $upload_result = $pf_addon->handle_copy_private_file_from_local_folder($post_id, null, $owner, $file);

        if ($upload_result !== true)
        {
            wp_delete_post($post_id);

            return new WP_Error('upload_error', $upload_result['error']);
        }
    }

    return $post_id;
}