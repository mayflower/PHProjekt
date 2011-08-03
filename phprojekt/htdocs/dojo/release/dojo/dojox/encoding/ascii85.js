/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.encoding.ascii85"]||(dojo._hasResource["dojox.encoding.ascii85"]=!0,dojo.provide("dojox.encoding.ascii85"),dojo.getObject("encoding.ascii85",!0,dojox),function(){var h=function(b,f,d){var c,e,a,g=[0,0,0,0,0];for(c=0;c<f;c+=4){if(a=((b[c]*256+b[c+1])*256+b[c+2])*256+b[c+3])for(e=0;e<5;g[e++]=a%85+33,a=Math.floor(a/85));else d.push("z");d.push(String.fromCharCode(g[4],g[3],g[2],g[1],g[0]))}};dojox.encoding.ascii85.encode=function(b){var f=[],d=b.length%4,c=b.length-d;h(b,c,
f);if(d){for(b=b.slice(c);b.length<4;)b.push(0);h(b,4,f);b=f.pop();b=="z"&&(b="!!!!!");f.push(b.substr(0,d+1))}return f.join("")};dojox.encoding.ascii85.decode=function(b){var f=b.length,d=[],c=[0,0,0,0,0],e,a,g,h,i;for(e=0;e<f;++e)if(b.charAt(e)=="z")d.push(0,0,0,0);else{for(a=0;a<5;++a)c[a]=b.charCodeAt(e+a)-33;i=f-e;if(i<5){for(a=i;a<4;c[++a]=0);c[i]=85}a=(((c[0]*85+c[1])*85+c[2])*85+c[3])*85+c[4];g=a&255;a>>>=8;h=a&255;a>>>=8;d.push(a>>>8,a&255,h,g);for(a=i;a<5;++a,d.pop());e+=4}return d}}());