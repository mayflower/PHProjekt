/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.drawing.tools.Rect"])dojo._hasResource["dojox.drawing.tools.Rect"]=!0,dojo.provide("dojox.drawing.tools.Rect"),dojox.drawing.tools.Rect=dojox.drawing.util.oo.declare(dojox.drawing.stencil.Rect,function(){},{draws:!0,onDrag:function(a){var b=a.start,c=b.x<a.x?b.x:a.x,d=b.y<a.y?b.y:a.y,e=b.x<a.x?a.x-b.x:b.x-a.x,a=b.y<a.y?a.y-b.y:b.y-a.y;this.keys.shift&&(e=a=Math.max(e,a));this.keys.alt&&(c-=e,d-=a,e*=2,a*=2,c=Math.max(c,0),d=Math.max(d,0));this.setPoints([{x:c,y:d},{x:c+
e,y:d},{x:c+e,y:d+a},{x:c,y:d+a}]);this.render()},onUp:function(a){if(!this.created&&this._downOnCanvas){this._downOnCanvas=!1;if(this.shape){if(a=this.data,a.width<this.minimumSize&&a.height<this.minimumSize){this.remove(this.shape,this.hit);return}}else{var a=a.start,b=this.minimumSize*4;this.setPoints([{x:a.x,y:a.y},{x:a.x+b,y:a.y},{x:a.x+b,y:a.y+b},{x:a.x,y:a.y+b}]);this.render()}this.onRender(this)}}}),dojox.drawing.tools.Rect.setup={name:"dojox.drawing.tools.Rect",tooltip:'<span class="drawingTipTitle">Rectangle Tool</span><br/><span class="drawingTipDesc">SHIFT - constrain to square</span>',
iconClass:"iconRect"},dojox.drawing.register(dojox.drawing.tools.Rect.setup,"tool");