/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojo._base.array"]||(dojo._hasResource["dojo._base.array"]=!0,dojo.provide("dojo._base.array"),dojo.require("dojo._base.lang"),function(){var h=function(b,c,a){return[typeof b=="string"?b.split(""):b,c||dojo.global,typeof a=="string"?new Function("item","index","array",a):a]},i=function(b,c,a,d){for(var a=h(c,d,a),c=a[0],d=0,e=c.length;d<e;++d){var f=!!a[2].call(a[1],c[d],d,c);if(b^f)return f}return b};dojo.mixin(dojo,{indexOf:function(b,c,a,d){var e=1,f=b.length||0,g=0;d&&(g=f-
1,e=f=-1);a!=void 0&&(g=a);if(d&&g>f||g<f)for(;g!=f;g+=e)if(b[g]==c)return g;return-1},lastIndexOf:function(b,c,a){return dojo.indexOf(b,c,a,!0)},forEach:function(b,c,a){if(b&&b.length)for(var c=h(b,a,c),b=c[0],a=0,d=b.length;a<d;++a)c[2].call(c[1],b[a],a,b)},every:function(b,c,a){return i(!0,b,c,a)},some:function(b,c,a){return i(!1,b,c,a)},map:function(b,c,a,d){for(var c=h(b,a,c),b=c[0],d=d?new d:[],a=0,e=b.length;a<e;++a)d.push(c[2].call(c[1],b[a],a,b));return d},filter:function(b,c,a){for(var c=
h(b,a,c),b=c[0],a=[],d=0,e=b.length;d<e;++d)c[2].call(c[1],b[d],d,b)&&a.push(b[d]);return a}})}());