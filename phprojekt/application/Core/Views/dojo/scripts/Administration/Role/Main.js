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
 * @version    Release: @package_version@
 * @author     Gustavo Solt <gustavo.solt@mayflower.de>
 */

dojo.provide("phpr.Role.Main");

dojo.declare("phpr.Role.Main", phpr.Core.Main, {
    constructor:function() {
        // Summary:
        //    Create a new instance of the module.
        this._module = 'Role';

        this._loadFunctions();

        this._gridWidget = phpr.Role.Grid;
        this._formWidget = phpr.Role.Form;
    },

    /************* Private functions *************/

    _customSetNavigationButtons:function() {
        // Summary:
        //     Called after the submodules are created.
        //     Is used for extend the navigation routine.
        this._setNewEntry();
    }
});
