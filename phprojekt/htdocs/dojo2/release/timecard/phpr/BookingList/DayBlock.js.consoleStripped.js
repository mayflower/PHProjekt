require({cache:{
'url:phpr/template/bookingList/dayBlock.html':"<div>\n    <div data-dojo-attach-point=\"header\" class=\"bookingBlockHeader\"></div>\n    <div data-dojo-attach-point=\"body, containerNode\" class=\"bookingBlockBody\"></div>\n</div>\n"}});
define("phpr/BookingList/DayBlock", [
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/_base/array',
    'dojo/dom-class',
    'dojo/date',
    'dojo/date/locale',
    'dojo/html',
    'phpr/Timehelper',
    'dijit/_WidgetBase',
    'dijit/_TemplatedMixin',
    'phpr/BookingList/BookingBlockWrapper',
    'dojo/text!phpr/template/bookingList/dayBlock.html'
], function(declare, lang, array, domClass, date, locale, html, time, _WidgetBase, _TemplatedMixin, BookingBlockWrapper,
    templateString) {
    return declare([_WidgetBase, _TemplatedMixin], {
        day: null,
        bookings: null,

        // Used when creating new items
        store: null,

        templateString: templateString,

        constructor: function() {
            this.day = new Date();
            this.bookings = [];
        },

        buildRendering: function() {
            this.inherited(arguments);
            this.bookings.sort(function(a, b) {
                var ta = time.datetimeToJsDate(a.startDatetime).getTime();
                var tb = time.datetimeToJsDate(b.startDatetime).getTime();
                return ta > tb;
            });

            array.forEach(this.bookings, function(b) {
                var widget = new BookingBlockWrapper({booking: b, store: this.store});
                widget.placeAt(this.body);
                this.own(widget);
            }, this);

            this._checkEmpty();

            html.set(this.header, locale.format(this.day, {selector: 'date', formatLength: 'long'}));

            if (date.compare(new Date(), this.day, 'date') === 0) {
                domClass.add(this.header, 'today');
            } else {
                domClass.remove(this.header, 'today');
            }
        },

        _checkEmpty: function() {
            if (this.body.children.length === 0) {
                if (date.compare(new Date(), this.day, 'date') === 0) {
                    domClass.add(this.body, 'empty');
                }
            } else {
                domClass.remove(this.body, 'empty');
            }
        }
    });
});
