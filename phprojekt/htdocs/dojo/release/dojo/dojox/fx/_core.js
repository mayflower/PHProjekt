/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.fx._core"])dojo._hasResource["dojox.fx._core"]=!0,dojo.provide("dojox.fx._core"),dojox.fx._Line=function(a,b){this.start=a;this.end=b;var f=dojo.isArray(a),d=f?[]:b-a;f?(dojo.forEach(this.start,function(e,c){d[c]=this.end[c]-e},this),this.getValue=function(e){var c=[];dojo.forEach(this.start,function(a,b){c[b]=d[b]*e+a},this);return c}):this.getValue=function(a){return d*a+this.start}};