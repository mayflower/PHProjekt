/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.highlight.languages.xml"]||(dojo._hasResource["dojox.highlight.languages.xml"]=!0,dojo.provide("dojox.highlight.languages.xml"),dojo.require("dojox.highlight._base"),function(){var a={className:"comment",begin:"<\!--",end:"--\>"},b={className:"attribute",begin:" [a-zA-Z-]+=",end:"^",contains:["value"]},c={className:"value",begin:'"',end:'"'};dojox.highlight.languages.xml={defaultMode:{contains:["pi","comment","cdata","tag"]},case_insensitive:!0,modes:[{className:"pi",begin:"<\\?",
end:"\\?>",relevance:10},a,{className:"cdata",begin:"<\\!\\[CDATA\\[",end:"\\]\\]>"},{className:"tag",begin:"</?",end:">",contains:["title","tag_internal"],relevance:1.5},{className:"title",begin:"[A-Za-z:_][A-Za-z0-9\\._:-]+",end:"^",relevance:0},{className:"tag_internal",begin:"^",endsWithParent:!0,contains:["attribute"],relevance:0,illegal:"[\\+\\.]"},b,c],XML_COMMENT:a,XML_ATTR:b,XML_VALUE:c}}());