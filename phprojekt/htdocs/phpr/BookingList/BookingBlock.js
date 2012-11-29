define([
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/html',
    'dojo/date',
    'dojo/date/locale',
    'dijit/_WidgetBase',
    'dijit/_TemplatedMixin',
    'dijit/_WidgetsInTemplateMixin',
    'dojo/Evented',
    'phpr/Timehelper',
    'phpr/Api',
    'dojo/text!phpr/template/bookingList/bookingBlock.html'
], function(declare, lang, html, date, locale, _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin,
    Evented, time, api, templateString) {

    return declare([_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, Evented], {
        store: null,
        booking: null,

        templateString: templateString,

        _setBookingAttr: function (booking) {
            api.projectTitleForId(booking.projectId).then(lang.hitch(this, function(title) {
                html.set(this.project, title);
            }));

            var start = time.datetimeToJsDate(booking.startDatetime), end = time.timeToJsDate(booking.endTime);
            end.setDate(start.getDate());
            end.setMonth(start.getMonth());
            end.setFullYear(start.getFullYear());

            var totalMinutes = date.difference(start, end, 'minute'),
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
});
