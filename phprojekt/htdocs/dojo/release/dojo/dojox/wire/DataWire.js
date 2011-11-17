/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.wire.DataWire"]||(dojo._hasResource["dojox.wire.DataWire"]=!0,dojo.provide("dojox.wire.DataWire"),dojo.require("dojox.wire.Wire"),dojo.declare("dojox.wire.DataWire",dojox.wire.Wire,{_wireClass:"dojox.wire.DataWire",constructor:function(){if(!this.dataStore&&this.parent)this.dataStore=this.parent.dataStore},_getValue:function(c){if(!c||!this.attribute||!this.dataStore)return c;var a=this.attribute.split("."),d;for(d in a)if(c=this._getAttributeValue(c,a[d]),!c)return;return c},
_setValue:function(c,a){if(!c||!this.attribute||!this.dataStore)return c;for(var d=c,b=this.attribute.split("."),e=b.length-1,f=0;f<e;f++)if(d=this._getAttributeValue(d,b[f]),!d)return;this._setAttributeValue(d,b[e],a);return c},_getAttributeValue:function(c,a){var d=void 0,b=a.indexOf("[");if(b>=0){var e=a.indexOf("]"),e=a.substring(b+1,e),a=a.substring(0,b);(b=this.dataStore.getValues(c,a))&&(d=e?b[e]:b)}else d=this.dataStore.getValue(c,a);return d},_setAttributeValue:function(c,a,d){var b=a.indexOf("[");
if(b>=0){var e=a.indexOf("]"),e=a.substring(b+1,e),a=a.substring(0,b),b=null;e?((b=this.dataStore.getValues(c,a))||(b=[]),b[e]=d):b=d;this.dataStore.setValues(c,a,b)}else this.dataStore.setValue(c,a,d)}}));