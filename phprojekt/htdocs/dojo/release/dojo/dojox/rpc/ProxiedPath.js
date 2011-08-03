/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.rpc.ProxiedPath"]||(dojo._hasResource["dojox.rpc.ProxiedPath"]=!0,dojo.provide("dojox.rpc.ProxiedPath"),dojo.require("dojox.rpc.Service"),dojox.rpc.envelopeRegistry.register("PROXIED-PATH",function(b){return b=="PROXIED-PATH"},{serialize:function(b,e,c){var a,d=dojox.rpc.getTarget(b,e);if(dojo.isArray(c))for(a=0;a<c.length;a++)d+="/"+(c[a]==null?"":c[a]);else for(a in c)d+="/"+a+"/"+c[a];return{data:"",target:(e.proxyUrl||b.proxyUrl)+"?url="+encodeURIComponent(d)}},deserialize:function(b){return b}}));