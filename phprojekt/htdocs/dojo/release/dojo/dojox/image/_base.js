/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.image._base"]||(dojo._hasResource["dojox.image._base"]=!0,dojo.provide("dojox.image._base"),function(a){var b;dojox.image.preload=function(c){b||(b=a.create("div",{style:{position:"absolute",top:"-9999px",height:"1px",overflow:"hidden"}},a.body()));return a.map(c,function(c){return a.create("img",{src:c},b)})};a.config.preloadImages&&a.addOnLoad(function(){dojox.image.preload(a.config.preloadImages)})}(dojo));