/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojo.currency"])dojo._hasResource["dojo.currency"]=!0,dojo.provide("dojo.currency"),dojo.require("dojo.number"),dojo.require("dojo.i18n"),dojo.requireLocalization("dojo.cldr","currency",null,"ROOT,ar,ca,cs,da,de,el,en,en-au,en-ca,es,fi,fr,he,hu,it,ja,ko,nb,nl,pl,pt,ro,ru,sk,sl,sv,th,tr,zh,zh-hant,zh-hk,zh-tw"),dojo.require("dojo.cldr.monetary"),dojo.getObject("currency",!0,dojo),dojo.currency._mixInDefaults=function(a){a=a||{};a.type="currency";var b=dojo.i18n.getLocalization("dojo.cldr",
"currency",a.locale)||{},d=a.currency,c=dojo.cldr.monetary.getData(d);dojo.forEach(["displayName","symbol","group","decimal"],function(a){c[a]=b[d+"_"+a]});c.fractional=[!0,!1];return dojo.mixin(c,a)},dojo.currency.format=function(a,b){return dojo.number.format(a,dojo.currency._mixInDefaults(b))},dojo.currency.regexp=function(a){return dojo.number.regexp(dojo.currency._mixInDefaults(a))},dojo.currency.parse=function(a,b){return dojo.number.parse(a,dojo.currency._mixInDefaults(b))};