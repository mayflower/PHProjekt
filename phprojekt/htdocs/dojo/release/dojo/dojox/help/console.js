/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.help.console"]||(dojo._hasResource["dojox.help.console"]=!0,dojo.provide("dojox.help.console"),dojo.require("dojox.help._base"),dojo.mixin(dojox.help,{_plainText:function(h){return h.replace(/(<[^>]*>|&[^;]{2,6};)/g,"")},_displayLocated:function(h){var c={};dojo.forEach(h,function(d){c[d[0]]=dojo.isMoz?{toString:function(){return"Click to view"},item:d[1]}:d[1]});console.dir(c)},_displayHelp:function(h,c){if(h){var d="Help for: "+c.name;console.log(d);for(var e="",a=0;a<d.length;a++)e+=
"=";console.log(e)}else if(c){d=!1;for(e in c)if(a=c[e],!(e=="returns"&&c.type!="Function"&&c.type!="Constructor")&&a&&(!dojo.isArray(a)||a.length))if(d=!0,console.info(e.toUpperCase()),a=dojo.isString(a)?dojox.help._plainText(a):a,e=="returns"){var g=dojo.map(a.types||[],"return item.title;").join("|");a.summary&&(g&&(g+=": "),g+=dojox.help._plainText(a.summary));console.log(g||"Uknown")}else if(e=="parameters")for(var g=0,f;f=a[g];g++){var b=dojo.map(f.types,"return item.title").join("|");console.log(b?
f.name+": "+b:f.name);b="";f.optional&&(b+="Optional. ");f.repating&&(b+="Repeating. ");b+=dojox.help._plainText(f.summary);if(b){for(var b="  - "+b,i=0;i<f.name.length;i++)b=" "+b;console.log(b)}}else console.log(a);d||console.log("No documentation for this object")}else console.log("No documentation for this object")}}),dojox.help.init());