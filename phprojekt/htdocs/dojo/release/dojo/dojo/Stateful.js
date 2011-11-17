/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojo.Stateful"]||(dojo._hasResource["dojo.Stateful"]=!0,dojo.provide("dojo.Stateful"),dojo.declare("dojo.Stateful",null,{postscript:function(a){a&&dojo.mixin(this,a)},get:function(a){return this[a]},set:function(a,c){if(typeof a==="object"){for(var b in a)this.set(b,a[b]);return this}b=this[a];this[a]=c;this._watchCallbacks&&this._watchCallbacks(a,b,c);return this},watch:function(a,c){var b=this._watchCallbacks;if(!b)var h=this,b=this._watchCallbacks=function(a,c,d,f){var e=function(b){if(b)for(var b=
b.slice(),g=0,e=b.length;g<e;g++)try{b[g].call(h,a,c,d)}catch(f){console.error(f)}};e(b["_"+a]);f||e(b["*"])};!c&&typeof a==="function"?(c=a,a="*"):a="_"+a;var d=b[a];typeof d!=="object"&&(d=b[a]=[]);d.push(c);return{unwatch:function(){d.splice(dojo.indexOf(d,c),1)}}}}));