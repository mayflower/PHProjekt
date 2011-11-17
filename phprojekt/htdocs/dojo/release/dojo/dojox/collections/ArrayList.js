/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.collections.ArrayList"])dojo._hasResource["dojox.collections.ArrayList"]=!0,dojo.provide("dojox.collections.ArrayList"),dojo.require("dojox.collections._base"),dojox.collections.ArrayList=function(d){var b=[];d&&(b=b.concat(d));this.count=b.length;this.add=function(a){b.push(a);this.count=b.length};this.addRange=function(a){if(a.getIterator)for(a=a.getIterator();!a.atEnd();)this.add(a.get());else for(var c=0;c<a.length;c++)b.push(a[c]);this.count=b.length};this.clear=
function(){b.splice(0,b.length);this.count=0};this.clone=function(){return new dojox.collections.ArrayList(b)};this.contains=function(a){for(var c=0;c<b.length;c++)if(b[c]==a)return!0;return!1};this.forEach=function(a,c){dojo.forEach(b,a,c)};this.getIterator=function(){return new dojox.collections.Iterator(b)};this.indexOf=function(a){for(var c=0;c<b.length;c++)if(b[c]==a)return c;return-1};this.insert=function(a,c){b.splice(a,0,c);this.count=b.length};this.item=function(a){return b[a]};this.remove=
function(a){a=this.indexOf(a);a>=0&&b.splice(a,1);this.count=b.length};this.removeAt=function(a){b.splice(a,1);this.count=b.length};this.reverse=function(){b.reverse()};this.sort=function(a){a?b.sort(a):b.sort()};this.setByIndex=function(a,c){b[a]=c;this.count=b.length};this.toArray=function(){return[].concat(b)};this.toString=function(a){return b.join(a||",")}};