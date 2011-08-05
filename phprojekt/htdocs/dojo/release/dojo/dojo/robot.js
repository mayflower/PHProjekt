/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojo.robot"]||(dojo._hasResource["dojo.robot"]=!0,dojo.provide("dojo.robot"),dojo.require("doh.robot"),dojo.require("dojo.window"),dojo.experimental("dojo.robot"),function(){dojo.mixin(doh.robot,{_resolveNode:function(b){typeof b=="function"&&(b=b());return b?dojo.byId(b):null},_scrollIntoView:function(b){var c=dojo,a=null;c.forEach(doh.robot._getWindowChain(b),function(f){c.withGlobal(f,function(){var e=c.position(b,!1),g=c._getPadBorderExtents(b),d=null;a?(d=a,a={x:a.x+e.x+g.l,
y:a.y+e.y+g.t,w:a.w,h:a.h}):a=e;dojo.window.scrollIntoView(b,a);e=c.position(b,!1);a=d?{x:d.x+e.x+g.l,y:d.y+e.y+g.t,w:a.w,h:a.h}:e;b=f.frameElement})})},_position:function(b){var c=dojo,a=null,f=Math.max,e=Math.min;c.forEach(doh.robot._getWindowChain(b),function(g){c.withGlobal(g,function(){var d=c.position(b,!1),h=c._getPadBorderExtents(b);if(a){var i;c.withGlobal(b.contentWindow,function(){i=dojo.window.getBox()});d.r=d.x+i.w;d.b=d.y+i.h;a={x:f(a.x+d.x,d.x)+h.l,y:f(a.y+d.y,d.y)+h.t,r:e(a.x+d.x+
a.w,d.r)+h.l,b:e(a.y+d.y+a.h,d.b)+h.t};a.w=a.r-a.x;a.h=a.b-a.y}else a=d;b=g.frameElement})});return a},_getWindowChain:function(b){var b=dojo.window.get(b.ownerDocument),c=[b],a=b.frameElement;return b==dojo.global||a==null?c:c.concat(doh.robot._getWindowChain(a))},scrollIntoView:function(b,c){doh.robot.sequence(function(){doh.robot._scrollIntoView(doh.robot._resolveNode(b))},c)},mouseMoveAt:function(b,c,a,f,e){doh.robot._assertRobot();a=a||100;this.sequence(function(){b=doh.robot._resolveNode(b);
doh.robot._scrollIntoView(b);var c=doh.robot._position(b);e===void 0&&(f=c.w/2,e=c.h/2);doh.robot._mouseMove(c.x+f,c.y+e,!1,a)},c,a)}})}());