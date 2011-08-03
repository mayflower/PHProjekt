/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojo.cookie"])dojo._hasResource["dojo.cookie"]=!0,dojo.provide("dojo.cookie"),dojo.require("dojo.regexp"),dojo.cookie=function(f,e,b){var a=document.cookie;if(arguments.length==1){var d=a.match(RegExp("(?:^|; )"+dojo.regexp.escapeString(f)+"=([^;]*)"));return d?decodeURIComponent(d[1]):void 0}else{b=b||{};a=b.expires;if(typeof a=="number"){var c=new Date;c.setTime(c.getTime()+a*864E5);a=b.expires=c}if(a&&a.toUTCString)b.expires=a.toUTCString();e=encodeURIComponent(e);a=f+"="+
e;for(d in b)a+="; "+d,c=b[d],c!==!0&&(a+="="+c);document.cookie=a}},dojo.cookie.isSupported=function(){if(!("cookieEnabled"in navigator))this("__djCookieTest__","CookiesAllowed"),navigator.cookieEnabled=this("__djCookieTest__")=="CookiesAllowed",navigator.cookieEnabled&&this("__djCookieTest__","",{expires:-1});return navigator.cookieEnabled};