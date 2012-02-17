/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.charting.Series"]||(dojo._hasResource["dojox.charting.Series"]=!0,dojo.provide("dojox.charting.Series"),dojo.require("dojox.charting.Element"),dojo.declare("dojox.charting.Series",dojox.charting.Element,{constructor:function(a,b,c){dojo.mixin(this,c);if(typeof this.plot!="string")this.plot="default";this.update(b)},clear:function(){this.dyn={}},update:function(a){dojo.isArray(a)?this.data=a:(this.source=a,this.data=this.source.data,this.source.setSeriesObject&&this.source.setSeriesObject(this));
this.dirty=!0;this.clear()}}));