define([
    'dojo/_base/declare',
    'dojo/_base/array',
    'dojo/_base/lang',
    'dojo/dom-style',
    'dojo/dom-geometry',
    'dojo/window',
    'dojo/on',
    'dijit/_Widget',
    'dijit/_TemplatedMixin',
    'dijit/_WidgetsInTemplateMixin',
    'dojo/text!phpr/template/bookingView.html',
    'phpr/BookingsDateChooser',
    'phpr/BookingList',
    'phpr/BookingList/SummaryBlock'
], function(declare, array, lang, style, geometry, win, on, widget, template, widgetsInTemplate, templateString) {
    return declare([widget, template, widgetsInTemplate], {
        templateString: templateString,

        startup: function() {
            this.inherited(arguments);
            var w = win.get(this.domNode.ownerDocument);
            this.own(
                on(w, 'resize', lang.hitch(this, 'resize')),
                on(this.domNode, 'resize', lang.hitch(this, 'resize')),
                this.dateChooserContainer.on('dateChange', lang.hitch(this.bookingListContainer, 'set', 'date')),
                this.bookingListContainer.bookingCreator.on('create', lang.hitch(this, function(newEntry) {
                    this.bookingListContainer.set('date', newEntry.date);
                    this.dateChooserContainer.setDate(newEntry.date);
                }))
            );
        },

        resize: function() {
            var winHeight = win.getBox().h;
            array.forEach([this.bookingListContainer.domNode, this.dateChooserContainer.domNode], function(node) {
                var top = geometry.position(node).y;
                style.set(node, 'minHeight', (winHeight - top) + 'px');
            });
        }
    });
});
