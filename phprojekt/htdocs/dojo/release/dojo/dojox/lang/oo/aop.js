/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.lang.oo.aop"]||(dojo._hasResource["dojox.lang.oo.aop"]=!0,dojo.provide("dojox.lang.oo.aop"),dojo.require("dojox.lang.oo.Decorator"),dojo.require("dojox.lang.oo.general"),function(){var a=dojox.lang.oo,d=a.makeDecorator,f=a.general,a=a.aop,e=dojo.isFunction;a.before=f.before;a.around=f.wrap;a.afterReturning=d(function(a,c,b){return e(b)?function(){var a=b.apply(this,arguments);c.call(this,a);return a}:function(){c.call(this)}});a.afterThrowing=d(function(a,c,b){return e(b)?
function(){var a;try{a=b.apply(this,arguments)}catch(d){throw c.call(this,d),d;}return a}:b});a.after=d(function(a,c,b){return e(b)?function(){var a;try{a=b.apply(this,arguments)}finally{c.call(this)}return a}:function(){c.call(this)}})}());