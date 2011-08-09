/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.io.scriptFrame"]||(dojo._hasResource["dojox.io.scriptFrame"]=!0,dojo.provide("dojox.io.scriptFrame"),dojo.require("dojo.io.script"),dojo.require("dojo.io.iframe"),function(){var e=dojo.io.script;dojox.io.scriptFrame={_waiters:{},_loadedIds:{},_getWaiters:function(a){return this._waiters[a]||(this._waiters[a]=[])},_fixAttachUrl:function(){},_loaded:function(a){var b=this._getWaiters(a);this._loadedIds[a]=!0;this._waiters[a]=null;for(var d=0;d<b.length;d++){var c=b[d];c.frameDoc=
dojo.io.iframe.doc(dojo.byId(a));e.attach(c.id,c.url,c.frameDoc)}}};var g=e._canAttach,f=dojox.io.scriptFrame;e._canAttach=function(a){var b=a.args.frameDoc;if(b&&dojo.isString(b)){var d=dojo.byId(b),c=f._getWaiters(b);d?f._loadedIds[b]?(a.frameDoc=dojo.io.iframe.doc(d),this.attach(a.id,a.url,a.frameDoc)):c.push(a):(c.push(a),dojo.io.iframe.create(b,dojox._scopeName+".io.scriptFrame._loaded('"+b+"');"));return!1}else return g.apply(this,arguments)}}());