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
 * @category   PHProjekt
 * @package    Application
 * @subpackage Core
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Role.Main");

dojo.declare("phpr.Role.Main", phpr.Core.Main, {
    constructor:function() {
        this.module = "Role";
        this.loadFunctions(this.module);

        this.gridWidget = phpr.Role.Grid;
        this.formWidget = phpr.Role.Form;
    },

    customSetSubmoduleNavigation:function() {
        this.setNewEntry();
    }
});
