/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojo.rpc.JsonService"]||(dojo._hasResource["dojo.rpc.JsonService"]=!0,dojo.provide("dojo.rpc.JsonService"),dojo.require("dojo.rpc.RpcService"),dojo.declare("dojo.rpc.JsonService",dojo.rpc.RpcService,{bustCache:!1,contentType:"application/json-rpc",lastSubmissionId:0,callRemote:function(a,c){var b=new dojo.Deferred;this.bind(a,c,b);return b},bind:function(a,c,b,d){dojo.rawXhrPost({url:d||this.serviceUrl,postData:this.createRequest(a,c),contentType:this.contentType,timeout:this.timeout,
handleAs:"json-comment-optional"}).addCallbacks(this.resultCallback(b),this.errorCallback(b))},createRequest:function(a,c){var b={params:c,method:a,id:++this.lastSubmissionId};return dojo.toJson(b)},parseResults:function(a){if(dojo.isObject(a)){if("result"in a)return a.result;if("Result"in a)return a.Result;if("ResultSet"in a)return a.ResultSet}return a}}));