dojo.provide("phpr.Administration.Role.Main");

dojo.require("phpr.Administration.Default.Main");
// app specific files
dojo.require("phpr.Administration.Role.Grid");
dojo.require("phpr.Administration.Role.Form");

dojo.declare("phpr.Administration.Role.Main", phpr.Administration.Default.Main, {
	 constructor: function(){
	 	this.module = "Role";
		this.gridWidget = phpr.Administration.Role.Grid;
		this.formWidget = phpr.Administration.Role.Form;
		dojo.subscribe("Administration.Role.load", this, "reload");
	 }
});
