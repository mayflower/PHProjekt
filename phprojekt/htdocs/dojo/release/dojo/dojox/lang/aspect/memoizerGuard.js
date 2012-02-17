/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.lang.aspect.memoizerGuard"]||(dojo._hasResource["dojox.lang.aspect.memoizerGuard"]=!0,dojo.provide("dojox.lang.aspect.memoizerGuard"),function(){var c=dojox.lang.aspect,e=function(a){var d=c.getContext().instance,b;if(b=d.__memoizerCache)arguments.length==0?delete d.__memoizerCache:dojo.isArray(a)?dojo.forEach(a,function(a){delete b[a]}):delete b[a]};c.memoizerGuard=function(a){return{after:function(){e(a)}}}}());