dojo.provide("phpr.app.default.Grid");

dojo.require("phpr.Component");
// The dijits the template uses
dojo.require("dijit.Menu");
dojo.require("dijit.form.FilteringSelect");
dojo.require("dijit.form.Form");
// Other classes, class specific
dojo.require("phpr.grid");
dojo.require("phpr._EditableGrid");

dojo.declare("phpr.app.default.Grid", [phpr.Component,phpr._EditableGrid], {
    
    _node:null,
	module:'Project',
	gridLayout: new Array(),
	
    constructor:function(updateUrl,main,id,module) {
		
        this._node = dojo.byId("gridBox");
		this.module = module;
		this.main = main;
		this.id = id;
		if (dijit.byId(this._node)) {
			phpr.destroyWidgets(this._node);
		}
		if (dijit.byId("submenu1")) {
			phpr.destroyWidgets("submenu1");
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
        this.grid.model.requestRows(null,null, dojo.hitch(this, "onLoaded"));
    },		
    onLoaded:function() {
        this.grid.widget = dijit.byId("gridNode");
		dojo.connect(dijit.byId("saveChanges"), "onClick", dojo.hitch(this, "saveChanges"));
		dojo.connect(this.grid.widget, "onRowDblClick", dojo.hitch(this, "onRowClick"));
		dojo.connect(this.grid.widget, "onApplyCellEdit", dojo.hitch(this, "onCellEdit"));
		window["gridHeaderContextMenu"] = dijit.byId("submenu1");
        gridHeaderContextMenu.bindDomNode(this.grid.widget.domNode);
		this.grid.widget.onCellContextMenu = function(e) {
				cellNode = e.cellNode;
			};
		this.grid.widget.onHeaderContextMenu = function(e) {
				cellNode = e.cellNode;
			};
		this.grid.widget.setModel(this.grid.model);
		meta= this.grid.widget.model.store.metaData;
		for (var i = 0; i < meta.length; i++) {
			switch(meta[i]["type"]){
				case'selectbox':
				var range = meta[i]["range"];
				var opts =new Array();
				var vals  =new Array();
				var j=0;
				for (j in range){
					opts.push(range[j])
					vals.push(j);
					j++;
				}
				this.gridLayout.push({
						name: meta[i]["label"],
						field: meta[i]["label"],
						styles: "text-align:right;",
						width:"auto",
						editor: dojox.grid.editors.Select,
						options: opts,
						values: vals
					});
					break;
				case'date':
					this.gridLayout.push({
						name: meta[i]["label"],
						field: meta[i]["label"],
						styles: "text-align:right;",
						width:"auto",
						formatter: phpr.grid.formatDate,
						editor: dojox.grid.editors.DateTextBox
					});
				break;
				default:
					this.gridLayout.push({
						name: meta[i]["label"],
						field: meta[i]["label"],
						styles: "text-align:right;",
						width:"auto",
						editor: dojox.grid.editors.Input
						});
				break;
			}
		}
		var gridStructure = [
        	{
				noscroll: true,
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

	onRowClick: function(e){
		var rowID=this.grid.model.getDatum(e.rowIndex,0);
		dojo.publish("grid.RowClick",[rowID,this.module]); 
		
	},
	onCellEdit: function(inValue, inRowIndex, inFieldIndex){
		var value=this.grid.model.getDatum(inRowIndex,inFieldIndex);
		dojo.publish("grid.CellEdit",[value, inRowIndex, inFieldIndex]); 
		this.cellEdited(value, inRowIndex, inFieldIndex);
		
	}
	
});