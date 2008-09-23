dojo.provide("phpr.Role.Form");

dojo.declare("phpr.Role.Form", phpr.Core.Form, {

    roleModuleAccessStore: null,

    initData:function() {
		// Get modules
        this.roleModuleAccessStore = new phpr.Store.RoleModuleAccess(this.id);
        this.roleModuleAccessStore.fetch();
    },

    addBasicFields:function() {
		var roleList = this.roleModuleAccessStore.getList();
		for (var i in roleList) {
			phpr.destroyWidgets("dataAccess[" + roleList[i].id + "]");
			phpr.destroyWidgets("checkReadAccess[" + roleList[i].id + "]");
			phpr.destroyWidgets("checkWriteAccess[" + roleList[i].id + "]");
			phpr.destroyWidgets("checkCreateAccess[" + roleList[i].id + "]");
			phpr.destroyWidgets("checkAdminAccess[" + roleList[i].id + "]");
		}
					
        this.formdata += this.render(["phpr.Core.Role.template", "formAccess.html"], null, {
        	accessModuleText: phpr.nls.get('Module'),
            accessReadText: phpr.nls.get('Read'),
            accessWriteText: phpr.nls.get('Write'),
            accessCreateText: phpr.nls.get('Create'),
            accessAdminText: phpr.nls.get('Admin'),
            labelfor: phpr.nls.get('Access'),
            label: phpr.nls.get('Access'),
			modules: roleList
        });
    },

    updateData:function() {
        phpr.DataStore.deleteData({url: this._url});
        this.roleModuleAccessStore.update();
    }
});