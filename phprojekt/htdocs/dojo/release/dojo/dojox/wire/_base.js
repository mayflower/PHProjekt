/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.wire._base"])dojo._hasResource["dojox.wire._base"]=!0,dojo.provide("dojox.wire._base"),dojox.wire._defaultWireClass="dojox.wire.Wire",dojox.wire._wireClasses={attribute:"dojox.wire.DataWire",path:"dojox.wire.XmlWire",children:"dojox.wire.CompositeWire",columns:"dojox.wire.TableAdapter",nodes:"dojox.wire.TreeAdapter",segments:"dojox.wire.TextAdapter"},dojox.wire.register=function(a,b){a&&b&&(dojox.wire._wireClasses[b]||(dojox.wire._wireClasses[b]=a))},dojox.wire._getClass=
function(a){dojo.require(a);return dojo.getObject(a)},dojox.wire.create=function(a){a||(a={});var b=a.wireClass;if(b)dojo.isString(b)&&(b=dojox.wire._getClass(b));else for(var c in a)if(a[c]&&(b=dojox.wire._wireClasses[c])){dojo.isString(b)&&(b=dojox.wire._getClass(b),dojox.wire._wireClasses[c]=b);break}if(!b){if(dojo.isString(dojox.wire._defaultWireClass))dojox.wire._defaultWireClass=dojox.wire._getClass(dojox.wire._defaultWireClass);b=dojox.wire._defaultWireClass}return new b(a)},dojox.wire.isWire=
function(a){return a&&a._wireClass},dojox.wire.transfer=function(a,b,c,d){a&&b&&(dojox.wire.isWire(a)||(a=dojox.wire.create(a)),dojox.wire.isWire(b)||(b=dojox.wire.create(b)),a=a.getValue(c),b.setValue(a,d||c))},dojox.wire.connect=function(a,b,c){if(a&&b&&c){var d={topic:a.topic};if(a.topic)d.handle=dojo.subscribe(a.topic,function(){dojox.wire.transfer(b,c,arguments)});else if(a.event)d.handle=dojo.connect(a.scope,a.event,function(){dojox.wire.transfer(b,c,arguments)});return d}},dojox.wire.disconnect=
function(a){a&&a.handle&&(a.topic?dojo.unsubscribe(a.handle):dojo.disconnect(a.handle))};