dojo.provide("phpr.Timecard.Grid");
dojo.require("phpr.Default.Grid");

dojo.declare("phpr.Timecard.Grid", phpr.Default.Grid, {
    // summary:
    //    This class is responsible for rendering the Grid of a Timecard module
    // description:
    //    The Grid for the Timecard module is rendered -  at the moment it is exactly
    //    the same as in the Default module

    setUrl:function() {
        this.url = phpr.webpath+"index.php/"+phpr.module+"/index/jsonList/nodeId/1";
    },

    showTags:function() {
    },

    setGridLayout:function(meta) {
        this.gridLayout.push({
             name:    "Datum",
             field:   "date",
             styles:  "text-align:center;",
             width:   "auto"
        });
        this.gridLayout.push({
            name:    "Anfang",
            field:   "startTime",
            styles:  "text-align:center;",
            width:   "auto"
        });
        this.gridLayout.push({
            name:    "Ende",
            field:   "endTime",
            styles:  "text-align:center;",
            width:   "auto"
        });
    }
});
