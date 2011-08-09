/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.drawing.annotations.Arrow"])dojo._hasResource["dojox.drawing.annotations.Arrow"]=!0,dojo.provide("dojox.drawing.annotations.Arrow"),dojo.require("dojox.drawing.stencil.Path"),dojox.drawing.annotations.Arrow=dojox.drawing.util.oo.declare(dojox.drawing.stencil.Path,function(){this.stencil.connectMult([[this.stencil,"select",this,"select"],[this.stencil,"deselect",this,"deselect"],[this.stencil,"render",this,"render"],[this.stencil,"onDelete",this,"destroy"]]);this.connect("onBeforeRender",
this,function(){var b=this.stencil.points[this.idx1],a=this.stencil.points[this.idx2];this.points=this.stencil.getRadius()>=this.minimumSize?this.arrowHead(a.x,a.y,b.x,b.y,this.style):[]})},{idx1:0,idx2:1,subShape:!0,minimumSize:30,arrowHead:function(b,a,d,e,c){var a={start:{x:b,y:a},x:d,y:e},b=this.util.angle(a),f=this.util.length(a),a=c.arrows.length,c=c.arrows.width/2;f<a&&(a=f/2);f=this.util.pointOnCircle(d,e,-a,b-c);c=this.util.pointOnCircle(d,e,-a,b+c);return[{x:d,y:e},f,c]}});