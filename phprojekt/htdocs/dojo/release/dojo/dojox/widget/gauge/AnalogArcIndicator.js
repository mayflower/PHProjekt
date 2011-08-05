/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.widget.gauge.AnalogArcIndicator"]||(dojo._hasResource["dojox.widget.gauge.AnalogArcIndicator"]=!0,dojo.provide("dojox.widget.gauge.AnalogArcIndicator"),dojo.require("dojox.widget.AnalogGauge"),dojo.experimental("dojox.widget.gauge.AnalogArcIndicator"),dojo.declare("dojox.widget.gauge.AnalogArcIndicator",[dojox.widget.gauge.AnalogLineIndicator],{_createArc:function(c){if(this.shapes[0]){var a=this._gauge._getRadians(this._gauge._getAngle(c)),f=Math.cos(a),g=Math.sin(a),e=this._gauge._getRadians(this._gauge.startAngle),
h=Math.cos(e),i=Math.sin(e),d=this.offset+this.width,b=["M"];b.push(this._gauge.cx+this.offset*i);b.push(this._gauge.cy-this.offset*h);b.push("A",this.offset,this.offset,0,a-e>Math.PI?1:0,1);b.push(this._gauge.cx+this.offset*g);b.push(this._gauge.cy-this.offset*f);b.push("L");b.push(this._gauge.cx+d*g);b.push(this._gauge.cy-d*f);b.push("A",d,d,0,a-e>Math.PI?1:0,0);b.push(this._gauge.cx+d*i);b.push(this._gauge.cy-d*h);this.shapes[0].setShape(b.join(" "));this.currentValue=c}},draw:function(c){var a=
this.value;if(a<this._gauge.min)a=this._gauge.min;if(a>this._gauge.max)a=this._gauge.max;if(this.shapes)c?this._createArc(a):(a=new dojo.Animation({curve:[this.currentValue,a],duration:this.duration,easing:this.easing}),dojo.connect(a,"onAnimate",dojo.hitch(this,this._createArc)),a.play());else{c={color:this.color,width:1};if(this.color.type)c.color=this.color.colors[0].color;this.shapes=[this._gauge.surface.createPath().setStroke(c).setFill(this.color)];this._createArc(a);this.hover&&this.shapes[0].getEventSource().setAttribute("hover",
this.hover);if(this.onDragMove&&!this.noChange)this._gauge.connect(this.shapes[0].getEventSource(),"onmousedown",this._gauge.handleMouseDown),this.shapes[0].getEventSource().style.cursor="pointer"}}}));