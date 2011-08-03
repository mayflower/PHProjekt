/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.geo.charting._base"]||(dojo._hasResource["dojox.geo.charting._base"]=!0,dojo.provide("dojox.geo.charting._base"),dojo.require("dojo.NodeList-traverse"),dojo.require("dojox.gfx.matrix"),dojo.require("dijit.Tooltip"),function(){var c=dojox.geo.charting;c.showTooltip=function(a,b,d){b=c._normalizeArround(b);return dijit.showTooltip(a,b,d)};c.hideTooltip=function(a){return dijit.hideTooltip(a)};c._normalizeArround=function(a){var b=c._getRealBBox(a),d=a._getRealMatrix()||{xx:1,
xy:0,yx:0,yy:1,dx:0,dy:0},e=dojox.gfx.matrix.multiplyPoint(d,b.x,b.y),f=dojo.coords(c._getGfxContainer(a));a.x=dojo.coords(f,!0).x+e.x;a.y=dojo.coords(f,!0).y+e.y;a.width=b.width*d.xx;a.height=b.height*d.yy;return a};c._getGfxContainer=function(a){return(new dojo.NodeList(a.rawNode)).parents("div")[0]};c._getRealBBox=function(a){var b=a.getBoundingBox();if(!b)a=a.children,b=dojo.clone(c._getRealBBox(a[0])),dojo.forEach(a,function(a){a=c._getRealBBox(a);b.x=Math.min(b.x,a.x);b.y=Math.min(b.y,a.y);
b.endX=Math.max(b.x+b.width,a.x+a.width);b.endY=Math.max(b.y+b.height,a.y+a.height)}),b.width=b.endX-b.x,b.height=b.endY-b.y;return b}}());