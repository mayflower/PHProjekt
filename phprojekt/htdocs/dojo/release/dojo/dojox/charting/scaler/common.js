/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.charting.scaler.common"]||(dojo._hasResource["dojox.charting.scaler.common"]=!0,dojo.provide("dojox.charting.scaler.common"),function(){var d=function(c,a){return Math.abs(c-a)<=1.0E-6*(Math.abs(c)+Math.abs(a))};dojo.mixin(dojox.charting.scaler.common,{findString:function(c,a){for(var c=c.toLowerCase(),b=0;b<a.length;++b)if(c==a[b])return!0;return!1},getNumericLabel:function(c,a,b){var e="",e=dojo.number?(b.fixed?dojo.number.format(c,{places:a<0?-a:0}):dojo.number.format(c))||
"":b.fixed?c.toFixed(a<0?-a:0):c.toString();if(b.labelFunc&&(a=b.labelFunc(e,c,a)))return a;if(b.labels){for(var b=b.labels,a=0,f=b.length;a<f;){var g=Math.floor((a+f)/2);b[g].value<c?a=g+1:f=g}if(a<b.length&&d(b[a].value,c))return b[a].text;--a;if(a>=0&&a<b.length&&d(b[a].value,c))return b[a].text;a+=2;if(a<b.length&&d(b[a].value,c))return b[a].text}return e}})}());