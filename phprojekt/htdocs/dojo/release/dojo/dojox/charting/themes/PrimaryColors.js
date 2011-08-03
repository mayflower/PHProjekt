/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.charting.themes.PrimaryColors"]||(dojo._hasResource["dojox.charting.themes.PrimaryColors"]=!0,dojo.provide("dojox.charting.themes.PrimaryColors"),dojo.require("dojox.charting.Theme"),dojo.require("dojox.charting.themes.gradientGenerator"),function(){var a=dojox.charting,b=a.themes;b.PrimaryColors=new a.Theme({seriesThemes:b.gradientGenerator.generateMiniTheme(["#f00","#0f0","#00f","#ff0","#0ff","#f0f"],{type:"linear",space:"plot",x1:0,y1:0,x2:0,y2:100},90,40,25)})}());