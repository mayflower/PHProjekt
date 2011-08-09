/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.drawing.annotations.Angle"])dojo._hasResource["dojox.drawing.annotations.Angle"]=!0,dojo.provide("dojox.drawing.annotations.Angle"),dojox.drawing.annotations.Angle=dojox.drawing.util.oo.declare(function(b){this.stencil=b.stencil;this.util=b.stencil.util;this.mouse=b.stencil.mouse;this.stencil.connectMult([["onDrag",this,"showAngle"],["onUp",this,"hideAngle"],["onTransformBegin",this,"showAngle"],["onTransform",this,"showAngle"],["onTransformEnd",this,"hideAngle"]])},{type:"dojox.drawing.tools.custom",
angle:0,showAngle:function(){if(this.stencil.selected||!this.stencil.created)if(this.stencil.getRadius()<this.stencil.minimumSize)this.hideAngle();else{var b=this.getAngleNode(),a=this.stencil.pointsToData(),a=dojox.drawing.util.positioning.angle({x:a.x1,y:a.y1},{x:a.x2,y:a.y2}),d=this.mouse.scrollOffset(),c=this.stencil.getTransform(),e=c.dx/this.mouse.zoom,c=c.dy/this.mouse.zoom;a.x/=this.mouse.zoom;a.y/=this.mouse.zoom;dojo.style(b,{left:this.stencil._offX+a.x-d.left+e+"px",top:this.stencil._offY+
a.y-d.top+c+"px",align:a.align});a=this.stencil.getAngle();b.innerHTML=this.stencil.style.zAxis&&this.stencil.shortType=="vector"?this.stencil.data.cosphi>0?"out of":"into":this.stencil.shortType=="line"?this.stencil.style.zAxis?"out of":Math.ceil(a%180):Math.ceil(a)}},getAngleNode:function(){if(!this._angleNode)this._angleNode=dojo.create("span",null,dojo.body()),dojo.addClass(this._angleNode,"textAnnotation"),dojo.style(this._angleNode,"opacity",1);return this._angleNode},hideAngle:function(){if(this._angleNode&&
dojo.style(this._angleNode,"opacity")>0.9)dojo.fadeOut({node:this._angleNode,duration:500,onEnd:function(b){dojo.destroy(b)}}).play(),this._angleNode=null}});