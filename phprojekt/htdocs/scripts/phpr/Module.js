dojo.provide("phpr.Module");

dojo.require("phpr.Component");

dojo.declare("phpr.Module", phpr.Component, {
    // summary:
    //    Get all the active modules
    // description:
    //    Get the modules and return the list
    //    for use with dojo fields
    _url:          null,
    _moduleList:   null,

    constructor:function(id) {
        this._url = phpr.webpath+"index.php/Project/index/jsonGetModulesProjectRelation/id/" + id
    },

    fetch:function() {
        // summary:
        //    Get all the active modules
        // description:
        //    Get all the active modules
        phpr.DataStore.addStore({url: this._url});
        phpr.DataStore.requestData({url: this._url, processData: dojo.hitch(this, "makeSelect")});
    },

    makeSelect: function() {
        // summary:
        //    This function get all the active modules
        // description:
        //    This function get all the active modules,
        //    and make the array for draw it with the relation module-project
        var modules = phpr.DataStore.getData({url: this._url});
        this._moduleList = new Array();
        for (i in modules) {
            this._moduleList.push({"id":modules[i]['id'],"name":modules[i]['name'],
                                  "inProject":modules[i]['inProject']})
        }
    },

    getModuleList:function() {
        return this._moduleList;
    },
});