/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.drawing.tools.Line"])dojo._hasResource["dojox.drawing.tools.Line"]=!0,dojo.provide("dojox.drawing.tools.Line"),dojox.drawing.tools.Line=dojox.drawing.util.oo.declare(dojox.drawing.stencil.Line,function(){},{draws:!0,showAngle:!0,onTransformEnd:function(){this._toggleSelected();if(this.getRadius()<this.minimumSize){var a=this.points;this.setPoints([{x:a[0].x,y:a[0].y},{x:a[0].x,y:a[0].y}])}else{var a=this.data,b=this.util.snapAngle({start:{x:a.x1,y:a.y1},x:a.x2,y:a.y2},
this.angleSnap/180);this.setPoints([{x:a.x1,y:a.y1},{x:b.x,y:b.y}]);this._isBeingModified=!1;this.onModify(this)}},onDrag:function(a){if(!this.created){var b=a.start.x,e=a.start.y,d=a.x,c=a.y;if(this.keys.shift)c=this.util.snapAngle(a,0.25),d=c.x,c=c.y;if(this.keys.alt){var a=d>b?(d-b)/2:(b-d)/-2,f=c>e?(c-e)/2:(e-c)/-2;b-=a;d-=a;e-=f;c-=f}this.setPoints([{x:b,y:e},{x:d,y:c}]);this.render()}},onUp:function(a){if(!this.created&&this._downOnCanvas){this._downOnCanvas=!1;if(this.shape){if(this.getRadius()<
this.minimumSize){this.remove(this.shape,this.hit);return}}else{var b=a.start;this.setPoints([{x:b.x,y:b.y+this.minimumSize*4},{x:b.x,y:b.y}]);this.render()}a=this.util.snapAngle(a,this.angleSnap/180);b=this.points;this.setPoints([{x:b[0].x,y:b[0].y},{x:a.x,y:a.y}]);this.renderedOnce=!0;this.onRender(this)}}}),dojox.drawing.tools.Line.setup={name:"dojox.drawing.tools.Line",tooltip:"Line Tool",iconClass:"iconLine"},dojox.drawing.register(dojox.drawing.tools.Line.setup,"tool");