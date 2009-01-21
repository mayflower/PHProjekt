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
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

dojo.provide("phpr.Default.Form");

dojo.declare("phpr.Default.Form", phpr.Component, {
    // summary:
    //    Class for displaying a PHProjekt Detail View
    // description:
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
    _historyUrl:        null,

    constructor:function(main, id, module) {
        // summary:
        //    render the form on construction
        // description:
        //    this function receives the form data from the server and renders the corresponding form
        //    If the module is a param, is setted
        this.main = main;
        this.id   = id;

        if (undefined != module) {
            phpr.module = module
        }

        this.setUrl();
        this.setNode();

        this._initData.push({'url': this._url, 'processData': dojo.hitch(this, "getFormData")});
        this.tabStore = new phpr.Store.Tab();
        this._initData.push({'store': this.tabStore});
        this.initData();
        this.getInitData();
    },

    setUrl:function() {
        // summary:
        //    Set the url for get the data
        // description:
        //    Set the url for get the data
        this._url = phpr.webpath+"index.php/" + phpr.module + "/index/jsonDetail/id/" + this.id
    },

    setNode:function() {
        // summary:
        //    Set the node to put the grid
        // description:
        //    Set the node to put the grid
        this._formNode = dijit.byId("detailsBox");
    },

    getInitData:function(params) {
        // summary:
        //    Process all the POST in cascade for get all the data from the server
        // description:
        //    Process all the POST in cascade for get all the data from the server
        params = this._initData.pop();

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
        // summary:
        //    Init all the data before draw the form
        // description:
        //    This function call all the needed data before the form is drawed
        //    The form will wait for all the data are loaded.
        //    Each module can overwrite this function for load the own data

        // Get all the active users
        this.userStore = new phpr.Store.User();
        this._initData.push({'store': this.userStore});

        // Get the tags
        this._tagUrl  = phpr.webpath + 'index.php/Default/Tag/jsonGetTagsByModule/moduleName/' + phpr.module + '/id/' + this.id;
        this._initData.push({'url': this._tagUrl});

        // History data
        if (this.id > 0) {
            this._historyUrl = phpr.webpath+"index.php/Core/history/jsonList/moduleName/" + phpr.module + "/itemId/" + this.id
            this._initData.push({'url': this._historyUrl, 'noCache': true});
        }
    },

    addAccessTab:function(data) {
        // summary:
        //    Access tab
        // description:
        //    Display all the users and the acces
        //    The user can assign to each user different access on the item
        var userList = this.userStore.getList();
        var accessContent = data[0]["rights"];
        var currentUser   = 0;
        if (this.id > 0) {
            currentUser = data[0]["rights"]["currentUser"]["userId"];
        }

        if (this._accessPermissions) {
            // template for the access tab
            var accessData = this.render(["phpr.Default.template", "accesstab.html"], null, {
                accessUserText:     phpr.nls.get('User'),
                accessReadText:     phpr.nls.get('Read'),
                accessWriteText:    phpr.nls.get('Write'),
                accessAccessText:   phpr.nls.get('Access'),
                accessCreateText:   phpr.nls.get('Create'),
                accessCopyText:     phpr.nls.get('Copy'),
                accessDeleteText:   phpr.nls.get('Delete'),
                accessDownloadText: phpr.nls.get('Download'),
                accessAdminText:    phpr.nls.get('Admin'),
                accessNoneText:     phpr.nls.get('None'),
                accessActionText:   phpr.nls.get('Action'),
                users:              userList,
                currentUser:        currentUser,
                accessContent:      accessContent
            });

            this.addTab(accessData, 'tabAccess', 'Access', 'accessFormTab');

            // add button for access
            var params = {
                label:     '',
                iconClass: 'add',
                alt:       'Add'
            };
            newAccess = new dijit.form.Button(params);
            dojo.byId("accessAddButton").appendChild(newAccess.domNode);
            dojo.connect(newAccess, "onClick", dojo.hitch(this, "newAccess"));
            dojo.connect(dijit.byId("checkAdminAccessAdd"), "onClick", dojo.hitch(this, "checkAllAccess", "Add"));
            dojo.connect(dijit.byId("checkNoneAccessAdd"), "onClick", dojo.hitch(this, "checkNoneAccess", "Add"));

            // delete buttons for access
            // add check all and none functions
            for (i in accessContent) {
                var userId     = accessContent[i]["userId"];
                var buttonName = "accessDeleteButton" + userId;
                var params = {
                    label:     '',
                    iconClass: 'cross',
                    alt:       'Delete'
                };

                var tmp = new dijit.form.Button(params);
                dojo.byId(buttonName).appendChild(tmp.domNode);
                dojo.connect(tmp, "onClick", dojo.hitch(this, "deleteAccess", userId));
                dojo.connect(dijit.byId("checkAdminAccess[" + userId + "]"), "onClick", dojo.hitch(this, "checkAllAccess", "[" + userId + "]"));
                dojo.connect(dijit.byId("checkNoneAccess[" + userId + "]"), "onClick", dojo.hitch(this, "checkNoneAccess", "[" + userId + "]"));
            }
        }
    },

    setPermissions:function(data) {
        // summary:
        //    Get the permission
        // description:
        //    Get the permission for the current user on the item
        if (this.id > 0) {
            this._writePermissions  = data[0]["rights"]["currentUser"]["write"];
            this._deletePermissions = data[0]["rights"]["currentUser"]["delete"];
            this._accessPermissions = data[0]["rights"]["currentUser"]["admin"];
        }
    },

    addTab:function(innerTabs, id, title, formId) {
        // summary:
        //    Add a tab
        // description:
        //    Add a tab and if have form, add the values
        //    to the array of values for save it later
        phpr.destroyWidget(id);
        phpr.destroyWidget(formId);
        var html = this.render(["phpr.Default.template", "tabs.html"], null,{
            innerTabs: innerTabs,
            formId:    formId || ''
        });
        var tab = new dijit.layout.ContentPane({
            id:    id,
            title: phpr.nls.get(title)
        });
        dojo.addOnLoad(function(){
            dijit.byId(id).resize();
        });
        tab.attr('content', html);
        this.form.addChild(tab);
        if (typeof formId != "undefined") {
            this.formsWidget.push(dijit.byId(formId));
        }
    },

    getTabs:function() {
        // summary:
        //    Return the tab list for make the form
        // description:
        //    Return the tab list for make the form or an empty array
        if (this.tabStore) {
            result = this.tabStore.getList();
        }
        return result;
    },

    getFormData:function(items, request) {
        // summary:
        //    This function renders the form data according to the database manager settings
        // description:
        //    This function processes the form data which is stored in a phpr.DataStore and
        //    renders the actual form according to the received data
        this.formdata    = new Array();
        this.formdata[0] = new Array();

        var meta = phpr.DataStore.getMetaData({url: this._url});
        var data = phpr.DataStore.getData({url: this._url});
        var tabs = this.getTabs();

        this.setPermissions(data);

        this.fieldTemplate = new phpr.Default.Field();

        for (var i = 0; i < meta.length; i++) {
            itemtype     = meta[i]["type"];
            itemid       = meta[i]["key"];
            itemlabel    = meta[i]["label"];
            itemdisabled = meta[i]["readOnly"];
            itemrequired = meta[i]["required"];
            itemlabel    = meta[i]["label"];
            itemvalue    = data[0][itemid];
            itemrange    = meta[i]["range"];
            itemtab      = meta[i]["tab"] || 1;
            itemhint     = meta[i]["hint"];

            // Special workaround for new projects - set parent to current ProjectId
            if(itemid == 'projectId' && !itemvalue){
                itemvalue = phpr.currentProjectId;
            }

            // Init formdata
            if (!this.formdata[itemtab]) {
                this.formdata[itemtab] = '';
            }

            // Render the fields according to their type
            switch (itemtype) {
                case 'checkbox':
                    this.formdata[itemtab] += this.fieldTemplate.checkRender(itemlabel, itemid, itemvalue, itemhint);
                    break;

                case'selectbox':
                    this.formdata[itemtab] += this.fieldTemplate.selectRender(itemrange ,itemlabel, itemid, itemvalue,
                                                itemrequired, itemdisabled, itemhint);
                    break;
                case'multipleselectbox':
                    this.formdata[itemtab] += this.fieldTemplate.multipleSelectRender(itemrange ,itemlabel, itemid,
                                                itemvalue, itemrequired, itemdisabled, 5, "multiple", itemhint);
                    break;
                case'date':
                    this.formdata[itemtab] += this.fieldTemplate.dateRender(itemlabel, itemid, itemvalue, itemrequired,
                                                itemdisabled, itemhint);
                    break;
                case 'time':
                    this.formdata[itemtab] += this.fieldTemplate.timeRender(itemlabel, itemid, itemvalue, itemrequired,
                                                itemdisabled, itemhint);
                    break;
                case 'textarea':
                    this.formdata[itemtab] += this.fieldTemplate.textAreaRender(itemlabel, itemid, itemvalue,
                                                itemrequired, itemdisabled, itemhint);
                    break;
                case 'password':
                    this.formdata[itemtab] += this.fieldTemplate.passwordFieldRender(itemlabel, itemid, itemvalue,
                                                itemrequired, itemdisabled, itemhint);
                    break;
                case 'percentage':
                    this.formdata[itemtab] += this.fieldTemplate.percentageFieldRender(itemlabel, itemid, itemvalue,
                                                itemrequired, itemdisabled, itemhint);
                    break;
                case 'upload':
                    iFramePath   = phpr.webpath + 'index.php/' + phpr.module + '/index/uploadForm/id/'+ this.id + '/field/' + itemid + '/value/' + itemvalue;
                    this.formdata[itemtab] += this.fieldTemplate.uploadFieldRender(itemlabel, itemid, itemvalue,
                                                itemrequired, itemdisabled, iFramePath, itemhint);
                    break;
                case 'hidden':
                    this.formdata[itemtab] += this.fieldTemplate.hiddenFieldRender('', itemid, itemvalue, itemrequired,
                                                itemdisabled, itemhint);
                    break;
                default:
                    this.formdata[itemtab] += this.fieldTemplate.textFieldRender(itemlabel, itemid, itemvalue,
                                                itemrequired, itemdisabled, itemhint);
                    break;
            }
        }

        // add special inputs to the Basic Data
        this.addBasicFields();

        this.form = this.setFormContent();
        this.formsWidget = new Array();

        for (t in tabs) {
            if (this.formdata[tabs[t].id]) {
                this.addTab(this.formdata[tabs[t].id], 'tabBasicData' + tabs[t].id, phpr.nls.get(tabs[t].name), 'dataFormTab' + tabs[t].id);
            }
        }

        this._formNode.attr('content',this.form.domNode);
        this.form.startup();

        this.render(["phpr.Default.template", "formbuttons.html"], dojo.byId("bottomContent"),{
            writePermissions:  this._writePermissions,
            deletePermissions: this._deletePermissions,
            saveText:          phpr.nls.get('Save'),
            deleteText:        phpr.nls.get('Delete')
        });

        // Action buttons for the form
        dojo.connect(dijit.byId("submitButton"), "onClick", dojo.hitch(this, "submitForm"));
        dojo.connect(dijit.byId("deleteButton"), "onClick", dojo.hitch(this, "deleteForm"));

        this.addModuleTabs(data);

        // Delete the data if is not used the cache
        if (!this.useCache()) {
            phpr.DataStore.deleteData({url: this._url});
        }
    },

    useCache:function() {
        // summary:
        //    Return true or false if the cache is used
        // description:
        //    Return true or false if the cache is used
        return true;
    },

    setFormContent:function() {
        // summary:
        //    Set the Container
        // description:
        //    Set the Container
        var tabContainer = new dijit.layout.TabContainer({
            style: 'height:100%;'
        }, document.createElement('div'));
        dojo.connect(tabContainer, 'selectChild', dojo.hitch(this, function() {
            dojo.byId('completeContent').focus();
        }));
        return tabContainer;
    },

    addModuleTabs:function(data) {
        // summary:
        //    Add all the tabs
        // description:
        //    Add all the tabs that are not the basic data
        this.addAccessTab(data);
        this.addNotificationTab(data);
        if (this.id > 0) {
            this.addTab(this.getHistoryData(), 'tabHistory', 'History');
        }
    },

    addBasicFields:function() {
        // summary:
        //    Add some special fields
        // description:
        //    Add some special fields
        this.formdata[1] += this.displayTagInput();
    },

    newAccess:function() {
        // summary:
        //    Add a new row of one user-accees
        // description:
        //    Add a the row of one user-accees
        //    with the values selected on the first row
        var userId = dijit.byId("dataAccessAdd").attr('value');
        if (!dojo.byId("trAccessFor" + userId) && userId > 0) {
            phpr.destroyWidget("dataAccess[" + userId + "]");
            phpr.destroyWidget("checkReadAccess[" + userId + "]");
            phpr.destroyWidget("checkWriteAccess[" + userId + "]");
            phpr.destroyWidget("checkAccessAccess[" + userId + "]");
            phpr.destroyWidget("checkCreateAccess[" + userId + "]");
            phpr.destroyWidget("checkCopyAccess[" + userId + "]");
            phpr.destroyWidget("checkDeleteAccess[" + userId + "]");
            phpr.destroyWidget("checkDownloadAccess[" + userId + "]");
            phpr.destroyWidget("checkAdminAccess[" + userId + "]");
            phpr.destroyWidget("checkNoneAccess[" + userId + "]");
            phpr.destroyWidget("accessDeleteButton" + userId);

            var userName = dijit.byId("dataAccessAdd").attr('displayedValue');
            var table    = dojo.byId("accessTable");
            var row      = table.insertRow(table.rows.length);
            row.id       = "trAccessFor" + userId;

            var cell = row.insertCell(0);
            cell.innerHTML = '<input id="dataAccess[' + userId + ']" name="dataAccess[' + userId + ']" type="hidden" value="' + userId + '" dojoType="dijit.form.TextBox" />' + userName;
            var cell = row.insertCell(1);
            cell.innerHTML = '<div align="center"><input type="checkbox" dojotype="dijit.form.CheckBox" id="checkReadAccess[' + userId + ']" name="checkReadAccess[' + userId + ']" checked="' + dijit.byId("checkReadAccessAdd").checked + '" value="1" />';
            var cell = row.insertCell(2);
            cell.innerHTML = '<div align="center"><input type="checkbox" dojotype="dijit.form.CheckBox" id="checkWriteAccess[' + userId + ']" name="checkWriteAccess[' + userId + ']" checked="' + dijit.byId("checkWriteAccessAdd").checked + '" value="1" /></div>';
            var cell = row.insertCell(3);
            cell.innerHTML = '<div align="center"><input type="checkbox" dojotype="dijit.form.CheckBox" id="checkAccessAccess[' + userId + ']" name="checkAccessAccess[' + userId + ']" checked="' + dijit.byId("checkAccessAccessAdd").checked + '" value="1" /></div>';
            var cell = row.insertCell(4);
            cell.innerHTML = '<div align="center"><input type="checkbox" dojotype="dijit.form.CheckBox" id="checkCreateAccess[' + userId + ']" name="checkCreateAccess[' + userId + ']" checked="' + dijit.byId("checkCreateAccessAdd").checked + '" value="1" /></div>';
            var cell = row.insertCell(5);
            cell.innerHTML = '<div align="center"><input type="checkbox" dojotype="dijit.form.CheckBox" id="checkCopyAccess[' + userId + ']" name="checkCopyAccess[' + userId + ']" checked="' + dijit.byId("checkCopyAccessAdd").checked + '" value="1" /></div>';
            var cell = row.insertCell(6);
            cell.innerHTML = '<div align="center"><input type="checkbox" dojotype="dijit.form.CheckBox" id="checkDeleteAccess[' + userId + ']" name="checkDeleteAccess[' + userId + ']" checked="' + dijit.byId("checkDeleteAccessAdd").checked + '" value="1" /></div>';
            var cell = row.insertCell(7);
            cell.innerHTML = '<div align="center"><input type="checkbox" dojotype="dijit.form.CheckBox" id="checkDownloadAccess[' + userId + ']" name="checkDownloadAccess[' + userId + ']" checked="' + dijit.byId("checkDownloadAccessAdd").checked + '" value="1" /></div>';
            var cell = row.insertCell(8);
            cell.innerHTML = '<div align="center"><input type="checkbox" dojotype="dijit.form.CheckBox" id="checkAdminAccess[' + userId + ']" name="checkAdminAccess[' + userId + ']" checked="' + dijit.byId("checkAdminAccessAdd").checked + '" value="1" /></div>';
            var cell = row.insertCell(9);
            cell.innerHTML = '<div align="center"><input type="checkbox" dojotype="dijit.form.CheckBox" id="checkNoneAccess[' + userId + ']" name="checkNoneAccess[' + userId + ']" checked="' + dijit.byId("checkNoneAccessAdd").checked + '" value="1" /></div>';

            var cell = row.insertCell(10);
            cell.innerHTML = '<div id="accessDeleteButton' + userId + '"></div>';

            dojo.parser.parse(row);

            var buttonName = "accessDeleteButton" + userId;
            var params = {
                label:     '',
                iconClass: 'cross',
                alt:       'Delete'
            };
            var tmp = new dijit.form.Button(params);
            dojo.byId(buttonName).appendChild(tmp.domNode);
            dojo.connect(dijit.byId(tmp.id), "onClick", dojo.hitch(this, "deleteAccess", userId));
            dojo.connect(dijit.byId("checkAdminAccess[" + userId + "]"), "onClick", dojo.hitch(this, "checkAllAccess", "[" + userId + "]"));
            dojo.connect(dijit.byId("checkNoneAccess[" + userId + "]"), "onClick", dojo.hitch(this, "checkNoneAccess", "[" + userId + "]"));
        }
    },

    deleteAccess:function(userId) {
        // summary:
        //    Remove the row of one user-accees
        // description:
        //    Remove the row of one user-accees
        //    and destroy all the used widgets
        phpr.destroyWidget("dataAccess[" + userId + "]");
        phpr.destroyWidget("checkReadAccess[" + userId + "]");
        phpr.destroyWidget("checkWriteAccess[" + userId + "]");
        phpr.destroyWidget("checkAccessAccess[" + userId + "]");
        phpr.destroyWidget("checkCreateAccess[" + userId + "]");
        phpr.destroyWidget("checkCopyAccess[" + userId + "]");
        phpr.destroyWidget("checkDeleteAccess[" + userId + "]");
        phpr.destroyWidget("checkDownloadAccess[" + userId + "]");
        phpr.destroyWidget("checkAdminAccess[" + userId + "]");
        phpr.destroyWidget("checkNoneAccess[" + userId + "]");
        phpr.destroyWidget("accessDeleteButton" + userId);

        var e = dojo.byId("trAccessFor" + userId);
        var parent = e.parentNode;
        parent.removeChild(e);
    },

    checkAllAccess:function(str) {
        // summary:
        //    Select all the access
        // description:
        //    Select all the access
        if (dijit.byId("checkAdminAccess"+str).checked) {
            dijit.byId("checkReadAccess"+str).attr('checked',true);
            dijit.byId("checkWriteAccess"+str).attr('checked',true);
            dijit.byId("checkAccessAccess"+str).attr('checked',true);
            dijit.byId("checkCreateAccess"+str).attr('checked',true);
            dijit.byId("checkCopyAccess"+str).attr('checked',true);
            dijit.byId("checkDeleteAccess"+str).attr('checked',true);
            dijit.byId("checkDownloadAccess"+str).attr('checked',true);
            dijit.byId("checkNoneAccess"+str).attr('checked',false);
        }
    },

    checkNoneAccess:function(str) {
        // summary:
        //    Un-select all the access
        // description:
        //    Un-select all the access
        if (dijit.byId("checkNoneAccess"+str).checked) {
            dijit.byId("checkReadAccess"+str).attr('checked',false);
            dijit.byId("checkWriteAccess"+str).attr('checked',false);
            dijit.byId("checkAccessAccess"+str).attr('checked',false);
            dijit.byId("checkCreateAccess"+str).attr('checked',false);
            dijit.byId("checkCopyAccess"+str).attr('checked',false);
            dijit.byId("checkDeleteAccess"+str).attr('checked',false);
            dijit.byId("checkDownloadAccess"+str).attr('checked',false);
            dijit.byId("checkAdminAccess"+str).attr('checked',false);
        }
    },

    prepareSubmission:function() {
        // summary:
        //    This function prepares the data for submission
        // description:
        //    This function prepares the content of this.sendData before it is
        //    submitted to the Server.
        this.sendData = new Array();
        for(var i = 0; i < this.formsWidget.length; i++) {
            if (!this.formsWidget[i].isValid()) {
                var parent = this.formsWidget[i].containerNode.parentNode.id;
                this.form.selectChild(parent);
                this.formsWidget[i].validate();
                return false;
            }
            this.sendData = dojo.mixin(this.sendData, this.formsWidget[i].attr('value'));
        }
        return true;
    },

    submitForm:function() {
        // summary:
        //    This function is responsible for submitting the formdata
        // description:
        //    This function sends the form data as json data to the server
        //    and call the reload routine
        if (!this.prepareSubmission()) {
            return false;
        }

        phpr.send({
            url:       phpr.webpath + 'index.php/' + phpr.module + '/index/jsonSave/id/' + this.id,
            content:   this.sendData,
            onSuccess: dojo.hitch(this, function(data) {
               new phpr.handleResponse('serverFeedback', data);
               if (!this.id) {
                   this.id = data['id'];
               }
               if (data.type == 'success') {
                   phpr.send({
                        url: phpr.webpath + 'index.php/Default/Tag/jsonSaveTags/moduleName/' + phpr.module + '/id/' + this.id,
                        content:   this.sendData,
                        onSuccess: dojo.hitch(this, function(data) {
                            new phpr.handleResponse('serverFeedback', data);
                            if (data.type =='success') {
                                this.publish("updateCacheData");
                                this.publish("reload");
                            }
                        })
                    });
               }
            })
        });
    },

    deleteForm:function() {
        // summary:
        //    This function is responsible for deleting a dojo element
        // description:
        //    This function calls jsonDeleteAction
        phpr.send({
            url:       phpr.webpath + 'index.php/' + phpr.module + '/index/jsonDelete/id/' + this.id,
            onSuccess: dojo.hitch(this, function(data) {
               new phpr.handleResponse('serverFeedback', data);
               if (data.type == 'success') {
                   phpr.send({
                        url: phpr.webpath + 'index.php/Default/Tag/jsonDeleteTags/moduleName/' + phpr.module + '/id/' + this.id,
                        onSuccess: dojo.hitch(this, function(data) {
                            new phpr.handleResponse('serverFeedback', data);
                            if (data.type =='success') {
                                this.publish("updateCacheData");
                                this.publish("reload");
                            }
                        })
                    });
               }
            })
        });
    },

    displayTagInput:function() {
        // summary:
        // This function manually receives the Tags for the current element
        // description:
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
        this.publish("drawTagsBox",[currentTags]);

        return this.fieldTemplate.textFieldRender(meta[0]['label'], meta[0]['key'], value, false, false);
    },

    getHistoryData:function() {
        // summary:
        //    This function renders the history data
        // description:
        //    This function processes the form data which is stored in a phpr.DataStore and
        //    renders the actual form according to the received data
        var history     = phpr.DataStore.getData({url: this._historyUrl});
        var historyData = '<tr><td colspan="2"><table  id="historyTable" style="position: relative; left: 75px">';

        if (history.length > 0) {
            historyData += "<tr><td><label>" + phpr.nls.get('Date');
            historyData += "</label></td><td><label>" + phpr.nls.get('User');
            historyData += "</label></td><td><label>" + phpr.nls.get('Field');
            historyData += "</label></td><td><label>" + phpr.nls.get('Old value') + "</label></td></tr>";
        }

        for (var i = 0; i < history.length; i++) {
            historyUser     = history[i]["userId"];
            historyModule   = history[i]["moduleId"];
            historyItemId   = history[i]["itemId"];
            historyField    = history[i]["field"];
            historyOldValue = history[i]["oldValue"];
            historyNewValue = history[i]["newValue"];
            historyAction   = history[i]["action"];
            historyDate     = history[i]["datetime"];

            historyData += "<tr><td>" + historyDate;
            historyData += "</td><td>" + historyUser;
            historyData += "</td><td>" + historyField;
            historyData += "</td><td>" + historyOldValue;
        }
        historyData += "</table></td></tr>";

        return historyData;
    },

    updateData:function() {
        // summary:
        //    Delete the cache for this form
        // description:
        //    Delete the cache for this form
        phpr.DataStore.deleteData({url: this._url});
        phpr.DataStore.deleteData({url: this._tagUrl});
    },

    addNotificationTab:function(data) {
        // summary:
        //    Adds a tab for sending a notification.
        // description:
        //    Adds a tab for sending a notification to the users with read access, telling them about the item added
        //	  or modified. It has a "Send Notification" checkbox.
        // Add field
        var notificationTab = this.fieldTemplate.checkRender(phpr.nls.get('Send Notification'), 'sendNotification', '');
        // Add the tab to the form
        this.addTab(notificationTab, 'tabNotify', 'Notification', 'accessnotificationTab');
    }
});
