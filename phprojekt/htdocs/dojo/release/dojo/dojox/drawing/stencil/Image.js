/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.drawing.stencil.Image"])dojo._hasResource["dojox.drawing.stencil.Image"]=!0,dojo.provide("dojox.drawing.stencil.Image"),dojox.drawing.stencil.Image=dojox.drawing.util.oo.declare(dojox.drawing.stencil._Base,function(){},{type:"dojox.drawing.stencil.Image",anchorType:"group",baseRender:!0,dataToPoints:function(a){a=a||this.data;return this.points=[{x:a.x,y:a.y},{x:a.x+a.width,y:a.y},{x:a.x+a.width,y:a.y+a.height},{x:a.x,y:a.y+a.height}]},pointsToData:function(a){var a=a||
this.points,b=a[0],a=a[2];return this.data={x:b.x,y:b.y,width:a.x-b.x,height:a.y-b.y,src:this.src||this.data.src}},_createHilite:function(){this.remove(this.hit);this.hit=this.container.createRect(this.data).setStroke(this.style.current).setFill(this.style.current.fill);this._setNodeAtts(this.hit)},_create:function(a,b){this.remove(this[a]);var c=this.container.getParent();this[a]=c.createImage(b);this.container.add(this[a]);this._setNodeAtts(this[a])},render:function(){this.data.width=="auto"||isNaN(this.data.width)?
(this.getImageSize(!0),console.warn("Image size not provided. Acquiring...")):(this.onBeforeRender(this),this.renderHit&&this._createHilite(),this._create("shape",this.data,this.style.current))},getImageSize:function(a){if(!this._gettingSize){this._gettingSize=!0;var b=dojo.create("img",{src:this.data.src},dojo.body()),c=dojo.connect(b,"error",this,function(){dojo.disconnect(d);dojo.disconnect(c);console.error("Error loading image:",this.data.src);console.warn("Error image:",this.data)}),d=dojo.connect(b,
"load",this,function(){var c=dojo.marginBox(b);this.setData({x:this.data.x,y:this.data.y,src:this.data.src,width:c.w,height:c.h});dojo.disconnect(d);dojo.destroy(b);a&&this.render(!0)})}}}),dojox.drawing.register({name:"dojox.drawing.stencil.Image"},"stencil");