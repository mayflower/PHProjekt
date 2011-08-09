/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.charting.widget.Sparkline"]||(dojo._hasResource["dojox.charting.widget.Sparkline"]=!0,dojo.provide("dojox.charting.widget.Sparkline"),dojo.require("dojox.charting.widget.Chart2D"),dojo.require("dojox.charting.themes.GreySkies"),dojo.require("dojox.charting.plot2d.Lines"),function(){var b=dojo;dojo.declare("dojox.charting.widget.Sparkline",dojox.charting.widget.Chart2D,{theme:dojox.charting.themes.GreySkies,margins:{l:0,r:0,t:0,b:0},type:"Lines",valueFn:"Number(x)",store:"",
field:"",query:"",queryOptions:"",start:"0",count:"Infinity",sort:"",data:"",name:"default",buildRendering:function(){var a=this.srcNodeRef;if(!a.childNodes.length||!b.query("> .axis, > .plot, > .action, > .series",a).length){var d=document.createElement("div");b.attr(d,{"class":"plot",name:"default",type:this.type});a.appendChild(d);var c=document.createElement("div");b.attr(c,{"class":"series",plot:"default",name:this.name,start:this.start,count:this.count,valueFn:this.valueFn});b.forEach(["store",
"field","query","queryOptions","sort","data"],function(a){this[a].length&&b.attr(c,a,this[a])},this);a.appendChild(c)}this.inherited(arguments)}})}());