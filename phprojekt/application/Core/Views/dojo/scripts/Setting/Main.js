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

dojo.provide("phpr.Setting.Main");

dojo.declare("phpr.Setting.Main", phpr.Core.Main, {
    constructor:function() {
        // Summary:
        //    Create a new instance of the module.
        this._module = 'Setting';

        this._loadFunctions();

        this._gridWidget = phpr.Setting.Grid;
        this._formWidget = phpr.Setting.Form;
    },

    processActionFromUrlHash:function(data) {
        // Summary:
        //     Check the action params and run the correct function.
        //     Reload is the default, but each function can redefine it.
        if (data[0]) {
            dojo.publish(this._module + '.reload', [data[0]]);
        }
    },

    /************* Private functions *************/

    _getSystemModules:function() {
        // Summary:
        //    Return the modules that are like normal modules instead of just a form.
        return new Array();
    },

    _defineModules:function(module) {
        // Summary:
        //    Set the global vars for this module.
        phpr.module       = this._module;
        phpr.submodule    = module;
        phpr.parentmodule = 'Setting';
    },

    _getSummary:function() {
        // Summary:
        //    Returns a text info of the Parent module.
        return '<b>' + phpr.nls.get('Setting') + '</b>'
            + '<br /><br />'
            + phpr.nls.get('This module is for the user to set and change specific configuration parameters of '
            + 'his/her profile.')
            + '<br /><br />'
            + phpr.nls.get('Please choose one of the tabs of above.');
    }
});
