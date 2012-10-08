/**
 This software is free software; you can redistribute it and/or
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
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Project.Form");

dojo.declare("phpr.Project.Form", phpr.Default.Form, {
    initData: function() {
        // Get roles
        this.roleStore = new phpr.Default.System.Store.Role(phpr.currentProjectId, this.id);
        this._initData.push({'store': this.roleStore});

        // Get modules
        this.moduleStore = new phpr.Default.System.Store.Module(phpr.currentProjectId, this.id);
        this._initData.push({'store': this.moduleStore});

        this.inherited(arguments);
    },

    addModuleTab: function(data) {
        // Summary:
        //    Add Tab for allow/disallow modules on the project
        // Description:
        //    Add Tab for allow/disallow modules on the project
        if (this._destroyed) {
            return;
        }

        var modulesData = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Project.template.moduleTab.html",
            templateData: {
                moduleNameText:   phpr.nls.get('Module'),
                moduleActiveText: phpr.nls.get('Active'),
                modules:          this.moduleStore.getList(),
                disabled:        (!this._accessPermissions) ? 'disabled="disabled"' : ''
            }
        });
        this.garbageCollector.addNode(modulesData);

        return this.addTab([modulesData], 'tabModules', 'Module', 'moduleFormTab');
    },

    addRoleTab: function(data) {
        // Summary:
        //    Add Tab for user-role relation into the project
        // Description:
        //    Add Tab for user-role relation into the project
        if (this._destroyed) {
            return;
        }

        var currentUser  = data[0].rights[phpr.currentUserId] ? phpr.currentUserId : 0;
        var users        = [];
        var userList     = phpr.userStore.getList();
        var relationList = this.roleStore.getRelationList();

        // Make an array with the users expect the current one
        if (userList) {
            for (var i in userList) {
                if (userList[i].id != currentUser) {
                    users.push({'id': userList[i].id, 'display': userList[i].display});
                }
            }
        }

        var rolesData = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Project.template.roleTab.html",
            templateData: {
                accessUserText:   phpr.nls.get('User'),
                accessRoleText:   phpr.nls.get('Role'),
                accessActionText: phpr.nls.get('Action'),
                disabled:         (users.length === 0 || !this._accessPermissions) ? 'disabled="disabled"' : '',
                users:            users,
                roles:            this.roleStore.getList()
            }
        });

        this._rolesTab = rolesData;

        for (var i in relationList) {
            this._addRoleTabRow(relationList[i], currentUser);
        }
        this.garbageCollector.addNode(rolesData);

        var def = this.addTab([rolesData], 'tabRoles', 'Role', 'roleFormTab');

        def = def.then(dojo.hitch(this, function() {
            if (this._destroyed) {
                return;
            }

            // Add "add" button for role-user relation
            if (this._accessPermissions && users.length > 0) {
                this.addTinyButton('add', this._rolesTab.relationAddButton, 'newRoleUser');
            }

        }));

        return def;
    },

    _deleteRoleRowForUserId: function(userId) {
        if (this._roleRowsForUsers[userId]) {
            this._roleRowsForUsers[userId].destroyRecursive();
            delete this._roleRowsForUsers[userId];
        }
    },

    _addRoleTabRow: function(relationData, currentUser) {
        if (!this._roleRowsForUsers) {
            this._roleRowsForUsers = {};
        }

        this._deleteRoleRowForUserId(relationData.userId);

        var row = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Project.template.roleRow.html",
            templateData: {
                userId:    relationData.userId
            }
        });

        this.garbageCollector.addNode(row);

        var userField = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Project.template.roleInputUser.html",
            templateData: {
                userId:      relationData.userId,
                disabled:    (!this._accessPermissions) ? 'disabled="disabled"' : '',
                userDisplay: relationData.userDisplay,
                currentUser: (relationData.userId == currentUser)
            }
        });
        this.garbageCollector.addNode(userField);

        row.userField.appendChild(userField.domNode);

        var roleField = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Project.template.roleInputRole.html",
            templateData: {
                userId:      relationData.userId,
                roleId:      relationData.roleId,
                disabled:    (!this._accessPermissions) ? 'disabled="disabled"' : '',
                currentUser: (relationData.userId == currentUser),
                roleName:    relationData.roleName
            }
        });
        this.garbageCollector.addNode(roleField);

        row.roleField.appendChild(roleField.domNode);

        var useDelete = (relationData.userId != currentUser && this._accessPermissions);
        if (useDelete) {
            var button = dojo.create('div');
            this.addTinyButton('delete', button, "_deleteRoleRowForUserId", relationData.userId);
            row.button.appendChild(button);
        }

        this._roleRowsForUsers[relationData.userId] = row;

        this._rolesTab.tbody.appendChild(row.domNode);
    },

    addModuleTabs: function(data) {
        if (this._destroyed) {
            return;
        }

        var def = this.addAccessTab(data);
        def = dojo.when(def, dojo.hitch(this, function() {
            return this.addModuleTab(data);
        }));
        def = dojo.when(def, dojo.hitch(this, function() {
            return this.addRoleTab(data);
        }));
        def = dojo.when(def, dojo.hitch(this, function() {
            return this.addNotificationTab(data);
        }));
        def = dojo.when(def, dojo.hitch(this, function() {
            return this.addWebDavTab();
        }));
        def = dojo.when(def, dojo.hitch(this, function() {
            return this.addHistoryTab();
        }));
        return def;
    },

    newRoleUser: function() {
        // Summary:
        //    Add a new row of one user-role
        // Description:
        //    Add a new row of one user-role
        //    with the values selected on the first row
        var roleWidget = this._rolesTab.relationRoleAdd;
        var userWidget = this._rolesTab.relationUserAdd;

        var roleId = roleWidget.get('value');
        var userId = userWidget.get('value');

        if (!roleId || !userId) {
            return;
        }

        var data = {
            userId:      userId,
            roleId:      roleId,
            disabled:    '',
            userDisplay: this._rolesTab.relationUserAdd.get('displayedValue'),
            roleName:    this._rolesTab.relationRoleAdd.get('displayedValue')
        };

        this._addRoleTabRow(data);

    },

    addWebDavTab: function() {
        // Summary:
        //    Add the webdav tab
        // Description:
        //    Add a tab with the webdav url of the project

        var url = 'index.php/Project/index/jsonTree';
        var that = this;
        phpr.DataStore.addStore({ url: url });
        return phpr.DataStore.requestData({ url: url}).then(
            function(data) {
                data = data.data;
                var path;
                var state = phpr.pageManager.getState();
                var projectId = parseInt(state.projectId, 10) || null;
                if (projectId && dojo.isArray(data.items)) {
                    var error = false,
                        path,
                        url = "",
                        errorMessage = "";

                    try {
                        path = that._buildPathFromTreeData(data.items);
                        url = phpr.getAbsoluteUrl('index.php/WebDAV/index/index/' + path);
                    } catch (e) {
                        error = true;
                        errorMessage = e.message;
                    }

                    var widget = new phpr.Default.System.TemplateWrapper({
                        templateName: "phpr.Project.template.webdavTab.html",
                        templateData: {
                            label: phpr.nls.get("WebDAV url"),
                            url: url,
                            error: error,
                            errorMessage: errorMessage
                        }
                    });

                    return that.addTab([widget], 'tabWebDav', "WebDAV", null);
                }
            }
        );

    },

    _buildPathFromTreeData: function(data) {
        var path = "";
        var hierarchy = phpr.tree.getProjectHierarchyArray(this.id);
        // Remove the toplevel item as the root project corresponds to /
        hierarchy.shift();
        var l = hierarchy.length;

        for (var i = 0; i < l; i++) {
            var segment = hierarchy[i].name[0];
            if (segment.indexOf('/') !== -1) {
                throw new Error(phpr.nls.get("There must be no slashes in project names for WebDAV to work.", "Project"));
            } else {
                path += encodeURIComponent(segment) + "/";
            }
        }

        return path;
    },

    deleteForm: function() {
        // Summary:
        //    This function is responsible for deleting a dojo element
        // Description:
        //    This function calls jsonDeleteAction
        //    Also show a warning since the process can take some time
        var result     = [];
        result.type    = 'warning';
        result.message = phpr.nls.get('The deletion of a project and its subprojects might take a while');
        new phpr.handleResponse('serverFeedback', result);

        this.inherited(arguments);
    },

    updateData: function() {
        this.inherited(arguments);

        var subModuleUrl = 'index.php/Default/index/jsonGetModulesPermission/nodeId/' + this.id;
        phpr.DataStore.deleteData({url: subModuleUrl});
        this.moduleStore.update();
        this.roleStore.update();
    }
});

dojo.declare("phpr.Project.DialogForm", [phpr.Project.Form, phpr.Default.DialogForm], {
});
