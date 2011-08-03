/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojo._base.json"])dojo._hasResource["dojo._base.json"]=!0,dojo.provide("dojo._base.json"),dojo.fromJson=function(a){return eval("("+a+")")},dojo._escapeString=function(a){return('"'+a.replace(/(["\\])/g,"\\$1")+'"').replace(/[\f]/g,"\\f").replace(/[\b]/g,"\\b").replace(/[\n]/g,"\\n").replace(/[\t]/g,"\\t").replace(/[\r]/g,"\\r")},dojo.toJsonIndentStr="\t",dojo.toJson=function(a,c,f){if(a===void 0)return"undefined";var d=typeof a;if(d=="number"||d=="boolean")return a+"";if(a===
null)return"null";if(dojo.isString(a))return dojo._escapeString(a);var i=arguments.callee,b,f=f||"",g=c?f+dojo.toJsonIndentStr:"";b=a.__json__||a.json;if(dojo.isFunction(b)&&(b=b.call(a),a!==b))return i(b,c,g);if(a.nodeType&&a.cloneNode)throw Error("Can't serialize DOM nodes");b=c?" ":"";var h=c?"\n":"";if(dojo.isArray(a))return"["+dojo.map(a,function(a){a=i(a,c,g);typeof a!="string"&&(a="undefined");return h+g+a}).join(","+b)+h+f+"]";if(d=="function")return null;var d=[],e;for(e in a){var j,k;if(typeof e==
"number")j='"'+e+'"';else if(typeof e=="string")j=dojo._escapeString(e);else continue;k=i(a[e],c,g);typeof k=="string"&&d.push(h+g+j+":"+b+k)}return"{"+d.join(","+b)+h+f+"}"};