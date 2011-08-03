/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.mdnd.LazyManager"]||(dojo._hasResource["dojox.mdnd.LazyManager"]=!0,dojo.provide("dojox.mdnd.LazyManager"),dojo.require("dojo.dnd.Manager"),dojo.require("dojox.mdnd.PureSource"),dojo.declare("dojox.mdnd.LazyManager",null,{constructor:function(){this._registry={};this._fakeSource=new dojox.mdnd.PureSource(dojo.create("div"),{copyOnly:!1});this._fakeSource.startup();dojo.addOnUnload(dojo.hitch(this,"destroy"));this.manager=dojo.dnd.manager()},getItem:function(b){var a=b.getAttribute("dndType");
return{data:b.getAttribute("dndData")||b.innerHTML,type:a?a.split(/\s*,\s*/):["text"]}},startDrag:function(b,a){if(a=a||b.target){var c=this.manager,d=this.getItem(a);a.id==""&&dojo.attr(a,"id",dojo.dnd.getUniqueId());dojo.addClass(a,"dojoDndItem");this._fakeSource.setItem(a.id,d);c.startDrag(this._fakeSource,[a],!1);c.onMouseMove(b)}},cancelDrag:function(){var b=this.manager;b.target=null;b.onMouseUp()},destroy:function(){this._fakeSource.destroy()}}));