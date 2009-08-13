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

dojo.provide("phpr.Project.Form");

dojo.declare("phpr.Project.Form", phpr.Default.Form, {
    initData:function() {
        // Get all the active users
        this.userStore = new phpr.Store.User();
        this._initData.push({'store': this.userStore});

        // Get roles
        this.roleStore = new phpr.Store.Role(this.id);
        this._initData.push({'store': this.roleStore});

        // Get modules
        this.moduleStore = new phpr.Store.Module(this.id);
        this._initData.push({'store': this.moduleStore});

        // Get the tags
        this._tagUrl  = phpr.webpath + 'index.php/Default/Tag/jsonGetTagsByModule/moduleName/' + phpr.module
            + '/id/' + this.id;
        this._initData.push({'url': this._tagUrl});
    },

    addModuleTab:function(data) {
        // Summary:
        //    Add Tab for allow/disallow modules on the project
        // Description:
        //    Add Tab for allow/disallow modules on the project
        if (this._accessPermissions) {
            var modulesData = this.render(["phpr.Project.template", "modulestab.html"], null, {
                moduleNameText:   phpr.nls.get('Module'),
                moduleActiveText: phpr.nls.get('Active'),
                modules:          this.moduleStore.getList()
            });

            this.addTab(modulesData, 'tabModules', 'Module', 'moduleFormTab');
        }
    },

    addRoleTab:function(data) {
        // Summary:
        //    Add Tab for user-role relation into the project
        // Description:
        //    Add Tab for user-role relation into the project
        if (this._accessPermissions) {
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

            var rolesData = this.render(["phpr.Project.template", "rolestab.html"], null, {
                accessUserText:   phpr.nls.get('User'),
                accessRoleText:   phpr.nls.get('Role'),
                accessActionText: phpr.nls.get('Action'),
                users:            users,
                roles:            this.roleStore.getList(),
                currentUser:      currentUser,
                relations:        relationList
            });

            this.addTab(rolesData, 'tabRoles', 'Role', 'roleFormTab');

            // add button for role-user
            var params = {
                label:     '',
                iconClass: 'add',
                alt:       'Add'
            };
            newRoleUser = new dijit.form.Button(params);
            dojo.byId("relationAddButton").appendChild(newRoleUser.domNode);
            dojo.connect(newRoleUser, "onClick", dojo.hitch(this, "newRoleUser"));

            // delete buttons for role-user relation
            for (i in relationList) {
                var userId     = relationList[i].userId;
                var buttonName = "relationDeleteButton" + userId;
                var params = {
                    label:     '',
                    iconClass: 'cross',
                    alt:       'Delete'
                };
                tmp = new dijit.form.Button(params);
                dojo.byId(buttonName).appendChild(tmp.domNode);
                dojo.connect(dijit.byId(tmp.id), "onClick", dojo.hitch(this, "deleteUserRoleRelation", userId));
            }
        }
    },

    addModuleTabs:function(data) {
        this.addAccessTab(data);
        this.addModuleTab(data);
        this.addRoleTab(data);
        this.addNotificationTab(data);
        if (this.id > 0) {
            this.addTab(this.render(["phpr.Default.template.history", "content.html"]), 'tabHistory', 'History');
        }
    },

    newRoleUser:function() {
        // Summary:
        //    Add a new row of one user-role
        // Description:
        //    Add a new row of one user-role
        //    with the values selected on the first row
        var roleId = dijit.byId("relationRoleAdd").attr('value');
        var userId = dijit.byId("relationUserAdd").attr('value');
        if (!dojo.byId("trRelationFor" + userId) && userId > 0) {
            phpr.destroyWidget("roleRelation[" + userId + "]");
            phpr.destroyWidget("userRelation[" + userId + "]");
            phpr.destroyWidget("relationDeleteButton" + userId);

            var roleName = dijit.byId("relationRoleAdd").attr('displayedValue');
            var userName = dijit.byId("relationUserAdd").attr('displayedValue');
            var table    = dojo.byId("relationTable");
            var row      = table.insertRow(table.rows.length);
            row.id       = "trRelationFor" + userId;

            var cell = row.insertCell(0);
            cell.innerHTML = '<input name="roleRelation[' + userId + ']" type="hidden" value="' + roleId
                + '" dojoType="dijit.form.TextBox" />' + roleName;
            var cell = row.insertCell(1);
            cell.innerHTML = '<input name="userRelation[' + userId + ']" type="hidden" value="' + userId
                + '" dojoType="dijit.form.TextBox" />' + userName;
            var cell = row.insertCell(2);
            cell.innerHTML = '<div id="relationDeleteButton' + userId + '"></div>';

            dojo.parser.parse(row);

            var buttonName = "relationDeleteButton" + userId;
            var params = {
                label:     '',
                iconClass: 'cross',
                alt:       'Delete'
            };
            tmp = new dijit.form.Button(params);
            dojo.byId(buttonName).appendChild(tmp.domNode);
            dojo.connect(dijit.byId(tmp.id), "onClick", dojo.hitch(this, "deleteUserRoleRelation", userId));
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
        phpr.DataStore.deleteData({url: this._url});
        var subModuleUrl = phpr.webpath + 'index.php/Default/index/jsonGetModulesPermission/nodeId/' + this.id;
        phpr.DataStore.deleteData({url: subModuleUrl});
        this.moduleStore.update();
        this.roleStore.update();
        phpr.DataStore.deleteData({url: this._tagUrl});

        // Delete cache for Timecard on places where Projects are shown
        phpr.DataStore.deleteData({url: phpr.webpath + 'index.php/Timecard/index/jsonGetFavoritesProjects'});
        phpr.DataStore.deleteDataPartialString({url: phpr.webpath + 'index.php/Timecard/index/jsonBookingDetail/'});
    }
});
