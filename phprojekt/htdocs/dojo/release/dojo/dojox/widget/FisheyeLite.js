/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.widget.FisheyeLite"]||(dojo._hasResource["dojox.widget.FisheyeLite"]=!0,dojo.provide("dojox.widget.FisheyeLite"),dojo.experimental("dojox.widget.FisheyeLite"),dojo.require("dijit._Widget"),dojo.require("dojo.fx.easing"),dojo.declare("dojox.widget.FisheyeLite",dijit._Widget,{durationIn:350,easeIn:dojo.fx.easing.backOut,durationOut:1420,easeOut:dojo.fx.easing.elasticOut,properties:null,units:"px",constructor:function(b){this.properties=b.properties||{fontSize:2.75}},postCreate:function(){this.inherited(arguments);
this._target=dojo.query(".fisheyeTarget",this.domNode)[0]||this.domNode;this._makeAnims();this.connect(this.domNode,"onmouseover","show");this.connect(this.domNode,"onmouseout","hide");this.connect(this._target,"onclick","onClick")},show:function(){this._runningOut.stop();this._runningIn.play()},hide:function(){this._runningIn.stop();this._runningOut.play()},_makeAnims:function(){var b={},d={},f=dojo.getComputedStyle(this._target),a;for(a in this.properties){var c=this.properties[a],g=dojo.isObject(c),
e=parseInt(f[a]);d[a]={end:e,units:this.units};b[a]=g?c:{end:c*e,units:this.units}}this._runningIn=dojo.animateProperty({node:this._target,easing:this.easeIn,duration:this.durationIn,properties:b});this._runningOut=dojo.animateProperty({node:this._target,duration:this.durationOut,easing:this.easeOut,properties:d});this.connect(this._runningIn,"onEnd",dojo.hitch(this,"onSelected",this))},onClick:function(){},onSelected:function(){}}));