dojo.provide("phpr.User.Main");

dojo.declare("phpr.User.Main", phpr.Core.Main, {
    constructor:function() {
        this.module = "User";
        this.loadFunctions(this.module);

        this.gridWidget = phpr.User.Grid;
        this.formWidget = phpr.User.Form;
        this.treeWidget = phpr.User.Tree;
    },
     
    customSetSubmoduleNavigation:function() {
        this.setNewEntry();
    }
});
