dojo.provide("phpr.Module.Form");

dojo.declare("phpr.Module.Form", phpr.Core.Form, {
    setPermissions:function (data) {
        this._writePermissions = true;
        this._deletePermissions = false;
        this._accessPermissions = true;
    },	
});