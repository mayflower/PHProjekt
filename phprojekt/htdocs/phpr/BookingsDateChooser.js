define([
    'dojo/_base/declare',
    'dojo/_base/array',
    'dojo/_base/lang',
    'dojo/window',
    'dojo/on',
    'dojo/dom-style',
    'dojo/dom-geometry',
    'dijit/_Widget',
    'dijit/_TemplatedMixin',
    'phpr/BookingsDateChooser/MonthGroup',
    'phpr/Scrollbar',
    'dojo/text!phpr/template/bookingsDateChooser.html'
], function(declare, array, lang, win, on, style, geometry, widget, template, MonthGroup, Scrollbar, templateString) {
    return declare([widget, template], {
        templateString: templateString,
        scrollbar: null,

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
            this.resize();
            this.addScrollBar();
            this.own(on(win.get(this.domNode.ownerDocument), 'resize', lang.hitch(this, 'resize')));
        },

        placeAt: function() {
            this.inherited(arguments);
            this.resize();
        },

        addScrollBar: function() {
            this.scrollbar = new Scrollbar({
                containingNode: this.domNode,
                nativeNode: this.nativeNode,
                contentNode: this.contentNode
            });
            this.scrollbar.placeAt(this.domNode);
        },

        resize: function() {
            var domNode = this.domNode;
            array.forEach([this.nativeNode, this.scrollNode], function(node) {
                style.set(node, {
                    height: (win.getBox().h - geometry.position(domNode).y) + 'px'
                });
            });

            if (this.scrollbar && this.scrollbar.resize) {
                this.scrollbar.resize();
            }
        }
    });
});
