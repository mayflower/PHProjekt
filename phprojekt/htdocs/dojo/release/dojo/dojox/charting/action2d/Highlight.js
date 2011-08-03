/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.charting.action2d.Highlight"]||(dojo._hasResource["dojox.charting.action2d.Highlight"]=!0,dojo.provide("dojox.charting.action2d.Highlight"),dojo.require("dojox.charting.action2d.Base"),dojo.require("dojox.color"),function(){var e=dojox.color,h=function(a){return function(){return a}},i=function(a){a=(new e.Color(a)).toHsl();a.s==0?a.l=a.l<50?100:0:(a.s=100,a.l=a.l<50?75:a.l>75?50:a.l-50>75-a.l?50:75);return e.fromHsl(a)};dojo.declare("dojox.charting.action2d.Highlight",dojox.charting.action2d.Base,
{defaultParams:{duration:400,easing:dojo.fx.easing.backOut},optionalParams:{highlight:"red"},constructor:function(a,c,d){this.colorFun=(a=d&&d.highlight)?dojo.isFunction(a)?a:h(a):i;this.connect()},process:function(a){if(a.shape&&a.type in this.overOutEvents){var c=a.run.name,d=a.index,b;c in this.anim?b=this.anim[c][d]:this.anim[c]={};if(b)b.action.stop(!0);else{b=a.shape.getFill();if(!b||!(b instanceof dojo.Color))return;this.anim[c][d]=b={start:b,end:this.colorFun(b)}}var f=b.start,g=b.end;if(a.type==
"onmouseout")var e=f,f=g,g=e;b.action=dojox.gfx.fx.animateFill({shape:a.shape,duration:this.duration,easing:this.easing,color:{start:f,end:g}});a.type=="onmouseout"&&dojo.connect(b.action,"onEnd",this,function(){this.anim[c]&&delete this.anim[c][d]});b.action.play()}}})}());