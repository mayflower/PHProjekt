define([
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/on',
    'dojo/topic',
    'dojo/dom-class',
    'dojo/dom-style',
    'dojo/dom-geometry',
    'dijit/_Widget',
    'dijit/_TemplatedMixin',
    'dijit/_WidgetsInTemplateMixin',
    'dijit/_CssStateMixin',
    'dojo/text!phpr/template/menubar/warningIcon.html',
    'dijit/DropDownMenu',
    'dijit/MenuItem'
], function(
    declare, lang,
    on, topic, clazz, style, geometry,
    widget, template, widgetsInTemplate, cssState,
    templateString) {
    return declare([widget, template, widgetsInTemplate,  cssState], {
        baseClass: 'warningIconButton',
        templateString: templateString,
        warnings: null,
        state: 'closed',

        constructor: function() {
            this.warnings = [];
        },

        postCreate: function() {
            this.inherited(arguments);
            this.own(on(this.domNode, 'click', lang.hitch(this, 'onClick')));
        },

        onClick: function() {
            this.toggleState();
        },

        toggleState: function() {
            if (this.state === 'closed') {
                this.state = 'open';
                this.openMenu();
            } else {
                this.state = 'closed';
                this.closeMenu();
            }
        },

        openMenu: function() {
            var pos = geometry.position(this.domNode);
            style.set(this.menu.domNode, {
                left: (pos.x - 197) + 'px',
                top: (pos.h) + 'px'
            });
            clazz.add(this.domNode, 'open');
        },

        closeMenu: function() {
            clazz.remove(this.domNode, 'open');
        }
    });
});
