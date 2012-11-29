define([
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/_base/array',
    'dojo/dom-class',
    'dojo/date',
    'dojo/date/locale',
    'dojo/html',
    'dijit/_WidgetBase',
    'dijit/_TemplatedMixin',
    'phpr/BookingList/BookingBlock',
    'dojo/text!phpr/template/bookingList/dayBlock.html'
], function(declare, lang, array, domClass, date, locale, html, _WidgetBase, _TemplatedMixin, BookingBlock,
        templateString) {
    return declare([_WidgetBase, _TemplatedMixin], {
        day: new Date(),
        bookings: [],

        // Used when creating new items
        store: null,

        templateString: templateString,

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
});
