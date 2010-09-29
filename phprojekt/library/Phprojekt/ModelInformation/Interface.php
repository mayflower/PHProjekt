<?php
/**
 * A generic interface to interact with ModelInformation.
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
 * @subpackage ModelInformation
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */

/**
 * A generic interface to interact with ModelInformation.
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage ModelInformation
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
interface Phprojekt_ModelInformation_Interface
{
    /**
     * Return an array of field definitions.
     *
     * @return array Array with all the fields definitions.
     */
     public function getFieldDefinition();

    /**
     * Return the type of one field.
     *
     * @param string $fieldName The name of the field to check.
     *
     * @return string Type of the field.
     */
    public function getType($fieldName);
}
