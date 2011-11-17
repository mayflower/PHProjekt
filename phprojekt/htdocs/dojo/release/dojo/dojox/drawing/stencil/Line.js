/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.drawing.stencil.Line"])dojo._hasResource["dojox.drawing.stencil.Line"]=!0,dojo.provide("dojox.drawing.stencil.Line"),dojox.drawing.stencil.Line=dojox.drawing.util.oo.declare(dojox.drawing.stencil._Base,function(){},{type:"dojox.drawing.stencil.Line",anchorType:"single",baseRender:!0,dataToPoints:function(a){a=a||this.data;if(a.radius||a.angle){var b=this.util.pointOnCircle(a.x,a.y,a.radius,a.angle);this.data=a={x1:a.x,y1:a.y,x2:b.x,y2:b.y}}return this.points=[{x:a.x1,
y:a.y1},{x:a.x2,y:a.y2}]},pointsToData:function(a){a=a||this.points;return this.data={x1:a[0].x,y1:a[0].y,x2:a[1].x,y2:a[1].y}},_create:function(a,b,c){this.remove(this[a]);this[a]=this.container.createLine(b).setStroke(c);this._setNodeAtts(this[a])},render:function(){this.onBeforeRender(this);this.renderHit&&this._create("hit",this.data,this.style.currentHit);this._create("shape",this.data,this.style.current)}}),dojox.drawing.register({name:"dojox.drawing.stencil.Line"},"stencil");