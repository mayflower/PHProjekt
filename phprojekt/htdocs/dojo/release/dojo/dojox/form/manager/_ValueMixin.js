/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.form.manager._ValueMixin"]||(dojo._hasResource["dojox.form.manager._ValueMixin"]=!0,dojo.provide("dojox.form.manager._ValueMixin"),dojo.declare("dojox.form.manager._ValueMixin",null,{elementValue:function(a,b){return a in this.formWidgets?this.formWidgetValue(a,b):this.formNodes&&a in this.formNodes?this.formNodeValue(a,b):this.formPointValue(a,b)},gatherFormValues:function(a){var b=this.inspectFormWidgets(function(a){return this.formWidgetValue(a)},a);this.inspectFormNodes&&
dojo.mixin(b,this.inspectFormNodes(function(a){return this.formNodeValue(a)},a));dojo.mixin(b,this.inspectAttachedPoints(function(a){return this.formPointValue(a)},a));return b},setFormValues:function(a){a&&(this.inspectFormWidgets(function(a,d,c){this.formWidgetValue(a,c)},a),this.inspectFormNodes&&this.inspectFormNodes(function(a,d,c){this.formNodeValue(a,c)},a),this.inspectAttachedPoints(function(a,d,c){this.formPointValue(a,c)},a));return this}}));