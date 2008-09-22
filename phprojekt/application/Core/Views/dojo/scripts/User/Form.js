dojo.provide("phpr.User.Form");

dojo.declare("phpr.User.Form", phpr.Core.Form, {
    
    setPermissions:function (data) {
        this._writePermissions = true;
        
        // users can't be deleted
        this._deletePermissions = false;
        this._accessPermissions = true;
    }
    
});