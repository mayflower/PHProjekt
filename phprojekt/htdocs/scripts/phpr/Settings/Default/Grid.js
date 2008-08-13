dojo.provide("phpr.Settings.Default.Grid");

dojo.require("phpr.Default.Grid");

dojo.declare("phpr.Settings.Default.Grid", phpr.Default.Grid, {
    canEdit:function(inRowIndex) {
        return true;
    },

    showTags:function() {
    },

    setUrl:function() {
        this.url = phpr.webpath+"index.php/User/index/jsonGetSettingList/nodeId/" + this.id;
    },

    useIdInGrid: function() {
        return false;
    },
});