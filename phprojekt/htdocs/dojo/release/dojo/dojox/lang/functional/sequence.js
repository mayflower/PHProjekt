/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.lang.functional.sequence"]||(dojo._hasResource["dojox.lang.functional.sequence"]=!0,dojo.provide("dojox.lang.functional.sequence"),dojo.require("dojox.lang.functional.lambda"),function(){var g=dojo,f=dojox.lang.functional;g.mixin(f,{repeat:function(d,c,a,b){var b=b||g.global,c=f.lambda(c),e=Array(d),h=1;for(e[0]=a;h<d;e[h]=a=c.call(b,a),++h);return e},until:function(d,c,a,b){for(var b=b||g.global,c=f.lambda(c),d=f.lambda(d),e=[];!d.call(b,a);e.push(a),a=c.call(b,a));return e}})}());