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

dojo.provide("phpr.Administration.Main");

dojo.declare("phpr.Administration.Main", phpr.Core.Main, {
    constructor:function() {
        // Summary:
        //    Create a new instance of the module.
        this._module = 'Administration';

        this._loadFunctions();

        this._gridWidget = phpr.Administration.Grid;
        this._formWidget = phpr.Administration.Form;
    },

    updateCacheData:function() {
        // Summary:
        //    Forces every widget of the page to update its data, by deleting its cache.
        phpr.DataStore.deleteAllCache();
        phpr.Tree.updateData();
        phpr.treeLastProjectSelected = null;
        if (this._forms[phpr.module + '-' + phpr.submodule]) {
            this._forms[phpr.module + '-' + phpr.submodule].updateData();
        }
    },

    /************* Private functions *************/

    _getSummary:function() {
        // Summary:
        //    Returns a text info of the Parent module.
        return '<b>' + phpr.nls.get('Administration') + '</b>'
            + '<br /><br />'
            + phpr.nls.get('Here can be configured general settings of the site that affects all the users.')
            + '<br /><br />'
            + phpr.nls.get('Please choose one of the tabs of above.');
    },

    _defineModules:function(module) {
        // Summary:
        //    Set the global vars for this module.
        phpr.module       = this._module;
        phpr.submodule    = module;
        phpr.parentmodule = 'Administration';
    }
});
