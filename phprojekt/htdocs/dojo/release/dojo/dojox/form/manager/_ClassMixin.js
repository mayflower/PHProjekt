/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.form.manager._ClassMixin"]||(dojo._hasResource["dojox.form.manager._ClassMixin"]=!0,dojo.provide("dojox.form.manager._ClassMixin"),dojo.require("dojox.form.manager._Mixin"),function(){var d=dojox.form.manager,e=d.actionAdapter,f=d.inspectorAdapter;dojo.declare("dojox.form.manager._ClassMixin",null,{gatherClassState:function(b,a){return this.inspect(f(function(a,c){return dojo.hasClass(c,b)}),a)},addClass:function(b,a){this.inspect(e(function(a,c){dojo.addClass(c,b)}),a);return this},
removeClass:function(b,a){this.inspect(e(function(a,c){dojo.removeClass(c,b)}),a);return this}})}());