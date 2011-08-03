/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


define(["dojo","dojo/cache"],function(h){var f={},i=function(a,b,d){f[a]=d;h.cache({toString:function(){return b}},d)},j=function(a){if(a){var a=a.replace(/^\s*<\?xml(\s)+version=[\'\"](\d)*.(\d)*[\'\"](\s)*\?>/im,""),b=a.match(/<body[^>]*>\s*([\s\S]+)\s*<\/body>/im);b&&(a=b[1])}else a="";return a};return{load:function(a,b,d){var c,g,e=a.split("!");if(b.toAbsMid&&(c=(a=e[0].match(/(.+)(\.[^\/]*)$/))?b.toAbsMid(a[1])+a[2]:b.toAbsMid(e[0]),c in f)){d(e[1]=="strip"?j(f[c]):f[c]);return}g=b.toUrl(e[0]);
h.xhrGet({url:g,load:function(a){c&&i(c,g,a);d(e[1]=="strip"?j(a):a)}})},cache:function(a,b,d,c){i(a,require.nameToUrl(b)+d,c)}}});