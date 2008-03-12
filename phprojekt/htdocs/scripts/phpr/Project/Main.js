dojo.provide("phpr.Project.Main");

dojo.require("phpr.Default.Main");

dojo.declare("phpr.Project.Main", phpr.Default.Main, {
	 constructor: function(webpath){
	 	this.module = "Project";
	 	dojo.subscribe("Project.load", this, "load");
		dojo.subscribe("Project.reload", this, "reload");
		dojo.subscribe("Project.grid.RowClick", this, "openForm");
		dojo.subscribe("Project.tree.nodeClick", this, "loadSubElements");
		dojo.subscribe("Project.form.Submitted", this, "submitForm");

	 },

});
