/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.widget.rotator.Fade"]||(dojo._hasResource["dojox.widget.rotator.Fade"]=!0,dojo.provide("dojox.widget.rotator.Fade"),dojo.require("dojo.fx"),function(b){function c(a,c){var d=a.next.node;b.style(d,{display:"",opacity:0});a.node=a.current.node;return b.fx[c]([b.fadeOut(a),b.fadeIn(b.mixin(a,{node:d}))])}b.mixin(dojox.widget.rotator,{fade:function(a){return c(a,"chain")},crossFade:function(a){return c(a,"combine")}})}(dojo));