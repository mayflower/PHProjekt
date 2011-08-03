/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.validate.isbn"])dojo._hasResource["dojox.validate.isbn"]=!0,dojo.provide("dojox.validate.isbn"),dojox.validate.isValidIsbn=function(a){var e,d=0,c;dojo.isString(a)||(a=String(a));a=a.replace(/[- ]/g,"");e=a.length;switch(e){case 10:c=e;for(var b=0;b<9;b++)d+=parseInt(a.charAt(b))*c,c--;a=a.charAt(9).toUpperCase();d+=a=="X"?10:parseInt(a);return d%11==0;case 13:c=-1;for(b=0;b<e;b++)d+=parseInt(a.charAt(b))*(2+c),c*=-1;return d%10==0}return!1};