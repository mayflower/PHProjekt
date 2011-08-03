/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.secure.fromJson"])dojo._hasResource["dojox.secure.fromJson"]=!0,dojo.provide("dojox.secure.fromJson"),dojox.secure.fromJson=typeof JSON!="undefined"?JSON.parse:function(){function m(k,g,f){return g?h[g]:String.fromCharCode(parseInt(f,16))}var n=RegExp('(?:false|true|null|[\\{\\}\\[\\]]|(?:-?\\b(?:0|[1-9][0-9]*)(?:\\.[0-9]+)?(?:[eE][+-]?[0-9]+)?\\b)|(?:"(?:[^\\0-\\x08\\x0a-\\x1f"\\\\]|\\\\(?:["/\\\\bfnrt]|u[0-9A-Fa-f]{4}))*"))',"g"),o=RegExp("\\\\(?:([^u])|u(.{4}))","g"),
h={'"':'"',"/":"/","\\":"\\",b:"\u0008",f:"\u000c",n:"\n",r:"\r",t:"\t"},p=new String(""),q=Object.hasOwnProperty;return function(k,g){var f=k.match(n),e,b=f[0],i=!1;"{"===b?e={}:"["===b?e=[]:(e=[],i=!0);for(var c,d=[e],j=1-i,h=f.length;j<h;++j){var b=f[j],a;switch(b.charCodeAt(0)){default:a=d[0];a[c||a.length]=+b;c=void 0;break;case 34:b=b.substring(1,b.length-1);b.indexOf("\\")!==-1&&(b=b.replace(o,m));a=d[0];if(!c)if(a instanceof Array)c=a.length;else{c=b||p;break}a[c]=b;c=void 0;break;case 91:a=
d[0];d.unshift(a[c||a.length]=[]);c=void 0;break;case 93:d.shift();break;case 102:a=d[0];a[c||a.length]=!1;c=void 0;break;case 110:a=d[0];a[c||a.length]=null;c=void 0;break;case 116:a=d[0];a[c||a.length]=!0;c=void 0;break;case 123:a=d[0];d.unshift(a[c||a.length]={});c=void 0;break;case 125:d.shift()}}if(i){if(d.length!==1)throw Error();e=e[0]}else if(d.length)throw Error();if(g){var l=function(a,c){var b=a[c];if(b&&typeof b==="object"){var d=null,e;for(e in b)if(q.call(b,e)&&b!==a){var f=l(b,e);f!==
void 0?b[e]=f:(d||(d=[]),d.push(e))}if(d)for(e=d.length;--e>=0;)delete b[d[e]]}return g.call(a,c,b)};e=l({"":e},"")}return e}}();