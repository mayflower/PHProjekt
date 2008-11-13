dojo.provide("phpr.Store");
dojo.provide("phpr.Store.User");
dojo.provide("phpr.Store.Module");
dojo.provide("phpr.Store.Role");
dojo.provide("phpr.Store.RoleModuleAccess");
dojo.provide("phpr.Store.Tab");

dojo.declare("phpr.Store", phpr.Component, {
    // summary:
    //    Get all the active users
    // description:
    //    Get the users and return the list
    //    for use with dojo fields
    _url:  null,
    _list: null,

    fetch:function() {
        // summary:
        //    Get all the active users
        // description:
        //    Get all the active users
        phpr.DataStore.addStore({url: this._url});
        phpr.DataStore.requestData({url: this._url, processData: dojo.hitch(this, "makeSelect")});
    },

    makeSelect: function() {
        this._list = new Array();
    },

    getList:function() {
        return this._list;
    },

    update:function() {
        // summary:
        //    Delete de cache
        // description:
        //    Delete de cache
        phpr.DataStore.deleteData({url: this._url});
    }
});

dojo.declare("phpr.Store.User", phpr.Store, {
    constructor:function() {
        this._url = phpr.webpath+"index.php/Core/user/jsonGetUsers";
    },

    makeSelect: function() {
        // summary:
        //    This function get all the active users
        // description:
        //    This function get all the active users, except the current user
        //    and make the array for the select
        var users    = phpr.DataStore.getData({url: this._url});
        this._list = new Array();
        for (i in users) {
            this._list.push({"id":users[i]['id'],"name":users[i]['username']});
        }
    }
});

dojo.declare("phpr.Store.Module", phpr.Store, {
    constructor:function(id) {
        this._url = phpr.webpath+"index.php/Project/index/jsonGetModulesProjectRelation/id/" + id
    },

    makeSelect: function() {
        // summary:
        //    This function get all the active modules
        // description:
        //    This function get all the active modules,
        //    and make the array for draw it with the relation module-project
        var modules = phpr.DataStore.getData({url: this._url});
        this._list = new Array();
        for (i in modules) {
            this._list.push({"id":modules[i]['id'],
                             "name":modules[i]['name'],
                             "label":modules[i]['label'],
                             "inProject":modules[i]['inProject']})
        }
    }
});

dojo.declare("phpr.Store.Role", phpr.Store, {
    _relationList: null,

    constructor:function(id) {
        this._url = phpr.webpath+"index.php/Project/index/jsonGetProjectRoleUserRelation/id/" + id
    },

    makeSelect: function() {
        // summary:
        //    This function get all the roles and their assignes user for onw project
        // description:
        //    This function get all the roles and their assignes user for onw project
        var roles  = phpr.DataStore.getData({url: this._url});
        this._list = new Array();
        this._relationList = new Array();
        for (i in roles) {
            this._list.push({"id":roles[i]['id'], "name":roles[i]['name']});
            for (j in roles[i]['users']) {
                this._relationList.push({"roleId": roles[i]['id'],
                                         "roleName": roles[i]['name'],
                                         "userId": roles[i]['users'][j]['id'],
                                         "userName": roles[i]['users'][j]['name']});
            }
        }
    },

    getRelationList:function() {
        return this._relationList;
    }
});

dojo.declare("phpr.Store.RoleModuleAccess", phpr.Store, {
    constructor:function(id) {
        this._url = phpr.webpath+"index.php/Core/"+phpr.module.toLowerCase()+"/jsonGetModulesAccess/id/" + id;
    },

    makeSelect: function() {
        // summary:
        //    This function get all the roles and their assignes user for onw project
        // description:
        //    This function get all the roles and their assignes user for onw project
        var modules = phpr.DataStore.getData({url: this._url});
        this._list = new Array();
        for (i in modules) {
            this._list.push({"id":modules[i]['id'],
                             "name":modules[i]['name'],
                             "label":modules[i]['label'],
                             "read":modules[i]['read'],
                             "write":modules[i]['write'],
                             "create":modules[i]['create'],
                             "admin":modules[i]['admin']})
        }
    }
});

dojo.declare("phpr.Store.Tab", phpr.Store, {
    constructor:function(id) {
        this._url = phpr.webpath+"index.php/Core/Tab/jsonList";
    },

    makeSelect: function() {
        // summary:
        //    This function get all the roles and their assignes user for onw project
        // description:
        //    This function get all the roles and their assignes user for onw project
        var tabs = phpr.DataStore.getData({url: this._url});
        this._list = new Array();
        for (i in tabs) {
            this._list.push({"id":tabs[i]['id'],"name":tabs[i]['label'],"nameId":tabs[i]['label'].split(' ').join('')})
        }
    }
});