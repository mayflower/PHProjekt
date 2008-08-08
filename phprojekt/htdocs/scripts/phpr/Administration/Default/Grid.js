dojo.provide("phpr.Administration.Default.Grid");

dojo.require("phpr.Default.Grid");

dojo.declare("phpr.Administration.Default.Grid", phpr.Default.Grid, {
    canEdit:function(inRowIndex) {
        return true;
    },

    showTags:function() {
    },
});