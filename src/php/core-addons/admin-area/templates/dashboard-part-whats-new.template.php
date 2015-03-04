<?php /** Template version: 1.0.0 */ ?>

<?php
$whats_new = array(
    array(
        'title' => __('Improved setup &amp; updates', 'cuar'),
        'text' => __('We have implemented a new setup assistant that will make it even easier to '
            . 'install the plugin. Updates will be smoother too.', 'cuar')
    ),
    array(
        'title' => __('Better permissions', 'cuar'),
        'text' => __('Some new permissions have been added to give you more control about what your '
            . 'users can do. On top of that, we have also improved the permissions screen to make it '
            . 'faster to set permissions.', 'cuar')
    ),
    array(
        'title' => __('Stability improvements', 'cuar'),
        'text' => __('As with any new release, we constantly provide bug fixes. This update is no exception '
            . 'with no less than 20 issues corrected.', 'cuar')
    ),
);
?>

<?php foreach ($whats_new as $item) : ?>
    <div class="cuar-whatsnew-box">
        <h3><?php echo $item['title']; ?></h3>
        <p><?php echo $item['text']; ?></p>
    </div>
<?php endforeach; ?>
