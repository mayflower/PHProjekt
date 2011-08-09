/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.charting.themes.ThreeD"]||(dojo._hasResource["dojox.charting.themes.ThreeD"]=!0,dojo.provide("dojox.charting.themes.ThreeD"),dojo.require("dojo.colors"),dojo.require("dojox.charting.Theme"),dojo.require("dojox.charting.themes.gradientGenerator"),dojo.require("dojox.charting.themes.PrimaryColors"),function(){var d=dojox.charting,b=d.themes,g=d.Theme,e={type:"linear",space:"shape",x1:0,y1:0,x2:100,y2:0},i=[{o:0,i:174},{o:0.08,i:231},{o:0.18,i:237},{o:0.3,i:231},{o:0.39,i:221},
{o:0.49,i:206},{o:0.58,i:187},{o:0.68,i:165},{o:0.8,i:128},{o:0.9,i:102},{o:1,i:174}],j=dojo.map(["#f00","#0f0","#00f","#ff0","#0ff","#f0f"],function(a){var h=dojo.delegate(e),a=(h.colors=b.gradientGenerator.generateGradientByIntensity(a,i))[2].color;a.r+=100;a.g+=100;a.b+=100;a.sanitize();return h});b.ThreeD=b.PrimaryColors.clone();b.ThreeD.series.shadow={dx:1,dy:1,width:3,color:[0,0,0,0.15]};b.ThreeD.next=function(a,b,d){if(a=="bar"||a=="column"){var c=this._current%this.seriesThemes.length,f=this.seriesThemes[c],
e=f.fill;f.fill=j[c];c=g.prototype.next.apply(this,arguments);f.fill=e;return c}return g.prototype.next.apply(this,arguments)}}());