dojo.provide("phpr.Calendar.Main");

dojo.declare("phpr.Calendar.Main", phpr.Default.Main, {
    constructor:function() {
		this.module = "Calendar";
		this.loadFunctions(this.module);

		this.gridWidget = phpr.Calendar.Grid;
		this.formWidget = phpr.Calendar.Form;
		this.treeWidget = phpr.Calendar.Tree;
    }
});