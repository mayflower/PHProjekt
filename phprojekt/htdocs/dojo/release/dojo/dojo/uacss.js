/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojo.uacss"]||(dojo._hasResource["dojo.uacss"]=!0,dojo.provide("dojo.uacss"),function(){var a=dojo,d=a.doc.documentElement,b=a.isIE,g=a.isOpera,c=Math.floor,h=a.isFF,i=a.boxModel.replace(/-/,""),b={dj_ie:b,dj_ie6:c(b)==6,dj_ie7:c(b)==7,dj_ie8:c(b)==8,dj_ie9:c(b)==9,dj_quirks:a.isQuirks,dj_iequirks:b&&a.isQuirks,dj_opera:g,dj_khtml:a.isKhtml,dj_webkit:a.isWebKit,dj_safari:a.isSafari,dj_chrome:a.isChrome,dj_gecko:a.isMozilla,dj_ff3:c(h)==3};b["dj_"+i]=!0;var e="",f;for(f in b)b[f]&&
(e+=f+" ");d.className=a.trim(d.className+" "+e);dojo._loaders.unshift(function(){if(!dojo._isBodyLtr()){var b="dj_rtl dijitRtl "+e.replace(/ /g,"-rtl ");d.className=a.trim(d.className+" "+b)}})}());