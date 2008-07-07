dojo.provide("phpr.Project.Form");
dojo.require("phpr.Default.Form");

dojo.declare("phpr.Project.Form", phpr.Default.Form, {

    moduleList: new Array(),
    roleList: new Array(),
    relationList: new Array(),

    // summary:
    //    This class is responsible for rendering the Form of a Project module
    // description:
    //    The Form for the Project module is rendered -  at the moment it is exactly
    //    the same as in the Default module
    constructor: function() {
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

        // Get modules
        this.roleStore = new phpr.ReadData({
            url: phpr.webpath+"index.php/Project/index/jsonGetProjectRoleUserRelation/id/" + this.id
        });
        this.roleStore.fetch({onComplete: dojo.hitch(this, "getRoleData")});

        // Get modules
        this.moduleStore = new phpr.ReadData({
            url: phpr.webpath+"index.php/Project/index/jsonGetModulesProjectRelation/id/" + this.id
        });
        this.moduleStore.fetch({onComplete: dojo.hitch(this, "getModuleData")});
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

            this.rolesData = this.render(["phpr.Project.template", "rolestab.html"], null, {
                accessUserText: phpr.nls.accessUser,
                accessRoleText: phpr.nls.accessRole,
                accessActionText: phpr.nls.accessAction,
                users: this.userList,
                roles: this.roleList,
                relations: this.relationList,
            });

            this.modulesData = this.render(["phpr.Project.template", "modulestab.html"], null, {
                moduleNameText: phpr.nls.moduleName,
                moduleActiveText: phpr.nls.moduleActive,
                modules: this.moduleList,
            });
        }
        // later on we need to provide different tabs depending on the metadata
        formtabs = this.render(["phpr.Default.template", "tabs.html"], null,{innerTabs:this.formdata,id:'tab1',title:'Basic Data'});
        if (accessPermissions) {
            formtabs += this.render(["phpr.Default.template", "tabs.html"], null,{innerTabs:this.accessData,id:'tab2',title:'Access'});
            formtabs += this.render(["phpr.Default.template", "tabs.html"], null,{innerTabs:this.rolesData,id:'tab3',title:'Roles'});
            formtabs += this.render(["phpr.Default.template", "tabs.html"], null,{innerTabs:this.modulesData,id:'tab4',title:'Modules'});
        }
        formtabs += this.render(["phpr.Default.template", "tabs.html"], null,{innerTabs:this.historyData,id:'tab5',title:'History'});
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

            // add button for role-user
            var params = {
                label:     '',
                id:        'newRoleUser',
                iconClass: 'add',
                alt:       'Add'
            };
            newRoleUser = new dijit.form.Button(params);
            dojo.byId("relationAddButton").appendChild(newRoleUser.domNode);
            dojo.connect(dijit.byId("newRoleUser"), "onClick", dojo.hitch(this, "newRoleUser"));

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

            // delete buttons for role-user relation
            for (i in this.relationList) {
                var userId     = this.relationList[i].userId;
                var idName     = "deleteRelation" + userId;
                var buttonName = "relationDeleteButton" + userId;
                var params = {
                    label:     '',
                    id:        idName,
                    iconClass: 'cross',
                    alt:       'Delete'
                };
                idName = new dijit.form.Button(params);
                dojo.byId(buttonName).appendChild(idName.domNode);
                dojo.connect(dijit.byId(idName), "onClick", dojo.hitch(this, "deleteUserRoleRelation", userId));
            }
        }

        // action buttons for the form
        dojo.connect(dijit.byId("submitButton"), "onClick", dojo.hitch(this, "submitForm"));
        dojo.connect(dijit.byId("deleteButton"), "onClick", dojo.hitch(this, "deleteForm"));
    },

    newRoleUser: function () {
        // summary:
        //    Add a new row of one user-role
        // description:
        //    Add a new row of one user-role
        //    with the values selected on the first row
        var roleId = dijit.byId("relationRoleAdd").getValue();
        var userId = dijit.byId("relationUserAdd").getValue();
        if (!dojo.byId("trRelationFor" + userId) && userId > 0) {
            var roleName = dijit.byId("relationRoleAdd").getDisplayedValue();
            var userName = dijit.byId("relationUserAdd").getDisplayedValue();
            var table    = dojo.byId("relationTable");
            var row      = table.insertRow(table.rows.length);
            row.id       = "trRelationFor" + userId;

            var cell = row.insertCell(0);
            cell.innerHTML = '<input id="roleRelation[' + userId + ']" name="roleRelation[' + userId + ']" type="hidden" value="' + roleId + '" dojoType="dijit.form.TextBox" />' + roleName;
            var cell = row.insertCell(1);
            cell.innerHTML = '<input id="userRelation[' + userId + ']" name="userRelation[' + userId + ']" type="hidden" value="' + userId + '" dojoType="dijit.form.TextBox" />' + userName;
            var cell = row.insertCell(2);
            cell.innerHTML = '<div id="relationDeleteButton' + userId + '"></div>';

            dojo.parser.parse(row);

            var idName     = "deleteRelation" + userId;
            var buttonName = "relationDeleteButton" + userId;
            var params = {
                label:     '',
                id:        idName,
                iconClass: 'cross',
                alt:       'Delete'
            };
            idName = new dijit.form.Button(params);
            dojo.byId(buttonName).appendChild(idName.domNode);
            dojo.connect(dijit.byId(idName), "onClick", dojo.hitch(this, "deleteUserRoleRelation", userId));
        }
    },

    deleteUserRoleRelation: function (userId) {
        // summary:
        //    Remove the row of one user-accees
        // description:
        //    Remove the row of one user-accees
        //    and destroy all the used widgets
        phpr.destroySimpleWidget("roleRelation[" + userId + "]");
        phpr.destroySimpleWidget("userRelation[" + userId + "]");
        phpr.destroyWidgets("deleteRelation" + userId);
        phpr.destroyWidgets("relationDeleteButton" + userId);

        var e = dojo.byId("trRelationFor" + userId);
        var parent = e.parentNode;
        parent.removeChild(e);
    },

    getModuleData: function(items, request) {
        // summary:
        //    This function get all the active modules
        // description:
        //    This function get all the active modules,
        //    and make the array for draw it with the relation module-project
        var modules = this.moduleStore.getValue(items[0], "data");
        this.moduleList = new Array();

        for (i in modules) {
            this.moduleList.push({"id":modules[i]['id'],"name":modules[i]['name'],
                                  "inProject":modules[i]['inProject']})
        }
    },

    getRoleData: function(items, request) {
        // summary:
        //    This function get all the roles and their assignes user for onw project
        // description:
        //    This function get all the roles and their assignes user for onw project
        var roles         = this.roleStore.getValue(items[0], "data");
        this.roleList     = new Array();
        this.relationList = new Array();

        for (i in roles) {
            this.roleList.push({"id":roles[i]['id'], "name":roles[i]['name']});
            for (j in roles[i]['users']) {
                this.relationList.push({"roleId": roles[i]['id'],
                                        "roleName": roles[i]['name'],
                                        "userId": roles[i]['users'][j]['id'],
                                        "userName": roles[i]['users'][j]['name']});
            }
        }
    }
});