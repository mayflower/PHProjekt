/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.string.tokenize"])dojo._hasResource["dojox.string.tokenize"]=!0,dojo.provide("dojox.string.tokenize"),dojox.string.tokenize=function(d,e,f,g){var c=[],b,a;for(a=0;b=e.exec(d);){a=d.slice(a,e.lastIndex-b[0].length);a.length&&c.push(a);if(f){if(dojo.isOpera){for(a=b.slice(0);a.length<b.length;)a.push(null);b=a}b=f.apply(g,b.slice(1).concat(c.length));typeof b!="undefined"&&c.push(b)}a=e.lastIndex}a=d.slice(a);a.length&&c.push(a);return c};