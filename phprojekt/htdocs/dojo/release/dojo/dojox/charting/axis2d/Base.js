/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.charting.axis2d.Base"]||(dojo._hasResource["dojox.charting.axis2d.Base"]=!0,dojo.provide("dojox.charting.axis2d.Base"),dojo.require("dojox.charting.Element"),dojo.declare("dojox.charting.axis2d.Base",dojox.charting.Element,{constructor:function(b,a){this.vertical=a&&a.vertical},clear:function(){return this},initialized:function(){return!1},calculate:function(){return this},getScaler:function(){return null},getTicks:function(){return null},getOffsets:function(){return{l:0,
r:0,t:0,b:0}},render:function(){this.dirty=!1;return this}}));