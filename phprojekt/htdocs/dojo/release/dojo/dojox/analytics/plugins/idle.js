/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.analytics.plugins.idle"])dojo._hasResource["dojox.analytics.plugins.idle"]=!0,dojo.require("dojox.analytics._base"),dojo.provide("dojox.analytics.plugins.idle"),dojox.analytics.plugins.idle=new function(){this.addData=dojo.hitch(dojox.analytics,"addData","idle");this.idleTime=dojo.config.idleTime||6E4;this.idle=!0;this.setIdle=function(){this.addData("isIdle");this.idle=!0};dojo.addOnLoad(dojo.hitch(this,function(){for(var b=["onmousemove","onkeydown","onclick","onscroll"],
a=0;a<b.length;a++)dojo.connect(dojo.doc,b[a],this,function(){this.idle?(this.idle=!1,this.addData("isActive")):clearTimeout(this.idleTimer);this.idleTimer=setTimeout(dojo.hitch(this,"setIdle"),this.idleTime)})}))};