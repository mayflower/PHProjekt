/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.lang.oo.Filter"]||(dojo._hasResource["dojox.lang.oo.Filter"]=!0,dojo.provide("dojox.lang.oo.Filter"),function(){var c=dojox.lang.oo,e=c.Filter=function(a,b){this.bag=a;this.filter=typeof b=="object"?function(){return b.exec.apply(b,arguments)}:b},d=function(a){this.map=a};d.prototype.exec=function(a){return this.map.hasOwnProperty(a)?this.map[a]:a};c.filter=function(a,b){return new e(a,new d(b))}}());