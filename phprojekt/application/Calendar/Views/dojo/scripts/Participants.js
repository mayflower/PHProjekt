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
 * @subpackage Calendar
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @version    Release: @package_version@
 * @author     Gustavo Solt <gustavo.solt@mayflower.de>
 */

dojo.provide("phpr.Calendar.Participants");

dojo.declare("phpr.Calendar.Participants", null, {
    // Summary:
    //    Class for display a participants matrix.
    // Description:
    //    Render a table with the participants for the current event.
    //    Provide a form for add new participants and edit or delete the current one.
    _accessPermissions: false,
    _cellIndex:         0,
    _currentUser:       0,
    _module:            null,
    _table:             null,
    _lastColor:        '',
    _relations:        [],
    _users:            [],

    constructor:function(module) {
        this._module = module;
    },

    createTable:function(data) {
        // Summary:
        //    Prepare the data and create the table
        // Description:
        //    Process the users,
        //    Create the table only one time.
        //    Create the user selectBox only one time.
        //    Create/Update all the users rows with the new values.
        this._accessPermissions = data['accessPermissions'] || false;
        this._cellIndex         = 0;
        this._currentUser       = data['currentUser'] || 0;
        this._relations         = [];
        this._users             = [];

        // Make an array with the users expect the current one
        if (data['userList']) {
            for (var i in data['userList']) {
                if (data['userList'][i].id != this._currentUser) {
                    this._users.push({'id': data['userList'][i].id, 'name': data['userList'][i].display});
                }
            }
        }

        // Make an array with the current participants
        if (data['participants']) {
            var temp = data['participants'].split(',');
            for (var i in temp) {
                if (temp[i] != this._currentUser) {
                    for (var j in data['userList']) {
                        if (data['userList'][j].id == temp[i]) {
                            var userName = data['userList'][j].display;
                            break;
                        }
                    }
                    //this._relations.push({'userId': temp[i], 'userName': userName});
                    this._relations[temp[i]] = userName;
                }
            }
        }

        if (!this._table) {
            this._table                   = dojo.doc.createElement('table');
            this._table.id                = 'participantTable-' + this._module;
            this._table.className         = 'form';
            this._table.style.width       = 'auto';
            this._table.style.marginLeft  = '35px';
            this._table.style.marginRight = '35px';

            var row = this._table.insertRow(this._table.rows.length);

            // Hidden field
            var cell           = this._createCell(row, this._createContainer());
            cell.style.display = 'none';

            // User
            var label = document.createElement('label');
            var txt   = document.createTextNode(phpr.nls.get('User'));
            label.appendChild(txt);
            var cell = this._createCell(row, label);
            cell.style.width = '310px';

            // Action
            var label = document.createElement('label');
            var txt   = document.createTextNode(phpr.nls.get('Action'));
            label.appendChild(txt);
            this._createCell(row, label);
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
        //    Create the user selectBox only one time.
        // Description:
        //    Show the user selectBox for add new participants or edit them.
        var rowId = 'trParticipantForAdd-' + this._module;
        var row   = dojo.byId(rowId);

        if (this._accessPermissions) {
            // Create and set the User widget
            var fieldValues = {
                type:     'selectbox',
                id:       'dataParticipantAdd',
                disabled: false,
                required: false,
                value:    '',
                range:    this._users,
                hint:     ''
            };
            var userWidgetClass = new phpr.Field.SelectField(fieldValues, this._module);

            if (!row) {
                // Have access but the row don't exists => Create it
                var row = this._table.insertRow(1);
                row.id  = rowId;

                // Hidden field
                var cell           = this._createCell(row, this._createContainer());
                cell.style.display = 'none';

                // User Select
                this._createCell(row, dijit.byId(userWidgetClass.fieldId).domNode);

                // Action
                if (this._accessPermissions && this._users.length > 0) {
                    var params = {
                        label:     '',
                        iconClass: 'add',
                        alt:       phpr.nls.get('Add'),
                        baseClass: 'dijitButton, smallIcon'
                    };
                    var addButton = new dijit.form.Button(params);
                    var container = this._createDivNode(addButton.domNode);
                    dojo.connect(addButton, 'onClick', dojo.hitch(this, '_newUserRow'));
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

        for (var userId in this._relations) {
            var isCurrentUser = (this._currentUser == userId) ? true : false;

            var rowId = 'trParticipantFor' + userId + '-' + this._module;
            var tr    = dojo.byId(rowId);
            if (tr && tr.parentNode.id == 'garbage') {
                // Restore tr into tbody
                dojo.place(tr, this._table.firstChild, 'last');
            }
            this._addUserRow(userId, isCurrentUser);
            toKeep[userId] = 1;
        }

        var self = this;
        dojo.query('.participantRow', this._table).forEach(function(ele) {
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
        var rowId             = 'trParticipantFor' + userId + '-' + this._module;
        var containerId       = 'tdParticipantFor' + userId + '-' + this._module;
        var containerButtonId = 'tdParticipantDeleteButtonFor' + userId + '-' + this._module;
        var buttonId          = 'participantDeleteButtonFor' + userId + '-' + this._module;

        // Get new values
        var value = this._relations[userId];

        // Check if there is any change
        var updateDisplay = true;
        var field         = dijit.byId('dataParticipant'+ '[' + userId + ']' + '-' + this._module);
        if (field) {
            if (field.get('value') == value) {
                updateDisplay = false;
            }
        }

        // Create/Update field value
        var hiddenFieldValues = {
            id:       'dataParticipant' + '[' + userId + ']',
            type:     'hidden',
            disabled: (!this._accessPermissions) ? true : false,
            required: false,
            value:    value,
            hint:     ''
        };
        var displayWidgetClass = new phpr.Field.HiddenField(hiddenFieldValues, this._module);

        var row = dojo.byId(rowId);
        if (!row) {
            // Add a new row
            var row                   = this._table.insertRow(this._table.rows.length);
            row.id                    = rowId;
            row.className             = 'participantRow';
            row.style.backgroundColor = this._getColor();
            row.setAttribute('internalId', userId);

            // Hidden field
            var cell           = this._createCell(row, dijit.byId(displayWidgetClass.fieldId).domNode);
            cell.style.display = 'none';

            // User display
            var containerDiv = this._createContainer();
            containerDiv.id  = containerId;
            this._createCell(row, containerDiv);

            // Action
            var containerButtonDiv = this._createContainer();
            containerButtonDiv.id  = containerButtonId;
            this._createCell(row, containerButtonDiv);
        } else {
            var containerDiv       = dojo.byId(containerId);
            var containerButtonDiv = dojo.byId(containerButtonId);
        }

        // Fill the user display
        if (updateDisplay) {
            dojo.empty(containerDiv);
            var txt = document.createTextNode(this._relations[userId]);
            containerDiv.appendChild(txt);
        }

        // Check the permissions for the action button
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
                dojo.connect(deleteButton, 'onClick', dojo.hitch(this, function() {
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
    },

    _newUserRow:function() {
        // Summary:
        //    Add a new user-row with.
        // Description:
        //    If the user-row already exists in garbage, restore it.
        //    If the user-row don't exists, create it.
        //    Create or update the entry in the this._relations array.
        //    After add, move the scroll to the bottom for see the new row.
        //    On success or on error, show a message.
        var userId = dijit.byId('dataParticipantAdd-' + this._module).get('value');
        var rowId  = 'trParticipantFor' + userId + '-' + this._module;

        if (userId <= 0) {
            return;
        }

        var tr = dojo.byId(rowId);
        if (!tr || tr.parentNode.id == 'garbage') {
            // Add/Update the values into this._relations
            this._relations[userId] = dijit.byId('dataParticipantAdd-' + this._module).get('displayedValue')

            if (tr) {
                // Move it into tbody
                dojo.place(tr, this._table.firstChild, 'last');
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
        dijit.byId('dataParticipant' + '[' + userId + ']' + '-' + this._module).set('value', null);

        // Move the row to garbage
        dojo.place('trParticipantFor' + userId + '-' + this._module, 'garbage');
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

    _createCell:function(row, child) {
        // Summary:
        //    Create a new cell.
        // Description:
        //    If child is defined, append it to the cell.
        var cell = row.insertCell(this._cellIndex);
        this._cellIndex++;
        if (this._cellIndex == 3) {
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
        dojo.query('.participantRow', this._table).forEach(function(ele) {
            ele.style.backgroundColor = self._getColor();
        });
        self = null;
    }
});
