/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.charting.plot3d.Cylinders"]||(dojo._hasResource["dojox.charting.plot3d.Cylinders"]=!0,dojo.provide("dojox.charting.plot3d.Cylinders"),dojo.require("dojox.charting.plot3d.Base"),function(){dojo.declare("dojox.charting.plot3d.Cylinders",dojox.charting.plot3d.Base,{constructor:function(c,d,a){this.depth="auto";this.gap=0;this.data=[];this.material={type:"plastic",finish:"shiny",color:"lime"};this.outline=null;if(a){if("depth"in a)this.depth=a.depth;if("gap"in a)this.gap=a.gap;
if("material"in a)c=a.material,typeof c=="string"||c instanceof dojo.Color?this.material.color=c:this.material=c;if("outline"in a)this.outline=a.outline}},getDepth:function(){if(this.depth=="auto"){var c=this.width;this.data&&this.data.length&&(c/=this.data.length);return c-2*this.gap}return this.depth},generate:function(c,d){if(!this.data)return this;for(var a=this.width/this.data.length,g=0,h=this.height,b=this.data,j=Math.max,e=void 0,b=typeof b=="string"?b.split(""):b,e=e||dojo.global,f=b[0],
i=1;i<b.length;f=j.call(e,f,b[i++]));h/=f;if(!d)d=c.view;for(b=0;b<this.data.length;++b,g+=a)d.createCylinder({center:{x:g+a/2,y:0,z:0},radius:a/2-this.gap,height:this.data[b]*h}).setTransform(dojox.gfx3d.matrix.rotateXg(-90)).setFill(this.material).setStroke(this.outline)}})}());