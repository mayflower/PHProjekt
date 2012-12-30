define([
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/_base/array',
    'dojo/on',
    'dojo/topic',
    'dojo/window',
    'dojo/dom-class',
    'dojo/dom-style',
    'dojo/dom-geometry',
    'dojo/dom-construct',
    'dojo/dom-class',
    'dijit/_Widget',
    'dijit/_TemplatedMixin',
    'dijit/_WidgetsInTemplateMixin',
    'dijit/_CssStateMixin',
    'dijit/MenuItem',
    'phpr/Api',
    'dojo/text!phpr/template/menubar/warningIcon.html',
    'dijit/DropDownMenu'
], function(declare, lang, array, on, topic, win, clazz, style, geometry, domConstruct, domClass, widget, template,
            widgetsInTemplate, cssState, MenuItem, api, templateString) {

    return declare([widget, template, widgetsInTemplate,  cssState], {
        baseClass: 'warningIconButton',
        templateString: templateString,
        state: 'closed',
        itemCount: 0,
        byTag: null,

        constructor: function() {
            this.byTag = {};
            topic.subscribe('notification', dojo.hitch(this, this._addNotification));
            topic.subscribe('notification/clear', dojo.hitch(this, this._clearNotifications));
        },

        postCreate: function() {
            this.inherited(arguments);
            this.own(on(this.domNode, 'click', lang.hitch(this, 'onClick')));
            var w = win.get(this.domNode.ownerDocument);
            w.onerror = function(err) {
                api.defaultErrorHandler(err);
            };
        },

        onClick: function() {
            this.toggleState();
        },

        toggleState: function() {
            if (this.isOpen()) {
                this.closeMenu();
            } else {
                this.openMenu();
            }
        },

        openMenu: function() {
            clazz.add(this.domNode, 'open');
            this.state = 'open';
        },

        closeMenu: function() {
            clazz.remove(this.domNode, 'open');
            this.state = 'open';
        },

        isOpen: function() {
            return this.state === 'open';
        },

        _remove: function(item) {
            this.menu.removeChild(item);
            // http://bugs.dojotoolkit.org/ticket/10296
            delete this.menu.focusedChild;
            item.destroyRecursive();
            this.itemCount -= 1;
            if (this.itemCount === 0) {
                this._markEmpty();
                this.closeMenu();
            }
        },

        _addNotification: function(notification, tag) {
            this._markNotEmpty();

            var item = new MenuItem({iconClass: "warningIcon", label: notification.message});
            item.own(item.on('click', dojo.hitch(this, this._remove, item)));

            this.menu.addChild(item, 0);
            this.itemCount += 1;

            if (this.itemCount > 0) {
                this.openMenu();
            }

            this.byTag[tag] = this.byTag[tag] || [];
            this.byTag[tag].push(item);
        },

        _clearNotifications: function(tag) {
            if (tag === undefined) {
                for (var t in this.byTag) {
                    array.forEach(this.byTag[t], lang.hitch(this, this._remove));
                }
                this.byTag = {};
            } else {
                array.forEach(this.byTag[tag], lang.hitch(this, this._remove));
                delete this.byTag[tag];
            }
        },

        dummyItem: new MenuItem(),

        _markEmpty: function() {
            this.menu.addChild(this.dummyItem);
            domClass.add(this.domNode, 'empty');
        },

        _markNotEmpty: function() {
            this.menu.removeChild(this.dummyItem);
            domClass.remove(this.domNode, 'empty');
        }
    });
});
