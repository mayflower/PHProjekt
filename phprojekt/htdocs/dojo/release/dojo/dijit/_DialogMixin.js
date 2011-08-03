/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dijit._DialogMixin"]||(dojo._hasResource["dijit._DialogMixin"]=!0,dojo.provide("dijit._DialogMixin"),dojo.require("dijit._Widget"),dojo.declare("dijit._DialogMixin",null,{attributeMap:dijit._Widget.prototype.attributeMap,execute:function(){},onCancel:function(){},onExecute:function(){},_onSubmit:function(){this.onExecute();this.execute(this.get("value"))},_getFocusItems:function(){var a=dijit._getTabNavigable(this.containerNode);this._firstFocusItem=a.lowest||a.first||this.closeButtonNode||
this.domNode;this._lastFocusItem=a.last||a.highest||this._firstFocusItem}}));