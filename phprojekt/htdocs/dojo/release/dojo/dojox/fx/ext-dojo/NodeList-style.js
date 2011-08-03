/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.fx.ext-dojo.NodeList-style"]||(dojo._hasResource["dojox.fx.ext-dojo.NodeList-style"]=!0,dojo.provide("dojox.fx.ext-dojo.NodeList-style"),dojo.experimental("dojox.fx.ext-dojo.NodeList-style"),dojo.require("dojo.NodeList-fx"),dojo.require("dojox.fx.style"),dojo.extend(dojo.NodeList,{addClassFx:function(a,b){return dojo.fx.combine(this.map(function(c){return dojox.fx.addClass(c,a,b)}))},removeClassFx:function(a,b){return dojo.fx.combine(this.map(function(c){return dojox.fx.removeClass(c,
a,b)}))},toggleClassFx:function(a,b,c){return dojo.fx.combine(this.map(function(d){return dojox.fx.toggleClass(d,a,b,c)}))}}));