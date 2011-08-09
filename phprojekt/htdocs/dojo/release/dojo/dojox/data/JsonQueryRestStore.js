/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.data.JsonQueryRestStore"]||(dojo._hasResource["dojox.data.JsonQueryRestStore"]=!0,dojo.provide("dojox.data.JsonQueryRestStore"),dojo.require("dojox.data.JsonRestStore"),dojo.require("dojox.data.util.JsonQuery"),dojo.requireIf(!!dojox.data.ClientFilter,"dojox.json.query"),dojo.declare("dojox.data.JsonQueryRestStore",[dojox.data.JsonRestStore,dojox.data.util.JsonQuery],{matchesQuery:function(a,b){return a.__id&&a.__id.indexOf("#")==-1&&this.inherited(arguments)}}));