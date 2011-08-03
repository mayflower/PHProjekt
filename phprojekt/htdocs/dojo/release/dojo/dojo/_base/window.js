/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojo._base.window"])dojo._hasResource["dojo._base.window"]=!0,dojo.provide("dojo._base.window"),dojo.doc=window.document||null,dojo.body=function(){return dojo.doc.body||dojo.doc.getElementsByTagName("body")[0]},dojo.setContext=function(b,a){dojo.global=b;dojo.doc=a},dojo.withGlobal=function(b,a,c,d){var e=dojo.global;try{return dojo.global=b,dojo.withDoc.call(null,b.document,a,c,d)}finally{dojo.global=e}},dojo.withDoc=function(b,a,c,d){var e=dojo.doc,f=dojo._bodyLtr,g=dojo.isQuirks;
try{return dojo.doc=b,delete dojo._bodyLtr,dojo.isQuirks=dojo.doc.compatMode=="BackCompat",c&&typeof a=="string"&&(a=c[a]),a.apply(c,d||[])}finally{dojo.doc=e;delete dojo._bodyLtr;if(f!==void 0)dojo._bodyLtr=f;dojo.isQuirks=g}};