/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojo.rpc.RpcService"]||(dojo._hasResource["dojo.rpc.RpcService"]=!0,dojo.provide("dojo.rpc.RpcService"),dojo.declare("dojo.rpc.RpcService",null,{constructor:function(a){if(a)if(dojo.isString(a)||a instanceof dojo._Url){var b=dojo.xhrGet({url:a instanceof dojo._Url?a+"":a,handleAs:"json-comment-optional",sync:!0});b.addCallback(this,"processSmd");b.addErrback(function(){throw Error("Unable to load SMD from "+a);})}else if(a.smdStr)this.processSmd(dojo.eval("("+a.smdStr+")"));else{if(a.serviceUrl)this.serviceUrl=
a.serviceUrl;this.timeout=a.timeout||3E3;if("strictArgChecks"in a)this.strictArgChecks=a.strictArgChecks;this.processSmd(a)}},strictArgChecks:!0,serviceUrl:"",parseResults:function(a){return a},errorCallback:function(a){return function(b){a.errback(b.message)}},resultCallback:function(a){return dojo.hitch(this,function(b){if(b.error!=null){var c;typeof b.error=="object"?(c=Error(b.error.message),c.code=b.error.code,c.error=b.error.error):c=Error(b.error);c.id=b.id;c.errorObject=b;a.errback(c)}else a.callback(this.parseResults(b))})},
generateMethod:function(a,b,c){return dojo.hitch(this,function(){var d=new dojo.Deferred;if(this.strictArgChecks&&b!=null&&arguments.length!=b.length)throw Error("Invalid number of parameters for remote method.");else this.bind(a,dojo._toArray(arguments),d,c);return d})},processSmd:function(a){a.methods&&dojo.forEach(a.methods,function(a){if(a&&a.name&&(this[a.name]=this.generateMethod(a.name,a.parameters,a.url||a.serviceUrl||a.serviceURL),!dojo.isFunction(this[a.name])))throw Error("RpcService: Failed to create"+
a.name+"()");},this);this.serviceUrl=a.serviceUrl||a.serviceURL;this.required=a.required;this.smd=a}}));