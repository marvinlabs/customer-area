<?php /** Template version: 1.0.0 */ ?>

<?php
$whats_new = array(
    '6.1' => array(
        'blog_post' => 'http://wp-customerarea.com' . __('/whats-new-in-wp-customer-area-6-1', 'cuar'),
        'codename'  => 'Jimi Hendrix',
        'tagline'   => __("WP Customer Area 6.1 focuses mainly on making administrator's life easier, introducing event logging and a better administration panel.",
            'cuar'),
        'blocks'    => array(
            array(
                'title' => __('Protect post types', 'cuar'),
                'text'  => __('We are introducing a new add-on which will allow protecting any kind of external post types. Posts created from third party plugins can be made private and assigned just like private pages or files.',
                    'cuar'),
                'more'  => 'http://wp-customerarea.com' . __('/downloads/wpca-protect-post-types', 'cuar')
            ),
            array(
                'title' => __('Shortcodes', 'cuar'),
                'text'  => __('we have added shortcodes to display the navigation menu, list protected content, etc. You will now be able to easily build custom pages with a list of the content you want to pull for the connected user.',
                    'cuar'),
                'more'  => 'http://wp-customerarea.com' . __('/documentation/shortcodes', 'cuar')
            ),
            array(
                'title' => __('Better admin panel', 'cuar'),
                'text'  => __('The main plugin menu has been improved a lot, it is now less cluttered and better organised. The content lists now feature powerful filters to help you find the content you are looking for faster.',
                    'cuar')
            ),
            array(
                'title' => __('Logging', 'cuar'),
                'text'  => __('When dealing with private content and secure areas, keeping track of events is crucial. We have added a module to track what is happening within your private area.',
                    'cuar')
            ),
            array(
                'title' => __('And more!', 'cuar'),
                'text'  => __('As with any new release, we constantly provide bug fixes. This update is no exception with no less than 23 issues corrected.',
                    'cuar'),
                'more'  => 'https://github.com/marvinlabs/customer-area/issues?q=milestone%3A6.1+is%3Aclosed'
            ),
        )
    ),
    '6.0' => array(
        'blog_post' => 'http://wp-customerarea.com' . __('/customer-area-is-dead-long-live-wp-customer-area/', 'cuar'),
        'codename'  => 'John Lennon',
        'tagline'   => __("WP Customer Area 6.0 is a major release which sees a lot of changes not only inside the plugin but also on the website.", 'cuar'),
        'blocks'    => array(
            array(
                'title' => __('Improved setup &amp; updates', 'cuar'),
                'text'  => __('We have implemented a new setup assistant that will make it even easier to install the plugin. Updates will be smoother too.',
                    'cuar')
            ),
            array(
                'title' => __('Better permissions', 'cuar'),
                'text'  => __('Some new permissions have been added to give you more control about what your users can do. On top of that, we have also improved the permissions screen to make it faster to set permissions.',
                    'cuar')
            ),
            array(
                'title' => __('And more!', 'cuar'),
                'text'  => __('As with any new release, we constantly provide bug fixes. This update is no exception with no less than 20 issues corrected.',
                    'cuar')
            ),
        )
    )
);
?>

<?php foreach ($whats_new as $ver => $desc) : ?>
    <h3><?php printf(__("New in %s ~ <em>%s</em>", 'cuar'), $ver, $desc['codename']); ?></h3>
    <h4><?php echo $desc['tagline']; ?></h4>
    <div class="cuar-whatsnew-boxes">
        <?php $i = 0;
        foreach ($desc['blocks'] as $item) : ?>
            <div class="cuar-whatsnew-box">
                <h3><?php echo $item['title']; ?></h3>

                <p><?php echo $item['text']; ?></p>
                <?php if ( !empty($item['more'])) : ?>
                    <p><a href="<?php echo $item['more']; ?>"><?php _e('Learn more', 'cuar'); ?></a></p>
                <?php endif; ?>
            </div>
            <?php
            $i++;
            if ($i > 2)
            {
                echo '<div class="clearfix">&nbsp;</div>';
                $i = 0;
            }
        endforeach; ?>
        <p class="clearfix">&nbsp;</p>
    </div>
<?php endforeach; ?>
