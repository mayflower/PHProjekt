/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.mdnd.DropIndicator"]||(dojo._hasResource["dojox.mdnd.DropIndicator"]=!0,dojo.provide("dojox.mdnd.DropIndicator"),dojo.require("dojox.mdnd.AreaManager"),dojo.declare("dojox.mdnd.DropIndicator",null,{node:null,constructor:function(){var a=document.createElement("div"),b=document.createElement("div");a.appendChild(b);dojo.addClass(a,"dropIndicator");this.node=a},place:function(a,b,c){if(c)this.node.style.height=c.h+"px";try{return b?a.insertBefore(this.node,b):a.appendChild(this.node),
this.node}catch(d){return null}},remove:function(){if(this.node)this.node.style.height="",this.node.parentNode&&this.node.parentNode.removeChild(this.node)},destroy:function(){this.node&&(this.node.parentNode&&this.node.parentNode.removeChild(this.node),dojo._destroyElement(this.node),delete this.node)}}),function(){dojox.mdnd.areaManager()._dropIndicator=new dojox.mdnd.DropIndicator}());