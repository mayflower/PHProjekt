/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.form.TimeSpinner"]||(dojo._hasResource["dojox.form.TimeSpinner"]=!0,dojo.provide("dojox.form.TimeSpinner"),dojo.require("dijit.form._Spinner"),dojo.require("dojo.date"),dojo.require("dojo.date.locale"),dojo.require("dojo.date.stamp"),dojo.declare("dojox.form.TimeSpinner",[dijit.form._Spinner],{required:!1,adjust:function(a,b){return dojo.date.add(a,"minute",b)},isValid:function(){return!0},smallDelta:5,largeDelta:30,timeoutChangeRate:0.5,parse:function(a){return dojo.date.locale.parse(a,
{selector:"time",formatLength:"short"})},format:function(a){return dojo.isString(a)?a:dojo.date.locale.format(a,{selector:"time",formatLength:"short"})},serialize:dojo.date.stamp.toISOString,value:"12:00 AM",_onKeyPress:function(a){if((a.charOrCode==dojo.keys.HOME||a.charOrCode==dojo.keys.END)&&!a.ctrlKey&&!a.altKey&&!a.metaKey&&typeof this.get("value")!="undefined"){var b=this.constraints[a.charOrCode==dojo.keys.HOME?"min":"max"];b&&this._setValueAttr(b,!0);dojo.stopEvent(a)}}}));