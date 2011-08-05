/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojo.data.util.filter"])dojo._hasResource["dojo.data.util.filter"]=!0,dojo.provide("dojo.data.util.filter"),dojo.getObject("data.util.filter",!0,dojo),dojo.data.util.filter.patternToRegExp=function(d,e){for(var a="^",c=null,b=0;b<d.length;b++)switch(c=d.charAt(b),c){case "\\":a+=c;b++;a+=d.charAt(b);break;case "*":a+=".*";break;case "?":a+=".";break;case "$":case "^":case "/":case "+":case ".":case "|":case "(":case ")":case "{":case "}":case "[":case "]":a+="\\";default:a+=
c}a+="$";return e?RegExp(a,"mi"):RegExp(a,"m")};