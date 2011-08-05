/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.dtl.Inline"])dojo._hasResource["dojox.dtl.Inline"]=!0,dojo.provide("dojox.dtl.Inline"),dojo.require("dojox.dtl._base"),dojo.require("dijit._Widget"),dojox.dtl.Inline=dojo.extend(function(b,a){this.create(b,a)},dijit._Widget.prototype,{context:null,render:function(b){this.context=b||this.context;this.postMixInProperties();dojo.query("*",this.domNode).orphan();this.domNode.innerHTML=this.template.render(this.context)},declaredClass:"dojox.dtl.Inline",buildRendering:function(){var b=
this.domNode=document.createElement("div"),a=this.srcNodeRef;a.parentNode&&a.parentNode.replaceChild(b,a);this.template=new dojox.dtl.Template(dojo.trim(a.text),!0);this.render()},postMixInProperties:function(){this.context=this.context.get===dojox.dtl._Context.prototype.get?this.context:new dojox.dtl._Context(this.context)}});