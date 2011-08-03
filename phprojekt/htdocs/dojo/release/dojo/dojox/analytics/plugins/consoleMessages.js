/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.analytics.plugins.consoleMessages"])dojo._hasResource["dojox.analytics.plugins.consoleMessages"]=!0,dojo.require("dojox.analytics._base"),dojo.provide("dojox.analytics.plugins.consoleMessages"),dojox.analytics.plugins.consoleMessages=new function(){this.addData=dojo.hitch(dojox.analytics,"addData","consoleMessages");var b=dojo.config.consoleLogFuncs||["error","warn","info","rlog"];console||(console={});for(var a=0;a<b.length;a++)console[b[a]]?dojo.connect(console,b[a],
dojo.hitch(this,"addData",b[a])):console[b[a]]=dojo.hitch(this,"addData",b[a])};