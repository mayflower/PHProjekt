dojo.provide("phpr.Project.Main");

dojo.require("phpr.Default.Main");
dojo.require("phpr.Project.Tree");
dojo.require("phpr.Project.Grid");
dojo.require("phpr.Project.Form");

dojo.declare("phpr.Project.Main", phpr.Default.Main, {
    constructor:function() {
		this.module = 'Project';
		this.loadFunctions(this.module);

		this.gridWidget = phpr.Project.Grid;
		this.formWidget = phpr.Project.Form;
		this.treeWidget = phpr.Project.Tree;
	},

    updateCacheData:function() {
        this.tree.updateData();
        this.grid.updateData();
        this.form.updateData();
    },
});