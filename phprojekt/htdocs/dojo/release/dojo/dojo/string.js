/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojo.string"])dojo._hasResource["dojo.string"]=!0,dojo.provide("dojo.string"),dojo.getObject("string",!0,dojo),dojo.string.rep=function(a,b){if(b<=0||!a)return"";for(var c=[];;){b&1&&c.push(a);if(!(b>>=1))break;a+=a}return c.join("")},dojo.string.pad=function(a,b,c,d){c||(c="0");a=String(a);b=dojo.string.rep(c,Math.ceil((b-a.length)/c.length));return d?a+b:b+a},dojo.string.substitute=function(a,b,c,d){d=d||dojo.global;c=c?dojo.hitch(d,c):function(a){return a};return a.replace(/\$\{([^\s\:\}]+)(?:\:([^\s\:\}]+))?\}/g,
function(a,e,f){a=dojo.getObject(e,!1,b);f&&(a=dojo.getObject(f,!1,d).call(d,a,e));return c(a,e).toString()})},dojo.string.trim=String.prototype.trim?dojo.trim:function(a){for(var a=a.replace(/^\s+/,""),b=a.length-1;b>=0;b--)if(/\S/.test(a.charAt(b))){a=a.substring(0,b+1);break}return a};