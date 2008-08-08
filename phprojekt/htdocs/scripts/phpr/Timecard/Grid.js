dojo.provide("phpr.Timecard.Grid");
dojo.require("phpr.Default.Grid");

dojo.declare("phpr.Timecard.Grid", phpr.Default.Grid, {
    // summary:
    //    This class is responsible for rendering the Grid of a Timecard module
    // description:
    //    The Grid for the Timecard module is rendered -  at the moment it is exactly
    //    the same as in the Default module

    setUrl: function(year, month) {
        if (typeof year == "undefined") {
            date = new Date();
            year = date.getFullYear();
        }
        if (typeof month == "undefined") {
            date = new Date();
            month = date.getMonth() + 1;
        }
        this.url = phpr.webpath+"index.php/"+phpr.module+"/index/jsonList/year/" + year + "/month/" + month;
    },

    showTags: function() {
    },

    canEdit: function(inRowIndex) {
        return true;
    },

    useIdInGrid: function () {
        return false;
    }
});
