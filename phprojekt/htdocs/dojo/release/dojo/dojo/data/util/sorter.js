/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojo.data.util.sorter"])dojo._hasResource["dojo.data.util.sorter"]=!0,dojo.provide("dojo.data.util.sorter"),dojo.getObject("data.util.sorter",!0,dojo),dojo.data.util.sorter.basicComparator=function(a,c){var d=-1;a===null&&(a=void 0);c===null&&(c=void 0);if(a==c)d=0;else if(a>c||a==null)d=1;return d},dojo.data.util.sorter.createSortFunction=function(a,c){function d(a,c,b,d){return function(e,f){var g=d.getValue(e,a),h=d.getValue(f,a);return c*b(g,h)}}for(var f=[],e,h=c.comparatorMap,
i=dojo.data.util.sorter.basicComparator,g=0;g<a.length;g++){e=a[g];var b=e.attribute;if(b){e=e.descending?-1:1;var j=i;h&&(typeof b!=="string"&&"toString"in b&&(b=b.toString()),j=h[b]||i);f.push(d(b,e,j,c))}}return function(a,c){for(var b=0;b<f.length;){var d=f[b++](a,c);if(d!==0)return d}return 0}};