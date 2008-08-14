dojo.provide("phpr.Settings.Default.Grid");

dojo.require("phpr.Default.Grid");

dojo.declare("phpr.Settings.Default.Grid", phpr.Default.Grid, {
    
    onLoaded:function(dataContent) {
        // summary:
        //    This function is called when the grid is loaded
        // description:
        //    It takes care of setting the grid headers to the right format, displays the contextmenu
        //    and renders the filter for the grid

        // Data of the grid
        this.gridData = {
            items: []
        };
        var content = phpr.DataStore.getData({url: this.url});
        for (var i = 0; i < content.length; i++) {
            this.gridData.items.push(content[i]);
        }
        store = new dojo.data.ItemFileWriteStore({data: this.gridData});

        // Render save Button
        if (!dijit.byId("saveChanges")) {
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

        // Layout of the grid
        var meta = phpr.DataStore.getMetaData({url: this.url});

        if (meta.length == 0) {
            this._node.setContent(phpr.nls.noresults);
        } else {
            this.setGridLayout(meta);
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

            this.setClickEdit();

            this._node.setContent(this.grid.domNode);
            this.grid.startup();

            dojo.connect(this.grid,"onCellClick",dojo.hitch(this,"showForm"));
            dojo.connect(this.grid,"onApplyCellEdit",dojo.hitch(this,"cellEdited"));
            dojo.connect(this.grid,"onStartEdit",dojo.hitch(this,"checkCanEdit"));
        }
    },
    canEdit:function(inRowIndex) {
        return true;
    },

    showTags:function() {
    },

    setUrl:function() {
        this.url = phpr.webpath+"index.php/User/index/jsonGetSettingList/nodeId/" + this.id;
    },

    useIdInGrid: function() {
        return false;
    },
});