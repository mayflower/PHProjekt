/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.drawing.manager.StencilUI"]||(dojo._hasResource["dojox.drawing.manager.StencilUI"]=!0,dojo.provide("dojox.drawing.manager.StencilUI"),function(){dojox.drawing.manager.StencilUI=dojox.drawing.util.oo.declare(function(a){this.canvas=a.canvas;this.defaults=dojox.drawing.defaults.copy();this.mouse=a.mouse;this.keys=a.keys;this._mouseHandle=this.mouse.register(this);this.stencils={}},{register:function(a){return this.stencils[a.id]=a},onUiDown:function(a){if(this._isStencil(a))this.stencils[a.id].onDown(a)},
onUiUp:function(a){if(this._isStencil(a))this.stencils[a.id].onUp(a)},onOver:function(a){if(this._isStencil(a))this.stencils[a.id].onOver(a)},onOut:function(a){if(this._isStencil(a))this.stencils[a.id].onOut(a)},_isStencil:function(a){return!!a.id&&!!this.stencils[a.id]&&this.stencils[a.id].type=="drawing.library.UI.Button"}})}());