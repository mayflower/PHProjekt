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
    'phpr/BookingList/DayBlock',
    'phpr/Timehelper',
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
], function(array, declare, _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, locale, html, json, JsonRest,
            date, domConstruct, domClass, DayBlock, time, lang, bookingListTemplate) {
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
