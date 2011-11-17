/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.lang.functional.listcomp"]||(dojo._hasResource["dojox.lang.functional.listcomp"]=!0,dojo.provide("dojox.lang.functional.listcomp"),function(){var e=/\bfor\b|\bif\b/gm,b=function(a){for(var b=a.split(e),a=a.match(e),f=["var r = [];"],g=[],d=0,i=a.length;d<i;){var h=a[d],c=b[++d];h=="for"&&!/^\s*\(\s*(;|var)/.test(c)&&(c=c.replace(/^\s*\(/,"(var "));f.push(h,c,"{");g.push("}")}return f.join("")+"r.push("+b[0]+");"+g.join("")+"return r;"};dojo.mixin(dojox.lang.functional,{buildListcomp:function(a){return"function(){"+
b(a)+"}"},compileListcomp:function(a){return new Function([],b(a))},listcomp:function(a){return(new Function([],b(a)))()}})}());