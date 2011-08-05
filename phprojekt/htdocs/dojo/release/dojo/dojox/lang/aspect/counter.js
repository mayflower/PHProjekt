/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.lang.aspect.counter"]||(dojo._hasResource["dojox.lang.aspect.counter"]=!0,dojo.provide("dojox.lang.aspect.counter"),function(){var b=dojox.lang.aspect,a=function(){this.reset()};dojo.extend(a,{before:function(){++this.calls},afterThrowing:function(){++this.errors},reset:function(){this.calls=this.errors=0}});b.counter=function(){return new a}}());