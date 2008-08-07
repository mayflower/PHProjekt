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

    constructor:function(/*String*/updateUrl, /*Object*/main, /*Int*/ id) {
        // summary:
        //    render the grid on construction
        // description:
        //    this function receives the list data from the server and renders the corresponding grid
        //this._node  = dojo.byId("gridBox");
        this.main          = main;
        this.id            = id;
        this.updateUrl     = updateUrl;
        this._newRowValues = {};
        this._oldRowValues = {};
        this.gridData      = {};

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

    onLoaded:function(dataContent, request) {
        // summary:
        //    This function is called when the grid is loaded
        // description:
        //    It takes care of setting the grid headers to the right format, displays the contextmenu
        //    and renders the filter for the grid

        // Data of the grid
        this.gridData = {
            items: []
        };
        var content = this.gridStore.getValue(dataContent[1], "data") || Array();
        for (var i = 0; i < content.length; i++) {
            this.gridData.items.push(content[i]);
        }
        store = new dojo.data.ItemFileWriteStore({data: this.gridData});

        //first of all render save Button
        var params = {
            baseClass: "positive",
            id: "saveChanges",
            iconClass: "disk",
            alt: "Save",
            disabled: true
        };
        var saveButton = new dijit.form.Button(params);
        dojo.byId("buttonRow").appendChild(saveButton.domNode);
        dojo.connect(dijit.byId("saveChanges"), "onClick", dojo.hitch(this, "saveChanges"));

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
            dojo.connect(dijit.byId("exportGrid"), "onClick", dojo.hitch(this, "export"));
        }

        if (meta.length == 0) {
            dijit.byId("gridBox").setContent(phpr.nls.noresults);
        } else {
            var porcent = (100 / meta.length) + '%';
            this.gridLayout.push({
                name:     "ID",
                field:    "id",
                width:    porcent,
                editable: false,
                styles:   "text-decoration:underline;"
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
                            width:    porcent,
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

            dojo.connect(this.grid,"onCellClick",dojo.hitch(this,"showForm"));
            dojo.connect(this.grid,"onApplyCellEdit",dojo.hitch(this,"cellEdited"));
            dojo.connect(this.grid,"onStartEdit",dojo.hitch(this,"checkCanEdit"));
        }
    },

    onSubmitFilter:function() {
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

    showForm:function(e) {
        // summary:
        //    This function publishes a "openForm" Topic
        // description:
        //    As soon as a ID cell is clicked the openForm Topic is published
        if (e.cellIndex == 0) {
            var item = this.grid.getItem(e.rowIndex);
            var rowID = this.grid.store.getValue(item, 'id');
            this.publish("openForm", [rowID]);
        }
    },

    checkCanEdit:function(inCell, inRowIndex) {
        // summary:
        //    Check the access of the item for the user
        // description:
        //    If the user can´t edit the item keep the current value for restor it later
        //    We can´t stop the edition, but we can restore the value
        var writePermissions = this.gridData.items[inRowIndex]["rights"][0]["currentUser"][0]["write"];
        var adminPermissions = this.gridData.items[inRowIndex]["rights"][0]["currentUser"][0]["admin"];
        if (!this._oldRowValues[inRowIndex]) {
            this._oldRowValues[inRowIndex] = {};
        }
        // Keep the old value if the user can´t edit
        if (writePermissions == 'false' && adminPermissions == 'false') {
            var item = this.grid.getItem(inRowIndex);
            var value = this.grid.store.getValue(item, inCell.field);
            this._oldRowValues[inRowIndex][inCell.field] = value;
        }
    },

    cellEdited:function(inValue, inRowIndex, inFieldIndex) {
        // summary:
        //    Save the changed values for store
        // description:
        //    Save only the items that was changed, for save it later
        //    If the user can´t edit the item, restore the last value
        var writePermissions = this.gridData.items[inRowIndex]["rights"][0]["currentUser"][0]["write"];
        var adminPermissions = this.gridData.items[inRowIndex]["rights"][0]["currentUser"][0]["admin"];
        if (!this._newRowValues[inRowIndex]) {
            this._newRowValues[inRowIndex] = {};
        }
        if (writePermissions == 'false' && adminPermissions == 'false') {
            var item  = this.grid.getItem(inRowIndex);
            var value = this._oldRowValues[inRowIndex][inFieldIndex];
            this.grid.store.setValue(item,inFieldIndex,value);
            var result = Array();
            result.type = 'error';
            result.message = phpr.nls.gridCantEdit;
            new phpr.handleResponse('serverFeedback',result);
        } else {
            this._newRowValues[inRowIndex][inFieldIndex] = inValue;
            this.toggleSaveButton();
        }
    },

    toggleSaveButton:function() {
        // highlight when button gets avtivated
        saveButton = dijit.byId('saveChanges');
        if (saveButton.disabled == true) {
            dojox.fx.highlight({
                node:'saveChanges',
                color:'#ffff99',
                duration:1600
            }).play();
        }
        // Activate/Deactivate "save changes" buttons.
        saveButton.disabled = false;
        saveButton = dojo.byId('saveChanges');
        saveButton.disabled = false;
    },

    saveChanges:function() {
        // Make sure, that an element that is still in edit mode calls "onApplyCellEdit",
        // so we also get the new data into _newRowValues.
        this.grid.edit.apply();

        // Get all the IDs for the data sets.
        var content = "";
        for (var i in this._newRowValues) {
            var item = this.grid.getItem(i);
            var curId = this.grid.store.getValue(item, 'id');
            for (var j in this._newRowValues[i]) {
                content += '&data['+ encodeURIComponent(curId) +']['+encodeURIComponent(j)+']='+encodeURIComponent(this._newRowValues[i][j]);
            }
        }

        //post the content of all changed forms
        dojo.rawXhrPost( {
            url: this.updateUrl,
            postData: content,
            handleAs: "json",
            load: dojo.hitch(this, function(response, ioArgs) {
                this._newRowValues = {};
                this._oldRowValues = {};
                new phpr.handleResponse('serverFeedback',response);
                return response;
                this.publish("reload");
            }),
            error:function(response, ioArgs) {
                new phpr.handleResponse('serverFeedback',response);
            }
        });
    },

    export:function() {
        window.open(phpr.webpath+"index.php/"+phpr.module+"/index/csvList/nodeId/"+this.id);
        return false;
    }
});
