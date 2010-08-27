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
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Default.Form");

dojo.declare("phpr.Default.Form", phpr.Component, {
    // Summary:
    //    Class for displaying a PHProjekt Detail View
    // Description:
    //    This Class takes care of displaying the form information we receive from our Server
    //    in a dojo form with tabs

    sendData:           new Array(),
    formdata:           new Array(),
    _url:               null,
    _formNode:          null,
    _writePermissions:  true,
    _deletePermissions: false,
    _accessPermissions: true,
    _initData:          new Array(),
    _tagUrl:            null,
    _accessUrl:         null,
    _historyUrl:        null,
    _presetValues:      null,
    _htmlEditorWidget:  null,
    _meta:              null,
    _rights:            new Array('Read', 'Write', 'Access', 'Create', 'Copy', 'Delete', 'Download', 'Admin'),

    constructor:function(main, id, module, params) {
        // Summary:
        //    render the form on construction
        // Description:
        //    this function receives the form data from the server and renders the corresponding form
        //    If the module is a param, is setted
        this.main = main;
        this.id   = id;

        if (undefined != module) {
            phpr.module = module
        }
        if (undefined != params) {
            this._presetValues = params;
        }

        this.setUrl();
        this.setNode();

        // Put loading
        this.render(["phpr.Default.template.form", "loading.html"], this._formNode.domNode, {
            webpath: phpr.webpath
        });

        this._initData.push({'url': this._url, 'processData': dojo.hitch(this, "getFormData")});
        this.tabStore = new phpr.Store.Tab();
        this._initData.push({'store': this.tabStore});
        this.initData();
        this.getInitData();
    },

    setUrl:function() {
        // Summary:
        //    Set the url for get the data
        // Description:
        //    Set the url for get the data
        this._url = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonDetail/nodeId/' + phpr.currentProjectId
            + '/id/' + this.id;
    },

    setNode:function() {
        // Summary:
        //    Set the node where put the form
        // Description:
        //    Set the node where put the form
        this._formNode = dijit.byId("detailsBox");
    },

    getInitData:function() {
        // Summary:
        //    Process all the POST in cascade for get all the data from the server
        // Description:
        //    Process all the POST in cascade for get all the data from the server
        var params = this._initData.pop();

        if (params.url || params.store) {
            if (!params.noCache) {
                params.noCache = false;
            }
            if (!params.processData) {
                params.processData = dojo.hitch(this, "getInitData");
            }
        }

        if (params.url) {
            phpr.DataStore.addStore({'url': params.url, 'noCache': params.noCache});
            phpr.DataStore.requestData({'url': params.url, 'processData': params.processData});
        } else if (params.store) {
            params.store.fetch(params.processData);
        }
    },

    initData:function() {
        // Summary:
        //    Init all the data before draw the form
        // Description:
        //    This function call all the needed data before the form is drawed
        //    The form will wait for all the data are loaded.
        //    Each module can overwrite this function for load the own data

        // Get the rights for other users
        this._accessUrl = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonGetUsersRights'
            + '/nodeId/' + phpr.currentProjectId + '/id/' + this.id;
        this._initData.push({'url': this._accessUrl});

        // Get all the active users
        this.userStore = new phpr.Store.User();
        this._initData.push({'store': this.userStore});

        // Get the tags
        this._tagUrl  = phpr.webpath + 'index.php/Default/Tag/jsonGetTagsByModule/moduleName/' + phpr.module
            + '/id/' + this.id;
        this._initData.push({'url': this._tagUrl});
    },

    addAccessTab:function(data) {
        // Summary:
        //    Access tab
        // Description:
        //    Display all the users and the acces
        //    The user can assign to each user different access on the item
        var userList      = this.userStore.getList();
        var accessContent = phpr.DataStore.getData({url: this._accessUrl});
        var currentUser   = data[0]["rights"]["currentUser"]["userId"] || 0;
        var users         = new Array();

        if (userList) {
            for (var i in userList) {
                // Make an array with the users except the current one and the admin
                if (userList[i].id != currentUser && userList[i].id != 1) {
                    users.push({'id': userList[i].id, 'display': userList[i].display});
                }
                // Found the name of each user
                for (j in accessContent) {
                    if (userList[i].id == accessContent[j].userId) {
                        accessContent[j].userDisplay = userList[i].display;
                        break;
                    }
                }
            }
        }

        var rows = '';
        for (var id in accessContent) {
            if (accessContent[id]['userDisplay']) {
                var isCurrentUser = (id == 'currentUser');
                var checkBoxs     = new Array();
                var userId        = isCurrentUser ? currentUser : accessContent[id]['userId'];
                if (userId == 1 && currentUser != 1) {
                    continue;
                }
                for (var i in this._rights) {
                    var fieldId = 'check' + this._rights[i] + 'Access[' + userId + ']';
                    checkBoxs.push(this.render(["phpr.Default.template.access", "checkbox.html"], null, {
                        fieldId:  fieldId,
                        checked:  accessContent[id][this._rights[i].toLowerCase()] ? 'checked' : '',
                        hidden:   (isCurrentUser && this._accessPermissions),
                        value:    (accessContent[id][this._rights[i].toLowerCase()]) ? 1 : 0,
                        disabled: (isCurrentUser || !this._accessPermissions) ? 'disabled="disabled"' : ''
                    }));
                }
                var input = this.render(["phpr.Default.template.access", "input.html"], null, {
                    id:          userId,
                    disabled:    (!this._accessPermissions) ? 'disabled="disabled"' : '',
                    userDisplay: accessContent[id]['userDisplay'],
                    currentUser: isCurrentUser
                });
                var button = this.render(["phpr.Default.template.access", "button.html"], null, {
                    id:        userId,
                    useDelete: !isCurrentUser
                });
                rows += this.render(["phpr.Default.template.access", "row.html"], null, {
                    id:        userId,
                    input:     input,
                    checkBoxs: checkBoxs,
                    button:    button
                });
            }
        }

        // Template for the access tab
        var accessData = this.render(["phpr.Default.template.access", "tab.html"], null, {
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
            users:              users,
            rows:               rows
        });

        this.addTab(accessData, 'tabAccess', 'Access', 'accessFormTab');

        // Add "add" button for access
        if (this._accessPermissions && users.length > 0) {
            this.addTinyButton('add', 'accessAddButton', 'newAccess');
            dojo.connect(dijit.byId("checkAdminAccessAdd"), "onClick", dojo.hitch(this, "checkAllAccess", "Add"));
        }

        if (this._accessPermissions) {
            // Add "delete" buttons for access
            // Add "check all" functions
            for (i in accessContent) {
                if (accessContent[i]['userDisplay']) {
                    var userId = accessContent[i]["userId"];
                    if (userId != currentUser && userId != 1) {
                        this.addTinyButton('delete', 'accessDeleteButton' + userId, 'deleteAccess', [userId]);
                        dojo.connect(dijit.byId("checkAdminAccess[" + userId + "]"), "onClick",
                            dojo.hitch(this, "checkAllAccess", "[" + userId + "]"));
                    }
                }
            }
        }
    },

    addTinyButton:function(type, nodeId, functionName, extraParams) {
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
        dojo.byId(nodeId).appendChild(button.domNode);
        dojo.connect(button, "onClick", dojo.hitch(this, functionName, extraParams));
    },

    setPermissions:function(data) {
        // Summary:
        //    Get the permission
        // Description:
        //    Get the permission for the current user on the item
        if (this.id > 0) {
            if (phpr.isGlobalModule(phpr.module)) {
                this._writePermissions  = true;
                this._deletePermissions = true;
                this._accessPermissions = false;
            } else {
                this._writePermissions  = data[0]["rights"]["currentUser"]["write"];
                this._deletePermissions = data[0]["rights"]["currentUser"]["delete"];
                this._accessPermissions = data[0]["rights"]["currentUser"]["admin"];
            }
        }
    },

    addTab:function(innerTabs, id, title, formId) {
        // Summary:
        //    Add a tab
        // Description:
        //    Add a tab and if have form, add the values
        //    to the array of values for save it later
        phpr.destroyWidget(id);
        phpr.destroyWidget(formId);
        var html = this.render(["phpr.Default.template.form", "tabs.html"], null, {
            innerTabs: innerTabs,
            formId:    formId || ''
        });
        var tab = new dijit.layout.ContentPane({
            id:    id,
            title: phpr.nls.get(title)
        });
        tab.set('content', html);
        this.form.addChild(tab);
        if (typeof formId != "undefined") {
            this.formsWidget.push(dijit.byId(formId));
        }
    },

    getTabs:function() {
        // Summary:
        //    Return the tab list for make the form
        // Description:
        //    Return the tab list for make the form or an empty array
        if (this.tabStore) {
            result = this.tabStore.getList();
        }
        return result;
    },

    getFormData:function(items, request) {
        // Summary:
        //    This function renders the form data according to the database manager settings
        // Description:
        //    This function processes the form data which is stored in a phpr.DataStore and
        //    renders the actual form according to the received data
        this.formdata    = new Array();
        this.formdata[0] = new Array();

        this._meta = phpr.DataStore.getMetaData({url: this._url});
        var data   = phpr.DataStore.getData({url: this._url});
        if (data.length == 0) {
            this._formNode.set('content', phpr.drawEmptyMessage('The Item was not found'));
        } else {
            var tabs               = this.getTabs();
            var firstRequiredField = null;

            this.setPermissions(data);
            this.presetValues(data);
            this.fieldTemplate = new phpr.Default.Field();

            for (var i = 0; i < this._meta.length; i++) {
                var fieldValues  = this.setFieldValues(this._meta[i], data[0]);
                var itemtype     = fieldValues['type'];
                var itemid       = fieldValues['id'];
                var itemlabel    = fieldValues['label'];
                var itemdisabled = fieldValues['disabled'];
                var itemrequired = fieldValues['required'];
                var itemlabel    = fieldValues['label'];
                var itemvalue    = fieldValues['value'];
                var itemrange    = fieldValues['range'];
                var itemtab      = fieldValues['tab'];
                var itemhint     = fieldValues['hint'];
                var itemlength   = fieldValues['length'];

                if (i == 0) {
                    this.setBreadCrumbItem(itemvalue);
                    phpr.BreadCrumb.draw();
                }

                // Get the first required field
                if (itemrequired && itemtype != 'hidden' && !firstRequiredField) {
                    firstRequiredField = itemid;
                }

                // Special workaround for new projects - set parent to current ProjectId
                if (itemid == 'projectId' && !itemvalue){
                    itemvalue = phpr.currentProjectId;
                }

                // Init formdata
                if (!this.formdata[itemtab]) {
                    this.formdata[itemtab] = '';
                }

                // Render the fields according to their type
                switch (itemtype) {
                    case 'checkbox':
                        this.formdata[itemtab] += this.fieldTemplate.checkRender(itemlabel, itemid, itemvalue,
                                                    itemdisabled, itemhint);
                        break;
                    case 'selectbox':
                        this.formdata[itemtab] += this.fieldTemplate.selectRender(itemrange, itemlabel, itemid,
                                                    itemvalue, itemrequired, itemdisabled, itemhint);
                        break;
                    case 'multipleselectbox':
                        this.formdata[itemtab] += this.fieldTemplate.multipleSelectRender(itemrange, itemlabel, itemid,
                                                    itemvalue, itemrequired, itemdisabled, itemhint);
                        break;
                    case 'date':
                        this.formdata[itemtab] += this.fieldTemplate.dateRender(itemlabel, itemid, itemvalue,
                                                    itemrequired, itemdisabled, itemhint);
                        break;
                    case 'time':
                        this.formdata[itemtab] += this.fieldTemplate.timeRender(itemlabel, itemid, itemvalue,
                                                    itemrequired, itemdisabled, itemhint);
                        break;
                    case 'datetime':
                        this.formdata[itemtab] += this.fieldTemplate.datetimeRender(itemlabel, itemid, itemvalue,
                                                    itemrequired, itemdisabled, itemhint);
                        break;
                    case 'textarea':
                        this.formdata[itemtab] += this.fieldTemplate.htmlAreaRender(itemlabel, itemid, itemvalue,
                                                    itemrequired, itemdisabled, itemhint);
                        break;
                    case 'simpletextarea':
                        this.formdata[itemtab] += this.fieldTemplate.textAreaRender(itemlabel, itemid, itemvalue,
                                                    itemrequired, itemdisabled, itemhint);
                        break;
                    case 'password':
                        this.formdata[itemtab] += this.fieldTemplate.passwordFieldRender(itemlabel, itemid, itemvalue,
                                                    itemlength, itemrequired, itemdisabled, itemhint);
                        break;
                    case 'percentage':
                        this.formdata[itemtab] += this.fieldTemplate.percentageFieldRender(itemlabel, itemid, itemvalue,
                                                    itemrequired, itemdisabled, itemhint);
                        break;
                    case 'upload':
                        iFramePath = phpr.webpath + 'index.php/Default/File/fileForm/moduleName/' + phpr.module
                            + '/id/' + this.id + '/field/' + itemid
                            + '/csrfToken/' + phpr.csrfToken;
                        this.formdata[itemtab] += this.fieldTemplate.uploadFieldRender(itemlabel, itemid, itemvalue,
                                                    itemrequired, itemdisabled, iFramePath, itemhint);
                        break;
                    case 'hidden':
                        this.formdata[itemtab] += this.fieldTemplate.hiddenFieldRender('', itemid, itemvalue,
                                                    itemrequired, itemdisabled, itemhint);
                        break;
                    case 'display':
                        this.formdata[itemtab] += this.fieldTemplate.displayFieldRender(itemlabel, itemid, itemvalue,
                                                    itemhint, itemrange);
                        break;
                    case 'rating':
                        this.formdata[itemtab] += this.fieldTemplate.ratingFieldRender(itemlabel, itemid, itemvalue,
                            itemdisabled, itemhint, itemrange);
                        break;
                    default:
                        this.formdata[itemtab] += this.fieldTemplate.textFieldRender(itemlabel, itemid, itemvalue,
                                                    itemlength, itemrequired, itemdisabled, itemhint);
                        break;
                }
            }

            // add special inputs to the Basic Data
            this.addBasicFields();

            this.form        = this.setFormContent();
            this.formsWidget = new Array();

            this._formNode.set('content', this.form.domNode);

            var firstTab = true;
            for (t in tabs) {
                if (this.formdata[tabs[t].id]) {
                    if (firstTab) {
                        this.setFormButtons(tabs[t].id);
                        firstTab = false;
                    }
                    this.addTab(this.formdata[tabs[t].id], 'tabBasicData' + tabs[t].id, tabs[t].name,
                        'dataFormTab' + tabs[t].id);
                }
            }

            this.setActionFormButtons();
            this.addModuleTabs(data);
            this.addSubModulesTab();

            // Delete the data if is not used the cache
            if (!this.useCache()) {
                phpr.DataStore.deleteData({url: this._url});
            }

            if (this.id > 0 && this.useHistoryTab()) {
                dojo.connect(dijit.byId("tabHistory"), "onShow", dojo.hitch(this, "showHistory"));
            }

            // Set cursor to the first required field
            if (dojo.byId(firstRequiredField)) {
                dojo.byId('completeContent').focus();
                dojo.byId(firstRequiredField).focus();
            }

            this.postRenderForm();
        }
    },

    setFieldValues:function(meta, data) {
        // Summary:
        //    Set the fields values for render the form
        // Description:
        //    Set the fields values for render the form
        var fieldValues = {
            type:     meta['type'],
            id:       meta['key'],
            label:    meta['label'],
            disabled: meta['readOnly'],
            required: meta['required'],
            label:    meta['label'],
            value:    data[meta['key']],
            range:    meta['range'],
            tab:      meta['tab'] || 1,
            hint:     meta['hint'],
            length:   meta['length'] || 0
        };

        return this.setCustomFieldValues(fieldValues);
    },

    setCustomFieldValues:function(fieldValues) {
        // Summary:
        //    Custom function for setFieldValues
        // Description:
        //    Custom function for setFieldValues
        return fieldValues;
    },

    setFormButtons:function(tabId) {
        // Summary:
        //    Render the save and delete buttons
        // Description:
        //    Render the save and delete buttons
        this.formdata[tabId] += this.render(["phpr.Default.template.form", "buttons.html"], null, {
            writePermissions:  this._writePermissions,
            deletePermissions: this._deletePermissions,
            saveText:          phpr.nls.get('Save'),
            deleteText:        phpr.nls.get('Delete')
        });
    },

    setActionFormButtons:function() {
        // Summary:
        //    Connect the buttons to the actions
        dojo.connect(dijit.byId("submitButton"), "onClick", dojo.hitch(this, "submitForm"));
        dojo.connect(dijit.byId("deleteButton"), "onClick", dojo.hitch(this, function() {
            phpr.confirmDialog(dojo.hitch(this, "deleteForm"), phpr.nls.get('Are you sure you want to delete?'))
        }));
    },

    useCache:function() {
        // Summary:
        //    Return true or false if the cache is used
        // Description:
        //    Return true or false if the cache is used
        return true;
    },

    setFormContent:function() {
        // Summary:
        //    Set the Container
        // Description:
        //    Set the Container
        var tabContainer = new dijit.layout.TabContainer({
            style:   'height: 100%;',
            useMenu: false
        }, document.createElement('div'));
        dojo.connect(tabContainer, 'selectChild', dojo.hitch(this, function() {
            dojo.byId('completeContent').focus();
        }));
        return tabContainer;
    },

    addModuleTabs:function(data) {
        // Summary:
        //    Add all the tabs
        // Description:
        //    Add all the tabs that are not the basic data
        this.addAccessTab(data);
        this.addNotificationTab(data);
        this.addHistoryTab();
    },

    addHistoryTab:function() {
        // Summary:
        //    History tab
        // Description:
        //    Display all the history of the item
        if (this.id > 0 && this.useHistoryTab()) {
            this.addTab(this.render(["phpr.Default.template.history", "content.html"]), 'tabHistory', 'History');
        }
    },

    addSubModulesTab:function() {
        // Summary:
        //    Add SubModules tabs
        // Description:
        //    Add all the SubModules that have the current module
        if (this.id > 0) {
            // Set the sub modules data
            var subModules   = new Array();
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
                return a['sort'] - b['sort'];
            });

            // Add the tabs
            for (var index in subModules) {
                var subModuleName = subModules[index]['name'];
                this.addTab('', 'tab' + subModuleName, phpr.nls.get(subModuleName, subModuleName),
                    subModuleName + 'FormTab');
                dojo.addClass('tab' + subModuleName, 'subModuleDiv');
                subModules[index]['class'].fillTab('tab' + subModuleName);
            }
        }
    },

    useHistoryTab:function() {
        //    Return true or false if the history tab is used
        // Description:
        //    Return true or false if the history tab is used
        return true;
    },

    addBasicFields:function() {
        // Summary:
        //    Add some special fields
        // Description:
        //    Add some special fields
        this.formdata[1] += this.displayTagInput();
    },

    postRenderForm:function() {
        // Summary:
        //    User functions after render the form
        // Description:
        //    Apply for special events on the fields
    },

    newAccess:function() {
        // Summary:
        //    Add a new row of one user-accees
        // Description:
        //    Add a the row of one user-accees
        //    with the values selected on the first row
        var userId = dijit.byId("dataAccessAdd").get('value');
        if (!dojo.byId("trAccessFor" + userId) && userId > 0) {
            phpr.destroyWidget("dataAccess[" + userId + "]");
            for (var i in this._rights) {
                var fieldId = 'check' + this._rights[i] + 'Access[' + userId + ']';
                phpr.destroyWidget(fieldId);
            }
            phpr.destroyWidget("accessDeleteButton" + userId);

            var table = dojo.byId("accessTable");
            var row   = table.insertRow(table.rows.length);
            row.id    = "trAccessFor" + userId;

            var cellIndex = 0;

            // Input
            var cell  = row.insertCell(cellIndex);
            var input = this.render(["phpr.Default.template.access", "input.html"], null, {
                id:          userId,
                disabled:    (!this._accessPermissions) ? 'disabled="disabled"' : '',
                userDisplay: dijit.byId("dataAccessAdd").get('displayedValue'),
                currentUser: false
            });
            cell.innerHTML = input;
            cellIndex++;

            // CheckBoxs
            for (var i in this._rights) {
                var cell       = row.insertCell(cellIndex);
                var fieldId    = 'check' + this._rights[i] + 'Access[' + userId + ']';
                var fieldAddId = 'check' + this._rights[i] + 'AccessAdd';
                var checkBox   = this.render(["phpr.Default.template.access", "checkbox.html"], null, {
                    fieldId:  fieldId,
                    checked:  dijit.byId(fieldAddId).checked ? 'checked' : '',
                    hidden:   false,
                    value:    1,
                    disabled: ''
                });
                cell.innerHTML = checkBox;
                cellIndex++;
            }

            // Delete Button
            var cell   = row.insertCell(cellIndex);
            var button = this.render(["phpr.Default.template.access", "button.html"], null, {
                id:        userId,
                useDelete: true
            });
            cell.innerHTML = button;
            cellIndex++;

            dojo.parser.parse(row);

            this.addTinyButton('delete', 'accessDeleteButton' + userId, 'deleteAccess', [userId]);
            dojo.connect(dijit.byId("checkAdminAccess[" + userId + "]"), "onClick",
                dojo.hitch(this, "checkAllAccess", "[" + userId + "]"));
        }
    },

    deleteAccess:function(userId) {
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

    checkAllAccess:function(str) {
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

    prepareSubmission:function() {
        // Summary:
        //    This function prepares the data for submission
        // Description:
        //    This function prepares the content of this.sendData before it is
        //    submitted to the Server.
        this.sendData = new Array();
        for (var i = 0; i < this.formsWidget.length; i++) {
            if (!this.formsWidget[i].isValid()) {
                var parent = this.formsWidget[i].containerNode.parentNode.id;
                this.form.selectChild(parent);
                this.formsWidget[i].validate();
                return false;
            }
            var sendData = this.formsWidget[i].get('value');
            if (typeof(sendData) != 'object') {
                sendData = new Array(sendData);
            } else {
                for (var k in sendData) {
                    // Allow empty arrays, set the value to an empty string
                    if (sendData[k] && typeof(sendData[k]) == 'object' && sendData[k].length == 0) {
                        sendData[k] = new Array("");
                    }
                }
            }
            dojo.mixin(this.sendData, sendData);
        }

        return true;
    },

    submitForm:function() {
        // Summary:
        //    This function is responsible for submitting the formdata
        // Description:
        //    This function sends the form data as json data to the server
        //    and call the reload routine
        if (!this.prepareSubmission()) {
            return false;
        }

        phpr.send({
            url: phpr.webpath + 'index.php/' + phpr.module + '/index/jsonSave/nodeId/' + phpr.currentProjectId
                + '/id/' + this.id,
            content:   this.sendData,
            onSuccess: dojo.hitch(this, function(data) {
               new phpr.handleResponse('serverFeedback', data);
               if (!this.id) {
                   this.id = data['id'];
               }
               if (data.type == 'success') {
                   phpr.send({
                        url: phpr.webpath + 'index.php/Default/Tag/jsonSaveTags/moduleName/' + phpr.module
                            + '/id/' + this.id,
                        content:   this.sendData,
                        onSuccess: dojo.hitch(this, function(data) {
                            if (this.sendData['string']) {
                                new phpr.handleResponse('serverFeedback', data);
                            }
                            if (data.type == 'success') {
                                this.publish("updateCacheData");
                                this.publish("setUrlHash", [phpr.module]);
                            }
                        })
                    });
                }
            })
        });
    },

    deleteForm:function() {
        // Summary:
        //    This function is responsible for deleting a dojo element
        // Description:
        //    This function calls jsonDeleteAction
        phpr.send({
            url:       phpr.webpath + 'index.php/' + phpr.module + '/index/jsonDelete/id/' + this.id,
            onSuccess: dojo.hitch(this, function(data) {
               new phpr.handleResponse('serverFeedback', data);
               if (data.type == 'success') {
                   phpr.send({
                        url: phpr.webpath + 'index.php/Default/Tag/jsonDeleteTags/moduleName/' + phpr.module
                            + '/id/' + this.id,
                        onSuccess: dojo.hitch(this, function(data) {
                            new phpr.handleResponse('serverFeedback', data);
                            if (data.type == 'success') {
                                this.publish("updateCacheData");
                                this.publish("setUrlHash", [phpr.module]);
                            }
                        })
                    });
               }
            })
        });
    },

    displayTagInput:function() {
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
                value += currentTags[i]['string'];
                if (i != currentTags.length - 1) {
                    value += ', ';
                }
            }
        }

        // Draw the tags
        this.publish("drawTagsBox", [currentTags]);

        return this.fieldTemplate.textFieldRender(meta[0]['label'], meta[0]['key'], value, 0, false, false);
    },

    showHistory:function() {
        // Summary:
        //    This function renders the history data
        // Description:
        //    This function renders the history data
        if (this.id > 0) {
            dojo.byId('historyContent').innerHTML = '';
            this._historyUrl = phpr.webpath + 'index.php/Core/history/jsonList/nodeId/1/moduleName/' + phpr.module
                + '/itemId/' + this.id
            phpr.DataStore.addStore({'url': this._historyUrl, 'noCache': true});
            phpr.DataStore.requestData({'url': this._historyUrl, 'processData': dojo.hitch(this, function() {
                this.render(["phpr.Default.template.history", "data.html"], dojo.byId('historyContent'), {
                    dateTxt:     phpr.nls.get('Date'),
                    userTxt:     phpr.nls.get('User'),
                    fieldTxt:    phpr.nls.get('Field'),
                    oldValueTxt: phpr.nls.get('Old value'),
                    newValueTxt: phpr.nls.get('New value'),
                    data:        this.getHistoryData()
                });
            })});
        }
    },

    getHistoryData:function() {
        // Summary:
        //    This function collect and process the history data
        // Description:
        //    This function collect and process the history data
        var history     = phpr.DataStore.getData({url: this._historyUrl});
        var userList    = this.userStore.getList();
        var historyData = new Array();
        var userDisplay = new Array();
        var row         = 0;

        for (var i = 0; i < history.length; i++) {
            // Search for the user name
            if (!userDisplay[history[i]["userId"]]) {
                for (var u in userList) {
                    if (userList[u].id == history[i]["userId"]) {
                        userDisplay[history[i]["userId"]] = userList[u].display;
                        break;
                    }
                }
            }
            if (userDisplay[history[i]["userId"]]) {
                historyUser = userDisplay[history[i]["userId"]];
            } else {
                historyUser = '';
            }
            historyModule   = history[i]["moduleId"];
            historyItemId   = history[i]["itemId"];
            historyField    = history[i]["label"] || '';
            historyOldValue = history[i]["oldValue"] || '';
            historyNewValue = history[i]["newValue"] || '';
            historyAction   = history[i]["action"];
            historyDate     = history[i]["datetime"];

            if (Math.floor(row / 2) == (row / 2)) {
                var trClass = 'grey';
            } else {
                var trClass = 'white';
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

    updateData:function() {
        // Summary:
        //    Delete the cache for this form
        // Description:
        //    Delete the cache for this form
        phpr.DataStore.deleteData({url: this._url});
        phpr.DataStore.deleteData({url: this._tagUrl});
        phpr.DataStore.deleteData({url: this._accessUrl});
    },

    addNotificationTab:function(data) {
        // Summary:
        //    Adds a tab for sending a notification.
        // Description:
        //    Adds a tab for sending a notification to the users with read access, telling them about the item added
        //    or modified. It has a "Send Notification" checkbox.
        // Default value
        var defaultValue = (phpr.config.notificationEnabledByDefault) ? 1 : 0;
        // Add field
        var notificationTab = this.fieldTemplate.checkRender(phpr.nls.get('Send Notification'), 'sendNotification',
            defaultValue, false, phpr.nls.get('Check this box to send an email notification to the participants'));
        // Add the tab to the form
        this.addTab(notificationTab, 'tabNotify', 'Notification', 'accessnotificationTab');
    },

    presetValues:function(data) {
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

    setBreadCrumbItem:function(itemValue) {
        // Summary:
        //    Set the Breadcrumb with the first item value
        // Description:
        //    Set the Breadcrumb with the first item value
        phpr.BreadCrumb.setItem(itemValue);
    },

    highlightChanges:function(data) {
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
                if (this._meta[k]['key'] == field) {
                    switch (this._meta[k]['type']) {
                        case 'datetime':
                            // Split the value to two values
                            var dateTime = value.split(" ");
                            var time     = dateTime[1].slice(0,5);

                            var key          = field + '_forDate';
                            var displayfield = 'widget_' + key;
                            if (fieldWidget = dijit.byId(key)) {
                                fieldWidget.set("displayedValue", dateTime[0]);
                                dojo.addClass(dojo.byId(displayfield), "highlightChanges");
                            }

                            var key          = field + '_forTime';
                            var displayfield = 'widget_' + key;
                            if (fieldWidget = dijit.byId(key)) {
                                fieldWidget.set("displayedValue", time);
                                dojo.addClass(dojo.byId(displayfield), "highlightChanges");
                            }
                            break;
                        case 'selectbox':
                            var displayfield = 'widget_' + field;
                            if (fieldWidget = dijit.byId(field)) {
                                fieldWidget.set("value", value);
                                dojo.addClass(dojo.byId(displayfield), "highlightChanges");
                            }
                            break;
                        case 'date':
                            var displayfield = 'widget_' + field;
                            if (fieldWidget = dojo.byId(field)) {
                                fieldWidget.value = value;
                                dojo.addClass(dojo.byId(displayfield), "highlightChanges");
                            }
                            break;
                        case 'time':
                            var displayfield = 'widget_' + field;
                            if (fieldWidget = dojo.byId(field)) {
                                fieldWidget.value = value.slice(0,5);
                                dojo.addClass(dojo.byId(displayfield), "highlightChanges");
                            }
                            break;
                        case 'percentage':
                            var displayfield = field;
                            if (fieldWidget = dijit.byId(field)) {
                                fieldWidget.set('value', value);
                                dojo.addClass(dojo.byId(displayfield), "highlightChanges");
                            }
                            break;
                        case 'upload':
                            var displayfield = 'filesIframe_' + field;
                            if (fieldWidget = dijit.byId(field)) {
                                fieldWidget.set('value', value);
                                dojo.byId('filesIframe_files').contentDocument.location.href = phpr.webpath
                                    + 'index.php/Default/File/fileForm/moduleName/' + phpr.module + '/id/'
                                    + this.id + '/field/' + field + '/value/' + value;
                                dojo.addClass(dojo.byId(displayfield), "highlightChanges");
                            }
                            break;
                        case 'checkbox':
                            var displayfield = field;
                            if (fieldWidget = dijit.byId(field)) {
                                value = (value == 1) ? true : false;
                                fieldWidget.set('checked', value);
                                dojo.addClass(dojo.byId(displayfield).parentNode, "highlightChanges");
                            }
                            break;
                        case 'multipleselectbox':
                            var displayfield = field + '[]';
                            if (fieldWidget = dijit.byId(displayfield)) {
                                value = value.split(',');
                                fieldWidget.set("value", value);
                                dojo.addClass(dojo.byId(displayfield).parentNode, "highlightChanges");
                            }
                            break;
                        case 'rating':
                            var displayfield = field;
                            if (fieldWidget = dijit.byId(field)) {
                                fieldWidget.setAttribute('value', value);
                                dojo.addClass(dojo.byId(displayfield).parentNode, "highlightChanges");
                            }
                            break;
                        default:
                            var displayfield = field;
                            if (fieldWidget = dijit.byId(field)) {
                                fieldWidget.set("displayedValue", value);
                                dojo.addClass(dojo.byId(displayfield), "highlightChanges");
                            }
                            break;
                    }
                    break;
                }
            }
        }
    }
});
