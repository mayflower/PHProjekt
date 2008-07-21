dojo.provide("phpr.Default.Form");
dojo.require("phpr.Component");
dojo.require("phpr.Default.field");

dojo.declare("phpr.Default.Form", phpr.Component, {
    // summary:
    //    Class for displaying a PHProjekt Detail View
    // description:
    //    This Class takes care of displaying the form information we receive from our Server
    //    in a dojo form with tabs

    formWidget:  null,
    range:       new Array(),
    sendData:    new Array(),
    formdata:    '',
    historyData: '',
    accessData:  '',
    userList:    new Array(),

    constructor:function(main, id, module) {
        // summary:
        //    render the form on construction
        // description:
        //    this function receives the form data from the server and renders the corresponding form
        //    If the module is a param, is setted
        this.main = main;
        this.id = id;

        if (undefined != module) {
            phpr.module = module
        }

        this.initData();

        // Render the form element on the right bottom
        this.formStore = new phpr.ReadStore({
            url: phpr.webpath+"index.php/" + phpr.module + "/index/jsonDetail/id/" + this.id
        });
        this.formStore.fetch({onComplete: dojo.hitch(this, "getFormData")});
    },

    initData: function() {
        // summary:
        //    Init all the data before draw the form
        // description:
        //    This function call all the needed data before the form is drawed
        //    The form will wait for all the data are loaded.
        //    Each module can overwrite this function for load the own data
        this.historyStore = new phpr.ReadHistory({
            url: phpr.webpath+"index.php/History/index/jsonList/moduleName/" + phpr.module + "/itemId/" + this.id
        });
        this.historyStore.fetch({onComplete: dojo.hitch(this, "getHistoryData")});

        // Get all the active users
        this.userStore = new phpr.ReadData({
            url: phpr.webpath+"index.php/User/index/jsonGetUsers"
        });
        this.userStore.fetch({onComplete: dojo.hitch(this, "getUserData")});
    },

    getFormData: function(items, request) {
        // summary:
        //    This function renders the form data according to the database manager settings
        // description:
        //    This function processes the form data which is stored in a phpr.ReadStore and
        //    renders the actual form according to the received data
        phpr.destroyWidgets("detailsBox");
        phpr.destroyWidgets("bottomContent");
        phpr.destroyWidgets("submitButton");
        phpr.destroyWidgets("deleteButton");
        phpr.destroySimpleWidget("dataAccessAdd");
        phpr.destroySimpleWidget("checkAdminAccessAdd");
        phpr.destroySimpleWidget("checkWriteAccessAdd");
        phpr.destroySimpleWidget("checkReadAccessAdd");

        this.formdata = "";
        meta = this.formStore.getValue(items[0], "metadata");
        data = this.formStore.getValue(items[1], "data");
        var writePermissions  = true;
        var deletePermissions = false;
        var accessPermissions = true;
        if (this.id > 0) {
            writePermissions  = data[0]["rights"]["currentUser"]["write"];
            deletePermissions = data[0]["rights"]["currentUser"]["delete"];
            accessPermissions = data[0]["rights"]["currentUser"]["admin"];
        }

        // Except the current user
        var accessContent = new Array();
        var currentUser   = 0;
        if (this.id > 0) {
            currentUser = data[0]["rights"]["currentUser"]["userId"];
            for (i in data[0]["rights"]) {
                if (i != "currentUser") {
                    accessContent.push(data[0]["rights"][i]);
                }
            }
        }

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
            //special workaround for new projects - set parent to current ProjectId
            if(itemid == 'projectId' && !itemvalue){
                itemvalue = phpr.currentProjectId;
            }

            //render the fields according to their type
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
                case 'textfield':
                default:
                    this.formdata += this.fieldTemplate.textFieldRender(itemlabel, itemid, itemvalue, itemrequired,
                                                                        itemdisabled);
                    break;
            }
        }

        // add tags at the end of the first tab
        this.formdata += this.displayTagInput();
        formtabs = "";

        if (accessPermissions) {
            // template for the access tab
            this.accessData = this.render(["phpr.Default.template", "accesstab.html"], null, {
                accessUserText: phpr.nls.accessUser,
                accessReadText: phpr.nls.accessRead,
                accessWriteText: phpr.nls.accessWrite,
                accessAccessText: phpr.nls.accessAccess,
                accessCreateText: phpr.nls.accessCreate,
                accessCopyText: phpr.nls.accessCopy,
                accessDeleteText: phpr.nls.accessDelete,
                accessDownloadText: phpr.nls.accessDownload,
                accessAdminText: phpr.nls.accessAdmin,
                accessNoneText: phpr.nls.accessNone,
                accessActionText: phpr.nls.accessAction,
                users: this.userList,
                currentUser: currentUser,
                accessContent: accessContent,
            });
        }

        // later on we need to provide different tabs depending on the metadata
        formtabs = this.render(["phpr.Default.template", "tabs.html"], null,{innerTabs:this.formdata,id:'tab1',title:'Basic Data'});
        if (accessPermissions) {
            formtabs += this.render(["phpr.Default.template", "tabs.html"], null,{innerTabs:this.accessData,id:'tab2',title:'Access'});
        }
        formtabs += this.render(["phpr.Default.template", "tabs.html"], null,{innerTabs:this.historyData,id:'tab3',title:'History'});
        this.render(["phpr.Default.template", "content.html"], dojo.byId("detailsBox"),{
            formId: 'detailForm' + this.id,
            id: 'formtab',
            tabsContent: formtabs
        });
        this.render(["phpr.Default.template", "formbuttons.html"], dojo.byId("bottomContent"),{
            writePermissions: writePermissions,
            deletePermissions: deletePermissions,
            saveText: phpr.nls.save,
            deleteText: phpr.nls.delete,
        });
        this.formWidget = dijit.byId('detailForm'+this.id);

        if (accessPermissions) {
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
                var userId     = accessContent[i]["userId"]
                var idName     = "deleteAccess" + userId;
                var buttonName = "accessDeleteButton" + userId;
                var params = {
                    label:     '',
                    id:        idName,
                    iconClass: 'cross',
                    alt:       'Delete'
                };
                idName = new dijit.form.Button(params);
                dojo.byId(buttonName).appendChild(idName.domNode);
                dojo.connect(dijit.byId(idName), "onClick", dojo.hitch(this, "deleteAccess", userId));
                dojo.connect(dijit.byId("checkAdminAccess[" + userId + "]"), "onClick", dojo.hitch(this, "checkAllAccess", "[" + userId + "]"));
                dojo.connect(dijit.byId("checkNoneAccess[" + userId + "]"), "onClick", dojo.hitch(this, "checkNoneAccess", "[" + userId + "]"));
            }
        }

        // action buttons for the form
        dojo.connect(dijit.byId("submitButton"), "onClick", dojo.hitch(this, "submitForm"));
        dojo.connect(dijit.byId("deleteButton"), "onClick", dojo.hitch(this, "deleteForm"));
    },

    newAccess: function () {
        // summary:
        //    Add a new row of one user-accees
        // description:
        //    Add a the row of one user-accees
        //    with the values selected on the first row
        var userId = dijit.byId("dataAccessAdd").getValue();
        if (!dojo.byId("trAccessFor" + userId) && userId > 0) {
            var userName = dijit.byId("dataAccessAdd").getDisplayedValue();
            var table    = dojo.byId("accessTable");
            var row      = table.insertRow(table.rows.length);
            row.id       = "trAccessFor" + userId;

            var cell = row.insertCell(0);
            cell.innerHTML = '<input id="dataAccess[' + userId + ']" name="dataAccess[' + userId + ']" type="hidden" value="' + userId + '" dojoType="dijit.form.TextBox" />' + userName;
            var cell = row.insertCell(1);
            cell.innerHTML = '<input type="checkbox" dojotype="dijit.form.CheckBox" id="checkReadAccess[' + userId + ']" name="checkReadAccess[' + userId + ']" checked="' + dijit.byId("checkReadAccessAdd").checked + '" value="1" />';
            var cell = row.insertCell(2);
            cell.innerHTML = '<input type="checkbox" dojotype="dijit.form.CheckBox" id="checkWriteAccess[' + userId + ']" name="checkWriteAccess[' + userId + ']" checked="' + dijit.byId("checkWriteAccessAdd").checked + '" value="1" />';
            var cell = row.insertCell(3);
            cell.innerHTML = '<input type="checkbox" dojotype="dijit.form.CheckBox" id="checkAccessAccess[' + userId + ']" name="checkAccessAccess[' + userId + ']" checked="' + dijit.byId("checkAccessAccessAdd").checked + '" value="1" />';
            var cell = row.insertCell(4);
            cell.innerHTML = '<input type="checkbox" dojotype="dijit.form.CheckBox" id="checkCreateAccess[' + userId + ']" name="checkCreateAccess[' + userId + ']" checked="' + dijit.byId("checkCreateAccessAdd").checked + '" value="1" />';
            var cell = row.insertCell(5);
            cell.innerHTML = '<input type="checkbox" dojotype="dijit.form.CheckBox" id="checkCopyAccess[' + userId + ']" name="checkCopyAccess[' + userId + ']" checked="' + dijit.byId("checkCopyAccessAdd").checked + '" value="1" />';
            var cell = row.insertCell(6);
            cell.innerHTML = '<input type="checkbox" dojotype="dijit.form.CheckBox" id="checkDeleteAccess[' + userId + ']" name="checkDeleteAccess[' + userId + ']" checked="' + dijit.byId("checkDeleteAccessAdd").checked + '" value="1" />';
            var cell = row.insertCell(7);
            cell.innerHTML = '<input type="checkbox" dojotype="dijit.form.CheckBox" id="checkDownloadAccess[' + userId + ']" name="checkDownloadAccess[' + userId + ']" checked="' + dijit.byId("checkDownloadAccessAdd").checked + '" value="1" />';
            var cell = row.insertCell(8);
            cell.innerHTML = '<input type="checkbox" dojotype="dijit.form.CheckBox" id="checkAdminAccess[' + userId + ']" name="checkAdminAccess[' + userId + ']" checked="' + dijit.byId("checkAdminAccessAdd").checked + '" value="1" />';
            var cell = row.insertCell(9);
            cell.innerHTML = '<input type="checkbox" dojotype="dijit.form.CheckBox" id="checkNoneAccess[' + userId + ']" name="checkNoneAccess[' + userId + ']" checked="' + dijit.byId("checkNoneAccessAdd").checked + '" value="1" />';

            var cell = row.insertCell(10);
            cell.innerHTML = '<div id="accessDeleteButton' + userId + '"></div>';

            dojo.parser.parse(row);

            var idName     = "deleteAccess" + userId;
            var buttonName = "accessDeleteButton" + userId;
            var params = {
                label:     '',
                id:        idName,
                iconClass: 'cross',
                alt:       'Delete'
            };
            idName = new dijit.form.Button(params);
            dojo.byId(buttonName).appendChild(idName.domNode);
            dojo.connect(dijit.byId(idName), "onClick", dojo.hitch(this, "deleteAccess", userId));
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
        phpr.destroySimpleWidget("dataAccess[" + userId + "]");
        phpr.destroySimpleWidget("checkReadAccess[" + userId + "]");
        phpr.destroySimpleWidget("checkWriteAccess[" + userId + "]");
        phpr.destroySimpleWidget("checkAccessAccess[" + userId + "]");
        phpr.destroySimpleWidget("checkCreateAccess[" + userId + "]");
        phpr.destroySimpleWidget("checkCopyAccess[" + userId + "]");
        phpr.destroySimpleWidget("checkDeleteAccess[" + userId + "]");
        phpr.destroySimpleWidget("checkDownloadAccess[" + userId + "]");
        phpr.destroySimpleWidget("checkAdminAccess[" + userId + "]");
        phpr.destroySimpleWidget("checkNoneAccess[" + userId + "]");
        phpr.destroyWidgets("deleteAccess" + userId);
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
            dijit.byId("checkReadAccess"+str).setAttribute('checked',true);
            dijit.byId("checkWriteAccess"+str).setAttribute('checked',true);
            dijit.byId("checkAccessAccess"+str).setAttribute('checked',true);
            dijit.byId("checkCreateAccess"+str).setAttribute('checked',true);
            dijit.byId("checkCopyAccess"+str).setAttribute('checked',true);
            dijit.byId("checkDeleteAccess"+str).setAttribute('checked',true);
            dijit.byId("checkDownloadAccess"+str).setAttribute('checked',true);
            dijit.byId("checkNoneAccess"+str).setAttribute('checked',false);
        }
    },

    checkNoneAccess: function(str) {
        // summary:
        //    Un-select all the access
        // description:
        //    Un-select all the access
        if (dijit.byId("checkNoneAccess"+str).checked) {
            dijit.byId("checkReadAccess"+str).setAttribute('checked',false);
            dijit.byId("checkWriteAccess"+str).setAttribute('checked',false);
            dijit.byId("checkAccessAccess"+str).setAttribute('checked',false);
            dijit.byId("checkCreateAccess"+str).setAttribute('checked',false);
            dijit.byId("checkCopyAccess"+str).setAttribute('checked',false);
            dijit.byId("checkDeleteAccess"+str).setAttribute('checked',false);
            dijit.byId("checkDownloadAccess"+str).setAttribute('checked',false);
            dijit.byId("checkAdminAccess"+str).setAttribute('checked',false);
        }
    },

    submitForm: function() {
        // summary:
        //    This function is responsible for submitting the formdata
        // description:
        //    This function sends the form data as json data to the server and publishes
        //    a form.Submitted topic after the data was send.
        this.sendData = this.formWidget.getValues();
        phpr.send({
            url:       phpr.webpath + 'index.php/' + phpr.module + '/index/jsonSave/id/' + this.id,
            content:   this.sendData,
            onSuccess: dojo.hitch(this, function(data){
               new phpr.handleResponse('serverFeedback',data);
               if (!this.id) {
                   this.id = data['id'];
               }
               if (data.type =='success') {
                   phpr.send({
                        url: phpr.webpath + 'index.php/' + phpr.module + '/Tag/jsonSaveTags/id/' + this.id,
                        content:   this.sendData,
                        onSuccess: dojo.hitch(this, function(data){
                            new phpr.handleResponse('serverFeedback',data);
                            if (data.type =='success') {
                                this.publish("reload");
                            }
                        }),
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
        this.sendData = this.formWidget.getValues();
        phpr.send({
            url:       phpr.webpath + 'index.php/' + phpr.module + '/index/jsonDelete/id/' + this.id,
            onSuccess: this.publish("reload")
        });
    },

    displayTagInput: function() {
        // summary:
        // This function manually receives the Tags for the current element
        // description:
        // By calling the TagController this function receives all data it needs
        // for rendering a Tag from the server and renders those tags in a Input separated by coma
        // The function also render the tags in the moveable pannel for click it and search by tags
        phpr.receiveCurrentTags(this.id);

        var currentTags = phpr.getCurrentTags();
        var meta        = currentTags["metadata"][0];
        var data        = new Array();
        var value       = '';

        if (this.id > 0) {
            for (var i = 0; i < currentTags['data'].length; i++) {
                value += currentTags['data'][i]['string'];
                if (i != currentTags['data'].length - 1) {
                    value += ', ';
                }
            }
        }

        // Draw the tags
        this.publish("drawTagsBox",[currentTags]);

        return this.fieldTemplate.textFieldRender(meta['label'], meta['key'], value, false, false);
    },

    getHistoryData: function(items, request) {
        // summary:
        //    This function renders the history data
        // description:
        //    This function processes the form data which is stored in a phpr.ReadStore and
        //    renders the actual form according to the received data
        var history = "";

        this.historyData = '<tr><td class="label" colspan="2"><table>';

        history = this.historyStore.getValue(items[0], "history");

        for (var i = 0; i < history.length; i++) {
            historyUser     = history[i]["userId"];
            historyModule   = history[i]["moduleId"];
            historyItemId   = history[i]["itemId"];
            historyField    = history[i]["field"];
            historyOldValue = history[i]["oldValue"];
            historyNewValue = history[i]["newValue"];
            historyAction   = history[i]["action"];
            historyDate     = history[i]["datetime"];

            this.historyData += "<tr><td>" + historyDate + "</td><td>" + historyUser + "</td><td>" + historyField + "</td><td>" + historyOldValue;
        }
        this.historyData += "</table></td></tr>";
    },

    getUserData: function(items, request) {
        // summary:
        //    This function get all the active users
        // description:
        //    This function get all the active users, except the current user
        //    and make the array for the select
        var users = this.userStore.getValue(items[0], "data");
        this.userList = new Array();

        for (i in users) {
            this.userList.push({"id":users[i]['id'],"name":users[i]['username']})
        }
    }
});