/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojo.store.util.QueryResults"])dojo._hasResource["dojo.store.util.QueryResults"]=!0,dojo.provide("dojo.store.util.QueryResults"),dojo.getObject("store.util",!0,dojo),dojo.store.util.QueryResults=function(a){function b(c){a[c]||(a[c]=function(){var b=arguments;return dojo.when(a,function(a){Array.prototype.unshift.call(b,a);return dojo.store.util.QueryResults(dojo[c].apply(dojo,b))})})}if(!a)return a;a.then&&(a=dojo.delegate(a));b("forEach");b("filter");b("map");if(!a.total)a.total=
dojo.when(a,function(a){return a.length});return a};