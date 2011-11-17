/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.lang.async.event"]||(dojo._hasResource["dojox.lang.async.event"]=!0,dojo.provide("dojox.lang.async.event"),function(){var b=dojo,g=dojox.lang.async.event;g.from=function(e,f){return function(){var a,c=function(){a&&(b.disconnect(a),a=null)},d=new b.Deferred(c);a=b.connect(e,f,function(a){c();d.callback(a)});return d}};g.failOn=function(e,f){return function(){var a,c=function(){a&&(b.disconnect(a),a=null)},d=new b.Deferred(c);a=b.connect(e,f,function(a){c();d.errback(Error(a))});
return d}}}());