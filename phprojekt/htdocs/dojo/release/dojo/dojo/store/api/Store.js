/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


define([],function(){dojo.declare("dojo.store.api.Store",null,{idProperty:"id",queryEngine:null,get:function(){},getIdentity:function(){},put:function(){},add:function(){},remove:function(b){delete this.index[b];for(var a=this.data,d=this.idProperty,c=0,e=a.length;c<e;c++)if(a[c][d]==b){a.splice(c,1);break}},query:function(){},transaction:function(){},getChildren:function(){},getMetadata:function(){}});dojo.store.api.Store.PutDirectives=function(b,a,d,c){this.id=b;this.before=a;this.parent=d;this.overwrite=
c};dojo.store.api.Store.SortInformation=function(b,a){this.attribute=b;this.descending=a};dojo.store.api.Store.QueryOptions=function(b,a,d){this.sort=b;this.start=a;this.count=d};dojo.declare("dojo.store.api.Store.QueryResults",null,{forEach:function(){},filter:function(){},map:function(){},then:function(){},observe:function(){},total:0});dojo.declare("dojo.store.api.Store.Transaction",null,{commit:function(){},abort:function(){}})});