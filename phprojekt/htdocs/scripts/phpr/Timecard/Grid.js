dojo.provide("phpr.Timecard.Grid");
dojo.require("phpr.Default.Grid");

dojo.declare("phpr.Timecard.Grid", phpr.Default.Grid, {
    // summary:
    //    This class is responsible for rendering the Grid of a Timecard module
    // description:
    //    The Grid for the Timecard module is rendered -  at the moment it is exactly
    //    the same as in the Default module
    constructor: function(/*String*/updateUrl, /*Object*/main,/*Int*/ id) {
        // summary:
        //    render the grid on construction
        // description:
        //    this function receives the list data from the server and renders the corresponding grid
        this._node  = dojo.byId("tcSummary");
        this.main   = main;
        this.id     = id;
        this.url    = phpr.webpath+"index.php/"+phpr.module+"/index/jsonList/nodeId/1";
        phpr.destroyWidgets("tcSummary");
        this.render(["phpr.Timecard.template", "grid.html"], this._node, {
            timecardQuickAccessText: phpr.nls.timecardQuickAccess,
        });

        this.grid = {
            widget:null,
            model:null,
            layout:null
        };

        this.gridLayout = new Array();
        this.gridStore  = new phpr.grid.ReadStore({url: this.url});
        this.grid.model = new phpr.grid.Model(null, null, {
            store: this.gridStore
        });

        this.grid.model.requestRows(null,null, dojo.hitch(this, "onLoaded"));

    },

    onLoaded:function() {
        // summary:
        //    This function is called when the grid is loaded
        // description:
        //    It takes care of setting the grid headers to the right format
        this.grid.widget = dijit.byId("tcGridNode");
        //connect doubleClick
        dojo.connect(this.grid.widget, "onRowDblClick",dojo.hitch(this, "onRowClick"));
        this.grid.widget.setModel(this.grid.model);
        this.gridLayout = [];
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
        var gridStructure = [{
                noscroll: true,
                cells: [this.gridLayout]
            }];

        this.grid.widget.setStructure(gridStructure);

    },
        onRowClick: function(e) {
        // summary:
        //    This function changes the date
        // description:
        //    As soon as a row is clicked the changeDate Topic is published
        var date = this.grid.model.getDatum(e.rowIndex,3);
        date = dojo.date.stamp.fromISOString(date);
        dijit.byId('date').setValue(date);
        this.publish("changeDate", [date]);

    }
});
