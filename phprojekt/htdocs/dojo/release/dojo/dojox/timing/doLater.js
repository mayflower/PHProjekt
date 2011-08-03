/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.timing.doLater"])dojo._hasResource["dojox.timing.doLater"]=!0,dojo.provide("dojox.timing.doLater"),dojo.experimental("dojox.timing.doLater"),dojox.timing.doLater=function(b,a,c){if(b)return!1;var d=dojox.timing.doLater.caller,e=dojox.timing.doLater.caller.arguments,a=a||dojo.global;setTimeout(function(){d.apply(a,e)},c||100);return!0};