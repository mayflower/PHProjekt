<?php
/**
 * Phprojekt own dispatcher.
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage Core
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

/**
 * Phprojekt own dispatcher.
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage Core
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_Dispatcher extends Zend_Controller_Dispatcher_Standard
{
    /**
     * Formats a string from a URI into a PHP-friendly name.
     *
     * By default, replaces words separated by the word separator character(s) with camelCaps.
     * If $isAction is false, it also preserves replaces words separated by the path
     * separation character with an underscore, making the following word Title cased.
     * All non-alphanumeric characters are removed.
     * The function works with calls like jsonSearch instead of jsonsearch.
     *
     * @param string  $unformatted String.
     * @param boolean $isAction    Defaults to false.
     *
     * @return string
     */
    protected function _formatName($unformatted, $isAction = false)
    {
        // Preserve directories
        if (!$isAction) {
            $segments = explode($this->getPathDelimiter(), $unformatted);
        } else {
            $segments = (array) $unformatted;
        }

        foreach ($segments as $segment) {
            $segment = str_replace($this->getWordDelimiter(), ' ', $segment);
            $segment = preg_replace('/[^a-z0-9 ]/', '', $segment);
        }

        return implode('_', $segments);
    }
}
