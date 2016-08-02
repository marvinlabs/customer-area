<?php /**
 * Template version: 3.0.0
 * Template zone: frontend
 *
 * -= 3.0.0 =-
 * - Initial version
 */ ?>

<?php /** @var string $title */ ?>
<?php /** @var string $message */ ?>
<?php /** @var array $actions */ ?>

<div class="panel panel-default">
    <div class="panel-heading">
        <span class="panel-title"><?php echo $title; ?></span>
    </div>
    <div class="panel-menu">
        <?php foreach ($actions as $a) : ?>
            <a href="<?php echo esc_url($a['url']); ?>" type="button" class="btn btn-sm btn-default">
                <?php if (!empty($a['icon'])) : ?>
                    <span class="<?php echo $a['icon']; ?> pr-sm"></span>&nbsp;
                <?php endif; ?>
                <?php echo $a['title']; ?>
            </a>
        <?php endforeach; ?>
    </div>
    <div class="panel-body">
        <?php echo wpautop($message); ?>
    </div>
</div>


