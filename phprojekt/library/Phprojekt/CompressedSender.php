<?php
/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2011 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

/**
 * Compresses Data and send it to the cliend.
 *
 * <pre>
 *  This class is a wrapper for a method of sending compressed data to the client.
 * </pre>
 */

class Phprojekt_CompressedSender {
    /**
     * Compresses data and send it to the client
     *
     * @param String $data - The string to send.
     */
    public static function send($data = '')
    {
        if (!headers_sent()) {
            if (array_key_exists('HTTP_ACCEPT_ENCODING', $_SERVER)) {
                $HTTP_ACCEPT_ENCODING = $_SERVER['HTTP_ACCEPT_ENCODING'];
                if ( headers_sent() ) {
                    $encoding = false;
                } else if ( strpos($HTTP_ACCEPT_ENCODING, 'x-gzip') !== false ) {
                    $encoding = 'x-gzip';
                } else if ( strpos($HTTP_ACCEPT_ENCODING, 'gzip') !== false ) {
                    $encoding = 'gzip';
                } else {
                    $encoding = false;
                }

                // no need to waste resources in compressing very little data
                if (strlen($data) > 2048 && $encoding && function_exists('gzencode')) {
                    $data = gzencode($data, 5);
                    header('Content-Encoding: ' . $encoding);
                }
            }
            header('Content-Length: ' . strlen($data));
        }

        echo $data;
    }
}
