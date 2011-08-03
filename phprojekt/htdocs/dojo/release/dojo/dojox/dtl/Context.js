/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.dtl.Context"])dojo._hasResource["dojox.dtl.Context"]=!0,dojo.provide("dojox.dtl.Context"),dojo.require("dojox.dtl._base"),dojox.dtl.Context=dojo.extend(function(a){this._this={};dojox.dtl._Context.call(this,a)},dojox.dtl._Context.prototype,{getKeys:function(){var a=[],c;for(c in this)this.hasOwnProperty(c)&&c!="_this"&&a.push(c);return a},extend:function(a){return dojo.delegate(this,a)},filter:function(a){var c=new dojox.dtl.Context,e=[],d,b;if(a instanceof dojox.dtl.Context)e=
a.getKeys();else if(typeof a=="object")for(b in a)e.push(b);else for(d=0;b=arguments[d];d++)typeof b=="string"&&e.push(b);for(d=0;b=e[d];d++)c[b]=this[b];return c},setThis:function(a){this._this=a},getThis:function(){return this._this},hasKey:function(a){return this._getter&&typeof this._getter(a)!="undefined"?!0:typeof this[a]!="undefined"?!0:!1}});