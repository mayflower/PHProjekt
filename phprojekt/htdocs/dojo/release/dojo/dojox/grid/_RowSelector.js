/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.grid._RowSelector"]||(dojo._hasResource["dojox.grid._RowSelector"]=!0,dojo.provide("dojox.grid._RowSelector"),dojo.require("dojox.grid._View"),dojo.declare("dojox.grid._RowSelector",dojox.grid._View,{defaultWidth:"2em",noscroll:!0,padBorderWidth:2,buildRendering:function(){this.inherited("buildRendering",arguments);this.scrollboxNode.style.overflow="hidden";this.headerNode.style.visibility="hidden"},getWidth:function(){return this.viewWidth||this.defaultWidth},buildRowContent:function(a,
b){b.innerHTML='<table class="dojoxGridRowbarTable" style="width:'+(this.contentWidth||0)+'px;height:1px;" border="0" cellspacing="0" cellpadding="0" role="presentation"><tr><td class="dojoxGridRowbarInner">&nbsp;</td></tr></table>'},renderHeader:function(){},updateRow:function(){},resize:function(){this.adaptHeight()},adaptWidth:function(){if(!("contentWidth"in this)&&this.contentNode)this.contentWidth=this.contentNode.offsetWidth-this.padBorderWidth},doStyleRowNode:function(a,b){var c=["dojoxGridRowbar dojoxGridNonNormalizedCell"];
this.grid.rows.isOver(a)&&c.push("dojoxGridRowbarOver");this.grid.selection.isSelected(a)&&c.push("dojoxGridRowbarSelected");b.className=c.join(" ")},domouseover:function(a){this.grid.onMouseOverRow(a)},domouseout:function(a){if(!this.isIntraRowEvent(a))this.grid.onMouseOutRow(a)}}));