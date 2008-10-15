dojo.provide("phpr.Default.Grid");

dojo.declare("phpr.Default.Grid", phpr.Component, {
    // summary:
    //    Class for displaying a PHProjekt grid
    // description:
    //    This Class takes care of displaying the list information we receive from our Server in a dojo grid
    main:          null,
    id:            0,
    updateUrl:     null,
    _newRowValues: new Array(),
    _oldRowValues: new Array(),
    gridData:      new Array(),
    url:           null,
    _tagUrl:       null,

    constructor:function(/*String*/updateUrl, /*Object*/main, /*Int*/ id) {
        // summary:
        //    render the grid on construction
        // description:
        //    this function receives the list data from the server and renders the corresponding grid
        this.main          = main;
        this.id            = id;
        this.updateUrl     = updateUrl;
        this._newRowValues = {};
        this._oldRowValues = {};
        this.gridData      = {};
        this.url           = null;

        this.setUrl();
        this.setNode();

        phpr.destroySimpleWidget("exportGrid");
        phpr.destroySimpleWidget("saveChanges");
        phpr.destroySimpleWidget("gridNode");

        this.gridLayout = new Array();

        phpr.DataStore.addStore({url: this.url});
        phpr.DataStore.requestData({url: this.url, processData: dojo.hitch(this, "onLoaded")});

        // Draw the tags
        this.showTags();
    },

    setUrl:function() {
        // summary:
        //    Set the url for get the data
        // description:
        //    Set the url for get the data
        this.url = phpr.webpath+"index.php/"+phpr.module+"/index/jsonList/nodeId/" + this.id;
    },

    setNode:function() {
        // summary:
        //    Set the node to put the grid
        // description:
        //    Set the node to put the grid
        this._node = dijit.byId("gridBox");
    },

    showTags:function() {
        // summary:
        //    Draw the tags
        // description:
        //    Draw the tags
        // Get the module tags
        this._tagUrl  = phpr.webpath + 'index.php/Default/Tag/jsonGetTags';
        phpr.DataStore.addStore({url: this._tagUrl});
        phpr.DataStore.requestData({url: this._tagUrl, processData: dojo.hitch(this, function() {
            this.publish("drawTagsBox",[phpr.DataStore.getData({url: this._tagUrl})]);
          })
        }); 
    },

    useIdInGrid: function() {
        // summary:
        //    Draw the ID on the grid
        // description:
        //    Draw the ID on the grid
        return true;
    },

    setGridLayout:function(meta) {
        // summary:
        //    Create the layout using the different field types
        // description:
        //    Create the layout using the different field types
        var porcent = (100 / meta.length) + '%';

        if (this.useIdInGrid()) {
            this.gridLayout.push({
                name:     "ID",
                field:    "id",
                width:    "40px",
                editable: false,
                styles:   "text-decoration:underline; cursor:pointer;"
            });
        }
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
                        values:   vals,
                        editable: meta[i]['readOnly'] ? false : true
                    });
                    break;

                case'date':
                    this.gridLayout.push({
                        width:      porcent,
                        name:       meta[i]["label"],
                        field:      meta[i]["key"],
                        styles:     "text-align:center;",
                        type:       phpr.grid.cells.DateTextBox,
                        //formatter:  phpr.grid.formatDate,
                        promptMessage: 'yyyy-MM-dd',
                        constraint: {formatLength: 'short', selector: "date", datePattern:'yyyy-MM-dd'},
                        editable:   meta[i]['readOnly'] ? false : true
                    });
                    break;

                case'percentage':
                    this.gridLayout.push({
                        width:       porcent,
                        name:        meta[i]["label"],
                        field:       meta[i]["key"],
                        styles:      "text-align:center;",
                        type:        dojox.grid.cells._Widget,
                        widgetClass: "dijit.form.HorizontalSlider",
                        formatter:   phpr.grid.formatPercentage,
                        editable:    meta[i]['readOnly'] ? false : true
                    });
                    break;

                case'time':
                    this.gridLayout.push({
                        width:      porcent,
                        name:       meta[i]["label"],
                        field:      meta[i]["key"],
                        styles:     "text-align:center;",
                        type:       dojox.grid.cells.Input,
                        formatter:  phpr.grid.formatTime,
                        editable:   meta[i]['readOnly'] ? false : true
                    });
                    break;

                default:
                    this.gridLayout.push({
                        width:    porcent,
                        name:     meta[i]["label"],
                        field:    meta[i]["key"],
                        type:     dojox.grid.cells.Input,
                        styles:   "",
                        editable: meta[i]['readOnly'] ? false : true
                    });
                    break;
            }
        }
        this.customGridLayout(meta);
    },


    customGridLayout:function(meta) {    
        // summary:
        //    Custom functions for the layout
        // description:
        //    Custom functions for the layout
    },
    
    setClickEdit:function() {
        // summary:
        //    Set the edit type
        // description:
        //    Set if each field is ediatable with one or two clicks
        this.grid.singleClickEdit = true;
    },

    setExport:function(meta) {
        // summary:
        //    Set the export button
        // description:
        //    If there is any row, render export Button
        if (meta.length > 0) {
            if (!dijit.byId("exportGrid")) {
                var params = {
                    baseClass: "positive",
                    id: "exportGrid",
                    iconClass: "export",
                    alt: "Export",
                    disabled: false
                };
                var exportButton = new dijit.form.Button(params);
                dojo.byId("buttonRow").appendChild(exportButton.domNode);
                dojo.connect(dijit.byId("exportGrid"), "onClick", dojo.hitch(this, "exportData"));
            }
        }
    },

    onLoaded:function(dataContent) {
        // summary:
        //    This function is called when the grid is loaded
        // description:
        //    It takes care of setting the grid headers to the right format, displays the contextmenu
        //    and renders the filter for the grid
        phpr.destroyWidgets("gridNode");
        // Data of the grid
        this.gridData = {
            items: []
        };
        var content = phpr.DataStore.getData({url: this.url});
        for (var i = 0; i < content.length; i++) {
            this.gridData.items.push(content[i]);
        }
        store = new dojo.data.ItemFileWriteStore({data: this.gridData});

        // Layout of the grid
        var meta = phpr.DataStore.getMetaData({url: this.url});

        // Render save Button
        if (!dijit.byId("saveChanges") && (meta.length >0)) {
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
        }

        this.setExport(meta);

        if (meta.length == 0) {
            this._node.attr('content', phpr.nls.get('There are no entries on this level'));
        } else {
            this.setGridLayout(meta);
            this.grid = new dojox.grid.DataGrid({
                id: "gridNode",
                store: store,
                structure: [{
                            defaultCell: {
                                editable: true,
                                type:     dojox.grid.cells.Input,
                                styles:   'text-align: left;'
                            },
                            rows: [this.gridLayout]
                }]
            }, document.createElement('div'));

            this.setClickEdit();

            this._node.attr('content', this.grid.domNode);
            this.grid.startup();

            dojo.connect(this.grid,"onCellClick",dojo.hitch(this,"showForm"));
            dojo.connect(this.grid,"onApplyCellEdit",dojo.hitch(this,"cellEdited"));
            dojo.connect(this.grid,"onStartEdit",dojo.hitch(this,"checkCanEdit"));
        }
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
        //    If the user can�t edit the item keep the current value for restor it later
        //    We can�t stop the edition, but we can restore the value
        if (!this.canEdit(inRowIndex)) {
            // Keep the old value if the user can�t edit
            if (!this._oldRowValues[inRowIndex]) {
                this._oldRowValues[inRowIndex] = {};
            }
            var item = this.grid.getItem(inRowIndex);
            var value = this.grid.store.getValue(item, inCell.field);
            this._oldRowValues[inRowIndex][inCell.field] = value;
        }
    },

    canEdit:function(inRowIndex) {
        // summary:
        //    Check the access of the item for the user
        // description:
        //    Return true if have write or admin accees
        var writePermissions = this.gridData.items[inRowIndex]["rights"][0]["currentUser"][0]["write"];
        var adminPermissions = this.gridData.items[inRowIndex]["rights"][0]["currentUser"][0]["admin"];
        if (writePermissions == 'false' && adminPermissions == 'false') {
            return false;
        } else {
            return true;
        }
    },

    cellEdited:function(inValue, inRowIndex, inFieldIndex) {
        // summary:
        //    Save the changed values for store
        // description:
        //    Save only the items that was changed, for save it later
        //    If the user can�t edit the item, restore the last value
        if (!this.canEdit(inRowIndex)) {
            var item  = this.grid.getItem(inRowIndex);
            var value = this._oldRowValues[inRowIndex][inFieldIndex];
            this.grid.store.setValue(item,inFieldIndex,value);
            var result = Array();
            result.type = 'error';
            result.message = phpr.nls.get('You do not have access for edit this item');
            new phpr.handleResponse('serverFeedback',result);
        } else {
            if (!this._newRowValues[inRowIndex]) {
                this._newRowValues[inRowIndex] = {};
            }
            this._newRowValues[inRowIndex][inFieldIndex] = inValue;
            this.toggleSaveButton();
        }
    },

    toggleSaveButton:function() {
        // summary:
        //    highlight when button gets avtivated
        // description:
        //    highlight when button gets avtivated
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
        // summary:
        //    Apply the changes into the server
        // description:
        //    Get all the new values into the _newRowValues
        //    and sent it to the server
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
                new phpr.handleResponse('serverFeedback',response);
                if (response.type =='success') {
                    this._newRowValues = {};
                    this._oldRowValues = {};
                    this.publish("updateCacheData");
                    this.publish("reload");
                }
            }),
            error:function(response, ioArgs) {
                new phpr.handleResponse('serverFeedback',response);
            }
        });
    },

    exportData:function() {
        // summary:
        //    Open a new widnows in CVS mode
        // description:
        //    Open a new widnows in CVS mode
        window.open(phpr.webpath+"index.php/"+phpr.module+"/index/csvList/nodeId/"+this.id);
        return false;
    },

    updateData:function() {
        // summary:
        //    Delete the cache for this grid
        // description:
        //    Delete the cache for this grid
        phpr.DataStore.deleteData({url: this.url});
        phpr.DataStore.deleteData({url: this._tagUrl});
    }
});