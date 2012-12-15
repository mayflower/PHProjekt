define([
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/on',
    'dojo/topic',
    'dojo/dom-class',
    'dojo/dom-style',
    'dojo/dom-geometry',
    'dojo/dom-construct',
    'dijit/_Widget',
    'dijit/_TemplatedMixin',
    'dijit/_WidgetsInTemplateMixin',
    'dijit/_CssStateMixin',
    'dijit/MenuItem',
    'dojo/text!phpr/template/menubar/warningIcon.html',
    'dijit/DropDownMenu'
], function(declare, lang, on, topic, clazz, style, geometry, domConstruct, widget, template,
            widgetsInTemplate, cssState, MenuItem, templateString) {
    return declare([widget, template, widgetsInTemplate,  cssState], {
        baseClass: 'warningIconButton',
        templateString: templateString,
        state: 'closed',

        constructor: function() {
            topic.subscribe('notification', dojo.hitch(this, this._addNotification));
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
                left: (pos.x - 196) + 'px',
                top: (pos.h) + 'px'
            });
            clazz.add(this.domNode, 'open');
        },

        closeMenu: function() {
            clazz.remove(this.domNode, 'open');
        },

        _addNotification: function(notification) {
            this.menu.addChild(
                new MenuItem({iconClass: "warningIcon", label: notification.message}),
                0
            );
        }
    });
});
