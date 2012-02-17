/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.gfx"]||(dojo._hasResource["dojox.gfx"]=!0,dojo.provide("dojox.gfx"),dojo.require("dojox.gfx.matrix"),dojo.require("dojox.gfx._base"),dojo.loadInit(function(){for(var a=dojo.getObject("dojox.gfx",!0),c,b;!a.renderer;){if(dojo.config.forceGfxRenderer){dojox.gfx.renderer=dojo.config.forceGfxRenderer;break}for(var e=(typeof dojo.config.gfxRenderer=="string"?dojo.config.gfxRenderer:"svg,vml,canvas,silverlight").split(","),d=0;d<e.length;++d){switch(e[d]){case "svg":if("SVGAngle"in
dojo.global)dojox.gfx.renderer="svg";break;case "vml":if(dojo.isIE)dojox.gfx.renderer="vml";break;case "silverlight":try{dojo.isIE?(c=new ActiveXObject("AgControl.AgControl"))&&c.IsVersionSupported("1.0")&&(b=!0):navigator.plugins["Silverlight Plug-In"]&&(b=!0)}catch(f){b=!1}finally{c=null}if(b)dojox.gfx.renderer="silverlight";break;case "canvas":if(dojo.global.CanvasRenderingContext2D)dojox.gfx.renderer="canvas"}if(a.renderer)break}break}dojo.config.isDebug&&console.log("gfx renderer = "+a.renderer);
a[a.renderer]?a.switchTo(a.renderer):(a.loadAndSwitch=a.renderer,dojo.require("dojox.gfx."+a.renderer))}));