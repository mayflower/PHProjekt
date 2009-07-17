/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 2.1 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

dojo.provide("phpr.Default.Grid");

dojo.declare("phpr.Default.Grid", phpr.Component, {
    // Summary:
    //    Class for displaying a PHProjekt grid
    // Description:
    //    This Class takes care of displaying the list information we receive from our Server in a dojo grid
    main:          null,
    id:            0,
    updateUrl:     null,
    _newRowValues: new Array(),
    _oldRowValues: new Array(),
    gridData:      new Array(),
    url:           null,
    _tagUrl:       null,
    _saveChanges:  null,

    constructor:function(/*String*/updateUrl, /*Object*/main, /*Int*/ id) {
        // Summary:
        //    render the grid on construction
        // Description:
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

        this.gridLayout = new Array();

        phpr.DataStore.addStore({url: this.url});
        phpr.DataStore.requestData({url: this.url, processData: dojo.hitch(this, "onLoaded")});

        // Draw the tags
        this.showTags();
    },

    setUrl:function() {
        // Summary:
        //    Set the url for getting the data
        // Description:
        //    Set the url for getting the data
        this.url = phpr.webpath + "index.php/" + phpr.module + "/index/jsonList/nodeId/" + this.id;
    },

    setNode:function() {
        // Summary:
        //    Set the node to put the grid
        // Description:
        //    Set the node to put the grid
        this._node = dijit.byId("gridBox");
    },

    showTags:function() {
        // Summary:
        //    Draw the tags
        // Description:
        //    Draw the tags
        // Get the module tags
        this._tagUrl  = phpr.webpath + 'index.php/Default/Tag/jsonGetTags';
        phpr.DataStore.addStore({url: this._tagUrl});
        phpr.DataStore.requestData({url: this._tagUrl, processData: dojo.hitch(this, function() {
            this.publish("drawTagsBox", [phpr.DataStore.getData({url: this._tagUrl})]);
          })
        });
    },

    useIdInGrid:function() {
        // Summary:
        //    Draw the ID on the grid
        // Description:
        //    Draw the ID on the grid
        return true;
    },

    setGridLayout:function(meta) {
        // Summary:
        //    Create the layout using the different field types
        // Description:
        //    Create the layout using the different field types
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
                case 'selectbox':
                    var range     = meta[i]["range"];
                    var opts      = new Array();
                    var vals      = new Array();
                    var j         = 0;
                    var maxLength = meta[i]["key"].length;
                    for (j in range){
                        vals.push(range[j]["id"]);
                        opts.push(range[j]["name"]);
                        if (range[j]["name"].length > maxLength) {
                            maxLength = range[j]["name"].length;
                        }
                    }
                    this.gridLayout.push({
                        name:     meta[i]["label"],
                        field:    meta[i]["key"],
                        styles:   "text-align: center;",
                        type:     phpr.grid.cells.Select,
                        width:    (maxLength * 8) + 'px',
                        options:  opts,
                        values:   vals,
                        editable: meta[i]['readOnly'] ? false : true
                    });
                    break;

                case 'date':
                    this.gridLayout.push({
                        width:         '90px',
                        name:          meta[i]["label"],
                        field:         meta[i]["key"],
                        styles:        "text-align: center;",
                        type:          phpr.grid.cells.DateTextBox,
                        promptMessage: 'yyyy-MM-dd',
                        constraint:    {formatLength: 'short', selector: "date", datePattern:'yyyy-MM-dd'},
                        editable:      meta[i]['readOnly'] ? false : true
                    });
                    break;

                case 'percentage':
                    this.gridLayout.push({
                        width:       '90px',
                        name:        meta[i]["label"],
                        field:       meta[i]["key"],
                        styles:      "text-align: center;",
                        type:        phpr.grid.cells.Percentage,
                        editable:    meta[i]['readOnly'] ? false : true
                    });
                    break;

                case 'time':
                    this.gridLayout.push({
                        width:      '60px',
                        name:       meta[i]["label"],
                        field:      meta[i]["key"],
                        styles:     "text-align: center;",
                        type:       phpr.grid.cells.Text,
                        formatter:  phpr.grid.formatTime,
                        editable:   meta[i]['readOnly'] ? false : true
                    });
                    break;

                case 'upload':
                    this.gridLayout.push({
                        width:       'auto',
                        name:        meta[i]["label"],
                        field:       meta[i]["key"],
                        styles:      "text-align: center;",
                        type:        dojox.grid.cells._Widget,
                        formatter:   phpr.grid.formatUpload,
                        editable:    false
                    });
                    break;

                case 'display':
                    var range = meta[i]["range"];
                    // Has it values for translating an Id into a descriptive String?
                    if (range[0] != undefined) {
                        // Yes
                        var opts      = new Array();
                        var vals      = new Array();
                        var j         = 0;
                        var maxLength = meta[i]["key"].length;
                        for (j in range){
                            vals.push(range[j]["id"]);
                            opts.push(range[j]["name"]);
                            if (range[j]["name"].length > maxLength) {
                                maxLength = range[j]["name"].length;
                            }
                        }
                        this.gridLayout.push({
                            name:     meta[i]["label"],
                            field:    meta[i]["key"],
                            styles:   "text-align: center;",
                            type:     phpr.grid.cells.Select,
                            width:    (maxLength * 8) + 'px',
                            options:  opts,
                            values:   vals,
                            editable: false
                        });
                    } else {
                        // No
                        this.gridLayout.push({
                            width:     'auto',
                            name:      meta[i]["label"],
                            field:     meta[i]["key"],
                            type:      phpr.grid.cells.Text,
                            styles:    "text-align: center;",
                            editable:  false
                        });
                    }
                    break;

                case 'text':
                    this.gridLayout.push({
                        width:     'auto',
                        name:      meta[i]["label"],
                        field:     meta[i]["key"],
                        type:      phpr.grid.cells.Text,
                        styles:    "",
                        editable:  meta[i]['readOnly'] ? false : true
                    });
                    break;

                case 'textarea':
                    this.gridLayout.push({
                        width:     'auto',
                        name:      meta[i]["label"],
                        field:     meta[i]["key"],
                        type:      phpr.grid.cells.Textarea,
                        styles:    "",
                        editable:  false
                    });
                    break;

                default:
                    this.gridLayout.push({
                        width:     'auto',
                        name:      meta[i]["label"],
                        field:     meta[i]["key"],
                        type:      phpr.grid.cells.Text,
                        styles:    "",
                        editable:  meta[i]['readOnly'] ? false : true
                    });
                    break;
            }
        }
        this.customGridLayout(meta);
    },

    customGridLayout:function(meta) {
        // Summary:
        //    Custom functions for the layout
        // Description:
        //    Custom functions for the layout
    },

    setClickEdit:function() {
        // Summary:
        //    Set the edit type
        // Description:
        //    Set if each field is ediatable with one or two clicks
        this.grid.singleClickEdit = true;
    },

    setExportButton:function(meta) {
        // Summary:
        //    Set the export button
        // Description:
        //    If there is any row, render export Button
        if (meta.length > 0) {
            var params = {
                label:     phpr.nls.get('Export all items to a CSV file'),
                showLabel: false,
                baseClass: "positive",
                iconClass: "export",
                disabled:  false
            };
            var exportButton = new dijit.form.Button(params);
            dojo.byId("buttonRow").appendChild(exportButton.domNode);
            dojo.connect(exportButton, "onClick", dojo.hitch(this, "exportData"));
        }
    },

    setSaveChangesButton:function(meta) {
        // Summary:
        //    Set the Save changes button
        // Description:
        //    If there is any row, render Save changes button
        if (meta.length > 0) {
            var params = {
                label:     phpr.nls.get('Save changes made to the grid through in-place editing'),
                showLabel: false,
                baseClass: "positive",
                iconClass: "disk",
                disabled:  true
            };
            this._saveChanges = new dijit.form.Button(params);
            dojo.byId("buttonRow").appendChild(this._saveChanges.domNode);
            dojo.connect(this._saveChanges, "onClick", dojo.hitch(this, "saveChanges"));
        }
    },

    onLoaded:function(dataContent) {
        // Summary:
        //    This function is called when the grid is loaded
        // Description:
        //    It takes care of setting the grid headers to the right format, displays the contextmenu
        //    and renders the filter for the grid
        // Layout of the grid
        var meta = phpr.DataStore.getMetaData({url: this.url});

        // Data of the grid
        this.gridData = {
            items: []
        };
        var content = dojo.clone(phpr.DataStore.getData({url: this.url}));
        for (var i in content) {
            this.gridData.items.push(content[i]);
        }
        store = new dojo.data.ItemFileWriteStore({data: this.gridData});

        // Render save Button
        this.setSaveChangesButton(meta);

        // Render export Button
        this.setExportButton(meta);

        if (meta.length == 0) {
            this._node.attr('content', phpr.drawEmptyMessage('There are no entries on this level'));
        } else {
            this.setGridLayout(meta);
            this.grid = new dojox.grid.DataGrid({
                store:     store,
                structure: [{
                            defaultCell: {
                                editable: true,
                                type:     phpr.grid.cells.Text,
                                styles:   'text-align: left;'
                            },
                            rows: [this.gridLayout]
                }]
            }, document.createElement('div'));

            this.setClickEdit();

            this._node.attr('content', this.grid.domNode);
            this.grid.startup();
            this.loadGridSorting();
            this.loadGridScroll();

            dojo.connect(this.grid, "onCellClick", dojo.hitch(this, "showForm"));
            dojo.connect(this.grid, "onApplyCellEdit", dojo.hitch(this, "cellEdited"));
            dojo.connect(this.grid, "onStartEdit", dojo.hitch(this, "checkCanEdit"));
            dojo.connect(this.grid, "onHeaderCellClick", this, "saveGridSorting");
            dojo.connect(this.grid.views.views[0].scrollboxNode, "onscroll", this, "saveGridScroll");
        }
    },

    saveGridScroll:function() {
        // Summary:
        //    Stores in cookies the new scroll position for the current grid
        var cookie = 'p6.' + phpr.module + ".grid.scroll";
        dojo.cookie(cookie, this.grid.scrollTop, {expires: 500});
    },

    loadGridScroll:function() {
        // Summary:
        //    Retrieves from cookies the scroll position for the current grid, if there is one
        var scrollTop = dojo.cookie('p6.' + phpr.module + ".grid.scroll");
        if (scrollTop != undefined) {
            this.grid.scrollTop = scrollTop;
        }
    },

    saveGridSorting:function(e) {
        // Summary:
        //    Stores in cookies the new sorting criterion for the current grid
        var sortColumn = this.grid.getSortIndex();
        var sortAsc    = this.grid.getSortAsc();

        var cookie = 'p6.' + phpr.module + ".grid.sortColumn";
        dojo.cookie(cookie, sortColumn, {expires: 500});

        cookie = 'p6.' + phpr.module + ".grid.sortAsc";
        dojo.cookie(cookie, sortAsc, {expires: 500});
    },

    loadGridSorting:function() {
        // Summary:
        //    Retrieves from cookies the sorting criterion for the current grid if any
        var sortColumn = dojo.cookie('p6.' + phpr.module + ".grid.sortColumn");
        var sortAsc    = dojo.cookie('p6.' + phpr.module + ".grid.sortAsc");
        if (sortColumn != undefined && sortAsc != undefined) {
            this.grid.setSortIndex(parseInt(sortColumn), eval(sortAsc));
        }
    },

    showForm:function(e) {
        // Summary:
        //    This function publishes a "openForm" Topic
        // Description:
        //    As soon as a ID cell is clicked the openForm Topic is published
        if (e.cellIndex == 0) {
            var item = this.grid.getItem(e.rowIndex);
            var rowID = this.grid.store.getValue(item, 'id');
            this.main.setUrlHash(phpr.module, rowID);
        }
    },

    checkCanEdit:function(inCell, inRowIndex) {
        // Summary:
        //    Check the access of the item for the user
        // Description:
        //    If the user can't edit the item keep the current value to restore it later
        //    We can't stop the edition, but we can restore the value
        if (!this.canEdit(inRowIndex)) {
            // Keep the old value if the user can't edit
            if (!this._oldRowValues[inRowIndex]) {
                this._oldRowValues[inRowIndex] = {};
            }
            var item  = this.grid.getItem(inRowIndex);
            var value = this.grid.store.getValue(item, inCell.field);
            this._oldRowValues[inRowIndex][inCell.field] = value;
        }
    },

    canEdit:function(inRowIndex) {
        // Summary:
        //    Check the access of the item for the user
        // Description:
        //    Return true if has write or admin accees
        var writePermissions = this.gridData.items[inRowIndex]["rights"][0]["currentUser"][0]["write"];
        var adminPermissions = this.gridData.items[inRowIndex]["rights"][0]["currentUser"][0]["admin"];
        if (writePermissions == 'false' && adminPermissions == 'false') {
            return false;
        } else {
            return true;
        }
    },

    cellEdited:function(inValue, inRowIndex, inFieldIndex) {
        // Summary:
        //    Save the changed values for store
        // Description:
        //    Save only the items that have changed, to save them later
        //    If the user can't edit the item, restore the last value
        if (!this.canEdit(inRowIndex)) {
            var item  = this.grid.getItem(inRowIndex);
            var value = this._oldRowValues[inRowIndex][inFieldIndex];
            this.grid.store.setValue(item, inFieldIndex, value);
            var result     = Array();
            result.type    = 'error';
            result.message = phpr.nls.get('You do not have access to edit this item');
            new phpr.handleResponse('serverFeedback', result);
        } else {
            if (!this._newRowValues[inRowIndex]) {
                this._newRowValues[inRowIndex] = {};
            }
            this._newRowValues[inRowIndex][inFieldIndex] = inValue;
            this.toggleSaveButton();
        }
    },

    toggleSaveButton:function() {
        // Summary:
        //    highlight when button gets activated
        // Description:
        //    highlight when button gets activated
        if (this._saveChanges.disabled == true) {
            dojox.fx.highlight({
                node:     this._saveChanges.id,
                color:    '#ffff99',
                duration: 1600
            }).play();
        }
        // Activate/Deactivate "save changes" buttons.
        this._saveChanges.disabled = false;
        var saveButton = dojo.byId(this._saveChanges.id);
        saveButton.disabled = false;
    },

    saveChanges:function() {
        // Summary:
        //    Apply the changes into the server
        // Description:
        //    Get all the new values into the _newRowValues
        //    and send them to the server
        this.grid.edit.apply();

        // Get all the IDs for the data sets.
        var content = "";
        for (var i in this._newRowValues) {
            var item = this.grid.getItem(i);
            var curId = this.grid.store.getValue(item, 'id');
            for (var j in this._newRowValues[i]) {
                content += '&data[' + encodeURIComponent(curId) + '][' + encodeURIComponent(j) + ']='
                    + encodeURIComponent(this._newRowValues[i][j]);
            }
        }

        // post the content of all changed forms
        dojo.rawXhrPost( {
            url:      this.updateUrl,
            postData: content,
            handleAs: "json",
            load: dojo.hitch(this, function(response, ioArgs) {
                new phpr.handleResponse('serverFeedback', response);
                if (response.type == 'success') {
                    this._newRowValues = {};
                    this._oldRowValues = {};
                    this.publish("updateCacheData");
                    this.publish("reload");
                }
            }),
            error:function(response, ioArgs) {
                phpr.handleError(this.url, 'exception');
            }
        });
    },

    exportData:function() {
        // Summary:
        //    Open a new window in CSV mode
        // Description:
        //    Open a new window in CSV mode
        window.open(phpr.webpath + "index.php/" + phpr.module + "/index/csvList/nodeId/" + this.id);
        return false;
    },

    updateData:function() {
        // Summary:
        //    Delete the cache for this grid
        // Description:
        //    Delete the cache for this grid
        phpr.DataStore.deleteData({url: this.url});
        phpr.DataStore.deleteData({url: this._tagUrl});
    }
});
