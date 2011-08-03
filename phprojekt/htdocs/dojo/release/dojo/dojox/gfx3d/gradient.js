/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.gfx3d.gradient"]||(dojo._hasResource["dojox.gfx3d.gradient"]=!0,dojo.provide("dojox.gfx3d.gradient"),dojo.require("dojox.gfx3d.vector"),dojo.require("dojox.gfx3d.matrix"),function(){var i=function(d,b){return Math.sqrt(Math.pow(b.x-d.x,2)+Math.pow(b.y-d.y,2))};dojox.gfx3d.gradient=function(d,b,a,e,c,h,f){var g=dojox.gfx3d.matrix,j=dojox.gfx3d.vector,f=g.normalize(f),k=g.multiplyPoint(f,e*Math.cos(c)+a.x,e*Math.sin(c)+a.y,a.z),l=g.multiplyPoint(f,e*Math.cos(h)+a.x,e*Math.sin(h)+
a.y,a.z),m=g.multiplyPoint(f,a.x,a.y,a.z),r=(h-c)/32,s=i(k,l)/2,n=d[b.type],o=b.finish,b=b.color,p=[{offset:0,color:n.call(d,j.substract(k,m),o,b)}];for(c+=r;c<h;c+=r){var q=g.multiplyPoint(f,e*Math.cos(c)+a.x,e*Math.sin(c)+a.y,a.z),t=i(k,q),u=i(l,q);p.push({offset:t/(t+u),color:n.call(d,j.substract(q,m),o,b)})}p.push({offset:1,color:n.call(d,j.substract(l,m),o,b)});return{type:"linear",x1:0,y1:-s,x2:0,y2:s,colors:p}}}());