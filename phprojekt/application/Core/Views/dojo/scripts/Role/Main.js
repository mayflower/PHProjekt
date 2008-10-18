dojo.provide("phpr.Role.Main");

dojo.declare("phpr.Role.Main", phpr.Core.Main, {
    constructor: function() {
        this.module = "Role";
        this.loadFunctions(this.module);

        this.gridWidget = phpr.Role.Grid;
        this.formWidget = phpr.Role.Form;
        this.treeWidget = phpr.Role.Tree;
    },
     
     customSetSubmoduleNavigation:function() {
        this.setNewEntry();
     }
});