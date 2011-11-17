/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojo.cache"]){dojo._hasResource["dojo.cache"]=!0;dojo.provide("dojo.cache");var cache={};dojo.cache=function(a,b,c){typeof a=="string"?a=dojo.moduleUrl(a,b):c=b;b=a.toString();a=c;c!=void 0&&!dojo.isString(c)&&(a="value"in c?c.value:void 0);c=c&&c.sanitize?!0:!1;typeof a=="string"?a=cache[b]=c?dojo.cache._sanitize(a):a:a===null?delete cache[b]:(b in cache||(a=dojo._getText(b),cache[b]=c?dojo.cache._sanitize(a):a),a=cache[b]);return a};dojo.cache._sanitize=function(a){if(a){var a=
a.replace(/^\s*<\?xml(\s)+version=[\'\"](\d)*.(\d)*[\'\"](\s)*\?>/im,""),b=a.match(/<body[^>]*>\s*([\s\S]+)\s*<\/body>/im);b&&(a=b[1])}else a="";return a}};