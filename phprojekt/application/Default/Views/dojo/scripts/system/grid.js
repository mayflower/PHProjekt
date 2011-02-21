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

dojo.provide("phpr.Grid._View");
dojo.provide("phpr.Grid.Cells.DateTextBox");
dojo.provide("phpr.Grid.Cells.Percentage");
dojo.provide("phpr.Grid.Cells.Select");
dojo.provide("phpr.Grid.Cells.Text");
dojo.provide("phpr.Grid.Cells.Textarea");
dojo.provide("phpr.Grid.Cells.Time");
dojo.provide("phpr.Grid.FilterExpandoPane");
dojo.provide("phpr.Grid.Filter");
dojo.provide("phpr.Grid.Layout");

/************* Formatters *************/

phpr.Grid.formatIcon = function(value) {
    // Summary:
    //    Formatter for icon fields.
    // Description:
    //    Format the value into and icon for show in the grid.
    data = value.split('||');
    if (!data[1]) {
        data[1] = '';
    }

    return '<div class="' + data[0] + '" title="' + data[1] + '"></div>';
},

phpr.Grid.formatUpload = function(value) {
    // Summary:
    //    Formatter for upload fields.
    // Description:
    //    Format the upload string into text for show in the grid.
    if (value.indexOf('|') > 0) {
        files = value.split('||');
        value = '';
        for (p in files) {
            if (p > 0) {
                value += ', ';
            }
            value += files[p].substring(files[p].indexOf('|') + 1, files[p].length);
        }
    }
    return value;
},

/************* Cells *************/

dojo.declare("phpr.Grid.Cells.Percentage", dojox.grid.cells._Widget, {
    // Summary:
    //    Set a percentage widget for edit the value in the grid.
    // Description:
    //    Redefine the function to return the correct value.
    widgetClass: phpr.Form.HorizontalSlider,

    getValue:function(inRowIndex) {
        return dojo.number.round(this.widget.get('value'), 1);
    },

    format:function(inRowIndex, inItem) {
        var f, i = this.grid.edit.info, d = this.get ? this.get(inRowIndex, inItem) : (this.value || this.defaultValue);
        if (this.editable && (this.alwaysEditing || (i.rowIndex == inRowIndex && i.cell == this))) {
            return this.formatEditing(d, inRowIndex);
        } else {
            var v = dojo.number.round(d, 1);
            return (typeof v == 'undefined' ? this.defaultValue : v);
        }
    }
});

dojo.declare("phpr.Grid.Cells.Select", dojox.grid.cells.Select, {
    // Summary:
    //    Set a select widget for edit the value in the grid.
    // Description:
    //    Redefine the function to return the correct value.
    format:function(inRowIndex, inItem) {
        var f, i = this.grid.edit.info, d = this.get ? this.get(inRowIndex, inItem) : (this.value || this.defaultValue);
        if (this.editable && (this.alwaysEditing || (i.rowIndex == inRowIndex && i.cell == this))) {
            return this.formatEditing(d, inRowIndex);
        } else {
            var v = '';
            for (var i=0, o; ((o = this.options[i]) !== undefined); i++){
                if (d == this.values[i]) {
                    v = o;
                }
            }
            return (typeof v == 'undefined' ? this.defaultValue : v);
        }
    }
});

dojo.declare("phpr.Grid.Cells.DateTextBox", dojox.grid.cells.DateTextBox, {
    // Summary:
    //    Set a date widget for edit the value in the grid.
    // Description:
    //    Redefine the function to return the correct value.
    widgetClass: phpr.Form.DateTextBox,

    getValue:function(inRowIndex) {
        var date = this.widget.get('value');
        var day = date.getDate();
        if (day < 10) {
            day = '0' + day;
        }
        var month = (date.getMonth()+1);
        if (month < 10) {
            month = '0' + month
        }
        return date.getFullYear() + '-' + month + '-' + day;
    },

    setValue:function(inRowIndex, inValue) {
        if (this.widget) {
            var parts = inValue.split('-');
            var year  = parts[0];
            var month = parts[1]-1;
            var day   = parts[2];
            this.widget.set('value', new Date(year, month, day));
        } else {
            this.inherited(arguments);
        }
    },

    getWidgetProps:function(inDatum) {
        var parts = inDatum.split('-');
        var year  = parts[0];
        var month = parts[1]-1;
        var day   = parts[2];
        return dojo.mixin(this.inherited(arguments), {
            value: new Date(year, month, day)
        });
    }
});
var dgc = dojox.grid.cells;
dgc.DateTextBox.markupFactory = function(node, cell) {
    dgc._Widget.markupFactory(node, cell);
};

dojo.declare("phpr.Grid.Cells.Text", dojox.grid.cells._Widget, {
    // Summary:
    //    Set a text widget for edit the value in the grid.
    // Description:
    //    Redefine the function to return the correct value.
    setValue:function(inRowIndex, inValue) {
        if (this.widget && this.widget.setValue) {
            this.widget.set('value', inValue);
        } else {
            this.inherited(arguments);
        }
    },

    format:function(inRowIndex, inItem) {
        var f, i = this.grid.edit.info, d = this.get ? this.get(inRowIndex, inItem) : (this.value || this.defaultValue);
        if (this.editable && (this.alwaysEditing || (i.rowIndex == inRowIndex && i.cell == this))) {
            return this.formatEditing(d, inRowIndex);
        } else {
            if (d) {
                var maxLength = (this.getHeaderNode().offsetWidth - 21) / 7;
                var output    = d.toString();

                if (output.length > maxLength) {
                    output = output.substr(0, maxLength) + '...';
                }
                output = output.replace(/&/g, "&amp;");
                output = output.replace(/</g, "&lt;");
                output = output.replace(/>/g, "&gt;");
            } else {
                var output = '';
            }

            return output;
        }
    },

    attachWidget:function(inNode, inDatum, inRowIndex){
        // Add fix for IE
        if (dojo.isIE) {
            this.widget.domNode.unselectable = 'off';
        }
        this.inherited(arguments);
    }
});

