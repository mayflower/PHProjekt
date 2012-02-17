/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.lang.functional.curry"]||(dojo._hasResource["dojox.lang.functional.curry"]=!0,dojo.provide("dojox.lang.functional.curry"),dojo.require("dojox.lang.functional.lambda"),function(){var f=dojox.lang.functional,i=Array.prototype,j=function(a){return function(){var b=a.args.concat(i.slice.call(arguments,0));return arguments.length+a.args.length<a.arity?j({func:a.func,arity:a.arity,args:b}):a.func.apply(this,b)}};dojo.mixin(f,{curry:function(a,b){a=f.lambda(a);b=typeof b=="number"?
b:a.length;return j({func:a,arity:b,args:[]})},arg:{},partial:function(a){for(var b=arguments,c=b.length,d=Array(c-1),e=[],g=1,h,a=f.lambda(a);g<c;++g)h=b[g],d[g-1]=h,h===f.arg&&e.push(g-1);return function(){for(var b=i.slice.call(d,0),c=0,f=e.length;c<f;++c)b[e[c]]=arguments[c];return a.apply(this,b)}},mixer:function(a,b){a=f.lambda(a);return function(){for(var c=Array(b.length),d=0,e=b.length;d<e;++d)c[d]=arguments[b[d]];return a.apply(this,c)}},flip:function(a){a=f.lambda(a);return function(){for(var b=
arguments,c=b.length-1,d=Array(c+1),e=0;e<=c;++e)d[c-e]=b[e];return a.apply(this,d)}}})}());