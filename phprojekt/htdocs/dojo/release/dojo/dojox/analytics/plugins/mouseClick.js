/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.analytics.plugins.mouseClick"])dojo._hasResource["dojox.analytics.plugins.mouseClick"]=!0,dojo.require("dojox.analytics._base"),dojo.provide("dojox.analytics.plugins.mouseClick"),dojox.analytics.plugins.mouseClick=new function(){this.addData=dojo.hitch(dojox.analytics,"addData","mouseClick");this.onClick=function(b){this.addData(this.trimEvent(b))};dojo.connect(dojo.doc,"onclick",this,"onClick");this.trimEvent=function(b){var e={},a;for(a in b)switch(a){case "target":case "originalTarget":case "explicitOriginalTarget":var d=
["id","className","nodeName","localName","href","spellcheck","lang"];e[a]={};for(var c=0;c<d.length;c++)b[a][d[c]]&&(d[c]=="text"||d[c]=="textContent"?b[a].localName!="HTML"&&b[a].localName!="BODY"&&(e[a][d[c]]=b[a][d[c]].substr(0,50)):e[a][d[c]]=b[a][d[c]]);break;case "clientX":case "clientY":case "pageX":case "pageY":case "screenX":case "screenY":e[a]=b[a]}return e}};