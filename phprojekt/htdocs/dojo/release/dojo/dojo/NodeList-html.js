/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojo.NodeList-html"]||(dojo._hasResource["dojo.NodeList-html"]=!0,dojo.provide("dojo.NodeList-html"),dojo.require("dojo.html"),dojo.extend(dojo.NodeList,{html:function(c,a){var b=new dojo.html._ContentSetter(a||{});this.forEach(function(a){b.node=a;b.set(c);b.tearDown()});return this}}));