/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.lang.functional.zip"]||(dojo._hasResource["dojox.lang.functional.zip"]=!0,dojo.provide("dojox.lang.functional.zip"),function(){var c=dojox.lang.functional;dojo.mixin(c,{zip:function(){for(var b=arguments[0].length,e=arguments.length,a=1,c=Array(b),d,f;a<e;b=Math.min(b,arguments[a++].length));for(a=0;a<b;++a){f=Array(e);for(d=0;d<e;f[d]=arguments[d][a],++d);c[a]=f}return c},unzip:function(b){return c.zip.apply(null,b)}})}());