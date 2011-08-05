/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.charting.themes.gradientGenerator"]||(dojo._hasResource["dojox.charting.themes.gradientGenerator"]=!0,dojo.provide("dojox.charting.themes.gradientGenerator"),dojo.require("dojox.charting.Theme"),function(){var b=dojox.charting.themes.gradientGenerator;b.generateFills=function(a,d,e,c){var f=dojox.charting.Theme;return dojo.map(a,function(a){return f.generateHslGradient(a,d,e,c)})};b.updateFills=function(a,d,e,c){var f=dojox.charting.Theme;dojo.forEach(a,function(a){if(a.fill&&
!a.fill.type)a.fill=f.generateHslGradient(a.fill,d,e,c)})};b.generateMiniTheme=function(a,d,e,c,f){var b=dojox.charting.Theme;return dojo.map(a,function(a){a=new dojox.color.Color(a);return{fill:b.generateHslGradient(a,d,e,c),stroke:{color:b.generateHslColor(a,f)}}})};b.generateGradientByIntensity=function(a,b){a=new dojo.Color(a);return dojo.map(b,function(b){var c=b.i/255;return{offset:b.o,color:new dojo.Color([a.r*c,a.g*c,a.b*c,a.a])}})}}());