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
dojo.provide("phpr.Timecard.GridWidget");

(function() {
    var _dayOfTheMonth = function(item) {
        return parseInt(item.startDatetime.substr(8, 2), 10);
    };

    var _weekDay = function(date) {
        return dojo.date.locale.format(date, { datePattern: 'EEE', selector: 'date' });
    };

    var _padTo2Chars = function(s) {
        s = '' + s;
        if (s.length === 1) {
            s = '0' + s;
        }
        return s;
    };

    dojo.declare('phpr.Timecard.InlineEditBox', dijit.InlineEditBox, {
        constructor: function() {
            this._onDblClick = this._onClick;
            this._onClick = function() {};
        },

        postMixInProperties: function() {
            this.inherited(arguments);
            this.connect(this.displayNode, 'ondblclick', '_onDblClick');
        }
    });

    dojo.declare("phpr.Timecard._GridEntry", [dijit._Widget], {
        item: null,
        showDate: true,
        dayNodes: [],
        _supportingWidgets: null,

        constructor: function(params) {
            this._supportingWidgets = [];
            this.dayNodes = [];
            dojo.mixin(this, params);
        },

        buildRendering: function() {
            this.domNode = dojo.create('tr');
            dojo.addClass(this.domNode, 'dojoxGridRow');

            if (this.showDate === false) {
                this.dayNodes.push(dojo.create('td', {colspan: "2"}, this.domNode));
            } else {
                this.dayNodes.push(dojo.create('td', null, this.domNode));
                this.dayNodes.push(dojo.create('td', null, this.domNode));
            }

            this.timeNode = dojo.create("td", null, this.domNode);
            this.durationNode = dojo.create("td", null, this.domNode);
            this.projectNode = dojo.create("td", null, this.domNode);
            this.notesNode = dojo.create("td", null, this.domNode);

            dojo.forEach(
                [this.timeNode, this.durationNode, this.projectNode, this.notesNode].concat(this.dayNodes),
                function(node) {
                    dojo.addClass(node, 'dojoxGridCell');
                }
            );

            this.connect(this.domNode, "onclick", "_onClick");
            this.connect(this.domNode, "onmouseover", "_onMouseOver");
            this.connect(this.domNode, "onmouseout", "_onMouseOut");
        },

        _onClick: function() {
            phpr.pageManager.modifyCurrentState({ id: this.item.id });
        },

        _onMouseOver: function() {
            dojo.addClass(this.domNode, 'dojoxGridRowOver');
        },

        _onMouseOut: function() {
            dojo.removeClass(this.domNode, 'dojoxGridRowOver');
        }
    });

    dojo.declare("phpr.Timecard.GridEntry", phpr.Timecard._GridEntry, {
        dayNodes: [],
        buildRendering: function() {
            this.inherited(arguments);

            if (this.showDate === true) {
                dojo.html.set(this.dayNodes[0], '' + _weekDay(phpr.date.isoDatetimeTojsDate(this.item.startDatetime)));
                dojo.html.set(this.dayNodes[1], '' + _dayOfTheMonth(this.item));
            }

            this._renderTimeNode();
            dojo.html.set(this.durationNode, '' + this._duration());
            dojo.html.set(this.notesNode, dojo.isString(this.item.notes) ? this.item.notes : '');

            phpr.MetadataStore.metadataFor('Timecard', 1).then(dojo.hitch(this, this._updateProjectName));
        },

        onChange: function(item) {

        },

        _renderTimeNode: function() {
            this.timeNode = timeNodeInline = new phpr.Timecard.InlineEditBox({
                editor: dijit.form.TextBox,
                editorParams: {
                    maxLength: "13"
                },
                value: '' + this._time(),
                autoSave: true
            }, dojo.create('div', null, this.timeNode));

            this.connect(this.timeNode, 'onChange', '_onTimeNodeChange');

            this._supportingWidgets.push(this.timeNode);

        },

        _onTimeNodeChange: function(value) {
            value = '' + value;
            var newTimes = this._parseTimeValue(value);

            if (newTimes === null) {
                return;
            }

            var newItem = dojo.clone(this.item);

            newItem.startDatetime = phpr.date.getIsoDatetime(
                phpr.date.isoDatetimeTojsDate(this.item.startDatetime),
                newTimes.startTime
            );

            newItem.endTime = newTimes.endTime + ':00';

            this.item = newItem;

            this.onChange(newItem);
        },

        _parseTimeValue: function(value) {
            var re = /^(\d{1,2}:\d{2})\s*-\s*(\d{1,2}:\d{2})$/;
            var match = value.match(re);

            if (match === null) {
                return null;
            }

            var ret = {};
            ret.startTime = match[1];
            ret.endTime = match[2];

            return ret;
        },

        _time: function() {
            return this.item.startDatetime.substr(11, 5) + ' - ' + this.item.endTime.substr(0, 5);
        },

        _duration: function() {
            var start = phpr.date.isoDatetimeTojsDate(this.item.startDatetime),
                end = new Date(start);
            end.setHours(this.item.endTime.substr(0, 2));
            end.setMinutes(this.item.endTime.substr(3, 2));

            var minutes = dojo.date.difference(start, end, 'minute');
            return _padTo2Chars('' + Math.floor(minutes / 60)) + ':' + _padTo2Chars(minutes % 60);
        },

        _updateProjectName: function(metadata) {
            if (this.destroyed) {
                return;
            }
            var projectId = parseInt(this.item.projectId, 10);
            if (projectId === 1) {
                dojo.html.set(this.projectNode, '' + phpr.nls.get('Unassigned', 'Timecard'));
                return;
            }

            for (var mdIndex in metadata) {
                if (metadata.hasOwnProperty(mdIndex) && metadata[mdIndex].key === "projectId") {
                    var range = metadata[mdIndex].range;
                    dojo.some(range, dojo.hitch(this, function(rItem) {
                        if (rItem.id !== projectId) {
                            return false;
                        }

                        dojo.html.set(this.projectNode, '' + rItem.name);
                        return true;
                    }));
                    return;
                }
            }
        }

    });

    dojo.declare("phpr.Timecard.DummyGridEntry", phpr.Timecard._GridEntry, {
        date: null,

        constructor: function(params) {
            dojo.mixin(this, params);
            this.date = this.date || new Date();
            this.dayOfTheWeek = _weekDay(this.date);
            this.dayOfTheMonth = '' + this.date.getDate();
        },

        _time: function() {
            return '';
        },

        _duration: function() {
            return '';
        },

        _onClick: function() {
            var presetDate = new Date(this.date);
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
        },

        buildRendering: function() {
            this.inherited(arguments);
            if (this.showDate === true) {
                dojo.html.set(this.dayNodes[0], this.dayOfTheWeek);
                dojo.html.set(this.dayNodes[1], this.dayOfTheMonth);
            }
        }
    });

    dojo.declare("phpr.Timecard.GridWidget", [dijit._Widget, dijit._Templated], {
        templateString: ['<div>',
            '<div>',
            '   <div dojoAttachpoint="yearMonthSelector"></div>',
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

        store: null,

        _supportingWidgets: [],
        monthStart: null,
        button: null,
        childNodes: null,

        constructor: function() {
            this.childNodes = [];
        },

        destroyDescendants: function() {
            dojo.forEach(this.childNodes, dojo.hitch(this, function(node) {
                dojo.forEach(dijit.findWidgets(node), function(widget) {
                    if (widget.destroyRecursive) {
                        widget.destroyRecursive();
                    }
                });
                dojo.destroy(node);
            }));

            this.childNodes = [];
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

            if (this.button) {
                this.button.set("label", this.getYearMonthLabel(year, month));
            }
            this.update();
            this.updateTotalTime();
        },

        updateTotalTime: function() {
            dojo.xhrGet({
                url: "index.php/Timecard/index/totalMinutesForYearMonth",
                content: {
                    csrfToken: phpr.csrfToken,
                    year: this.monthStart.getFullYear(),
                    month: this.monthStart.getMonth() + 1
                }
            }).then(dojo.hitch(this, function(data) {
                var minutes = dojo.fromJson(data).minutes;
                this.totalTime.innerHTML = Math.floor(minutes / 60) + ":" + _padTo2Chars(minutes % 60);
            }));
        },

        buildRendering: function() {
            this.inherited(arguments);
            this.addYearMonthSelector();

            var date = new Date();
            this.setYearAndMonth(date.getFullYear(), date.getMonth());
        },

        addYearMonthSelector: function() {
            dojo.xhrGet({
                url: "index.php/Timecard/index/yearsAndMonthsWithEntries",
                content: {csrfToken: phpr.csrfToken}
            }).then(dojo.hitch(this, function(response) {
                var entries = dojo.fromJson(response).values;
                entries = dojo.map(entries, function(entry) {
                    return {year: entry.year, month: entry.month - 1};
                });
                entries = this.addLastMonths(entries);

                var menu = new dijit.Menu({style: "display: none;"});
                dojo.forEach(entries, dojo.hitch(this, function(entry) {
                    menu.addChild(new dijit.MenuItem({
                        label: this.getYearMonthLabel(entry.year, entry.month),
                        onClick: dojo.hitch(this, this.setYearAndMonth, entry.year, entry.month)
                    }));
                }));

                var today = new Date();
                this.button = new dijit.form.DropDownButton({
                    label: this.getYearMonthLabel(today.getFullYear(), today.getMonth()),
                    name: "yearMonthSelector",
                    dropDown: menu
                }, this.yearMonthSelector);
            }));
        },

        addLastMonths: function(entries) {
            for (var i = 0; i <= 4; i++) {
                var d = dojo.date.add(new Date(), "month", -i);
                if (!entries[i] || entries[i].month != d.getMonth() || entries[i].year != d.getFullYear()) {
                    entries.splice(i, 0, {month: d.getMonth(), year: d.getFullYear()});
                }
            }

            return entries;
        },

        getYearMonthLabel: function(year, month) {
            return year + " " + this.getMonthName(month);
        },

        getMonthName: function(month) {
            return dojo.date.locale.getNames("months", "wide")[month];
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
                        var group = this._addDayGroup(day);
                        if (itemsByDay[dateString]) {
                            this._addRow({item: itemsByDay[dateString].shift(), showDate: true}, group);
                            dojo.forEach(itemsByDay[dateString], dojo.hitch(this, function(item) {
                                this._addRow({item: item, showDate: false}, group);
                            }));
                        } else {
                            this._addDummyRow({ date: day }, group);
                        }
                    })
                );
            }));
        },

        _addDayGroup: function(day) {
            var group = dojo.create('tbody', null, this.tableNode, 'last');
            dojo.addClass(group, 'day' + day.getDate());
            this.childNodes.push(group);
            return group;
        },

        _addRow: function(params, group) {
            var placeholder = dojo.create('tr', null, group);
            var newRow = new phpr.Timecard.GridEntry({
                    item: params.item,
                    showDate: params.showDate
                }, placeholder);
            this.connect(newRow, 'onChange', '_onRowDataChange');
            this._supportingWidgets.push(newRow);
        },

        _onRowDataChange: function(item) {
            phpr.loading.show();

            this.store.put(item, { override: true }).then(dojo.hitch(phpr.loading, 'hide'));
        },

        _addDummyRow: function(params, group) {
            var placeholder = dojo.create('tr', null, group);
            this._supportingWidgets.push(
                new phpr.Timecard.DummyGridEntry(params, placeholder)
            );
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
