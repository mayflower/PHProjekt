/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.collections._base"])dojo._hasResource["dojox.collections._base"]=!0,dojo.provide("dojox.collections._base"),dojox.collections.DictionaryEntry=function(b,a){this.key=b;this.value=a;this.valueOf=function(){return this.value};this.toString=function(){return String(this.value)}},dojox.collections.Iterator=function(b){var a=0;this.element=b[a]||null;this.atEnd=function(){return a>=b.length};this.get=function(){return this.atEnd()?null:this.element=b[a++]};this.map=function(a,
c){return dojo.map(b,a,c)};this.reset=function(){a=0;this.element=b[a]}},dojox.collections.DictionaryIterator=function(b){var a=[],e={},c;for(c in b)e[c]||a.push(b[c]);var d=0;this.element=a[d]||null;this.atEnd=function(){return d>=a.length};this.get=function(){return this.atEnd()?null:this.element=a[d++]};this.map=function(b,c){return dojo.map(a,b,c)};this.reset=function(){d=0;this.element=a[d]}};