/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.date.relative"]||(dojo._hasResource["dojox.date.relative"]=!0,dojo.provide("dojox.date.relative"),dojo.require("dojo.date"),dojo.require("dojo.date.locale"),function(b){function h(a){a=dojo.clone(a);a.setHours(0);a.setMinutes(0);a.setSeconds(0);a.setMilliseconds(0);return a}var d=b.delegate,b=b.date.locale,i=b._getGregorianBundle,e=b.format;dojox.date.relative.format=function(a,c){var c=c||{},b=h(c.relativeDate||new Date),g=b.getTime()-h(a).getTime(),f={locale:c.locale};return g===
0?e(a,d(f,{selector:"time"})):g<=5184E5&&g>0&&c.weekCheck!==!1?e(a,d(f,{selector:"date",datePattern:"EEE"}))+" "+e(a,d(f,{selector:"time",formatLength:"short"})):a.getFullYear()==b.getFullYear()?(b=i(dojo.i18n.normalizeLocale(c.locale)),e(a,d(f,{selector:"date",datePattern:b["dateFormatItem-MMMd"]}))):e(a,d(f,{selector:"date",formatLength:"medium",locale:c.locale}))}}(dojo));