/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


define(["dojo"],function(h){var m=/(^.*(^|\/)nls(\/|$))([^\/]*)\/?([^\/]*)/,n=function(a,b,c,f){for(var g=[c+f],b=b.split("-"),d="",e=0;e<b.length;e++)d+=b[e],a[d]&&g.push(c+d+"/"+f);return g},c={};return{load:function(a,b,l){var a=m.exec(a),f=b.toAbsMid&&b.toAbsMid(a[1])||a[1],g=a[5]||a[4],d=f+g,e=a[5]&&a[4]||h.locale,j=d+"/"+e;c[j]?l(c[j]):b([d],function(a){var i=c[d+"/"]=h.clone(a.root),k=n(a,e,f,g);b(k,function(){for(var a=1;a<k.length;a++)c[d+"/"+k[a]]=i=h.mixin(h.clone(i),arguments[a]);c[j]=
i;l(i)})})},cache:function(a,b){c[a]=b}}});