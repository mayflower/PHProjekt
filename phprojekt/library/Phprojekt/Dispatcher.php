<?php
/**
 * Phprojekt own dispatcher to handle customized controller names
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 2.1 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * Phprojekt own dispatcher to handle customized controller names.
 * Customized controllers have the name {MODULE}_Customized_{FOO}Controller
 *
 * @todo not supported yet
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_Dispatcher extends Zend_Controller_Dispatcher_Standard
{
    /**
     * Formats a string from a URI into a PHP-friendly name.
     *
     * By default, replaces words separated by the word separator character(s)
     * with camelCaps. If $isAction is false, it also preserves replaces words
     * separated by the path separation character with an underscore, making
     * the following word Title cased. All non-alphanumeric characters are
     * removed.
     *
     * @param string $unformatted
     * @param boolean $isAction Defaults to false
     * @return string
     */
    protected function _formatName($unformatted, $isAction = false)
    {
        // preserve directories
        if (!$isAction) {
            $segments = explode($this->getPathDelimiter(), $unformatted);
        } else {
            $segments = (array) $unformatted;
        }

        foreach ($segments as $segment) {
            $segment        = str_replace($this->getWordDelimiter(), ' ', $segment);
            $segment        = preg_replace('/[^a-z0-9 ]/', '', $segment);
        }

        return implode('_', $segments);
    }
}
