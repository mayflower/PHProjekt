/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.math.round"])dojo._hasResource["dojox.math.round"]=!0,dojo.provide("dojox.math.round"),dojo.getObject("math.round",!0,dojox),dojo.experimental("dojox.math.round"),dojox.math.round=function(c,b,a){var a=10/(a||10),d=Math.pow(10,-15+Math.log(Math.abs(c))/Math.log(10));return(a*(+c+(c>0?d:-d))).toFixed(b)/a},(0.9).toFixed()==0&&function(){var c=dojox.math.round;dojox.math.round=function(b,a,d){var e=Math.pow(10,-a||0),f=Math.abs(b);if(!b||f>=e||f*Math.pow(10,a+1)<5)e=0;return c(b,
a,d)+(b>0?e:-e)}}();