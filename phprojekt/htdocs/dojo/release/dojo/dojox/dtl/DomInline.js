/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.dtl.DomInline"])dojo._hasResource["dojox.dtl.DomInline"]=!0,dojo.provide("dojox.dtl.DomInline"),dojo.require("dojox.dtl.dom"),dojo.require("dijit._Widget"),dojox.dtl.DomInline=dojo.extend(function(a,b){this.create(a,b)},dijit._Widget.prototype,{context:null,render:function(a){this.context=a||this.context;this.postMixInProperties();a=this.template.render(this.context).getRootNode();if(a!=this.containerNode)this.containerNode.parentNode.replaceChild(a,this.containerNode),
this.containerNode=a},declaredClass:"dojox.dtl.Inline",buildRendering:function(){var a=this.domNode=document.createElement("div");this.containerNode=a.appendChild(document.createElement("div"));var b=this.srcNodeRef;b.parentNode&&b.parentNode.replaceChild(a,b);this.template=new dojox.dtl.DomTemplate(dojo.trim(b.text),!0);this.render()},postMixInProperties:function(){this.context=this.context.get===dojox.dtl._Context.prototype.get?this.context:new dojox.dtl.Context(this.context)}});