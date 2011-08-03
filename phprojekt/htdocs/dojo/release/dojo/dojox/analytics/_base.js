/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.analytics._base"])dojo._hasResource["dojox.analytics._base"]=!0,dojo.provide("dojox.analytics._base"),dojox.analytics=function(){this._data=[];this._id=1;this.sendInterval=dojo.config.sendInterval||5E3;this.inTransitRetry=dojo.config.inTransitRetry||200;this.dataUrl=dojo.config.analyticsUrl||dojo.moduleUrl("dojox.analytics.logger","dojoxAnalytics.php");this.sendMethod=dojo.config.sendMethod||"xhrPost";this.maxRequestSize=dojo.isIE?2E3:dojo.config.maxRequestSize||4E3;dojo.addOnLoad(this,
"schedulePusher");dojo.addOnUnload(this,"pushData",!0)},dojo.extend(dojox.analytics,{schedulePusher:function(a){setTimeout(dojo.hitch(this,"checkData"),a||this.sendInterval)},addData:function(a,c){if(arguments.length>2){for(var d=[],b=1;b<arguments.length;b++)d.push(arguments[b]);c=d}this._data.push({plugin:a,data:c})},checkData:function(){this._inTransit?this.schedulePusher(this.inTransitRetry):this.pushData()||this.schedulePusher()},pushData:function(){if(this._data.length){this._inTransit=this._data;
this._data=[];var a;switch(this.sendMethod){case "script":a=dojo.io.script.get({url:this.getQueryPacket(),preventCache:1,callbackParamName:"callback"});break;default:a=dojo.xhrPost({url:this.dataUrl,content:{id:this._id++,data:dojo.toJson(this._inTransit)}})}a.addCallback(this,"onPushComplete");return a}return!1},getQueryPacket:function(){for(;;){var a={id:this._id++,data:dojo.toJson(this._inTransit)},a=this.dataUrl+"?"+dojo.objectToQuery(a);if(a.length>this.maxRequestSize)this._data.unshift(this._inTransit.pop()),
this._split=1;else return a}},onPushComplete:function(){this._inTransit&&delete this._inTransit;this._data.length>0?this.schedulePusher(this.inTransitRetry):this.schedulePusher()}}),dojox.analytics=new dojox.analytics;