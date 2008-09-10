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
        	accessModuleText: phpr.nls.get('accessModule'),
            accessReadText: phpr.nls.get('accessRead'),
            accessWriteText: phpr.nls.get('accessWrite'),
            accessCreateText: phpr.nls.get('accessCreate'),
            accessAdminText: phpr.nls.get('accessAdmin'),
            labelfor: phpr.nls.get('accessAccess'),
            label: phpr.nls.get('accessAccess'),
			modules: this.roleModuleAccessStore.getList()
        });
    },

    updateData:function() {
        phpr.DataStore.deleteData({url: this._url});
        this.roleModuleAccessStore.update();
    }
});