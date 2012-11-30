define([
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/_base/array',
    'dojo/dom-class',
    'dojo/dom-construct',
    'dojo/date',
    'dojo/date/locale',
    'dojo/html',
    'dojo/json',
    'dojo/when',
    'dojo/Evented',
    'dijit/_WidgetBase',
    'dijit/_TemplatedMixin',
    'phpr/BookingList/BookingBlock',
    'dojo/text!phpr/template/bookingList/dayBlock.html'
], function(declare, lang, array, domClass, domConst, date, locale, html, json, when, Evented, _WidgetBase,
        _TemplatedMixin, BookingBlock, templateString) {
    return declare([_WidgetBase, _TemplatedMixin, Evented], {
        day: null,
        id2widget: null,
        observer: null,

        // Used when creating new items
        store: null,

        templateString: templateString,

        constructor: function() {
            this.day = new Date();
            this.id2widget = {};
        },

        _setDayAttr: function(day) {
            html.set(this.header, locale.format(day, {selector: 'date', formatLength: 'long'}));

            if (date.compare(new Date(), day, 'date') === 0) {
                domClass.add(this.header, 'today');
            } else {
                domClass.remove(this.header, 'today');
            }

            this.day = day;
            this._update();
        },

        _setStoreAttr: function(store) {
            this.store = store;
            this._update();
        },

        _update: function() {
            this._clearObserver();

            var startDate = new Date(this.day.getTime());
            startDate.setHours(0);
            startDate.setMinutes(0);
            startDate.setSeconds(0);
            var results = this.store.query({
                filter: json.stringify({
                    startDatetime: {
                        '!ge': startDate.toString(),
                        '!lt': date.add(startDate, 'day', 1).toString()
                    }
                })
            });

            when(results, lang.hitch(this, function(bookings) {
                domConst.empty(this.body);
                array.forEach(bookings, lang.hitch(this, function(b) {
                    var widget = new BookingBlock({booking: b, store: this.store});
                    widget.placeAt(this.body);
                    this.id2widget['' + b.id] = widget;
                    this.own(widget);
                }));

                this._checkEmpty();
            }));

            this.observer = results.observe(lang.hitch(this, '_storeChanged'));
        },

        _storeChanged: function(object, removedFrom, insertedInto) {
            var widget = this.id2widget[object.id];
            if (widget && removedFrom !== -1 && insertedInto === -1) {
                widget.destroyRecursive();
                delete this.id2widget[object.id];
                this._checkEmpty();
                this._clearObserver();
                this.emit('delete', this.day);
            } else {
                this._update();
            }
        },

        _clearObserver: function() {
            if (this.observer) {
                this.observer.cancel();
                this.observer = null;
            }
        },

        _checkEmpty: function() {
            if (this.body.children.length === 0) {
                if (date.compare(new Date(), this.day, 'date') === 0) {
                    domClass.add(this.body, 'empty');
                } else {
                    this.emit('empty', this.day);
                }
            } else {
                domClass.remove(this.body, 'empty');
            }
        }
    });
});
