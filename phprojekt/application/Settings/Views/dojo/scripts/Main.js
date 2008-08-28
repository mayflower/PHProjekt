dojo.provide("phpr.Settings.Main");

dojo.declare("phpr.Settings.Main", phpr.Default.Main, {
    constructor:function() {
        this.module = "Settings";
        this.loadFunctions(this.module);

        this.gridWidget = phpr.Settings.Grid;
		this.formWidget = phpr.Settings.Form;
		this.treeWidget = phpr.Settings.Tree;
    },

    reload:function() {
        phpr.module = this.module;
        phpr.destroyWidgets("bottomContent");
        phpr.destroyWidgets("submitButton");
        phpr.destroyWidgets("deleteButton");
        phpr.destroySimpleWidget("exportGrid");
        phpr.destroySimpleWidget("saveChanges");
        phpr.destroySimpleWidget("gridNode");
        this.render(["phpr.Settings.template", "mainContent.html"],dojo.byId('centerMainContent') ,{webpath:phpr.webpath, currentModule:phpr.module});
        this.setSubmoduleNavigation();
        this.hideSuggest();
        this.setSearchForm();
        this.tree     = new this.treeWidget(this);
        var updateUrl = phpr.webpath + 'index.php/Core/user/jsonSaveMultipleSetting';
        this.grid     = new this.gridWidget(updateUrl, this, phpr.currentProjectId);
    },
});