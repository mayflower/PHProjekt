<?php
/**
 * Bootstrap file.
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
 * @category  PHProjekt
 * @package   Htdocs
 * @copyright Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license   LGPL v3 (See LICENSE file)
 * @link      http://www.phprojekt.com
 * @since     File available since Release 6.0
 * @version   Release: @package_version@
 * @author    David Soria Parra <david.soria_parra@mayflower.de>
 */

/**
* Section to use from configuration.php.
 */
define('PHPR_CONFIG_SECTION', 'production');

/**
 * Root path.
 */
define('PHPR_ROOT_PATH', realpath(dirname(__FILE__) . '/../'));

require_once PHPR_ROOT_PATH . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'Phprojekt.php';

Phprojekt::getInstance()->run();
