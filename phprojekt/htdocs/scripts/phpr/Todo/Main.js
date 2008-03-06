dojo.provide("phpr.Todo.Main");

dojo.require("phpr.Default.Main");

dojo.declare("phpr.Todo.Main", phpr.Default.Main,{
	 constructor: function(webpath, availmodules){
	 	this.module = "Todo";
	 	dojo.subscribe("Todo.load", this, "load");
		dojo.subscribe("Todo.grid.RowClick",this, "openForm");
		dojo.subscribe("Project.tree.nodeClick",this, "loadSubElements");
		dojo.subscribe("Todo.form.Submitted",this, "submitForm");
	 }

});
