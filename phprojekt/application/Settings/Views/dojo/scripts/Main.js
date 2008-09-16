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
    
    openForm:function(/*int*/id, /*String*/module) {
        //summary: this function opens a new Detail View
        if (!dojo.byId('detailsBox')) {
            this.reload();
        }
        this.form = new this.formWidget(this,id,module);
    },
	
    setSubmoduleNavigation:function(currentModule) {
        // summary:
        //    This function is responsible for displaying the Navigation of the current Module
        // description:
        //    When calling this function, the available Submodules for the current Module
        //    are received from the server and the Navigation is rendered accordingly
        phpr.destroySimpleWidget("newEntry");
        dojo.byId("subModuleNavigation").innerHTML = '';
    }
});