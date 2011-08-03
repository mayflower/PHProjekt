/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.drawing.plugins.tools.Zoom"]||(dojo._hasResource["dojox.drawing.plugins.tools.Zoom"]=!0,dojo.provide("dojox.drawing.plugins.tools.Zoom"),dojo.require("dojox.drawing.plugins._Plugin"),function(){var c=Math.pow(2,0.25),a=1,b=dojox.drawing.plugins.tools;b.ZoomIn=dojox.drawing.util.oo.declare(function(){},{});b.ZoomIn=dojox.drawing.util.oo.declare(dojox.drawing.plugins._Plugin,function(){},{type:"dojox.drawing.plugins.tools.ZoomIn",onZoomIn:function(){a*=c;a=Math.min(a,10);this.canvas.setZoom(a);
this.mouse.setZoom(a)},onClick:function(){this.onZoomIn()}});b.Zoom100=dojox.drawing.util.oo.declare(dojox.drawing.plugins._Plugin,function(){},{type:"dojox.drawing.plugins.tools.Zoom100",onZoom100:function(){a=1;this.canvas.setZoom(a);this.mouse.setZoom(a)},onClick:function(){this.onZoom100()}});b.ZoomOut=dojox.drawing.util.oo.declare(dojox.drawing.plugins._Plugin,function(){},{type:"dojox.drawing.plugins.tools.ZoomOut",onZoomOut:function(){a/=c;a=Math.max(a,0.1);this.canvas.setZoom(a);this.mouse.setZoom(a)},
onClick:function(){this.onZoomOut()}});b.ZoomIn.setup={name:"dojox.drawing.plugins.tools.ZoomIn",tooltip:"Zoom In"};dojox.drawing.register(b.ZoomIn.setup,"plugin");b.Zoom100.setup={name:"dojox.drawing.plugins.tools.Zoom100",tooltip:"Zoom to 100%"};dojox.drawing.register(b.Zoom100.setup,"plugin");b.ZoomOut.setup={name:"dojox.drawing.plugins.tools.ZoomOut",tooltip:"Zoom In"};dojox.drawing.register(b.ZoomOut.setup,"plugin")}());