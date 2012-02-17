/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.form.manager._DisplayMixin"]||(dojo._hasResource["dojox.form.manager._DisplayMixin"]=!0,dojo.provide("dojox.form.manager._DisplayMixin"),dojo.declare("dojox.form.manager._DisplayMixin",null,{gatherDisplayState:function(a){return this.inspectAttachedPoints(function(a,c){return dojo.style(c,"display")!="none"},a)},show:function(a,b){arguments.length<2&&(b=!0);this.inspectAttachedPoints(function(a,b,d){dojo.style(b,"display",d?"":"none")},a,b);return this},hide:function(a){return this.show(a,
!1)}}));