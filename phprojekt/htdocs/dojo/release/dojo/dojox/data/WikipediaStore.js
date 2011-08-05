/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.data.WikipediaStore"]||(dojo._hasResource["dojox.data.WikipediaStore"]=!0,dojo.provide("dojox.data.WikipediaStore"),dojo.require("dojo.io.script"),dojo.require("dojox.rpc.Service"),dojo.require("dojox.data.ServiceStore"),dojo.experimental("dojox.data.WikipediaStore"),dojo.declare("dojox.data.WikipediaStore",dojox.data.ServiceStore,{constructor:function(a){this.service=a&&a.service?a.service:(new dojox.rpc.Service(dojo.moduleUrl("dojox.rpc.SMDLibrary","wikipedia.smd"))).query;
this.idAttribute=this.labelAttribute="title"},fetch:function(a){var b=dojo.mixin({},a.query);if(b&&(!b.action||b.action==="parse"))b.action="parse",b.page=b.title,delete b.title;else if(b.action==="query"){b.list="search";b.srwhat="text";b.srsearch=b.text;if(a.start)b.sroffset=a.start-1;if(a.count)b.srlimit=a.count>=500?500:a.count;delete b.text}a.query=b;return this.inherited(arguments)},_processResults:function(a,b){if(a.parse)a.parse.title=dojo.queryToObject(b.ioArgs.url.split("?")[1]).page,a=
[a.parse];else if(a.query&&a.query.search){var a=a.query.search,d=this,c;for(c in a)a[c]._loadObject=function(a){d.fetch({query:{action:"parse",title:this.title},onItem:a});delete this._loadObject}}return this.inherited(arguments)}}));