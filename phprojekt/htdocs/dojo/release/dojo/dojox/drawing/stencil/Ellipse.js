/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.drawing.stencil.Ellipse"])dojo._hasResource["dojox.drawing.stencil.Ellipse"]=!0,dojo.provide("dojox.drawing.stencil.Ellipse"),dojox.drawing.stencil.Ellipse=dojox.drawing.util.oo.declare(dojox.drawing.stencil._Base,function(){},{type:"dojox.drawing.stencil.Ellipse",anchorType:"group",baseRender:!0,dataToPoints:function(a){var a=a||this.data,b=a.cx-a.rx,c=a.cy-a.ry,d=a.rx*2,a=a.ry*2;return this.points=[{x:b,y:c},{x:b+d,y:c},{x:b+d,y:c+a},{x:b,y:c+a}]},pointsToData:function(a){var a=
a||this.points,b=a[0],a=a[2];return this.data={cx:b.x+(a.x-b.x)/2,cy:b.y+(a.y-b.y)/2,rx:(a.x-b.x)*0.5,ry:(a.y-b.y)*0.5}},_create:function(a,b,c){this.remove(this[a]);this[a]=this.container.createEllipse(b).setStroke(c).setFill(c.fill);this._setNodeAtts(this[a])},render:function(){this.onBeforeRender(this);this.renderHit&&this._create("hit",this.data,this.style.currentHit);this._create("shape",this.data,this.style.current)}}),dojox.drawing.register({name:"dojox.drawing.stencil.Ellipse"},"stencil");