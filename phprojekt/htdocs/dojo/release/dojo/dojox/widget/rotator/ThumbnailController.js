/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.widget.rotator.ThumbnailController"]||(dojo._hasResource["dojox.widget.rotator.ThumbnailController"]=!0,dojo.provide("dojox.widget.rotator.ThumbnailController"),function(a){a.declare("dojox.widget.rotator.ThumbnailController",null,{rotator:null,constructor:function(b,c){a.mixin(this,b);this._domNode=c;var d=this.rotator;if(d){for(;c.firstChild;)c.removeChild(c.firstChild);for(var e=0;e<d.panes.length;e++){var f=d.panes[e].node,g=a.attr(f,"thumbsrc")||a.attr(f,"src"),h=a.attr(f,
"alt")||"";/img/i.test(f.tagName)&&function(b){a.create("a",{classname:"dojoxRotatorThumb dojoxRotatorThumb"+b+" "+(b==d.idx?"dojoxRotatorThumbSelected":""),href:g,onclick:function(c){a.stopEvent(c);d&&d.control.apply(d,["go",b])},title:h,innerHTML:'<img src="'+g+'" alt="'+h+'"/>'},c)}(e)}this._con=a.connect(d,"onUpdate",this,"_onUpdate")}},destroy:function(){a.disconnect(this._con);a.destroy(this._domNode)},_onUpdate:function(b){var c=this.rotator;b=="onAfterTransition"&&(b=a.query(".dojoxRotatorThumb",
this._domNode).removeClass("dojoxRotatorThumbSelected"),c.idx<b.length&&a.addClass(b[c.idx],"dojoxRotatorThumbSelected"))}})}(dojo));