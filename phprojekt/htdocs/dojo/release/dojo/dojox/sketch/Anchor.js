/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.sketch.Anchor"]||(dojo._hasResource["dojox.sketch.Anchor"]=!0,dojo.provide("dojox.sketch.Anchor"),dojo.require("dojox.gfx"),function(){var b=dojox.sketch;b.Anchor=function(a,c,f){var g=this,e=null;this.type=function(){return"Anchor"};this.annotation=a;this.id=c;this._key="anchor-"+b.Anchor.count++;this.shape=null;this.isControl=f!=null?f:!0;this.beginEdit=function(){this.annotation.beginEdit(b.CommandTypes.Modify)};this.endEdit=function(){this.annotation.endEdit()};this.zoom=
function(d){if(this.shape){var b=Math.floor(4/d),d=dojox.gfx.renderer=="vml"?1:1/d;this.shape.setShape({x:a[c].x-b,y:a[c].y-b,width:b*2,height:b*2}).setStroke({color:"black",width:d})}};this.setBinding=function(b){a[c]={x:a[c].x+b.dx,y:a[c].y+b.dy};a.draw();a.drawBBox()};this.setUndo=function(){a.setUndo()};this.enable=function(){if(a.shape)a.figure._add(this),e={x:a[c].x-4,y:a[c].y-4,width:8,height:8},this.shape=a.shape.createRect(e).setFill([255,255,255,0.35]),this.shape.getEventSource().setAttribute("id",
g._key),this.shape.getEventSource().setAttribute("shape-rendering","crispEdges"),this.zoom(a.figure.zoomFactor)};this.disable=function(){a.figure._remove(this);a.shape&&a.shape.remove(this.shape);e=this.shape=null}};b.Anchor.count=0}());