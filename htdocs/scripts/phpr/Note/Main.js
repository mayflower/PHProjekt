dojo.provide("phpr.Note.Main");

dojo.require("phpr.Default.Main");
dojo.require("phpr.Note.Tree");
dojo.require("phpr.Note.Grid");
dojo.require("phpr.Note.Form");

dojo.declare("phpr.Note.Main", phpr.Default.Main, {
    constructor:function() {
        this.module = 'Note';
        this.loadFunctions(this.module);

        this.gridWidget = phpr.Note.Grid;
		this.formWidget = phpr.Note.Form;
		this.treeWidget = phpr.Note.Tree;
	}
});