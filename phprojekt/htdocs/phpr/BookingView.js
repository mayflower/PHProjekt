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
    'phpr/BookingList'
], function(declare, array, lang, style, geometry, win, on, widget, template, widgetsInTemplate, templateString) {
    return declare([widget, template, widgetsInTemplate], {
        templateString: templateString,

        startup: function() {
            this.inherited(arguments);
            var w = win.get(this.domNode.ownerDocument);
            this.own(
                on(w, 'onresize', lang.hitch(this, 'resize')),
                on(this.domNode, 'onresize', lang.hitch(this, 'resize')),
                on(this.dateChooserContainer, 'DateChange', lang.hitch(this, 'onDateChanged'))
            );
        },

        resize: function() {
            var winHeight = win.getBox().h;
            array.forEach([this.bookingListContainer.domNode, this.dateChooserContainer.domNode], function(node) {
                var top = geometry.position(node).y;
                style.set(node, 'minHeight', (winHeight - top) + 'px');
            });
        },

        onDateChanged: function(date) {
            this.bookingListContainer.set('date', date);
        }
    });
});
