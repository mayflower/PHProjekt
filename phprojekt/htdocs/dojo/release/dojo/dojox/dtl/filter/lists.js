/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.dtl.filter.lists"]||(dojo._hasResource["dojox.dtl.filter.lists"]=!0,dojo.provide("dojox.dtl.filter.lists"),dojo.require("dojox.dtl._base"),dojo.mixin(dojox.dtl.filter.lists,{_dictsort:function(a,c){return a[0]==c[0]?0:a[0]<c[0]?-1:1},dictsort:function(a,c){if(!c)return a;var d,b,e=[];if(!dojo.isArray(a))for(b in d=a,a=[],d)a.push(d[b]);for(d=0;d<a.length;d++)e.push([(new dojox.dtl._Filter("var."+c)).resolve(new dojox.dtl._Context({"var":a[d]})),a[d]]);e.sort(dojox.dtl.filter.lists._dictsort);
var f=[];for(d=0;b=e[d];d++)f.push(b[1]);return f},dictsortreversed:function(a,c){return!c?a:dojox.dtl.filter.lists.dictsort(a,c).reverse()},first:function(a){return a.length?a[0]:""},join:function(a,c){return a.join(c||",")},length:function(a){return isNaN(a.length)?(a+"").length:a.length},length_is:function(a,c){return a.length==parseInt(c)},random:function(a){return a[Math.floor(Math.random()*a.length)]},slice:function(a,c){for(var d=(c||"").split(":"),b=[],e=0;e<d.length;e++)d[e].length?b.push(parseInt(d[e])):
b.push(null);b[0]===null&&(b[0]=0);b[0]<0&&(b[0]=a.length+b[0]);if(b.length<2||b[1]===null)b[1]=a.length;b[1]<0&&(b[1]=a.length+b[1]);return a.slice(b[0],b[1])},_unordered_list:function(a,c){var d=dojox.dtl.filter.lists,b,e="";for(b=0;b<c;b++)e+="\t";if(a[1]&&a[1].length){var f=[];for(b=0;b<a[1].length;b++)f.push(d._unordered_list(a[1][b],c+1));return e+"<li>"+a[0]+"\n"+e+"<ul>\n"+f.join("\n")+"\n"+e+"</ul>\n"+e+"</li>"}else return e+"<li>"+a[0]+"</li>"},unordered_list:function(a){return dojox.dtl.filter.lists._unordered_list(a,
1)}}));