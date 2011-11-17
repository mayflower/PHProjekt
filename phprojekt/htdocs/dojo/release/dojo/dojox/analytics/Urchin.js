/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.analytics.Urchin"]||(dojo._hasResource["dojox.analytics.Urchin"]=!0,dojo.provide("dojox.analytics.Urchin"),dojo.declare("dojox.analytics.Urchin",null,{acct:"",constructor:function(a){this.tracker=null;dojo.mixin(this,a);this.acct=this.acct||dojo.config.urchin;var d=/loaded|complete/,a="https:"==dojo.doc.location.protocol?"https://ssl.":"http://www.",c=dojo.doc.getElementsByTagName("head")[0],b=dojo.create("script",{src:a+"google-analytics.com/ga.js"},c);b.onload=b.onreadystatechange=
dojo.hitch(this,function(a){if(a&&a.type=="load"||d.test(b.readyState))b.onload=b.onreadystatechange=null,this._gotGA(),c.removeChild(b)})},_gotGA:function(){this.tracker=_gat._getTracker(this.acct);this.GAonLoad.apply(this,arguments)},GAonLoad:function(){this.trackPageView()},trackPageView:function(a){this.tracker._trackPageview.apply(this,arguments)}}));