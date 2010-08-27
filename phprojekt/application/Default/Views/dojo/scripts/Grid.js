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
 * @author     Gustavo Solt <solt@mayflower.de>
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
    getActionsUrl: null,
    _tagUrl:       null,
    _exportButton: null,
    _filterButton: null,
    extraColumns:  new Array(),
    comboActions:  new Array(),
    firstExtraCol: null,
    gridLayout:    new Array(),
    splitFields:   new Array(),
    _lastTime:     null,
    _active:       false,
    _doubleClick:  false,

    // Grid cookies
    _sortColumnCookie: null,
    _sortAscCookie:    null,
    _scrollTopCookie:  null,

    // Filters
    filterField:       new Array(),
    _rules:            new Array(),
    _filterCookie:     null,
    _deleteAllFilters: null,
    _filterSeparator:  ';',
    _filterData:       new Array(),

    // Constants
    MODE_XHR:        0,
    MODE_WINDOW:     1,
    MODE_CLIENT:     2,
    TARGET_SINGLE:   0,
    TARGET_MULTIPLE: 1,

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
        this.getActionsUrl = null;
        this.extraColumns  = new Array();
        this.comboActions  = new Array();

        // Set cookies urls
        if (phpr.isGlobalModule(phpr.module)) {
            var getHashForCookie = phpr.module;
        } else {
            var getHashForCookie = phpr.module + '.' + phpr.currentProjectId;
        }
        this._filterCookie     = getHashForCookie + '.filters';
        this._sortColumnCookie = getHashForCookie + ".grid.sortColumn";
        this._sortAscCookie    = getHashForCookie + ".grid.sortAsc";
        this._scrollTopCookie  = getHashForCookie + ".grid.scroll";

        this.gridLayout  = new Array();
        this.filterField = new Array();

        this.setFilterQuery(this.getFilters());
        this.setGetExtraActionsUrl();
        this.setNode();

        phpr.DataStore.addStore({url: this.url});
        phpr.DataStore.requestData({url: this.url, serverQuery: {'filters[]': this._filterData},
            processData: dojo.hitch(this, function() {
                phpr.DataStore.addStore({url: this.getActionsUrl});
                phpr.DataStore.requestData({url: this.getActionsUrl, processData: dojo.hitch(this, "onLoaded")});
            })
        });
    },

    setUrl:function() {
        // Summary:
        //    Set the url for getting the data
        // Description:
        //    Set the url for getting the data
        this.url = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonList/nodeId/' + this.id;
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

    usePencilForEdit:function() {
        // Summary:
        //    Draw the pencil icon for edit the row
        // Description:
        //    Draw the pencil icon for edit the row
        return false;
    },

    useCheckbox:function() {
        // Summary:
        //    Whether to show or not the checkbox in the grid list
        return true;
    },

    setGridLayout:function(meta) {
        // Summary:
        //    Create the layout using the different field types
        // Description:
        //    Create the layout using the different field types
        //    Also create the filter fields
        this.gridLayout  = new Array();
        this.filterField = new Array();

        // Checkbox column
        if (this.useCheckbox()) {
            this.gridLayout.push({
                name:     " ",
                field:    "gridComboBox",
                width:    "20px",
                type:     dojox.grid.cells.Bool,
                editable: true
            });
        }

        // Pencil column
        if (this.usePencilForEdit()) {
            this.gridLayout.push({
                name:      " ",
                field:     "gridEdit",
                width:     "20px",
                type:      dojox.grid.cells.Cell,
                editable:  false,
                styles:    "vertical-align: middle;",
                formatter: phpr.grid.formatIcon
            });
        }

        // Id column
        if (this.useIdInGrid()) {
            this.gridLayout.push({
                name:     "ID",
                field:    "id",
                width:    "40px",
                type:      dojox.grid.cells.Cell,
                editable: false,
                styles:   "text-align: right;"
            });
            this.filterField.push({
                key:   '_fixedId',
                label: 'ID',
                type:  'text'
            });
        }

        // Module columns
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
                    this.filterField.push({
                        key:     meta[i]["key"],
                        label:   meta[i]["label"],
                        type:    'selectbox',
                        options: range
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
                    this.filterField.push({
                        key:   meta[i]["key"],
                        label: meta[i]["label"],
                        type:  'date'
                    });
                    break;

                case 'datetime':
                    this.gridLayout.push({
                        width:         '90px',
                        name:          meta[i]["label"] + ' (' + phpr.nls.get('Date') + ')',
                        field:         meta[i]["key"] + '_forDate',
                        styles:        "text-align: center;",
                        type:          phpr.grid.cells.DateTextBox,
                        promptMessage: 'yyyy-MM-dd',
                        constraint:    {formatLength: 'short', selector: "date", datePattern:'yyyy-MM-dd'},
                        editable:      meta[i]['readOnly'] ? false : true
                    });
                    this.gridLayout.push({
                        width:    '90px',
                        name:     meta[i]["label"] + ' (' + phpr.nls.get('Hour') + ')',
                        field:    meta[i]["key"] + '_forTime',
                        styles:   "text-align: center;",
                        type:     phpr.grid.cells.Time,
                        editable: meta[i]['readOnly'] ? false : true
                    });
                    this.filterField.push({
                        key:   meta[i]["key"] + '_forDate',
                        label: meta[i]["label"] + ' (' + phpr.nls.get('Date') + ')',
                        type:  'date'
                    });
                    this.filterField.push({
                        key:   meta[i]["key"] + '_forTime',
                        label: meta[i]["label"] + ' (' + phpr.nls.get('Hour') + ')',
                        type:  'time'
                    });
                    break;

                case 'percentage':
                    this.gridLayout.push({
                        width:    '90px',
                        name:     meta[i]["label"],
                        field:    meta[i]["key"],
                        styles:   "text-align: center;",
                        type:     phpr.grid.cells.Percentage,
                        editable: meta[i]['readOnly'] ? false : true
                    });
                    this.filterField.push({
                        key:   meta[i]["key"],
                        label: meta[i]["label"],
                        type:  'text'
                    });
                    break;

                case 'time':
                    this.gridLayout.push({
                        width:    '60px',
                        name:     meta[i]["label"],
                        field:    meta[i]["key"],
                        styles:   "text-align: center;",
                        type:     phpr.grid.cells.Time,
                        editable: meta[i]['readOnly'] ? false : true
                    });
                    this.filterField.push({
                        key:   meta[i]["key"],
                        label: meta[i]["label"],
                        type:  'time'
                    });
                    break;

                case 'upload':
                    this.gridLayout.push({
                        width:     'auto',
                        name:      meta[i]["label"],
                        field:     meta[i]["key"],
                        styles:    "text-align: center;",
                        type:      dojox.grid.cells._Widget,
                        formatter: phpr.grid.formatUpload,
                        editable:  false
                    });
                    this.filterField.push({
                        key:   meta[i]["key"],
                        label: meta[i]["label"],
                        type:  'text'
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
                        this.filterField.push({
                            key:     meta[i]["key"],
                            label:   meta[i]["label"],
                            type:    'selectbox',
                            options: range
                        });
                    } else {
                        // No
                        this.gridLayout.push({
                            width:    'auto',
                            name:     meta[i]["label"],
                            field:    meta[i]["key"],
                            type:     phpr.grid.cells.Text,
                            styles:   "text-align: center;",
                            editable: false
                        });
                        this.filterField.push({
                            key:   meta[i]["key"],
                            label: meta[i]["label"],
                            type: 'text'
                        });
                    }
                    break;

                case 'text':
                    this.gridLayout.push({
                        width:    'auto',
                        name:     meta[i]["label"],
                        field:    meta[i]["key"],
                        type:     phpr.grid.cells.Text,
                        styles:   "",
                        editable: meta[i]['readOnly'] ? false : true
                    });
                    this.filterField.push({
                        key:   meta[i]["key"],
                        label: meta[i]["label"],
                        type:  'text'
                    });
                    break;

                case 'textarea':
                    this.gridLayout.push({
                        width:    'auto',
                        name:     meta[i]["label"],
                        field:    meta[i]["key"],
                        type:     phpr.grid.cells.Textarea,
                        styles:   "",
                        editable: false
                    });
                    this.filterField.push({
                        key:   meta[i]["key"],
                        label: meta[i]["label"],
                        type: 'text'
                    });
                    break;

                case 'rating':
                    var max       = parseInt(meta[i]["range"]["id"]);
                    var opts      = new Array();
                    var vals      = new Array();
                    var maxLength = meta[i]["key"].length;
                    for (var j = 1; j <= max; j++){
                        vals.push(j);
                        opts.push(j);
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
                    this.filterField.push({
                        key:     meta[i]["key"],
                        label:   meta[i]["label"],
                        type:    'rating',
                        max:     max
                    });
                    break;

                default:
                    this.gridLayout.push({
                        width:    'auto',
                        name:     meta[i]["label"],
                        field:    meta[i]["key"],
                        type:     phpr.grid.cells.Text,
                        styles:   "",
                        editable: meta[i]['readOnly'] ? false : true
                    });
                    this.filterField.push({
                        key:   meta[i]["key"],
                        label: meta[i]["label"],
                        type:  'text'
                    });
                    break;
            }
        }

        if (this.extraColumns.length > 0) {
            this.firstExtraCol = this.gridLayout.length;
        }

        // Extra Columns for current module
        for (var i in this.extraColumns) {
            this.gridLayout.push({
                name:      " ",
                field:     this.extraColumns[i]['key'],
                width:     "20px",
                type:      dojox.grid.cells.Cell,
                editable:  false,
                styles:    "vertical-align: middle;",
                formatter: phpr.grid.formatIcon
            });
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
        this.grid.singleClickEdit = false;
    },

    setExportButton:function(meta) {
        // Summary:
        //    Set the export button
        // Description:
        //    If there is any row, render export Button
        if (meta.length > 0 && this._exportButton === null) {
            var params = {
                label:     phpr.nls.get('Export to CSV'),
                showLabel: true,
                baseClass: "positive",
                iconClass: "export",
                disabled:  false
            };
            this._exportButton = new dijit.form.Button(params);
            dojo.byId("buttonRow").appendChild(this._exportButton.domNode);
            dojo.connect(this._exportButton, "onClick", dojo.hitch(this, "exportData"));
        }
    },

    setFilterButton:function(meta) {
        // Summary:
        //    Set the filter button
        // Description:
        //    Render filter Button
        if (meta.length > 0 && this._filterButton === null) {
            var params = {
                label:     phpr.nls.get('Filters'),
                showLabel: true,
                baseClass: "positive",
                iconClass: "filter",
                disabled:  false
            };
            this._filterButton = new dijit.form.Button(params);
            dojo.byId("buttonRow").appendChild(this._filterButton.domNode);            ;
            dojo.connect(this._filterButton, "onClick", dojo.hitch(this, function() {
                dijit.byId('gridFiltersBox').toggle();
            }));
        }
    },

    manageFilters:function() {
        // Summary:
        //    Prepare the filter form
        // Description:
        //    Prepare the filter form for manage filters
        //    and open it if there is any
        if (this._rules.length == 0) {
            this._rules['like']       = phpr.nls.get('Filter_like_rule');
            this._rules['notLike']    = phpr.nls.get('Filter_not_like_rule');
            this._rules['equal']      = phpr.nls.get('Filter_equal_rule');
            this._rules['notEqual']   = phpr.nls.get('Filter_not_equal_rule');
            this._rules['major']      = phpr.nls.get('Filter_major_rule');
            this._rules['majorEqual'] = phpr.nls.get('Filter_major_equal_rule');
            this._rules['minor']      = phpr.nls.get('Filter_minor_rule');
            this._rules['minorEqual'] = phpr.nls.get('Filter_minor_equal_rule');
            this._rules['begins']     = phpr.nls.get('Filter_begins_rule');
            this._rules['ends']       = phpr.nls.get('Filter_ends_rule');
        }

        var filters = this.getFilters();

        if (dojo.byId('gridFiltersBox')) {
            dijit.byId('gridFiltersBox').titleNode.innerHTML = phpr.nls.get('Filters');
            // Closed div
            if (dojo.byId('gridFiltersBox').style.height == '0px') {
                var html = this.render(["phpr.Default.template.filters", "form.html"], null, {
                    module:  phpr.module,
                    andTxt:  phpr.nls.get("Filter_AND"),
                    orTxt:   phpr.nls.get("Filter_OR"),
                    okTxt:   phpr.nls.get("OK")
                });

                dijit.byId('gridFiltersBox').set('content', html);
                this.drawFilters(filters);

                // Only open div if there is any filter
                if (filters.length > 0) {
                    dijit.byId('gridFiltersBox').toggle();
                }
            // Opened div
            } else {
                this.drawFilters(filters);
            }
        }
    },

    changeInputFilter:function(field) {
        // Summary:
        //    Manage filters
        // Description:
        //    Change the rule and value fields depend on the selected field type
        var fieldTemplate = new phpr.Default.Field();
        var rulesOptions  = new Array();

        for (var i in this.filterField) {
            if (this.filterField[i].key == field) {
                switch(this.filterField[i].type) {
                    case 'selectbox':
                        var input = '<select name="filterValue" dojoType="phpr.FilteringSelect" autocomplete="false">';
                        for (var j in this.filterField[i].options) {
                            input += '<option value="' + this.filterField[i].options[j].id + '">'
                                + this.filterField[i].options[j].name + '</option>';
                        }
                        input       += '</select>';
                        rulesOptions = new Array('equal', 'notEqual');
                        break;
                    case 'date':
                        var input = '<input type="text" name="filterValue" dojoType="phpr.DateTextBox" '
                            + 'constraints="{datePattern: \'yyyy-MM-dd\'}" promptMessage="yyyy-MM-dd" />';
                        rulesOptions = new Array('equal', 'notEqual', 'major', 'majorEqual', 'minor', 'minorEqual');
                        break;
                    case 'time':
                        var input = '<input type="text" name="filterValue" dojoType="dijit.form.TimeTextBox" '
                            + 'constraints="{formatLength: \'short\', timePattern: \'HH:mm\'}" />';
                        rulesOptions = new Array('equal', 'notEqual');
                        break;
                    case 'rating':
                        var input = '<select name="filterValue" dojoType="phpr.FilteringSelect" autocomplete="false">';
                        for (var j = 1; j <= this.filterField[i].max; j++) {
                            input += '<option value="' + j + '">' + j + '</option>';
                        }
                        input       += '</select>';
                        rulesOptions = new Array('equal', 'notEqual', 'major', 'majorEqual', 'minor', 'minorEqual');
                        break;
                    default:
                        var input  = '<input type="text" name="filterValue" dojoType="dijit.form.ValidationTextBox" '
                            + 'regExp="' + phpr.regExpForFilter.getExp() + '" '
                            + 'invalidMessage="' + phpr.regExpForFilter.getMsg() + '" />';
                        rulesOptions = new Array('like', 'notLike', 'begins', 'ends', 'equal', 'notEqual',
                            'major', 'majorEqual', 'minor', 'minorEqual');
                        break;
                }

                var rule = '<select name="filterRule" dojoType="phpr.FilteringSelect" autocomplete="false" '
                    + 'style="width: 120px;">';
                for (var j in rulesOptions) {
                    rule += '<option value="' + rulesOptions[j] + '">' + this._rules[rulesOptions[j]] +  '</option>';
                }
                rule += '</select>';

                dijit.byId('filterRuleDiv').set('content', rule);
                dijit.byId('filterInputDiv').set('content', input);
                dojo.style(dojo.byId('filterButtonDiv'), 'display', 'inline');
                break;
            }
        }
    },

    submitFilterForm:function() {
        // Summary:
        //    Prepare the data for send it to the server
        // Description:
        //    Process the operator, field, value and rule for send them
        if (!dijit.byId('filterForm').isValid()) {
            dijit.byId('filterForm').validate();
            return false;
        }
        var filters  = this.getFilters();
        var found    = 0;
        var sendData = dijit.byId('filterForm').get('value');

        if (sendData['filterField'].indexOf('_forDate') > 0) {
            // Convert date
            sendData['filterValue'] = phpr.Date.getIsoDate(sendData['filterValue']);
            sendData['filterField'] = sendData['filterField'].replace('_forDate', '');
        } else if (sendData['filterField'].indexOf('_forTime') > 0) {
            // Convert time
            sendData['filterValue'] = phpr.Date.getIsoTime(sendData['filterValue']);
            sendData['filterField'] = sendData['filterField'].replace('_forTime', '');
        } else {
            // Check for other date or times values
            var type = null;
            for (var i in this.filterField) {
                if (this.filterField[i].key == sendData['filterField']) {
                    type = this.filterField[i].type;
                    break;
                }
            }
            switch (type) {
                case 'date':
                    sendData['filterValue'] = phpr.Date.getIsoDate(sendData['filterValue']);
                    break;
                case 'time':
                    sendData['filterValue'] = phpr.Date.getIsoTime(sendData['filterValue']);
                    break;
                default:
                    break;
            }
        }

        var data = new Array(sendData['filterOperator'] || 'AND', sendData['filterField'], sendData['filterRule'],
            sendData['filterValue']);
        var newFilter = data.join(this._filterSeparator);

        // Don't save the same filter two times
        for (var i in filters) {
            if (filters[i] === newFilter) {
                found = 1;
                break;
            }
        }
        if (!found) {
            filters.push(newFilter);
        }

        this.sendFilterRequest(filters);
    },

    deleteFilter:function(index) {
        // Summary:
        //    Delete a filter
        // Description:
        //    Delete one filter and make a new request to the server
        var filters = this.getFilters();
        if (index == 'all') {
            filters = new Array();
        } else {
            filters.splice(index, 1);
        }
        this.sendFilterRequest(filters);
    },

    setFilterQuery:function(filters) {
        // Summary:
        //    Make the POST values of the filters
        // Description:
        //    Make the POST values of the filters if there are any
        //    Save the used filters in the cookie
        this.setUrl();

        // Fix id
        this._filterData = new Array();
        for (var f in filters) {
            this._filterData.push(filters[f].replace('_fixedId', 'id'));
        }

        var currentCookie = dojo.cookie(this._filterCookie);
        if (typeof(currentCookie) == 'undefined') {
            // New cookie
            if (filters.length > 0) {
                dojo.cookie(this._filterCookie, filters, {expires: 365});
            }
        } else {
            // Existing cookie
            if (filters != currentCookie) {
                if (filters.length > 0) {
                    dojo.cookie(this._filterCookie, filters, {expires: 365});
                } else {
                    dojo.cookie(this._filterCookie, filters);
                }
            }
        }
    },

    sendFilterRequest:function(filters) {
        // Summary:
        //    Make the request to the server
        // Description:
        //    Make the request to the server
        this.setFilterQuery(filters);

        phpr.DataStore.deleteData({url: this.url});
        phpr.DataStore.addStore({url: this.url});
        phpr.DataStore.requestData({url: this.url, serverQuery: {'filters[]': this._filterData},
            processData: dojo.hitch(this, "onLoaded")});
    },

    getFilters:function() {
        // Summary:
        //    Returns the filters saved in the cookie
        // Description:
        //    Returns the filters saved in the cookie,
        //    clean the array for return only valid filters
        var filters = dojo.cookie(this._filterCookie);
        if (filters == undefined) {
            filters = new Array();
        } else {
            filters = filters.split(",");
            for (var i in filters) {
                var data  = filters[i].split(this._filterSeparator, 4);
                if (!data[0] || !data[1] || !data[2] || !data[3]) {
                    filters.splice(i, 1);
                }
            }
        }

        return filters;
    },

    drawFilters:function(filters) {
        // Summary:
        //    Draw the all the used filters
        // Description:
        //    Display each used filter with the user translation
        //    and a button for delete it.
        var html  = '';
        var first = 1;

        // Message
        if (dijit.byId('filterLabelDiv')) {
            if (this.filterField.length > 0) {
                dijit.byId('filterLabelDiv').set('content', phpr.nls.get("Add a filter"));
            } else {
                dijit.byId('filterLabelDiv').set('content',
                    phpr.nls.get("Please, delete some filters for get a correct result set."));
            }
        }

        // Field
        var fieldDiv = null;
        if (fieldDiv = dojo.byId('filterFieldDiv')) {
            if (fieldDiv.style.display == 'none') {
                if (this.filterField.length > 0) {
                    var fieldSelect = '<select name="filterField" dojoType="phpr.FilteringSelect" '
                        + 'autocomplete="false" onchange="dojo.publish(\'' + phpr.module + '.gridProxy\', '
                        + '[\'changeInputFilter\', this.value]); return false;">';
                    fieldSelect += '<option value=""></option>';
                    for (var i in this.filterField) {
                        fieldSelect += '<option value="' + this.filterField[i].key + '">'
                            + this.filterField[i].label + '</option>';
                    }
                    fieldSelect += '</select>';
                    dojo.style(fieldDiv, 'display', 'inline');
                    dijit.byId('filterFieldDiv').set('content', fieldSelect);
                }
            }
        }

        // Operator
        if (filters.length == 0 && dojo.byId('filterOperatorDiv')) {
            dojo.style(dojo.byId('filterOperatorDiv'), 'display', 'none');
        }

        for (var i in filters) {
            var data  = filters[i].split(this._filterSeparator, 4);
            if (data[0] && data[1] && data[2] && data[3]) {
                // Operator
                if (first) {
                    var operator = false;
                    first        = 0;
                    if (this.filterField.length > 0 && dojo.byId('filterOperatorDiv')) {
                        dojo.style(dojo.byId('filterOperatorDiv'), 'display', 'inline');
                    }
                } else {
                    var operator = data[0];
                }

                // Label and value
                var label = data[1];
                var value = data[3];
                for (var j in this.filterField) {
                    if (this.filterField[j].key == data[1]) {
                        label = this.filterField[j].label;
                        switch(this.filterField[j].type) {
                            case 'selectbox':
                                for (var o in this.filterField[j].options) {
                                    if (this.filterField[j].options[o].id == data[3]) {
                                        value = this.filterField[j].options[o].name;
                                        break;
                                    }
                                }
                                break;
                            case 'date':
                                value = new Date(phpr.Date.isoDateTojsDate(value));
                                value = dojo.date.locale.format(value, {datePattern: 'yyyy-MM-dd', selector: 'date'});
                                break;
                            case 'time':
                                value = new Date(phpr.Date.isoTimeTojsDate(value));
                                value = dojo.date.locale.format(value, {datePattern: 'HH:mm', selector: 'time'});
                                break;
                            default:
                                break;
                        }
                    }
                }
                html += this.render(["phpr.Default.template.filters", "display.html"], null, {
                    module:   phpr.module,
                    id:       i,
                    operator: operator,
                    field:    label,
                    rule:     this._rules[data[2]],
                    value:    value
                });
            }
        }

        dijit.byId('filterDisplayDiv').set('content', html);

        if (filters.length > 0) {
            if (this._deleteAllFilters === null && dojo.byId("filterDisplayDelete").children.length == 0) {
                var params = {
                    label:     phpr.nls.get('Delete all'),
                    showLabel: true,
                    baseClass: "positive",
                    iconClass: "cross",
                    disabled:  false,
                    style:     'margin-left: 10px;'
                };
                this._deleteAllFilters = new dijit.form.Button(params);
                dojo.byId("filterDisplayDelete").appendChild(this._deleteAllFilters.domNode);
                dojo.connect(this._deleteAllFilters, "onClick", dojo.hitch(this, "deleteFilter", ['all']));
            }
        } else {
            phpr.destroySubWidgets('filterDisplayDelete');
            this._deleteAllFilters = null;
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

        if (meta.length == 0) {
            // Create a "ADD" button
            var params = {
                label:     phpr.nls.get('Add a new item'),
                showLabel: true,
                baseClass: "positive",
                iconClass: 'add'
            };

            this._node.set('content', phpr.drawEmptyMessage('There are no entries on this level'));
            var buttonRow = dojo.byId('buttonRow');
            if (buttonRow.children.length != 0) {
                var button = buttonRow.children[0].children[0].children[0].children[0];
                if (button.className == 'dijitReset dijitInline dijitIcon add') {
                    // There is an 'add' button, so the user have create access
                    var newEntry = new dijit.form.Button(params);
                    dojo.addClass(this._node.domNode, 'addButtonText');
                    this._node.domNode.appendChild(newEntry.domNode);

                    dojo.connect(newEntry, "onClick", dojo.hitch(this, function() {
                        this.main.newEntry();
                    }));
                }
            }
        } else {
            dojo.removeClass(this._node.domNode, 'addButtonText');
            this.processActions();

            // Data of the grid
            this.gridData = {
                items: []
            };
            var content = dojo.clone(phpr.DataStore.getData({url: this.url}));

            // Get datetime keys
            this.splitFields = new Array();
            for (var i = 0; i < meta.length; i++) {
                if (meta[i]['type'] == 'datetime') {
                    if (!this.splitFields['datetime']) {
                        this.splitFields['datetime'] = new Array();
                    }
                    this.splitFields['datetime'].push(meta[i]['key']);
                }
            }

            for (var i in content) {
                if (this.usePencilForEdit()) {
                    content[i]['gridEdit'] = 'editButton || ' + phpr.nls.get('Open this item in the form to edit it');
                }

                // Split a value in two values
                for (var index in content[i]) {
                    // For datetime values
                    if (phpr.inArray(index, this.splitFields['datetime'])) {
                        var key         = index + '_forDate';
                        var value       = content[i][index].substr(0, 10);
                        content[i][key] = value;

                        var key         = index + '_forTime';
                        var value       = content[i][index].substr(11, 5);
                        content[i][key] = value;
                    }
                }

                // Extra Columns for current module
                for (var indexCol in this.extraColumns) {
                    var key         = this.extraColumns[indexCol]['key'];
                    var divClass    = this.extraColumns[indexCol]['class'];
                    var label       = this.extraColumns[indexCol]['label'];
                    content[i][key] = divClass + ' || ' + phpr.nls.get(label);
                };
                this.gridData.items.push(content[i]);
            }
            var store = new dojo.data.ItemFileWriteStore({data: this.gridData});

            // Render export Button
            this.setExportButton(meta);

            this.setGridLayout(meta);

            var type   = this.useCheckbox() ? "phpr.grid._View" : "dojox.grid._View";
            var p6Grid = this;
            this.grid  = new dojox.grid.DataGrid({
                store:     store,
                structure: [{type: type,
                            defaultCell: {
                                editable: true,
                                type:     phpr.grid.cells.Text,
                                styles:   'text-align: left;'
                            },
                            rows: this.gridLayout
                }],
                doclick:    function(e) { p6Grid.doClick(e); },
                dodblclick: function(e) { p6Grid.doDblClick(e); },
                onRowClick: function(e) { }
            }, document.createElement('div'));

            this.setClickEdit();

            this._node.set('content', this.grid.domNode);
            this.grid.startup();
            this.loadGridSorting();
            this.loadGridScroll();

            dojo.connect(this.grid, "onCellClick", dojo.hitch(this, "cellClick"));
            dojo.connect(this.grid, "onApplyCellEdit", dojo.hitch(this, "cellEdited"));
            dojo.connect(this.grid, "onStartEdit", dojo.hitch(this, "checkCanEdit"));
            dojo.connect(this.grid, "onHeaderCellClick", this, "saveGridSorting");
            dojo.connect(this.grid.views.views[0].scrollboxNode, "onscroll", this, "saveGridScroll");
            dojo.connect(this.grid, "onCellMouseOver", dojo.hitch(this, "showTooltip"));
            dojo.connect(this.grid, "onCellMouseOut", dojo.hitch(this, "hideTooltip"));

            if (this.useCheckbox()) {
                this.render(["phpr.Default.template", "gridActions.html"], this.grid.views.views[0].gridActions, {
                    module:        phpr.module,
                    actions:       this.comboActions,
                    checkAllTxt:   phpr.nls.get('Check All'),
                    uncheckAllTxt: phpr.nls.get('Uncheck All')
                });

                dojo.connect(dojo.byId("gridComboAction"), "onchange", this, "doComboAction");
            }
        }

        // Filters
        this.setFilterButton(meta);
        this.manageFilters();

        // Draw the tags
        this.showTags();
    },

    saveGridScroll:function() {
        // Summary:
        //    Stores in cookies the new scroll position for the current grid
        //    Use the hash for identify the cookie
        dojo.cookie(this._scrollTopCookie, this.grid.scrollTop, {expires: 365});
    },

    loadGridScroll:function() {
        // Summary:
        //    Retrieves from cookies the scroll position for the current grid, if there is one
        //    Use the hash for identify the module grid
        var scrollTop = dojo.cookie(this._scrollTopCookie);
        if (scrollTop != undefined) {
            this.grid.scrollTop = scrollTop;
        }
    },

    saveGridSorting:function(e) {
        // Summary:
        //    Stores in cookies the new sorting criterion for the current grid
        //    Use the hash for identify the cookie
        var sortColumn = this.grid.getSortIndex();
        var sortAsc    = this.grid.getSortAsc();

        dojo.cookie(this._sortColumnCookie, sortColumn, {expires: 365});
        dojo.cookie(this._sortAscCookie, sortAsc, {expires: 365});
    },

    loadGridSorting:function() {
        // Summary:
        //    Retrieves from cookies the sorting criterion for the current grid if any
        //    Use the hash for identify the cookie
        var sortColumn = dojo.cookie(this._sortColumnCookie);
        var sortAsc    = dojo.cookie(this._sortAscCookie);
        if (sortColumn != undefined && sortAsc != undefined) {
            this.grid.setSortIndex(parseInt(sortColumn), eval(sortAsc));
        }
    },

    doClick:function(e) {
        // Summary:
        //    Re-write the function for allow single and double clicks with different functions
        // Description:
        //    On one click, wait 500 milliseconds and process it.
        //    If there is other click meanwhile, is a double click.
        //    We use 300 milliseconds as a difference between one click and the second in a double-click.
        if (window.gridTimeOut) {
            window.clearTimeout(window.gridTimeOut);
        }
        var date = new Date();

        if (this.grid.edit.isEditing()) {
            if (!this.grid.edit.isEditCell(e.rowIndex, e.cellIndex)) {
                // Click outside the current edit widget
                // Just apply the changes, and do not process it as rowClick (open a form)
                this.grid.edit.apply();
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
            if (this.useCheckbox() && e.cellIndex == 0) {
                this.grid.edit.cancel();
            }

            return;
        }

        // Set a new time for wait
        if (null === this._lastTime) {
            this._lastTime = date.getMilliseconds();
            window.gridTimeOut = window.setTimeout(dojo.hitch(this, "doClick", e), 500);
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
                    this.grid.edit.setEditCell(e.cell, e.rowIndex);
                    this.grid.onRowDblClick(e);
                } else {
                    this.grid.onRowDblClick(e);
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
                this.grid.onCellClick(e);
            } else {
                this.grid.onRowClick(e);
            }
        }
    },

    doDblClick:function(e) {
        // Summary:
        //    Empry function for disable the double click, since is processes by the doClick
        if (dojo.isIE) {
            this._doubleClick = true;
            // Process a double click
            if (e.cellNode) {
                this.grid.edit.setEditCell(e.cell, e.rowIndex);
                this.grid.onRowDblClick(e);
            } else {
                this.grid.onRowDblClick(e);
            }
        }
    },

    cellClick:function(e) {
        // Summary:
        //    This function process a 'row' action
        // Description:
        //    As soon as a Pencil icon or an extra action cell is clicked the corresponding action is processed
        //    If the click is on other cell, just open the form
        var useCheckBox      = this.useCheckbox();
        var usePencilForEdit = this.usePencilForEdit();
        var index            = e.cellIndex;
        var openForm         = false;
        if ((useCheckBox && usePencilForEdit && index == 1) ||
            (!useCheckBox && usePencilForEdit && index == 0) ||
            (this.firstExtraCol && index >= this.firstExtraCol)) {
            if ((useCheckBox && index == 1) || (!useCheckBox && index == 0)) {
                // Click on the pencil
                openForm = true;
            } else {
                var key    = e['cell']['field'];
                var temp   = key.split('|');
                var action = temp[0];
                var mode   = parseInt(temp[1]);
                var item   = this.grid.getItem(e.rowIndex);
                var rowId  = this.grid.store.getValue(item, 'id');
                // Click on an extra action button
                this.doAction(action, rowId, mode, this.TARGET_SINGLE);
            }
        } else if (useCheckBox && index == 0) {
            // Click on the checkbox, do nothing
        } else {
            // Click on the row
            openForm = true;
        }

        // Open the form
        if (openForm) {
            var item  = this.grid.getItem(e.rowIndex);
            var rowId = this.grid.store.getValue(item, 'id');
            this.getLinkForEdit(rowId);
        }
    },

    getLinkForEdit:function(id) {
        // Summary:
        //    Return the link for open the form
        // Description:
        //    Return the link for open the form
        this.main.setUrlHash(phpr.module, id);
    },

    checkCanEdit:function(inCell, inRowIndex) {
        // Summary:
        //    Check the access of the item for the user
        // Description:
        //    Keep the current value to restore or check it later
        //    We can't stop the edition, but we can restore the value
        this.hideTooltip(null, inCell.getNode(inRowIndex));
        if (!this._oldRowValues[inRowIndex]) {
            this._oldRowValues[inRowIndex] = {};
        }
        var item  = this.grid.getItem(inRowIndex);
        var value = this.grid.store.getValue(item, inCell.field);
        this._oldRowValues[inRowIndex][inCell.field] = value;
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

        // Skip for combobox
        if (inFieldIndex == 'gridComboBox') {
            return;
        }

        if (dojo.isIE) {
            this._doubleClick = false;
        }

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

            if (inFieldIndex.indexOf('_forDate') > 0) {
                // Convert date to datetime
                var item             = this.grid.getItem(inRowIndex);
                var key              = inFieldIndex.replace('_forDate', '');
                inFieldIndexConvined = key + '_forTime';
                inValue              = inValue + ' ' + this.grid.store.getValue(item, inFieldIndexConvined);

                if (inValue != this._oldRowValues[inRowIndex][key]) {
                    this._newRowValues[inRowIndex][key] = inValue;
                }
            } else if (inFieldIndex.indexOf('_forTime') > 0) {
                // Convert time to datetime
                var item             = this.grid.getItem(inRowIndex);
                var key              = inFieldIndex.replace('_forTime', '');
                inFieldIndexConvined = key + '_forDate';
                inValue              = inValue + ' ' + this.grid.store.getValue(item, inFieldIndexConvined);

                if (inValue != this._oldRowValues[inRowIndex][key]) {
                    this._newRowValues[inRowIndex][key] = inValue;
                }
            } else {
                // Normal widgets
                if (inValue != this._oldRowValues[inRowIndex][inFieldIndex]) {
                    this._newRowValues[inRowIndex][inFieldIndex] = inValue;
                }
            }

            this.applyChanges();
        }
    },

    applyChanges:function() {
        // Summary:
        //    Call the saveChanges function
        // Description:
        //    Wait until the last call is finished
        if (this._active == true) {
            setTimeout(dojo.hitch(this, "applyChanges"), 500);
        } else {
            this._active = true;
            this.saveChanges();
        }
    },

    saveChanges:function() {
        // Summary:
        //    Apply the changes into the server
        // Description:
        //    Get all the new values into the _newRowValues
        //    and send them to the server

        // Get all the IDs for the data sets.

        var changed = false;
        var content = new Array();
        var ids     = new Array();
        for (var i in this._newRowValues) {
            var item  = this.grid.getItem(i);
            var curId = this.grid.store.getValue(item, 'id');
            for (var j in this._newRowValues[i]) {
                changed = true;
                content['data[' + curId + '][' + j + ']'] = this._newRowValues[i][j];
                ids[i] = j;
            }
        }

        // Post the content of all changed forms
        // Only if there are changes
        if (changed) {
            phpr.send({
                url:       this.updateUrl,
                content:   content,
                onSuccess: dojo.hitch(this, function(response) {
                    this._active = false;
                    if (response.type == 'error') {
                        new phpr.handleResponse('serverFeedback', response);
                    }
                    if (response.type == 'success') {
                        this.updateAfterSaveChanges();
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

    updateAfterSaveChanges:function() {
        // Summary:
        //    Actions after the saveChanges call returns success
        this.publish("updateCacheData");
    },

    exportData:function() {
        // Summary:
        //    Open a new window in CSV mode
        // Description:
        //    Open a new window in CSV mode
        window.open(phpr.webpath + 'index.php/' + phpr.module + '/index/csvList/nodeId/' + this.id
            + '/csrfToken/' + phpr.csrfToken);
        return false;
    },

    updateData:function() {
        // Summary:
        //    Delete the cache for this grid
        // Description:
        //    Delete the cache for this grid
        phpr.DataStore.deleteData({url: this.url});
        phpr.DataStore.deleteData({url: this._tagUrl});
    },

    setGetExtraActionsUrl:function() {
        // Summary:
        //    Sets the url where to get the grid actions data from
        this.getActionsUrl = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonGetExtraActions';
    },

    itemsCheck:function(state) {
        // Summary:
        //    Checks or unchecks all items depending on the received boolean
        if (state == 'false') {
            state = false;
        }
        for (row = 0; row < this.grid.rowCount; row ++) {
            this.grid.store.setValue(this.grid.getItem(row), 'gridComboBox', state);
        }
    },

    processActions:function() {
        // Summary:
        //    Processes the info of the grid actions and fills the appropriate arrays
        var actions = phpr.DataStore.getData({url: this.getActionsUrl});

        if (this.comboActions.length == 0 && this.extraColumns.length == 0) {
            this.comboActions[0]          = new Array();
            this.comboActions[0]['key']   = '';
            this.comboActions[0]['label'] = phpr.nls.get('With selected');
            this.comboActions[0]['class'] = '';

            for (var i = 0; i < actions.length; i ++) {
                var actionData      = new Array();
                actionData['key']   = actions[i]['action'] + '|' + actions[i]['mode'];
                actionData['label'] = actions[i]['label'];
                actionData['class'] = actions[i]['class'];
                switch (actions[i]['target']) {
                    case this.TARGET_SINGLE:
                    default:
                        var next                = this.extraColumns.length;
                        this.extraColumns[next] = actionData;
                        break;
                    case this.TARGET_MULTIPLE:
                        var next                = this.comboActions.length;
                        this.comboActions[next] = actionData;
                        break;
                }
            }
        }
    },

    doComboAction:function() {
        // Summary:
        //    Process the action selected in the combo, for the checked Ids. Calls 'doAction' function
        if (this.useCheckbox()) {
            var ids = new Array();
            for (row = 0; row < this.grid.rowCount; row ++) {
                var checked = this.grid.store.getValue(this.grid.getItem(row), 'gridComboBox');
                if (checked) {
                    ids[ids.length] = this.grid.store.getValue(this.grid.getItem(row), 'id');
                }
            }
            if (ids.length > 0) {
                var idsSend = ids.join(',');
                var select  = dojo.byId("gridComboAction");
                var key     = select.value;
                if (key != null) {
                    var temp   = key.split('|');
                    var action = temp[0];
                    var mode   = parseInt(temp[1]);

                    // Check for multiple rows
                    var actionName = select.children[select.selectedIndex].text;
                    phpr.confirmDialog(dojo.hitch(this, function() {
                        this.doAction(action, idsSend, mode, this.TARGET_MULTIPLE);
                    }), phpr.nls.get('Please confirm implement') + ' "' + actionName + '"<br />(' + ids.length + ' '
                        + phpr.nls.get('rows selected') + ')');
                    select.selectedIndex = 0;
                }
            } else {
                dojo.byId("gridComboAction").selectedIndex = 0;
            }
        }
    },

    doAction:function(action, ids, mode, target) {
        // Summary:
        //    Performs a specific action with one or more items of the module,
        //    called by an extra grid icon or the combo
        if (target == this.TARGET_SINGLE) {
            var idUrl = 'id';
        } else if (target == this.TARGET_MULTIPLE) {
            var idUrl = 'ids';
        }

        var actionUrl = this.getDoActionUrl(action, idUrl, ids);

        if (mode == this.MODE_XHR) {
            // Call the requested action with the selected ids and wait for a response
            phpr.send({
                url:       actionUrl,
                onSuccess: dojo.hitch(this, function(data) {
                    new phpr.handleResponse('serverFeedback', data);
                    if (data.type == 'success') {
                        if (target == this.TARGET_MULTIPLE) {
                            this.publish("updateCacheData");
                            this.publish("reload");
                        }
                    }
                })
            });
        } else if (mode == this.MODE_WINDOW) {
            // Call the requested action with the selected ids in a new windows
            window.open(actionUrl + '/csrfToken/' + phpr.csrfToken);
        } else if (mode == this.MODE_CLIENT) {
            // Call the requested action with the selected ids in the main
            eval("this.main." + action + "('" + ids + "')");
        }

        if (this.useCheckbox()) {
            dojo.byId("gridComboAction").selectedIndex = 0;
        }
    },

    getDoActionUrl:function(action, idUrl, ids) {
        // Summary:
        //    Isolated code for easy customization, this function returns the URL to be called for the requested action.

        return phpr.webpath + 'index.php/' + phpr.module + '/index/' + action + '/nodeId/' + phpr.currentProjectId
            + '/' + idUrl + '/' + ids;
    },

    showTooltip:function(e) {
        // Summary:
        //    This function shows the tooltip
        // Description:
        //    Uses the dijit function 'showTooltip' to show the tooltip.
        if (!this.grid.edit.isEditing()) {
            var useCheckBox = this.useCheckbox();
            var index       = e.cellIndex;
            if ((!useCheckBox || (useCheckBox && index != 0)) && e.cell.editable) {
                dijit.showTooltip(phpr.nls.get("Double click to edit"), e.cellNode, 'above');
            }
        }
    },

    hideTooltip:function(e, cellNode) {
        // Summary:
        //    Hides the tooltip.
        // Description:
        //    Uses the dijit function 'hideTooltip' to hide the tooltip.
        if (e) {
            cellNode = e.cellNode;
        }
        dijit.hideTooltip(cellNode);
    }
});
