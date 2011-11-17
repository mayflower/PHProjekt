/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.lang.async.topic"]||(dojo._hasResource["dojox.lang.async.topic"]=!0,dojo.provide("dojox.lang.async.topic"),function(){var b=dojo,f=dojox.lang.async.topic;f.from=function(e){return function(){var a,c=function(){a&&(b.unsubscribe(a),a=null)},d=new b.Deferred(c);a=b.subscribe(e,function(){c();d.callback(arguments)});return d}};f.failOn=function(e){return function(){var a,c=function(){a&&(b.unsubscribe(a),a=null)},d=new b.Deferred(c);a=b.subscribe(e,function(a){c();d.errback(Error(arguments))});
return d}}}());