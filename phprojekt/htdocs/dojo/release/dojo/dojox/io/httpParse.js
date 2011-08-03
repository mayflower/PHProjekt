/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.io.httpParse"])dojo._hasResource["dojox.io.httpParse"]=!0,dojo.provide("dojox.io.httpParse"),dojox.io.httpParse=function(b,h,i){var f=[],j=b.length;do{var e={},c=b.match(/(\n*[^\n]+)/);if(!c)return null;for(var b=b.substring(c[0].length+1),c=c[1],a=b.match(/([^\n]+\n)*/)[0],b=b.substring(a.length),k=b.substring(0,1),b=b.substring(1),l=a=(h||"")+a,a=a.match(/[^:\n]+:[^\n]+\n/g),d=0;d<a.length;d++){var g=a[d].indexOf(":");e[a[d].substring(0,g)]=a[d].substring(g+1).replace(/(^[ \r\n]*)|([ \r\n]*)$/g,
"")}c=c.split(" ");c={status:parseInt(c[1],10),statusText:c[2],readyState:3,getAllResponseHeaders:function(){return l},getResponseHeader:function(a){return e[a]}};if(a=e["Content-Length"])if(a<=b.length)a=b.substring(0,a);else break;else if(a=b.match(/(.*)HTTP\/\d\.\d \d\d\d[\w\s]*\n/))a=a[0];else if(!i||k=="\n")a=b;else break;f.push(c);b=b.substring(a.length);c.responseText=a;c.readyState=4;c._lastIndex=j-b.length}while(b);return f};