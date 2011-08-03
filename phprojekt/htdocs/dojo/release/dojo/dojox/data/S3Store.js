/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.data.S3Store"]||(dojo._hasResource["dojox.data.S3Store"]=!0,dojo.provide("dojox.data.S3Store"),dojo.require("dojox.data.JsonRestStore"),dojo.require("dojox.rpc.ProxiedPath"),dojo.declare("dojox.data.S3Store",dojox.data.JsonRestStore,{_processResults:function(a){for(var a=a.getElementsByTagName("Key"),b=[],e=this,c=0;c<a.length;c++){var d={_loadObject:function(a){return function(b){delete this._loadObject;e.service(a).addCallback(b)}}(a[c].firstChild.nodeValue,d)};b.push(d)}return{totalCount:b.length,
items:b}}}));