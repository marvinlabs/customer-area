<?php /** Template version: 1.0.0 */ ?>

<div class="cuar-installer-section cuar-setup-section cuar-setup-wizard-section">
    <h2 class="cuar-section-title">1. <?php _e("Setup pages and navigation menu", 'cuar'); ?></h2>

    <div class="clear"></div>

    <p class="cuar-instructions">
        <?php _e('WP Customer Area needs to create a few pages to host the private area. Additionally, each of those pages '
            . 'shows a navigation menu to the user. Let\'s create those items automatically!', 'cuar'); ?>
    </p>

    <div class="cuar-actions">
        <a href="#" class="button button-primary cuar-launch-setup-wizard"><?php _e('Create pages and menu', 'cuar'); ?></a>
        <span class="cuar-loading-indicator" style="display: none;"></span>
        <div class="cuar-response"></div>
    </div>
</div>

<div class="clear"></div>

<div class="cuar-installer-section cuar-setup-section cuar-permissions-section cuar-faded">
    <h2 class="cuar-section-title">2. <?php _e("Configure permissions", 'cuar'); ?></h2>

    <div class="clear"></div>

    <p class="cuar-instructions">
        <?php _e('Because you have your own security policy, WP Customer Area only sets permissions for the '
            . 'administrators. You should configure them for the other roles of your website', 'cuar'); ?>
    </p>

    <div class="cuar-actions">
        <a href="<?php echo admin_url('admin.php?page=cuar-settings&cuar_tab=cuar_capabilities'); ?>" class="button button-primary cuar-configure-permissions" target="_blank"><?php _e('Configure permissions', 'cuar'); ?></a>
        <a href="<?php _e('http://wp-customerarea.com/documentation/permissions-reference/', 'cuar'); ?>" class="button button-primary cuar-permissions-guide" target="_blank"><?php _e('Permissions reference guide', 'cuar'); ?></a>
    </div>
</div>

<div class="clear"></div>

<div class="cuar-installer-section cuar-setup-section cuar-status-section cuar-faded">
    <h2 class="cuar-section-title">3. <?php _e("Read our getting started guide", 'cuar'); ?></h2>

    <div class="clear"></div>

    <p class="cuar-instructions">
        <?php _e('WP Customer Area is very easy to use. To get you started, we have written a small tutorial teaching '
            . 'you how to quickly publish some private content for your users', 'cuar'); ?>
    </p>

    <div class="cuar-actions">
        <a href="<?php _e('http://wp-customerarea.com/documentation/getting-started/', 'cuar'); ?>" class="button button-primary cuar-getting-started-tutorial" target="_blank"><?php _e('Getting started tutorial', 'cuar'); ?></a>
    </div>
</div>

<div class="clear"></div>

<div class="cuar-installer-section cuar-setup-section cuar-readynow-section cuar-faded">
    <h2 class="cuar-section-title"><?php _e("Feeling ready?", 'cuar'); ?></h2>
    <p>
        <a href="<?php echo admin_url('admin.php?page=customer-area'); ?>" class="button button-primary cuar-lets-go"><?php _e("Let's go!", 'cuar'); ?></a>
    </p>
</div>

<!--suppress JSUnresolvedVariable -->
<script type="text/javascript">
    jQuery(document).ready(function($) {

        // Button to create the pages and navigation
        $('.cuar-launch-setup-wizard').click(function(event) {
            var button = $(this);
            var currentSection = button.closest('.cuar-setup-section');
            var loadingIndicator = button.siblings('.cuar-loading-indicator');

            button.attr('disabled', 'disabled');
            button.css('opacity', '0.5');
            loadingIndicator.fadeIn();

            // Ajax call to create pages and navigation menu
            var data = {
                'action': 'cuar_installer_create_pages_and_nav'
            };
            $.post(ajaxurl, data, function(response) {
                loadingIndicator.fadeOut();
                button.css('opacity', '1');

                var responseDiv = button.closest('.cuar-actions').children('.cuar-response');
                if (!response.success) {
                    responseDiv.html(response.error)
                        .removeClass('cuar-success')
                        .addClass('cuar-error');
                    button.removeAttr('disabled');
                } else {
                    // When done, enable the next step
                    currentSection.siblings('.cuar-setup-section').removeClass('cuar-faded');
                    currentSection.addClass('cuar-faded');
                    responseDiv.html(response.message)
                        .addClass('cuar-success')
                        .removeClass('cuar-error');
                }
            });

            // Don't follow the link
            event.preventDefault();
        });

        // Button to configure permissions
        $('.cuar-configure-permissions').click(function() {
            var data = {
                'action': 'cuar_mark_permissions_as_configured'
            };
            $.post(ajaxurl, data, function (response) {
            });
        });

    });
</script>