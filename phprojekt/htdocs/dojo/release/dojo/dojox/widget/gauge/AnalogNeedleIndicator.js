/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.widget.gauge.AnalogNeedleIndicator"]||(dojo._hasResource["dojox.widget.gauge.AnalogNeedleIndicator"]=!0,dojo.provide("dojox.widget.gauge.AnalogNeedleIndicator"),dojo.require("dojox.widget.AnalogGauge"),dojo.experimental("dojox.widget.gauge.AnalogNeedleIndicator"),dojo.declare("dojox.widget.gauge.AnalogNeedleIndicator",[dojox.widget.gauge.AnalogLineIndicator],{_getShapes:function(){if(!this._gauge)return null;var b=Math.floor(this.width/2),c=[],d={color:this.color,width:1};
if(this.color.type)d.color=this.color.colors[0].color;var a=Math.sqrt(2)*b;c[0]=this._gauge.surface.createPath().setStroke(d).setFill(this.color).moveTo(a,-a).arcTo(2*b,2*b,0,0,0,-a,-a).lineTo(0,-this.length).closePath();c[1]=this._gauge.surface.createCircle({cx:0,cy:0,r:this.width}).setStroke({color:this.color}).setFill(this.color);return c}}));