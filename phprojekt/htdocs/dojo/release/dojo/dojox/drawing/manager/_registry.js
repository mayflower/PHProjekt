/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.drawing.manager._registry"]||(dojo._hasResource["dojox.drawing.manager._registry"]=!0,dojo.provide("dojox.drawing.manager._registry"),function(){var c={tool:{},stencil:{},drawing:{},plugin:{},button:{}};dojox.drawing.register=function(a,b){b=="drawing"?c.drawing[a.id]=a:b=="tool"?c.tool[a.name]=a:b=="stencil"?c.stencil[a.name]=a:b=="plugin"?c.plugin[a.name]=a:b=="button"&&(c.button[a.toolType]=a)};dojox.drawing.getRegistered=function(a,b){return b?c[a][b]:c[a]}}());