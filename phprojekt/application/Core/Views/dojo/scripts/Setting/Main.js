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

dojo.provide("phpr.Setting.Main");

dojo.declare("phpr.Setting.Main", phpr.Core.Main, {
    constructor:function() {
        this.module = "Setting";
        this.loadFunctions(this.module);

        this.gridWidget = phpr.Setting.Grid;
        this.formWidget = phpr.Setting.Form;
    },

    getSummary:function() {
        return '<b>' + phpr.nls.get('Setting') + '</b>'
            + '<br /><br />'
            + phpr.nls.get('This module is for the user to set and change specific configuration parameters of '
            + 'his/her profile.')
            + '<br /><br />'
            + phpr.nls.get('Please choose one of the tabs of above.');
    },

    getSystemModules:function() {
        return new Array();
    },

    defineModules:function(module) {
        phpr.module       = this.module;
        phpr.submodule    = module;
        phpr.parentmodule = 'Setting';
    }
});
