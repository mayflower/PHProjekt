/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.timing.Sequence"]||(dojo._hasResource["dojox.timing.Sequence"]=!0,dojo.provide("dojox.timing.Sequence"),dojo.experimental("dojox.timing.Sequence"),dojo.declare("dojox.timing.Sequence",null,{_goOnPause:0,_running:!1,constructor:function(){this._defsResolved=[]},go:function(b,a){this._running=!0;dojo.forEach(b,function(a){if(a.repeat>1)for(var d=a.repeat,b=0;b<d;b++)a.repeat=1,this._defsResolved.push(a);else this._defsResolved.push(a)},this);a&&this._defsResolved.push({func:a});
this._defsResolved.push({func:[this.stop,this]});this._curId=0;this._go()},_go:function(){function b(a){var b=null;return b=dojo.isArray(a)?a.length>2?a[0].apply(a[1],a.slice(2)):a[0].apply(a[1]):a()}if(this._running){var a=this._defsResolved[this._curId];this._curId+=1;if(this._curId>=this._defsResolved.length)b(a.func);else if(a.pauseAfter)b(a.func)!==!1?setTimeout(dojo.hitch(this,"_go"),a.pauseAfter):this._goOnPause=a.pauseAfter;else if(a.pauseBefore){var c=dojo.hitch(this,function(){b(a.func)!==
!1&&this._go()});setTimeout(c,a.pauseBefore)}else b(a.func)!==!1&&this._go()}},goOn:function(){this._goOnPause?(setTimeout(dojo.hitch(this,"_go"),this._goOnPause),this._goOnPause=0):this._go()},stop:function(){this._running=!1}}));