/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.drawing.tools.Pencil"])dojo._hasResource["dojox.drawing.tools.Pencil"]=!0,dojo.provide("dojox.drawing.tools.Pencil"),dojox.drawing.tools.Pencil=dojox.drawing.util.oo.declare(dojox.drawing.stencil.Path,function(){this._started=!1},{draws:!0,minDist:15,onDown:function(a){this._started=!0;a={x:a.x,y:a.y};this.points=[a];this.lastPoint=a;this.revertRenderHit=this.renderHit;this.closePath=this.renderHit=!1},onDrag:function(a){if(this._started&&!(this.minDist>this.util.distance(a.x,
a.y,this.lastPoint.x,this.lastPoint.y))){var b={x:a.x,y:a.y};this.points.push(b);this.render();this.checkClosePoint(this.points[0],a);this.lastPoint=b}},onUp:function(a){if(this._started)if(!this.points||this.points.length<2)this._started=!1,this.points=[];else{var b=this.getBounds();if(b.w<this.minimumSize&&b.h<this.minimumSize)this.remove(this.hit,this.shape,this.closeGuide),this._started=!1,this.setPoints([]);else{if(this.checkClosePoint(this.points[0],a,!0))this.closePath=!0;this.renderHit=this.revertRenderHit;
this.renderedOnce=!0;this.render();this.onRender(this)}}}}),dojox.drawing.tools.Pencil.setup={name:"dojox.drawing.tools.Pencil",tooltip:"Pencil Tool",iconClass:"iconLine"},dojox.drawing.register(dojox.drawing.tools.Pencil.setup,"tool");