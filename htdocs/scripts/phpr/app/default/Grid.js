dojo.provide("phpr.app.default.Grid");

dojo.require("phpr.Component");
// The dijits the template uses
dojo.require("dijit.Menu");
dojo.require("dijit.form.FilteringSelect");
dojo.require("dijit.form.Form");
dojo.require("dijit.layout.TabContainer");
// Other classes, class specific
dojo.require("phpr.grid");

dojo.declare("phpr.app.default.Grid", phpr.Component, {
    
    _node:null,
	module:'Project',
	gridLayout: new Array(),
	
    constructor:function(main,id,module) {
		
        this._node = dojo.byId("gridBox");
		this.module = module;
		this.main = main;
		this.id = id;
		if (dijit.byId(this._node)) {
			phpr.destroyWidgets(this._node);
		}
        this.render(["phpr.app.default.template", "grid.html"], this._node);
      
        this.grid = {
            widget:null,
            model:null,
            layout:null
        };
		this.gridLayout=new Array();
		this.gridStore = new phpr.grid.ReadStore({url: this.main.webpath+"index.php/"+this.module+"/index/jsonList/nodeId/"+id});
        this.grid.model = new phpr.grid.Model(null, null, {
            store:this.gridStore
        });
        // I am not 100% sure this is the best way, but it works quite well for now.
        // We trigger the request by hand here, and pass the model to the grid,
        // this way we have received the first set of rows and can render the grid.
        // Ask me (Wolfram) in a while and I know a better way :-).
        // this still has the triggering two request ... for whatever reason :-(
        this.grid.model.requestRows(null,null, dojo.hitch(this, "onLoaded"));

    },		
    onLoaded:function() {
        this.grid.widget = dijit.byId("gridNode");
		dojo.connect(this.grid.widget, "onRowDblClick", dojo.hitch(this, "onRowClick"));
		this.grid.widget.setModel(this.grid.model);
		meta= this.grid.widget.model.store.metaData;
		for (var i = 0; i < meta.length; i++) {
			this.gridLayout.push({
				name: meta[i]["label"],
				field: meta[i]["label"]
			});
		}
		var gridStructure = [
        	{
            	cells: [this.gridLayout
						]
			}
		];
        this.grid.widget.setStructure(gridStructure);
        // Initially we have to update the row count, since we dont know it before we have received the
        // answer from this request, now we know it, so update it, so we also see the
        // numRows available.
        this._filterForm = dijit.byId("gridFilterForm");
        dojo.connect(dijit.byId("gridFilterSubmitButton"),"onClick", dojo.hitch(this, "onSubmitFilter"));

    },

    onSubmitFilter:function() {
        var values = this._filterForm.getValues();
        var vals = {};
        for (var i in values) {
            vals["filter["+i+"]"] = values[i];
        }
        this.grid.model.query = vals;				
		this.grid.model.clearData();
        this.grid.model.requestRows(null, null, dojo.hitch(this, function() {
            this.grid.widget.updateRowCount(this.grid.model.getRowCount());
        }));
    },

    /**Doesnt work
     * 1) i dont know how to connect to onHeaderContextMenu to open the contextmenu
     * 2) using the way below i dont get a ref to the header the context menu opened up on top of
     * 3) connect() must be called AFTER the grid was rendered
     *  tooo much work for now :-(
    */
    connect:function() {
        // Connect the header nodes on double click (like Albrecht wants it) to start filtering
        //dojo.connect(this.grid.widget, "onHeaderContextMenu", dojo.hitch(this, "openHeaderContextMenu"));
        var nodes = dojo.query("th", this.grid.widget.headerNode);
        var wdgt = dijit.byId("gridHeaderContextMenu");
        for (var i=0, l=nodes.length; i<l; i++) {
            wdgt.bindDomNode(nodes[i]);
            //dojo.connect(nodes[i], "oncontextmenu", dojo.hitch(this, "openHeaderContextMenu"));
        }
        dojo.connect(wdgt, "onOpen", dojo.hitch(this, "onOpenContextMenu"));
    },
    
    onOpenContextMenu:function(e) {
        var wdgt = dijit.byId("gridHeaderContextMenu");
        wdgt.show();
    },
	onRowClick: function(e){
		var rowID=this.grid.model.getDatum(e.rowIndex,0);
		dojo.publish("grid.RowClick",[rowID,this.module]); 
		
	}
});