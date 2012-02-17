/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.widget.gauge.AnalogArrowIndicator"]||(dojo._hasResource["dojox.widget.gauge.AnalogArrowIndicator"]=!0,dojo.provide("dojox.widget.gauge.AnalogArrowIndicator"),dojo.require("dojox.widget.AnalogGauge"),dojo.experimental("dojox.widget.gauge.AnalogArrowIndicator"),dojo.declare("dojox.widget.gauge.AnalogArrowIndicator",[dojox.widget.gauge.AnalogLineIndicator],{_getShapes:function(){if(!this._gauge)return null;var a=Math.floor(this.width/2),b=this.width*5,d=this.width&1,c=[];c[0]=
this._gauge.surface.createPolyline([{x:-a,y:0},{x:-a,y:-this.length+b},{x:-2*a,y:-this.length+b},{x:0,y:-this.length},{x:2*a+d,y:-this.length+b},{x:a+d,y:-this.length+b},{x:a+d,y:0},{x:-a,y:0}]).setStroke({color:this.color}).setFill(this.color);c[1]=this._gauge.surface.createLine({x1:-a,y1:0,x2:-a,y2:-this.length+b}).setStroke({color:this.highlight});c[2]=this._gauge.surface.createLine({x1:-a-3,y1:-this.length+b,x2:0,y2:-this.length}).setStroke({color:this.highlight});c[3]=this._gauge.surface.createCircle({cx:0,
cy:0,r:this.width}).setStroke({color:this.color}).setFill(this.color);return c}}));