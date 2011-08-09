/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.fx.scroll"])dojo._hasResource["dojox.fx.scroll"]=!0,dojo.provide("dojox.fx.scroll"),dojo.experimental("dojox.fx.scroll"),dojo.require("dojox.fx._core"),dojox.fx.smoothScroll=function(a){if(!a.target)a.target=dojo.position(a.node);var d=dojo[dojo.isIE?"isObject":"isFunction"](a.win.scrollTo),c={x:a.target.x,y:a.target.y};if(!d){var e=dojo.position(a.win);c.x-=e.x;c.y-=e.y}var f=new dojo.Animation(dojo.mixin({beforeBegin:function(){this.curve&&delete this.curve;var b=d?
dojo._docScroll():{x:a.win.scrollLeft,y:a.win.scrollTop};f.curve=new dojox.fx._Line([b.x,b.y],[b.x+c.x,b.y+c.y])},onAnimate:d?function(b){a.win.scrollTo(b[0],b[1])}:function(b){a.win.scrollLeft=b[0];a.win.scrollTop=b[1]}},a));return f};