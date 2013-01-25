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
    'dojo/json',
    'dojo/NodeList-dom',
    'dijit/_WidgetBase',
    'dijit/_TemplatedMixin',
    'dijit/_WidgetsInTemplateMixin',
    'dojo/Evented',
    'phpr/Timehelper',
    'phpr/Api',
    'dojo/text!phpr/template/bookingList/bookingBlock.html'
], function(declare, lang, win, html, on, clazz, date, locale, topic, query, json, nodeList_dom, _WidgetBase,
            _TemplatedMixin, _WidgetsInTemplateMixin, Evented, time, api, templateString) {

    var unselectAll = function() {
        query('.bookingEntry.selected, .bookingEntry.confirmDeletion').removeClass('selected confirmDeletion');
    };

    on(win.doc, 'click', unselectAll);

    var defaultClickDelay = 500;

    return declare([_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, Evented], {
        store: null,
        booking: null,
        _actionTimeout: null,

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
            this._doClickAction(function() {
                unselectAll();
                clazz.add(this.domNode, 'confirmDeletion');
            });
        },

        _confirmDeletion: function(evt) {
            evt.stopPropagation();
            this._doClickAction(function() {
                this.store.remove(this.booking.id).then(undefined, function(error) {
                    topic.publish('notification', json.parse(error.responseText));
                }).always(function() {
                    topic.publish('timecard/bookingDeleted', this.booking);
                });
            });
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
            if (clazz.contains(this.domNode, 'confirmDeletion')) {
                return;
            }

            unselectAll();
            clazz.add(this.domNode, 'selected');
        },

        _edit: function() {
            this.emit('editClick');
        },

        _doClickAction: function(fun) {
            clearTimeout(this._actionTimeout);
            this._actionTimeout = setTimeout(lang.hitch(this, fun), defaultClickDelay);
        }
    });
});
