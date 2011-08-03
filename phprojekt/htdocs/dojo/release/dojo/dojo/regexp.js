/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojo.regexp"])dojo._hasResource["dojo.regexp"]=!0,dojo.provide("dojo.regexp"),dojo.getObject("regexp",!0,dojo),dojo.regexp.escapeString=function(a,b){return a.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g,function(a){return b&&b.indexOf(a)!=-1?a:"\\"+a})},dojo.regexp.buildGroupRE=function(a,b,e){if(!(a instanceof Array))return b(a);for(var d=[],c=0;c<a.length;c++)d.push(b(a[c]));return dojo.regexp.group(d.join("|"),e)},dojo.regexp.group=function(a,b){return"("+(b?"?:":"")+a+")"};