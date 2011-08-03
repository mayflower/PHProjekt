/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.charting.action2d.Base"]||(dojo._hasResource["dojox.charting.action2d.Base"]=!0,dojo.provide("dojox.charting.action2d.Base"),dojo.require("dojo.fx.easing"),dojo.require("dojox.lang.functional.object"),dojo.require("dojox.gfx.fx"),function(){var d=dojo.fx.easing.backOut,c=dojox.lang.functional;dojo.declare("dojox.charting.action2d.Base",null,{overOutEvents:{onmouseover:1,onmouseout:1},constructor:function(b,e,a){this.chart=b;this.plot=e||"default";this.anim={};a||(a={});this.duration=
a.duration?a.duration:400;this.easing=a.easing?a.easing:d},connect:function(){this.handle=this.chart.connectToPlot(this.plot,this,"process")},disconnect:function(){if(this.handle)dojo.disconnect(this.handle),this.handle=null},reset:function(){},destroy:function(){this.disconnect();c.forIn(this.anim,function(b){c.forIn(b,function(b){b.action.stop(!0)})});this.anim={}}})}());