dojo.provide("phpr.Calendar.Main");

dojo.require("phpr.Default.Main");
dojo.require("phpr.Calendar.Tree");
dojo.require("phpr.Calendar.Grid");
dojo.require("phpr.Calendar.Form");

dojo.declare("phpr.Calendar.Main", phpr.Default.Main, {
    constructor:function() {
		this.module = "Calendar";
		this.loadFunctions(this.module);

		this.gridWidget = phpr.Calendar.Grid;
		this.formWidget = phpr.Calendar.Form;
		this.treeWidget = phpr.Calendar.Tree;
    }
});