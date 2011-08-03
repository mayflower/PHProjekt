/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.grid.enhanced.plugins.exporter.CSVWriter"]||(dojo._hasResource["dojox.grid.enhanced.plugins.exporter.CSVWriter"]=!0,dojo.provide("dojox.grid.enhanced.plugins.exporter.CSVWriter"),dojo.require("dojox.grid.enhanced.plugins.exporter._ExportWriter"),dojox.grid.enhanced.plugins.Exporter.registerWriter("csv","dojox.grid.enhanced.plugins.exporter.CSVWriter"),dojo.declare("dojox.grid.enhanced.plugins.exporter.CSVWriter",dojox.grid.enhanced.plugins.exporter._ExportWriter,{_separator:",",
_newline:"\r\n",constructor:function(a){if(a)this._separator=a.separator?a.separator:this._separator,this._newline=a.newline?a.newline:this._newline;this._headers=[];this._dataRows=[]},_formatCSVCell:function(a){if(a===null||a===void 0)return"";a=String(a).replace(/"/g,'""');if(a.indexOf(this._separator)>=0||a.search(/[" \t\r\n]/)>=0)a='"'+a+'"';return a},beforeContentRow:function(a){var b=[],d=this._formatCSVCell;dojo.forEach(a.grid.layout.cells,function(c){!c.hidden&&dojo.indexOf(a.spCols,c.index)<
0&&b.push(d(this._getExportDataForCell(a.rowIndex,a.row,c,a.grid)))},this);this._dataRows.push(b);return!1},handleCell:function(a){var b=a.cell;a.isHeader&&!b.hidden&&dojo.indexOf(a.spCols,b.index)<0&&this._headers.push(b.name||b.field)},toString:function(){for(var a=this._headers.join(this._separator),b=this._dataRows.length-1;b>=0;--b)this._dataRows[b]=this._dataRows[b].join(this._separator);return a+this._newline+this._dataRows.join(this._newline)}}));