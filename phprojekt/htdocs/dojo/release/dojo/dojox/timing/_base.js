/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.timing._base"])dojo._hasResource["dojox.timing._base"]=!0,dojo.provide("dojox.timing._base"),dojo.experimental("dojox.timing"),dojox.timing.Timer=function(a){this.timer=null;this.isRunning=!1;this.interval=a;this.onStop=this.onStart=null},dojo.extend(dojox.timing.Timer,{onTick:function(){},setInterval:function(a){this.isRunning&&window.clearInterval(this.timer);this.interval=a;if(this.isRunning)this.timer=window.setInterval(dojo.hitch(this,"onTick"),this.interval)},start:function(){if(typeof this.onStart==
"function")this.onStart();this.isRunning=!0;this.timer=window.setInterval(dojo.hitch(this,"onTick"),this.interval)},stop:function(){if(typeof this.onStop=="function")this.onStop();this.isRunning=!1;window.clearInterval(this.timer)}});