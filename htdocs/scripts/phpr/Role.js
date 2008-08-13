dojo.provide("phpr.Role");

dojo.require("phpr.Component");

dojo.declare("phpr.Role", phpr.Component, {
    // summary:
    //    Get all the roles
    // description:
    //    Get the roles and return the list
    //    for use with dojo fields
    _url:          null,
    _roleList:     null,
    _relationList: null,

    constructor:function(id) {
        this._url = phpr.webpath+"index.php/Project/index/jsonGetProjectRoleUserRelation/id/" + id
    },

    fetch:function() {
        // summary:
        //    Get all the roles
        // description:
        //    Get all the roles
        phpr.DataStore.addStore({url: this._url});
        phpr.DataStore.requestData({url: this._url, processData: dojo.hitch(this, "makeSelect")});
    },

    makeSelect: function() {
        // summary:
        //    This function get all the roles and their assignes user for onw project
        // description:
        //    This function get all the roles and their assignes user for onw project
        var roles          = phpr.DataStore.getData({url: this._url});
        this._roleList     = new Array();
        this._relationList = new Array();
        for (i in roles) {
            this._roleList.push({"id":roles[i]['id'], "name":roles[i]['name']});
            for (j in roles[i]['users']) {
                this._relationList.push({"roleId": roles[i]['id'],
                                         "roleName": roles[i]['name'],
                                         "userId": roles[i]['users'][j]['id'],
                                         "userName": roles[i]['users'][j]['name']});
            }
        }
    },

    getRoleList:function() {
        return this._roleList;
    },

    getRelationList:function() {
        return this._relationList;
    },
});