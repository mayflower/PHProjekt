/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.wire.TreeAdapter"]||(dojo._hasResource["dojox.wire.TreeAdapter"]=!0,dojo.provide("dojox.wire.TreeAdapter"),dojo.require("dojox.wire.CompositeWire"),dojo.declare("dojox.wire.TreeAdapter",dojox.wire.CompositeWire,{_wireClass:"dojox.wire.TreeAdapter",constructor:function(){this._initializeChildren(this.nodes)},_getValue:function(b){if(!b||!this.nodes)return b;dojo.isArray(b)||(b=[b]);var c=[],a;for(a in b)for(var d in this.nodes)c=c.concat(this._getNodes(b[a],this.nodes[d]));
return c},_setValue:function(){throw Error("Unsupported API: "+this._wireClass+"._setValue");},_initializeChildren:function(b){if(b)for(var c in b){var a=b[c];if(a.node&&(a.node.parent=this,!dojox.wire.isWire(a.node)))a.node=dojox.wire.create(a.node);if(a.title&&(a.title.parent=this,!dojox.wire.isWire(a.title)))a.title=dojox.wire.create(a.title);a.children&&this._initializeChildren(a.children)}},_getNodes:function(b,c){var a=null;if(c.node){a=c.node.getValue(b);if(!a)return[];dojo.isArray(a)||(a=
[a])}else a=[b];var d=[],g;for(g in a){var b=a[g],f={};f.title=c.title?c.title.getValue(b):b;if(c.children){var e=[],h;for(h in c.children)e=e.concat(this._getNodes(b,c.children[h]));if(e.length>0)f.children=e}d.push(f)}return d}}));