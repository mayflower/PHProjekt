define([
    'dojo/_base/declare',
    'dojo/_base/array',
    'dojo/window',
    'dojo/dom-style',
    'dojo/dom-geometry',
    'dijit/_Widget',
    'dijit/_TemplatedMixin',
    'phpr/BookingsDateChooser/MonthGroup',
    'phpr/Scrollbar',
    'dojo/text!phpr/template/bookingsDateChooser.html'
], function(declare, array, win, style, geometry, widget, template, MonthGroup, Scrollbar, templateString) {
    return declare([widget, template], {
        templateString: templateString,

        buildRendering: function() {
            this.inherited(arguments);
            var month = new MonthGroup({ dateObject: new Date(2012, 9)});
            month.placeAt(this.contentNode);
            var month = new MonthGroup({ dateObject: new Date()});
            month.placeAt(this.contentNode);
            month.set('selected', true);
        },

        startup: function() {
            this.inherited(arguments);
            this.fixHeight();
            this.addScrollBar();
        },

        placeAt: function() {
            this.inherited(arguments);
            this.fixHeight();
        },

        addScrollBar: function() {
            var scrollbar = new Scrollbar({
                containingNode: this.domNode,
                nativeNode: this.nativeNode,
                contentNode: this.contentNode
            });
            scrollbar.placeAt(this.domNode);
        },

        fixHeight: function() {
            var domNode = this.domNode;
            array.forEach([this.nativeNode, this.scrollNode], function(node) {
                style.set(node, {
                    height: (win.getBox().h - geometry.position(domNode).y) + 'px'
                });
            });
        }
    });
});
