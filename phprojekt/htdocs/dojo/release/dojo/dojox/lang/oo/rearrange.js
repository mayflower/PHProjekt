/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.lang.oo.rearrange"]||(dojo._hasResource["dojox.lang.oo.rearrange"]=!0,dojo.provide("dojox.lang.oo.rearrange"),function(){var h=dojo._extraNames,i=h.length,j=Object.prototype.toString,e={};dojox.lang.oo.rearrange=function(c,g){var a,b,d,f;for(a in g)if(b=g[a],!b||j.call(b)=="[object String]")if(d=c[a],!(a in e)||e[a]!==d)delete c[a]||(c[a]=void 0),b&&(c[b]=d);if(i)for(f=0;f<i;++f)if(a=h[f],b=g[a],!b||j.call(b)=="[object String]")if(d=c[a],!(a in e)||e[a]!==d)delete c[a]||(c[a]=
void 0),b&&(c[b]=d);return c}}());