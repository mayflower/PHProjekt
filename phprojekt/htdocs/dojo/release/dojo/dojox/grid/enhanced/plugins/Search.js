/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.grid.enhanced.plugins.Search"]||(dojo._hasResource["dojox.grid.enhanced.plugins.Search"]=!0,dojo.provide("dojox.grid.enhanced.plugins.Search"),dojo.require("dojox.grid.enhanced._Plugin"),dojo.require("dojo.data.util.filter"),dojo.declare("dojox.grid.enhanced.plugins.Search",dojox.grid.enhanced._Plugin,{name:"search",constructor:function(a,b){this.grid=a;b=b&&dojo.isObject(b)?b:{};this._cacheSize=b.cacheSize||-1;a.searchRow=dojo.hitch(this,"searchRow")},searchRow:function(a,
b){if(dojo.isFunction(b)){dojo.isString(a)&&(a=dojo.data.util.filter.patternToRegExp(a));var d=!1;if(a instanceof RegExp)d=!0;else if(dojo.isObject(a)){var e=!0,f;for(f in a)dojo.isString(a[f])&&(a[f]=dojo.data.util.filter.patternToRegExp(a[f])),e=!1;if(e)return}else return;this._search(a,0,b,d)}},_search:function(a,b,d,e){var f=this,c=this._cacheSize,h={start:b,onBegin:function(a){f._storeSize=a},onComplete:function(g){dojo.some(g,function(c,g){return f._checkRow(c,a,e)?(d(b+g,c),!0):!1})||(c>0&&
b+c<f._storeSize?f._search(a,b+c,d,e):d(-1,null))}};if(c>0)h.count=c;this.grid._storeLayerFetch(h)},_checkRow:function(a,b,d){var e=this.grid,f=e.store,c,e=dojo.filter(e.layout.cells,function(a){return!a.hidden});if(d)return dojo.some(e,function(c){try{if(c.field)return String(f.getValue(a,c.field)).search(b)>=0}catch(d){console.log("Search._checkRow() error: ",d)}return!1});else{for(c in b)if(b[c]instanceof RegExp){for(d=e.length-1;d>=0;--d)if(e[d].field==c)try{if(String(f.getValue(a,c)).search(b[c])<
0)return!1;break}catch(h){return!1}if(d<0)return!1}return!0}}}),dojox.grid.EnhancedGrid.registerPlugin(dojox.grid.enhanced.plugins.Search));