dojo.provide("phpr.Todo.Main");

dojo.declare("phpr.Todo.Main", phpr.Default.Main, {
    constructor:function() {
        this.module = "Todo";
        this.loadFunctions(this.module);

        this.gridWidget = phpr.Todo.Grid;
		this.formWidget = phpr.Todo.Form;
		this.treeWidget = phpr.Todo.Tree;
    }
});