define([
    'dojo/_base/declare',
    'dojo/_base/array',
    'dojo/dom-style',
    'dojo/dom-geometry',
    'dojo/window',
    'dijit/_Widget',
    'dijit/_TemplatedMixin',
    'dijit/_WidgetsInTemplateMixin',
    'dojo/text!phpr/template/bookingView.html',
    'phpr/BookingsDateChooser',
    'phpr/BookingList'
], function(declare, array, style, geometry, win, widget, template, widgetsInTemplate, templateString) {
    return declare([widget, template, widgetsInTemplate], {
        templateString: templateString,
        resize: function() {
            var winHeight = win.getBox().h;
            array.forEach([this.bookingListContainer.domNode, this.dateChooserContainer.domNode], function(node) {
                var top = geometry.position(node).y;
                style.set(node, 'min-height', (winHeight - top) + 'px');
            });
        }
    });
});
