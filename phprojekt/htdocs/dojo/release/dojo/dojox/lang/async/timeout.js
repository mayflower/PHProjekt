/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.lang.async.timeout"]||(dojo._hasResource["dojox.lang.async.timeout"]=!0,dojo.provide("dojox.lang.async.timeout"),function(){var e=dojo,f=dojox.lang.async.timeout;f.from=function(b){return function(){var a,c=function(){a&&(clearTimeout(a),a=null)},d=new e.Deferred(c);a=setTimeout(function(){c();d.callback(b)},b);return d}};f.failOn=function(b){return function(){var a,c=function(){a&&(clearTimeout(a),a=null)},d=new e.Deferred(c);a=setTimeout(function(){c();d.errback(b)},b);
return d}}}());