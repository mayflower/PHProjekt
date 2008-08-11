dojo.provide("phpr.Settings.Setting.Main");
dojo.require("phpr.Settings.Default.Main");
// app specific files
dojo.require("phpr.Settings.Setting.Grid");
dojo.require("phpr.Settings.Setting.Form");

dojo.declare("phpr.Settings.Setting.Main", phpr.Settings.Default.Main, {
	 constructor: function(){
	 	this.module = "Setting";
		this.gridWidget = phpr.Settings.Setting.Grid;
		this.formWidget = phpr.Settings.Setting.Form;
		dojo.subscribe("Setting.reload", this, "reload");
		dojo.subscribe("Setting.openForm", this, "openForm");
	 }
});
