<?php /** Template version: 1.0.0 */ ?>

<?php /** @var array $steps */ ?>
<?php /** @var bool $current_step */ ?>

<ul class="cuar-wizard-progress-indicator">
    <?php foreach ($steps as $i => $step) :
        $extra_step_class = '';
        if ($i <= $current_step) $extra_step_class = 'cuar-completed';
        ?>
        <li class="cuar-wizard-step <?php echo $extra_step_class; ?>">
            <span class="cuar-bubble"></span> <?php echo $step['label']; ?>
        </li>
    <?php endforeach; ?>
</ul>
