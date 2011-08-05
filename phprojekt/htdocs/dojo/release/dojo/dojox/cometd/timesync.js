/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.cometd.timesync"])dojo._hasResource["dojox.cometd.timesync"]=!0,dojo.provide("dojox.cometd.timesync"),dojo.require("dojox.cometd._base"),dojox.cometd.timesync=new function(){this._window=10;this._lags=[];this._offsets=[];this.samples=this.offset=this.lag=0;this.getServerTime=function(){return(new Date).getTime()+this.offset};this.getServerDate=function(){return new Date(this.getServerTime())};this.setTimeout=function(c,a){var b=(a instanceof Date?a.getTime():0+a)-this.offset-
(new Date).getTime();b<=0&&(b=1);return setTimeout(c,b)};this._in=function(c){var a=c.channel;if(a&&a.indexOf("/meta/")==0&&c.ext&&c.ext.timesync){var b=c.ext.timesync,a=((new Date).getTime()-b.tc-b.p)/2-b.a,b=b.ts-b.tc-a;this._lags.push(a);this._offsets.push(b);this._offsets.length>this._window&&(this._offsets.shift(),this._lags.shift());this.samples++;var b=a=0,d;for(d in this._offsets)a+=this._lags[d],b+=this._offsets[d];this.offset=parseInt((b/this._offsets.length).toFixed());this.lag=parseInt((a/
this._lags.length).toFixed())}return c};this._out=function(c){var a=c.channel;if(a&&a.indexOf("/meta/")==0){a=(new Date).getTime();if(!c.ext)c.ext={};c.ext.timesync={tc:a,l:this.lag,o:this.offset}}return c}},dojox.cometd._extendInList.push(dojo.hitch(dojox.cometd.timesync,"_in")),dojox.cometd._extendOutList.push(dojo.hitch(dojox.cometd.timesync,"_out"));