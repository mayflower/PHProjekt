/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.encoding.easy64"]||(dojo._hasResource["dojox.encoding.easy64"]=!0,dojo.provide("dojox.encoding.easy64"),dojo.getObject("encoding.easy64",!0,dojox),function(){var f=function(a,d,e){for(var b=0;b<d;b+=3)e.push(String.fromCharCode((a[b]>>>2)+33),String.fromCharCode(((a[b]&3)<<4)+(a[b+1]>>>4)+33),String.fromCharCode(((a[b+1]&15)<<2)+(a[b+2]>>>6)+33),String.fromCharCode((a[b+2]&63)+33))};dojox.encoding.easy64.encode=function(a){var d=[],e=a.length%3,b=a.length-e;f(a,b,d);if(e){for(a=
a.slice(b);a.length<3;)a.push(0);f(a,3,d);for(a=3;a>e;d.pop(),--a);}return d.join("")};dojox.encoding.easy64.decode=function(a){var d=a.length,e=[],b=[0,0,0,0],g,c,f;for(g=0;g<d;g+=4){for(c=0;c<4;++c)b[c]=a.charCodeAt(g+c)-33;for(c=f=d-g;c<4;b[++c]=0);e.push((b[0]<<2)+(b[1]>>>4),((b[1]&15)<<4)+(b[2]>>>2),((b[2]&3)<<6)+b[3]);for(c=f;c<4;++c,e.pop());}return e}}());