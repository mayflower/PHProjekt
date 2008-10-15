dojo.provide("phpr.Default.Form");

dojo.declare("phpr.Default.Form", phpr.Component, {
    // summary:
    //    Class for displaying a PHProjekt Detail View
    // description:
    //    This Class takes care of displaying the form information we receive from our Server
    //    in a dojo form with tabs

    sendData:    new Array(),
    formdata:    '',
    historyData: '',

    _url:               null,
    _formNode:          null,
    _urlUsers:          null,
    _urlHistory:        null,
    _writePermissions:  true,
    _deletePermissions: false,
    _accessPermissions: true,
    _tagUrl:            null,

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

        this.initData();

        // Render the form element on the right bottom
        phpr.DataStore.addStore({url: this._url});
        phpr.DataStore.requestData({url: this._url, processData: dojo.hitch(this, "getFormData")});
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

    initData: function() {
        // summary:
        //    Init all the data before draw the form
        // description:
        //    This function call all the needed data before the form is drawed
        //    The form will wait for all the data are loaded.
        //    Each module can overwrite this function for load the own data
        if (this.id > 0) {
            this.historyStore = new phpr.ReadHistory({
                url: phpr.webpath+"index.php/Core/history/jsonList/moduleName/" + phpr.module + "/itemId/" + this.id
            });
            this.historyStore.fetch({onComplete: dojo.hitch(this, "getHistoryData")});
        }

        // Get the tags
        this._tagUrl  = phpr.webpath + 'index.php/Default/Tag/jsonGetTagsByModule/moduleName/' + phpr.module + '/id/' + this.id;
        phpr.DataStore.addStore({url: this._tagUrl});
        phpr.DataStore.requestData({url: this._tagUrl});

        // Get all the active users
        this.userStore = new phpr.Store.User();
        this.userStore.fetch();
    },

    addAccessTab:function(data) {
        // summary:
        //    Access tab
        // description:
        //    Display all the users and the acces
        //    The user can assign to each user different access on the item
        phpr.destroyWidgets("tabAccess");
        phpr.destroyWidgets("newAccess");
        phpr.destroyWidgets("dataAccessAdd");
        phpr.destroyWidgets("checkAdminAccessAdd");
        phpr.destroyWidgets("checkWriteAccessAdd");
        phpr.destroyWidgets("checkReadAccessAdd");
        var userList = this.userStore.getList();
        for (var i in userList) {
            phpr.destroyWidgets("dataAccess[" + userList[i].id + "]");
            phpr.destroyWidgets("checkReadAccess[" + userList[i].id + "]");
            phpr.destroyWidgets("checkWriteAccess[" + userList[i].id + "]");
            phpr.destroyWidgets("checkAccessAccess[" + userList[i].id + "]");
            phpr.destroyWidgets("checkCreateAccess[" + userList[i].id + "]");
            phpr.destroyWidgets("checkCopyAccess[" + userList[i].id + "]");
            phpr.destroyWidgets("checkDeleteAccess[" + userList[i].id + "]");
            phpr.destroyWidgets("checkDownloadAccess[" + userList[i].id + "]");
            phpr.destroyWidgets("checkAdminAccess[" + userList[i].id + "]");
            phpr.destroyWidgets("checkNoneAccess[" + userList[i].id + "]");
        }
        
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
                id:        'newAccess',
                iconClass: 'add',
                alt:       'Add'
            };
            newAccess = new dijit.form.Button(params);
            dojo.byId("accessAddButton").appendChild(newAccess.domNode);
            dojo.connect(dijit.byId("newAccess"), "onClick", dojo.hitch(this, "newAccess"));
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
                dojo.connect(dijit.byId(tmp.id), "onClick", dojo.hitch(this, "deleteAccess", userId));
                dojo.connect(dijit.byId("checkAdminAccess[" + userId + "]"), "onClick", dojo.hitch(this, "checkAllAccess", "[" + userId + "]"));
                dojo.connect(dijit.byId("checkNoneAccess[" + userId + "]"), "onClick", dojo.hitch(this, "checkNoneAccess", "[" + userId + "]"));
            }
        }
    },

    setPermissions:function (data) {
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

    addTab:function (innerTabs, id, title, formId) {
        // summary:
        //    Add a tab
        // description:
        //    Add a tab and if have form, add the values
        //    to the array of values for save it later
        phpr.destroyWidgets(formId);
        var html = this.render(["phpr.Default.template", "tabs.html"], null,{
            innerTabs: innerTabs,
            formId:    formId || ''
        });
        var tab = new dijit.layout.ContentPane({
            id:        id,
            title:     phpr.nls.get(title)
        });
        tab.attr('content', html);
        this.form.addChild(tab);
        if (typeof formId != "undefined") {
            this.formsWidget.push(dijit.byId(formId));
        }
    },

    getFormData: function(items, request) {
        // summary:
        //    This function renders the form data according to the database manager settings
        // description:
        //    This function processes the form data which is stored in a phpr.DataStore and
        //    renders the actual form according to the received data
        phpr.destroyWidgets(this._formNode.attr("id"));

        phpr.destroyWidgets("formtab");
        phpr.destroyWidgets("tabBasicData");
        phpr.destroyWidgets("tabHistory");


        phpr.destroyWidgets("bottomContent");
        phpr.destroyWidgets("submitButton");
        phpr.destroyWidgets("deleteButton");

        this.formdata = "";

        meta = phpr.DataStore.getMetaData({url: this._url});
        data = phpr.DataStore.getData({url: this._url});

        this.setPermissions(data);

        this.fieldTemplate = new phpr.Default.field();

        for (var i = 0; i < meta.length; i++) {
            itemtype     = meta[i]["type"];
            itemid       = meta[i]["key"];
            itemlabel    = meta[i]["label"];
            itemdisabled = meta[i]["readOnly"];
            itemrequired = meta[i]["required"];
            itemlabel    = meta[i]["label"];
            itemvalue    = data[0][itemid];
            itemrange    = meta[i]["range"];

            // Special workaround for new projects - set parent to current ProjectId
            if(itemid == 'projectId' && !itemvalue){
                itemvalue = phpr.currentProjectId;
            }

            // Render the fields according to their type
            switch (itemtype) {
                case 'checkbox':
                    this.formdata += this.fieldTemplate.checkRender(itemlabel, itemid, itemvalue);
                    break;

                case'selectbox':
                    this.formdata += this.fieldTemplate.selectRender(itemrange ,itemlabel, itemid, itemvalue, itemrequired,
                                                                       itemdisabled);
                    break;
                case'multipleselectbox':
                    this.formdata += this.fieldTemplate.multipleSelectBoxRender(itemrange ,itemlabel, itemid, itemvalue, itemrequired,
                                                                       itemdisabled, 5, "multiple");
                    break;
                case 'multipleselect':
                    this.formdata += this.fieldTemplate.multipleSelectRender(itemrange ,itemlabel, itemid, itemvalue, itemrequired,
                                                                                itemdisabled);
                    break;
                case'date':
                    this.formdata += this.fieldTemplate.dateRender(itemlabel, itemid, itemvalue, itemrequired,
                                                                   itemdisabled);
                    break;
                case 'time':
                    this.formdata += this.fieldTemplate.timeRender(itemlabel, itemid, itemvalue, itemrequired,
                                                                   itemdisabled);
                    break;
                case 'textarea':
                    this.formdata += this.fieldTemplate.textAreaRender(itemlabel, itemid, itemvalue, itemrequired,
                                                                       itemdisabled);
                    break;
                case 'password':
                    this.formdata += this.fieldTemplate.passwordFieldRender(itemlabel, itemid, itemvalue, itemrequired,
                                                                        itemdisabled);
                    break;
                case 'percentage':
                    this.formdata += this.fieldTemplate.percentageFieldRender(itemlabel, itemid, itemvalue, itemrequired,
                                                                        itemdisabled);
                    break;
                case 'upload':
                    iFramePath = phpr.webpath + 'index.php/' + phpr.module + '/index/uploadForm/';
                    formPath = phpr.webpath + 'index.php/' + phpr.module + '/index/uploadFile/';
                    downloadlink = '';
                    downloadtext = '';
                    if (itemvalue.indexOf('|') > 0) {
                        downloadlink = phpr.webpath + 'index.php/' + phpr.module + '/index/downloadFile/id/' + this.id + '/field/' + itemid;
                        downloadtext = itemvalue.substring(itemvalue.indexOf('|') + 1, itemvalue.length);
                        md5mane = itemvalue.substring(0, itemvalue.indexOf('|'));
                    } else {
                        md5name = itemvalue;
                    }
                    
                    this.formdata += this.fieldTemplate.uploadFieldRender(itemlabel, itemid, itemvalue, itemrequired,
                                                                        itemdisabled, iFramePath, formPath, downloadlink,
                                                                        downloadtext);
                    break;
                case 'textfield':
                default:
                    this.formdata += this.fieldTemplate.textFieldRender(itemlabel, itemid, itemvalue, itemrequired,
                                                                        itemdisabled);
                    break;
            }
        }

        // add special inputs to the Basic Data
        this.addBasicFields();

        this.form = this.setFormContent();
        this.formsWidget = new Array();

        this.addTab(this.formdata, 'tabBasicData', 'Basic Data', 'dataFormTab');

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
        return new dijit.layout.TabContainer({
            id:   'formtab',
            style: 'height:100%;'
        }, document.createElement('div'));
    },

    addModuleTabs:function(data) {
        // summary:
        //    Add all the tabs
        // description:
        //    Add all the tabs that are not the basic data
        this.addAccessTab(data);
        if (this.id > 0) {
            this.addTab(this.historyData, 'tabHistory', 'History');
        }
    },

    addBasicFields:function() {
        // summary:
        //    Add some special fields
        // description:
        //    Add some special fields
        this.formdata += this.displayTagInput();
    },

    newAccess: function () {
        // summary:
        //    Add a new row of one user-accees
        // description:
        //    Add a the row of one user-accees
        //    with the values selected on the first row
        var userId = dijit.byId("dataAccessAdd").attr('value');
        if (!dojo.byId("trAccessFor" + userId) && userId > 0) {        
            phpr.destroyWidgets("dataAccess[" + userId + "]");
            phpr.destroyWidgets("checkReadAccess[" + userId + "]");
            phpr.destroyWidgets("checkWriteAccess[" + userId + "]");
            phpr.destroyWidgets("checkAccessAccess[" + userId + "]");
            phpr.destroyWidgets("checkCreateAccess[" + userId + "]");
            phpr.destroyWidgets("checkCopyAccess[" + userId + "]");
            phpr.destroyWidgets("checkDeleteAccess[" + userId + "]");
            phpr.destroyWidgets("checkDownloadAccess[" + userId + "]");
            phpr.destroyWidgets("checkAdminAccess[" + userId + "]");
            phpr.destroyWidgets("checkNoneAccess[" + userId + "]");
            phpr.destroyWidgets("accessDeleteButton" + userId);
            
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

    deleteAccess: function (userId) {
        // summary:
        //    Remove the row of one user-accees
        // description:
        //    Remove the row of one user-accees
        //    and destroy all the used widgets
        phpr.destroyWidgets("dataAccess[" + userId + "]");
        phpr.destroyWidgets("checkReadAccess[" + userId + "]");
        phpr.destroyWidgets("checkWriteAccess[" + userId + "]");
        phpr.destroyWidgets("checkAccessAccess[" + userId + "]");
        phpr.destroyWidgets("checkCreateAccess[" + userId + "]");
        phpr.destroyWidgets("checkCopyAccess[" + userId + "]");
        phpr.destroyWidgets("checkDeleteAccess[" + userId + "]");
        phpr.destroyWidgets("checkDownloadAccess[" + userId + "]");
        phpr.destroyWidgets("checkAdminAccess[" + userId + "]");
        phpr.destroyWidgets("checkNoneAccess[" + userId + "]");       
        phpr.destroyWidgets("accessDeleteButton" + userId);

        var e = dojo.byId("trAccessFor" + userId);
        var parent = e.parentNode;
        parent.removeChild(e);
    },

    checkAllAccess: function(str) {
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

    checkNoneAccess: function(str) {
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

    submitForm: function() {
        // summary:
        //    This function is responsible for submitting the formdata
        // description:
        //    This function sends the form data as json data to the server
        //    and call the reload routine
        for(var i = 0; i < this.formsWidget.length; i++) {
            this.sendData = dojo.mixin(this.sendData, this.formsWidget[i].attr('value'));
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

    deleteForm: function() {
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

    displayTagInput: function() {
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

    getHistoryData: function(items, request) {
        // summary:
        //    This function renders the history data
        // description:
        //    This function processes the form data which is stored in a phpr.DataStore and
        //    renders the actual form according to the received data
        var history = "";

        this.historyData = '<tr><td class="label" colspan="2"><table  id="historyTable" style="position: relative; left: 75px">';

        history = this.historyStore.getValue(items[0], "history");
        
        
        if (history.length > 0) {
            this.historyData += "<tr><td><label>" + phpr.nls.get('Date');
            this.historyData += "</label></td><td><label>" + phpr.nls.get('User');
            this.historyData += "</label></td><td><label>" + phpr.nls.get('Field');
            this.historyData += "</label></td><td><label>" + phpr.nls.get('Old value') + "</label></td></tr>";
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

            this.historyData += "<tr><td>" + historyDate;
            this.historyData += "</td><td>" + historyUser;
            this.historyData += "</td><td>" + historyField;
            this.historyData += "</td><td>" + historyOldValue;
        }
        this.historyData += "</table></td></tr>";
    },

    updateData:function() {
        // summary:
        //    Delete the cache for this form
        // description:
        //    Delete the cache for this form
        phpr.DataStore.deleteData({url: this._url});
        phpr.DataStore.deleteData({url: this._tagUrl});
    }
});
