/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojo.store.Memory"]||(dojo._hasResource["dojo.store.Memory"]=!0,dojo.provide("dojo.store.Memory"),dojo.require("dojo.store.util.QueryResults"),dojo.require("dojo.store.util.SimpleQueryEngine"),dojo.declare("dojo.store.Memory",null,{constructor:function(a){this.index={};dojo.mixin(this,a);this.setData(this.data||[])},data:null,idProperty:"id",index:null,queryEngine:dojo.store.util.SimpleQueryEngine,get:function(a){return this.index[a]},getIdentity:function(a){return a[this.idProperty]},
put:function(a,b){var d=b&&b.id||a[this.idProperty]||Math.random();this.index[d]=a;for(var c=this.data,f=this.idProperty,e=0,g=c.length;e<g;e++)if(c[e][f]==d)return c[e]=a,d;this.data.push(a);return d},add:function(a,b){if(this.index[b&&b.id||a[this.idProperty]])throw Error("Object already exists");return this.put(a,b)},remove:function(a){delete this.index[a];for(var b=this.data,d=this.idProperty,c=0,f=b.length;c<f;c++)if(b[c][d]==a){b.splice(c,1);break}},query:function(a,b){return dojo.store.util.QueryResults(this.queryEngine(a,
b)(this.data))},setData:function(a){var e;a.items?(this.idProperty=a.identifier,e=this.data=a.items,a=e):this.data=a;for(var b=0,d=a.length;b<d;b++){var c=a[b];this.index[c[this.idProperty]]=c}}}));