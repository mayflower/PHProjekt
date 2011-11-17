/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojo.data.util.simpleFetch"])dojo._hasResource["dojo.data.util.simpleFetch"]=!0,dojo.provide("dojo.data.util.simpleFetch"),dojo.require("dojo.data.util.sorter"),dojo.getObject("data.util.simpleFetch",!0,dojo),dojo.data.util.simpleFetch.fetch=function(b){b=b||{};if(!b.store)b.store=this;var h=this;this._fetchItems(b,function(d,a){var b=a.abort||null,e=!1,f=a.start?a.start:0,i=a.count&&a.count!==Infinity?f+a.count:d.length;a.abort=function(){e=!0;b&&b.call(a)};var g=a.scope||
dojo.global;if(!a.store)a.store=h;a.onBegin&&a.onBegin.call(g,d.length,a);a.sort&&d.sort(dojo.data.util.sorter.createSortFunction(a.sort,h));if(a.onItem)for(var c=f;c<d.length&&c<i;++c){var j=d[c];e||a.onItem.call(g,j,a)}a.onComplete&&!e&&(c=null,a.onItem||(c=d.slice(f,i)),a.onComplete.call(g,c,a))},function(b,a){a.onError&&a.onError.call(a.scope||dojo.global,b,a)});return b};