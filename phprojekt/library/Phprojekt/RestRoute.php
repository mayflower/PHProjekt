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
 * @copyright  Copyright (c) 2012 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

/**
 * Extend Zend_Rest_Route to fit PHProjekt's module system.
 */
class Phprojekt_RestRoute extends Zend_Rest_Route
{
    /**
     * Check if the given controller is restful.
     *
     * This is overwritten because Zend_Rest_Route only allows a list of restful controllers on initialization.
     * To find out if a controller ist restful, we need to check it's class. So, instead of checking all Controllers on
     * Startup, we just overwrite this function to check it on demand.
     */
    protected function _checkRestfulController($moduleName, $controllerName)
    {
        $controllerName = ucfirst($moduleName) . '_' . ucfirst($controllerName) . 'Controller';
        if (!@class_exists($controllerName)) {
            return false;
        }
        $class = new ReflectionClass($controllerName);
        if ($class->isSubclassOf('Phprojekt_RestController')) {
            return true;
        }
        return false;
    }
}
