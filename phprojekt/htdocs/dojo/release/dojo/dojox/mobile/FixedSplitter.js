/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.mobile.FixedSplitter"]||(dojo._hasResource["dojox.mobile.FixedSplitter"]=!0,dojo.provide("dojox.mobile.FixedSplitter"),dojo.require("dijit._WidgetBase"),dojo.declare("dojox.mobile.FixedSplitter",dijit._WidgetBase,{orientation:"H",isContainer:!0,buildRendering:function(){this.domNode=this.containerNode=this.srcNodeRef?this.srcNodeRef:dojo.doc.createElement("DIV");dojo.addClass(this.domNode,"mblFixedSpliter")},startup:function(){var d=dojo.filter(this.domNode.childNodes,function(a){return a.nodeType==
1});dojo.forEach(d,function(a){dojo.addClass(a,"mblFixedSplitterPane"+this.orientation)},this);dojo.forEach(this.getChildren(),function(a){a.startup&&a.startup()});this._started=!0;var b=this;setTimeout(function(){b.resize()},0);dijit.getEnclosingWidget(this.domNode.parentNode)||(dojo.global.onorientationchange!==void 0?this.connect(dojo.global,"onorientationchange","resize"):this.connect(dojo.global,"onresize","resize"))},resize:function(){this.layout()},layout:function(){for(var d=this.orientation==
"H"?"w":"h",b=dojo.filter(this.domNode.childNodes,function(a){return a.nodeType==1}),a=0,c=0;c<b.length;c++)dojo.marginBox(b[c],this.orientation=="H"?{l:a}:{t:a}),c<b.length-1&&(a+=dojo.marginBox(b[c])[d]);a=dojo.marginBox(this.domNode)[d]-a;c={};c[d]=a;dojo.marginBox(b[b.length-1],c);dojo.forEach(this.getChildren(),function(a){a.resize&&a.resize()})}}),dojo.declare("dojox.mobile.FixedSplitterPane",dijit._WidgetBase,{buildRendering:function(){this.inherited(arguments);dojo.addClass(this.domNode,"mblFixedSplitterPane")}}));