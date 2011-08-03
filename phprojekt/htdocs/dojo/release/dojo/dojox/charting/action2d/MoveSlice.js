/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.charting.action2d.MoveSlice"]||(dojo._hasResource["dojox.charting.action2d.MoveSlice"]=!0,dojo.provide("dojox.charting.action2d.MoveSlice"),dojo.require("dojox.charting.action2d.Base"),dojo.require("dojox.gfx.matrix"),dojo.require("dojox.lang.functional"),dojo.require("dojox.lang.functional.scan"),dojo.require("dojox.lang.functional.fold"),function(){var f=dojox.gfx.matrix,d=dojox.lang.functional;dojo.declare("dojox.charting.action2d.MoveSlice",dojox.charting.action2d.Base,
{defaultParams:{duration:400,easing:dojo.fx.easing.backOut,scale:1.05,shift:7},optionalParams:{},constructor:function(a,d,b){b||(b={});this.scale=typeof b.scale=="number"?b.scale:1.05;this.shift=typeof b.shift=="number"?b.shift:7;this.connect()},process:function(a){if(a.shape&&a.element=="slice"&&a.type in this.overOutEvents){if(!this.angles){var e=f._degToRad(a.plot.opt.startAngle);this.angles=typeof a.run.data[0]=="number"?d.map(d.scanl(a.run.data,"+",e),"* 2 * Math.PI / this",d.foldl(a.run.data,
"+",0)):d.map(d.scanl(a.run.data,"a + b.y",e),"* 2 * Math.PI / this",d.foldl(a.run.data,"a + b.y",0))}var b=a.index,c,g,h,i,j;c=(this.angles[b]+this.angles[b+1])/2;var e=f.rotateAt(-c,a.cx,a.cy),k=f.rotateAt(c,a.cx,a.cy);(c=this.anim[b])?c.action.stop(!0):this.anim[b]=c={};a.type=="onmouseover"?(i=0,j=this.shift,g=1,h=this.scale):(i=this.shift,j=0,g=this.scale,h=1);c.action=dojox.gfx.fx.animateTransform({shape:a.shape,duration:this.duration,easing:this.easing,transform:[k,{name:"translate",start:[i,
0],end:[j,0]},{name:"scaleAt",start:[g,a.cx,a.cy],end:[h,a.cx,a.cy]},e]});a.type=="onmouseout"&&dojo.connect(c.action,"onEnd",this,function(){delete this.anim[b]});c.action.play()}},reset:function(){delete this.angles}})}());