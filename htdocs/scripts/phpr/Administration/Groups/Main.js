dojo.provide("phpr.Administration.Groups.Main");

dojo.require("phpr.Administration.Default.Main");
// app specific files
dojo.require("phpr.Administration.Groups.Grid");
dojo.require("phpr.Administration.Groups.Form");

dojo.declare("phpr.Administration.Groups.Main", phpr.Administration.Default.Main, {
	 constructor: function(){
	 	this.module = "Groups";
		this.gridWidget = phpr.Administration.Groups.Grid;
		this.formWidget = phpr.Administration.Groups.Form;
		dojo.subscribe("Administration.Groups.load", this, "reload");
	 }
});
