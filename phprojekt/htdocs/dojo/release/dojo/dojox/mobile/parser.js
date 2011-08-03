/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.mobile.parser"])dojo._hasResource["dojox.mobile.parser"]=!0,dojo.provide("dojox.mobile.parser"),dojo.provide("dojo.parser"),dojox.mobile.parser=new function(){this.instantiate=function(c,i){var f=[];if(c){var a,d;d=c.length;for(a=0;a<d;a++){var b=c[a],j=dojo.getObject(dojo.attr(b,"dojoType")),k=j.prototype,g={};if(i)for(var h in i)g[h]=i[h];for(var e in k){var l=dojo.attr(b,e);l&&(typeof k[e]=="string"?g[e]=l:typeof k[e]=="number"?g[e]=l-0:typeof k[e]=="boolean"?g[e]=
l!="false":typeof k[e]=="object"&&(g[e]=eval("("+l+")")))}g["class"]=b.className;g.style=b.style&&b.style.cssText;j=new j(g,b);f.push(j);(b=b.getAttribute("jsId"))&&dojo.setObject(b,j)}d=f.length;for(a=0;a<d;a++)h=f[a],h.startup&&!h._started&&(!h.getParent||!h.getParent())&&h.startup()}return f};this.parse=function(c,i){if(c){if(!i&&c.rootNode)c=c.rootNode}else c=dojo.body();for(var f=c.getElementsByTagName("*"),a=[],d=0,b=f.length;d<b;d++)f[d].getAttribute("dojoType")&&a.push(f[d]);return this.instantiate(a,
i)}},dojo._loaders.unshift(function(){dojo.config.parseOnLoad&&dojox.mobile.parser.parse()});