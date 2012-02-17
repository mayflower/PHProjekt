/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.dnd.Selector"]||(dojo._hasResource["dojox.dnd.Selector"]=!0,dojo.provide("dojox.dnd.Selector"),dojo.require("dojo.dnd.Selector"),dojo.declare("dojox.dnd.Selector",dojo.dnd.Selector,{isSelected:function(a){a=dojo.isString(a)?a:a.id;return this.getItem(a)&&this.selected[a]},selectNode:function(a,c){c||this.selectNone();var b=dojo.isString(a)?a:a.id;if(this.getItem(b))this._removeAnchor(),this.anchor=dojo.byId(a),this._addItemClass(this.anchor,"Anchor"),this.selection[b]=1,this._addItemClass(this.anchor,
"Selected");return this},deselectNode:function(a){var c=dojo.isString(a)?a:a.id;this.getItem(c)&&this.selection[c]&&(this.anchor===dojo.byId(a)&&this._removeAnchor(),delete this.selection[c],this._removeItemClass(this.anchor,"Selected"));return this},selectByBBox:function(a,c,b,f,d){d||this.selectNone();this.forInItems(function(d,g){var h=dojo.byId(g);h&&this._isBoundedByBox(h,a,c,b,f)&&this.selectNode(g,!0)},this);return this},_isBoundedByBox:function(a,c,b,f,d){var a=dojo.coords(a),e;c>f&&(e=c,
c=f,f=e);b>d&&(e=b,b=d,d=e);return a.x>=c&&a.x+a.w<=f&&a.y>=b&&a.y+a.h<=d},shift:function(a,c){var b=this.getSelectedNodes();b&&b.length&&this.selectNode(this._getNodeId(b[b.length-1].id,a),c)},_getNodeId:function(a,c){for(var b=this.getAllNodes(),f=a,d=0,e=b.length;d<e;++d)if(b[d].id==a){e=Math.min(e-1,Math.max(0,d+(c?1:-1)));if(d!=e)f=b[e].id;break}return f}}));