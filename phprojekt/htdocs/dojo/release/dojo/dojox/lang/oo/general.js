/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.lang.oo.general"]||(dojo._hasResource["dojox.lang.oo.general"]=!0,dojo.provide("dojox.lang.oo.general"),dojo.require("dojox.lang.oo.Decorator"),function(){var a=dojox.lang.oo,d=a.makeDecorator,a=a.general,e=dojo.isFunction;a.augment=d(function(a,c,b){return typeof b=="undefined"?c:b});a.override=d(function(a,c,b){return typeof b!="undefined"?c:b});a.shuffle=d(function(a,c,b){return e(b)?function(){return b.apply(this,c.apply(this,arguments))}:b});a.wrap=d(function(a,c,b){return function(){return c.call(this,
b,arguments)}});a.tap=d(function(a,c){return function(){c.apply(this,arguments);return this}});a.before=d(function(a,c,b){return e(b)?function(){c.apply(this,arguments);return b.apply(this,arguments)}:c});a.after=d(function(a,c,b){return e(b)?function(){b.apply(this,arguments);return c.apply(this,arguments)}:c})}());