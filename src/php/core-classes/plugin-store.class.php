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


class CUAR_PluginStore implements CUAR_LicenseStore
{
    private $bypass_ssl = false;

    /**
     * CUAR_PluginStore constructor.
     *
     * @param bool $bypass_ssl
     */
    public function __construct($bypass_ssl)
    {
        $this->bypass_ssl = $bypass_ssl;
    }


    /**
     * Get the main store URL
     * @return string
     */
    public function get_store_url()
    {
        $protocol = $this->bypass_ssl ? 'http' : 'https';
        $domain = CUAR_DEBUG_LICENSING
            ? 'wp-customerarea.local'
            : 'wp-customerarea.com';

        return $protocol . '://' . $domain;
    }
}