/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.widget.gauge.BarIndicator"]||(dojo._hasResource["dojox.widget.gauge.BarIndicator"]=!0,dojo.provide("dojox.widget.gauge.BarIndicator"),dojo.require("dojox.widget.BarGauge"),dojo.experimental("dojox.widget.gauge.BarIndicator"),dojo.declare("dojox.widget.gauge.BarIndicator",[dojox.widget.gauge.BarLineIndicator],{_getShapes:function(){if(!this._gauge)return null;var b=this.value;if(b<this._gauge.min)b=this._gauge.min;if(b>this._gauge.max)b=this._gauge.max;b=this._gauge._getPosition(b);
b==this.dataX&&(b=this.dataX+1);var c=this._gauge.dataY+Math.floor((this._gauge.dataHeight-this.width)/2)+this.offset,a=[];a[0]=this._gauge.surface.createRect({x:this._gauge.dataX,y:c,width:b-this._gauge.dataX,height:this.width});a[0].setStroke({color:this.color});a[0].setFill(this.color);a[1]=this._gauge.surface.createLine({x1:this._gauge.dataX,y1:c,x2:b,y2:c});a[1].setStroke({color:this.highlight});this.highlight2&&(c--,a[2]=this._gauge.surface.createLine({x1:this._gauge.dataX,y1:c,x2:b,y2:c}),
a[2].setStroke({color:this.highlight2}));return a},_createShapes:function(b){for(var c in this.shapes){c=this.shapes[c];var a={},d;for(d in c)a[d]=c[d];if(c.shape.type=="line")a.shape.x2=b+a.shape.x1;else if(c.shape.type=="rect")a.width=b;c.setShape(a)}},_move:function(b){var c,a=this.value;if(a<this.min)a=this.min;if(a>this.max)a=this.max;c=this._gauge._getPosition(this.currentValue);this.currentValue=a;a=this._gauge._getPosition(a)-this._gauge.dataX;b?this._createShapes(a):c!=a&&(b=new dojo.Animation({curve:[c,
a],duration:this.duration,easing:this.easing}),dojo.connect(b,"onAnimate",dojo.hitch(this,this._createShapes)),b.play())}}));