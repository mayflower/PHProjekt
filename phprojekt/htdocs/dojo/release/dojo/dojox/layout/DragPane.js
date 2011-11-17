/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.layout.DragPane"]||(dojo._hasResource["dojox.layout.DragPane"]=!0,dojo.provide("dojox.layout.DragPane"),dojo.require("dijit._Widget"),dojo.declare("dojox.layout.DragPane",dijit._Widget,{invert:!0,postCreate:function(){this.connect(this.domNode,"onmousedown","_down");this.connect(this.domNode,"onmouseleave","_up");this.connect(this.domNode,"onmouseup","_up")},_down:function(b){var a=this.domNode;b.preventDefault();dojo.style(a,"cursor","move");this._x=b.pageX;this._y=b.pageY;
if(this._x<a.offsetLeft+a.clientWidth&&this._y<a.offsetTop+a.clientHeight)dojo.setSelectable(a,!1),this._mover=this.connect(a,"onmousemove","_move")},_up:function(){dojo.setSelectable(this.domNode,!0);dojo.style(this.domNode,"cursor","pointer");this._mover&&this.disconnect(this._mover);delete this._mover},_move:function(b){var a=this.invert?1:-1;this.domNode.scrollTop+=(this._y-b.pageY)*a;this.domNode.scrollLeft+=(this._x-b.pageX)*a;this._x=b.pageX;this._y=b.pageY}}));