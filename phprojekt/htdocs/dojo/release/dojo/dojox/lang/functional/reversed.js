/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.lang.functional.reversed"]||(dojo._hasResource["dojox.lang.functional.reversed"]=!0,dojo.provide("dojox.lang.functional.reversed"),dojo.require("dojox.lang.functional.lambda"),function(){var e=dojo,f=dojox.lang.functional;e.mixin(f,{filterRev:function(a,c,d){typeof a=="string"&&(a=a.split(""));for(var d=d||e.global,c=f.lambda(c),b=[],g,h=a.length-1;h>=0;--h)g=a[h],c.call(d,g,h,a)&&b.push(g);return b},forEachRev:function(a,c,d){typeof a=="string"&&(a=a.split(""));for(var d=
d||e.global,c=f.lambda(c),b=a.length-1;b>=0;c.call(d,a[b],b,a),--b);},mapRev:function(a,c,d){typeof a=="string"&&(a=a.split(""));var d=d||e.global,c=f.lambda(c),b=a.length,g=Array(b);b-=1;for(var h=0;b>=0;g[h++]=c.call(d,a[b],b,a),--b);return g},everyRev:function(a,c,d){typeof a=="string"&&(a=a.split(""));for(var d=d||e.global,c=f.lambda(c),b=a.length-1;b>=0;--b)if(!c.call(d,a[b],b,a))return!1;return!0},someRev:function(a,c,d){typeof a=="string"&&(a=a.split(""));for(var d=d||e.global,c=f.lambda(c),
b=a.length-1;b>=0;--b)if(c.call(d,a[b],b,a))return!0;return!1}})}());