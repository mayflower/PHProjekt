dojo.provide("phpr.Settings.Grid");

dojo.declare("phpr.Settings.Grid", phpr.Default.Grid, {
    canEdit:function(inRowIndex) {
        return true;
    },

    showTags:function() {
    },

    setUrl:function() {
        this.url = phpr.webpath+"index.php/Core/user/jsonGetSettingList/nodeId/" + this.id;
    },

    showForm:function(e) {
    },

    useIdInGrid: function() {
        return false;
    },
});