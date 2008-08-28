dojo.provide("phpr.Groups.Main");

dojo.declare("phpr.Groups.Main", phpr.Core.Main, {
	 constructor: function(){
	 	this.module = "Groups";
	 	this.loadFunctions(this.module);

		this.gridWidget = phpr.Groups.Grid;
		this.formWidget = phpr.Groups.Form;
		this.treeWidget = phpr.Groups.Tree;
	 }
});