dojo.declare("phpr.Grid.Cells.Textarea", phpr.Grid.Cells.Text, {
    // Summary:
    //    Set a textarea widget for edit the value in the grid.
    // Description:
    //    Redefine the function to return the correct value.
    format:function(inRowIndex, inItem) {
        var f, i = this.grid.edit.info, d = this.get ? this.get(inRowIndex, inItem) : (this.value || this.defaultValue);
        if (this.editable && (this.alwaysEditing || (i.rowIndex == inRowIndex && i.cell == this))) {
            return this.formatEditing(d, inRowIndex);
        } else {
            var maxLength = (this.getHeaderNode().offsetWidth - 21) / 7;
            var output    = this.strip_tags(d);
            if (output.length > maxLength) {
                output = output.substr(0, maxLength) + '...';
            }
            output = output.replace(/&/g, "&amp;");
            output = output.replace(/</g, "&lt;");
            output = output.replace(/>/g, "&gt;");

            return output;
        }
    },

    strip_tags:function(str, allowed_tags) {
        // Summary
        //    Strip tags function by Kevin van Zonneveld (http://kevin.vanzonneveld.net) improved by Luke Godfrey.
        // Example of use
        //    strip_tags('<p>Kevin</p> <br /><b>van</b> <i>Zonneveld</i>', '<i><b>');
        //    Returns: 'Kevin <b>van</b> <i>Zonneveld</i>'
        var key           = '';
        var allowed       = false;
        var matches       = [];
        var allowed_array = [];
        var allowed_tag   = '';
        var i             = 0;
        var k             = '';
        var html          = '';

        var replacer = function(search, replace, str) {
            return str.split(search).join(replace);
        };

        // Build allowes tags associative array
        if (allowed_tags) {
            allowed_array = allowed_tags.match(/([a-zA-Z]+)/gi);
        }

        str += '';

        // Match tags
        matches = str.match(/(<\/?[\S][^>]*>)/gi);

        // Go through all HTML tags
        for (key in matches) {
            if (isNaN(key)) {
                // IE7 Hack
                continue;
            }

            // Save HTML tag
            html = matches[key].toString();

            // Is tag not in allowed list? Remove from str!
            allowed = false;

            // Go through all allowed tags
            for (k in allowed_array) {
                // Init
                allowed_tag = allowed_array[k];
                i = -1;

                if (i != 0) { i = html.toLowerCase().indexOf('<'+allowed_tag+'>');}
                if (i != 0) { i = html.toLowerCase().indexOf('<'+allowed_tag+' ');}
                if (i != 0) { i = html.toLowerCase().indexOf('</'+allowed_tag)   ;}

                // Determine
                if (i == 0) {
                    allowed = true;
                    break;
                }
            }

            if (!allowed) {
                str = replacer(html, "", str); // Custom replace. No regexing
            }
        }
        return str;
    }
});

dojo.declare("phpr.Grid.Cells.Time", dojox.grid.cells._Widget, {
    // Summary:
    //    Set a textarea widget for edit the value in the grid.
    // Description:
    //    Redefine the function to return the correct value.
    setValue:function(inRowIndex, inValue) {
        inValue = phpr.Date.getIsoTime(inValue);
        if (this.widget && this.widget.setValue) {
            this.widget.set('value', inValue);
        } else {
            this.inherited(arguments);
        }
    },

    getValue:function(inRowIndex) {
        var value = this.widget.get('value');
        return phpr.Date.getIsoTime(value);
    },

    format:function(inRowIndex, inItem) {
        var f, i = this.grid.edit.info, d = this.get ? this.get(inRowIndex, inItem) : (this.value || this.defaultValue);
        if (this.editable && (this.alwaysEditing || (i.rowIndex == inRowIndex && i.cell == this))) {
            var d = phpr.Date.getIsoTime(d);
            return this.formatEditing(d, inRowIndex);
        } else {
            return phpr.Date.getIsoTime(d);
        }
    }
});

