/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.grid.LazyTreeGridStoreModel"]||(dojo._hasResource["dojox.grid.LazyTreeGridStoreModel"]=!0,dojo.provide("dojox.grid.LazyTreeGridStoreModel"),dojo.require("dijit.tree.ForestStoreModel"),dojo.declare("dojox.grid.LazyTreeGridStoreModel",dijit.tree.ForestStoreModel,{serverStore:!1,constructor:function(b){this.serverStore=b.serverStore===!0?!0:!1},mayHaveChildren:function(b){var a=null;return dojo.some(this.childrenAttrs,function(c){a=this.store.getValue(b,c);return dojo.isString(a)?
parseInt(a,10)>0||a.toLowerCase()==="true"?!0:!1:typeof a=="number"?a>0:typeof a=="boolean"?a:this.store.isItem(a)?(a=this.store.getValues(b,c),dojo.isArray(a)?a.length>0:!1):!1},this)},getChildren:function(b,a,c,d){if(d){var e=d.start||0,f=d.count,i=d.parentId,g=d.sort;if(b===this.root)this.root.size=0,this.store.fetch({start:e,count:f,sort:g,query:this.query,onBegin:dojo.hitch(this,function(a){this.root.size=a}),onComplete:dojo.hitch(this,function(b){a(b,d,this.root.size)}),onError:c});else{var h=
this.store;if(h.isItemLoaded(b))this.serverStore&&!this._isChildrenLoaded(b)?(this.childrenSize=0,this.store.fetch({start:e,count:f,sort:g,query:dojo.mixin({parentId:i},this.query||{}),onBegin:dojo.hitch(this,function(a){this.childrenSize=a}),onComplete:dojo.hitch(this,function(b){a(b,d,this.childrenSize)}),onError:c})):this.inherited(arguments);else{var j=dojo.hitch(this,arguments.callee);h.loadItem({item:b,onItem:function(b){j(b,a,c,d)},onError:c})}}}else this.inherited(arguments)},_isChildrenLoaded:function(b){var a=
null;return dojo.every(this.childrenAttrs,function(c){a=this.store.getValues(b,c);return dojo.every(a,function(a){return this.store.isItemLoaded(a)},this)},this)}}));