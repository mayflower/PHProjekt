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

dojo.provide("phpr.Role.Form");

dojo.declare("phpr.Role.Form", phpr.Core.Form, {
    // Roles
    _roleModuleRender: null,
    _roleModuleStore:  null,

    init:function(id, params) {
        // Summary:
        //    Init the form for a new render.
        this._roleModuleStore = null;

        this.inherited(arguments);
    },

    /************* Private functions *************/

    _constructor:function(module, subModules) {
        // Summary:
        //    Construct the form only one time.
        this.inherited(arguments);

        // Role vars
        this._roleModuleRender = new phpr.Role.Roles(this._module);
    },

    _initData:function() {
        // Summary:
        //    Init all the data before draw the form.
        // Get modules
        this._roleModuleStore = new phpr.Store.RoleModuleAccess(this._id);
        this._initDataArray.push({'store': this._roleModuleStore});
    },

    _setPermissions:function(data) {
        // Summary:
        //    Get the permission for the current user on the item.
        this._writePermissions  = true;
        this._deletePermissions = false;
        if (this._id > 1) {
            this._deletePermissions = true;
        }
        this._accessPermissions = true;
    },

    _addBasicFields:function() {
        // Summary:
        //    Add some special fields.
        // Description:
        //    Add a module-row table.
        var data = {
            relationList: this._roleModuleStore.getList()
        }
        this._roleModuleRender.createTable(data);

        var table = this._fieldTemplate.getTable(1);
        if (table.rows.length == 1) {
            var row = table.insertRow(table.rows.length);

            // Label
            var label = document.createElement('label');
            var txt   = document.createTextNode(phpr.nls.get('Access') + ' ');
            label.appendChild(txt);
            var cell = row.insertCell(0);
            cell.className = 'label';
            cell.appendChild(label);

            // Table
            var cell = row.insertCell(1);
            cell.setAttribute('colspan', 2);
            cell.style.width = '100%';
            cell.appendChild(this._roleModuleRender.getTable());
        }
    },

    _getFieldForDelete:function() {
        // Summary:
        //    Return an array of fields for delete.
        var fields = this.inherited(arguments);

        fields.push('checkReadAccess*');
        fields.push('checkWriteAccess*');
        fields.push('checkCreateAcces*');
        fields.push('checkAdminAccess*');

        return fields;
    }
});
