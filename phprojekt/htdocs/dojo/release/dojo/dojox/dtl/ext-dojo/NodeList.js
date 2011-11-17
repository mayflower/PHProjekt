/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.dtl.ext-dojo.NodeList"]||(dojo._hasResource["dojox.dtl.ext-dojo.NodeList"]=!0,dojo.provide("dojox.dtl.ext-dojo.NodeList"),dojo.require("dojox.dtl._base"),dojo.extend(dojo.NodeList,{dtl:function(a,c){var b=dojox.dtl,d=this,f=function(a,e){var c=a.render(new b._Context(e));d.forEach(function(a){a.innerHTML=c})};b.text._resolveTemplateArg(a).addCallback(function(d){a=new b.Template(d);b.text._resolveContextArg(c).addCallback(function(b){f(a,b)})});return this}}));