/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.charting.action2d.Shake"]||(dojo._hasResource["dojox.charting.action2d.Shake"]=!0,dojo.provide("dojox.charting.action2d.Shake"),dojo.require("dojox.charting.action2d.Base"),dojo.require("dojox.gfx.matrix"),dojo.require("dojo.fx"),function(){var h=dojox.gfx.matrix,g=dojox.gfx.fx;dojo.declare("dojox.charting.action2d.Shake",dojox.charting.action2d.Base,{defaultParams:{duration:400,easing:dojo.fx.easing.backOut,shiftX:3,shiftY:3},optionalParams:{},constructor:function(a,c,b){b||
(b={});this.shiftX=typeof b.shiftX=="number"?b.shiftX:3;this.shiftY=typeof b.shiftY=="number"?b.shiftY:3;this.connect()},process:function(a){if(a.shape&&a.type in this.overOutEvents){var c=a.run.name,b=a.index,e=[],d;c in this.anim?d=this.anim[c][b]:this.anim[c]={};d?d.action.stop(!0):this.anim[c][b]=d={};var f={shape:a.shape,duration:this.duration,easing:this.easing,transform:[{name:"translate",start:[this.shiftX,this.shiftY],end:[0,0]},h.identity]};a.shape&&e.push(g.animateTransform(f));if(a.oultine)f.shape=
a.outline,e.push(g.animateTransform(f));if(a.shadow)f.shape=a.shadow,e.push(g.animateTransform(f));e.length?(d.action=dojo.fx.combine(e),a.type=="onmouseout"&&dojo.connect(d.action,"onEnd",this,function(){this.anim[c]&&delete this.anim[c][b]}),d.action.play()):delete this.anim[c][b]}}})}());