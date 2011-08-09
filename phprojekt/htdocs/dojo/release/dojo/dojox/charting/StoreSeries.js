/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.charting.StoreSeries"]||(dojo._hasResource["dojox.charting.StoreSeries"]=!0,dojo.provide("dojox.charting.StoreSeries"),dojo.declare("dojox.charting.StoreSeries",null,{constructor:function(c,a,b){this.store=c;this.kwArgs=a;this.value=b?typeof b=="function"?b:typeof b=="object"?function(d){var a={},c;for(c in b)a[c]=d[b[c]];return a}:function(a){return a[b]}:function(a){return a.value};this.data=[];this.fetch()},destroy:function(){this.observeHandle&&this.observeHandle.dismiss()},
setSeriesObject:function(c){this.series=c},fetch:function(){function c(){a.data=dojo.map(a.objects,function(b){return a.value(b,a.store)});a._pushDataChanges()}this.objects=[];var a=this;this.observeHandle&&this.observeHandle.dismiss();var b=this.store.query(this.kwArgs.query,this.kwArgs);dojo.when(b,function(b){a.objects=b;c()});if(b.observe)this.observeHandle=b.observe(c,!0)},_pushDataChanges:function(){this.series&&(this.series.chart.updateSeries(this.series.name,this),this.series.chart.delayedRender())}}));