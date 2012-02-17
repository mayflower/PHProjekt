/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.timing.Streamer"])dojo._hasResource["dojox.timing.Streamer"]=!0,dojo.provide("dojox.timing.Streamer"),dojo.require("dojox.timing._base"),dojox.timing.Streamer=function(c,d,e,f,b){var g=[];this.interval=e||1E3;this.minimumSize=f||10;this.inputFunction=c||function(){};this.outputFunction=d||function(){};var a=new dojox.timing.Timer(this.interval);this.setInterval=function(b){this.interval=b;a.setInterval(b)};this.onTick=function(){};this.start=function(){if(typeof this.inputFunction==
"function"&&typeof this.outputFunction=="function")a.start();else throw Error("You cannot start a Streamer without an input and an output function.");};this.onStart=function(){};this.stop=function(){a.stop()};this.onStop=function(){};a.onTick=this.tick;a.onStart=this.onStart;a.onStop=this.onStop;b&&g.concat(b)};