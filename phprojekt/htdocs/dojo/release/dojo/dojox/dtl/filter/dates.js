/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.dtl.filter.dates"]||(dojo._hasResource["dojox.dtl.filter.dates"]=!0,dojo.provide("dojox.dtl.filter.dates"),dojo.require("dojox.dtl.utils.date"),function(){var c=dojox.dtl.filter.dates;dojo.mixin(c,{_toDate:function(a){if(a instanceof Date)return a;a=new Date(a);return a.getTime()==(new Date(0)).getTime()?"":a},date:function(a,b){a=c._toDate(a);return!a?"":dojox.dtl.utils.date.format(a,b||"N j, Y")},time:function(a,b){a=c._toDate(a);return!a?"":dojox.dtl.utils.date.format(a,
b||"P")},timesince:function(a,b){a=c._toDate(a);if(!a)return"";var d=dojox.dtl.utils.date.timesince;return b?d(b,a):d(a)},timeuntil:function(a,b){a=c._toDate(a);if(!a)return"";var d=dojox.dtl.utils.date.timesince;return b?d(b,a):d(new Date,a)}})}());