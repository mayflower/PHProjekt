/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojo.store.JsonRest"]||(dojo._hasResource["dojo.store.JsonRest"]=!0,dojo.provide("dojo.store.JsonRest"),dojo.require("dojo.store.util.QueryResults"),dojo.declare("dojo.store.JsonRest",null,{constructor:function(b){dojo.mixin(this,b)},target:"",idProperty:"id",get:function(b,a){var c=a||{};c.Accept="application/javascript, application/json";return dojo.xhrGet({url:this.target+b,handleAs:"json",headers:c})},getIdentity:function(b){return b[this.idProperty]},put:function(b,a){var a=
a||{},c="id"in a?a.id:this.getIdentity(b),d=typeof c!="undefined";return dojo.xhr(d&&!a.incremental?"PUT":"POST",{url:d?this.target+c:this.target,postData:dojo.toJson(b),handleAs:"json",headers:{"Content-Type":"application/json","If-Match":a.overwrite===!0?"*":null,"If-None-Match":a.overwrite===!1?"*":null}})},add:function(b,a){a=a||{};a.overwrite=!1;return this.put(b,a)},remove:function(b){return dojo.xhrDelete({url:this.target+b})},query:function(b,a){var c={Accept:"application/javascript, application/json"},
a=a||{};if(a.start>=0||a.count>=0)c.Range="items="+(a.start||"0")+"-"+("count"in a&&a.count!=Infinity?a.count+(a.start||0)-1:"");dojo.isObject(b)&&(b=(b=dojo.objectToQuery(b))?"?"+b:"");if(a&&a.sort){b+=(b?"&":"?")+"sort(";for(var d=0;d<a.sort.length;d++){var f=a.sort[d];b+=(d>0?",":"")+(f.descending?"-":"+")+encodeURIComponent(f.attribute)}b+=")"}var e=dojo.xhrGet({url:this.target+(b||""),handleAs:"json",headers:c});e.total=e.then(function(){var a=e.ioArgs.xhr.getResponseHeader("Content-Range");
return a&&(a=a.match(/\/(.*)/))&&+a[1]});return dojo.store.util.QueryResults(e)}}));