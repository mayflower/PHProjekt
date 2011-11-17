/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojo.NodeList-fx"]||(dojo._hasResource["dojo.NodeList-fx"]=!0,dojo.provide("dojo.NodeList-fx"),dojo.require("dojo.fx"),dojo.extend(dojo.NodeList,{_anim:function(a,e,c){var c=c||{},d=dojo.fx.combine(this.map(function(b){b={node:b};dojo.mixin(b,c);return a[e](b)}));return c.auto?d.play()&&this:d},wipeIn:function(a){return this._anim(dojo.fx,"wipeIn",a)},wipeOut:function(a){return this._anim(dojo.fx,"wipeOut",a)},slideTo:function(a){return this._anim(dojo.fx,"slideTo",a)},fadeIn:function(a){return this._anim(dojo,
"fadeIn",a)},fadeOut:function(a){return this._anim(dojo,"fadeOut",a)},animateProperty:function(a){return this._anim(dojo,"animateProperty",a)},anim:function(a,e,c,d,b){var f=dojo.fx.combine(this.map(function(b){return dojo.animateProperty({node:b,properties:a,duration:e||350,easing:c})}));d&&dojo.connect(f,"onEnd",d);return f.play(b||0)}}));