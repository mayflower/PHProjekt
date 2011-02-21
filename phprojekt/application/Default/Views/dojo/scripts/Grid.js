/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Default
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <gustavo.solt@mayflower.de>
 */

dojo.provide("phpr.Default.Grid");

dojo.declare("phpr.Default.Grid", null, {
    // Summary:
    //    Class for displaying a PHProjekt grid.
    // Description:
    //    The Class takes care of displaying the list information
    //    that receive from the Server in a dojo grid.
    // General
    _id:            0,
    _access:        [],
    _newRowValues:  [],
    _oldRowValues:  [],
    _splitFields:   [],
    _firstExtraCol: null,
    _grid:          null,
    _lastTime:      null,
    _store:         null,
    _active:        false,
    _doubleClick:   false,

    // Cache control
    _lastId: 0,
    _cached: [],

    // Actions
    _comboActions: [],
    _extraColumns: [],

    // Url
    _getActionsUrl: null,
    _tagUrl:        null,
    _updateUrl:     null,

    // Filters
    _filterField:     [],
    _filterRender:    null,

    // Cookies
    _sortAscCookie:    null,
    _sortColumnCookie: null,
    _scrollTopCookie:  null,

    // Constants
    MODE_XHR:        0,
    MODE_WINDOW:     1,
    MODE_CLIENT:     2,
    TARGET_SINGLE:   0,
    TARGET_MULTIPLE: 1,

    constructor:function(module) {
        // Summary:
        //    Construct the grid only one time.
        // Description:
        //    Init general vars and call a private function for constructor
        //    that can be overwritten by other modules.
        this._lastId        = 0;
        this._cached        = [];
        this._comboActions  = [];
        this._extraColumns  = [];
        this._filterField   = [];
        this._getActionsUrl = null;
        this._grid          = null;

        // Set cookies urls
        if (phpr.isGlobalModule(phpr.module)) {
            var getHashForCookie = phpr.module;
        } else {
            var getHashForCookie = phpr.module + '.' + phpr.currentProjectId;
        }
        this._scrollTopCookie  = getHashForCookie + '.grid.scroll';
        this._sortAscCookie    = getHashForCookie + '.grid.sortAsc';
        this._sortColumnCookie = getHashForCookie + '.grid.sortColumn';

        this._constructor(module);
        this._setGetExtraActionsUrl();
        this._setUpdateUrl();
    },

    init:function(id) {
        // Summary:
        //    Init the grid for a new render.
        // Reset vars
        this._id           = id;
        this._newRowValues = [];
        this._oldRowValues = [];
        this._lastTime     = null;
        this._url          = null;
        this._active       = false;
        this._doubleClick  = false;

        // Set the detail url
        this._setUrl();

        // Init the filters
        this._filterRender.init();

        // Get the filters for send in the request to the server
        var filterData = this._filterRender.setFilterQuery();

        phpr.DataStore.addStore({url: this._url});
        phpr.DataStore.requestData({url: this._url, serverQuery: {'filters': filterData},
            processData: dojo.hitch(this, function() {
                phpr.DataStore.addStore({url: this._getActionsUrl});
                phpr.DataStore.requestData({url: this._getActionsUrl, processData: dojo.hitch(this, '_getGridData')});
            })
        });
    },

    updateData:function() {
        // Summary:
        //    Delete the cache for this grid.
        this._cached[this._id] = false;
        phpr.DataStore.deleteData({url: this._url});
        phpr.DataStore.deleteData({url: this._tagUrl});
    },

    destroyLayout:function() {
        // Summary:
        //    Destroy the grid for create it again.
        if (this._grid) {
            this._grid.destroy();
            this._grid = null;
        }
    },

    /************* Public events *************/

    doClick:function(e) {
        // Summary:
        //    Re-write the function for allow single and double clicks with different functions.
        // Description:
        //    On one click, wait 500 milliseconds and process it.
        //    If there is other click meanwhile, is a double click.
        //    We use 300 milliseconds as a difference between one click and the second in a double-click.
        if (window.gridTimeOut) {
            window.clearTimeout(window.gridTimeOut);
        }
        var date = new Date();

        if (this._grid.edit.isEditing()) {
            if (!this._grid.edit.isEditCell(e.rowIndex, e.cellIndex)) {
                // Click outside the current edit widget
                // Just apply the changes, and do not process it as rowClick (open a form)
                this._grid.edit.apply();
                if (dojo.isIE) {
                    this._lastTime = null;
                } else {
                    this._lastTime = date.getMilliseconds();
                }
            } else {
                this._lastTime = null;
            }

            if (dojo.isIE) {
                this._doubleClick = false;
            }

            // Disable edit for checkbox
            if (this._useCheckbox() && e.cellIndex == 0) {
                this._grid.edit.cancel();
            }

            return;
        }

        // Set a new time for wait
        if (null === this._lastTime) {
            this._lastTime = date.getMilliseconds();
            window.gridTimeOut = window.setTimeout(dojo.hitch(this, 'doClick', e), 500);
            return;
        }

        // Check the times between the 2 clicks
        var newTime     = date.getMilliseconds();
        var singleClick = false;
        var doubleClick = false;
        if (newTime > this._lastTime) {
            // Same second
            if ((newTime - this._lastTime) < 300) {
                doubleClick = true;
            } else {
                singleClick = true;
            }
        } else if (newTime <= this._lastTime) {
            // Other second
            if ((newTime + (1000 - this._lastTime)) < 300) {
                doubleClick = true;
            } else {
                singleClick = true;
            }
        }

        this._lastTime = null;
        if (doubleClick) {
            if (!dojo.isIE) {
                // Works for FF
                // Process a double click
                if (e.cellNode) {
                    this._grid.edit.setEditCell(e.cell, e.rowIndex);
                    this._grid.onRowDblClick(e);
                } else {
                    this._grid.onRowDblClick(e);
                }
            }
        } else {
            if (dojo.isIE) {
                if (this._doubleClick) {
                    // Restore value
                    this._doubleClick = false;
                    return;
                }
            }
            // Process a single click
            if (e.cellNode) {
                this._grid.onCellClick(e);
            } else {
                this._grid.onRowClick(e);
            }
        }
    },

    doDblClick:function(e) {
        // Summary:
        //    Empry function for disable the double click, since is processes by the doClick.
        if (dojo.isIE) {
            this._doubleClick = true;
            // Process a double click
            if (e.cellNode) {
                this._grid.edit.setEditCell(e.cell, e.rowIndex);
                this._grid.onRowDblClick(e);
            } else {
                this._grid.onRowDblClick(e);
            }
        }
    },

    exportData:function() {
        // Summary:
        //    Open a new window in CSV mode.
        window.open(phpr.webpath + 'index.php/' + phpr.module + '/index/csvList/nodeId/' + this._id
            + '/csrfToken/' + phpr.csrfToken);

        return false;
    },

    openFilterBox:function() {
        // Summary:
        //    Open the filter box div for the current module.
        dijit.byId('gridFiltersBox-' + this._module).toggle();
    },

    sendFilterRequest:function() {
        // Summary:
        //    Make the request to the server.
        var filterData         = this._filterRender.setFilterQuery();
        this._cached[this._id] = false;

        phpr.DataStore.deleteData({url: this._url});
        phpr.DataStore.addStore({url: this._url});
        phpr.DataStore.requestData({url: this._url, serverQuery: {'filters': filterData},
            processData: dojo.hitch(this, '_getGridData')});
    },

    /************* Private functions *************/

    _constructor:function(module) {
        // Summary:
        //    Construct the grid only one time.
        // Description:
        //    Use this.inherited(arguments) in constructor function produce a dojo error,
        //    so this function can be easy overwritted whithout that problem.

        // phpr.module is the current module and is used for all the URL.
        // this._module can be any string that represent the module and is used for all the widgetIds.
        this._module = module

        // Create a new filter render
        this._filterRender = new phpr.Grid.Filter(this._module);
    },

    _setGetExtraActionsUrl:function() {
        // Summary:
        //    Sets the url where to get the grid actions data from.
        this._getActionsUrl = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonGetExtraActions';
    },

    _setUpdateUrl:function() {
        // Summary:
        //    Sets the url for save the changes.
        this._updateUrl = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonSaveMultiple/nodeId/'
            + phpr.currentProjectId;
    },

    _setUrl:function() {
        // Summary:
        //    Set the url for getting the data.
        this._url = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonList/nodeId/' + this._id;
    },

    _getGridData:function() {
        // Summary:
        //    Renders the grid data according to the database manager settings.
        // Description:
        //    Processes the grid data and render a dojox.grid and the filters.
        // Layout of the grid
        var meta = phpr.DataStore.getMetaData({url: this._url});
        if (meta.length == 0) {
            // Hide grid
            if (this._grid) {
                this._grid.domNode.style.display = 'none';
            }

            // Create empty div
            var emptyNode = dojo.byId('emptyGrid');
            if (!emptyNode) {
                var emptyNode       = document.createElement('div');
                emptyNode.id        = 'emptyGrid';
                emptyNode.className = 'addButtonText';

                var messsageDiv =  document.createElement('div');
                messsageDiv.style.textAlign  = 'center';
                messsageDiv.style.margin     = '10px 10px 10px 10px';
                messsageDiv.style.fontWeight = 'bold';
                messsageDiv.innerHTML        = phpr.nls.get('There are no entries on this level');

                // Create an "ADD" button
                var params = {
                    id:        'gridNewEntry',
                    label:     phpr.nls.get('Add a new item'),
                    showLabel: true,
                    baseClass: 'positive',
                    iconClass: 'add',
                    onClick:   function() { dojo.publish(phpr.module + '.newEntry') }
                };
                var button = new dijit.form.Button(params);

                emptyNode.appendChild(messsageDiv);
                emptyNode.appendChild(button.domNode);
            }

            dojo.place(emptyNode, dojo.byId(this._getNodeId()), 'first');

            var buttonAdd = dojo.byId('newEntry');
            if (buttonAdd || buttonAdd.style.display != 'none') {
                // There is an 'add' button, so the user have create access
                dojo.byId('gridNewEntry').style.display = 'inline';
            } else {
                dojo.byId('gridNewEntry').style.display = 'none';
            }
        } else {
            // Data of the grid
            if (!this._grid) {
                // Hide the empty node
                if (dojo.byId('emptyGrid')) {
                    dojo.place('emptyGrid', 'garbage');
                }

                // Process extra actions data
                this._processActions();

                // Get datetime keys
                this._splitFields = [];
                for (var i = 0; i < meta.length; i++) {
                    if (meta[i]['type'] == 'datetime') {
                        if (!this._splitFields['datetime']) {
                            this._splitFields['datetime'] = [];
                        }
                        this._splitFields['datetime'].push(meta[i]['key']);
                    }
                }

                // Process grid data
                var gridData = this._processData();

                // Create new Store
                var store = new dojo.data.ItemFileWriteStore({
                    data:         gridData,
                    clearOnClose: true
                });

                // Set grid layout
                var gridLayout = this._setGridLayout(meta);

                // Create the grid, one per module
                var type   = this._useCheckbox() ? 'phpr.Grid._View' : 'dojox.grid._View';

                // Discover the module
                if (phpr.module == this._module || 'Administration' == phpr.parentmodule) {
                    var module = phpr.module;
                } else {
                    // Sub-Module
                    var module = this._module;
                }
                this._grid = new dojox.grid.DataGrid({
                    store:     store,
                    structure: [{type:        type,
                                 rows:        gridLayout,
                                 defaultCell: {
                                    editable: true,
                                    type:     phpr.Grid.Cells.Text,
                                    styles:   'text-align: left;'
                                }}],
                    doclick:function(e) {
                        dojo.publish(module + '.gridProxy', ['doClick', e]);
                    },
                    dodblclick:function(e) {
                        dojo.publish(module + '.gridProxy', ['doDblClick', e]);
                    },
                    onRowClick:function(e) {
                    }
                });

                // Set click for edit
                this._setClickEdit();

                // Events for the grid
                dojo.connect(this._grid, 'onCellClick', dojo.hitch(this, '_cellClick'));
                dojo.connect(this._grid, 'onCellMouseOver', dojo.hitch(this, '_showTooltip'));
                dojo.connect(this._grid, 'onCellMouseOut', dojo.hitch(this, '_hideTooltip'));
                dojo.connect(this._grid, 'onStartEdit', dojo.hitch(this, '_startEdit'));
                dojo.connect(this._grid, 'onApplyCellEdit', dojo.hitch(this, '_cellEdited'));
                dojo.connect(this._grid, 'onHeaderCellClick', this, '_saveGridSorting');
                dojo.connect(this._grid.views.views[0].scrollboxNode, 'onscroll', this, '_saveGridScroll');

                // Add a form for use the checkboxs
                if (this._useCheckbox()) {
                    // Create Action table
                    var table = dojo.doc.createElement('table');
                    var row   = table.insertRow(table.rows.length);

                    // Create arrow
                    var cell = row.insertCell(0);
                    dojo.style(cell, {
                        width:         '45px',
                        align:         'center',
                        verticalAlign: 'middle'
                    });
                    var div = dojo.doc.createElement('div');
                    div.id  = 'arrow';
                    cell.appendChild(div);

                    // Create Check/Uncheck all links
                    var cell         = row.insertCell(1);
                    cell.style.align = 'center';

                    var div = document.createElement('div');

                    var linkCheck = document.createElement('a');
                    linkCheck.setAttribute('href', 'javascript:void(0)');
                    dojo.connect(linkCheck, 'onclick', dojo.hitch(this, '_itemsCheck', [true]));
                    linkCheck.innerHTML = phpr.nls.get('Check All');

                    var space = document.createElement('span');
                    space.innerHTML = '&nbsp;/&nbsp;';

                    var linkUncheck = document.createElement('a');
                    linkUncheck.setAttribute('href', 'javascript:void(0)');
                    dojo.connect(linkUncheck, 'onclick', dojo.hitch(this, '_itemsCheck', [false]));
                    linkUncheck.innerHTML = phpr.nls.get('Uncheck All');

                    div.appendChild(linkCheck);
                    div.appendChild(space);
                    div.appendChild(linkUncheck);
                    cell.appendChild(div);

                    // Create Extra action select
                    var cell    = row.insertCell(2);
                    var select  = dojo.doc.createElement('select')
                    select.id   = 'gridComboAction-' + this._module;
                    select.name = 'gridComboAction-' + this._module;
                    select.size = 1,
                    select.style.width = '160px';
                    for (var i in this._comboActions) {
                        var option = dojo.doc.createElement('option')
                        option.value     = this._comboActions[i]['key'];
                        option.className = this._comboActions[i]['class'];
                        option.text      = this._comboActions[i]['label'];
                        select.options.add(option);
                    }
                    cell.appendChild(select)
                    this._grid.views.views[0].gridActions.appendChild(table);
                    dojo.connect(select, 'onchange', dojo.hitch(this, '_doComboAction'));
                }

                // Start the grid
        		dojo.byId(this._getNodeId()).appendChild(this._grid.domNode);
                this._grid.startup();
                this._loadGridSorting();
                this._loadGridScroll();
            } else {
                // Hide the empty node
                if (dojo.byId('emptyGrid')) {
                    dojo.place('emptyGrid', 'garbage');
                    this._grid.domNode.style.display = 'block';
                }
            }

            if (this._id != this._lastId || !this._cached[this._id]) {
                // Update the store if the last project is different than the current one
                // or if the data was updated with "updateData()"
                var gridData = this._processData();

                // Store
                var store = new dojo.data.ItemFileWriteStore({
                    data:         gridData,
                    clearOnClose: true
                });
                this._grid.setStore(store);
            }

            // Set the last id used and _cached to false
            this._lastId           = this._id;
            this._cached[this._id] = true;
        }

        // Show export Button
        this._setExportButton(meta);

        // Filter Button
        this._setFilterButton(meta);

        // Render filters
        this._renderFilters();

        // Draw the tags
        this._showTags();
    },

    _processActions:function() {
        // Summary:
        //    Processes the info of the grid actions and fills the appropriate arrays.
        if (this._comboActions.length == 0 && this._extraColumns.length == 0) {
            var actions = phpr.DataStore.getData({url: this._getActionsUrl});

            this._comboActions[0]          = [];
            this._comboActions[0]['key']   = null;
            this._comboActions[0]['label'] = phpr.nls.get('With selected');
            this._comboActions[0]['class'] = null;

            for (var i = 0; i < actions.length; i ++) {
                var actionData      = [];
                actionData['key']   = actions[i]['action'] + '|' + actions[i]['mode'];
                actionData['label'] = actions[i]['label'];
                actionData['class'] = actions[i]['class'];
                switch (actions[i]['target']) {
                    case this.TARGET_SINGLE:
                    default:
                        var next                 = this._extraColumns.length;
                        this._extraColumns[next] = actionData;
                        break;
                    case this.TARGET_MULTIPLE:
                        var next                 = this._comboActions.length;
                        this._comboActions[next] = actionData;
                        break;
                }
            }
        }
    },

    _processData:function() {
        // Summary:
        //    Set a new data set for this id
        // Description:
        //    Process the data from the server:
        //    Add pencil icon is is needed.
        //    Split dateTime values in date and time.
        //    Add extra columns values.
        //    Keep the access information.
        var gridData = {items: []};
        this._access = [];
        var content  = phpr.clone(phpr.DataStore.getData({url: this._url}));
        for (var i in content) {
            if (this._usePencilForEdit()) {
                content[i]['gridEdit'] = 'editButton || '
                    + phpr.nls.get('Open this item in the form to edit it');
            }

            // Split a value in two values
            for (var index in content[i]) {
                // For datetime values
                if (phpr.inArray(index, this._splitFields['datetime'])) {
                    var key         = index + '_forDate';
                    var value       = content[i][index].substr(0, 10);
                    content[i][key] = value;

                    var key         = index + '_forTime';
                    var value       = content[i][index].substr(11, 5);
                    content[i][key] = value;
                }
            }

            // Extra Columns for current module
            for (var indexCol in this._extraColumns) {
                var key         = this._extraColumns[indexCol]['key'];
                var divClass    = this._extraColumns[indexCol]['class'];
                var label       = this._extraColumns[indexCol]['label'];
                content[i][key] = divClass + ' || ' + phpr.nls.get(label);
            };

            gridData.items.push(content[i]);

            if (content[i]['rights'] && content[i]['rights']['currentUser']) {
                this._access.push({
                    write: content[i]['rights']['currentUser']['write'],
                    admin: content[i]['rights']['currentUser']['admin']
                });
            } else {
                this._access.push({
                    write: false,
                    admin: false
                });
            }
        }

        return gridData;
    },

    _usePencilForEdit:function() {
        // Summary:
        //    Draw the pencil icon for edit the row.
        return false;
    },

    _setGridLayout:function(meta) {
        // Summary:
        //    Create the layout using the different field types and set the _filterField.
        var layout = new phpr.Grid.Layout();

        // Checkbox column
        if (this._useCheckbox()) {
            layout.addCheckField();
        }

        // Pencil column
        if (this._usePencilForEdit()) {
            layout.addEditField();
        }

        // Id column
        if (this._useIdInGrid()) {
            layout.addIdField();
        }

        // Module columns
        layout.addModuleFields(meta);

        if (this._extraColumns.length > 0) {
            this._firstExtraCol = layout.gridLayout.length;
        }

        // Extra Columns for current module
        for (var i in this._extraColumns) {
            layout.addExtraField(this._extraColumns[i]['key']);
        }

        // Return only the values, remove the keys
        var gridLayout    = [];
        this._filterField = [];
        for (var i in layout.gridLayout) {
            gridLayout.push(layout.gridLayout[i]);
            // Set _filterField array
            this._filterField[i] = {
                key:     layout.gridLayout[i].filterKey,
                label:   layout.gridLayout[i].filterLabel,
                type:    layout.gridLayout[i].filterType,
                options: layout.gridLayout[i].filterOptions
            };
        }

        layout = null;

        return this._customGridLayout(gridLayout);
    },

    _useCheckbox:function() {
        // Summary:
        //    Whether to show or not the checkbox in the grid list.
        return true;
    },

    _useIdInGrid:function() {
        // Summary:
        //    Draw the ID on the grid.
        return true;
    },

    _customGridLayout:function(gridLayout) {
        // Summary:
        //    Custom functions for the layout.
        return gridLayout;
    },

    _setClickEdit:function() {
        // Summary:
        //    Set if each field is ediatable with one or two clicks.
        this._grid.singleClickEdit = false;
    },

    _getNodeId:function() {
        // Summary:
        //    Set the node Id where put the grid.
        return 'gridBox-' + this._module;
    },

    _loadGridSorting:function() {
        // Summary:
        //    Retrieves from cookies the sorting criterion for the current grid if any.
        //    Use the hash for identify the cookie
        var sortColumn = dojo.cookie(this._sortColumnCookie);
        var sortAsc    = dojo.cookie(this._sortAscCookie);
        if (sortColumn != undefined && sortAsc != undefined) {
            this._grid.setSortIndex(parseInt(sortColumn), eval(sortAsc));
        }
    },

    _loadGridScroll:function() {
        // Summary:
        //    Retrieves from cookies the scroll position for the current grid, if there is one.
        //    Use the hash for identify the module grid
        var scrollTop = dojo.cookie(this._scrollTopCookie);
        if (scrollTop != undefined) {
            this._grid.views.views[0].scrollboxNode.scrollTop = scrollTop;
        }
    },

    _setExportButton:function(meta) {
        // Summary:
        //    If there is any row, render an export Button.
        if (meta.length > 0) {
            var button = dijit.byId('exportCsvButton');
            if (!button) {
                var params = {
                    id:        'exportCsvButton',
                    label:     phpr.nls.get('Export to CSV'),
                    showLabel: true,
                    baseClass: 'positive',
                    iconClass: 'export',
                    disabled:  false,
                    onClick:   function() {
                        dojo.publish(phpr.module + '.gridProxy', ['exportData']);
                    }
                };
                var button = new dijit.form.Button(params);
                dojo.byId('buttonRow').appendChild(button.domNode);
            } else {
                dojo.style(button.domNode, 'display', 'inline');
            }
        }
    },

    _setFilterButton:function(meta) {
        // Summary:
        //    If there is any row, render a filter Button.
        if (meta.length > 0) {
            var button = dijit.byId('gridFiltersButton');
            if (!button) {
                var params = {
                    id:        'gridFiltersButton',
                    label:     phpr.nls.get('Filters'),
                    showLabel: true,
                    baseClass: 'positive',
                    iconClass: 'filter',
                    disabled:  false,
                    onClick:   function() {
                        dojo.publish(phpr.module + '.gridProxy', ['openFilterBox']);
                    }
                };
                var button = new dijit.form.Button(params);
                dojo.byId('buttonRow').appendChild(button.domNode);
            } else {
                dojo.style(button.domNode, 'display', 'inline');
            }
        }
    },

    _renderFilters:function() {
        // Summary:
        //    Prepare the filter form.
        // Description:
        //    Create a form for manage filters and open the box if there is any.
        var filterBoxWidget = dijit.byId('gridFiltersBox-' + this._module);
        if (filterBoxWidget) {
            if (filterBoxWidget.getChildren().length == 0) {
                filterBoxWidget.titleNode.innerHTML = phpr.nls.get('Filters');
                filterBoxWidget.set('content', this._filterRender.getLayout());
            }

            // Show current filters
            this._filterRender.drawFilters(this._filterField);
        }
    },

    _showTags:function() {
        // Summary:
        //    Draw the tags.
        // Get the module tags
        this._tagUrl = phpr.webpath + 'index.php/Default/Tag/jsonGetTags';
        phpr.DataStore.addStore({url: this._tagUrl});
        phpr.DataStore.requestData({url: this._tagUrl, processData: dojo.hitch(this, function() {
            dojo.publish(phpr.module + '.drawTagsBox', [phpr.DataStore.getData({url: this._tagUrl})]);
        })});
    },

    /************* Private events *************/

    _cellClick:function(e) {
        // Summary:
        //    This function process a click on a 'row'.
        // Description:
        //    As soon as a Pencil icon or an extra action cell is clicked the corresponding action is processed.
        //    If the click is on other cell, just open the form.
        var useCheckBox      = this._useCheckbox();
        var usePencilForEdit = this._usePencilForEdit();
        var index            = e.cellIndex;
        var openForm         = false;
        if ((useCheckBox && usePencilForEdit && index == 1) ||
            (!useCheckBox && usePencilForEdit && index == 0) ||
            (this._firstExtraCol && index >= this._firstExtraCol)) {
            if ((useCheckBox && index == 1) || (!useCheckBox && index == 0)) {
                // Click on the pencil
                openForm = true;
            } else {
                var key    = e['cell']['field'];
                var temp   = key.split('|');
                var action = temp[0];
                var mode   = parseInt(temp[1]);
                var item   = this._grid.getItem(e.rowIndex);
                var rowId  = this._grid.store.getValue(item, 'id');
                // Click on an extra action button
                this._doAction(action, rowId, mode, this.TARGET_SINGLE);
            }
        } else if (useCheckBox && index == 0) {
            // Click on the checkbox, do nothing
        } else {
            // Click on the row
            openForm = true;
        }

        // Open the form
        if (openForm) {
            var item  = this._grid.getItem(e.rowIndex);
            var rowId = this._grid.store.getValue(item, 'id');
            this._getLinkForEdit(rowId);
        }
    },

    _doAction:function(action, ids, mode, target) {
        // Summary:
        //    Performs an specific action with one or more items of the module,
        //    called by an extra grid icon or the combo.
        if (target == this.TARGET_SINGLE) {
            var idUrl = 'id';
        } else if (target == this.TARGET_MULTIPLE) {
            var idUrl = 'ids';
        }

        var actionUrl = this._getDoActionUrl(action, idUrl, ids);

        if (mode == this.MODE_XHR) {
            // Call the requested action with the selected ids and wait for a response
            phpr.send({
                url:       actionUrl,
                onSuccess: dojo.hitch(this, function(data) {
                    new phpr.handleResponse('serverFeedback', data);
                    if (data.type == 'success') {
                        if (target == this.TARGET_MULTIPLE) {
                            dojo.publish(phpr.module + '.updateCacheData');
                            dojo.publish(phpr.module + '.reload');
                        }
                    }
                })
            });
        } else if (mode == this.MODE_WINDOW) {
            // Call the requested action with the selected ids in a new windows
            window.open(actionUrl + '/csrfToken/' + phpr.csrfToken);
        } else if (mode == this.MODE_CLIENT) {
            // Call the requested action with the selected ids in the main
            dojo.publish(phpr.module + '.' + action, [ids]);
        }

        if (this._useCheckbox()) {
            dojo.byId('gridComboAction-' + this._module).selectedIndex = 0;
        }
    },

    _getDoActionUrl:function(action, idUrl, ids) {
        // Summary:
        //    Isolated code for easy customization,
        //    this function returns the URL to be called for the requested action.
        return phpr.webpath + 'index.php/' + phpr.module + '/index/' + action + '/nodeId/' + phpr.currentProjectId
            + '/' + idUrl + '/' + ids;
    },

    _getLinkForEdit:function(id) {
        // Summary:
        //    Return the link for open the form.
        dojo.publish(phpr.module + '.setUrlHash', [phpr.module, id]);
    },

    _showTooltip:function(e) {
        // Summary:
        //    Shows the tooltip.
        // Description:
        //    Uses the dijit function 'showTooltip' to show the tooltip.
        if (!this._grid.edit.isEditing()) {
            var useCheckBox = this._useCheckbox();
            var index       = e.cellIndex;
            if ((!useCheckBox || (useCheckBox && index != 0)) && e.cell.editable) {
                dijit.showTooltip(phpr.nls.get('Double click to edit'), e.cellNode, 'above');
            }
        }
    },

    _hideTooltip:function(e, cellNode) {
        // Summary:
        //    Hides the tooltip.
        // Description:
        //    Uses the dijit function 'hideTooltip' to hide the tooltip.
        if (e) {
            cellNode = e.cellNode;
        }
        dijit.hideTooltip(cellNode);
    },

    _startEdit:function(inCell, inRowIndex) {
        // Summary:
        //    On start the edit process, keep the current value.
        // Description:
        //    Keep the current value to restore or check it later.
        //    We can't stop the edition, but we can restore the value.
        this._hideTooltip(null, inCell.getNode(inRowIndex));
        if (!this._oldRowValues[inRowIndex]) {
            this._oldRowValues[inRowIndex] = {};
        }
        var item  = this._grid.getItem(inRowIndex);
        var value = this._grid.store.getValue(item, inCell.field);
        this._oldRowValues[inRowIndex][inCell.field] = value;
    },

    _cellEdited:function(inValue, inRowIndex, inFieldIndex) {
        // Summary:
        //    Save the new value.
        // Description:
        //    Save only the item that have changed, to call the save later.
        //    If the user can't edit the item, restore the last value.
        // Skip for combobox
        if (inFieldIndex == 'gridComboBox') {
            return;
        }

        if (dojo.isIE) {
            this._doubleClick = false;
        }

        if (!this._canEdit(inRowIndex)) {
            var item  = this._grid.getItem(inRowIndex);
            var value = this._oldRowValues[inRowIndex][inFieldIndex];
            this._grid.store.setValue(item, inFieldIndex, value);
            var result     = Array();
            result.type    = 'error';
            result.message = phpr.nls.get('You do not have access to edit this item');
            new phpr.handleResponse('serverFeedback', result);
        } else {
            if (!this._newRowValues[inRowIndex]) {
                this._newRowValues[inRowIndex] = {};
            }

            if (inFieldIndex.indexOf('_forDate') > 0) {
                // Convert date to datetime
                var item             = this._grid.getItem(inRowIndex);
                var key              = inFieldIndex.replace('_forDate', '');
                inFieldIndexConvined = key + '_forTime';
                inValue              = inValue + ' ' + this._grid.store.getValue(item, inFieldIndexConvined);

                if (inValue != this._oldRowValues[inRowIndex][key]) {
                    this._newRowValues[inRowIndex][key] = inValue;
                }
            } else if (inFieldIndex.indexOf('_forTime') > 0) {
                // Convert time to datetime
                var item             = this._grid.getItem(inRowIndex);
                var key              = inFieldIndex.replace('_forTime', '');
                inFieldIndexConvined = key + '_forDate';
                inValue              = inValue + ' ' + this._grid.store.getValue(item, inFieldIndexConvined);

                if (inValue != this._oldRowValues[inRowIndex][key]) {
                    this._newRowValues[inRowIndex][key] = inValue;
                }
            } else {
                // Normal widgets
                if (inValue != this._oldRowValues[inRowIndex][inFieldIndex]) {
                    this._newRowValues[inRowIndex][inFieldIndex] = inValue;
                }
            }

            this._applyChanges();
        }
    },

    _canEdit:function(inRowIndex) {
        // Summary:
        //    Check the user access on the item.
        // Description:
        //    Return true if has write or admin accees
        var writePermissions = this._access[inRowIndex].write;
        var adminPermissions = this._access[inRowIndex].admin;
        if (!writePermissions && !adminPermissions) {
            return false;
        } else {
            return true;
        }
    },

    _applyChanges:function() {
        // Summary:
        //    Call the _saveChanges function.
        // Description:
        //    Wait until the last call is finished.
        if (this._active == true) {
            setTimeout(dojo.hitch(this, '_applyChanges'), 500);
        } else {
            this._active = true;
            this._saveChanges();
        }
    },

    _saveChanges:function() {
        // Summary:
        //    Apply the changes into the server.
        // Description:
        //    Get all the new values into the _newRowValues
        //    and send them to the server.
        // Get all the IDs for the data sets.
        var changed = false;
        var content = [];
        var ids     = [];
        for (var i in this._newRowValues) {
            var item  = this._grid.getItem(i);
            var curId = this._grid.store.getValue(item, 'id');
            for (var j in this._newRowValues[i]) {
                changed = true;
                content['data[' + curId + '][' + j + ']'] = this._newRowValues[i][j];
                ids[i] = j;
            }
        }

        // Post the content of all changes, only if there is any.
        if (changed) {
            phpr.send({
                url:       this._updateUrl,
                content:   content,
                onSuccess: dojo.hitch(this, function(response) {
                    this._active = false;
                    if (response.type == 'error') {
                        new phpr.handleResponse('serverFeedback', response);
                    }
                    if (response.type == 'success') {
                        this._updateAfterSaveChanges();
                    }
                    // Delete the changes that are already saved
                    for (var i in ids) {
                        delete this._newRowValues[i][ids[i]];
                    }
                })
            });
        } else {
            this._active = false;
        }
    },

    _updateAfterSaveChanges:function() {
        // Summary:
        //    Actions after the saveChanges call returns success.
        dojo.publish(phpr.module + '.updateCacheData');
    },

    _saveGridSorting:function(e) {
        // Summary:
        //    Stores in cookies the new sorting criterion for the current grid.
        //    Use the hash for identify the cookie.
        var sortColumn = this._grid.getSortIndex();
        var sortAsc    = this._grid.getSortAsc();

        dojo.cookie(this._sortColumnCookie, sortColumn, {expires: 365});
        dojo.cookie(this._sortAscCookie, sortAsc, {expires: 365});
    },

    _saveGridScroll:function() {
        // Summary:
        //    Stores in cookies the new scroll position for the current grid.
        //    Use the hash for identify the cookie.
        dojo.cookie(this._scrollTopCookie, this._grid.scrollTop, {expires: 365});
    },

    _itemsCheck:function(state) {
        // Summary:
        //    Checks or unchecks all items depending on the received boolean.
        if (state == 'false') {
            state = false;
        }
        for (var row = 0; row < this._grid.rowCount; row ++) {
            this._grid.store.setValue(this._grid.getItem(row), 'gridComboBox', state);
        }
    },

    _doComboAction:function() {
        // Summary:
        //    Process the action selected in the combo,
        //    for the checked Ids. Calls 'doAction' function.
        if (this._useCheckbox()) {
            var ids = new Array();
            for (var row = 0; row < this._grid.rowCount; row ++) {
                var checked = this._grid.store.getValue(this._grid.getItem(row), 'gridComboBox');
                if (checked) {
                    ids[ids.length] = this._grid.store.getValue(this._grid.getItem(row), 'id');
                }
            }
            if (ids.length > 0) {
                var idsSend = ids.join(',');
                var select  = dojo.byId('gridComboAction-' + this._module);
                var key     = select.value;
                if (key != null) {
                    var temp   = key.split('|');
                    var action = temp[0];
                    var mode   = parseInt(temp[1]);

                    // Check for multiple rows
                    var actionName = select.children[select.selectedIndex].text;
                    phpr.confirmDialog(dojo.hitch(this, function() {
                        this._doAction(action, idsSend, mode, this.TARGET_MULTIPLE);
                    }), phpr.nls.get('Please confirm implement') + ' "' + actionName + '"<br />(' + ids.length + ' '
                        + phpr.nls.get('rows selected') + ')');
                    select.selectedIndex = 0;
                }
            } else {
                dojo.byId('gridComboAction-' + this._module).selectedIndex = 0;
            }
        }
    }
});
