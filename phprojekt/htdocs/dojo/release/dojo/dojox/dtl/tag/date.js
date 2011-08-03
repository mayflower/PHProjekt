/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.dtl.tag.date"])dojo._hasResource["dojox.dtl.tag.date"]=!0,dojo.provide("dojox.dtl.tag.date"),dojo.require("dojox.dtl._base"),dojo.require("dojox.dtl.utils.date"),dojox.dtl.tag.date.NowNode=function(a,b){this._format=a;this.format=new dojox.dtl.utils.date.DateFormat(a);this.contents=b},dojo.extend(dojox.dtl.tag.date.NowNode,{render:function(a,b){this.contents.set(this.format.format(new Date));return this.contents.render(a,b)},unrender:function(a,b){return this.contents.unrender(a,
b)},clone:function(a){return new this.constructor(this._format,this.contents.clone(a))}}),dojox.dtl.tag.date.now=function(a,b){var c=b.split_contents();if(c.length!=2)throw Error("'now' statement takes one argument");return new dojox.dtl.tag.date.NowNode(c[1].slice(1,-1),a.create_text_node())};