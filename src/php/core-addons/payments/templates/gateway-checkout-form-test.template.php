<?php /** Template version: 3.0.0 */ ?>

<div class="row">
    <div class="form-group col-xs-12">
        <p><?php _e('The test gateway allows you to see how your website is behaving when the gateways return different results.', 'cuar'); ?></p>
    </div>
    <div class="form-group col-sm-8">
        <label for="gateway_test_expected_result"><?php _e('Result to return when calling the test gateway', 'cuar'); ?></label>
    </div>
    <div class="form-group col-sm-4">
        <select class="form-control" id="gateway_test_expected_result" name="gateway[test][expected_result]">
            <option><?php _e('Payment success', 'cuar'); ?></option>
            <option><?php _e('Payment rejected', 'cuar'); ?></option>
            <option><?php _e('Payment pending', 'cuar'); ?></option>
        </select>
    </div>
</div>