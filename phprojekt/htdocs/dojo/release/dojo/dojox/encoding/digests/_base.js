/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.encoding.digests._base"]||(dojo._hasResource["dojox.encoding.digests._base"]=!0,dojo.provide("dojox.encoding.digests._base"),dojo.getObject("encoding.digests",!0,dojox),function(){var d=dojox.encoding.digests;d.outputTypes={Base64:0,Hex:1,String:2,Raw:3};d.addWords=function(b,c){var a=(b&65535)+(c&65535);return(b>>16)+(c>>16)+(a>>16)<<16|a&65535};d.stringToWord=function(b){for(var c=[],a=0,e=b.length*8;a<e;a+=8)c[a>>5]|=(b.charCodeAt(a/8)&255)<<a%32;return c};d.wordToString=
function(b){for(var c=[],a=0,e=b.length*32;a<e;a+=8)c.push(String.fromCharCode(b[a>>5]>>>a%32&255));return c.join("")};d.wordToHex=function(b){for(var c=[],a=0,e=b.length*4;a<e;a++)c.push("0123456789abcdef".charAt(b[a>>2]>>a%4*8+4&15)+"0123456789abcdef".charAt(b[a>>2]>>a%4*8&15));return c.join("")};d.wordToBase64=function(b){for(var c=[],a=0,e=b.length*4;a<e;a+=3)for(var d=(b[a>>2]>>8*(a%4)&255)<<16|(b[a+1>>2]>>8*((a+1)%4)&255)<<8|b[a+2>>2]>>8*((a+2)%4)&255,f=0;f<4;f++)a*8+f*6>b.length*32?c.push("="):
c.push("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/".charAt(d>>6*(3-f)&63));return c.join("")}}());