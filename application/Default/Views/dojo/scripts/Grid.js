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
    // summary:
    //    Class for displaying a PHProjekt grid
    // description:
    //    This Class takes care of displaying the list information we receive from our Server in a dojo grid
    main:               null,
    id:                 0,
    updateUrl:          null,
    _newRowValues:      new Array(),
    _oldRowValues:      new Array(),
    gridData:           new Array(),
    url:                null,
    _tagUrl:            null,
    _saveChanges:       null,
    _entitiesConverted: false,

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
        this.url = phpr.webpath + "index.php/" + phpr.module + "/index/jsonList/nodeId/" + this.id;
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
            this.publish("drawTagsBox", [phpr.DataStore.getData({url: this._tagUrl})]);
          })
        });
    },

    useIdInGrid:function() {
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
                case 'selectbox':
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
                        styles:   "text-align: center;",
                        type:     phpr.grid.cells.Select,
                        width:    porcent,
                        options:  opts,
                        values:   vals,
                        editable: meta[i]['readOnly'] ? false : true
                    });
                    break;

                case 'date':
                    this.gridLayout.push({
                        width:         porcent,
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
                        width:       porcent,
                        name:        meta[i]["label"],
                        field:       meta[i]["key"],
                        styles:      "text-align: center;",
                        type:        dojox.grid.cells._Widget,
                        widgetClass: "dijit.form.HorizontalSlider",
                        formatter:   phpr.grid.formatPercentage,
                        editable:    meta[i]['readOnly'] ? false : true
                    });
                    break;

                case 'time':
                    this.gridLayout.push({
                        width:      porcent,
                        name:       meta[i]["label"],
                        field:      meta[i]["key"],
                        styles:     "text-align: center;",
                        type:       dojox.grid.cells.Input,
                        formatter:  phpr.grid.formatTime,
                        editable:   meta[i]['readOnly'] ? false : true
                    });
                    break;

                case 'upload':
                    this.gridLayout.push({
                        width:       porcent,
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
                            styles:   "text-align: center;",
                            type:     phpr.grid.cells.Select,
                            width:    porcent,
                            options:  opts,
                            values:   vals,
                            editable: false
                        });
                    } else {
                        // No
                        this.gridLayout.push({
                            width:     porcent,
                            name:      meta[i]["label"],
                            field:     meta[i]["key"],
                            type:      dojox.grid.cells.Input,
                            styles:    "text-align: center;",
                            formatter: phpr.grid.formatText,
                            editable:  false
                        });
                    }
                    break;

                case 'text':
                    if (!this._entitiesConverted) {
                        this.gridLayout.push({
                            width:     porcent,
                            name:      meta[i]["label"],
                            field:     meta[i]["key"],
                            type:      dojox.grid.cells.Input,
                            styles:    "",
                            formatter: phpr.grid.formatText,
                            editable:  meta[i]['readOnly'] ? false : true
                        });
                        } else {
                        this.gridLayout.push({
                            width:     porcent,
                            name:      meta[i]["label"],
                            field:     meta[i]["key"],
                            type:      dojox.grid.cells.Input,
                            styles:    "",
                            formatter: "",
                            editable:  meta[i]['readOnly'] ? false : true
                        });
                    }
                    break;

                default:
                    this.gridLayout.push({
                        width:     porcent,
                        name:      meta[i]["label"],
                        field:     meta[i]["key"],
                        type:      dojox.grid.cells.Input,
                        styles:    "",
                        formatter: phpr.grid.formatText,
                        editable:  meta[i]['readOnly'] ? false : true
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

    setExportButton:function(meta) {
        // summary:
        //    Set the export button
        // description:
        //    If there is any row, render export Button
        if (meta.length > 0) {
            var params = {
                baseClass: "positive",
                iconClass: "export",
                alt:       "Export",
                disabled:  false
            };
            var exportButton = new dijit.form.Button(params);
            dojo.byId("buttonRow").appendChild(exportButton.domNode);
            dojo.connect(exportButton, "onClick", dojo.hitch(this, "exportData"));
        }
    },

    setSaveChangesButton:function(meta) {
        // summary:
        //    Set the Save changes button
        // description:
        //    If there is any row, render Save changes button
        if (meta.length > 0) {
            var params = {
                baseClass: "positive",
                iconClass: "disk",
                alt:       "Save",
                disabled:  true
            };
            this._saveChanges = new dijit.form.Button(params);
            dojo.byId("buttonRow").appendChild(this._saveChanges.domNode);
            dojo.connect(this._saveChanges, "onClick", dojo.hitch(this, "saveChanges"));
        }
    },

    onLoaded:function(dataContent) {
        // summary:
        //    This function is called when the grid is loaded
        // description:
        //    It takes care of setting the grid headers to the right format, displays the contextmenu
        //    and renders the filter for the grid
        // Layout of the grid
        var meta = phpr.DataStore.getMetaData({url: this.url});

        // Data of the grid
        this.gridData = {
            items: []
        };
        var content = phpr.DataStore.getData({url: this.url});
        this.specialChars2Entities(meta, content);
        for (var i = 0; i < content.length; i++) {
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

            dojo.connect(this.grid, "onCellClick", dojo.hitch(this, "showForm"));
            dojo.connect(this.grid, "onApplyCellEdit", dojo.hitch(this, "cellEdited"));
            dojo.connect(this.grid, "onStartEdit", dojo.hitch(this, "checkCanEdit"));
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
        // summary:
        //    highlight when button gets activated
        // description:
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
                content += '&data[' + encodeURIComponent(curId) + '][' + encodeURIComponent(j) + ']='
                    + encodeURIComponent(this._newRowValues[i][j]);
            }
        }

        //post the content of all changed forms
        dojo.rawXhrPost( {
            url: this.updateUrl,
            postData: content,
            handleAs: "json",
            load: dojo.hitch(this, function(response, ioArgs) {
                new phpr.handleResponse('serverFeedback', response);
                if (response.type =='success') {
                    this._newRowValues = {};
                    this._oldRowValues = {};
                    this.publish("updateCacheData");
                    this.publish("reload");
                }
            }),
            error:function(response, ioArgs) {
                new phpr.handleResponse('serverFeedback', response);
            }
        });
    },

    exportData:function() {
        // summary:
        //    Open a new widnows in CVS mode
        // description:
        //    Open a new widnows in CVS mode
        window.open(phpr.webpath + "index.php/" + phpr.module + "/index/csvList/nodeId/" + this.id);
        return false;
    },

    updateData:function() {
        // summary:
        //    Delete the cache for this grid
        // description:
        //    Delete the cache for this grid
        phpr.DataStore.deleteData({url: this.url});
        phpr.DataStore.deleteData({url: this._tagUrl});
    },

    specialChars2Entities:function(meta, content) {
        // Summary:
        //    Converts the characters that could be misunderstood by the dojo grid into HTML entities.
        // Description
        //    It permits showing the following the '<' '>' special chars, in all their possible combinations 
        for (var i in meta) {
            if (meta[i]['type'] == 'text') {
                field = meta[i]['key'];
                for (var j in content) {
                    content[j][field] = this.htmlEntities(content[j][field]);
                }
            }
        }
    },

    htmlEntities:function(str) {
        // Summary:
        //    Converts the characters '<' and '>' into readable HTML entities.
        // Description:
        //    Example: receives 'This is very <important>' and returns 'This is a &#60;important&#62;'
        var output    = '';
        var character = '';

        for (var i = 0; i < str.length; i++) {
            str = str.toString(); // To avoid a bug
            character = str.charCodeAt(i);
            if (character == 60 || character == 62) {
                output += "&#" + str.charCodeAt(i) + ";";
                this._entitiesConverted = true;
            } else {
                output += str.charAt(i);
            }
        }

        return output;
    }
});
