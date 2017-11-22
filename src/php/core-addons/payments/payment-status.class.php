<?php

/*  Copyright 2015 MarvinLabs (contact@marvinlabs.com) */

class CUAR_PaymentStatus
{
    public static $STATUS_PENDING = 'pending';
    public static $STATUS_COMPLETE = 'publish';
    public static $STATUS_REFUNDED = 'refunded';
    public static $STATUS_FAILED = 'failed';
    public static $STATUS_ABANDONED = 'abandoned';
    public static $STATUS_DRAFT = 'draft';

    /**
     * Retrieves all available statuses for payments.
     *
     * @return array $payment_status All the available payment statuses
     */
    public static function get_payment_statuses()
    {
        $payment_statuses = array(
            self::$STATUS_COMPLETE  => __('Complete', 'cuar'),
            self::$STATUS_PENDING   => __('Pending', 'cuar'),
            self::$STATUS_REFUNDED  => __('Refunded', 'cuar'),
            self::$STATUS_ABANDONED => __('Abandoned', 'cuar'),
            self::$STATUS_FAILED    => __('Failed', 'cuar'),
            self::$STATUS_DRAFT     => __('Draft', 'cuar'),
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

    public static function register_statuses()
    {
        $statuses = self::get_payment_statuses();

        foreach ($statuses as $id => $label) {
            if (in_array($id, array('publish', 'draft', 'pending'), true)) {
                continue;
            }

            register_post_status($id, array(
                'label'                     => $label,
                'public'                    => true,
                'internal'                  => true,
                'exclude_from_search'       => true,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
            ));
        }
    }
}