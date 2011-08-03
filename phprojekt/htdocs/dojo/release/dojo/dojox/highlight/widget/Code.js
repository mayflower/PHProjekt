/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.highlight.widget.Code"]||(dojo._hasResource["dojox.highlight.widget.Code"]=!0,dojo.provide("dojox.highlight.widget.Code"),dojo.require("dijit._Widget"),dojo.require("dijit._Templated"),dojo.require("dojox.highlight"),dojo.declare("highlight.Code",[dijit._Widget,dijit._Templated],{url:"",range:null,style:"",listType:"1",lang:"",templateString:'<div class="formatted" style="${style}"><div class="titleBar"></div><ol type="${listType}" dojoAttachPoint="codeList" class="numbers"></ol><div style="display:none" dojoAttachPoint="containerNode"></div></div>',
postCreate:function(){this.inherited(arguments);this.url?dojo.xhrGet({url:this.url,load:dojo.hitch(this,"_populate"),error:dojo.hitch(this,"_loadError")}):this._populate(this.containerNode.innerHTML)},_populate:function(a){this.containerNode.innerHTML="<pre><code class='"+this.lang+"'>"+a.replace(/\</g,"&lt;")+"</code></pre>";dojo.query("pre > code",this.containerNode).forEach(dojox.highlight.init);a=this.containerNode.innerHTML.split("\n");dojo.forEach(a,function(a,b){var c=dojo.doc.createElement("li");
dojo.addClass(c,b%2!==0?"even":"odd");a=("<pre><code>"+a+"&nbsp;</code></pre>").replace(/\t/g," &nbsp; ");c.innerHTML=a;this.codeList.appendChild(c)},this);this._lines=dojo.query("li",this.codeList);this._updateView()},setRange:function(a){if(dojo.isArray(a))this.range=a,this._updateView()},_updateView:function(){if(this.range){var a=this.range;this._lines.style({display:"none"}).filter(function(d,b){return b+1>=a[0]&&b+1<=a[1]}).style({display:""});dojo.attr(this.codeList,"start",a[0])}},_loadError:function(a){console.warn("loading: ",
this.url," FAILED",a)}}));