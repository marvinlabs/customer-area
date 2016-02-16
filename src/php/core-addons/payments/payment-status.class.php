<?php
/*  Copyright 2015 MarvinLabs (contact@marvinlabs.com) */

class CUAR_PaymentStatus 
{
    /**
     * Retrieves all available statuses for payments.
     *
     * @return array $payment_status All the available payment statuses
     */
    public static function get_payment_statuses() {
        $payment_statuses = array(
            'pending'   => __( 'Pending', 'cuar' ),
            'publish'   => __( 'Complete', 'cuar' ),
            'refunded'  => __( 'Refunded', 'cuar' ),
            'failed'    => __( 'Failed', 'cuar' ),
            'abandoned' => __( 'Abandoned', 'cuar' ),
            'revoked'   => __( 'Revoked', 'cuar' )
        );

        return apply_filters( 'cuar/core/payments/statuses', $payment_statuses );
    }

    /**
     * Retrieves keys for all available statuses for payments
     *
     * @return array $payment_status All the available payment statuses
     */
    public static function get_payment_status_keys() {
        $statuses = array_keys( self::get_payment_statuses() );
        asort( $statuses );

        return array_values( $statuses );
    }
}