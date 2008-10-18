dojo.provide("phpr.Module.Main");

dojo.declare("phpr.Module.Main", phpr.Core.Main, {
     constructor: function(){
        this.module = "Module";
        this.loadFunctions(this.module);

        this.gridWidget = phpr.Module.Grid;
        this.formWidget = phpr.Module.Form;
        this.treeWidget = phpr.Module.Tree;
     },
     
     customSetSubmoduleNavigation:function() {
        this.setNewEntry();
     }
});
