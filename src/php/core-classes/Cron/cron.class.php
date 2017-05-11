<?php

/*  Copyright 2013 MarvinLabs (contact@marvinlabs.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

class CUAR_Cron
{
    public function __construct()
    {
    }

    public function register_hooks()
    {
        add_filter('cron_schedules', array($this, 'add_schedules'));
        add_action('wp', array($this, 'schedule_events'));
    }

    /**
     * Registers new cron schedules
     *
     * @param array $schedules *
     *
     * @return array
     */
    public function add_schedules($schedules = array())
    {
        $schedules['weekly'] = array(
            'interval' => 604800,
            'display'  => __('Once weekly', 'cuar')
        );

        return $schedules;
    }

    /**
     * Schedules our events
     *
     * @return void
     */
    public function schedule_events()
    {
        $this->schedule_weekly_events();
        $this->schedule_daily_events();
        $this->schedule_hourly_events();
    }

    /**
     * Schedule weekly events
     *
     * @return void
     */
    private function schedule_weekly_events()
    {
        if ( !wp_next_scheduled('cuar/cron/events?schedule=weekly')) {
            wp_schedule_event(current_time('timestamp', true), 'weekly', 'cuar/cron/events?schedule=weekly');
        }
    }

    /**
     * Schedule daily events
     *
     * @return void
     */
    private function schedule_daily_events()
    {
        if ( !wp_next_scheduled('cuar/cron/events?schedule=daily')) {
            wp_schedule_event(current_time('timestamp', true), 'daily', 'cuar/cron/events?schedule=daily');
        }
    }

    /**
     * Schedule daily events
     *
     * @return void
     */
    private function schedule_hourly_events()
    {
        if ( !wp_next_scheduled('cuar/cron/events?schedule=hourly')) {
            wp_schedule_event(current_time('timestamp', true), 'hourly', 'cuar/cron/events?schedule=hourly');
        }
    }

}