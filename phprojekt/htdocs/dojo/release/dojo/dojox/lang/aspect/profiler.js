/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.lang.aspect.profiler"]||(dojo._hasResource["dojox.lang.aspect.profiler"]=!0,dojo.provide("dojox.lang.aspect.profiler"),function(){var c=dojox.lang.aspect,b=function(a){this.args=a?[a]:[];this.inCall=0};dojo.extend(b,{before:function(){this.inCall++||console.profile.apply(console,this.args)},after:function(){--this.inCall||console.profileEnd()}});c.profiler=function(a){return new b(a)}}());