/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.data.css"])dojo._hasResource["dojox.data.css"]=!0,dojo.provide("dojox.data.css"),dojox.data.css.rules={},dojox.data.css.rules.forEach=function(c,d,b){b&&dojo.forEach(b,function(a){dojo.forEach(a[a.cssRules?"cssRules":"rules"],function(e){if(!e.type||e.type!==3){var b="";if(a&&a.href)b=a.href;c.call(d?d:this,e,a,b)}})})},dojox.data.css.findStyleSheets=function(c){var d=[];dojo.forEach(c,function(b){(b=dojox.data.css.findStyleSheet(b))&&dojo.forEach(b,function(a){dojo.indexOf(d,
a)===-1&&d.push(a)})});return d},dojox.data.css.findStyleSheet=function(c){var d=[];c.charAt(0)==="."&&(c=c.substring(1));var b=function(a){return a.href&&a.href.match(c)?(d.push(a),!0):a.imports?dojo.some(a.imports,function(a){return b(a)}):dojo.some(a[a.cssRules?"cssRules":"rules"],function(a){return a.type&&a.type===3&&b(a.styleSheet)?!0:!1})};dojo.some(document.styleSheets,b);return d},dojox.data.css.determineContext=function(c){var d=[],c=c&&c.length>0?dojox.data.css.findStyleSheets(c):document.styleSheets,
b=function(a){d.push(a);a.imports&&dojo.forEach(a.imports,function(a){b(a)});dojo.forEach(a[a.cssRules?"cssRules":"rules"],function(a){a.type&&a.type===3&&b(a.styleSheet)})};dojo.forEach(c,b);return d};