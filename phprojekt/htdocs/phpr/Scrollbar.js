define([
    'dojo/_base/declare',
    'dojo/_base/array',
    'dojo/_base/lang',
    'dojo/_base/event',
    'dojo/_base/fx',
    'dojo/dom-style',
    'dojo/dom-class',
    'dojo/dom-geometry',
    'dojo/on',
    'dojo/window',
    'dijit/_Widget',
    'dijit/_TemplatedMixin',
    'dojo/text!phpr/template/scrollbar.html'
], function(declare, array, lang, evt, fx, style, domClass, geometry, on, win, widget, template, templateString) {
    return declare([widget, template], {
        containingNode: null,
        nativeNode: null,
        contentNode: null,
        boxHeight: 0,
        contentHeight: 0,
        templateString: templateString,
        scrollingWithMouse: false,
        currentScrollPosition: 0,
        mouseOver: false,
        autoHide: false,

        buildRendering: function() {
            this.inherited(arguments);
            this.own(
                on(this.nativeNode, 'scroll', lang.hitch(this, 'onScroll')),
                on(this.containingNode, 'mouseover', lang.hitch(this, 'onMouseOver')),
                on(this.containingNode, 'mouseout', lang.hitch(this, 'onMouseOut'))
            );
        },

        startup: function() {
            this.inherited(arguments);

            if (this.autoHide) {
                this.fadeOut();
            }

            this.resize();

            this.own(
                on(this.containingNode, 'scroll', lang.hitch(this, function() {
                    this.containingNode.scrollLeft = 0;
                    this.containingNode.scrollTop = 0;
                })),
                on(this.handleNode, 'mousedown', lang.hitch(this, function(e) {
                    var startY = e.clientY;
                    var dwin = win.get(this.handleNode.ownerDocument);
                    var scrollPosition;
                    this.scrollingWithMouse = true;
                    var selectstart = on(dwin, 'selectstart', function() {
                        return false;
                    });
                    var mousemove = on(dwin, 'mousemove', lang.hitch(this, function (e) {
                        scrollPosition = (e.clientY - startY) + this.currentScrollPosition;
                        var contentPosition = scrollPosition / this.boxHeight;
                        this.nativeNode.scrollTop = this.contentHeight * contentPosition;
                    }));
                    var mouseup = on(dwin, 'mouseup', lang.hitch(this, function () {
                        this.currentScrollPosition = Math.ceil(
                            (this.nativeNode.scrollTop / this.contentHeight) * this.boxHeight
                        );
                        mousemove.remove();
                        mouseup.remove();
                        selectstart.remove();
                        if (!this.mouseOver && this.autoHide) {
                            this.fadeOut();
                        }
                        this.scrollingWithMouse = false;
                    }));
                    this.own(selectstart, mouseup, mousemove);
                    evt.stop(e);
                    return false;
                }))
            );
        },

        onMouseOver: function() {
            if (!this.mouseOver && this.autoHide) {
                this.fadeIn();
                this.mouseOver = true;
            }
        },

        onMouseOut: function() {
            if (this.mouseOver && this.autoHide) {
                this.mouseOver = false;
                if (!this.scrollingWithMouse) {
                    this.fadeOut();
                }
            }
        },

        fadeIn: function() {
            fx.fadeIn({node: this.domNode}).play();
        },

        fadeOut: function() {
            fx.fadeOut({node: this.domNode}).play();
        },

        onScroll: function(e) {
            var position = this.nativeNode.scrollTop / this.contentHeight;
            var scrollPosition = position * this.boxHeight;
            style.set(this.handleNode, {top: scrollPosition + 'px'});

            if (!this.scrollingWithMouse) {
                this.currentScrollPosition = scrollPosition;
            }
        },

        hideHandleIfNeeded: function() {
            var contentHeight = geometry.getMarginBox(this.contentNode).h;
            var containerHeight = geometry.getMarginBox(this.containingNode).h;

            if (contentHeight <= containerHeight) {
                domClass.add(this.handleNode, 'hidden');
            } else {
                domClass.remove(this.handleNode, 'hidden');
            }
        },

        resize: function() {
            this.containerHeight = geometry.getMarginBox(this.containingNode).h;
            this.handleHeight = geometry.getMarginBox(this.handleNode).h;
            this.boxHeight = this.containerHeight - this.handleHeight;
            this.contentHeight = this.contentNode.scrollHeight - geometry.getMarginBox(this.nativeNode).h;
            style.set(this.domNode, { height: this.containerHeight + 'px'});

            this.hideHandleIfNeeded();
        }
    });
});
