/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.fx.style"]||(dojo._hasResource["dojox.fx.style"]=!0,dojo.provide("dojox.fx.style"),dojo.experimental("dojox.fx.style"),dojo.require("dojo.fx"),function(){var b=dojo,i=function(a){return b.map(dojox.fx._allowedProperties,function(b){return a[b]})},k=function(a,c,d){var a=b.byId(a),e=b.getComputedStyle(a),g=i(e);b[d?"addClass":"removeClass"](a,c);var f=i(e);b[d?"removeClass":"addClass"](a,c);var j={},h=0;b.forEach(dojox.fx._allowedProperties,function(a){g[h]!=f[h]&&(j[a]=parseInt(f[h]));
h++});return j};b.mixin(dojox.fx,{addClass:function(a,c,d){var a=b.byId(a),e=function(a){return function(){b.addClass(a,c);a.style.cssText=f}}(a),g=k(a,c,!0),f=a.style.cssText,a=b.animateProperty(b.mixin({node:a,properties:g},d));b.connect(a,"onEnd",a,e);return a},removeClass:function(a,c,d){var a=b.byId(a),e=function(a){return function(){b.removeClass(a,c);a.style.cssText=f}}(a),g=k(a,c),f=a.style.cssText,a=b.animateProperty(b.mixin({node:a,properties:g},d));b.connect(a,"onEnd",a,e);return a},toggleClass:function(a,
c,d,e){typeof d=="undefined"&&(d=!b.hasClass(a,c));return dojox.fx[d?"addClass":"removeClass"](a,c,e)},_allowedProperties:["width","height","left","top","backgroundColor","color","borderBottomWidth","borderTopWidth","borderLeftWidth","borderRightWidth","paddingLeft","paddingRight","paddingTop","paddingBottom","marginLeft","marginTop","marginRight","marginBottom","lineHeight","letterSpacing","fontSize"]})}());