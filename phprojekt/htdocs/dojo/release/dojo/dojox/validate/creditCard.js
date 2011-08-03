/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.validate.creditCard"])dojo._hasResource["dojox.validate.creditCard"]=!0,dojo.provide("dojox.validate.creditCard"),dojo.require("dojox.validate._base"),dojox.validate._cardInfo={mc:"5[1-5][0-9]{14}",ec:"5[1-5][0-9]{14}",vi:"4(?:[0-9]{12}|[0-9]{15})",ax:"3[47][0-9]{13}",dc:"3(?:0[0-5][0-9]{11}|[68][0-9]{12})",bl:"3(?:0[0-5][0-9]{11}|[68][0-9]{12})",di:"6011[0-9]{12}",jcb:"(?:3[0-9]{15}|(2131|1800)[0-9]{11})",er:"2(?:014|149)[0-9]{11}"},dojox.validate.isValidCreditCard=function(a,
c){return(c.toLowerCase()=="er"||dojox.validate.isValidLuhn(a))&&dojox.validate.isValidCreditCardNumber(a,c.toLowerCase())},dojox.validate.isValidCreditCardNumber=function(a,c){var a=String(a).replace(/[- ]/g,""),b=dojox.validate._cardInfo,d=[];if(c)return(b="^"+b[c.toLowerCase()]+"$")?!!a.match(b):!1;for(var e in b)a.match("^"+b[e]+"$")&&d.push(e);return d.length?d.join("|"):!1},dojox.validate.isValidCvv=function(a,c){dojo.isString(a)||(a=String(a));var b;switch(c.toLowerCase()){case "mc":case "ec":case "vi":case "di":b=
"###";break;case "ax":b="####"}return!!b&&a.length&&dojox.validate.isNumberFormat(a,{format:b})};