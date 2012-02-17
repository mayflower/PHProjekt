/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.xml.Script"]||(dojo._hasResource["dojox.xml.Script"]=!0,dojo.provide("dojox.xml.Script"),dojo.require("dojo.parser"),dojo.require("dojox.xml.widgetParser"),dojo.declare("dojox.xml.Script",null,{constructor:function(b,a){dojo.parser.instantiate(dojox.xml.widgetParser._processScript(a))}}));