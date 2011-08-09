/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojo.fx.Toggler"]||(dojo._hasResource["dojo.fx.Toggler"]=!0,dojo.provide("dojo.fx.Toggler"),dojo.declare("dojo.fx.Toggler",null,{node:null,showFunc:dojo.fadeIn,hideFunc:dojo.fadeOut,showDuration:200,hideDuration:200,constructor:function(a){dojo.mixin(this,a);this.node=a.node;this._showArgs=dojo.mixin({},a);this._showArgs.node=this.node;this._showArgs.duration=this.showDuration;this.showAnim=this.showFunc(this._showArgs);this._hideArgs=dojo.mixin({},a);this._hideArgs.node=this.node;
this._hideArgs.duration=this.hideDuration;this.hideAnim=this.hideFunc(this._hideArgs);dojo.connect(this.showAnim,"beforeBegin",dojo.hitch(this.hideAnim,"stop",!0));dojo.connect(this.hideAnim,"beforeBegin",dojo.hitch(this.showAnim,"stop",!0))},show:function(a){return this.showAnim.play(a||0)},hide:function(a){return this.hideAnim.play(a||0)}}));