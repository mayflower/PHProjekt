dojo.provide("phpr.Module.Grid");

dojo.declare("phpr.Module.Grid", phpr.Core.Grid, {

    canEdit: function(inRowIndex) {
        return false;
    }
});