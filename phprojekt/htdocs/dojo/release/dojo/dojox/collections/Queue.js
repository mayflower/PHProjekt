/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.collections.Queue"])dojo._hasResource["dojox.collections.Queue"]=!0,dojo.provide("dojox.collections.Queue"),dojo.require("dojox.collections._base"),dojox.collections.Queue=function(d){var a=[];d&&(a=a.concat(d));this.count=a.length;this.clear=function(){a=[];this.count=a.length};this.clone=function(){return new dojox.collections.Queue(a)};this.contains=function(b){for(var c=0;c<a.length;c++)if(a[c]==b)return!0;return!1};this.copyTo=function(b,c){b.splice(c,0,a)};this.dequeue=
function(){var b=a.shift();this.count=a.length;return b};this.enqueue=function(b){this.count=a.push(b)};this.forEach=function(b,c){dojo.forEach(a,b,c)};this.getIterator=function(){return new dojox.collections.Iterator(a)};this.peek=function(){return a[0]};this.toArray=function(){return[].concat(a)}};