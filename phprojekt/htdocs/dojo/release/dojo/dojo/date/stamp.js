/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojo.date.stamp"])dojo._hasResource["dojo.date.stamp"]=!0,dojo.provide("dojo.date.stamp"),dojo.getObject("date.stamp",!0,dojo),dojo.date.stamp.fromISOString=function(e,d){if(!dojo.date.stamp._isoRegExp)dojo.date.stamp._isoRegExp=/^(?:(\d{4})(?:-(\d{2})(?:-(\d{2}))?)?)?(?:T(\d{2}):(\d{2})(?::(\d{2})(.\d+)?)?((?:[+-](\d{2}):(\d{2}))|Z)?)?$/;var a=dojo.date.stamp._isoRegExp.exec(e),f=null;if(a){a.shift();a[1]&&a[1]--;a[6]&&(a[6]*=1E3);d&&(d=new Date(d),dojo.forEach(dojo.map(["FullYear",
"Month","Date","Hours","Minutes","Seconds","Milliseconds"],function(a){return d["get"+a]()}),function(b,c){a[c]=a[c]||b}));f=new Date(a[0]||1970,a[1]||0,a[2]||1,a[3]||0,a[4]||0,a[5]||0,a[6]||0);a[0]<100&&f.setFullYear(a[0]||1970);var b=0,c=a[7]&&a[7].charAt(0);c!="Z"&&(b=(a[8]||0)*60+(Number(a[9])||0),c!="-"&&(b*=-1));c&&(b-=f.getTimezoneOffset());b&&f.setTime(f.getTime()+b*6E4)}return f},dojo.date.stamp.toISOString=function(e,d){var a=function(a){return a<10?"0"+a:a},d=d||{},f=[],b=d.zulu?"getUTC":
"get",c="";d.selector!="time"&&(c=e[b+"FullYear"](),c=["0000".substr((c+"").length)+c,a(e[b+"Month"]()+1),a(e[b+"Date"]())].join("-"));f.push(c);if(d.selector!="date"){c=[a(e[b+"Hours"]()),a(e[b+"Minutes"]()),a(e[b+"Seconds"]())].join(":");b=e[b+"Milliseconds"]();d.milliseconds&&(c+="."+(b<100?"0":"")+a(b));if(d.zulu)c+="Z";else if(d.selector!="time"){var b=e.getTimezoneOffset(),g=Math.abs(b);c+=(b>0?"-":"+")+a(Math.floor(g/60))+":"+a(g%60)}f.push(c)}return f.join("T")};