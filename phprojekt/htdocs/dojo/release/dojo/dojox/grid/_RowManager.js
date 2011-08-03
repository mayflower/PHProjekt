/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.grid._RowManager"]||(dojo._hasResource["dojox.grid._RowManager"]=!0,dojo.provide("dojox.grid._RowManager"),function(){dojo.declare("dojox.grid._RowManager",null,{constructor:function(a){this.grid=a},linesToEms:2,overRow:-2,prepareStylingRow:function(a,b){return{index:a,node:b,odd:Boolean(a&1),selected:!!this.grid.selection.isSelected(a),over:this.isOver(a),customStyles:"",customClasses:"dojoxGridRow"}},styleRowNode:function(a,b){var c=this.prepareStylingRow(a,b);this.grid.onStyleRow(c);
this.applyStyles(c)},applyStyles:function(a){a.node.className=a.customClasses;var b=a.node.style.height,c=a.node,d=a.customStyles+";"+(a.node._style||"");c.style.cssText==void 0?c.setAttribute("style",d):c.style.cssText=d;a.node.style.height=b},updateStyles:function(a){this.grid.updateRowStyles(a)},setOverRow:function(a){var b=this.overRow;this.overRow=a;b!=this.overRow&&(dojo.isString(b)||b>=0)&&this.updateStyles(b);this.updateStyles(this.overRow)},isOver:function(a){return this.overRow==a&&!dojo.hasClass(this.grid.domNode,
"dojoxGridColumnResizing")}})}());