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
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Default.Grid");

dojo.require("dijit.form.Button");
dojo.require("dojo.data.ItemFileWriteStore");
dojo.require("dojox.grid.DataGrid");
dojo.require("dojo.store.JsonRest");
dojo.require("dojo.data.ObjectStore");

dojo.declare("phpr.Default.Grid", phpr.Default.System.Component, {
    // Summary:
    //    Class for displaying a PHProjekt grid
    // Description:
    //    This Class takes care of displaying the list information we receive from our Server in a dojo grid
    main:          null,
    id:            0,
    updateUrl:     null,
    _newRowValues: [],
    _oldRowValues: [],
    gridData:      [],
    getActionsUrl: null,
    _tagUrl:       null,
    _exportButton: null,
    _filterButton: null,
    extraColumns:  [],
    comboActions:  [],
    firstExtraCol: null,
    gridLayout:    [],
    splitFields:   [],
    _lastTime:     null,
    _active:       false,
    _doubleClickMaxTime: 750,
    _gridComboAction: null,

    // gridFilters Widget
    gridFilters:   null,

    // Grid cookies
    _sortColumnCookie: null,
    _sortAscCookie:    null,
    _scrollTopCookie:  null,

    // Filters
    filterField:       [],
    _rules:            [],
    _filterCookie:     null,
    _deleteAllFilters: null,

    // Constants
    MODE_XHR:        0,
    MODE_WINDOW:     1,
    MODE_CLIENT:     2,
    TARGET_SINGLE:   0,
    TARGET_MULTIPLE: 1,

    // Timer
    _doubleClickTimer: null,

    constructor: function(/*String*/updateUrl, /*Object*/main, /*Int*/ id, /*Widget*/gridBoxContainer, /*Object*/extraParams) {
        // Summary:
        //    render the grid on construction
        // Description:
        //    this function receives the list data from the server and renders the corresponding grid
        this.extraParams = extraParams || {};
        this.main             = main;
        this.id               = id;
        this.gridBoxContainer = gridBoxContainer;
        this.node             = gridBoxContainer.gridBox || gridBoxContainer;
        this.updateUrl        = updateUrl;
        this._newRowValues    = {};
        this._oldRowValues    = {};
        this.gridData         = {};
        this.getActionsUrl    = null;
        this.extraColumns     = [];
        this.comboActions     = [];

        phpr.tutorialAnchors.grid = this.node.containerNode;

        var getHashForCookie  = null;

        // Set cookies urls
        if (phpr.isGlobalModule(this.main.module)) {
            getHashForCookie = this.main.module;
        } else {
            getHashForCookie = this.main.module + '.' + phpr.currentProjectId;
        }
        this._filterCookie     = getHashForCookie + '.filters';
        this._sortColumnCookie = getHashForCookie + ".grid.sortColumn";
        this._sortAscCookie    = getHashForCookie + ".grid.sortAsc";
        this._scrollTopCookie  = getHashForCookie + ".grid.scroll";

        this.gridLayout  = [];
        this.filterField = [];

        this.saveFilters(this.getFilters());
        this.setGetExtraActionsUrl();

        phpr.DataStore.addStore({url: this.getActionsUrl});
        var dlist = new dojo.DeferredList([
            phpr.MetadataStore.metadataFor(this.main.module, this.main.state.projectId || 1),
            phpr.DataStore.requestData({url: this.getActionsUrl})
        ]);
        dlist.addCallback(dojo.hitch(this, "onLoaded"));
    },

    destroy: function() {
        // Summary:
        //    Destroys all nodes and event handlers previously added to
        //    this._events and this._domNodes to prevent memleaks.
        // Description:
        //    This method should be called before the grid is replaced or
        //    deleted. It ensures that all event handlers and dom nodes are
        //    removed to prevent cyclic dependencies and therefore prevent
        //    memory leaks

        // Clean up all potential references
        this.inherited(arguments);

        this.main              = null;
        this.id                = null;
        this.updateUrl         = null;
        this._newRowValues     = null;
        this._oldRowValues     = null;
        this.gridData          = null;
        this.grid              = null;
        this.node              = null;
        this._exportButton     = null;
        this._deleteAllFilters = null;
        if (this._fieldTemplate && dojo.isFunction(this._fieldTemplate.destroy)) {
            this._fieldTemplate.destroy();
        }
    },

    getRestUrl: function() {
        // Summary:
        //    Get the url of the rest api to use
        return 'index.php/' + this.main.module + '/' + this.main.module + '/';
    },

    useIdInGrid: function() {
        // Summary:
        //    Draw the ID on the grid
        // Description:
        //    Draw the ID on the grid
        return true;
    },

    usePencilForEdit: function() {
        // Summary:
        //    Draw the pencil icon for edit the row
        // Description:
        //    Draw the pencil icon for edit the row
        return false;
    },

    useCheckbox: function() {
        // Summary:
        //    Whether to show or not the checkbox in the grid list
        return true;
    },

    setGridLayout: function(meta) {
        // Summary:
        //    Create the layout using the different field types
        // Description:
        //    Create the layout using the different field types
        //    Also create the filter fields
        this.gridLayout  = [];
        this.filterField = [];

        // Checkbox column
        if (this.useCheckbox()) {
            this.gridLayout.push({
                name:     " ",
                field:    "gridComboBox",
                width:    "20px",
                type:     phpr.Default.System.Grid.cells.NonSubmittingCheckbox,
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
                formatter: phpr.Default.System.Grid.formatIcon
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
                key:   'id',
                label: 'ID',
                type:  'text'
            });
        }

        var range = null;
        var opts = null;
        var vals = null;
        var maxLength = null;

        var dateFormatter = function(date) {
            if (!date) {
                return '';
            } else {
                return dojo.date.locale.format(
                    phpr.date.isoDateTojsDate(date),
                    this.constraint
                );
            }
        };

        var getDateWidgetProps = function(date) {
            return { value: phpr.date.isoDateTojsDate(date) };
        };

        var getDateValue = function(row) {
            var date = this.widget.get("value");
            if (!date) {
                return;
            }

            var dt = this.grid.getItem(row)[this.field];
            dt = phpr.date.isoDatetimeTojsDate(dt);
            dt.setFullYear(date.getFullYear());
            dt.setMonth(date.getMonth());
            dt.setDate(date.getDate());
            return phpr.date.getIsoDatetime(dt, dt);
        };

        var dtTimeGetValue = function(row) {
            var time = this.widget.get("value");
            if (!time) {
                return;
            }
            var hours = time.getHours(),
                minutes = time.getMinutes();

            var dt = this.grid.getItem(row)[this.field];
            dt = phpr.date.isoDateTojsDate(dt);
            dt.setHours(hours);
            dt.setMinutes(minutes);
            return phpr.date.getIsoTime(dt);
        };

        // Module columns
        for (var i = 0; i < meta.length; i++) {
            switch (meta[i].type) {
                case 'selectbox':
                    range     = meta[i].range;
                    opts      = [];
                    vals      = [];
                    j         = 0;
                    maxLength = meta[i].key.length;
                    for (var j in range) {
                        vals.push(range[j].id);
                        opts.push(range[j].name);
                        if (range[j].name.length > maxLength) {
                            maxLength = range[j].name.length;
                        }
                    }
                    this.gridLayout.push({
                        name:     meta[i].label,
                        field:    meta[i].key,
                        styles:   "text-align: center;",
                        type:     phpr.Default.System.Grid.cells.Select,
                        width:    (maxLength * 8) + 'px',
                        options:  opts,
                        values:   vals,
                        editable: meta[i].readOnly ? false : true
                    });
                    this.filterField.push({
                        key:     meta[i].key,
                        label:   meta[i].label,
                        type:    'selectbox',
                        options: range
                    });
                    break;

                case 'date':
                    this.gridLayout.push({
                        width:         '90px',
                        name:          meta[i].label,
                        field:         meta[i].key,
                        styles:        "text-align: center;",
                        type:          dojox.grid.cells.DateTextBox,
                        promptMessage: 'yyyy-MM-dd',
                        formatter: dateFormatter,
                        getValue: getDateValue,
                        constraint:    {
                            formatLength:   'short',
                            selector:       'date'
                        },
                        editable:      meta[i].readOnly ? false : true
                    });
                    this.filterField.push({
                        key:   meta[i].key,
                        label: meta[i].label,
                        type:  'date'
                    });
                    break;

                case 'datetime':
                    this.gridLayout.push({
                        width: '90px',
                        name: meta[i].label + ' (' + phpr.nls.get('Date') + ')',
                        field: meta[i].key,
                        styles: "text-align: center;",
                        type: dojox.grid.cells.DateTextBox,
                        getValue: getDateValue,
                        formatter: dateFormatter,
                        constraint: {
                            formatLength:   'short',
                            selector:       'date'
                        },
                        editable: meta[i].readOnly ? false : true
                    });
                    this.gridLayout.push({
                        width: '90px',
                        name: meta[i].label + ' (' + phpr.nls.get('Hour') + ')',
                        field: meta[i].key,
                        styles: "text-align: center;",
                        type: phpr.Default.System.Grid.cells.Time,
                        getValue: dtTimeGetValue,
                        editable: meta[i].readOnly ? false : true
                    });
                    this.filterField.push({
                        key:   meta[i].key + '_forDate',
                        label: meta[i].label + ' (' + phpr.nls.get('Date') + ')',
                        type:  'date'
                    });
                    this.filterField.push({
                        key:   meta[i].key + '_forTime',
                        label: meta[i].label + ' (' + phpr.nls.get('Hour') + ')',
                        type:  'time'
                    });
                    break;

                case 'percentage':
                    this.gridLayout.push({
                        width:    '90px',
                        name:     meta[i].label,
                        field:    meta[i].key,
                        styles:   "text-align: center;",
                        type:     phpr.Default.System.Grid.cells.Percentage,
                        editable: meta[i].readOnly ? false : true
                    });
                    this.filterField.push({
                        key:   meta[i].key,
                        label: meta[i].label,
                        type:  'text'
                    });
                    break;

                case 'time':
                    this.gridLayout.push({
                        width:    '60px',
                        name:     meta[i].label,
                        field:    meta[i].key,
                        styles:   "text-align: center;",
                        type:     phpr.Default.System.Grid.cells.Time,
                        editable: meta[i].readOnly ? false : true
                    });
                    this.filterField.push({
                        key:   meta[i].key,
                        label: meta[i].label,
                        type:  'time'
                    });
                    break;

                case 'upload':
                    this.gridLayout.push({
                        width:     'auto',
                        name:      meta[i].label,
                        field:     meta[i].key,
                        styles:    "text-align: center;",
                        type:      dojox.grid.cells._Widget,
                        formatter: phpr.Default.System.Grid.formatUpload,
                        editable:  false
                    });
                    this.filterField.push({
                        key:   meta[i].key,
                        label: meta[i].label,
                        type:  'text'
                    });
                    break;

                case 'display':
                    range = meta[i].range;
                    // Has it values for translating an Id into a descriptive String?
                    if (range[0] !== undefined) {
                        // Yes
                        opts      = [];
                        vals      = [];
                        j         = 0;
                        maxLength = meta[i].key.length;
                        for (var j in range) {
                            vals.push(range[j].id);
                            opts.push(range[j].name);
                            if (range[j].name.length > maxLength) {
                                maxLength = range[j].name.length;
                            }
                        }
                        this.gridLayout.push({
                            name:     meta[i].label,
                            field:    meta[i].key,
                            styles:   "text-align: center;",
                            type:     phpr.Default.System.Grid.cells.Select,
                            width:    (maxLength * 8) + 'px',
                            options:  opts,
                            values:   vals,
                            editable: false
                        });
                        this.filterField.push({
                            key:     meta[i].key,
                            label:   meta[i].label,
                            type:    'selectbox',
                            options: range
                        });
                    } else {
                        // No
                        this.gridLayout.push({
                            width:    'auto',
                            name:     meta[i].label,
                            field:    meta[i].key,
                            type:     phpr.Default.System.Grid.cells.Text,
                            styles:   "text-align: center;",
                            editable: false
                        });
                        this.filterField.push({
                            key:   meta[i].key,
                            label: meta[i].label,
                            type: 'text'
                        });
                    }
                    break;

                case 'text':
                    this.gridLayout.push({
                        width:    'auto',
                        name:     meta[i].label,
                        field:    meta[i].key,
                        type:     phpr.Default.System.Grid.cells.Text,
                        styles:   "",
                        editable: meta[i].readOnly ? false : true
                    });
                    this.filterField.push({
                        key:   meta[i].key,
                        label: meta[i].label,
                        type:  'text'
                    });
                    break;

                case 'textarea':
                    this.gridLayout.push({
                        width:    'auto',
                        name:     meta[i].label,
                        field:    meta[i].key,
                        type:     phpr.Default.System.Grid.cells.Textarea,
                        styles:   "",
                        editable: false
                    });
                    this.filterField.push({
                        key:   meta[i].key,
                        label: meta[i].label,
                        type: 'text'
                    });
                    break;

                case 'rating':
                    var max   = parseInt(meta[i].range.id, 10);
                    opts      = [];
                    vals      = [];
                    maxLength = meta[i].key.length;
                    for (var j = 1; j <= max; j++) {
                        vals.push(j);
                        opts.push(j);
                    }
                    this.gridLayout.push({
                        name:     meta[i].label,
                        field:    meta[i].key,
                        styles:   "text-align: center;",
                        type:     phpr.Default.System.Grid.cells.Select,
                        width:    (maxLength * 8) + 'px',
                        options:  opts,
                        values:   vals,
                        editable: meta[i].readOnly ? false : true
                    });
                    this.filterField.push({
                        key:     meta[i].key,
                        label:   meta[i].label,
                        type:    'rating',
                        max:     max
                    });
                    break;

                default:
                    this.gridLayout.push({
                        width:    'auto',
                        name:     meta[i].label,
                        field:    meta[i].key,
                        type:     phpr.Default.System.Grid.cells.Text,
                        styles:   "",
                        editable: meta[i].readOnly ? false : true
                    });
                    this.filterField.push({
                        key:   meta[i].key,
                        label: meta[i].label,
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
                field:     this.extraColumns[i].key,
                width:     "20px",
                type:      dojox.grid.cells.Cell,
                editable:  false,
                styles:    "vertical-align: middle;",
                formatter: phpr.Default.System.Grid.formatIcon
            });
        }
    },

    setExportButton: function(meta) {
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

            this.garbageCollector.addNode(this._exportButton);

            phpr.viewManager.getView().buttonRow.domNode.appendChild(this._exportButton.domNode);
            this.garbageCollector.addEvent(
                dojo.connect(this._exportButton, "onClick", dojo.hitch(this, "exportData")));
        }
    },

    setFilterButton: function(meta) {
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

            this.garbageCollector.addNode(this._filterButton);

            phpr.viewManager.getView().buttonRow.domNode.appendChild(this._filterButton.domNode);
            this.garbageCollector.addEvent(
                dojo.connect(this._filterButton, "onClick",
                    dojo.hitch(this, function() {
                        this.gridBoxContainer.gridFiltersBox.toggle();
                    })));
        }
    },

    manageFilters: function() {
        // Summary:
        //    Prepare the filter form
        // Description:
        //    Prepare the filter form for manage filters
        //    and open it if there is any
        if (this._rules.length === 0) {
            this._rules.like       = phpr.nls.get('Filter_like_rule');
            this._rules.notLike    = phpr.nls.get('Filter_not_like_rule');
            this._rules.equal      = phpr.nls.get('Filter_equal_rule');
            this._rules.notEqual   = phpr.nls.get('Filter_not_equal_rule');
            this._rules.major      = phpr.nls.get('Filter_major_rule');
            this._rules.majorEqual = phpr.nls.get('Filter_major_equal_rule');
            this._rules.minor      = phpr.nls.get('Filter_minor_rule');
            this._rules.minorEqual = phpr.nls.get('Filter_minor_equal_rule');
            this._rules.begins     = phpr.nls.get('Filter_begins_rule');
            this._rules.ends       = phpr.nls.get('Filter_ends_rule');
        }

        var filters = this.getFilters();

        if (this.gridBoxContainer && this.gridBoxContainer.gridFiltersBox) {
            var gridFiltersBox = this.gridBoxContainer.gridFiltersBox;
            gridFiltersBox.titleNode.innerHTML = phpr.nls.get('Filters');
            // Closed div
            if (!this.gridFilters || gridFiltersBox.domNode.style.height == '0px') {
                if (!this.gridFilters) {
                    var filterform = new phpr.Default.System.TemplateWrapper({
                        templateName: "phpr.Default.template.filters.form.html",
                        templateData: {
                            module:  this.main.module,
                            andTxt:  phpr.nls.get("Filter_AND"),
                            orTxt:   phpr.nls.get("Filter_OR"),
                            okTxt:   phpr.nls.get("OK")
                        }
                    });

                    gridFiltersBox.set('content', filterform);
                    filterform.startup();
                    this.gridFilters = filterform;
                }

                this.drawFilters(filters);

                // Only open div if there is any filter
                if (filters.length > 0) {
                    gridFiltersBox.toggle();
                }
            // Opened div
            } else {
                this.drawFilters(filters);
            }
        }
    },

    changeInputFilter: function(field) {
        // Summary:
        //    Manage filters
        // Description:
        //    Change the rule and value fields depend on the selected field type
        this._fieldTemplate = new phpr.Default.Field();
        var rulesOptions  = [];

        for (var i in this.filterField) {
            if (this.filterField[i].key == field) {
                var input = "";
                switch (this.filterField[i].type) {
                    case 'selectbox':
                        input = '<select name="filterValue" dojoType="phpr.FilteringSelect" autocomplete="false">';
                        for (var j in this.filterField[i].options) {
                            input += '<option value="' + this.filterField[i].options[j].id + '">' +
                                this.filterField[i].options[j].name + '</option>';
                        }
                        input       += '</select>';
                        rulesOptions = new Array('equal', 'notEqual');
                        break;
                    case 'date':
                        input = '<input type="text" name="filterValue" dojoType="phpr.DateTextBox" ' +
                            'constraints="{datePattern: \'yyyy-MM-dd\'}" promptMessage="yyyy-MM-dd" />';
                        rulesOptions = new Array('equal', 'notEqual', 'major', 'majorEqual', 'minor', 'minorEqual');
                        break;
                    case 'time':
                        input = '<input type="text" name="filterValue" dojoType="dijit.form.TimeTextBox" ' +
                            'constraints="{formatLength: \'short\', timePattern: \'HH:mm\'}" />';
                        rulesOptions = new Array('equal', 'notEqual');
                        break;
                    case 'rating':
                        input = '<select name="filterValue" dojoType="phpr.FilteringSelect" autocomplete="false">';
                        for (var j = 1; j <= this.filterField[i].max; j++) {
                            input += '<option value="' + j + '">' + j + '</option>';
                        }
                        input       += '</select>';
                        rulesOptions = new Array('equal', 'notEqual', 'major', 'majorEqual', 'minor', 'minorEqual');
                        break;
                    default:
                        input  = '<input type="text" name="filterValue" dojoType="dijit.form.ValidationTextBox" ' +
                            'invalidMessage="' + phpr.regExpForFilter.getMsg() + '" />';
                        rulesOptions = new Array('like', 'notLike', 'begins', 'ends', 'equal', 'notEqual',
                            'major', 'majorEqual', 'minor', 'minorEqual');
                        break;
                }

                var rule = '<select name="filterRule" dojoType="phpr.FilteringSelect" autocomplete="false" ' +
                    'style="width: 120px;">';
                for (var j in rulesOptions) {
                    rule += '<option value="' + rulesOptions[j] + '">' + this._rules[rulesOptions[j]] +  '</option>';
                }
                rule += '</select>';

                this.gridFilters.filterRuleDiv.set('content', rule);
                this.gridFilters.filterInputDiv.set('content', input);
                dojo.style(this.gridFilters.filterButtonDiv.domNode, 'display', 'inline');
                break;
            }
        }
    },

    submitFilterForm: function() {
        // Summary:
        //    Prepare the data for send it to the server
        // Description:
        //    Process the operator, field, value and rule for send them
        if (!this.gridFilters.filterForm.isValid()) {
            this.gridFilters.filterForm.validate();
            return false;
        }
        var filters  = this.getFilters();
        var found    = 0;
        var sendData = this.gridFilters.filterForm.get('value');

        if (sendData.filterField.indexOf('_forDate') > 0) {
            // Convert date
            sendData.filterValue = phpr.date.getIsoDate(sendData.filterValue);
            sendData.filterField = sendData.filterField.replace('_forDate', '');
        } else if (sendData.filterField.indexOf('_forTime') > 0) {
            // Convert time
            sendData.filterValue = phpr.date.getIsoTime(sendData.filterValue);
            sendData.filterField = sendData.filterField.replace('_forTime', '');
        } else {
            // Check for other date or times values
            var type = null;
            for (var i in this.filterField) {
                if (this.filterField[i].key == sendData.filterField) {
                    type = this.filterField[i].type;
                    break;
                }
            }
            switch (type) {
                case 'date':
                    sendData.filterValue = phpr.date.getIsoDate(sendData.filterValue);
                    break;
                case 'time':
                    sendData.filterValue = phpr.date.getIsoTime(sendData.filterValue);
                    break;
                default:
                    break;
            }
        }

        var data = new Array(sendData.filterOperator || 'AND', sendData.filterField, sendData.filterRule,
            sendData.filterValue);

        // Don't save the same filter two times
        for (var i in filters) {
            if (filters[i].join(",") === data.join(",")) {
                found = 1;
                break;
            }
        }
        if (!found) {
            filters.push(data);
        }

        this.saveFilters(filters);
        this.manageFilters();
        this.refresh();
        return false;
    },

    deleteFilter: function(index) {
        // Summary:
        //    Delete a filter
        // Description:
        //    Delete one filter and make a new request to the server
        var filters = this.getFilters();
        if (index == 'all') {
            filters = [];
        } else {
            filters.splice(index, 1);
        }
        this.saveFilters(filters);
        this.manageFilters();
        this.refresh();
    },

    saveFilters: function(filters) {
        // Summary:
        //    Make the POST values of the filters
        // Description:
        //    Make the POST values of the filters if there are any
        //    Save the used filters in the cookie
        dojo.cookie(this._filterCookie, dojo.toJson(filters), {expires: 365});
    },

    getFilters: function() {
        // Summary:
        //    Returns the filters saved in the cookie
        // Description:
        //    Returns the filters saved in the cookie,
        //    clean the array for return only valid filters
        var filters = [];
        try {
            var filters = dojo.fromJson(dojo.cookie(this._filterCookie));
        } catch (e) {
            //ignore
        }
        if (filters === undefined || !dojo.isArray(filters)) {
            filters = [];
        }

        return filters;
    },

    drawFilters: function(filters) {
        // Summary:
        //    Draw the all the used filters
        // Description:
        //    Display each used filter with the user translation
        //    and a button for delete it.
        var html  = '';
        var first = 1;

        // Message
        if (this.gridFilters.filterLabelDiv) {
            if (this.filterField.length > 0) {
                this.gridFilters.filterLabelDiv.set('content', phpr.nls.get("Add a filter"));
            } else {
                this.gridFilters.filterLabelDiv.set('content',
                    phpr.nls.get("Please, delete some filters for get a correct result set."));
            }
        }

        // Field
        if (this.gridFilters.filterFieldDiv) {
            if (this.gridFilters.filterFieldDiv.domNode.style.display == 'none') {
                if (this.filterField.length > 0) {
                    var fieldSelect = '<select name="filterField" dojoType="phpr.FilteringSelect" ' +
                        'autocomplete="false" onchange="dojo.publish(\'' + this.main.module + '.gridProxy\', ' +
                        '[\'changeInputFilter\', this.value]); return false;">';
                    fieldSelect += '<option value=""></option>';
                    for (var i in this.filterField) {
                        fieldSelect += '<option value="' + this.filterField[i].key + '">' +
                            this.filterField[i].label + '</option>';
                    }
                    fieldSelect += '</select>';
                    dojo.style(this.gridFilters.filterFieldDiv.domNode, 'display', 'inline');
                    this.gridFilters.filterFieldDiv.set('content', fieldSelect);
                }
            }
        }

        // Operator
        if (filters.length === 0 && this.gridFilters.filterOperatorDiv) {
            dojo.style(this.gridFilters.filterOperatorDiv.domNode, 'display', 'none');
        }

        var operator = null;
        for (var i in filters) {
            var data  = filters[i];
            if (data[0] && data[1] && data[2] && data[3]) {
                // Operator
                if (first) {
                    operator = false;
                    first        = 0;
                    if (this.filterField.length > 0 && this.gridFilters.filterOperatorDiv) {
                        dojo.style(this.gridFilters.filterOperatorDiv.domNode, 'display', 'inline');
                    }
                } else {
                    operator = data[0];
                }

                // Label and value
                var label = data[1];
                var value = data[3];
                for (var j in this.filterField) {
                    if (this.filterField[j].key == data[1]) {
                        label = this.filterField[j].label;
                        switch (this.filterField[j].type) {
                            case 'selectbox':
                                for (var o in this.filterField[j].options) {
                                    if (this.filterField[j].options[o].id == data[3]) {
                                        value = this.filterField[j].options[o].name;
                                        break;
                                    }
                                }
                                break;
                            case 'date':
                                value = new Date(phpr.date.isoDateTojsDate(value));
                                value = dojo.date.locale.format(value, {datePattern: 'yyyy-MM-dd', selector: 'date'});
                                break;
                            case 'time':
                                value = new Date(phpr.date.isoTimeTojsDate(value));
                                value = dojo.date.locale.format(value, {datePattern: 'HH:mm', selector: 'time'});
                                break;
                            default:
                                break;
                        }
                    }
                }
                html += phpr.fillTemplate("phpr.Default.template.filters.display.html", {
                    module:   this.main.module,
                    id:       i,
                    operator: operator,
                    field:    label,
                    rule:     this._rules[data[2]],
                    value:    value
                });
            }
        }

        this.gridFilters.filterDisplayDiv.set('content', html);

        if (filters.length > 0) {
            if (this._deleteAllFilters === null &&
                    this.gridFilters.filterDisplayDelete.domNode.children.length === 0) {
                var params = {
                    label:     phpr.nls.get('Delete all'),
                    showLabel: true,
                    baseClass: "positive",
                    iconClass: "cross",
                    disabled:  false,
                    style:     'margin-left: 10px;'
                };
                this._deleteAllFilters = new dijit.form.Button(params);

                this.garbageCollector.addNode(this._deleteAllFilters);

                this.gridFilters.filterDisplayDelete.domNode.appendChild(this._deleteAllFilters.domNode);
                this.garbageCollector.addEvent(
                    dojo.connect(this._deleteAllFilters, "onClick", dojo.hitch(this, "deleteFilter", ['all'])));
            }
        } else {
            this.gridFilters.filterDisplayDelete.destroyDescendants();
            this._deleteAllFilters = null;
        }
    },

    _addButtonCb: function() {
        // Summary:
        //    Callback for the "add item" button
        this.main.newEntry();
    },

    onLoaded: function(dataContent) {
        // Summary:
        //    This function is called when the grid is loaded
        // Description:
        //    It takes care of setting the grid headers to the right format, displays the contextmenu
        //    and renders the filter for the grid
        // Layout of the grid
        if (this._destroyed) {
            return;
        }

        var meta = dataContent[0][1];

        dojo.removeClass(this.node.domNode, 'addButtonText');
        this.processActions();

        // Data of the grid
        this.gridData = {
            items: []
        };

        // Get datetime keys
        this.splitFields = [];
        for (var i = 0; i < meta.length; i++) {
            if (meta[i].type == 'datetime') {
                if (!this.splitFields.datetime) {
                    this.splitFields.datetime = [];
                }
                this.splitFields.datetime.push(meta[i].key);
            }
        }

        for (var i in content) {
            if (this.usePencilForEdit()) {
                content[i].gridEdit = 'editButton || ' + phpr.nls.get('Open this item in the form to edit it');
            }

            // Split a value in two values
            for (var index in content[i]) {
                // For datetime values
                if (phpr.inArray(index, this.splitFields.datetime)) {
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
                var key         = this.extraColumns[indexCol].key;
                var divClass    = this.extraColumns[indexCol]["class"];
                var label       = this.extraColumns[indexCol].label;
                content[i][key] = divClass + ' || ' + phpr.nls.get(label);
            }
            this.gridData.items.push(content[i]);
        }
        // Render export Button
        this.setExportButton(meta);

        this.setGridLayout(meta);

        this.grid = new dojox.grid.DataGrid({
            store: new dojo.data.ObjectStore({objectStore: new dojo.store.JsonRest({target: this.getRestUrl()})}),
            query: this.getQuery(),
            structure: [{
                type: this.useCheckbox() ? "phpr.Default.System.Grid._View" : "dojox.grid._View",
                defaultCell: {
                    editable: true,
                    type:     phpr.Default.System.Grid.cells.Text,
                    styles:   'text-align: left;'
                },
                rows: this.gridLayout
            }]
        }, dojo.create('div'));

        this.grid.singleClickEdit = false;

        this.node.set('content', this.grid);

        this.loadGridSorting();
        this.loadGridScroll();

        this.garbageCollector.addEvent(
                dojo.connect(this.grid, "onRowClick", dojo.hitch(this, "doClick"))
                );
        this.garbageCollector.addEvent(
                dojo.connect(this.grid, "onCellDblClick", dojo.hitch(this, "doDblClick"))
                );
        this.garbageCollector.addEvent(
                dojo.connect(this.grid, "onApplyCellEdit", dojo.hitch(this, "cellEdited"))
                );
        this.garbageCollector.addEvent(
                dojo.connect(this.grid, "onStartEdit", dojo.hitch(this, "checkCanEdit"))
                );
        this.garbageCollector.addEvent(
                dojo.connect(this.grid, "onHeaderCellClick", this, "saveGridSorting")
                );
        this.garbageCollector.addEvent(
                dojo.connect(this.grid.views.views[0].scrollboxNode, "onscroll", this, "saveGridScroll")
                );
        this.garbageCollector.addEvent(
                dojo.connect(this.grid, "onCellMouseOver", dojo.hitch(this, "showTooltip"))
                );
        this.garbageCollector.addEvent(
                dojo.connect(this.grid, "onCellMouseOut", dojo.hitch(this, "hideTooltip"))
                );

        if (this.useCheckbox()) {
            var content = new phpr.Default.System.TemplateWrapper({
                templateName: "phpr.Default.template.gridActions.html",
                templateData: {
                    module:        this.main.module,
                    actions:       this.comboActions,
                    checkAllTxt:   phpr.nls.get('Check All'),
                    uncheckAllTxt: phpr.nls.get('Uncheck All')
                }
            });

            this._gridComboAction = content.gridComboAction;
            this.grid.views.views[0].gridActions.set('content', content);

            content.startup();

            this.garbageCollector.addNode(this.grid.views.views[0]);
            this.garbageCollector.addNode(this.grid.views.views[0].gridActions);
            this.garbageCollector.addNode(content);

            this.garbageCollector.addEvent(
                dojo.connect(content.gridComboAction,
                    "onchange",
                    this,
                    "doComboAction"));
        }

        // Filters
        this.setFilterButton(meta);
        this.manageFilters();
    },

    refresh: function(idx) {
        if (idx !== undefined) {
            if (this.grid._by_idx[idx] && this.grid._by_idx[idx].item) {
                delete this.grid._by_idx[idx].item;
            }
            this.grid.updateRow(idx);
        } else {
            this.grid.setQuery(this.getQuery());
        }
    },

    getQuery: function() {
        var q = {};
        var state = phpr.pageManager.getState();
        var filters = this.getFilters();

        if (!phpr.isGlobalModule(state.moduleName)) {
            q.projectId = state.projectId || 1;
        }
        if (state.includeSubentries == "true") {
            q.recursive = "true";
        }
        if (filters.length > 0) {
            q.filters = dojo.toJson(filters);
        }

        return q;
    },

    saveGridScroll: function() {
        // Summary:
        //    Stores in cookies the new scroll position for the current grid
        //    Use the hash for identify the cookie
        dojo.cookie(this._scrollTopCookie, this.grid.scrollTop, {expires: 365});
    },

    loadGridScroll: function() {
        // Summary:
        //    Retrieves from cookies the scroll position for the current grid, if there is one
        //    Use the hash for identify the module grid
        var scrollTop = dojo.cookie(this._scrollTopCookie);
        if (scrollTop !== undefined) {
            this.grid.scrollTop = scrollTop;
        }
    },

    saveGridSorting: function(e) {
        // Summary:
        //    Stores in cookies the new sorting criterion for the current grid
        //    Use the hash for identify the cookie
        var sortColumn = this.grid.getSortIndex();
        var sortAsc    = this.grid.getSortAsc();

        dojo.cookie(this._sortColumnCookie, sortColumn, {expires: 365});
        dojo.cookie(this._sortAscCookie, sortAsc, {expires: 365});
    },

    loadGridSorting: function() {
        // Summary:
        //    Retrieves from cookies the sorting criterion for the current grid if any
        //    Use the hash for identify the cookie
        var sortColumn = dojo.cookie(this._sortColumnCookie);
        var sortAsc    = dojo.cookie(this._sortAscCookie);
        if (sortColumn !== undefined && sortAsc !== undefined) {
            this.grid.setSortIndex(parseInt(sortColumn, 10), eval(sortAsc));
        }
    },

    doDblClick: function(e) {
        // Summary:
        //      Process a double click
        if (this._doubleClickTimer !== null) {
            window.clearTimeout(this._doubleClickTimer);
            this._doubleClickTimer = null;
        }
        if (e.cellNode) {
            this.grid.edit.setEditCell(e.cell, e.rowIndex);
            this.grid.onRowDblClick(e);
        } else {
            this.grid.onRowDblClick(e);
        }
    },

    doClick: function(e) {
        // Summary:
        //    This function process a 'row' action
        // Description:
        //    As soon as a Pencil icon or an extra action cell is clicked the corresponding action is processed
        //    If the click is on other cell, just open the form
        if (e.cellIndex) {
            var useCheckBox      = this.useCheckbox();
            var usePencilForEdit = this.usePencilForEdit();
            var index            = e.cellIndex;
            var openForm         = false;
            if ((useCheckBox && usePencilForEdit && index == 1) ||
                    (!useCheckBox && usePencilForEdit && index === 0) ||
                    (this.firstExtraCol && index >= this.firstExtraCol)) {
                if ((useCheckBox && index == 1) || (!useCheckBox && index === 0)) {
                    // Click on the pencil
                    openForm = true;
                } else {
                    var key    = e.cell.field;
                    var temp   = key.split('|');
                    var action = temp[0];
                    var mode   = parseInt(temp[1], 10);
                    var item   = this.grid.getItem(e.rowIndex);
                    var rowId  = this.grid.store.getValue(item, 'id');
                    // Click on an extra action button
                    this.doAction(action, rowId, mode, this.TARGET_SINGLE);
                }
            } else {
                // Click on the row
                openForm = true;
            }

            // Open the form
            if (openForm && !this.grid.edit.isEditing() && this._doubleClickTimer === null) {
                this._doubleClickTimer = window.setTimeout(
                    dojo.hitch(this,
                        function(e) {
                            var item  = this.grid.getItem(e.rowIndex);
                            var rowId = this.grid.store.getValue(item, 'id');
                            this.hideTooltip(e);
                            this._doubleClickTimer = null;
                            this.editItemWithId(rowId);
                        },
                        e
                    ),
                    this._doubleClickMaxTime
                );
            }
        }
    },

    editItemWithId: function(id) {
        phpr.pageManager.modifyCurrentState({ id: id });
    },

    checkCanEdit: function(inCell, inRowIndex) {
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

    canEdit: function(inRowIndex) {
        // Summary:
        //    Check the access of the item for the user
        // Description:
        //    Return true if has write or admin accees
        var writePermissions = this.gridData.items[inRowIndex].rights[0][phpr.currentUserId][0].write;
        var adminPermissions = this.gridData.items[inRowIndex].rights[0][phpr.currentUserId][0].admin;
        if (writePermissions == 'false' && adminPermissions == 'false') {
            return false;
        } else {
            return true;
        }
    },

    cellEdited: function(inValue, inRowIndex, inFieldIndex) {
        // Summary:
        //    Save the changed values for store
        // Description:
        //    Save only the items that have changed, to save them later
        //    If the user can't edit the item, restore the last value
        if (this.grid.store.isDirty()) {
            this.grid.store.save({
                onComplete: dojo.hitch(this, "refresh", inRowIndex),
                scope: this
            });
        }
    },

    exportData: function() {
        // Summary:
        //    Open a new window in CSV mode
        // Description:
        //    Open a new window in CSV mode
        window.open('index.php/' + this.main.module +
                '/index/csvList/nodeId/' + this.id +
                '/csrfToken/' + phpr.csrfToken);
        return false;
    },

    setGetExtraActionsUrl: function() {
        // Summary:
        //    Sets the url where to get the grid actions data from
        this.getActionsUrl = 'index.php/' + this.main.module + '/index/jsonGetExtraActions';
    },

    itemsCheck: function(state) {
        // Summary:
        //    Checks or unchecks all items depending on the received boolean
        if (state == 'false') {
            state = false;
        }
        for (row = 0; row < this.grid.rowCount; row ++) {
            this.grid.store.setValue(this.grid.getItem(row), 'gridComboBox', state);
        }
    },

    processActions: function() {
        // Summary:
        //    Processes the info of the grid actions and fills the appropriate arrays
        var actions = phpr.DataStore.getData({url: this.getActionsUrl});

        if (this.comboActions.length === 0 && this.extraColumns.length === 0) {
            this.comboActions[0]          = [];
            this.comboActions[0].key   = '';
            this.comboActions[0].label = phpr.nls.get('With selected');
            this.comboActions[0]["class"] = '';

            for (var i = 0; i < actions.length; i ++) {
                var actionData      = [];
                actionData.key   = actions[i].action + '|' + actions[i].mode;
                actionData.label = actions[i].label;
                actionData["class"] = actions[i]["class"];
                switch (actions[i].target) {
                    case this.TARGET_SINGLE:
                        /* falls through */
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

    doComboAction: function() {
        // Summary:
        //    Process the action selected in the combo, for the checked Ids. Calls 'doAction' function
        if (this.useCheckbox()) {
            var checkBoxCellIndex = 0;
            var ids = [];
            for (var i = 0; i < this.grid.rowCount; i++) {
                var inputQry = dojo.query('td[idx="0"] > input', this.grid.getRowNode(i));
                if (inputQry.length > 0) {
                    var checked = inputQry[0].checked;
                    if (checked) {
                        ids.push(this.grid.store.getValue(this.grid.getItem(i), 'id'));
                    }
                }
            }

            if (ids.length > 0) {
                var idsSend = ids.join(',');
                var select = this._gridComboAction;
                var key     = select.value;
                if (key !== null) {
                    var temp   = key.split('|');
                    var action = temp[0];
                    var mode   = parseInt(temp[1], 10);

                    // Check for multiple rows
                    var actionName = select.children[select.selectedIndex].text;
                    this.garbageCollector.addNode(
                        phpr.confirmDialog(dojo.hitch(this, function() {
                                this.doAction(action, idsSend, mode, this.TARGET_MULTIPLE);
                            }),
                            phpr.nls.get('Please confirm the following action:') + ' "' +
                            actionName + '"<br />(' + ids.length + ' ' +
                            phpr.nls.get('rows selected') + ')'));
                    select.selectedIndex = 0;
                }
                select = null;// avoid cyclic ref
            } else {
                this._gridComboAction.selectedIndex = 0;
            }
        }
    },

    doAction: function(action, ids, mode, target) {
        // Summary:
        //    Performs a specific action with one or more items of the module,
        //    called by an extra grid icon or the combo
        var idUrl = '';
        if (target == this.TARGET_SINGLE) {
            idUrl = 'id';
        } else if (target == this.TARGET_MULTIPLE) {
            idUrl = 'ids';
        }

        var actionUrl = this.getDoActionUrl(action, idUrl, ids);

        if (mode == this.MODE_XHR) {
            // Call the requested action with the selected ids and wait for a response
            phpr.send({
                url:       actionUrl
            }).then(dojo.hitch(this, function(data) {
                if (data) {
                    new phpr.handleResponse('serverFeedback', data);
                    if (data.type == 'success') {
                        if (target == this.TARGET_MULTIPLE) {
                            this.publish("updateCacheData");
                            this.publish("reload");
                        }
                    }
                }
            }));
        } else if (mode == this.MODE_WINDOW) {
            // Call the requested action with the selected ids in a new windows
            window.open(actionUrl + '/csrfToken/' + phpr.csrfToken);
        } else if (mode == this.MODE_CLIENT) {
            // Call the requested action with the selected ids in the main
            eval("this.main." + action + "('" + ids + "')");
        }

        if (this.useCheckbox()) {
            this._gridComboAction.selectedIndex = 0;
        }
    },

    getDoActionUrl: function(action, idUrl, ids) {
        // Summary:
        //    Isolated code for easy customization, this function returns the URL to be called for the requested action.

        return 'index.php/' + this.main.module + '/index/' + action +
            '/nodeId/' + phpr.currentProjectId + '/' + idUrl + '/' + ids;
    },

    showTooltip: function(e) {
        // Summary:
        //    This function shows the tooltip
        // Description:
        //    Uses the dijit function 'showTooltip' to show the tooltip.
        if (!this.grid.edit.isEditing()) {
            var useCheckBox = this.useCheckbox();
            var index       = e.cellIndex;
            if ((!useCheckBox || (useCheckBox && index !== 0)) && e.cell.editable) {
                dijit.showTooltip(phpr.nls.get("Double click to edit"), e.cellNode, 'above');
            }
        }
    },

    hideTooltip: function(e, cellNode) {
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
