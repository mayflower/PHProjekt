define([
    'dojo/_base/array',
    'dojo/_base/declare',
    'dijit/_WidgetBase',
    'dijit/_TemplatedMixin',
    'dijit/_WidgetsInTemplateMixin',
    'dojo/date/locale',
    'dojo/html',
    'dojo/json',
    'dojo/when',
    'dojo/store/JsonRest',
    'dojo/store/Memory',
    'dojo/store/Observable',
    'dojo/store/Cache',
    'dojo/date',
    'dojo/dom-construct',
    'dojo/dom-class',
    'phpr/BookingList/DayBlock',
    'phpr/Timehelper',
    'phpr/JsonRestQueryEngine',
    'dojo/_base/lang',
    //templates
    'dojo/text!phpr/template/bookingList.html',
    // only used in templates
    'phpr/BookingList/BookingCreator',
    'dijit/form/FilteringSelect',
    'dijit/form/ValidationTextBox',
    'dijit/form/Textarea',
    'dijit/form/Button',
    'dijit/form/DateTextBox',
    'dijit/form/Form',
    'phpr/DateTextBox'
], function(array, declare, _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, locale, html, json, when,
            JsonRest, Memory, Observable, Cache,
            date, domConstruct, domClass, DayBlock, time, JsonRestQueryEngine, lang, bookingListTemplate) {
    return declare([_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
        store: null,

        date: new Date(),

        templateString: bookingListTemplate,

        observer: null,

        day2dayBlock: null,

        constructor: function() {
            this._setStoreAttr(new JsonRest({
                target: 'index.php/Timecard/Timecard/',
                queryEngine: JsonRestQueryEngine
            }));

            this.day2dayBlock = {};
        },

        startup: function() {
            this.inherited(arguments);
            this._update();
        },

        _setStoreAttr: function(store) {
            this.store = new Observable(new Cache(
                store,
                new Memory()
            ));

            this._update();
        },

        _setDateAttr: function(date) {
            this.date = date;
            html.set(this.selectedDate, locale.format(date, {selector: 'date', datePattern: 'MMMM yyy'}));
            this.bookingCreator.set('date', date);
            this._update();
        },

        _updating: false,

        _update: function() {
            if (this._updating || !this._started) {
                return;
            }

            this._updating = true;

            this.bookingCreator.set('store', this.store);

            if (this.observer) {
                this.observer.cancel();
                this.observer = null;
            }

            var results = this.store.query(
                {filter: this._getQueryString()},
                {sort: [{attribute: 'start_datetime', descending: true}]}
            );

            when(results, lang.hitch(this, function(data) {
                var bookingsByDay = this._partitionBookingsByDay(data);

                domConstruct.empty(this.content);

                array.forEach(bookingsByDay, this._addDayBlock, this);
                this._updating = false;
            }));

            this.observer = results.observe(lang.hitch(this, '_storeChanged'));
        },

        _storeChanged: function(object, removedFrom, insertedInto) {
            var idate = time.datetimeToJsDate(object.startDatetime);
            idate = new Date(idate.getFullYear(), idate.getMonth(), idate.getDate());

            var widget = this.day2dayBlock[idate.getTime()];
            if (!widget && removedFrom === -1 && insertedInto !== -1) {
                this._addDayBlock({day: idate});
            }
        },

        _addDayBlock: function(params) {
            params.store = this.store;
            var widget = new DayBlock(params);
            widget.on('empty', lang.hitch(this, function(day) {
                var w = this.day2dayBlock[day.getTime()];
                if (w) {
                    w.destroyRecursive();
                    delete this.day2dayBlock[day.getTime()];
                }
            }));
            widget.placeAt(this.content);
            this.own(widget);
            this.day2dayBlock[params.day.getTime()] = widget;
        },

        _getQueryString: function(start, end) {
            if (!start) {
                start = this.date || new Date();
                start = new Date(start.getFullYear(), start.getMonth(), 1);
            }
            end = end || date.add(start, 'month', 1);

            return json.stringify({
                startDatetime: {
                    '!ge': start.toString(),
                    '!lt': end.toString()
                }
            });
        },

        _partitionBookingsByDay: function(bookings) {
            var partitions = {};
            array.forEach(bookings, function(b) {
                var start = time.datetimeToJsDate(b.startDatetime),
                    day = new Date(
                    start.getFullYear(),
                    start.getMonth(),
                    start.getDate()
                );
                partitions[day] = partitions[day] || [];
                partitions[day].push(b);
            });

            var ret = [];
            for (var day in partitions) {
                ret.push({
                    day: new Date(day)
                });
            }

            return ret;
        },

        _reorderDayWidgets: function() {
            var entries = [];

            for (var timestamp in this.day2dayBlock) {
                entries.push({
                    ts: timestamp,
                    entry: this.day2dayBlock[timestamp]
                });
            }

            entries.sort(function(a, b) {
                return parseInt(b.ts, 10) > parseInt(a.ts, 10);
            });

            array.forEach(entries, function(e, index, a) {
                if (e.entry.placed === false) {
                    var w = e.entry.widget;
                    if (index === 0) {
                        w.placeAt(this.content, 'first');
                    } else if (index === a.length - 1) {
                        w.placeAt(this.content, 'last');
                    } else {
                        w.placeAt(a[index - 1].entry.widget, 'after');
                    }
                    e.entry.placed = true;
                }
            }, this);
        }
    });
});
