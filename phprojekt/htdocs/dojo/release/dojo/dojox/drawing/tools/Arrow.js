/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.drawing.tools.Arrow"])dojo._hasResource["dojox.drawing.tools.Arrow"]=!0,dojo.provide("dojox.drawing.tools.Arrow"),dojox.drawing.tools.Arrow=dojox.drawing.util.oo.declare(dojox.drawing.tools.Line,function(a){if(this.arrowStart)this.begArrow=new dojox.drawing.annotations.Arrow({stencil:this,idx1:0,idx2:1});if(this.arrowEnd)this.endArrow=new dojox.drawing.annotations.Arrow({stencil:this,idx1:1,idx2:0});this.points.length&&(this.render(),a.label&&this.setLabel(a.label))},
{draws:!0,type:"dojox.drawing.tools.Arrow",baseRender:!1,arrowStart:!1,arrowEnd:!0,labelPosition:function(){var a=this.data,a=dojox.drawing.util.positioning.label({x:a.x1,y:a.y1},{x:a.x2,y:a.y2});return{x:a.x,y:a.y}},onUp:function(a){if(!this.created&&this.shape){var b=this.points;this.util.distance(b[0].x,b[0].y,b[1].x,b[1].y)<this.minimumSize?this.remove(this.shape,this.hit):(a=this.util.snapAngle(a,this.angleSnap/180),this.setPoints([{x:b[0].x,y:b[0].y},{x:a.x,y:a.y}]),this.renderedOnce=!0,this.onRender(this))}}}),
dojox.drawing.tools.Arrow.setup={name:"dojox.drawing.tools.Arrow",tooltip:"Arrow Tool",iconClass:"iconArrow"},dojox.drawing.register(dojox.drawing.tools.Arrow.setup,"tool");