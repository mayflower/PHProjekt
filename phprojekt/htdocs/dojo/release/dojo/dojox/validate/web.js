/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.validate.web"])dojo._hasResource["dojox.validate.web"]=!0,dojo.provide("dojox.validate.web"),dojo.require("dojox.validate._base"),dojox.validate.isIpAddress=function(b,a){return RegExp("^"+dojox.validate.regexp.ipAddress(a)+"$","i").test(b)},dojox.validate.isUrl=function(b,a){return RegExp("^"+dojox.validate.regexp.url(a)+"$","i").test(b)},dojox.validate.isEmailAddress=function(b,a){return RegExp("^"+dojox.validate.regexp.emailAddress(a)+"$","i").test(b)},dojox.validate.isEmailAddressList=
function(b,a){return RegExp("^"+dojox.validate.regexp.emailAddressList(a)+"$","i").test(b)},dojox.validate.getEmailAddressList=function(b,a){a||(a={});if(!a.listSeparator)a.listSeparator="\\s;,";return dojox.validate.isEmailAddressList(b,a)?b.split(RegExp("\\s*["+a.listSeparator+"]\\s*")):[]};