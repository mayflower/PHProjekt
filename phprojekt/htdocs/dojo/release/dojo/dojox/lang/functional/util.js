/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.lang.functional.util"]||(dojo._hasResource["dojox.lang.functional.util"]=!0,dojo.provide("dojox.lang.functional.util"),dojo.require("dojox.lang.functional.lambda"),function(){var g=dojox.lang.functional;dojo.mixin(g,{inlineLambda:function(b,e,f){b=g.rawLambda(b);f&&g.forEach(b.args,f);var h=(f=typeof e=="string")?b.args.length:Math.min(b.args.length,e.length),a=Array(4*h+4),d,c=1;for(d=0;d<h;++d)a[c++]=b.args[d],a[c++]="=",a[c++]=f?e+"["+d+"]":e[d],a[c++]=",";a[0]="(";a[c++]=
"(";a[c++]=b.body;a[c]="))";return a.join("")}})}());