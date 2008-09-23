dojo.provide("phpr.Core.Main");

dojo.declare("phpr.Core.Main", phpr.Default.Main, {
    constructor:function() {
	 	this.module = "Core";
	 	this.loadFunctions(this.module);

		this.gridWidget = phpr.Core.Grid;
		this.formWidget = phpr.Core.Form;
		this.treeWidget = phpr.Core.Tree;
    },

    reload:function() {
        phpr.module = this.module;
        phpr.destroyWidgets("bottomContent");
        phpr.destroyWidgets("submitButton");
        phpr.destroyWidgets("deleteButton");
        phpr.destroySimpleWidget("exportGrid");
        phpr.destroySimpleWidget("saveChanges");
        phpr.destroySimpleWidget("gridNode");
        this.render(["phpr.Default.template", "mainContent.html"],dojo.byId('centerMainContent') ,{webpath:phpr.webpath, currentModule:phpr.module});
        this.setSubmoduleNavigation();
        this.hideSuggest();
        this.setSearchForm();
        this.tree     = new this.treeWidget(this);
        var updateUrl = phpr.webpath + 'index.php/Core/'+phpr.module.toLowerCase()+'/jsonSaveMultiple/nodeId/' + phpr.currentProjectId;
        this.grid     = new this.gridWidget(updateUrl, this, phpr.currentProjectId);
    },

    setSubmoduleNavigation:function(currentModule) {
        // summary:
        //    This function is responsible for displaying the Navigation of the current Module
        // description:
        //    When calling this function, the available Submodules for the current Module
        //    are received from the server and the Navigation is rendered accordingly
		phpr.destroyWidgets("buttonRow");
        var newEntry   = null;
        var navigation = '<ul id="nav_main">';
        var activeTab  = false;
        var modules    = new Array();

        modules.push({"name":"User", "label": phpr.nls.get("User")});
        modules.push({"name":"Role", "label": phpr.nls.get("Role")});
        modules.push({"name":"Module", "label": phpr.nls.get("Module")});

        for (var i = 0; i < modules.length; i++) {
            var liclass        = '';
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
	
        var params = {
            label:     '',
            id:        'newEntry',
            iconClass: 'add',
            alt:       'Add'
        };
        newEntry = new dijit.form.Button(params);
        dojo.byId("buttonRow").appendChild(newEntry.domNode);
        phpr.initWidgets(dojo.byId("subModuleNavigation"));
        dojo.connect(dijit.byId("newEntry"), "onClick", dojo.hitch(this, "newEntry"));
	 }
});