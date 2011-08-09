/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.drawing.ui.dom.Zoom"])dojo._hasResource["dojox.drawing.ui.dom.Zoom"]=!0,dojo.provide("dojox.drawing.ui.dom.Zoom"),dojo.require("dojox.drawing.plugins._Plugin"),dojox.drawing.ui.dom.Zoom=dojox.drawing.util.oo.declare(dojox.drawing.plugins._Plugin,function(c){this.domNode=dojo.create("div",{id:"btnZoom","class":"toolCombo"},c.node,"replace");this.makeButton("ZoomIn",this.topClass);this.makeButton("Zoom100",this.midClass);this.makeButton("ZoomOut",this.botClass)},{type:"dojox.drawing.ui.dom.Zoom",
zoomInc:0.1,maxZoom:10,minZoom:0.1,zoomFactor:1,baseClass:"drawingButton",topClass:"toolComboTop",midClass:"toolComboMid",botClass:"toolComboBot",makeButton:function(c,d){var a=dojo.create("div",{id:"btn"+c,"class":this.baseClass+" "+d,innerHTML:'<div title="Zoom In" class="icon icon'+c+'"></div>'},this.domNode);dojo.connect(document,"mouseup",function(b){dojo.stopEvent(b);dojo.removeClass(a,"active")});dojo.connect(a,"mouseup",this,function(b){dojo.stopEvent(b);dojo.removeClass(a,"active");this["on"+
c]()});dojo.connect(a,"mouseover",function(b){dojo.stopEvent(b);dojo.addClass(a,"hover")});dojo.connect(a,"mousedown",this,function(b){dojo.stopEvent(b);dojo.addClass(a,"active")});dojo.connect(a,"mouseout",this,function(b){dojo.stopEvent(b);dojo.removeClass(a,"hover")})},onZoomIn:function(){this.zoomFactor+=this.zoomInc;this.zoomFactor=Math.min(this.zoomFactor,this.maxZoom);this.canvas.setZoom(this.zoomFactor);this.mouse.setZoom(this.zoomFactor)},onZoom100:function(){this.zoomFactor=1;this.canvas.setZoom(this.zoomFactor);
this.mouse.setZoom(this.zoomFactor)},onZoomOut:function(){this.zoomFactor-=this.zoomInc;this.zoomFactor=Math.max(this.zoomFactor,this.minZoom);this.canvas.setZoom(this.zoomFactor);this.mouse.setZoom(this.zoomFactor)}});