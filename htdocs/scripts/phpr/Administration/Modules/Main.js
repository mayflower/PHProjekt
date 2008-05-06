dojo.provide("phpr.Administration.Modules.Main");

dojo.require("phpr.Administration.Default.Main");
// app specific files
dojo.require("phpr.Administration.Modules.Grid");
dojo.require("phpr.Administration.Modules.Form");

dojo.declare("phpr.Administration.Modules.Main", phpr.Administration.Default.Main, {
	 constructor: function(){
	 	this.module = "Modules";
		this.gridWidget = phpr.Administration.Modules.Grid;
		dojo.subscribe("Administration.Modules.load", this, "reload");
	 }
});
