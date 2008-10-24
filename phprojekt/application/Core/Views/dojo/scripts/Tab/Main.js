dojo.provide("phpr.Tab.Main");

dojo.declare("phpr.Tab.Main", phpr.Core.Main, {
     constructor: function(){
        this.module = "Tab";
        this.loadFunctions(this.module);

        this.gridWidget = phpr.Tab.Grid;
        this.formWidget = phpr.Tab.Form;
        this.treeWidget = phpr.Tab.Tree;
     },
     
     customSetSubmoduleNavigation:function() {
        this.setNewEntry();
     }
});