dojo.provide("phpr.Project.Form");

dojo.require("phpr.Default.Form");

dojo.declare("phpr.Project.Form", phpr.Default.Form, {
    initData: function() {
        dojo.inher
        this.historyStore = new phpr.ReadHistory({
            url: phpr.webpath+"index.php/History/index/jsonList/moduleName/" + phpr.module + "/itemId/" + this.id
        });
        this.historyStore.fetch({onComplete: dojo.hitch(this, "getHistoryData")});

        // Get all the active users
        this.userStore = new phpr.User();
        this.userStore.fetch();

        // Get modules
        this.roleStore = new phpr.Role(this.id);
        this.roleStore.fetch();

        // Get modules
        this.moduleStore = new phpr.Module(this.id);
        this.moduleStore.fetch();
    },

    addModuleTab:function(data) {
        // summary:
        //    Add Tab for allow/disallow modules on the project
        // description:
        //    Add Tab for allow/disallow modules on the project
        if (this._accessPermissions) {
            var modulesData = this.render(["phpr.Project.template", "modulestab.html"], null, {
                moduleNameText:   phpr.nls.moduleName,
                moduleActiveText: phpr.nls.moduleActive,
                modules:          this.moduleStore.getModuleList(),
            });

            this.addTab(modulesData, 'tabModules', 'Modules', 'moduleFormTab');
        }
    },

    addRoleTab:function(data) {
        // summary:
        //    Add Tab for user-role relation into the project
        // description:
        //    Add Tab for user-role relation into the project
        if (this._accessPermissions) {
            var rolesData = this.render(["phpr.Project.template", "rolestab.html"], null, {
                accessUserText:   phpr.nls.accessUser,
                accessRoleText:   phpr.nls.accessRole,
                accessActionText: phpr.nls.accessAction,
                users:            this.userStore.getUserList(),
                roles:            this.roleStore.getRoleList(),
                relations:        this.roleStore.getRelationList(),
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
    },

    addModuleTabs:function(data) {
        this.addAccessTab(data);
        this.addModuleTab(data);
        this.addRoleTab(data);
        this.addTab(this.historyData, 'tabHistory', 'History');
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
});