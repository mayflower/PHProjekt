/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.dtl.filter.integers"]||(dojo._hasResource["dojox.dtl.filter.integers"]=!0,dojo.provide("dojox.dtl.filter.integers"),dojo.mixin(dojox.dtl.filter.integers,{add:function(a,b){a=parseInt(a,10);b=parseInt(b,10);return isNaN(b)?a:a+b},get_digit:function(a,b){a=parseInt(a,10);b=parseInt(b,10)-1;b>=0&&(a+="",a=b<a.length?parseInt(a.charAt(b),10):0);return isNaN(a)?0:a}}));