dojo.provide("phpr.Default.Grid");
// Other classes, class specific
dojo.require("phpr.grid");
//dojo.require("phpr._EditableGrid");

dojo.require("dojox.grid.DataGrid");
dojo.require("dojo.data.ItemFileWriteStore");

dojo.declare("phpr.Default.Grid", phpr.Component, {
    // summary:
    //    Class for displaying a PHProjekt grid
    // description:
    //    This Class takes care of displaying the list information we receive from our Server in a dojo grid

    constructor: function(/*String*/updateUrl, /*Object*/main, /*Int*/ id) {
        // summary:
        //    render the grid on construction
        // description:
        //    this function receives the list data from the server and renders the corresponding grid
        //this._node  = dojo.byId("gridBox");
        this.main   = main;
        this.id     = id;

        phpr.destroyWidgets("gridBox");

        this.gridLayout = new Array();
        this.gridStore = new phpr.ReadStore({
            url: phpr.webpath+"index.php/"+phpr.module+"/index/jsonList/nodeId/"+id
        });
        this.gridStore.fetch({onComplete: dojo.hitch(this, "onLoaded")});

        // Draw the tags
        phpr.receiveUserTags();
        this.publish("drawTagsBox",[phpr.getUserTags()]);
    },

    onLoaded: function(dataContent, request) {
        // summary:
        //    This function is called when the grid is loaded
        // description:
        //    It takes care of setting the grid headers to the right format, displays the contextmenu
        //    and renders the filter for the grid

        // Data of the grid
        data = {
            items: []
        };
        var content = this.gridStore.getValue(dataContent[1], "data") || Array();
        for (var i = 0; i < content.length; i++) {
            data.items.push(content[i]);
        }
        var store = new dojo.data.ItemFileWriteStore({data: data});

        //first of all render save Button
        //var params = {
        //    baseClass: "positive",
        //    id: "saveChanges",
        //    iconClass: "disk",
        //    alt: "Save",
        //    disabled: true
        //};
        //var saveButton = new dijit.form.Button(params);
        //dojo.byId("buttonRow").appendChild(saveButton.domNode);
        //dojo.connect(dijit.byId("saveChanges"), "onClick", dojo.hitch(this, "saveChanges"));

        //dojo.connect(this.grid.widget,          "onRowDblClick",   dojo.hitch(this, "onRowClick"));
        //dojo.connect(this.grid.widget,          "onApplyCellEdit", dojo.hitch(this, "onCellEdit"));

        //gridHeaderContextMenu = dijit.byId("headerContext");
        //gridHeaderContextMenu.bindDomNode(this.grid.widget.domNode);

        //this.grid.widget.onCellContextMenu = function(e) {
        //    cellNode = e.cellNode;
        //};

        //this.grid.widget.onHeaderContextMenu = function(e) {
        //    cellNode = e.cellNode;
        //};

        //this.grid.widget.setModel(this.grid.model);

        // Layout of the grus
        var meta = this.gridStore.getValue(dataContent[0], "metadata") || Array();

        // if there is any row, render export Button
        if (meta.length > 0) {
            var params = {
                baseClass: "positive",
                id: "exportGrid",
                iconClass: "export",
                alt: "Export",
                disabled: false
            };
            var exportButton = new dijit.form.Button(params);
            dojo.byId("buttonRow").appendChild(exportButton.domNode);
            dojo.connect(dijit.byId("exportGrid"), "onClick", dojo.hitch(this, "onExport"));
        }

        if (meta.length == 0) {
            dijit.byId("gridBox").setContent(phpr.nls.noresults);
        } else {
            var porcent = (100 / meta.length) + '%';
            this.gridLayout.push({
                name:   "ID",
                field:  "id",
                width:  porcent,
                editable: false,
            });
            for (var i = 0; i < meta.length; i++) {
                switch(meta[i]["type"]) {
                    case'selectbox':
                        var range = meta[i]["range"];
                        var opts  = new Array();
                        var vals  = new Array();
                        var j     = 0;
                        for (j in range){
                            vals.push(range[j]["id"]);
                            opts.push(range[j]["name"]);
                            j++;
                        }
                        this.gridLayout.push({
                            name:     meta[i]["label"],
                            field:    meta[i]["key"],
                            styles:   "text-align:center;",
                            type:     phpr.grid.cells.Select,
                            width:  porcent,
                            options:  opts,
                            values:   vals
                        });
                        break;

                    case'date':
                        this.gridLayout.push({
                            width:      porcent,
                            name:       meta[i]["label"],
                            field:      meta[i]["key"],
                            styles:     "text-align:center;",
        					type:       phpr.grid.cells.DateTextBox,
		          			formatter:  phpr.grid.formatDate,
                            constraint: {formatLength: 'short', selector: "date"}
                        });
                        break;

                    default:
                        this.gridLayout.push({
                            width:  porcent,
                            name:   meta[i]["label"],
                            field:  meta[i]["key"],
                            type: dojox.grid.cells.TextBox
                            });
                        break;
                }
            }

            this.grid = new dojox.grid.DataGrid({
                id: "gridNode",
                store: store,
                structure: [{
                            defaultCell: {
                                editable: true,
                                type: dojox.grid.cells._Widget,
                                styles: 'text-align: left;'
                            },
                            rows: [this.gridLayout]
                }]
            }, document.createElement('div'));

            // Edit on one click
            this.grid.singleClickEdit = true;

            dijit.byId("gridBox").setContent(this.grid.domNode);
            this.grid.startup();
        }
    },

    onSubmitFilter: function() {
        // summary: This function reloads the grid after submitting filters
        var vals   = {};
        var values = this._filterForm.getValues();

        for (var i in values) {
            vals["filter["+i+"]"] = values[i];
        }

        this.grid.model.query = vals;
        this.grid.model.clearData();
        this.grid.model.requestRows(null, null, dojo.hitch(this, function() {
            this.grid.widget.updateRowCount(this.grid.model.getRowCount());
        }));
    },

    onRowClick: function(e) {
        // summary:
        //    This function publishes a "openForm" Topic
        // description:
        //    As soon as a row is clicked the openForm Topic is published
        var rowID=this.grid.model.getDatum(e.rowIndex,0);
        this.publish("openForm", [rowID]);

    },

    onCellEdit: function(inValue, inRowIndex, inFieldIndex) {
        // summary:
        //    This function publishes a "grid.CellEdi" Topic
        // description:
        //    As soon as a Cell in the grid is edited the grid.CellEdit Topic is published
        //    and the cellEdited function is called
        var value = this.grid.model.getDatum(inRowIndex,inFieldIndex);

        this.publish("grid.CellEdit", [value, inRowIndex, inFieldIndex]);
        this.cellEdited(value, inRowIndex, inFieldIndex);
    },

    onExport: function () {
        window.open(phpr.webpath+"index.php/"+phpr.module+"/index/csvList/nodeId/"+this.id);
        return false;
    }
});
