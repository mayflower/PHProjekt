/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.mobile.app._event"]){dojo._hasResource["dojox.mobile.app._event"]=!0;dojo.provide("dojox.mobile.app._event");dojo.experimental("dojox.mobile.app._event.js");dojo.mixin(dojox.mobile.app,{eventMap:{},connectFlick:function(c,b,d){var e,f,g=!1,h,i,j,k,l,m;dojo.connect("onmousedown",c,function(a){g=!1;e=a.targetTouches?a.targetTouches[0].clientX:a.clientX;f=a.targetTouches?a.targetTouches[0].clientY:a.clientY;m=(new Date).getTime();j=dojo.connect(c,"onmousemove",n);k=dojo.connect(c,
"onmouseup",o)});var n=function(a){dojo.stopEvent(a);h=a.targetTouches?a.targetTouches[0].clientX:a.clientX;i=a.targetTouches?a.targetTouches[0].clientY:a.clientY;Math.abs(Math.abs(h)-Math.abs(e))>15?(g=!0,l=h>e?"ltr":"rtl"):Math.abs(Math.abs(i)-Math.abs(f))>15&&(g=!0,l=i>f?"ttb":"btt")},o=function(a){dojo.stopEvent(a);j&&dojo.disconnect(j);k&&dojo.disconnect(k);if(g)if(a={target:c,direction:l,duration:(new Date).getTime()-m},b&&d)b[d](a);else d(a)}}});dojox.mobile.app.isIPhone=dojo.isSafari&&(navigator.userAgent.indexOf("iPhone")>
-1||navigator.userAgent.indexOf("iPod")>-1);dojox.mobile.app.isWebOS=navigator.userAgent.indexOf("webOS")>-1;dojox.mobile.app.isAndroid=navigator.userAgent.toLowerCase().indexOf("android")>-1;if(dojox.mobile.app.isIPhone||dojox.mobile.app.isAndroid)dojox.mobile.app.eventMap={onmousedown:"ontouchstart",mousedown:"ontouchstart",onmouseup:"ontouchend",mouseup:"ontouchend",onmousemove:"ontouchmove",mousemove:"ontouchmove"};dojo._oldConnect=dojo._connect;dojo._connect=function(c,b,d,e,f){b=dojox.mobile.app.eventMap[b]||
b;if(b=="flick"||b=="onflick")if(dojo.global.Mojo)b=Mojo.Event.flick;else return dojox.mobile.app.connectFlick(c,d,e);return dojo._oldConnect(c,b,d,e,f)}};