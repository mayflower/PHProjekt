/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.lang.functional.scan"]||(dojo._hasResource["dojox.lang.functional.scan"]=!0,dojo.provide("dojox.lang.functional.scan"),dojo.require("dojox.lang.functional.lambda"),function(){var h=dojo,i=dojox.lang.functional,j={};h.mixin(i,{scanl:function(a,g,d,c){typeof a=="string"&&(a=a.split(""));var c=c||h.global,g=i.lambda(g),e,f,b;if(h.isArray(a)){e=Array((f=a.length)+1);e[0]=d;for(b=0;b<f;d=g.call(c,d,a[b],b,a),e[++b]=d);}else if(typeof a.hasNext=="function"&&typeof a.next=="function"){e=
[d];for(b=0;a.hasNext();e.push(d=g.call(c,d,a.next(),b++,a)));}else for(b in e=[d],a)b in j||e.push(d=g.call(c,d,a[b],b,a));return e},scanl1:function(a,g,d){typeof a=="string"&&(a=a.split(""));var d=d||h.global,g=i.lambda(g),c,e,f;e=!0;if(h.isArray(a)){c=Array(e=a.length);c[0]=f=a[0];for(var b=1;b<e;c[b]=f=g.call(d,f,a[b],b,a),++b);}else if(typeof a.hasNext=="function"&&typeof a.next=="function"){if(a.hasNext()){c=[f=a.next()];for(b=1;a.hasNext();c.push(f=g.call(d,f,a.next(),b++,a)));}}else for(b in a)b in
j||(e?(c=[f=a[b]],e=!1):c.push(f=g.call(d,f,a[b],b,a)));return c},scanr:function(a,g,d,c){typeof a=="string"&&(a=a.split(""));var c=c||h.global,g=i.lambda(g),e=a.length,f=Array(e+1),b=e;for(f[e]=d;b>0;--b,d=g.call(c,d,a[b],b,a),f[b]=d);return f},scanr1:function(a,g,d){typeof a=="string"&&(a=a.split(""));var d=d||h.global,g=i.lambda(g),c=a.length,e=Array(c),f=a[c-1];c-=1;for(e[c]=f;c>0;--c,f=g.call(d,f,a[c],c,a),e[c]=f);return e}})}());