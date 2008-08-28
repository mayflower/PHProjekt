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

	customSetSubmoduleNavigation:function() {
        phpr.destroySimpleWidget("newEntry");
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
	 },
});