/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.grid.enhanced._Plugin"]||(dojo._hasResource["dojox.grid.enhanced._Plugin"]=!0,dojo.provide("dojox.grid.enhanced._Plugin"),dojo.require("dojox.grid.EnhancedGrid"),dojo.declare("dojox.grid.enhanced._Plugin",null,{name:"plugin",grid:null,option:{},_connects:[],_subscribes:[],privates:{},constructor:function(a,b){this.grid=a;this.option=b;this._connects=[];this._subscribes=[];this.privates=dojo.mixin({},dojox.grid.enhanced._Plugin.prototype);this.init()},init:function(){},onPreInit:function(){},
onPostInit:function(){},onStartUp:function(){},connect:function(a,b,c){a=dojo.connect(a,b,this,c);this._connects.push(a);return a},disconnect:function(a){dojo.some(this._connects,function(b,c,d){return b==a?(dojo.disconnect(a),d.splice(c,1),!0):!1})},subscribe:function(a,b){var c=dojo.subscribe(a,this,b);this._subscribes.push(c);return c},unsubscribe:function(a){dojo.some(this._subscribes,function(b,c,d){return b==a?(dojo.unsubscribe(a),d.splice(c,1),!0):!1})},onSetStore:function(){},destroy:function(){dojo.forEach(this._connects,
dojo.disconnect);dojo.forEach(this._subscribes,dojo.unsubscribe);delete this._connects;delete this._subscribes;delete this.option;delete this.privates}}));