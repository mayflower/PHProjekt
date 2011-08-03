/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.form.manager._EnableMixin"]||(dojo._hasResource["dojox.form.manager._EnableMixin"]=!0,dojo.provide("dojox.form.manager._EnableMixin"),dojo.require("dojox.form.manager._Mixin"),function(){var d=dojox.form.manager,e=d.actionAdapter,f=d.inspectorAdapter;dojo.declare("dojox.form.manager._EnableMixin",null,{gatherEnableState:function(a){var b=this.inspectFormWidgets(f(function(b,a){return!a.get("disabled")}),a);this.inspectFormNodes&&dojo.mixin(b,this.inspectFormNodes(f(function(b,
a){return!dojo.attr(a,"disabled")}),a));return b},enable:function(a,b){if(arguments.length<2||b===void 0)b=!0;this.inspectFormWidgets(e(function(a,b,c){b.set("disabled",!c)}),a,b);this.inspectFormNodes&&this.inspectFormNodes(e(function(b,a,c){dojo.attr(a,"disabled",!c)}),a,b);return this},disable:function(a){var b=this.gatherEnableState();this.enable(a,!1);return b}})}());