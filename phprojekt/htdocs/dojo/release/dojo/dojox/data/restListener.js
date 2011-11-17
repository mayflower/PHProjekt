/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.data.restListener"])dojo._hasResource["dojox.data.restListener"]=!0,dojo.provide("dojox.data.restListener"),dojox.data.restListener=function(a){var b=a.channel,d=dojox.rpc.JsonRest,c=d.getServiceAndId(b).service,d=dojox.json.ref.resolveJson(a.result,{defaultId:a.event=="put"&&b,index:dojox.rpc.Rest._index,idPrefix:c.servicePath.replace(/[^\/]*$/,""),idAttribute:d.getIdAttribute(c),schemas:d.schemas,loader:d._loader,assignAbsoluteIds:!0}),b=dojox.rpc.Rest._index&&dojox.rpc.Rest._index[b],
a="on"+a.event.toLowerCase(),c=c&&c._store;if(b&&b[a])b[a](d);else if(c)switch(a){case "onpost":c.onNew(d);break;case "ondelete":c.onDelete(b)}};