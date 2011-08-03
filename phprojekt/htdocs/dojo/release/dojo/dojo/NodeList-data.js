/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojo.NodeList-data"]||(dojo._hasResource["dojo.NodeList-data"]=!0,dojo.provide("dojo.NodeList-data"),function(a){var c={},i=0,g=a.NodeList,h=function(d){var b=a.attr(d,"data-dojo-dataid");b||(b="pid"+i++,a.attr(d,"data-dojo-dataid",b));return b},k=a._nodeData=function(d,b,j){var e=h(d),f;c[e]||(c[e]={});arguments.length==1&&(f=c[e]);typeof b=="string"?arguments.length>2?c[e][b]=j:f=c[e][b]:f=a._mixin(c[e],b);return f},l=a._removeNodeData=function(d,b){var a=h(d);c[a]&&(b?delete c[a][b]:
delete c[a])};a._gcNodeData=function(){var a=dojo.query("[data-dojo-dataid]").map(h),b;for(b in c)dojo.indexOf(a,b)<0&&delete c[b]};a.extend(g,{data:g._adaptWithCondition(k,function(a){return a.length===0||a.length==1&&typeof a[0]=="string"}),removeData:g._adaptAsForEach(l)})}(dojo));