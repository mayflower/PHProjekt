/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dijit.form.MultiSelect"]||(dojo._hasResource["dijit.form.MultiSelect"]=!0,dojo.provide("dijit.form.MultiSelect"),dojo.require("dijit.form._FormWidget"),dojo.declare("dijit.form.MultiSelect",dijit.form._FormValueWidget,{size:7,templateString:"<select multiple='true' ${!nameAttrSetting} dojoAttachPoint='containerNode,focusNode' dojoAttachEvent='onchange: _onChange'></select>",attributeMap:dojo.delegate(dijit.form._FormWidget.prototype.attributeMap,{size:"focusNode"}),reset:function(){this._hasBeenBlurred=
!1;this._setValueAttr(this._resetValue,!0)},addSelected:function(a){a.getSelected().forEach(function(b){this.containerNode.appendChild(b);this.domNode.scrollTop=this.domNode.offsetHeight;b=a.domNode.scrollTop;a.domNode.scrollTop=0;a.domNode.scrollTop=b},this)},getSelected:function(){return dojo.query("option",this.containerNode).filter(function(a){return a.selected})},_getValueAttr:function(){return this.getSelected().map(function(a){return a.value})},multiple:!0,_setValueAttr:function(a){dojo.query("option",
this.containerNode).forEach(function(b){b.selected=dojo.indexOf(a,b.value)!=-1})},invertSelection:function(a){dojo.query("option",this.containerNode).forEach(function(a){a.selected=!a.selected});this._handleOnChange(this.get("value"),a==!0)},_onChange:function(){this._handleOnChange(this.get("value"),!0)},resize:function(a){a&&dojo.marginBox(this.domNode,a)},postCreate:function(){this._onChange()}}));