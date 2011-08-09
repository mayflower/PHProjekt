/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojo.dnd.TimedMoveable"]||(dojo._hasResource["dojo.dnd.TimedMoveable"]=!0,dojo.provide("dojo.dnd.TimedMoveable"),dojo.require("dojo.dnd.Moveable"),function(){var c=dojo.dnd.Moveable.prototype.onMove;dojo.declare("dojo.dnd.TimedMoveable",dojo.dnd.Moveable,{timeout:40,constructor:function(a,b){b||(b={});if(b.timeout&&typeof b.timeout=="number"&&b.timeout>=0)this.timeout=b.timeout},markupFactory:function(a,b){return new dojo.dnd.TimedMoveable(b,a)},onMoveStop:function(a){a._timer&&
(clearTimeout(a._timer),c.call(this,a,a._leftTop));dojo.dnd.Moveable.prototype.onMoveStop.apply(this,arguments)},onMove:function(a,b){a._leftTop=b;if(!a._timer){var d=this;a._timer=setTimeout(function(){a._timer=null;c.call(d,a,a._leftTop)},this.timeout)}}})}());