/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.lang.utils"]||(dojo._hasResource["dojox.lang.utils"]=!0,dojo.provide("dojox.lang.utils"),function(){var i={},f=dojox.lang.utils,g=Object.prototype.toString,j=function(b){if(b)switch(g.call(b)){case "[object Array]":return b.slice(0);case "[object Object]":return dojo.delegate(b)}return b};dojo.mixin(f,{coerceType:function(b,a){switch(typeof b){case "number":return Number(eval("("+a+")"));case "string":return String(a);case "boolean":return Boolean(eval("("+a+")"))}return eval("("+
a+")")},updateWithObject:function(b,a,e){if(!a)return b;for(var d in b)if(d in a&&!(d in i)){var c=b[d];c&&typeof c=="object"?f.updateWithObject(c,a[d],e):b[d]=e?f.coerceType(c,a[d]):j(a[d])}return b},updateWithPattern:function(b,a,e,d){if(!a||!e)return b;for(var c in e)c in a&&!(c in i)&&(b[c]=d?f.coerceType(e[c],a[c]):j(a[c]));return b},merge:function(b,a){if(a){var e=g.call(b),d=g.call(a),c,h;switch(d){case "[object Array]":if(d==e){e=Array(Math.max(b.length,a.length));c=0;for(d=e.length;c<d;++c)e[c]=
f.merge(b[c],a[c]);return e}return a.slice(0);case "[object Object]":if(d==e&&b){e=dojo.delegate(b);for(c in a)c in b?(d=b[c],h=a[c],h!==d&&(e[c]=f.merge(d,h))):e[c]=dojo.clone(a[c]);return e}return dojo.clone(a)}}return a}})}());