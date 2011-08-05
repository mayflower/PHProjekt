/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.lang.aspect.tracer"]||(dojo._hasResource["dojox.lang.aspect.tracer"]=!0,dojo.provide("dojox.lang.aspect.tracer"),function(){var c=dojox.lang.aspect,b=function(a){this.method=a?"group":"log";if(a)this.after=this._after};dojo.extend(b,{before:function(){var a=c.getContext(),d=a.joinPoint,b=Array.prototype.join.call(arguments,", ");console[this.method](a.instance,"=>",d.targetName+"("+b+")")},afterReturning:function(a){var b=c.getContext().joinPoint;typeof a!="undefined"?console.log(b.targetName+
"() returns:",a):console.log(b.targetName+"() returns")},afterThrowing:function(a){console.log(c.getContext().joinPoint.targetName+"() throws:",a)},_after:function(){console.groupEnd()}});c.tracer=function(a){return new b(a)}}());