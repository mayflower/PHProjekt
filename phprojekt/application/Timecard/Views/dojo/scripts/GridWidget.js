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
 * @category  PHProjekt
 * @package   Template
 * @copyright Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license   LGPL v3 (See LICENSE file)
 * @link      http://www.phprojekt.com
 * @since     File available since Release 6.0
 * @version   Release: 6.1.0
 * @author    Gustavo Solt <solt@mayflower.de>
 */

dojo.require('dijit.InlineEditBox');
dojo.require("phpr.Timecard.YearMonthSelector");
dojo.provide("phpr.Timecard.GridWidget");

(function() {
    var _dayOfTheMonth = function(item) {
        return parseInt(item.startDatetime.substr(8, 2), 10);
    };

    var _weekDay = function(date) {
        return dojo.date.locale.format(date, { datePattern: 'EEE', selector: 'date' });
    };

    dojo.declare('phpr.Timecard._InlineEditorBase', dijit._Widget, {
        value: '',
        _valueChanged: false,
        _editing: false,
        _editor: null,

        buildRendering: function() {
            this.inherited(arguments);
            dojo.html.set(this.domNode, this._getDisplayedValue(this.value));
            var events = {
                ondblclick: "_onDblClick",
                onclick: "_onClick",
                onmouseover: "_onMouseOver",
                onmouseout: "_onMouseOut"
            };

            for (var name in events) {
                this.connect(this.domNode, name, events[name]);
            }
        },

        onChange: function() {

        },

        _getDisplayedValue: function () {
            return this.value;
        },

        _onClick: function(e) {
            if (e && this._editing === true) {
                dojo.stopEvent(e);
            }
        },

        _onDblClick: function() {
            if (this._editing === true) {
                return;
            }

            this._editing = true;

            this._insertEditor();
        },

        _onMouseOver: function() {
            if (this._editing === false) {
                dijit.showTooltip(phpr.nls.get("Double click to edit"), this.domNode, 'above');
            }
        },

        _onMouseOut: function() {
            dijit.hideTooltip(this.domNode);
        },

        _close: function() {
            if (this._editor) {
                this._editor.destroyRecursive();
                this._editor = null;
            }

            dojo.html.set(this.domNode, this._getDisplayedValue(this.value));
            this._editing = false;
        },

        _cancel: function() {
            this._close();
        },

        _saveAndClose: function() {
            this._save();
            this._close();
            this._notifyOnChange();
        },

        _save: function() {
            if (!this._editing) {
                return;
            }

            var val = this._editor.get('value');
            if (val != this.value) {
                this._valueChanged = true;
            }
            this.value = dojo.trim(val);
        },

        _notifyOnChange: function() {
            if (this._valueChanged === true) {
                this._valueChanged = false;
                this.onChange(this.value);
            }
        },

        _onEditorBlur: function() {
            this._saveAndClose();
        },

        _setValueAttr: function(/*String*/ val) {
            val = dojo.trim(val);
            this.value = val;
            if (this._editing === true) {
                this._editor.set('value', val);
            } else {
                dojo.html.set(this.domNode, this._getDisplayedValue(val));
            }
        }
    });

    var _padTo2Chars = function(s) {
        s = '' + s;
        if (s.length === 1) {
            s = '0' + s;
        }
        return s;
    };

    dojo.declare('phpr.Timecard.InlineEditorText', phpr.Timecard._InlineEditorBase, {
        editorParams: null,

        _insertEditor: function() {
            dojo.html.set(this.domNode, '');
            var params = this.editorParams || {};
            params.value = this.value;
            this._editor = new dijit.form.TextBox(params, dojo.create('div', null, this.domNode));

            this._editor.startup();
            this._editor.focus();

            this.connect(this._editor, 'onBlur', '_onEditorBlur');
            this.connect(this._editor, 'onKeyPress', '_onEditorKeyPress');
        },

        _onEditorKeyPress: function(e) {
            if (e.altKey || e.ctrlKey) {
                return;
            }

            // If Enter/Esc pressed, treat as _save/cancel.
            if (e.charOrCode == dojo.keys.ESCAPE) {
                dojo.stopEvent(e);
                this._cancel();
            } else if (e.charOrCode == dojo.keys.ENTER) {
                dojo.stopEvent(e);
                this._saveAndClose();
            }
        }
    });

    dojo.declare('phpr.Timecard.InlineEditorTextarea', phpr.Timecard._InlineEditorBase, {
        maxDisplayedChars: null,
        editorParams: null,
        _placeHolderChar: '&nbsp;',

        postMixInProperties: function() {
            this.inherited(arguments);
            if (dojo.trim(this.value).length === 0) {
                this.value = this._placeHolderChar;
            }
        },

        _getDisplayedValue: function(val) {
            if (this.maxDisplayedChars !== null && this.value.length > this.maxDisplayedChars) {
                return this.value.substring(0, this.maxDisplayedChars) + '..';
            }

            return this.value;
        },

        _insertEditor: function() {
            dojo.html.set(this.domNode, '');
            var params = this.editorParams || {};
            params.value = this.value === this._placeHolderChar ? '' : this.value;
            this._editor = new dijit.form.Textarea(params, dojo.create('div', null, this.domNode));

            this._editor.startup();
            this._editor.focus();

            this.connect(this._editor, 'onBlur', '_onEditorBlur');
            this.connect(this._editor, 'onKeyPress', '_onEditorKeyPress');
        },

        _onEditorKeyPress: function(e) {
            if (e.altKey || e.ctrlKey) {
                return;
            }

            // If Enter/Esc pressed, treat as _save/cancel.
            if (e.charOrCode == dojo.keys.ESCAPE) {
                dojo.stopEvent(e);
                this._cancel();
            }
        }
    });

    dojo.declare('phpr.Timecard.InlineEditorSelect', phpr.Timecard._InlineEditorBase, {
        _getDisplayedValue: function() {
            return this._getLabel(this.value);
        },

        _insertEditor: function() {
            dojo.html.set(this.domNode, '');
            var params = this.params || {};
            this._editor = new dijit.form.Select(params, dojo.create('div', null, this.domNode));

            this._editor.startup();
            this._editor.focus();

            this.connect(this._editor, 'onBlur', '_onEditorBlur');
            this.connect(this._editor, 'onChange', '_onEditorChange');
        },

        _onEditorChange: function() {
            this._saveAndClose();
        },

        _getLabel: function(val) {
            if (!this.params || !this.params.hasOwnProperty('options')) {
                return '';
            }

            var label = '';
            dojo.some(this.params.options, function(item) {
                if (item.value === val) {
                    label = item.label;
                    return true;
                }

                return false;
            });

            return label;
        }
    });

    dojo.declare("phpr.Timecard._GridEntry", [dijit._Widget], {
        item: null,
        showDate: true,
        _dayNodes: [],
        _supportingWidgets: null,
        _doubleClickDelay: 500,
        _doubleClickTimer: null,

        constructor: function(params) {
            this._supportingWidgets = [];
            this._dayNodes = [];
            dojo.mixin(this, params);
        },

        postCreate: function() {
            this.inherited(arguments);

            dojo.forEach(
                [this.timeNode, this.durationNode, this.projectNode, this.notesNode],
                dojo.hitch(this, function(node) {
                    this.connect(node, "ondblclick", "_onDblClick");
                    this.connect(node, "onmouseover", dojo.hitch(this, "_onBookingMouseOver", node));
                    this.connect(node, "onmouseout", dojo.hitch(this, "_onBookingMouseOut", node));
                })
            );

            dojo.forEach(this._dayNodes, dojo.hitch(this, function(node) {
                this.connect(node, "onclick", "_onNewItemClick");
                this.connect(node, "ondblclick", "_onDblClick");
                this.connect(node, "onmouseover", dojo.hitch(this, "_onDayMouseOver", node));
                this.connect(node, "onmouseout", dojo.hitch(this, "_onDayMouseOut", node));
            }));
        },

        buildRendering: function() {
            this.inherited(arguments);

            if (this.showDate === false) {
                this._dayNodes.push(dojo.create('td', {colspan: "2"}, this.domNode));
            } else {
                this._dayNodes.push(dojo.create('td', null, this.domNode));
                this._dayNodes.push(dojo.create('td', null, this.domNode));
                dojo.html.set(this._dayNodes[0], '' + _weekDay(phpr.date.isoDatetimeTojsDate(this.item.startDatetime)));
                dojo.html.set(this._dayNodes[1], '' + _dayOfTheMonth(this.item));
            }

            this.timeNode = dojo.create("td", null, this.domNode);
            this.durationNode = dojo.create("td", null, this.domNode);
            this.projectNode = dojo.create("td", null, this.domNode);
            this.notesNode = dojo.create("td", null, this.domNode);

            this.connect(this.domNode, "onclick", "_onClick");
        },

        _onClick: function(evt) {
            if (evt) {
                dojo.stopEvent(evt);
            }
            clearTimeout(this._doubleClickTimer);
            this._doubleClickTimer = setTimeout(
                dojo.hitch(this, function() {
                    phpr.pageManager.modifyCurrentState({ id: this.item.id });
                }),
                this._doubleClickDelay
            );
        },

        _onNewItemClick: function(evt) {
            if (evt) {
                dojo.stopEvent(evt);
            }
            clearTimeout(this._doubleClickTimer);
            this._doubleClickTimer = setTimeout(
                dojo.hitch(this, function() {
                    var presetDate = new Date(phpr.date.isoDatetimeTojsDate(this.item.startDatetime));
                    var now = new Date();
                    presetDate.setHours(now.getHours());
                    presetDate.setMinutes(now.getMinutes());
                    phpr.pageManager.modifyCurrentState({
                        id: 0
                    }, {
                        presetValues: {
                            startDatetime: phpr.date.jsDateToIsoDatetime(presetDate)
                        }
                    });
                }),
                this._doubleClickDelay
            );
        },

        _onDblClick: function(evt) {
            if (evt) {
                dojo.stopEvent(evt);
            }
            clearTimeout(this._doubleClickTimer);
        },

        _onBookingMouseOver: function() {
            dojo.forEach(
                [this.timeNode, this.durationNode, this.projectNode, this.notesNode],
                dojo.hitch(this, function(node) {
                    dojo.addClass(node, 'cellOver');
                })
            );
        },

        _onBookingMouseOut: function() {
            dojo.forEach(
                [this.timeNode, this.durationNode, this.projectNode, this.notesNode],
                dojo.hitch(this, function(node) {
                    dojo.removeClass(node, 'cellOver');
                })
            );
        },

        _onDayMouseOver: function(node) {
            dojo.forEach(this._dayNodes, dojo.hitch(this, function(node) {
                dojo.addClass(node, 'cellOver');
            }));

            if (node) {
                dijit.showTooltip(phpr.nls.get('Click to add booking'), node, 'above');
            }
        },

        _onDayMouseOut: function(node) {
            dojo.forEach(this._dayNodes, dojo.hitch(this, function(node) {
                dojo.removeClass(node, 'cellOver');
            }));

            if (node) {
                dijit.hideTooltip(node);
            }
        }
    });

    dojo.declare("phpr.Timecard.GridEntry", phpr.Timecard._GridEntry, {
        _dayNodes: [],
        buildRendering: function() {
            this.inherited(arguments);

            if (!this.item.endTime) {
                dojo.addClass(this.domNode, 'unfinishedBooking');
            }

            this._renderTimeNode();
            dojo.html.set(this.durationNode, '' + this._duration());
            this._renderNotesNode();

            this._renderProjectNode();
        },

        _fetchProjectRange: function() {
            return phpr.MetadataStore.metadataFor('Timecard', 1).then(function(metadata) {
                for (var mdIndex in metadata) {
                    if (metadata.hasOwnProperty(mdIndex) &&
                        metadata[mdIndex].key === "projectId" &&
                        metadata[mdIndex].hasOwnProperty('range')) {
                        return metadata[mdIndex].range;
                    }
                }
                return null;
            });
        },

        onChange: function(item) {

        },

        _renderTimeNode: function() {
            this._timeNodeInline = new phpr.Timecard.InlineEditorText({
                value: '' + this._time(),
                editorParams: {
                    style: 'width: 80px;',
                    maxLength: "13"
                }
            }, dojo.create('div', null, this.timeNode));

            this.connect(this._timeNodeInline, 'onChange', '_onTimeNodeChange');

            this._supportingWidgets.push(this._timeNodeInline);
        },

        _onTimeNodeChange: function(value) {
            value = '' + value;
            var newTimes = this._parseTimeValue(value);

            if (newTimes === null) {
                this._timeNodeInline.set('value', this._time());
                return;
            }

            var newItem = dojo.clone(this.item);

            newItem.startDatetime = phpr.date.getIsoDatetime(
                phpr.date.isoDatetimeTojsDate(this.item.startDatetime),
                newTimes.startTime
            );

            if (newTimes.endTime) {
                newItem.endTime = newTimes.endTime + ':00';
            } else {
                newItem.endTime = null;
            }

            this.item = newItem;

            this._onChange();
        },

        _onChange: function() {
            this.onChange(this.item);
        },

        _parseTimeValue: function(value) {
            var re = /^((\d{1,2})(:|\.)?(\d{2}))\s*-(\s*((\d{1,2})(:|\.)?(\d{2}))?)?$/;
            var match = value.match(re);

            if (match === null) {
                return null;
            }

            var ret = {};
            ret.startTime = _padTo2Chars(match[2]) + ':' + match[4];
            if (match[7] !== undefined && match[9] !== undefined) {
                ret.endTime = _padTo2Chars(match[7]) + ':' + match[9];
            }

            return ret;
        },

        _time: function() {
            var ret = this.item.startDatetime.substr(11, 5) + ' - ';

            if (this.item.endTime) {
                ret += this.item.endTime.substr(0, 5);
            }

            return ret;
        },

        _duration: function() {
            if (!this.item.endTime) {
                return '';
            }

            var start = phpr.date.isoDatetimeTojsDate(this.item.startDatetime),
                end = new Date(start);
            end.setHours(this.item.endTime.substr(0, 2));
            end.setMinutes(this.item.endTime.substr(3, 2));

            var minutes = dojo.date.difference(start, end, 'minute');
            return _padTo2Chars('' + Math.floor(minutes / 60)) + ':' + _padTo2Chars(minutes % 60);
        },

        _renderProjectNode: function() {
            this._fetchProjectRange().then(dojo.hitch(this, '_insertProjectNode'));
        },

        _insertProjectNode: function(range) {
            if (this.destroyed) {
                return;
            }

            var options = [];

            for (var j in range) {
                var item = {
                    label: range[j].name,
                    value: '' + range[j].id
                };

                if (range[j].id == this.item.projectId) {
                    item.selected = true;
                }

                options.push(item);
            }

            this._projectNodeInline = new phpr.Timecard.InlineEditorSelect({
                options: options,
                value: '' + this.item.projectId
            }, dojo.create('div', null, this.projectNode));

            this.connect(this._projectNodeInline, 'onChange', '_onProjectSelectorChange');

            this._supportingWidgets.push(this._projectNodeInline);
        },

        _onProjectSelectorChange: function(value) {
            this._fetchProjectRange().then(dojo.hitch(this, function(range) {
                if (!this._idInProjectRange(value, range)) {
                    return;
                }

                var newItem = dojo.clone(this.item);
                newItem.projectId = value;
                this.item = newItem;
                this._onChange();
            }));
        },

        _idInProjectRange: function(id, range) {
            for (var i in range) {
                if (range[i].id == id) {
                    return true;
                }
            }

            return false;
        },

        _renderNotesNode: function() {
            this._NotesNodeInline = new phpr.Timecard.InlineEditorTextarea({
                value: this.item.notes || '',
                maxDisplayedChars: 15,
                editorParams: {
                    style: 'width: 100px;'
                }

            }, dojo.create('div', null, this.notesNode));

            this.connect(this._NotesNodeInline, 'onChange', '_onNotesChange');

            this._supportingWidgets.push(this._NotesNodeInline);
        },

        _onNotesChange: function(value) {
            value = dojo.trim(value);
            var newItem = dojo.clone(this.item);
            newItem.notes = value;
            this.item = newItem;
            this._onChange();
        }
    });

    dojo.declare("phpr.Timecard.DummyGridEntry", phpr.Timecard._GridEntry, {
        date: null,

        constructor: function(params) {
            dojo.mixin(this, params);
            this.date = this.date || new Date();
            this.dayOfTheWeek = _weekDay(this.date);
            this.dayOfTheMonth = '' + this.date.getDate();
            this._onClick = this._onNewItemClick;

            var dmover = dojo.hitch(this, this._onDayMouseOver);
            var dmout = dojo.hitch(this, this._onDayMouseOut);
            var bmover = dojo.hitch(this, this._onBookingMouseOver);
            var bmout = dojo.hitch(this, this._onBookingMouseOut);
            this._onDayMouseOver = function(node) {
                dmover(node);
                bmover();
            };
            this._onDayMouseOut = function(node) {
                dmout();
                bmout(node);
            };
            this._onBookingMouseOver = function(node) {
                bmover();
                dmover(node);
            };
            this._onBookingMouseOut = function(node) {
                bmout();
                dmout(node);
            };
        },

        _time: function() {
            return '';
        },

        _duration: function() {
            return '';
        }
    });

    dojo.declare("phpr.Timecard.GridWidget", [dijit._Widget, dijit._Templated], {
        templateString: ['<div>',
            '<div class="yearMonthSelector" dojoAttachpoint="yearMonthSelector" dojoType="phpr.Timecard.YearMonthSelector">',
            '</div>',
            '<table class="timecardGrid" dojoAttachPoint="tableNode">',
            '  <thead>',
            '    <tr>',
            '        <th colspan="2">Date</th>',
            '        <th>Time</th>',
            '        <th>Duration</th>',
            '        <th>Project</th>',
            '        <th>Notes</th>',
            '    </tr>',
            '  </thead>',
            '  <tfoot>',
            '    <tr>',
            '      <td/>',
            '      <td/>',
            '      <td style="text-align: right; padding-right: 4px;">Total:</td>',
            '      <td dojoAttachPoint="totalTime"/>',
            '      <td/>',
            '      <td/>',
            '    </tr>',
            '  </tfoot>',
            '</table>',
            '</div>'
        ].join("\n"),

        widgetsInTemplate: true,

        store: null,

        _supportingWidgets: [],
        monthStart: null,
        button: null,
        dayGroups: null,

        constructor: function() {
            this.dayGroups = {};
        },

        destroyDescendants: function() {
            var nodes = [];

            for (var i in this.dayGroups) {
                if (this.dayGroups.hasOwnProperty(i)) {
                    var group = this.dayGroups[i];
                    this.clearGroup(group);
                    nodes.push(group.groupNode);
                }
            }

            dojo.forEach(nodes, dojo.hitch(this, function(node) {
                dojo.forEach(dijit.findWidgets(node), function(widget) {
                    if (widget.destroyRecursive) {
                        widget.destroyRecursive();
                    }
                });
                dojo.destroy(node);
            }));

            this.dayGroups = {};
        },

        setYearAndMonth: function(year, month) {
            this.monthStart = new Date();
            this.monthStart.setYear(year);
            this.monthStart.setMonth(month);
            this.monthStart.setDate(1);
            this.monthStart.setHours(0);
            this.monthStart.setMinutes(0);
            this.monthStart.setSeconds(0);
            this.monthStart.setMilliseconds(0);

            this.update();
            this.updateTotalTime();
        },

        updateTotalTime: function() {
            phpr.get({
                url: "index.php/Timecard/index/totalMinutesForYearMonth",
                content: {
                    year: this.monthStart.getFullYear(),
                    month: this.monthStart.getMonth() + 1
                }
            }).then(dojo.hitch(this, function(data) {
                var minutes = data.minutes;
                this.totalTime.innerHTML = Math.floor(minutes / 60) + ":" + _padTo2Chars(minutes % 60);
            }));
        },

        buildRendering: function() {
            this.inherited(arguments);
            this.connect(this.yearMonthSelector, "onDateChange", dojo.hitch(this, this.setYearAndMonth));
        },

        update: function() {
            this.destroyDescendants();
            this.store.query({
                filter: dojo.toJson({
                    startDatetime: {
                        "!ge": this.monthStart.toString(),
                        "!lt": dojo.date.add(this.monthStart, "month", 1).toString()
                    }
                })
            }, {
                sort: [{attribute: "start_datetime", descending: false}]
            }).then(dojo.hitch(this, function(items) {
                var itemsByDay = {};
                dojo.forEach(items, function(item) {
                    var itemStart = phpr.date.isoDatetimeTojsDate(item.startDatetime).toDateString();
                    itemsByDay[itemStart] = itemsByDay[itemStart] || [];
                    itemsByDay[itemStart].push(item);
                });

                this._forEachDayBetween(
                    this.monthStart,
                    dojo.date.add(this.monthStart, "month", 1),
                    dojo.hitch(this, function(day) {
                        var dateString = day.toDateString();
                        var group = this.addDayGroup(day);
                        if (itemsByDay[dateString]) {
                            this.addRows(itemsByDay[dateString], group);
                        } else {
                            this._addDummyRow({
                                item: {
                                    startDatetime: phpr.date.jsDateToIsoDatetime(day)
                                }
                            }, group);
                        }
                    })
                );
            }));
        },

        addDayGroup: function(day) {
            var groupNode = dojo.create('tbody', null, this.tableNode, 'last');
            dojo.addClass(groupNode, 'day' + day.getDate());

            if (dojo.date.compare(day, new Date(), 'date') === 0) {
                dojo.addClass(groupNode, 'today');
            } else if (dojo.date.locale.isWeekend(day)) {
                dojo.addClass(groupNode, 'weekend');
            }

            group = this.dayGroups[phpr.date.getIsoDate(day)] = {
                groupNode: groupNode,
                entries: []
            };

            return group;
        },

        addRows: function(items, group) {
            this._addRow({item: items.shift(), showDate: true}, group);
            dojo.forEach(items, dojo.hitch(this, function(item) {
                this._addRow({item: item, showDate: false}, group);
            }));
        },

        _addRow: function(params, group) {
            var placeholder = dojo.create('tr', null, group.groupNode);

            var newRow = new phpr.Timecard.GridEntry({
                    item: params.item,
                    showDate: params.showDate
                }, placeholder);

            this.connect(newRow, 'onChange', dojo.hitch(this, '_onRowDataChange', newRow));
            this._supportingWidgets.push(newRow);

            group.entries.push(newRow);
        },

        _onRowDataChange: function(row, item) {
            phpr.loading.show();

            this.updateTotalTime();
            this.store.put(item, { override: true }).then(dojo.hitch(this, function(newData) {
                var groupIndex = phpr.date.getIsoDate(phpr.date.isoDatetimeTojsDate(newData.startDatetime));
                var group = this.dayGroups[groupIndex];
                this.removeEntryFromGroup(row, group);
                this._addRow({ item: newData }, group);
                this.sortGroup(group);
                phpr.loading.hide();
            }));
        },

        removeEntryFromGroup: function(entry, group) {
            var entryIndex = -1;
            var l = group.entries.length;
            for (var i = 0; i < l; i++) {
                var ent = group.entries[i];
                if (ent.id === entry.id) {
                    entryIndex = i;
                    break;
                }
            }

            if (entryIndex !== -1) {
                group.entries[entryIndex].destroyRecursive();
                group.entries.splice(entryIndex, 1);
            }
        },

        clearGroup: function(group) {
            dojo.forEach([].concat(group.entries), dojo.hitch(this, function(entry) {
                this.removeEntryFromGroup(entry, group);
            }));
        },

        sortGroup: function(group) {
            if (group.entries.length === 0) {
                return;
            }

            var timeToItem = {};

            var times = dojo.map(
                dojo.filter(group.entries, function(entry) {
                    return dojo.isObject(entry.item) && typeof entry.item.startDatetime !== 'undefined';
                }),
                dojo.hitch(this, function(entry) {
                    var startDate = phpr.date.isoDatetimeTojsDate(entry.item.startDatetime);
                    var startTime = startDate.getTime();
                    timeToItem[startTime] = dojo.clone(entry.item);
                    return startTime;
                })
            );

            times.sort(function(a, b) {
                return a - b;
            });

            this.clearGroup(group);

            this.addRows(dojo.map(times, function(time) {
                return timeToItem[time];
            }), group);
        },

        _addDummyRow: function(params, group) {
            var placeholder = dojo.create('tr', null, group.groupNode);
            var newRow = new phpr.Timecard.DummyGridEntry(params, placeholder);

            this._supportingWidgets.push(newRow);

            group.entries.push(newRow);
        },

        _getDatePart: function(isoDatetime) {
            return isoDatetime.substr(0, 10);
        },

        _forEachDayBetween: function(from, to, fun) {
            for (; (from.getFullYear() < to.getFullYear()) ||
                        (from.getMonth() < to.getMonth()) ||
                        (from.getDate() < to.getDate());
                    from = dojo.date.add(from, 'day', 1)) {
                fun(from);
            }
        }
    });
})();
