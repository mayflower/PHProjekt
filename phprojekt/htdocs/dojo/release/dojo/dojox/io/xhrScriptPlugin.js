/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.io.xhrScriptPlugin"])dojo._hasResource["dojox.io.xhrScriptPlugin"]=!0,dojo.provide("dojox.io.xhrScriptPlugin"),dojo.require("dojox.io.xhrPlugins"),dojo.require("dojo.io.script"),dojo.require("dojox.io.scriptFrame"),dojox.io.xhrScriptPlugin=function(b,f,c){dojox.io.xhrPlugins.register("script",function(d,a){return a.sync!==!0&&(d=="GET"||c)&&a.url.substring(0,b.length)==b},function(d,a,b){var e=function(){a.callbackParamName=f;if(dojo.body())a.frameDoc="frame"+Math.random();
return dojo.io.script.get(a)};return(c?c(e,!0):e)(d,a,b)})};