/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.layout.ext-dijit.layout.StackContainer-touch"]||(dojo._hasResource["dojox.layout.ext-dijit.layout.StackContainer-touch"]=!0,dojo.provide("dojox.layout.ext-dijit.layout.StackContainer-touch"),dojo.experimental("dojox.layout.ext-dijit.layout.StackContainer-touch"),dojo.require("dijit.layout.StackContainer"),dojo.connect(dijit.layout.StackContainer.prototype,"postCreate",function(){this.axis=this.baseClass=="dijitAccordionContainer"?"Y":"X";dojo.forEach(["touchstart","touchmove",
"touchend","touchcancel"],function(b){this.connect(this.domNode,b,function(a){switch(a.type){case "touchmove":a.preventDefault();this.touchPosition&&(a=a.touches[0]["page"+this.axis]-this.touchPosition,Math.abs(a)>100&&(this.axis=="Y"&&(a*=-1),delete this.touchPosition,a>0?!this.selectedChildWidget.isLastChild&&this.forward():!this.selectedChildWidget.isFirstChild&&this.back()));break;case "touchstart":if(a.touches.length==1){this.touchPosition=a.touches[0]["page"+this.axis];break}case "touchend":case "touchcancel":delete this.touchPosition}})},
this)}));