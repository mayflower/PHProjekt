/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.validate.us"])dojo._hasResource["dojox.validate.us"]=!0,dojo.provide("dojox.validate.us"),dojo.require("dojox.validate._base"),dojox.validate.us.isState=function(a,b){return RegExp("^"+dojox.validate.regexp.us.state(b)+"$","i").test(a)},dojox.validate.us.isPhoneNumber=function(a){return dojox.validate.isNumberFormat(a,{format:["###-###-####","(###) ###-####","(###) ### ####","###.###.####","###/###-####","### ### ####","###-###-#### x#???","(###) ###-#### x#???","(###) ### #### x#???",
"###.###.#### x#???","###/###-#### x#???","### ### #### x#???","##########"]})},dojox.validate.us.isSocialSecurityNumber=function(a){return dojox.validate.isNumberFormat(a,{format:["###-##-####","### ## ####","#########"]})},dojox.validate.us.isZipCode=function(a){return dojox.validate.isNumberFormat(a,{format:["#####-####","##### ####","#########","#####"]})};