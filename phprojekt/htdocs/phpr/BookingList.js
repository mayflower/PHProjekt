define([
    'dojo/_base/array',
    'dojo/_base/declare',
    'dijit/_WidgetBase',
    'dijit/_TemplatedMixin',
    'dijit/_WidgetsInTemplateMixin',
    'dojo/date/locale',
    'dojo/html',
    'dojo/json',
    'dojo/store/JsonRest',
    'dojo/date',
    'dojo/dom-construct',
    'dojo/dom-class',
    'phpr/BookingList/BookingBlock',
    'phpr/BookingList/BookingCreator',
    'phpr/Timehelper',
    'dojo/_base/lang',
    //templates
    'dojo/text!phpr/template/bookingList/dayBlock.html',
    'dojo/text!phpr/template/bookingList.html',
    // only used in templates
    'dijit/form/FilteringSelect',
    'dijit/form/ValidationTextBox',
    'dijit/form/Textarea',
    'dijit/form/Button',
    'dijit/form/DateTextBox',
    'dijit/form/Form',
    'phpr/DateTextBox'
], function(array, declare, _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, locale, html, json, JsonRest, 
            date, domConstruct, domClass, BookingBlock, BookingCreator, time, lang,
            dayBlockTemplate, bookingListTemplate) {
    var DayBlock = declare([_WidgetBase, _TemplatedMixin], {
        day: new Date(),
        bookings: [],

        // Used when creating new items
        store: null,

        templateString: dayBlockTemplate,

        _setDayAttr: function(day) {
            html.set(this.header, locale.format(day, {selector: 'date', formatLength: 'long'}));
            if (date.compare(new Date(), day, 'date') === 0) {
                domClass.add(this.header, 'today');
            } else {
                domClass.remove(this.header, 'today');
            }
        },

        _setBookingsAttr: function(bookings) {
            array.forEach(bookings, lang.hitch(this, function(b) {
                var widget = new BookingBlock({booking: b, store: this.store});
                widget.placeAt(this.body);
                this.own(widget);
                this.own(widget.on('delete', lang.hitch(this, this._checkEmpty)));
            }));

            this._checkEmpty();
        },

        _checkEmpty: function() {
            if (this.body.children.length === 0) {
                if (date.compare(new Date(), this.day, 'date') === 0) {
                    domClass.add(this.body, 'empty');
                } else {
                    this.destroyRecursive();
                }
            } else {
                domClass.remove(this.body, 'empty');
            }
        }
    });

    return declare([_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
        store: new JsonRest({
            target: 'index.php/Timecard/Timecard/'
        }),

        date: new Date(),

        templateString: bookingListTemplate,

        _setStoreAttr: function(store) {
            this._update();
        },

        _setDateAttr: function(date) {
            date.setDate(1);
            html.set(this.selectedDate, locale.format(date, {selector: 'date', formatLength: 'long'}));
            this.date = date;
            this._update();
        },

        _updating: false,

        _update: function() {
            if (this._updating) {
                return;
            }
            this._updating = true;

            this.store.query(
                {filter: this._getQueryString()},
                {sort: [{attribute: 'start_datetime', descending: true}]}
            ).then(lang.hitch(this, function(data) {
                var bookingsByDay = this._partitionBookingsByDay(data);

                domConstruct.empty(this.content);
                if (bookingsByDay.length === 0 || date.compare(new Date(), bookingsByDay[0].day, 'date') !== 0) {
                    this._addDayBlock({day: new Date(), bookings: []});
                }

                array.forEach(bookingsByDay, this._addDayBlock, this);
                this._updating = false;
            }));
        },

        _addDayBlock: function(params) {
            params.store = this.store;
            var widget = new DayBlock(params);
            widget.placeAt(this.content);
            this.own(widget);
        },

        _getQueryString: function() {
            var monthStart = this.date || new Date();
            monthStart = new Date(monthStart.getFullYear(), monthStart.getMonth(), 1);

            return json.stringify({
                startDatetime: {
                    '!ge': monthStart.toString(),
                    '!lt': date.add(monthStart, 'month', 1).toString()
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
                    day: new Date(day),
                    bookings: partitions[day]
                });
            }

            return ret;
        }
    });
});
