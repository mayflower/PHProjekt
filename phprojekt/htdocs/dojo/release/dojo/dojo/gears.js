/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojo.gears"])dojo._hasResource["dojo.gears"]=!0,dojo.provide("dojo.gears"),dojo.getObject("gears",!0,dojo),dojo.gears._gearsObject=function(){var a,b=dojo.getObject("google.gears");if(b)return b;if(typeof GearsFactory!="undefined")a=new GearsFactory;else if(dojo.isIE)try{a=new ActiveXObject("Gears.Factory")}catch(c){}else if(navigator.mimeTypes["application/x-googlegears"])a=document.createElement("object"),a.setAttribute("type","application/x-googlegears"),a.setAttribute("width",
0),a.setAttribute("height",0),a.style.display="none",document.documentElement.appendChild(a);if(!a)return null;dojo.setObject("google.gears.factory",a);return dojo.getObject("google.gears")},dojo.gears.available=!!dojo.gears._gearsObject()||0;