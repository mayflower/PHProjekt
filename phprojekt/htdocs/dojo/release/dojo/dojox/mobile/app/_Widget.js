/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.mobile.app._Widget"]||(dojo._hasResource["dojox.mobile.app._Widget"]=!0,dojo.provide("dojox.mobile.app._Widget"),dojo.experimental("dojox.mobile.app._Widget"),dojo.require("dijit._WidgetBase"),dojo.declare("dojox.mobile.app._Widget",dijit._WidgetBase,{getScroll:function(){return{x:dojo.global.scrollX,y:dojo.global.scrollY}},connect:function(b,a,c){return(a.toLowerCase()=="dblclick"||a.toLowerCase()=="ondblclick")&&dojo.global.Mojo?this.connect(b,Mojo.Event.tap,c):this.inherited(arguments)}}));