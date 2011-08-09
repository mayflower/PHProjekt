/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.editor.plugins._SpellCheckParser"]||(dojo._hasResource["dojox.editor.plugins._SpellCheckParser"]=!0,dojo.provide("dojox.editor.plugins._SpellCheckParser"),dojo.declare("dojox.editor.plugins._SpellCheckParser",null,{lang:"english",parseIntoWords:function(b){function f(a){a=a.charCodeAt(0);return 48<=a&&a<=57||65<=a&&a<=90||97<=a&&a<=122}for(var g=this.words=[],h=this.indices=[],a=0,c=b&&b.length,e=0;a<c;){for(var d;a<c&&!f(d=b.charAt(a))&&d!="&";)a++;if(d=="&")for(;++a<c&&
(d=b.charAt(a))!=";"&&f(d););else{for(e=a;++a<c&&f(b.charAt(a)););e<c&&(g.push(b.substring(e,a)),h.push(e))}}return g},getIndices:function(){return this.indices}}),dojo.subscribe(dijit._scopeName+".Editor.plugin.SpellCheck.getParser",null,function(b){if(!b.parser)b.parser=new dojox.editor.plugins._SpellCheckParser}));