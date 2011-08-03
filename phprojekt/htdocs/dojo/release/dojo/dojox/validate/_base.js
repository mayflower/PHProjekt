/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.validate._base"])dojo._hasResource["dojox.validate._base"]=!0,dojo.provide("dojox.validate._base"),dojo.experimental("dojox.validate"),dojo.require("dojo.regexp"),dojo.require("dojo.number"),dojo.require("dojox.validate.regexp"),dojox.validate.isText=function(b,a){a=typeof a=="object"?a:{};return/^\s*$/.test(b)?!1:typeof a.length=="number"&&a.length!=b.length?!1:typeof a.minlength=="number"&&a.minlength>b.length?!1:typeof a.maxlength=="number"&&a.maxlength<b.length?!1:
!0},dojox.validate._isInRangeCache={},dojox.validate.isInRange=function(b,a){b=dojo.number.parse(b,a);if(isNaN(b))return!1;var a=typeof a=="object"?a:{},e=typeof a.max=="number"?a.max:Infinity,d=typeof a.min=="number"?a.min:-Infinity,c=dojox.validate._isInRangeCache,f=b+"max"+e+"min"+d+"dec"+(typeof a.decimal=="string"?a.decimal:".");if(typeof c[f]!="undefined")return c[f];c[f]=!(b<d||b>e);return c[f]},dojox.validate.isNumberFormat=function(b,a){return RegExp("^"+dojox.validate.regexp.numberFormat(a)+
"$","i").test(b)},dojox.validate.isValidLuhn=function(b){var a=0,e,d;dojo.isString(b)||(b=String(b));b=b.replace(/[- ]/g,"");e=b.length%2;for(var c=0;c<b.length;c++)d=parseInt(b.charAt(c)),c%2==e&&(d*=2),d>9&&(d-=9),a+=d;return!(a%10)};