define([
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/html',
    'dojo/window',
    'dojo/on',
    'dojo/dom-class',
    'dojo/date',
    'dojo/date/locale',
    'dojo/topic',
    'dojo/query',
    'dojo/NodeList-dom',
    'dijit/_WidgetBase',
    'dijit/_TemplatedMixin',
    'dijit/_WidgetsInTemplateMixin',
    'phpr/Timehelper',
    'phpr/Api',
    'dojo/text!phpr/template/bookingList/bookingBlock.html'
], function(declare, lang, html, win, on, clazz, date, locale, topic, query, nodeList_dom, _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin,
    time, api, templateString) {

    var unselectAll = function() {
        query('.bookingEntry.selected').removeClass('selected confirmDeletion');
    };

    on(query('body'), 'click', unselectAll);

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
            clazz.add(this.domNode, 'confirmDeletion');
            clazz.remove(this.domNode, 'selected');
        },

        _confirmDeletion: function() {
            this.store.remove(this.booking.id);
        },

        startup: function() {
            this.inherited(arguments);
            if (this.booking && this.booking.highlight === true) {
                clazz.add(this.domNode, 'highlight');
            }
            this.own(on(this.domNode, "click", lang.hitch(this, this._markSelected)));
            this.own(topic.subscribe('BookingList/removeSelection', lang.hitch(this, this._unmarkSelected)));
        },

        _markSelected: function(event) {
            if (clazz.contains(this.domNode, 'confirmDeletion')) {
                return;
            }

            unselectAll();
            clazz.add(this.domNode, 'selected');
            event.stopPropagation();
        }
    });
});
