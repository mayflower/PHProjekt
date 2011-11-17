/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.wire.ml.DataStore"]||(dojo._hasResource["dojox.wire.ml.DataStore"]=!0,dojo.provide("dojox.wire.ml.DataStore"),dojo.require("dijit._Widget"),dojo.require("dojox.wire._base"),dojo.declare("dojox.wire.ml.DataStore",dijit._Widget,{storeClass:"",postCreate:function(){this.store=this._createStore()},_createStore:function(){if(!this.storeClass)return null;var a=dojox.wire._getClass(this.storeClass);if(!a)return null;for(var d={},e=this.domNode.attributes,c=0;c<e.length;c++){var b=
e.item(c);if(b.specified&&!this[b.nodeName])d[b.nodeName]=b.nodeValue}return new a(d)},getFeatures:function(){return this.store.getFeatures()},fetch:function(a){return this.store.fetch(a)},save:function(a){this.store.save(a)},newItem:function(a){return this.store.newItem(a)},deleteItem:function(a){return this.store.deleteItem(a)},revert:function(){return this.store.revert()}}));