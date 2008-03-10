dojo.provide("phpr.grid");
dojo.provide("phpr.grid.Model");
dojo.provide("phpr.grid.QueryReadStore");

dojo.require("dojox.grid.Grid");
dojo.require("dojox.grid._data.dijitEditors");
dojo.require("dojox.grid._data.model"); // dojox.grid.data.DojoData is in there
dojo.require("dojox.data.QueryReadStore");

phpr.grid.formatDateTime = function(date) {
    if (!date || !String(date).match(/\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}/)) {
        return date;
    }
    var iso = String(date).replace(" ", "T"); // Make it a real date ISO string
    var dateObj = dojo.date.stamp.fromISOString(iso);
    return dojo.date.locale.format(dateObj, {formatLength:'short', selector:'dateTime'});
};

phpr.grid.formatDate = function(date) {
    if (!date || ! String(date).match(/\d{4}-\d{2}-\d{2}/)) {
        return date;
    }
    var iso = String(date).replace(" ", "T"); // Make it a real date ISO string
    var dateObj = dojo.date.stamp.fromISOString(iso);
    return dojo.date.locale.format(dateObj, {formatLength:'medium', selector:'date'});
};

phpr.grid.updateRows = function(gridWidget, rowNumbers) {
    //  summary:
    //      Updates the given rowNumbers in the given grid.
    //
    //  description:
    //      To do so, it requests the data for these rows from the server and
    //      triggers the update of the visual grid row.
    //      It would be great if there was an integrated way for this in the grid
    //      model, but I didnt find it.
    //
    var model = gridWidget.model;
    var ids = [];
    for (var i=0, l=rowNumbers.length; i<l; i++) {
        ids.push(model.data[rowNumbers[i]].id);
    }
    var params = {
        query:{ids:ids},
        onComplete:dojo.hitch(this, function(items, req) {
            for (var i=0, l=items.length; i<l; i++) {
                var item = items[i];
                var id = req.store.getIdentity(item);
                var rowNum = model.getRowByIdentity(id); // Make sure we get the right row number by using the ID!
                model.setRow(item, rowNum);
                gridWidget.updateRow(rowNum);
            }
        })
    };
    model.store.fetch(params);
};

dojo.declare("phpr.grid.Model", dojox.grid.data.DojoData, {
    // Thanks to Maine for the kick start: http://dojotoolkit.org/book/dojo-book-0-9-1-0/part-2-dijit-dojo-widget-library/advanced-editing-and-display/grid-1-0/sortin#comment-9112
    
    // The number of items to load per request.
    // This is also the number of items (rows) initially shown.
    rowsPerPage:20,
    query:{name:"*"},
    clientSort:false,
    metaData:null,
    _numRows:0,
    getRowCount:function() {
        return this._numRows;
    },
    
    requestRows:function(inRowIndex, inCount, onComplete) {
        // creates serverQuery-parameter
        var row  = inRowIndex || 0;
        var params = {
            start: row,
            count: inCount || this.rowsPerPage,
            serverQuery: dojo.mixin(
              { start: row,
                count: inCount || this.rowsPerPage,
                sort:(this._sortColumn || '')
              },
              this.query
            ),
            query: this.query,
            // onBegin: dojo.hitch(this, "beginReturn"),
            //onComplete: dojo.hitch(this, "processRows"),
            onComplete: dojo.hitch(this, function(items, request) {
                if (dojo.isFunction(onComplete)) {
                    onComplete();
                }
                this.processRows(items, request);
            }),
            onBegin:dojo.hitch(this, function(numRows) {
                this._numRows = numRows;
            })
        }
        this.store.fetch(params);
    },
    
    canSort:function() {
        return true;
    },
    
    sort:function(colIndex) {
        // clears old data to force loading of new, then requests new rows
        var name = this.fields.get(Math.abs(colIndex)-1).name;
        if (name) {
            this._sortColumn = (colIndex<0?'-':'')+name;
            // This clears the data and triggers the reload too.
            this.clearData();
        }
    },
    
    setData: function(inData){
        // edited not to reset the store
        this.data = [];
        this.allChange();
    }
});

dojo.declare("phpr.grid.ReadStore", dojox.data.QueryReadStore, {
    // We need the store explicitly here, since we have to pass it to the grid model.
    requestMethod:"post",
    doClientPaging:false,
    
    _filterResponse: function(data){
		this.metaData=data.metadata;
        // We need to pre-process the data before passing them to the QueryReadStore,
        // since the data structure sent form the server does not comply to what
        // the QueryReadStore expects, we just need to extract the data-key.
        ret = {
            numRows:data.numRows,
            items:data.data
        };
        return ret;
    }

});