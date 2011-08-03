/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.math.random.prng4"]||(dojo._hasResource["dojox.math.random.prng4"]=!0,dojo.provide("dojox.math.random.prng4"),dojo.getObject("math.random.prng4",!0,dojox),function(){function f(){this.j=this.i=0;this.S=Array(256)}dojo.extend(f,{init:function(d){var a,c,b,e=this.S,f=d.length;for(a=0;a<256;++a)e[a]=a;for(a=c=0;a<256;++a)c=c+e[a]+d[a%f]&255,b=e[a],e[a]=e[c],e[c]=b;this.j=this.i=0},next:function(){var d,a,c,b=this.S;this.i=a=this.i+1&255;this.j=c=this.j+b[a]&255;d=b[a];b[a]=b[c];
b[c]=d;return b[d+b[a]&255]}});dojox.math.random.prng4=function(){return new f};dojox.math.random.prng4.size=256}());