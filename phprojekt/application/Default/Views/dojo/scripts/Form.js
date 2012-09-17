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
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Default.Form");
dojo.provide("phpr.Default.DialogForm");

dojo.require("dijit.form.Button");
dojo.require("dijit.layout.ContentPane");
dojo.require("dijit.layout.TabContainer");

dojo.declare("phpr.Default.Form", phpr.Default.System.Component, {
    // Summary:
    //    Class for displaying a PHProjekt Detail View
    // Description:
    //    This Class takes care of displaying the form information we receive from our Server
    //    in a dojo form with tabs

    sendData:           {},
    formdata:           [],
    userStore:          null,
    _url:               null,
    _writePermissions:  true,
    _deletePermissions: false,
    _accessPermissions: true,
    _initData:          [],
    _tagUrl:            null,
    _accessUrl:         null,
    _historyUrl:        null,
    _presetValues:      null,
    _meta:              null,
    _rights:            new Array('Read', 'Write', 'Access', 'Create', 'Copy', 'Delete', 'Download', 'Admin'),
    _submitInProgress:  false,
    _loadIndicator: null,
    _subModules: null,
    _historyContent: null,

    tabs: { _empty: true },

    constructor: function(main, id, module, params, formContainer) {
        // Summary:
        //    render the form on construction
        // Description:
        //    this function receives the form data from the server and renders the corresponding form
        //    If the module is a param, is setted
        this.main = main;
        this.id   = id;

        this.setContainer(formContainer);

        if (undefined !== params) {
            this._presetValues = params;
        }

        this.setUrl(params);

        // Put loading
        this._loadIndicator = new phpr.Default.loadingOverlay(this.node.domNode);
        this._loadIndicator.show();

        this._initData.push({'url': this._url});
        this.tabStore = new phpr.Default.System.Store.Tab();
        this._initData.push({'store': this.tabStore});
        this.initData();
        this.getInitData([dojo.hitch(this, "getFormData")]);
    },

    destroy: function() {
        // Summary:
        //    Destroy the form
        // Description:
        //    Destroys the form and collects all events and widgets
        this.inherited(arguments);
        this.node = null;
        this.form = null;

        if (this.fieldTemplate && dojo.isFunction(this.fieldTemplate.destroy)) {
            this.fieldTemplate.destroy();
        }

        this.fieldTemplate = null;
        if (this._loadIndicator && dojo.isFunction(this._loadIndicator.hide)) {
            this._loadIndicator.hide();
        }
        this._loadIndicator = null;
        this._destroySubModules();
    },

    _destroySubModules: function() {
        var subModules = this._subModules;
        for (var index in subModules) {
            var subModuleName = subModules[index].name;
            subModules[index]['class'].destroy();
        }
    },

    setContainer: function(container) {
        // Summary:
        //    Set the node to render in
        // Description:
        //    Set the node to render in
        var layout = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Default.template.form.splitContainer.html"
        });
        this._splitContainer = layout;
        this.node = this._splitContainer.formContainer;
        this.garbageCollector.addNode(layout);
        container.set('content', layout);
    },

    setUrl: function() {
        // Summary:
        //    Set the url for get the data
        // Description:
        //    Set the url for get the data
        this._url = 'index.php/' + phpr.module +
            '/index/jsonDetail/nodeId/' + phpr.currentProjectId + '/id/' + this.id;
    },

    getInitData: function(callbacks) {
        // Summary:
        //    Process all the POST in cascade for get all the data from the server
        // Description:
        //    Process all the POST in cascade for get all the data from the server
        var deferreds = [];
        while (this._initData.length > 0) {
            var params = this._initData.pop();

            if (params.url || params.store) {
                if (!params.noCache) {
                    params.noCache = false;
                }
                if (!params.processData) {
                    params.processData = function() {};
                }
            }

            if (params.url) {
                phpr.DataStore.addStore({'url': params.url, 'noCache': params.noCache});
                deferreds.push(phpr.DataStore.requestData({'url': params.url, 'processData': params.processData}));
            } else if (params.store) {
                deferreds.push(params.store.fetch(params.processData));
            }
        }
        var dlist = new dojo.DeferredList(deferreds);

        for (var i = 0; i < callbacks.length; i++) {
            dlist.addCallback(callbacks[i]);
        }
    },

    initData: function() {
        // Summary:
        //    Init all the data before draw the form
        // Description:
        //    This function call all the needed data before the form is drawed
        //    The form will wait for all the data are loaded.
        //    Each module can overwrite this function for load the own data

        // Get the rights for other users
        this._accessUrl = 'index.php/' + phpr.module +
            '/index/jsonGetUsersRights' + '/nodeId/' + phpr.currentProjectId + '/id/' + this.id;
        this._initData.push({'url': this._accessUrl});

        // Get the tags
        this._tagUrl = 'index.php/Default/Tag/jsonGetTagsByModule/moduleName/' + phpr.module + '/id/' + this.id;
        this._initData.push({'url': this._tagUrl});
    },

    addAccessTab: function(data) {
        // Summary:
        //    Access tab
        // Description:
        //    Display all the users and the acces
        //    The user can assign to each user different access on the item
        if (this._destroyed) {
            return;
        }

        // use an eventual override, if there is no local userStore, use the global
        var userList      = this.userStore ? this.userStore.getList() : phpr.userStore.getList();
        var accessContent = phpr.DataStore.getData({url: this._accessUrl});
        var currentUser   = data[0].rights[phpr.currentUserId] ? phpr.currentUserId : 0;
        var users         = [];

        if (userList) {
            for (var i in userList) {
                // Make an array with the users except the current one and the admin
                users.push({'id': userList[i].id, 'display': userList[i].display});
                // Found the name of each user
                if (accessContent[userList[i].id]) {
                    accessContent[userList[i].id].userDisplay = userList[i].display;
                }
            }
        }

        // Template for the access tab
        var accessData = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Default.template.access.tab.html",
            templateData: {
                accessUserText:     phpr.nls.get('User'),
                accessReadText:     phpr.nls.get('Read'),
                accessWriteText:    phpr.nls.get('Write'),
                accessAccessText:   phpr.nls.get('Access'),
                accessCreateText:   phpr.nls.get('Create'),
                accessCopyText:     phpr.nls.get('Copy'),
                accessDeleteText:   phpr.nls.get('Delete'),
                accessDownloadText: phpr.nls.get('Download'),
                accessAdminText:    phpr.nls.get('Admin'),
                accessActionText:   phpr.nls.get('Action'),
                accessPermissions:  (users.length > 0) ? this._accessPermissions : false,
                users:              users
            }
        });
        this._accessTab = accessData;
        this.garbageCollector.addNode(accessData);

        for (var id in accessContent) {
            if (accessContent[id].userDisplay) {
                this._addAccessTabRow(accessContent[id], currentUser);
            }
        }

        var def = this.addTab([accessData], 'tabAccess', 'Access', 'accessFormTab');
        return def.then(dojo.hitch(this, function() {
            if (this._destroyed) {
                return;
            }

            // Add "add" button for access
            if (this._accessPermissions && users.length > 0) {
                this.addTinyButton('add', accessData.accessAddButton, 'newAccess');
                this.garbageCollector.addEvent(
                    dojo.connect(dijit.byId("checkAdminAccessAdd"),
                        "onClick", dojo.hitch(this, "checkAllAccess", "Add")));
            }
        }));
    },

    _deleteAccessRowForUserId: function(userId) {
        if (this._accessRowsForUsers[userId]) {
            this._accessRowsForUsers[userId].destroyRecursive();
            delete this._accessRowsForUsers[userId];
        }
    },

    _addAccessTabRow: function(accessContent, currentUser) {
        if (!this._accessRowsForUsers) {
            this._accessRowsForUsers = {};
        }

        var id = accessContent.userId;

        var isCurrentUser = (id == phpr.currentUserId);
        var userId        = isCurrentUser ? currentUser : id;

        var accessPermission = this._accessPermissions &&
                                userId != currentUser &&
                                userId != 1;

        if ((userId == 1 && currentUser != 1) || (!accessPermission && this._accessRowsForUsers[userId])) {
            return;
        }

        this._deleteAccessRowForUserId(id);

        var input = phpr.fillTemplate("phpr.Default.template.access.input.html", {
            id:          userId,
            disabled:    (!this._writePermissions) ? 'disabled="disabled"' : '',
            userDisplay: accessContent.userDisplay,
            currentUser: isCurrentUser
        });

        var row = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Default.template.access.row.html",
            templateData: {
                id: userId,
                input: input,
                rights: this._rights
            }
        });

        var checkBoxes = [];
        for (var i in this._rights) {
            var fieldId = 'check' + this._rights[i] + 'Access[' + userId + ']';
            var rightName = accessContent[this._rights[i].toLowerCase()];
            var checkBox = new phpr.Default.System.TemplateWrapper({
                templateName: "phpr.Default.template.access.checkbox.html",
                templateData: {
                    fieldId:  fieldId,
                    checked:  rightName ? 'checked' : '',
                    hidden:   (isCurrentUser && this._accessPermissions),
                    value:    rightName ? 1 : 0,
                    disabled: (!this._writePermissions) ? 'disabled="disabled"' : ''
                }
            });

            this.garbageCollector.addNode(checkBox);

            if (accessPermission && this._rights[i] === "Admin") {
                this.garbageCollector.addEvent(
                    dojo.connect(
                        checkBox.checkBox,
                        "onClick",
                        dojo.hitch(this, "_checkAllAccessBoxesIfChecked", checkBox.checkBox, checkBoxes)));
            }
            row[this._rights[i]].appendChild(checkBox.domNode);
            checkBoxes.push(checkBox);
        }

        if (accessPermission) {
            var button = dojo.create('div');
            this.addTinyButton('delete', button, '_deleteAccessRowForUserId', [userId]);
            row.button.appendChild(button);
        }

        this._accessRowsForUsers[userId] = row;
        this._accessTab.tbody.appendChild(row.domNode);
        this.garbageCollector.addNode(row);
    },

    _checkAllAccessBoxesIfChecked: function(controlBox, checkBoxes) {
        if (controlBox.checked) {
            for (var idx in checkBoxes) {
                checkBoxes[idx].checkBox.set('checked', true);
            }
        }
    },

    addTinyButton: function(type, node, functionName, extraParams) {
        // Summary:
        //    Add a button
        // Description:
        //    Add a button into the node and connect id to one function
        var params = {
            label:     '',
            iconClass: (type == 'add') ? 'add' : 'cross',
            alt:       (type == 'add') ? phpr.nls.get('Add') : phpr.nls.get('Delete'),
            baseClass: 'dijitButton, smallIcon'
        };
        var button = new dijit.form.Button(params);
        this.garbageCollector.addNode(button);

        node.appendChild(button.domNode);

        this.garbageCollector.addEvent(
            dojo.connect(button, "onClick",
                dojo.hitch(this, functionName, extraParams)));
    },

    setPermissions: function(data) {
        // Summary:
        //    Get the permission
        // Description:
        //    Get the permission for the current user on the item
        if (phpr.isAdminUser) {
            if (phpr.isGlobalModule(phpr.module)) {
                this._writePermissions  = true;
                this._deletePermissions = true;
                this._accessPermissions = false;
            } else {
                this._writePermissions  = true;
                this._deletePermissions = true;
                this._accessPermissions = true;
            }
        } else {
            if (this.id > 0) {
                if (phpr.isGlobalModule(phpr.module)) {
                    this._writePermissions  = true;
                    this._deletePermissions = true;
                    this._accessPermissions = false;
                } else {
                    this._writePermissions  = data[0].rights[phpr.currentUserId].write;
                    this._deletePermissions = data[0].rights[phpr.currentUserId]['delete'];
                    this._accessPermissions = data[0].rights[phpr.currentUserId].admin;
                }
            }
        }
    },

    addTab: function(innerWidgets, id, title, formId) {
        // Summary:
        //    Add a tab
        // Description:
        //    Add a tab and if have form, add the values
        //    to the array of values for save it later
        if (this._destroyed) {
            return;
        }

        var deferred = new dojo.Deferred();
        var ret = deferred.then(dojo.hitch(this, function() {
            if (this._destroyed) {
                return;
            }

            var content = new phpr.Default.System.TemplateWrapper({
                templateName: "phpr.Default.template.form.section.html",
                templateData: {
                    formId: formId || '',
                    title: phpr.nls.get(title)
                }
            });

            var l = innerWidgets.length;
            for (var i = 0; i < l; i++) {
                var widget = innerWidgets[i];
                content.formtable.appendChild(widget.domNode);
                this.garbageCollector.addNode(widget);
            }

            dojo.style(
                content.containerNode,
                {
                    'width': '100%',
                    'height': 'auto'
                }
            );

            content.startup();

            if (this.tabs._empty === false) {
                dojo.place("<hr>", this.form.containerNode);
            }

            dojo.place(content.domNode, this.form.containerNode);
            if (typeof content.tabform != "undefined") {
                this.tabs[id] = content;
                this.tabs._empty = false;
                this.formsWidget.push(content.tabform);
            }

            this.garbageCollector.addNode(content);

            content.tabform.onSubmit = dojo.hitch(this, "_submitForm");
        }));

        window.setTimeout(function() {
            deferred.callback();
        }, 0);

        return ret;
    },

    getTabs: function() {
        // Summary:
        //    Return the tab list for make the form
        // Description:
        //    Return the tab list for make the form or an empty array
        if (this.tabStore) {
            result = this.tabStore.getList();
        }
        return result;
    },

    getFormData: function() {
        // Summary:
        //    This function renders the form data according to the database manager settings
        // Description:
        //    This function processes the form data which is stored in a phpr.DataStore and
        //    renders the actual form according to the received data
        if (this._destroyed) {
            return;
        }

        this.formdata    = [];
        this.formdata[0] = [];

        this._subModules = this._getSubModules();

        var p = phpr;

        this._meta = p.DataStore.getMetaData({url: this._url});
        var data   = p.DataStore.getData({url: this._url});

        if (data.length === 0) {
            this.node.set('content', p.drawEmptyMessage('The Item was not found'));
            this._finishFormRendering();
        } else {
            var firstRequiredField = null;

            this.setPermissions(data);
            this.presetValues(data);
            this.fieldTemplate = new p.Default.Field();

            var itemdisabled = !this._writePermissions;

            var l = this._meta.length;
            for (var i = 0; i < l; i++) {
                var fieldValues  = this.setFieldValues(this._meta[i], data[0]);
                var itemtype     = fieldValues.type;
                var itemid       = fieldValues.id;
                var itemlabel    = fieldValues.label;
                var itemrequired = fieldValues.required;
                var itemlabel    = fieldValues.label;
                var itemvalue    = fieldValues.value;
                var itemrange    = fieldValues.range;
                var itemtab      = fieldValues.tab;
                var itemhint     = fieldValues.hint;
                var itemlength   = fieldValues.length;

                if (i === 0) {
                    this.setBreadCrumbItem(itemvalue);
                    p.BreadCrumb.draw();
                }

                // Get the first required field
                if (itemrequired && itemtype != 'hidden' && !firstRequiredField) {
                    firstRequiredField = itemid;
                }

                // Special workaround for new projects - set parent to current ProjectId
                if (itemid == 'projectId' && !itemvalue) {
                    itemvalue = p.currentProjectId;
                }

                // Init formdata
                if (!this.formdata[itemtab]) {
                    this.formdata[itemtab] = [];
                }

                // Render the fields according to their type
                switch (itemtype) {
                    case 'checkbox':
                        this.formdata[itemtab].push(this.fieldTemplate.checkRender(itemlabel, itemid, itemvalue,
                                                    itemdisabled, itemhint));
                        break;
                    case 'selectbox':
                        this.formdata[itemtab].push(this.fieldTemplate.selectRender(itemrange, itemlabel, itemid,
                                                    itemvalue, itemrequired, itemdisabled, itemhint));
                        break;
                    case 'multipleselectbox':
                        this.formdata[itemtab].push(this.fieldTemplate.multipleSelectRender(itemrange, itemlabel, itemid,
                                                    itemvalue, itemrequired, itemdisabled, itemhint));
                        break;
                    case 'multiplefilteringselectbox':
                        this.formdata[itemtab].push(this.fieldTemplate.multipleFilteringSelectRender(itemrange, itemlabel, itemid,
                                                    itemvalue, itemrequired, itemdisabled, itemhint));
                        break;
                    case 'date':
                        this.formdata[itemtab].push(this.fieldTemplate.dateRender(itemlabel, itemid, itemvalue,
                                                    itemrequired, itemdisabled, itemhint));
                        break;
                    case 'time':
                        this.formdata[itemtab].push(this.fieldTemplate.timeRender(itemlabel, itemid, itemvalue,
                                                    itemrequired, itemdisabled, itemhint));
                        break;
                    case 'datetime':
                        this.formdata[itemtab].push(this.fieldTemplate.datetimeRender(itemlabel, itemid, itemvalue,
                                                    itemrequired, itemdisabled, itemhint));
                        break;
                    case 'textarea':
                        this.formdata[itemtab].push(this.fieldTemplate.htmlAreaRender(itemlabel, itemid, itemvalue,
                                                    itemrequired, itemdisabled, itemhint));
                        break;
                    case 'simpletextarea':
                        this.formdata[itemtab].push(this.fieldTemplate.textAreaRender(itemlabel, itemid, itemvalue,
                                                    itemrequired, itemdisabled, itemhint));
                        break;
                    case 'password':
                        this.formdata[itemtab].push(this.fieldTemplate.passwordFieldRender(itemlabel, itemid, itemvalue,
                                                    itemlength, itemrequired, itemdisabled, itemhint));
                        break;
                    case 'percentage':
                        this.formdata[itemtab].push(this.fieldTemplate.percentageFieldRender(itemlabel, itemid, itemvalue,
                                                    itemrequired, itemdisabled, itemhint));
                        break;
                    case 'upload':
                        iFramePath              = this.getUploadIframePath(itemid);
                        this.formdata[itemtab].push(this.fieldTemplate.uploadFieldRender(itemlabel, itemid, itemvalue,
                                                    itemrequired, itemdisabled, iFramePath, itemhint));
                        break;
                    case 'hidden':
                        this.formdata[itemtab].push(this.fieldTemplate.hiddenFieldRender('', itemid, itemvalue,
                                                    itemrequired, itemdisabled, itemhint));
                        break;
                    case 'display':
                        this.formdata[itemtab].push(this.fieldTemplate.displayFieldRender(itemlabel, itemid, itemvalue,
                                                    itemhint, itemrange));
                        break;
                    case 'rating':
                        this.formdata[itemtab].push(this.fieldTemplate.ratingFieldRender(itemlabel, itemid, itemvalue,
                            itemdisabled, itemhint, itemrange));
                        break;
                    default:
                        this.formdata[itemtab].push(this.fieldTemplate.textFieldRender(itemlabel, itemid, itemvalue,
                                                    itemlength, itemrequired, itemdisabled, itemhint));
                        break;
                }
            }

            // add special inputs to the Basic Data
            this.addBasicFields();

            this.form        = this.setFormContent();
            this.formsWidget = [];

            var deferred = new dojo.Deferred();

            deferred.callback();

            var firstTab = true;
            var tabs = this.getTabs();
            var l = tabs.length;
            for (var t = 0; t < l; t++) {
                var tab = tabs[t];
                if (this.formdata[tab.id]) {
                    if (firstTab) {
                        this.setFormButtons(tab.id);
                        firstTab = false;
                    }
                    deferred = deferred.then(
                        dojo.hitch(this, function(tab) {
                            return this.addTab(this.formdata[tab.id], 'tabBasicData' + tab.id, tab.name,
                                'dataFormTab' + tab.id);
                        }, tab)
                    );
                }
            }

            deferred = dojo.when(deferred, dojo.hitch(this, function() {
                if (this._destroyed) {
                    return;
                }

                this.setActionFormButtons();
                return this.addModuleTabs(data);
            }));
            deferred = dojo.when(deferred, dojo.hitch(this, function() {
                if (this._destroyed) {
                    return;
                }

                return this.addSubModulesTab();
            }));
            deferred = dojo.when(deferred, dojo.hitch(this, function() {
                if (this._destroyed) {
                    return;
                }

                // Delete the data if is not used the cache
                if (!this.useCache()) {
                    p.DataStore.deleteData({url: this._url});
                }

                this.node.set('content', this.form);

                this._finishFormRendering();

                // Set cursor to the first required field
                if (dojo.byId(firstRequiredField)) {
                    dojo.byId(firstRequiredField).focus();
                }
            }));
        }
    },

    _finishFormRendering: function() {
        this.node.resize();

        this.postRenderForm();
        this._loadIndicator.hide();
    },

    setFieldValues: function(meta, data) {
        // Summary:
        //    Set the fields values for render the form
        // Description:
        //    Set the fields values for render the form
        var fieldValues = {
            type:     meta.type,
            id:       meta.key,
            label:    meta.label,
            disabled: meta.readOnly,
            required: meta.required,
            value:    data[meta.key],
            range:    meta.range,
            tab:      meta.tab || 1,
            hint:     meta.hint,
            length:   meta.length || 0
        };

        return this.setCustomFieldValues(fieldValues);
    },

    setCustomFieldValues: function(fieldValues) {
        // Summary:
        //    Custom function for setFieldValues
        // Description:
        //    Custom function for setFieldValues
        return fieldValues;
    },

    setFormButtons: function(tabId) {
        // Summary:
        //    Render the save and delete buttons
        // Description:
        //    Render the save and delete buttons
        if (this._destroyed) {
            return;
        }

        var buttons = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Default.template.form.buttons.html",
            templateData: {
                writePermissions:  this._writePermissions,
                deletePermissions: this._deletePermissions,
                saveText:          phpr.nls.get('Save'),
                deleteText:        phpr.nls.get('Delete')
            }
        });
        this.garbageCollector.addNode(buttons);
        this._splitContainer.buttonsContainer.set('content', buttons);
    },

    setActionFormButtons: function() {
        // Summary:
        //    Connect the buttons to the actions

        if (dijit.byId("submitButton")) {
            dijit.byId("submitButton").onClick = dojo.hitch(this, "_submitForm");
        }

        if (dijit.byId("deleteButton")) {
            this.garbageCollector.addEvent(
                    dojo.connect(dijit.byId("deleteButton"),
                        "onClick", dojo.hitch(this, function() {
                            this.garbageCollector.addNode(
                                phpr.confirmDialog(
                                    dojo.hitch(this, "deleteForm"),
                                    phpr.nls.get('Are you sure you want to delete?')));
                        })));
        }
    },

    useCache: function() {
        // Summary:
        //    Return true or false if the cache is used
        // Description:
        //    Return true or false if the cache is used
        return true;
    },

    _formCallback: function() {
        phpr.viewManager.getView().completeContent.domNode.focus();
    },

    setFormContent: function() {
        // Summary:
        //    Set the Container
        // Description:
        //    Set the Container

        var container = new dijit.layout.ContentPane({}, dojo.create('div'));

        this.garbageCollector.addNode(container);

        return container;
    },

    addModuleTabs: function(data) {
        // Summary:
        //    Add all the tabs
        // Description:
        //    Add all the tabs that are not the basic data
        if (this._destroyed) {
            return;
        }

        var def = this.addAccessTab(data);
        def = dojo.when(
            def,
            dojo.hitch(this, function() {
                    return this.addNotificationTab(data);
                }
            )
        );
        def = dojo.when(
            def,
            dojo.hitch(this, function() {
                    return this.addHistoryTab();
                }
            )
        );
        return def;
    },

    addHistoryTab: function() {
        // Summary:
        //    History tab
        // Description:
        //    Display all the history of the item
        if (this._destroyed) {
            return;
        }

        if (this.id > 0 && this.useHistoryTab()) {
            var widget = new phpr.Default.System.TemplateWrapper({
                templateName: "phpr.Default.template.history.content.html"
            });
            this._historyContent = widget;
            this.garbageCollector.addNode(widget);
            var ret = this.addTab([widget], 'tabHistory', 'History', 'accesshistoryTab');
            return ret.then(dojo.hitch(this, "showHistory"));
        }
    },

    addSubModulesTab: function() {
        // Summary:
        //    Add SubModules tabs
        // Description:
        //    Add all the SubModules that have the current module
        if (this._destroyed) {
            return;
        }

        var def = new dojo.Deferred();
        def.callback();

        var subModules = this._subModules;
        // Add the tabs
        for (var index in subModules) {
            var subModuleName = subModules[index].name;
            def = def.then(dojo.hitch(this, function(name) {
                if (this._destroyed) {
                    return;
                }
                return subModules[index]['class'].createTab(this);
            }, subModuleName));
        }
        this.form.resize();

        return def;
    },

    _getSubModules: function() {
        var subModules = [];
        if (this.id > 0) {
            // Set the sub modules data
            var nextPosition = 0;
            for (var index in this.main.subModules) {
                var subModuleName  = this.main.subModules[index];
                var subModuleClass = 'phpr.' + subModuleName + '.Main';
                var subModule      = eval('new ' + subModuleClass + '(' + this.id + ')');
                var sort           = (subModule.sortPosition) ? subModule.sortPosition : nextPosition++;
                subModules.push({
                    'sort':  sort,
                    'name':  subModuleName,
                    'class': subModule
                });
            }

            // Sort the sub modules
            subModules.sort(function(a, b) {
                return a.sort - b.sort;
            });
        }

        return subModules;
    },

    useHistoryTab: function() {
        //    Return true or false if the history tab is used
        // Description:
        //    Return true or false if the history tab is used
        return true;
    },

    addBasicFields: function() {
        // Summary:
        //    Add some special fields
        // Description:
        //    Add some special fields
        this.formdata[1].push(this.displayTagInput());
    },

    postRenderForm: function() {
        // Summary:
        //    User functions after render the form
        // Description:
        //    Apply for special events on the fields
    },

    newAccess: function() {
        // Summary:
        //    Add a new row of one user-accees
        // Description:
        //    Add a the row of one user-accees
        //    with the values selected on the first row
        var userId = this._accessTab.dataAccessAdd.get('value');
        var data = {
            userId:          userId,
            disabled:    (!this._accessPermissions) ? 'disabled="disabled"' : '',
            userDisplay: this._accessTab.dataAccessAdd.get('displayedValue'),
            currentUser: false
        };

        if (!data.userDisplay) {
            return;
        }

        for (var i in this._rights) {
            var fieldAddId = 'check' + this._rights[i] + 'AccessAdd';
            data[this._rights[i].toLowerCase()] = this._accessTab[fieldAddId].checked;
        }

        this._addAccessTabRow(data, phpr.currentUserId);
    },

    deleteAccess: function(userId) {
        // Summary:
        //    Remove the row of one user-accees
        // Description:
        //    Remove the row of one user-accees
        //    and destroy all the used widgets
        phpr.destroyWidget("dataAccess[" + userId + "]");
        for (var i in this._rights) {
            var fieldId = 'check' + this._rights[i] + 'Access[' + userId + ']';
            phpr.destroyWidget(fieldId);
        }
        phpr.destroyWidget("accessDeleteButton" + userId);

        var e      = dojo.byId("trAccessFor" + userId);
        var parent = e.parentNode;
        parent.removeChild(e);
    },

    checkAllAccess: function(str) {
        // Summary:
        //    Select all the access
        // Description:
        //    Select all the access
        if (dijit.byId("checkAdminAccess" + str).checked) {
            for (var i in this._rights) {
                var fieldId = 'check' + this._rights[i] + 'Access' + str;
                dijit.byId(fieldId).set('checked', true);
            }
        }
    },

    prepareSubmission: function() {
        // Summary:
        //    This function prepares the data for submission
        // Description:
        //    This function prepares the content of this.sendData before it is
        //    submitted to the Server.
        this.sendData = {};
        for (var i = 0; i < this.formsWidget.length; i++) {
            if (!this.formsWidget[i].isValid()) {
                this.formsWidget[i].validate();
                return false;
            }
            var sendData = this.formsWidget[i].get('value');
            if (typeof(sendData) != 'object') {
                sendData = new Array(sendData);
            } else {
                for (var k in sendData) {
                    // Allow empty arrays, set the value to an empty string
                    if (sendData[k] && typeof(sendData[k]) == 'object' && sendData[k].length === 0) {
                        sendData[k] = new Array("");
                    }
                }
            }
            dojo.mixin(this.sendData, sendData);
        }

        return true;
    },

    _submitForm: function(evt) {
        // Summary:
        //    Event handler for submit events
        // Description:
        //    Triggers submitForm and prevents the event from bubbeling upwards
        var ret;
        if (!this.isSubmitInProgress()) {
            ret = this.submitForm(evt);
        }
        dojo.stopEvent(evt);
        return ret || false;
    },

    submitForm: function(evt) {
        // Summary:
        //    This function is responsible for submitting the formdata
        // Description:
        //    This function sends the form data as json data to the server
        //    and call the reload routine

        if (!this.prepareSubmission()) {
            return;
        }

        this.setSubmitInProgress(true);
        var pid = phpr.currentProjectId;

        phpr.send({
            url: 'index.php/' + phpr.module +
            '/index/jsonSave/nodeId/' + pid +
            '/id/' + this.id,
            content:   this.sendData
        }).then(dojo.hitch(this, function(data) {
            if (data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    if (data.id) {
                        this.id = data.id;
                    }
                    return phpr.send({
                        url: 'index.php/Default/Tag/jsonSaveTags/moduleName/' +
                        phpr.module + '/id/' + this.id,
                        content: this.sendData
                    });
                } else {
                    this.setSubmitInProgress(false);
                }
            } else {
                this.setSubmitInProgress(false);
            }
        })).then(dojo.hitch(this, function(data) {
            this.setSubmitInProgress(false);
            if (data) {
                if (this.sendData.string) {
                    new phpr.handleResponse('serverFeedback', data);
                }
                if (data.type == 'success') {
                    this.publish("updateCacheData");
                    // reload the page and trigger the form load
                    phpr.pageManager.modifyCurrentState(
                        {
                            moduleName: phpr.module,
                            projectId: pid,
                            id: undefined
                        }, {
                            forceModuleReload: true
                        }
                    );
                }
            }
        }));

    },

    setSubmitInProgress: function(inProgress) {
        this._submitInProgress = inProgress;
    },

    isSubmitInProgress: function() {
        return this._submitInProgress;
    },

    deleteForm: function() {
        // Summary:
        //    This function is responsible for deleting a dojo element
        // Description:
        //    This function calls jsonDeleteAction

        var pid = phpr.currentProjectId;

        phpr.send({
            url: 'index.php/' + phpr.module + '/index/jsonDelete/id/' + this.id
        }).then(dojo.hitch(this, function(data) {
            if (data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    return phpr.send({
                        url: 'index.php/Default/Tag/jsonDeleteTags/moduleName/' + phpr.module + '/id/' + this.id
                    });
                }
            }
        })).then(dojo.hitch(this, function(data) {
            if (data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    this.publish("updateCacheData");
                    // reload the page
                    phpr.pageManager.modifyCurrentState({
                        moduleName: phpr.module,
                        projectId: pid,
                        id: undefined
                    });
                }
            }
        }));
    },

    displayTagInput: function() {
        // Summary:
        // This function manually receives the Tags for the current element
        // Description:
        // By calling the TagController this function receives all data it needs
        // for rendering a Tag from the server and renders those tags in a Input separated by coma
        // The function also render the tags in the moveable pannel for click it and search by tags
        var currentTags = phpr.DataStore.getData({url: this._tagUrl});
        var meta        = phpr.DataStore.getMetaData({url: this._tagUrl});
        var value       = '';

        if (this.id > 0) {
            for (var i = 0; i < currentTags.length; i++) {
                value += currentTags[i];
                if (i != currentTags.length - 1) {
                    value += ' ';
                }
            }
        }

        return this.fieldTemplate.textFieldRender(meta[0].label, meta[0].key, value, 0, false, !this._writePermissions);
    },

    showHistory: function() {
        // Summary:
        //    This function renders the history data
        // Description:
        //    This function renders the history data
        if (this.id > 0 && this._historyContent !== null) {
            this._historyContent.historyContent.set('content', '');
            this._historyUrl = 'index.php/Core/history/jsonList/nodeId/1/moduleName/' +
                phpr.module + '/itemId/' + this.id;
            phpr.DataStore.addStore({'url': this._historyUrl, 'noCache': true});
            phpr.DataStore.requestData({'url': this._historyUrl}).then(dojo.hitch(this,
                function() {
                    var widget = new phpr.Default.System.TemplateWrapper({
                        templateName: "phpr.Default.template.history.data.html",
                        templateData: {
                            dateTxt:     phpr.nls.get('Date'),
                            userTxt:     phpr.nls.get('User'),
                            fieldTxt:    phpr.nls.get('Field'),
                            oldValueTxt: phpr.nls.get('Old value'),
                            newValueTxt: phpr.nls.get('New value'),
                            data:        this.getHistoryData()
                        }
                    });
                    this._historyContent.historyContent.set('content', widget);
                    this.garbageCollector.addNode(widget);
                    dojo.style(this._historyContent.domNode, { height: "100%" });
                })
            );
        }
    },

    getHistoryData: function() {
        // Summary:
        //    This function collect and process the history data
        // Description:
        //    This function collect and process the history data
        var history = phpr.DataStore.getData({url: this._historyUrl});
        var userList = this.userStore ? this.userStore.getList() : phpr.userStore.getList();
        var historyData = [];
        var userDisplay = [];
        var row         = 0;
        var trClass;

        for (var i = 0; i < history.length; i++) {
            // Search for the user name
            if (!userDisplay[history[i].userId]) {
                for (var u in userList) {
                    if (userList[u].id == history[i].userId) {
                        userDisplay[history[i].userId] = userList[u].display;
                        break;
                    }
                }
            }
            if (userDisplay[history[i].userId]) {
                historyUser = userDisplay[history[i].userId];
            } else {
                historyUser = '';
            }
            historyModule   = history[i].moduleId;
            historyItemId   = history[i].itemId;
            historyField    = history[i].label || '';
            historyOldValue = history[i].oldValue || '';
            historyNewValue = history[i].newValue || '';
            historyAction   = history[i].action;
            historyDate     = history[i].datetime;

            if (Math.floor(row / 2) == (row / 2)) {
                trClass = 'grey';
            } else {
                trClass = 'white';
            }

            historyData.push({
                trClass:  trClass,
                date:     historyDate,
                user:     historyUser,
                field:    historyField,
                oldValue: historyOldValue,
                newValue: historyNewValue
            });

            row++;
        }

        return historyData;
    },

    updateData: function() {
        // Summary:
        //    Delete the cache for this form
        // Description:
        //    Delete the cache for this form
        phpr.DataStore.deleteData({url: this._url});
        phpr.DataStore.deleteData({url: this._tagUrl});
        phpr.DataStore.deleteData({url: this._accessUrl});
    },

    addNotificationTab: function(data) {
        // Summary:
        //    Adds a tab for sending a notification.
        // Description:
        //    Adds a tab for sending a notification to the users with read access, telling them about the item added
        //    or modified. It has a "Send Notification" checkbox.
        if (this._destroyed) {
            return;
        }

        // Default value
        var defaultValue = (phpr.config.notificationEnabledByDefault) ? 1 : 0;
        // Add field
        var notificationTab = this.fieldTemplate.checkRender(phpr.nls.get('Send Notification'), 'sendNotification',
            defaultValue, false, phpr.nls.get('Check this box to send an email notification to the participants'));
        // Add the tab to the form
        return this.addTab([ notificationTab ], 'tabNotify', 'Notification', 'accessnotificationTab');
    },

    presetValues: function(data) {
        // Summary:
        //    Function used to preset values in the form.
        // Description:
        //    The form is able to receive some values when it is instanced for adding and item, and put that values
        //    in each field.
        if (this._presetValues && (typeof this._presetValues === 'object')) {
            for (var field in this._presetValues) {
                data[0][field] = this._presetValues[field];
            }
        }
    },

    setBreadCrumbItem: function(itemValue) {
        // Summary:
        //    Set the Breadcrumb with the first item value
        // Description:
        //    Set the Breadcrumb with the first item value
        phpr.BreadCrumb.setItem(itemValue);
    },

    highlightChanges: function(data) {
        // Summary:
        //    Highlights changes done by any other user with a style comming from the CSS class "highlightChanges".
        // Description:
        //    Checks if I am on the same data record as the user who changes something.
        //    If so, adds the CSS class "highlightChanges" to the form element
        //    (typicall border: 3px solid #ff0000) and overwrites the given value with the new one.
        var details    = data.details;
        var detailsLen = details.length;
        for (var i = 0; i < detailsLen; i++) {
            var field = details[i].field;
            var value = details[i].newValue;

            // Search the field
            for (var k = 0; k < this._meta.length; k++) {
                if (this._meta[k].key == field) {
                    switch (this._meta[k].type) {
                        case 'datetime':
                            // Split the value to two values
                            var dateTime = value.split(" ");
                            var time     = dateTime[1].slice(0, 5);

                            var key          = field + '_forDate';
                            var displayfield = 'widget_' + key;
                            var fieldWidget = dijit.byId(key);
                            if (fieldWidget) {
                                fieldWidget.set("displayedValue", dateTime[0]);
                                dojo.addClass(dojo.byId(displayfield), "highlightChanges");
                            }

                            var key          = field + '_forTime';
                            var displayfield = 'widget_' + key;
                            fieldWidget = dijit.byId(key);
                            if (fieldWidget) {
                                fieldWidget.set("displayedValue", time);
                                dojo.addClass(dojo.byId(displayfield), "highlightChanges");
                            }
                            break;
                        case 'selectbox':
                            var displayfield = 'widget_' + field;
                            var fieldWidget = dijit.byId(field);
                            if (fieldWidget) {
                                fieldWidget.set("value", value);
                                dojo.addClass(dojo.byId(displayfield), "highlightChanges");
                            }
                            break;
                        case 'date':
                            var displayfield = 'widget_' + field;
                            var fieldWidget = dojo.byId(field);
                            if (fieldWidget) {
                                fieldWidget.value = value;
                                dojo.addClass(dojo.byId(displayfield), "highlightChanges");
                            }
                            break;
                        case 'time':
                            var displayfield = 'widget_' + field;
                            var fieldWidget = dojo.byId(field);
                            if (fieldWidget) {
                                fieldWidget.value = value.slice(0, 5);
                                dojo.addClass(dojo.byId(displayfield), "highlightChanges");
                            }
                            break;
                        case 'percentage':
                            var displayfield = field;
                            var fieldWidget = dijit.byId(field);
                            if (fieldWidget) {
                                fieldWidget.set('value', value);
                                dojo.addClass(dojo.byId(displayfield), "highlightChanges");
                            }
                            break;
                        case 'upload':
                            var displayfield = 'filesIframe_' + field;
                            var fieldWidget = dijit.byId(field);
                            if (fieldWidget) {
                                fieldWidget.set('value', value);
                                dojo.byId('filesIframe_files').contentDocument.location.href =
                                    'index.php/Default/File/fileForm/moduleName/' +
                                    phpr.module + '/id/' + this.id + '/field/' +
                                    field + '/value/' + value;
                                dojo.addClass(dojo.byId(displayfield), "highlightChanges");
                            }
                            break;
                        case 'checkbox':
                            var displayfield = field;
                            var fieldWidget = dijit.byId(field);
                            if (fieldWidget) {
                                value = (value == 1) ? true : false;
                                fieldWidget.set('checked', value);
                                dojo.addClass(dojo.byId(displayfield).parentNode, "highlightChanges");
                            }
                            break;
                        case 'multipleselectbox':
                            var displayfield = field + '[]';
                            var fieldWidget = dijit.byId(displayfield);
                            if (fieldWidget) {
                                value = value.split(',');
                                fieldWidget.set("value", value);
                                dojo.addClass(dojo.byId(displayfield).parentNode, "highlightChanges");
                            }
                            break;
                        case 'rating':
                            var displayfield = field;
                            var fieldWidget = dijit.byId(field);
                            if (fieldWidget) {
                                fieldWidget.setAttribute('value', value);
                                dojo.addClass(dojo.byId(displayfield).parentNode, "highlightChanges");
                            }
                            break;
                        default:
                            var displayfield = field;
                            var fieldWidget = dijit.byId(field);
                            if (fieldWidget) {
                                fieldWidget.set("displayedValue", value);
                                dojo.addClass(dojo.byId(displayfield), "highlightChanges");
                            }
                            break;
                    }
                    break;
                }
            }
        }
    },

    getUploadIframePath: function(itemid) {
        return 'index.php/' + phpr.module + '/index/fileForm' +
            '/nodeId/' + phpr.currentProjectId + '/id/' + this.id + '/field/' +
            itemid + '/csrfToken/' + phpr.csrfToken;
    }
});

