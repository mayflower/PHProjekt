/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.lang.functional.object"]||(dojo._hasResource["dojox.lang.functional.object"]=!0,dojo.provide("dojox.lang.functional.object"),dojo.require("dojox.lang.functional.lambda"),function(){var h=dojo,i=dojox.lang.functional,f={};h.mixin(i,{keys:function(c){var b=[],a;for(a in c)a in f||b.push(a);return b},values:function(c){var b=[],a;for(a in c)a in f||b.push(c[a]);return b},filterIn:function(c,b,a){var a=a||h.global,b=i.lambda(b),d={},e,g;for(g in c)g in f||(e=c[g],b.call(a,e,g,
c)&&(d[g]=e));return d},forIn:function(c,b,a){var a=a||h.global,b=i.lambda(b),d;for(d in c)d in f||b.call(a,c[d],d,c);return a},mapIn:function(c,b,a){var a=a||h.global,b=i.lambda(b),d={},e;for(e in c)e in f||(d[e]=b.call(a,c[e],e,c));return d}})}());