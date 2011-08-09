/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dijit.DialogUnderlay"]||(dojo._hasResource["dijit.DialogUnderlay"]=!0,dojo.provide("dijit.DialogUnderlay"),dojo.require("dojo.window"),dojo.require("dijit._Widget"),dojo.require("dijit._Templated"),dojo.declare("dijit.DialogUnderlay",[dijit._Widget,dijit._Templated],{templateString:"<div class='dijitDialogUnderlayWrapper'><div class='dijitDialogUnderlay' dojoAttachPoint='node'></div></div>",dialogId:"","class":"",attributeMap:{id:"domNode"},_setDialogIdAttr:function(a){dojo.attr(this.node,
"id",a+"_underlay");this._set("dialogId",a)},_setClassAttr:function(a){this.node.className="dijitDialogUnderlay "+a;this._set("class",a)},postCreate:function(){dojo.body().appendChild(this.domNode)},layout:function(){var a=this.node.style,b=this.domNode.style;b.display="none";var c=dojo.window.getBox();b.top=c.t+"px";b.left=c.l+"px";a.width=c.w+"px";a.height=c.h+"px";b.display="block"},show:function(){this.domNode.style.display="block";this.layout();this.bgIframe=new dijit.BackgroundIframe(this.domNode)},
hide:function(){this.bgIframe.destroy();delete this.bgIframe;this.domNode.style.display="none"}}));