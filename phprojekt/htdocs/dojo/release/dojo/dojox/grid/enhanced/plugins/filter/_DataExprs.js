/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.grid.enhanced.plugins.filter._DataExprs"]||(dojo._hasResource["dojox.grid.enhanced.plugins.filter._DataExprs"]=!0,dojo.provide("dojox.grid.enhanced.plugins.filter._DataExprs"),dojo.require("dojox.grid.enhanced.plugins.filter._ConditionExpr"),dojo.require("dojo.date.locale"),function(){var b=dojox.grid.enhanced.plugins.filter;dojo.declare("dojox.grid.enhanced.plugins.filter.BooleanExpr",b._DataExpr,{_name:"bool",_convertData:function(a){return!!a}});dojo.declare("dojox.grid.enhanced.plugins.filter.StringExpr",
b._DataExpr,{_name:"string",_convertData:function(a){return String(a)}});dojo.declare("dojox.grid.enhanced.plugins.filter.NumberExpr",b._DataExpr,{_name:"number",_convertDataToExpr:function(a){return parseFloat(a)}});dojo.declare("dojox.grid.enhanced.plugins.filter.DateExpr",b._DataExpr,{_name:"date",_convertData:function(a){if(a instanceof Date)return a;else if(typeof a=="number")return new Date(a);else{var b=dojo.date.locale.parse(String(a),dojo.mixin({selector:this._name},this._convertArgs));if(!b)throw Error("Datetime parse failed: "+
a);return b}},toObject:function(){if(this._value instanceof Date){var a=this._value;this._value=this._value.valueOf();var b=this.inherited(arguments);this._value=a;return b}else return this.inherited(arguments)}});dojo.declare("dojox.grid.enhanced.plugins.filter.TimeExpr",b.DateExpr,{_name:"time"})}());