dojo.provide("phpr.Project.Main");

dojo.require("phpr.Default.Main");
// app specific files
dojo.require("phpr.Project.Tree");
dojo.require("phpr.Project.Grid");
dojo.require("phpr.Project.Form");

dojo.declare("phpr.Project.Main", phpr.Default.Main, {

	constructor:function(){
		this.module     = 'Project';
		this.gridWidget = phpr.Project.Grid;
		this.formWidget = phpr.Project.Form;
		this.treeWidget = phpr.Project.Tree;

		//subscribe to all topics which concern this module
		dojo.subscribe("Project.load", this, "load");
		dojo.subscribe("Project.changeProjekt",this, "loadSubElements");
		dojo.subscribe("Project.reload", this, "reload");
		dojo.subscribe("Project.openForm", this, "openForm");
		dojo.subscribe("Project.form.Submitted", this, "submitForm");
		dojo.subscribe("Project.submitSearchForm", this, "submitSearchForm");
		dojo.subscribe("Project.showSearchResults", this, "showSearchResults");
		dojo.subscribe("Project.drawTagsBox", this, "drawTagsBox");
		dojo.subscribe("Project.showTagsResults", this, "showTagsResults");
	}
});