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

dojo.provide("phpr.Project.Modules");

dojo.declare("phpr.Project.Modules", null, {
    // Summary:
    //    Class for display a modules matrix.
    // Description:
    //    Render a table with the modules and if are part of the current project or not using a checkbox.
    _accessPermissions: false,
    _cellIndex:         0,
    _module:            null,
    _table:             null,
    _lastColor:         '',
    _relations:         [],

    constructor:function(module) {
        this._module = module;
    },

    createTable:function(data) {
        // Summary:
        //    Prepare the data and create the table
        // Description:
        //    Create the table only one time.
        //    Create the modules and the checkbox only one time.
        this._accessPermissions = data['accessPermissions'] || false;
        this._relations         = data['relationList'] || [];
        this._cellIndex         = 0;

        if (!this._table) {
            this._table                   = dojo.doc.createElement('table');
            this._table.id                = 'moduleRelationTable-' + this._module;
            this._table.className         = 'form';
            this._table.style.width       = 'auto';
            this._table.style.marginLeft  = '35px';
            this._table.style.marginRight = '35px';

            var row = this._table.insertRow(this._table.rows.length);

            // Module
            var label = document.createElement('label');
            var txt   = document.createTextNode(phpr.nls.get('Module'));
            label.appendChild(txt);
            var cell = this._createCell(row, label);
            cell.style.width = '150px';

            // Active
            var label = document.createElement('label');
            var txt   = document.createTextNode(phpr.nls.get('Active'));
            label.appendChild(txt);
            this._createCell(row, label);
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
        var rowId = 'trModuleFor' + data['id'] + '-' + this._module;

        // Create/Update field value
        var fieldValues = {
            id:       'moduleRelation[' + data['id'] + ']',
            type:     'checkbox',
            disabled: false,
            required: false,
            value:    (data['inProject']) ? true : false,
            hint:     ''
        };
        var widgetClass = new phpr.Field.CheckField(fieldValues, this._module);

        var row = dojo.byId(rowId);
        if (!row) {
            // Add a new row
            var row                   = this._table.insertRow(this._table.rows.length);
            row.id                    = rowId;
            row.style.backgroundColor = this._getColor();

            // Module display
            var txt  = document.createTextNode(data['label']);
            var cell = this._createCell(row, txt);

            // CheckBox
            var container = this._createDivNode(dijit.byId(widgetClass.fieldId).domNode);
            this._createCell(row, container);

            dojo.connect(dijit.byId(widgetClass.fieldId), 'onClick', function() {
                var response      = {};
                response.type     = 'warning';
                response.message  = phpr.nls.get('Module edited correctly but not saved yet.');
                response.message += '<br />';
                response.message += phpr.nls.get('For save the changes, click SAVE in the first tab.');
                new phpr.handleResponse('serverFeedback', response);
            });
        }

        // Update access
        if (this._accessPermissions) {
            dijit.byId(widgetClass.fieldId).setAttribute('disabled', false);
        } else {
            dijit.byId(widgetClass.fieldId).setAttribute('disabled', true);
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

    _createDivNode:function(child) {
        // Summary:
        //    Create a new div for display the buttons.
        // Description:
        //    If child is defined, append it to the div.
        var node = document.createElement('div');
        node.setAttribute('align', 'center');

        if (!child) {
            child = document.createTextNode("-\u00a0");
        }
        node.appendChild(child);

        return node;
    }
});
