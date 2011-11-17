/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.collections.Dictionary"])dojo._hasResource["dojox.collections.Dictionary"]=!0,dojo.provide("dojox.collections.Dictionary"),dojo.require("dojox.collections._base"),dojox.collections.Dictionary=function(c){var b={};this.count=0;var e={};this.add=function(a,c){var d=a in b;b[a]=new dojox.collections.DictionaryEntry(a,c);d||this.count++};this.clear=function(){b={};this.count=0};this.clone=function(){return new dojox.collections.Dictionary(this)};this.contains=this.containsKey=
function(a){return e[a]?!1:b[a]!=null};this.containsValue=function(a){for(var b=this.getIterator();b.get();)if(b.element.value==a)return!0;return!1};this.entry=function(a){return b[a]};this.forEach=function(a,c){var d=[],f;for(f in b)e[f]||d.push(b[f]);dojo.forEach(d,a,c)};this.getKeyList=function(){return this.getIterator().map(function(a){return a.key})};this.getValueList=function(){return this.getIterator().map(function(a){return a.value})};this.item=function(a){if(a in b)return b[a].valueOf()};
this.getIterator=function(){return new dojox.collections.DictionaryIterator(b)};this.remove=function(a){return a in b&&!e[a]?(delete b[a],this.count--,!0):!1};if(c)for(c=c.getIterator();c.get();)this.add(c.element.key,c.element.value)};