dojo.provide("phpr.Todo.Main");

dojo.require("phpr.Default.Main");

dojo.declare("phpr.Todo.Main", phpr.Default.Main, {
	 constructor: function(webpath, availmodules){
	 	this.module = "Todo";
		this.gridWidget = phpr.Todo.Grid;
		this.formWidget = phpr.Todo.Form;
		this.treeWidget = phpr.Todo.Tree;
	 	dojo.subscribe("Todo.load", this, "load");
		dojo.subscribe("Todo.reload", this, "reload");
		dojo.subscribe("Todo.grid.RowClick",this, "openForm");
		dojo.subscribe("Todo.form.Submitted",this, "submitForm");
	 }

});
