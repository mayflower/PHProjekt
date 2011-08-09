/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.math.random.Simple"]||(dojo._hasResource["dojox.math.random.Simple"]=!0,dojo.provide("dojox.math.random.Simple"),dojo.declare("dojox.math.random.Simple",null,{destroy:function(){},nextBytes:function(b){for(var a=0,c=b.length;a<c;++a)b[a]=Math.floor(256*Math.random())}}));