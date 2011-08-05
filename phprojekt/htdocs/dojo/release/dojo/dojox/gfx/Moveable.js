/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.gfx.Moveable"]||(dojo._hasResource["dojox.gfx.Moveable"]=!0,dojo.provide("dojox.gfx.Moveable"),dojo.require("dojox.gfx.Mover"),dojo.declare("dojox.gfx.Moveable",null,{constructor:function(a,b){this.shape=a;this.delay=b&&b.delay>0?b.delay:0;this.mover=b&&b.mover?b.mover:dojox.gfx.Mover;this.events=[this.shape.connect("onmousedown",this,"onMouseDown")]},destroy:function(){dojo.forEach(this.events,this.shape.disconnect,this.shape);this.events=this.shape=null},onMouseDown:function(a){this.delay?
(this.events.push(this.shape.connect("onmousemove",this,"onMouseMove"),this.shape.connect("onmouseup",this,"onMouseUp")),this._lastX=a.clientX,this._lastY=a.clientY):new this.mover(this.shape,a,this);dojo.stopEvent(a)},onMouseMove:function(a){if(Math.abs(a.clientX-this._lastX)>this.delay||Math.abs(a.clientY-this._lastY)>this.delay)this.onMouseUp(a),new this.mover(this.shape,a,this);dojo.stopEvent(a)},onMouseUp:function(){this.shape.disconnect(this.events.pop());this.shape.disconnect(this.events.pop())},
onMoveStart:function(a){dojo.publish("/gfx/move/start",[a]);dojo.addClass(dojo.body(),"dojoMove")},onMoveStop:function(a){dojo.publish("/gfx/move/stop",[a]);dojo.removeClass(dojo.body(),"dojoMove")},onFirstMove:function(){},onMove:function(a,b){this.onMoving(a,b);this.shape.applyLeftTransform(b);this.onMoved(a,b)},onMoving:function(){},onMoved:function(){}}));