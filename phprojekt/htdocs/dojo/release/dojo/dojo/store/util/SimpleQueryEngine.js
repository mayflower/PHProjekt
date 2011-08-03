/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojo.store.util.SimpleQueryEngine"])dojo._hasResource["dojo.store.util.SimpleQueryEngine"]=!0,dojo.provide("dojo.store.util.SimpleQueryEngine"),dojo.getObject("store.util",!0,dojo),dojo.store.util.SimpleQueryEngine=function(a,b){function e(c){c=dojo.filter(c,a);b&&b.sort&&c.sort(function(c,a){for(var d,e=0;d=b.sort[e];e++){var f=c[d.attribute],g=a[d.attribute];if(f!=g)return!!d.descending==f>g?-1:1}return 0});if(b&&(b.start||b.count)){var d=c.length,c=c.slice(b.start||0,(b.start||
0)+(b.count||Infinity));c.total=d}return c}switch(typeof a){default:throw Error("Can not query with a "+typeof a);case "object":case "undefined":var f=a,a=function(c){for(var a in f){var b=f[a];if(b&&b.test){if(!b.test(c[a]))return!1}else if(b!=c[a])return!1}return!0};break;case "string":if(!this[a])throw Error("No filter function "+a+" was found in store");a=this[a];case "function":}e.matches=a;return e};