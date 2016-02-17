<?php

/*  Copyright 2015 MarvinLabs (contact@marvinlabs.com) */

class CUAR_PaymentStatus
{
    public static $STATUS_PENDING = 'pending';
    public static $STATUS_COMPLETE = 'publish';
    public static $STATUS_REFUNDED = 'refunded';
    public static $STATUS_FAILED = 'failed';
    public static $STATUS_ABANDONED = 'abandoned';

    /**
     * Retrieves all available statuses for payments.
     *
     * @return array $payment_status All the available payment statuses
     */
    public static function get_payment_statuses()
    {
        $payment_statuses = array(
            self::$STATUS_PENDING   => __('Pending', 'cuar'),
            self::$STATUS_COMPLETE  => __('Complete', 'cuar'),
            self::$STATUS_REFUNDED  => __('Refunded', 'cuar'),
            self::$STATUS_FAILED    => __('Failed', 'cuar'),
            self::$STATUS_ABANDONED => __('Abandoned', 'cuar'),
        );

        return apply_filters('cuar/core/payments/statuses', $payment_statuses);
    }

    /**
     * Retrieves keys for all available statuses for payments
     *
     * @return array $payment_status All the available payment statuses
     */
    public static function get_payment_status_keys()
    {
        $statuses = array_keys(self::get_payment_statuses());
        asort($statuses);

        return array_values($statuses);
    }
}