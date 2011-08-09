/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.drawing.plugins.drawing.Grid"])dojo._hasResource["dojox.drawing.plugins.drawing.Grid"]=!0,dojo.provide("dojox.drawing.plugins.drawing.Grid"),dojo.require("dojox.drawing.plugins._Plugin"),dojox.drawing.plugins.drawing.Grid=dojox.drawing.util.oo.declare(dojox.drawing.plugins._Plugin,function(a){if(a.gap)this.major=a.gap;this.majorColor=a.majorColor||this.majorColor;this.minorColor=a.minorColor||this.minorColor;this.setGrid();dojo.connect(this.canvas,"setZoom",this,"setZoom")},
{type:"dojox.drawing.plugins.drawing.Grid",gap:100,major:100,minor:0,majorColor:"#00ffff",minorColor:"#d7ffff",zoom:1,setZoom:function(a){this.zoom=a;this.setGrid()},setGrid:function(){var a=Math.floor(this.major*this.zoom),e=this.minor?Math.floor(this.minor*this.zoom):a;this.grid&&this.grid.removeShape();var c,g,d,h,b,i,j,f=this.canvas.underlay.createGroup(),k=this.majorColor,l=this.minorColor,m=function(a,b,c,d,e){f.createLine({x1:a,y1:b,x2:c,y2:d}).setStroke({style:"Solid",width:1,cap:"round",
color:e})};b=1;for(j=1E3/e;b<j;b++)c=0,g=2E3,h=d=e*b,i=d%a?l:k,m(c,d,g,h,i);b=1;for(j=2E3/e;b<j;b++)d=0,h=1E3,g=c=e*b,i=c%a?l:k,m(c,d,g,h,i);f.moveToBack();this.grid=f;this.util.attr(f,"id","grid");return f}});