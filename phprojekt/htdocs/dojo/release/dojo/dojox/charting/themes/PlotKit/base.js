/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.charting.themes.PlotKit.base"]||(dojo._hasResource["dojox.charting.themes.PlotKit.base"]=!0,dojo.provide("dojox.charting.themes.PlotKit.base"),dojo.require("dojox.charting.Theme"),function(){var c=dojox.charting,b=c.themes.PlotKit;b.base=new c.Theme({chart:{stroke:null,fill:"yellow"},plotarea:{stroke:null,fill:"yellow"},axis:{stroke:{color:"#fff",width:1},line:{color:"#fff",width:0.5},majorTick:{color:"#fff",width:0.5,length:6},minorTick:{color:"#fff",width:0.5,length:3},
tick:{font:"normal normal normal 7pt Helvetica,Arial,sans-serif",fontColor:"#999"}},series:{stroke:{width:2.5,color:"#fff"},fill:"#666",font:"normal normal normal 7.5pt Helvetica,Arial,sans-serif",fontColor:"#666"},marker:{stroke:{width:2},fill:"#333",font:"normal normal normal 7pt Helvetica,Arial,sans-serif",fontColor:"#666"},colors:["red","green","blue"]});b.base.next=function(b,d,e){var a=c.Theme.prototype.next.apply(this,arguments);b=="line"?(a.marker.outline={width:2,color:"#fff"},a.series.stroke.width=
3.5,a.marker.stroke.width=2):b=="candlestick"?a.series.stroke.width=1:a.series.stroke.color="#fff";return a}}());