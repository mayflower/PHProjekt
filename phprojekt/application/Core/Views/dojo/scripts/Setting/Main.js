/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 2.1 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

dojo.provide("phpr.Setting.Main");

dojo.declare("phpr.Setting.Main", phpr.Core.Main, {
    constructor:function() {
        this.module = "Setting";
        this.loadFunctions(this.module);

        this.gridWidget = phpr.Setting.Grid;
        this.formWidget = phpr.Setting.Form;
        this.treeWidget = phpr.Setting.Tree;
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
    },

    processActionFromUrlHash:function(data) {
        if (data[0]) {
            dojo.publish(this.module + ".reload", [data[0]]);
        }
    }
});
