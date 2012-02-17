/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojo.behavior"])dojo._hasResource["dojo.behavior"]=!0,dojo.provide("dojo.behavior"),dojo.behavior=new function(){function f(a,b){a[b]||(a[b]=[]);return a[b]}function g(a,b,e){var d={},c;for(c in a)typeof d[c]=="undefined"&&(e?e.call(b,a[c],c):b(a[c],c))}var h=0;this._behaviors={};this.add=function(a){g(a,this,function(b,a){var d=f(this._behaviors,a);if(typeof d.id!="number")d.id=h++;var c=[];d.push(c);if(dojo.isString(b)||dojo.isFunction(b))b={found:b};g(b,function(a,b){f(c,
b).push(a)})})};var i=function(a,b,e){dojo.isString(b)?e=="found"?dojo.publish(b,[a]):dojo.connect(a,e,function(){dojo.publish(b,arguments)}):dojo.isFunction(b)&&(e=="found"?b(a):dojo.connect(a,e,b))};this.apply=function(){g(this._behaviors,function(a,b){dojo.query(b).forEach(function(b){var d=0,c="_dj_behavior_"+a.id;if(typeof b[c]=="number"&&(d=b[c],d==a.length))return;for(var f;f=a[d];d++)g(f,function(a,c){dojo.isArray(a)&&dojo.forEach(a,function(a){i(b,a,c)})});b[c]=a.length})})}},dojo.addOnLoad(dojo.behavior,
"apply");