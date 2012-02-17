/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.cometd.ack"])dojo._hasResource["dojox.cometd.ack"]=!0,dojo.provide("dojox.cometd.ack"),dojo.require("dojox.cometd._base"),dojox.cometd._ack=new function(){var b=!1,c=-1;this._in=function(a){a.channel=="/meta/handshake"?b=a.ext&&a.ext.ack:b&&a.channel=="/meta/connect"&&a.ext&&a.ext.ack&&a.successful&&(c=parseInt(a.ext.ack));return a};this._out=function(a){if(a.channel=="/meta/handshake"){if(!a.ext)a.ext={};a.ext.ack=dojox.cometd.ackEnabled;c=-1}if(b&&a.channel=="/meta/connect"){if(!a.ext)a.ext=
{};a.ext.ack=c}return a}},dojox.cometd._extendInList.push(dojo.hitch(dojox.cometd._ack,"_in")),dojox.cometd._extendOutList.push(dojo.hitch(dojox.cometd._ack,"_out")),dojox.cometd.ackEnabled=!0;