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
    'dojo/Deferred',
    'phpr/Api',
    'dojo/_base/lang',
    'dojo/Evented',
    // only used in templates
    'dijit/form/Button'
], function(array, declare, _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, locale, html, json, JsonRest,
            date, domConstruct, domClass, Deferred, api, lang, Evented) {
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

    var projectTitleForId = (function() {
        var titlesById = null;
        var def = new Deferred();

        api.getData(
            '/index.php/Project/Project',
            {query: {projectId: 1, recursive: true}}
        ).then(function(projects) {
            titlesById = {};
            array.forEach(projects, function(p) {
                titlesById[p.id] = p.title;
            });

            def.resolve(titlesById);
            def = null;
        });

        return function(id) {
            if (id == 1) {
                var d = new Deferred();
                d.resolve("Unassigned");
                return d;
            } else if (titlesById === null) {
                return def.then(function(idMap) {
                    return idMap[id];
                });
            } else {
                var d = new Deferred();
                d.resolve(titlesById[id]);
                return d;
            }
        };
    })();

    var BookingBlock = declare([_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, Evented], {
        store: null,
        booking: null,

        templateString:
            '<div class="bookingEntry">' +
            '   <span data-dojo-attach-point="project" class="project"></span>' +
            '   <span data-dojo-attach-point="time"    class="time"></span>' +
            '   <button data-dojo-type="dijit/form/Button" type="button" data-dojo-attach-point="deleteButton"' +
            '           data-dojo-props="showLabel: false, iconClass: \'dijitEditorIcon dijitEditorIconDelete\'"' +
            '           data-dojo-attach-event="onClick:_delete" class="deleteButton"></button>' +
            '   <span data-dojo-attach-point="notes" class="notes"></span>' +
            '</div>',

        _setBookingAttr: function (booking) {
            projectTitleForId(booking.projectId).then(lang.hitch(this, function(title) {
                html.set(this.project, title);
            }));

            var start = datetimeToJsDate(booking.startDatetime), end = timeToJsDate(booking.endTime);
            end.setDate(start.getDate());
            end.setMonth(start.getMonth());
            end.setFullYear(start.getFullYear());

            var totalMinutes = date.difference(start, end, "minute"),
                minutes = totalMinutes % 60, hours = Math.floor(totalMinutes / 60);

            html.set(
                this.time,
                locale.format(start, {selector: 'time'}) +
                    ' - ' +
                    locale.format(end, {selector: 'time'}) +
                    ' (' + hours + 'h ' + minutes + 'm)'
            );

            html.set(this.notes, booking.notes);
        },

        _delete: function() {
            this.store.remove(this.booking.id).then(lang.hitch(this, function() {
                this.destroyRecursive();
                this.emit('delete', this.booking);
            }));
        }
    });

    var DayBlock = declare([_WidgetBase, _TemplatedMixin], {
        day: new Date(),
        bookings: [],

        // Used when creating new items
        store: null,

        templateString:
            '<div>' +
            '   <div data-dojo-attach-point="header" class="bookingBlockHeader"></div>' +
            '   <div data-dojo-attach-point="body" class="bookingBlockBody"></div>' +
            '</div>',

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
                if (date.compare(new Date(), this.day, "date") === 0) {
                    domClass.add(this.body, 'empty');
                } else {
                    this.destroyRecursive();
                }
            } else {
                domClass.remove(this.body, 'empty');
            }
        }
    });

    return declare(_WidgetBase, {
        store: new JsonRest({
            target: '/index.php/Timecard/Timecard/'
        }),

        // We default to today if date is null. If this is set to new Date() here, _update will be called twice on
        // instantiation
        date: null,

        buildRendering: function() {
            this.domNode = domConstruct.create('div', {'class': 'bookingList'});
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
                {sort: [{attribute: 'startDatetime', descending: true}]}
            ).then(lang.hitch(this, function(data) {
                var bookingsByDay = this._partitionBookingsByDay(data);

                domConstruct.empty(this.domNode);
                if (bookingsByDay.length === 0 || date.compare(new Date(), bookingsByDay[0].day, "date") !== 0) {
                    this._addDayBlock({day: new Date(), bookings: []});
                }

                array.forEach(bookingsByDay, this._addDayBlock, this);
            }));
        },

        _addDayBlock: function(params) {
            params.store = this.store;
            var widget = new DayBlock(params);
            widget.placeAt(this.domNode);
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
                var start = datetimeToJsDate(b.startDatetime),
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
