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
		// we might wanna move the subscribes for the default elements in the default module?
		this.subscribe("load", this, "load");
		this.subscribe("changeProjekt",this, "loadSubElements"); 
		this.subscribe("reload", this, "reload");
		this.subscribe("grid.RowClick", this, "openForm");
		this.subscribe("form.Submitted", this, "submitForm");
	}

});
