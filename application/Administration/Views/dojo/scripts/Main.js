dojo.provide("phpr.Administration.Main");

dojo.declare("phpr.Administration.Main", phpr.Default.Main, {
    constructor:function() {
		this.module = "Administration";
		this.loadFunctions(this.module);

		this.gridWidget = phpr.Administration.Grid;
		this.formWidget = phpr.Administration.Form;
		this.treeWidget = phpr.Administration.Tree;
	},

    reload:function() {
        // summary:
        //    This function reloads the current module
        // description:
        //    This function initializes a module that might have been called before.
        //    It only reloads those parts of the page which might change during a PHProjekt session

        // important set the global phpr.module to the module which is currently loaded!!!
        phpr.module = this.module;
        phpr.destroyWidgets("bottomContent");
        phpr.destroyWidgets("submitButton");
        phpr.destroyWidgets("deleteButton");
        phpr.destroySimpleWidget("exportGrid");
        phpr.destroySimpleWidget("saveChanges");
        phpr.destroySimpleWidget("gridNode");
        this.render(["phpr.Default.template", "mainContentEmpty.html"],dojo.byId('centerMainContent') ,{webpath:phpr.webpath, currentModule:phpr.module});
        this.setSubmoduleNavigation();
        this.hideSuggest();
        this.setSearchForm();
        this.tree = new this.treeWidget(this);
    },
	
    setSubmoduleNavigation:function(currentModule) {
        // summary:
        //    This function is responsible for displaying the Navigation of the current Module
        // description:
        //    When calling this function, the available Submodules for the current Module
        //    are received from the server and the Navigation is rendered accordingly
		phpr.destroySimpleWidget("newEntry");
        var navigation = '<ul id="nav_main">';
        var activeTab  = false;
        var modules    = new Array();
		
		modules.push({"name":"User", "label": phpr.nls.get("User")});
		modules.push({"name":"Role", "label": phpr.nls.get("Role")});
		modules.push({"name":"Module", "label": phpr.nls.get("Module")});

        for (var i = 0; i < modules.length; i++) {
			var liclass = '';
			var moduleName     = modules[i].name;
			var moduleLabel    = modules[i].label;
			var moduleFunction = modules[i].moduleFunction || "reload";
			if (moduleName == phpr.module && !activeTab) {
				liclass = 'class = active';
				activeTab = true;
			}
			navigation += this.render(["phpr.Default.template", "navigation.html"], null, {
				moduleName: moduleName,
				moduleLabel: moduleLabel,
				liclass: liclass,
				moduleFunction: moduleFunction
			});
		}
        navigation += "</ul>";
        dojo.byId("subModuleNavigation").innerHTML = navigation;
        phpr.initWidgets(dojo.byId("subModuleNavigation"));
    }
});