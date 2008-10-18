dojo.provide("phpr.Project.Main");

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
        this.basicData();
    },

    basicData:function() {
        phpr.module = this.module;
        this.render(["phpr.Project.template", "BasicData.html"], dojo.byId('centerMainContent'));
        this.setSubmoduleNavigation('BasicData');
        this.hideSuggest();
        this.setSearchForm();
        this.tree = new this.treeWidget(this);
        this.openForm(phpr.currentProjectId, phpr.module);
        // Remove delete button
        if (dijit.byId("deleteButton")) {
            dijit.byId("deleteButton").destroy();
        }
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
    }
});