dojo.declare('phpr.Grid._View', [dojox.grid._View], {
    // Summary
    //    Extend the normal grid view.
    // Description
    //    Add a div after the grid for allow multiple actions.
    //    Overwrite some functions for custom use.
    templateString: '<div class="dojoxGridView" wairole="presentation">\r\n\t<div class="dojoxGridHeader" '
        + 'dojoAttachPoint="headerNode" wairole="presentation">\r\n\t\t<div dojoAttachPoint="headerNodeContainer" '
        + 'style="width:9000em" wairole="presentation">\r\n\t\t\t<div dojoAttachPoint="headerContentNode" '
        + 'wairole="row"></div>\r\n\t\t</div>\r\n\t</div>\r\n\t<input type="checkbox" class="dojoxGridHiddenFocus" '
        + 'dojoAttachPoint="hiddenFocusNode" wairole="presentation" />\r\n\t<input type="checkbox" '
        + 'class="dojoxGridHiddenFocus" wairole="presentation" />\r\n\t<div class="dojoxGridScrollbox" '
        + 'dojoAttachPoint="scrollboxNode" wairole="presentation">\r\n\t\t<div class="dojoxGridContent" '
        + 'dojoAttachPoint="contentNode" hidefocus="hidefocus" wairole="presentation"></div>\r\n\t\t'
        + '<div dojoAttachPoint="gridActions" class="gridActions" style="vertical-align: baseline;">'
        + '</div>\r\n\t</div>\r\n</div>\r\n',

    doStyleRowNode:function(inRowIndex, inRowNode) {
        // Summary
        //    Change the style of the row.
        // Description
        //    Mark as checked the checked rows.
        if (inRowNode) {
            var row = this.grid.rows.prepareStylingRow(inRowIndex, inRowNode);
            this.grid.onStyleRow(row);
            var item = this.grid.getItem(inRowIndex);
            if (item) {
                if (item['gridComboBox'] == 'true') {
                    row.customClasses += ' dojoxGridRowChecked';
                }
            }
            this.grid.rows.applyStyles(row);
        }
    },

    doHeaderEvent:function(e) {
        // Summary
        //    Overwrite the function for remove effect on the action bar.
        if(this.header.decorateEvent(e)){
            if (e.type == 'click') {
                dojo.style(this.gridActions, 'display', 'none');
                this.grid.onHeaderEvent(e);
                dojo.style(this.gridActions, 'display', 'inline');
            } else {
                this.grid.onHeaderEvent(e);
            }
        }
    },

	renderHeader:function() {
        // Summary
        //    Create the header only one time.
	    if (this.headerContentNode.innerHTML == '') {
            this.headerContentNode.innerHTML = this.header.generateHtml(this._getHeaderContent);
	    }
		if (this.flexCells){
			this.contentWidth = this.getContentWidth();
			this.headerContentNode.firstChild.style.width = this.contentWidth;
		}
		dojox.grid.util.fire(this, 'onAfterRow', [-1, this.structure.cells, this.headerContentNode]);
	}
});

dojo.declare('phpr.Grid.FilterExpandoPane', [dojox.layout.ExpandoPane], {
    // Summary
    //    Extend the widget for allow height 0.
    _startupSizes:function() {
        // Summary
        //    Overwrite the function for allow height 0.
        this._container   = this.getParent();
        this._titleHeight = dojo.marginBox(this.titleWrapper).h;
        this._closedSize  = 0;

        this._currentSize = dojo.contentBox(this.domNode);
        this._showSize    = this._currentSize['h'];
        this._setupAnims();

        if (this.startExpanded) {
            this._showing = true;
        } else {
            this._showing = false;
            this._hideWrapper();
            this._hideAnim.gotoPercent(99, true);
        }

        this._hasSizes = true;
    },

    resize:function(psize) {
        // Summary
        //    Overwrite the function for allow height 0.
        if (!this._hasSizes) {
            this._startupSizes(psize);
        }

        var size = (psize && psize.h) ? psize : dojo.marginBox(this.domNode);

        this._contentBox = {
            w: size.w || dojo.marginBox(this.domNode).w,
            h: size.h - 26
        };

        if (this._contentBox.h < 0) {
            this._contentBox.h = 0;
        }
        dojo.style(this.containerNode, 'height', this._contentBox.h + 'px');
        dojo.style(this.containerNode, 'overflowX', 'hidden');
        this._layoutChildren();
    }
});

/************* Helpers *************/

