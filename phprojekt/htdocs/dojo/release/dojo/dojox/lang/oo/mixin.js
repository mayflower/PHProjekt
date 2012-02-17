/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.lang.oo.mixin"]||(dojo._hasResource["dojox.lang.oo.mixin"]=!0,dojo.provide("dojox.lang.oo.mixin"),dojo.experimental("dojox.lang.oo.mixin"),dojo.require("dojox.lang.oo.Filter"),dojo.require("dojox.lang.oo.Decorator"),function(){var h=dojox.lang.oo,p=h.Filter,m=h.Decorator,e={},q=function(b){return b},r=function(b,a){return a},s=function(b,a,d){b[a]=d},n=dojo._extraNames,o=n.length,j=h.applyDecorator=function(b,a,d,k){if(d instanceof m){var e=d.decorator,d=j(b,a,d.value,k);
return e(a,d,k)}return b(a,d,k)};h.__mixin=function(b,a,d,k,h){var g,f,c,i,l;for(g in a)if(c=a[g],!(g in e)||e[g]!==c)if((f=k(g,b,a,c))&&(!(f in b)||!(f in e)||e[f]!==c))i=b[f],c=j(d,f,c,i),i!==c&&h(b,f,c,i);if(o)for(l=0;l<o;++l)if(g=n[l],c=a[g],!(g in e)||e[g]!==c)if((f=k(g,b,a,c))&&(!(f in b)||!(f in e)||e[f]!==c))i=b[f],c=j(d,f,c,i),i!==c&&h(b,f,c,i);return b};h.mixin=function(b,a){for(var d,e,j=1,g=arguments.length;j<g;++j)a=arguments[j],a instanceof p?(e=a.filter,a=a.bag):e=q,a instanceof m?
(d=a.decorator,a=a.value):d=r,h.__mixin(b,a,d,e,s);return b}}());