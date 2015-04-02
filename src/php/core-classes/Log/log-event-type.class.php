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

class CUAR_LogEventType extends CUAR_CustomTaxonomy
{
    /** @var string THe taxonomy name */
    public static $TAXONOMY = 'cuar_log_event_type';

    /**
     * Constructor
     *
     * @param $tax_id
     */
    public function __construct($tax_id)
    {
        parent::__construct(self::$TAXONOMY);
    }

    /**
     * Register custom taxonomies
     */
    public static function register_custom_types()
    {
        register_taxonomy(self::$TAXONOMY, CUAR_LogEvent::$POST_TYPE, array('public' => false));
    }

}