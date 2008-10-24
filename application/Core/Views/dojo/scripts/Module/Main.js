dojo.provide("phpr.Module.Main");

dojo.declare("phpr.Module.Main", phpr.Core.Main, {
     constructor: function(){
        this.module = "Module";
        this.loadFunctions(this.module);

        this.gridWidget = phpr.Module.Grid;
        this.formWidget = phpr.Module.Form;
        this.treeWidget = phpr.Module.Tree;
        
        dojo.subscribe("Module.openDialog", this, "openDialog");
        dojo.subscribe("Module.submitForm", this, "submitForm");
     },
     
     customSetSubmoduleNavigation:function() {
        this.setNewEntry();
     },
     
     openDialog: function() {
         this.form.openDialog();
     },
     
     submitForm: function() {
         this.form.submitForm();
     }
});
