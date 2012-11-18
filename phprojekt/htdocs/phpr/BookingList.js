define([
    'dojo/_base/array',
    'dojo/_base/declare',
    'dijit/_WidgetBase',
    'dijit/_TemplatedMixin',
    'dojo/date/locale',
    'dojo/html',
    'dojo/json',
    'dojo/store/JsonRest',
    'dojo/date',
    'dojo/dom-construct'
], function(array, declare, _WidgetBase, _TemplatedMixin, locale, html, json, JsonRest, date, domConstruct) {
    var stripLeadingZero = function(s) {
        if (s.substr(0, 1) === '0') {
            return s.substr(1);
        } else {
            return s;
        }
    };

    var datetimeToJsDate = function(dt) {
        return new Date(
            dt.substr(0, 4),
            stripLeadingZero(dt.substr(5, 2)) - 1,
            stripLeadingZero(dt.substr(8, 2)),
            stripLeadingZero(dt.substr(11, 2)),
            stripLeadingZero(dt.substr(14, 2)),
            stripLeadingZero(dt.substr(17, 2))
        );
    };

    var timeToJsDate = function(t) {
        return new Date(
            0,
            0,
            0,
            stripLeadingZero(t.substr(0, 2)),
            stripLeadingZero(t.substr(3, 2)),
            stripLeadingZero(t.substr(6, 2))
        );
    };

    var BookingBlock = declare([_WidgetBase, _TemplatedMixin], {
        booking: {},

        templateString:
            '<div>' +
            '   <span data-dojo-attach-point="project"></span>' +
            '   <span data-dojo-attach-point="time"></span>' +
            '   <br/>' +
            '   <span data-dojo-attach-point="notes"></span>' +
            '</div>',

        _setBookingAttr: function (booking) {
            html.set(this.project, booking.projectId);
            var start = datetimeToJsDate(booking.startDatetime), end = timeToJsDate(booking.endTime);
            html.set(this.time, locale.format(start) + " - " + locale.format(end, {selector: "time"}));
            html.set(this.notes, booking.notes);
        }
    });

    var DayBlock = declare([_WidgetBase, _TemplatedMixin], {
        day: new Date(),
        bookings: [],

        templateString:
            '<div>' +
            '   <div data-dojo-attach-point="header"></div>' +
            '   <div data-dojo-attach-point="body"></div>' +
            '</div>',

        _setDayAttr: function(day) {
            html.set(this.header, day);
        },

        _setBookingsAttr: function(bookings) {
            array.forEach(bookings, dojo.hitch(this, function(b) {
                var widget = new BookingBlock({booking: b});
                widget.placeAt(this.body);
                this.own(widget);
            }));
        }
    });

    return declare(_WidgetBase, {
        store: new JsonRest({
            target: '/index.php/Timecard/Timecard'
        }),

        date: new Date(),

        buildRendering: function() {
            this.domNode = domConstruct.create('div');
        },

        _setStoreAttr: function(store) {
            this._update();
        },

        _setDateAttr: function(date) {
            this._update();
        },

        _update: function() {
            this.store.query(
                {filter: this._getQueryString()},
                {sort: [{attribute: "startDatetime", descending: false}]}
            ).then(dojo.hitch(this, function(data) {
                var bookingsByDay = this._partitionBookingsByDay(data);

                for (var day in bookingsByDay) {
                    var widget = new DayBlock({day: day, bookings: bookingsByDay[day]});
                    widget.placeAt(this.domNode);
                    this.own(widget);
                }
            }));
        },

        _getQueryString: function() {
            var monthStart = new Date(this.date.getFullYear(), this.date.getMonth(), 1);
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
                var start = datetimeToJsDate(b.startDatetime),
                    day = new Date(
                    start.getFullYear(),
                    start.getMonth(),
                    start.getDate()
                );
                partitions[day] = partitions[day] || [];
                partitions[day].push(b);
            });
            return partitions;
        }
    });
});
