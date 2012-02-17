/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.rpc.JsonRPC"]){dojo._hasResource["dojox.rpc.JsonRPC"]=!0;dojo.provide("dojox.rpc.JsonRPC");dojo.require("dojox.rpc.Service");var jsonRpcEnvelope=function(b){return{serialize:function(a,c,d){a={id:this._requestId++,method:c.name,params:d};if(b)a.jsonrpc=b;return{data:dojo.toJson(a),handleAs:"json",contentType:"application/json",transport:"POST"}},deserialize:function(a){"Error"==a.name&&(a=dojo.fromJson(a.responseText));if(a.error){var b=Error(a.error.message||a.error);
b._rpcErrorObject=a.error;return b}return a.result}}};dojox.rpc.envelopeRegistry.register("JSON-RPC-1.0",function(b){return b=="JSON-RPC-1.0"},dojo.mixin({namedParams:!1},jsonRpcEnvelope()));dojox.rpc.envelopeRegistry.register("JSON-RPC-2.0",function(b){return b=="JSON-RPC-2.0"},jsonRpcEnvelope("2.0"))};