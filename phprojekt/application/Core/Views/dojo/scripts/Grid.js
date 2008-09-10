dojo.provide("phpr.Core.Grid");

dojo.declare("phpr.Core.Grid", phpr.Default.Grid, {

    setUrl:function() {
        this.url = phpr.webpath+"index.php/Core/"+phpr.module.toLowerCase()+"/jsonList";
    },

    canEdit:function(inRowIndex) {
        return true;
    },

    showTags:function() {
    }
});