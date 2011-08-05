/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.io.xhrWindowNamePlugin"])dojo._hasResource["dojox.io.xhrWindowNamePlugin"]=!0,dojo.provide("dojox.io.xhrWindowNamePlugin"),dojo.require("dojox.io.xhrPlugins"),dojo.require("dojox.io.windowName"),dojo.require("dojox.io.httpParse"),dojo.require("dojox.secure.capability"),dojox.io.xhrWindowNamePlugin=function(b,e,h){dojox.io.xhrPlugins.register("windowName",function(c,a){return a.sync!==!0&&(c=="GET"||c=="POST"||e)&&a.url.substring(0,b.length)==b},function(c,a,b){var f=dojox.io.windowName.send,
g=a.load;a.load=void 0;var d=(e?e(f,!0):f)(c,a,b);d.addCallback(function(a){var b=d.ioArgs;b.xhr={getResponseHeader:function(a){return dojo.queryToObject(b.hash.match(/[^#]*$/)[0])[a]}};return b.handleAs=="json"?(h||dojox.secure.capability.validate(a,["Date"],{}),dojo.fromJson(a)):dojo._contentHandlers[b.handleAs||"text"]({responseText:a})});(a.load=g)&&d.addCallback(g);return d})};