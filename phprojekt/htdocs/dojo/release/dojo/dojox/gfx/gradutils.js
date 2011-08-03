/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.gfx.gradutils"]||(dojo._hasResource["dojox.gfx.gradutils"]=!0,dojo.provide("dojox.gfx.gradutils"),dojo.require("dojox.gfx.matrix"),function(){function j(a,d){if(a<=0)return d[0].color;var b=d.length;if(a>=1)return d[b-1].color;for(var c=0;c<b;++c){var e=d[c];if(e.offset>=a)return c?(b=d[c-1],i.blendColors(new h(b.color),new h(e.color),(a-b.offset)/(e.offset-b.offset))):e.color}return d[b-1].color}var i=dojo,f=dojox.gfx.matrix,h=i.Color;dojox.gfx.gradutils.getColor=function(a,
d){var b;if(a){switch(a.type){case "linear":b=f.rotate(-Math.atan2(a.y2-a.y1,a.x2-a.x1));var c=f.project(a.x2-a.x1,a.y2-a.y1),e=f.multiplyPoint(c,d),g=f.multiplyPoint(c,a.x1,a.y1),c=f.multiplyPoint(c,a.x2,a.y2),c=f.multiplyPoint(b,c.x-g.x,c.y-g.y).x;b=f.multiplyPoint(b,e.x-g.x,e.y-g.y).x/c;break;case "radial":b=d.x-a.cx,e=d.y-a.cy,b=Math.sqrt(b*b+e*e)/a.r}return j(b,a.colors)}return new h(a||[0,0,0,0])};dojox.gfx.gradutils.reverse=function(a){if(a)switch(a.type){case "linear":case "radial":if(a=dojo.delegate(a),
a.colors){for(var d=a.colors,b=d.length,c=0,e,f=a.colors=Array(d.length);c<b;++c)e=d[c],f[c]={offset:1-e.offset,color:e.color};f.sort(function(a,b){return a.offset-b.offset})}}return a}}());