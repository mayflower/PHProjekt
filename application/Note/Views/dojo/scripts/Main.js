dojo.provide("phpr.Note.Main");

dojo.declare("phpr.Note.Main", phpr.Default.Main, {
    constructor:function() {
        this.module = 'Note';
        this.loadFunctions(this.module);

        this.gridWidget = phpr.Note.Grid;
        this.formWidget = phpr.Note.Form;
        this.treeWidget = phpr.Note.Tree;
    }
});