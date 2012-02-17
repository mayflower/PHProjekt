/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.lang.functional.fold"]||(dojo._hasResource["dojox.lang.functional.fold"]=!0,dojo.provide("dojox.lang.functional.fold"),dojo.require("dojox.lang.functional.lambda"),function(){var h=dojo,f=dojox.lang.functional,i={};h.mixin(f,{foldl:function(a,d,e,c){typeof a=="string"&&(a=a.split(""));var c=c||h.global,d=f.lambda(d),b,g;if(h.isArray(a)){b=0;for(g=a.length;b<g;e=d.call(c,e,a[b],b,a),++b);}else if(typeof a.hasNext=="function"&&typeof a.next=="function")for(b=0;a.hasNext();e=
d.call(c,e,a.next(),b++,a));else for(b in a)b in i||(e=d.call(c,e,a[b],b,a));return e},foldl1:function(a,d,e){typeof a=="string"&&(a=a.split(""));var e=e||h.global,d=f.lambda(d),c,b,g;if(h.isArray(a)){c=a[0];b=1;for(g=a.length;b<g;c=d.call(e,c,a[b],b,a),++b);}else if(typeof a.hasNext=="function"&&typeof a.next=="function"){if(a.hasNext()){c=a.next();for(b=1;a.hasNext();c=d.call(e,c,a.next(),b++,a));}}else for(b in g=!0,a)b in i||(g?(c=a[b],g=!1):c=d.call(e,c,a[b],b,a));return c},foldr:function(a,
d,e,c){typeof a=="string"&&(a=a.split(""));for(var c=c||h.global,d=f.lambda(d),b=a.length;b>0;--b,e=d.call(c,e,a[b],b,a));return e},foldr1:function(a,d,e){typeof a=="string"&&(a=a.split(""));var e=e||h.global,d=f.lambda(d),c=a.length,b=a[c-1];for(c-=1;c>0;--c,b=d.call(e,b,a[c],c,a));return b},reduce:function(a,d,e){return arguments.length<3?f.foldl1(a,d):f.foldl(a,d,e)},reduceRight:function(a,d,e){return arguments.length<3?f.foldr1(a,d):f.foldr(a,d,e)},unfold:function(a,d,e,c,b){for(var b=b||h.global,
d=f.lambda(d),e=f.lambda(e),a=f.lambda(a),g=[];!a.call(b,c);g.push(d.call(b,c)),c=e.call(b,c));return g}})}());