dojo.declare("phpr.Grid.Layout", null, {
    // Summary:
    //    Class for set the layout of each field.
    // Description:
    //    Set the diferent settings for each field in the grid.
    gridLayout: [],
    _max:       0,
    _maxLength: 0,
    _opts:      [],
    _vals:      [],

    constructor:function() {
        // Summary:
        //    Create a new layout.
        this.gridLayout = [];
        this._max       = 0;
        this._maxLength = 0;
        this._opts      = [];
        this._vals      = [];
    },

    addCheckField:function() {
        // Summary:
        //    Add a checkbox to the layout.
        var meta = {
            label:    ' ',
            key:      'gridComboBox',
            readOnly: false
        }
        var field       = this._initData(meta);
        field.type      = dojox.grid.cells.Bool;
        field.width     = '20px';
        field.filterKey = null;

        this.gridLayout['gridComboBox'] = field;
    },

    addEditField:function() {
        // Summary:
        //    Add an edit icon to the layout.
        var meta = {
            label:    ' ',
            key:      'gridEdit',
            readOnly: true
        }
        var field       = this._initData(meta);
        field.width     = '20px';
        field.styles    = 'vertical-align: middle;';
        field.formatter = phpr.Grid.formatIcon;
        field.filterKey = null;

        this.gridLayout['gridEdit'] = field;
    },

    addIdField:function() {
        // Summary:
        //    Add an ID field to the layout.
        var meta = {
            label:    'ID',
            key:      'id',
            readOnly: true
        }
        var field         = this._initData(meta);
        field.width       = '40px';
        field.styles      = 'text-align: right;';
        field.filterKey   = 'gridId';

        this.gridLayout['gridId'] = field;
    },

    addModuleFields:function(meta) {
        // Summary:
        //    Add all the fields to the layout.
        for (var i = 0; i < meta.length; i++) {
            var data = this._initData(meta[i]);
            switch(meta[i]['type']) {
                case 'selectbox':
                    this._setRangeValues(meta[i]);
                    data.styles        = 'text-align: center;';
                    data.type          = phpr.Grid.Cells.Select;
                    data.width         = (this._maxLength * 8) + 'px';
                    data.options       = this._opts;
                    data.values        = this._vals;
                    data.filterType    = 'selectbox';
                    data.filterOptions = meta[i]['range'];

                    this.gridLayout[data.field] = data;
                    break;

                case 'date':
                    data.width         = '90px';
                    data.styles        = 'text-align: center;';
                    data.type          = phpr.Grid.Cells.DateTextBox;
                    data.promptMessage = 'yyyy-MM-dd';
                    data.constraint    = {formatLength: 'short', selector: 'date', datePattern:'yyyy-MM-dd'};
                    data.filterType    = 'date';

                    this.gridLayout[data.field] = data;
                    break;

                case 'datetime':
                    var dateData           = dojo.clone(data);
                    dateData.name          = dateData.name + ' (' + phpr.nls.get('Date') + ')';
                    dateData.field         = dateData.field + '_forDate';
                    dateData.width         = '90px';
                    dateData.styles        = 'text-align: center;';
                    dateData.type          = phpr.Grid.Cells.DateTextBox;
                    dateData.promptMessage = 'yyyy-MM-dd';
                    dateData.constraint    = {formatLength: 'short', selector: 'date', datePattern:'yyyy-MM-dd'};
                    dateData.filterKey     = dateData.field;
                    dateData.filterLabel   = dateData.name;
                    dateData.filterType    = 'date';

                    this.gridLayout[dateData.field] = dateData;

                    var timeData         = dojo.clone(data);
                    timeData.name        = timeData.name + ' (' + phpr.nls.get('Hour') + ')';
                    timeData.field       = timeData.field + '_forTime';
                    timeData.width       = '60px';
                    timeData.styles      = 'text-align: center;';
                    timeData.type        = phpr.Grid.Cells.Time;
                    timeData.filterKey   = timeData.field;
                    timeData.filterLabel = timeData.name;
                    timeData.filterType  = 'time';

                    this.gridLayout[timeData.field] = timeData;
                    break;

                case 'percentage':
                    data.width  = '90px';
                    data.styles = 'text-align: center;';
                    data.type   = phpr.Grid.Cells.Percentage;

                    this.gridLayout[data.field] = data;
                    break;

                case 'time':
                    data.width      = '60px';
                    data.styles     = 'text-align: center;';
                    data.type       = phpr.Grid.Cells.Time;
                    data.filterType = 'time';

                    this.gridLayout[data.field] = data;
                    break;

                case 'upload':
                    data.styles    = 'text-align: center;';
                    data.type      = dojox.grid.cells._Widget;
                    data.formatter = phpr.Grid.formatUpload;
                    data.editable  = false;

                    this.gridLayout[data.field] = data;
                    break;

                case 'display':
                    // Has it values for translating an Id into a descriptive String?
                    if (meta[i]['range'][0] != undefined) {
                        this._setRangeValues(meta[i]);
                        data.styles        = 'text-align: center;';
                        data.type          = phpr.Grid.Cells.Select;
                        data.width         = (this._maxLength * 8) + 'px';
                        data.options       = this._opts;
                        data.values        = this._vals;
                        data.filterType    = 'selectbox';
                        data.filterOptions = meta[i]['range'];
                        data.editable      = false;
                    } else {
                        data.styles        = 'text-align: center;';
                        data.type          = phpr.Grid.Cells.Text;
                        data.editable      = false;
                    }

                    this.gridLayout[data.field] = data;
                    break;

                case 'text':
                    data.type = phpr.Grid.Cells.Text;

                    this.gridLayout[data.field] = data;
                    break;

                case 'textarea':
                    data.type     = phpr.Grid.Cells.Textarea;
                    data.editable = false;

                    this.gridLayout[data.field] = data;
                    break;

                case 'rating':
                    this._setRatingValues(meta[i]);
                    data.styles        = 'text-align: center;';
                    data.type          = phpr.Grid.Cells.Select;
                    data.width         = (this._maxLength * 8) + 'px';
                    data.options       = this._opts;
                    data.values        = this._vals;
                    data.filterType    = 'rating';
                    data.filterOptions = meta[i]['range'];
                    data.editable      = false;
                    data.filterOptions = this._max;

                    this.gridLayout[data.field] = data;
                    break;

                default:
                    this.gridLayout[data.field] = data;
                    break;
            }
        }
    },

    addExtraField:function(key) {
        // Summary:
        //    Add an extra field to the layout.
        var meta = {
            label:    ' ',
            key:      key,
            readOnly: true
        };

        var field       = this._initData(meta);
        field.width     = '20px';
        field.styles    = 'vertical-align: middle';
        field.filterKey = null;

        this.gridLayout[key] = field;
    },

    /************* Private functions *************/

    _initData:function(meta) {
        // Summary:
        //    Set the default data for add to the layout.
        return {
            name:          meta['label'],
            field:         meta['key'],
            styles:        '',
            type:          dojox.grid.cells.Cell,
            width:         'auto',
            options:       null,
            values:        null,
            promptMessage: null,
            constraint:    null,
            formatter:     null,
            editable:      (meta['readOnly']) ? false : true,
            filterKey:     meta['key'],
            filterLabel:   meta['label'],
            filterType:    'text',
            filterOptions: null
        };
    },

    _setRangeValues:function(data) {
        // Summary:
        //    Convert server range data into layout range data.
        this._opts      = [];
        this._vals      = [];
        this._maxLength = data['key'].length;
        for (var j in data['range']){
            this._vals.push(data['range'][j]['id']);
            this._opts.push(data['range'][j]['name']);
            if (data['range'][j]['name'].length > this._maxLength) {
                this._maxLength = data['range'][j]['name'].length;
            }
        }
    },

    _setRatingValues:function(data) {
        // Summary:
        //    Convert server range data into layout range data for rating fields.
        this._max       = parseInt(data['range']['id']);
        this._opts      = [];
        this._vals      = [];
        this._maxLength = data['key'].length;
        for (var j = 1; j <= this._max; j++){
            this._vals.push(j);
            this._opts.push(j);
        }
    }
});

