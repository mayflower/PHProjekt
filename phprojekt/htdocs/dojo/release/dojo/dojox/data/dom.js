/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.data.dom"])dojo._hasResource["dojox.data.dom"]=!0,dojo.provide("dojox.data.dom"),dojo.require("dojox.xml.parser"),dojo.deprecated("dojox.data.dom","Use dojox.xml.parser instead.","2.0"),dojox.data.dom.createDocument=function(a,b){dojo.deprecated("dojox.data.dom.createDocument()","Use dojox.xml.parser.parse() instead.","2.0");try{return dojox.xml.parser.parse(a,b)}catch(c){return null}},dojox.data.dom.textContent=function(a,b){dojo.deprecated("dojox.data.dom.textContent()",
"Use dojox.xml.parser.textContent() instead.","2.0");return arguments.length>1?dojox.xml.parser.textContent(a,b):dojox.xml.parser.textContent(a)},dojox.data.dom.replaceChildren=function(a,b){dojo.deprecated("dojox.data.dom.replaceChildren()","Use dojox.xml.parser.replaceChildren() instead.","2.0");dojox.xml.parser.replaceChildren(a,b)},dojox.data.dom.removeChildren=function(a){dojo.deprecated("dojox.data.dom.removeChildren()","Use dojox.xml.parser.removeChildren() instead.","2.0");return dojox.xml.parser.removeChildren(a)},
dojox.data.dom.innerXML=function(a){dojo.deprecated("dojox.data.dom.innerXML()","Use dojox.xml.parser.innerXML() instead.","2.0");return dojox.xml.parser.innerXML(a)};