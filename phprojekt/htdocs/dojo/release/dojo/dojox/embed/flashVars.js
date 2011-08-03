/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.embed.flashVars"]||(dojo._hasResource["dojox.embed.flashVars"]=!0,dojo.provide("dojox.embed.flashVars"),dojo.mixin(dojox.embed.flashVars,{serialize:function(e,b){var f=function(a){typeof a=="string"&&(a=a.replace(/;/g,"_sc_"),a=a.replace(/\./g,"_pr_"),a=a.replace(/\:/g,"_cl_"));return a},g=dojox.embed.flashVars.serialize,d="";if(dojo.isArray(b)){for(var c=0;c<b.length;c++)d+=g(e+"."+c,f(b[c]))+";";return d.replace(/;{2,}/g,";")}else if(dojo.isObject(b)){for(c in b)d+=g(e+
"."+c,f(b[c]))+";";return d.replace(/;{2,}/g,";")}return e+":"+b}}));