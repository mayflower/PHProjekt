/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.gfx.Mover"]||(dojo._hasResource["dojox.gfx.Mover"]=!0,dojo.provide("dojox.gfx.Mover"),dojo.declare("dojox.gfx.Mover",null,{constructor:function(a,b,c){this.shape=a;this.lastX=b.clientX;this.lastY=b.clientY;a=this.host=c;b=document;c=dojo.connect(b,"onmousemove",this,"onFirstMove");this.events=[dojo.connect(b,"onmousemove",this,"onMouseMove"),dojo.connect(b,"onmouseup",this,"destroy"),dojo.connect(b,"ondragstart",dojo,"stopEvent"),dojo.connect(b,"onselectstart",dojo,"stopEvent"),
c];if(a&&a.onMoveStart)a.onMoveStart(this)},onMouseMove:function(a){var b=a.clientX,c=a.clientY;this.host.onMove(this,{dx:b-this.lastX,dy:c-this.lastY});this.lastX=b;this.lastY=c;dojo.stopEvent(a)},onFirstMove:function(){this.host.onFirstMove(this);dojo.disconnect(this.events.pop())},destroy:function(){dojo.forEach(this.events,dojo.disconnect);var a=this.host;if(a&&a.onMoveStop)a.onMoveStop(this);this.events=this.shape=null}}));