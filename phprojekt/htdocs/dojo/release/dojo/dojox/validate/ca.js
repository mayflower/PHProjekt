/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.validate.ca"]||(dojo._hasResource["dojox.validate.ca"]=!0,dojo.provide("dojox.validate.ca"),dojo.require("dojox.validate._base"),dojo.mixin(dojox.validate.ca,{isPhoneNumber:function(a){return dojox.validate.us.isPhoneNumber(a)},isProvince:function(a){return RegExp("^"+dojox.validate.regexp.ca.province()+"$","i").test(a)},isSocialInsuranceNumber:function(a){return dojox.validate.isNumberFormat(a,{format:["###-###-###","### ### ###","#########"]})},isPostalCode:function(a){return RegExp("^"+
dojox.validate.regexp.ca.postalCode()+"$","i").test(a)}}));