/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.charting.plot2d._PlotEvents"]||(dojo._hasResource["dojox.charting.plot2d._PlotEvents"]=!0,dojo.provide("dojox.charting.plot2d._PlotEvents"),dojo.declare("dojox.charting.plot2d._PlotEvents",null,{constructor:function(){this._shapeEvents=[];this._eventSeries={}},destroy:function(){this.resetEvents();this.inherited(arguments)},plotEvent:function(){},raiseEvent:function(a){this.plotEvent(a);var b=dojo.delegate(a);b.originalEvent=a.type;b.originalPlot=a.plot;b.type="onindirect";
dojo.forEach(this.chart.stack,function(a){if(a!==this&&a.plotEvent)b.plot=a,a.plotEvent(b)},this)},connect:function(a,b){this.dirty=!0;return dojo.connect(this,"plotEvent",a,b)},events:function(){var a=this.plotEvent._listeners;if(!a||!a.length)return!1;for(var b in a)if(!(b in Array.prototype))return!0;return!1},resetEvents:function(){if(this._shapeEvents.length)dojo.forEach(this._shapeEvents,function(a){a.shape.disconnect(a.handle)}),this._shapeEvents=[];this.raiseEvent({type:"onplotreset",plot:this})},
_connectSingleEvent:function(a,b){this._shapeEvents.push({shape:a.eventMask,handle:a.eventMask.connect(b,this,function(c){a.type=b;a.event=c;this.raiseEvent(a);a.event=null})})},_connectEvents:function(a){if(a)a.chart=this.chart,a.plot=this,a.hAxis=this.hAxis||null,a.vAxis=this.vAxis||null,a.eventMask=a.eventMask||a.shape,this._connectSingleEvent(a,"onmouseover"),this._connectSingleEvent(a,"onmouseout"),this._connectSingleEvent(a,"onclick")},_reconnectEvents:function(a){(a=this._eventSeries[a])&&
dojo.forEach(a,this._connectEvents,this)},fireEvent:function(a,b,c,d){if((a=this._eventSeries[a])&&a.length&&c<a.length)c=a[c],c.type=b,c.event=d||null,this.raiseEvent(c),c.event=null}}));