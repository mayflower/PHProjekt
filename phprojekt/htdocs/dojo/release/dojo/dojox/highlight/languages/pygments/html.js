/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.highlight.languages.pygments.html"]||(dojo._hasResource["dojox.highlight.languages.pygments.html"]=!0,dojo.provide("dojox.highlight.languages.pygments.html"),dojo.require("dojox.highlight._base"),dojo.require("dojox.highlight.languages.pygments._html"),function(){var b=dojox.highlight.languages,a=[],d=b.pygments._html.tags,c;for(c in d)a.push(c);a="\\b("+a.join("|")+")\\b";b.html={case_insensitive:!0,defaultMode:{contains:["name entity","comment","comment preproc","_script",
"_style","_tag"]},modes:[{className:"comment",begin:"<\!--",end:"--\>"},{className:"comment preproc",begin:"\\<\\!\\[CDATA\\[",end:"\\]\\]\\>"},{className:"comment preproc",begin:"\\<\\!",end:"\\>"},{className:"string",begin:"'",end:"'",illegal:"\\n",relevance:0},{className:"string",begin:'"',end:'"',illegal:"\\n",relevance:0},{className:"name entity",begin:"\\&[a-z]+;",end:"^"},{className:"name tag",begin:a,end:"^",relevance:5},{className:"name attribute",begin:"\\b[a-z0-9_\\:\\-]+\\s*=",end:"^",
relevance:0},{className:"_script",begin:"\\<script\\b",end:"\\<\/script\\>",relevance:5},{className:"_style",begin:"\\<style\\b",end:"\\</style\\>",relevance:5},{className:"_tag",begin:"\\<(?!/)",end:"\\>",contains:["name tag","name attribute","string","_value"]},{className:"_tag",begin:"\\</",end:"\\>",contains:["name tag"]},{className:"_value",begin:"[^\\s\\>]+",end:"^"}]}}());