/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.dtl.render.dom"])dojo._hasResource["dojox.dtl.render.dom"]=!0,dojo.provide("dojox.dtl.render.dom"),dojo.require("dojox.dtl.Context"),dojo.require("dojox.dtl.dom"),dojox.dtl.render.dom.Render=function(a,b){this._tpl=b;this.domNode=dojo.byId(a)},dojo.extend(dojox.dtl.render.dom.Render,{setAttachPoint:function(a){this.domNode=a},render:function(a,b,c){if(!this.domNode)throw Error("You cannot use the Render object without specifying where you want to render it");this._tpl=
b=b||this._tpl;c=c||b.getBuffer();a=a||new dojox.dtl.Context;a=b.render(a,c).getParent();if(!a)throw Error("Rendered template does not have a root node");if(this.domNode!==a)this.domNode.parentNode.replaceChild(a,this.domNode),this.domNode=a}});