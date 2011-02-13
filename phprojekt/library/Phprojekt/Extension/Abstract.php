<?php
/**
 * PHProjekt Extension API
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
 * @since      File available since Release 6.1
 * @version    Release: @package_version@
 * @author     David Soria Parra <david.soria_parra@mayflower.de>
 */

/**
 * Phprojekt Class for initialize the Zend Framework.
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage Core
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @version    Release: @package_version@
 * @author     David Soria Parra <david.soria_parra@mayflower.de>
 */
abstract class PHProjekt_Extension_Abstract {
    public function init() {
    }

    /**
     * Return the extension api version to use. Current is 6.0.1.
     *
     * @return string The version.
     */
    public abstract function getVersion();

    /**
     * This function has to be implemented if the module needs to upgrade the
     * database between minor changes.
     *
     * The returned object must be a subclass of Phprojekt_Migration_Abstract or
     * null. If it is null, Phprojekt will assume that this module never needs
     * to upgrade.
     */
    public function getMigration()
    {
        return null;
    }
}
