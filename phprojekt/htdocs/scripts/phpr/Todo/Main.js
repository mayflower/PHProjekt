dojo.provide("phpr.Todo.Main");

dojo.require("phpr.Default.Main");
dojo.require("phpr.Todo.Tree");
dojo.require("phpr.Todo.Grid");
dojo.require("phpr.Todo.Form");

dojo.declare("phpr.Todo.Main", phpr.Default.Main, {
	 constructor: function(webpath){
	 	this.module     = "Todo";
		this.gridWidget = phpr.Todo.Grid;
		this.formWidget = phpr.Todo.Form;
		this.treeWidget = phpr.Todo.Tree;

		// Subscribe to all topics which concern this module
	 	dojo.subscribe("Todo.load", this, "load");
        dojo.subscribe("Todo.changeProject",this, "loadSubElements");
		dojo.subscribe("Todo.reload", this, "reload");
		dojo.subscribe("Todo.openForm",this, "openForm");
		dojo.subscribe("Todo.form.Submitted",this, "submitForm");
        dojo.subscribe("Todo.submitSearchForm", this, "submitSearchForm");
		dojo.subscribe("Todo.showSearchResults", this, "showSearchResults");
        dojo.subscribe("Todo.drawTagsBox", this, "drawTagsBox");
		dojo.subscribe("Todo.showTagsResults", this, "showTagsResults");
		dojo.subscribe("Todo.updateCacheData", this, "updateCacheData");
	 }
});