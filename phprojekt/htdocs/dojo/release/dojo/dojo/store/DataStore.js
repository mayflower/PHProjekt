/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojo.store.DataStore"]||(dojo._hasResource["dojo.store.DataStore"]=!0,dojo.provide("dojo.store.DataStore"),dojo.require("dojo.store.util.QueryResults"),dojo.declare("dojo.store.DataStore",null,{target:"",constructor:function(a){dojo.mixin(this,a)},_objectConverter:function(a){var d=this.store;return function(b){for(var c={},e=d.getAttributes(b),f=0;f<e.length;f++)c[e[f]]=d.getValue(b,e[f]);return a(c)}},get:function(a){var d,b,c=new dojo.Deferred;this.store.fetchItemByIdentity({identity:a,
onItem:this._objectConverter(function(e){c.resolve(d=e)}),onError:function(e){c.reject(b=e)}});if(d)return d;if(b)throw b;return c.promise},put:function(a,d){var b=d&&typeof d.id!="undefined"||this.getIdentity(a),c=this.store;typeof b=="undefined"?c.newItem(a):c.fetchItemByIdentity({identity:b,onItem:function(e){if(e)for(var b in a)c.getValue(e,b)!=a[b]&&c.setValue(e,b,a[b]);else c.newItem(a)}})},remove:function(a){var d=this.store;this.store.fetchItemByIdentity({identity:a,onItem:function(b){d.deleteItem(b)}})},
query:function(a,d){var b=new dojo.Deferred;b.total=new dojo.Deferred;var c=this._objectConverter(function(b){return b});this.store.fetch(dojo.mixin({query:a,onBegin:function(a){b.total.resolve(a)},onComplete:function(a){b.resolve(dojo.map(a,c))},onError:function(a){b.reject(a)}},d));return dojo.store.util.QueryResults(b)},getIdentity:function(a){return a[this.idProperty||this.store.getIdentityAttributes()[0]]}}));