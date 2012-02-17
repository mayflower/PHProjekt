/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojo._base._loader.loader_debug"])dojo._hasResource["dojo._base._loader.loader_debug"]=!0,dojo.provide("dojo._base._loader.loader_debug"),dojo.nonDebugProvide=dojo.provide,dojo.provide=function(b){var a=dojo._xdDebugQueue;a&&a.length>0&&b==a.currentResourceName&&(dojo.isAIR?window.setTimeout(function(){dojo._xdDebugFileLoaded(b)},1):window.setTimeout(dojo._scopeName+"._xdDebugFileLoaded('"+b+"')",1));return dojo.nonDebugProvide.apply(dojo,arguments)},dojo._xdDebugFileLoaded=
function(b){if(!dojo._xdDebugScopeChecked){if(dojo._scopeName!="dojo")window.dojo=window[dojo.config.scopeMap[0][1]],window.dijit=window[dojo.config.scopeMap[1][1]],window.dojox=window[dojo.config.scopeMap[2][1]];dojo._xdDebugScopeChecked=!0}var a=dojo._xdDebugQueue;b&&b==a.currentResourceName&&a.shift();a.length==0&&dojo._xdWatchInFlight();if(a.length==0){a.currentResourceName=null;for(var c in dojo._xdInFlight)if(dojo._xdInFlight[c]===!0)return;dojo._xdNotifyLoaded()}else if(b==a.currentResourceName)a.currentResourceName=
a[0].resourceName,b=document.createElement("script"),b.type="text/javascript",b.src=a[0].resourcePath,document.getElementsByTagName("head")[0].appendChild(b)};