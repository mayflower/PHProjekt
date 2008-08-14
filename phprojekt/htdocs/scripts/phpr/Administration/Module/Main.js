dojo.provide("phpr.Administration.Module.Main");

dojo.require("phpr.Administration.Default.Main");
dojo.require("phpr.Administration.Module.Grid");
dojo.require("phpr.Administration.Module.Form");

dojo.declare("phpr.Administration.Module.Main", phpr.Administration.Default.Main, {
	 constructor: function(){
	 	this.module = "Module";
	 	this.loadFunctions(this.module);

		this.gridWidget = phpr.Administration.Module.Grid;
		this.formWidget = phpr.Administration.Module.Form;
	 }
});
