/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.wire.CompositeWire"]||(dojo._hasResource["dojox.wire.CompositeWire"]=!0,dojo.provide("dojox.wire.CompositeWire"),dojo.require("dojox.wire._base"),dojo.require("dojox.wire.Wire"),dojo.declare("dojox.wire.CompositeWire",dojox.wire.Wire,{_wireClass:"dojox.wire.CompositeWire",constructor:function(){this._initializeChildren(this.children)},_getValue:function(a){if(!a||!this.children)return a;var c=dojo.isArray(this.children)?[]:{},b;for(b in this.children)c[b]=this.children[b].getValue(a);
return c},_setValue:function(a,c){if(!a||!this.children)return a;for(var b in this.children)this.children[b].setValue(c[b],a);return a},_initializeChildren:function(a){if(a)for(var c in a){var b=a[c];b.parent=this;dojox.wire.isWire(b)||(a[c]=dojox.wire.create(b))}}}));