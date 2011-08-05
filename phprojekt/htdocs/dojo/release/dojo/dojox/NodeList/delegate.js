/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.NodeList.delegate"]||(dojo._hasResource["dojox.NodeList.delegate"]=!0,dojo.provide("dojox.NodeList.delegate"),dojo.require("dojo.NodeList-traverse"),dojo.extend(dojo.NodeList,{delegate:function(c,d,e){return this.connect(d,function(a){var b=dojo.query(a.target).closest(c,this);b.length&&e.call(b[0],a)})}}));