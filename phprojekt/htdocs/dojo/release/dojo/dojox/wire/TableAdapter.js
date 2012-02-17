/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.wire.TableAdapter"]||(dojo._hasResource["dojox.wire.TableAdapter"]=!0,dojo.provide("dojox.wire.TableAdapter"),dojo.require("dojox.wire.CompositeWire"),dojo.declare("dojox.wire.TableAdapter",dojox.wire.CompositeWire,{_wireClass:"dojox.wire.TableAdapter",constructor:function(){this._initializeChildren(this.columns)},_getValue:function(a){if(!a||!this.columns)return a;dojo.isArray(a)||(a=[a]);var c=[],b;for(b in a){var d=this._getRow(a[b]);c.push(d)}return c},_setValue:function(){throw Error("Unsupported API: "+
this._wireClass+"._setValue");},_getRow:function(a){var c=dojo.isArray(this.columns)?[]:{},b;for(b in this.columns)c[b]=this.columns[b].getValue(a);return c}}));