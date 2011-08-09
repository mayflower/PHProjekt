/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.dtl.filter.htmlstrings"]||(dojo._hasResource["dojox.dtl.filter.htmlstrings"]=!0,dojo.provide("dojox.dtl.filter.htmlstrings"),dojo.require("dojox.dtl._base"),dojo.mixin(dojox.dtl.filter.htmlstrings,{_linebreaksrn:/(\r\n|\n\r)/g,_linebreaksn:/\n{2,}/g,_linebreakss:/(^\s+|\s+$)/g,_linebreaksbr:/\n/g,_removetagsfind:/[a-z0-9]+/g,_striptags:/<[^>]*?>/g,linebreaks:function(a){for(var c=[],d=dojox.dtl.filter.htmlstrings,a=a.replace(d._linebreaksrn,"\n"),a=a.split(d._linebreaksn),
b=0;b<a.length;b++){var e=a[b].replace(d._linebreakss,"").replace(d._linebreaksbr,"<br />");c.push("<p>"+e+"</p>")}return c.join("\n\n")},linebreaksbr:function(a){var c=dojox.dtl.filter.htmlstrings;return a.replace(c._linebreaksrn,"\n").replace(c._linebreaksbr,"<br />")},removetags:function(a,c){for(var d=dojox.dtl.filter.htmlstrings,b=[],e;e=d._removetagsfind.exec(c);)b.push(e[0]);b="("+b.join("|")+")";return a.replace(RegExp("</?s*"+b+"s*[^>]*>","gi"),"")},striptags:function(a){return a.replace(dojox.dtl.filter.htmlstrings._striptags,
"")}}));