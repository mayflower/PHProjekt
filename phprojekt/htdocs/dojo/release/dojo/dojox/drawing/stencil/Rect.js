/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.drawing.stencil.Rect"])dojo._hasResource["dojox.drawing.stencil.Rect"]=!0,dojo.provide("dojox.drawing.stencil.Rect"),dojox.drawing.stencil.Rect=dojox.drawing.util.oo.declare(dojox.drawing.stencil._Base,function(){},{type:"dojox.drawing.stencil.Rect",anchorType:"group",baseRender:!0,dataToPoints:function(a){a=a||this.data;return this.points=[{x:a.x,y:a.y},{x:a.x+a.width,y:a.y},{x:a.x+a.width,y:a.y+a.height},{x:a.x,y:a.y+a.height}]},pointsToData:function(a){var a=a||this.points,
b=a[0],a=a[2];return this.data={x:b.x,y:b.y,width:a.x-b.x,height:a.y-b.y,r:this.data.r||0}},_create:function(a,b,c){this.remove(this[a]);this[a]=this.container.createRect(b).setStroke(c).setFill(c.fill);this._setNodeAtts(this[a])},render:function(){this.onBeforeRender(this);this.renderHit&&this._create("hit",this.data,this.style.currentHit);this._create("shape",this.data,this.style.current)}}),dojox.drawing.register({name:"dojox.drawing.stencil.Rect"},"stencil");