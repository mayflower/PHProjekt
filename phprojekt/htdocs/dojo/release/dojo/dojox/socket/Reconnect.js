/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.socket.Reconnect"])dojo._hasResource["dojox.socket.Reconnect"]=!0,dojo.provide("dojox.socket.Reconnect"),dojox.socket.Reconnect=function(a,b){var b=b||{},c=b.reconnectTime||1E4;dojo.connect(a,"onclose",function(b){clearTimeout(d);b.wasClean||a.disconnected(function(){dojox.socket.replace(a,e=a.reconnect())})});var d,e;if(!a.disconnected)a.disconnected=function(a){setTimeout(function(){a();d=setTimeout(function(){e.readyState<2&&(c=b.reconnectTime||1E4)},1E4)},c);c*=b.backoffRate||
2};if(!a.reconnect)a.reconnect=function(){return a.args?dojox.socket.LongPoll(a.args):dojox.socket.WebSocket({url:a.URL||a.url})};return a};