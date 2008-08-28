dojo.provide("phpr.Role.Form");

dojo.declare("phpr.Role.Form", phpr.Core.Form, {

    roleModuleAccessStore: null,

    initData:function() {
		// Get modules
        this.roleModuleAccessStore = new phpr.Store.RoleModuleAccess(this.id);
        this.roleModuleAccessStore.fetch();
    },

    addBasicFields:function() {
        this.formdata += this.render(["phpr.Core.Role.template", "formAccess.html"], null, {
        	accessModuleText: phpr.nls.accessModule,
            accessReadText: phpr.nls.accessRead,
            accessWriteText: phpr.nls.accessWrite,
            accessCreateText: phpr.nls.accessCreate,
            accessAdminText: phpr.nls.accessAdmin,
            labelfor: phpr.nls.accessAccess,
            label: phpr.nls.accessAccess,
			modules: this.roleModuleAccessStore.getList(),
        });
    },

    updateData:function() {
        phpr.DataStore.deleteData({url: this._url});
        this.roleModuleAccessStore.update();
    },
});