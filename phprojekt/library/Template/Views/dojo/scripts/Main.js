dojo.provide("phpr.##TEMPLATE##.Main");

dojo.declare("phpr.##TEMPLATE##.Main", phpr.Default.Main, {
    constructor:function() {
        this.module = "##TEMPLATE##";
        this.loadFunctions(this.module);

        this.gridWidget = phpr.##TEMPLATE##.Grid;
        this.formWidget = phpr.##TEMPLATE##.Form;
        this.treeWidget = phpr.##TEMPLATE##.Tree;
    }
});
