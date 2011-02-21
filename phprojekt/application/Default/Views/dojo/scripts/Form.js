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
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <gustavo.solt@mayflower.de>
 */

dojo.provide("phpr.Default.Form");

dojo.declare("phpr.Default.Form", null, {
    // Summary:
    //    Class for displaying a PHProjekt Detail View
    // Description:
    //    This Class takes care of displaying the form information we receive from our Server
    //    in a dojo form with tabs
    // General
    _id:                0,
    _fieldTemplate:     null,
    _form:              null,
    _module:            null,
    _userStore:         null,
    _accessPermissions: true,
    _deletePermissions: false,
    _usedHighlight:     false,
    _writePermissions:  true,
    _initDataArray:     [],
    _formsWidget:       [],
    _presetValuesArray: [],
    _sendData:          [],
    _subModules:        [],

    // Urls
    _accessUrl:  null,
    _historyUrl: null,
    _tagUrl:     null,
    _url:        null,

    // Events
    _events: [],
    // Events Tabs
    _eventForAccessTab:     null,
    _eventForHistoryTab:    null,
    _eventForSubModulesTab: null,
    // Events Buttons
    _eventForDelete: null,
    _eventForSubmit: null,

    // Access
    _accessRender:    null,
    _hiddenAccessTab: false,

    constructor:function(module, subModules) {
        // Summary:
        //    Construct the form only one time.
        // Description:
        //    Init general vars and call a private function for constructor
        //    that can be overwritten by other modules.
        this._events                = [];
        this._formsWidget           = [];
        this._eventForAccessTab     = null;
        this._eventForHistoryTab    = null;
        this._eventForSubModulesTab = null;
        this._eventForDelete        = null;
        this._eventForSubmit        = null;
        this._form                  = null;
        this._usedHighlight         = false;

        this._constructor(module, subModules);
    },

    init:function(id, params) {
        // Summary:
        //    Init the form for a new render.
        // Reset vars
        this._id                = id;
        this._userStore         = null;
        this._accessPermissions = true;
        this._deletePermissions = false;
        this._writePermissions  = true;
        this._initDataArray     = [];
        this._presetValuesArray = (params) ? params : [];

        // If the form was updated with a highlight class (from FrontEndMesage)
        // Call the remove function
        if (this._usedHighlight) {
            this._fieldTemplate.removeHighlight();
            this._usedHighlight = false;
        }

        // Show the loading
        this.showLayout('loading');

        // Get the new data
        this._setUrl();
        this._initDataArray.push({'url': this._url, 'processData': dojo.hitch(this, '_getFormData')});
        this._initDataArray.push({'store': phpr.TabStore});
        this._initData();
        this._getInitData();
    },

    updateData:function() {
        // Summary:
        //    Delete the cache for this form.
        phpr.DataStore.deleteData({url: this._url});
        phpr.DataStore.deleteData({url: this._tagUrl});
        phpr.DataStore.deleteData({url: this._accessUrl});
    },

    showLayout:function(type) {
        // Summary:
        //    Show a loading image, an error text or and empty div, and hide the form.
        // Description:
        //    For error, hide the form and show an error message.
        //    For none, hide the form and show an empty div.
        //    For loadind, move the formInit div to the detailBox position and size for hide these div.
        if (type == 'error') {
            // Hide form
            if (this._form) {
                this._form.domNode.style.display = 'none';
            }

            // Create error div
            var emptyNode = dojo.byId('errorForm');
            if (!emptyNode) {
                var emptyNode              = document.createElement('div');
                emptyNode.id               = 'errorForm';
                emptyNode.style.textAlign  = 'center';
                emptyNode.style.margin     = '10px 10px 10px 10px';
                emptyNode.style.fontWeight = 'bold';
                emptyNode.innerHTML        = '<b>' + phpr.nls.get('The Item was not found') + '</b>';
            }

            // Show error div
            dojo.place(emptyNode, dojo.byId(this._getNodeId()));

            // Hide none div
            if (dojo.byId('noneForm')) {
                dojo.place('noneForm', 'garbage');
            }

            // Hide loading div
            dojo.style('formInitImg', 'display', 'none');
            dojo.style('formInit', {
                left:    0 + 'px',
                top:     0 + 'px',
                display: 'none',
                zIndex:  -20
            });
        } else if (type == 'none') {
            // Hide form
            if (this._form) {
                this._form.domNode.style.display = 'none';
            }

            // Create none div
            var emptyNode = dojo.byId('noneForm');
            if (!emptyNode) {
                var emptyNode = document.createElement('div');
                emptyNode.id  = 'noneForm';
            }

            // Show none div
            dojo.place(emptyNode, dojo.byId(this._getNodeId()));

            // Hide error div
            if (dojo.byId('errorForm')) {
                dojo.place('errorForm', 'garbage');
            }

            // Hide loading div
            dojo.style('formInitImg', 'display', 'none');
            dojo.style('formInit', {
                left:    0 + 'px',
                top:     0 + 'px',
                display: 'none',
                zIndex:  -20
            });
        } else if (type == 'loading') {
            // Hide error and none div
            if (dojo.byId('errorForm')) {
                dojo.place('errorForm', 'garbage');
            }
            if (dojo.byId('noneForm')) {
                dojo.place('noneForm', 'garbage');
            }

            // Show form
            if (this._form) {
                this._form.domNode.style.display = 'block';
            }

            // Set the size of "formInit" with the size of the form
            dojo.marginBox('formInit', dojo.marginBox(this._getNodeId()));

            // Set the position of "formInit" with the position of the form
            var pos = dojo.position(this._getNodeId());
            dojo.style('formInitImg', 'display', 'inline');
            dojo.style('formInit', {
                left:    pos.x + 'px',
                top:     pos.y + 'px',
                display: 'inline',
                zIndex:  20
            });
        }
    },

    highlightChanges:function(data) {
        // Summary:
        //    Highlights changes done by any other user with a style comming from the CSS class "highlightChanges".
        // Description:
        //    Checks if I am on the same data record as the user who changes something.
        //    If so, adds the CSS class "highlightChanges" to the form element
        //    (typicall border: 3px solid #ff0000) and overwrites the given value with the new one.
        var meta       = phpr.DataStore.getMetaData({url: this._url});
        var details    = data.details;
        var detailsLen = details.length;
        for (var i = 0; i < detailsLen; i++) {
            var field = details[i].field;
            var value = details[i].newValue;

            // Search the field
            for (var k = 0; k < meta.length; k++) {
                if (meta[k]['key'] == field) {
                    var fieldData                   = [];
                    fieldData[meta[k]['key']] = details[i].newValue;
                    var fieldValues           = this._setFieldValues(meta[k], fieldData);
                    if (fieldValues['type'] == 'upload') {
                        fieldValues['iFramePath'] = this._getUploadIframePath(fieldValues['id']);
                    }
                    this._fieldTemplate.addHighlight(fieldValues);
                    break;
                }
            }
        }

        this._usedHighlight = true;
    },

    destroyLayout:function() {
        // Summary:
        //    Destroy the widgets for create them again.
        // Description:
        //    If the Module Designer change the fields of this module,
        //    All the widgets must be destroyed for create them again with the new values like
        //    label, type, etc.
        //    Disconnect all the button acctions.
        //    Destroy all the fields.
        //    Destroy all the popups that are not destroyed by dijit.destroy().
        // Disconnect buttons
        for (var index in this._events) {
            var link = eval('this.' + this._events[index]);
            dojo.disconnect(link);
            eval('this.' + this._events[index] + ' = null');
        };

        // Destroy Widgets
        this._fieldTemplate.destroyLayout();

        // Destroy popup widgets (tooltips and textarea popups)
        dojo.forEach(dojo.query('*.dijitPopup'), function(popup) {
            dojo.body().removeChild(popup);
        });
    },

    /************* Private functions *************/

    _constructor:function(module, subModules) {
        // Summary:
        //    Construct the form only one time.
        // Description:
        //    Use this.inherited(arguments) in constructor function produce a dojo error,
        //    so this function can be easy overwritted whithout that problem.

        // phpr.module is the current module and is used for all the URL.
        // this._module can be any string that represent the module and is used for all the widgetIds.
        this._module = module

        this._subModules    = subModules;
        this._fieldTemplate = new phpr.TableForm(this._module);
        this._accessRender  = new phpr.Default.Access(this._module);
    },

    _setUrl:function() {
        // Summary:
        //    Set the url for get the data.
        this._url = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonDetail/nodeId/' + phpr.currentProjectId
            + '/id/' + this._id;
    },

    _initData:function() {
        // Summary:
        //    Init all the data before draw the form.
        // Description:
        //    This function call all the needed data before the form is drawed.
        //    The form will wait for all the data are loaded.
        //    Each module can overwrite this function for load the own data.
        // Get the rights for other users
        this._accessUrl = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonGetUsersRights'
            + '/nodeId/' + phpr.currentProjectId + '/id/' + this._id;
        this._initDataArray.push({'url': this._accessUrl});

        // Get all the active users
        this._userStore = new phpr.Store.User();
        this._initDataArray.push({'store': this._userStore});

        // Get the tags
        this._tagUrl = phpr.webpath + 'index.php/Default/Tag/jsonGetTagsByModule/moduleName/' + phpr.module
            + '/id/' + this._id;
        this._initDataArray.push({'url': this._tagUrl});
    },

    _getInitData:function() {
        // Summary:
        //    Process all the POST in cascade for get all the data from the server.
        var params = this._initDataArray.pop();

        if (params.url || params.store) {
            if (!params.noCache) {
                params.noCache = false;
            }
            if (!params.processData) {
                params.processData = dojo.hitch(this, '_getInitData');
            }
        }

        if (params.url) {
            phpr.DataStore.addStore({'url': params.url, 'noCache': params.noCache});
            phpr.DataStore.requestData({'url': params.url, 'processData': params.processData});
            params.processData = null;
        } else if (params.store) {
            params.store.fetch(params.processData);
            params.processData = null;
        }
    },

    _getFormData:function() {
        // Summary:
        //    Renders the form data according to the database manager settings.
        // Description:
        //    Processes the form data which is stored in a phpr.DataStore and
        //    renders the actual form according to the received data.
        // Get the data and metadata
        var meta = phpr.DataStore.getMetaData({url: this._url});
        var data = phpr.clone(phpr.DataStore.getData({url: this._url}));

        if (data.length == 0) {
            // Show error
            this.showLayout('error');
        } else {
            var tabs               = this._getTabs();
            var firstRequiredField = null;

            // Set the write, delete and access rights
            this._setPermissions(data);

            // Set custom values
            this._presetValues(data);

            // Process each field
            for (var i = 0; i < meta.length; i++) {
                // Set the field values
                var fieldValues = this._setFieldValues(meta[i], data[0]);

                // Draw the BreadCrumb with the first field value
                if (i == 0) {
                    this._setBreadCrumbItem(fieldValues['value']);
                    phpr.BreadCrumb.draw();
                }

                // Get the first required field
                if (fieldValues['required'] && fieldValues['type'] != 'hidden' && !firstRequiredField) {
                    firstRequiredField = fieldValues['id'] + '-' + this._module;
                }

                // Special workaround for new projects - set parent to current ProjectId
                if (fieldValues['id'] == 'projectId' && !fieldValues['value']){
                    fieldValues['value'] = phpr.currentProjectId;
                }

                // Special workaround for upload fields - set the iFramePath
                if (fieldValues['type'] == 'upload') {
                    fieldValues['iFramePath'] = this._getUploadIframePath(fieldValues['id']);
                }

                // Init formdata and table for each tab
                if (!this._fieldTemplate.existsTable(fieldValues['tab'])) {
                    this._fieldTemplate.createTable(fieldValues['tab']);
                }

                // Add the table row with label - field - buttons
                this._fieldTemplate.addRow(fieldValues);
            }

            // Add special inputs to the Basic Data
            this._addBasicFields();

            // Set the TabContainer for this module
            if (dijit.byId(this._getNodeId()).getChildren().length == 0) {
                this._form        = this._setFormContent();
                this._formsWidget = [];
                dijit.byId(this._getNodeId()).set('content', this._form.domNode);
            } else {
                this._form = dijit.byId(this._getNodeId()).getChildren()[0];
            }

            // Add the tabs with the table of fields
            var firstTab = true;
            for (t in tabs) {
                if (this._fieldTemplate.existsTable(tabs[t].id)) {
                    if (firstTab) {
                        this._setFormButtons(tabs[t].id);
                        firstTab = false;
                    }
                    var tabId  = 'tabBasicData' + tabs[t].id + '-' + this._module;
                    var formId = 'dataFormTab' + tabs[t].id + '-' + this._module;
                    this._addTab(this._fieldTemplate.getTable(tabs[t].id), tabId, tabs[t].name, formId);
                }
            }

            this._setActionFormButtons();
            this._addModuleTabs(data);
            this._addSubModulesTab();

            // Delete the data if is not used the cache
            if (!this._useCache()) {
                phpr.DataStore.deleteData({url: this._url});
            }

            // Set cursor to the first required field in the first tab
            if (dojo.byId(firstRequiredField)) {
                dojo.byId('completeContent').focus();
                this._form.selectChild(this._form.getChildren()[0]);
                dijit.byId(firstRequiredField).focus();
            }

            this._postRenderForm();

            // Hide loading div
            dojo.style('formInitImg', 'display', 'none');
            dojo.style('formInit', {
                left:    0 + 'px',
                top:     0 + 'px',
                display: 'none',
                zIndex:  -20
            });
        }
    },

    _getTabs:function() {
        // Summary:
        //    Return the tab list for make the form or an empty array.
        return phpr.TabStore.getList();
    },

    _setPermissions:function(data) {
        // Summary:
        //    Get the permission for the current user on the item.
        if (this._id > 0) {
            if (phpr.isGlobalModule(phpr.module)) {
                this._writePermissions  = true;
                this._deletePermissions = true;
                this._accessPermissions = false;
            } else {
                this._writePermissions  = (data[0]['rights']['currentUser']['write']) ? true : false;
                this._deletePermissions = (data[0]['rights']['currentUser']['delete']) ? true : false;
                this._accessPermissions = (data[0]['rights']['currentUser']['admin']) ? true : false;
            }
        }
    },

    _presetValues:function(data) {
        // Summary:
        //    Function used to preset values in the form.
        // Description:
        //    The form is able to receive some values when it is instanced for adding and item,
        //    and put that values in each field.
        for (var field in this._presetValuesArray) {
            data[0][field] = this._presetValuesArray[field];
        }
    },

    _setFieldValues:function(meta, data) {
        // Summary:
        //    Set the fields values for render the form.
        var fieldValues = {
            type:     meta['type'],
            id:       meta['key'],
            label:    meta['label'],
            disabled: meta['readOnly'],
            required: meta['required'],
            value:    data[meta['key']],
            range:    meta['range'],
            tab:      meta['tab'] || 1,
            hint:     meta['hint'],
            length:   meta['length'] || 0
        };

        return this._setCustomFieldValues(fieldValues);
    },

    _setCustomFieldValues:function(fieldValues) {
        // Summary:
        //    Custom function for setFieldValues.
        return fieldValues;
    },

    _setBreadCrumbItem:function(itemValue) {
        // Summary:
        //    Set the Breadcrumb with the first item value.
        phpr.BreadCrumb.setItem(itemValue);
    },

    _getUploadIframePath:function(itemId) {
        // Summary:
        //    Set the URL for request the upload file.
        return phpr.webpath + 'index.php/' + phpr.module + '/index/fileForm'
            + '/nodeId/' + phpr.currentProjectId + '/id/' + this._id + '/field/' + itemId
            + '/csrfToken/' + phpr.csrfToken;
    },

    _addBasicFields:function() {
        // Summary:
        //    Add some special fields.
        this._displayTagInput();
    },

    _displayTagInput:function() {
        // Summary:
        //    This function manually receives the Tags for the current element.
        // Description:
        //    By calling the TagController this function receives all data it needs
        //    for rendering a Tag from the server and renders those tags in a Input separated by coma.
        //    The function also call the main render function
        //    for show the tags in the moveable pannel to click it and search by tags.
        var currentTags = phpr.DataStore.getData({url: this._tagUrl});
        var meta        = phpr.DataStore.getMetaData({url: this._tagUrl});
        var value       = '';

        if (this._id > 0) {
            for (var i = 0; i < currentTags.length; i++) {
                value += currentTags[i]['string'];
                if (i != currentTags.length - 1) {
                    value += ', ';
                }
            }
        }

        // Draw the tags
        dojo.publish(phpr.module + '.drawTagsBox', [currentTags]);

        var fieldValues = {
            type:     'text',
            id:       meta[0]['key'],
            label:    meta[0]['label'],
            disabled: false,
            required: false,
            value:    value,
            tab:      1,
            hint:     '',
            length:   0
        };

        this._fieldTemplate.addRow(fieldValues);
    },

    _getNodeId:function() {
        // Summary:
        //    Set the node where put the form.
        return 'detailsBox-' + this._module;
    },

    _setFormContent:function() {
        // Summary:
        //    Set the form container.
        var tabContainer = new dijit.layout.TabContainer({
            style:   'height: 100%;',
            useMenu: false
        }, document.createElement('div'));

        dojo.connect(tabContainer, 'selectChild', dojo.hitch(this, function() {
            dojo.byId('completeContent').focus();
        }));

        return tabContainer;
    },

    _setFormButtons:function(tabId) {
        // Summary:
        //    Render the save and delete buttons.
        var fieldValues = {
            type:              'formButtons',
            id:                'buttons',
            label:             '',
            disabled:          false,
            required:          false,
            value:             '',
            range:             '',
            tab:               tabId,
            hint:              '',
            length:            0,
            writePermissions:  this._writePermissions,
            deletePermissions: this._deletePermissions
        };

        this._fieldTemplate.addRow(fieldValues);
    },

    _addTab:function(content, id, title, formId) {
        // Summary:
        //    Add a tab.
        // Description:
        //    Add a tab and if have form, add the values
        //    to the array of values for save it later.
        var tabWidget = dijit.byId(id);
        if (!tabWidget) {
            // New form
            var formWidget = new dijit.form.Form({
                id:       formId,
                name:     formId,
                style:    'height: 100%',
                onSubmit: function() {
                    return false;
                }
            });
            if (content) {
                formWidget.domNode.appendChild(content);
            }

            // New tab
            var tab = new dijit.layout.ContentPane({
                id:    id,
                title: phpr.nls.get(title)
            });
            tab.set('content', formWidget);

            // Add the tab with the form into the TabContainer
            this._form.addChild(tab);

            // Keep the formId
            if (typeof formId != 'undefined') {
                this._formsWidget.push(formWidget);
            }
        } else {
            // Update title
            tabWidget.set('title', phpr.nls.get(title));
        }
    },

    _setActionFormButtons:function() {
        // Summary:
        //    Connect the buttons to the actions.
        // Save button
        if (!this._eventForSubmit) {
            this._eventForSubmit = dojo.connect(dijit.byId('submitButton-' + this._module), 'onClick',
                dojo.hitch(this, '_submitForm'));
            this._events.push('_eventForSubmit');
        };

        // Delete button
        if (!this._eventForDelete) {
            this._eventForDelete = dojo.connect(dijit.byId('deleteButton-' + this._module), 'onClick',
                dojo.hitch(this, function() {
                    phpr.confirmDialog(dojo.hitch(this, '_deleteForm'),
                        phpr.nls.get('Are you sure you want to delete?'));
                })
            );
            this._events.push('_eventForDelete');
        };
    },

    _addModuleTabs:function(data) {
        // Summary:
        //    Add extra tabs.
        // Description:
        //    Add some system tabs.
        //    Each module can add here their own tabs.
        this._addAccessTab(data);
        this._addNotificationTab(data);
        this._addHistoryTab();
    },

    _addAccessTab:function(data) {
        // Summary:
        //    Access tab.
        // Description:
        //    Display all the users and the access.
        //    The user with admin righst, can assign to each user different access on the item.
        //    Rights for the current user can't be edited, but are displayed.
        var currentUser       = data[0]['rights']['currentUser']['userId'] || 0;
        this._hiddenAccessTab = true;

        var tabId  = 'tabAccess-' + this._module;
        var formId = 'accessFormTab-' + this._module;
        this._addTab(null, tabId, 'Access', formId);

        // Create table only when the tab is required
        if (!this._eventForAccessTab) {
            this._eventForAccessTab = dojo.connect(dijit.byId(tabId), 'onShow', dojo.hitch(this, function() {
                if (this._hiddenAccessTab) {
                    // Do not refresh the data until the module is reloaded
                    this._hiddenAccessTab = false;

                    var data                  = this._getAccessData();
                    data['accessPermissions'] = this._accessPermissions;
                    data['currentUser']       = currentUser;

                    this._accessRender.createTable(data);

                    if (dijit.byId(formId).getChildren().length == 0) {
                        dijit.byId(formId).domNode.appendChild(this._accessRender.getTable());
                    }
                }
            }));
        }
    },

    _getAccessData:function() {
        // Summary:
        //    Set the new data for show the tab.
        var userList      = this._userStore.getList();
        var accessContent = phpr.DataStore.getData({url: this._accessUrl});

        // Set the new data
        return {
            accessContent: accessContent,
            userList:      userList
        };
    },

    _addNotificationTab:function(data) {
        // Summary:
        //    Adds a tab for sending a notification.
        // Description:
        //    Adds a tab for sending a notification to the users with read access,
        //    telling them about the item added or modified.
        //    It has a "Send Notification" checkbox.
        var tableTabId = 'notification';

        // Init the table
        this._fieldTemplate.createTable(tableTabId);

        // Add the row with label - field - buttons
        var fieldValues = {
            type:     'checkbox',
            id:       'sendNotification',
            label:    phpr.nls.get('Send Notification'),
            tab:      tableTabId,
            disabled: false,
            required: false,
            value:    (phpr.config.notificationEnabledByDefault) ? 1 : 0,
            hint:     phpr.nls.get('Check this box to send an email notification to the participants')
        };
        this._fieldTemplate.addRow(fieldValues);

        // Add the tab to the form
        var tabId  = 'tabNotify-' + this._module;
        var formId = 'Notification-' + this._module;
        this._addTab(this._fieldTemplate.getTable(tableTabId), tabId, 'Notification', formId);
    },

    _addHistoryTab:function() {
        // Summary:
        //    History tab.
        // Description:
        //    Display all the history of the item.
        if (this._id > 0 && this._useHistoryTab()) {
            var historyId = 'historyContent-' + this._module;
            var history   = dijit.byId(historyId);
            if (!history) {
                var history = new dijit.layout.ContentPane({
                    id:    historyId,
                    style: 'overflow: hidden;'
                }, document.createElement('div'));
            }

            var tabId = 'tabHistory-' + this._module;
            this._addTab(history.domNode, tabId, 'History');

            // Create table only when the tab is required
            if (!this._eventForHistoryTab) {
                this._eventForHistoryTab = dojo.connect(dijit.byId(tabId), 'onShow', dojo.hitch(this, '_showHistory'));
            }
        }
    },

    _useHistoryTab:function() {
        // Summary:
        //    Return true or false if the history tab is used.
        return true;
    },

    _showHistory:function() {
        // Summary:
        //    This function renders the history data.
        if (this._id > 0) {
            // Create the table and the headers if not exists
            var tableId = 'historyTable-' + this._module;
            var table   = dojo.byId(tableId);
            if (!table) {
                var table               = dojo.doc.createElement('table');
                table.id                = tableId;
                table.className         = 'historyTable';
                table.style.width       = 'auto';
                table.style.marginLeft  = '35px';
                table.style.marginRight = '35px';
            }

            // Remove old rows
            dojo.query('th, tr', table).forEach(function(ele) {
                dojo.destroy(ele);
            });

            this._historyUrl = phpr.webpath + 'index.php/Core/history/jsonList/nodeId/1/moduleName/' + phpr.module
                + '/itemId/' + this._id
            phpr.DataStore.addStore({'url': this._historyUrl, 'noCache': true});
            phpr.DataStore.requestData({'url': this._historyUrl, 'processData': dojo.hitch(this, function() {
                // Headers
                var row     = table.insertRow(table.rows.length);
                var headers = new Array('Date', 'User', 'Field', 'Old value', 'New value');
                for (var i = 0; i < headers.length; i++) {
                    var label = document.createElement('label');
                    var txt   = document.createTextNode(phpr.nls.get(headers[i]));
                    label.appendChild(txt);
                    var cell = document.createElement('th');
                    cell.appendChild(label);
                    row.appendChild(cell);
                }

                // Data
                var data = this._getHistoryData();
                for (var i in data) {
                    var row      = table.insertRow(table.rows.length);
                    row.className = (Math.floor(i / 2) == (i / 2)) ? 'grey' : 'white';
                    for (var j = 0; j < data[i].length; j++) {
                        if (data[i][j]) {
                            var txt = document.createTextNode(data[i][j]);
                        } else {
                            var txt = document.createTextNode("\u00a0");
                        }
                        var cell = row.insertCell(j);
                        cell.appendChild(txt);
                    }
                }

                if (!dojo.byId('historyContent-' + this._module).firstChild) {
                    dijit.byId('historyContent-' + this._module).set('content', table);
                }
            })});
        }
    },

    _getHistoryData:function() {
        // Summary:
        //    Collect and process the history data.
        // Description:
        //    Return an array with date, user, field, oldValue and newValue.
        var history     = phpr.DataStore.getData({url: this._historyUrl});
        var userList    = this._userStore.getList();
        var historyData = new Array();
        var userDisplay = new Array();

        for (var i = 0; i < history.length; i++) {
            // Search for the user name
            if (!userDisplay[history[i]['userId']]) {
                for (var u in userList) {
                    if (userList[u].id == history[i]['userId']) {
                        userDisplay[history[i]['userId']] = userList[u].display;
                        break;
                    }
                }
            }

            historyData.push([
                history[i]['datetime'],
                (userDisplay[history[i]['userId']]) ? userDisplay[history[i]['userId']] : '',
                history[i]['label'] || '',
                history[i]['oldValue'] || '',
                history[i]['newValue'] || ''
            ]);
        }

        return historyData;
    },

    _addSubModulesTab:function() {
        // Summary:
        //    Add all the SubModules that have the current module.
        if (this._id > 0) {
            if (!this._eventForSubModulesTab) {
                // Set the sub modules data
                var subModules   = new Array();
                var nextPosition = 0;
                for (var index in this._subModules) {
                    var subModuleName  = this._subModules[index];
                    var subModuleClass = 'phpr.' + subModuleName + '.Main';
                    var subModule      = eval('new ' + subModuleClass + '()');
                    var sort           = (subModule.sortPosition) ? subModule.sortPosition : nextPosition++;
                    subModules.push({
                        'sort':  sort,
                        'name':  subModuleName,
                        'class': subModule
                    });
                }

                // Sort the sub modules
                subModules.sort(function(a, b) {
                    return a['sort'] - b['sort'];
                });

                // Set the array
                this._eventForSubModulesTab = [];

                // Add the tabs
                for (var index in subModules) {
                    var subModuleName = subModules[index]['name'];

                    var tabId  = 'tab' + subModuleName + '-' + this._module;
                    var formId = subModuleName + 'FormTab-' + this._module;
                    this._addTab(null, tabId, phpr.nls.get(subModuleName, subModuleName), formId);
                    dojo.addClass(tabId, 'subModuleDiv');
                    subModules[index]['class'].fillTab(tabId);

                    this._eventForSubModulesTab[tabId] = dojo.connect(dijit.byId(tabId), 'onShow',
                        dojo.hitch(this, function() {
                            subModules[index]['class'].renderSubModule(this._id);
                        })
                    );
                }
            }
        }
    },

    _useCache:function() {
        // Summary:
        //    Return true or false if the cache is used.
        return true;
    },

    _postRenderForm:function() {
        // Summary:
        //    User functions after render the form.
        // Description:
        //    Apply for special events on the fields.
    },

    _submitForm:function() {
        // Summary:
        //    Submit the forms.
        // Description:
        //    Sends the form data as json data to the server.
        //    Call the jsonSave action and then the jsonSaveTags for save the tags.
        //    When all is saved, call the update and reload routine.
        if (!this._prepareSubmission()) {
            return false;
        }

        // Save data
        phpr.send({
            url: phpr.webpath + 'index.php/' + phpr.module + '/index/jsonSave/nodeId/' + phpr.currentProjectId
                + '/id/' + this._id,
            content:   this._sendData,
            onSuccess: dojo.hitch(this, function(data) {
               new phpr.handleResponse('serverFeedback', data);
               if (!this._id) {
                   this._id = data['id'];
               }
               if (data.type == 'success') {
                   // Save tags
                   phpr.send({
                        url: phpr.webpath + 'index.php/Default/Tag/jsonSaveTags/moduleName/' + phpr.module
                            + '/id/' + this._id,
                        content:   this._sendData,
                        onSuccess: dojo.hitch(this, function(data) {
                            if (this._sendData['string']) {
                                new phpr.handleResponse('serverFeedback', data);
                            }
                            if (data.type == 'success') {
                                dojo.publish(phpr.module + '.updateCacheData');
                                dojo.publish(phpr.module + '.setUrlHash', [phpr.module]);
                            }
                        })
                    });
                }
            })
        });
    },

    _prepareSubmission:function() {
        // Summary:
        //    Prepares the data for submission.
        // Description:
        //    Prepares the content of this._sendData before it is submitted to the Server.
        //    Collect all the form values into one array.
        this._sendData = [];
        for (var i in this._formsWidget) {
            if (!this._formsWidget[i].isValid()) {
                var parent = this._formsWidget[i].containerNode.parentNode.id;
                this._form.selectChild(parent);
                this._formsWidget[i].validate();
                return false;
            }
            var formData = this._formsWidget[i].get('value');
            var sendData = [];

            // Add the fields without the module string
            for (var index in formData) {
                var newIndex       = index.substr(0, index.length - 1 - this._module.length);
                sendData[newIndex] = formData[index];
            }

            // Any internal or not used field must be deleted?
            var deleteField = this._getFieldForDelete();
            for (var i in deleteField) {
                // delete all 'fieldName*'
                if (deleteField[i].indexOf('*') == deleteField[i].length - 1) {
                    var string = deleteField[i].substr(0, deleteField[i].length - 1);
                    for (var index in sendData) {
                        if (index.indexOf(string) == 0) {
                            var indexLeft = index.substring(0, deleteField[i].length - 1);
                            if (indexLeft == string) {
                                delete sendData[index];
                            }
                        }
                    }
                } else {
                     // Delete exact 'fieldName'
                    if (undefined != sendData[deleteField[i]]) {
                        delete sendData[deleteField[i]];
                    }
                }
            }
            if (typeof(sendData) != 'object') {
                sendData = new Array(sendData);
            } else {
                for (var k in sendData) {
                    // Allow empty arrays, set the value to an empty string
                    if (sendData[k] && typeof(sendData[k]) == 'object' && sendData[k].length == 0) {
                        sendData[k] = new Array('');
                    }
                }
            }

            dojo.mixin(this._sendData, sendData);

            delete formData;
        }

        delete sendData;

        return true;
    },

    _getFieldForDelete:function() {
        // Summary:
        //    Return an array of fields for delete.
        // Description:
        //    Since the form is only one per module,
        //    when the user show a tab, the fields are added to the form,
        //    If the user change the itemId, the form is the same, and the fields in the tab are still there.
        //    If the user on the new form, don't open the tab for refresh the values, then must be deleted.
        //    The array contain all the fields that must be deleted if the tabs are not open.
        //    Also contain some fields that are not needed in the backend.
        // Access fields
        var fields = [
            'checkAccessAccessAdd', 'checkAdminAccessAdd', 'checkCopyAccessAdd',
            'checkCreateAccesAdd', 'checkDeleteAccessAdd', 'checkDownloadAccessAdd',
            'checkReadAccessAdd', 'checkWriteAccessAdd', 'dataAccessAdd'
        ];
        if (this._hiddenAccessTab) {
            // If the tab was not requested, delete any old values for access
            fields.push('dataAccess*');
        }

        return fields;
    },

    _deleteForm:function() {
        // Summary:
        //    Delete an item.
        // Description:
        //    Call the jsonDelete action and then the jsonDeleteTags for delete the tags.
        //    When all is deleted, call the update and reload routine.
        // Delete the data
        phpr.send({
            url:       phpr.webpath + 'index.php/' + phpr.module + '/index/jsonDelete/id/' + this._id,
            onSuccess: dojo.hitch(this, function(data) {
               new phpr.handleResponse('serverFeedback', data);
               if (data.type == 'success') {
                   // Delete the tags
                   phpr.send({
                        url: phpr.webpath + 'index.php/Default/Tag/jsonDeleteTags/moduleName/' + phpr.module
                            + '/id/' + this._id,
                        onSuccess: dojo.hitch(this, function(data) {
                            new phpr.handleResponse('serverFeedback', data);
                            if (data.type == 'success') {
                                dojo.publish(phpr.module + '.updateCacheData');
                                dojo.publish(phpr.module + '.setUrlHash', [phpr.module]);
                            }
                        })
                    });
               }
            })
        });
    }
});
