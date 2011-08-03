/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.drawing.util.typeset"]||(dojo._hasResource["dojox.drawing.util.typeset"]=!0,dojo.provide("dojox.drawing.util.typeset"),dojo.require("dojox.drawing.library.greek"),function(){var c=dojox.drawing.library.greek;dojox.drawing.util.typeset={convertHTML:function(b){return b?b.replace(/&([^;]+);/g,function(b,a){if(a.charAt(0)=="#"){var d=+a.substr(1);if(!isNaN(d))return String.fromCharCode(d)}else if(c[a])return String.fromCharCode(c[a]);console.warn("no HTML conversion for ",b);
return b}):b},convertLaTeX:function(b){return b?b.replace(/\\([a-zA-Z]+)/g,function(e,a){if(c[a])return String.fromCharCode(c[a]);else if(a.substr(0,2)=="mu")return String.fromCharCode(c.mu)+a.substr(2);else if(a.substr(0,5)=="theta")return String.fromCharCode(c.theta)+a.substr(5);else if(a.substr(0,3)=="phi")return String.fromCharCode(c.phi)+a.substr(3);console.log("no match for ",e," in ",b);console.log("Need user-friendly error handling here!")}).replace(/\\\\/g,"\\"):b}}}());