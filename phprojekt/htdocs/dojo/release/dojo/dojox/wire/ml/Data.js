/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.wire.ml.Data"]||(dojo._hasResource["dojox.wire.ml.Data"]=!0,dojo.provide("dojox.wire.ml.Data"),dojo.provide("dojox.wire.ml.DataProperty"),dojo.require("dijit._Widget"),dojo.require("dijit._Container"),dojo.require("dojox.wire.ml.util"),dojo.declare("dojox.wire.ml.Data",[dijit._Widget,dijit._Container],{startup:function(){this._initializeProperties()},_initializeProperties:function(a){if(!this._properties||a)this._properties={};var a=this.getChildren(),b;for(b in a){var d=
a[b];d instanceof dojox.wire.ml.DataProperty&&d.name&&this.setPropertyValue(d.name,d.getValue())}},getPropertyValue:function(a){return this._properties[a]},setPropertyValue:function(a,b){this._properties[a]=b}}),dojo.declare("dojox.wire.ml.DataProperty",[dijit._Widget,dijit._Container],{name:"",type:"",value:"",_getValueAttr:function(){return this.getValue()},getValue:function(){var a=this.value;if(this.type)if(this.type=="number")a=parseInt(a);else if(this.type=="boolean")a=a=="true";else if(this.type==
"array"){var a=[],b=this.getChildren(),d;for(d in b){var c=b[d];c instanceof dojox.wire.ml.DataProperty&&a.push(c.getValue())}}else if(this.type=="object")for(d in a={},b=this.getChildren(),b)c=b[d],c instanceof dojox.wire.ml.DataProperty&&c.name&&(a[c.name]=c.getValue());else if(this.type=="element")for(d in a=new dojox.wire.ml.XmlElement(a),b=this.getChildren(),b)c=b[d],c instanceof dojox.wire.ml.DataProperty&&c.name&&a.setPropertyValue(c.name,c.getValue());return a}}));