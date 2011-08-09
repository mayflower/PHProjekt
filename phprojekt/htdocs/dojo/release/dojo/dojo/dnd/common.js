/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojo.dnd.common"])dojo._hasResource["dojo.dnd.common"]=!0,dojo.provide("dojo.dnd.common"),dojo.getObject("dnd",!0,dojo),dojo.dnd.getCopyKeyState=dojo.isCopyKey,dojo.dnd._uniqueId=0,dojo.dnd.getUniqueId=function(){var a;do a=dojo._scopeName+"Unique"+ ++dojo.dnd._uniqueId;while(dojo.byId(a));return a},dojo.dnd._empty={},dojo.dnd.isFormElement=function(a){a=a.target;if(a.nodeType==3)a=a.parentNode;return" button textarea input select option ".indexOf(" "+a.tagName.toLowerCase()+
" ")>=0};