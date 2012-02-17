/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojo.rpc.JsonpService"]||(dojo._hasResource["dojo.rpc.JsonpService"]=!0,dojo.provide("dojo.rpc.JsonpService"),dojo.require("dojo.rpc.RpcService"),dojo.require("dojo.io.script"),dojo.declare("dojo.rpc.JsonpService",dojo.rpc.RpcService,{constructor:function(a,b){this.required&&(b&&dojo.mixin(this.required,b),dojo.forEach(this.required,function(a){if(a==""||a==void 0)throw Error("Required Service Argument not found: "+a);}))},strictArgChecks:!1,bind:function(a,b,c,d){dojo.io.script.get({url:d||
this.serviceUrl,callbackParamName:this.callbackParamName||"callback",content:this.createRequest(b),timeout:this.timeout,handleAs:"json",preventCache:!0}).addCallbacks(this.resultCallback(c),this.errorCallback(c))},createRequest:function(a){a=dojo.isArrayLike(a)&&a.length==1?a[0]:{};dojo.mixin(a,this.required);return a}}));