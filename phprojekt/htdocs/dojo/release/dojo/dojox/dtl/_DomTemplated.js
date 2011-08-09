/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.dtl._DomTemplated"])dojo._hasResource["dojox.dtl._DomTemplated"]=!0,dojo.provide("dojox.dtl._DomTemplated"),dojo.require("dijit._Templated"),dojo.require("dojox.dtl.dom"),dojo.require("dojox.dtl.render.dom"),dojo.require("dojox.dtl.contrib.dijit"),dojox.dtl._DomTemplated=function(){},dojox.dtl._DomTemplated.prototype={_dijitTemplateCompat:!1,buildRendering:function(){this.domNode=this.srcNodeRef;if(!this._render){var a=dojox.dtl.contrib.dijit,b=a.widgetsInTemplate;a.widgetsInTemplate=
this.widgetsInTemplate;this.template=this.template||this._getCachedTemplate(this.templatePath,this.templateString);this._render=new dojox.dtl.render.dom.Render(this.domNode,this.template);a.widgetsInTemplate=b}a=this._getContext();this._created||delete a._getter;this.render(a);this.domNode=this.template.getRootNode();this.srcNodeRef&&this.srcNodeRef.parentNode&&(dojo.destroy(this.srcNodeRef),delete this.srcNodeRef)},setTemplate:function(a,b){this.template=dojox.dtl.text._isTemplate(a)?this._getCachedTemplate(null,
a):this._getCachedTemplate(a);this.render(b)},render:function(a,b){if(b)this.template=b;this._render.render(this._getContext(a),this.template)},_getContext:function(a){a instanceof dojox.dtl.Context||(a=!1);a=a||new dojox.dtl.Context(this);a.setThis(this);return a},_getCachedTemplate:function(a,b){if(!this._templates)this._templates={};var c=b||a.toString(),d=this._templates;return d[c]?d[c]:d[c]=new dojox.dtl.DomTemplate(dijit._Templated.getCachedTemplate(a,b,!0))}};