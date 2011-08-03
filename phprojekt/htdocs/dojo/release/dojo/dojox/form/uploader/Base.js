/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.form.uploader.Base"]||(dojo._hasResource["dojox.form.uploader.Base"]=!0,dojo.provide("dojox.form.uploader.Base"),dojo.require("dijit._Widget"),dojo.require("dijit._Templated"),dojo.declare("dojox.form.uploader.Base",[dijit._Widget,dijit._Templated],{getForm:function(){if(!this.form)for(var a=this.domNode;a&&a.tagName&&a!==document.body;){if(a.tagName.toLowerCase()=="form"){this.form=a;break}a=a.parentNode}return this.form},getUrl:function(){if(this.uploadUrl)this.url=this.uploadUrl;
if(this.url)return this.url;if(this.getForm())this.url=this.form.action;return this.url},connectForm:function(){this.url=this.getUrl();if(!this._fcon&&this.getForm())this._fcon=!0,this.connect(this.form,"onsubmit",function(a){dojo.stopEvent(a);this.submit(dojo.formToObject(this.form))})},supports:function(a){if(!this._hascache)this._hascache={testDiv:dojo.create("div"),testInput:dojo.create("input",{type:"file"}),xhr:window.XMLHttpRequest?new XMLHttpRequest:{}},dojo.style(this._hascache.testDiv,"opacity",
0.7);switch(a){case "FormData":return!!window.FormData;case "sendAsBinary":return!!this._hascache.xhr.sendAsBinary;case "opacity":return dojo.style(this._hascache.testDiv,"opacity")==0.7;case "multiple":if(this.force=="flash"||this.force=="iframe")break;a=dojo.attr(this._hascache.testInput,"multiple");return a===!0||a===!1}return!1},getMimeType:function(){return"application/octet-stream"},getFileType:function(a){return a.substring(a.lastIndexOf(".")+1).toUpperCase()},convertBytes:function(a){var c=
Math.round(a/1024*1E5)/1E5,d=Math.round(a/1048576*1E5)/1E5,e=Math.round(a/1073741824*1E5)/1E5,b=a;c>1&&(b=c.toFixed(1)+" kb");d>1&&(b=d.toFixed(1)+" mb");e>1&&(b=e.toFixed(1)+" gb");return{kb:c,mb:d,gb:e,bytes:a,value:b}}}));