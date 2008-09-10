dojo.provide("phpr.Project.Form");

dojo.declare("phpr.Project.Form", phpr.Default.Form, {
    initData: function() {
        if (this.id > 0) {
            this.historyStore = new phpr.ReadHistory({
                url: phpr.webpath+"index.php/Core/history/jsonList/moduleName/" + phpr.module + "/itemId/" + this.id
            });
            this.historyStore.fetch({onComplete: dojo.hitch(this, "getHistoryData")});
        }

        // Get all the active users
        this.userStore = new phpr.Store.User();
        this.userStore.fetch();

        // Get modules
        this.roleStore = new phpr.Store.Role(this.id);
        this.roleStore.fetch();

        // Get modules
        this.moduleStore = new phpr.Store.Module(this.id);
        this.moduleStore.fetch();
    },

    addModuleTab:function(data) {
        // summary:
        //    Add Tab for allow/disallow modules on the project
        // description:
        //    Add Tab for allow/disallow modules on the project
        phpr.destroyWidgets("tabModules");
        if (this._accessPermissions) {
            var modulesData = this.render(["phpr.Project.template", "modulestab.html"], null, {
                moduleNameText:   phpr.nls.get('moduleName'),
                moduleActiveText: phpr.nls.get('moduleActive'),
                modules:          this.moduleStore.getList()
            });

            this.addTab(modulesData, 'tabModules', 'Modules', 'moduleFormTab');
        }
    },

    addRoleTab:function(data) {
        // summary:
        //    Add Tab for user-role relation into the project
        // description:
        //    Add Tab for user-role relation into the project
        phpr.destroyWidgets("tabRoles");
        phpr.destroyWidgets("newRoleUser");

        if (this._accessPermissions) {
            var relationList = this.roleStore.getRelationList();
            var rolesData = this.render(["phpr.Project.template", "rolestab.html"], null, {
                accessUserText:   phpr.nls.get('accessUser'),
                accessRoleText:   phpr.nls.get('accessRole'),
                accessActionText: phpr.nls.get('accessAction'),
                users:            this.userStore.getList(),
                roles:            this.roleStore.getList(),
                relations:        relationList
            });

            this.addTab(rolesData, 'tabRoles', 'Roles', 'roleFormTab');

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

            // delete buttons for role-user relation
            for (i in relationList) {
                var userId     = relationList[i].userId;
                var idName     = "deleteRelation" + userId;
                var buttonName = "relationDeleteButton" + userId;
                phpr.destroyWidgets(idName);
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
    },

    addModuleTabs:function(data) {
        this.addAccessTab(data);
        this.addModuleTab(data);
        this.addRoleTab(data);
        if (this.id > 0) {
            this.addTab(this.historyData, 'tabHistory', 'History');
        }
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

    updateData:function() {
        phpr.DataStore.deleteData({url: this._url});
        var subModuleUrl = phpr.webpath + 'index.php/Default/index/jsonGetModulesPermission/nodeId/' + this.id;
        phpr.DataStore.deleteData({url: subModuleUrl});
        this.moduleStore.update();
    }
});