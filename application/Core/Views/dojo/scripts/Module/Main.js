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
 * @copyright  2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    CVS: $Id:
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

dojo.provide("phpr.Module.Main");

dojo.declare("phpr.Module.Main", phpr.Core.Main, {
    constructor: function(){
        this.module = "Module";
        this.loadFunctions(this.module);

        this.gridWidget = phpr.Module.Grid;
        this.formWidget = phpr.Module.Form;
        this.treeWidget = phpr.Module.Tree;

        dojo.subscribe("Module.openDialog", this, "openDialog");
        dojo.subscribe("Module.submitForm", this, "submitForm");
    },

    customSetSubmoduleNavigation:function() {
        this.setNewEntry();
    },

    openDialog: function() {
        this.form.openDialog();
    },

    submitForm: function() {
        this.form.submitForm();
    },

    updateCacheData:function() {
        if (this.tree) {
            this.tree.updateData();
        }
        if (this.grid) {
            this.grid.updateData();
        }
        if (this.form) {
            this.form.updateData();
        }
    }
});
