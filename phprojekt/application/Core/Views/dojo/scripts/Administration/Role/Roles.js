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
 * @subpackage Project
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @version    Release: @package_version@
 * @author     Gustavo Solt <gustavo.solt@mayflower.de>
 */

dojo.provide("phpr.Role.Roles");

dojo.declare("phpr.Role.Roles", null, {
    // Summary:
    //    Class for display a role matrix.
    // Description:
    //    Render a table with the roles and if are allowed or not using a checkbox.
    _cellIndex: 0,
    _module:    null,
    _table:     null,
    _lastColor: '',
    _relations: [],
    _rights:            [
        {id: 'checkReadAccess',   name: 'Read'},
        {id: 'checkWriteAccess',  name: 'Write'},
        {id: 'checkCreateAccess', name: 'Create'},
        {id: 'checkAdminAccess',  name: 'Admin'}
    ],

    constructor:function(module) {
        this._module = module;
    },

    createTable:function(data) {
        // Summary:
        //    Prepare the data and create the table
        // Description:
        //    Create the table only one time.
        //    Create the modules and the checkbox only one time.
        this._relations = data['relationList'] || [];
        this._cellIndex = 0;

        if (!this._table) {
            this._table                   = dojo.doc.createElement('table');
            this._table.id                = 'roleModuleRelationTable-' + this._module;
            this._table.className         = 'form';
            this._table.style.width       = 'auto';
            this._table.style.marginRight = '35px';

            var row = this._table.insertRow(this._table.rows.length);

            // Module
            var label = document.createElement('label');
            var txt   = document.createTextNode(phpr.nls.get('Module'));
            label.appendChild(txt);
            var cell = this._createCell(row, label);
            cell.style.width = '150px';

            // Rights
            var container = this._createContainer();
            for (var i = 0; i < this._rights.length; i++) {
                var label = document.createElement('label');
                var txt =   document.createTextNode(phpr.nls.get(this._rights[i].name));
                label.appendChild(txt);

                var node = this._createDivNode(label);
                container.appendChild(node);
            }
            var cell = this._createCell(row, container);
            cell.style.width = '300px';
        }

        // Modules rows
        this._addModuleRows();
    },

    getTable:function() {
        // Summary:
        //    Return the current table.
        return this._table;
    },

    /************* Private functions *************/

    _addModuleRows:function() {
        // Summary:
        //    Create/Update all the module-rows with the new values.
        // Description:
        //    If a row-user don't exists, add it.
        for (var id in this._relations) {
            this._addModuleRow(this._relations[id]);
        }
    },

    _addModuleRow:function(data) {
        // Summary:
        //    Create/Update an module-row with the new values.
        // Description:
        //    If a row-user don't exists, add it.
        //    If the value was changed, update the widget.
        var roleId = data['id'];
        var rowId  = 'trRoleModuleFor' + roleId + '-' + this._module;

        // Get new values
        var value = {};
        for (var i = 0; i < this._rights.length; i++) {
            var key    = this._rights[i].name.toLowerCase();
            value[key] = (data[key]) ? 1 : 0;
        }
        var jsonValue = dojo.toJson(value);

        // Create/Update module field value
        var hiddenFieldValues = {
            id:       'dataAccess[' + roleId + ']',
            type:     'hidden',
            disabled: false,
            required: false,
            value:    jsonValue,
            hint:     ''
        };
        var displayWidgetClass = new phpr.Field.HiddenField(hiddenFieldValues, this._module);

        // Create/Update checkbox field value
        var widgetClass = [];
        for (var i = 0; i < this._rights.length; i++) {
            var key   = this._rights[i].name.toLowerCase();
            var value = (data[key]) ? 1 : 0;
            var fieldValues = {
                id:       this._rights[i].id + '[' + roleId + ']',
                type:     'checkbox',
                disabled: false,
                required: false,
                value:    value,
                hint:     ''
            };
            widgetClass[i] = new phpr.Field.CheckField(fieldValues, this._module);
        }

        var row = dojo.byId(rowId);
        if (!row) {
            // Add a new row
            var row                   = this._table.insertRow(this._table.rows.length);
            row.id                    = rowId;
            row.style.backgroundColor = this._getColor();

            // Module display
            var cell = this._createCell(row, dijit.byId(displayWidgetClass.fieldId).domNode);
            var txt = document.createTextNode(data['label']);
            cell.appendChild(txt);

            // CheckBox
            var container = this._createContainer();
            for (var i = 0; i < this._rights.length; i++) {
                var checkWidget = dijit.byId(widgetClass[i].fieldId);
                var node        = this._createDivNode(checkWidget.domNode);
                container.appendChild(node);
                dojo.connect(checkWidget, 'onClick', dojo.hitch(this, function(e) {
                    var widget = dijit.byId('dataAccess[' + roleId + ']-' + this._module);
                    var data   = dojo.fromJson(widget.get('value'));

                    var accessRegExp = /^check(.*)Access(.*)$/;
                    var match        = accessRegExp.exec(e.target.id);
                    var access       = match[1].toLowerCase();
                    if (dijit.byId(e.target.id).checked) {
                        data[access] = 1;
                    } else {
                        data[access] = 0;
                    }
                    widget.set('value', dojo.toJson(data));
                }));
            }
            this._createCell(row, container);
        }
    },

    _getColor:function() {
        // Summary:
        //    Switch between 2 colors.
        if (this._lastColor == '#F2F5F9') {
            this._lastColor = '#FFF';
        } else {
            this._lastColor = '#F2F5F9';
        }

        return this._lastColor;
    },

    _createCell:function(row, child) {
        // Summary:
        //    Create a new cell.
        // Description:
        //    If child is defined, append it to the cell.
        var cell = row.insertCell(this._cellIndex);
        this._cellIndex++;
        if (this._cellIndex == 2) {
            this._cellIndex = 0;
        }
        if (child) {
            cell.appendChild(child);
        }

        return cell;
    },

    _createContainer:function() {
        // Summary:
        //    Create a new div.
        return document.createElement('div');
    },

    _createDivNode:function(child) {
        // Summary:
        //    Create a new div for display the buttons.
        // Description:
        //    If child is defined, append it to the div.
        var node = document.createElement('div');
        node.setAttribute('align', 'center');
        node.className = 'accessTableDiv';
        node.appendChild(child);

        return node;
    }
});
