dojo.provide("phpr.Administration.User.Main");

dojo.require("phpr.Administration.Default.Main");
dojo.require("phpr.Administration.User.Grid");
dojo.require("phpr.Administration.User.Form");

dojo.declare("phpr.Administration.User.Main", phpr.Administration.Default.Main, {
	 constructor: function() {
	 	this.module = "User";
	 	this.loadFunctions(this.module);

		this.gridWidget = phpr.Administration.User.Grid;
		this.formWidget = phpr.Administration.User.Form;
	 }
});
