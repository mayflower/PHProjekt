dojo.provide("phpr.Administration.Role.Form");
dojo.provide("phpr.Administration.Role.ReadModule");

dojo.require("phpr.Administration.Default.Form");

dojo.declare("phpr.Administration.Role.Form", phpr.Administration.Default.Form, {

    roleModuleAccessStore: null,

    initData:function() {
		// Get modules
        this.roleModuleAccessStore = new phpr.Store.RoleModuleAccess(this.id);
        this.roleModuleAccessStore.fetch();
    },

    addBasicFields:function() {
        this.formdata += this.render(["phpr.Administration.Role.template", "formAccess.html"], null, {
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