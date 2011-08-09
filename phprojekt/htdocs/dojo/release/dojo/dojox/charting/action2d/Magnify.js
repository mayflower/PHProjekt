/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.charting.action2d.Magnify"]||(dojo._hasResource["dojox.charting.action2d.Magnify"]=!0,dojo.provide("dojox.charting.action2d.Magnify"),dojo.require("dojox.charting.action2d.Base"),dojo.require("dojox.gfx.matrix"),dojo.require("dojo.fx"),function(){var i=dojox.gfx.matrix,g=dojox.gfx.fx;dojo.declare("dojox.charting.action2d.Magnify",dojox.charting.action2d.Base,{defaultParams:{duration:400,easing:dojo.fx.easing.backOut,scale:2},optionalParams:{},constructor:function(a,c,d){this.scale=
d&&typeof d.scale=="number"?d.scale:2;this.connect()},process:function(a){if(a.shape&&a.type in this.overOutEvents&&"cx"in a&&"cy"in a){var c=a.run.name,d=a.index,f=[],e,b,h;c in this.anim?e=this.anim[c][d]:this.anim[c]={};e?e.action.stop(!0):this.anim[c][d]=e={};a.type=="onmouseover"?(b=i.identity,h=this.scale):(b=i.scaleAt(this.scale,a.cx,a.cy),h=1/this.scale);b={shape:a.shape,duration:this.duration,easing:this.easing,transform:[{name:"scaleAt",start:[1,a.cx,a.cy],end:[h,a.cx,a.cy]},b]};a.shape&&
f.push(g.animateTransform(b));if(a.oultine)b.shape=a.outline,f.push(g.animateTransform(b));if(a.shadow)b.shape=a.shadow,f.push(g.animateTransform(b));f.length?(e.action=dojo.fx.combine(f),a.type=="onmouseout"&&dojo.connect(e.action,"onEnd",this,function(){this.anim[c]&&delete this.anim[c][d]}),e.action.play()):delete this.anim[c][d]}}})}());