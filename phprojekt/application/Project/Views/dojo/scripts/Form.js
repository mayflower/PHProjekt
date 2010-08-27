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
 * @subpackage Project
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Project.Form");

dojo.declare("phpr.Project.Form", phpr.Default.Form, {
    initData:function() {
        // Get roles
        this.roleStore = new phpr.Store.Role(phpr.currentProjectId, this.id);
        this._initData.push({'store': this.roleStore});

        // Get modules
        this.moduleStore = new phpr.Store.Module(phpr.currentProjectId, this.id);
        this._initData.push({'store': this.moduleStore});

        this.inherited(arguments);
    },

    addModuleTab:function(data) {
        // Summary:
        //    Add Tab for allow/disallow modules on the project
        // Description:
        //    Add Tab for allow/disallow modules on the project
        var modulesData = this.render(["phpr.Project.template", "moduleTab.html"], null, {
            moduleNameText:   phpr.nls.get('Module'),
            moduleActiveText: phpr.nls.get('Active'),
            modules:          this.moduleStore.getList(),
            disabled:        (!this._accessPermissions) ? 'disabled="disabled"' : ''
        });

        this.addTab(modulesData, 'tabModules', 'Module', 'moduleFormTab');
    },

    addRoleTab:function(data) {
        // Summary:
        //    Add Tab for user-role relation into the project
        // Description:
        //    Add Tab for user-role relation into the project
        var currentUser  = data[0]["rights"]["currentUser"]["userId"] || 0;
        var users        = new Array();
        var userList     = this.userStore.getList();
        var relationList = this.roleStore.getRelationList();

        // Make an array with the users expect the current one
        if (userList) {
            for (var i in userList) {
                if (userList[i].id != currentUser) {
                    users.push({'id': userList[i].id, 'display': userList[i].display});
                }
            }
        }

        var rows = '';
        for (i in relationList) {
            var userField = this.render(["phpr.Project.template", "roleInputUser.html"], null, {
                userId:      relationList[i].userId,
                disabled:    (!this._accessPermissions) ? 'disabled="disabled"' : '',
                userDisplay: relationList[i].userDisplay,
                currentUser: (relationList[i].userId == currentUser)
            });

            var roleField = this.render(["phpr.Project.template", "roleInputRole.html"], null, {
                userId:      relationList[i].userId,
                roleId:      relationList[i].roleId,
                disabled:    (!this._accessPermissions) ? 'disabled="disabled"' : '',
                currentUser: (relationList[i].userId == currentUser),
                roleName:    relationList[i].roleName
            });

            var button = this.render(["phpr.Project.template", "roleButton.html"], null, {
                userId:    relationList[i].userId,
                useDelete: (relationList[i].userId != currentUser && this._accessPermissions)
            });
            rows += this.render(["phpr.Project.template", "roleRow.html"], null, {
                userId:    relationList[i].userId,
                userField: userField,
                roleField: roleField,
                button:    button
            });
        }
        var rolesData = this.render(["phpr.Project.template", "roleTab.html"], null, {
            accessUserText:   phpr.nls.get('User'),
            accessRoleText:   phpr.nls.get('Role'),
            accessActionText: phpr.nls.get('Action'),
            disabled:         (users.length == 0 || !this._accessPermissions) ? 'disabled="disabled"' : '',
            users:            users,
            roles:            this.roleStore.getList(),
            rows:             rows
        });

        this.addTab(rolesData, 'tabRoles', 'Role', 'roleFormTab');

        // Add "add" button for role-user relation
        if (this._accessPermissions && users.length > 0) {
            this.addTinyButton('add', 'relationAddButton', 'newRoleUser');
        }

        // Add "delete" buttons for role-user relation
        for (i in relationList) {
            if (relationList[i].userId != currentUser && this._accessPermissions) {
                var userId = relationList[i].userId;
                this.addTinyButton('delete', 'relationDeleteButton' + userId, 'deleteUserRoleRelation', [userId]);
            }
        }
    },

    addModuleTabs:function(data) {
        this.addAccessTab(data);
        this.addModuleTab(data);
        this.addRoleTab(data);
        this.addNotificationTab(data);
        this.addHistoryTab();
    },

    newRoleUser:function() {
        // Summary:
        //    Add a new row of one user-role
        // Description:
        //    Add a new row of one user-role
        //    with the values selected on the first row
        var roleId = dijit.byId("relationRoleAdd").get('value');
        var userId = dijit.byId("relationUserAdd").get('value');
        if (!dojo.byId("trRelationFor" + userId) && userId > 0) {
            phpr.destroyWidget("roleRelation[" + userId + "]");
            phpr.destroyWidget("userRelation[" + userId + "]");
            phpr.destroyWidget("relationDeleteButton" + userId);

            var table = dojo.byId("relationTable");
            var row   = table.insertRow(table.rows.length);
            row.id    = "trRelationFor" + userId;

            // User field
            var cellIndex = 0;
            var userField = this.render(["phpr.Project.template", "roleInputUser.html"], null, {
                userId:      userId,
                disabled:    '',
                userDisplay: dijit.byId("relationUserAdd").get('displayedValue'),
                currentUser: false
            });
            var cell = row.insertCell(cellIndex);
            cell.innerHTML = userField;
            cellIndex++;

            // Role field
            var roleField = this.render(["phpr.Project.template", "roleInputRole.html"], null, {
                userId:      userId,
                roleId:      roleId,
                disabled:    '',
                currentUser: false,
                roleName:    dijit.byId("relationRoleAdd").get('displayedValue')
            });
            var cell = row.insertCell(cellIndex);
            cell.innerHTML = roleField;
            cellIndex++;

            // Delete button
            var button = this.render(["phpr.Project.template", "roleButton.html"], null, {
                userId:      userId,
                useDelete:   true
            });
            var cell = row.insertCell(cellIndex);
            cell.innerHTML = button;
            cellIndex++;

            dojo.parser.parse(row);

            this.addTinyButton('delete', 'relationDeleteButton' + userId, 'deleteUserRoleRelation', [userId]);
        }
    },

    deleteUserRoleRelation:function(userId) {
        // Summary:
        //    Remove the row of one user-accees
        // Description:
        //    Remove the row of one user-accees
        //    and destroy all the used widgets
        phpr.destroyWidget("roleRelation[" + userId + "]");
        phpr.destroyWidget("userRelation[" + userId + "]");
        phpr.destroyWidget("relationDeleteButton" + userId);

        var e      = dojo.byId("trRelationFor" + userId);
        var parent = e.parentNode;
        parent.removeChild(e);
    },

    deleteForm:function() {
        // Summary:
        //    This function is responsible for deleting a dojo element
        // Description:
        //    This function calls jsonDeleteAction
        //    Also show a warning since the process can take some time
        var result     = Array();
        result.type    = 'warning';
        result.message = phpr.nls.get('The deletion of a project and its subprojects might take a while');
        new phpr.handleResponse('serverFeedback', result);

        this.inherited(arguments);
    },

    updateData:function() {
        this.inherited(arguments);

        var subModuleUrl = phpr.webpath + 'index.php/Default/index/jsonGetModulesPermission/nodeId/' + this.id;
        phpr.DataStore.deleteData({url: subModuleUrl});
        this.moduleStore.update();
        this.roleStore.update();

        // Delete cache for Timecard on places where Projects are shown
        phpr.destroyWidget('timecardTooltipDialog');
        phpr.DataStore.deleteData({url: phpr.webpath + 'index.php/Timecard/index/jsonGetFavoritesProjects'});
        phpr.DataStore.deleteDataPartialString({url: phpr.webpath + 'index.php/Timecard/index/jsonDetail/'});
    }
});
