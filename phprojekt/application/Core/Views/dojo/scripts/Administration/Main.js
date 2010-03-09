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
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

dojo.provide("phpr.Administration.Main");

dojo.declare("phpr.Administration.Main", phpr.Core.Main, {
    constructor:function() {
        this.module = "Administration";
        this.loadFunctions(this.module);

        this.gridWidget = phpr.Administration.Grid;
        this.formWidget = phpr.Administration.Form;
    },

    getSummary:function() {
        return '<b>' + phpr.nls.get('Administration') + '</b>'
            + '<br /><br />'
            + phpr.nls.get('Here can be configured general settings of the site that affects all the users.')
            + '<br /><br />'
            + phpr.nls.get('Please choose one of the tabs of above.');
    },

    defineModules:function(module) {
        phpr.module       = this.module;
        phpr.submodule    = module;
        phpr.parentmodule = 'Administration';
    },

    updateCacheData:function() {
        phpr.DataStore.deleteAllCache();
        phpr.Tree.updateData();
        phpr.treeLastProjectSelected = null;
        if (this.form) {
            this.form.updateData();
        }
    }
});
