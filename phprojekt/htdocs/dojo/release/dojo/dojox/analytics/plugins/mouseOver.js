/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.analytics.plugins.mouseOver"])dojo._hasResource["dojox.analytics.plugins.mouseOver"]=!0,dojo.require("dojox.analytics._base"),dojo.provide("dojox.analytics.plugins.mouseOver"),dojox.analytics.plugins.mouseOver=new function(){this.watchMouse=dojo.config.watchMouseOver||!0;this.mouseSampleDelay=dojo.config.sampleDelay||2500;this.addData=dojo.hitch(dojox.analytics,"addData","mouseOver");this.targetProps=dojo.config.targetProps||["id","className","localName","href","spellcheck",
"lang","textContent","value"];this.toggleWatchMouse=function(){this._watchingMouse?(dojo.disconnect(this._watchingMouse),delete this._watchingMouse):dojo.connect(dojo.doc,"onmousemove",this,"sampleMouse")};this.watchMouse&&(dojo.connect(dojo.doc,"onmouseover",this,"toggleWatchMouse"),dojo.connect(dojo.doc,"onmouseout",this,"toggleWatchMouse"));this.sampleMouse=function(b){if(!this._rateLimited)this.addData("sample",this.trimMouseEvent(b)),this._rateLimited=!0,setTimeout(dojo.hitch(this,function(){this._rateLimited&&
(this.trimMouseEvent(this._lastMouseEvent),delete this._lastMouseEvent,delete this._rateLimited)}),this.mouseSampleDelay);return this._lastMouseEvent=b};this.trimMouseEvent=function(b){var e={},a;for(a in b)switch(a){case "target":var d=this.targetProps;e[a]={};for(var c=0;c<d.length;c++)dojo.isObject(b[a])&&d[c]in b[a]&&(d[c]=="text"||d[c]=="textContent"?b[a].localName&&b[a].localName!="HTML"&&b[a].localName!="BODY"&&(e[a][d[c]]=b[a][d[c]].substr(0,50)):e[a][d[c]]=b[a][d[c]]);break;case "x":case "y":b[a]&&
(e[a]=b[a]+"")}return e}};