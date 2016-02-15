<?php /** Template version: 3.0.0 */ ?>

<?php /** @var CUAR_AddressesAddOn $ad_addon */ ?>
<?php /** @var array $address */ ?>
<?php /** @var bool $is_address_editable */ ?>

<?php if ($is_address_editable) : ?>

    <?php $ad_addon->print_address_editor($address, 'billing', '', array(), '', 'checkout'); ?>

<?php else: ?>

    <?php echo CUAR_AddressHelper::get_address_as_hidden_input($address, 'cuar_address'); ?>

    <?php $ad_addon->print_address($address, 'billing', '', 'checkout'); ?>

<?php endif; ?>
