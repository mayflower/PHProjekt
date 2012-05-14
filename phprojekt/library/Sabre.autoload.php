<?php
/**
 * Phprojekt's autoloader for SabreDav Library
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
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @version    Release: 6.1.0
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 */

/**
 * Phprojekt's autoloader for SabreDav Library
 *
 * This is mostly a copy of the included Sabre.autoload.php. (Look inside the SabreDav folder)
 * We need this because we can't call spl_autoload_register for the autoloader. Our catch-all P6 autoloader will try to
 * load the file before SabreDav's autoloader get's a try. This would work, but it generates evil-looking warnings en
 * masse, so we do this instead.
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @version    Release: 6.1.0
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 */
function Sabre_autoload($className) {
    if(strpos($className,'Sabre_')===0) {
        include dirname(__FILE__) . '/SabreDAV/lib/Sabre/' . str_replace('_','/',substr($className,6)) . '.php';
    }
}

Zend_Loader_Autoloader::getInstance()->pushautoloader('Sabre_autoload', 'Sabre_');

