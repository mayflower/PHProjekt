dojo.provide("phpr.Settings.Setting.Main");

dojo.require("phpr.Settings.Default.Main");
dojo.require("phpr.Settings.Setting.Grid");

dojo.declare("phpr.Settings.Setting.Main", phpr.Settings.Default.Main, {
	 constructor: function(){
	 	this.module = "Setting";
	 	this.loadFunctions(this.module);

		this.gridWidget = phpr.Settings.Setting.Grid;
	 }
});
