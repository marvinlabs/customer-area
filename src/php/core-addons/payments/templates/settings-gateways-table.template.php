<?php /** Template version: 3.0.0 */ ?>

<?php /** @var array $gateways */ ?>
<?php /** @var CUAR_Settings $settings */ ?>
<?php /** @var CUAR_PaymentGateway $gateway */ ?>

<div id="gateways_tabs" class="tab-container tab-vertical">
    <ul class="tab-wrapper">
        <?php foreach ($gateways as $gateway_id => $gateway) : ?>
            <li class="nav-tab"><a href="#gateway_tab_<?php echo esc_attr($gateway_id); ?>"><?php echo $gateway->get_name(); ?></a></li>
        <?php endforeach; ?>
    </ul>

    <?php foreach ($gateways as $gateway_id => $gateway) : ?>
        <div id="gateway_tab_<?php echo esc_attr($gateway_id); ?>">
            <table class="form-table">
                <tbody>
                <?php $gateway->print_settings($settings); ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
</div>

<script>
    jQuery(function ($) {
        $("#gateways_tabs").tabs();
    });
</script>