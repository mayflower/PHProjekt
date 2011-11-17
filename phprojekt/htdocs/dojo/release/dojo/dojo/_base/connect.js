/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojo._base.connect"])dojo._hasResource["dojo._base.connect"]=!0,dojo.provide("dojo._base.connect"),dojo.require("dojo._base.lang"),dojo._listener={getDispatcher:function(){return function(){var a=Array.prototype,b=arguments.callee,c=b._listeners,b=(b=b.target)&&b.apply(this,arguments),d,c=[].concat(c);for(d in c)d in a||c[d].apply(this,arguments);return b}},add:function(a,b,c){var a=a||dojo.global,d=a[b];if(!d||!d._listeners){var g=dojo._listener.getDispatcher();g.target=d;
g._listeners=[];d=a[b]=g}return d._listeners.push(c)},remove:function(a,b,c){(a=(a||dojo.global)[b])&&a._listeners&&c--&&delete a._listeners[c]}},dojo.connect=function(a,b,c,d,g){var e=arguments,h=[],f=0;h.push(dojo.isString(e[0])?null:e[f++],e[f++]);var i=e[f+1];h.push(dojo.isString(i)||dojo.isFunction(i)?e[f++]:null,e[f++]);for(i=e.length;f<i;f++)h.push(e[f]);return dojo._connect.apply(this,h)},dojo._connect=function(a,b,c,d){var g=dojo._listener,c=g.add(a,b,dojo.hitch(c,d));return[a,b,c,g]},dojo.disconnect=
function(a){a&&a[0]!==void 0&&(dojo._disconnect.apply(this,a),delete a[0])},dojo._disconnect=function(a,b,c,d){d.remove(a,b,c)},dojo._topics={},dojo.subscribe=function(a,b,c){return[a,dojo._listener.add(dojo._topics,a,dojo.hitch(b,c))]},dojo.unsubscribe=function(a){a&&dojo._listener.remove(dojo._topics,a[0],a[1])},dojo.publish=function(a,b){var c=dojo._topics[a];c&&c.apply(this,b||[])},dojo.connectPublisher=function(a,b,c){var d=function(){dojo.publish(a,arguments)};return c?dojo.connect(b,c,d):dojo.connect(b,
d)};