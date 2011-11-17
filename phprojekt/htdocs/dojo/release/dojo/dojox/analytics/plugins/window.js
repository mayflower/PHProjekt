/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.analytics.plugins.window"])dojo._hasResource["dojox.analytics.plugins.window"]=!0,dojo.require("dojox.analytics._base"),dojo.provide("dojox.analytics.plugins.window"),dojox.analytics.plugins.window=new function(){this.addData=dojo.hitch(dojox.analytics,"addData","window");this.windowConnects=dojo.config.windowConnects||["open","onerror"];for(var a=0;a<this.windowConnects.length;a++)dojo.connect(window,this.windowConnects[a],dojo.hitch(this,"addData",this.windowConnects[a]));
dojo.addOnLoad(dojo.hitch(this,function(){var a={},b;for(b in window)if(dojo.isObject(window[b]))switch(b){case "location":case "console":a[b]=window[b]}else a[b]=window[b];this.addData(a)}))};