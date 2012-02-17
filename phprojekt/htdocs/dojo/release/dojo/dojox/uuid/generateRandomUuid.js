/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.uuid.generateRandomUuid"])dojo._hasResource["dojox.uuid.generateRandomUuid"]=!0,dojo.provide("dojox.uuid.generateRandomUuid"),dojox.uuid.generateRandomUuid=function(){function a(){for(var a=Math.floor(Math.random()%1*Math.pow(2,32)).toString(e);a.length<8;)a="0"+a;return a}var e=16,d=a(),b=a(),b=b.substring(0,4)+"-4"+b.substring(5,8),c=a(),c="8"+c.substring(1,4)+"-"+c.substring(4,8),f=a();return d=(d+"-"+b+"-"+c+f).toLowerCase()};