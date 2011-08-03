/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.dtl.filter.misc"]||(dojo._hasResource["dojox.dtl.filter.misc"]=!0,dojo.provide("dojox.dtl.filter.misc"),dojo.mixin(dojox.dtl.filter.misc,{filesizeformat:function(a){a=parseFloat(a);if(a<1024)return a==1?a+" byte":a+" bytes";else if(a<1048576)return(a/1024).toFixed(1)+" KB";else if(a<1073741824)return(a/1024/1024).toFixed(1)+" MB";return(a/1024/1024/1024).toFixed(1)+" GB"},pluralize:function(a,b){b=b||"s";b.indexOf(",")==-1&&(b=","+b);var c=b.split(",");if(c.length>2)return"";
var d=c[0],c=c[1];return parseInt(a,10)!=1?c:d},_phone2numeric:{a:2,b:2,c:2,d:3,e:3,f:3,g:4,h:4,i:4,j:5,k:5,l:5,m:6,n:6,o:6,p:7,r:7,s:7,t:8,u:8,v:8,w:9,x:9,y:9},phone2numeric:function(a){var b=dojox.dtl.filter.misc;a+="";for(var c="",d=0;d<a.length;d++){var e=a.charAt(d).toLowerCase();b._phone2numeric[e]?c+=b._phone2numeric[e]:c+=a.charAt(d)}return c},pprint:function(a){return dojo.toJson(a)}}));