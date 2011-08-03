/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.fx.ext-dojo.NodeList"]||(dojo._hasResource["dojox.fx.ext-dojo.NodeList"]=!0,dojo.provide("dojox.fx.ext-dojo.NodeList"),dojo.experimental("dojox.fx.ext-dojo.NodeList"),dojo.require("dojo.NodeList-fx"),dojo.require("dojox.fx"),dojo.extend(dojo.NodeList,{sizeTo:function(a){return this._anim(dojox.fx,"sizeTo",a)},slideBy:function(a){return this._anim(dojox.fx,"slideBy",a)},highlight:function(a){return this._anim(dojox.fx,"highlight",a)},fadeTo:function(a){return this._anim(dojo,
"_fade",a)},wipeTo:function(a){return this._anim(dojox.fx,"wipeTo",a)}}));