/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.lang.aspect.memoizer"]||(dojo._hasResource["dojox.lang.aspect.memoizer"]=!0,dojo.provide("dojox.lang.aspect.memoizer"),function(){var f=dojox.lang.aspect,h={around:function(d){var c=f.getContext(),e=c.joinPoint,b=c.instance,a;if((a=b.__memoizerCache)&&(a=a[e.targetName])&&d in a)return a[d];c=f.proceed.apply(null,arguments);if(!(a=b.__memoizerCache))a=b.__memoizerCache={};if(!(b=a[e.targetName]))b=a[e.targetName]={};return b[d]=c}},i=function(d){return{around:function(){var c=
f.getContext(),e=c.joinPoint,b=c.instance,a,g=d.apply(b,arguments);if((a=b.__memoizerCache)&&(a=a[e.targetName])&&g in a)return a[g];c=f.proceed.apply(null,arguments);if(!(a=b.__memoizerCache))a=b.__memoizerCache={};if(!(b=a[e.targetName]))b=a[e.targetName]={};return b[g]=c}}};f.memoizer=function(d){return arguments.length==0?h:i(d)}}());