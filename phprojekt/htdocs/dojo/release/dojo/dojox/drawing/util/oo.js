/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.drawing.util.oo"])dojo._hasResource["dojox.drawing.util.oo"]=!0,dojo.provide("dojox.drawing.util.oo"),dojox.drawing.util.oo={declare:function(){var b,c,e=0,a=arguments;a.length<2&&console.error("gfx.oo.declare; not enough arguments");a.length==2?(b=a[0],c=a[1]):(a=Array.prototype.slice.call(arguments),c=a.pop(),b=a.pop(),e=1);for(var d in c)b.prototype[d]=c[d];e&&(a.unshift(b),b=this.extend.apply(this,a));return b},extend:function(){var b=arguments,c=b[0];b.length<2&&
console.error("gfx.oo.extend; not enough arguments");for(var e=function(){for(var a=1;a<b.length;a++)b[a].prototype.constructor.apply(this,arguments);c.prototype.constructor.apply(this,arguments)},a=1;a<b.length;a++)for(var d in b[a].prototype)e.prototype[d]=b[a].prototype[d];for(d in c.prototype)e.prototype[d]=c.prototype[d];return e}};