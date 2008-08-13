dojo.provide("phpr.User");

dojo.require("phpr.Component");

dojo.declare("phpr.User", phpr.Component, {
    // summary:
    //    Get all the active users
    // description:
    //    Get the users and return the list
    //    for use with dojo fields
    _url:      null,
    _userList: null,

    constructor:function() {
        this._url = phpr.webpath+"index.php/User/index/jsonGetUsers";
    },

    fetch:function() {
        // summary:
        //    Get all the active users
        // description:
        //    Get all the active users
        phpr.DataStore.addStore({url: this._url});
        phpr.DataStore.requestData({url: this._url, processData: dojo.hitch(this, "makeSelect")});
    },

    makeSelect: function() {
        // summary:
        //    This function get all the active users
        // description:
        //    This function get all the active users, except the current user
        //    and make the array for the select
        var users    = phpr.DataStore.getData({url: this._url});
        this._userList = new Array();
        for (i in users) {
            this._userList.push({"id":users[i]['id'],"name":users[i]['username']});
        }
    },

    getUserList:function() {
        return this._userList;
    }
});