dojo.provide("phpr.User.Form");

dojo.declare("phpr.User.Form", phpr.Core.Form, {
    setPermissions:function (data) {
        this._writePermissions = true;
        
        // users can't be deleted
        this._deletePermissions = false;
        this._accessPermissions = true;
    },
	
    updateData: function(){
        phpr.DataStore.deleteData({url: this._url});

		// Delete User Cache
		this.userStore = new phpr.Store.User();
		this.userStore.update();
	}
});