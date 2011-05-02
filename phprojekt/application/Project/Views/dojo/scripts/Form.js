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
 * @author     Gustavo Solt <gustavo.solt@mayflower.de>
 */

dojo.provide("phpr.Project.Form");

dojo.declare("phpr.Project.Form", phpr.Default.Form, {
    // Event Tab
    _eventForRoleTab:   null,
    _eventForModuleTab: null,

    // Modules
    _hiddenModuleTab: true,
    _moduleRender:    null,
    _moduleStore:     null,

    // Roles
    _hiddenRoleTab: true,
    _roleRender:    null,
    _roleStore:     null,

    init:function(id, params) {
        // Summary:
        //    Init the form for a new render.
        this._moduleStore = null;
        this._roleStore   = null;

        this.inherited(arguments);
    },

    updateData:function() {
        // Summary:
        //    Delete the cache for this form.
        this.inherited(arguments);

        var subModuleUrl = phpr.webpath + 'index.php/Default/index/jsonGetModulesPermission/nodeId/' + this._id;
        phpr.DataStore.deleteData({url: subModuleUrl});

        this._moduleStore.update();
        this._roleStore.update();

        // Delete cache for Timecard on places where Projects are shown
        dojo.publish('Timecard.formProxy', ['forceUpdate']);
        phpr.DataStore.deleteData({url: phpr.webpath + 'index.php/Timecard/index/jsonGetFavoritesProjects'});
        phpr.DataStore.deleteDataPartialString({url: phpr.webpath + 'index.php/Timecard/index/jsonDetail/'});
    },

    /************* Private functions *************/

    _constructor:function(module, subModules) {
        // Summary:
        //    Construct the form only one time.
        this.inherited(arguments);

        // Project vars
        this._moduleRender      = new phpr.Project.Modules(this._module);
        this._roleRender        = new phpr.Project.Roles(this._module);
        this._eventForRoleTab   = null;
        this._eventForModuleTab = null;
    },

    _initData:function() {
        // Summary:
        //    Init all the data before draw the form.
        // Get roles
        this._roleStore = new phpr.Store.Role(phpr.currentProjectId, this._id);
        this._initDataArray.push({'store': this._roleStore});

        // Get modules
        this._moduleStore = new phpr.Store.Module(phpr.currentProjectId, this._id);
        this._initDataArray.push({'store': this._moduleStore});

        this.inherited(arguments);
    },

    _addModuleTabs:function(data) {
        // Summary:
        //    Add extra tabs.
        this._addAccessTab(data);
        this._addModuleTab(data);
        this._addRoleTab(data);
        this._addNotificationTab(data);
        this._addHistoryTab();
    },

    _addModuleTab:function(data) {
        // Summary:
        //    Module tab.
        // Description:
        //    Display all the modules and a checkbox
        //    for add or remove the module on this project.
        this._hiddenModuleTab = true;

        var tabId  = 'tabModules-' + this._module;
        var formId = 'ModulesFormTab-' + this._module;
        this._addTab(null, tabId, 'Module', formId);

        // Create table only when the tab is required
        if (!this._eventForModuleTab) {
            this._eventForModuleTab = dojo.connect(dijit.byId(tabId), 'onShow', dojo.hitch(this, function() {
                if (this._hiddenModuleTab) {
                    // Do not refresh the data until the module is reloaded
                    this._hiddenModuleTab = false;

                    var data                  = this._getModuleData();
                    data['accessPermissions'] = this._accessPermissions;

                    this._moduleRender.createTable(data);

                    if (dijit.byId(formId).getChildren().length == 0) {
                        dijit.byId(formId).domNode.appendChild(this._moduleRender.getTable());
                    }
                }
            }));
        }
    },

    _getModuleData:function(data) {
        // Summary:
        //    Set the new data for show the tab.
        return {
            relationList: this._moduleStore.getList()
        };
    },

    _addRoleTab:function(data) {
        // Summary:
        //    Role tab.
        // Description:
        //    Display all the user-role relation for this project.
        //    Provide a form for add/edit/delete them.
        var currentUser     = data[0]['rights']['currentUser']['userId'] || 0;
        this._hiddenRoleTab = true;

        var tabId  = 'tabRoles-' + this._module;
        var formId = 'RolesFormTab-' + this._module;
        this._addTab(null, tabId, 'Roles', formId);

        // Create table only when the tab is required
        if (!this._eventForRoleTab) {
            this._eventForRoleTab = dojo.connect(dijit.byId(tabId), 'onShow', dojo.hitch(this, function() {
                if (this._hiddenRoleTab) {
                    // Do not refresh the data until the module is reloaded
                    this._hiddenRoleTab = false;

                    var data                  = this._getRoleData();
                    data['accessPermissions'] = this._accessPermissions;
                    data['currentUser']       = currentUser;

                    this._roleRender.createTable(data);

                    if (dijit.byId(formId).getChildren().length == 0) {
                        dijit.byId(formId).domNode.appendChild(this._roleRender.getTable());
                    }
                }
            }));
        }
    },

    _getRoleData:function() {
        // Summary:
        //    Set the new data for show the tab.
        return {
            relationList: this._roleStore.getRelationList(),
            roleList:     this._roleStore.getList(),
            userList:     this._userStore.getList()
        };
    },

    _getFieldForDelete:function() {
        // Summary:
        //    Return an array of fields for delete.
        var fields = this.inherited(arguments);

        // Module fields
        if (this._hiddenModuleTab) {
            // If the tab was not requested, delete any old values for modules
            fields.push('moduleRelation*');
        }

        // Role fields
        fields.push('relationUserAdd');
        fields.push('relationRoleAdd');
        if (this._hiddenRoleTab) {
            // If the tab was not requested, delete any old values for roles
            fields.push('userRoleRelation*');
        }

        return fields;
    },

    _deleteForm:function() {
        // Summary:
        //    Delete an item.
        var result     = Array();
        result.type    = 'warning';
        result.message = phpr.nls.get('The deletion of a project and its subprojects might take a while');
        new phpr.handleResponse('serverFeedback', result);

        this.inherited(arguments);
    }
});
