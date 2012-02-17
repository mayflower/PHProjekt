/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.wire.TextAdapter"]||(dojo._hasResource["dojox.wire.TextAdapter"]=!0,dojo.provide("dojox.wire.TextAdapter"),dojo.require("dojox.wire.CompositeWire"),dojo.declare("dojox.wire.TextAdapter",dojox.wire.CompositeWire,{_wireClass:"dojox.wire.TextAdapter",constructor:function(){this._initializeChildren(this.segments);if(!this.delimiter)this.delimiter=""},_getValue:function(a){if(!a||!this.segments)return a;var b="",c;for(c in this.segments)var d=this.segments[c].getValue(a),b=this._addSegment(b,
d);return b},_setValue:function(){throw Error("Unsupported API: "+this._wireClass+"._setValue");},_addSegment:function(a,b){return b?a?a+this.delimiter+b:b:a}}));