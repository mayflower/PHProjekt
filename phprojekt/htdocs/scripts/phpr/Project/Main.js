dojo.provide("phpr.Project.Main");

dojo.require("phpr.Default.Main");
// app specific files
dojo.require("phpr.Project.Tree");
dojo.require("phpr.Project.Grid");
dojo.require("phpr.Project.Form");

dojo.declare("phpr.Project.Main", phpr.Default.Main, {
	 constructor: function(webpath){
	 	this.module = "Project";
		this.gridWidget = phpr.Project.Grid;
		this.formWidget = phpr.Project.Form;
		this.treeWidget = phpr.Project.Tree;
	 	dojo.subscribe("Project.load", this, "load");
		dojo.subscribe("Project.reload", this, "reload");
		dojo.subscribe("Project.grid.RowClick", this, "openForm");
		dojo.subscribe("Project.form.Submitted", this, "submitForm");

	 },

});
