/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.geo.charting.widget.Legend"]||(dojo._hasResource["dojox.geo.charting.widget.Legend"]=!0,dojo.provide("dojox.geo.charting.widget.Legend"),dojo.require("dijit._Widget"),dojo.require("dijit._Templated"),dojo.require("dojox.lang.functional.array"),dojo.require("dojox.lang.functional.fold"),dojo.declare("dojox.geo.charting.widget.Legend",[dijit._Widget,dijit._Templated],{templateString:"<table dojoAttachPoint='legendNode' class='dojoxLegendNode'><tbody dojoAttachPoint='legendBody'></tbody></table>",
horizontal:!0,legendNode:null,legendBody:null,swatchSize:18,postCreate:function(){if(this.map)this.series=this.map.series,dojo.byId(this.map.container).appendChild(this.domNode),this.refresh()},refresh:function(){for(;this.legendBody.lastChild;)dojo.destroy(this.legendBody.lastChild);if(this.horizontal)dojo.addClass(this.legendNode,"dojoxLegendHorizontal"),this._tr=dojo.doc.createElement("tr"),this.legendBody.appendChild(this._tr);var a=this.series;a.length!=0&&dojo.forEach(a,function(a){this._addLabel(a.color,
a.name)},this)},_addLabel:function(a,f){var b=dojo.doc.createElement("td"),c=dojo.doc.createElement("td"),d=dojo.doc.createElement("div");dojo.addClass(b,"dojoxLegendIcon");dojo.addClass(c,"dojoxLegendText");d.style.width=this.swatchSize+"px";d.style.height=this.swatchSize+"px";b.appendChild(d);if(this.horizontal)this._tr.appendChild(b),this._tr.appendChild(c);else{var e=dojo.doc.createElement("tr");this.legendBody.appendChild(e);e.appendChild(b);e.appendChild(c)}d.style.background=a;c.innerHTML=
String(f)}}));