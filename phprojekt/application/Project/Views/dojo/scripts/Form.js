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
        this._tagUrl  = phpr.webpath + 'index.php/Default/Tag/jsonGetTagsByModule/moduleName/' + phpr.module + '/id/' + this.id;
        this._initData.push({'url': this._tagUrl});

        // History data
        if (this.id > 0) {
            this._historyUrl = phpr.webpath+"index.php/Core/history/jsonList/moduleName/" + phpr.module + "/itemId/" + this.id
            this._initData.push({'url': this._historyUrl, 'noCache': true});
        }
    },

    addModuleTab:function(data) {
        // summary:
        //    Add Tab for allow/disallow modules on the project
        // description:
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
        // summary:
        //    Add Tab for user-role relation into the project
        // description:
        //    Add Tab for user-role relation into the project
        if (this._accessPermissions) {
            var currentUser = 0;
            var users       = new Array();
            var userList    = this.userStore.getList();

            if (this.id > 0) {
                currentUser = data[0]["rights"]["currentUser"]["userId"];
            }

            // Make an array with the users expect the current one
            if (userList) {
                for (var i in userList) {
                    if (userList[i].id != currentUser) {
                        users.push({'id': userList[i].id, 'name': userList[i].name});
                    }
                }
            }

            var relationList = this.roleStore.getRelationList();
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
            this.addTab(this.getHistoryData(), 'tabHistory', 'History');
        }
    },

    newRoleUser:function() {
        // summary:
        //    Add a new row of one user-role
        // description:
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
            cell.innerHTML = '<input name="roleRelation[' + userId + ']" type="hidden" value="' + roleId + '" dojoType="dijit.form.TextBox" />' + roleName;
            var cell = row.insertCell(1);
            cell.innerHTML = '<input name="userRelation[' + userId + ']" type="hidden" value="' + userId + '" dojoType="dijit.form.TextBox" />' + userName;
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
        // summary:
        //    Remove the row of one user-accees
        // description:
        //    Remove the row of one user-accees
        //    and destroy all the used widgets
        phpr.destroyWidget("roleRelation[" + userId + "]");
        phpr.destroyWidget("userRelation[" + userId + "]");
        phpr.destroyWidget("relationDeleteButton" + userId);

        var e = dojo.byId("trRelationFor" + userId);
        var parent = e.parentNode;
        parent.removeChild(e);
    },

    updateData:function() {
        phpr.DataStore.deleteData({url: this._url});
        var subModuleUrl = phpr.webpath + 'index.php/Default/index/jsonGetModulesPermission/nodeId/' + this.id;
        phpr.DataStore.deleteData({url: subModuleUrl});
        this.moduleStore.update();
        this.roleStore.update();
        phpr.DataStore.deleteData({url: this._tagUrl});
    }
});
