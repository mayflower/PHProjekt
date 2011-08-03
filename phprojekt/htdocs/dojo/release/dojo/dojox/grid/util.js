/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.grid.util"]||(dojo._hasResource["dojox.grid.util"]=!0,dojo.provide("dojox.grid.util"),function(){var a=dojox.grid.util;a.na="...";a.rowIndexTag="gridRowIndex";a.gridViewTag="gridView";a.fire=function(b,e,c){var a=b&&e&&b[e];return a&&(c?a.apply(b,c):b[e]())};a.setStyleHeightPx=function(b,e){if(e>=0){var a=b.style,d=e+"px";b&&a.height!=d&&(a.height=d)}};a.mouseEvents=["mouseover","mouseout","mousedown","mouseup","click","dblclick","contextmenu"];a.keyEvents=["keyup","keydown",
"keypress"];a.funnelEvents=function(b,e,c,d){for(var d=d?d:a.mouseEvents.concat(a.keyEvents),f=0,g=d.length;f<g;f++)e.connect(b,"on"+d[f],c)};a.removeNode=function(b){(b=dojo.byId(b))&&b.parentNode&&b.parentNode.removeChild(b);return b};a.arrayCompare=function(b,a){for(var c=0,d=b.length;c<d;c++)if(b[c]!=a[c])return!1;return b.length==a.length};a.arrayInsert=function(b,a,c){b.length<=a?b[a]=c:b.splice(a,0,c)};a.arrayRemove=function(b,a){b.splice(a,1)};a.arraySwap=function(b,a,c){var d=b[a];b[a]=b[c];
b[c]=d}}());