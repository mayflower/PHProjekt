/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.grid.enhanced.plugins.exporter._ExportWriter"]||(dojo._hasResource["dojox.grid.enhanced.plugins.exporter._ExportWriter"]=!0,dojo.provide("dojox.grid.enhanced.plugins.exporter._ExportWriter"),dojo.require("dojox.grid.enhanced.plugins.Exporter"),dojo.declare("dojox.grid.enhanced.plugins.exporter._ExportWriter",null,{constructor:function(){},_getExportDataForCell:function(c,d,b,a){a=(b.get||a.get).call(b,c,d);return this.formatter?this.formatter(a,b,c,d):a},beforeHeader:function(){return!0},
afterHeader:function(){},beforeContent:function(){return!0},afterContent:function(){},beforeContentRow:function(){return!0},afterContentRow:function(){},beforeView:function(){return!0},afterView:function(){},beforeSubrow:function(){return!0},afterSubrow:function(){},handleCell:function(){},toString:function(){return""}}));