/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.drawing.tools.Ellipse"])dojo._hasResource["dojox.drawing.tools.Ellipse"]=!0,dojo.provide("dojox.drawing.tools.Ellipse"),dojox.drawing.tools.Ellipse=dojox.drawing.util.oo.declare(dojox.drawing.stencil.Ellipse,function(){},{draws:!0,onDrag:function(a){var b=a.start,d=b.x<a.x?b.x:a.x,e=b.y<a.y?b.y:a.y,c=b.x<a.x?a.x-b.x:b.x-a.x,a=b.y<a.y?a.y-b.y:b.y-a.y;this.keys.shift&&(c=a=Math.max(c,a));this.keys.alt?(e-a<0&&(a=e),d-c<0&&(c=d)):(d+=c/2,e+=a/2,c/=2,a/=2);this.points=[{x:d-
c,y:e-a},{x:d+c,y:e-a},{x:d+c,y:e+a},{x:d-c,y:e+a}];this.render()},onUp:function(a){if(!this.created&&this._downOnCanvas){this._downOnCanvas=!1;if(this.shape){if(a=this.pointsToData(),console.log("Create a default shape here, pt to data: ",a),a.rx*2<this.minimumSize&&a.ry*2<this.minimumSize){this.remove(this.shape,this.hit);return}}else{var a=a.start,b=this.minimumSize*2;this.data={cx:a.x+b,cy:a.y+b,rx:b,ry:b};this.dataToPoints();this.render()}this.onRender(this)}}}),dojox.drawing.tools.Ellipse.setup=
{name:"dojox.drawing.tools.Ellipse",tooltip:"Ellipse Tool",iconClass:"iconEllipse"},dojox.drawing.register(dojox.drawing.tools.Ellipse.setup,"tool");