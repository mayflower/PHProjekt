/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.dtl.filter.logic"]||(dojo._hasResource["dojox.dtl.filter.logic"]=!0,dojo.provide("dojox.dtl.filter.logic"),dojo.mixin(dojox.dtl.filter.logic,{default_:function(a,b){return a||b||""},default_if_none:function(a,b){return a===null?b||"":a||""},divisibleby:function(a,b){return parseInt(a,10)%parseInt(b,10)===0},_yesno:/\s*,\s*/g,yesno:function(a,b){b||(b="yes,no,maybe");var c=b.split(dojox.dtl.filter.logic._yesno);return c.length<2?a:a?c[0]:!a&&a!==null||c.length<3?c[1]:c[2]}}));