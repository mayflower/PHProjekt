/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.lang.oo.Decorator"]||(dojo._hasResource["dojox.lang.oo.Decorator"]=!0,dojo.provide("dojox.lang.oo.Decorator"),function(){var c=dojox.lang.oo,d=c.Decorator=function(b,a){this.value=b;this.decorator=typeof a=="object"?function(){return a.exec.apply(a,arguments)}:a};c.makeDecorator=function(b){return function(a){return new d(a,b)}}}());