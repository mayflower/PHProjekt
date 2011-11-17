/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.charting.scaler.primitive"])dojo._hasResource["dojox.charting.scaler.primitive"]=!0,dojo.provide("dojox.charting.scaler.primitive"),dojox.charting.scaler.primitive={buildScaler:function(a,b,c){a==b&&(a-=0.5,b+=0.5);return{bounds:{lower:a,upper:b,from:a,to:b,scale:c/(b-a),span:c},scaler:dojox.charting.scaler.primitive}},buildTicks:function(){return{major:[],minor:[],micro:[]}},getTransformerFromModel:function(a){var b=a.bounds.from,c=a.bounds.scale;return function(a){return(a-
b)*c}},getTransformerFromPlot:function(a){var b=a.bounds.from,c=a.bounds.scale;return function(a){return a/c+b}}};