define([
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/_base/window',
    'dojo/html',
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
    'dojo/Evented',
    'phpr/Timehelper',
    'phpr/Api',
    'dojo/text!phpr/template/bookingList/bookingBlock.html'
], function(declare, lang, win, html, on, clazz, date, locale, topic, query, nodeList_dom, _WidgetBase, _TemplatedMixin,
    _WidgetsInTemplateMixin, Evented, time, api, templateString) {

    var unselectAll = function() {
        query('.bookingEntry.selected, .bookingEntry.confirmDeletion').removeClass('selected confirmDeletion');
    };

    on(win.doc, 'click', unselectAll);

    var defaultClickDelay = 500;

    return declare([_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, Evented], {
        store: null,
        booking: null,
        lastAction: 0,

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

        _delete: function(evt) {
            evt.stopPropagation();
            if (this.lastAction + defaultClickDelay > new Date().getTime()) {
                return;
            }
            unselectAll();
            clazz.add(this.domNode, 'confirmDeletion');
            this._setLastAction();
        },

        _confirmDeletion: function(evt) {
            evt.stopPropagation();
            if (this.lastAction + defaultClickDelay > new Date().getTime()) {
                return;
            }
            this.store.remove(this.booking.id);
            this._setLastAction();
        },

        _setLastAction: function() {
            this.lastAction = new Date().getTime();
        },

        startup: function() {
            this.inherited(arguments);
            if (this.booking && this.booking.highlight === true) {
                clazz.add(this.domNode, 'highlight');
            }
            this.own(on(this.domNode, 'click', lang.hitch(this, this._markSelected)));
        },

        _markSelected: function(evt) {
            evt.stopPropagation();
            if (this.lastAction + defaultClickDelay > new Date().getTime()) {
                return;
            }
            if (clazz.contains(this.domNode, 'confirmDeletion')) {
                return;
            }

            unselectAll();
            clazz.add(this.domNode, 'selected');
            this._setLastAction();
        },

        _edit: function() {
            this.emit('editClick');
        }
    });
});
