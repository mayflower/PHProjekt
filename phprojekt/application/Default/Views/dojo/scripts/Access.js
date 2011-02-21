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
 * @subpackage Default
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @version    Release: @package_version@
 * @author     Gustavo Solt <gustavo.solt@mayflower.de>
 */

dojo.provide("phpr.Default.Access");

dojo.declare("phpr.Default.Access", null, {
    // Summary:
    //    Class for display an access matrix.
    // Description:
    //    Render a table with the users and their rights for the current item.
    //    Provide a form for add new users and edit or delete the current one.
    _accessPermissions: false,
    _cellIndex:         0,
    _currentUser:       0,
    _accessContent:     null,
    _module:            null,
    _table:             null,
    _lastColor:         '',
    _rights:            [
        {id: 'checkReadAccess',     name: 'Read'},
        {id: 'checkWriteAccess',    name: 'Write'},
        {id: 'checkAccessAccess',   name: 'Access'},
        {id: 'checkCreateAccess',   name: 'Create'},
        {id: 'checkCopyAccess',     name: 'Copy'},
        {id: 'checkDeleteAccess',   name: 'Delete'},
        {id: 'checkDownloadAccess', name: 'Download'},
        {id: 'checkAdminAccess',    name: 'Admin'}
    ],
    _users: [],

    constructor:function(module) {
        this._module = module;
    },

    createTable:function(data) {
        // Summary:
        //    Prepare the data and create the table
        // Description:
        //    Process the rights and the users,
        //    Create the table only one time.
        //    Create the user selectBox and the checkboxs only one time.
        //    Create/Update all the users rows with the new values.
        this._accessContent     = phpr.clone(data['accessContent']) || {};
        this._accessPermissions = data['accessPermissions'] || false;
        this._cellIndex         = 0;
        this._currentUser       = data['currentUser'] || 0;
        this._users             = [];

        if (data['userList']) {
            for (var i in data['userList']) {
                // Make an array with the users except the current one and the admin
                if (data['userList'][i].id != this._currentUser && data['userList'][i].id != 1) {
                    this._users.push({'id': data['userList'][i].id, 'name': data['userList'][i].display});
                }
                // Found the name of each user
                for (var j in this._accessContent) {
                    if (data['userList'][i].id == this._accessContent[j].userId) {
                        this._accessContent[j].userDisplay = data['userList'][i].display;
                        break;
                    }
                }
            }
        }

        if (!this._table) {
            this._table                   = dojo.doc.createElement("table");
            this._table.id                = 'accessTable-' + this._module;
            this._table.className         = "form";
            this._table.style.width       = 'auto';
            this._table.style.marginLeft  = '35px';
            this._table.style.marginRight = '35px';

            var row = this._table.insertRow(this._table.rows.length);

            // User
            var label = document.createElement('label');
            var txt   = document.createTextNode(phpr.nls.get('User'));
            label.appendChild(txt);
            this._createCell(row, label, 'accessUserDiv');

            // Rights
            var container = this._createContainer();
            for (var i = 0; i < this._rights.length; i++) {
                var label = document.createElement('label');
                var txt =   document.createTextNode(phpr.nls.get(this._rights[i].name));
                label.appendChild(txt);

                var node = this._createDivNode(label);
                container.appendChild(node);
            }
            this._createCell(row, container, 'accessTableContent');

            // Action
            var accessPermissions = (this._users.length > 0) ? this._accessPermissions : false
            if (accessPermissions) {
                var label = document.createElement('label');
                var txt   = document.createTextNode(phpr.nls.get('Action'));
                label.appendChild(txt);

                var node = this._createDivNode(label);
            } else {
                var node = document.createTextNode("\u00a0");
            }
            this._createCell(row, node);
        }

        // First row
        this._addSelectRow();

        // User rows
        this._addUserRows();
    },

    getTable:function() {
        // Summary:
        //    Return the current table.
        return this._table;
    },

    /************* Private functions *************/

    _addSelectRow:function() {
        // Summary:
        //    Create the user selectBox and the checkboxs only one time.
        // Description:
        //    If the user have admin accees,
        //    show the user selectBox and the checkboxs for add new rights
        //    or edit them
        var rowId = 'trDataAccess-' + this._module;
        var row   = dojo.byId(rowId);

        if (this._accessPermissions) {
            // Create and set the User widget
            var fieldValues = {
                type:     'selectbox',
                id:       'dataAccessAdd',
                disabled: false,
                required: false,
                value:    '',
                range:    this._users,
                hint:     ''
            };
            var widgetClass = new phpr.Field.SelectField(fieldValues, this._module);

            if (!row) {
                // Have access but the row don't exists => Create it
                var row = this._table.insertRow(1);
                row.id  = rowId;

                // User Select
                this._createCell(row, dijit.byId(widgetClass.fieldId).domNode, 'accessUserDiv');

                // Rights
                var fieldValues = {
                    type:     'checkbox',
                    disabled: false,
                    required: false,
                    value:    '',
                    hint:     ''
                };
                var container = this._createContainer();
                for (var i = 0; i < this._rights.length; i++) {
                    fieldValues['id'] = this._rights[i].id + 'Add';

                    var widgetClass = new phpr.Field.CheckField(fieldValues, this._module);

                    var node = this._createDivNode(dijit.byId(widgetClass.fieldId).domNode);
                    container.appendChild(node);

                    // Add event for checkAllAccess only
                    if (this._rights[i].id == 'checkAdminAccess') {
                        dojo.connect(dijit.byId(widgetClass.fieldId), "onClick", dojo.hitch(this, "_checkAllAccess"));
                    }
                }
                this._createCell(row, container, 'accessTableContent');

                // Action
                if (this._accessPermissions && this._users.length > 0) {
                    var params = {
                        id:        'newAccess-' + this._module,
                        label:     '',
                        iconClass: 'add',
                        alt:       phpr.nls.get('Add'),
                        baseClass: 'dijitButton, smallIcon'
                    };
                    var addButton = new dijit.form.Button(params);
                    var container = this._createDivNode(addButton.domNode);
                    dojo.connect(addButton, "onClick", dojo.hitch(this, '_newUserRow'));
                } else {
                    var container = this._createDivNode(document.createTextNode("\u00a0"));
                }
                this._createCell(row, container);
            } else {
                // Have access but the row is hidden => Show it
                row.style.display = (dojo.isIE) ? 'block' : 'table-row';
            }
        } else {
            if (row) {
                // Don't have access but the row exists => Hidde it
                row.style.display = 'none';
            }
        }
    },

    _addUserRows:function() {
        // Summary:
        //    Create/Update all the user-rows with the new values.
        // Description:
        //    Process the data and add/edit/delete the users rows.
        //    If a row-user don't exists, add it.
        //    If a row-user is in garbage and in the new data, restore it and update the values.
        //    If a row-user is not in the data anymore, delete it. (Move to garbage)
        var toKeep = new Array();

        for (var id in this._accessContent) {
            if (this._accessContent[id]['userDisplay']) {
                var isCurrentUser = (id == 'currentUser');
                var userId        = isCurrentUser ? 'currentUser' : this._accessContent[id]['userId'];
                if (userId == 1 && this._currentUser != 1) {
                    continue;
                }

                var rowId = 'trAccessFor' + userId + '-' + this._module;
                var tr    = dojo.byId(rowId);
                if (tr && tr.parentNode.id == 'garbage') {
                    // Restore tr into tbody
                    dojo.place(tr, this._table.firstChild, "last");
                }
                this._addUserRow(userId, isCurrentUser);
                toKeep[userId] = 1;
            }
        }

        var self = this;
        dojo.query('.accessRow', this._table).forEach(function(ele) {
            var userId = ele.getAttribute('internalId');
            if (!toKeep[userId]) {
                self._removeUserRow(userId);
            }
        });

        // Fix row colors
        this._fixRowColors();

        // Delete vars
        toKeep = [];
        self   = null;
    },

    _addUserRow:function(userId, isCurrentUser) {
        // Summary:
        //    Create/Update an user-row with the new values.
        // Description:
        //    If a row-user don't exists, add it.
        //    If the value was changed, update the widget and re-draw the row display only
        var rowId             = 'trAccessFor' + userId + '-' + this._module;
        var containerId       = 'tdAccessFor' + userId + '-' + this._module;
        var containerButtonId = 'tdAccessDeleteButtonFor' + userId + '-' + this._module;
        var buttonId          = 'accessDeleteButtonFor' + userId + '-' + this._module;

        // Get new values
        var value = {};
        for (var i = 0; i < this._rights.length; i++) {
            var key    = this._rights[i].name.toLowerCase();
            value[key] = (this._accessContent[userId][key]) ? 1 : 0;
        }
        var jsonValue = dojo.toJson(value);

        // Check if there is any change
        var updateDisplay = true;
        var field         = dijit.byId('dataAccess'+ '[' + userId + ']' + '-' + this._module);
        if (field) {
            if (field.get('value') == jsonValue) {
                updateDisplay = false;
            }
        }

        // Create/Update field value
        var hiddenFieldValues = {
            id:       'dataAccess'+ '[' + userId + ']',
            type:     'hidden',
            disabled: (!this._accessPermissions) ? true : false,
            required: false,
            value:    jsonValue,
            hint:     ''
        };
        var displayWidgetClass = new phpr.Field.HiddenField(hiddenFieldValues, this._module);

        var row = dojo.byId(rowId);
        if (!row) {
            // Add a new row
            var row                   = this._table.insertRow(this._table.rows.length);
            row.id                    = rowId;
            row.className             = 'accessRow';
            row.style.backgroundColor = this._getColor();
            row.setAttribute('internalId', userId);

            // User display
            var cell = this._createCell(row, dijit.byId(displayWidgetClass.fieldId).domNode, 'accessUserDiv');

            var txt = document.createTextNode(this._accessContent[userId]['userDisplay']);
            if (isCurrentUser) {
                var bold = document.createElement('b');
                bold.appendChild(txt);
                cell.appendChild(bold);
            } else {
                cell.appendChild(txt);
            }

            // Rights
            var containerDiv = this._createContainer();
            containerDiv.id  = containerId;
            this._createCell(row, containerDiv, 'accessTableContent');

            // Action
            var containerButtonDiv = this._createContainer();
            containerButtonDiv.id  = containerButtonId;
            this._createCell(row, containerButtonDiv);
        } else {
            var containerDiv       = dojo.byId(containerId);
            var containerButtonDiv = dojo.byId(containerButtonId);
        }

        // Fill the rights display
        if (updateDisplay) {
            dojo.empty(containerDiv);
            for (var i = 0; i < this._rights.length; i++) {
                var value = (this._accessContent[userId][this._rights[i].name.toLowerCase()]) ? 1 : 0;
                if (value) {
                    var node = this._createDivNode(null, 'accessTableChecked');
                } else {
                    var node = this._createDivNode();
                }
                containerDiv.appendChild(node);
            }
        }

        // Check the permissions for the action button
        if (isCurrentUser) {
            // Current user can't be deleted => Show an empty div
            dojo.empty(containerButtonDiv);
            var container = this._createDivNode(document.createTextNode("\u00a0"));
            containerButtonDiv.appendChild(container);
        } else {
            var deleteButton = dijit.byId(buttonId);
            if (this._accessPermissions) {
                if (!deleteButton) {
                    // Have access but the button don't exist => Create it
                    var params = {
                        id:        buttonId,
                        label:     '',
                        iconClass: 'cross',
                        alt:       phpr.nls.get('Delete'),
                        baseClass: 'dijitButton, smallIcon'
                    };
                    var deleteButton = new dijit.form.Button(params);
                    dojo.connect(deleteButton, "onClick", dojo.hitch(this, function() {
                        this._removeUserRow(userId);
                        this._showMessage('delete');
                        this._fixRowColors();
                    }));
                    dojo.empty(containerButtonDiv);
                    var container = this._createDivNode(deleteButton.domNode);
                    containerButtonDiv.appendChild(container);
                } else {
                    // Have access and the button exist, maybe is hidden => Show it
                    deleteButton.domNode.style.display = 'inline-block';
                }
            } else {
                if (deleteButton) {
                    // Don't have access but the button don't exist => Hidde it
                    deleteButton.domNode.style.display = 'none';
                } else {
                    // Don't have access and the button don't exists => Show an empty div
                    dojo.empty(containerButtonDiv);
                    var container = this._createDivNode(document.createTextNode("\u00a0"));
                    containerButtonDiv.appendChild(container);
                }
            }
        }
    },

    _newUserRow:function() {
        // Summary:
        //    Add a new user-row with.
        // Description:
        //    If the user-row already exists in garbage, restore it.
        //    If the user-row don't exists, create it.
        //    Create or update the entry in the this._accessContent array.
        //    After add, move the scroll to the bottom for see the new row.
        //    On success or on error, show a message.
        var userId = dijit.byId('dataAccessAdd-' + this._module).get('value');
        var rowId  = 'trAccessFor' + userId + '-' + this._module;

        if (userId <= 0) {
            return;
        }

        var tr = dojo.byId(rowId);
        if (!tr || tr.parentNode.id == 'garbage') {
            // Add/Update the values into this._accessContent
            this._accessContent[userId] = {
                userId:      userId,
                userDisplay: dijit.byId('dataAccessAdd-' + this._module).get('displayedValue'),
                read:        (dijit.byId('checkReadAccessAdd-' + this._module).checked) ? true : false,
                write:       (dijit.byId('checkWriteAccessAdd-' + this._module).checked) ? true : false,
                access:      (dijit.byId('checkAccessAccessAdd-' + this._module).checked) ? true : false,
                create:      (dijit.byId('checkCreateAccessAdd-' + this._module).checked) ? true : false,
                copy:        (dijit.byId('checkCopyAccessAdd-' + this._module).checked) ? true : false,
                'delete':    (dijit.byId('checkDeleteAccessAdd-' + this._module).checked) ? true : false,
                download:    (dijit.byId('checkDownloadAccessAdd-' + this._module).checked) ? true : false,
                admin:       (dijit.byId('checkAdminAccessAdd-' + this._module).checked) ? true : false
            };

            if (tr) {
                // Move it into tbody
                dojo.place(tr, this._table.firstChild, "last");
            }
            // Add/Update user row
            this._addUserRow(userId, false);

            // Scroll to the bottom for see the new user
            var tabContainer = this._table.parentNode.parentNode;
            tabContainer.scrollTop = tabContainer.scrollHeight;

            // Show a warning about save
            this._showMessage('add');
        } else {
            // Show an error mesage
            this._showMessage('error');
        }
    },

    _removeUserRow:function(userId) {
        // Summary:
        //    Delete an user-row.
        // Description:
        //    Move the row to garbage and delete all the values to force a change in the next use.
        // Update value to force a change in the next use
        dijit.byId('dataAccess' + '[' + userId + ']' + '-' + this._module).set('value', null);

        // Move the row to garbage
        dojo.place('trAccessFor' + userId + '-' + this._module, "garbage");
    },

    _checkAllAccess:function(str) {
        // Summary:
        //    Select all the checkbox access.
        if (dijit.byId('checkAdminAccessAdd-' + this._module).checked) {
            dojo.query('.dijitCheckBoxInput', 'trDataAccess-' + this._module).forEach(function(ele) {
                dijit.byId(ele.id).set('checked', true);
            });
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

    _showMessage:function(type) {
        // Summary:
        //    Show a system message.
        var response  = {};

        switch(type) {
            case 'add':
                response.type     = 'warning';
                response.message  = phpr.nls.get('User added correctly but not saved yet.');
                response.message += '<br />';
                response.message += phpr.nls.get('For save the changes, click SAVE in the first tab.');
                break;
            case 'delete':
                response.type     = 'warning';
                response.message  = phpr.nls.get('User deleted correctly but not saved yet.');
                response.message += '<br />';
                response.message += phpr.nls.get('For save the changes, click SAVE in the first tab.');
                break;
            case 'error':
                response.type     = 'error';
                response.message  = phpr.nls.get('The user already exists in the list.');
                response.message += '<br />';
                response.message += phpr.nls.get('For change the rights, remove the user first.');
                break;
        }

        new phpr.handleResponse('serverFeedback', response);
    },

    _createCell:function(row, child, baseClass) {
        // Summary:
        //    Create a new cell.
        // Description:
        //    Add a class to the cell if baseClass is not null.
        //    If child is defined, append it to the cell.
        var cell = row.insertCell(this._cellIndex);
        this._cellIndex++;
        if (this._cellIndex == 3) {
            this._cellIndex = 0;
        }
        if (baseClass) {
            cell.className = baseClass;
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

    _createDivNode:function(child, baseClass) {
        // Summary:
        //    Create a new div for display the access.
        // Description:
        //    Add a class to the div if baseClass is not null.
        //    If child is defined, append it to the div.
        var node = document.createElement('div');
        node.setAttribute('align', 'center');

        if (baseClass) {
            node.className = 'accessTableDiv '+ baseClass;
        } else {
            node.className = 'accessTableDiv';
        }

        if (!child) {
            child = document.createTextNode("-\u00a0");
        }
        node.appendChild(child);

        return node;
    },

    _fixRowColors:function() {
        // Summary:
        //    After delete, update the rows background.
        var self        = this;
        this._lastColor = '#FFF';
        dojo.query('.accessRow', this._table).forEach(function(ele) {
            ele.style.backgroundColor = self._getColor();
        });
        self = null;
    }
});
