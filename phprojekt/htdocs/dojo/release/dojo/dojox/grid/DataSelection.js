/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.grid.DataSelection"]||(dojo._hasResource["dojox.grid.DataSelection"]=!0,dojo.provide("dojox.grid.DataSelection"),dojo.require("dojox.grid.Selection"),dojo.declare("dojox.grid.DataSelection",dojox.grid.Selection,{getFirstSelected:function(){var a=dojox.grid.Selection.prototype.getFirstSelected.call(this);return a==-1?null:this.grid.getItem(a)},getNextSelected:function(a){a=this.grid.getItemIndex(a);a=dojox.grid.Selection.prototype.getNextSelected.call(this,a);return a==-1?
null:this.grid.getItem(a)},getSelected:function(){for(var a=[],b=0,c=this.selected.length;b<c;b++)this.selected[b]&&a.push(this.grid.getItem(b));return a},addToSelection:function(a){if(this.mode!="none"){var b=null,b=typeof a=="number"||typeof a=="string"?a:this.grid.getItemIndex(a);dojox.grid.Selection.prototype.addToSelection.call(this,b)}},deselect:function(a){if(this.mode!="none"){var b=null,b=typeof a=="number"||typeof a=="string"?a:this.grid.getItemIndex(a);dojox.grid.Selection.prototype.deselect.call(this,
b)}},deselectAll:function(a){var b=null;a||typeof a=="number"?(b=typeof a=="number"||typeof a=="string"?a:this.grid.getItemIndex(a),dojox.grid.Selection.prototype.deselectAll.call(this,b)):this.inherited(arguments)}}));