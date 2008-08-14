dojo.provide("phpr.Administration.Groups.Main");

dojo.require("phpr.Administration.Default.Main");
dojo.require("phpr.Administration.Groups.Grid");
dojo.require("phpr.Administration.Groups.Form");

dojo.declare("phpr.Administration.Groups.Main", phpr.Administration.Default.Main, {
	 constructor: function(){
	 	this.module = "Groups";
	 	this.loadFunctions(this.module);

		this.gridWidget = phpr.Administration.Groups.Grid;
		this.formWidget = phpr.Administration.Groups.Form;
	 }
});