dojo.declare("phpr.Grid.Filter", null, {
    // Summary:
    //    Display the filters and a form for create/delete them.
    _fields:          [],
    _rules:           [],
    _layout:          null,
    _module:          null,
    _deleteAll:       null,
    _displayDiv:      null,
    _fieldSelect:     null,
    _filterCookie:    null,
    _formWidget:      null,
    _inputDiv:        null,
    _ruleSelect:      null,
    _filterSeparator: ';',

    constructor:function(module) {
        // Summary:
        //    Create a new filter manager, one per module.
        this._module      = module;
        this._fields      = [];
        this._rules       = [];
        this._deleteAll   = null;
        this._displayDiv  = null;
        this._fieldSelect = null;
        this._formWidget  = null;
        this._inputDiv    = null;
        this._layout      = null;
        this._operatorDiv = null;
        this._ruleSelect  = null;

        this._rules = {
            'like':       phpr.nls.get('Filter_like_rule'),
            'notLike':    phpr.nls.get('Filter_not_like_rule'),
            'equal':      phpr.nls.get('Filter_equal_rule'),
            'notEqual':   phpr.nls.get('Filter_not_equal_rule'),
            'major':      phpr.nls.get('Filter_major_rule'),
            'majorEqual': phpr.nls.get('Filter_major_equal_rule'),
            'minor':      phpr.nls.get('Filter_minor_rule'),
            'minorEqual': phpr.nls.get('Filter_minor_equal_rule'),
            'begins':     phpr.nls.get('Filter_begins_rule'),
            'ends':       phpr.nls.get('Filter_ends_rule')
        };
    },

    init:function() {
        // Summary:
        //    Init the class for a new item.
        // Set cookies urls
        if (phpr.isGlobalModule(phpr.module)) {
            var getHashForCookie = phpr.module;
        } else {
            var getHashForCookie = phpr.module + '.' + phpr.currentProjectId;
        }
        this._filterCookie = getHashForCookie + '.filters';
    },

    setFilterQuery:function() {
        // Summary:
        //    Make the POST values of the filters.
        var filterData = [];
        var filters    = this._getFilters();

        for (var f in filters) {
            var data = filters[f].split(this._filterSeparator, 4);
            filterData.push({
                operator: data[0],
                field:    (data[1] == 'gridId') ? 'id' : data[1],
                rule:     data[2],
                value:    data[3]
            });
        }
        return dojo.toJson(filterData);
    },

    getLayout:function() {
        // Summary:
        //    Create the layout for a module only one time.
        // Description:
        //    Create a form with 2 rows, one for show the fields, and other for show an empty string.
        //    Create a div for display the filters and the "delete all" button.
        if (!this._layout) {
            // New form
            this._formWidget = new dijit.form.Form({
                id:       'filterForm-' + this._module,
                name:     'filterForm-' + this._module,
                onSubmit: function() {
                    return false;
                }
            });

            // Table
            var table           = dojo.doc.createElement('table');
            table.style.padding = '5px 5px 3px 5px';
            var row             = table.insertRow(table.rows.length);
            row.id              = 'filterRow-' + this._module;

            // Label
            var cell        = row.insertCell(0);
            var label       = document.createElement('label');
            label.innerHTML = phpr.nls.get('Add a filter');
            cell.appendChild(label);

            // Space
            var cell       = row.insertCell(1);
            cell.innerHTML = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

            // Operators
            var cell          = row.insertCell(2);
            this._operatorDiv = document.createElement('div');
            var select        = new phpr.Form.FilteringSelect({
                id:             'filterOperator-' + this._module,
                name:           'filterOperator-' + this._module,
                required:       false,
                disabled:       false,
                autoComplete:   false,
                store:          new dojo.data.ItemFileWriteStore({data: {
                    identifier: 'id',
                    label:      'name',
			        items:      [
                        {id: 'AND', name: phpr.nls.get('Filter_AND')},
                        {id: 'OR',  name: phpr.nls.get('Filter_OR')}
                    ]}}),
                searchAttr:     'name',
                value:          'AND',
                invalidMessage: '',
                style:          'width: 70px;'
            });
            this._operatorDiv.appendChild(select.domNode);
            cell.appendChild(this._operatorDiv);

            // Space
            var cell       = row.insertCell(3);
            cell.innerHTML = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

            // Fields
            var cell          = row.insertCell(4);
            var node          = document.createElement('div');
            this._fieldSelect = new phpr.Form.FilteringSelect({
                id:             'filterField-' + this._module,
                name:           'filterField-' + this._module,
                required:       false,
                disabled:       false,
                autoComplete:   false,
                store:          new dojo.data.ItemFileWriteStore({data: {
                    identifier: 'id',
                    label:      'name',
			        items:      []}}),
                searchAttr:     'name',
                invalidMessage: '',
                onChange:       dojo.hitch(this, '_changeInputAndRule')
            });
            node.appendChild(this._fieldSelect.domNode);
            cell.appendChild(node);

            // Space
            var cell       = row.insertCell(5);
            cell.innerHTML = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

            // Rule
            var cell  = row.insertCell(6);
            var node  = document.createElement('div');
            var range = [];
            for (var i in this._rules) {
                range.push({id: i, name: this._rules[i]});
            }
            this._ruleSelect = new phpr.Form.FilteringSelect({
                id:             'filterRule-' + this._module,
                name:           'filterRule-' + this._module,
                required:       false,
                disabled:       false,
                autoComplete:   false,
                store:          new dojo.data.ItemFileWriteStore({data: {
                    identifier: 'id',
                    label:      'name',
			        items:      range}}),
                searchAttr:     'name',
                invalidMessage: '',
                style:          'width: 120px;'
            });
            node.appendChild(this._ruleSelect.domNode);
            cell.appendChild(node);

            // Space
            var cell       = row.insertCell(7);
            cell.innerHTML = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

            // Input
            var cell       = row.insertCell(8);
            this._inputDiv = new dijit.layout.ContentPane({}, cell);

            // Space
            var cell       = row.insertCell(9);
            cell.innerHTML = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

            // Action
            var cell   = row.insertCell(10);
            var node   = document.createElement('div');
            var params = {
                id:        'newFilter-' + this._module,
                label:     phpr.nls.get('OK'),
                iconClass: 'tick',
                type:      'button',
                alt:       phpr.nls.get('OK'),
                baseClass: 'positive',
                onClick:   dojo.hitch(this, '_submitFilterForm')
            };
            var addButton = new dijit.form.Button(params);
            node.appendChild(addButton.domNode);
            cell.appendChild(node);

            // Second Row for empty values
            var row = table.insertRow(table.rows.length);
            row.id  = 'filterRowEmpty-' + this._module;

            // Label
            var cell = row.insertCell(0);
            cell.setAttribute('colspan', 10);

            var label       = document.createElement('label');
            label.innerHTML = phpr.nls.get('Please, delete some filters for get a correct result set.');
            cell.appendChild(label);

            // Add the table to the form
            this._formWidget.domNode.appendChild(table);

            // Div for display the current filters
            this._displayDiv = new dijit.layout.ContentPane({
                id:        'filterDisplayDiv-' + this._module,
                baseClass: 'filterDisplayDiv'
            }, document.createElement('div'));

            // Div for display the Delete ALL button
            var displayDelete = document.createElement('div');
            displayDelete.className = 'filterDisplayDelete';
            var params        = {
                label:     phpr.nls.get('Delete all'),
                showLabel: true,
                baseClass: 'positive',
                iconClass: 'cross',
                disabled:  false,
                style:     'margin-left: 10px;',
                onClick:   dojo.hitch(this, '_deleteFilter', ['all'])
            };
            this._deleteAll = new dijit.form.Button(params);
            displayDelete.appendChild(this._deleteAll.domNode);

            // Add all to the layout
            this._layout = document.createElement('div');
            this._layout.style.width  = 'auto';
            this._layout.style.height = 'auto';
            this._layout.appendChild(this._formWidget.domNode);
            this._layout.appendChild(this._displayDiv.domNode);
            this._layout.appendChild(this._deleteAll.domNode);
        }

        return this._layout;
    },

    drawFilters:function(fields) {
        // Summary:
        //    Draw all the used filters.
        // Description:
        //    Display each used filter with the user translation and a button for delete it.
        //    If there are not fields, show just the empty row with the message.
        //    If there are fields, show the row with the normal form.
        //    There is a div per item for show the filters, so if already exists, use it,
        //    and hide all the other existing divs of other items.
        // Show form-row or empty-row?
        var haveFields = false;
        this._fields   = fields;
        var filters    = this._getFilters();

        // Check for fields, show form or empty row depend if there are fields or not
        for (var i in this._fields) {
            haveFields = true;
            break;
        }
        if (!haveFields) {
            dojo.byId('filterRowEmpty-' + this._module).style.display = (dojo.isIE) ? 'block' : 'table-row';
            dojo.byId('filterRow-' + this._module).style.display      = 'none';
        } else {
            if (!this._fieldSelect.store._itemsByIdentity) {
                // The select is empty, update the store with the fields.
                var range = [];
                for (var i in this._fields) {
                    range.push({id: this._fields[i].key, name: this._fields[i].label});
                }
                this._fieldSelect.store = new dojo.data.ItemFileWriteStore({data: {
                    identifier: 'id',
                    label:      'name',
			        items:      range}});
            }
            dojo.byId('filterRowEmpty-' + this._module).style.display = 'none';
            dojo.byId('filterRow-' + this._module).style.display      = (dojo.isIE) ? 'block' : 'table-row';
        }

        // Hide all the existing display for this module
        dojo.forEach(this._displayDiv.getChildren(), function(item) {
            dojo.style(item.domNode, 'display', 'none');
        });

        // More than one filters? => show the operator
        if (filters.length > 0) {
            dojo.style(this._operatorDiv, 'display', 'inline');
        } else {
            dojo.style(this._operatorDiv, 'display', 'none');
        }

        // Create a new div for the current module-projectId,
        // or use the old one.
        if (phpr.isGlobalModule(phpr.module)) {
            var id = 'displayFor-' + phpr.module;
        } else {
            var id = 'displayFor-' + phpr.module + '-' + phpr.currentProjectId;
        }
        var displayNode = dijit.byId(id);
        if (!displayNode) {
            var displayNode = new dijit.layout.ContentPane({
                id: id
            }, document.createElement('div'));
            this._displayDiv.domNode.appendChild(displayNode.domNode);
        }

        // Fill the display with the filters
        if (dijit.byId(id).get('content') == '' && filters.length > 0) {
            for (var i in filters) {
                var data = filters[i].split(this._filterSeparator, 4);
                if (data[0] && data[1] && data[2] && data[3]) {
                    this._addDisplayFilter(displayNode.domNode, data, i);
                }
            }
        }
        displayNode.domNode.style.display = 'inline';

        // Show/hide 'delete all' button
        if (filters.length == 0) {
            this._deleteAll.domNode.style.display = 'none';
        } else {
            this._deleteAll.domNode.style.display = 'inline';
        }

        // Toggle the filter box
        var filterBoxNode = dojo.byId('gridFiltersBox-' + this._module);
        if (filterBoxNode.style.height == '0px' && filters.length > 0) {
            // Closed div => Only open div if there is any filter
            dijit.byId('gridFiltersBox-' + this._module).toggle();
        } else if (filterBoxNode.style.height != '0px' && filters.length == 0) {
            // Open div => Close if there are not filters
            dijit.byId('gridFiltersBox-' + this._module).toggle();
        }
    },

    /************* Private functions *************/

    _getFilters:function() {
        // Summary:
        //    Returns the filters saved in the cookie.
        // Description:
        //    Get the saved filters and clean the array for return only valid filters.
        var cookie = dojo.cookie(this._filterCookie);
        if (cookie == undefined) {
            var filters = [];
        } else {
            var filters = cookie.split(',');
            for (var i in filters) {
                var data  = filters[i].split(this._filterSeparator, 4);
                if (!data[0] || !data[1] || !data[2] || !data[3]) {
                    filters.splice(i, 1);
                }
            }
        }

        return filters;
    },

    _addDisplayFilter:function(content, data, filterId) {
        // Summary:
        //    Create a display for the filter.
        // Description:
        //    Append a display with operator-field-rule-value to the content.
        // Set operator
        if (filterId == 0) {
            var filterOperator = '';
        } else {
            var filterOperator = data[0];
        }

        // Use cookie values
        var filterField = data[1];
        var filterRule  = this._rules[data[2]];
        var filterValue = data[3];

        if (this._fields[data[1]]) {
            // If the field exists, update label and value
            filterField = this._fields[data[1]].label;
            switch(this._fields[data[1]].type) {
                case 'selectbox':
                    for (var o in this._fields[data[1]].options) {
                        if (this._fields[data[1]].options[o].id == data[3]) {
                            filterValue = this._fields[data[1]].options[o].name;
                            break;
                        }
                    }
                    break;
                case 'date':
                    filterValue = new Date(phpr.Date.isoDateTojsDate(filterValue));
                    filterValue = dojo.date.locale.format(filterValue, {datePattern: 'yyyy-MM-dd', selector: 'date'});
                    break;
                case 'time':
                    filterValue = new Date(phpr.Date.isoTimeTojsDate(filterValue));
                    filterValue = dojo.date.locale.format(filterValue, {datePattern: 'HH:mm', selector: 'time'});
                    break;
                default:
                    break;
            }
        }

        // Operator
        var operator       = document.createElement('div');
        operator.className = 'displayOperator';
        if (filterOperator) {
            operator.innerHTML = filterOperator;
        } else {
            operator.innerHTML = '';
        }
        content.appendChild(operator);

        // Display content
        var display       = document.createElement('div');
        display.className = 'displayFilter';
        content.appendChild(display);

        // Left space
        var left       = document.createElement('div');
        left.className = 'displayLeftFilter';
        left.innerHTML = '&nbsp;';
        display.appendChild(left);

        // Field display
        var field = document.createElement('div');
        field.className = 'displayFilerField';
        field.innerHTML = filterField;
        display.appendChild(field);

        // Rule display
        var rule       = document.createElement('div');
        rule.className = 'displayFilerRule';
        rule.innerHTML = '<i>' + filterRule + '</i>';
        display.appendChild(rule);

        // Value display
        var value = document.createElement('div');
        value.className = 'displayFilerValue';
        value.innerHTML = '<b>' + filterValue + '</b>';
        display.appendChild(value);

        // Delete button
        var button       = document.createElement('div');
        button.className = 'displayFilerButton';
        var link = document.createElement('a');
        link.style.textDecoration = 'none';
        link.setAttribute('href', 'javascript:void(0)');
        dojo.connect(link, 'onclick', dojo.hitch(this, '_deleteFilter', [filterId]));
        link.innerHTML = '<span class="closeButton close">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>';
        button.appendChild(link);
        display.appendChild(button);

        // Right space
        var right       = document.createElement('div');
        right.className = 'displayRightFilter';
        right.innerHTML = '&nbsp;';
        display.appendChild(right);
    },

    /************* Private events *************/

    _changeInputAndRule:function() {
        // Summary:
        //    Change the rule and input field type depend on the selected field type.
        var rulesOptions = [];
        var field        = dijit.byId('filterField-' + this._module).get('value');

        // Field don't exists? return
        if (!this._fields[field]) {
            return;
        }

        // Set params
        var id     = 'filterValue_' + field + '-' + this._module;
        var params = {
            id:             id,
            name:           'filterValue-' + this._module,
            required:       false,
            disabled:       false,
            value:          '',
            invalidMessage: ''
        }

        // Set disable and hide all the input fields
        dojo.forEach(this._inputDiv.getChildren(), function(item) {
            item.set('disabled', true);
            dojo.style(item.domNode, 'display', 'none');
        });

        // Create a new input field if not exists yet
        switch(this._fields[field].type) {
            case 'selectbox':
                var widget = dijit.byId(id);
                if (!widget) {
                    var range = [];
                    for (var j in this._fields[field].options) {
                        range.push({id: this._fields[field].options[j].id, name: this._fields[field].options[j].name});
                    }
                    params.autoComplete = false;
                    params.searchAttr   = 'name';
                    params.store        = new dojo.data.ItemFileWriteStore({data: {
                        identifier: 'id',
                        label:      'name',
    			        items:      range}})

                    var widget = new phpr.Form.FilteringSelect(params);
                    this._inputDiv.domNode.appendChild(widget.domNode);
                }
                rulesOptions = ['equal', 'notEqual'];
                break;
            case 'date':
                var widget = dijit.byId(id);
                if (!widget) {
                    params.constraints   = {datePattern: 'yyyy-MM-dd'};
                    params.promptMessage = 'yyyy-MM-dd';

                    var widget = new phpr.Form.DateTextBox(params);
                    this._inputDiv.domNode.appendChild(widget.domNode);
                }
                rulesOptions = ['equal', 'notEqual', 'major', 'majorEqual', 'minor', 'minorEqual'];
                break;
            case 'time':
                var widget = dijit.byId(id);
                if (!widget) {
                    params.constraints = {formatLength: 'short', timePattern: 'HH:mm'};

                    var widget = new phpr.Form.TimeTextBox(params);
                    this._inputDiv.domNode.appendChild(widget.domNode);
                }
                rulesOptions = ['equal', 'notEqual'];
                break;
            case 'rating':
                var widget = dijit.byId(id);
                if (!widget) {
                    var range = [];
                    for (var j = 1; j <= this._fields[field].options; j++) {
                        range.push({id: j, name: j});
                    }
                    params.autoComplete = false;
                    params.searchAttr   = 'name';
                    params.store        = new dojo.data.ItemFileWriteStore({data: {
                        identifier: 'id',
                        label:      'name',
    			        items:      range}})

                    var widget = new phpr.Form.FilteringSelect(params);
                    this._inputDiv.domNode.appendChild(widget.domNode);
                }
                rulesOptions = ['equal', 'notEqual', 'major', 'majorEqual', 'minor', 'minorEqual'];
                break;
            default:
                var widget = dijit.byId(id);
                if (!widget) {
                    params.regExp         = phpr.regExpForFilter.getExp();
                    params.invalidMessage = phpr.regExpForFilter.getMsg();

                    var widget = new dijit.form.ValidationTextBox(params);
                    this._inputDiv.domNode.appendChild(widget.domNode);
                }
                rulesOptions = ['like', 'notLike', 'begins', 'ends', 'equal', 'notEqual',
                    'major', 'majorEqual', 'minor', 'minorEqual'];
                break;
        }

        // Set enable and show the current input field
        widget.set('disabled', false);
        dojo.style(widget.domNode, 'display', 'inline-block');

        // Update the store of the rule select
        var range = [];
        range.push({id: '', name: ''});
        for (var i in rulesOptions) {
            range.push({id: rulesOptions[i], name: this._rules[rulesOptions[i]]});
        }
        this._ruleSelect.store = new dojo.data.ItemFileWriteStore({data: {
            identifier: 'id',
            label:      'name',
	        items:      range}});
        this._ruleSelect.set('value', '');
    },

    _submitFilterForm:function() {
        // Summary:
        //    Submit the data for send it to the server.
        // Description:
        //    Process the operator, field, value and rule for send them.
        //    Add the filter to the cookie and make a new request to the server.
        if (!this._formWidget.isValid()) {
            this._formWidget.validate();
            return false;
        } else {
            var sendData = this._formWidget.get('value');

            var field = sendData['filterField-' + this._module];
            var value = sendData['filterValue-' + this._module];
            var rule  = sendData['filterRule-' + this._module];

            if (!field || !value || !rule) {
                return;
            }
        }

        if (field.indexOf('_forDate') > 0) {
            // Convert date
            sendData['filterValue'] = phpr.Date.getIsoDate(value);
            sendData['filterField'] = field.replace('_forDate', '');
        } else if (field.indexOf('_forTime') > 0) {
            // Convert time
            sendData['filterValue'] = phpr.Date.getIsoTime(value);
            sendData['filterField'] = field.replace('_forTime', '');
        } else {
            // Check for other date or times values
            if (this._fields[field]) {
                sendData['filterField'] = field;
                switch(this._fields[field].type) {
                    case 'date':
                        sendData['filterValue'] = phpr.Date.getIsoDate(value);
                        break;
                    case 'time':
                        sendData['filterValue'] = phpr.Date.getIsoTime(value);
                        break;
                    default:
                        sendData['filterValue'] = value;
                        break;
                }
            }
        }

        var data = [sendData['filterOperator-' + this._module] || 'AND', sendData['filterField'], rule,
            sendData['filterValue']];

        this._addFilter(data);

        dojo.publish(phpr.module + '.gridProxy', ['sendFilterRequest']);
    },

    _addFilter:function(data) {
        // Summary:
        //    Add a new filter into the cookie.
        var filters   = this._getFilters();
        var newFilter = data.join(this._filterSeparator);

        // Don't save the same filter two times
        var found = 0;
        for (var i in filters) {
            if (filters[i] === newFilter) {
                found = 1;
                break;
            }
        }
        if (!found) {
            filters.push(newFilter);
        }

        this._saveFilterCookie(filters);
    },

    _saveFilterCookie:function(filters) {
        // Summary:
        //    Save the filters to the cookie.
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

        // Delete all the displayed filters
        if (phpr.isGlobalModule(phpr.module)) {
            var id = 'displayFor-' + phpr.module;
        } else {
            var id = 'displayFor-' + phpr.module + '-' + phpr.currentProjectId;
        }
        dijit.byId(id).set('content', '');
    },

    _deleteFilter:function(index) {
        // Summary:
        //    Delete a filter and make a new request to the server.
        var filters = this._getFilters();
        if (index == 'all') {
            filters = [];
        } else {
            filters.splice(index, 1);
        }

        this._saveFilterCookie(filters);

        dojo.publish(phpr.module + '.gridProxy', ['sendFilterRequest']);
    }
});
