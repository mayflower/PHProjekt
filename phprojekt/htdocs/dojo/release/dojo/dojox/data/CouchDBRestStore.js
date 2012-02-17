/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.data.CouchDBRestStore"])dojo._hasResource["dojox.data.CouchDBRestStore"]=!0,dojo.provide("dojox.data.CouchDBRestStore"),dojo.require("dojox.data.JsonRestStore"),dojo.declare("dojox.data.CouchDBRestStore",dojox.data.JsonRestStore,{save:function(a){for(var c=this.inherited(arguments),d=this.service.servicePath,b=0;b<c.length;b++)(function(a,b){b.addCallback(function(b){if(b)a.__id=d+b.id,a._rev=b.rev;return b})})(c[b].content,c[b].deferred)},fetch:function(a){a.query=a.query||
"_all_docs?";if(a.start)a.query=(a.query?a.query+"&":"")+"startkey="+a.start,delete a.start;if(a.count)a.query=(a.query?a.query+"&":"")+"limit="+a.count,delete a.count;return this.inherited(arguments)},_processResults:function(a){var c=a.rows;if(c){for(var d=this.service.servicePath,b=0;b<c.length;b++){var e=c[b].value;e.__id=d+c[b].id;e._id=c[b].id;e._loadObject=dojox.rpc.JsonRest._loader;c[b]=e}return{totalCount:a.total_rows,items:a.rows}}else return{items:a}}}),dojox.data.CouchDBRestStore.getStores=
function(a){var c={};dojo.xhrGet({url:a+"_all_dbs",handleAs:"json",sync:!0}).addBoth(function(d){for(var b=0;b<d.length;b++)c[d[b]]=new dojox.data.CouchDBRestStore({target:a+d[b],idAttribute:"_id"});return c});return c};