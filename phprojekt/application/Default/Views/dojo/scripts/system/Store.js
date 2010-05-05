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
 * @subpackage Default
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Store");
dojo.provide("phpr.Store.User");
dojo.provide("phpr.Store.Module");
dojo.provide("phpr.Store.Role");
dojo.provide("phpr.Store.RoleModuleAccess");
dojo.provide("phpr.Store.Tab");
dojo.provide("phpr.Store.Config");

dojo.declare("phpr.Store", phpr.Component, {
    // Summary:
    //    Get all the active users
    // Description:
    //    Get the users and return the list
    //    for use with dojo fields
    _url:  null,
    _list: null,

    fetch:function(processData) {
        // Summary:
        //    Get all the active users
        // Description:
        //    Get all the active users
        var self = this;
        if (typeof processData == "undefined") {
            processData = null;
        }
        phpr.DataStore.addStore({url: this._url});
        phpr.DataStore.requestData({url: this._url, processData: dojo.hitch(this, function() {
            self.makeSelect();
            if (processData) {
                processData.call();
            }
        })});
    },

    makeSelect:function() {
        this._list = new Array();
    },

    getList:function() {
        return this._list;
    },

    update:function() {
        // Summary:
        //    Delete de cache
        // Description:
        //    Delete de cache
        phpr.DataStore.deleteData({url: this._url});
    }
});

dojo.declare("phpr.Store.User", phpr.Store, {
    constructor:function(projectId) {
        if (!projectId) {
            projectId = phpr.currentProjectId;
        }
        this._url = phpr.webpath + 'index.php/Core/user/jsonGetUsers/nodeId/' + projectId;
    },

    makeSelect:function() {
        // Summary:
        //    This function get all the active users
        // Description:
        //    This function get all the active users, except the current user
        //    and make the array for the select
        var users  = phpr.DataStore.getData({url: this._url});
        this._list = new Array();
        for (i in users) {
            this._list.push({"id":      users[i]['id'],
                             "display": users[i]['display'],
                             "current": users[i]['current']});
        }
    }
});

dojo.declare("phpr.Store.Module", phpr.Store, {
    constructor:function(nodeId, id) {
        this._url = phpr.webpath + 'index.php/Project/index/jsonGetModulesProjectRelation'
            + '/nodeId/' + nodeId + '/id/' + id;
    },

    makeSelect:function() {
        // Summary:
        //    This function get all the active modules
        // Description:
        //    This function get all the active modules,
        //    and make the array for draw it with the relation module-project
        var modules = phpr.DataStore.getData({url: this._url});
        this._list  = new Array();
        for (i in modules) {
            this._list.push({"id":        modules[i]['id'],
                             "name":      modules[i]['name'],
                             "label":     modules[i]['label'],
                             "inProject": modules[i]['inProject']})
        }
    }
});

dojo.declare("phpr.Store.Role", phpr.Store, {
    _relationList: null,

    constructor:function(nodeId, id) {
        this._url = phpr.webpath + 'index.php/Project/index/jsonGetProjectRoleUserRelation'
            + '/nodeId/' + nodeId + '/id/' + id;
    },

    makeSelect:function() {
        // Summary:
        //    This function get all the roles and their assignes user for onw project
        // Description:
        //    This function get all the roles and their assignes user for onw project
        var roles          = phpr.DataStore.getData({url: this._url});
        this._list         = new Array();
        this._relationList = new Array();
        for (i in roles) {
            this._list.push({"id":roles[i]['id'], "name":roles[i]['name']});
            for (j in roles[i]['users']) {
                this._relationList.push({"roleId":      roles[i]['id'],
                                         "roleName":    roles[i]['name'],
                                         "userId":      roles[i]['users'][j]['id'],
                                         "userDisplay": roles[i]['users'][j]['display']});
            }
        }
    },

    getRelationList:function() {
        return this._relationList;
    }
});

dojo.declare("phpr.Store.RoleModuleAccess", phpr.Store, {
    constructor:function(id) {
        this._url = phpr.webpath + 'index.php/Core/role/jsonGetModulesAccess/id/' + id;
    },

    makeSelect:function() {
        // Summary:
        //    This function get all the roles and their assignes user for onw project
        // Description:
        //    This function get all the roles and their assignes user for onw project
        var modules = phpr.DataStore.getData({url: this._url});
        this._list  = new Array();
        for (i in modules) {
            this._list.push({"id":     modules[i]['id'],
                             "name":   modules[i]['name'],
                             "label":  modules[i]['label'],
                             "read":   modules[i]['read'],
                             "write":  modules[i]['write'],
                             "create": modules[i]['create'],
                             "admin":  modules[i]['admin']})
        }
    }
});

dojo.declare("phpr.Store.Tab", phpr.Store, {
    constructor:function(id) {
        this._url = phpr.webpath + 'index.php/Core/tab/jsonList/nodeId/1';
    },

    makeSelect:function() {
        // Summary:
        //    This function get all the roles and their assignes user for onw project
        // Description:
        //    This function get all the roles and their assignes user for onw project
        var tabs   = phpr.DataStore.getData({url: this._url});
        this._list = new Array();
        for (i in tabs) {
            var nameId = tabs[i]['label'].toString().split(' ').join('');
            this._list.push({"id":     tabs[i]['id'],
                             "name":   tabs[i]['label'],
                             "nameId": nameId})
        }
    }
});

dojo.declare("phpr.Store.Config", phpr.Store, {
    constructor:function(id) {
        this._url = phpr.webpath + 'index.php/Default/index/jsonGetConfigurations/'
    },

    makeSelect:function() {
        // Summary:
        //    Return all the front configurations from the configuration.php
        // Description:
        //    Return all the front configurations from the configuration.php
        var config = phpr.DataStore.getData({url: this._url});
        this._list = new Object();
        for (i in config) {
            if (config[i]['name']) {
                this._list[config[i]['name']] = config[i]['value'];
            }
        }
    }
});