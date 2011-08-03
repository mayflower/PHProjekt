/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.charting.plot3d.Bars"]||(dojo._hasResource["dojox.charting.plot3d.Bars"]=!0,dojo.provide("dojox.charting.plot3d.Bars"),dojo.require("dojox.charting.plot3d.Base"),function(){dojo.declare("dojox.charting.plot3d.Bars",dojox.charting.plot3d.Base,{constructor:function(c,d,a){this.depth="auto";this.gap=0;this.data=[];this.material={type:"plastic",finish:"dull",color:"lime"};if(a){if("depth"in a)this.depth=a.depth;if("gap"in a)this.gap=a.gap;if("material"in a)c=a.material,typeof c==
"string"||c instanceof dojo.Color?this.material.color=c:this.material=c}},getDepth:function(){if(this.depth=="auto"){var c=this.width;this.data&&this.data.length&&(c/=this.data.length);return c-2*this.gap}return this.depth},generate:function(c,d){if(!this.data)return this;for(var a=this.width/this.data.length,e=0,j=this.depth=="auto"?a-2*this.gap:this.depth,h=this.height,b=this.data,k=Math.max,f=void 0,b=typeof b=="string"?b.split(""):b,f=f||dojo.global,g=b[0],i=1;i<b.length;g=k.call(f,g,b[i++]));
h/=g;if(!d)d=c.view;for(b=0;b<this.data.length;++b,e+=a)d.createCube({bottom:{x:e+this.gap,y:0,z:0},top:{x:e+a-this.gap,y:this.data[b]*h,z:j}}).setFill(this.material)}})}());