dojo.provide("phpr.Project.Main");

dojo.require("phpr.Default.Main");
dojo.require("phpr.Project.Tree");
dojo.require("phpr.Project.Grid");
dojo.require("phpr.Project.Form");

dojo.declare("phpr.Project.Main", phpr.Default.Main, {
    constructor:function() {
		this.module = 'Project';
		this.loadFunctions(this.module);

        dojo.subscribe("Project.basicData", this, "basicData");

		this.gridWidget = phpr.Project.Grid;
		this.formWidget = phpr.Project.Form;
		this.treeWidget = phpr.Project.Tree;
	},

    loadResult:function(id, module, projectId) {
        phpr.currentProjectId = id;
        this.reload();
        this.openForm(id, module);
    },

    basicData:function() {
        phpr.module = this.module;
        phpr.destroyWidgets("bottomContent");
        phpr.destroyWidgets("submitButton");
        phpr.destroyWidgets("deleteButton");
        phpr.destroySimpleWidget("exportGrid");
        phpr.destroySimpleWidget("saveChanges");
        phpr.destroySimpleWidget("gridNode");
        this.render(["phpr.Project.template", "BasicData.html"], dojo.byId('centerMainContent') ,{webpath:phpr.webpath, currentModule:phpr.module});
        this.setSubmoduleNavigation('BasicData');
        this.hideSuggest();
        this.setSearchForm();
        this.tree = new this.treeWidget(this);
        this.openForm(phpr.currentProjectId, phpr.module);
    },

    updateCacheData:function() {
        if (this.tree) {
            this.tree.updateData();
        }
        if (this.grid) {
            this.grid.updateData();
        }
        if (this.form) {
            this.form.updateData();
        }
    },
});