dojo.declare("phpr.Default.DialogForm", phpr.Default.Form, {
    constructor: function() {
        this._resizeSubscribe = dojo.subscribe("phpr.resize", this, '_onResize');
    },

    destroy: function() {
        this.inherited(arguments);
        dojo.unsubscribe(this._resizeSubscribe);
        this._resizeSubscribe = null;
        this.dialog.destroyRecursive();
        this.dialog = null;
    },

    setFormButtons: function() {
        if (this._destroyed) {
            return;
        }

        this.buttons.set('content', phpr.fillTemplate("phpr.Default.template.form.dialogButtons.html", {
                writePermissions:  this._writePermissions,
                deletePermissions: this._deletePermissions,
                saveText:          phpr.nls.get('Save'),
                deleteText:        phpr.nls.get('Delete')
            }));
    },

    setContainer: function(container) {
        this.node = new dijit.layout.ContentPane({style: "width: 100%; height: 100%; overflow: hidden;"});
        this.buttons = new dijit.layout.ContentPane({style: "width: 100%; height: 30px; padding-top: 10px;"});
        //draggable = false must be set because otherwise the dialog can not be closed on the ipad
        //bug: http://bugs.dojotoolkit.org/ticket/13488
        this.dialog = new dijit.Dialog({style: "width: 80%; height: 80%;", draggable: false});

        this.dialog.show();

        this.dialog.containerNode.appendChild(this.node.domNode);
        this.dialog.containerNode.appendChild(this.buttons.domNode);

        this._setNodeSizes();

        this.node.startup();
        this.garbageCollector.addNode(this.node);
        this.garbageCollector.addNode(this.buttons);
        this.garbageCollector.addNode(this.dialog);

        // remove the form opening part from the url
        this.garbageCollector.addEvent(
            dojo.connect(this.dialog, "onHide",
                dojo.hitch(this, function() {
                    phpr.pageManager.modifyCurrentState({
                            id: undefined
                        }, {
                            noAction: true
                        }
                    );
                })));
    },

    _setNodeSizes: function() {
        var dialogBox = dojo.contentBox(this.dialog.domNode);
        var dialogTitleBox = dojo.contentBox(this.dialog.titleBar);
        var dialogContainerBox = dojo.contentBox(this.dialog.containerNode);

        dojo.style(this.dialog.containerNode, {
            height: (dialogBox.h - dialogTitleBox.h - dialogContainerBox.t - 50) + 'px',
            width: dialogTitleBox.w - dialogContainerBox.l + 'px'
        });
    },

    _onResize: function() {
        this.dialog.resize();
        this._setNodeSizes();
        this.form.resize();
    }
});
