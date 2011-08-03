/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.lang.aspect.timer"]||(dojo._hasResource["dojox.lang.aspect.timer"]=!0,dojo.provide("dojox.lang.aspect.timer"),function(){var c=dojox.lang.aspect,d=0,b=function(a){this.name=a||"DojoAopTimer #"+ ++d;this.inCall=0};dojo.extend(b,{before:function(){this.inCall++||console.time(this.name)},after:function(){--this.inCall||console.timeEnd(this.name)}});c.timer=function(a){return new b(a)}}());