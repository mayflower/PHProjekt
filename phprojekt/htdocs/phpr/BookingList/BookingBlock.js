define([
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/html',
    'dojo/window',
    'dojo/on',
    'dojo/dom-class',
    'dojo/date',
    'dojo/date/locale',
    'dijit/_WidgetBase',
    'dijit/_TemplatedMixin',
    'dijit/_WidgetsInTemplateMixin',
    'phpr/Timehelper',
    'phpr/Api',
    'dojo/text!phpr/template/bookingList/bookingBlock.html'
], function(declare, lang, html, win, on, clazz, date, locale, _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin,
    time, api, templateString) {

    return declare([_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
        store: null,
        booking: null,

        templateString: templateString,

        _setBookingAttr: function (booking) {
            api.projectTitleForId(booking.projectId).then(lang.hitch(this, function(title) {
                html.set(this.project, title);
            }));

            var start = time.datetimeToJsDate(booking.startDatetime);
            var end = booking.endTime || '';
            var hasEnd = end !== '';

            var commonPrefix = locale.format(start, {selector: 'time'}) + ' - ';

            if (hasEnd) {
                end = time.timeToJsDate(booking.endTime);
                end.setDate(start.getDate());
                end.setMonth(start.getMonth());
                end.setFullYear(start.getFullYear());
                var totalMinutes = date.difference(start, end, 'minute');
                var minutes = totalMinutes % 60;
                var hours = Math.floor(totalMinutes / 60);

                html.set(
                    this.time,
                    commonPrefix +
                    locale.format(end, {selector: 'time'}) +
                    ' (' + hours + 'h ' + minutes + 'm)'
                );
            } else {
                html.set(
                    this.time,
                    commonPrefix
                );
            }


            html.set(this.notes, booking.notes);
        },

        _delete: function() {
            this.store.remove(this.booking.id);
        },

        startup: function() {
            this.inherited(arguments);
            if (this.booking && this.booking.highlight === true) {
                clazz.add(this.domNode, 'highlight');
            }
        }
    });
});
